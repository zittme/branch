<?php

namespace Zittme\Modules\Branch\Models;

/**
 * 지점 — 매장 정보의 단일 출처.
 *
 * 예약, 주문 같은 다른 모듈은 지점을 스스로 갖지 않고 branch_srl 로 참조한다.
 * 지점이 늘거나 영업시간이 바뀌어도 고칠 곳은 여기 한 군데다.
 *
 * 다른 모듈이 쓰는 것은 get, getList, getTodayHours, isOpenNow 넷이다.
 * 이 넷의 반환 형태는 바꾸지 않는다.
 */
class Branch
{
	public const STATUS_OPEN = 'open';
	public const STATUS_READY = 'ready';
	public const STATUS_CLOSED = 'closed';

	public const REPEAT_ONCE = 'once';
	public const REPEAT_MONTHLY_NTH = 'monthly_nth';

	/**
	 * 요청 한 번 안에서 같은 지점을 여러 번 묻는다. 그때마다 조회하지 않는다.
	 *
	 * @var array
	 */
	protected static $_cache = [];

	/**
	 * 지점 하나.
	 *
	 * @param int $branch_srl
	 * @return ?object
	 */
	public static function get(int $branch_srl): ?object
	{
		if ($branch_srl <= 0)
		{
			return null;
		}
		if (array_key_exists($branch_srl, self::$_cache))
		{
			return self::$_cache[$branch_srl];
		}

		$output = executeQuery('branch.getBranch', (object)['branch_srl' => $branch_srl]);
		$branch = ($output->toBool() && is_object($output->data) && !empty($output->data->branch_srl)) ? Lang::branch($output->data) : null;

		return self::$_cache[$branch_srl] = $branch;
	}

	/**
	 * 그 번호의 지점이 실제로 있는가.
	 *
	 * @param int $branch_srl
	 * @return bool
	 */
	public static function exists(int $branch_srl): bool
	{
		if ($branch_srl <= 0)
		{
			return false;
		}

		// 캐시에는 없는 것으로 기억된 값이 있을 수 있다. 저장 판단은 표를 직접 본다
		$output = executeQuery('branch.getBranch', (object)['branch_srl' => $branch_srl]);

		return $output->toBool() && is_object($output->data) && !empty($output->data->branch_srl);
	}

	/**
	 * 코드로 지점 하나. 외부 연동에서 번호 대신 쓴다.
	 *
	 * @param string $code
	 * @return ?object
	 */
	public static function getByCode(string $code): ?object
	{
		$code = trim($code);
		if ($code === '')
		{
			return null;
		}

		$output = executeQuery('branch.getBranchByCode', (object)['code' => $code]);
		return ($output->toBool() && is_object($output->data) && !empty($output->data->branch_srl)) ? Lang::branch($output->data) : null;
	}

	/**
	 * 지점 목록.
	 *
	 * @param array $filters status, region, search_keyword
	 * @return array branch_srl 을 키로 하는 목록
	 */
	public static function getList(array $filters = []): array
	{
		$args = new \stdClass;
		foreach (['status', 'region', 'search_keyword'] as $key)
		{
			if (!empty($filters[$key]))
			{
				$args->{$key} = $filters[$key];
			}
		}

		$output = executeQueryArray('branch.getBranchList', $args);
		if (!$output->toBool() || !is_array($output->data))
		{
			return [];
		}

		$list = [];
		foreach ($output->data as $row)
		{
			$list[(int)$row->branch_srl] = Lang::branch($row);
			self::$_cache[(int)$row->branch_srl] = $row;
		}

		return $list;
	}

	/**
	 * 영업 중인 지점만.
	 *
	 * @return array
	 */
	public static function getOpenList(): array
	{
		return self::getList(['status' => self::STATUS_OPEN]);
	}

	/**
	 * 지점이 몇 곳인가. 한 곳뿐이면 화면에서 지점 고르는 단계를 건너뛴다.
	 *
	 * @return int
	 */
	public static function countOpen(): int
	{
		return count(self::getOpenList());
	}

	/**
	 * 지역 목록. 목록 화면의 지역 거르개를 만든다.
	 *
	 * @return array
	 */
	public static function getRegions(): array
	{
		$regions = [];
		foreach (self::getOpenList() as $branch)
		{
			$region = trim((string)$branch->region);
			if ($region !== '' && !in_array($region, $regions, true))
			{
				$regions[] = $region;
			}
		}

		sort($regions);
		return $regions;
	}

	/**
	 * 요일별 영업시간.
	 *
	 * @param int $branch_srl
	 * @return array 요일(0-6)을 키로 하는 목록
	 */
	public static function getHours(int $branch_srl): array
	{
		if ($branch_srl <= 0)
		{
			return [];
		}

		$output = executeQueryArray('branch.getHourList', (object)['branch_srl' => $branch_srl]);
		if (!$output->toBool() || !is_array($output->data))
		{
			return [];
		}

		$hours = [];
		foreach ($output->data as $row)
		{
			$hours[(int)$row->weekday] = $row;
		}

		return $hours;
	}

	/**
	 * 그날의 영업시간. 휴무면 null 을 준다.
	 *
	 * @param int $branch_srl
	 * @param ?string $date YYYYMMDD. 비우면 오늘
	 * @return ?object
	 */
	public static function getTodayHours(int $branch_srl, ?string $date = null): ?object
	{
		$date = $date ?: date('Ymd');
		if (!preg_match('/^\d{8}$/', $date))
		{
			return null;
		}
		if (self::isHoliday($branch_srl, $date))
		{
			return null;
		}

		$hours = self::getHours($branch_srl);
		$weekday = (int)date('w', strtotime($date));
		$today = $hours[$weekday] ?? null;

		if (!$today || (string)$today->is_closed === 'Y')
		{
			return null;
		}
		if (trim((string)$today->open_time) === '' || trim((string)$today->close_time) === '')
		{
			return null;
		}

		return $today;
	}

	/**
	 * 그날이 휴무일인가. 특정 날짜와 매달 n번째 요일 반복을 함께 본다.
	 *
	 * @param int $branch_srl
	 * @param string $date YYYYMMDD
	 * @return bool
	 */
	public static function isHoliday(int $branch_srl, string $date): bool
	{
		if ($branch_srl <= 0 || !preg_match('/^\d{8}$/', $date))
		{
			return false;
		}

		$output = executeQueryArray('branch.getHolidayList', (object)['branch_srl' => $branch_srl]);
		if (!$output->toBool() || !is_array($output->data))
		{
			return false;
		}

		$timestamp = strtotime($date);
		$weekday = (int)date('w', $timestamp);
		// 그 달에서 몇 번째 해당 요일인가. 1일부터 세어 7일마다 한 번씩 돌아온다
		$nth = (int)ceil((int)date('j', $timestamp) / 7);

		foreach ($output->data as $row)
		{
			if ((string)$row->repeat_type === self::REPEAT_ONCE)
			{
				if ((string)$row->holiday_date === $date)
				{
					return true;
				}
				continue;
			}

			if ((string)$row->repeat_type === self::REPEAT_MONTHLY_NTH)
			{
				if ((int)$row->weekday === $weekday && (int)$row->nth === $nth)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * 지금 영업 중인가. 쉬는 시간도 영업 중이 아니다.
	 *
	 * @param int $branch_srl
	 * @param ?int $timestamp 비우면 지금
	 * @return bool
	 */
	public static function isOpenNow(int $branch_srl, ?int $timestamp = null): bool
	{
		$timestamp = $timestamp ?: time();
		$branch = self::get($branch_srl);
		if (!$branch || (string)$branch->status !== self::STATUS_OPEN)
		{
			return false;
		}

		$today = self::getTodayHours($branch_srl, date('Ymd', $timestamp));
		if (!$today)
		{
			return false;
		}

		$now = (int)date('G', $timestamp) * 60 + (int)date('i', $timestamp);
		$open = self::toMinutes((string)$today->open_time);
		$close = self::toMinutes((string)$today->close_time);
		if ($open === null || $close === null)
		{
			return false;
		}

		// 자정을 넘겨 닫는 매장은 종료 시각이 시작보다 작다
		$is_open = $close > $open ? ($now >= $open && $now < $close) : ($now >= $open || $now < $close);
		if (!$is_open)
		{
			return false;
		}

		$break_start = self::toMinutes((string)$today->break_start);
		$break_end = self::toMinutes((string)$today->break_end);
		if ($break_start !== null && $break_end !== null && $break_end > $break_start)
		{
			if ($now >= $break_start && $now < $break_end)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * 사진 목록. 저장은 JSON 이고 읽을 때는 배열로 준다.
	 *
	 * @param object $branch
	 * @return array
	 */
	public static function getImages(object $branch): array
	{
		$raw = trim((string)($branch->images ?? ''));
		if ($raw === '')
		{
			return [];
		}

		$list = json_decode($raw, true);
		return is_array($list) ? array_values(array_filter($list, 'strlen')) : [];
	}

	/**
	 * 편의시설 목록. 저장은 쉼표 구분이다.
	 *
	 * @param object $branch
	 * @return array
	 */
	public static function getAmenities(object $branch): array
	{
		$raw = trim((string)($branch->amenities ?? ''));
		if ($raw === '')
		{
			return [];
		}

		return array_values(array_filter(array_map('trim', explode(',', $raw)), 'strlen'));
	}

	/**
	 * 지도에 찍을 좌표가 있는가.
	 *
	 * @param object $branch
	 * @return bool
	 */
	public static function hasCoords(object $branch): bool
	{
		return is_numeric($branch->lat ?? null) && is_numeric($branch->lng ?? null);
	}

	/**
	 * 지점 저장. branch_srl 이 있으면 수정이다.
	 *
	 * @param object $args
	 * @return int 저장된 branch_srl. 실패하면 0
	 */
	public static function save(object $args): int
	{
		$branch_srl = (int)($args->branch_srl ?? 0);
		$args->last_update = date('YmdHis');

		/*
		  번호가 있어도 행이 없으면 새로 넣는다.
		  에디터에 올린 사진이 붙을 자리가 필요해 저장 전에 번호를 먼저 뽑는 경우가 있다.
		  번호만 보고 수정으로 넘기면 아무것도 저장되지 않는다.
		*/
		if ($branch_srl > 0 && self::exists($branch_srl))
		{
			$output = executeQuery('branch.updateBranch', $args);
			unset(self::$_cache[$branch_srl]);
			return $output->toBool() ? $branch_srl : 0;
		}

		if ($branch_srl <= 0)
		{
			$branch_srl = getNextSequence();
		}

		$args->branch_srl = $branch_srl;
		$args->regdate = $args->last_update;

		$output = executeQuery('branch.insertBranch', $args);
		unset(self::$_cache[$branch_srl]);

		return $output->toBool() ? $branch_srl : 0;
	}

	/**
	 * 요일별 영업시간을 통째로 다시 쓴다.
	 *
	 * @param int $branch_srl
	 * @param array $rows 요일을 키로 하는 배열
	 * @return bool
	 */
	public static function replaceHours(int $branch_srl, array $rows): bool
	{
		if ($branch_srl <= 0)
		{
			return false;
		}

		$output = executeQuery('branch.deleteHours', (object)['branch_srl' => $branch_srl]);
		if (!$output->toBool())
		{
			return false;
		}

		$now = date('YmdHis');
		foreach ($rows as $weekday => $row)
		{
			$weekday = (int)$weekday;
			if ($weekday < 0 || $weekday > 6)
			{
				continue;
			}

			$args = new \stdClass;
			$args->hour_srl = getNextSequence();
			$args->branch_srl = $branch_srl;
			$args->weekday = $weekday;
			$args->open_time = trim((string)($row['open_time'] ?? ''));
			$args->close_time = trim((string)($row['close_time'] ?? ''));
			$args->break_start = trim((string)($row['break_start'] ?? ''));
			$args->break_end = trim((string)($row['break_end'] ?? ''));
			$args->is_closed = !empty($row['is_closed']) ? 'Y' : 'N';
			$args->regdate = $now;

			$output = executeQuery('branch.insertHour', $args);
			if (!$output->toBool())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * 지점을 지운다. 영업시간과 휴무일도 함께 지운다.
	 *
	 * @param int $branch_srl
	 * @return bool
	 */
	public static function remove(int $branch_srl): bool
	{
		if ($branch_srl <= 0)
		{
			return false;
		}

		executeQuery('branch.deleteHours', (object)['branch_srl' => $branch_srl]);

		foreach (self::getHolidays($branch_srl) as $holiday)
		{
			executeQuery('branch.deleteHoliday', (object)[
				'holiday_srl' => (int)$holiday->holiday_srl,
				'branch_srl' => $branch_srl,
			]);
		}

		$output = executeQuery('branch.deleteBranch', (object)['branch_srl' => $branch_srl]);
		unset(self::$_cache[$branch_srl]);

		return $output->toBool();
	}

	/**
	 * 휴무일 목록.
	 *
	 * @param int $branch_srl
	 * @return array
	 */
	public static function getHolidays(int $branch_srl): array
	{
		if ($branch_srl <= 0)
		{
			return [];
		}

		$output = executeQueryArray('branch.getHolidayList', (object)['branch_srl' => $branch_srl]);
		return ($output->toBool() && is_array($output->data)) ? $output->data : [];
	}

	/**
	 * HH:MM 을 자정 기준 분으로.
	 *
	 * @param string $time
	 * @return ?int
	 */
	public static function toMinutes(string $time): ?int
	{
		$time = trim($time);
		if (!preg_match('/^(\d{1,2}):(\d{2})$/', $time, $m))
		{
			return null;
		}

		$hour = (int)$m[1];
		$minute = (int)$m[2];
		if ($hour > 24 || $minute > 59)
		{
			return null;
		}

		return $hour * 60 + $minute;
	}
}

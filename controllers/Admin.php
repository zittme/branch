<?php

namespace Zittme\Modules\Branch\Controllers;

use Zittme\Modules\Branch\Models\Branch as BranchModel;
use Zittme\Modules\Branch\Models\Lang;

/**
 * 지점 관리 화면.
 */
class Admin extends Base
{
	/**
	 * 공통 컨텍스트 + 템플릿.
	 *
	 * @param string $tab
	 * @param string $file
	 * @return void
	 */
	protected function renderView(string $tab, string $file): void
	{
		\Context::set('bra_tab', $tab);
		$this->setTemplatePath($this->module_path . 'views/admin/');
		$this->setTemplateFile($file);
	}

	/**
	 * 지점 목록.
	 */
	public function dispBranchAdminList()
	{
		$branches = BranchModel::getList();

		// 목록에서 오늘 여는지 바로 보이게 한다. 지점마다 영업시간을 열어 볼 필요가 없다
		$today = [];
		foreach ($branches as $branch_srl => $branch)
		{
			$hours = BranchModel::getTodayHours($branch_srl);
			$today[$branch_srl] = $hours ? ($hours->open_time . ' - ' . $hours->close_time) : '';
		}

		\Context::set('branches', array_values($branches));
		\Context::set('today_hours', $today);
		$this->renderView('list', 'list');
	}

	/**
	 * 지점 편집 - 기본 정보, 영업시간, 휴무일을 한 화면에서.
	 */
	public function dispBranchAdminEdit()
	{
		$branch_srl = (int)\Context::get('branch_srl');
		$branch = null;
		$hours = [];
		$holidays = [];
		$images = [];
		$is_new = $branch_srl <= 0;

		if (!$is_new)
		{
			$branch = BranchModel::get($branch_srl);
			if (!$branch)
			{
				return new \BaseObject(-1, 'msg_branch_not_found');
			}

			$hours = BranchModel::getHours($branch_srl);
			$holidays = BranchModel::getHolidays($branch_srl);
			$images = BranchModel::getImages($branch);
		}
		else
		{
			// 에디터에 올린 사진이 붙을 자리가 필요하다. 저장 전에 번호를 미리 뽑는다
			$branch_srl = getNextSequence();
		}

		/*
		  코어 에디터를 붙인다. 마지막 인자(content)와 같은 이름의 필드가 폼 안에
		  있어야 기존 내용을 읽어 오고, 제출 직전에 그 필드로 내용을 옮겨야 저장된다.
		  둘 중 하나만 빠져도 조용히 빈 값이 된다.
		*/
		$editor = \EditorModel::getModuleEditor('module', self::instanceSrl(), $branch_srl, 'branch_srl', 'content');

		\Context::set('is_new', $is_new);
		\Context::set('form_branch_srl', $branch_srl);
		\Context::set('editor', $editor);

		\Context::set('branch', $branch);
		\Context::set('hours', $hours);
		\Context::set('holidays', $holidays);
		\Context::set('images_text', implode("\n", $images));
		$this->renderView('edit', 'edit');
	}

	/**
	 * 다국어 코드 목록 — 이미 만들어 둔 코드를 골라 쓰기 위한 검색.
	 */
	public function procBranchAdminGetLangCodes()
	{
		$rows = [];
		foreach (Lang::search((string)\Context::get('keyword'), 40) as $row)
		{
			$rows[] = ['code' => $row->code, 'value' => $row->value];
		}
		$this->add('codes', $rows);
	}

	/**
	 * 다국어 코드 하나의 언어별 값.
	 */
	public function procBranchAdminGetLangCode()
	{
		$code = Lang::filterCode((string)\Context::get('code'));
		$this->add('code', $code);
		$this->add('values', Lang::values($code));
	}

	/**
	 * 다국어 코드 저장 — 코어 lang 테이블에 그대로 쓴다.
	 */
	public function procBranchAdminSaveLangCode()
	{
		$values = \Context::get('values');
		$code = Lang::save((string)\Context::get('code'), is_array($values) ? $values : []);
		if ($code === '')
		{
			return new \BaseObject(-1, lang('branch.branch_adm_lang_need_value'));
		}
		$this->add('code', $code);
		$this->add('value', Lang::display($code));
	}

	/**
	 * 지점 저장.
	 */
	public function procBranchAdminInsert()
	{
		$name = trim((string)\Context::get('name'));
		if ($name === '')
		{
			return new \BaseObject(-1, 'msg_branch_need_name');
		}

		$code = trim((string)\Context::get('code'));
		$branch_srl = (int)\Context::get('branch_srl');

		// 코드는 외부 연동의 열쇠다. 겹치면 어느 지점인지 알 수 없다
		if ($code !== '')
		{
			$exists = BranchModel::getByCode($code);
			if ($exists && (int)$exists->branch_srl !== $branch_srl)
			{
				return new \BaseObject(-1, 'msg_branch_duplicate_code');
			}
		}

		$args = (object)[
			'branch_srl' => $branch_srl,
			'module_srl' => self::instanceSrl(),
			'code' => mb_substr($code, 0, 40),
			'name' => Lang::fromRequest('name', mb_substr($name, 0, 150)),
			'summary' => Lang::fromRequest('summary', mb_substr(trim((string)\Context::get('summary')), 0, 250)),
			// 에디터로 쓴 HTML 이다. 그대로 두면 스크립트가 섞일 수 있으므로 걸러 담는다
			'content' => Lang::fromRequest('content', \Zittme\Framework\Filters\HTMLFilter::clean((string)\Context::get('content'))),
			'thumb' => mb_substr(trim((string)\Context::get('thumb')), 0, 250),
			'images' => self::collectImages(),
			'tel' => mb_substr(trim((string)\Context::get('tel')), 0, 40),
			'postcode' => mb_substr(trim((string)\Context::get('postcode')), 0, 10),
			'address' => mb_substr(trim((string)\Context::get('address')), 0, 250),
			'address_detail' => Lang::fromRequest('address_detail', mb_substr(trim((string)\Context::get('address_detail')), 0, 250)),
			'lat' => self::cleanCoord((string)\Context::get('lat')),
			'lng' => self::cleanCoord((string)\Context::get('lng')),
			'region' => Lang::fromRequest('region', mb_substr(trim((string)\Context::get('region')), 0, 60)),
			'amenities' => Lang::fromRequest('amenities', mb_substr(trim((string)\Context::get('amenities')), 0, 250)),
			'status' => in_array((string)\Context::get('status'), [BranchModel::STATUS_OPEN, BranchModel::STATUS_READY, BranchModel::STATUS_CLOSED], true)
				? (string)\Context::get('status') : BranchModel::STATUS_OPEN,
			'list_order' => (int)\Context::get('list_order'),
		];

		$branch_srl = BranchModel::save($args);
		if ($branch_srl <= 0)
		{
			return new \BaseObject(-1, 'msg_branch_save_failed');
		}

		BranchModel::replaceHours($branch_srl, self::collectHours());

		// 에디터로 올린 사진을 이 지점에 묶어 둔다. 안 하면 임시 파일로 보고 나중에 지워진다
		\FileController::getInstance()->setFilesValid($branch_srl);

		$this->setMessage('success_registed');
		$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispBranchAdminEdit', 'branch_srl', $branch_srl));
	}

	/**
	 * 지점 삭제.
	 */
	public function procBranchAdminDelete()
	{
		$branch_srl = (int)\Context::get('branch_srl');
		if (!BranchModel::remove($branch_srl))
		{
			return new \BaseObject(-1, 'msg_branch_not_found');
		}

		$this->setMessage('success_deleted');
		$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispBranchAdminList'));
	}

	/**
	 * 휴무일 추가.
	 */
	public function procBranchAdminInsertHoliday()
	{
		$branch_srl = (int)\Context::get('branch_srl');
		if (!BranchModel::get($branch_srl))
		{
			return new \BaseObject(-1, 'msg_branch_not_found');
		}

		$repeat = (string)\Context::get('repeat_type') === BranchModel::REPEAT_MONTHLY_NTH
			? BranchModel::REPEAT_MONTHLY_NTH : BranchModel::REPEAT_ONCE;

		$args = new \stdClass;
		$args->holiday_srl = getNextSequence();
		$args->branch_srl = $branch_srl;
		$args->repeat_type = $repeat;
		$args->reason = mb_substr(trim((string)\Context::get('reason')), 0, 250);
		$args->regdate = self::now();

		if ($repeat === BranchModel::REPEAT_ONCE)
		{
			$date = preg_replace('/\D/', '', (string)\Context::get('holiday_date'));
			if (strlen($date) !== 8)
			{
				return new \BaseObject(-1, 'msg_invalid_request');
			}
			$args->holiday_date = $date;
			$args->nth = 0;
			$args->weekday = 0;
		}
		else
		{
			$nth = (int)\Context::get('nth');
			$weekday = (int)\Context::get('weekday');
			if ($nth < 1 || $nth > 5 || $weekday < 0 || $weekday > 6)
			{
				return new \BaseObject(-1, 'msg_invalid_request');
			}
			$args->holiday_date = '';
			$args->nth = $nth;
			$args->weekday = $weekday;
		}

		$output = executeQuery('branch.insertHoliday', $args);
		if (!$output->toBool())
		{
			return $output;
		}

		$this->setMessage('success_registed');
		$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispBranchAdminEdit', 'branch_srl', $branch_srl));
	}

	/**
	 * 휴무일 삭제.
	 */
	public function procBranchAdminDeleteHoliday()
	{
		$branch_srl = (int)\Context::get('branch_srl');
		$holiday_srl = (int)\Context::get('holiday_srl');
		if ($branch_srl <= 0 || $holiday_srl <= 0)
		{
			return new \BaseObject(-1, 'msg_invalid_request');
		}

		executeQuery('branch.deleteHoliday', (object)[
			'holiday_srl' => $holiday_srl,
			'branch_srl' => $branch_srl,
		]);

		$this->setMessage('success_deleted');
		$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispBranchAdminEdit', 'branch_srl', $branch_srl));
	}

	/**
	 * 화면에서 적은 요일별 영업시간을 모은다.
	 *
	 * @return array
	 */
	protected static function collectHours(): array
	{
		$opens = (array)\Context::get('open_time');
		$closes = (array)\Context::get('close_time');
		$break_starts = (array)\Context::get('break_start');
		$break_ends = (array)\Context::get('break_end');
		$closed = (array)\Context::get('day_closed');

		$rows = [];
		for ($weekday = 0; $weekday <= 6; $weekday++)
		{
			$is_closed = in_array((string)$weekday, array_map('strval', $closed), true);
			$open = trim((string)($opens[$weekday] ?? ''));
			$close = trim((string)($closes[$weekday] ?? ''));

			// 휴무도 아니고 시간도 안 적었으면 그 요일은 저장할 것이 없다
			if (!$is_closed && ($open === '' || $close === ''))
			{
				continue;
			}

			$rows[$weekday] = [
				'open_time' => $open,
				'close_time' => $close,
				'break_start' => trim((string)($break_starts[$weekday] ?? '')),
				'break_end' => trim((string)($break_ends[$weekday] ?? '')),
				'is_closed' => $is_closed,
			];
		}

		return $rows;
	}

	/**
	 * 사진 주소를 줄 단위로 받아 JSON 으로.
	 *
	 * @return string
	 */
	protected static function collectImages(): string
	{
		$raw = trim((string)\Context::get('images_text'));
		if ($raw === '')
		{
			return '';
		}

		$list = array_values(array_filter(array_map('trim', preg_split('/\R/', $raw)), 'strlen'));
		return count($list) ? json_encode($list, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES) : '';
	}

	/**
	 * 좌표는 숫자만 받는다. 잘못 적으면 지도가 엉뚱한 곳을 가리킨다.
	 *
	 * @param string $value
	 * @return string
	 */
	protected static function cleanCoord(string $value): string
	{
		$value = trim($value);
		return is_numeric($value) ? $value : '';
	}
}

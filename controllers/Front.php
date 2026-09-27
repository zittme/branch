<?php

namespace Zittme\Modules\Branch\Controllers;

use Zittme\Modules\Branch\Models\Branch as BranchModel;

/**
 * 지점 안내 화면.
 *
 * 스킨으로 그린다. 테마가 자기 스킨을 얹어 매장 색을 입힐 수 있다.
 */
class Front extends Base
{
	/**
	 * 스킨 경로를 잡는다.
	 *
	 * 스킨 이름을 경로에 그대로 붙이면 안 된다. 테마가 적용된 사이트에서는
	 * 이름이 "테마|@|스킨" 형태라 존재하지 않는 경로가 된다.
	 *
	 * @return void
	 */
	protected function prepareSkin(): void
	{
		$this->setTemplatePath($this->getSkinPath());
	}

	/**
	 * 지점 목록.
	 */
	public function dispBranchList()
	{
		$region = trim((string)\Context::get('region'));
		$keyword = trim((string)\Context::get('search_keyword'));

		$branches = BranchModel::getList([
			'status' => BranchModel::STATUS_OPEN,
			'search_keyword' => $keyword,
		]);

		// 지역은 화면에 보이는 언어의 값으로 고른다. 저장값이 다국어 코드일 수 있어 조회 조건으로 쓰지 않는다
		if ($region !== '')
		{
			$branches = array_filter($branches, function ($branch) use ($region) {
				return trim((string)$branch->region) === $region;
			});
		}

		// 목록에 지금 여는지 함께 보인다. 손님이 가장 먼저 궁금해하는 것이다
		$open_now = [];
		$today_hours = [];
		foreach ($branches as $branch_srl => $branch)
		{
			$open_now[$branch_srl] = BranchModel::isOpenNow($branch_srl);
			$hours = BranchModel::getTodayHours($branch_srl);
			$today_hours[$branch_srl] = $hours;
		}

		\Context::set('branches', array_values($branches));
		\Context::set('open_now', $open_now);
		\Context::set('today_hours', $today_hours);
		\Context::set('regions', BranchModel::getRegions());
		\Context::set('region', $region);
		\Context::set('search_keyword', $keyword);

		$this->prepareSkin();
		$this->setTemplateFile('list');
	}

	/**
	 * 지점 상세.
	 */
	public function dispBranchView()
	{
		$branch_srl = (int)\Context::get('branch_srl');
		if ($branch_srl <= 0)
		{
			$branch_srl = (int)((BranchModel::getByCode((string)\Context::get('code'))->branch_srl ?? 0));
		}

		$branch = BranchModel::get($branch_srl);
		if (!$branch || (string)$branch->status === BranchModel::STATUS_CLOSED)
		{
			return new \BaseObject(-1, 'msg_branch_not_found');
		}

		\Context::set('branch', $branch);
		\Context::set('hours', BranchModel::getHours($branch_srl));
		\Context::set('holidays', BranchModel::getHolidays($branch_srl));
		\Context::set('images', BranchModel::getImages($branch));
		\Context::set('amenities', BranchModel::getAmenities($branch));
		\Context::set('open_now', BranchModel::isOpenNow($branch_srl));
		\Context::set('today_hours', BranchModel::getTodayHours($branch_srl));
		\Context::setBrowserTitle((string)$branch->name);

		$this->prepareSkin();
		$this->setTemplateFile('view');
	}

	/**
	 * 지점 목록 JSON. 다른 화면에서 지점을 고르게 할 때 쓴다.
	 */
	public function procBranchGetList()
	{
		$list = [];
		foreach (BranchModel::getOpenList() as $branch_srl => $branch)
		{
			$hours = BranchModel::getTodayHours($branch_srl);
			$list[] = [
				'branch_srl' => $branch_srl,
				'code' => (string)$branch->code,
				'name' => (string)$branch->name,
				'address' => trim($branch->address . ' ' . $branch->address_detail),
				'tel' => (string)$branch->tel,
				'region' => (string)$branch->region,
				'lat' => (string)$branch->lat,
				'lng' => (string)$branch->lng,
				'open_now' => BranchModel::isOpenNow($branch_srl),
				'today_open' => $hours ? (string)$hours->open_time : '',
				'today_close' => $hours ? (string)$hours->close_time : '',
			];
		}

		$this->add('branches', $list);
	}
}

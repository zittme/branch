<?php

namespace Zittme\Modules\Branch\Controllers;

/**
 * 코어 확장점 처리.
 */
class Trigger extends Base
{
	/**
	 * 사이트맵의 "모듈 연결" 목록에 자기 자신을 올린다.
	 *
	 * 코어는 인스턴스가 하나 이상 있는 모듈만 목록에 올린다. 그대로 두면 설치 직후
	 * 첫 페이지를 만들 방법이 없다.
	 *
	 * @param array $moduleList
	 * @return object
	 */
	public function addToSitemap(&$moduleList)
	{
		if (is_array($moduleList) && !in_array('branch', $moduleList, true))
		{
			$moduleList[] = 'branch';
		}

		return new \BaseObject();
	}
}

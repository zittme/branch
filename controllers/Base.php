<?php

namespace Zittme\Modules\Branch\Controllers;

/**
 * 공통 바탕.
 *
 * ModuleObject 에는 init() 이 없다. 하위 컨트롤러가 parent::init() 을 부를 수 있게
 * 하는 일이 없더라도 여기에 빈 init() 을 둔다.
 */
class Base extends \ModuleObject
{
	public function init()
	{
	}

	/**
	 * 지점이 매달릴 인스턴스 번호.
	 *
	 * 지점은 사이트에 하나뿐인 목록이다. 관리 화면과 프론트가 다른 값을 쓰면
	 * 한쪽에서 목록이 통째로 비어 보인다. 두 곳 모두 이 함수만 부른다.
	 *
	 * @return int
	 */
	public static function instanceSrl(): int
	{
		return 0;
	}

	/**
	 * 지금 시각.
	 *
	 * @return string
	 */
	public static function now(): string
	{
		return date('YmdHis');
	}

	/**
	 * 스킨 경로.
	 *
	 * 스킨 이름을 경로에 그대로 이어 붙이면 안 된다. 테마가 적용된 사이트에서는
	 * 이름이 "테마|@|스킨" 결합명이라 없는 경로가 되고 화면이 통째로 빈다.
	 *
	 * @return string
	 */
	protected function getSkinPath(): string
	{
		$skin = (string)($this->module_info->skin ?? '');
		// 기본 위임이면 사이트 기본 디자인 값을 따른다. 테마 적용이 이 값을 바꾼다
		if ($skin === '' || $skin === '/USE_DEFAULT/')
		{
			$skin = (string)(\ModuleModel::getModuleDefaultSkin('branch', 'P') ?: 'default');
		}
		// 일반 이름과 결합명만 허용한다. 경로 조작을 막는다
		if (!preg_match('/^[A-Za-z0-9_-]+(\|@\|[A-Za-z0-9_-]+)?$/', $skin))
		{
			$skin = 'default';
		}

		$path = \Zittme\Framework\Theme::resolveSkinPath($this->module_path, $skin, 'skins');
		if (!is_dir($path) && strpos($skin, \Zittme\Framework\Theme::SEPARATOR) === false)
		{
			foreach (array_keys(\Zittme\Framework\Theme::getModuleSkins('branch', 'skins')) as $combined)
			{
				if (substr($combined, -strlen(\Zittme\Framework\Theme::SEPARATOR . $skin)) === \Zittme\Framework\Theme::SEPARATOR . $skin)
				{
					$path = \Zittme\Framework\Theme::resolveSkinPath($this->module_path, $combined, 'skins');
					break;
				}
			}
		}
		if (!is_dir($path))
		{
			$path = $this->module_path . 'skins/default/';
		}

		return rtrim($path, '/') . '/';
	}
}

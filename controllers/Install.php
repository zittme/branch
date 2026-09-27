<?php

namespace Zittme\Modules\Branch\Controllers;

/**
 * 설치와 업데이트.
 *
 * 테이블은 schemas/*.xml 을 보고 코어가 만든다. 스키마에 표나 칼럼을 더하면
 * 반드시 아래 목록에도 같이 적을 것. 코어는 업데이트 때 알아서 붙여 주지 않는다.
 */
class Install extends Base
{
	public const ADDED_TABLES = [];

	public const ADDED_COLUMNS = [];

	public function moduleInstall()
	{
		return new \BaseObject();
	}

	public function checkUpdate()
	{
		$oDB = \DB::getInstance();

		foreach (self::ADDED_TABLES as $table)
		{
			if (!$oDB->isTableExists($table))
			{
				return true;
			}
		}
		foreach (self::ADDED_COLUMNS as [$table, $column])
		{
			if (!$oDB->isColumnExists($table, $column))
			{
				return true;
			}
		}

		return false;
	}

	public function moduleUpdate()
	{
		$oDB = \DB::getInstance();

		foreach (self::ADDED_TABLES as $table)
		{
			if (!$oDB->isTableExists($table))
			{
				$oDB->createTableByXmlFile(\RX_BASEDIR . 'modules/branch/schemas/' . $table . '.xml');
			}
		}
		foreach (self::ADDED_COLUMNS as [$table, $column, $type, $size, $default])
		{
			if (!$oDB->isColumnExists($table, $column))
			{
				$oDB->addColumn($table, $column, $type, $size, $default, $default !== null);
			}
		}

		return new \BaseObject();
	}

	public function recompileCache()
	{
	}
}

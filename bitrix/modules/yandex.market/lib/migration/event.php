<?php

namespace Yandex\Market\Migration;

use Bitrix\Main;
use Yandex\Market;

class Event
{
	public static function canRestore($exception)
	{
		return false;
	}

	public static function check()
	{
		$result = !Version::check('event');

		if ($result)
		{
			Version::update('event');

			static::reset();
		}

		return $result;
	}

	public static function reset()
	{
		$connection = Main\Application::getConnection();
		$trackTableName = Market\Export\Track\Table::getTableName();

		$connection->truncateTable($trackTableName);

		Market\Reference\Event\Controller::deleteAll();
		Market\Reference\Event\Controller::updateRegular();

		$setupList = Market\Export\Setup\Model::loadList([
			'filter' => [ '=AUTOUPDATE' => '1' ]
		]);

		/** @var Market\Export\Setup\Model $setup */
		foreach ($setupList as $setup)
		{
			$setup->handleChanges(true);
		}
	}
}
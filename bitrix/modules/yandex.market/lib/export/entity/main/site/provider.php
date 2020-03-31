<?php

namespace Yandex\Market\Export\Entity\Main\Site;

use Bitrix\Iblock;
use Bitrix\Main;
use Yandex\Market;

class Provider
{
	protected static $domainToIdCache = [];

	public static function getIdByDomain($domain, $path = '')
	{
		$result = null;
		$domain = trim($domain);

		if ($domain === '')
		{
			// nothing
		}
		else if (array_key_exists($domain, static::$domainToIdCache))
		{
			$result = static::$domainToIdCache[$domain];
		}
		else
		{
			$result = static::getIdByDomainFromDomainTable($domain, $path);

			if ($result === null)
			{
				$result = static::getIdByDomainFromSiteTable($domain, $path);
			}

			static::$domainToIdCache[$domain] = $result;
		}

		return $result;
	}

	protected static function getIdByDomainFromSiteTable($domain, $path)
	{
		$result = null;

		$entity = Main\SiteTable::getEntity();
		$connection = $entity->getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$query = Main\SiteTable::getList([
			'filter' => [ '=SERVER_NAME' => $domain ],
			'select' => [ 'LID', 'DIR' ],
			'order' => [
				'DIR_LENGTH' => 'DESC',
				'SORT' => 'ASC',
			],
			'runtime' => [
				new Main\Entity\ExpressionField('DIR_LENGTH', $sqlHelper->getLengthFunction('%s'), [ 'DIR' ]),
			],
		]);

		while ($row = $query->fetch())
		{
			if (static::compareDir($row['DIR'], $path) === 0)
			{
				$result = (string)$row['LID'];
				break;
			}
		}

		return $result;
	}

	protected static function getIdByDomainFromDomainTable($domain, $path)
	{
		$result = null;

		$entity = Main\SiteTable::getEntity();
		$connection = $entity->getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$query = Main\SiteDomainTable::getList([
			'filter' => [ '=DOMAIN' => static::encodeDomain($domain) ],
			'select' => [ 'LID', 'DIR' => 'SITE.DIR' ],
			'order' => [
				'DIR_LENGTH' => 'DESC',
				'SITE.SORT' => 'ASC',
			],
			'runtime' => [
				new Main\Entity\ExpressionField('DIR_LENGTH', $sqlHelper->getLengthFunction('%s'), [ 'SITE.DIR' ])
			],
		]);

		while ($row = $query->fetch())
		{
			if (static::compareDir($row['DIR'], $path) === 0)
			{
				$result = (string)$row['LID'];
				break;
			}
		}

		return $result;
	}

	protected static function encodeDomain($domain)
	{
		$errorList = [];
		$encodedDomain = \CBXPunycode::ToASCII($domain, $errorList);

		return $encodedDomain !== false ? $encodedDomain : $domain;
	}

	protected static function compareDir($firstPath, $secondPath)
	{
		$firstPath = rtrim($firstPath, '/');
		$secondPath = rtrim($secondPath, '/');

		return strcasecmp($firstPath, $secondPath);
	}
}
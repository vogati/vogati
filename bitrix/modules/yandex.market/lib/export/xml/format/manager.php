<?php

namespace Yandex\Market\Export\Xml\Format;

use Bitrix\Main;
use Yandex\Market;

Main\Localization\Loc::loadMessages(__FILE__);

class Manager
{
	const EXPORT_SERVICE_YANDEX_MARKET = 'Yandex.Market';
	const EXPORT_SERVICE_BERU_RU = 'Beru.ru';

	const EXPORT_FORMAT_SIMPLE = 'simple';
	const EXPORT_FORMAT_VENDOR_MODEL = 'vendor.model';
	const EXPORT_FORMAT_BOOK = 'book';
	const EXPORT_FORMAT_AUDIOBOOK = 'audiobook';
	const EXPORT_FORMAT_ARTIST_TITLE = 'artist.title';
	const EXPORT_FORMAT_EVENT_TICKET = 'event-ticket';
	const EXPORT_FORMAT_MEDICINE = 'medicine';
	const EXPORT_FORMAT_TOUR = 'tour';
	const EXPORT_FORMAT_ALCO = 'alco';
	const EXPORT_FORMAT_PRICE = 'price';

	protected static $customServiceList;

	public static function getServiceList()
	{
		$customServiceList = static::getCustomServiceList();

		return array_merge(
			[
				static::EXPORT_SERVICE_YANDEX_MARKET,
				static::EXPORT_SERVICE_BERU_RU
			],
			array_keys($customServiceList)
		);
	}

	public static function getServiceTitle($service)
	{
		$serviceLangKey = str_replace(['.', ' ', '-'], '_', $service);
		$serviceLangKey = strtoupper($serviceLangKey);

		return Market\Config::getLang('EXPORT_XML_FORMAT_SERVICE_' . $serviceLangKey);
	}

	public static function getTypeTitle($type)
	{
		$typeLangKey = str_replace(['.', ' ', '-'], '_', $type);
		$typeLangKey = strtoupper($typeLangKey);

		return Market\Config::getLang('EXPORT_XML_FORMAT_TYPE_' . $typeLangKey, null, $type);
	}

	public static function getTypeList($service)
	{
		$result = null;

		switch ($service)
		{
			case static::EXPORT_SERVICE_YANDEX_MARKET:
				$result = [
					static::EXPORT_FORMAT_VENDOR_MODEL,
					static::EXPORT_FORMAT_SIMPLE,
					static::EXPORT_FORMAT_BOOK,
					static::EXPORT_FORMAT_AUDIOBOOK,
					static::EXPORT_FORMAT_ARTIST_TITLE,
					static::EXPORT_FORMAT_EVENT_TICKET,
					static::EXPORT_FORMAT_MEDICINE,
					static::EXPORT_FORMAT_TOUR,
					static::EXPORT_FORMAT_ALCO
				];
			break;

			case static::EXPORT_SERVICE_BERU_RU:
				$result = [
					static::EXPORT_FORMAT_VENDOR_MODEL,
					static::EXPORT_FORMAT_PRICE,
				];
			break;

			default:
				$customServiceList = static::getCustomServiceList();

				if (isset($customServiceList[$service]))
				{
					$result = array_keys($customServiceList[$service]);
				}
			break;
		}

		return $result;
	}

	/**
	 * @param $type string
	 *
	 * @return Reference\Base
	 */
	public static function getEntity($service, $type)
	{
		$result = null;
		$customServiceList = static::getCustomServiceList();

		if (isset($customServiceList[$service][$type]))
		{
			$className = $customServiceList[$service][$type];
		}
		else
		{
			$className = __NAMESPACE__ . '\\' . str_replace('.', '', $service) . '\\' . str_replace(['.', '-'], '', $type);
		}

		if (class_exists($className))
		{
			$result = new $className;
		}
		else
		{
			throw new Main\ObjectNotFoundException('format ' . $type .' not found for service ' . $service);
		}

		return $result;
	}

	protected static function getCustomServiceList()
	{
		if (static::$customServiceList === null)
		{
			static::$customServiceList = static::loadCustomServiceList();
		}

		return static::$customServiceList;
	}

	protected static function loadCustomServiceList()
	{
		$result = [];

		$event = new Main\Event(Market\Config::getModuleName(), 'onExportXmlFormatBuildList');
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			$eventData = $eventResult->getParameters();

			if (isset($eventData['SERVICE']))
			{
				if (!isset($eventData['TYPE_LIST']))
				{
					throw new Main\ArgumentOutOfRangeException('TYPE_LIST must be defined for service ' . $eventData['SERVICE']);
				}
				else if (!is_array($eventData['TYPE_LIST']))
				{
					throw new Main\ArgumentOutOfRangeException('TYPE_LIST must be array for service ' . $eventData['SERVICE']);
				}
				else if (count($eventData['TYPE_LIST']) === 0)
				{
					throw new Main\ArgumentOutOfRangeException('TYPE_LIST must be not empty for service ' . $eventData['SERVICE']);
				}
				else
				{
					$formatReferenceClassName = 'Yandex\Market\Export\Xml\Format\Reference\Base';

					foreach ($eventData['TYPE_LIST'] as $formatName => $formatClassName)
					{
						if (!is_subclass_of($formatClassName, $formatReferenceClassName))
						{
							throw new Main\ArgumentOutOfRangeException($formatClassName . ' must inherit ' . $formatReferenceClassName . ' for service ' . $eventData['SERVICE']);
						}
					}
				}

				$result[$eventData['SERVICE']] = $eventData['TYPE_LIST'];
			}
		}

		return $result;
	}
}
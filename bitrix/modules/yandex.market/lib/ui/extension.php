<?php

namespace Yandex\Market\Ui;

use Bitrix\Main;

class Extension
{
	public static function loadOne(array $variants, $fallbackFirst = false)
	{
		$name = static::getOne($variants, $fallbackFirst);

		\CJSCore::Init([$name]);
	}

	public static function getOne(array $variants, $fallbackFirst = false)
	{
		$result = null;

		foreach ($variants as $variant)
		{
			if (static::canLoad($variant))
			{
				$result = $variant;
				break;
			}
			else if ($fallbackFirst && $result === null)
			{
				$result = $variant;
			}
		}

		if ($result === null)
		{
			throw new Main\SystemException(sprintf(
				'cant find valid extension from %s',
				implode(', ', $variants)
			));
		}

		return $result;
	}

	public static function canLoad($name)
	{
		$result = true;

		if (!\CJSCore::IsExtRegistered($name))
		{
			$result = false;
		}
		else
		{
			$info = \CJSCore::getExtInfo($name);
			$types = [ 'css', 'js' ];
			$docRoot = Main\Loader::getDocumentRoot();

			foreach ($types as $type)
			{
				if (!isset($info[$type])) { continue; }

				$pathList = (array)$info[$type];

				foreach ($pathList as $path)
				{
					$absolutePath = $docRoot . $path;

					if (!file_exists($absolutePath))
					{
						$result = false;
						break;
					}
				}
			}
		}

		return $result;
	}
}
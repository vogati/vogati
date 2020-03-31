<?php

namespace Yandex\Market\Ui\UserField;

use Bitrix\Main;

class ReferenceType extends EnumerationType
{
	function GetList($arUserField)
	{
		static $cache = [];

		$dataClass = isset($arUserField['SETTINGS']['DATA_CLASS']) ? Main\Entity\Base::normalizeEntityClass($arUserField['SETTINGS']['DATA_CLASS']) : null;

		if ($dataClass === null)
		{
			$values = [];
		}
		else if (isset($cache[$dataClass]))
		{
			$values = $cache[$dataClass];
		}
		else
		{
			$values = [];

			/** @var Main\Entity\DataManager $dataClass*/
			$query = $dataClass::getList([
				'select' => [
					'ID',
					'NAME'
				]
			]);

			while ($row = $query->fetch())
			{
				$values[] = [
					'ID' => $row['ID'],
					'VALUE' => '[' . $row['ID'] . '] ' . $row['NAME']
				];
			}

			$cache[$dataClass] = $values;
		}

		$result = new \CDBResult();
		$result->InitFromArray($values);

		return $result;
	}
}
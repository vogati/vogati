<?php

namespace Yandex\Market\Export\Entity\Iblock\Offer\Field;

use Yandex\Market;
use Bitrix\Main;

Main\Localization\Loc::loadMessages(__FILE__);

class Source extends Market\Export\Entity\Iblock\Element\Field\Source
{
	public function getQuerySelect($select)
	{
		return [
			'ELEMENT' => $select,
			'OFFERS' => $select
		];
	}

	public function getQueryFilter($filter, $select)
	{
		$offersFilter = $filter;
		$distinctFilter = [];

		foreach ($filter as $filterItemKey => $filterItem)
		{
			if ($filterItem['FIELD'] === 'DISTINCT')
			{
				$distinctVariants = $this->getDistinctVariants();
				$distinctFilter[] = $distinctVariants[$filterItem['VALUE']];

				unset($offersFilter[$filterItemKey]);
			}
		}

		$result = [
			'OFFERS' => $this->buildQueryFilter($offersFilter)
		];

		if (!empty($distinctFilter))
		{
			$result['DISTINCT'] = $distinctFilter;
		}

		return $result;
	}

	public function getElementListValues($elementList, $parentList, $select, $queryContext, $sourceValues)
	{
		$result = [];

		$this->preloadFieldValues($elementList, $select, $queryContext);

		foreach ($elementList as $elementId => $element)
		{
			$parent = isset($parentList[$element['PARENT_ID']]) ? $parentList[$element['PARENT_ID']] : null;

			$result[$elementId] = $this->getFieldValues($element, $select, $parent, $queryContext); // extract for all
		}

		return $result;
	}

	public function getFields(array $context = [])
	{
		if (isset($context['OFFER_IBLOCK_ID']))
		{
			$result = parent::getFields($context);
			$langPrefix = $this->getLangPrefix();

			$result[] = [
				'ID' => 'DISTINCT',
				'TYPE' => Market\Export\Entity\Data::TYPE_DISTINCT,
				'VALUE' => Market\Config::getLang($langPrefix . 'FIELD_DISTINCT'),
				'FILTERABLE' => true,
				'SELECTABLE' => false,
				'AUTOCOMPLETE' => false,
			];
		}
		else
		{
			$result = [];
		}

		return $result;
	}

	public function getFieldEnum($field, array $context = [])
	{
		if ($field['ID'] === 'DISTINCT')
		{
			$result = [];
			$langPrefix = $this->getLangPrefix();

			foreach ($this->getDistinctVariants() as $variantKey => $variant)
			{
				$result[] = [
					'ID' => $variantKey,
					'VALUE' => Market\Config::getLang($langPrefix . 'FIELD_DISTINCT_ENUM_' . $variantKey),
				];
			}
		}
		else
		{
			$result = parent::getFieldEnum($field, $context);
		}

		return $result;
	}

	protected function getContextIblockId(array $context = [])
    {
        return isset($context['OFFER_IBLOCK_ID']) ? (int)$context['OFFER_IBLOCK_ID'] : null;
    }

    protected function getLangPrefix()
	{
		return 'IBLOCK_OFFER_FIELD_';
	}

	protected function getDistinctVariants()
	{
		return [
			'AVAILABLE' => [
				'TAG' => 'offer',
				'ATTRIBUTE' => 'available',
				'ORDER' => 'desc',
			],
			'PRICE_MIN' => [
				'TAG' => 'price',
				'ORDER' => 'asc',
			],
			'PRICE_MAX' => [
				'TAG' => 'price',
				'ORDER' => 'desc',
			],
			'OLDPRICE_MIN' => [
				'TAG' => 'oldprice',
				'ORDER' => 'asc',
			],
			'OLDPRICE_MAX' => [
				'TAG' => 'oldprice',
				'ORDER' => 'desc',
			],
			'ID_MIN' => [
				'SOURCE' => $this->getType(),
				'FIELD' => 'ID',
				'ORDER' => 'asc',
			],
			'ID_MAX' => [
				'SOURCE' => $this->getType(),
				'FIELD' => 'ID',
				'ORDER' => 'desc',
			],
		];
	}
}
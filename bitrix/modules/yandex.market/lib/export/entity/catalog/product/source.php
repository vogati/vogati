<?php

namespace Yandex\Market\Export\Entity\Catalog\Product;

use Yandex\Market;
use Bitrix\Main;
use Bitrix\Catalog;

Main\Localization\Loc::loadMessages(__FILE__);

class Source extends Market\Export\Entity\Reference\Source
{
	public function getQuerySelect($select)
	{
		$result = [
			'CATALOG' => []
		];

		if (!Market\Export\Entity\Catalog\Provider::useCatalogShortFields())
		{
			$splitFields = $this->getSplitFields();
			$externalFields = $this->getExternalFields();

			foreach ($select as $fieldName)
			{
				if (isset($splitFields[$fieldName]))
				{
					foreach ($splitFields[$fieldName] as $partFieldName)
					{
						$result['CATALOG'][] = $this->getFieldFullName($partFieldName);
					}
				}
				else if (!in_array($fieldName, $externalFields, true))
				{
					$result['CATALOG'][] = $this->getFieldFullName($fieldName);
				}
			}
		}

		return $result;
	}

	public function isFilterable()
	{
		return true;
	}

	public function getQueryFilter($filter, $select)
	{
		$result = [
			'ELEMENT' => [],
			'CATALOG' => []
		];

		foreach ($filter as $filterItem)
		{
			$fieldFullName = $this->getFieldFullName($filterItem['FIELD']);
			$sourceKey = 'CATALOG';

			if ($filterItem['FIELD'] === 'TYPE')
			{
				$sourceKey = 'ELEMENT';
			}

            $this->pushQueryFilter($result[$sourceKey], $filterItem['COMPARE'], $fieldFullName, $filterItem['VALUE']);
		}

		return $result;
	}

	public function getElementListValues($elementList, $parentList, $select, $queryContext, $sourceValues)
	{
		$result = [];

		if (!empty($elementList) && Main\Loader::includeModule('catalog'))
		{
			$internalData = null;
			$externalData = null;

			if (Market\Export\Entity\Catalog\Provider::useCatalogShortFields())
			{
				$internalData = $this->loadInternalData($elementList, $select);
			}
			else
			{
				$externalData = $this->loadExternalData($elementList, $select);
			}

			foreach ($elementList as $elementId => $element)
			{
				$result[$elementId] = [];
				$elementInternalData = isset($internalData[$elementId]) ? $internalData[$elementId] : null;

				foreach ($select as $fieldName)
				{
					if ($elementInternalData !== null)
					{
						$result[$elementId][$fieldName] = $this->getDisplayValue($elementInternalData, $fieldName, $queryContext);
					}
					else if (isset($externalData[$fieldName]))
					{
						$result[$elementId][$fieldName] = isset($externalData[$fieldName][$elementId])
							? $externalData[$fieldName][$elementId]
							: null;
					}
					else
					{
						$result[$elementId][$fieldName] = $this->getDisplayValue($element, $fieldName, $queryContext);
					}
				}
			}
		}

		return $result;
	}

	public function getFields(array $context = [])
	{
		$result = [];

		if ($context['HAS_CATALOG'])
		{
			$result = $this->buildFieldsDescription([
				'WEIGHT' => [
					'TYPE' => Market\Export\Entity\Data::TYPE_NUMBER
				],
				'LENGTH' => [
					'TYPE' => Market\Export\Entity\Data::TYPE_NUMBER
				],
				'HEIGHT' => [
					'TYPE' => Market\Export\Entity\Data::TYPE_NUMBER
				],
				'WIDTH' => [
					'TYPE' => Market\Export\Entity\Data::TYPE_NUMBER
				],
				'YM_SIZE' => [
					'TYPE' => Market\Export\Entity\Data::TYPE_STRING,
					'FILTERABLE' => false
				],
				'AVAILABLE' => [
					'TYPE' => Market\Export\Entity\Data::TYPE_BOOLEAN
				],
				'QUANTITY' => [
					'TYPE' => Market\Export\Entity\Data::TYPE_NUMBER
				],
				'MEASURE' => [
					'TYPE' => Market\Export\Entity\Data::TYPE_STRING
				],
				'MEASURE_RATIO' => [
					'TYPE' => Market\Export\Entity\Data::TYPE_NUMBER,
					'FILTERABLE' => false
				],
				'PURCHASING_PRICE_RUR' => [
					'TYPE' => Market\Export\Entity\Data::TYPE_NUMBER,
					'FILTERABLE' => false
				],
				'PURCHASING_PRICE' => [
					'TYPE' => Market\Export\Entity\Data::TYPE_NUMBER
				],
				'PURCHASING_CURRENCY' => [
					'TYPE' => Market\Export\Entity\Data::TYPE_CURRENCY
				],
				'VAT' => [
					'TYPE' => Market\Export\Entity\Data::TYPE_NUMBER,
					'FILTERABLE' => false,
				],
				'TYPE' => [
					'TYPE' => Market\Export\Entity\Data::TYPE_ENUM,
					'SELECTABLE' => false
				]
			]);
		}

		return $result;
	}

	public function getFieldEnum($field, array $context = [])
	{
		$result = null;

		switch ($field['ID'])
		{
			case 'TYPE':
				$result = $this->getCatalogProductTypes();
			break;

			default:
				$result = parent::getFieldEnum($field, $context);
			break;
		}

		return $result;
	}

	protected function getLangPrefix()
	{
		return 'CATALOG_PRODUCT_';
	}

	protected function getCatalogProductTypes()
	{
		$result = [];

		if (Main\Loader::includeModule('catalog'))
		{
			$types = [
				'TYPE_PRODUCT',
				'TYPE_SET',
				'TYPE_SKU'
			];

			foreach ($types as $type)
			{
				$constantName = '\CCatalogProduct::' . $type;

				if (defined($constantName))
				{
					$result[] = [
						'ID' => constant($constantName),
						'VALUE' => Market\Config::getLang($this->getLangPrefix() . 'FIELD_TYPE_ENUM_' . $type)
					];
				}
			}
		}

		return $result;
	}

	protected function getDisplayValue($element, $fieldName, $context = null)
	{
		$result = null;

		if ($fieldName === 'YM_SIZE')
		{
			$keys = [ $this->getFieldFullName('LENGTH'), $this->getFieldFullName('WIDTH'), $this->getFieldFullName('HEIGHT') ];
			$values = [];
			$hasIsset = false;

			foreach ($keys as $key)
			{
				$value = 0;

				if (isset($element[$key]))
				{
					$value = (float)$element[$key];

					if ($value > 0)
					{
						$hasIsset = true;
					}
				}

				$values[] = $value;
			}

			if ($hasIsset)
			{
				$result = implode('/', $values);
			}
		}
		else if ($fieldName === 'PURCHASING_PRICE_RUR')
		{
			$elementKey = $this->getFieldFullName('PURCHASING_PRICE');

			if (isset($element[$elementKey]))
			{
				$price = (float)$element[$elementKey];
				$currency = (string)$element[$this->getFieldFullName('PURCHASING_CURRENCY')];
				$convertCurrency = Market\Data\Currency::getCurrency('RUB');

				if ($convertCurrency !== false && $currency !== $convertCurrency)
				{
					$price = Market\Data\Currency::convert($price, $currency, $convertCurrency);
					$currency = $convertCurrency;
				}

				$result = Market\Data\Currency::round($price, $currency);
			}
		}
		else
		{
			$elementKey = $this->getFieldFullName($fieldName);

			if (isset($element[$elementKey]))
			{
				$originalValue = $element[$elementKey];

				switch ($fieldName)
				{
					case 'PURCHASING_PRICE':
						$price = (float)$originalValue;
						$currency = (string)$element[$this->getFieldFullName('PURCHASING_CURRENCY')];

						if (!empty($context['CONVERT_CURRENCY']))
						{
							$price = Market\Data\Currency::convert($price, $currency, $context['CONVERT_CURRENCY']);
							$currency = $context['CONVERT_CURRENCY'];
						}

						$result = Market\Data\Currency::round($price, $currency);
					break;

					case 'PURCHASING_CURRENCY':
						$result = !empty($context['CONVERT_CURRENCY']) ? $context['CONVERT_CURRENCY'] : $originalValue;
					break;

					case 'WEIGHT':
						if ((float)$originalValue > 0)
						{
							$result = $originalValue;
						}
					break;

					default:
						$result = $originalValue;
					break;
				}
			}
		}

		return $result;
	}

	protected function loadInternalData($elementList, $select)
	{
		$result = [];
		$entity = Catalog\ProductTable::getEntity();

		list($internalSelect, $referenceMap, $runtime) = $this->convertSelectToInternalFields($select, $entity);
		$internalSelect[] = 'ID';

		$query = Catalog\ProductTable::getList([
			'filter' => [ '=ID' => array_keys($elementList) ],
			'select' => $internalSelect,
			'runtime' => $runtime
		]);

		while ($row = $query->fetch())
		{
			foreach ($referenceMap as $selectName => $fieldName)
			{
				$row[$fieldName] = isset($row[$selectName]) ? $row[$selectName] : null;
			}

			$result[$row['ID']] = $row;
		}

		return $result;
	}

	protected function convertSelectToInternalFields($select, Main\ORM\Entity $entity)
	{
		$querySelect = [];
		$referenceMap = [];
		$runtime = [];
		$referenceFields = $this->getReferenceFields();
		$splitFields = $this->getSplitFields();

		foreach ($select as $field)
		{
			if (isset($splitFields[$field]))
			{
				foreach ($splitFields[$field] as $partField)
				{
					if ($entity->hasField($partField))
					{
						$querySelect[] = $partField;
					}
				}
			}
			else if (isset($referenceFields[$field]))
			{
				$reference = $referenceFields[$field];
				$selectName = 'YM_FIELD_' . $field;

				$querySelect[$selectName] = $reference[0] . '.' . $reference[1];
				$referenceMap[$selectName] = $field;

				if (!isset($runtime[$reference[0]]))
				{
					$runtime[$reference[0]] = $this->getRuntimeField($reference[0]);
				}
			}
			else if ($entity->hasField($field))
			{
				$querySelect[] = $field;
			}
		}

		return [ $querySelect, $referenceMap, $runtime ];
	}

	protected function loadExternalData($elementList, $select)
	{
		$idList = array_keys($elementList);
		$externalFields = $this->getExternalFields();
		$externalSelect = $this->extractSelectFields($externalFields, $select);
		$result = [];

		foreach ($externalSelect as $externalField)
		{
			$result[$externalField] = $this->loadExternalField($idList, $externalField);
		}

		return $result;
	}

	protected function loadExternalField($idList, $field)
	{
		$result = [];

		switch ($field)
		{
			case 'MEASURE_RATIO':
				if (Main\Loader::includeModule('catalog'))
				{
					$query = \CCatalogMeasureRatio::GetList(
						[],
						[ '=PRODUCT_ID' => $idList ],
						false,
						false,
						[ 'PRODUCT_ID', 'RATIO' ]
					);

					while ($row = $query->Fetch())
					{
						$result[$row['PRODUCT_ID']] = $row['RATIO'];
					}
				}
			break;
		}

		return $result;
	}

	protected function getSplitFields()
	{
		return [
			'YM_SIZE' => [
				'LENGTH',
				'WIDTH',
				'HEIGHT',
			],
			'PURCHASING_PRICE_RUR' => [
				'PURCHASING_PRICE',
				'PURCHASING_CURRENCY',
			]
		];
	}

	protected function getReferenceFields()
	{
		return [
			'MEASURE_RATIO' => ['YM_MEASURE_RATIO', 'RATIO'],
			'VAT' => ['YM_VAT', 'RATE'],
		];
	}

	protected function getRuntimeField($key)
	{
		switch ($key)
		{
			case 'YM_MEASURE_RATIO':
				$result = new Main\Entity\ReferenceField(
					'YM_MEASURE_RATIO',
					'\Bitrix\Catalog\MeasureRatioTable',
					[ '=this.ID' => 'ref.PRODUCT_ID', '=ref.IS_DEFAULT' => [ '?', 'Y' ] ]
				);
			break;

			case 'YM_VAT';
				$result = new Main\Entity\ReferenceField(
					'YM_VAT',
					'\Bitrix\Catalog\VatTable',
					[ '=this.VAT_ID' => 'ref.ID' ]
				);
			break;

			default:
				throw new Main\SystemException('undefined reference field');
			break;
		}

		return $result;
	}

	protected function getExternalFields()
	{
		return [
			'MEASURE_RATIO'
		];
	}

	protected function extractSelectFields($searchFields, $sourceFields)
	{
		$result = [];

		foreach ($searchFields as $searchField)
		{
			if (in_array($searchField, $sourceFields, true))
			{
				$result[] = $searchField;
			}
		}

		return $result;
	}

	protected function getFieldFullName($field)
	{
		if (!Market\Export\Entity\Catalog\Provider::useCatalogShortFields())
		{
			$result = 'CATALOG_' . $field;
		}
		else
		{
			$result = $field;
		}

		return $result;
	}
}
<?php

namespace Yandex\Market\Export\Entity\Iblock\Element\Property;

use Bitrix\Highloadblock;
use Yandex\Market;
use Bitrix\Main;
use Bitrix\Iblock;
use Bitrix\Catalog;

Main\Localization\Loc::loadMessages(__FILE__);

class Source extends Market\Export\Entity\Reference\Source
{
	protected $highloadDataClassCache = [];
	protected $specialTypes = [
		'directory' => true,
		'SKU' => true,
		'HTML' => true
	];

	public function isFilterable()
	{
		return true;
	}

	public function getQueryFilter($filter, $select)
	{
		return [
			'ELEMENT' => $this->buildQueryFilter($filter)
		];
	}

	public function getOrder()
	{
		return 200;
	}

	public function getElementListValues($elementList, $parentList, $select, $queryContext, $sourceValues)
	{
		$result = [];
		$parentToElementMapByIblock = [];

		foreach ($elementList as $elementId => $element)
		{
			$parent = null;

			if (!isset($element['PARENT_ID'])) // is not offer
			{
				$parent = $element;
			}
			else if (isset($parentList[$element['PARENT_ID']])) // has parent
			{
				$parent = $parentList[$element['PARENT_ID']];
			}

			if (isset($parent))
			{
				if (!isset($parentToElementMapByIblock[$parent['IBLOCK_ID']]))
				{
					$parentToElementMapByIblock[$parent['IBLOCK_ID']] = [];
				}

				if (!isset($parentToElementMapByIblock[$parent['IBLOCK_ID']][$parent['ID']]))
				{
					$parentToElementMapByIblock[$parent['IBLOCK_ID']][$parent['ID']] = [];
				}

				$parentToElementMapByIblock[$parent['IBLOCK_ID']][$parent['ID']][] = $elementId;
			}
		}

		if (!empty($parentToElementMapByIblock))
		{
			foreach ($parentToElementMapByIblock as $iblockId => $parentToElementMap)
			{
				$parentIds = array_keys($parentToElementMap);
				$propertyValuesList = $this->getPropertyValues($iblockId, $parentIds, $select, $queryContext);

				foreach ($propertyValuesList as $parentId => $propertyValues)
				{
					if (isset($parentToElementMap[$parentId]))
					{
						foreach ($parentToElementMap[$parentId] as $elementId)
						{
							$result[$elementId] = $propertyValues;
						}
					}
				}
			}
		}

		return $result;
	}

	public function getFields(array $context = [])
	{
		return $this->getPropertyFields($context['IBLOCK_ID']);
	}

	public function getPropertyFields($iblockId)
	{
		$iblockId = (int)$iblockId;
		$result = [];

		if ($iblockId > 0 && Main\Loader::includeModule('iblock'))
		{
			$langPrefix = $this->getLangPrefix();
			$supportAutocompleteTypes = $this->getSupportAutocompleteTypes();

			$query = Iblock\PropertyTable::getList([
				'filter' => ['=IBLOCK_ID' => $iblockId],
				'select' => ['ID', 'NAME', 'PROPERTY_TYPE', 'USER_TYPE', 'USER_TYPE_SETTINGS', 'WITH_DESCRIPTION', 'LINK_IBLOCK_ID'],
			]);

			while ($propertyRow = $query->fetch())
			{
				$propertyType = $this->getPropertyType($propertyRow);
				$dataType = $propertyRow['PROPERTY_TYPE'];
				$linkIblockId = (int)$propertyRow['LINK_IBLOCK_ID'];

				switch ($propertyRow['USER_TYPE'])
				{
					case 'DateTime':
						$dataType = Market\Export\Entity\Data::TYPE_DATETIME;
					break;

					case 'Date':
						$dataType = Market\Export\Entity\Data::TYPE_DATE;
					break;

					case 'ym_service_category':
						$dataType = Market\Export\Entity\Data::TYPE_SERVICE_CATEGORY;
					break;

					case 'directory':
						$dataType = Market\Export\Entity\Data::TYPE_ENUM;
					break;
				}

				$propertyData = [
					'VALUE' => '[' . $propertyRow['ID'] . '] ' . $propertyRow['NAME'],
					'PROPERTY_TYPE' => $propertyRow['PROPERTY_TYPE'],
					'USER_TYPE' => $propertyRow['USER_TYPE'],
					'USER_TYPE_SETTINGS' => $propertyRow['USER_TYPE_SETTINGS'] ? unserialize($propertyRow['USER_TYPE_SETTINGS']) : null,
				];

				$result[] = [
					'ID' => $propertyRow['ID'],
					'TYPE' => $dataType,
					'FILTERABLE' => true,
					'SELECTABLE' => true,
					'AUTOCOMPLETE' => isset($supportAutocompleteTypes[$propertyType]),
					'LINK_IBLOCK_ID' => $linkIblockId
				] +  $propertyData;

				if ($propertyRow['WITH_DESCRIPTION'] === 'Y')
				{
					$descriptionSuffix = Market\Config::getLang($langPrefix . 'FIELD_DESCRIPTION_SUFFIX');

					$result[] = [
						'ID' => $propertyRow['ID'] . '.DESCRIPTION',
						'VALUE' => $propertyData['VALUE']  . $descriptionSuffix,
						'TYPE' => Market\Export\Entity\Data::TYPE_STRING,
						'FILTERABLE' => false,
						'SELECTABLE' => true,
					] + $propertyData;
				}

				if ($propertyRow['PROPERTY_TYPE'] === 'L')
				{
					$xmlIdSuffix = Market\Config::getLang($langPrefix . 'FIELD_XML_ID_SUFFIX');

					$result[] = [
						'ID' => $propertyRow['ID'] . '.VALUE_XML_ID',
						'VALUE' => $propertyData['VALUE'] . $xmlIdSuffix,
						'TYPE' => Market\Export\Entity\Data::TYPE_STRING,
						'FILTERABLE' => false,
						'SELECTABLE' => true,
					] + $propertyData;
				}
			}
		}

		return $result;
	}

	public function getFieldEnum($field, array $context = [])
	{
		$result = null;

		$propertyType = $this->getPropertyType($field);
		$limit = 50;

		switch ($propertyType)
		{
			case 'L':
			case 'directory':
				$dbQuery = $this->getAutocompleteQuery($field, [], $limit + 1);

				$result = $this->getAutocompleteResultItems($field, $dbQuery);
			break;

		}

		if ($result === null)
		{
			$result = parent::getFieldEnum($field, $context);
		}
		else if (count($result) > $limit)
		{
			$result = null; // use autocomplete
		}

		return $result;
	}

	public function getFieldAutocomplete($field, $query, array $context = [])
	{
		$filter = $this->getAutocompleteFilter($field, $query);
		$dbQuery = $this->getAutocompleteQuery($field, $filter);

		return $this->getAutocompleteResultItems($field, $dbQuery);
	}

	public function getFieldDisplayValue($field, $valueList, array $context = [])
	{
		$filter = $this->getDisplayValueFilter($field, $valueList);
		$dbQuery = $this->getAutocompleteQuery($field, $filter, count($valueList));

		return $this->getAutocompleteResultItems($field, $dbQuery);
	}

	/**
	 * @param array $field
	 * @param \CDBResult|Main\DB\Result|null $dbQuery
	 * @return array
	 */
	protected function getAutocompleteResultItems($field, $dbQuery)
	{
		$propertyType = $this->getPropertyType($field);
		$result = [];

		if ($dbQuery !== null)
		{
			while ($row = $dbQuery->fetch())
			{
				switch ($propertyType)
				{
					case Market\Export\Entity\Data::TYPE_ENUM:
						$item = [
							'ID' => $row['ID'],
							'VALUE' => $row['VALUE'],
						];
					break;

					case 'directory':
						$item = [
							'ID' => $row['UF_XML_ID'],
							'VALUE' => $row['UF_NAME'],
						];
					break;

					default:
						$item = [
							'ID' => $row['ID'],
							'VALUE' => '[' . $row['ID'] . '] ' . $row['NAME'],
						];
					break;
				}

				$result[] = $item;
			}
		}

		return $result;
	}

	protected function getSupportAutocompleteTypes()
	{
		return [
			Market\Export\Entity\Data::TYPE_IBLOCK_ELEMENT => true,
			'SKU' => true,
			Market\Export\Entity\Data::TYPE_IBLOCK_SECTION => true,
			Market\Export\Entity\Data::TYPE_ENUM => true,
			'directory' => true,
		];
	}

	protected function getDisplayValueFilter($field, $values)
	{
		$result = null;
		$propertyType = $this->getPropertyType($field);

		switch ($propertyType)
		{
			case 'directory':
				$result = [ '=UF_XML_ID' => $values ];
			break;

			default:
				$result = [ '=ID' => $values ];
			break;
		}

		return $result;
	}

	protected function getAutocompleteFilter($field, $query)
	{
		$result = null;
		$propertyType = $this->getPropertyType($field);

		switch ($propertyType)
		{
			case 'SKU':
			case Market\Export\Entity\Data::TYPE_IBLOCK_ELEMENT:
				$result = [
					[
						'LOGIC' => 'OR',
						[ '%ID' => $query ],
						[ '%NAME' => $query ],
					]
				];
			break;

			case Market\Export\Entity\Data::TYPE_ENUM:
				$result = [ '%VALUE' => $query ];
			break;

			case 'directory':
				$result = [ '%UF_NAME' => $query ];
			break;

			default:
				$result = [ '%NAME' => $query ];
			break;
		}

		return $result;
	}

	protected function getAutocompleteQuery($field, $filter, $limit = 20)
	{
		$result = null;
		$propertyType = $this->getPropertyType($field);

		switch ($propertyType)
		{
			case 'SKU':
			case Market\Export\Entity\Data::TYPE_IBLOCK_ELEMENT:
				$iblockId = (int)$field['LINK_IBLOCK_ID'];

				if ($iblockId > 0)
				{
					$queryFilter = array_merge([
						'IBLOCK_ID' => $iblockId,
						'ACTIVE' => 'Y',
						'CHECK_PERMISSIONS' => 'Y',
					], $filter);

					$result = \CIBlockElement::GetList(
						[],
						$queryFilter,
						false,
						[ 'nTopCount' => $limit ],
						[ 'ID', 'NAME' ]
					);
				}
			break;

			case Market\Export\Entity\Data::TYPE_IBLOCK_SECTION:
				$iblockId = (int)$field['LINK_IBLOCK_ID'];

				if ($iblockId > 0)
				{
					$queryFilter = array_merge([
						'IBLOCK_ID' => $iblockId,
						'ACTIVE' => 'Y',
						'CHECK_PERMISSIONS' => 'Y',
					], $filter);

					$result = \CIBlockSection::GetList(
						[],
						$queryFilter,
						false,
						[ 'ID', 'NAME' ],
						[ 'nTopCount' => $limit ]
					);
				}
			break;

			case Market\Export\Entity\Data::TYPE_ENUM:
				$queryFilter = array_merge([ 'PROPERTY_ID' => $field['ID'], ], $filter);

				$result = Iblock\PropertyEnumerationTable::getList([
					'filter' => $queryFilter,
					'select' => [ 'ID', 'VALUE' ],
					'limit' => $limit,
				]);
			break;

			case 'directory':
				try
				{
					$highloadEntity = $this->getHighloadEntity($field);

					if (
						$highloadEntity
						&& $highloadEntity->hasField('UF_XML_ID')
						&& $highloadEntity->hasField('UF_NAME')
					)
					{
						$dataClass = $highloadEntity->getDataClass();

						$result = $dataClass::getList([
							'filter' => $filter,
							'select' => [ 'UF_XML_ID', 'UF_NAME' ],
							'limit' => $limit,
						]);
					}
				}
				catch (Main\DB\SqlException $exception)
				{
					 // nothing
				}
			break;
		}

		return $result;
	}

	protected function getLangPrefix()
	{
		return 'IBLOCK_ELEMENT_PROPERTY_';
	}

	protected function buildQueryFilter($filter)
	{
		$result = [];

		foreach ($filter as $filterItem)
		{
            $this->pushQueryFilter($result, $filterItem['COMPARE'], 'PROPERTY_' . $filterItem['FIELD'], $filterItem['VALUE']);
        }

        return $result;
	}

	protected function getPropertyValues($iblockId, $elementIds, $select, $queryContext, $originalPropertyIds = null)
	{
		$isNeedDiscountCache = (!empty($queryContext['DISCOUNT_CACHE']) && Main\Loader::includeModule('catalog'));
		$isNeedSelectAll = $isNeedDiscountCache && empty($queryContext['DISCOUNT_PROPERTIES_OPTIMIZATION']);
		$parsedSelect = $this->parseSelect($select);
		$propertyIds = $parsedSelect['PROPERTY_ID'];
		$propertyValuesList = $this->queryProperties($iblockId, $elementIds, $propertyIds, $isNeedSelectAll);
		$result = [];

		if ($isNeedDiscountCache)
		{
			foreach ($propertyValuesList as $elementId => $propertyList)
			{
				if (!empty($queryContext['DISCOUNT_ONLY_SALE']))
				{
					if (\method_exists('\Bitrix\Catalog\Discount\DiscountManager', 'setProductPropertiesCache'))
					{
						Catalog\Discount\DiscountManager::setProductPropertiesCache($elementId, $propertyList);
					}
				}
				else
				{
					\CCatalogDiscount::SetProductPropertiesCache($elementId, $propertyList);
				}
			}
		}

		$this->extendSelectInnerDefaults($parsedSelect, $propertyValuesList);

		$this->extendInnerValue($result, $propertyValuesList, $parsedSelect['INNER_FIELD'], $parsedSelect['NAME_MAP'], $queryContext);
		$this->extendPlainValue($result, $propertyValuesList, $parsedSelect['PLAIN_FIELD'], $parsedSelect['NAME_MAP'], $queryContext);

		return $result;
	}

	protected function parseSelect($select)
	{
		$result = [
			'PROPERTY_ID' => [],
			'PLAIN_FIELD' => [],
			'INNER_FIELD' => [],
			'NAME_MAP' => []
		];
		$plainFields = [
			'VALUE' => true,
			'VALUE_ENUM_ID' => true,
			'VALUE_XML_ID' => true,
			'DISPLAY_VALUE' => true,
			'DESCRIPTION' => true,
		];
		$propertyIds = [];

		foreach ($select as $field)
		{
			$dotPosition = strpos($field, '.');
			$propertyId = null;
			$propertyField = null;

			if ($dotPosition !== false)
			{
				$propertyId = (int)substr($field, 0, $dotPosition);
				$propertyField = substr($field, $dotPosition + 1);
			}
			else
			{
				$propertyId = (int)$field;
				$propertyField = 'DISPLAY_VALUE';

				$result['NAME_MAP'][$propertyId . '.' . $propertyField] = $propertyId;
			}

			$propertyIds[$propertyId] = true;

			if (isset($plainFields[$propertyField]))
			{
				if (!isset($result['PLAIN_FIELD'][$propertyId]))
				{
					$result['PLAIN_FIELD'][$propertyId] = [ $propertyField ];
				}
				else if (!in_array($propertyField, $result['PLAIN_FIELD'][$propertyId], true))
				{
					$result['PLAIN_FIELD'][$propertyId][] = $propertyField;
				}
			}
			else
			{
				if (!isset($result['INNER_FIELD'][$propertyId]))
				{
					$result['INNER_FIELD'][$propertyId] = [ $propertyField ];
				}
				else if (!in_array($propertyField, $result['INNER_FIELD'][$propertyId], true))
				{
					$result['INNER_FIELD'][$propertyId][] = $propertyField;
				}
			}
		}

		$result['PROPERTY_ID'] = array_keys($propertyIds);

		return $result;
	}

	protected function extendSelectInnerDefaults(&$parsedSelect, $propertyValuesList)
	{
		if (!empty($propertyValuesList) && !empty($parsedSelect['PLAIN_FIELD']))
		{
			$propertyList = reset($propertyValuesList);
			$optimizedTypes = [
				'E' => 'NAME',
				'F' => 'SRC',
				'directory' => 'UF_NAME'
			];

			foreach ($propertyList as $property)
			{
				if (isset($parsedSelect['PLAIN_FIELD'][$property['ID']]))
				{
					$displayValueIndex = array_search('DISPLAY_VALUE', $parsedSelect['PLAIN_FIELD'][$property['ID']], true);

					if ($displayValueIndex !== false)
					{
						$propertyType = $this->getPropertyType($property);

						if (isset($optimizedTypes[$propertyType]))
						{
							// remove from plain

							array_splice($parsedSelect['PLAIN_FIELD'][$property['ID']], $displayValueIndex, 1);

							if (empty($parsedSelect['PLAIN_FIELD'][$property['ID']]))
							{
								unset($parsedSelect['PLAIN_FIELD'][$property['ID']]);
							}

							// add to inner

							if (!isset($parsedSelect['INNER_FIELD'][$property['ID']]))
							{
								$parsedSelect['INNER_FIELD'][$property['ID']] = [];
							}

							$innerField = $optimizedTypes[$propertyType];

							if (!in_array($innerField, $parsedSelect['INNER_FIELD'][$property['ID']], true))
							{
								$parsedSelect['INNER_FIELD'][$property['ID']][] = $innerField;
							}

							// add to map

							$parsedSelect['NAME_MAP'][$property['ID'] . '.' . $innerField] = $property['ID'];
						}
					}
				}
			}
		}
	}

	protected function queryProperties($iblockId, $elementIds, $propertyIds, $isSelectAll = false)
	{
		$result = [];

		if (
			(!empty($propertyIds) || $isSelectAll)
			&& Main\Loader::includeModule('iblock')
		)
		{
			// build result for iblock method

			foreach ($elementIds as $elementId)
			{
				$result[$elementId] = [];
			}

			// query values

			\CIBlockElement::GetPropertyValuesArray($result, $iblockId, ['ID' => $elementIds], $isSelectAll ? [] : ['ID' => $propertyIds]);
		}

		return $result;
	}

	protected function extendInnerValue(&$result, $propertyValuesList, $selectMap, $nameMap, $context)
	{
		$valuesMap = $this->extractPropertyValuesListField($propertyValuesList, $selectMap);
		$propertyList = reset($propertyValuesList);
		$propertyListMap = [];

		foreach ($propertyList as $propertyKey => $property)
		{
			$propertyListMap[$property['ID']] = $propertyKey;
		}

		foreach ($valuesMap as $propertyId => $propertyValuesToElementMap)
		{
			if (isset($propertyListMap[$propertyId]))
			{
				$propertySelect = $selectMap[$propertyId];
				$propertyKey = $propertyListMap[$propertyId];
				$property = $propertyList[$propertyKey];
				$propertyType = $this->getPropertyType($property);
				$propertyValues = array_keys($propertyValuesToElementMap);
				$innerResult = [];

				switch ($propertyType)
				{
					case 'E':
					case 'SKU':
						$innerFieldSelect = [];
						$innerPropertySelect = [];
						$innerIblockId = null;
						$innerContext = null;
						$propertyFieldMarker = 'PROPERTY_';
						$elementList = [];

						foreach ($propertySelect as $field)
						{
							if (strpos($field, $propertyFieldMarker) === 0)
							{
								$innerPropertySelect[] = str_replace($propertyFieldMarker, '', $field);
							}
							else
							{
								$innerFieldSelect[] = $field;
							}
						}

						$queryElementList = \CIBlockElement::GetList(
							[],
							[ 'ID' => $propertyValues ],
							false,
							false,
							array_merge(
								[ 'IBLOCK_ID', 'ID' ],
								$innerFieldSelect
							)
						);

						while ($element = $queryElementList->GetNext())
						{
							if ($innerIblockId === null)
							{
								$innerIblockId = (int)$element['IBLOCK_ID'];
							}

							$elementList[$element['ID']] = $element;
						}

						if ($innerIblockId > 0)
						{
							$innerContext = Market\Export\Entity\Iblock\Provider::getContext($innerIblockId);
						}

						if (!empty($innerFieldSelect))
						{
							$fieldSource = Market\Export\Entity\Manager::getSource(
								Market\Export\Entity\Manager::TYPE_IBLOCK_ELEMENT_FIELD
							);

							$innerResult = $fieldSource->getElementListValues($elementList, [], $innerFieldSelect, $innerContext, []);
						}

						if (!empty($innerPropertySelect))
						{
							$propertySource = Market\Export\Entity\Manager::getSource(
								Market\Export\Entity\Manager::TYPE_IBLOCK_ELEMENT_PROPERTY
							);

							$innerValues = $propertySource->getElementListValues($elementList, [], $innerPropertySelect, $innerContext, []);

							foreach ($innerValues as $elementId => $elementInnerValues)
							{
								if (!isset($innerResult[$elementId]))
								{
									$innerResult[$elementId] = [];
								}

								foreach ($elementInnerValues as $fieldName => $fieldValue)
								{
									$innerResult[$elementId]['PROPERTY_' . $fieldName] = $fieldValue;
								}
							}
						}
					break;

					case 'F':
						$query = \CFile::GetList([], ['@ID' => $propertyValues]);

						while ($row = $query->Fetch())
						{
							$row['SRC'] = \CFile::GetFileSRC($row);

							$innerResult[$row['ID']] = $row;
						}
					break;

					case 'directory':
						try
						{
							$highloadEntity = $this->getHighloadEntity($property);

							if ($highloadEntity && $highloadEntity->hasField('UF_XML_ID'))
							{
								$highloadDataClass = $highloadEntity->getDataClass();

								$queryEnum = $highloadDataClass::getList([
									'filter' => [
										'=UF_XML_ID' => $propertyValues,
									]
								]);

								while ($enum = $queryEnum->fetch())
								{
									$innerResult[$enum['UF_XML_ID']] = $enum;
								}
							}
						}
						catch (Main\DB\SqlException $exception)
						{
							// nothing
						}
					break;
				}

				// fill display value

				foreach ($innerResult as $innerId => $innerFields)
				{
					if (isset($propertyValuesToElementMap[$innerId]))
					{
						foreach ($propertyValuesToElementMap[$innerId] as $elementId)
						{
							if (!isset($result[$elementId]))
							{
								$result[$elementId] = [];
							}

							foreach ($propertySelect as $fieldName)
							{
								$resultKey = $property['ID'] . '.' . $fieldName;

								if (isset($innerFields[$fieldName]))
								{
									$innerValue = $innerFields[$fieldName];

									if (isset($result[$elementId][$resultKey]))
									{
										if (!is_array($result[$elementId][$resultKey]))
										{
											$result[$elementId][$resultKey] = (array)$result[$elementId][$resultKey];
										}

										$result[$elementId][$resultKey][] = $innerValue;
									}
									else
									{
										$result[$elementId][$resultKey] = $innerValue;
									}

									if (isset($nameMap[$resultKey]))
									{
										$result[$elementId][$nameMap[$resultKey]] = $result[$elementId][$resultKey];
									}
								}
							}
						}
					}
				}
			}
		}
	}

	protected function extendPlainValue(&$result, $propertyValuesList, $selectMap, $nameMap, $context)
	{
		if (!empty($selectMap) && !empty($propertyValuesList))
		{
			foreach ($propertyValuesList as $elementId => $propertyList)
			{
				foreach ($propertyList as $property)
				{
					if (isset($selectMap[$property['ID']]))
					{
						$propertySelect = $selectMap[$property['ID']];

						foreach ($propertySelect as $fieldName)
						{
							$fieldValue = null;

							if ($fieldName === 'DISPLAY_VALUE')
							{
								$fieldValue = $this->getDisplayValue($property);
							}
							else if (isset($property[$fieldName]))
							{
								$fieldValue = $property[$fieldName];
							}

							if ($fieldValue !== null)
							{
								$resultKey = $property['ID'] . '.' . $fieldName;

								if (!isset($result[$elementId]))
								{
									$result[$elementId] = [];
								}

								$result[$elementId][$resultKey] = $fieldValue;

								if (isset($nameMap[$resultKey]))
								{
									$result[$elementId][$nameMap[$resultKey]] = $fieldValue;
								}
							}
						}
					}
				}
			}
		}
	}

	protected function extractPropertyValuesListField($propertyValuesList, $usedMap, $field = 'VALUE')
	{
		$result = [];

		foreach ($propertyValuesList as $elementId => $propertyList)
		{
			foreach ($propertyList as $property)
			{
				if (!empty($property[$field]) && isset($usedMap[$property['ID']]))
				{
					if (!isset($result[$property['ID']]))
					{
						$result[$property['ID']] = [];
					}

					if (is_array($property[$field]))
					{
						foreach ($property[$field] as $value)
						{
							$value = trim($value);

							if ($value !== '')
							{
								if (!isset($result[$property['ID']][$value]))
								{
									$result[$property['ID']][$value] = [];
								}

								$result[$property['ID']][$value][] = $elementId;
							}
						}
					}
					else
					{
						$value = trim($property[$field]);

						if ($value !== '')
						{
							if (!isset($result[$property['ID']][$value]))
							{
								$result[$property['ID']][$value] = [];
							}

							$result[$property['ID']][$value][] = $elementId;
						}
					}
				}
			}
		}

		return $result;
	}

	protected function getDisplayValue($property)
	{
		$result = null;

		if (isset($property['VALUE']) && !$this->isEmptyValue($property['VALUE']))
		{
			switch ($this->getPropertyType($property))
			{
				case 'F':
					$fileIds = (array)$property['VALUE'];
					$result = [];

					foreach ($fileIds as $fileId)
					{
						$result[] = \CFile::GetPath($fileId);
					}
				break;

				case 'HTML':
					$propertyValue = isset($property['~VALUE']) ? $property['~VALUE'] : $property['VALUE'];
					$valueList = $propertyValue;

					if (isset($propertyValue['TEXT'], $propertyValue['TYPE']))
					{
						$valueList = [ $propertyValue ];
					}

					if (is_array($valueList))
					{
						$isMultipleProperty = ($property['MULTIPLE'] === 'Y');

						foreach ($valueList as $value)
						{
							if (isset($value['TEXT'], $value['TYPE']))
							{
								$displayValue = FormatText($value['TEXT'], $value['TYPE']);

								if ($isMultipleProperty)
								{
									if ($result === null) { $result = []; }

									$result[] = $displayValue;
								}
								else
								{
									$result = $displayValue;
								}
							}
						}
					}
				break;

				default:
					$result = (isset($property['~VALUE']) ? $property['~VALUE'] : htmlspecialcharsback($property['VALUE']));
				break;
			}
		}

		return $result;
	}

	protected function getPropertyType($property)
	{
		$result = $property['PROPERTY_TYPE'];

		if (isset($this->specialTypes[$property['USER_TYPE']]))
		{
			$result = $property['USER_TYPE'];
		}

		return $result;
	}

	/**
	 * @param $property
	 *
	 * @return \Bitrix\Main\Entity\Base|false
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected function getHighloadEntity($property)
	{
		$result = false;
		$tableName = !empty($property['USER_TYPE_SETTINGS']['TABLE_NAME'])
			? $property['USER_TYPE_SETTINGS']['TABLE_NAME']
			: null;

		if ($tableName === null)
		{
			// nothing
		}
		else if (isset($this->highloadDataClassCache[$tableName]))
		{
			$result = $this->highloadDataClassCache[$tableName];
		}
		else if (Main\Loader::includeModule('highloadblock'))
		{
			$queryHighload = Highloadblock\HighloadBlockTable::getList([
				'filter' => ['=TABLE_NAME' => $tableName],
			]);

			if ($highload = $queryHighload->fetch())
			{
				$result = Highloadblock\HighloadBlockTable::compileEntity($highload);
			}

			$this->highloadDataClassCache[$tableName] = $result;
		}

		return $result;
	}

	protected function isEmptyValue($value)
	{
		if (is_scalar($value))
		{
			$result = ((string)$value === '');
		}
		else
		{
			$result = empty($value);
		}

		return $result;
	}
}

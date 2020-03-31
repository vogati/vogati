<?php

namespace Yandex\Market\Export\Entity\Iblock\Element\Field;

use Yandex\Market;
use Bitrix\Main;
use Bitrix\Iblock;

Main\Localization\Loc::loadMessages(__FILE__);

class Source extends Market\Export\Entity\Reference\Source
{
	protected $siteCache = [];
	protected $sectionFilter = [];
	protected $previousSectionFilter = [];
	protected $invalidValuesCache = [];

	public function getQuerySelect($select)
	{
		return [
			'ELEMENT' => $select
		];
	}

	public function isFilterable()
	{
		return true;
	}

	public function initializeFilterContext($filter, &$queryContext, &$sourceFilter)
	{
		$this->initializeSectionFilter($filter);
	}

	protected function initializeSectionFilter($filter)
	{
		foreach ($filter as $filterItem)
		{
			if (empty($filterItem['VALUE'])) { continue; }

			if ($filterItem['FIELD'] === 'IBLOCK_SECTION_ID' || $filterItem['FIELD'] === 'SECTION_ID')
			{
				$isCompareEqual = (strpos($filterItem['COMPARE'], '!') !== 0);
				$sectionMargins = $this->loadSectionMargin($filterItem['VALUE']);

				if ($this->hasSectionChild($sectionMargins))
				{
					$filterByMargins = [];

					if ($isCompareEqual && count($sectionMargins) > 1)
					{
						$filterByMargins['LOGIC'] = 'OR';
					}

					foreach ($sectionMargins as $sectionMargin)
					{
						if ($isCompareEqual)
						{
							$filterByMargins[] = [
								'>=LEFT_MARGIN' => $sectionMargin[0],
								'<=RIGHT_MARGIN' => $sectionMargin[1],
							];
						}
						else
						{
							$filterByMargins[] = [
								'LOGIC' => 'OR',
								'<RIGHT_MARGIN' => $sectionMargin[0],
								'>LEFT_MARGIN' => $sectionMargin[1],
							];
						}
					}

					$this->sectionFilter[] = $filterByMargins;
				}
				else
				{
					$this->sectionFilter[] = [
						$filterItem['COMPARE'] . 'ID' => $filterItem['VALUE'],
					];
				}
			}
			else if ($filterItem['FIELD'] === 'STRICT_SECTION_ID')
			{
				$this->sectionFilter[] = [
					$filterItem['COMPARE'] . 'ID' => $filterItem['VALUE'],
				];
			}
		}

		if (
			$this->previousSectionFilter !== $this->sectionFilter
			&& isset($this->invalidValuesCache[Market\Export\Entity\Data::TYPE_IBLOCK_SECTION])
		)
		{
			$this->invalidValuesCache[Market\Export\Entity\Data::TYPE_IBLOCK_SECTION] = [];
		}
	}

	public function releaseFilterContext($filter, $queryContext, $sourceFilter)
	{
		$this->previousSectionFilter = $this->sectionFilter;
		$this->sectionFilter = [];
	}

	public function getQueryFilter($filter, $select)
	{
		return [
			'ELEMENT' => $this->buildQueryFilter($filter)
		];
	}

	public function getOrder()
	{
		return 100;
	}

	public function getElementListValues($elementList, $parentList, $select, $queryContext, $sourceValues)
	{
		$result = [];
		$searchElements = [];

		foreach ($elementList as $elementId => $element)
		{
			if (!isset($element['PARENT_ID'])) // is not offer
			{
				$searchElements[$elementId] = $element;
			}
			else if (isset($parentList[$element['PARENT_ID']])) // has parent element
			{
				$searchElements[$elementId] = $parentList[$element['PARENT_ID']];
			}
		}

		$this->preloadFieldValues($searchElements, $select, $queryContext);
		$this->resolveInvalidValues($searchElements, $select, $queryContext);

		foreach ($searchElements as $elementId => $parent)
		{
			$result[$elementId] = $this->getFieldValues($parent, $select, null, $queryContext);
		}

		return $result;
	}

	public function getFields(array $context = [])
	{
		return $this->buildFieldsDescription([
			'ID' => [
				'TYPE' => Market\Export\Entity\Data::TYPE_NUMBER,
                'AUTOCOMPLETE' => true,
                'AUTOCOMPLETE_FIELD' => 'NAME'
			],
			'NAME' => [
				'TYPE' => Market\Export\Entity\Data::TYPE_STRING,
                'AUTOCOMPLETE' => true,
			],
			'IBLOCK_SECTION_ID' => [
				'TYPE' => Market\Export\Entity\Data::TYPE_IBLOCK_SECTION,
				'AUTOCOMPLETE' => true,
				'AUTOCOMPLETE_PRIMARY' => 'ID',
				'AUTOCOMPLETE_FIELD' => 'NAME',
			],
			'STRICT_SECTION_ID' => [
				'TYPE' => Market\Export\Entity\Data::TYPE_IBLOCK_SECTION,
				'SELECTABLE' => false,
				'AUTOCOMPLETE' => true,
				'AUTOCOMPLETE_PRIMARY' => 'ID',
				'AUTOCOMPLETE_FIELD' => 'NAME',
			],
			'CODE'=> [
				'TYPE' => Market\Export\Entity\Data::TYPE_STRING,
                'AUTOCOMPLETE' => true,
                'AUTOCOMPLETE_FIELD' => 'NAME'
			],
			'PREVIEW_PICTURE' => [
				'TYPE' => Market\Export\Entity\Data::TYPE_FILE
			],
			'PREVIEW_TEXT' => [
				'TYPE' => Market\Export\Entity\Data::TYPE_STRING
			],
			'DETAIL_PICTURE' => [
				'TYPE' => Market\Export\Entity\Data::TYPE_FILE
			],
			'DETAIL_TEXT' => [
				'TYPE' => Market\Export\Entity\Data::TYPE_STRING
			],
			'DETAIL_PAGE_URL' => [
				'TYPE' => Market\Export\Entity\Data::TYPE_URL
			],
			'DATE_CREATE' => [
				'TYPE' => Market\Export\Entity\Data::TYPE_DATE
			],
			'TIMESTAMP_X' => [
				'TYPE' => Market\Export\Entity\Data::TYPE_DATE
			],
			'XML_ID' => [
				'TYPE' => Market\Export\Entity\Data::TYPE_STRING
			]
		]);
	}

	public function getFieldEnum($field, array $context = [])
	{
		$result = null;
		$limit = 50;

		if ($field['TYPE'] === Market\Export\Entity\Data::TYPE_IBLOCK_SECTION)
		{
			$dbQuery = $this->getAutocompleteQuery($field, [], $limit + 1, $context);

			$result = $this->getAutocompleteResultItems($field, $dbQuery);
		}

		if ($result === null)
		{
			$result = parent::getFieldEnum($field, $context);
		}
		else if (!empty($field['AUTOCOMPLETE']) && count($result) > $limit)
		{
			$result = null;
		}

		return $result;
	}

	public function getFieldAutocomplete($field, $query, array $context = [])
    {
    	$limit = 20;
	    $filter = $this->getAutocompleteFilter($field, $query);
	    $dbQuery = $this->getAutocompleteQuery($field, $filter, $limit, $context);

	    return $this->getAutocompleteResultItems($field, $dbQuery);
    }

    public function getFieldDisplayValue($field, $valueList, array $context = [])
    {
        $result = null;
	    $limit = count($valueList);
	    $filter = $this->getDisplayValueFilter($field, $valueList);
	    $dbQuery = null;

	    if ($filter !== null)
	    {
		    $dbQuery = $this->getAutocompleteQuery($field, $filter, $limit, $context);
	    }

	    return $this->getAutocompleteResultItems($field, $dbQuery);
    }

    protected function getAutocompleteFilter($field, $query)
    {
	    $primaryField = isset($field['AUTOCOMPLETE_PRIMARY']) ? $field['AUTOCOMPLETE_PRIMARY'] : $field['ID'];
	    $searchField = isset($field['AUTOCOMPLETE_FIELD']) ? $field['AUTOCOMPLETE_FIELD'] : $primaryField;

	    if ($field['TYPE'] === Market\Export\Entity\Data::TYPE_IBLOCK_SECTION)
	    {
		    $result = [
			    '%' . $searchField => $query,
		    ];
	    }
	    else if ($primaryField !== $searchField)
	    {
		    $result = [
			    'LOGIC' => 'OR',
			    [ '%' . $primaryField => $query ],
			    [ '%' . $searchField => $query ],
		    ];
	    }
	    else
	    {
		    $result = [
		    	'%' . $primaryField => $query,
		    ];
	    }

	    return $result;
    }

	protected function getDisplayValueFilter($field, $values)
	{
		$result = null;

		if (isset($field['AUTOCOMPLETE_FIELD']))
		{
			$primaryField = isset($field['AUTOCOMPLETE_PRIMARY']) ? $field['AUTOCOMPLETE_PRIMARY'] : $field['ID'];

			$result = [
				'=' . $primaryField => $values,
			];
		}

		return $result;
	}

    protected function getAutocompleteQuery($field, $filter, $limit, $context)
    {
    	$iblockId = (int)$this->getContextIblockId($context);
	    $result = null;

    	if ($iblockId <= 0) { return null; }

	    $primaryField = isset($field['AUTOCOMPLETE_PRIMARY']) ? $field['AUTOCOMPLETE_PRIMARY'] : $field['ID'];
	    $searchField = isset($field['AUTOCOMPLETE_FIELD']) ? $field['AUTOCOMPLETE_FIELD'] : $primaryField;

        if ($field['TYPE'] === Market\Export\Entity\Data::TYPE_IBLOCK_SECTION)
	    {
	        $queryFilter = [
			    'IBLOCK_ID' => $context['IBLOCK_ID'],
			    'ACTIVE' => 'Y',
			    'CHECK_PERMISSIONS' => 'N',
		    ];
	        $queryFilter = array_merge($queryFilter, $filter);

		    $querySelect = [
			    $primaryField,
			    $searchField,
			    'DEPTH_LEVEL',
		    ];

		    $result = \CIBlockSection::getList(
			    [ 'LEFT_MARGIN' => 'ASC' ],
			    $queryFilter,
			    false,
			    $querySelect,
			    [ 'nTopCount' => $limit ]
		    );
	    }
        else
        {
	        $queryFilter = [
			    'IBLOCK_ID' => $iblockId,
			    'ACTIVE' => 'Y',
			    'ACTIVE_DATE' => 'Y',
		    ];
		    $queryFilter[] = $filter;

		    $querySelect = [
			    $primaryField,
			    $searchField,
		    ];

			$result = \CIBlockElement::GetList(
				[],
				$queryFilter,
				false,
				[ 'nTopCount' => $limit ],
				$querySelect
			);
	    }

    	return $result;
    }

	/**
	 * @param array $field
	 * @param \CDBResult|Main\DB\Result|null $dbQuery
	 * @return array
	 */
	protected function getAutocompleteResultItems($field, $dbQuery)
	{
		$result = [];

		if ($dbQuery === null) { return $result; }

		$valueField = isset($field['AUTOCOMPLETE_PRIMARY']) ? $field['AUTOCOMPLETE_PRIMARY'] : $field['ID'];
		$titleField = isset($field['AUTOCOMPLETE_FIELD']) ? $field['AUTOCOMPLETE_FIELD'] : $field['ID'];

		while ($row = $dbQuery->fetch())
		{
			$itemValue = isset($row[$valueField]) ? trim($row[$valueField]) : '';
			$itemTitle = isset($row[$titleField]) ? trim($row[$titleField]) : '';

			if ($itemValue !== '' && $itemTitle !== '')
			{
				if ($field['TYPE'] === Market\Export\Entity\Data::TYPE_IBLOCK_SECTION)
				{
					$displayValue = str_repeat('.', $row['DEPTH_LEVEL'] - 1) . $itemTitle;
				}
				else if ($itemTitle !== $itemValue)
				{
					$displayValue = '[' . $itemValue . '] ' . $itemTitle;
				}
				else
				{
					$displayValue = $itemValue;
				}

				$result[] = [
					'ID' => $itemValue,
					'VALUE' => $displayValue,
				];
			}
		}

		return $result;
	}

    protected function buildQueryFilter($filter)
	{
		$result = [];

		foreach ($filter as $filterItem)
		{
			$compare = $filterItem['COMPARE'];
			$field = $filterItem['FIELD'];
			$value = $filterItem['VALUE'];

			if ($field === 'IBLOCK_SECTION_ID' || $field === 'SECTION_ID')
			{
				$field = 'SECTION_ID';
				$isCompareEqual = (strpos($compare, '!') !== 0);
				$compare = $isCompareEqual ? '' : '!';

				if (empty($value))
				{
					// nothing
				}
				else if ($isCompareEqual)
				{
					$result['INCLUDE_SUBSECTIONS'] = 'Y';
					$result['SECTION_GLOBAL_ACTIVE'] = 'Y';
				}
				else
				{
					$result['SECTION_GLOBAL_ACTIVE'] = 'Y';
					$sectionMargins = $this->loadSectionMargin($value);

					if ($this->hasSectionChild($sectionMargins))
					{
						$field = 'SUBSECTION';
						$value = $sectionMargins;
					}
				}
			}
			else if ($field === 'STRICT_SECTION_ID')
			{
				$field = 'SECTION_ID';
				$isCompareEqual = (strpos($compare, '!') !== 0);
				$compare = $isCompareEqual ? '' : '!';

				$result['SECTION_GLOBAL_ACTIVE'] = 'Y';
			}

			$this->pushQueryFilter($result, $compare, $field, $value);
        }

        return $result;
	}

	protected function getContextIblockId(array $context = [])
    {
        return $context['IBLOCK_ID'];
    }

    protected function preloadFieldValues(&$elementList, $select, $context = null)
	{
		$fieldsByType = [
			'F' => [
				'PREVIEW_PICTURE',
				'DETAIL_PICTURE'
			]
		];

		foreach ($fieldsByType as $type => $fields)
		{
			$selectedFields = array_intersect($fields, $select);

			if (empty($selectedFields)) { continue; }

			$valueMap = $this->collectValuesMap($elementList, $selectedFields);
			$valueList = array_keys($valueMap);

			foreach (array_chunk($valueList, 500) as $valueChunk)
			{
				switch ($type)
				{
					case 'F':
						$query = \CFile::GetList([], ['@ID' => $valueChunk]);

						while ($row = $query->Fetch())
						{
							if (!isset($valueMap[$row['ID']])) { continue; }

							$preloadValue = \CFile::GetFileSRC($row);

							foreach ($valueMap[$row['ID']] as list($elementId, $field))
							{
								$element = &$elementList[$elementId];

								if (!isset($element['PRELOAD']))
								{
									$element['PRELOAD'] = [];
								}

								$element['PRELOAD'][$field] = $preloadValue;

								unset($element);
							}
						}
					break;
				}
			}
		}
	}

	protected function resolveInvalidValues(&$elementList, $select, $context = null)
	{
		$fieldsByType = [
			Market\Export\Entity\Data::TYPE_IBLOCK_SECTION => [
				'IBLOCK_SECTION_ID',
			],
		];

		foreach ($fieldsByType as $type => $fields)
		{
			$selectedFields = array_intersect($fields, $select);

			if (empty($selectedFields)) { continue; }

			$valueMap = $this->collectValuesMap($elementList, $selectedFields);
			$valueList = array_keys($valueMap);

			foreach (array_chunk($valueList, 500) as $valueChunk)
			{
				$invalidValues = $this->getInvalidValues($type, $valueChunk, $context);

				if (empty($invalidValues)) { continue; }

				$invalidValuesMap = array_intersect_key($valueMap, $invalidValues);
				$searchIds = [];

				foreach ($invalidValuesMap as $invalidItems)
				{
					foreach ($invalidItems as list($elementId))
					{
						$searchId = $elementList[$elementId]['ID'];
						$searchIds[$searchId] = true;
					}
				}

				$validValues = $this->resolveValidValues($type, array_keys($searchIds), $context);

				foreach ($invalidValuesMap as $invalidItems)
				{
					foreach ($invalidItems as list($elementId, $field))
					{
						$searchId = $elementList[$elementId]['ID'];
						$validValue = isset($validValues[$searchId]) ? $validValues[$searchId] : null;

						$elementList[$elementId][$field] = $validValue;
					}
				}
			}
		}
	}

	protected function getInvalidValues($type, $valueList, $context = null)
	{
		if (!isset($this->invalidValuesCache[$type]))
		{
			$this->invalidValuesCache[$type] = [];
		}

		$valueMap = array_flip($valueList);
		$searchValues = array_diff_key($valueMap, $this->invalidValuesCache[$type]);
		$result = array_intersect_key($this->invalidValuesCache[$type], $valueMap);

		if (!empty($searchValues))
		{
			$fetchedValues = $this->fetchInvalidValues($type, array_keys($searchValues), $context);

			if (!empty($fetchedValues))
			{
				$result += $fetchedValues;
				$this->invalidValuesCache[$type] += $fetchedValues;
			}
		}

		return array_filter($result);
	}

	protected function fetchInvalidValues($type, $valueList, $context = null)
	{
		$result = [];

		switch ($type)
		{
			case Market\Export\Entity\Data::TYPE_IBLOCK_SECTION:
				$iblockId = $this->getContextIblockId($context);

				if ($iblockId > 0)
				{
					$result = array_fill_keys($valueList, true);
					$filter = [
						'=IBLOCK_ID' => $iblockId,
						'=ID' => $valueList,
						'=GLOBAL_ACTIVE' => 'Y',
					];
					$filter = array_merge($filter, $this->sectionFilter);

					$query = Iblock\SectionTable::getList([
						'filter' => $filter,
						'select' => [ 'ID' ],
					]);

					while ($row = $query->fetch())
					{
						$result[$row['ID']] = false;
					}
				}
			break;
		}

		return $result;
	}

	protected function resolveValidValues($type, $elementIds, $context = null)
	{
		$result = [];

		switch ($type)
		{
			case Market\Export\Entity\Data::TYPE_IBLOCK_SECTION:
				// load element to sections link

				$usedSections = [];
				$elementToSectionsMap = [];

				$query = \CIBlockElement::GetElementGroups($elementIds, true, [
					'ID',
					'GLOBAL_ACTIVE',
					'IBLOCK_ELEMENT_ID'
				]);

				while ($row = $query->fetch())
				{
					if ($row['GLOBAL_ACTIVE'] !== 'Y') { continue; }

					$elementId = (int)$row['IBLOCK_ELEMENT_ID'];
					$sectionId = (int)$row['ID'];

					if (!isset($elementToSectionsMap[$elementId]))
					{
						$elementToSectionsMap[$elementId] = [];
					}

					$elementToSectionsMap[$elementId][] = $sectionId;
					$usedSections[$sectionId] = true;
				}

				// unset invalid sections

				if (!empty($usedSections) && !empty($this->sectionFilter))
				{
					$invalidSections = $this->getInvalidValues($type, array_keys($usedSections), $context);
					$usedSections = array_diff_key($usedSections, $invalidSections);
				}

				// resolve one element section

				foreach ($elementToSectionsMap as $elementId => $sectionIds)
				{
					$elementResult = null;

					foreach ($sectionIds as $sectionId)
					{
						if (!isset($usedSections[$sectionId])) { continue; }

						if ($elementResult === null || $sectionId > $elementResult)
						{
							$elementResult = $sectionId;
						}
					}

					$result[$elementId] = $elementResult;
				}
			break;
		}

		return $result;
	}

	protected function collectValuesMap($elementList, $select)
	{
		$valueMap = [];

		foreach ($elementList as $elementId => $element)
		{
			foreach ($select as $field)
			{
				if (!empty($element[$field]))
				{
					$value = $element[$field];

					if (!isset($valueMap[$value])) { $valueMap[$value] = []; }

					$valueMap[$value][] = [ $elementId, $field ];
				}
			}
		}

		return $valueMap;
	}

	protected function getFieldValues($element, $select, $parent = null, $context = null)
	{
		$result = [];
		$hasPreload = isset($element['PRELOAD']);

		foreach ($select as $fieldName)
		{
			$fieldValue = null;

			if (isset($element[$fieldName]))
			{
				$fieldValue = $element[$fieldName];

				switch ($fieldName)
				{
					case 'PREVIEW_PICTURE':
					case 'DETAIL_PICTURE':
						if ($hasPreload)
						{
							$fieldValue = isset($element['PRELOAD'][$fieldName]) ? $element['PRELOAD'][$fieldName] : null;
						}
						else
						{
							$fieldValue = \CFile::GetPath($fieldValue);
						}
					break;

					case 'DETAIL_PAGE_URL':
						if (isset($parent['DETAIL_PAGE_URL']) && strpos($fieldValue, '#PRODUCT_URL#') !== false)
						{
							$parent['DETAIL_PAGE_URL'] = $this->replaceUrlSiteVariables($parent['DETAIL_PAGE_URL'], $context);
							$parentUrl = \CIBlock::ReplaceDetailUrl($parent['DETAIL_PAGE_URL'], $parent, false, 'E');

							$fieldValue = str_replace('#PRODUCT_URL#', $parentUrl, $fieldValue);
						}

						$fieldValue = $this->replaceUrlSiteVariables($fieldValue, $context);
						$fieldValue = \CIBlock::ReplaceDetailUrl($fieldValue, $element, false, 'E');
					break;
				}
			}

			$result[$fieldName] = $fieldValue;
		}

		return $result;
	}

	protected function getLangPrefix()
	{
		return 'IBLOCK_ELEMENT_FIELD_';
	}

	protected function loadSectionMargin($sectionIds)
	{
		$result = [];

		$querySections = Iblock\SectionTable::getList([
			'filter' => [
				'=ID' => $sectionIds
			],
			'select' => [
				'ID',
				'LEFT_MARGIN',
				'RIGHT_MARGIN'
			]
		]);

		while ($section = $querySections->fetch())
		{
			$result[] = [
				(int)$section['LEFT_MARGIN'],
				(int)$section['RIGHT_MARGIN']
			];
		}

		return $result;
	}

	protected function hasSectionChild($sectionMargins)
	{
		$result = false;

		foreach ($sectionMargins as $sectionMargin)
		{
			if ($sectionMargin[1] > $sectionMargin[0] + 1)
			{
				$result = true;
				break;
			}
		}

		return $result;
	}

	protected function replaceUrlSiteVariables($url, $context)
	{
		$result = $url;
		$replaces = $this->getSiteVariables($context['SITE_ID']);

		if ($replaces !== false)
		{
			$result = str_replace($replaces['from'], $replaces['to'], $result);
		}

		return $result;
	}

	protected function getSiteVariables($siteId)
	{
		if (!isset($this->siteCache[$siteId]))
		{
			$this->siteCache[$siteId] = $this->loadSiteVariables($siteId);
		}

		return $this->siteCache[$siteId];
	}

	protected function loadSiteVariables($siteId)
	{
		$result = false;

		$query = Main\SiteTable::getList([
			'filter' => [ '=LID' => $siteId ],
			'select' => [ 'SERVER_NAME', 'DIR' ]
		]);

		if ($site = $query->fetch())
		{
			$result = [
				'from' => [ '#SITE_DIR#', '#SERVER_NAME#', '#LANG#' ],
				'to' => [ $site['DIR'], $site['SERVER_NAME'], $site['DIR'] ]
			];
		}

		return $result;
	}
}

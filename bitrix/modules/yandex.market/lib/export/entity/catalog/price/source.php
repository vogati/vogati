<?php

namespace Yandex\Market\Export\Entity\Catalog\Price;

use Yandex\Market;
use Bitrix\Main;
use Bitrix\Catalog;
use Bitrix\Sale;

Main\Localization\Loc::loadMessages(__FILE__);

class Source extends Market\Export\Entity\Reference\Source
{
	protected $publicPriceIds;
	protected $basePriceIds;
	protected $isOptimalPriceHandlersExists;

	public function getOrder()
	{
		return 600; // after catalog_product
	}

	public function isFilterable()
	{
		return true;
	}

	/**
	 * @param $filter
	 * @param $select
	 *
	 * @return array|null
	 */
	public function getQueryFilter($filter, $select)
	{
		$isShortSyntax = Market\Export\Entity\Catalog\Provider::useCatalogShortFields();
		$usedPriceIds = [];
		$result = [
			'CATALOG' => []
		];

		foreach ($filter as $filterItem)
		{
			$fieldParts = $this->getFieldParts($filterItem['FIELD']);
			$priceIds = $this->getTypePriceIds($fieldParts['TYPE']);
			$isMultipleField = (count($priceIds) > 1);
			$multipleQueryPart = null;
            $multipleQueryLogic = null;

			foreach ($priceIds as $priceId)
            {
                $filterItemField = null;

                switch ($fieldParts['FIELD'])
                {
                    case 'CURRENCY':
                        $filterItemField = ($isShortSyntax ? '' : 'CATALOG_') . 'CURRENCY_' . $priceId;
                    break;

                    case 'DISCOUNT_VALUE': // for discount filter
                    case 'VALUE':
                        $filterItemField = ($isShortSyntax ? '' : 'CATALOG_') . 'PRICE_' . $priceId;
                    break;
                }

                if ($filterItemField !== null)
                {
                    if (!isset($usedPriceIds[$priceId]))
                    {
                        $usedPriceIds[$priceId] = true;

                        if ($isShortSyntax)
						{
							$result['CATALOG']['QUANTITY_RANGE_FILTER_' . $priceId] = 1;
                    	}
                        else
						{
							$result['CATALOG']['CATALOG_SHOP_QUANTITY_' . $priceId] = 1;
						}
                    }

                    if ($isMultipleField)
                    {
                        if ($multipleQueryPart === null)
                        {
                            $multipleQueryPart = [];
                            $multipleQueryLogic = (strpos($filterItem['COMPARE'], '>') !== false || strpos($filterItem['COMPARE'], '!') !== false ? 'AND' : 'OR'); // for minimal => more then AND else OR
                        }

                        if ($multipleQueryLogic === 'AND')
                        {
                            $multipleQueryPart[] = [
                                'LOGIC' => 'OR',
                                [ $filterItem['COMPARE'] . $filterItemField => $filterItem['VALUE'] ],
                                [ $filterItemField => false ]
                            ];
                        }
                        else
                        {
                            $multipleQueryPart[] = [
                                $filterItem['COMPARE'] . $filterItemField => $filterItem['VALUE']
                            ];
                        }
                    }
                    else
                    {
                        $this->pushQueryFilter($result['CATALOG'], $filterItem['COMPARE'], $filterItemField, $filterItem['VALUE']);
                    }
                }
			}

			if ($multipleQueryPart !== null)
            {
                $multipleQueryPart['LOGIC'] = $multipleQueryLogic;

                $result['CATALOG'][] = $multipleQueryPart;
            }
        }

		return $result;
	}

	public function initializeQueryContext($select, &$queryContext, &$sourceSelect)
	{
		global $USER;

		// vat select

		if (Market\Export\Entity\Catalog\Provider::useCatalogShortFields())
		{
			$priceSelect = $this->getPriceSelectFields($select);

			if (isset($priceSelect['OPTIMAL']))
			{
				$isNeedSelectVatInfo = (count($priceSelect) > 1);
			}
			else
			{
				$isNeedSelectVatInfo = (count($priceSelect) > 0);
			}

			if ($isNeedSelectVatInfo)
			{
				$catalogProductType = Market\Export\Entity\Manager::TYPE_CATALOG_PRODUCT;

				if (!isset($sourceSelect[$catalogProductType])) { $sourceSelect[$catalogProductType] = []; }

				$sourceSelect[$catalogProductType][] = 'VAT';
				$sourceSelect[$catalogProductType][] = 'VAT_INCLUDED';
			}
		}

		// discount

		$queryContext['DISCOUNT_USE'] = false;
		$queryContext['DISCOUNT_CACHE'] = false;
		$queryContext['DISCOUNT_ONLY_SALE'] = false;

		if ($this->hasQueryDiscountValue($select, $queryContext))
		{
			$queryContext['DISCOUNT_USE'] = true;
			$isDiscountCouponCleared = false;

			// initialize discount

			if (Main\Loader::includeModule('sale'))
			{
				if (method_exists('Bitrix\Sale\DiscountCouponsManager', 'freezeCouponStorage'))
				{
					$isDiscountCouponCleared = true;
					Sale\DiscountCouponsManager::freezeCouponStorage();
				}

				$queryContext['DISCOUNT_ONLY_SALE'] = ((string)Main\Config\Option::get('sale', 'use_sale_discount_only') === 'Y');
			}

			if (Main\Loader::includeModule('catalog'))
			{
				\CCatalogDiscountSave::Disable();

				\CCatalogProduct::setPriceVatIncludeMode(true);
				\CCatalogProduct::setUseDiscount(true);

				if (!$isDiscountCouponCleared)
				{
					\CCatalogDiscountCoupon::ClearCoupon();

					if ($USER !== null && $USER instanceof \CUser && $USER->IsAuthorized())
					{
						\CCatalogDiscountCoupon::ClearCouponsByManage($USER->GetID());
					}
				}
			}

			// discount cache

			if ($this->isNeedDiscountCache($select, $queryContext))
			{
				$queryContext['DISCOUNT_CACHE'] = true;
				$elementProperties = [];
				$offerProperties = [];

				if ($this->isDiscountPropertiesOptimizationEnabled())
				{
					$queryContext['DISCOUNT_PROPERTIES_OPTIMIZATION'] = true;
					$queryContext['DISCOUNT_PROPERTIES_OPTIMIZATION_EMPTY'] = [];

					$usedProperties = $this->getDiscountUsedProperties($queryContext);

					if (isset($usedProperties[$queryContext['IBLOCK_ID']]))
					{
						$elementProperties = array_keys($usedProperties[$queryContext['IBLOCK_ID']]);
					}
					else
					{
						$elementProperties = null; // no need properties in discount
						$queryContext['DISCOUNT_PROPERTIES_OPTIMIZATION_EMPTY'][$queryContext['IBLOCK_ID']] = true;
					}

					if (isset($queryContext['OFFER_IBLOCK_ID']))
					{
						if (isset($usedProperties[$queryContext['OFFER_IBLOCK_ID']]))
						{
							$offerProperties = array_keys($usedProperties[$queryContext['OFFER_IBLOCK_ID']]);
						}

						$offerProperties[] = $queryContext['OFFER_PROPERTY_ID']; // always need link to parent
					}
				}

				// need select properties

				$elementPropertyType = Market\Export\Entity\Manager::TYPE_IBLOCK_ELEMENT_PROPERTY;
				$offerPropertyType = Market\Export\Entity\Manager::TYPE_IBLOCK_OFFER_PROPERTY;

				if ($elementProperties !== null)  // then select element properties
				{
					if (!isset($sourceSelect[$elementPropertyType]))
					{
						$sourceSelect[$elementPropertyType] = [];
					}

					$alreadyRequestedProperties = array_flip($sourceSelect[$elementPropertyType]);

					foreach ($elementProperties as $propertyId)
					{
						$propertyKey = $propertyId . '.VALUE';

						if (!isset($alreadyRequestedProperties[$propertyId]) && !isset($alreadyRequestedProperties[$propertyKey]))
						{
							$sourceSelect[$elementPropertyType][] = $propertyKey;
						}
					}
				}

				if ($offerProperties !== null && isset($queryContext['OFFER_IBLOCK_ID'])) // then select offer properties
				{
					if (!isset($sourceSelect[$offerPropertyType]))
					{
						$sourceSelect[$offerPropertyType] = [];
					}

					$alreadyRequestedProperties = array_flip($sourceSelect[$offerPropertyType]);

					foreach ($offerProperties as $propertyId)
					{
						$propertyKey = $propertyId . '.VALUE';

						if (!isset($alreadyRequestedProperties[$propertyId]) && !isset($alreadyRequestedProperties[$propertyKey]))
						{
							$sourceSelect[$offerPropertyType][] = $propertyKey;
						}
					}
				}
			}
		}
	}

	public function releaseQueryContext($select, $queryContext, $sourceSelect)
	{
		if ($queryContext['DISCOUNT_USE'])
		{
			if (Main\Loader::includeModule('catalog'))
			{
				\CCatalogDiscountSave::Enable();
			}

			if (Main\Loader::includeModule('sale'))
			{
				if (method_exists('Bitrix\Sale\DiscountCouponsManager', 'unFreezeCouponStorage'))
				{
					Sale\DiscountCouponsManager::unFreezeCouponStorage();
				}
			}
		}
	}

	public function getElementListValues($elementList, $parentList, $select, $queryContext, $sourceValues)
	{
		$result = [];
		$priceFieldsList = $this->getPriceSelectFields($select);

		if (
			!empty($elementList)
			&& !empty($priceFieldsList)
			&& Main\Loader::includeModule('catalog')
			&& Main\Loader::includeModule('iblock')
		)
		{
			if ($queryContext['DISCOUNT_CACHE'])
			{
				$this->initializeElementListDiscountCache($elementList, $parentList, $select, $queryContext);
			}

			$preloadPrices = $priceFieldsList;
			$elementIdList = array_keys($elementList);

			if (isset($preloadPrices['OPTIMAL']))
			{
				if ($this->hasGetOptimalPriceHandlers())
				{
					unset($preloadPrices['OPTIMAL']);
				}
				else
				{
					$this->preloadOptimalData($elementIdList);
				}
			}

			$priceIdsByType = $this->getTypePriceIdsList(array_keys($preloadPrices));
			$priceIds = $this->getPriceIdsFromListByType($priceIdsByType);
			$optimalPriceFieldsMap = null;
			$elementListPrices = $this->loadElementListPrices($elementIdList, $priceIds);

			foreach ($elementList as $elementId => $element)
			{
				$result[$elementId] = [];

				foreach ($priceFieldsList as $priceType => $fields)
				{
					if ($priceType === 'OPTIMAL')
					{
						if (!empty($priceIdsByType[$priceType]))
						{
							$elementPrices = $this->combinePriceList($priceIdsByType[$priceType], $elementListPrices[$elementId]);
						}
						else
						{
							$elementPrices = [];
						}

						$optimalPrice = $this->getElementOptimalPrice($element, $fields, $elementPrices, $queryContext);

						if (!empty($optimalPrice['RESULT_PRICE']))
						{
							if (!isset($optimalPriceFieldsMap))
							{
								$optimalPriceFieldsMap = $this->getOptimalPriceFieldsMap();
							}

							foreach ($fields as $field)
							{
								if (isset($optimalPriceFieldsMap[$field]))
								{
									$optimalField = $optimalPriceFieldsMap[$field];

									$result[$elementId][$priceType . '.' . $field] = (
										isset($optimalPrice['RESULT_PRICE'][$optimalField])
											? $optimalPrice['RESULT_PRICE'][$optimalField]
											: null
									);
								}
							}
						}
					}
					else if (!empty($priceIdsByType[$priceType]) && !empty($elementListPrices[$elementId]))
					{
						if (Market\Export\Entity\Catalog\Provider::useCatalogShortFields())
						{
							$element += $this->extendElementBySiblingSources($sourceValues[$elementId]);
						}

						$elementPrice = $this->getElementCatalogPrice($element, $fields, $priceType, $queryContext, $priceIdsByType[$priceType], $elementListPrices[$elementId]);

						if (!empty($elementPrice))
						{
							foreach ($fields as $field)
							{
								$result[$elementId][$priceType . '.' . $field] = (
									isset($elementPrice[$field])
										? $elementPrice[$field]
										: null
								);
							}
						}
					}
				}
			}

			if ($queryContext['DISCOUNT_CACHE'])
			{
				$this->releaseElementListDiscountCache($queryContext);
			}
		}

		return $result;
	}

	public function getFields(array $context = [])
	{
		$result = [];

		if ($context['HAS_CATALOG'] && Main\Loader::includeModule('catalog'))
		{
			$langPrefix = $this->getLangPrefix();

			// price types

			$priceTypes = [
				'MINIMAL' => [
					'VALUE' => Market\Config::getLang($langPrefix . 'TYPE_MINIMAL'),
					'FILTERABLE' => false,
					'SELECTABLE' => true
				],
				'BASE' => [
					'VALUE' => Market\Config::getLang($langPrefix . 'TYPE_BASE'),
					'FILTERABLE' => true,
					'SELECTABLE' => true
				],
				'OPTIMAL' => [
					'VALUE' => Market\Config::getLang($langPrefix . 'TYPE_OPTIMAL'),
					'FILTERABLE' => false,
					'SELECTABLE' => true
				]
			];

			$catalogPrices = \CCatalogGroup::GetListArray();

			foreach ($catalogPrices as $catalogPrice)
			{
				$priceTypes[$catalogPrice['ID']] = [
					'VALUE' => $catalogPrice['NAME_LANG'] ?: $catalogPrice['NAME'],
					'FILTERABLE' => true,
					'SELECTABLE' => true
				];
			}

			// price fields

			$priceFields = [
				'DISCOUNT_VALUE' => [
					'VALUE' => Market\Config::getLang($langPrefix . 'FIELD_DISCOUNT_VALUE'),
					'TYPE' => Market\Export\Entity\Data::TYPE_NUMBER,
					'FILTERABLE' => false,
					'SELECTABLE' => true
				],
				'VALUE' => [
					'VALUE' => Market\Config::getLang($langPrefix . 'FIELD_VALUE'),
					'TYPE' => Market\Export\Entity\Data::TYPE_NUMBER,
					'FILTERABLE' => true,
					'SELECTABLE' => true
				],
				'CURRENCY' => [
					'VALUE' => Market\Config::getLang($langPrefix . 'FIELD_CURRENCY'),
					'TYPE' => Market\Export\Entity\Data::TYPE_CURRENCY,
					'FILTERABLE' => true,
					'SELECTABLE' => true
				]
			];

			// build result

			foreach ($priceTypes as $priceType => $priceTypeData)
			{
				foreach ($priceFields as $priceField => $priceFieldData)
				{
					$result[] = [
						'ID' => $priceType . '.' . $priceField,
						'VALUE' => $priceTypeData['VALUE'] . ': ' . $priceFieldData['VALUE'],
						'TYPE' => $priceFieldData['TYPE'],
						'FILTERABLE' => $priceTypeData['FILTERABLE'] && $priceFieldData['FILTERABLE'],
						'SELECTABLE' => $priceTypeData['SELECTABLE'] && $priceFieldData['SELECTABLE']
					];
				}
			}
		}

		return $result;
	}

	public function hasCurrencyConversion($fieldName, $settings = null)
	{
		return (strpos($fieldName, 'OPTIMAL.') === 0);
	}

	protected function getLangPrefix()
	{
		return 'CATALOG_PRICE_';
	}

	protected function loadElementListPrices($elementIds, $priceIds)
	{
		$result = [];

		if (!empty($elementIds) && !empty($priceIds) && Main\Loader::includeModule('catalog'))
		{
			$query = \CPrice::GetList(
				[],
				[
					'@PRODUCT_ID' => $elementIds,
					'@CATALOG_GROUP_ID' => $priceIds,
					'+<=QUANTITY_FROM' => 1,
					'+>=QUANTITY_TO' => 1
				],
				false,
				false,
				[
					'ID',
					'PRODUCT_ID',
					'CATALOG_GROUP_ID',
					'PRICE',
					'CURRENCY'
				]
			);

			while ($price = $query->Fetch())
			{
				$productId = (int)$price['PRODUCT_ID'];
				$priceGroupId = (int)$price['CATALOG_GROUP_ID'];

				if (!isset($result[$productId])) { $result[$productId] = []; }

				$result[$productId][$priceGroupId] = $price;
			}
		}

		return $result;
	}

	public function getPriceIds($select)
	{
		$priceFieldsList = $this->getPriceSelectFields($select);
		$priceTypes = array_keys($priceFieldsList);
		$priceIdsList = $this->getTypePriceIdsList($priceTypes);

		return $this->getPriceIdsFromListByType($priceIdsList);
	}

	protected function getPriceIdsFromListByType($priceIdsList)
	{
		$result = [];

		foreach ($priceIdsList as $priceType => $priceIds)
		{
			foreach ($priceIds as $priceId)
			{
				if (!isset($result[$priceId]))
				{
					$result[$priceId] = true;
				}
			}
		}

		return array_keys($result);
	}

	protected function getPriceSelectFields($selectFields)
	{
		$result = [];

		foreach ($selectFields as $selectField)
		{
			$parts = $this->getFieldParts($selectField);

			if (!isset($result[$parts['TYPE']]))
			{
				$result[$parts['TYPE']] = [];
			}

			$result[$parts['TYPE']][] = $parts['FIELD'];
		}

		return $result;
	}

	protected function getFieldParts($selectField)
	{
		$priceType = strtok($selectField, '.'); // before first underscore - price type
		$fieldName = strtok(null); // after - price variant

		return [
			'TYPE' => $priceType,
			'FIELD' => $fieldName
		];
	}

	protected function getTypePriceIdsList($priceTypeList)
	{
		$result = [];

		foreach ($priceTypeList as $priceType)
		{
			$result[$priceType] = $this->getTypePriceIds($priceType);
		}

		return $result;
	}

	protected function getTypePriceIds($priceType)
	{
		$result = [];

		switch ($priceType)
		{
			case 'MINIMAL':
				$result = $this->getPublicPriceIds('view');
			break;

			case 'OPTIMAL':
				$result = $this->getPublicPriceIds('buy');
			break;

			case 'BASE':
				$result = $this->getBasePriceIds();
			break;

			default:
				$priceId = (int)$priceType;

				if ($priceId > 0)
				{
					$result[] = $priceId;
				}
			break;
		}

		return $result;
	}

	protected function getPublicPriceIds($rule)
	{
		if ($this->publicPriceIds === null)
		{
			$this->publicPriceIds = $this->loadPublicPriceIds();
		}

		return isset($this->publicPriceIds[$rule]) ? $this->publicPriceIds[$rule] : [];
	}

	protected function loadPublicPriceIds()
	{
		$result = [];

		if (Main\Loader::includeModule('catalog'))
		{
			$userGroups = [2]; // only public
			$result = \CCatalogGroup::GetGroupsPerms($userGroups);
		}

		return $result;
	}

	protected function getBasePriceIds()
	{
		if (!isset($this->basePriceIds))
		{
			$this->basePriceIds = $this->loadBasePriceIds();
		}

		return $this->basePriceIds;
	}

	protected function loadBasePriceIds()
	{
		$result = [];

		if (Main\Loader::includeModule('catalog'))
		{
			$baseGroup = \CCatalogGroup::GetBaseGroup();

			if ($baseGroup)
			{
				$result[] = (int)$baseGroup['ID'];
			}
		}

		return $result;
	}

	protected function getOptimalPriceFieldsMap()
	{
		return [
			'VALUE' => 'BASE_PRICE',
			'DISCOUNT_VALUE' => 'DISCOUNT_PRICE',
			'CURRENCY' => 'CURRENCY'
		];
	}

	protected function hasQueryDiscountValue($select, $context)
	{
		$result = false;
		$priceFieldsList = $this->getPriceSelectFields($select);

		foreach ($priceFieldsList as $priceType => $fields)
		{
			if (in_array('DISCOUNT_VALUE', $fields, true))
			{
				$result = true;
				break;
			}
		}

		return $result;
	}

	protected function isNeedDiscountCache($select, $context)
	{
		$priceIds = $this->getPriceIds($select);
		$result = false;

		if ($context['DISCOUNT_ONLY_SALE'] && !$this->isSupportDiscountManagerCache())
		{
			$result = false;
		}
		else if (!empty($priceIds))
		{
			$result = \CIBlockPriceTools::SetCatalogDiscountCache($priceIds, $context['USER_GROUPS']);
		}

		return $result;
	}

	public function isDiscountPropertiesOptimizationEnabled()
	{
		return Market\Config::getOption('export_catalog_price_discount_properties_optimize', 'Y') === 'Y';
	}

	protected function getDiscountUsedProperties($context)
	{
		$result = [];

		if ($context['DISCOUNT_ONLY_SALE'] && Main\Loader::includeModule('sale'))
		{
			$discountIds = [];

			$queryAvailableUserDiscounts = Sale\Internals\DiscountGroupTable::getList(array(
				'select' => ['DISCOUNT_ID'],
				'filter' => [
					'@GROUP_ID' => $context['USER_GROUPS'],
					'=ACTIVE' => 'Y'
				]
			));

			while ($availableUserDiscount = $queryAvailableUserDiscounts->fetch())
			{
				$discountId = (int)$availableUserDiscount['DISCOUNT_ID'];

				if ($discountId > 0 && !isset($discountIds[$discountId]))
				{
					$discountIds[$discountId] = $discountId;
				}
			}

			if (!empty($discountIds))
			{
				$queryDiscountEntityList = Sale\Internals\DiscountEntitiesTable::getList([
					'filter' => [
						'=MODULE_ID' => 'catalog',
						'=ENTITY' => 'ELEMENT_PROPERTY',
						'=DISCOUNT_ID' => $discountIds
					],
					'select' => [
						'FIELD_TABLE'
					]
				]);

				while ($discountEntity = $queryDiscountEntityList->fetch())
				{
					$discountEntityFieldParts = explode(':', $discountEntity['FIELD_TABLE']);

					if (is_array($discountEntityFieldParts) && count($discountEntityFieldParts) === 2)
					{
						$iblockId = (int)$discountEntityFieldParts[0];
						$propertyId = (int)$discountEntityFieldParts[1];

						if (!isset($result[$iblockId])) { $result[$iblockId] = []; }

						$result[$iblockId][$propertyId] = true;
					}
				}
			}
		}
		else if (Main\Loader::includeModule('catalog'))
		{
			$now = ConvertTimeStamp(time(), 'FULL');
			$readyDiscountList = [];

			$queryDiscountList = \CCatalogDiscount::GetList(
				[],
				[
					'SITE_ID' => $context['SITE_ID'],
					'TYPE' => \CCatalogDiscount::ENTITY_ID,
					'RENEWAL' => 'N',
					'ACTIVE' => 'Y',
					'+<=ACTIVE_FROM' => $now,
					'+>=ACTIVE_TO' => $now,
					'+USER_GROUP_ID' => $context['USER_GROUPS'],
					'+COUPON' => []
				],
				false,
				false,
				[
					'ID',
					'CONDITIONS'
				]
			);

			while ($discount = $queryDiscountList->Fetch())
			{
				if (isset($readyDiscountList[$discount['ID']])) { continue; } // DISTINCT

				$readyDiscountList[$discount['ID']] = true;
				$discountConditions = isset($discount['CONDITIONS']) ? unserialize($discount['CONDITIONS']) : null;

				$this->parseSaleDiscountActionProperties($result, $discountConditions);
			}
		}

		return $result;
	}

	protected function parseSaleDiscountActionProperties(&$result, $actionList)
	{
		if (isset($actionList['CHILDREN']) && is_array($actionList['CHILDREN']))
		{
			foreach ($actionList['CHILDREN'] as $child)
			{
				if (isset($child['CLASS_ID']))
				{
					if (preg_match('/^CondIBProp:(\d+):(\d+)$/', $child['CLASS_ID'], $match))
					{
						$iblockId = (int)$match[1];
						$propertyId = (int)$match[2];

						if (!isset($result[$iblockId])) { $result[$iblockId] = []; }

						$result[$iblockId][$propertyId] = true;
					}
				}

				if (isset($child['CHILDREN']))
				{
					$this->parseSaleDiscountActionProperties($result, $child);
				}
			}
		}
	}

	protected function initializeElementListDiscountCache($elementList, $parentList, $select, $context)
	{
		$chunkSize = 500;

		if ($context['DISCOUNT_ONLY_SALE'])
		{
			if ($this->isSupportDiscountManagerCache())
			{
				$elementIds = array_keys($elementList);
				$priceIds = $this->getPriceIds($select);

				foreach (array_chunk($elementIds, $chunkSize) as $chunkElementIds)
				{
					Catalog\Discount\DiscountManager::preloadPriceData($chunkElementIds, $priceIds);
					Catalog\Discount\DiscountManager::preloadProductDataToExtendOrder($chunkElementIds, $context['USER_GROUPS']);
				}
			}
		}
		else
		{
			$elementIdsByIblock = [];
			$parentIblockIds = [];

			foreach ($elementList as $elementId => $element)
			{
				// add parent

				if (isset($parentList[$element['PARENT_ID']]))
				{
					$parent = $parentList[$element['PARENT_ID']];

					if (!isset($elementIdsByIblock[$parent['IBLOCK_ID']]))
					{
						$elementIdsByIblock[$parent['IBLOCK_ID']] = [];
					}

					$parentIblockIds[$parent['IBLOCK_ID']] = true;
					$elementIdsByIblock[$parent['IBLOCK_ID']][] = (int)$parent['ID'];
				}

				// add self

				if (!isset($elementIdsByIblock[$element['IBLOCK_ID']]))
				{
					$elementIdsByIblock[$element['IBLOCK_ID']] = [];
				}

				$elementIdsByIblock[$element['IBLOCK_ID']][] = (int)$element['ID'];
			}

			if (!empty($parentIblockIds))
			{
				uksort($elementIdsByIblock, function($a, $b) use ($parentIblockIds) {
					$isAParent = isset($parentIblockIds[$a]);
					$isBParent = isset($parentIblockIds[$b]);

					if ($isAParent === $isBParent) { return 0; }

					return ($isAParent ? -1 : 1);
				});
			}

			foreach ($elementIdsByIblock as $iblockId => $elementIds)
			{
				$hasEmptyPropertiesCache = isset($context['DISCOUNT_PROPERTIES_OPTIMIZATION_EMPTY'][$iblockId]);

				foreach (array_chunk($elementIds, $chunkSize) as $chunkElementIds)
				{
					if ($hasEmptyPropertiesCache)
					{
						foreach ($chunkElementIds as $elementId)
						{
							\CCatalogDiscount::setProductPropertiesCache($elementId, []);
						}
					}

					\CCatalogDiscount::SetProductSectionsCache($chunkElementIds);
					\CCatalogDiscount::SetDiscountProductCache($chunkElementIds, ['IBLOCK_ID' => $iblockId, 'GET_BY_ID' => 'Y']);
				}
			}
		}
	}

	protected function releaseElementListDiscountCache($context)
	{
		if ($context['DISCOUNT_ONLY_SALE'])
		{
			if ($this->isSupportDiscountManagerCache())
			{
				Catalog\Discount\DiscountManager::clearProductsCache();
				Catalog\Discount\DiscountManager::clearProductPricesCache();
				Catalog\Discount\DiscountManager::clearProductPropertiesCache();
			}
		}
		else
		{
			\CCatalogDiscount::ClearDiscountCache(array(
				'PRODUCT' => true,
				'SECTIONS' => true,
				'SECTION_CHAINS' => true,
				'PROPERTIES' => true
			));
			\CCatalogProduct::ClearCache();
		}
	}

	protected function hasGetOptimalPriceHandlers()
	{
		if ($this->isOptimalPriceHandlersExists !== null)
		{
			$result = $this->isOptimalPriceHandlersExists;
		}
		else
		{
			$option = Market\Config::getOption('export_catalog_price_ignore_optimal_handlers', 'N');

			if ($option === 'Y')
			{
				$result = false;
			}
			else
			{
				$handlers = GetModuleEvents('catalog', 'OnGetOptimalPrice', true);
				$result = !empty($handlers);
			}

			$this->isOptimalPriceHandlersExists = $result;
		}

		return $result;
	}

	protected function preloadOptimalData($elementIdList)
	{
		if (method_exists('CIBlockElement', 'GetIBlockByIDList'))
		{
			\CIBlockElement::GetIBlockByIDList($elementIdList);
		}

		if (method_exists('CCatalogProduct', 'GetVATDataByIDList'))
		{
			\CCatalogProduct::GetVATDataByIDList($elementIdList);
		}
	}

	protected function combinePriceList($priceIds, $elementPrices)
	{
		$result = [];

		foreach ($priceIds as $priceId)
		{
			if (isset($elementPrices[$priceId]))
			{
				$result[$priceId] = $elementPrices[$priceId];
			}
		}

		return $result;
	}

	protected function getElementOptimalPrice($element, $fields, $elementPrices, $context)
	{
		$previousUseDiscount = (bool)\CCatalogProduct::getUseDiscount();
		$useDiscount = in_array('DISCOUNT_VALUE', $fields);

		if ($previousUseDiscount !== $useDiscount)
		{
			\CCatalogProduct::setUseDiscount($useDiscount);
		}

		$result = \CCatalogProduct::GetOptimalPrice($element['ID'], 1, $context['USER_GROUPS'], 'N', $elementPrices, $context['SITE_ID']);

		if (
			!empty($result['RESULT_PRICE']['CURRENCY'])
			&& !empty($context['CONVERT_CURRENCY'])
			&& $result['RESULT_PRICE']['CURRENCY'] !== $context['CONVERT_CURRENCY']
			&& Main\Loader::includeModule('currency')
		)
		{
			$oldCurrency = $result['RESULT_PRICE']['CURRENCY'];
			$newCurrency = $context['CONVERT_CURRENCY'];
			$catalogGroupId = (int)$result['RESULT_PRICE']['PRICE_TYPE_ID'];
			$priceKeys = [ 'BASE_PRICE', 'DISCOUNT_PRICE' ];

			foreach ($priceKeys as $priceKey)
			{
				if (isset($result['RESULT_PRICE'][$priceKey]) && $result['RESULT_PRICE'][$priceKey] !== '')
				{
					$newPrice = Market\Data\Currency::convert($result['RESULT_PRICE'][$priceKey], $oldCurrency, $newCurrency);
					$newPrice = $this->roundCatalogPrice($catalogGroupId, $newPrice, $newCurrency);

					$result['RESULT_PRICE'][$priceKey] = $newPrice;
				}
			}

			$result['RESULT_PRICE']['CURRENCY'] = $newCurrency;
		}

		if ($previousUseDiscount !== $useDiscount)
		{
			\CCatalogProduct::setUseDiscount($previousUseDiscount);
		}

		return $result;
	}

	protected function isSupportDiscountManagerCache()
	{
		return (
			\method_exists('\Bitrix\Catalog\Discount\DiscountManager', 'preloadPriceData')
			&& \method_exists('\Bitrix\Catalog\Discount\DiscountManager', 'clearProductsCache')
		);
	}

	protected function getElementCatalogPrice($element, $fields, $priceType, $context, $catalogPriceIds, $elementPrices)
	{
		$result = null;

		if (!empty($catalogPriceIds))
		{
			$isNeedDiscount = in_array('DISCOUNT_VALUE', $fields, true);
			$percentVat = isset($element['CATALOG_VAT']) ? $element['CATALOG_VAT'] * 0.01 : 0;
			$percentPriceWithVat = 1 + $percentVat;
			$minPriceCurrency = null;
			$minPriceDiscountValue = null;
			$contextCurrency = !empty($context['CONVERT_CURRENCY']) ? $context['CONVERT_CURRENCY'] : null;

			if ($isNeedDiscount && !$this->isEnabledGlobalDiscountCalculation())
			{
				$isNeedDiscount = false;
			}

			foreach ($catalogPriceIds as $catalogPriceId)
			{
				if (isset($elementPrices[$catalogPriceId]['PRICE']) && trim($elementPrices[$catalogPriceId]['PRICE']) !== '')
				{
					$discountPrice = null;
					$price = (float)$elementPrices[$catalogPriceId]['PRICE'];
					$currency = $elementPrices[$catalogPriceId]['CURRENCY'];
					$discounts = [];

					// get final price with VAT included.
					if (!isset($element['CATALOG_VAT_INCLUDED']) || $element['CATALOG_VAT_INCLUDED'] !== 'Y')
					{
						$price *= $percentPriceWithVat;
					}

					if ($isNeedDiscount)
					{
						$discounts = \CCatalogDiscount::GetDiscount(
							$element['ID'],
							$element['IBLOCK_ID'],
							[ $catalogPriceId ],
							$context['USER_GROUPS'],
							'N',
							$context['SITE_ID'],
							[]
						);
					}

					$discountPrice = \CCatalogProduct::CountPriceWithDiscount($price, $currency, $discounts);

					if ($discountPrice === false) { continue; }

					if ($contextCurrency !== null && $currency !== $contextCurrency && Main\Loader::includeModule('currency'))
					{
						$discountPrice = Market\Data\Currency::convert($discountPrice, $currency, $contextCurrency);
						$price = Market\Data\Currency::convert($price, $currency, $contextCurrency);
						$currency = $contextCurrency;
					}

					$priceCompareValue = (float)$discountPrice;
					$discountPrice = $this->roundCatalogPrice($catalogPriceId, $discountPrice, $currency);
					$price = $this->roundCatalogPrice($catalogPriceId, $price, $currency);

					$elementPrice = [
						'DISCOUNT_VALUE' => $discountPrice,
						'VALUE' => $price,
						'CURRENCY' => $currency
					];

					if ($priceType !== 'MINIMAL')
					{
						$result = $elementPrice;
						break;
					}
					else if ($minPriceDiscountValue === null)
					{
						$result = $elementPrice;
						$minPriceCurrency = $currency;
						$minPriceDiscountValue = $priceCompareValue;
					}
					else
					{
						if ($minPriceCurrency !== $currency && Main\Loader::includeModule('currency'))
						{
							$priceCompareValue = Market\Data\Currency::convert($priceCompareValue, $currency, $minPriceCurrency);
						}

						if ($priceCompareValue < $minPriceDiscountValue)
						{
							$result = $elementPrice;
							$minPriceDiscountValue = $priceCompareValue;
						}
					}
				}
			}
		}

		return $result;
	}

	protected function extendElementBySiblingSources($sourceValues)
	{
		$catalogProductType = Market\Export\Entity\Manager::TYPE_CATALOG_PRODUCT;
		$result = [];

		if (isset($sourceValues[$catalogProductType]))
		{
			$result['CATALOG_VAT'] = $sourceValues[$catalogProductType]['VAT'];
			$result['CATALOG_VAT_INCLUDED'] = $sourceValues[$catalogProductType]['VAT_INCLUDED'];
		}

		return $result;
	}

	protected function roundCatalogPrice($catalogPriceTypeId, $price, $currency)
	{
		static $isSupportRound = null;

		if ($isSupportRound === null)
		{
			$isSupportRound = method_exists('\Bitrix\Catalog\Product\Price', 'roundPrice');
		}

		$result = $price;

		if ($isSupportRound)
		{
			$result = Catalog\Product\Price::roundPrice($catalogPriceTypeId, $price, $currency);
		}

		$result = Market\Data\Currency::round($result, $currency);

		return $result;
	}

	protected function isEnabledGlobalDiscountCalculation()
	{
		static $isSupportByCore = null;

		if ($isSupportByCore === null)
		{
            $isSupportByCore = method_exists('\CIBlockPriceTools', 'isEnabledCalculationDiscounts');
		}

		if ($isSupportByCore)
		{
			$result = \CIBlockPriceTools::isEnabledCalculationDiscounts();
		}
		else
		{
			$result = true;
		}

		return $result;
	}
}

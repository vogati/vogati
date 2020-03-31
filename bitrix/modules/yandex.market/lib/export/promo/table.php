<?php

namespace Yandex\Market\Export\Promo;

use Bitrix\Main;
use Bitrix\Currency;
use Yandex\Market;

Main\Localization\Loc::loadMessages(__FILE__);

class Table extends Market\Reference\Storage\Table
{
	const PROMO_TYPE_PROMO_CODE = 'promo code';
	const PROMO_TYPE_FLASH_DISCOUNT = 'flash discount';
	const PROMO_TYPE_GIFT_N_PLUS_M = 'n plus m';
	const PROMO_TYPE_GIFT_WITH_PURCHASE = 'gift with purchase';

	const DISCOUNT_UNIT_PERCENT = 'percent';
	const DISCOUNT_UNIT_CURRENCY = 'currency';

	public static function getTableName()
	{
		return 'yamarket_export_promo';
	}

	public static function getUfId()
	{
		return 'YAMARKET_EXPORT_PROMO';
	}

	public static function getMap()
	{
		return [
			new Main\Entity\IntegerField('ID', [
				'autocomplete' => true,
				'primary' => true
			]),
			new Main\Entity\BooleanField('ACTIVE', [
				'default_value' => static::BOOLEAN_Y,
				'values' => [
					static::BOOLEAN_N,
					static::BOOLEAN_Y
				]
			]),
			new Main\Entity\StringField('NAME', [
				'required' => true,
				'validation' => [__CLASS__, 'validateName']
			]),
			new Main\Entity\StringField('URL', [
				'validation' => [__CLASS__, 'validateUrl']
			]),
			new Main\Entity\TextField('DESCRIPTION', [
				'validation' => [__CLASS__, 'validateDescription']
			]),
			new Main\Entity\StringField('PROMO_TYPE', [
				'required' => true,
                'validation' => [__CLASS__, 'validatePromoType']
			]),

			new Main\Entity\BooleanField('SETUP_EXPORT_ALL', [
				'default_value' => static::BOOLEAN_N,
				'values' => [
					static::BOOLEAN_N,
					static::BOOLEAN_Y
				]
			]),
			new Main\Entity\ReferenceField('SETUP_LINK', Internals\SetupLinkTable::getClassName(), [
				'=this.ID' => 'ref.PROMO_ID'
			]),
			new Main\Entity\ReferenceField('SETUP', Market\Export\Setup\Table::getClassName(), [
				'=this.SETUP_LINK.SETUP_ID' => 'ref.ID',
			]),

			new Main\Entity\IntegerField('EXTERNAL_ID'),

			new Main\Entity\DatetimeField('START_DATE', [
                'validation' => [__CLASS__, 'validateStartDate']
            ]),
			new Main\Entity\DatetimeField('FINISH_DATE'),

			new Main\Entity\EnumField('DISCOUNT_UNIT', [
				'values' => [
					static::DISCOUNT_UNIT_PERCENT,
					static::DISCOUNT_UNIT_CURRENCY
				]
			]),
			new Main\Entity\StringField('DISCOUNT_CURRENCY', [
                'validation' => [__CLASS__, 'validateDiscountCurrency']
            ]),
			new Main\Entity\FloatField('DISCOUNT_VALUE', [
				'scale' => 2,
				'validation' => [__CLASS__, 'validatePositiveFloat']
			]),

			new Main\Entity\StringField('PROMO_CODE', [
				'validation' => [__CLASS__, 'validatePromoCode']
			]),

			new Main\Entity\IntegerField('GIFT_REQUIRED_QUANTITY', [
			    'default_value' => 1,
				'validation' => [__CLASS__, 'validatePositiveInteger']
            ]),
			new Main\Entity\IntegerField('GIFT_FREE_QUANTITY', [
                'default_value' => 1,
				'validation' => [__CLASS__, 'validatePositiveInteger']
            ]),

			new Main\Entity\ReferenceField('PROMO_PRODUCT', Market\Export\PromoProduct\Table::getClassName(), [
				'=this.ID' => 'ref.PROMO_ID'
			]),

			new Main\Entity\ReferenceField('PROMO_GIFT', Market\Export\PromoGift\Table::getClassName(), [
				'=this.ID' => 'ref.PROMO_ID'
			])
		];
	}

	public static function getReference($primary = null)
	{
		return [
			'SETUP_LINK' => [
				'TABLE' => Internals\SetupLinkTable::getClassName(),
				'LINK_FIELD' => 'PROMO_ID',
				'LINK' => [
					'PROMO_ID' => $primary
				]
			],
			'PROMO_PRODUCT' => [
				'TABLE' => Market\Export\PromoProduct\Table::getClassName(),
				'LINK_FIELD' => 'PROMO_ID',
				'LINK' => [
					'PROMO_ID' => $primary
				]
			],
			'PROMO_GIFT' => [
				'TABLE' => Market\Export\PromoGift\Table::getClassName(),
				'LINK_FIELD' => 'PROMO_ID',
				'LINK' => [
					'PROMO_ID' => $primary
				]
			]
		];
	}

	public static function getMapDescription()
	{
		global $USER_FIELD_MANAGER;

		$result = parent::getMapDescription();

        if (isset($result['PROMO_TYPE']))
        {
            $result['PROMO_TYPE']['USER_TYPE'] = $USER_FIELD_MANAGER->GetUserType('enumeration');
            $result['PROMO_TYPE']['USER_TYPE']['CLASS_NAME'] = 'Yandex\Market\Ui\UserField\EnumerationType';
            $result['PROMO_TYPE']['VALUES'] = Discount\Manager::getTypeEnum();
        }

		if (isset($result['DESCRIPTION']))
		{
			$result['DESCRIPTION']['USER_TYPE']['CLASS_NAME'] = '\Yandex\Market\Ui\UserField\HtmlType';
		}

		if (isset($result['SETUP']))
		{
			$result['SETUP']['MULTIPLE'] = 'Y';
			$result['SETUP']['USER_TYPE']['CLASS_NAME'] = '\Yandex\Market\Ui\UserField\SetupLinkType';
		}

		if (isset($result['START_DATE']))
		{
			$result['START_DATE']['USER_TYPE'] = $USER_FIELD_MANAGER->GetUserType('datetime');
		}

		if (isset($result['FINISH_DATE']))
		{
			$result['FINISH_DATE']['USER_TYPE'] = $USER_FIELD_MANAGER->GetUserType('datetime');
		}

        if (isset($result['DISCOUNT_CURRENCY']) && Main\Loader::includeModule('currency'))
        {
            $currencyList = [];

            $queryCurrencyList = Currency\CurrencyTable::getList([
                'select' => [ 'CURRENCY', 'SORT', 'BASE' ]
            ]);

            while ($currency = $queryCurrencyList->fetch())
            {
                $currencyList[] = $currency;
            }

            if (!empty($currencyList))
            {
                uasort($currencyList, function($a, $b) {
                    $isABase = ($a['BASE'] === 'Y');
                    $isBBase = ($b['BASE'] === 'Y');
                    $result = 0;

                    if ($isABase === $isBBase)
                    {
                        $aSort = (int)$a['SORT'];
                        $bSort = (int)$b['SORT'];

                        if ($aSort !== $bSort)
                        {
                            $result = ($aSort < $bSort ? -1 : 1);
                        }
                    }
                    else
                    {
                        $result = ($isABase ? -1 : 1);
                    }

                    return $result;
                });

                $result['DISCOUNT_CURRENCY']['USER_TYPE'] = $USER_FIELD_MANAGER->GetUserType('enumeration');
                $result['DISCOUNT_CURRENCY']['USER_TYPE']['CLASS_NAME'] = 'Yandex\Market\Ui\UserField\EnumerationType';
                $result['DISCOUNT_CURRENCY']['MANDATORY'] = 'Y';
                $result['DISCOUNT_CURRENCY']['VALUES'] = [];

                foreach ($currencyList as $currency)
                {
                    $result['DISCOUNT_CURRENCY']['VALUES'][] = [
                        'ID' => $currency['CURRENCY'],
                        'VALUE' => $currency['CURRENCY']
                    ];
                }
            }
        }

		return $result;
	}

	public static function loadExternalReference($primaryList, $select = null, $isCopy = false)
	{
		$result = parent::loadExternalReference($primaryList, $select, $isCopy);

		if (!empty($primaryList))
		{
			$referenceMap = [
				'SETUP' => 'loadExternalReferenceSetup',
			];

			foreach ($referenceMap as $field => $method)
			{
				if (empty($select) || in_array($field, $select))
				{
					$referenceDataList = static::$method($primaryList);

					foreach ($referenceDataList as $primary => $referenceValue)
					{
						if (!isset($result[$primary]))
						{
							$result[$primary] = [];
						}

						$result[$primary][$field] = $referenceValue;
					}
				}
			}
		}

		return $result;
	}

	protected static function loadExternalReferenceSetup($primaryList)
	{
		$result = [];

		// load row data

		$query = Internals\SetupLinkTable::getList([
			'filter' => [
				'=PROMO_ID' => $primaryList,
			],
			'select' => [
				'ID',
				'PROMO_ID',
				'SETUP_ID',
			],
		]);

		while ($row = $query->fetch())
		{
			if (!isset($result[$row['PROMO_ID']]))
			{
				$result[$row['PROMO_ID']] = [];
			}

			$result[$row['PROMO_ID']][$row['ID']] = $row['SETUP_ID'];
		}

		return $result;
	}

	public static function saveExtractReference(array &$data)
	{
		$result = parent::saveExtractReference($data);

		if (array_key_exists('SETUP', $data))
		{
			unset($data['SETUP']);
		}

		return $result;
	}

    protected static function onBeforeRemove($primary)
    {
        /** @var Model $model */
        $model = Model::loadById($primary);

        $model->onBeforeRemove();
    }

    protected static function onAfterSave($primary)
    {
        /** @var Model $model */
        $model = Model::loadById($primary);

        $model->onAfterSave();
    }

	public static function validateName()
	{
		return [
			new Main\Entity\Validator\Length(null, 255)
		];
	}

	public static function validatePromoCode()
	{
		return [
			new Main\Entity\Validator\Length(null, 20)
		];
	}

	public static function validateUrl()
	{
		return [
			function ($value, $primary, $row, $field)
            {
		        $result = false;

		        if (filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED))
                {
                    $result = true;
                }
		        else
                {
                    $valueWithDomain = 'http://local.site' . (strpos($value, '/') === 0 ? '' : '/') . $value;

                    if (filter_var($valueWithDomain, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED))
                    {
                        $result = true;
                    }
                }

		        if (!$result)
                {
                    $result = Market\Config::getLang('EXPORT_PROMO_VALIDATE_URL_REQUIRE_PATH');
                }

		        return $result;
		    }
		];
	}

	public static function validateDescription()
	{
		return [
			new Main\Entity\Validator\Length(null, 500)
		];
	}

    public static function validatePromoType()
    {
        return [
            new Main\Entity\Validator\Length(null, 25)
        ];
    }

    public static function validateDiscountCurrency()
    {
        return [
            new Main\Entity\Validator\Length(null, 10)
        ];
    }

    public static function validatePositiveInteger()
    {
        return [
            function($value, $primary, $row, $field)
			{
				/** @var $field Main\Entity\ScalarField */
				$result = true;
				$valueString = (string)$value;

				if ($valueString !== '')
				{
					$valueInteger = (int)$value;

					if ((string)$valueInteger !== $valueString)
					{
						$result = Market\Config::getLang('EXPORT_PROMO_VALIDATE_POSITIVE_INTEGER_NOT_NUMBER', [
							'#NAME#' => $field->getTitle()
						]);
					}
					else if ($valueInteger <= 0)
					{
						$result = Market\Config::getLang('EXPORT_PROMO_VALIDATE_POSITIVE_INTEGER_NOT_POSITIVE', [
							'#NAME#' => $field->getTitle()
						]);
					}
				}

				return $result;
			}
        ];
    }

    public static function validatePositiveFloat()
    {
        return [
            function($value, $primary, $row, $field)
			{
				/** @var $field Main\Entity\ScalarField */
				$result = true;
				$valueString = (string)$value;

				if ($valueString !== '')
				{
					$valueFloat = (float)$value;

					if ((string)$valueFloat !== $valueString)
					{
						$result = Market\Config::getLang('EXPORT_PROMO_VALIDATE_POSITIVE_FLOAT_NOT_NUMBER', [
							'#NAME#' => $field->getTitle()
						]);
					}
					else if ($valueFloat <= 0)
					{
						$result = Market\Config::getLang('EXPORT_PROMO_VALIDATE_POSITIVE_FLOAT_NOT_POSITIVE', [
							'#NAME#' => $field->getTitle()
						]);
					}
				}

				return $result;
			}
        ];
    }

    public static function validateStartDate()
    {
        return [
            function ($value, $primary, $row, $field)
            {
                $result = true;

                if (
                    !empty($value) && $value instanceof Main\Type\Date
                    && !empty($row['FINISH_DATE']) && $row['FINISH_DATE'] instanceof Main\Type\Date
                    && $row['FINISH_DATE']->getTimestamp() <= $value->getTimestamp()
                )
                {
                    $result = Market\Config::getLang('EXPORT_PROMO_VALIDATE_START_DATE_MUST_LESS_FINISH_DATE');
                }

                return $result;
            }
        ];
    }
}
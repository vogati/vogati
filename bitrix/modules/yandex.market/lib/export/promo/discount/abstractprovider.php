<?php

namespace Yandex\Market\Export\Promo\Discount;

use Yandex\Market;

abstract class AbstractProvider
{
    /** @var int */
    protected $id;
    /** @var array */
    protected $fields;
    /** @var string|null */
    protected $promoType;

    public static function getClassName()
    {
        return '\\' . get_called_class();
    }

    public static function isEnvironmentSupport()
    {
        return true;
    }

    public static function getTitle()
    {
        return '';
    }

    public static function getDescription()
    {
        return '';
    }

    public static function getExternalEnum()
    {
        return null;
    }

    public function __construct($id)
    {
        $this->id = (int)$id;
    }

    public function isActive()
    {
        return ($this->getField('ACTIVE') === 'Y');
    }

    public function getPromoType()
    {
        if ($this->promoType === null)
        {
            $this->promoType = $this->detectPromoType();
        }

        return $this->promoType;
    }

    abstract protected function detectPromoType();

    abstract public function getPromoFields();

    abstract public function getProductFilterList($context);

	/**
	 * Список инфоблоков с подарками.
	 * При вовзрате null будут использованы инфоблоки, которые указаны в профиле выгрузки.
	 *
	 * @return int[]|null
	 */
	public function getGiftIblockList()
	{
		return null;
	}

    abstract public function getGiftFilterList($context);

    abstract public function applyDiscountRules($productId, $price, $currency = null, $filterData = null);

    /**
     * Список источников для отслеживания
     *
     * @return array
     */
    public function getTrackSourceList()
    {
        return [];
    }

    /**
     * Выгружать товары, которые не попали в выгрузку
     *
     * @param $context
     *
     * @return bool
     */
    public function isExportExternalGift($context)
    {
        $option = Market\Config::getOption('export_promo_discount_external_gift');

        return ($option !== 'N');
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getField($key)
    {
        $fields = $this->getFields();

        return (isset($fields[$key]) ? $fields[$key] : null);
    }

    /**
     * @return array
     */
    public function getFields()
    {
        if ($this->fields === null)
        {
            $this->fields = $this->loadFields();
        }

        return $this->fields;
    }

    abstract protected function loadFields();
}
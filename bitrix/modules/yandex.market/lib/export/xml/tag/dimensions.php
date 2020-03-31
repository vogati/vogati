<?php

namespace Yandex\Market\Export\Xml\Tag;

use Bitrix\Main;
use Yandex\Market;

Main\Localization\Loc::loadMessages(__FILE__);

class Dimensions extends Base
{
	protected $unit;

	public function getDefaultParameters()
	{
		return [
			'name' => 'dimensions',
			'value_type' => Market\Type\Manager::TYPE_DIMENSIONS
		];
	}

	public function getSourceRecommendation(array $context = [])
	{
		return [
			[
				'TYPE' => Market\Export\Entity\Manager::TYPE_CATALOG_PRODUCT,
				'FIELD' => 'YM_SIZE'
			]
		];
	}

	public function getSettingsDescription()
	{
		$langKey = $this->getLangKey();

		$result = [
			'BITRIX_UNIT' => [
				'TITLE' => Market\Config::getLang($langKey . '_SETTINGS_BITRIX_UNIT_TITLE'),
				'TYPE' => 'enumeration',
				'VALUES' => []
			]
		];

		// fill unit

		$unitMap = $this->getUnitMap();

		foreach ($unitMap as $unit => $ratio)
		{
			$result['BITRIX_UNIT']['VALUES'][] = [
				'ID' => $unit,
				'VALUE' => Market\Config::getLang($langKey . '_SETTINGS_BITRIX_UNIT_ENUM_' . strtoupper($unit))
			];
		}

		return $result;
	}

	public function validate($value, array $context, $siblingsValues = null, Market\Result\XmlNode $nodeResult = null, $settings = null)
	{
		$this->resolveUnit($settings);

		return parent::validate($value, $context, $siblingsValues, $nodeResult, $settings);
	}

	protected function formatValue($value, array $context = [], Market\Result\XmlNode $nodeResult = null, $settings = null)
	{
		$this->resolveUnit($settings);

		return parent::formatValue($value, $context, $nodeResult, $settings);
	}

	protected function resolveUnit($settings)
	{
		$this->unit = isset($settings['BITRIX_UNIT']) ? $settings['BITRIX_UNIT'] : '';
	}

	public function getUnit()
	{
		return (string)$this->unit !== '' ? $this->unit : 'mm';
	}

	public function getUnitRatio()
	{
		$unit = $this->getUnit();
		$map = $this->getUnitMap();

		return isset($map[$unit]) ? (float)$map[$unit] : 1;
	}

	protected function getUnitMap()
	{
		return [
			'mm' => 0.1,
			'cm' => 1,
			'dm' => 10,
			'm' => 100,
		];
	}
}
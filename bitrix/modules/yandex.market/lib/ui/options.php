<?php

namespace Yandex\Market\Ui;

use Bitrix\Main;
use Yandex\Market;

Main\Localization\Loc::loadMessages(__FILE__);

class Options
{
	/** @var Main\HttpRequest */
	protected $request;

	public function setRequest(Main\HttpRequest $request)
	{
		$this->request = $request;
	}

	public function save()
	{
		if ($this->request === null) { throw new Main\SystemException('request not set'); }

		$options = $this->getOptions();
		$values = $this->fillValues($options);

		$this->validateValues($options, $values);
		$this->saveValues($options, $values);
	}

	protected function fillValues($options)
	{
		$result = [];

		foreach ($options as $name => $option)
		{
			if (!empty($option['DISABLED'])) { continue; }

			$requestValue = (string)$this->getRequestValue($name);

			$result[$name] = $this->convertFieldToOptionValue($name, $option, $requestValue);
		}

		return $result;
	}

	protected function validateValues($options, $values)
	{
		foreach ($options as $name => $option)
		{
			if (!isset($values[$name]) || $values[$name] === '') { continue; }

			$value = $values[$name];

			switch ($option['TYPE'])
			{
				case 'integer':
					if (!is_numeric($value))
					{
						$message = Market\Config::getLang('UI_OPTION_VALUE_MUST_BE_NUMERIC', [
							'#FIELD#' => $option['NAME'],
						]);

						throw new Main\SystemException($message);
					}
					else if (isset($option['MIN']) && (int)$option['MIN'] > (int)$value)
					{
						$message = Market\Config::getLang('UI_OPTION_VALUE_LESS_THAN', [
							'#FIELD#' => $option['NAME'],
							'#MIN#' => $option['MIN']
						]);

						throw new Main\SystemException($message);
					}
					else if (isset($option['MAX']) && (int)$option['MAX'] < (int)$value)
					{
						$message = Market\Config::getLang('UI_OPTION_VALUE_MORE_THAN', [
							'#FIELD#' => $option['NAME'],
							'#MAX#' => $option['MAX']
						]);

						throw new Main\SystemException($message);
					}
				break;

				case 'enumeration':
					if (isset($option['OPTIONS']) && !isset($option['OPTIONS'][$value]))
					{
						$message = Market\Config::getLang('UI_OPTION_VALUE_NOT_MATCH_OPTIONS', [
							'#FIELD#' => $option['NAME'],
						]);

						throw new Main\SystemException($message);
					}
				break;
			}
		}
	}

	protected function saveValues($options, $values)
	{
		foreach ($values as $name => $value)
		{
			$option = $options[$name];
			$optionValue = (string)$this->getOptionValue($name);
			$valueString = (string)$value;
			$isChanged = true;
			$matchDefault = (
				$option['TYPE'] === 'boolean'
				&& isset($option['DEFAULT'])
				&& (string)$option['DEFAULT'] === $valueString
			);

			if ($optionValue === $valueString)
			{
				$isChanged = false;
			}
			else if ($optionValue === '' && isset($option['DEFAULT']))
			{
				$isChanged = !$matchDefault;
			}

			if ($isChanged)
			{
				if ($valueString === '' || $matchDefault)
				{
					Market\Config::removeOption($name);
				}
				else
				{
					Market\Config::setOption($name, $value);
				}
			}
		}
	}

	public function showTab()
	{
		$this->loadCss();

		$options = $this->getOptions();
		$activeGroup = null;

		foreach ($options as $optionName => $option)
		{
			if (!empty($option['DISABLED'])) { continue; }

			if (isset($option['GROUP']) && $option['GROUP'] !== $activeGroup)
			{
				$activeGroup = $option['GROUP'];

				$this->showGroupHeading($activeGroup);
			}

			$this->showField($optionName, $option);
		}
	}

	protected function loadCss()
	{
		global $APPLICATION;

		$APPLICATION->SetAdditionalCSS('/bitrix/css/yandex.market/base.css');
	}

	protected function showGroupHeading($group)
	{
		echo
			'<tr class="heading"><td colspan="2">'
			. Market\Config::getLang('UI_OPTION_GROUP_' . $group)
		 	. '</td></tr>';
	}

	protected function showField($name, $option)
	{
		echo
			'<tr>'
			. '<td class="adm-detail-content-cell-l adm-detail-valign-middle" width="40%" valign="middle">';

		if (isset($option['HINT']))
		{
			echo
				'<span class="b-icon icon--question indent--right b-tag-tooltip--holder">'
					. '<span class="b-tag-tooltip--content">' . $option['HINT'] . '</span>'
				. '</span>';
		}

		echo $option['NAME'] . ':';

		echo
			'</td>'
			. '<td class="adm-detail-content-cell-r" width="60%">';

		$this->showFieldControl($name, $option);

		echo
			'</td>'
			.'</tr>';
	}

	protected function showFieldControl($name, $option)
	{
		global $USER_FIELD_MANAGER;

		$value = $this->getFieldValue($name, $option);
		$hasDefaultValue = isset($option['DEFAULT']);
		$supportDefaultValue = $this->isFieldSupportDefaultValue($option['TYPE']);

		if ($value === null && $hasDefaultValue && !$supportDefaultValue)
		{
			$value = $this->convertOptionToFieldValue($name, $option, $option['DEFAULT']);
		}

		$field = $this->getField($name, $option);
		$field['VALUE'] = $value;

		$html = $USER_FIELD_MANAGER->GetEditFormHTML(false, null, $field);
		$html = $this->extractAdminInput($html);

		if ($hasDefaultValue && $supportDefaultValue)
		{
			$html = $this->insertInputDefaultValue($html, $field['DEFAULT']);
		}

		echo $html;
	}

	protected function getField($name, $option)
	{
		$result = $option + [
			'EDIT_IN_LIST' => 'Y',
			'MULTIPLE' => 'N',
			'EDIT_FORM_LABEL' => $option['NAME'],
			'FIELD_NAME' => $name,
			'MANDATORY' => 'N',
			'ENTITY_VALUE_ID' => 1, // fix for boolean type
		];

		if (!isset($result['USER_TYPE']))
		{
			$result += $this->getFieldUserType($option);
		}

		return $result;
	}

	protected function getFieldUserType($option)
	{
		global $USER_FIELD_MANAGER;

		$result = [
			'USER_TYPE' => $USER_FIELD_MANAGER->GetUserType($option['TYPE']),
		];

		if ($option['TYPE'] === 'enumeration' && isset($option['OPTIONS']))
		{
			$result['USER_TYPE']['CLASS_NAME'] = 'Yandex\Market\Ui\UserField\EnumerationType';
			$result['SETTINGS'] = [
				'CAPTION_NO_VALUE' => Market\Config::getLang('UI_OPTION_VALUE_DEFAULT'),
			];
			$result['OPTIONS'] = [];

			foreach ($option['OPTIONS'] as $key => $value)
			{
				$result['VALUES'][] = [
					'ID' => $key,
					'VALUE' => $value,
				];
			}
		}
		else if ($option['TYPE'] === 'boolean')
		{
			$result['SETTINGS'] = [
				'LABEL_CHECKBOX' => ' ', // hide label
			];
		}

		return $result;
	}

	protected function extractAdminInput($html)
	{
		$result = $html;

		if (preg_match('/^<tr.*?>(?:<td.*?>.*?<\/td>)?<td.*?>(.*)<\/td><\/tr>$/s', $html, $match))
		{
			$result = $match[1];
		}

		return $result;
	}

	protected function isFieldSupportDefaultValue($type)
	{
		return (
			$type !== 'boolean'
			&& $type !== 'enumeration'
		);
	}

	protected function insertInputDefaultValue($html, $value)
	{
		$result = $html;

		if (preg_match('/^(.*?)(<(?:input|textarea).*?)(\/?>.*)$/', $html, $matches))
		{
			$result =
				$matches[1]
				. $matches[2]
				. ' placeholder ="' . htmlspecialcharsbx($value) . '"'
				. $matches[3];
		}

		return $result;
	}

	protected function getFieldValue($name, $option)
	{
		$value = $this->getRequestValue($name);

		if ($value === null)
		{
			$value = $this->getOptionValue($name);
			$value = $this->convertOptionToFieldValue($name, $option, $value);
		}

		return $value;
	}

	protected function getRequestValue($name)
	{
		$result = null;

		if ($this->request !== null)
		{
			$value = $this->request->getPost($name);

			if ($value !== null)
			{
				$result = trim($value);
			}
		}

		return $result;
	}

	protected function getOptionValue($name)
	{
		$option = (string)Market\Config::getOption($name);
		$result = null;

		if ($option !== '')
		{
			$result = $option;
		}

		return $result;
	}

	protected function convertFieldToOptionValue($name, $option, $value)
	{
		$result = $value;
		$valueString = (string)$value;

		if ($valueString !== '' && $option['TYPE'] === 'boolean')
		{
			$result = ($valueString === '1' ? 'Y' : 'N');
		}

		return $result;
	}

	protected function convertOptionToFieldValue($name, $option, $value)
	{
		$result = $value;

		if ($value !== null && $option['TYPE'] === 'boolean')
		{
			$result = ($value === 'Y');
		}

		return $result;
	}

	public function getOptions()
	{
		return
			$this->getExportOptions()
			+ $this->getPromoOptions()
			+ $this->getCatalogOptions()
			+ $this->getAdditionalOptions();
	}

	protected function getExportOptions()
	{
		$isAgentCli = Market\Utils::isAgentUseCron();

		return [
			'export_run_offer_page_size' => [
				'TYPE' => 'integer',
				'GROUP' => 'EXPORT',
				'NAME' => Market\Config::getLang('UI_OPTION_EXPORT_OFFER_PAGE_SIZE'),
				'HINT' => Market\Config::getLang('UI_OPTION_EXPORT_OFFER_PAGE_SIZE_HINT'),
				'DEFAULT' => 50,
				'MIN' => 1,
			],
			'export_run_agent_changes_limit' => [
				'TYPE' => 'integer',
				'GROUP' => 'EXPORT',
				'NAME' => Market\Config::getLang('UI_OPTION_EXPORT_AGENT_CHANGES_LIMIT'),
				'HINT' => Market\Config::getLang('UI_OPTION_EXPORT_AGENT_CHANGES_LIMIT_HINT'),
				'DEFAULT' => 1000,
				'MIN' => 1,
			],
			'export_run_agent_time_limit_cli' => [
				'TYPE' => 'integer',
				'GROUP' => 'EXPORT',
				'NAME' => Market\Config::getLang('UI_OPTION_EXPORT_AGENT_TIME_LIMIT'),
				'HINT' => Market\Config::getLang('UI_OPTION_EXPORT_AGENT_TIME_LIMIT_HINT'),
				'DEFAULT' => 30,
				'MIN' => 0,
				'MAX' => 50,
				'DISABLED' => !$isAgentCli,
			],
			'export_run_agent_time_limit' => [
				'TYPE' => 'integer',
				'GROUP' => 'EXPORT',
				'NAME' => Market\Config::getLang('UI_OPTION_EXPORT_AGENT_TIME_LIMIT'),
				'HINT' => Market\Config::getLang('UI_OPTION_EXPORT_AGENT_TIME_LIMIT_HINT'),
				'DEFAULT' => 5,
				'MIN' => 0,
				'MAX' => 50,
				'DISABLED' => $isAgentCli,
			],
		];
	}

	protected function getCatalogOptions()
	{
		$hasCatalog = Main\ModuleManager::isModuleInstalled('catalog');

		return [
			'export_catalog_price_discount_properties_optimize' => [
				'TYPE' => 'boolean',
				'GROUP' => 'CATALOG',
				'NAME' => Market\Config::getLang('UI_OPTION_EXPORT_CATALOG_PRICE_DISCOUNT_PROPERTIES_OPTIMIZE'),
				'HINT' => Market\Config::getLang('UI_OPTION_EXPORT_CATALOG_PRICE_DISCOUNT_PROPERTIES_OPTIMIZE_HINT'),
				'DEFAULT' => 'Y',
				'DISABLED' => !$hasCatalog,
			],
			'export_entity_catalog_use_short' => [
				'TYPE' => 'boolean',
				'GROUP' => 'CATALOG',
				'NAME' => Market\Config::getLang('UI_OPTION_EXPORT_CATALOG_USE_SHORT'),
				'HINT' => Market\Config::getLang('UI_OPTION_EXPORT_CATALOG_USE_SHORT_HINT'),
				'DEFAULT' => 'Y',
				'DISABLED' => !$hasCatalog || !Market\Export\Entity\Catalog\Provider::supportCatalogShortFields(),
			],
			'export_offer_catalog_type_compatibility' => [
				'TYPE' => 'enumeration',
				'GROUP' => 'CATALOG',
				'NAME' => Market\Config::getLang('UI_OPTION_EXPORT_CATALOG_TYPE_COMPATIBILITY'),
				'HINT' => Market\Config::getLang('UI_OPTION_EXPORT_CATALOG_TYPE_COMPATIBILITY_HINT'),
				'OPTIONS' => [
					'N' => Market\Config::getLang('UI_OPTION_VALUE_N'),
					'Y' => Market\Config::getLang('UI_OPTION_VALUE_Y'),
				],
				'DISABLED' => !$hasCatalog,
			],
			'export_entity_catalog_sku_available_auto' => [
				'TYPE' => 'enumeration',
				'GROUP' => 'CATALOG',
				'NAME' => Market\Config::getLang('UI_OPTION_EXPORT_CATALOG_SKU_AVAILABLE_AUTO'),
				'HINT' => Market\Config::getLang('UI_OPTION_EXPORT_CATALOG_SKU_AVAILABLE_AUTO_HINT'),
				'OPTIONS' => [
					'N' => Market\Config::getLang('UI_OPTION_VALUE_N'),
					'Y' => Market\Config::getLang('UI_OPTION_VALUE_Y'),
				],
				'DISABLED' => !$hasCatalog,
			],
		];
	}

	protected function getPromoOptions()
	{
		$hasCatalog = Main\ModuleManager::isModuleInstalled('catalog');
		$hasSale = Main\ModuleManager::isModuleInstalled('sale');

		return [
			'export_promo_discount_external_gift' => [
				'TYPE' => 'boolean',
				'GROUP' => 'PROMO',
				'NAME' => Market\Config::getLang('UI_OPTION_EXPORT_PROMO_DISCOUNT_EXTERNAL_GIFT'),
				'HINT' => Market\Config::getLang('UI_OPTION_EXPORT_PROMO_DISCOUNT_EXTERNAL_GIFT_HINT'),
				'DEFAULT' => 'Y',
				'DISABLED' => !$hasCatalog && !$hasSale,
			],
		];
	}

	protected function getAdditionalOptions()
	{
		return [
			'expert_mode' => [
				'TYPE' => 'boolean',
				'GROUP' => 'ADDITIONAL',
				'NAME' => Market\Config::getLang('UI_OPTION_EXPERT_MODE'),
				'DEFAULT' => 'N',
			],
		];
	}
}
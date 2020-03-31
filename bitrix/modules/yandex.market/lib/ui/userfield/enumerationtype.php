<?php

namespace Yandex\Market\Ui\UserField;

use Yandex\Market;

class EnumerationType extends \CUserTypeEnum
{
	function GetList($arUserField)
	{
		$result = new \CDBResult();
		$result->InitFromArray($arUserField['VALUES']);

		return $result;
	}

	function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		$result = '';

		if (isset($arUserField['SETTINGS']['DISPLAY']) && $arUserField['SETTINGS']['DISPLAY'] === 'CHECKBOX')
		{
			$result = parent::GetEditFormHTML($arUserField, $arHtmlControl);
		}
		else
		{
			if ($arUserField['ENTITY_VALUE_ID'] < 1 && strlen($arUserField['SETTINGS']['DEFAULT_VALUE']) > 0)
			{
				$arHtmlControl['VALUE'] = $arUserField['SETTINGS']['DEFAULT_VALUE'];
			}

			$queryEnum = call_user_func(
				[ $arUserField['USER_TYPE']['CLASS_NAME'], 'getList'],
				$arUserField
			);

			if ($queryEnum)
			{
				$hasSelected = false;
				$optionHtml = '';
				$optionGroup = null;

				while ($option = $queryEnum->GetNext())
				{
					$isSelected = (
						($arHtmlControl['VALUE'] == $option['ID'])
						|| ($arUserField['ENTITY_VALUE_ID'] <= 0 && $option['DEF'] === 'Y')
					);

					if ($isSelected) { $hasSelected = true; }

					if (isset($option['GROUP']) && $option['GROUP'] !== $optionGroup)
					{
						if ($optionGroup !== null) { $optionHtml .= '</optgroup>'; }

						$optionGroup = $option['GROUP'];
						$optionHtml .= '<optgroup label="' . str_replace('"', '\\"', $option['GROUP']) . '">';
					}

					$optionHtml .= '<option value="'.$option['ID'].'" ' . ($isSelected? 'selected' : '') . '>'.$option['VALUE'].'</option>';
				}

				if ($optionGroup !== null) { $optionHtml .= '</optgroup>'; }

				if ($arUserField['SETTINGS']['LIST_HEIGHT'] > 1)
				{
					$size = ' size="'.$arUserField['SETTINGS']['LIST_HEIGHT'].'"';
				}
				else
				{
					$arHtmlControl['VALIGN'] = 'middle';
					$size = '';
				}

				$result = '<select name="'.$arHtmlControl['NAME'].'"'. $size . ($arUserField["EDIT_IN_LIST"] !== 'Y' ? ' disabled': '') . '>';

				if ($arUserField['MANDATORY'] !== 'Y')
				{
					$noValueCaption = (string)$arUserField['SETTINGS']['CAPTION_NO_VALUE'] !== '' ? $arUserField['SETTINGS']['CAPTION_NO_VALUE'] : GetMessage('MAIN_NO');

					$result .= '<option value="" ' . (!$hasSelected ? 'selected': '').'>'.htmlspecialcharsbx($noValueCaption).'</option>';
				}

				$result .= $optionHtml;

				$result .= '</select>';
			}
		}

		return $result;
	}

	function GetAdminListViewHTML($arUserField, $arHtmlControl)
	{
		$result = '&nbsp;';
		$isFoundResult = false;

		if (!empty($arHtmlControl['VALUE']))
		{
			$query = call_user_func([ $arUserField['USER_TYPE']['CLASS_NAME'], 'getlist' ], $arUserField);

			if ($query)
			{
				while ($option = $query->Fetch())
				{
					if ($option['ID'] == $arHtmlControl['VALUE'])
					{
						$isFoundResult = true;
						$result = Market\Utils::htmlEscape($option['VALUE']);
						break;
					}
				}
			}

			if (!$isFoundResult)
			{
				$result = '[' . Market\Utils::htmlEscape($arHtmlControl['VALUE']) . ']';
			}
		}

		return $result;
	}

	function GetAdminListViewHTMLMulty($arUserField, $arHtmlControl)
	{
		$result = '';

		if (!empty($arHtmlControl['VALUE']))
		{
			$query = call_user_func([ $arUserField['USER_TYPE']['CLASS_NAME'], 'getlist' ], $arUserField);
			$valueList = (array)$arHtmlControl['VALUE'];
			$valueMap = array_flip($valueList);

			if ($query)
			{
				while ($option = $query->Fetch())
				{
					if (isset($valueMap[$option['ID']]))
					{
						$result .= ($result !== '' ? ' / ' : '') . Market\Utils::htmlEscape($option['VALUE']);
					}
				}
			}
		}

		if ($result === '')
		{
			$result = '&nbsp;';
		}

		return $result;
	}
}
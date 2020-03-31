<?php

namespace Yandex\Market\Ui\UserField;

use Bitrix\Main;
use Yandex\Market;

Main\Localization\Loc::loadMessages(__FILE__);

class SetupLinkType extends EnumerationType
{
	function GetList($arUserField)
	{
		static $result = null;

		if ($result === null)
		{
			$result = [];

			if (Main\Loader::includeModule('yandex.market'))
			{
				$querySetupList = Market\Export\Setup\Table::getList([
					'select' => [ 'ID', 'NAME' ]
				]);

				while ($setup = $querySetupList->fetch())
				{
					$result[] = [
						'ID' => $setup['ID'],
						'VALUE' => '[' . $setup['ID'] . '] ' . $setup['NAME']
					];
				}
			}
		}

		$queryResult = new \CDBResult();
		$queryResult->InitFromArray($result);

		return $queryResult;
	}

	function GetAdminListViewHTML($arUserField, $arHtmlControl)
	{
		if (static::isExportAllRow($arUserField))
		{
			$result = Market\Config::getLang('USER_FIELD_SETUP_LINK_TYPE_EXPORT_ALL');
		}
		else
		{
			$result = parent::GetAdminListViewHTML($arUserField, $arHtmlControl);
		}

		$result = static::getExportStatusInfo($arUserField['ROW'], $result);

		return $result;
	}

	function GetAdminListViewHTMLMulty($arUserField, $arHtmlControl)
	{
		if (static::isExportAllRow($arUserField))
		{
			$result = Market\Config::getLang('USER_FIELD_SETUP_LINK_TYPE_EXPORT_ALL');
		}
		else
		{
			$result = parent::GetAdminListViewHTMLMulty($arUserField, $arHtmlControl);
		}

		$result = static::getExportStatusInfo($arUserField['ROW'], $result);

		return $result;
	}

	function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		if (isset($arUserField['SETTINGS'])) { $arUserField['SETTINGS'] = []; }

		$arUserField['MANDATORY'] = 'Y';
		$arUserField['SETTINGS']['DISPLAY'] = 'CHECKBOX';

		return parent::GetEditFormHTML($arUserField, $arHtmlControl);
	}

	public function GetEditFormHTMLMulty($arUserField, $arHtmlControl)
	{
		if (isset($arUserField['SETTINGS'])) { $arUserField['SETTINGS'] = []; }

		$arUserField['MANDATORY'] = 'Y';
		$arUserField['SETTINGS']['DISPLAY'] = 'CHECKBOX';

		return parent::GetEditFormHTMLMulty($arUserField, $arHtmlControl);
	}

	protected static function isExportAllRow($arUserField)
	{
		$exportAllFieldName = $arUserField['FIELD_NAME'] . '_EXPORT_ALL';

		return (
			isset($arUserField['ROW'][$exportAllFieldName])
			&& $arUserField['ROW'][$exportAllFieldName] === Market\Reference\Storage\Table::BOOLEAN_Y
		);
	}

	protected static function getExportStatusInfo($row, $displaValue)
	{
		$result = $displaValue;
		$status = null;
		$reason = null;

		if (isset($row['EXPORT_STATUS']))
		{
			$hasExportSuccess = false;
			$hasExportErrors = false;
			$reason = $row['EXPORT_STATUS']['REASON'];
			$messageData = [
				'#EXPORT_URL#' => '/bitrix/admin/yamarket_promo_run.php?lang=' . LANGUAGE_ID . '&id=' . $row['ID'],
				'#LOG_URL#' => '/bitrix/admin/yamarket_log.php?lang=' . LANGUAGE_ID . '&find_promo_id=' . $row['ID'] . '&set_filter=Y'
			];

			foreach ($row['EXPORT_STATUS']['RESULT'] as $exportResult)
			{
				if ((int)$exportResult['STATUS'] === Market\Export\Run\Steps\Base::STORAGE_STATUS_FAIL) // fail
				{
					$hasExportErrors = true;
				}
				else if (
					(int)$exportResult['STATUS'] === Market\Export\Run\Steps\Base::STORAGE_STATUS_SUCCESS // success
					|| ((int)$exportResult['STATUS'] === Market\Export\Run\Steps\Base::STORAGE_STATUS_INVALID && (string)$exportResult['HASH'] !== '') // process changes, but early export success
				)
				{
					$hasExportSuccess = true;
				}
			}

			if ($row['EXPORT_STATUS']['STATUS'])
			{
				if ($hasExportErrors)
				{
					$action = 'LOG';

					if ($hasExportSuccess)
					{
						$status = 'yellow';
						$reason = Market\Config::getLang('USER_FIELD_SETUP_LINK_TYPE_EXPORT_REASON_FAIL_PART', $messageData);
					}
					else
					{
						$status = 'red';
						$reason = Market\Config::getLang('USER_FIELD_SETUP_LINK_TYPE_EXPORT_REASON_FAIL_ALL', $messageData);
					}
				}
				else if ($hasExportSuccess)
				{
					$status = 'green';
					$reason = Market\Config::getLang('USER_FIELD_SETUP_LINK_TYPE_EXPORT_REASON_SUCCESS', $messageData);
				}
				else
				{
					$status = 'red';
					$reason = Market\Config::getLang('USER_FIELD_SETUP_LINK_TYPE_EXPORT_REASON_NO_RESULT', $messageData);
				}
			}
			else if ($hasExportSuccess)
			{
				$status = 'red';
				$reason = ($reason ? $reason . ', ' : '') . Market\Config::getLang('USER_FIELD_SETUP_LINK_TYPE_EXPORT_REASON_NEED_DELETE', $messageData);
			}
			else
			{
				$status = 'grey';
			}
		}

		if ($status !== null)
		{
			$result = '<img class="b-log-icon" src="/bitrix/images/yandex.market/' . $status . '.gif" width="14" height="14" alt="" />';
			$result .= $displaValue;

			if ($reason !== null)
			{
				$result .= ', ' . $reason;
			}
		}

		return $result;
	}
}
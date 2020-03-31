<?php

namespace Yandex\Market\Ui\UserField;

use Yandex\Market;
use Bitrix\Main;

Main\Localization\Loc::loadMessages(__FILE__);

class ExportFileType extends \CUserTypeString
{
	function GetAdminListViewHTML($arUserField, $arHtmlControl)
	{
		$result = '';
		$rowId = !empty($arUserField['ROW']['ID']) ? (int)$arUserField['ROW']['ID'] : null;
		$fileName = Market\Export\Setup\Model::normalizeFileName($arHtmlControl['VALUE'], $rowId);

		if ($fileName !== null)
		{
			$filePath = BX_ROOT . '/catalog_export/' . $fileName;
			$status = static::getSetupStatus($rowId, $filePath);

			switch ($status)
			{
				case 'READY':
					$result = '<img class="b-log-icon" src="/bitrix/images/yandex.market/green.gif" width="14" height="14" alt="" />';
					$result .= '<a href="' . htmlspecialcharsbx($filePath) . '" target="_blank">' . htmlspecialcharsbx($fileName) . '</a>';
				break;

				case 'PROGRESS':
					$result = '<img class="b-log-icon" src="/bitrix/images/yandex.market/yellow.gif" width="14" height="14" alt="" />';
					$result .= Market\Config::getLang('UI_USER_FIELD_EXPORT_FILE_TYPE_FILE_PROGRESS');
				break;

				default:
					$result = '<img class="b-log-icon" src="/bitrix/images/yandex.market/red.gif" width="14" height="14" alt="" />';
					$result .= Market\Config::getLang('UI_USER_FIELD_EXPORT_FILE_TYPE_FILE_' . $status);

					if ($rowId)
					{
						$result .=
							'<a href="yamarket_setup_run.php?lang=' . LANGUAGE_ID . '&id=' . $rowId . '">'
							. Market\Config::getLang('UI_USER_FIELD_EXPORT_FILE_TYPE_RUN_EXPORT')
							. '</a>';
					}
					else
					{
						$result = $fileName;
					}
				break;
			}
		}

		return $result;
	}

	protected static function getSetupStatus($setupId, $filePath)
	{
		$absolutePath = $_SERVER['DOCUMENT_ROOT'] . $filePath;
		$tempPath = $absolutePath . '.tmp';
		$hasProgress = Market\Export\Run\Admin::hasProgress($setupId);

		if (file_exists($tempPath) || $hasProgress)
		{
			$result = ($hasProgress && !Market\Export\Run\Admin::isProgressExpired($setupId) ? 'PROGRESS' : 'FAIL');
		}
		else if (file_exists($absolutePath))
		{
			$result = 'READY';
		}
		else
		{
			$result = 'NOT_FOUND';
		}

		return $result;
	}
}
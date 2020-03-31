<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Yandex\Market;

Loc::loadMessages(__FILE__);

$module_id = 'yandex.market';
$accessLevel = $APPLICATION->GetGroupRight($module_id);

if ($accessLevel < 'R')
{
	$APPLICATION->AuthForm(Loc::getMessage('YANDEX_MARKET_OPTIONS_ACCESS_DENIED'));
	return;
}
else if (!Main\Loader::includeModule($module_id))
{
	\CAdminMessage::ShowMessage([
		'TYPE' => 'ERROR',
		'MESSAGE' => Loc::getMessage('YANDEX_MARKET_OPTIONS_REQUIRE_MODULE')
	]);
}
else
{
	// process request

	$uiOptions = new Market\Ui\Options();
	$request = Main\Context::getCurrent()->getRequest();
	$requestAction = $request->getPost('action');
	$errorMessage = null;

	if ($requestAction !== null)
	{
		if ($accessLevel < 'W')
		{
			$errorMessage = Market\Config::getLang('OPTIONS_REQUEST_ACCESS_DENIED');
		}
		else if (!check_bitrix_sessid())
		{
			$errorMessage = Market\Config::getLang('OPTIONS_REQUEST_SESSION_EXPIRED');
		}
		else if ($requestAction === 'save')
		{
			try
			{
				$uiOptions->setRequest($request);
				$uiOptions->save();

				ob_start();
				$Update = '1'; // need inside main module
				require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights.php';
				ob_end_clean();

				LocalRedirect($APPLICATION->GetCurPageParam());
			}
			catch (Main\SystemException $exception)
			{
				$message = new CAdminMessage([
					'TYPE' => 'ERROR',
					'MESSAGE' => $exception->getMessage(),
					'HTML' => true,
				]);

				echo $message->Show();
			}
		}
		else
		{
			$errorMessage = Market\Config::getLang('OPTIONS_REQUEST_UNKNOWN_ACTION');
		}
	}

	if ($errorMessage !== null)
	{
		$message = new CAdminMessage([ 'MESSAGE' => $errorMessage ]);
		echo $message->Show();
	}

	// tabs view

	$tabs = [
		[ 'DIV' => 'options', 'TAB' => Market\Config::getLang('OPTIONS_TAB_OPTIONS') ],
		[ 'DIV' => 'permissions', 'TAB' => Market\Config::getLang('OPTIONS_TAB_PERMISSIONS') ],
	];
	$tabControl = new CAdminTabControl(Market\Config::getLangPrefix() . 'OPTIONS', $tabs, true, true);

	$tabControl->Begin();
	?>
	<form method="POST" action="<?= POST_FORM_ACTION_URI; ?>">
		<input type="hidden" name="action" value="save">
		<?
		echo bitrix_sessid_post();

		$tabControl->BeginNextTab();
		$uiOptions->showTab();

		$tabControl->BeginNextTab();
		require_once $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/admin/group_rights.php';

		$tabControl->Buttons();
		?>
		<input type="submit" class="adm-btn-save" value="<?= Market\Config::getLang('OPTIONS_BUTTON_SAVE'); ?>" <?= $accessLevel < 'W' ? 'disabled' : ''; ?> />
		<input type="reset" value="<?= Market\Config::getLang('OPTIONS_BUTTON_RESET'); ?>">
	</form>
	<?

	$tabControl->End();
}
<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

use Yandex\Market;
use Bitrix\Main\Localization\Loc;

/** @var $component \Yandex\Market\Components\AdminFormEdit */

Market\Ui\Extension::loadOne([ 'jquery2', 'jquery' ], true);

$this->addExternalCss('/bitrix/css/yandex.market/base.css');

$this->addExternalJs('/bitrix/js/yandex.market/utils.js');
$this->addExternalJs('/bitrix/js/yandex.market/plugin/base.js');
$this->addExternalJs('/bitrix/js/yandex.market/plugin/manager.js');
$this->addExternalJs('/bitrix/js/yandex.market/ui/form/notifyunsaved.js');
$this->addExternalJs('/bitrix/js/yandex.market/field/reference/base.js');
$this->addExternalJs('/bitrix/js/yandex.market/field/reference/complex.js');
$this->addExternalJs('/bitrix/js/yandex.market/field/reference/collection.js');
$this->addExternalJs('/bitrix/js/yandex.market/field/reference/summary.js');

if (!empty($arResult['CONTEXT_MENU']))
{
	$context = new CAdminContextMenu($arResult['CONTEXT_MENU']);
	$context->Show();
}

if ($component->hasErrors())
{
	$component->showErrors();
}

$langNotifyUnsaved = [
	'MESSAGE' => Loc::getMessage('YANDEX_MARKET_T_ADMIN_FORM_EDIT_NOTIFY_UNSAVED_MESSAGE')
];

$tabControl = new \CAdminTabControl($arParams['FORM_ID'], $arResult['TABS'], false, true);

if ($arParams['FORM_BEHAVIOR'] === 'steps')
{
	foreach ($arResult['TABS'] as $tab)
	{
		if ($tab['STEP'] === $arResult['STEP'])
		{
			if (isset($tab['DATA']['METRIKA_GOAL']))
			{
				Market\Metrika::reachGoal($tab['DATA']['METRIKA_GOAL']);
			}

			$_REQUEST[$arParams['FORM_ID'] . '_active_tab'] = $tab['DIV'];
			break;
		}
	}
}

include __DIR__ . '/partials/check-javascript.php';

?>
<form class="js-plugin" method="POST" action="<?echo $APPLICATION->GetCurPageParam(); ?>" data-plugin="Ui.Form.NotifyUnsaved" <?= $arResult['HAS_REQUEST'] ? 'data-changed="true"' : ''; ?>>
	<?
	$tabControl->Begin();

	echo bitrix_sessid_post();

	foreach ($arResult['TABS'] as $tab)
	{
		$tabControl->BeginNextTab([ 'showTitle' => false ]);

		$isActiveTab = ($arParams['FORM_BEHAVIOR'] !== 'steps' || $tab['STEP'] === $arResult['STEP']);
		$tabLayout = $tab['LAYOUT'] ?: 'default';
		$fields = $tab['FIELDS'];

		include __DIR__ . '/partials/hidden.php';
		include __DIR__ . '/partials/tab-' . $tabLayout . '.php';
	}

	$tabControl->Buttons();

	switch ($arParams['FORM_BEHAVIOR'])
	{
		case 'steps':
			?>
			<input type="hidden" name="STEP" value="<?= $arResult['STEP']; ?>" />
			<?

			// previous button

			if ($arResult['STEP'] === 0)
			{
				?>
				<button class="adm-btn" type="submit" name="cancel" value="Y"><?= Loc::getMessage('YANDEX_MARKET_T_ADMIN_FORM_EDIT_NOTIFY_BTN_CANCEL'); ?></button>
				<?
			}
			else
			{
				?>
				<button class="adm-btn" type="submit" name="stepAction" value="previous"><?= Loc::getMessage('YANDEX_MARKET_T_ADMIN_FORM_EDIT_NOTIFY_BTN_PREV_STEP'); ?></button>
				<?
			}

			// next button

			if ($arResult['STEP_FINAL'])
			{
				?>
				<button
					class="adm-btn adm-btn-save <?= $arParams['ALLOW_SAVE'] ? '' : 'adm-btn-disabled'; ?>"
					type="submit"
					name="save"
					value="Y"
					<?= $arParams['ALLOW_SAVE'] ? '' : 'disabled'; ?>
				><?= $arParams['BTN_SAVE'] ?: Loc::getMessage('YANDEX_MARKET_T_ADMIN_FORM_EDIT_NOTIFY_BTN_SAVE'); ?></button>
				<?
			}
			else
			{
				?>
				<button class="adm-btn adm-btn-save" type="submit" name="stepAction" value="next"><?= Loc::getMessage('YANDEX_MARKET_T_ADMIN_FORM_EDIT_NOTIFY_BTN_NEXT_STEP'); ?></button>
				<?
			}

		break;

		default:
			?>
			<input type="submit" name="save" value="<?= $arParams['BTN_SAVE'] ?: Loc::getMessage('YANDEX_MARKET_T_ADMIN_FORM_EDIT_NOTIFY_BTN_SAVE'); ?>" class="adm-btn-save">
			<input type="submit" name="apply" value="<?= Loc::getMessage('YANDEX_MARKET_T_ADMIN_FORM_EDIT_NOTIFY_BTN_APPLY'); ?>" class="adm-btn">
			<?
		break;

	}

	$tabControl->End();
	?>
</form>
<script>
	(function() {
		var utils = BX.namespace('YandexMarket.Utils');

		utils.registerLang(<?= Market\Utils::jsonEncode($langNotifyUnsaved, JSON_UNESCAPED_UNICODE); ?>, 'YANDEX_MARKET_FORM_NOTIFY_UNSAVED_');
	})();
</script>
<?
if ($arParams['FORM_BEHAVIOR'] === 'steps')
{
	?>
	<script>
		<?
		foreach ($arResult['TABS'] as $tab)
		{
			if ($tab['STEP'] !== $arResult['STEP'])
			{
				?>
				<?= $arParams['FORM_ID']; ?>.DisableTab('<?= $tab['DIV']; ?>');
				<?
			}
		}
		?>
	</script>
	<?
}

<?
/** @global CMain$APPLICATION */
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;

$accessLevel = $APPLICATION->GetGroupRight('yandex.market');

if ($accessLevel >= 'R')
{
	Loc::loadMessages(__FILE__);

	$assets = Asset::getInstance();
	$iconPath = BX_ROOT . '/images/yandex.market/menu.png';

	$assets->addString('<style> .yamarket_menu_icon { background: url("' . htmlspecialcharsbx($iconPath) . '") no-repeat 50% 50%; background-size: 100% auto; } </style>');

	return array(
		"parent_menu" => "global_menu_services",
		"section" => "yamarket",
		"sort" => 1000,
		"text" => Loc::getMessage("YANDEX_MARKET_MENU_CONTROL"),
		"title" => Loc::getMessage("YANDEX_MARKET_MENU_TITLE"),
		"icon" => "yamarket_menu_icon",
		"items_id" => "menu_yamarket",
		"items" => array(
			array(
				"text" => Loc::getMessage("YANDEX_MARKET_MENU_SETTINGS"),
				"title" => Loc::getMessage("YANDEX_MARKET_MENU_SETTINGS"),
				"url" => "yamarket_setup_list.php?lang=".LANGUAGE_ID,
				"more_url" => array(
					"yamarket_setup_list.php",
					"yamarket_setup_edit.php",
					"yamarket_setup_run.php",
					"yamarket_migration.php"
				)
			),
			array(
				"text" => Loc::getMessage("YANDEX_MARKET_MENU_PROMO"),
				"title" => Loc::getMessage("YANDEX_MARKET_MENU_PROMO"),
				"url" => "yamarket_promo_list.php?lang=".LANGUAGE_ID,
				"more_url" => array(
					"yamarket_promo_list.php",
					"yamarket_promo_edit.php",
					"yamarket_promo_run.php",
					"yamarket_promo_result.php",
				)
			),
			array(
				"text" => Loc::getMessage("YANDEX_MARKET_MENU_LOG"),
				"title" => Loc::getMessage("YANDEX_MARKET_MENU_LOG"),
				"url" => "yamarket_log.php?lang=".LANGUAGE_ID,
				"more_url" => array()
			),
			array(
				"text" => Loc::getMessage("YANDEX_MARKET_MENU_HELP"),
				"title" => Loc::getMessage("YANDEX_MARKET_MENU_HELP"),
				"url" => "javascript:window.open('https://yandex.ru/support/market-cms/', '_blank');void(0);",
				"more_url" => array()
			)
		)
	);
}
else
{
	return false;
}
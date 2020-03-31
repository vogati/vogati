<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?$APPLICATION->IncludeComponent(
	"bitrix:eshop.socnet.links", 
	"bootstrap_v4", 
	array(
		"COMPONENT_TEMPLATE" => "bootstrap_v4",
		"FACEBOOK" => "https://www.facebook.com/vogati.ru/",
		"VKONTAKTE" => "https://vk.com/vogati",
		"TWITTER" => "https://twitter.com/Vogati_ru",
		"GOOGLE" => "",
		"INSTAGRAM" => "https://www.instagram.com/vogati_ru/"
	),
	false,
	array(
		"HIDE_ICONS" => "N"
	)
);?>
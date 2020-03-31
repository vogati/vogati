<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Title");
?><?$APPLICATION->IncludeComponent(
	"bitrix:catalog", 
	"catalog-template", 
	array(
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "7",
		"HIDE_NOT_AVAILABLE" => "N",
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => SITE_DIR."catalog/",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"CACHE_TYPE" => "N",
		"CACHE_TIME" => "36000000",
		"CACHE_FILTER" => "N",
		"CACHE_GROUPS" => "Y",
		"SET_STATUS_404" => "Y",
		"SET_TITLE" => "Y",
		"ADD_SECTIONS_CHAIN" => "Y",
		"ADD_ELEMENT_CHAIN" => "Y",
		"USE_ELEMENT_COUNTER" => "Y",
		"USE_FILTER" => "Y",
		"FILTER_NAME" => "",
		"FILTER_FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"FILTER_PROPERTY_CODE" => array(
			0 => "EMARKET_BRAND",
			1 => "",
		),
		"FILTER_PRICE_CODE" => array(
			0 => "BASE",
		),
		"FILTER_OFFERS_FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"FILTER_OFFERS_PROPERTY_CODE" => array(
			0 => "EMARKET_SKU_MEMORY",
			1 => "EMARKET_SKU_COLOR",
			2 => "",
		),
		"FILTER_VIEW_MODE" => "VERTICAL",
		"USE_REVIEW" => "N",
		"USE_COMPARE" => "Y",
		"COMPARE_NAME" => "CATALOG_COMPARE_LIST",
		"COMPARE_FIELD_CODE" => array(
			0 => "PREVIEW_PICTURE",
			1 => "",
		),
		"COMPARE_PROPERTY_CODE" => array(
			0 => "EMARKET_PR_STAND_COLOR",
			1 => "EMARKET_PR_GPS",
			2 => "EMARKET_PR_LED",
			3 => "EMARKET_PR_AUTOFOCUS",
			4 => "EMARKET_PR_LENS_MOUNT",
			5 => "EMARKET_PR_INTERNET_WIRELESS",
			6 => "EMARKET_BRAND",
			7 => "EMARKET_PR_NUMBER",
			8 => "EMARKET_PR_VERTICAL",
			9 => "EMARKET_PR_WEIGHT",
			10 => "EMARKET_PR_ACCELERATOR",
			11 => "EMARKET_PR_RUNTIMES",
			12 => "EMARKET_PR_MICROPHONE",
			13 => "EMARKET_PR_HORIZONTAL",
			14 => "EMARKET_PR_DIAGONAL",
			15 => "EMARKET_PR_DIA",
			16 => "EMARKET_PR_DISPLAY",
			17 => "EMARKET_PR_BATTERY_SIZE",
			18 => "EMARKET_PR_USB2",
			19 => "EMARKET_PR_USB3",
			20 => "EMARKET_PR_AMOUNT",
			21 => "EMARKET_PR_WEBCAM_PIXELS",
			22 => "EMARKET_PR_CORE_NUMBER",
			23 => "EMARKET_PR_CONSTRUCT",
			24 => "EMARKET_PR_FIXATION",
			25 => "EMARKET_PR_DIAGONAL_NOTE",
			26 => "EMARKET_PR_AREA",
			27 => "EMARKET_PR_MAX_ISO",
			28 => "EMARKET_PR_MAX_VIDEOS",
			29 => "EMARKET_PR_PERMIT",
			30 => "EMARKET_PR_MAX_FOCUS",
			31 => "EMARKET_PR_NOICE_LVL",
			32 => "EMARKET_PR_MATERIAL",
			33 => "EMARKET_PR_MIN_ISO",
			34 => "EMARKET_PR_MIN_FOCUS",
			35 => "EMARKET_PR_MODEL",
			36 => "EMARKET_PR_DGRAPHICS_MODEL",
			37 => "EMARKET_PR_IGRAPHICS_MODEL",
			38 => "EMARKET_PR_PROCESSOR_MODEL",
			39 => "EMARKET_PR_CHIPSET_MODEL",
			40 => "EMARKET_PR_POWER",
			41 => "EMARKET_PR_PIXELS",
			42 => "EMARKET_PR_SSD_SIZE",
			43 => "EMARKET_PR_HDD_SIZE",
			44 => "EMARKET_PR_VMEMORY_SIZE",
			45 => "EMARKET_PR_HARD",
			46 => "EMARKET_PR_MEMORY",
			47 => "EMARKET_PR_ZOOM",
			48 => "EMARKET_PR_OPTICAL_DRIVE",
			49 => "EMARKET_PR_OS",
			50 => "EMARKET_PR_MODE",
			51 => "EMARKET_PR_DATA",
			52 => "EMARKET_PR_RANGES",
			53 => "EMARKET_PR_3D",
			54 => "EMARKET_PR_HDTV",
			55 => "EMARKET_PR_CLARITY",
			56 => "EMARKET_PR_CARD_READER",
			57 => "EMARKET_PR_FLOOR_SCREEN",
			58 => "EMARKET_PR_SOFTWARE",
			59 => "EMARKET_PR_GPU_POWER",
			60 => "EMARKET_PR_PROCESSOR_POWER",
			61 => "EMARKET_PR_RAM_SIZE",
			62 => "EMARKET_PR_SCREEN_SIZE",
			63 => "EMARKET_PR_SPEAKER_SYSTEM",
			64 => "EMARKET_PR_SCREEN_TOUCH",
			65 => "EMARKET_PR_COMPLIANT",
			66 => "EMARKET_PR_STABILIZER",
			67 => "EMARKET_PR_MATRIX",
			68 => "EMARKET_PR_TYPE",
			69 => "EMARKET_PR_BATTERY_TYPE",
			70 => "EMARKET_PR_VMEMORY_TYPE",
			71 => "EMARKET_PR_FLASH",
			72 => "EMARKET_PR_ARRAY",
			73 => "EMARKET_PR_RAM_TYPE",
			74 => "EMARKET_PR_DEVICE_TYPE",
			75 => "EMARKET_PR_FILTER",
			76 => "EMARKET_PR_FOCUS",
			77 => "EMARKET_PR_COOLANT",
			78 => "EMARKET_SKU_COLOR",
			79 => "EMARKET_PR_FRAME_COLOR",
			80 => "EMARKET_PR_FREQUENCY",
			81 => "EMARKET_PR_RAM_FREQUENCY",
			82 => "EMARKET_PR_CPU",
			83 => "EMARKET_PR_COLOR",
			84 => "",
		),
		"COMPARE_OFFERS_FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"COMPARE_OFFERS_PROPERTY_CODE" => array(
			0 => "EMARKET_SKU_MEMORY",
			1 => "",
		),
		"COMPARE_ELEMENT_SORT_FIELD" => "sort",
		"COMPARE_ELEMENT_SORT_ORDER" => "asc",
		"DISPLAY_ELEMENT_SELECT_BOX" => "N",
		"PRICE_CODE" => array(
			0 => "BASE",
		),
		"USE_PRICE_COUNT" => "N",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "Y",
		"PRICE_VAT_SHOW_VALUE" => "N",
		"CONVERT_CURRENCY" => "N",
		"CURRENCY_ID" => "RUB",
		"BASKET_URL" => SITE_DIR."personal/basket/",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"USE_PRODUCT_QUANTITY" => "Y",
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",
		"ADD_PROPERTIES_TO_BASKET" => "Y",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"PARTIAL_PRODUCT_PROPERTIES" => "N",
		"PRODUCT_PROPERTIES" => "",
		"OFFERS_CART_PROPERTIES" => array(
			0 => "EMARKET_SKU_MEMORY",
			1 => "EMARKET_SKU_COLOR",
		),
		"SHOW_TOP_ELEMENTS" => "N",
		"SECTION_COUNT_ELEMENTS" => "Y",
		"SECTION_TOP_DEPTH" => "2",
		"SECTIONS_VIEW_MODE" => "TILE",
		"SECTIONS_SHOW_PARENT_NAME" => "Y",
		"SECTIONS_HIDE_SECTION_NAME" => "N",
		"PAGE_ELEMENT_COUNT" => "",
		"LINE_ELEMENT_COUNT" => "4",
		"ELEMENT_SORT_FIELD" => "PROPERTY_EMARKET_BRAND",
		"ELEMENT_SORT_ORDER" => "asc",
		"ELEMENT_SORT_FIELD2" => "",
		"ELEMENT_SORT_ORDER2" => "",
		"LIST_PROPERTY_CODE" => array(
			0 => "EMARKET_BRAND",
			1 => "",
		),
		"INCLUDE_SUBSECTIONS" => "N",
		"LIST_META_KEYWORDS" => "-",
		"LIST_META_DESCRIPTION" => "-",
		"LIST_BROWSER_TITLE" => "-",
		"LIST_OFFERS_FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"LIST_OFFERS_PROPERTY_CODE" => array(
			0 => "EMARKET_SKU_LENS",
			1 => "EMARKET_SKU_LENSS",
			2 => "EMARKET_SKU_MEMORY",
			3 => "EMARKET_SKU_COLOR",
			4 => "",
		),
		"LIST_OFFERS_LIMIT" => "10",
		"DETAIL_PROPERTY_CODE" => array(
			0 => "EMARKET_ARTICLE",
			1 => "",
		),
		"DETAIL_META_KEYWORDS" => "-",
		"DETAIL_META_DESCRIPTION" => "-",
		"DETAIL_BROWSER_TITLE" => "-",
		"DETAIL_OFFERS_FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"DETAIL_OFFERS_PROPERTY_CODE" => array(
			0 => "EMARKET_ARTICLE",
			1 => "EMARKET_SKU_LENS",
			2 => "EMARKET_SKU_LENSS",
			3 => "EMARKET_SKU_MEMORY",
			4 => "EMARKET_SKU_PHOTO",
			5 => "EMARKET_SKU_COLOR",
			6 => "EMARKET_SKU_ARTICLE",
			7 => "",
		),
		"GROUPS_OF_PROP_IBLOCK_TYPE" => "catalog",
		"GROUPS_OF_PROP_IBLOCK_ID" => "6",
		"DETAIL_DISPLAY_NAME" => "Y",
		"DETAIL_DETAIL_PICTURE_MODE" => "IMG",
		"DETAIL_ADD_DETAIL_TO_SLIDER" => "Y",
		"DETAIL_DISPLAY_PREVIEW_TEXT_MODE" => "S",
		"LINK_IBLOCK_TYPE" => "offers",
		"LINK_IBLOCK_ID" => "8",
		"LINK_PROPERTY_SID" => "CML2_LINK",
		"LINK_ELEMENTS_URL" => "/link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#",
		"USE_ALSO_BUY" => "N",
		"ALSO_BUY_ELEMENT_COUNT" => "5",
		"ALSO_BUY_MIN_BUYES" => "1",
		"USE_STORE" => "N",
		"OFFERS_SORT_FIELD" => "sort",
		"OFFERS_SORT_ORDER" => "asc",
		"OFFERS_SORT_FIELD2" => "id",
		"OFFERS_SORT_ORDER2" => "desc",
		"PAGER_TEMPLATE" => "modern",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"PAGER_TITLE" => "Товары",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "Y",
		"TEMPLATE_THEME" => "red",
		"ADD_PICT_PROP" => "EMARKET_PHOTO",
		"LABEL_PROP" => "-",
		"PRODUCT_DISPLAY_MODE" => "Y",
		"OFFER_ADD_PICT_PROP" => "EMARKET_SKU_PHOTO",
		"OFFER_TREE_PROPS" => array(
			0 => "EMARKET_SKU_COLOR",
			1 => "EMARKET_SKU_MEMORY",
			2 => "EMARKET_SKU_LENS",
			3 => "EMARKET_SKU_LENSS",
		),
		"SHOW_DISCOUNT_PERCENT" => "Y",
		"SHOW_OLD_PRICE" => "Y",
		"DETAIL_SHOW_MAX_QUANTITY" => "N",
		"MESS_BTN_BUY" => "Добавить в корзину",
		"MESS_BTN_ADD_TO_BASKET" => "В корзину",
		"MESS_BTN_COMPARE" => "Сравнение",
		"MESS_BTN_DETAIL" => "Подробнее",
		"MESS_NOT_AVAILABLE" => "Нет в наличии",
		"DETAIL_USE_VOTE_RATING" => "N",
		"DETAIL_USE_COMMENTS" => "Y",
		"DETAIL_BLOG_USE" => "Y",
		"DETAIL_BLOG_HLBLOCK_PROP_CODE" => "EMARKET_COMMENTS",
		"DETAIL_BLOG_HLBLOCK_CR_PROP_CODE" => "EMARKET_CRITERIA",
		"DETAIL_VK_USE" => "N",
		"DETAIL_FB_USE" => "N",
		"DETAIL_BRAND_USE" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"DETAIL_BLOG_URL" => "catalog_comments",
		"ELEMENT_SORT_FIELD_BOX" => "name",
		"ELEMENT_SORT_ORDER_BOX" => "asc",
		"ELEMENT_SORT_FIELD_BOX2" => "id",
		"ELEMENT_SORT_ORDER_BOX2" => "desc",
		"DETAIL_CHECK_SECTION_ID_VARIABLE" => "N",
		"COMPONENT_TEMPLATE" => "catalog-template",
		"HIDE_NOT_AVAILABLE_OFFERS" => "N",
		"USER_CONSENT" => "N",
		"USER_CONSENT_ID" => "0",
		"USER_CONSENT_IS_CHECKED" => "Y",
		"USER_CONSENT_IS_LOADED" => "N",
		"USE_MAIN_ELEMENT_SECTION" => "N",
		"DETAIL_STRICT_SECTION_CHECK" => "N",
		"SET_LAST_MODIFIED" => "N",
		"SECTION_BACKGROUND_IMAGE" => "-",
		"DETAIL_SET_CANONICAL_URL" => "N",
		"DETAIL_BACKGROUND_IMAGE" => "-",
		"SHOW_DEACTIVATED" => "N",
		"USE_GIFTS_DETAIL" => "Y",
		"USE_GIFTS_SECTION" => "Y",
		"USE_GIFTS_MAIN_PR_SECTION_LIST" => "Y",
		"GIFTS_DETAIL_PAGE_ELEMENT_COUNT" => "4",
		"GIFTS_DETAIL_HIDE_BLOCK_TITLE" => "N",
		"GIFTS_DETAIL_BLOCK_TITLE" => "Выберите один из подарков",
		"GIFTS_DETAIL_TEXT_LABEL_GIFT" => "Подарок",
		"GIFTS_SECTION_LIST_PAGE_ELEMENT_COUNT" => "4",
		"GIFTS_SECTION_LIST_HIDE_BLOCK_TITLE" => "N",
		"GIFTS_SECTION_LIST_BLOCK_TITLE" => "Подарки к товарам этого раздела",
		"GIFTS_SECTION_LIST_TEXT_LABEL_GIFT" => "Подарок",
		"GIFTS_SHOW_DISCOUNT_PERCENT" => "Y",
		"GIFTS_SHOW_OLD_PRICE" => "Y",
		"GIFTS_SHOW_NAME" => "Y",
		"GIFTS_SHOW_IMAGE" => "Y",
		"GIFTS_MESS_BTN_BUY" => "Выбрать",
		"GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT" => "4",
		"GIFTS_MAIN_PRODUCT_DETAIL_HIDE_BLOCK_TITLE" => "N",
		"GIFTS_MAIN_PRODUCT_DETAIL_BLOCK_TITLE" => "Выберите один из товаров, чтобы получить подарок",
		"PAGER_BASE_LINK_ENABLE" => "N",
		"SHOW_404" => "N",
		"MESSAGE_404" => "",
		"COMPATIBLE_MODE" => "Y",
		"DISABLE_INIT_JS_IN_COMPONENT" => "N",
		"DETAIL_SET_VIEWED_IN_COMPONENT" => "N",
		"SEF_URL_TEMPLATES" => array(
			"sections" => "",
			"section" => "#SECTION_CODE_PATH#/",
			"element" => "#SECTION_CODE_PATH#/#ELEMENT_ID#/",
			"compare" => "compare.php?action=#ACTION_CODE#",
			"smart_filter" => "#SECTION_CODE_PATH#/filter/#SMART_FILTER_PATH#/apply/",
		),
		"VARIABLE_ALIASES" => array(
			"compare" => array(
				"ACTION_CODE" => "action",
			),
		)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
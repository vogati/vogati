<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
return array(
    'nodes' =>
        array(
            '.smone_title' =>
                array(
                    'name' => 'Текст',
                    'type' => 'text',
                ),
            '.smone_social_link' =>
                array(
                    'name' => '',
                    'type' => 'link',
                ),
            '.smone_logo' =>
                array(
                    'name' => 'Ссылка',
                    'type' => 'link',
                ),
            '.smone_social_link_img' =>
                array(
                    'name' => 'Иконка',
                    'type' => 'img',
                ),
            '.s_fxg6miqlzu' =>
                array(
                    'name' => 'Логотип',
                    'type' => 'img',
                ),
            '.smone_catalog_btn' =>
                array(
                    'name' => 'Каталог',
                    'type' => 'link',
                ),
            '.smone_link_item' =>
                array(
                    'name' => 'Ссылка',
                    'type' => 'link',
                ),
            '.smone_contacts_href' =>
                array(
                    'name' => 'Контакт',
                    'type' => 'link',
                ),
        ),
    'style' =>
        array(
            'block' =>
                array(),
            'nodes' =>
                array(
                    '.smone_top_wrp.no-gutters' =>
                        array(
                            'name' => 'Фон',
                            'type' => 'box',
                        ),
                    '.smone_title' =>
                        array(
                            'name' => 'Текст',
                            'type' => 'typo',
                        ),
                    '.smone_line' =>
                        array(
                            'name' => 'Разделительная линия',
                            'type' => 'box',
                        ),
                    '.smone_bottom_wrp.no-gutters' =>
                        array(
                            'name' => 'Фон',
                            'type' => 'box',
                        ),
                    '.smone_catalog_btn' =>
                        array(
                            'name' => 'Каталог',
                            'type' => 'button',
                        ),
                    '.smone_link_item' =>
                        array(
                            'name' => 'Ссылка',
                            'type' => 'typo',
                        ),
                    '.smone_contacts_href' =>
                        array(
                            'name' => 'Контакт',
                            'type' => 'typo',
                        ),
                ),
        ),
    'cards' =>
        array(
            '.iconcard' =>
                array(
                    'name' => 'Соц. сеть',
                ),
            '.linkcard' =>
                array(
                    'name' => 'Ссылка',
                ),
        ),
    'assets' =>
        array(
            'css' =>
                array(
                    0 => 'https://bitrix24.market/upload/iblock/9d2/9d2013c55ec40fd394d7225b9f96c677.css',
                ),
            'js' =>
                array(
                    0 => 'https://bitrix24.market/upload/iblock/175/175fc353ae815095b4c4dc43f629f1c7.js',
                ),
        ),
    'attrs' =>
        array(
            '.smone_link_wrp' =>
                array(
                    'name' => 'Цвет рамки вокруг ссылок',
                    'type' => 'text',
                    'attribute' => 'color',
                ),
        ),
    'block' =>
        array(
            'name' => 'Меню для сайта с логотипом, ссылками на соц. сети, контактными данными, ссылками на внутренние страницы и возможностью добавлять элементы',
            'section' =>
                array(
                    0 => 'menu',
                ),
        ),
);
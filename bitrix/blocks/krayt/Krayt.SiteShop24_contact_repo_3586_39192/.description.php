<?php
    if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    {
        die();
    }
    return array (
  'cards' => 
  array (
    '.landing-block-card-contact' => 
    array (
      'name' => 'Контакт',
      'label' => 
      array (
        0 => '.landing-block-card-contact-title',
      ),
    ),
  ),
  'nodes' => 
  array (
    '.landing-block-node-main-title' => 
    array (
      'name' => 'Заголовок',
      'type' => 'text',
    ),
    '.landing-block-node-text' => 
    array (
      'name' => 'Текст',
      'type' => 'text',
    ),
    '.landing-block-card-contact-icon' => 
    array (
      'name' => 'Иконка',
      'type' => 'icon',
    ),
    '.landing-block-card-contact-title' => 
    array (
      'name' => 'Заголовок',
      'type' => 'text',
    ),
    '.landing-block-card-contact-text' => 
    array (
      'name' => 'Текст',
      'type' => 'text',
    ),
    '.landing-block-card-contact-link' => 
    array (
      'name' => 'Ссылка',
      'type' => 'link',
    ),
  ),
  'style' => 
  array (
    'block' => 
    array (
    ),
    'nodes' => 
    array (
      'nodes' => 
      array (
        '.landing-block-node-main-title' => 
        array (
          'name' => 'Заголовок',
          'type' => 'typo',
        ),
        '.landing-block-node-text' => 
        array (
          'name' => 'Текст',
          'type' => 'typo',
        ),
        '.landing-block-card-contact-title' => 
        array (
          'name' => 'Заголовок',
          'type' => 'typo',
        ),
        '.landing-block-card-contact-text' => 
        array (
          'name' => 'Текст',
          'type' => 'typo',
        ),
        '.landing-block-card-contact-icon-container' => 
        array (
          'name' => 'Иконка',
          'type' => 'color',
        ),
        '.landing-block-card-contact-link' => 
        array (
          'name' => 'Ссылка',
          'type' => 'typo',
        ),
        '.landing-block-card-contact' => 
        array (
          'name' => 'Контакт',
          'type' => 'animation',
        ),
      ),
    ),
  ),
  'assets' => 
  array (
    'ext' => 
    array (
      0 => 'landing_form',
    ),
  ),
  'attrs' => 
  array (
    '.bitrix24forms' => 
    array (
      0 => 
      array (
        'attribute' => 'data-b24form-original-domain',
        'hidden' => '1',
      ),
      1 => 
      array (
        'name' => 'CRM-форма',
        'attribute' => 'data-b24form',
        'items' => 
        array (
          0 => 
          array (
            'name' => 'Обратный звонок. Битрикс24.Сайты',
            'value' => '16|cgtw6k',
          ),
          1 => 
          array (
            'name' => 'Обратный звонок. Крайт',
            'value' => '18|a2aff0',
          ),
          2 => 
          array (
            'name' => 'Обратный звонок. MarketPlace',
            'value' => '10|d7ng3k',
          ),
        ),
        'type' => 'list',
      ),
      2 => 
      array (
        'name' => 'Заголовок формы',
        'attribute' => 'data-b24form-show-header',
        'type' => 'list',
        'items' => 
        array (
          0 => 
          array (
            'name' => 'Показать',
            'value' => 'Y',
          ),
          1 => 
          array (
            'name' => 'Скрыть',
            'value' => 'N',
          ),
        ),
      ),
      3 => 
      array (
        'name' => 'Дизайн формы',
        'attribute' => 'data-b24form-use-style',
        'type' => 'list',
        'items' => 
        array (
          0 => 
          array (
            'name' => 'Использовать дизайн блока',
            'value' => 'Y',
          ),
          1 => 
          array (
            'name' => 'Использовать дизайн CRM-формы',
            'value' => 'N',
          ),
        ),
      ),
    ),
  ),
  'namespace' => 'bitrix',
  'code' => 'repo_3586',
  'block' => 
  array (
    'name' => 'Форма на светлом фоне с текстом справа',
    'section' => 
    array (
      0 => 'other',
    ),
  ),
);
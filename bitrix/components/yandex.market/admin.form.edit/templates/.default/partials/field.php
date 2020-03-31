<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

use Yandex\Market;

/** @var $field array */

$rowAttributes = [];
$rowAttributesString = '';

if (isset($field['DEPEND']))
{
	$this->addExternalJs('/bitrix/js/yandex.market/ui/input/dependfield.js');

	$rowAttributes['class'] = 'js-plugin';
	$rowAttributes['data-plugin'] = 'Ui.Input.DependField';
	$rowAttributes['data-depend'] = Market\Utils::jsonEncode($field['DEPEND'], JSON_UNESCAPED_UNICODE);

	if ($field['DEPEND_HIDDEN'])
	{
		$rowAttributes['class'] .= ' is--hidden';
	}
}

foreach ($rowAttributes as $attribute => $attributeValue)
{
	$rowAttributesString .= ' ' . $attribute  . '="' . Market\Utils::htmlEscape($attributeValue) .  '"';
}

?>
<tr <?= $rowAttributesString; ?>>
	<td class="adm-detail-content-cell-l" width="40%" align="right" valign="top">
		<?
		include __DIR__ . '/field-title.php';
		?>
	</td>
	<td class="adm-detail-content-cell-r" width="60%"><?= $component->getFieldHtml($field); ?></td>
</tr>

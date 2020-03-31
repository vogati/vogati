<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

/** @var $component Yandex\Market\Components\AdminFormEdit */

if (!empty($tab['HIDDEN']))
{
	?>
	<tr>
		<td class="b-form-hidden-row" colspan="2">
			<?
			foreach ($tab['HIDDEN'] as $fieldKey)
			{
				$field = $component->getField($fieldKey);
				$fieldValue = $component->getFieldValue($field);

				?>
				<input type="hidden" name="<?= $field['FIELD_NAME']; ?>" value="<?= htmlspecialcharsbx($fieldValue); ?>" />
				<?
			}
			?>
		</td>
	</tr>
	<?
}
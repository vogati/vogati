<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

use Yandex\Market;

/** @var $component Yandex\Market\Components\AdminFormEdit */
/** @var $specialFields array */

$this->addExternalJs('/bitrix/js/yandex.market/ui/input/dependlist.js');

$serviceList = Market\Export\Xml\Format\Manager::getServiceList();
$typeList = [];
$selectedService = null;
$availableTypeCount = 0;

foreach ($serviceList as $service)
{
	// selected

	if ($selectedService === null || $service === $arResult['ITEM']['EXPORT_SERVICE'])
	{
		$selectedService = $service;
	}

	// types

	$serviceTypeList = Market\Export\Xml\Format\Manager::getTypeList($service);

	foreach ($serviceTypeList as $type)
	{
		if (!isset($typeList[$type]))
		{
			$typeList[$type] = [];
		}

		$typeList[$type][$service] = true;
	}

	if ($selectedService === $service)
	{
		$availableTypeCount = count($serviceTypeList);
	}
}

foreach ($specialFields as $specialFieldKey)
{
	$field = $component->getField($specialFieldKey);

	if ($field)
	{
		?>
		<tr class="<?= $specialFieldKey === 'EXPORT_FORMAT' && $availableTypeCount <= 1 ? 'is--hidden' : ''; ?> js-form-field">
			<td width="40%" align="right" valign="top">
				<?
				include __DIR__ . '/field-title.php';
				?>
			</td>
			<td width="60%"><?

				switch ($specialFieldKey)
				{
					case 'EXPORT_SERVICE':
						?>
						<select name="<?= $field['FIELD_NAME']; ?>" id="FIELD_EXPORT_SERVICE">
							<?
							foreach ($serviceList as $service)
							{
								$isSelected = ($service === $selectedService);

								?>
								<option value="<?= $service; ?>" <?= $isSelected ? 'selected' : ''; ?>><?= Market\Export\Xml\Format\Manager::getServiceTitle($service) ?: $service; ?></option>
								<?
							}
							?>
						</select>
						<?
					break;

					case 'EXPORT_FORMAT':
						?>
						<select class="b-depend-select js-plugin" name="<?= $field['FIELD_NAME']; ?>" data-plugin="Ui.Input.DependList" data-depend-element="#FIELD_EXPORT_SERVICE">
							<?
							foreach ($typeList as $type => $availableServices)
							{
								$isSelected = ($type === $arResult['ITEM']['EXPORT_FORMAT']);
								$isAvailable = isset($availableServices[$selectedService]);
								$availableServicesList = array_keys($availableServices);

								?>
								<option value="<?= $type; ?>" <?= $isSelected ? 'selected' : ''; ?> <?= $isAvailable ? '' : 'disabled hidden'; ?> data-available="<?= implode(',', $availableServicesList); ?>">
									<?= Market\Export\Xml\Format\Manager::getTypeTitle($type); ?>
								</option>
								<?
							}
							?>
						</select>
						<?
					break;
				}
			?></td>
		</tr>
		<?
	}
}

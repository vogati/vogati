<?php

use Sprint\Migration\Locale;

$search = '';
$listview = '';

$getOnclickMenu = function ()  {
    $menu = [];
    $menu[] = [
        'TEXT' => Locale::getMessage('UP_START_WITH_TAG'),
        'ONCLICK' => 'migrationMigrationsUpWithTag()',
    ];
    $menu[] = [
        'TEXT' => Locale::getMessage('DOWN_START'),
        'ONCLICK' => 'migrationMigrationsDownConfirm()',
    ];
    return CUtil::PhpToJSObject($menu);
}
?>
<div id="migration-container" data-sessid="<?= bitrix_sessid() ?>">
    <div class="sp-group">
        <div class="sp-group-row2">
            <div class="sp-block sp-block-scroll sp-white">
                <div id="migration_migrations" class="sp-scroll"></div>
            </div>
            <div class="sp-block sp-block-scroll">
                <div id="migration_progress" class="sp-scroll"></div>
            </div>
        </div>
    </div>
    <div class="sp-group">
        <div class="sp-group-row2">
            <div class="sp-block">
                <input type="button"
                       value="<?= Locale::getMessage('UP_START') ?>"
                       onclick="migrationMigrationsUpConfirm();"
                       class="adm-btn-green"/>
                <a onclick="this.blur();BX.adminShowMenu(this, <?= $getOnclickMenu() ?>, {active_class: 'adm-btn-active',public_frame: '0'}); return false;"
                   href="javascript:void(0)"
                   class="adm-btn"
                   hidefocus="true">&equiv;</a>
            </div>
            <div class="sp-block">
                <input placeholder="<?= Locale::getMessage('SEARCH') ?>"
                       style=""
                       type="text"
                       value="<?= $search ?>"
                       class="adm-input"
                       name="migration_search"/>
                <select name="migration_filter">
                    <option <? if ($listview == 'migration_view_all'): ?>selected="selected"<? endif ?>
                            value="migration_view_all"><?= Locale::getMessage('TOGGLE_LIST') ?></option>
                    <option <? if ($listview == 'migration_view_new'): ?>selected="selected"<? endif ?>
                            value="migration_view_new"><?= Locale::getMessage('TOGGLE_NEW') ?></option>
                    <option <? if ($listview == 'migration_view_installed'): ?>selected="selected"<? endif ?>
                            value="migration_view_installed"><?= Locale::getMessage('TOGGLE_INSTALLED') ?></option>
                    <option <? if ($listview == 'migration_view_tag'): ?>selected="selected"<? endif ?>
                            value="migration_view_tag"><?= Locale::getMessage('TOGGLE_TAG') ?></option>
                    <option <? if ($listview == 'migration_view_modified'): ?>selected="selected"<? endif ?>
                            value="migration_view_modified"><?= Locale::getMessage('TOGGLE_MODIFIED') ?></option>
                    <option <? if ($listview == 'migration_view_older'): ?>selected="selected"<? endif ?>
                            value="migration_view_older"><?= Locale::getMessage('TOGGLE_OLDER') ?></option>
                    <option <? if ($listview == 'migration_view_status'): ?>selected="selected"<? endif ?>
                            value="migration_view_status"><?= Locale::getMessage('TOGGLE_STATUS') ?></option>
                </select>
                <input type="button" value="<?= Locale::getMessage('SEARCH') ?>" class="sp-search"/>

            </div>
        </div>
    </div>
    <div class="sp-separator"></div>
    <? foreach (['default', 'configurator'] as $builderGroup): ?>
        <? include __DIR__ . '/builder_group.php' ?>
    <? endforeach ?>

    <div class="sp-separator"></div>
</div>

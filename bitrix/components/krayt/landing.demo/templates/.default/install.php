<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}
$APPLICATION->setTitle(GetMessage('k_step_install_title_page'));
use \Bitrix\Landing\Manager;
use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ModuleManager;
Loc::loadMessages(__FILE__);

if ($arResult['ERRORS'])
{
    foreach ($arResult['ERRORS'] as $code => $error)
    {
        echo '<p style="color: red;">' . $error . '</p>';
    }
}
?>
<div class="install-site">
    <?if($arResult['is_add_site']):?>
        <h3><?=GetMessage('k_step_install_title_ok')?></h3>
        <small>
            <?=GetMessage('k_step_install_title_ok_text')?>
        </small>
    <?else:?>
    <div class="form-wrp">
        <form name="install_site" method="post" action="<?=$arResult['CURL']?>">
            <input type="hidden" name="site_code" value="<?=$arResult['INSTALL']['ID']?>">
            <h3><?=GetMessage('k_step_install_title')?> "<?=$arResult['INSTALL']['TITLE']?>"</h3>
            <?if($arResult['SITES']):?>
                <div class="install-item">
                    <label for="site_id"><?=GetMessage('k_step_select_site')?></label>
                    <select name="site_id" id="site_id">
                        <?foreach ($arResult['SITES'] as $site):?>
                            <option value="<?=$site['ID']?>"><?=$site['TITLE']?></option>
                        <?endforeach;?>
                    </select>
                </div>
            <?else:?>
                <div class="install_site_error">
                    <b><?=GetMessage('k_step__1_no_site_error')?></b><br>
                    <span>
                        <?=GetMessage('k_step__1_no_site_desc')?>
                   </span>
                </div>
            <?endif;?>
            <div class="btns">
                <button  class="adm-btn" type="button" onclick="BX.SidePanel.Instance.close();"><?=GetMessage('k_step_install_btn_cancel')?></button>
                <button onclick="BX.showWait();" class="adm-btn adm-btn-save" name="install_btn"><?=GetMessage('k_step_install_btn_install')?></button>
            </div>
        </form>
        <div class="adm-info-message-wrap">
            <div class="adm-info-message">
                <span class="required">
                    <?=GetMessage('k_war_install_text')?>
                </span>
            </div>
        </div>
    </div>

    <?endif;?>
</div>




<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Manager;
use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ModuleManager;
Loc::loadMessages(__FILE__);

// some errors
if ($arResult['ERRORS'])
{
	foreach ($arResult['ERRORS'] as $code => $error)
	{
		echo '<p style="color: red;">' . $error . '</p>';
	}
}

// show message for license renew if need
if (empty($arResult['DEMO']))
{
	if (ModuleManager::isModuleInstalled('bitrix24'))
	{
		\showError(Loc::getMessage('LANDING_TPL_EMPTY_REPO_SERVICE'));
	}
	else
	{
		if (Manager::licenseIsValid())
		{
			\showError(Loc::getMessage('LANDING_TPL_EMPTY_REPO_SERVICE'));
		}
		else
		{
			$link = Manager::isB24()
					? 'https://www.bitrix24.ru/prices/self-hosted.php'
					: 'https://www.1c-bitrix.ru/buy/cms.php#tab-updates-link';
			?>
			<div class="landing-license-wrapper">
				<div class="landing-license-inner">
					<div class="landing-license-icon-container">
						<div class="landing-license-icon"></div>
					</div>
					<div class="landing-license-info">
						<span class="landing-license-info-text"><?= Loc::getMessage('LANDING_TPL_EMPTY_REPO_EXPIRED');?></span>
						<div class="landing-license-info-btn">
							<?= Loc::getMessage('LANDING_TPL_EMPTY_REPO_EXPIRED_LINK', array(
								'#LINK1#' => '<a href="' . $link . '" target="_blank" class="landing-license-info-link">',
								'#LINK2#' => '</a>'
							));?>
						</div>
					</div>
				</div>
			</div>
			<?
		}
	}
}

// exit on fatal
if ($arResult['FATAL'])
{
	return;
}

// some vars
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$curUrl = $request->getRequestUri();

// title
$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '') . 'no-all-paddings no-background');
$APPLICATION->setTitle(Loc::getMessage('LANDING_TPL_TITLE'));

// additional assets
\CJSCore::Init(array('popup', 'action_dialog', 'loader', 'sidepanel'));
Asset::getInstance()->addCSS('/bitrix/components/bitrix/landing.sites/templates/.default/style.css');
Asset::getInstance()->addJS('/bitrix/components/bitrix/landing.sites/templates/.default/script.js');
?>

<div class="grid-tile-wrap" id="grid-tile-wrap">
	<div class="grid-tile-inner" id="grid-tile-inner">

<?foreach ($arResult['DEMO'] as $item):
	if ($item['HIDE'])
	{
		continue;
	}
	$uriSelect = new \Bitrix\Main\Web\Uri($curUrl);
	$uriSelect->addParams(array(
		'tpl' => $item['ID'],
        'k_type' => $item['K_TYPE'],
        'action' => 'install'
	));
	?>
     <?if ($item['AVAILABLE']):?>
	<span data-href="<?= $uriSelect->getUri();?>" class="landing-template-pseudo-link landing-item landing-item-hover<?= $arResult['LIMIT_REACHED'] ? ' landing-item-payment' : '';?>">
	<?else:?>
	<span class="landing-item landing-item-hover landing-item-disabled">
	<?endif;?>
		<span class="landing-item-inner">
			<div class="landing-title">
				<div class="landing-title-wrap">
					<div class="landing-title-overflow"><?= \htmlspecialcharsbx($item['TITLE'])?></div>
				</div>
                <?if(false):?>
                    <?if($item['PRICE']):?>
                        <span class="price"><?=$item['PRICE']?></span>
                    <?else:?>
                        <span class="free"><?=Loc::getMessage('KRAYT_TPL_FREE')?></span>
                    <?endif;?>
                <?endif;?>
			</div>
			<?if (trim($item['DESCRIPTION'])):?>
				<span class="landing-item-cover landing-item-cover-short">
					<?if ($item['PREVIEW']):?>
						<img class="landing-item-cover-img"
							 src="<?= \htmlspecialcharsbx($item['PREVIEW'])?>"
							 srcset="<?= \htmlspecialcharsbx($item['PREVIEW'])?> 2x,
									<?= \htmlspecialcharsbx($item['PREVIEW'])?> 3x">
					<?endif;?>
				</span>
				<span class="landing-item-description">
					<span class="landing-item-desc-inner">
						<span class="landing-item-desc-overflow">
							<span class="landing-item-desc-height">
								<?= \htmlspecialcharsbx($item['DESCRIPTION'])?>
							</span>
						</span>
						<span class="landing-item-desc-open"></span>
					</span>
				</span>
			<?else:?>
				<span class="landing-item-cover landing-item-cover-short">
					<?if ($item['PREVIEW']):?>
						<img class="landing-item-cover-img"
							 src="<?= \htmlspecialcharsbx($item['PREVIEW'])?>"
							 srcset="<?= \htmlspecialcharsbx($item['PREVIEW'])?> 2x,
									<?= \htmlspecialcharsbx($item['PREVIEW'])?> 3x">
					<?endif;?>
				</span>
			<?endif?>
            <span class="landing-item-description">
					<span class="landing-item-desc-inner">
						<button class="adm-btn demo-open-page btn" data-href="<?=$item['URL_PREVIEW']?>"><?=Loc::getMessage('KRAYT_TPL_DEMO')?></button>
                        <?if($item['PRICE']):?>
                            <button class="btn">Купить</button>
                        <?else:?>
                            <button data-href="<?= $uriSelect->getUri();?>" class="adm-btn adm-btn-save install-open-page"><?=Loc::getMessage('KRAYT_TPL_BTN_INSTALL')?></button>
                        <?endif;?>

					</span>
				</span>
		</span>
	<?if (!$item['AVAILABLE']):?>
	</span>
	<?else:?>
	</span>
	<?endif;?>
<?endforeach;?>

	</div>
</div>

<script type="text/javascript">
	BX.ready(function ()
	{
		var items = [].slice.call(document.querySelectorAll('.demo-open-page'));

		items.forEach(function(item) {
            BX.bind(item, 'click', function(event) {

               window.open(event.currentTarget.dataset.href);
            });
        });
        var items = [].slice.call(document.querySelectorAll('.install-open-page'));

        items.forEach(function(item) {
            BX.bind(item, 'click', function(event) {

                BX.SidePanel.Instance.open(event.currentTarget.dataset.href+"&"+Date.now(), {
                    allowChangeHistory: false
                });
            });
        });
		var wrapper = BX('grid-tile-wrap');
		var tiles = Array.prototype.slice.call(wrapper.getElementsByClassName('landing-item'));
		new BX.Landing.Component.Demo({
			wrapper : wrapper,
			inner: BX('grid-tile-inner'),
			tiles : tiles
		});
		<?if ($arResult['LIMIT_REACHED']):?>
		if (typeof BX.Landing.PaymentAlert !== 'undefined')
		{
			BX.Landing.PaymentAlert({
				nodes: wrapper.querySelectorAll('.landing-item-payment'),
				title: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_LIMIT_REACHED_TITLE'));?>',
				message: '<?= ($arParams['SITE_ID'] > 0)
					? \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_PAGE_LIMIT_REACHED_TEXT'))
					: \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_SITE_LIMIT_REACHED_TEXT'));
					?>'
			});
		}
		<?endif;?>
	})
</script>
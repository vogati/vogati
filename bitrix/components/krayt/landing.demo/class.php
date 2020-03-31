<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}

use \Bitrix\Landing\Site;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Domain;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Syspage;
use \Bitrix\Landing\Template;
use \Bitrix\Landing\TemplateRef;
use \Bitrix\Landing\Hook\Page\Settings;
use \Bitrix\Highloadblock;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Web\HttpClient;
use \Bitrix\Main\Web\Json;
use \Bitrix\Main\Loader;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingSiteDemoComponent extends LandingBaseComponent
{
    /**
     * Tag for managed cache.
     */
    const DEMO_TAG = 'landing_demo';

    /**
     * Path with demo templates data for site.
     */
    const DEMO_DIR_SITE = 'site';

    /**
     * Path with demo templates data for page.
     */
    const DEMO_DIR_PAGE = 'page';

    /**
     * Relative url for new site.
     * @var string
     */
    protected $urlTpl = '/#rand#/';

    /**
     * Redirect to the landing.
     * @param int $landingId Landing id.
     * @return boolean If error.
     */
    protected function redirectToLanding($landingId)
    {
        $landing = Landing::createInstance($landingId);
        if ($landing->exist())
        {
            $siteId = $landing->getSiteId();
            $redirect = str_replace(
                array('#site_show#', '#landing_edit#'),
                array($siteId, $landingId),
                $this->arParams['PAGE_URL_LANDING_VIEW']
            );
            $redirect .= (strpos($redirect, '?') === false ? '?' : '&')
                . 'IFRAME=N';
            \localRedirect($redirect, true);
        }
        else
        {
            $this->setErrors($landing->getError()->getErrors());
            return false;
        }
        return true;
    }

    /**
     * Prepare array of additional data.
     * @param array $data Data item array.
     * @return array
     */
    protected function prepareAdditionalFields($data)
    {
        if (
            !isset($data['ADDITIONAL_FIELDS']) ||
            !is_array($data['ADDITIONAL_FIELDS'])
        )
        {
            $data['ADDITIONAL_FIELDS'] = array();
        }
        if ($this->request('theme'))
        {
            $data['ADDITIONAL_FIELDS']['THEME_CODE'] = $this->request('theme');
        }
        return $data;
    }

    /**
     * Create one page in site.
     * @param int $siteId Site id.
     * @param string $code Page code.
     * @return boolean|int Id of new landing.
     */

    protected function addPage($siteId, $page)
    {
        if ($page)
        {
            $pageData = $page['fields'];
            $pageData = $this->prepareAdditionalFields($pageData);
            $pageData['SITE_ID'] = $siteId;
            $pageData['TITLE'] = $page['name'];
            $pageData['PUBLIC'] = 'N';
            $pageData['XML_ID'] = $page['name'] . '|' . $page['code'];

            Landing::setEditMode();
            $res = Landing::add($pageData);
            // and fill each page with blocks
            //#landing
            if ($res->isSuccess())
            {
                return $res->getId();
            }
        }
    }
    protected function createPage($landingId, $page)
    {
        if ($page && $landingId)
        {
            Landing::setEditMode();
            // and fill each page with blocks
            if ($landingId)
            {
                $landing = Landing::createInstance($landingId);
                if ($landing->exist())
                {

                    $sort = 0;
                    $blocks = array();
                    $blocksCodes = array();
                    $encoded = isset($page['encoded']) && $page['encoded'];
                    foreach ($page['items'] as $k => $block)
                    {
                        if (is_array($block))
                        {
                            if ($page['version'] == 2)
                            {
                                if (!isset($block['code']))
                                {
                                    continue;
                                }
                                $blocksCodes[$k] = $block['code'];
                                if (SITE_CHARSET == 'cp1251')
                                {
                                    $page['items'][$k] = \Bitrix\Main\Text\Encoding::convertEncoding(
                                        $page['items'][$k],
                                        'utf-8',
                                        SITE_CHARSET
                                    );
                                }
                                $blockId = $landing->addBlock(
                                    $block['code'],
                                    array(
                                        'PUBLIC' => 'N',
                                        'SORT' => $sort
                                    )
                                );
                                $sort += 500;
                                $blocks[$blockId] = $k;
                            }
                            else
                            {
                                if ((SITE_CHARSET == 'cp1251')&& isset($block['CONTENT']))
                                {
                                    $block['CONTENT'] = \Bitrix\Main\Text\Encoding::convertEncoding(
                                        $block['CONTENT'],
                                        'utf-8',
                                        SITE_CHARSET
                                    );
                                }
                                $block['PUBLIC'] = 'N';
                                $blockId = $landing->addBlock(
                                    isset($block['CODE']) ? $block['CODE'] : $k,
                                    $block
                                );
                                $blocks[$k] = $blockId;
                            }
                        }
                        else
                        {
                            $blockId = $landing->addBlock($block, array(
                                'PUBLIC' => 'N',
                            ));
                            $blocks[$block] = $blockId;
                        }
                    }
                    // redefine content of blocks
                    foreach ($landing->getBlocks() as $k => $block) {
                        if (!$block->getManifest()) {
                            continue;
                        }
                        $updated = false;
                        if ($page['version'] == 2) {
                            if (isset($page['items'][$blocks[$k]])) {
                                $newData = $page['items'][$blocks[$k]];
                                // adjust cards
                                if (isset($newData['cards']) && is_array($newData['cards']) && !empty($newData['cards'])) {
                                    foreach ($newData['cards'] as $selector => $count) {
                                        $changed = false;
                                        $block->adjustCards($selector, $count, $changed);
                                        if ($changed) {
                                            $updated = true;
                                        }
                                    }
                                }
                                // update style
                                if (isset($newData['style']) && is_array($newData['style']) && !empty($newData['style'])) {
                                    foreach ($newData['style'] as $selector => $classes) {
                                        if ($selector == '#wrapper') {
                                            $selector = '#' . $block->getAnchor($block->getId());
                                        }
                                        $updated = true;
                                        $block->setClasses(array(
                                            $selector => array(
                                                'classList' => $classes
                                            )
                                        ));
                                    }
                                }
                                // update nodes
                                if (isset($newData['nodes']) && !empty($newData['nodes'])) {
                                    $updated = true;
                                    $block->updateNodes($newData['nodes']);
                                }
                                // update attrs
                                if (isset($newData['attrs']) && !empty($newData['attrs'])) {
                                    $updated = true;
                                    $block->setAttributes($newData['attrs']);
                                }
                            }
                        }
                        // replace links and some content
                        $content = $block->getContent();
                        foreach ($blocks as $blockCode => $blockId) {
                            if ($page['version'] == 2) {
                                $count = 0;
                                $content = str_replace(
                                    '@block[' . $blocksCodes[$blockId] . ']',
                                    $blockCode,
                                    $content,
                                    $count
                                );
                                if ($count) {
                                    $updated = true;
                                }
                            } else {
                                $count = 0;
                                $content = str_replace(
                                    '@block[' . $blockCode . ']',
                                    $blockId,
                                    $content,
                                    $count
                                );
                                if ($count) {
                                    $updated = true;
                                }
                            }
                            if (isset($page['replace']) && is_array($page['replace'])) {
                                foreach ($page['replace'] as $find => $replace) {
                                    $count = 0;
                                    $content = str_replace(
                                        $find,
                                        $replace,
                                        $content,
                                        $count
                                    );
                                    if ($count) {
                                        $updated = true;
                                    }
                                }
                            }
                        }
                        if ($updated) {
                            $block->saveContent($content);
                            $block->save();
                        }
                    }
                    return $landing->getId();
                }
                else
                {
                    $this->setErrors($landing->getError()->getErrors());
                    return false;
                }
            }
            else
            {
                $this->setErrors($res->getErrors());
                return false;
            }
        }

        return false;
    }

    /**
     * Get domain id for new site.
     * @return mixed
     */
    protected function getDomainId()
    {
        return !Manager::isB24()
            ? Domain::getCurrentId()
            : ' ';
    }

    /**
     * Create demo page for preview.
     * @param string $code Code of page.
     * @return string
     */
    public function getUrlPreview($code)
    {
        // preview now gets always from repo
        if (!defined('LANDING_IS_REPO') || LANDING_IS_REPO !== true)
        {
            if ($restSrc = Manager::getRestPath())
            {
                $http = new HttpClient;
                try
                {
                    if (Option::get('landing', 'b24partner', 'N') == 'Y')
                    {
                        $partnerId = 0;
                    }
                    $res = Json::decode($http->get(
                        $restSrc . 'landing_cloud.cloud.getUrlPreview?user_lang=' . LANGUAGE_ID .
                        '&code=' . $code . '&type=' . $this->arParams['TYPE'] .
                        (isset($partnerId) ? '&partner_id=' . $partnerId : '')//tmp
                    ));
                }
                catch (\Exception $e) {}
                if (isset($res['result']))
                {
                    return $res['result'];
                }
                else
                {
                    return null;
                }
            }
        }

        $demo = $this->getDemoPage();
        if (isset($demo[$code]))
        {
            $smnSiteId = defined('SMN_SITE_ID')
                ? SMN_SITE_ID
                : SITE_ID;
            $funcGetSites = function()
            {
                return $this->getSites(array(
                    'select' => array(
                        'ID', 'SMN_SITE_ID'
                    ),
                    'filter' => array(
                        '=TYPE' => 'PREVIEW'
                    ),
                    'limit' => 1
                ));
            };
            $site = $funcGetSites();
            if (!$site)
            {
                $res = Site::add(array(
                    'TITLE' => 'Preview',
                    'CODE' => str_replace(
                        '#rand#',
                        strtolower(\randString(5)),
                        $this->urlTpl
                    ),
                    'DOMAIN_ID' => $this->getDomainId(),
                    'TYPE' => 'PREVIEW',
                    'SMN_SITE_ID' => $smnSiteId
                ));
                if ($res->isSuccess())
                {
                    $site = $funcGetSites();
                }
                else
                {
                    $this->setErrors($res->getErrors());
                    return null;
                }
            }
            if ($site)
            {
                $site = array_shift($site);
                if (
                    !$site['SMN_SITE_ID'] ||
                    ($smnSiteId != !$site['SMN_SITE_ID'])
                )
                {
                    Site::update($site['ID'], array(
                        'SMN_SITE_ID' => $smnSiteId
                    ));
                }
                $page = $this->getLandings(array(
                    'filter' => array(
                        'SITE_ID' => $site['ID'],
                        'XML_ID' => '%|' . $code
                    )
                ));
                if ($page)
                {
                    $page = array_shift($page);
                    $pageId = $page['ID'];
                }
                else
                {
                    $pageId = $this->createPage($site['ID'], $code);
                    $landing = Landing::createInstance($pageId);
                    $landing->publication();
                }
                if ($pageId)
                {
                    $landing = Landing::createInstance($pageId);
                    $uri = new \Bitrix\Main\Web\Uri(
                        $landing->getPublicUrl(false, true, true)
                    );
                    $uri->addParams(array(
                        'preview' => 'Y',
                        'user_lang' => $this->request('user_lang')
                            ? $this->request('user_lang')
                            : LANGUAGE_ID
                    ));
                    return $uri->getUri();
                }
            }
        }

        return null;
    }

    /**
     * Create site or page by template.
     * @param string $code Demo site code.
     * @return boolean
     */
    protected function actionSelect($code)
    {


        // else create site and pages into
        $demo = $this->getDemoSite();
        if (isset($demo[$code]))
        {

            $data = $demo[$code]['DATA'];

            $siteData = $data['fields'];
            $siteData = $this->prepareAdditionalFields($siteData);
            $siteData['DOMAIN_ID'] = $this->getDomainId();
            $siteData['CODE'] = str_replace(
                '#rand#',
                strtolower(\randString(5)),
                $this->urlTpl
            );
            $siteData['XML_ID'] = $data['name'] . '|' . $code;
            $siteData['TYPE'] = $demo[$code]['TYPE'];

            $siteId = $this->arParams['SITE_ID'];

            if ($siteId)
            {
                $siteData['ID'] = $siteId;
                $firstLandingId = false;
                $excludePages = array();
                if (
                    !isset($data['syspages']) ||
                    !is_array($data['syspages'])
                )
                {
                    $data['syspages'] = array();
                }
                // check system pages for unique
                foreach (Syspage::get($siteId) as $sysCode => $sysItem)
                {
                    if (isset($data['syspages'] [$sysCode]))
                    {
                        $excludePages[] = $data['syspages'] [$sysCode];
                        unset($data['syspages'] [$sysCode]);
                    }
                }
                // then create pages
                $landings = array();
                if (empty($data['items']))
                {
                    $data['items'][] = $code;
                }
                Landing::disableUpdate();
                foreach ($data['items'] as $page)
                {
                    if (in_array($page, $excludePages))
                    {
                        continue;
                    }

                    $landingId = $this->addPage($siteData['ID'], $page);
                    if (!$landingId)
                    {

                        print_r($this->getErrors());
                        return false;
                    }
                    elseif (!$firstLandingId)
                    {
                        $firstLandingId = $landingId;
                    }
                    $landings[$page['code']] = $landingId;

                    $landingsUpdate[$page['code']] = array(
                        'id_landing' => $landingId,
                        'page' => $page
                    );
                }

                /*$arOldLandins = array();
                foreach ($landings as $landCode1 => $landing1)
                {
                    $old_id = 0;
                    if($data['items_old'][$landCode1]) {
                        $old_id = $data['items_old'][$landCode1];
                        $arOldLandins["#landing".$old_id] = "#landing".$landing1;
                    }
                }*/
                foreach ($landingsUpdate as $landCode => $landingD)
                {
                    $this->createPage($landingD['id_landing'], $landingD['page']);
                }

                //$landingId = $this->createPage($siteData['ID'], $page);

                Landing::enableUpdate();
                // redefine content of pages
                foreach ($landings as $landCode => $landId)
                {
                    $landing = Landing::createInstance($landId);
                    if ($landing->exist())
                    {
                        foreach ($landing->getBlocks() as $block)
                        {
                            $updated = false;
                            $content = $block->getContent();
                            foreach ($landings as $landCode => $landId)
                            {
                                if($data['items_old'][$landCode])
                                {
                                    $old_id = $data['items_old'][$landCode];

                                    $count = 0;
                                    $content = str_replace(
                                        '#landing'.$old_id,
                                        '#landing'.$landId,
                                        $content,
                                        $count
                                    );
                                    if ($count)
                                    {
                                        $updated = true;
                                    }
                                }

                            }
                            if ($updated)
                            {
                                $block->saveContent($content);
                                $block->save();
                            }
                        }
                        $landing->publication();
                    }
                }
                // set layout
                $tplsXml = array();

                $res = Template::getList(array(
                    'select' => array(
                        'ID', 'XML_ID'
                    )
                ));
                while ($row = $res->fetch())
                {
                    $tplsXml[$row['XML_ID']] = $row['ID'];
                }
                // for site
                if (
                    isset($data['layout']['code']) &&
                    isset($tplsXml[$data['layout']['code']])
                )
                {
                    $ref = array();
                    if (isset($data['layout']['ref']))
                    {
                        foreach ((array)$data['layout']['ref'] as $ac => $aLidCode)
                        {
                            if (isset($landings[$aLidCode]))
                            {
                                $ref[$ac] = $landings[$aLidCode];
                            }
                        }
                    }
                    Site::update($siteData['ID'], array(
                        'TPL_ID' => $tplsXml[$data['layout']['code']]
                    ));
                    TemplateRef::setForSite(
                        $siteData['ID'],
                        $ref
                    );
                }
                // and for pages
                foreach ($data['items'] as $pageCode => $page)
                {
                    if (
                        isset($landings[$pageCode]) &&
                        isset($page['layout']['code']) &&
                        isset($tplsXml[$page['layout']['code']])
                    )
                    {
                        if (isset($page['layout']['ref']))
                        {
                            foreach ((array)$page['layout']['ref'] as $ac => $aLidCode)
                            {
                                if (isset($landings[$aLidCode]))
                                {
                                    $ref[$ac] = $landings[$aLidCode];
                                }
                            }
                        }
                        if ($tplsXml[$page['layout']['code']] > 0)
                        {
                            Landing::update($landings[$pageCode], array(
                                'TPL_ID' => $tplsXml[$page['layout']['code']]
                            ));
                            TemplateRef::setForLanding(
                                $landings[$pageCode],
                                $ref
                            );
                        }
                    }
                }
                // set pages to folders
                $alreadyFolders = array();
                if (isset($data['folders']) && is_array($data['folders']))
                {
                    foreach ($data['folders'] as $folder => $items)
                    {
                        if (isset($landings[$folder]) && is_array($items))
                        {
                            foreach ($items as $item)
                            {
                                if (isset($landings[$item]))
                                {
                                    if (!in_array($landings[$folder], $alreadyFolders))
                                    {
                                        $alreadyFolders[] = $landings[$folder];
                                        Landing::update($landings[$folder], array(
                                            'FOLDER' => 'Y'
                                        ));
                                    }
                                    Landing::update($landings[$item], array(
                                        'FOLDER_ID' => $landings[$folder]
                                    ));
                                }
                            }
                        }
                    }
                }
                // create system refs
                foreach ($data['syspages'] as $sysCode => $pageCode)
                {
                    if (isset($landings[$pageCode]))
                    {
                        Syspage::set(
                            $siteData['ID'],
                            $sysCode,
                            $landings[$pageCode]
                        );
                    }
                }

            }
            else
            {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Get demo templates.
     * @param string $subDir Subdir for data dir.
     * @return array
     */
    protected function getDemo($subDir)
    {
        $subDir = 'krayt';
        static $data = array();

        $eventFunc = function($data)
        {
            $event = new \Bitrix\Main\Event('landing', 'onDemosGetKraytRepository', array(
                'data' => $data
            ));
            $event->send();
            foreach ($event->getResults() as $result)
            {
                if ($result->getResultType() != \Bitrix\Main\EventResult::ERROR)
                {
                    if (($modified = $result->getModified()))
                    {
                        if (isset($modified['data']))
                        {
                            $data = $modified['data'];
                        }
                    }
                }
            }
            return $data;
        };

        /**
         * Attention! method also used into
         * \Bitrix\Landing\PublicAction\Demos::getList.
         */

        if (!isset($data[$subDir]))
        {
            $path =  \Bitrix\Main\Application::getDocumentRoot()."/bitrix/components/krayt/landing.demo/demo/site/";

            $directory = new \Bitrix\Main\IO\Directory($path, $siteId = null);

            $list = $directory->getChildren();

            if(is_array($list))
            {
                foreach ($list as $f)
                {
                    //  $dataSiteJson = \Bitrix\Main\IO\File::getFileContents($f->getPhysicalPath());
                    //  print_r($dataSiteJson);
                    $arData = include $f->getPhysicalPath();

                    if($arData)
                    {
                        $data[$subDir][$arData['code']] = array(
                            "ID" => $arData['code'],
                            'TYPE' => $arData['type'],
                            "TITLE" => $arData['name'],
                            "ACTIVE" => true,
                            "AVAILABLE" => true,
                            "DESCRIPTION" => $arData['description'],
                            'PREVIEW' => $arData['PREVIEW'],
                            'URL_PREVIEW' => $arData['URL_PREVIEW'],
                            'URL_BUY' => $arData['URL_BUY'],
                            'PRICE' => $arData['PRICE'],
                            'K_TYPE' => 'site',
                            "DATA" => array(
                                'name' => $arData['name'],
                                "description" => $arData['description'],
                                "fields" => $arData['fields'],
                                'items' => $arData['items'],
                                'layout' => $arData['layout'],
                                'folders' => $arData['folders'],
                                'syspages' => $arData['syspages'],
                                'items_old' => $arData['items_old'],
                                'type' => 'PAGE',
                                'version' => 1,
                                'encode' => 1

                            )
                        );
                    }
                }
            }



            return $eventFunc($data[$subDir]);
        }

        return $eventFunc($data[$subDir]);
    }

    /**
     * Get demo site templates.
     * @return array
     */
    public function getDemoSite()
    {
        return $this->getDemo($this::DEMO_DIR_SITE);
    }

    /**
     * Get demo page templates.
     * @return array
     */
    public function getDemoPage()
    {
        return $this->getDemo($this::DEMO_DIR_PAGE);
    }

    /**
     * Checking site or page activity depending on portal zone
     * Format:
     * $zones['ONLY_IN'] - show site only in these zones
     * $zones['EXCEPT'] - not show site, if zone in this list
     * @param array $zones Zones array.
     * @return bool
     */
    public static function checkActive($zones = array())
    {
        if (!empty($zones))
        {
            $currentZone = Manager::getZone();
            if (
                isset($zones['ONLY_IN']) &&
                is_array($zones['ONLY_IN']) && !empty($zones['ONLY_IN']) &&
                !in_array($currentZone, $zones['ONLY_IN'])
            )
            {
                return false;
            }
            if (
                isset($zones['EXCEPT']) &&
                is_array($zones['EXCEPT']) && !empty($zones['EXCEPT']) &&
                in_array($currentZone, $zones['EXCEPT'])
            )
            {
                return false;
            }
            return true;
        }
    }

    /**
     * Create some highloadblocks.
     * @return void
     */
    public static function createHLblocks()
    {
        if (!Loader::includeModule('highloadblock'))
        {
            return;
        }

        $xmlPath = '/bitrix/components/bitrix/landing.demo/data/xml';

        // demo data
        $sort = 0;
        $colorValues = array();
        $colors = array(
            'PURPLE' => 'colors_files/iblock/0d3/0d3ef035d0cf3b821449b0174980a712.jpg',
            'BROWN' => 'colors_files/iblock/f5a/f5a37106cb59ba069cc511647988eb89.jpg',
            'SEE' => 'colors_files/iblock/f01/f01f801e9da96ae5a7f26aae01255f38.jpg',
            'BLUE' => 'colors_files/iblock/c1b/c1ba082577379bdc75246974a9f08c8b.jpg',
            'ORANGERED' => 'colors_files/iblock/0ba/0ba3b7ecdef03a44b145e43aed0cca57.jpg',
            'REDBLUE' => 'colors_files/iblock/1ac/1ac0a26c5f47bd865a73da765484a2fa.jpg',
            'RED' => 'colors_files/iblock/0a7/0a7513671518b0f2ce5f7cf44a239a83.jpg',
            'GREEN' => 'colors_files/iblock/b1c/b1ced825c9803084eb4ea0a742b2342c.jpg',
            'WHITE' => 'colors_files/iblock/b0e/b0eeeaa3e7519e272b7b382e700cbbc3.jpg',
            'BLACK' => 'colors_files/iblock/d7b/d7bdba8aca8422e808fb3ad571a74c09.jpg',
            'PINK' => 'colors_files/iblock/1b6/1b61761da0adce93518a3d613292043a.jpg',
            'AZURE' => 'colors_files/iblock/c2b/c2b274ad2820451d780ee7cf08d74bb3.jpg',
            'JEANS' => 'colors_files/iblock/24b/24b082dc5e647a3a945bc9a5c0a200f0.jpg',
            'FLOWERS' => 'colors_files/iblock/64f/64f32941a654a1cbe2105febe7e77f33.jpg'
        );
        foreach ($colors as $colorName => $colorFile)
        {
            $sort += 100;
            $colorValues[] = array(
                'UF_NAME' => $colorName,
                'UF_FILE' =>
                    array (
                        'name' => strtolower($colorName) . '.jpg',
                        'type' => 'image/jpeg',
                        'tmp_name' => Manager::getDocRoot() . $xmlPath . '/hl/' . $colorFile
                    ),
                'UF_SORT' => $sort,
                'UF_DEF' => ($sort > 100) ? '0' : '1',
                'UF_XML_ID' => strtolower($colorName)
            );
        }
        $sort = 0;
        $brandValues = array();
        $brands = array(
            'Company1' => 'brands_files/cm-01.png',
            'Company2' => 'brands_files/cm-02.png',
            'Company3' => 'brands_files/cm-03.png',
            'Company4' => 'brands_files/cm-04.png',
            'Brand1' => 'brands_files/bn-01.png',
            'Brand2' => 'brands_files/bn-02.png',
            'Brand3' => 'brands_files/bn-03.png',
        );
        foreach ($brands as $brandName => $brandFile)
        {
            $sort += 100;
            $brandValues[] = array(
                'UF_NAME' => $brandName,
                'UF_FILE' =>
                    array (
                        'name' => strtolower($brandName) . '.jpg',
                        'type' => 'image/jpeg',
                        'tmp_name' => Manager::getDocRoot() . $xmlPath . '/hl/' . $brandFile
                    ),
                'UF_SORT' => $sort,
                'UF_XML_ID' => strtolower($brandName)
            );
        }

        // some tables
        $tables = array(
            'eshop_color_reference' => array(
                'name' => 'ColorReference',
                'fields' => array(
                    array(
                        'FIELD_NAME' => 'UF_NAME',
                        'USER_TYPE_ID' => 'string',
                        'XML_ID' => 'UF_COLOR_NAME'
                    ),
                    array(
                        'FIELD_NAME' => 'UF_FILE',
                        'USER_TYPE_ID' => 'file',
                        'XML_ID' => 'UF_COLOR_FILE'
                    ),
                    array(
                        'FIELD_NAME' => 'UF_SORT',
                        'USER_TYPE_ID' => 'double',
                        'XML_ID' => 'UF_COLOR_SORT'
                    ),
                    array(
                        'FIELD_NAME' => 'UF_DEF',
                        'USER_TYPE_ID' => 'boolean',
                        'XML_ID' => 'UF_COLOR_DEF'
                    ),
                    array(
                        'FIELD_NAME' => 'UF_XML_ID',
                        'USER_TYPE_ID' => 'string',
                        'XML_ID' => 'UF_XML_ID'
                    )
                ),
                'values' => $colorValues
            ),
            'eshop_brand_reference' => array(
                'name' => 'BrandReference',
                'fields' => array(
                    array(
                        'FIELD_NAME' => 'UF_NAME',
                        'USER_TYPE_ID' => 'string',
                        'XML_ID' => 'UF_BRAND_NAME'
                    ),
                    array(
                        'FIELD_NAME' => 'UF_FILE',
                        'USER_TYPE_ID' => 'file',
                        'XML_ID' => 'UF_BRAND_FILE'
                    ),
                    array(
                        'FIELD_NAME' => 'UF_SORT',
                        'USER_TYPE_ID' => 'double',
                        'XML_ID' => 'UF_BRAND_SORT'
                    ),
                    array(
                        'FIELD_NAME' => 'UF_XML_ID',
                        'USER_TYPE_ID' => 'string',
                        'XML_ID' => 'UF_XML_ID'
                    )
                ),
                'values' => $brandValues
            )
        );

        // create tables and fill with demo-data
        foreach ($tables as $tableName => &$table)
        {
            // if this hl isn't exist
            $res = Highloadblock\HighloadBlockTable::getList(
                array(
                    'filter' => array(
                        'NAME' => $table['name'],
                        'TABLE_NAME' => $tableName
                    )
                )
            );
            if (!$res->fetch())
            {
                // add new hl block
                $result = Highloadblock\HighloadBlockTable::add(array(
                    'NAME' => $table['name'],
                    'TABLE_NAME' => $tableName
                ));
                if ($result->isSuccess())
                {
                    $sort = 100;
                    $tableId = $result->getId();
                    // add fields
                    $userField  = new \CUserTypeEntity;
                    foreach ($table['fields'] as $field)
                    {
                        $field['ENTITY_ID'] = 'HLBLOCK_' . $tableId;
                        $res = \CUserTypeEntity::getList(
                            array(),
                            array(
                                'ENTITY_ID' => $field['ENTITY_ID'],
                                'FIELD_NAME' => $field['FIELD_NAME']
                            )
                        );
                        if (!$res->Fetch())
                        {
                            $field['SORT'] = $sort;
                            $userField->Add($field);
                            $sort += 100;
                        }
                    }
                    // add data
                    $hldata = Highloadblock\HighloadBlockTable::getById($tableId)->fetch();
                    $hlentity = Highloadblock\HighloadBlockTable::compileEntity($hldata);
                    $entityClass = $hlentity->getDataClass();
                    foreach ($table['values'] as $item)
                    {
                        $entityClass::add($item);
                    }
                }
            }
        }
    }

    /**
     * Create products in CRM catalog.
     * @return bool|string True or error message.
     */
    public static function createCatalog($xmlcode)
    {
        $currentZone = Manager::getZone();
        $xmlProductCatalog = 'CRM_PRODUCT_CATALOG';
        $xmlPath = '/bitrix/components/bitrix/landing.demo/data/xml';
        $xmls = array(
            'catalog' => 0,
            'catalog_prices' => 0,
            'catalog_sku' => 0,
            'catalog_prices_sku' => 0
        );

        if (
            !Loader::includeModule('iblock') ||
            !Loader::includeModule('catalog')
        )
        {
            return Loc::getMessage('LANDING_CMP_ERROR_MASTER_NO_SERVICE');
        }

        self::createHLblocks();

        // ru-zones
        if (in_array(LANGUAGE_ID, array('ru')))
        {
            // prepare xml_id for xml import
            if (Loader::includeModule('crm'))
            {
                $iblockId = Option::get(
                    'crm',
                    'default_product_catalog_id'
                );
                if (
                    $iblockId &&
                    ($iblock = \CIBlock::getByID($iblockId)->fetch()) &&
                    ($iblock['XML_ID'] != 'FUTURE-1C-CATALOG')
                )
                {
                    $iblock = new \CIBlock();
                    $iblock->update(
                        $iblockId,
                        array(
                            'XML_ID' => 'FUTURE-1C-CATALOG'
                        )
                    );
                }
            }
            // import xml
            \Bitrix\Catalog\Product\Sku::disableUpdateAvailable();
            foreach ($xmls as $xml => &$bid)
            {
                $res = \importXMLFile(
                    Manager::getDocRoot() . $xmlPath . '/clothes_ru/' . $xml . '.xml',
                    $xmlProductCatalog,
                    array(SITE_ID),
                    false, false, false,
                    false, false, true,
                    true
                );
                // error
                if (intval($res) <= 0)
                {
                    return $res;
                }
                else
                {
                    $bid = $res;
                }
            }

            // set rights for crm
            if (
                Loader::includeModule('crm') &&
                is_callable(array('\CCrmSaleHelper', 'getShopGroupIdByType')) &&
                is_callable(array('\CIBlockRights', 'setGroupRight'))
            )
            {
                \CIBlockRights::setGroupRight(
                    \CCrmSaleHelper::getShopGroupIdByType('admin'),
                    $xmlProductCatalog,
                    'X',
                    $xmls['catalog_sku']
                );
                \CIBlockRights::setGroupRight(
                    \CCrmSaleHelper::getShopGroupIdByType('manager'),
                    $xmlProductCatalog,
                    'W',
                    $xmls['catalog_sku']
                );
            }

            \Bitrix\Catalog\Product\Sku::enableUpdateAvailable();

            // link iblocks
            $propId = \CCatalog::linkSKUIBlock(
                $xmls['catalog'],
                $xmls['catalog_sku']
            );
            $res = \CCatalog::getList(
                array(),
                array(
                    'IBLOCK_ID' => $xmls['catalog_sku']
                ),
                false,
                false,
                array(
                    'IBLOCK_ID'
                )
            );
            if ($res->fetch())
            {
                \CCatalog::update(
                    $xmls['catalog_sku'],
                    array(
                        'PRODUCT_IBLOCK_ID' => $xmls['catalog'],
                        'SKU_PROPERTY_ID' => $propId
                    )
                );
            }
            else
            {
                \CCatalog::add(array(
                    'IBLOCK_ID' => $xmls['catalog_sku'],
                    'PRODUCT_IBLOCK_ID' => $xmls['catalog'],
                    'SKU_PROPERTY_ID' => $propId
                ));
            }

            // additional updates -- common
            foreach (array('catalog', 'catalog_sku') as $ibCode)
            {
                $iblockId = $xmls[$ibCode];
                // uniq code
                $defValueCode = array (
                    'UNIQUE' => 'Y',
                    'TRANSLITERATION' => 'Y',
                    'TRANS_LEN' => 100,
                    'TRANS_CASE' => 'L',
                    'TRANS_SPACE' => '_',
                    'TRANS_OTHER' => '_',
                    'TRANS_EAT' => 'Y',
                    'USE_GOOGLE' => 'Y'
                );
                $iblock = new \CIBlock;
                $iblock->update($iblockId, array(
                    'FIELDS' => array(
                        'CODE' => array (
                            'IS_REQUIRED' => 'N',
                            'DEFAULT_VALUE' => $defValueCode
                        ),
                        'SECTION_CODE' => array (
                            'IS_REQUIRED' => 'N',
                            'DEFAULT_VALUE' => $defValueCode
                        )
                    ),
                    'LIST_MODE' => 'S'
                ));
                // delete all props
                $toDelete = array(
                    /*'CML2_TAXES', 'CML2_BASE_UNIT', 'CML2_TRAITS', 'CML2_ATTRIBUTES', 'CML2_ARTICLE',
                    'CML2_BAR_CODE', 'CML2_MANUFACTURER', */'CML2_PICTURES', 'CML2_FILES'
                );
                foreach ($toDelete as $code)
                {
                    $res = \CIBlockProperty::getList(
                        array(),
                        array(
                            'IBLOCK_ID' => $iblockId,
                            'XML_ID' => $code
                        )
                    );
                    if($row = $res->GetNext())
                    {
                        \CIBlockProperty::delete($row['ID']);
                    }
                }
                // reindex
                $index = \Bitrix\Iblock\PropertyIndex\Manager::createIndexer(
                    $iblockId
                );
                $index->startIndex();
                $index->continueIndex(0);
                $index->endIndex();
            }

            // update only for catalog - some magic
            $iblockId = $xmls['catalog'];
            $count = \Bitrix\Iblock\ElementTable::getCount(array(
                '=IBLOCK_ID' => $iblockId,
                '=WF_PARENT_ELEMENT_ID' => null
            ));
            if ($count > 0)
            {
                $catalogReindex = new \CCatalogProductAvailable('', 0, 0);
                $catalogReindex->initStep($count, 0, 0);
                $catalogReindex->setParams(array(
                    'IBLOCK_ID' => $iblockId
                ));
                $catalogReindex->run();
            }

            // update only for offers - some magic
            $iblockId = $xmls['catalog_sku'];
            $count = \Bitrix\Iblock\ElementTable::getCount(array(
                '=IBLOCK_ID' => $iblockId,
                '=WF_PARENT_ELEMENT_ID' => null
            ));
            if ($count > 0)
            {
                $catalogReindex = new \CCatalogProductAvailable('', 0, 0);
                $catalogReindex->initStep($count, 0, 0);
                $catalogReindex->setParams(array(
                    'IBLOCK_ID' => $iblockId
                ));
                $catalogReindex->run();
            }
            $iterator = \Bitrix\Catalog\ProductTable::getList(array(
                'select' => array(
                    'ID'
                ),
                'filter' => array(
                    '=IBLOCK_ELEMENT.IBLOCK_ID' => $iblockId
                ),
                'order' => array(
                    'ID' => 'ASC'
                )
            ));
            while ($row = $iterator->fetch())
            {
                $check = \Bitrix\Catalog\MeasureRatioTable::getList(array(
                    'filter' => array(
                        'PRODUCT_ID' => $row['ID'],
                        'RATIO' => 1
                    )
                ));
                if (!$check->fetch())
                {
                    \Bitrix\Catalog\MeasureRatioTable::add(array(
                        'PRODUCT_ID' => $row['ID'],
                        'RATIO' => 1
                    ));
                }
            }

            return true;
        }
        else
        {
            return Loc::getMessage('LANDING_CMP_ERROR_MASTER_NO_DATA');
        }
    }

    /**
     * Base executable method.
     * @return void
     */
    public function executeComponent()
    {

        $this->checkParam('SITE_ID', 0);
        $this->checkParam('TYPE', '');
        $this->checkParam('ACTION_FOLDER', 'folderId');
        $this->checkParam('PAGE_URL_SITES', '');
        $this->checkParam('PAGE_URL_LANDING_VIEW', '');
        $this->checkParam('SITE_WORK_MODE', 'N');

        $this->checkParam('tpl', false);
        $this->checkParam('k_type', false);

        if($this->arParams['tpl'] && $this->arParams['k_type'])
        {
            $demo = $this->getDemoPage();
            $this->arResult['SITES'] = Site::getList(array(
                'select' => array('ID',"TITLE")
            ))->fetchAll();

            $request = Application::getInstance()->getContext()->getRequest();
            $this->arResult['CURL'] = $request->getRequestUri();

            if($request->getPost('site_code') &&  $request->getPost('site_id'))
            {
                if(!isset($demo[$request->getPost('site_code')]))
                {
                    $this->setErrors("NOt tempalte site");
                }
                $this->arParams['SITE_ID'] = $request->getPost('site_id');
                unset($demo);
                set_time_limit(60);
                $resInstall  = $this->actionSelect($request->getPost('site_code'));
                if($resInstall)
                {
                    $this->arResult['is_add_site'] = true;
                }
            }else{
                $this->arResult['INSTALL'] = $demo[$this->arParams['tpl']];
            }

            $this->includeComponentTemplate('install');

        }else{
            $this->arResult['DEMO'] = $this->getDemoPage();
            $this->IncludeComponentTemplate($this->template);
        }


    }
}
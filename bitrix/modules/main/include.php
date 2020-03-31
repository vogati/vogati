<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

require_once(substr(__FILE__, 0, strlen(__FILE__) - strlen("/include.php"))."/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/start.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/virtual_io.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/virtual_file.php");

$application = \Bitrix\Main\Application::getInstance();
$application->initializeExtendedKernel(array(
	"get" => $_GET,
	"post" => $_POST,
	"files" => $_FILES,
	"cookie" => $_COOKIE,
	"server" => $_SERVER,
	"env" => $_ENV
));

//define global application object
$GLOBALS["APPLICATION"] = new CMain;

if(defined("SITE_ID"))
	define("LANG", SITE_ID);

if(defined("LANG"))
{
	if(defined("ADMIN_SECTION") && ADMIN_SECTION===true)
		$db_lang = CLangAdmin::GetByID(LANG);
	else
		$db_lang = CLang::GetByID(LANG);

	$arLang = $db_lang->Fetch();

	if(!$arLang)
	{
		throw new \Bitrix\Main\SystemException("Incorrect site: ".LANG.".");
	}
}
else
{
	$arLang = $GLOBALS["APPLICATION"]->GetLang();
	define("LANG", $arLang["LID"]);
}

if($arLang["CULTURE_ID"] == '')
{
	throw new \Bitrix\Main\SystemException("Culture not found, or there are no active sites or languages.");
}

$lang = $arLang["LID"];
if (!defined("SITE_ID"))
	define("SITE_ID", $arLang["LID"]);
define("SITE_DIR", $arLang["DIR"]);
define("SITE_SERVER_NAME", $arLang["SERVER_NAME"]);
define("SITE_CHARSET", $arLang["CHARSET"]);
define("FORMAT_DATE", $arLang["FORMAT_DATE"]);
define("FORMAT_DATETIME", $arLang["FORMAT_DATETIME"]);
define("LANG_DIR", $arLang["DIR"]);
define("LANG_CHARSET", $arLang["CHARSET"]);
define("LANG_ADMIN_LID", $arLang["LANGUAGE_ID"]);
define("LANGUAGE_ID", $arLang["LANGUAGE_ID"]);

$culture = \Bitrix\Main\Localization\CultureTable::getByPrimary($arLang["CULTURE_ID"], ["cache" => ["ttl" => CACHED_b_lang]])->fetchObject();

$context = $application->getContext();
$context->setLanguage(LANGUAGE_ID);
$context->setCulture($culture);

$request = $context->getRequest();
if (!$request->isAdminSection())
{
	$context->setSite(SITE_ID);
}

$application->start();

$GLOBALS["APPLICATION"]->reinitPath();

if (!defined("POST_FORM_ACTION_URI"))
{
	define("POST_FORM_ACTION_URI", htmlspecialcharsbx(GetRequestUri()));
}

$GLOBALS["MESS"] = array();
$GLOBALS["ALL_LANG_FILES"] = array();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/tools.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/database.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/main.php");
IncludeModuleLangFile(__FILE__);

error_reporting(COption::GetOptionInt("main", "error_reporting", E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE) & ~E_STRICT & ~E_DEPRECATED);

if(!defined("BX_COMP_MANAGED_CACHE") && COption::GetOptionString("main", "component_managed_cache_on", "Y") <> "N")
{
	define("BX_COMP_MANAGED_CACHE", true);
}

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/filter_tools.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/ajax_tools.php");

/*ZDUyZmZZjU3YWU5YTcwNTBjNzM1YmIxZWVjZjczODFlZGY0NmE=*/$GLOBALS['_____651066800']= array(base64_decode('R2V'.'0'.'T'.'W9kdWxlR'.'X'.'ZlbnRz'),base64_decode('R'.'X'.'hlY3V0ZU1vZH'.'VsZUV2ZW5'.'0RXg'.'='));$GLOBALS['____346423011']= array(base64_decode('Z'.'GVmaW5l'),base64_decode('c'.'3RybGV'.'u'),base64_decode('Ym'.'F'.'zZTY0'.'X'.'2R'.'lY29'.'kZQ='.'='),base64_decode('dW5z'.'ZXJ'.'pYW'.'xpem'.'U='),base64_decode('aXNf'.'YXJ'.'yY'.'Xk='),base64_decode('Y291bnQ'.'='),base64_decode('aW5f'.'YXJy'.'YXk='),base64_decode(''.'c'.'2'.'VyaWFsaXpl'),base64_decode('YmFz'.'ZT'.'Y0X2'.'VuY2'.'9kZQ=='),base64_decode('c3RybGVu'),base64_decode(''.'YXJyYX'.'lfa2V5'.'X'.'2V4aXN0cw=='),base64_decode(''.'YXJyYXlfa2V5X2V4aX'.'N0'.'cw=='),base64_decode('bW'.'t0aW1l'),base64_decode('ZGF0'.'Z'.'Q=='),base64_decode('ZG'.'F0ZQ='.'='),base64_decode('YXJyY'.'Xlfa2V5X2V4aXN0cw=='),base64_decode('c3RybGVu'),base64_decode('YXJyYXlfa2V5X2V4aXN0cw=='),base64_decode('c3Ryb'.'GVu'),base64_decode('YXJyYXlfa2V'.'5X2V4aXN'.'0cw=='),base64_decode(''.'YXJyYXlf'.'a2'.'V5X2V4aXN0cw=='),base64_decode(''.'b'.'Wt0aW1'.'l'),base64_decode('ZGF0ZQ='.'='),base64_decode(''.'ZGF0ZQ=='),base64_decode('b'.'WV'.'0aG9kX2V4aXN0cw=='),base64_decode('Y'.'2FsbF9'.'1c2VyX2Z1b'.'mN'.'f'.'YX'.'JyYXk='),base64_decode(''.'c'.'3'.'RybGVu'),base64_decode('YX'.'Jy'.'YXl'.'fa'.'2V5'.'X2V4aX'.'N0cw='.'='),base64_decode('YXJ'.'yYXl'.'fa2V5X2V4aXN0cw='.'='),base64_decode('c'.'2VyaWF'.'saXpl'),base64_decode(''.'Y'.'mFzZT'.'Y'.'0X2VuY29kZQ='.'='),base64_decode('c'.'3'.'R'.'yb'.'GV'.'u'),base64_decode('Y'.'XJyYXlfa'.'2V5X2V'.'4a'.'X'.'N'.'0'.'cw'.'='.'='),base64_decode('Y'.'XJyYXlf'.'a'.'2V5X2V4aXN0c'.'w='.'='),base64_decode('Y'.'XJyYX'.'lfa2V5'.'X2'.'V4'.'aX'.'N0cw=='),base64_decode(''.'a'.'XNf'.'YXJ'.'y'.'YXk='),base64_decode('YX'.'J'.'yYXlfa2V5'.'X'.'2V'.'4aXN0cw=='),base64_decode('c'.'2VyaWFsaXpl'),base64_decode('Y'.'mFzZTY'.'0X2'.'Vu'.'Y29kZQ=='),base64_decode('YX'.'JyYXl'.'fa'.'2'.'V5'.'X2V4aXN0cw=='),base64_decode('YXJyYXlfa2'.'V'.'5X2V4aXN0c'.'w=='),base64_decode('c'.'2VyaWFs'.'aXpl'),base64_decode(''.'YmFzZTY0X2VuY'.'29kZQ=='),base64_decode('a'.'XNfYXJyY'.'Xk='),base64_decode('aXNfYXJy'.'YXk='),base64_decode('aW5fYXJ'.'yY'.'Xk='),base64_decode('YXJyYX'.'lfa2V5'.'X'.'2V4aXN0cw='.'='),base64_decode('aW5fYX'.'JyYXk='),base64_decode(''.'bWt'.'0aW1l'),base64_decode('ZG'.'F0ZQ=='),base64_decode('ZGF0ZQ='.'='),base64_decode('ZGF0ZQ=='),base64_decode('b'.'Wt0a'.'W1l'),base64_decode('ZGF0ZQ'.'='.'='),base64_decode('Z'.'GF0Z'.'Q=='),base64_decode(''.'aW5fYXJ'.'yYXk='),base64_decode(''.'YXJyYXl'.'fa2V5X2V'.'4a'.'X'.'N0cw='.'='),base64_decode('YXJy'.'Y'.'X'.'lfa2V5X2V4aXN'.'0cw=='),base64_decode('c2Vya'.'WFsaXpl'),base64_decode('YmFzZT'.'Y0X'.'2VuY29'.'kZQ'.'=='),base64_decode('YXJyYXl'.'fa2V5X2V4'.'aXN0cw=='),base64_decode('aW50dmFs'),base64_decode(''.'dGlt'.'ZQ='.'='),base64_decode('YXJy'.'YXlfa2V5X2V4a'.'XN0cw=='),base64_decode('Zm'.'lsZV9l'.'eGlzdHM='),base64_decode('c3RyX'.'3'.'JlcGxhY'.'2'.'U='),base64_decode(''.'Y2xhc3Nf'.'ZXh'.'pc3Rz'),base64_decode('Z'.'G'.'VmaW5l'));if(!function_exists(__NAMESPACE__.'\\___526600228')){function ___526600228($_214536514){static $_1619453283= false; if($_1619453283 == false) $_1619453283=array('SU5'.'UUk'.'FORVRfRURJV'.'ElPTg==','WQ='.'=','bWF'.'pbg='.'=','fmNwZl'.'9tYXBfdmFsdWU=','',''.'ZQ==','Zg==','Z'.'Q==','Rg==','WA==','Zg==','bWFpbg='.'=','fmNwZl9tYXB'.'fdmFsd'.'WU=',''.'UG9'.'y'.'dGF'.'s','Rg==','ZQ'.'==','Z'.'Q'.'==','WA==','R'.'g='.'=','RA==','RA==','bQ==','ZA==','WQ='.'=','Zg==','Z'.'g==','Zg==','Zg==','UG9ydGFs','Rg==','ZQ==','ZQ='.'=','WA==','Rg='.'=','RA==','R'.'A==','bQ'.'==',''.'ZA==',''.'WQ'.'==','bW'.'Fpbg'.'==','T2'.'4=','U2'.'V0d'.'GluZ3NDaGFuZ2U=','Z'.'g==','Zg='.'=','Zg==','Zg==','bWFpbg==','fmNwZl9tYX'.'Bfdm'.'F'.'s'.'dWU=','ZQ'.'='.'=','ZQ==','ZQ==','RA'.'==','ZQ'.'='.'=','ZQ'.'='.'=','Z'.'g==','Zg'.'='.'=','Zg==','ZQ==','bWFpbg==','fmNwZl9t'.'YXBfdmFsdWU'.'=','ZQ'.'==','Zg='.'=','Zg==','Zg==','Zg'.'='.'=','bWFp'.'b'.'g==','f'.'mNw'.'Zl9t'.'Y'.'XBf'.'d'.'mFsdWU=','Z'.'Q='.'=','Z'.'g==','U'.'G'.'9ydGF'.'s','UG9'.'ydG'.'Fs','ZQ==','ZQ==','UG9y'.'dGF'.'s',''.'Rg'.'==',''.'W'.'A==','Rg'.'==','RA==','ZQ==','ZQ==','RA==','b'.'Q'.'==','ZA==','WQ'.'==','ZQ='.'=','WA==','ZQ==','R'.'g'.'='.'=','Z'.'Q==',''.'RA==','Z'.'g==','ZQ==','RA==','ZQ'.'==','bQ==',''.'ZA==','WQ==','Zg==','Zg==','Z'.'g'.'==','Zg='.'=','Zg='.'=','Zg='.'=','Zg'.'==','Zg==','bWFpbg'.'='.'=','fmNwZl9tYXBfdmF'.'s'.'dWU=','ZQ==',''.'ZQ==',''.'UG9ydGFs','Rg==','WA==','VF'.'lQR'.'Q='.'=','REFURQ='.'=','Rk'.'VB'.'VF'.'VSRVM=','RVhQ'.'SV'.'JFRA='.'=',''.'VFlQRQ==','RA='.'=','VFJZX'.'0'.'R'.'BW'.'VNfQ09'.'VTl'.'Q=','REFURQ==','VFJZX0RBWVNfQ09VT'.'l'.'Q'.'=','RVhQSVJFRA==','Rk'.'V'.'BVFVS'.'RVM=',''.'Zg==','Zg==','RE9DVU'.'1F'.'TlR'.'fUk9'.'PVA'.'==',''.'L2JpdHJpeC9tb2R1'.'b'.'GVzLw==',''.'L2'.'lu'.'c3RhbGwvaW5kZ'.'Xg'.'uc'.'G'.'hw','Lg==','Xw==',''.'c2V'.'hcmNo','Tg='.'=','','','Q'.'UNUSVZF','WQ'.'==','c'.'29jaWFsbmV0d29'.'yaw='.'=','YWxsb'.'3'.'dfZnJ'.'p'.'ZWxkcw==','WQ==','SUQ=',''.'c29jaWFsb'.'mV0d'.'29ya'.'w='.'=','YW'.'xsb3'.'dfZ'.'nJpZWxkcw'.'==','SUQ=',''.'c'.'2'.'9jaWFsbmV0d29yaw==','YW'.'xsb3dfZn'.'J'.'pZW'.'xk'.'cw==','Tg==','','','QU'.'N'.'USVZF','WQ='.'=','c29jaWFsbm'.'V0d29ya'.'w==','YWxs'.'b3df'.'bW'.'ljcm9ibG9n'.'X3VzZXI=',''.'W'.'Q='.'=','S'.'UQ=','c29jaWFs'.'bmV0d29y'.'aw==','YWxsb3'.'dfb'.'Wljc'.'m'.'9ibG9nX3VzZX'.'I=','SU'.'Q=','c29jaW'.'F'.'sbm'.'V'.'0d29ya'.'w==','Y'.'Wx'.'sb3'.'df'.'bWl'.'jcm9ibG9n'.'X3VzZX'.'I=','c29jaWF'.'sb'.'mV'.'0'.'d29yaw==',''.'YWxsb3df'.'bWl'.'jcm9ibG9nX2'.'dyb'.'3Vw','W'.'Q==','SUQ=','c'.'29jaWF'.'sb'.'m'.'V0d29'.'yaw='.'=','YW'.'xsb3dfbWljcm9ibG9'.'nX2dy'.'b3Vw','SUQ'.'=','c'.'29j'.'aWFsbmV0'.'d2'.'9y'.'aw==','YWx'.'sb3df'.'bWljcm'.'9ibG9nX2dyb3V'.'w','Tg==','','','QU'.'N'.'USV'.'Z'.'F','WQ'.'==','c29jaWF'.'sb'.'mV0d29y'.'aw==','Y'.'Wxs'.'b'.'3'.'dfZml'.'sZXNf'.'dXN'.'lcg==','WQ'.'='.'=','SUQ'.'=','c29jaW'.'Fsbm'.'V0d29y'.'a'.'w==','YWxs'.'b3dfZ'.'mlsZ'.'XNfdXNlcg'.'==','SUQ=',''.'c'.'29'.'ja'.'W'.'FsbmV0d'.'29'.'ya'.'w==','YWxsb3dfZm'.'ls'.'ZXNfdXN'.'l'.'cg==','Tg'.'==','','',''.'QUN'.'USVZ'.'F',''.'W'.'Q='.'=','c29ja'.'WFsbmV0d'.'29'.'yaw='.'=','YWxs'.'b3dfYmxvZ191c2V'.'y','WQ==','SU'.'Q=','c2'.'9jaWF'.'sbmV0d29y'.'aw==','YWxsb3'.'dfYmxvZ1'.'91c2V'.'y','SUQ'.'=',''.'c29'.'jaWFsbmV0d29'.'yaw='.'=','YWxs'.'b3'.'dfYmxvZ1'.'91'.'c'.'2'.'Vy','T'.'g'.'==','','','QUN'.'USVZF','WQ==','c'.'29ja'.'WFsbmV0d29ya'.'w='.'=','YWxsb3d'.'fcGh'.'v'.'d'.'G9fdXNl'.'cg'.'='.'=','WQ==','SUQ=','c2'.'9jaWFsb'.'mV'.'0d29yaw'.'==','YWx'.'sb3dfcGhvdG9fdXNlcg='.'=',''.'SUQ'.'=','c29ja'.'WFsbmV0d2'.'9ya'.'w='.'=','YWxsb3dfcG'.'hvd'.'G9'.'fd'.'X'.'Nlcg'.'==','Tg==','','','QU'.'NU'.'SVZ'.'F','W'.'Q==','c29jaWFsbmV0d29yaw='.'=','YWxsb3dfZm9ydW1fdXNlc'.'g='.'=','WQ==',''.'S'.'UQ=','c'.'29j'.'aWF'.'sbmV'.'0d'.'29yaw='.'=','YWxs'.'b3'.'d'.'fZ'.'m9yd'.'W1fdXNlcg==','SU'.'Q=','c29'.'jaWFs'.'bmV'.'0d2'.'9yaw==','YW'.'xs'.'b'.'3dfZm9yd'.'W1fd'.'XNlcg==','Tg==','','',''.'QUNUSV'.'Z'.'F','W'.'Q==','c29'.'ja'.'W'.'FsbmV0d29y'.'aw==','YWxsb3dfdGF'.'za3Nf'.'dX'.'Nlcg'.'==','W'.'Q==','SUQ=','c29jaWFsbm'.'V0'.'d29yaw==',''.'YW'.'xs'.'b'.'3d'.'fd'.'GFza3'.'NfdXNlc'.'g==','SUQ'.'=','c29jaWFs'.'bmV0d29ya'.'w==','YWx'.'sb3dfdGF'.'za3'.'Nfd'.'XNl'.'cg='.'=','c'.'29jaW'.'F'.'sbmV0d29y'.'aw==','Y'.'Wxsb3df'.'dGFza3N'.'fZ'.'3'.'JvdXA=',''.'WQ==',''.'SU'.'Q'.'=','c2'.'9'.'j'.'aW'.'Fsbm'.'V0d29yaw==','YWxsb3dfdGFza3'.'Nf'.'Z3J'.'v'.'dX'.'A=',''.'S'.'UQ'.'=',''.'c'.'29jaWF'.'sb'.'m'.'V0d29yaw='.'=','YWxsb3dfdGFza3NfZ3JvdXA=',''.'d'.'G'.'Fza'.'3M=',''.'Tg==','','','QUNUSV'.'ZF',''.'WQ==','c'.'29jaWFs'.'bmV'.'0'.'d'.'29yaw==','YWxsb3dfY2'.'Fs'.'Z'.'W'.'5kYXJfd'.'XNlcg'.'==','WQ==','SUQ=','c29jaWFsbmV0'.'d29y'.'aw='.'=','YWxsb3dfY2FsZW5kY'.'XJfdXNlc'.'g==','S'.'UQ=','c'.'2'.'9jaWF'.'sbmV0d29y'.'aw='.'=','YWxsb'.'3dfY'.'2FsZW5kYXJfdXNlcg==',''.'c29jaWFsbmV'.'0d2'.'9yaw==',''.'Y'.'Wx'.'sb3dfY2F'.'sZW5k'.'YXJfZ3JvdXA'.'=','WQ'.'==','SUQ=','c29j'.'aWF'.'sbmV0d2'.'9y'.'aw='.'=','YWxsb3dfY2F'.'sZW'.'5k'.'YX'.'JfZ3Jvd'.'XA=',''.'SUQ'.'=','c'.'29'.'jaWFsbm'.'V0'.'d29'.'yaw==','YWxsb3'.'dfY2F'.'sZ'.'W5kYXJ'.'fZ3JvdX'.'A=','QUNUSVZF','WQ='.'=','T'.'g==','ZXh0c'.'mF'.'uZXQ=','aWJsb'.'2Nr','T25'.'B'.'ZnR'.'lcklCbG9'.'ja'.'0'.'Vs'.'ZW1'.'lbnRVc'.'GRh'.'dGU'.'=','aW'.'50'.'cmF'.'uZXQ'.'=',''.'Q0ludHJhbmV0RXZ'.'l'.'bnRIYW5kbGVycw'.'==',''.'U1BSZWdpc3RlclVw'.'ZGF0ZWRJdGVt','Q0l'.'udH'.'Jhb'.'mV0U'.'2hhcm'.'Vwb2ludDo6QWd'.'lbnRMaXN0cygpOw==','aW50cm'.'FuZ'.'XQ=','Tg='.'=','Q0lu'.'dHJh'.'bm'.'V0U'.'2'.'hhcmVw'.'b2ludDo'.'6QWdlbnR'.'R'.'dWV'.'1ZS'.'gpOw'.'==',''.'aW'.'50'.'cmF'.'uZ'.'X'.'Q'.'=',''.'Tg'.'==','Q0ludHJ'.'hbm'.'V0U2hhcmVwb2lu'.'dDo6'.'QWdlb'.'nRVcGRhdGU'.'oKTs=','aW50'.'c'.'mFuZXQ=','Tg==','aWJsb2N'.'r',''.'T25'.'BZnRl'.'cklCbG9'.'ja0'.'VsZW1'.'lb'.'nR'.'BZGQ'.'=','aW50cmFuZXQ=','Q0ludHJ'.'hbmV'.'0RXZlbnRIYW5k'.'bGVycw='.'=','U'.'1BSZWdpc'.'3Rlc'.'lVwZGF0ZWRJdG'.'Vt','aWJ'.'sb2Nr','T'.'2'.'5BZ'.'nR'.'lck'.'lCbG9j'.'a0V'.'sZW1lb'.'n'.'RVcGRhd'.'GU=','aW5'.'0cm'.'Fu'.'Z'.'XQ=','Q0l'.'udHJhbmV0'.'RXZlbnRI'.'YW'.'5kbGVyc'.'w==','U1BS'.'ZWdpc3RlclVw'.'ZGF0ZWRJ'.'dG'.'Vt','Q0lu'.'dHJh'.'bmV0U2hhcmVwb2lu'.'d'.'D'.'o6'.'QWdlbn'.'R'.'Ma'.'XN'.'0cygpOw==','aW50'.'cm'.'FuZXQ'.'=','Q0l'.'udHJhbmV0U2hhcmVwb'.'2'.'ludDo'.'6'.'QWdlbnRRdW'.'V1ZSg'.'pOw'.'==','aW50cmFu'.'ZXQ'.'=','Q0ludHJh'.'bmV0U2hhcm'.'Vwb2lud'.'D'.'o6QWdlb'.'nRVcGR'.'hdGUo'.'KTs'.'=','a'.'W50c'.'mFuZXQ=','Y'.'3'.'Jt','b'.'WFpb'.'g='.'=','T25CZWZvcm'.'VQcm'.'9s'.'b2c=',''.'b'.'W'.'F'.'pb'.'g==','Q'.'1dpem'.'Fy'.'ZFNvbFBhbmVsSW50cmFuZXQ'.'=','U2'.'hvd1Bh'.'bmVs','L21vZH'.'VsZXMvaW50cm'.'FuZXQvcGFuZWxfYn'.'V0d'.'G9'.'uL'.'nB'.'oc'.'A='.'=','R'.'U5D'.'T0'.'RF','WQ==');return base64_decode($_1619453283[$_214536514]);}};$GLOBALS['____346423011'][0](___526600228(0), ___526600228(1));class CBXFeatures{ private static $_504635618= 30; private static $_1534534909= array( "Portal" => array( "CompanyCalendar", "CompanyPhoto", "CompanyVideo", "CompanyCareer", "StaffChanges", "StaffAbsence", "CommonDocuments", "MeetingRoomBookingSystem", "Wiki", "Learning", "Vote", "WebLink", "Subscribe", "Friends", "PersonalFiles", "PersonalBlog", "PersonalPhoto", "PersonalForum", "Blog", "Forum", "Gallery", "Board", "MicroBlog", "WebMessenger",), "Communications" => array( "Tasks", "Calendar", "Workgroups", "Jabber", "VideoConference", "Extranet", "SMTP", "Requests", "DAV", "intranet_sharepoint", "timeman", "Idea", "Meeting", "EventList", "Salary", "XDImport",), "Enterprise" => array( "BizProc", "Lists", "Support", "Analytics", "crm", "Controller",), "Holding" => array( "Cluster", "MultiSites",),); private static $_1110622911= false; private static $_1334740129= false; private static function __250462040(){ if(self::$_1110622911 == false){ self::$_1110622911= array(); foreach(self::$_1534534909 as $_1714656542 => $_295372433){ foreach($_295372433 as $_1759505400) self::$_1110622911[$_1759505400]= $_1714656542;}} if(self::$_1334740129 == false){ self::$_1334740129= array(); $_2070198851= COption::GetOptionString(___526600228(2), ___526600228(3), ___526600228(4)); if($GLOBALS['____346423011'][1]($_2070198851)> min(136,0,45.333333333333)){ $_2070198851= $GLOBALS['____346423011'][2]($_2070198851); self::$_1334740129= $GLOBALS['____346423011'][3]($_2070198851); if(!$GLOBALS['____346423011'][4](self::$_1334740129)) self::$_1334740129= array();} if($GLOBALS['____346423011'][5](self::$_1334740129) <=(878-2*439)) self::$_1334740129= array(___526600228(5) => array(), ___526600228(6) => array());}} public static function InitiateEditionsSettings($_1488893714){ self::__250462040(); $_237654751= array(); foreach(self::$_1534534909 as $_1714656542 => $_295372433){ $_2008506992= $GLOBALS['____346423011'][6]($_1714656542, $_1488893714); self::$_1334740129[___526600228(7)][$_1714656542]=($_2008506992? array(___526600228(8)): array(___526600228(9))); foreach($_295372433 as $_1759505400){ self::$_1334740129[___526600228(10)][$_1759505400]= $_2008506992; if(!$_2008506992) $_237654751[]= array($_1759505400, false);}} $_521849886= $GLOBALS['____346423011'][7](self::$_1334740129); $_521849886= $GLOBALS['____346423011'][8]($_521849886); COption::SetOptionString(___526600228(11), ___526600228(12), $_521849886); foreach($_237654751 as $_192949728) self::__85791554($_192949728[(860-2*430)], $_192949728[round(0+0.25+0.25+0.25+0.25)]);} public static function IsFeatureEnabled($_1759505400){ if($GLOBALS['____346423011'][9]($_1759505400) <= 0) return true; self::__250462040(); if(!$GLOBALS['____346423011'][10]($_1759505400, self::$_1110622911)) return true; if(self::$_1110622911[$_1759505400] == ___526600228(13)) $_671806960= array(___526600228(14)); elseif($GLOBALS['____346423011'][11](self::$_1110622911[$_1759505400], self::$_1334740129[___526600228(15)])) $_671806960= self::$_1334740129[___526600228(16)][self::$_1110622911[$_1759505400]]; else $_671806960= array(___526600228(17)); if($_671806960[(182*2-364)] != ___526600228(18) && $_671806960[(166*2-332)] != ___526600228(19)){ return false;} elseif($_671806960[(1176/2-588)] == ___526600228(20)){ if($_671806960[round(0+0.2+0.2+0.2+0.2+0.2)]< $GLOBALS['____346423011'][12]((872-2*436),(216*2-432), min(34,0,11.333333333333), Date(___526600228(21)), $GLOBALS['____346423011'][13](___526600228(22))- self::$_504635618, $GLOBALS['____346423011'][14](___526600228(23)))){ if(!isset($_671806960[round(0+1+1)]) ||!$_671806960[round(0+0.66666666666667+0.66666666666667+0.66666666666667)]) self::__1215579767(self::$_1110622911[$_1759505400]); return false;}} return!$GLOBALS['____346423011'][15]($_1759505400, self::$_1334740129[___526600228(24)]) || self::$_1334740129[___526600228(25)][$_1759505400];} public static function IsFeatureInstalled($_1759505400){ if($GLOBALS['____346423011'][16]($_1759505400) <= 0) return true; self::__250462040(); return($GLOBALS['____346423011'][17]($_1759505400, self::$_1334740129[___526600228(26)]) && self::$_1334740129[___526600228(27)][$_1759505400]);} public static function IsFeatureEditable($_1759505400){ if($GLOBALS['____346423011'][18]($_1759505400) <= 0) return true; self::__250462040(); if(!$GLOBALS['____346423011'][19]($_1759505400, self::$_1110622911)) return true; if(self::$_1110622911[$_1759505400] == ___526600228(28)) $_671806960= array(___526600228(29)); elseif($GLOBALS['____346423011'][20](self::$_1110622911[$_1759505400], self::$_1334740129[___526600228(30)])) $_671806960= self::$_1334740129[___526600228(31)][self::$_1110622911[$_1759505400]]; else $_671806960= array(___526600228(32)); if($_671806960[(180*2-360)] != ___526600228(33) && $_671806960[min(224,0,74.666666666667)] != ___526600228(34)){ return false;} elseif($_671806960[(240*2-480)] == ___526600228(35)){ if($_671806960[round(0+1)]< $GLOBALS['____346423011'][21]((126*2-252), min(228,0,76),(954-2*477), Date(___526600228(36)), $GLOBALS['____346423011'][22](___526600228(37))- self::$_504635618, $GLOBALS['____346423011'][23](___526600228(38)))){ if(!isset($_671806960[round(0+0.4+0.4+0.4+0.4+0.4)]) ||!$_671806960[round(0+2)]) self::__1215579767(self::$_1110622911[$_1759505400]); return false;}} return true;} private static function __85791554($_1759505400, $_794067544){ if($GLOBALS['____346423011'][24]("CBXFeatures", "On".$_1759505400."SettingsChange")) $GLOBALS['____346423011'][25](array("CBXFeatures", "On".$_1759505400."SettingsChange"), array($_1759505400, $_794067544)); $_95146538= $GLOBALS['_____651066800'][0](___526600228(39), ___526600228(40).$_1759505400.___526600228(41)); while($_820291690= $_95146538->Fetch()) $GLOBALS['_____651066800'][1]($_820291690, array($_1759505400, $_794067544));} public static function SetFeatureEnabled($_1759505400, $_794067544= true, $_640741965= true){ if($GLOBALS['____346423011'][26]($_1759505400) <= 0) return; if(!self::IsFeatureEditable($_1759505400)) $_794067544= false; $_794067544=($_794067544? true: false); self::__250462040(); $_1434736820=(!$GLOBALS['____346423011'][27]($_1759505400, self::$_1334740129[___526600228(42)]) && $_794067544 || $GLOBALS['____346423011'][28]($_1759505400, self::$_1334740129[___526600228(43)]) && $_794067544 != self::$_1334740129[___526600228(44)][$_1759505400]); self::$_1334740129[___526600228(45)][$_1759505400]= $_794067544; $_521849886= $GLOBALS['____346423011'][29](self::$_1334740129); $_521849886= $GLOBALS['____346423011'][30]($_521849886); COption::SetOptionString(___526600228(46), ___526600228(47), $_521849886); if($_1434736820 && $_640741965) self::__85791554($_1759505400, $_794067544);} private static function __1215579767($_1714656542){ if($GLOBALS['____346423011'][31]($_1714656542) <= 0 || $_1714656542 == "Portal") return; self::__250462040(); if(!$GLOBALS['____346423011'][32]($_1714656542, self::$_1334740129[___526600228(48)]) || $GLOBALS['____346423011'][33]($_1714656542, self::$_1334740129[___526600228(49)]) && self::$_1334740129[___526600228(50)][$_1714656542][min(184,0,61.333333333333)] != ___526600228(51)) return; if(isset(self::$_1334740129[___526600228(52)][$_1714656542][round(0+0.4+0.4+0.4+0.4+0.4)]) && self::$_1334740129[___526600228(53)][$_1714656542][round(0+0.4+0.4+0.4+0.4+0.4)]) return; $_237654751= array(); if($GLOBALS['____346423011'][34]($_1714656542, self::$_1534534909) && $GLOBALS['____346423011'][35](self::$_1534534909[$_1714656542])){ foreach(self::$_1534534909[$_1714656542] as $_1759505400){ if($GLOBALS['____346423011'][36]($_1759505400, self::$_1334740129[___526600228(54)]) && self::$_1334740129[___526600228(55)][$_1759505400]){ self::$_1334740129[___526600228(56)][$_1759505400]= false; $_237654751[]= array($_1759505400, false);}} self::$_1334740129[___526600228(57)][$_1714656542][round(0+0.5+0.5+0.5+0.5)]= true;} $_521849886= $GLOBALS['____346423011'][37](self::$_1334740129); $_521849886= $GLOBALS['____346423011'][38]($_521849886); COption::SetOptionString(___526600228(58), ___526600228(59), $_521849886); foreach($_237654751 as $_192949728) self::__85791554($_192949728[(1404/2-702)], $_192949728[round(0+0.25+0.25+0.25+0.25)]);} public static function ModifyFeaturesSettings($_1488893714, $_295372433){ self::__250462040(); foreach($_1488893714 as $_1714656542 => $_273898424) self::$_1334740129[___526600228(60)][$_1714656542]= $_273898424; $_237654751= array(); foreach($_295372433 as $_1759505400 => $_794067544){ if(!$GLOBALS['____346423011'][39]($_1759505400, self::$_1334740129[___526600228(61)]) && $_794067544 || $GLOBALS['____346423011'][40]($_1759505400, self::$_1334740129[___526600228(62)]) && $_794067544 != self::$_1334740129[___526600228(63)][$_1759505400]) $_237654751[]= array($_1759505400, $_794067544); self::$_1334740129[___526600228(64)][$_1759505400]= $_794067544;} $_521849886= $GLOBALS['____346423011'][41](self::$_1334740129); $_521849886= $GLOBALS['____346423011'][42]($_521849886); COption::SetOptionString(___526600228(65), ___526600228(66), $_521849886); self::$_1334740129= false; foreach($_237654751 as $_192949728) self::__85791554($_192949728[(952-2*476)], $_192949728[round(0+0.5+0.5)]);} public static function SaveFeaturesSettings($_1629216241, $_1110497724){ self::__250462040(); $_422186904= array(___526600228(67) => array(), ___526600228(68) => array()); if(!$GLOBALS['____346423011'][43]($_1629216241)) $_1629216241= array(); if(!$GLOBALS['____346423011'][44]($_1110497724)) $_1110497724= array(); if(!$GLOBALS['____346423011'][45](___526600228(69), $_1629216241)) $_1629216241[]= ___526600228(70); foreach(self::$_1534534909 as $_1714656542 => $_295372433){ if($GLOBALS['____346423011'][46]($_1714656542, self::$_1334740129[___526600228(71)])) $_645302208= self::$_1334740129[___526600228(72)][$_1714656542]; else $_645302208=($_1714656542 == ___526600228(73))? array(___526600228(74)): array(___526600228(75)); if($_645302208[(1060/2-530)] == ___526600228(76) || $_645302208[(1192/2-596)] == ___526600228(77)){ $_422186904[___526600228(78)][$_1714656542]= $_645302208;} else{ if($GLOBALS['____346423011'][47]($_1714656542, $_1629216241)) $_422186904[___526600228(79)][$_1714656542]= array(___526600228(80), $GLOBALS['____346423011'][48]((1100/2-550),(150*2-300), min(34,0,11.333333333333), $GLOBALS['____346423011'][49](___526600228(81)), $GLOBALS['____346423011'][50](___526600228(82)), $GLOBALS['____346423011'][51](___526600228(83)))); else $_422186904[___526600228(84)][$_1714656542]= array(___526600228(85));}} $_237654751= array(); foreach(self::$_1110622911 as $_1759505400 => $_1714656542){ if($_422186904[___526600228(86)][$_1714656542][(992-2*496)] != ___526600228(87) && $_422186904[___526600228(88)][$_1714656542][(1084/2-542)] != ___526600228(89)){ $_422186904[___526600228(90)][$_1759505400]= false;} else{ if($_422186904[___526600228(91)][$_1714656542][(1348/2-674)] == ___526600228(92) && $_422186904[___526600228(93)][$_1714656542][round(0+0.25+0.25+0.25+0.25)]< $GLOBALS['____346423011'][52]((826-2*413), min(240,0,80),(189*2-378), Date(___526600228(94)), $GLOBALS['____346423011'][53](___526600228(95))- self::$_504635618, $GLOBALS['____346423011'][54](___526600228(96)))) $_422186904[___526600228(97)][$_1759505400]= false; else $_422186904[___526600228(98)][$_1759505400]= $GLOBALS['____346423011'][55]($_1759505400, $_1110497724); if(!$GLOBALS['____346423011'][56]($_1759505400, self::$_1334740129[___526600228(99)]) && $_422186904[___526600228(100)][$_1759505400] || $GLOBALS['____346423011'][57]($_1759505400, self::$_1334740129[___526600228(101)]) && $_422186904[___526600228(102)][$_1759505400] != self::$_1334740129[___526600228(103)][$_1759505400]) $_237654751[]= array($_1759505400, $_422186904[___526600228(104)][$_1759505400]);}} $_521849886= $GLOBALS['____346423011'][58]($_422186904); $_521849886= $GLOBALS['____346423011'][59]($_521849886); COption::SetOptionString(___526600228(105), ___526600228(106), $_521849886); self::$_1334740129= false; foreach($_237654751 as $_192949728) self::__85791554($_192949728[min(146,0,48.666666666667)], $_192949728[round(0+0.2+0.2+0.2+0.2+0.2)]);} public static function GetFeaturesList(){ self::__250462040(); $_1373281065= array(); foreach(self::$_1534534909 as $_1714656542 => $_295372433){ if($GLOBALS['____346423011'][60]($_1714656542, self::$_1334740129[___526600228(107)])) $_645302208= self::$_1334740129[___526600228(108)][$_1714656542]; else $_645302208=($_1714656542 == ___526600228(109))? array(___526600228(110)): array(___526600228(111)); $_1373281065[$_1714656542]= array( ___526600228(112) => $_645302208[min(156,0,52)], ___526600228(113) => $_645302208[round(0+0.5+0.5)], ___526600228(114) => array(),); $_1373281065[$_1714656542][___526600228(115)]= false; if($_1373281065[$_1714656542][___526600228(116)] == ___526600228(117)){ $_1373281065[$_1714656542][___526600228(118)]= $GLOBALS['____346423011'][61](($GLOBALS['____346423011'][62]()- $_1373281065[$_1714656542][___526600228(119)])/ round(0+28800+28800+28800)); if($_1373281065[$_1714656542][___526600228(120)]> self::$_504635618) $_1373281065[$_1714656542][___526600228(121)]= true;} foreach($_295372433 as $_1759505400) $_1373281065[$_1714656542][___526600228(122)][$_1759505400]=(!$GLOBALS['____346423011'][63]($_1759505400, self::$_1334740129[___526600228(123)]) || self::$_1334740129[___526600228(124)][$_1759505400]);} return $_1373281065;} private static function __1415832007($_1628448334, $_649525181){ if(IsModuleInstalled($_1628448334) == $_649525181) return true; $_1299335563= $_SERVER[___526600228(125)].___526600228(126).$_1628448334.___526600228(127); if(!$GLOBALS['____346423011'][64]($_1299335563)) return false; include_once($_1299335563); $_629977471= $GLOBALS['____346423011'][65](___526600228(128), ___526600228(129), $_1628448334); if(!$GLOBALS['____346423011'][66]($_629977471)) return false; $_1666531885= new $_629977471; if($_649525181){ if(!$_1666531885->InstallDB()) return false; $_1666531885->InstallEvents(); if(!$_1666531885->InstallFiles()) return false;} else{ if(CModule::IncludeModule(___526600228(130))) CSearch::DeleteIndex($_1628448334); UnRegisterModule($_1628448334);} return true;} protected static function OnRequestsSettingsChange($_1759505400, $_794067544){ self::__1415832007("form", $_794067544);} protected static function OnLearningSettingsChange($_1759505400, $_794067544){ self::__1415832007("learning", $_794067544);} protected static function OnJabberSettingsChange($_1759505400, $_794067544){ self::__1415832007("xmpp", $_794067544);} protected static function OnVideoConferenceSettingsChange($_1759505400, $_794067544){ self::__1415832007("video", $_794067544);} protected static function OnBizProcSettingsChange($_1759505400, $_794067544){ self::__1415832007("bizprocdesigner", $_794067544);} protected static function OnListsSettingsChange($_1759505400, $_794067544){ self::__1415832007("lists", $_794067544);} protected static function OnWikiSettingsChange($_1759505400, $_794067544){ self::__1415832007("wiki", $_794067544);} protected static function OnSupportSettingsChange($_1759505400, $_794067544){ self::__1415832007("support", $_794067544);} protected static function OnControllerSettingsChange($_1759505400, $_794067544){ self::__1415832007("controller", $_794067544);} protected static function OnAnalyticsSettingsChange($_1759505400, $_794067544){ self::__1415832007("statistic", $_794067544);} protected static function OnVoteSettingsChange($_1759505400, $_794067544){ self::__1415832007("vote", $_794067544);} protected static function OnFriendsSettingsChange($_1759505400, $_794067544){ if($_794067544) $_262548077= "Y"; else $_262548077= ___526600228(131); $_151135899= CSite::GetList(($_2008506992= ___526600228(132)),($_1478226043= ___526600228(133)), array(___526600228(134) => ___526600228(135))); while($_653907497= $_151135899->Fetch()){ if(COption::GetOptionString(___526600228(136), ___526600228(137), ___526600228(138), $_653907497[___526600228(139)]) != $_262548077){ COption::SetOptionString(___526600228(140), ___526600228(141), $_262548077, false, $_653907497[___526600228(142)]); COption::SetOptionString(___526600228(143), ___526600228(144), $_262548077);}}} protected static function OnMicroBlogSettingsChange($_1759505400, $_794067544){ if($_794067544) $_262548077= "Y"; else $_262548077= ___526600228(145); $_151135899= CSite::GetList(($_2008506992= ___526600228(146)),($_1478226043= ___526600228(147)), array(___526600228(148) => ___526600228(149))); while($_653907497= $_151135899->Fetch()){ if(COption::GetOptionString(___526600228(150), ___526600228(151), ___526600228(152), $_653907497[___526600228(153)]) != $_262548077){ COption::SetOptionString(___526600228(154), ___526600228(155), $_262548077, false, $_653907497[___526600228(156)]); COption::SetOptionString(___526600228(157), ___526600228(158), $_262548077);} if(COption::GetOptionString(___526600228(159), ___526600228(160), ___526600228(161), $_653907497[___526600228(162)]) != $_262548077){ COption::SetOptionString(___526600228(163), ___526600228(164), $_262548077, false, $_653907497[___526600228(165)]); COption::SetOptionString(___526600228(166), ___526600228(167), $_262548077);}}} protected static function OnPersonalFilesSettingsChange($_1759505400, $_794067544){ if($_794067544) $_262548077= "Y"; else $_262548077= ___526600228(168); $_151135899= CSite::GetList(($_2008506992= ___526600228(169)),($_1478226043= ___526600228(170)), array(___526600228(171) => ___526600228(172))); while($_653907497= $_151135899->Fetch()){ if(COption::GetOptionString(___526600228(173), ___526600228(174), ___526600228(175), $_653907497[___526600228(176)]) != $_262548077){ COption::SetOptionString(___526600228(177), ___526600228(178), $_262548077, false, $_653907497[___526600228(179)]); COption::SetOptionString(___526600228(180), ___526600228(181), $_262548077);}}} protected static function OnPersonalBlogSettingsChange($_1759505400, $_794067544){ if($_794067544) $_262548077= "Y"; else $_262548077= ___526600228(182); $_151135899= CSite::GetList(($_2008506992= ___526600228(183)),($_1478226043= ___526600228(184)), array(___526600228(185) => ___526600228(186))); while($_653907497= $_151135899->Fetch()){ if(COption::GetOptionString(___526600228(187), ___526600228(188), ___526600228(189), $_653907497[___526600228(190)]) != $_262548077){ COption::SetOptionString(___526600228(191), ___526600228(192), $_262548077, false, $_653907497[___526600228(193)]); COption::SetOptionString(___526600228(194), ___526600228(195), $_262548077);}}} protected static function OnPersonalPhotoSettingsChange($_1759505400, $_794067544){ if($_794067544) $_262548077= "Y"; else $_262548077= ___526600228(196); $_151135899= CSite::GetList(($_2008506992= ___526600228(197)),($_1478226043= ___526600228(198)), array(___526600228(199) => ___526600228(200))); while($_653907497= $_151135899->Fetch()){ if(COption::GetOptionString(___526600228(201), ___526600228(202), ___526600228(203), $_653907497[___526600228(204)]) != $_262548077){ COption::SetOptionString(___526600228(205), ___526600228(206), $_262548077, false, $_653907497[___526600228(207)]); COption::SetOptionString(___526600228(208), ___526600228(209), $_262548077);}}} protected static function OnPersonalForumSettingsChange($_1759505400, $_794067544){ if($_794067544) $_262548077= "Y"; else $_262548077= ___526600228(210); $_151135899= CSite::GetList(($_2008506992= ___526600228(211)),($_1478226043= ___526600228(212)), array(___526600228(213) => ___526600228(214))); while($_653907497= $_151135899->Fetch()){ if(COption::GetOptionString(___526600228(215), ___526600228(216), ___526600228(217), $_653907497[___526600228(218)]) != $_262548077){ COption::SetOptionString(___526600228(219), ___526600228(220), $_262548077, false, $_653907497[___526600228(221)]); COption::SetOptionString(___526600228(222), ___526600228(223), $_262548077);}}} protected static function OnTasksSettingsChange($_1759505400, $_794067544){ if($_794067544) $_262548077= "Y"; else $_262548077= ___526600228(224); $_151135899= CSite::GetList(($_2008506992= ___526600228(225)),($_1478226043= ___526600228(226)), array(___526600228(227) => ___526600228(228))); while($_653907497= $_151135899->Fetch()){ if(COption::GetOptionString(___526600228(229), ___526600228(230), ___526600228(231), $_653907497[___526600228(232)]) != $_262548077){ COption::SetOptionString(___526600228(233), ___526600228(234), $_262548077, false, $_653907497[___526600228(235)]); COption::SetOptionString(___526600228(236), ___526600228(237), $_262548077);} if(COption::GetOptionString(___526600228(238), ___526600228(239), ___526600228(240), $_653907497[___526600228(241)]) != $_262548077){ COption::SetOptionString(___526600228(242), ___526600228(243), $_262548077, false, $_653907497[___526600228(244)]); COption::SetOptionString(___526600228(245), ___526600228(246), $_262548077);}} self::__1415832007(___526600228(247), $_794067544);} protected static function OnCalendarSettingsChange($_1759505400, $_794067544){ if($_794067544) $_262548077= "Y"; else $_262548077= ___526600228(248); $_151135899= CSite::GetList(($_2008506992= ___526600228(249)),($_1478226043= ___526600228(250)), array(___526600228(251) => ___526600228(252))); while($_653907497= $_151135899->Fetch()){ if(COption::GetOptionString(___526600228(253), ___526600228(254), ___526600228(255), $_653907497[___526600228(256)]) != $_262548077){ COption::SetOptionString(___526600228(257), ___526600228(258), $_262548077, false, $_653907497[___526600228(259)]); COption::SetOptionString(___526600228(260), ___526600228(261), $_262548077);} if(COption::GetOptionString(___526600228(262), ___526600228(263), ___526600228(264), $_653907497[___526600228(265)]) != $_262548077){ COption::SetOptionString(___526600228(266), ___526600228(267), $_262548077, false, $_653907497[___526600228(268)]); COption::SetOptionString(___526600228(269), ___526600228(270), $_262548077);}}} protected static function OnSMTPSettingsChange($_1759505400, $_794067544){ self::__1415832007("mail", $_794067544);} protected static function OnExtranetSettingsChange($_1759505400, $_794067544){ $_974065427= COption::GetOptionString("extranet", "extranet_site", ""); if($_974065427){ $_1615149317= new CSite; $_1615149317->Update($_974065427, array(___526600228(271) =>($_794067544? ___526600228(272): ___526600228(273))));} self::__1415832007(___526600228(274), $_794067544);} protected static function OnDAVSettingsChange($_1759505400, $_794067544){ self::__1415832007("dav", $_794067544);} protected static function OntimemanSettingsChange($_1759505400, $_794067544){ self::__1415832007("timeman", $_794067544);} protected static function Onintranet_sharepointSettingsChange($_1759505400, $_794067544){ if($_794067544){ RegisterModuleDependences("iblock", "OnAfterIBlockElementAdd", "intranet", "CIntranetEventHandlers", "SPRegisterUpdatedItem"); RegisterModuleDependences(___526600228(275), ___526600228(276), ___526600228(277), ___526600228(278), ___526600228(279)); CAgent::AddAgent(___526600228(280), ___526600228(281), ___526600228(282), round(0+166.66666666667+166.66666666667+166.66666666667)); CAgent::AddAgent(___526600228(283), ___526600228(284), ___526600228(285), round(0+150+150)); CAgent::AddAgent(___526600228(286), ___526600228(287), ___526600228(288), round(0+720+720+720+720+720));} else{ UnRegisterModuleDependences(___526600228(289), ___526600228(290), ___526600228(291), ___526600228(292), ___526600228(293)); UnRegisterModuleDependences(___526600228(294), ___526600228(295), ___526600228(296), ___526600228(297), ___526600228(298)); CAgent::RemoveAgent(___526600228(299), ___526600228(300)); CAgent::RemoveAgent(___526600228(301), ___526600228(302)); CAgent::RemoveAgent(___526600228(303), ___526600228(304));}} protected static function OncrmSettingsChange($_1759505400, $_794067544){ if($_794067544) COption::SetOptionString("crm", "form_features", "Y"); self::__1415832007(___526600228(305), $_794067544);} protected static function OnClusterSettingsChange($_1759505400, $_794067544){ self::__1415832007("cluster", $_794067544);} protected static function OnMultiSitesSettingsChange($_1759505400, $_794067544){ if($_794067544) RegisterModuleDependences("main", "OnBeforeProlog", "main", "CWizardSolPanelIntranet", "ShowPanel", 100, "/modules/intranet/panel_button.php"); else UnRegisterModuleDependences(___526600228(306), ___526600228(307), ___526600228(308), ___526600228(309), ___526600228(310), ___526600228(311));} protected static function OnIdeaSettingsChange($_1759505400, $_794067544){ self::__1415832007("idea", $_794067544);} protected static function OnMeetingSettingsChange($_1759505400, $_794067544){ self::__1415832007("meeting", $_794067544);} protected static function OnXDImportSettingsChange($_1759505400, $_794067544){ self::__1415832007("xdimport", $_794067544);}} $GLOBALS['____346423011'][67](___526600228(312), ___526600228(313));/**/			//Do not remove this

//component 2.0 template engines
$GLOBALS["arCustomTemplateEngines"] = array();

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/urlrewriter.php");

/**
 * Defined in dbconn.php
 * @param string $DBType
 */

\Bitrix\Main\Loader::registerAutoLoadClasses(
	"main",
	array(
		"CSiteTemplate" => "classes/general/site_template.php",
		"CBitrixComponent" => "classes/general/component.php",
		"CComponentEngine" => "classes/general/component_engine.php",
		"CComponentAjax" => "classes/general/component_ajax.php",
		"CBitrixComponentTemplate" => "classes/general/component_template.php",
		"CComponentUtil" => "classes/general/component_util.php",
		"CControllerClient" => "classes/general/controller_member.php",
		"PHPParser" => "classes/general/php_parser.php",
		"CDiskQuota" => "classes/".$DBType."/quota.php",
		"CEventLog" => "classes/general/event_log.php",
		"CEventMain" => "classes/general/event_log.php",
		"CAdminFileDialog" => "classes/general/file_dialog.php",
		"WLL_User" => "classes/general/liveid.php",
		"WLL_ConsentToken" => "classes/general/liveid.php",
		"WindowsLiveLogin" => "classes/general/liveid.php",
		"CAllFile" => "classes/general/file.php",
		"CFile" => "classes/".$DBType."/file.php",
		"CTempFile" => "classes/general/file_temp.php",
		"CFavorites" => "classes/".$DBType."/favorites.php",
		"CUserOptions" => "classes/general/user_options.php",
		"CGridOptions" => "classes/general/grids.php",
		"CUndo" => "/classes/general/undo.php",
		"CAutoSave" => "/classes/general/undo.php",
		"CRatings" => "classes/".$DBType."/ratings.php",
		"CRatingsComponentsMain" => "classes/".$DBType."/ratings_components.php",
		"CRatingRule" => "classes/general/rating_rule.php",
		"CRatingRulesMain" => "classes/".$DBType."/rating_rules.php",
		"CTopPanel" => "public/top_panel.php",
		"CEditArea" => "public/edit_area.php",
		"CComponentPanel" => "public/edit_area.php",
		"CTextParser" => "classes/general/textparser.php",
		"CPHPCacheFiles" => "classes/general/cache_files.php",
		"CDataXML" => "classes/general/xml.php",
		"CXMLFileStream" => "classes/general/xml.php",
		"CRsaProvider" => "classes/general/rsasecurity.php",
		"CRsaSecurity" => "classes/general/rsasecurity.php",
		"CRsaBcmathProvider" => "classes/general/rsabcmath.php",
		"CRsaOpensslProvider" => "classes/general/rsaopenssl.php",
		"CASNReader" => "classes/general/asn.php",
		"CBXShortUri" => "classes/".$DBType."/short_uri.php",
		"CFinder" => "classes/general/finder.php",
		"CAccess" => "classes/general/access.php",
		"CAuthProvider" => "classes/general/authproviders.php",
		"IProviderInterface" => "classes/general/authproviders.php",
		"CGroupAuthProvider" => "classes/general/authproviders.php",
		"CUserAuthProvider" => "classes/general/authproviders.php",
		"CTableSchema" => "classes/general/table_schema.php",
		"CCSVData" => "classes/general/csv_data.php",
		"CSmile" => "classes/general/smile.php",
		"CSmileGallery" => "classes/general/smile.php",
		"CSmileSet" => "classes/general/smile.php",
		"CGlobalCounter" => "classes/general/global_counter.php",
		"CUserCounter" => "classes/".$DBType."/user_counter.php",
		"CUserCounterPage" => "classes/".$DBType."/user_counter.php",
		"CHotKeys" => "classes/general/hot_keys.php",
		"CHotKeysCode" => "classes/general/hot_keys.php",
		"CBXSanitizer" => "classes/general/sanitizer.php",
		"CBXArchive" => "classes/general/archive.php",
		"CAdminNotify" => "classes/general/admin_notify.php",
		"CBXFavAdmMenu" => "classes/general/favorites.php",
		"CAdminInformer" => "classes/general/admin_informer.php",
		"CSiteCheckerTest" => "classes/general/site_checker.php",
		"CSqlUtil" => "classes/general/sql_util.php",
		"CFileUploader" => "classes/general/uploader.php",
		"LPA" => "classes/general/lpa.php",
		"CAdminFilter" => "interface/admin_filter.php",
		"CAdminList" => "interface/admin_list.php",
		"CAdminUiList" => "interface/admin_ui_list.php",
		"CAdminUiResult" => "interface/admin_ui_list.php",
		"CAdminUiContextMenu" => "interface/admin_ui_list.php",
		"CAdminUiSorting" => "interface/admin_ui_list.php",
		"CAdminListRow" => "interface/admin_list.php",
		"CAdminTabControl" => "interface/admin_tabcontrol.php",
		"CAdminForm" => "interface/admin_form.php",
		"CAdminFormSettings" => "interface/admin_form.php",
		"CAdminTabControlDrag" => "interface/admin_tabcontrol_drag.php",
		"CAdminDraggableBlockEngine" => "interface/admin_tabcontrol_drag.php",
		"CJSPopup" => "interface/jspopup.php",
		"CJSPopupOnPage" => "interface/jspopup.php",
		"CAdminCalendar" => "interface/admin_calendar.php",
		"CAdminViewTabControl" => "interface/admin_viewtabcontrol.php",
		"CAdminTabEngine" => "interface/admin_tabengine.php",
		"CCaptcha" => "classes/general/captcha.php",
		"CMpNotifications" => "classes/general/mp_notifications.php",

		//deprecated
		"CHTMLPagesCache" => "lib/composite/helper.php",
		"StaticHtmlMemcachedResponse" => "lib/composite/responder.php",
		"StaticHtmlFileResponse" => "lib/composite/responder.php",
		"Bitrix\\Main\\Page\\Frame" => "lib/composite/engine.php",
		"Bitrix\\Main\\Page\\FrameStatic" => "lib/composite/staticarea.php",
		"Bitrix\\Main\\Page\\FrameBuffered" => "lib/composite/bufferarea.php",
		"Bitrix\\Main\\Page\\FrameHelper" => "lib/composite/bufferarea.php",
		"Bitrix\\Main\\Data\\StaticHtmlCache" => "lib/composite/page.php",
		"Bitrix\\Main\\Data\\StaticHtmlStorage" => "lib/composite/data/abstractstorage.php",
		"Bitrix\\Main\\Data\\StaticHtmlFileStorage" => "lib/composite/data/filestorage.php",
		"Bitrix\\Main\\Data\\StaticHtmlMemcachedStorage" => "lib/composite/data/memcachedstorage.php",
		"Bitrix\\Main\\Data\\StaticCacheProvider" => "lib/composite/data/cacheprovider.php",
		"Bitrix\\Main\\Data\\AppCacheManifest" => "lib/composite/appcache.php",
	)
);

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/agent.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/user.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/event.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/menu.php");
AddEventHandler("main", "OnAfterEpilog", array("\\Bitrix\\Main\\Data\\ManagedCache", "finalize"));
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/usertype.php");

if(file_exists(($_fname = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/update_db_updater.php")))
{
	$US_HOST_PROCESS_MAIN = False;
	include($_fname);
}

if(file_exists(($_fname = $_SERVER["DOCUMENT_ROOT"]."/bitrix/init.php")))
	include_once($_fname);

if(($_fname = getLocalPath("php_interface/init.php", BX_PERSONAL_ROOT)) !== false)
	include_once($_SERVER["DOCUMENT_ROOT"].$_fname);

if(($_fname = getLocalPath("php_interface/".SITE_ID."/init.php", BX_PERSONAL_ROOT)) !== false)
	include_once($_SERVER["DOCUMENT_ROOT"].$_fname);

if(!defined("BX_FILE_PERMISSIONS"))
	define("BX_FILE_PERMISSIONS", 0644);
if(!defined("BX_DIR_PERMISSIONS"))
	define("BX_DIR_PERMISSIONS", 0755);

//global var, is used somewhere
$GLOBALS["sDocPath"] = $GLOBALS["APPLICATION"]->GetCurPage();

if((!(defined("STATISTIC_ONLY") && STATISTIC_ONLY && substr($GLOBALS["APPLICATION"]->GetCurPage(), 0, strlen(BX_ROOT."/admin/"))!=BX_ROOT."/admin/")) && COption::GetOptionString("main", "include_charset", "Y")=="Y" && strlen(LANG_CHARSET)>0)
	header("Content-Type: text/html; charset=".LANG_CHARSET);

if(COption::GetOptionString("main", "set_p3p_header", "Y")=="Y")
	header("P3P: policyref=\"/bitrix/p3p.xml\", CP=\"NON DSP COR CUR ADM DEV PSA PSD OUR UNR BUS UNI COM NAV INT DEM STA\"");

header("X-Powered-CMS: Bitrix Site Manager (".(LICENSE_KEY == "DEMO"? "DEMO" : md5("BITRIX".LICENSE_KEY."LICENCE")).")");
if (COption::GetOptionString("main", "update_devsrv", "") == "Y")
	header("X-DevSrv-CMS: Bitrix");

define("BX_CRONTAB_SUPPORT", defined("BX_CRONTAB"));

if(COption::GetOptionString("main", "check_agents", "Y")=="Y")
{
	define("START_EXEC_AGENTS_1", microtime());
	$GLOBALS["BX_STATE"] = "AG";
	$GLOBALS["DB"]->StartUsingMasterOnly();
	CAgent::CheckAgents();
	$GLOBALS["DB"]->StopUsingMasterOnly();
	define("START_EXEC_AGENTS_2", microtime());
	$GLOBALS["BX_STATE"] = "PB";
}

//session initialization
ini_set("session.cookie_httponly", "1");

if(($domain = \Bitrix\Main\Web\Cookie::getCookieDomain()) <> '')
{
	ini_set("session.cookie_domain", $domain);
}

if(COption::GetOptionString("security", "session", "N") === "Y"	&& CModule::IncludeModule("security"))
	CSecuritySession::Init();

session_start();

foreach (GetModuleEvents("main", "OnPageStart", true) as $arEvent)
	ExecuteModuleEventEx($arEvent);

//define global user object
$GLOBALS["USER"] = new CUser;

//session control from group policy
$arPolicy = $GLOBALS["USER"]->GetSecurityPolicy();
$currTime = time();
if(
	(
		//IP address changed
		$_SESSION['SESS_IP']
		&& strlen($arPolicy["SESSION_IP_MASK"])>0
		&& (
			(ip2long($arPolicy["SESSION_IP_MASK"]) & ip2long($_SESSION['SESS_IP']))
			!=
			(ip2long($arPolicy["SESSION_IP_MASK"]) & ip2long($_SERVER['REMOTE_ADDR']))
		)
	)
	||
	(
		//session timeout
		(!defined("BX_SKIP_SESSION_EXPAND") || BX_SKIP_SESSION_EXPAND === false)
		&& $arPolicy["SESSION_TIMEOUT"]>0
		&& $_SESSION['SESS_TIME']>0
		&& $currTime-$arPolicy["SESSION_TIMEOUT"]*60 > $_SESSION['SESS_TIME']
	)
	||
	(
		//session expander control
		isset($_SESSION["BX_SESSION_TERMINATE_TIME"])
		&& $_SESSION["BX_SESSION_TERMINATE_TIME"] > 0
		&& $currTime > $_SESSION["BX_SESSION_TERMINATE_TIME"]
	)
	||
	(
		//signed session
		isset($_SESSION["BX_SESSION_SIGN"])
		&& $_SESSION["BX_SESSION_SIGN"] <> bitrix_sess_sign()
	)
	||
	(
		//session manually expired, e.g. in $User->LoginHitByHash
		isSessionExpired()
	)
)
{
	$_SESSION = array();
	@session_destroy();

	//session_destroy cleans user sesssion handles in some PHP versions
	//see http://bugs.php.net/bug.php?id=32330 discussion
	if(COption::GetOptionString("security", "session", "N") === "Y"	&& CModule::IncludeModule("security"))
		CSecuritySession::Init();

	session_id(md5(uniqid(rand(), true)));
	session_start();
	$GLOBALS["USER"] = new CUser;
}
$_SESSION['SESS_IP'] = $_SERVER['REMOTE_ADDR'];
$_SESSION['SESS_TIME'] = time();
if(!isset($_SESSION["BX_SESSION_SIGN"]))
	$_SESSION["BX_SESSION_SIGN"] = bitrix_sess_sign();

//session control from security module
if(
	(COption::GetOptionString("main", "use_session_id_ttl", "N") == "Y")
	&& (COption::GetOptionInt("main", "session_id_ttl", 0) > 0)
	&& !defined("BX_SESSION_ID_CHANGE")
)
{
	if(!array_key_exists('SESS_ID_TIME', $_SESSION))
	{
		$_SESSION['SESS_ID_TIME'] = $_SESSION['SESS_TIME'];
	}
	elseif(($_SESSION['SESS_ID_TIME'] + COption::GetOptionInt("main", "session_id_ttl")) < $_SESSION['SESS_TIME'])
	{
		if(COption::GetOptionString("security", "session", "N") === "Y" && CModule::IncludeModule("security"))
		{
			CSecuritySession::UpdateSessID();
		}
		else
		{
			session_regenerate_id();
		}
		$_SESSION['SESS_ID_TIME'] = $_SESSION['SESS_TIME'];
	}
}

define("BX_STARTED", true);

if (isset($_SESSION['BX_ADMIN_LOAD_AUTH']))
{
	define('ADMIN_SECTION_LOAD_AUTH', 1);
	unset($_SESSION['BX_ADMIN_LOAD_AUTH']);
}

if(!defined("NOT_CHECK_PERMISSIONS") || NOT_CHECK_PERMISSIONS!==true)
{
	$bLogout = isset($_REQUEST["logout"]) && (strtolower($_REQUEST["logout"]) == "yes");

	if($bLogout && $GLOBALS["USER"]->IsAuthorized())
	{
		$GLOBALS["USER"]->Logout();
		LocalRedirect($GLOBALS["APPLICATION"]->GetCurPageParam('', array('logout')));
	}

	// authorize by cookies
	if(!$GLOBALS["USER"]->IsAuthorized())
	{
		$GLOBALS["USER"]->LoginByCookies();
	}

	$arAuthResult = false;

	//http basic and digest authorization
	if(($httpAuth = $GLOBALS["USER"]->LoginByHttpAuth()) !== null)
	{
		$arAuthResult = $httpAuth;
		$GLOBALS["APPLICATION"]->SetAuthResult($arAuthResult);
	}

	//Authorize user from authorization html form
	if(isset($_REQUEST["AUTH_FORM"]) && $_REQUEST["AUTH_FORM"] <> '')
	{
		$bRsaError = false;
		if(COption::GetOptionString('main', 'use_encrypted_auth', 'N') == 'Y')
		{
			//possible encrypted user password
			$sec = new CRsaSecurity();
			if(($arKeys = $sec->LoadKeys()))
			{
				$sec->SetKeys($arKeys);
				$errno = $sec->AcceptFromForm(array('USER_PASSWORD', 'USER_CONFIRM_PASSWORD'));
				if($errno == CRsaSecurity::ERROR_SESS_CHECK)
					$arAuthResult = array("MESSAGE"=>GetMessage("main_include_decode_pass_sess"), "TYPE"=>"ERROR");
				elseif($errno < 0)
					$arAuthResult = array("MESSAGE"=>GetMessage("main_include_decode_pass_err", array("#ERRCODE#"=>$errno)), "TYPE"=>"ERROR");

				if($errno < 0)
					$bRsaError = true;
			}
		}

		if($bRsaError == false)
		{
			if(!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
				$USER_LID = SITE_ID;
			else
				$USER_LID = false;

			if($_REQUEST["TYPE"] == "AUTH")
			{
				$arAuthResult = $GLOBALS["USER"]->Login($_REQUEST["USER_LOGIN"], $_REQUEST["USER_PASSWORD"], $_REQUEST["USER_REMEMBER"]);
			}
			elseif($_REQUEST["TYPE"] == "OTP")
			{
				$arAuthResult = $GLOBALS["USER"]->LoginByOtp($_REQUEST["USER_OTP"], $_REQUEST["OTP_REMEMBER"], $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
			}
			elseif($_REQUEST["TYPE"] == "SEND_PWD")
			{
				$arAuthResult = CUser::SendPassword($_REQUEST["USER_LOGIN"], $_REQUEST["USER_EMAIL"], $USER_LID, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"], $_REQUEST["USER_PHONE_NUMBER"]);
			}
			elseif($_SERVER['REQUEST_METHOD'] == 'POST' && $_REQUEST["TYPE"] == "CHANGE_PWD")
			{
				$arAuthResult = $GLOBALS["USER"]->ChangePassword($_REQUEST["USER_LOGIN"], $_REQUEST["USER_CHECKWORD"], $_REQUEST["USER_PASSWORD"], $_REQUEST["USER_CONFIRM_PASSWORD"], $USER_LID, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"], true, $_REQUEST["USER_PHONE_NUMBER"]);
			}
			elseif(COption::GetOptionString("main", "new_user_registration", "N") == "Y" && $_SERVER['REQUEST_METHOD'] == 'POST' && $_REQUEST["TYPE"] == "REGISTRATION" && (!defined("ADMIN_SECTION") || ADMIN_SECTION!==true))
			{
				$arAuthResult = $GLOBALS["USER"]->Register($_REQUEST["USER_LOGIN"], $_REQUEST["USER_NAME"], $_REQUEST["USER_LAST_NAME"], $_REQUEST["USER_PASSWORD"], $_REQUEST["USER_CONFIRM_PASSWORD"], $_REQUEST["USER_EMAIL"], $USER_LID, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"], false, $_REQUEST["USER_PHONE_NUMBER"]);
			}

			if($_REQUEST["TYPE"] == "AUTH" || $_REQUEST["TYPE"] == "OTP")
			{
				//special login form in the control panel
				if($arAuthResult === true && defined('ADMIN_SECTION') && ADMIN_SECTION === true)
				{
					//store cookies for next hit (see CMain::GetSpreadCookieHTML())
					$GLOBALS["APPLICATION"]->StoreCookies();
					$_SESSION['BX_ADMIN_LOAD_AUTH'] = true;

					CMain::FinalActions('<script type="text/javascript">window.onload=function(){top.BX.AUTHAGENT.setAuthResult(false);};</script>');
					die();
				}
			}
		}
		$GLOBALS["APPLICATION"]->SetAuthResult($arAuthResult);
	}
	elseif(!$GLOBALS["USER"]->IsAuthorized())
	{
		//Authorize by unique URL
		$GLOBALS["USER"]->LoginHitByHash();
	}
}

//logout or re-authorize the user if something importand has changed
$GLOBALS["USER"]->CheckAuthActions();

//magic short URI
if(defined("BX_CHECK_SHORT_URI") && BX_CHECK_SHORT_URI && CBXShortUri::CheckUri())
{
	//local redirect inside
	die();
}

//application password scope control
if(($applicationID = $GLOBALS["USER"]->GetParam("APPLICATION_ID")) !== null)
{
	$appManager = \Bitrix\Main\Authentication\ApplicationManager::getInstance();
	if($appManager->checkScope($applicationID) !== true)
	{
		$event = new \Bitrix\Main\Event("main", "onApplicationScopeError", Array('APPLICATION_ID' => $applicationID));
		$event->send();

		CHTTP::SetStatus("403 Forbidden");
		die();
	}
}

//define the site template
if(!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)
{
	$siteTemplate = "";
	if(is_string($_REQUEST["bitrix_preview_site_template"]) && $_REQUEST["bitrix_preview_site_template"] <> "" && $GLOBALS["USER"]->CanDoOperation('view_other_settings'))
	{
		//preview of site template
		$signer = new Bitrix\Main\Security\Sign\Signer();
		try
		{
			//protected by a sign
			$requestTemplate = $signer->unsign($_REQUEST["bitrix_preview_site_template"], "template_preview".bitrix_sessid());

			$aTemplates = CSiteTemplate::GetByID($requestTemplate);
			if($template = $aTemplates->Fetch())
			{
				$siteTemplate = $template["ID"];

				//preview of unsaved template
				if(isset($_GET['bx_template_preview_mode']) && $_GET['bx_template_preview_mode'] == 'Y' && $GLOBALS["USER"]->CanDoOperation('edit_other_settings'))
				{
					define("SITE_TEMPLATE_PREVIEW_MODE", true);
				}
			}
		}
		catch(\Bitrix\Main\Security\Sign\BadSignatureException $e)
		{
		}
	}
	if($siteTemplate == "")
	{
		$siteTemplate = CSite::GetCurTemplate();
	}
	define("SITE_TEMPLATE_ID", $siteTemplate);
	define("SITE_TEMPLATE_PATH", getLocalPath('templates/'.SITE_TEMPLATE_ID, BX_PERSONAL_ROOT));
}

//magic parameters: show page creation time
if(isset($_GET["show_page_exec_time"]))
{
	if($_GET["show_page_exec_time"]=="Y" || $_GET["show_page_exec_time"]=="N")
		$_SESSION["SESS_SHOW_TIME_EXEC"] = $_GET["show_page_exec_time"];
}

//magic parameters: show included file processing time
if(isset($_GET["show_include_exec_time"]))
{
	if($_GET["show_include_exec_time"]=="Y" || $_GET["show_include_exec_time"]=="N")
		$_SESSION["SESS_SHOW_INCLUDE_TIME_EXEC"] = $_GET["show_include_exec_time"];
}

//magic parameters: show include areas
if(isset($_GET["bitrix_include_areas"]) && $_GET["bitrix_include_areas"] <> "")
	$GLOBALS["APPLICATION"]->SetShowIncludeAreas($_GET["bitrix_include_areas"]=="Y");

//magic sound
if($GLOBALS["USER"]->IsAuthorized())
{
	$cookie_prefix = COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM');
	if(!isset($_COOKIE[$cookie_prefix.'_SOUND_LOGIN_PLAYED']))
		$GLOBALS["APPLICATION"]->set_cookie('SOUND_LOGIN_PLAYED', 'Y', 0);
}

//magic cache
\Bitrix\Main\Composite\Engine::shouldBeEnabled();

foreach(GetModuleEvents("main", "OnBeforeProlog", true) as $arEvent)
	ExecuteModuleEventEx($arEvent);

if((!defined("NOT_CHECK_PERMISSIONS") || NOT_CHECK_PERMISSIONS!==true) && (!defined("NOT_CHECK_FILE_PERMISSIONS") || NOT_CHECK_FILE_PERMISSIONS!==true))
{
	$real_path = $request->getScriptFile();

	if(!$GLOBALS["USER"]->CanDoFileOperation('fm_view_file', array(SITE_ID, $real_path)) || (defined("NEED_AUTH") && NEED_AUTH && !$GLOBALS["USER"]->IsAuthorized()))
	{
		/** @noinspection PhpUndefinedVariableInspection */
		if($GLOBALS["USER"]->IsAuthorized() && $arAuthResult["MESSAGE"] == '')
			$arAuthResult = array("MESSAGE"=>GetMessage("ACCESS_DENIED").' '.GetMessage("ACCESS_DENIED_FILE", array("#FILE#"=>$real_path)), "TYPE"=>"ERROR");

		if(defined("ADMIN_SECTION") && ADMIN_SECTION==true)
		{
			if ($_REQUEST["mode"]=="list" || $_REQUEST["mode"]=="settings")
			{
				echo "<script>top.location='".$GLOBALS["APPLICATION"]->GetCurPage()."?".DeleteParam(array("mode"))."';</script>";
				die();
			}
			elseif ($_REQUEST["mode"]=="frame")
			{
				echo "<script type=\"text/javascript\">
					var w = (opener? opener.window:parent.window);
					w.location.href='".$GLOBALS["APPLICATION"]->GetCurPage()."?".DeleteParam(array("mode"))."';
				</script>";
				die();
			}
			elseif(defined("MOBILE_APP_ADMIN") && MOBILE_APP_ADMIN==true)
			{
				echo json_encode(Array("status"=>"failed"));
				die();
			}
		}

		/** @noinspection PhpUndefinedVariableInspection */
		$GLOBALS["APPLICATION"]->AuthForm($arAuthResult);
	}
}

/*ZDUyZmZYWRhZjUzOWYzN2JiZmVlNjVlYzE5NzQ3NGRlMjcwMmE=*/$GLOBALS['____220152903']= array(base64_decode('bXR'.'fcmFuZA=='),base64_decode('ZX'.'hw'.'bG9k'.'ZQ'.'=='),base64_decode(''.'cGFjaw=='),base64_decode('bWQ1'),base64_decode('Y29u'.'c3'.'RhbnQ='),base64_decode('aGFzaF'.'9obWF'.'j'),base64_decode('c3RyY'.'21'.'w'),base64_decode(''.'aXNfb'.'2Jq'.'ZWN0'),base64_decode('Y2FsbF9'.'1c2V'.'yX2'.'Z1'.'b'.'mM='),base64_decode('Y2'.'Fs'.'bF91c2'.'VyX2Z1bm'.'M='),base64_decode('Y2'.'Fs'.'bF91'.'c2V'.'yX2Z1bmM='),base64_decode(''.'Y2F'.'sbF'.'91c2VyX2'.'Z1bmM='),base64_decode('Y'.'2FsbF'.'91c2VyX2Z1bm'.'M='));if(!function_exists(__NAMESPACE__.'\\___116307793')){function ___116307793($_133921560){static $_957007473= false; if($_957007473 == false) $_957007473=array('REI=','U0VM'.'RU'.'NUIFZBTFVFIEZS'.'T00gYl9vcHR'.'pb'.'24gV0'.'hFUkUgT'.'kFNR'.'T0nfl'.'B'.'BUkFN'.'X01B'.'WF9VU0VSUycgQU5E'.'I'.'E1P'.'RF'.'VM'.'RV'.'9'.'JRD0nbWF'.'pbicg'.'QU'.'5EIFNJVEV'.'fSU'.'QgS'.'VMgTlVMTA==','VkFMVU'.'U'.'=','Lg='.'=','SCo'.'=','Yml0c'.'ml4','TEl'.'DR'.'U5TR'.'V'.'9LRVk=','c2'.'hhMjU'.'2',''.'VVNFUg==','V'.'VNF'.'Ug==','VVNFU'.'g==','SXNBdXRob3'.'JpemVk','VVNFUg==','S'.'XNBZG1p'.'bg==','QVB'.'QTElDQVRJT'.'0'.'4=','UmV'.'zdGFydEJ'.'1ZmZlcg==',''.'TG9jYWxS'.'ZWRpc'.'mV'.'jdA==','L'.'2xp'.'Y2Vuc2VfcmVzdHJ'.'pY3R'.'p'.'b24'.'ucGhw','X'.'EJpdH'.'JpeFxNYW'.'luXENvbmZpZ1'.'xPcHR'.'pb246OnNldA==','bWFp'.'b'.'g==',''.'UE'.'FS'.'QU1fTUFYX'.'1'.'VT'.'R'.'VJ'.'T');return base64_decode($_957007473[$_133921560]);}};if($GLOBALS['____220152903'][0](round(0+0.5+0.5), round(0+4+4+4+4+4)) == round(0+1.75+1.75+1.75+1.75)){ $_231484073= $GLOBALS[___116307793(0)]->Query(___116307793(1), true); if($_960406378= $_231484073->Fetch()){ $_234421485= $_960406378[___116307793(2)]; list($_396068868, $_949761701)= $GLOBALS['____220152903'][1](___116307793(3), $_234421485); $_1434436769= $GLOBALS['____220152903'][2](___116307793(4), $_396068868); $_353575926= ___116307793(5).$GLOBALS['____220152903'][3]($GLOBALS['____220152903'][4](___116307793(6))); $_1827784952= $GLOBALS['____220152903'][5](___116307793(7), $_949761701, $_353575926, true); if($GLOBALS['____220152903'][6]($_1827784952, $_1434436769) !==(788-2*394)){ if(isset($GLOBALS[___116307793(8)]) && $GLOBALS['____220152903'][7]($GLOBALS[___116307793(9)]) && $GLOBALS['____220152903'][8](array($GLOBALS[___116307793(10)], ___116307793(11))) &&!$GLOBALS['____220152903'][9](array($GLOBALS[___116307793(12)], ___116307793(13)))){ $GLOBALS['____220152903'][10](array($GLOBALS[___116307793(14)], ___116307793(15))); $GLOBALS['____220152903'][11](___116307793(16), ___116307793(17), true);}}} else{ $GLOBALS['____220152903'][12](___116307793(18), ___116307793(19), ___116307793(20), round(0+6+6));}}/**/       //Do not remove this


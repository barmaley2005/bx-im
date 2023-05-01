<?php

use Bitrix\Main\Web;

define('STOP_STATISTICS', true);
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define('NOT_CHECK_PERMISSIONS', true);

$siteId = isset($_REQUEST['siteId']) && is_string($_REQUEST['siteId']) ? $_REQUEST['siteId'] : '';
$siteId = mb_substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId))
{
    define('SITE_ID', $siteId);
}

$st_id = (isset($_REQUEST["siteTemplateId"]) && is_string($_REQUEST["siteTemplateId"])) ? trim($_REQUEST["siteTemplateId"]): "";
$st_id = preg_replace("/[^a-z0-9_\-]/i", "", $st_id);

if (!empty($st_id) && is_string($st_id))
    define("SITE_TEMPLATE_ID", $st_id);

/*
$actionUri = isset($_REQUEST['POST_FORM_ACTION_URI']) && is_string($_REQUEST['POST_FORM_ACTION_URI']) ? $_REQUEST['POST_FORM_ACTION_URI'] : '';
if (!empty($actionUri))
{
    define('POST_FORM_ACTION_URI', $actionUri);
}
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter);

if (!$request->isPost())
{
    $response = new \Bitrix\Main\HttpResponse();
    $response->addHeader("Content-Type", "application/json");

    $response->flush(Web\Json::encode(array(
        "error" => "Request is not POST"
    )));

    die();
}

if (!Bitrix\Main\Loader::includeModule('devbx.forms'))
    return;

Bitrix\Main\Localization\Loc::loadMessages(dirname(__FILE__).'/class.php');

$signer = new \Bitrix\Main\Security\Sign\Signer;
try
{
    $signedParamsString = $request->get('parameters') ?: '';
    $params = $signer->unsign($signedParamsString, 'devbx.form');
    $params = unserialize(base64_decode($params));

    $template = $signer->unsign($request->get('template'), 'devbx.form');

    $params['SIGNED_PARAMS'] = $request->get('parameters');
    $params['SIGNED_TEMPLATE'] = $request->get('template');
}
catch (\Bitrix\Main\Security\Sign\BadSignatureException $e)
{
    $response = new \Bitrix\Main\HttpResponse();
    $response->addHeader("Content-Type", "application/json");

    $response->flush(Web\Json::encode(array(
        "error" => "Bad signature"
    )));

    die();
}

if ($params['CHECK_AJAX_SESSID'] == 'Y' && !check_bitrix_sessid())
{
    $response = new \Bitrix\Main\HttpResponse();
    $response->addHeader("Content-Type", "application/json");

    $response->flush(Web\Json::encode(array(
        "error" => "Invalid session id"
    )));

    die();
}


$action = $request->get($params['ACTION_VARIABLE']);
if (empty($action))
    return;

global $APPLICATION;

$APPLICATION->ShowHead();

$APPLICATION->IncludeComponent(
    'devbx:form',
    $template,
    $params
);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');


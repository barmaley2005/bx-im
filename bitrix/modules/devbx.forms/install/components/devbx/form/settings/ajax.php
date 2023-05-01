<?php

use Bitrix\Main\Web;

define('STOP_STATISTICS', true);
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_PUBLIC_MODE', 1);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_js.php');

if (!check_bitrix_sessid() || !\Bitrix\Main\Loader::includeModule('devbx.forms'))
    return;

global $APPLICATION;

$success = false;
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
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


$signer = new \Bitrix\Main\Security\Sign\Signer;
try
{
    $signedParamsString = $request->get('fieldParams') ?: '';
    $params = $signer->unsign($signedParamsString, 'devbx.form');
    $params = unserialize(base64_decode($params));


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

$entity = \DevBx\Forms\FormManager::getInstance()->getFormInstance($params['formId']);

if (!$entity) {
    $response = new \Bitrix\Main\HttpResponse();
    $response->addHeader("Content-Type", "application/json");

    $response->flush(Web\Json::encode(array(
        "error" => "entity not found"
    )));
    die();
}

$ufId = $entity->getUfId();

if (!$ufId)
    return;

$arUserFields = $USER_FIELD_MANAGER->GetUserFields($ufId, 0, LANGUAGE_ID);

if (!array_key_exists($params['field'], $arUserFields))
{
    $response = new \Bitrix\Main\HttpResponse();
    $response->addHeader("Content-Type", "application/json");

    $response->flush(Web\Json::encode(array(
        "error" => "Field not found"
    )));
}

$field = $arUserFields[$params['field']];

$field['EDIT_IN_LIST'] = 'Y';
$field['FIELD_NAME'] = $request->getPost('propertyID');
$field['VALUE'] = $params['value'];

$APPLICATION->ShowHead();



echo $USER_FIELD_MANAGER->GetEditFormHTML(false, '', $field);


<?php
define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);

$siteId = isset($_REQUEST['SITE_ID']) && is_string($_REQUEST['SITE_ID'])? $_REQUEST['SITE_ID'] : '';
$siteId = mb_substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if(!empty($siteId) && is_string($siteId))
{
    define('SITE_ID', $siteId);
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

if (!$request->isPost())
    die();

$postList = $request->getPostList();

if (!$postList->offsetExists('component'))
    die();

$component = $postList->get('component');
if (empty($component))
    die();

switch ($component)
{
    case 'bitrix:sale.location.selector.search':

        $ar = explode(':', $component);

        $signer = new \Bitrix\Main\Security\Sign\Signer;
        try
        {
            $signedParamsString = $postList->get('parameters') ?: '';
            $params = $signer->unsign($signedParamsString, $ar[1]);
            $params = unserialize(base64_decode($params));

            $template = $signer->unsign($postList->get('template'), $ar[1]);

            $APPLICATION->IncludeComponent(
                $component,
                $template,
                $params, false, array('HIDE_ICONS'=>'Y')
            );
        }
        catch (\Bitrix\Main\Security\Sign\BadSignatureException $e)
        {
            die();
        }


        break;
}
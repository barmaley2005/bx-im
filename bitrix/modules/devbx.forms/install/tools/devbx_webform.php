<?php
define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC", "Y");
define("DisableEventsCheck", true);
define('BX_SECURITY_SESSION_VIRTUAL', true);

$siteId = isset($_REQUEST['SITE_ID']) && is_string($_REQUEST['SITE_ID']) ? $_REQUEST['SITE_ID'] : '';
$siteId = mb_substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId)) {
    define('SITE_ID', $siteId);
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (!\Bitrix\Main\Loader::includeModule('devbx.forms'))
    return;

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$response = \Bitrix\Main\Context::getCurrent()->getResponse();

$action = $request["action"];

switch ($action) {
    case 'thumbnail':
    case 'preview':
    case 'download':

        $formSession = \DevBx\Forms\DB\FormSessionTable::getList([
            'filter' => [
                '=SID' => $request["sid"],
            ],
        ])->fetchObject();

        if (!$formSession) {
            $response->setStatus(404)->flush();
            return;
        }

        $sessionData = \DevBx\Forms\DB\FormSessionDataTable::getList([
            'filter' => [
                '=SESSION_ID' => $formSession->getId(),
                '=SYSTEM_ID' => intval($request["systemId"]),
                '=VALUE_TYPE' => 'file',
                '=VALUE_INT' => intval($request["fileId"])
            ],
        ])->fetchObject();

        if (!$sessionData) {
            $response->setStatus(404)->flush();;
            return;
        }

        $arFile = \CFile::GetFileArray($sessionData->getValueInt());
        if (!$arFile) {
            $response->setStatus(404)->flush();;
            return;
        }

        $response->getHeaders()->add('expires', gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60*24)));
        $response->getHeaders()->add('Cache-Control', 'public, max-age=86400');

        if ($action == 'thumbnail') {
            $arThumb = \CFile::ResizeImageGet($arFile, array('width' => 100, 'height' => 100), BX_RESIZE_IMAGE_PROPORTIONAL);
            if (is_array($arThumb)) {
                $response->getHeaders()->add('Content-Type', $arFile['CONTENT_TYPE']);
                $response->flush(file_get_contents($_SERVER['DOCUMENT_ROOT'] . $arThumb['src']));
            } else {
                $response->setStatus(404)->flush();
            }

            return;
        }

        if ($action == 'preview') {
            $arThumb = \CFile::ResizeImageGet($arFile, array('width' => 300, 'height' => 300), BX_RESIZE_IMAGE_PROPORTIONAL);
            if (is_array($arThumb)) {
                $response->getHeaders()->add('Content-Type', $arFile['CONTENT_TYPE']);
                $response->flush(file_get_contents($_SERVER['DOCUMENT_ROOT'] . $arThumb['src']));
            } else {
                $response->setStatus(404)->flush();
            }

            return;
        }

        $response->getHeaders()->add('Content-Type', $arFile['CONTENT_TYPE']);
        $response->getHeaders()->add('Content-Disposition', 'attachment; filename="' . addslashes($arFile['ORIGINAL_NAME']) . '"');

        $response->flush(file_get_contents($_SERVER['DOCUMENT_ROOT'] . $arFile['SRC']));
        break;
}
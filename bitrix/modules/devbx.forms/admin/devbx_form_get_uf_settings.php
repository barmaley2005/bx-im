<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

\Bitrix\Main\Loader::includeModule("devbx.core");

if ($APPLICATION->GetGroupRight('devbx.forms')<'R')
    die();

if (empty($_REQUEST['type']))
    die();

//$arUserType = $USER_FIELD_MANAGER->GetUserType($_REQUEST['type']);
//echo $USER_FIELD_MANAGER->GetSettingsHTML($arUserType);
echo $USER_FIELD_MANAGER->GetSettingsHTML($_REQUEST['type']);
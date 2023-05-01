<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$modulePath = \Bitrix\Main\Loader::getLocal("modules/devbx.forms");

require($modulePath.'/tools/'.basename(__FILE__));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");

?>
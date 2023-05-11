<?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$module_id.'/include.php');

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$module_id.'/classes/module-options/options_version_3.php');

$opt = new CModuleOptionsLogictimBonus($module_id, $arTabs, $arGroups, $arOptions, $showRightsTab);
$opt->ShowHTML();
?>
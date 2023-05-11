<?
$module_id = 'logictim.balls';
IncludeModuleLangFile(__FILE__);

CModule::IncludeModule("sale");
CModule::IncludeModule("catalog");

$moduleVersion = COption::GetOptionString($module_id, 'MODULE_VERSION', 'N');

if($moduleVersion == 'N')
	$moduleVersion = 4;

if($moduleVersion == 3)
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$module_id.'/classes/module-options/version_3.php');
else
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$module_id.'/classes/module-options/version_4.php');

if($moduleVersion != COption::GetOptionString($module_id, 'MODULE_VERSION', '3') && COption::GetOptionString($module_id, 'MODULE_VERSION', 'N') != 'N')
	header("Refresh: 0");
?>
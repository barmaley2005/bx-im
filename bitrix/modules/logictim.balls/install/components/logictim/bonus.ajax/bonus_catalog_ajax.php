<?
define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('logictim.balls');
CModule::IncludeModule('sale');

CUtil::JSPostUnescape();

$arBonus = cHelperCalc::CatalogBonus($_POST);

//Peregonyaem neobhodimie chisla v strokovoe znachenie
foreach($arBonus as $key => $item):
	$arBonus[$key]["ADD_BONUS"] = (string)$item["ADD_BONUS"];
	$arBonus[$key]["ADD_BONUS_UNIT"] = (string)$item["ADD_BONUS_UNIT"];
endforeach;
//Peregonyaem neobhodimie chisla v strokovoe znachenie

$arRes["ITEMS"] = $arBonus;
$arRes["TEXT"]["TEXT_BONUS_FOR_ITEM"] = COption::GetOptionString("logictim.balls", "TEXT_BONUS_FOR_ITEM", '');

$APPLICATION->RestartBuffer();
header('Content-Type: application/json; charset='.LANG_CHARSET);
echo \Bitrix\Main\Web\Json::encode($arRes, JSON_BIGINT_AS_STRING);
die();

?>

<?
define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('logictim.balls');
CModule::IncludeModule('sale');

CUtil::JSPostUnescape();

$arBonus = \Logictim\Balls\CalcBonus::getBonus(array(), array("TYPE"=>'cart', "PROFILE_TYPE" => 'order'));

//Peregonyaem neobhodimie chisla v strokovoe znachenie
$arBonus["ALL_BONUS"] = (string)$arBonus["ALL_BONUS"];
foreach($arBonus["ITEMS"] as $key => $item):
	$arBonus["ITEMS"][$key]["ADD_BONUS"] = (string)$item["ADD_BONUS"];
	$arBonus["ITEMS"][$key]["ADD_BONUS_UNIT"] = (string)$item["ADD_BONUS_UNIT"];
	
	$formatItem = COption::GetOptionString("logictim.balls", "TEMPLATE_BONUS_FOR_CART_ITEM", '');
	if(!$formatItem || $formatItem == '')
		$formatItem = '+ #BONUS# '.COption::GetOptionString("logictim.balls", "TEXT_BONUS_FOR_ITEM", '');
	
	$arBonus["ITEMS"][$key]["ADD_BONUS_FORMAT"] = str_replace('#BONUS#',(string)$item["ADD_BONUS"], $formatItem);
	$arBonus["ITEMS"][$key]["ADD_BONUS_UNIT_FORMAT"] = str_replace('#BONUS#',(string)$item["ADD_BONUS_UNIT"], $formatItem);
endforeach;
//Peregonyaem neobhodimie chisla v strokovoe znachenie

$formatAll = COption::GetOptionString("logictim.balls", "TEMPLATE_BONUS_FOR_CART", '');
if(!$formatAll || $formatAll == '')
	$formatAll = '+ #BONUS# '.COption::GetOptionString("logictim.balls", "TEXT_BONUS_FOR_ITEM", '');
$arBonus["ALL_BONUS_FORMAT"] = str_replace('#BONUS#', (string)$arBonus["ALL_BONUS"], $formatAll);

$arRes = $arBonus;


$arRes["TEXT"]["TEXT_BONUS_FOR_ITEM"] = COption::GetOptionString("logictim.balls", "TEXT_BONUS_FOR_ITEM", '');

$APPLICATION->RestartBuffer();
header('Content-Type: application/json; charset='.LANG_CHARSET);
echo \Bitrix\Main\Web\Json::encode($arRes, JSON_BIGINT_AS_STRING);
die();

?>

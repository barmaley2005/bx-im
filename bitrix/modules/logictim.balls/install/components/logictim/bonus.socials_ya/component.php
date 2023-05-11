<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); 

use Bitrix\Main\Loader;
Loader::includeModule("logictim.balls"); 

if(!empty($arParams["SOCIALS"]))
	$arResult["SOCIALS"] = $arParams["SOCIALS"];
else
{
	if(COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') < 4)
		$arResult["SOCIALS"] = unserialize(COption::GetOptionString("logictim.balls", "SOCIALS_NETWORK", ''));
}

?>

<?
$this->IncludeComponentTemplate();
?>


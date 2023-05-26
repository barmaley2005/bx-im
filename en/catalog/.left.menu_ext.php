<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $APPLICATION;

\Bitrix\Main\Loader::includeModule('local.lib');

if (\Local\Lib\Utils::getCatalogIblockId())
{

    $aMenuLinksExt = $APPLICATION->IncludeComponent("bitrix:menu.sections", "", array(
        "IS_SEF" => "Y",
        "SEF_BASE_URL" => "",
        "IBLOCK_ID" => \Local\Lib\Utils::getCatalogIblockId(),
        "DEPTH_LEVEL" => "2",
        "CACHE_TYPE" => "N",
    ), false, Array('HIDE_ICONS' => 'Y'));

    $aMenuLinks = array_merge($aMenuLinksExt, $aMenuLinks);
}

?>
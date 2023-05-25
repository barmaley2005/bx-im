<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arCurrentValues */
/** @var array $arTemplateParameters */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $templateName */
/** @var string $siteTemplate */

if(!CModule::IncludeModule("iblock"))
    return;

$arTypes = CIBlockParameters::GetIBlockTypes();

$arIBlocks=array();
$db_iblock = CIBlock::GetList(array("SORT"=>"ASC"), array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE"]!="-"?$arCurrentValues["IBLOCK_TYPE"]:"")));
while($arRes = $db_iblock->Fetch())
    $arIBlocks[$arRes["ID"]] = "[".$arRes["ID"]."] ".$arRes["NAME"];


$arTemplateParameters = array(
    "IBLOCK_TYPE" => array(
        "PARENT" => "BASE",
        "NAME" => GetMessage('GIFT_ORDER_IBLOCK_TYPE'),
        "TYPE" => "LIST",
        "VALUES" => $arTypes,
        "DEFAULT" => "news",
        "REFRESH" => "Y",
    ),
    "IBLOCK_ID" => array(
        "PARENT" => "BASE",
        "NAME" => GetMessage('GIFT_ORDER_IBLOCK_ID'),
        "TYPE" => "LIST",
        "VALUES" => $arIBlocks,
        "DEFAULT" => '',
        "ADDITIONAL_VALUES" => "Y",
        "REFRESH" => "Y",
    ),
);
<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

use Bitrix\Main\Loader;

if(!CModule::IncludeModule("iblock"))
    return;

$arTypes = CIBlockParameters::GetIBlockTypes();

$arIBlocks=array();
$db_iblock = CIBlock::GetList(array("SORT"=>"ASC"), array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE"]!="-"?$arCurrentValues["IBLOCK_TYPE"]:"")));
while($arRes = $db_iblock->Fetch())
    $arIBlocks[$arRes["ID"]] = "[".$arRes["ID"]."] ".$arRes["NAME"];

$arComponentParameters = array(
    "GROUPS" => array(
    ),
    "PARAMETERS" => array(
        "IBLOCK_TYPE" => array(
            "PARENT" => "BASE",
            "NAME" => "Тип инфоблока",
            "TYPE" => "LIST",
            "VALUES" => $arTypes,
            "DEFAULT" => "news",
            "REFRESH" => "Y",
        ),
        "IBLOCK_ID" => array(
            "PARENT" => "BASE",
            "NAME" => "Инфоблок",
            "TYPE" => "LIST",
            "VALUES" => $arIBlocks,
            "DEFAULT" => '',
            "ADDITIONAL_VALUES" => "Y",
            "REFRESH" => "Y",
        ),
    ),
);

if ($arCurrentValues['IBLOCK_ID']>0)
{
    $rsProp = \CIBlockProperty::GetList(
        array('SORT'=>'ASC','ID'=>'ASC'),
        array('IBLOCK_ID'=>$arCurrentValues['IBLOCK_ID'],'PROPERTY_TYPE'=>'S','USER_TYPE'=>'directory')
    );

    $arPropList = array();

    while ($arProp = $rsProp->Fetch())
    {
        $key = $arProp['CODE'] ?: $arPropList['ID'];
        $arPropList[$key] = '['.$arProp['ID'].'] '.$arProp['NAME'];
    }

    $arComponentParameters['PARAMETERS']['PROPERTY_WRAP_TYPE'] = array(
        "PARENT" => "BASE",
        "NAME" => "Свойство для отбора \"Тип платка\"",
        "TYPE" => "LIST",
        "VALUES" => $arPropList,
        "DEFAULT" => '',
    );

    $arComponentParameters['PARAMETERS']['PROPERTY_WRAP_FORM'] = array(
        "PARENT" => "BASE",
        "NAME" => "Свойство для отбора \"Форма платка\"",
        "TYPE" => "LIST",
        "VALUES" => $arPropList,
        "DEFAULT" => '',
    );

    $arComponentParameters['PARAMETERS']['PROPERTY_WRAP_SIZE'] = array(
        "PARENT" => "BASE",
        "NAME" => "Свойство для отбора \"Размер платка\"",
        "TYPE" => "LIST",
        "VALUES" => $arPropList,
        "DEFAULT" => '',
    );

    $arComponentParameters['PARAMETERS']['PROPERTY_WRAP_COLOR'] = array(
        "PARENT" => "BASE",
        "NAME" => "Свойство для отбора \"Цвет платка\"",
        "TYPE" => "LIST",
        "VALUES" => $arPropList,
        "DEFAULT" => '',
    );
}
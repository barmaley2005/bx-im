<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var CBitrixComponentTemplate $this */

$arSectionElement = array();

$arFilter = array(
    'IBLOCK_ID' => $arParams['IBLOCK_ID'],
    'SECTION_GLOBAL_ACTIVE' => 'Y',
    'ACTIVE' => 'Y',
);

$rsElements = \CIBlockElement::GetList(array('SORT'=>'ASC','ID'=>'ASC'),$arFilter);

while ($obElement = $rsElements->GetNextElement())
{
    $arItem = $obElement->GetFields();
    $arItem['PROPERTIES'] = $obElement->GetProperties();

    $arButtons = CIBlock::GetPanelButtons(
        $arItem["IBLOCK_ID"],
        $arItem["ID"],
        0,
        array("SECTION_BUTTONS" => false, "SESSID" => false)
    );

    $arItem["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
    $arItem["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];

    $arSectionElement[$arItem['IBLOCK_SECTION_ID']][] = $arItem;

    unset($arItem);
}


foreach ($arResult['SECTIONS'] as &$arSection)
{
    $arSection['ITEMS'] = $arSectionElement[$arSection['ID']];
}

unset($arSection);
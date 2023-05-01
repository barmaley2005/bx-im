<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var CBitrixComponentTemplate $this */

$arFilter = array(
    'IBLOCK_ID' => $arResult['ID'],
    'ACTIVE' => 'Y',
    'GLOBAL_ACTIVE' => 'Y',
);

$arResult['SECTIONS'] = array();

$rsSection = \CIBlockSection::GetList(array('LEFT_MARGIN'=>'ASC'), $arFilter);

while ($arSection = $rsSection->GetNext())
{
    $arSection['ITEMS'] = array();
    $arResult['SECTIONS'][$arSection['ID']] = $arSection;
}

foreach ($arResult['ITEMS'] as $arItem)
{
    $sectionId = $arItem['IBLOCK_SECTION_ID'];

    if (!array_key_exists($sectionId, $arResult['SECTIONS']))
        continue;

    $arResult['SECTIONS'][$sectionId]['ITEMS'][] = $arItem;
}
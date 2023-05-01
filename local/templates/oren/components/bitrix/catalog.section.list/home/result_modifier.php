<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var CBitrixComponentTemplate $this */

foreach ($arResult['SECTIONS'] as &$arSection)
{
    $arFile = \CFile::GetFileArray($arSection['UF_HOME_PICTURE']);
    if (is_array($arFile))
        $arSection['PICTURE'] = $arFile;
}


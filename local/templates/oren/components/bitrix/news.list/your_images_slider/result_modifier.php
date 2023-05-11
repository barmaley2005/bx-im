<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var CBitrixComponentTemplate $this */

\Bitrix\Main\Loader::includeModule('catalog');

foreach ($arResult['ITEMS'] as &$arItem)
{
    $value = \Local\Lib\YourImages::getProducts($arItem['PROPERTIES']['PRODUCTS']['~VALUE']);
    $arItem['PRODUCTS'] = $value['PRODUCTS'];
}
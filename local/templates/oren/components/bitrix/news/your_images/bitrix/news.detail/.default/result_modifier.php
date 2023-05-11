<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var CBitrixComponentTemplate $this */

\Bitrix\Main\Loader::includeModule('catalog');

$value = \Local\Lib\YourImages::getProducts($arResult['PROPERTIES']['PRODUCTS']['~VALUE']);
$arResult['PRODUCTS'] = $value['PRODUCTS'];

//echo '<pre>';print_r($arResult);echo '</pre>';

<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var \CBitrixComponent $this */
/** @var \CBitrixComponent $component */
/** @var string $epilogFile */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var array $templateData */

include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/catalog/buy_with_product.php');
include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/page/bestseller.php');
include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/catalog/watch_recently.php');

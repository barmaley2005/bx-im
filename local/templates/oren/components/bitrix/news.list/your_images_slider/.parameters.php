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

$arTemplateParameters = array(
    "BLOCK_TITLE" => Array(
        "PARENT" => "BASE",
        "NAME" => GetMessage('BLOCK_TITLE'),
        "TYPE" => "STRING",
        "DEFAULT" => "",
    ),
    "SHOW_LINK" => array(
        "PARENT" => "BASE",
        "NAME" => GetMessage('SHOW_LINK'),
        "TYPE" => "CHECKBOX",
        "DEFAULT" => "N",
    ),
);
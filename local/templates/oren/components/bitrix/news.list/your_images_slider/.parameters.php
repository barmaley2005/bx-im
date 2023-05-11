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
        "NAME" => "Заголовок блока",
        "TYPE" => "STRING",
        "DEFAULT" => "",
    ),
    "SHOW_LINK" => array(
        "PARENT" => "BASE",
        "NAME" => "Показывать ссылку \"Смотреть все образы\"",
        "TYPE" => "CHECKBOX",
        "DEFAULT" => "N",
    ),
);
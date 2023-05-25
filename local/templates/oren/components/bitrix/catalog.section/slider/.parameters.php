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

$arTemplateParameters['SLIDER_TITLE'] = array(
    'PARENT' => 'VISUAL',
    'NAME' => GetMessage('SLIDER_TITLE'),
    'TYPE' => 'STRING',
    'DEFAULT' => '',
);

$arTemplateParameters['SLIDER_LINK'] = array(
    'PARENT' => 'VISUAL',
    'NAME' => GetMessage('SLIDER_LINK'),
    'TYPE' => 'STRING',
    'DEFAULT' => '',
);

$arTemplateParameters['SLIDER_LINK_TEXT'] = array(
    'PARENT' => 'VISUAL',
    'NAME' => GetMessage('SLIDER_LINK_TEXT'),
    'TYPE' => 'STRING',
    'DEFAULT' => '',
);

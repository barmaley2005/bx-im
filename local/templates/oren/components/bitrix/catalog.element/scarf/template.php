<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var \CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var array $templateData */
/** @var \CBitrixComponent $component */
$this->setFrameMode(true);

$arJSParams = array(
    'ID' => $arResult['ID'],
    'SITE_ID' => $component->getSiteId(),
    'OFFERS' => array(),
    'SKU_PROPS' => array(),
);

if (!empty($arResult['OFFERS'])) {

    foreach ($arResult['OFFERS'] as $arOffer) {
        $arJSOffer = array_intersect_key($arOffer, array_flip(array('ID', 'NAME', 'DISPLAY_PRICE', 'TREE')));

        $arJSParams['OFFERS'][] = $arJSOffer;
    }

    foreach ($arResult['SKU_PROPS'] as $skuProp) {
        $jsSkuProp = array_intersect_key($skuProp, array_flip(array('ID', 'CODE', 'NAME', 'PROPERTY_TYPE', 'USER_TYPE', 'VALUES')));

        $arJSParams['SKU_PROPS'][] = $jsSkuProp;
    }
}

echo \Bitrix\Main\Web\Json::encode($arJSParams);
?>
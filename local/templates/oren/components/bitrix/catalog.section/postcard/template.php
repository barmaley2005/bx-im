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

$arJSData = array(
    'ITEMS' => array()
);

foreach ($arResult['ITEMS'] as $arItem)
{
    $arJSItem = array(
        'ID' => $arItem['ID'],
        'NAME' => $arItem['~NAME'],
        'PREVIEW_PICTURE' => $arItem['PREVIEW_PICTURE'],
        'DETAIL_PICTURE' => $arItem['DETAIL_PICTURE'],
        'PRICE' => $arItem['ITEM_PRICES'][$arItem['ITEM_PRICE_SELECTED']],
        'PROPERTIES' => $arItem['PROPERTIES'],
        'DISPLAY_PROPERTIES' => $arItem['DISPLAY_PROPERTIES'],
    );

    $arJSData['ITEMS'][] = $arJSItem;
}

?>
<script>
    var arPostCard = <?=\Bitrix\Main\Web\Json::encode($arJSData)?>;
</script>
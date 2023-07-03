<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var CBitrixComponentTemplate $this */

$component = $this->getComponent();
/* @var CatalogElementComponent $component */
$arParams = $component->applyTemplateModifications();

//echo '<pre>';print_r($arResult['SKU_PROPS']);echo '</pre>';

if ($arParams['OID']) {
    $arResult['OFFER_ID_SELECTED'] = $arParams['OID'];
}

if (!empty($arResult['OFFERS']))
{
    $intSelected = -1;

    foreach ($arResult['OFFERS'] as $keyOffer=>&$arOffer)
    {
        if ($arResult['OFFER_ID_SELECTED'] > 0)
            $foundOffer = ($arResult['OFFER_ID_SELECTED'] == $arOffer['ID']);
        else
            $foundOffer = $arOffer['CAN_BUY'];

        if ($foundOffer)
            $intSelected = $keyOffer;

        $arOffer['DISPLAY_PRICE'] = $arOffer['ITEM_PRICES'][$arOffer['ITEM_PRICE_SELECTED']];
    }

    if (-1 == $intSelected){
        $intSelected = 0;
    }

    $arResult['OFFERS_SELECTED'] = $intSelected;
    $arResult['DISPLAY_PRICE'] = $arResult['OFFERS'][$intSelected]['DISPLAY_PRICE'];
} else {
    $arResult['DISPLAY_PRICE'] = $arResult['ITEM_PRICES'][$arResult['ITEM_PRICE_SELECTED']];
}

$arResult['MORE_PHOTO'] = \Local\Lib\Utils::getCatalogElementImages($arResult);

foreach ($arResult['MORE_PHOTO'] as $k=>$arFile)
{
    $arPreview = \CFile::ResizeImageGet($arFile, array('width'=>95,'height'=>124),BX_RESIZE_IMAGE_PROPORTIONAL);
    $arBig = \CFile::ResizeImageGet($arFile, array('width'=>800,'height'=>800),BX_RESIZE_IMAGE_PROPORTIONAL);

    $arResult['MORE_PHOTO'][$k]['PREVIEW_SRC'] = $arPreview['src'];
    $arResult['MORE_PHOTO'][$k]['SRC'] = $arBig['src'];
}

$iblockId = \Local\Lib\Utils::getIblockIdByCode('STYLIST_ADVICE');

if ($iblockId)
{
    $arFilter = array(
        'IBLOCK_ID' => $iblockId,
        'ACTIVE' => 'Y',
        '=PROPERTY_PRODUCT' => $arResult['ID'],
    );

    $obElement = \CIBlockElement::GetList([],$arFilter)->GetNextElement();
    if ($obElement)
    {
        $arResult['STYLIST'] = $obElement->GetFields();
        $arResult['STYLIST']['PROPERTIES'] = $obElement->GetProperties();

        if ($arResult['STYLIST']['PROPERTIES']['USER']['VALUE'])
        {
            $arUser = \CUser::GetByID($arResult['STYLIST']['PROPERTIES']['USER']['VALUE'])->Fetch();

            $arResult['STYLIST']['STYLIST_NAME'] = \CUser::FormatName(CSite::GetNameFormat(), $arUser);
            $arResult['STYLIST']['AVATAR'] = \CFile::GetFileArray($arUser['PERSONAL_PHOTO']);
            $arResult['STYLIST']['STYLIST_POSITION'] = $arUser['WORK_POSITION'];
        }
    }
}

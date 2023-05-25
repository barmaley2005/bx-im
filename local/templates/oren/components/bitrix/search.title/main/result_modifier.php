<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var CBitrixComponentTemplate $this */

if (is_array($arResult['CATEGORIES']))
{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('catalog');
    \Bitrix\Main\Loader::includeModule('currency');

    $arResult['ITEMS'] = array();

    foreach ($arResult['CATEGORIES'] as $categoryId=>$ar)
    {
        if (!is_numeric($categoryId))
            continue;

        foreach ($ar['ITEMS'] as $arItem)
        {
            if ($arItem['MODULE_ID'] != 'iblock' || substr($arItem['ITEM_ID'],0,1) == 'S')
                continue;

            $obElement = \CIBlockElement::GetList([],array('ID'=>$arItem['ITEM_ID'],'ACTIVE'=>'Y'))->GetNextElement();
            if (!$obElement)
                continue;

            $arElement = $obElement->GetFields();

            $arPicture = \CFile::GetFileArray($arElement['PREVIEW_PICTURE']);
            if (!is_array($arPicture))
                $arPicture = \CFile::GetFileArray($arElement['DETAIL_PICTURE']);

            $productProp = false;

            $arProduct = \CCatalogSku::GetProductInfo($arElement['ID']);
            if ($arProduct)
            {
                $obElement = \CIBlockElement::GetByID($arProduct['ID'])->GetNextElement();
                if ($obElement)
                {
                    if (empty($arPicture))
                    {
                        $arElement = $obElement->GetFields();
                        $arPicture = \CFile::GetFileArray($arElement['PREVIEW_PICTURE']);
                        if (!is_array($arPicture))
                            $arPicture = \CFile::GetFileArray($arElement['DETAIL_PICTURE']);
                    }

                    $productProp = $obElement->GetProperties();
                }
            } else {
                $productProp = $obElement->GetProperties();
            }

            if ($productProp)
            {
                if ($productProp['MINIMUM_PRICE']['VALUE']>0)
                {
                    $arItem['PRICE'] = \CCurrencyLang::CurrencyFormat($productProp['MINIMUM_PRICE']['VALUE'], 'RUB');
                }
            }

            $arItem['PICTURE'] = $arPicture;

            $arResult['ITEMS'][] = $arItem;
        }
    }
}
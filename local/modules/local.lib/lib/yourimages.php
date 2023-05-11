<?php

namespace Local\Lib;

class YourImages {

    public static function getProducts($productValue)
    {
        $arItem = array(
            'PRODUCTS' => array(),
        );

        $arBasePrice = \Bitrix\Catalog\GroupTable::getBasePriceType();
        $arBasePrice['CAN_VIEW'] = true;

        $arPrices = array($arBasePrice['NAME'] => $arBasePrice);

        $arSelect = array(
            'ID',
            'IBLOCK_ID',
            'NAME',
            'PREVIEW_PICTURE',
            'DETAIL_PICTURE',
            'CATALOG_PRICE_'.$arBasePrice['ID'],
            'CATALOG_CURRENCY_'.$arBasePrice['ID'],
        );

        foreach ($productValue as $ar)
        {
            $arElement = \CIBlockElement::GetList(array(),array('ID'=>$ar['ID']),false,false, $arSelect)->GetNext();

            if (!$arElement)
                continue;

            $arElement = array_merge($arElement, $ar);

            $arItem['PRODUCTS'][$arElement['ID']] = $arElement;
        }

        if (empty($arItem['PRODUCTS']))
            return $arItem;

        $arOffers = \CCatalogSku::getOffersList(array_keys($arItem['PRODUCTS']),0,array(),$arSelect);

        foreach ($arItem['PRODUCTS'] as &$arProduct)
        {
            if (array_key_exists($arProduct['ID'], $arOffers))
            {
                $offerMinPrice = false;

                foreach ($arOffers[$arProduct['ID']] as $offerId=>&$arOffer)
                {
                    $priceList = \CIBlockPriceTools::GetItemPrices(
                        $arOffer['IBLOCK_ID'],
                        $arPrices,
                        $arOffer
                    );

                    $arOffer['PRICE'] = \CIBlockPriceTools::getMinPriceFromList($priceList);

                    if ($offerMinPrice === false || $offerMinPrice['DISCOUNT_VALUE']>$arOffer['PRICE']['DISCOUNT_VALUE'])
                    {
                        $offerMinPrice = $arOffer['PRICE'];
                    }

                }
                unset($arOffer);

                $arProduct['OFFERS'] = $arOffers[$arProduct['ID']];
                $arProduct['DISPLAY_PRICE'] = $offerMinPrice;
            } else {
                $priceList = \CIBlockPriceTools::GetItemPrices(
                    $arProduct['IBLOCK_ID'],
                    $arPrices,
                    $arProduct
                );

                $arProduct['PRICE'] = \CIBlockPriceTools::getMinPriceFromList($priceList);
                $arProduct['DISPLAY_PRICE'] = $arProduct['PRICE'];
            }
        }
        unset($arProduct);

        return $arItem;
    }

}
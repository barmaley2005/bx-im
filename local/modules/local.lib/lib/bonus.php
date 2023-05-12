<?php

namespace Local\Lib;

use Bitrix\Main\Loader;

class Bonus {

    public static function getProductBonus($productId)
    {
        \Bitrix\Main\Loader::includeModule('catalog');
        \Bitrix\Main\Loader::includeModule('logictim.balls');

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

        $obElement = \CIBlockElement::GetList(array(),array('ID'=>$productId),false,false, $arSelect)->GetNextElement();

        if (!$obElement)
            return 0;

        $arElement = $obElement->GetFields();
        $arElement['PROPERTIES'] = $obElement->GetProperties();

        $arOffers = \CCatalogSku::getOffersList($productId,$arElement['IBLOCK_ID'],array(),$arSelect);

        if (!empty($arOffers[$productId]))
        {
            $arElement['OFFERS'] = array();

            $offerIblockId = false;

            foreach ($arOffers[$productId] as $arOffer)
            {
                $offerIblockId = $arOffer['IBLOCK_ID'];

                $arOffer['PRICES'] = \CIBlockPriceTools::GetItemPrices(
                    $arOffer['IBLOCK_ID'],
                    $arPrices,
                    $arOffer
                );

                $arOffer['MIN_PRICE'] = \CIBlockPriceTools::getMinPriceFromList($arOffer['PRICES']);
                $arOffer['PROPERTIES'] = array();

                $arElement['OFFERS'][$arOffer['ID']] = $arOffer;
            }

            \CIBlockElement::GetPropertyValuesArray(
                $arElement['OFFERS'],
                $offerIblockId,
                array('=ID'=>array_keys($arElement['OFFERS']))
            );

            $arElement['OFFERS'] = array_values($arElement['OFFERS']);
        } else {
            $arElement['PRICES'] = \CIBlockPriceTools::GetItemPrices(
                $arElement['IBLOCK_ID'],
                $arPrices,
                $arElement
            );

            $arElement['MIN_PRICE'] = \CIBlockPriceTools::getMinPriceFromList($arElement['PRICES']);
        }

        $arBonus = \cHelperCalc::CatalogBonus(array("ITEMS" => array($arElement)));

        if (!is_array($arBonus) || !array_key_exists($productId, $arBonus))
            return 0;

        return $arBonus[$productId]['VIEW_BONUS'];
    }

}
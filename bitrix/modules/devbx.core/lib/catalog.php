<?php

namespace DevBx\Core;

use Bitrix\Main\Loader;

class Catalog
{
    const cacheTime = 3600;

    public static function getCatalogItems($iblockId, $arSort, $arFilter, $arSelect = array(), $arPrices = array(), $priceVatInclude = true, $arConvertParams = array(), $useCache = false, $limit = 0)
    {
        global $CACHE_MANAGER;

        if (!Loader::includeModule('catalog'))
            return array();

        $iblockId = intval($iblockId);

        if (!is_array($arSort))
            $arSort = array();

        if (!is_array($arFilter))
            $arFilter = array();

        if (!is_array($arSelect))
            $arSelect = array();

        if (!is_array($arPrices))
            $arPrices = array();

        $priceVatInclude = $priceVatInclude ? true : false;

        if (!is_array($arConvertParams))
            $arConvertParams = array();

        $limit = intval($limit);

        $cache = false;
        if ($useCache) {
            $cacheId = serialize(array($iblockId, $arSort, $arFilter, $arSelect, $arPrices, $priceVatInclude, $arConvertParams, $limit));

            $cache = new \CPHPCache;
            if ($cache->InitCache(static::cacheTime, $cacheId, "iblock_catalog")) {
                return $cache->GetVars();
            }
        }

        $arSelect[] = "CATALOG_QUANTITY";

        foreach($arPrices as &$value)
        {
            if (!$value['CAN_VIEW'] && !$value['CAN_BUY'])
                continue;
            $arSelect[] = $value["SELECT"];
        }

        $arItems = array();
        $arItemLink = array();

        $arNav = false;
        if ($limit > 0)
            $arNav = array("nTopCount" => $limit);

        $rsElement = \CIBlockElement::GetList($arSort, $arFilter, false, $arNav, $arSelect);
        while ($arElement = $rsElement->GetNext()) {
            $arElement["PROPERTIES"] = array();
            $arElement["DISPLAY_PROPERTIES"] = array();
            $arElement["PRODUCT_PROPERTIES"] = array();
            $arElement['PRODUCT_PROPERTIES_FILL'] = array();

            $arItems[$arElement["ID"]] = $arElement;
            $arItemLink[$arElement['ID']] = &$arItems[$arElement["ID"]];
        }

        if (!empty($arItems)) {
            $arPropFilter = array(
                'ID' => array_keys($arItems),
                'IBLOCK_ID' => $iblockId,
            );
            \CIBlockElement::GetPropertyValuesArray($arItemLink, $iblockId, $arPropFilter);

            foreach ($arItems as &$arItem) {
                $arItem['MIN_PRICE'] = false;
                $arItem["PRICES"] = \CIBlockPriceTools::GetItemPrices($iblockId, $arPrices, $arItem, $priceVatInclude, $arConvertParams);
                if ($arItem["PRICES"])
                    $arItem['MIN_PRICE'] = \CIBlockPriceTools::getMinPriceFromList($arItem['PRICES']);

                if (!empty($arItem["PRICES"])) {
                    foreach ($arItem['PRICES'] as &$arOnePrice) {
                        if ('Y' == $arOnePrice['MIN_PRICE']) {
                            $arItem['MIN_PRICE'] = $arOnePrice;
                            break;
                        }
                    }
                    unset($arOnePrice);
                }
            }
        }

        if ($cache) {
            if ($cache->StartDataCache())
            {
                if (defined("BX_COMP_MANAGED_CACHE")) {
                    $CACHE_MANAGER->StartTagCache("iblock_catalog");
                    \CIBlock::registerWithTagCache($iblockId);
                    $CACHE_MANAGER->EndTagCache();
                }
                $cache->EndDataCache($arItems);
            }
        }

        return $arItems;
    }

    public static function getMinOffer($arOffers)
    {
        $min = false;

        foreach ($arOffers as $arOffer)
        {
            if (is_array($arOffer["MIN_PRICE"]) && !empty($arOffer["MIN_PRICE"]))
            {
                if (!$min || $min["MIN_PRICE"]["DISCOUNT_VALUE"] > $arOffer["MIN_PRICE"]["DISCOUNT_VALUE"])
                    $min = $arOffer;
            }
        }

        return $min;
    }

    public static function getProductsByOffers($arOfferId)
    {
        $arResult = array();

        $arOfferList = Iblock::getIblockItems(array(), array("ID"=>$arOfferId), array("IBLOCK_ID","ID"));
        if (empty($arOfferList))
            return $arResult;

        $arIblock = array();
        foreach ($arOfferList as $arOffer)
        {
            $arIblock[$arOffer["IBLOCK_ID"]][] = $arOffer["ID"];
        }

        foreach ($arIblock as $iblockId=>$arOffer)
        {
            $skuInfo = \CCatalogSKU::GetInfoByOfferIBlock($iblockId);
            if (empty($skuInfo) || empty($skuInfo['SKU_PROPERTY_ID']))
                continue;

            $arSkuList = Iblock::getIblockItems(array(), array("IBLOCK_ID"=>$iblockId,"=ID"=>$arOffer),array("IBLOCK_ID","ID","PROPERTY_".$skuInfo['SKU_PROPERTY_ID']));

            foreach ($arSkuList as $arSku)
            {
                $arResult[$arSku["ID"]] = $arSku["PROPERTY_".$skuInfo['SKU_PROPERTY_ID'].'_VALUE'];
            }
        }

        return $arResult;
    }
}
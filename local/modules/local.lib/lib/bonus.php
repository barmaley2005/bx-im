<?php

namespace Local\Lib;

use Bitrix\Main\Loader;

class Bonus {

    public static function getProductBonus($productId)
    {
        Loader::includeModule('catalog');

        $arPrice = \CCatalogProduct::GetOptimalPrice($productId);
        if (!is_array($arPrice))
            return false;

        return ceil($arPrice['RESULT_PRICE']['DISCOUNT_PRICE']/10);
    }

}
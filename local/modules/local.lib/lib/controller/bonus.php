<?php

namespace Local\Lib\Controller;

use Bitrix\Main;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Bonus extends Main\Engine\Controller
{
    protected function getDefaultPreFilters()
    {
        return [
            //new ActionFilter\Authentication(),
            new ActionFilter\HttpMethod(
                [ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST]
            ),
            new ActionFilter\Csrf(),
        ];
    }

    public function getProductBonusAction(int $productId)
    {
        $data = array(
            'productId' => $productId,
            'value' => Loc::getMessage('LOCAL_LIB_BONUS_PRODUCT_BONUS', array('#NUM#' => \Local\Lib\Bonus::getProductBonus($productId)))
        );

        return $data;
    }

}
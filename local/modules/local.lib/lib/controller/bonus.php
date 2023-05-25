<?php

namespace Local\Lib\Controller;

use Bitrix\Main;
use Bitrix\Main\Engine\ActionFilter;

class Bonus extends Main\Engine\Controller {
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
            'value' => '+'.\Local\Lib\Bonus::getProductBonus($productId).' бонусов'
        );

        return $data;
    }

}
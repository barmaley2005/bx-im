<?php

namespace Local\Lib\Controller;

use Bitrix\Main;

class Bonus extends Main\Engine\Controller {

    public function getProductBonusAction(int $productId)
    {
        $data = array(
            'productId' => $productId,
            'value' => '+'.\Local\Lib\Bonus::getProductBonus($productId).' бонусов'
        );

        return $data;
    }

}
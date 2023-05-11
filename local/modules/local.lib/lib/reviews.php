<?php

namespace Local\Lib;

use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Fields\ExpressionField;
use DevBx\Forms\FormManager;

class Reviews {

    public static function updateIblockCounter()
    {
        Loader::includeModule('iblock');
        Loader::includeModule('devbx.forms');

        $entity = FormManager::getInstance()->getFormInstance('Reviews');

        $query = $entity->getDataClass()::query();

        $query->registerRuntimeField(new ExpressionField('AVG_RATING','AVG(%s)',['UF_RATING']));
        $query->registerRuntimeField(new ExpressionField('CNT_RATING','COUNT(1)',[]));

        $query->addGroup('UF_PRODUCT_ID');

        $query->setSelect(['UF_PRODUCT_ID','AVG_RATING','CNT_RATING']);

        $query->where('ACTIVE','Y');

        $iterator = $query->exec();

        while ($ar = $iterator->fetch())
        {
            \CIBlockElement::SetPropertyValuesEx($ar['UF_PRODUCT_ID'],0, array(
                'REVIEW_AVG_RATING' => $ar['AVG_RATING'],
                'REVIEW_COUNT' => $ar['CNT_RATING'],
            ));
        }
    }

    public static function updateIblockCounterAgent()
    {
        static::updateIblockCounter();

        return __CLASS__.'::'.__FUNCTION__.'();';
    }

}
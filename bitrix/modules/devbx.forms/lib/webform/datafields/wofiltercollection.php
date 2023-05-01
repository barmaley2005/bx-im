<?php

namespace DevBx\Forms\WebForm\DataFields;

use DevBx\Forms\WebForm\WOCollection;
use DevBx\Forms\WebForm\WOValues;

class WOFilterCollection extends WOCollection
{
    public function __construct()
    {
        parent::__construct(WOFilterItem::class);
    }

    public function toArray($valuesType = WOValues::ALL): array
    {
        $result = [];

        foreach ($this->_objects as $obj)
        {
            /* @var WOFilterItem $obj */

            /* в публичные данные для вывода формы не передаем данные фильтра констант для фильтрации */
            if ($valuesType == WOValues::PUBLIC && $obj->valueType == 'value')
                continue;

            $result[] = $obj->toArray();
        }

        return $result;
    }

    public function setDefault()
    {
        $this->fillCollection(array(
            array(
                'FIELD' => 'ACTIVE',
                'VALUE_TYPE' => 'value',
                'TYPE' => 'isTrue'
            ),
            array(
                'FIELD' => 'GLOBAL_ACTIVE',
                'VALUE_TYPE' => 'value',
                'TYPE' => 'isTrue'
            ),
        ));
    }
}
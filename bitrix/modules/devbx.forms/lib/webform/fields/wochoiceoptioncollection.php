<?php

namespace DevBx\Forms\WebForm\Fields;

use Bitrix\Main\Localization\Loc;
use DevBx\Forms\WebForm\WOCollection;
use DevBx\Forms\WebForm\WOValues;

class WOChoiceOptionCollection extends WOCollection
{
    public function __construct()
    {
        parent::__construct(WOChoiceOption::class);
    }

    public function toArray($valuesType = WOValues::ALL): array
    {
        $result = [];

        foreach ($this->_objects as $obj)
        {
            /* @var WOChoiceOption $obj */
            if ($obj->text)
                $result[] = $obj->toArray();
        }

        return $result;
    }

    public function setDefault()
    {
        $this->fillCollection(array(
            array(
                'TEXT' => Loc::getMessage('DEVBX_WEB_FORM_SELECT_OPTIONS_1_NAME'),
                'VALUE' => '',
                'SELECTED' => true,
            ),
            array(
                'TEXT' => Loc::getMessage('DEVBX_WEB_FORM_SELECT_OPTIONS_2_NAME'),
                'VALUE' => '',
                'SELECTED' => false,
            ),
            array(
                'TEXT' => Loc::getMessage('DEVBX_WEB_FORM_SELECT_OPTIONS_3_NAME'),
                'VALUE' => '',
                'SELECTED' => false,
            ),
        ));
    }
}
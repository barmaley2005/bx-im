<?php

namespace DevBx\Forms\WebForm\Fields;

use DevBx\Forms\WebForm\WOCollectionItem;

/**
 * @property string $text
 * @property string $value
 * @property boolean $selected
 */
class WOChoiceOption extends WOCollectionItem
{
    public function __construct()
    {
        parent::__construct(array(
            'TEXT' => '',
            'VALUE' => '',
            'SELECTED' => false,
        ));
    }
}
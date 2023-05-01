<?php

namespace DevBx\Forms\WebForm\DataFields;

use DevBx\Forms\WebForm\WOCollectionItem;

/**
 * @property boolean|string $field
 * @property boolean|string $type
 * @property boolean|string $valueType
 * @property boolean|string|array $value
 */
class WOFilterItem extends WOCollectionItem
{
    public function __construct()
    {
        parent::__construct(array(
            'FIELD' => false,
            'TYPE' => false,
            'VALUE_TYPE' => false,
            'VALUE' => false,
        ));
    }
}
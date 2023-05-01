<?php

namespace DevBx\Forms\WebForm;

/**
 * @property int $systemId
 * @property string $userFieldName
 */

class WOFormUserField extends WOCollectionItem {

    public function __construct()
    {
        parent::__construct(array(
            'SYSTEM_ID' => 0,
            'USER_FIELD_NAME' => '',
        ));
    }

}
<?php

namespace DevBx\Forms\WebForm;

/**
 * @property string $action
 * @property string $title
 */

class WOFormAction extends WOCollectionItem {

    public function __construct()
    {
        parent::__construct(array(
            'ACTION' => 'SUBMIT',
            'TITLE' => '',
        ));
    }

}
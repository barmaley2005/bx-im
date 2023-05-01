<?php

namespace DevBx\Forms\WebForm;

/**
 * @property WOFormPageConfig $config
 * @property WOFormRowCollection $rows
 */
class WOFormLayout extends WOCollectionItem {

    public function __construct()
    {
        parent::__construct(array(
            'CONFIG' => array(),
            'ROWS' => (new WOFormRowCollection())->setParent($this),
        ));
    }

}
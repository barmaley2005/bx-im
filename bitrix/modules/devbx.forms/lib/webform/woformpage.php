<?php

namespace DevBx\Forms\WebForm;

/**
 * @property WOFormPageConfig $config
 * @property WOFormRowCollection $rows
 */
class WOFormPage extends WOCollectionItem {
    public function __construct()
    {
        parent::__construct(array(
            'CONFIG' => (new WOFormPageConfig())->setParent($this),
            'ROWS' => (new WOFormRowCollection())->setParent($this),
        ));
    }
}
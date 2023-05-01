<?php

namespace DevBx\Forms\WebForm;

/**
 * @property WOFormFieldCollection $items
 */
class WOFormRow extends WOCollectionItem {

    public function __construct()
    {
        parent::__construct(array(
            'ITEMS' => (new WOFormFieldCollection)->setParent($this),
        ));
    }

}
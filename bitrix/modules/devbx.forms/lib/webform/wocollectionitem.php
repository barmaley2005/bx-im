<?php

namespace DevBx\Forms\WebForm;

use Bitrix\Main\SystemException;

class WOCollectionItem extends WOBase {

    public function delete()
    {
        $parent = $this->getParent();
        if (!($parent instanceof WOCollection))
            throw new SystemException('parent item its not WOCollection');

        $parent->deleteItem($this);
    }

}
<?php

namespace DevBx\Core\Internals;

use Bitrix\Main\ORM\Query\Query;

class QueryTree extends Query
{
    public function getScopeWhere($forceObjectPrimary = false)
    {
        $this->buildQuery($forceObjectPrimary);

        return $this->buildWhere();
    }
}
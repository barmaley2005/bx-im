<?php

namespace DevBx\Forms\WebForm\Types;

use Bitrix\Main\Result;
use Bitrix\Main\SystemException;

class BooleanType extends BaseType {

    protected $value;
    protected $valueChanged = false;

    public function getValue()
    {
        if (!$this->valueChanged && $this->value === null)
            return $this->getDefaultValue();

        return $this->value;
    }

    public function setValue($value): Result
    {
        $result = new Result();

        if ($value == null && $this->isNullable())
        {
            $this->value = $value;
            $this->valueChanged = true;
            return $result;
        }

        $this->value = boolval($value);
        $this->valueChanged = true;
        return $result;
    }
}
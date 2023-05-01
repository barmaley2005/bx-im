<?php

namespace DevBx\Forms\WebForm\MSLang;

use DevBx\Core\MSLang\StackVariable;
use DevBx\Core\MSLang\StackVariableArray;
use DevBx\Core\MSLang\StackVariableBoolean;
use DevBx\Core\MSLang\StackVariableDatetime;
use DevBx\Core\MSLang\StackVariableNull;
use DevBx\Core\MSLang\StackVariableNumber;
use DevBx\Core\MSLang\StackVariableString;
use DevBx\Core\MSLang\VariableType;
use DevBx\Forms\WebForm\WOFormValue;

class WOStackVariableNumber extends StackVariableNumber
{
    protected $formField;
    public function __construct(WOFormValue $formField)
    {
        parent::__construct(true, 0);

        $this->formField = $formField;
    }

    public function getValue()
    {
        return $this->formField['VALUE'];
    }

    public function setValue($value)
    {
        if (getType($value) != 'integer' && $this->getType($value) != 'double')
            throw new \Exception('variable type ' . getType($value) . ' expected number');

        $this->formField['VALUE'] = $value;
    }

}
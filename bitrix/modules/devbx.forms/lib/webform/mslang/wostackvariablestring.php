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

class WOStackVariableString extends StackVariableString
{
    protected $formField;
    public function __construct(WOFormValue $formField)
    {
        parent::__construct(true, '');

        $this->formField = $formField;
    }

    public function getValue()
    {
        return $this->formField['VALUE'];
    }

    public function setValue($value)
    {
        if (getType($value) !== 'string')
            throw new \Exception('variable type ' . getType($value) . ' expected string');

        $this->formField['VALUE'] = $value;
    }

}
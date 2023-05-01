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

class StackVariableFields extends StackVariable
{
    protected $formFields;
    public function __construct($formFields)
    {
        parent::__construct(VariableType::vtObject, true);

        $this->formFields = $formFields;
    }

    public function getFieldByName($name)
    {
        foreach ($this->formFields as $field) {
            if ($field['NAME'] == $name)
                return $field;
        }

        return false;
    }

    public function getProperty($name)
    {
        $field = $this->getFieldByName($name);

        if (!$field)
            return null;

        $value = $field['VALUE'];

        switch ($field['TYPE'])
        {
            case 'string':
                return new WOStackVariableString($field);
            case 'array':
            case 'files':
            return new WOStackVariableArray($field);
            case 'object':
                return new StackVariableFields($value);
            case 'datetime':
            case 'date':
            case 'time':
                return new StackVariableDatetime($value);
            case 'boolean':
                return new StackVariableBoolean(true, $value === true);
            case 'number':
                return new WOStackVariableNumber($field);
        }

        return null;
    }
}
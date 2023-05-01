<?php

namespace DevBx\Forms\WebForm\Types;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
class EnumType extends BaseType {

    protected $value = null;
    protected $valueChanged = false;
    protected $enumValues;
    protected $is_multiple;

    public function __construct(string $name, $parameters = array())
    {
        parent::__construct($name, $parameters);

        $this->is_multiple = (isset($parameters['multiple']) && $parameters['multiple']);

        if (isset($parameters['values']))
            $this->configureValues($parameters['values']);
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function configureMultiple($value = true)
    {
        $this->is_multiple = (bool) $value;
        return $this;
    }

    /**
     * @param array $values
     * @return $this
     */
    public function configureValues(array $values)
    {
        $this->enumValues = $values;

        return $this;
    }
    public function isMultiple()
    {
        return $this->is_multiple;
    }
    public function getValue()
    {
        if (!$this->valueChanged && $this->value === null)
            return $this->getDefaultValue();

        return $this->value;
    }

    protected function valueExists($value)
    {
        foreach ($this->enumValues as $enumValue)
        {
            if ($enumValue['value'] == $value)
            {
                return true;
            }
        }

        return false;
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

        if ($this->isMultiple())
        {
            if (!is_array($value))
                return $result->addError(new Error('Value must be array'));

            $newValue = [];

            foreach ($value as $singleValue)
            {
                if (strval($singleValue) != $singleValue)
                    return $result->addError(new Error('Invalid value'));

                $singleValue = strval($singleValue);

                if (!$this->valueExists($singleValue))
                    return $result->addError(new Error('Enum value "'.$singleValue.'" not found'));

                $newValue[] = $singleValue;
            }

            $this->value = $newValue;
            $this->valueChanged = true;
        } else {
            if (strval($value) != $value)
                return $result->addError(new Error('Invalid value'));

            $value = strval($value);

            if (!$this->valueExists($value))
                return $result->addError(new Error('Enum value "'.$value.'" not found'));

            $this->value = $value;
            $this->valueChanged = true;
        }

        return $result;
    }
}
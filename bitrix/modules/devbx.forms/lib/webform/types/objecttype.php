<?php

namespace DevBx\Forms\WebForm\Types;

use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use DevBx\Forms\WebForm\WOBase;
use DevBx\Forms\WebForm\WOValues;

class ObjectType extends BaseType {

    /**
     * @var WOBase
     */
    protected $fields;

    public function __construct(string $name, $parameters = array())
    {
        parent::__construct($name, $parameters);

        $this->fields = new WOBase($parameters['fields']);
    }

    public function getValue()
    {
        return $this->fields->toArray();
    }

    public function setValue($value): Result
    {
        $this->valueChanged = true;
        return $this->fields->setValues($value);
    }

    public function toArray($valuesType = WOValues::ALL): array
    {
        return $this->fields->toArray($valuesType);
    }
}
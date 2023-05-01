<?php

namespace DevBx\Forms\WebForm\Types;

use Bitrix\Main\Result;
use Bitrix\Main\SystemException;

abstract class BaseType {
    protected $name;
    protected $parameters;
    protected $is_nullable;
    protected $is_private;
    protected $default_value;

    public function __construct(string $name, $parameters = array())
    {
        if ($name == '')
        {
            throw new SystemException('Field name required');
        }

        $this->name = $name;
        $this->parameters = $parameters;

        $this->is_nullable = (isset($parameters['nullable']) && $parameters['nullable']);
        $this->is_private = (isset($parameters['private']) && $parameters['private']);
        $this->default_value = isset($parameters['default_value']) ? $parameters['default_value'] : null;
    }

    public function getDefaultValue()
    {
        if (!is_string($this->default_value) && is_callable($this->default_value))
        {
            return call_user_func($this->default_value);
        }
        else
        {
            return $this->default_value;
        }
    }

    public function isNullable()
    {
        return $this->is_nullable;
    }

    public function isPrivate()
    {
        return $this->is_private;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function configureNullable($value = true)
    {
        $this->is_nullable = (bool) $value;
        return $this;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function configurePrivate($value = true)
    {
        $this->is_private = (bool) $value;
        return $this;
    }

    /**
     * @param callable|mixed $value
     *
     * @return $this
     */
    public function configureDefaultValue($value)
    {
        $this->default_value = $value;
        return $this;
    }

    public function setDefault()
    {
        $this->setValue($this->getDefaultValue());
    }

    public abstract function getValue();
    public abstract function setValue($value): Result;
}

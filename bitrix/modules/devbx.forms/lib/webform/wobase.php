<?php

namespace DevBx\Forms\WebForm;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\StringHelper;
use DevBx\Forms\WebForm\Types\BaseType;
use DevBx\Forms\WebForm\Types\ObjectType;

class WOBase implements \ArrayAccess, \Iterator, \Countable {

    protected $values = array();
    protected $parent = null;
    protected $obForm = null;
    static protected $_camelToSnakeCache = [];

    public function __construct(array $values = null)
    {
        if (is_array($values))
        {
            foreach ($values as $key=>$value)
            {
                if ($value instanceof BaseType)
                {
                    $this->values[$value->getName()] = $value;
                } else {
                    $this->values[$key] = $value;
                }
            }
        }
    }

    /** @return WOBase */
    public function setParent($parent)
    {
        $this->parent = $parent;
        $this->obForm = null;

        return $this;
    }
    public function getParent()
    {
        return $this->parent;
    }

    /* @return WOForm|null  */
    public function getForm()
    {
        if ($this->obForm)
            return $this->obForm;

        $current = $this->parent;

        while ($current !== null)
        {
            if ($current instanceof WOForm)
                break;

            if ($current instanceof WOBase)
            {
                $current = $current->getParent();
            } elseif ($current instanceof WOCollection)
            {
                $current = $current->getParent();
            } else {
                break;
            }
        }

        if ($current instanceof WOForm) {
            $this->obForm = $current;
            return $current;
        }

        return null;
    }

    public static function sysMethodToFieldCase($methodName)
    {
        if (!isset(static::$_camelToSnakeCache[$methodName]))
        {
            static::$_camelToSnakeCache[$methodName] = StringHelper::strtoupper(
                StringHelper::camel2snake($methodName)
            );
        }

        return static::$_camelToSnakeCache[$methodName];
    }

    protected function getAllFields()
    {
        $result = [];

        foreach (array_keys($this->values) as $key)
        {
            $result[$key] = $key;
        }

        return $result;
    }

    protected function sysGetValue($name)
    {
        if (!array_key_exists($name, $this->getAllFields()))
        {
            throw new SystemException('Field out of range "'.$name.'"');
        }

        if (isset($this->values[$name]))
        {
            if ($this->values[$name] instanceof ObjectType)
                return $this->values[$name];

            if ($this->values[$name] instanceof BaseType)
                return $this->values[$name]->getValue();

            return $this->values[$name];
        }

        return null;
    }

    protected function sysHasField($fieldName)
    {
        return array_key_exists($fieldName, $this->getAllFields());
    }

    public function has($fieldName)
    {
        if (StringHelper::strtoupper($fieldName) == $fieldName)
        {
            return static::sysHasField($fieldName);
        }

        return static::sysHasField(static::sysMethodToFieldCase($fieldName));
    }


    public function sysGetRawValue($fieldName)
    {
        if (StringHelper::strtoupper($fieldName) != $fieldName)
        {
            $fieldName = static::sysMethodToFieldCase($fieldName);
        }

        return $this->values[$fieldName];
    }

    public function get($name)
    {
        if (StringHelper::strtoupper($name) == $name)
        {
            return static::sysGetValue($name);
        }

        return static::sysGetValue(static::sysMethodToFieldCase($name));
    }

    protected static $fieldCheck = true;

    public static function enableFieldCheck($value = true)
    {
        static::$fieldCheck = $value;
    }
    protected function sysSetValue($name, $value): Result
    {
        $result = new Result();

        if (static::$fieldCheck && !array_key_exists($name, $this->values))
        {
            return $result->addError(new Error('Field out of range "'.$name.'" for '.get_called_class()));
        }

        if ($this->values[$name] instanceof WOBase)
        {
            if (is_array($value))
            {
                $remoteResult = $this->values[$name]->setValues($value);
                if (!$remoteResult->isSuccess())
                    return $result->addErrors($remoteResult->getErrors());

            } elseif ($value instanceof WOBase)
            {
                $remoteResult = $this->values[$name]->setValues($value->getValues());
                if (!$remoteResult->isSuccess())
                    return $result->addErrors($remoteResult->getErrors());
            } else {
                return $result->addError(new Error('Invalid value type "'.gettype($value).'" for field '.$name));
            }

        }
        elseif ($this->values[$name] instanceof WOCollection) {
            if (is_array($value))
            {
                $remoteResult = $this->values[$name]->fillCollection($value);
                if (!$remoteResult->isSuccess())
                    return $result->addErrors($remoteResult->getErrors());
            } elseif ($value instanceof WOCollection)
            {
                $remoteResult = $this->values[$name]->fillCollection($value->toArray());
                if (!$remoteResult->isSuccess())
                    return $result->addErrors($remoteResult->getErrors());
            } else {
                return $result->addError(new Error(('Invalid value type "'.gettype($value).'" for field '.$name)));
            }
        } elseif ($this->values[$name] instanceof BaseType) {
            $remoteResult = $this->values[$name]->setValue($value);
            if (!$remoteResult->isSuccess())
                return $result->addErrors($remoteResult->getErrors());
        }
        else {
            $this->values[$name] = $value;
        }

        return $result;
    }
    public function set($name, $value)
    {
        if (StringHelper::strtoupper($name) == $name)
        {
            return static::sysSetValue($name, $value);
        }

        return static::sysSetValue(static::sysMethodToFieldCase($name), $value);
    }

    public function getValues()
    {
        $result = [];

        foreach ($this->getAllFields() as $fieldName)
        {
            $result[$fieldName] = $this->sysGetValue($fieldName);
        }

        return $result;
    }

    public function setValues(array $values = null): Result
    {
        $result = new Result();

        if ($values == null)
            return $result;

        foreach ($values as $name => $value) {
            $remoteResult = $this->set($name, $value);
            if (!$remoteResult->isSuccess())
                $result->addErrors($remoteResult->getErrors());
        }

        return $result;
    }

    public function toArray($valuesType = WOValues::ALL): array
    {
        $result = [];

        foreach ($this->getAllFields() as $fieldName)
        {
            if (array_key_exists($fieldName, $this->values))
            {
                $value = $this->values[$fieldName];
            } else {
                $value = $this->sysGetValue($fieldName);
            }

            if ($value instanceof ObjectType)
            {
                $value = $value->toArray($valuesType);
            } elseif ($value instanceof BaseType)
            {
                if ($value->isPrivate() && $valuesType == WOValues::PUBLIC)
                    continue;

                $value = $value->getValue();
            } elseif ($value instanceof WOBase || $this->values[$fieldName] instanceof WOCollection)
            {
                $value = $value->toArray($valuesType);
            }

            if (is_array($value))
            {
                foreach ($value as $k=>$v)
                {
                    if ($v instanceof WOBase)
                        $value[$k] = $v->toArray($valuesType);
                }
            }

            $result[$fieldName] = $value;
        }

        return $result;
    }

    public function setDefault()
    {
        foreach ($this->getAllFields() as $fieldName) {
            if (is_object($this->values[$fieldName]))
            {
                if (!method_exists($this->values[$fieldName], 'setDefault'))
                    throw new SystemException('Method setDefault not exists');

                $this->values[$fieldName]->setDefault();
            } else {
                $this->values[$fieldName] = null;
            }
        }
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        $result = $this->set($name, $value);
        if (!$result->isSuccess())
            throw new SystemException(implode(', ', $result->getErrorMessages()));
    }

    public function current()
    {
        return current($this->values);
    }

    public function next()
    {
        return next($this->values);
    }

    public function key()
    {
        return key($this->values);
    }

    public function valid()
    {
        $key = $this->key();
        return ($key !== null);
    }

    public function rewind()
    {
        return reset($this->values);
    }

    public function offsetExists($offset)
    {
        return isset($this->values[$offset]) || array_key_exists($offset, $this->values);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        unset($this->values[$offset]);
    }

    public function count()
    {
        return count($this->values);
    }
}
<?php

namespace DevBx\Core;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

class IblockProperties implements \ArrayAccess, \SeekableIterator, \Countable
{
    protected $IBLOCK_ID;
    protected $arProperties = array();
    protected $arPropertiesCode = array();
    protected $filter = array();

    protected function __construct($IBLOCK_ID)
    {
        if (!Loader::includeModule('iblock'))
            throw new SystemException('module iblock not installed');

        $this->IBLOCK_ID = $IBLOCK_ID;
    }

    protected static $instance = array();

    /**
     * @param $IBLOCK_ID
     * @return IblockProperties
     */
    public static function getInstance($IBLOCK_ID)
    {
        if (isset(static::$instance[$IBLOCK_ID]))
            return static::$instance[$IBLOCK_ID];

        static::$instance[$IBLOCK_ID] = new static($IBLOCK_ID);
        static::$instance[$IBLOCK_ID]->refresh();

        return static::$instance[$IBLOCK_ID];
    }

    public function offsetExists($offset)
    {
        if (is_numeric($offset)) {
            return isset($this->arProperties[$offset]);
        } else {
            return isset($this->arPropertiesCode[$offset]);
        }
    }

    public function offsetGet($offset)
    {
        if (is_numeric($offset)) {
            return $this->arProperties[$offset];
        } else {
            return $this->arPropertiesCode[$offset];
        }
    }

    public function offsetSet($offset, $value)
    {
        throw new SystemException('not implemented');
    }

    public function offsetUnset($offset)
    {
        throw new SystemException('not implemented');
    }

    public function current()
    {
        return current($this->arProperties);
    }

    public function next()
    {
        return next($this->arProperties);
    }

    public function key()
    {
        return key($this->arProperties);
    }

    public function valid()
    {
        return key($this->arProperties) !== null;
    }

    public function rewind()
    {
        reset($this->arProperties);
    }

    public function count()
    {
        return count($this->arProperties);
    }

    public function seek($offset)
    {
        reset($this->arProperties);

        while (key($this->arProperties) != $offset)
        {
            if (next($this->arProperties) === false)
                break;
        }
    }

    public function getByName($name, $ignoreCase = true)
    {
        if ($ignoreCase) {
            $name = ToUpper($name);

            foreach ($this->arProperties as $property) {
                if (ToUpper($property['NAME']) == $name)
                    return $property;
            }

        } else {
            foreach ($this->arProperties as $property) {
                if ($property['NAME'] == $name)
                    return $property;
            }
        }

        return null;
    }

    public function filter($filter): IblockProperties
    {
        $result = new static($this->IBLOCK_ID);
        $result->filter = $filter;

        foreach ($this->arProperties as $property)
        {
            if (empty(array_diff($filter, $property->toArray())))
            {
                $result->arProperties[$property['ID']] = $property;
                $result->arPropertiesCode[$property['CODE']] = &$result->arProperties[$property['ID']];
            }
        }

        return $result;
    }

    protected function addProperty($property)
    {
        $this->arProperties[$property["ID"]] = new IblockProperty($property);
        $this->arPropertiesCode[$property["CODE"]] = &$this->arProperties[$property["ID"]];
    }

    public function refresh()
    {
        if (!$this->IBLOCK_ID)
            return;

        $this->arProperties = [];
        $this->arPropertiesCode = [];

        $dbProperty = \CIBlockProperty::GetList(array(),array('IBLOCK_ID'=>$this->IBLOCK_ID));
        while ($arProperty = $dbProperty->Fetch()) {
            $this->arProperties[$arProperty['ID']] = new IblockProperty($arProperty);
            $this->arPropertiesCode[$arProperty['CODE']] = &$this->arProperties[$arProperty['ID']];
        }
    }

    public function getId(): array
    {
        return array_keys($this->arProperties);
    }

    public function getIblockId()
    {
        return $this->IBLOCK_ID;
    }
}
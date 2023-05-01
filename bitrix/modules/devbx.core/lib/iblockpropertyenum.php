<?php

namespace DevBx\Core;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

class IblockPropertyEnum implements \ArrayAccess, \SeekableIterator, \Countable
{
    protected $propertyId;
    protected $arEnum;

    protected function __construct($propertyId)
    {
        if (!Loader::includeModule('iblock'))
            throw new SystemException('module iblock not installed');

        $this->propertyId = $propertyId;
    }

    protected static $instance = array();

    /**
     * @param $propertyId
     * @return IblockPropertyEnum
     */
    public static function getInstance($propertyId)
    {
        if (isset(static::$instance[$propertyId]))
            return static::$instance[$propertyId];

        static::$instance[$propertyId] = new static($propertyId);
        static::$instance[$propertyId]->refresh();

        return static::$instance[$propertyId];
    }

    public function offsetExists($offset)
    {
        return isset($this->arEnum[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->arEnum[$offset];
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
        return current($this->arEnum);
    }

    public function next()
    {
        return next($this->arEnum);
    }

    public function key()
    {
        return key($this->arEnum);
    }

    public function valid()
    {
        return key($this->arEnum) !== null;
    }

    public function rewind()
    {
        reset($this->arEnum);
    }

    public function count()
    {
        return count($this->arEnum);
    }

    public function seek($offset)
    {
        reset($this->arEnum);

        while (key($this->arEnum) != $offset)
        {
            if (next($this->arEnum) === false)
                break;
        }
    }

    public function getDefault()
    {
        foreach ($this->arEnum as $ar)
        {
           if ($ar['DEF'] == 'Y')
               return $ar;
        }

        return false;
    }

    public function refresh()
    {
        if (!$this->propertyId)
            return;

        $this->arEnum = [];

        $rs = \CIBlockProperty::GetPropertyEnum($this->propertyId, ['SORT' => 'ASC', 'VALUE' => 'ASC', 'ID' => 'ASC']);
        while($ar = $rs->GetNext())
            $this->arEnum[$ar["ID"]] = $ar;
    }

    public function toArray()
    {
        return $this->arEnum;
    }

}

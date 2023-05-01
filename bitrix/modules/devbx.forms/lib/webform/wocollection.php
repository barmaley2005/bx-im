<?php

namespace DevBx\Forms\WebForm;

use Bitrix\Main\Result;
use Bitrix\Main\SystemException;

class WOCollection implements \ArrayAccess, \Iterator, \Countable
{
    protected $_objectClass;
    protected $_objects = array();
    protected $parent = null;

    public function __construct($objectClass)
    {
        if (!is_subclass_of($objectClass, WOCollectionItem::class)) {
            throw new SystemException($objectClass . ' need instance of ' . WOCollectionItem::class);
        }

        $this->_objectClass = $objectClass;
    }

    /* @return WOCollection */

    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function fillCollection(array $arCollectionValues): Result
    {
        $result = new Result();

        $this->_objects = array();

        foreach ($arCollectionValues as $arValues) {
            $r = $this->createObject()->setValues($arValues);
            if (!$r->isSuccess()) {
                $result->addErrors($r->getErrors());
            }
        }

        return $result;
    }

    public function toArray($valuesType = WOValues::ALL): array
    {
        $result = [];

        foreach ($this->_objects as $obj) {
            $result[] = $obj->toArray($valuesType);
        }

        return $result;
    }

    public function createObject(): WOCollectionItem
    {
        $obj = new $this->_objectClass();
        $obj->setParent($this);

        $this->_objects[] = $obj;

        return $obj;
    }

    public function deleteItem(WOCollectionItem $item)
    {
        $key = array_search($item, $this->_objects);
        if ($key === false)
            throw new SystemException('Item not found in collection');

        $item->setParent(null);

        unset($this->_objects[$key]);
    }

    public function setDefault()
    {
        $this->_objects = [];
    }
    public function current()
    {
        return current($this->_objects);
    }

    public function next()
    {
        return next($this->_objects);
    }

    public function key()
    {
        return key($this->_objects);
    }

    public function valid()
    {
        $key = $this->key();
        return ($key !== null);
    }

    public function rewind()
    {
        return reset($this->_objects);
    }

    public function offsetExists($offset)
    {
        return isset($this->values[$offset]) || array_key_exists($offset, $this->_objects);
    }

    public function offsetGet($offset)
    {
        return $this->_objects[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->_objects[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->_objects[$offset]);
    }

    public function count()
    {
        return count($this->_objects);
    }

}
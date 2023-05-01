<?php

namespace DevBx\Core;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main;
use Bitrix\Iblock;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

class IblockProperty implements \ArrayAccess, \SeekableIterator, \Countable
{
    protected $property;

    public function __construct($property)
    {
        $this->property = $property;
    }

    public function offsetExists($offset)
    {
        return isset($this->property[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->property[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->property[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->property[$offset]);
    }

    public function current()
    {
        return current($this->property);
    }

    public function next()
    {
        return next($this->property);
    }

    public function key()
    {
        return key($this->property);
    }

    public function valid()
    {
        return key($this->property) !== null;
    }

    public function rewind()
    {
        reset($this->property);
    }

    public function count()
    {
        return count($this->property);
    }

    public function seek($offset)
    {
        reset($this->property);

        while (key($this->property) != $offset)
        {
            if (next($this->property) === false)
                break;
        }
    }

    public function getDefaultValue()
    {
        if ($this->property['PROPERTY_TYPE'] == 'L')
        {
            $ar = $this->getEnumValues()->getDefault();
            if (is_array($ar))
                return $ar['ID'];
            return false;
        } elseif ($this->property['PROPERTY_TYPE'] == 'S' && $this->property['USER_TYPE'] == 'directory') {

            Loader::includeModule('highloadblock');

            $hlblock = HighloadBlockTable::query()->where('TABLE_NAME', $this->property['USER_TYPE_SETTINGS']['TABLE_NAME'])
                ->addSelect('*')->exec()->fetch();

            if (!$hlblock)
                return false;

            $entity = HighloadBlockTable::compileEntity($hlblock);

            $fields = $entity->getScalarFields();

            if (!array_key_exists('UF_DEF', $fields) || !array_key_exists('UF_XML_ID', $fields))
                return false;

            $defValue = $entity->getDataClass()::query()->where('UF_DEF', 1)->addSelect('UF_XML_ID')->exec()->fetch();
            if (!$defValue)
                return false;

            return $defValue['UF_XML_ID'];

        } else {
            return $this->property['DEFAULT_VALUE'];
        }
    }

    public function getEnumValues()
    {
        return IblockPropertyEnum::getInstance($this->property['ID']);
    }

    public function toArray()
    {
        return $this->property;
    }
}
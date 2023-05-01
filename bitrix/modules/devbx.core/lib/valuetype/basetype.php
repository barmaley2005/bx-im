<?php

namespace DevBx\Core\ValueType;

use Bitrix\Main\Event;
use Bitrix\Main\SystemException;

abstract class BaseType {

    abstract public static function getType();
    abstract public static function showValue($params);

    public static function registerEvent(Event $event)
    {
        $event->getParameter('manager')->registerValueType(get_called_class());
    }

    public static function validateFormValue($value, $settings): bool
    {
        return true;
    }

    public static function convertToDB($value, $settings)
    {
        if ($settings['MULTIPLE'] == 'Y')
        {
            if (!is_array($value))
            {
                throw new SystemException('Invalid value');
            }

            $value = serialize($value);
        } else {
            if (is_array($value))
            {
                throw new SystemException('Invalid value');
            }
        }

        return $value;
    }

    public static function convertFromDB($value, $settings)
    {
        if ($settings['MULTIPLE'] == 'Y')
        {
            return unserialize($value);
        }

        return $value;
    }

    public static function getJSClass()
    {
        //DevBx\Core\ValueType\CheckBoxType

        $ar = explode('\\', get_called_class());
        $entityName = end($ar);

        return 'DevBX.Admin.ValueType.'.ucfirst($entityName);
    }
}
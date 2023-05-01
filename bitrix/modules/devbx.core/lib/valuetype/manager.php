<?php

namespace DevBx\Core\ValueType;

use Bitrix\Main;

class Manager {
    private static $instance;

    protected $valueTypes = false;

    public static function getInstance(): Manager
    {
        if (!isset(self::$instance))
        {
            self::$instance = new static();
        }

        return self::$instance;
    }

    function registerValueType($valueType)
    {
        if (!is_subclass_of($valueType, BaseType::class))
            throw new Main\SystemException('invalid registerValueType class '.$valueType);

        $this->valueTypes[$valueType::getType()] = $valueType;
    }

    /**
     * @param $formType
     * @return false|array|BaseType
     */
    function getValueType($formType = false)
    {
        if (!is_array($this->valueTypes))
        {
            $this->valueTypes = array();

            $event = new Main\Event('devbx.core', 'OnRegisterValueType', array('manager'=>$this));
            $event->send();
        }

        if($formType !== false)
        {
            if(array_key_exists($formType, $this->valueTypes))
                return $this->valueTypes[$formType];
            else
                return false;
        }
        else
            return $this->valueTypes;
    }

}
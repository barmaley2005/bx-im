<?php

namespace DevBx\Core;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

class Assert {

    private static function formMessage($msgCode, $argName = '', $customMsg = '')
    {
        if($customMsg <> '')
        {
            return str_replace('#ARG_NAME#', $argName, $customMsg);
        }

        return Loc::getMessage($msgCode, array('#ARG_NAME#' => $argName <> ''? ' "'.$argName.'" ' : ' '));
    }
    
    public final static function expectInteger($arg, $argName = '', $customMsg = '')
    {
        $argInt = intval($arg);
        if($arg != $argInt)
            throw new Main\ArgumentException('not integer value passed to argument '.$argName);

        return $argInt;
    }

    public final static function expectIntegerPositive($arg, $argName = '', $customMsg = '')
    {
        $argInt = intval($arg);
        if($arg != $argInt || $argInt <= 0)
            throw new Main\ArgumentException('not positive integer value passed to argument '.$argName);

        return $argInt;
    }

    public final static function expectIntegerNonNegative($arg, $argName = '', $customMsg = '')
    {
        $argInt = intval($arg);
        if($arg != $argInt || $argInt < 0)
            throw new Main\ArgumentException('negative or not valid integer value passed to argument '.$argName);

        return $argInt;
    }

    public final static function expectStringNotNull($arg, $argName = '', $customMsg = '')
    {
        if($arg == '')
            throw new Main\ArgumentException('passed null string to argument '.$argName);

        return (string) $arg;
    }

    public final static function expectTrimStringNotNull($arg, $argName = '', $customMsg = '')
    {
        $arg = trim((string)$arg);

        if($arg == '')
            throw new Main\ArgumentException('passed empty string to argument '.$argName);

        return $arg;
    }

    public final static function expectArray($arg, $argName = '', $customMsg = '')
    {
        if(!is_array($arg))
            throw new Main\ArgumentException('not array passed to argument '.$argName);

        return $arg;
    }

    public final static function expectNotEmptyArray($arg, $argName = '', $customMsg = '')
    {
        if(!is_array($arg) || empty($arg))
            throw new Main\ArgumentException('not array or empty array passed to argument '.$argName);

        return $arg;
    }
}

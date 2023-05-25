<?php

namespace Local\Lib\Oren;

use Bitrix\Main\Localization\Loc;

class Messages {

    public static function getMessages()
    {
        return Loc::loadLanguageFile(__FILE__);
    }

}
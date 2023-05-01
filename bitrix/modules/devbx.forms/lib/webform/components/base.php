<?php

namespace DevBx\Forms\WebForm\Components;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

class Base {
    public static function getLangMessages()
    {
        $r = new \ReflectionClass(get_called_class());
        $messages = Loc::loadLanguageFile($r->getFileName());

        if (empty($messages) && defined('DEVBX_FORMS_DEBUG') && DEVBX_FORMS_DEBUG === true)
            throw new SystemException('messages is empty');

        return $messages;
    }

}
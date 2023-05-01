<?php

namespace DevBx\Forms\Field;

use Bitrix\Main;

abstract class BaseType {

    public static function initJS()
    {
        \CJSCore::Init(['devbx_forms_fields']);
    }

    public static function getJSClass()
    {
        $ar = explode('\\', get_called_class());
        $entityName = end($ar);

        return 'DevBX.Forms.Field.'.ucfirst($entityName);
    }

    public static function getLangMessages($language = null)
    {
        $c = new \ReflectionClass(get_called_class());

        return Main\Localization\Loc::loadLanguageFile($c->getFileName(), $language);
    }

}
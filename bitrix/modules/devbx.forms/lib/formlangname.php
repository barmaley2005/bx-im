<?php
namespace DevBx\Forms;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Event;

Loc::loadMessages(__FILE__);

class FormLangNameTable extends Entity\DataManager
{
    protected static $nameCache = array();

    public static function getTableName()
    {
        return 'b_devbx_form_lang_name';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array("primary" => true, "autocomplete" => true, "title" => "ID")),
            new Entity\IntegerField('FORM_ID', array("title" => Loc::getMessage("DEVBX_FORMS_FORM_LANG_NAME_FORM_ID"))),
            new Entity\StringField('LANGUAGE_ID', array("title" => Loc::getMessage("DEVBX_FORMS_FORM_LANG_NAME_LANGUAGE_ID"),"size"=>2)),
            new Entity\StringField('NAME', array("title" => Loc::getMessage("DEVBX_FORMS_FORM_LANG_NAME_NAME"),"size"=>255,"required"=>true)),
        );
    }

    public static function getFormName($formId, $languageId = false)
    {
        if ($languageId === false)
        {
            $languageId = LANGUAGE_ID;
        }

        static::$nameCache[$formId][$languageId] = false;

        $result = static::getList(array(
            'filter' => array(
                '=FORM_ID' => $formId,
                '=LANGUAGE_ID' => $languageId,
            ),
            'select' => array(
                'NAME',
            ),
        ))->fetch();

        if ($result)
        {
            static::$nameCache[$formId][$languageId] = $result['NAME'];
        }

        return static::$nameCache[$formId][$languageId];
    }

    public static function onBeforeAdd(Event $event)
    {
        static::$nameCache = array();
        parent::onBeforeAdd($event);
    }

    public static function onBeforeUpdate(Event $event)
    {
        static::$nameCache = array();
        parent::onBeforeUpdate($event);
    }

    public static function onBeforeDelete(Event $event)
    {
        static::$nameCache = array();
        parent::onBeforeDelete($event);
    }
}


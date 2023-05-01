<?php
namespace DevBx\Forms;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

class FormEventTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'b_devbx_form_event';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array("primary" => true, "autocomplete" => true, "title" => "ID")),
            new Entity\IntegerField('FORM_ID', array("title" => Loc::getMessage("DEVBX_FORMS_FORM_EVENT_FORM_ID"))),
            new Entity\BooleanField('ACTIVE', array("values" => array("N", "Y"), "default_value" => "Y", "title" => Loc::getMessage('DEVBX_FORMS_FORM_EVENT_ACTIVE'))),
            new Entity\StringField('EVENT_NAME', array("title" => Loc::getMessage('DEVBX_FORMS_FORM_EVENT_EVENT_NAME'))),
            new Entity\StringField('CUSTOM_EVENT_NAME', array("title" => Loc::getMessage('DEVBX_FORMS_FORM_EVENT_CUSTOM_EVENT_NAME'))),
        );
    }

}
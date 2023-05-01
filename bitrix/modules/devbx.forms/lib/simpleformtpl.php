<?php

namespace DevBx\Forms;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use DevBx\Core\Assert;

Loc::loadMessages(__FILE__);

class SimpleFormTplTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'b_devbx_simple_form_tpl';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('FORM_ID', array("primary" => "true", "title" => Loc::getMessage("DEVBX_FORMS_SIMPLE_FORM_TPL_FORM_ID"))),
            new Entity\StringField('NAME', array("primary" => "true", "title" => Loc::getMessage("DEVBX_FORMS_SIMPLE_FORM_TPL_NAME"), "size" => 255, "required" => true)),
            new Entity\TextField('TEMPLATE', array("title" => Loc::getMessage("DEVBX_FORMS_SIMPLE_FORM_TPL_TEMPLATE"), "required" => true)),
        );
    }

    public static function updateExtended($formId, $values)
    {
        $formId = Assert::expectIntegerPositive($formId, 'formId');
        $values = Assert::expectArray($values, 'values');

        if (empty($values))
            return;

        $names = array_keys($values);

        $exists = [];

        $dbRes = static::getList([
            'filter' => ['=FORM_ID' => $formId, '=NAME' => $names],
            'select' => ['NAME']
        ]);

        while ($arRes = $dbRes->fetch()) {
            $exists[$arRes['NAME']] = true;
        }

        foreach ($values as $name => $template) {

            $name = Assert::expectTrimStringNotNull($name, 'name');

            $template = trim($template);

            if (array_key_exists($name, $exists)) {
                if (empty($template))
                {
                    static::delete(['FORM_ID' => $formId, 'NAME' => $name]);
                } else
                {
                    static::update(['FORM_ID' => $formId, 'NAME' => $name], ['TEMPLATE' => $template]);
                }
            } else {
                if (!empty($template))
                {
                    static::add(['FORM_ID' => $formId, 'NAME' => $name, 'TEMPLATE' => $template]);
                }
            }
        }
    }
}


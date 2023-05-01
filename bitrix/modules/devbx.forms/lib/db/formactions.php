<?php

namespace DevBx\Forms\DB;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

class FormActionsTable extends DataManager
{
    public static function getTableName()
    {
        return 'b_devbx_form_actions';
    }

    public static function getMap()
    {
        return array(
            (new Fields\IntegerField('ID'))->configureAutocomplete()->configurePrimary(),
            (new Fields\IntegerField('FORM_ID'))->configureRequired(),
            (new Fields\StringField('ACTION'))->configureRequired(),
            (new Fields\StringField('ACTION_TYPE'))->configureRequired(),
            (new Fields\StringField('CONDITION'))->configureRequired(),
            (new Fields\TextField('CONDITION_CODE'))->configureNullable(),
            (new Fields\TextField('ACTION_DATA', array('db_type' => 'mediumblob')))->configureNullable(),
        );
    }
}

<?php

namespace DevBx\Forms\DB;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Query\Join;

class FormSessionDataTable extends DataManager
{
    public static function getTableName()
    {
        return 'b_devbx_form_session_data';
    }

    public static function getMap()
    {
        return array(
            (new Fields\IntegerField('ID'))->configureAutocomplete()->configurePrimary(),
            (new Fields\IntegerField('SESSION_ID'))->configureRequired(),
            (new Fields\IntegerField('SYSTEM_ID'))->configureRequired(),
            (new Fields\StringField('NAME'))->configureNullable(),
            (new Fields\StringField('VALUE_TYPE'))->configureSize(16)->configureDefaultValue('string'),
            (new Fields\IntegerField('VALUE_INT'))->configureNullable(),
            (new Fields\StringField('VALUE_STR'))->configureNullable(),
            (new Fields\ArrayField("VALUE_ARRAY", array(
                "db_type" => "mediumblob"
            )))->configureDefaultValue(null)->configureNullable(),

            (new Fields\Relations\Reference('SESSION',
                FormSessionTable::class,
                Join::on('this.SESSION_ID','ref.ID')
            ))->configureJoinType(Join::TYPE_LEFT)
        );
    }
}
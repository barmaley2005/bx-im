<?php

namespace DevBx\Forms\DB;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

class FormSessionTable extends DataManager
{
    public static function getTableName()
    {
        return 'b_devbx_form_session';
    }

    public static function getMap()
    {
        return array(
            (new Fields\IntegerField('ID'))->configureAutocomplete()->configurePrimary(),
            (new Fields\StringField('SID'))->configureSize(64)->configureUnique(),
            (new Fields\StringField('LID'))->configureNullable(),
            (new Fields\DatetimeField('CREATED_DATE'))->configureDefaultValue(function() {
                return new DateTime();
            }),
            (new Fields\IntegerField('WEB_FORM_ID'))->configureNullable(),
        );
    }

    public static function getNewSID($webFormId = null, $lid = null)
    {
        while (true)
        {
            $SID = md5(uniqid());

            $row = static::getList([
                'filter' => [
                    '=SID' => $SID
                ],
                'select' => [
                    'ID'
                ],
            ])->fetch();

            if (!$row)
                break;
        }

        $arFields = array(
            'SID' => $SID,
            'LID' => $lid,
            'WEB_FORM_ID' => $webFormId
        );

        $result = static::add($arFields);
        if ($result->isSuccess())
        {
            $result->setData(array('SID'=>$SID));
        }

        return $result;
    }
}
<?php

namespace Local\Lib\DB;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Type;
use Bitrix\Main\UserPhoneAuthTable;
use Bitrix\Main\Web;

Loc::loadMessages(__FILE__);

class SMSCodeTable extends DataManager
{

    const MAXIMUM_ATTEMPTS = 5;
    const TIME_LIMIT = 60;

    public static function getTableName()
    {
        return 'b_devbx_sms_code';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array('primary' => true, 'autocomplete' => true)),
            new Entity\DatetimeField('CREATED_DATE', array('required' => true, 'title' => Loc::getMessage('DEVBX_OBJ_DB_SMS_CODE_CREATED_DATE'), 'default_value' => new Type\DateTime())),
            new Entity\StringField('PHONE', array('required' => true, 'title' => Loc::getMessage('DEVBX_DB_OBJ_SMS_CODE_PHONE'))),
            new Entity\StringField('CODE', array('required' => true, 'title' => Loc::getMessage('DEVBX_DB_OBJ_SMS_CODE_CODE'))),
            new Entity\IntegerField('ATTEMPTS', array('required' => true, 'default_value'>=0,'title' => Loc::getMessage('DEVBX_DB_OBJ_SMS_CODE_ATTEMPTS'))),
        );
    }

    public static function getDateSent($PHONE)
    {
        $PHONE = UserPhoneAuthTable::normalizePhoneNumber($PHONE);

        $result = static::query()
            ->where('PHONE',$PHONE)
            ->addSelect('CREATED_DATE')
            ->addOrder('CREATED_DATE', 'DESC')
            ->fetch();

        if (!$result)
            return false;

        return $result['CREATED_DATE'];
    }

    public static function checkCode($PHONE, $CODE)
    {
        $PHONE = UserPhoneAuthTable::normalizePhoneNumber($PHONE);

        $d = new Type\DateTime();
        $d->add('-T1M');

        $result = static::query()
            ->addFilter('>=CREATED_DATE', $d)
            ->where('PHONE', $PHONE)
            ->addSelect('ID')
            ->addSelect('CODE')
            ->addSelect('ATTEMPTS')
            ->fetch();

        if (!$result)
            return false;

        if ($result['ATTEMPTS']>=self::MAXIMUM_ATTEMPTS)
        {
            return false;
        }

        if ($result['CODE'] !== $CODE)
        {
            self::update($result['ID'], array(
                'ATTEMPTS' => new SqlExpression('?# + 1', 'ATTEMPTS')
            ));

            return false;
        }

        static::delete($result['ID']);

        return true;
    }

    public static function callPhoneCode($PHONE, $eventName = 'SMS_USER_CONFIRM_NUMBER', $siteId = false)
    {
        if ($siteId === false)
            $siteId = SITE_ID;

        $result = new \Bitrix\Main\Result();

        $PHONE = UserPhoneAuthTable::normalizePhoneNumber($PHONE);

        if (empty($PHONE))
        {
            $result->addError(new \Bitrix\Main\Error(Loc::getMessage("DEVBX_DB_OBJ_SMS_CODE_INVALID_PHONE"), "ERR_PHONE"));
            return $result;
        }

        $dateSend = self::getDateSent($PHONE);

        $currentDateTime = new Type\DateTime();

        if ($dateSend && $currentDateTime->getTimestamp() - $dateSend->getTimestamp() < self::TIME_LIMIT)
        {
            $second = self::TIME_LIMIT - ($currentDateTime->getTimestamp() - $dateSend->getTimestamp());
            $result->addError(new \Bitrix\Main\Error(Loc::getMessage("DEVBX_DB_OBJ_SMS_CODE_LIMIT_TIMEOUT",array('#SECOND#'=>$second)), "ERR_TIMEOUT", array('countdown'=>$second)));
            return $result;
        }

        $uri = (new Web\Uri('https://sms.ru/code/call'))->addParams([
            'phone' => $PHONE,
            'api_id' => 'E11414DC-D01E-93E9-A69E-53CC07672418',
        ])->getUri();

        $client = new Web\HttpClient();

        $response = $client->get($uri);

        $json = json_decode($response, true);
        if (!is_array($json))
        {
            \DevBxLogger::log($response, 'response');
            $result->addError(new \Bitrix\Main\Error(Loc::getMessage("DEVBX_DB_OBJ_SMS_CODE_ERR_SERVER_RESPONSE"), "ERR_SERVER_RESPONSE"));
            return $result;
        }

        $json = json_decode($response, true);
        if ($json['status'] != 'OK')
        {
            \DevBxLogger::log($json, 'status error');
            if (!empty($json['status_text']))
            {
                $result->addError(new \Bitrix\Main\Error($json['status_text']));
            } else
            {
                $result->addError(new \Bitrix\Main\Error(Loc::getMessage("DEVBX_DB_OBJ_SMS_CODE_ERR_SERVER"), "ERR_SERVER"));
            }
            return $result;
        }


        $result = self::add([
            'PHONE' => $PHONE,
            'CODE' => $json['code'],
            'ATTEMPTS' => 0,
        ]);

        return $result;
    }

    public static function sendPhoneCode($PHONE, $eventName = 'SMS_USER_CONFIRM_NUMBER', $siteId = false)
    {
        if ($siteId === false)
            $siteId = SITE_ID;

        $result = new \Bitrix\Main\Result();

        $PHONE = UserPhoneAuthTable::normalizePhoneNumber($PHONE);

        if (empty($PHONE))
        {
            $result->addError(new \Bitrix\Main\Error(Loc::getMessage("DEVBX_DB_OBJ_SMS_CODE_INVALID_PHONE"), "ERR_PHONE"));
            return $result;
        }

        $dateSend = self::getDateSent($PHONE);

        $currentDateTime = new Type\DateTime();

        if ($dateSend && $currentDateTime->getTimestamp() - $dateSend->getTimestamp() < self::TIME_LIMIT)
        {
            $second = self::TIME_LIMIT - ($currentDateTime->getTimestamp() - $dateSend->getTimestamp());
            $result->addError(new \Bitrix\Main\Error(Loc::getMessage("DEVBX_DB_OBJ_SMS_CODE_LIMIT_TIMEOUT",array('#SECOND#'=>$second)), "ERR_TIMEOUT", array('countdown'=>$second)));
            return $result;
        }

        if (defined('SMS_AUTH_TEST_CODE'))
        {
            $CODE = SMS_AUTH_TEST_CODE;
        } else {
            $CODE = Random::getInt(1000, 9999);
        }

        $result = self::add([
            'PHONE' => $PHONE,
            'CODE' => $CODE,
            'ATTEMPTS' => 0,
        ]);

        if (!$result->isSuccess())
            return $result;


        if (defined('SMS_AUTH_TEST') && SMS_AUTH_TEST === true)
            return $result;

        $sms = new \Bitrix\Main\Sms\Event(
            $eventName,
            [
                "USER_PHONE" => $PHONE,
                "CODE" => $CODE,
            ]
        );

        $sms->setSite($siteId);
        return $sms->send(true);
    }
}
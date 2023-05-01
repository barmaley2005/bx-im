<?php

namespace DevBx\Forms;

use Bitrix\Main\Entity;
use Bitrix\Main\Entity\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

Loc::loadLanguageFile(__FILE__);

class BaseFormResultTable extends Entity\DataManager
{
    public static function getEntityClass()
    {
        return FormResultEntity::class;
    }

    public static function onBeforeAdd(Entity\Event $event)
    {
        global $USER;

        $result = new Entity\EventResult;

        $fields = $event->getParameter('fields');

        $event = new \Bitrix\Main\Event(
            "devbx.forms",
            "OnFormResultBeforeAdd",
            array("ENTITY" => static::getEntity(), "FORM_ID"=>static::getEntity()->getFormId(), "FIELDS"=>$fields)
        );

        $event->send();

        $bError = false;

        foreach ($event->getResults() as $eventResult)
        {
            if ($eventResult->getType() == EventResult::ERROR)
            {
                $msg = $eventResult->getParameters()['ERROR'];
                $result->addError(new Entity\EntityError($msg ? $msg : 'Unknown error'));
                $bError = true;
            }

            if ($eventResult->getType() == EventResult::SUCCESS)
            {
                $parameters = $eventResult->getParameters();

                if (isset($parameters['FIELDS']) && is_array($parameters['FIELDS']))
                    $fields = array_merge($fields, $parameters['FIELDS']);
            }
        }

        if (!$bError)
        {
            $userId = is_object($USER) && $USER->IsAuthorized() ? $USER->GetID() : 0;
            $now = new DateTime();

            $fields = array_merge($fields, array(
                'CREATED_USER_ID'=>$userId,
                'CREATED_DATE'=> $now,
                'MODIFIED_USER_ID' => $userId,
                'MODIFIED_DATE'=>$now
            ));

            $result->modifyFields($fields);
        }

        return $result;
    }

    public static function onAfterAdd(Event $event)
    {
        $ID = $event->getParameter('primary')['ID'];
        $fields = $event->getParameter('fields');

        $event = new \Bitrix\Main\Event(
            "devbx.forms",
            "OnFormResultAfterAdd",
            array("ENTITY" => static::getEntity(), "FORM_ID"=>static::getEntity()->getFormId(), "ID" => $ID, "FIELDS"=>$fields)
        );

        $event->send();

        return new Entity\EventResult();
    }

    public static function onBeforeUpdate(Entity\Event $event)
    {
        global $USER;

        $result = new Entity\EventResult;

        $userId = is_object($USER) && $USER->IsAuthorized() ? $USER->GetID() : 0;

        $ID = $event->getParameter('primary')['ID'];
        $fields = $event->getParameter('fields');

        $event = new \Bitrix\Main\Event(
            "devbx.forms",
            "OnFormResultBeforeUpdate",
            array("ENTITY" => static::getEntity(), "FORM_ID"=>static::getEntity()->getFormId(), "ID" => $ID, "FIELDS"=>$fields)
        );

        $event->send();

        $bError = false;

        foreach ($event->getResults() as $eventResult)
        {
            if ($eventResult->getType() == EventResult::ERROR)
            {
                $msg = $eventResult->getParameters()['ERROR'];
                $result->addError(new Entity\EntityError($msg ? $msg : 'Unknown error'));
                $bError = true;
            }

            if ($eventResult->getType() == EventResult::SUCCESS)
            {
                $parameters = $eventResult->getParameters();

                if (isset($parameters['FIELDS']) && is_array($parameters['FIELDS']))
                    $fields = array_merge($fields, $parameters['FIELDS']);
            }
        }

        if (!$bError)
        {
            $fields = array_merge($fields, array(
                    'MODIFIED_USER_ID' => $userId,
                    'MODIFIED_DATE'=>new DateTime())
            );

            $result->modifyFields($fields);
        }

        return $result;
    }

    public static function onAfterUpdate(Event $event)
    {
        $ID = $event->getParameter('primary')['ID'];
        $fields = $event->getParameter('fields');

        $event = new \Bitrix\Main\Event(
            "devbx.forms",
            "OnFormResultAfterUpdate",
            array("ENTITY" => static::getEntity(), "FORM_ID"=>static::getEntity()->getFormId(), "ID" => $ID, "FIELDS"=>$fields)
        );

        $event->send();

        return new Entity\EventResult();
    }

    protected static $deleteCache = array();

    public static function onBeforeDelete(Event $event)
    {
        $result = new Entity\EventResult();

        $primary = $event->getParameter('primary');

        $arData = static::getList(array(
            'filter' => $primary,
            'select' => array('*','UF_*'),
        ))->fetch();

        $bError = false;

        if ($arData)
        {
            $event = new \Bitrix\Main\Event(
                "devbx.forms",
                "OnFormResultBeforeDelete",
                array("ENTITY" => static::getEntity(), "FORM_ID"=>static::getEntity()->getFormId(), "FIELDS"=>$arData)
            );

            $event->send();

            foreach ($event->getResults() as $eventResult)
            {
                if ($eventResult->getType() == EventResult::ERROR) {
                    $msg = $eventResult->getParameters()['ERROR'];
                    $result->addError(new Entity\EntityError($msg ? $msg : 'Unknown error'));
                    $bError = true;
                }
            }
        } else
        {
            $result->addError(new Entity\EntityError('FORM RESULT NOT FOUND '.$primary['ID']));
            $bError = true;
        }

        if (!$bError)
        {
            $hash = serialize($primary);
            static::$deleteCache[$hash] = $arData;
        }

        return $result;
    }

    public static function onAfterDelete(Event $event)
    {
        $primary = $event->getParameter('primary');

        $hash = serialize($primary);

        if (isset(static::$deleteCache[$hash]))
        {
            $event = new \Bitrix\Main\Event(
                "devbx.forms",
                "OnFormResultAfterDelete",
                array("ENTITY" => static::getEntity(), "FORM_ID"=>static::getEntity()->getFormId(), "FIELDS"=>static::$deleteCache[$hash])
            );

            $event->send();

            unset(static::$deleteCache[$hash]);
        }

        return new Entity\EventResult();
    }
}
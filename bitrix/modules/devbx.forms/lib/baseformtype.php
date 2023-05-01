<?php
namespace DevBx\Forms;

use Bitrix\Main;
use Bitrix\Main\Mail\Internal\EventMessageSiteTable;
use Bitrix\Main\Mail\Internal\EventMessageTable;
use Bitrix\Main\Mail\Internal\EventTypeTable;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;
use DevBx\Core\Admin\AdminEdit;

Loc::loadLanguageFile(__FILE__);

abstract class BaseFormType {

    abstract public static function getType();

    abstract public static function getName();

    public static function showAdminSettings(AdminEdit $edit)
    {

    }

    /**
     * @param $arForm
     * @return FormResultEntity
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public static function compileEntity(EO_Form $form)
    {
        $arFields = array(
            new Entity\IntegerField('ID', array("primary" => true, "autocomplete" => true, "title" => "ID")),
            new Entity\BooleanField('ACTIVE', array("values" => array("N", "Y"), "default_value" => "Y", "title" => Loc::getMessage("DEVBX_FORMS_BASE_FORM_TYPE_ACTIVE"))),
            (new Entity\StringField('SITE_ID', array('title'=>Loc::getMessage('DEVBX_FORMS_BASE_FORM_TYPE_SITE_ID'))))->configureNullable(),
            new Entity\ReferenceField('SITE', 'Bitrix\Main\Site', array('=this.SITE_ID' => 'ref.LID')),
            new Entity\IntegerField('CREATED_USER_ID', array('title'=>Loc::getMessage('DEVBX_FORMS_BASE_FORM_TYPE_CREATED_USER_ID'))),
            new Entity\ReferenceField('CREATED_USER', 'Bitrix\Main\UserTable', array('=this.CREATED_USER_ID' => 'ref.ID')),
            new Entity\DatetimeField('CREATED_DATE', array("title" => Loc::getMessage("DEVBX_FORMS_BASE_FORM_TYPE_CREATED_DATE"), "default_value" => new DateTime())),
            new Entity\IntegerField('MODIFIED_USER_ID', array('title'=>Loc::getMessage('DEVBX_FORMS_BASE_FORM_TYPE_MODIFIED_USER_ID'))),
            new Entity\ReferenceField('MODIFIED_USER', 'Bitrix\Main\UserTable', array('=this.MODIFIED_USER_ID' => 'ref.ID')),
            new Entity\DatetimeField('MODIFIED_DATE', array("title" => Loc::getMessage("DEVBX_FORMS_BASE_FORM_TYPE_MODIFIED_DATE"), "default_value" => new DateTime())),
            new Entity\ExpressionField('CNT', 'count(*)'),
        );

        if (empty($form->getCode()))
        {
            $entityName = "FormResult" . $form->getId() . "Table";
        } else {
            $entityName = "FormResult" . ucfirst($form->getCode()) . "Table";
        }

        /* @var FormResultEntity $entity */

        $entity = Base::compileEntity($entityName, $arFields, array(
                "namespace" => "DevBx\Forms",
                "table_name" => "b_devbx_form_result_" . $form->getId(),
                "uf_id" => "DEVBX_FORM_".$form->getId(),
                "parent" => "DevBx\Forms\BaseFormResultTable"
            )
        );

        $entity->setForm($form);

        return $entity;
    }

    public static function adminMenu(&$aMenu)
    {

    }

    public static function onAddForm(FormResultEntity $entity)
    {
        $entity->createDbTable();
    }

    public static function onUpdateForm(FormResultEntity $entity)
    {

    }

    public static function onDeleteForm(FormResultEntity $entity)
    {
        $conn = $entity->getConnection();

        if ($conn->isTableExists($entity->getDBTableName()))
        {
            $conn->dropTable($entity->getDBTableName());
        }
    }

    public static function getMessageEvents(FormResultEntity $entity, $langId)
    {
        return array();
    }

    public static function createEventForForm(FormResultEntity $entity)
    {
        $result = new Main\Result();

        $arLangSite = array();
        $dbRes = Main\SiteTable::getList(array());
        while ($arRes = $dbRes->fetch())
        {
            $arLangSite[$arRes['LANGUAGE_ID']][] = $arRes['LID'];
        }

        $dbLang = Main\Localization\LanguageTable::getList();

        while ($arLang = $dbLang->fetch())
        {
            $arEvents = static::getMessageEvents($entity, $arLang['LID']);

            foreach ($arEvents as $eventName=>$arEvent)
            {
                $eventName .= '_'.$entity->getFormId();

                $arFields = array(
                    'LID' => $arLang['LID'],
                    'EVENT_NAME' => $eventName,
                    'NAME' => $arEvent['NAME'],
                    'DESCRIPTION' => $arEvent['DESCRIPTION'],
                    'SORT' => $arEvent['SORT'] ?: 100,
                    'EVENT_TYPE' => $arEvent['EVENT_TYPE'],
                );

                foreach ($arEvent['FIELDS'] as $k=>$v)
                {
                    $arFields['DESCRIPTION'] .= $k.' - '.$v."\r\n";
                }

                $dbEventType = EventTypeTable::getList([
                    'filter' => [
                        '=LID' => $arLang['LID'],
                        '=EVENT_NAME' => $eventName,
                    ],
                ])->fetch();

                if (!$dbEventType)
                {
                    $dbResult = EventTypeTable::add($arFields);
                    if (!$dbResult->isSuccess())
                    {
                        $result->addErrors($dbResult->getErrors());
                        return $result;
                    }

                    $bNew = true;
                } else {
                    EventTypeTable::update($dbEventType['ID'], $arFields);
                    $bNew = false;
                }

                if ($bNew)
                {
                    if (!empty($arLangSite[$arLang['LID']])) //если есть сайты на этом языке
                    {
                        $messageData = array(
                            'EVENT_NAME' => $eventName,
                            'LID' => $arLang['LID'],
                            'ACTIVE' => 'Y',
                            'EMAIL_FROM' => $arEvent['EMAIL_FROM'],
                            'EMAIL_TO' => $arEvent['EMAIL_TO'],
                            'SUBJECT' => $arEvent['SUBJECT'],
                            'MESSAGE' => $arEvent['MESSAGE'],
                            'BODY_TYPE' => $arEvent['BODY_TYPE'] ?: 'text',
                        );

                        $dbResult = EventMessageTable::add($messageData);
                        if (!$dbResult->isSuccess())
                        {
                            $result->addErrors($dbResult->getErrors());
                            return $result;
                        }

                        $eventMessageId = $dbResult->getId();
                        foreach ($arLangSite[$arLang['LID']] as $siteId)
                        {
                            EventMessageSiteTable::add(array('EVENT_MESSAGE_ID'=>$eventMessageId,'SITE_ID'=>$siteId));
                        }
                    }
                }
            }
        }

        return $result;
    }
}
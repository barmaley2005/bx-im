<?php

namespace DevBx\Forms\FormTypes;

use Bitrix\Main\Entity\Base;
use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use DevBx\Core\Admin\AdminEdit;
use DevBx\Core\Admin\editFieldOld;
use DevBx\Forms;
use DevBx\Forms\FormResultEntity;
use Bitrix\Main\Entity;

Loc::loadLanguageFile(__FILE__);

class ReplyType extends Forms\BaseFormType
{
    public static function registerFormType(Event $event)
    {
        $event->getParameter('manager')->registerFormType(__CLASS__);
    }

    public static function getName()
    {
        return Loc::getMessage('DEVBX_FORMS_REPLY_FORM_NAME');
    }

    public static function getType()
    {
        return 'REPLY';
    }

    public static function compileEntity($arForm)
    {
        $entity = parent::compileEntity($arForm);

        $arFields = array(
            new Entity\IntegerField('ID', array("primary" => true, "autocomplete" => true, "title" => "ID")),
            new Entity\IntegerField('RESULT_ID', array("title" => Loc::getMessage("DEVBX_FORMS_FORM_REPLY_RESULT_ID"), "required" => true)),
            new Entity\ReferenceField('RESULT', $entity, array('this.RESULT_ID', 'ref.ID')),
            new Entity\IntegerField('LEFT_MARGIN', array("title" => Loc::getMessage("DEVBX_FORMS_FORM_REPLY_LEFT_MARGIN"))),
            new Entity\IntegerField('RIGHT_MARGIN', array("title" => Loc::getMessage("DEVBX_FORMS_FORM_REPLY_RIGHT_MARGIN"))),
            new Entity\IntegerField('DEPTH_LEVEL', array("title" => Loc::getMessage("DEVBX_FORMS_FORM_REPLY_DEPTH_LEVEL"))),
            new Entity\IntegerField('PARENT_ID', array("title" => Loc::getMessage("DEVBX_FORMS_FORM_REPLY_PARENT_ID"))),
            new Entity\BooleanField('ACTIVE', array("values" => array("N", "Y"), "default_value" => "Y", "title" => Loc::getMessage("DEVBX_FORMS_FORM_RESULT_ACTIVE"))),
            new Entity\IntegerField('CREATED_USER_ID', array('title'=>Loc::getMessage('DEVBX_FORMS_FORM_RESULT_CREATED_USER_ID'))),
            new Entity\ReferenceField('CREATED_USER', 'Bitrix\Main\UserTable', array('=this.CREATED_USER_ID' => 'ref.ID')),
            new Entity\DatetimeField('CREATED_DATE', array("title" => Loc::getMessage("DEVBX_FORMS_FORM_RESULT_CREATED_DATE"), "default_value" => new DateTime())),
            new Entity\IntegerField('MODIFIED_USER_ID', array('title'=>Loc::getMessage('DEVBX_FORMS_FORM_RESULT_MODIFIED_USER_ID'))),
            new Entity\ReferenceField('MODIFIED_USER', 'Bitrix\Main\UserTable', array('=this.MODIFIED_USER_ID' => 'ref.ID')),
            new Entity\DatetimeField('MODIFIED_DATE', array("title" => Loc::getMessage("DEVBX_FORMS_FORM_RESULT_MODIFIED_DATE"), "default_value" => new DateTime())),
        );

        $replyEntity = Base::compileEntity("FormReply" . $arForm['ID'] . "Table", $arFields, array(
                "namespace" => "DevBx\Forms",
                "table_name" => "b_devbx_form_reply_" . $arForm['ID'],
                "uf_id" => "DEVBX_FORM_REPLY_".$arForm['ID'],
                "parent" => "DevBx\Forms\BaseFormReplyTable"
            )
        );

        $entity->setParameter('replyEntity', $replyEntity);

        return $entity;
    }

    public static function onAddForm(FormResultEntity $entity)
    {
        parent::onAddForm($entity);

        $entity->getParameter('replyEntity')->createDbTable();
    }

    public static function onDeleteForm(FormResultEntity $entity)
    {
        parent::onDeleteForm($entity);

        $conn = $entity->getConnection();

        $replyEntity = $entity->getParameter('replyEntity');
        if (!$replyEntity)
            return;


        if ($conn->isTableExists($replyEntity->getDBTableName()))
        {
            $conn->dropTable($replyEntity->getDBTableName());
        }
    }

    public static function getModulePath()
    {
        static $modulePath = false;

        if ($modulePath)
            return $modulePath;

        $modulePath = \Bitrix\Main\Loader::getLocal('modules/devbx.forms');

        return $modulePath;
    }

    public static function showFieldUserFields(\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues)
    {
        $entity = Forms\FormManager::getInstance()->getFormInstance($arValues['ID']);

        if ($entity) {
            $edit->getTabControl()->BeginCustomField($key, Loc::getMessage('DEVBX_FORMS_FORM_REPLY_EDIT_USER_FIELDS'));

            echo '<td colspan="2">';
            $ENTITY_NAME = $entity->getFullName();

            require(static::getModulePath() . '/tools/devbx_form_fields_list.php');

            $edit->getTabControl()->EndCustomField($key);
        }
    }

    public static function showFieldReplyUserFields(\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues)
    {
        $entity = Forms\FormManager::getInstance()->getFormInstance($arValues['ID']);

        if ($entity) {

            $replyEntity = $entity->getParameter('replyEntity');

            if ($replyEntity)
            {
                $edit->getTabControl()->BeginCustomField($key, Loc::getMessage('DEVBX_FORMS_FORM_REPLY_EDIT_USER_FIELDS'));

                echo '<td colspan="2">';
                $ENTITY_NAME = $replyEntity->getFullName();

                require(static::getModulePath() . '/tools/devbx_form_fields_list.php');

                $edit->getTabControl()->EndCustomField($key);
            }
        }
    }

    public static function showAdminSettings(AdminEdit $edit)
    {
        $fields = [
            new EditFieldOld('USER_FIELDS', [
                'showField' => array(__CLASS__, 'showFieldUserFields')
            ])
        ];

        $edit->addTab('tab_form_edit2', Loc::getMessage('DEVBX_FORMS_FORM_REPLY_EDIT_TAB2'), Loc::getMessage('DEVBX_FORMS_FORM_REPLY_EDIT_TAB2_TITLE'), $fields);

        $fields = [
            new EditFieldOld('REPLY_USER_FIELDS', [
                'showField' => array(__CLASS__, 'showFieldReplyUserFields')
            ])
        ];

        $edit->addTab('tab_form_edit3', Loc::getMessage('DEVBX_FORMS_FORM_REPLY_EDIT_TAB3'), Loc::getMessage('DEVBX_FORMS_FORM_REPLY_EDIT_TAB3_TITLE'), $fields);
    }
}
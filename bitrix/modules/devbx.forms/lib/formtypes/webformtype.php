<?php

namespace DevBx\Forms\FormTypes;

use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail\Internal\EventMessageSiteTable;
use Bitrix\Main\Mail\Internal\EventMessageTable;
use Bitrix\Main\Mail\Internal\EventTypeTable;
use Bitrix\Main\Entity;
use DevBx\Core\Admin\AdminEdit;
use DevBx\Core\Admin\editFieldOld;
use DevBx\Forms;

Loc::loadMessages(__FILE__);

class WebFormType extends Forms\BaseFormType
{
    public static function registerFormType(Event $event)
    {
        //$event->getParameter('manager')->registerFormType(__CLASS__);
    }

    public static function compileEntity(Forms\EO_Form $form)
    {
        $entity = parent::compileEntity($form);

        $eventName = $entity->getNamespace() . $entity->getName() . '::';

        EventManager::getInstance()->addEventHandler(
            $entity->getModule(),
            $eventName . $entity->getDataClass()::EVENT_ON_AFTER_ADD,
            array(__CLASS__, 'onAfterAddHandler')
        );

        return $entity;
    }

    public static function getName()
    {
        return Loc::getMessage('DEVBX_FORMS_WEB_FORM_NAME');
    }

    public static function onAddForm(Forms\FormResultEntity $entity)
    {
        parent::onAddForm($entity);
    }

    public static function getMessageEvents(Forms\FormResultEntity $entity, $langId)
    {
        return [];
    }

    public static function onUpdateForm(Forms\FormResultEntity $entity)
    {
        parent::onUpdateForm($entity);
    }

    public static function onDeleteForm(Forms\FormResultEntity $entity)
    {
        parent::onDeleteForm($entity);
    }

    public static function onAfterAddHandler(Entity\Event $event)
    {
        $result = new Entity\EventResult;

        return $result;
    }

    public static function showFieldUserFields(\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues)
    {

        if ($edit->isNewForm())
            return;

        $entity = Forms\FormManager::getInstance()->getFormInstance($arValues['ID']);

        if ($entity) {
            $edit->getTabControl()->BeginCustomField($key, Loc::getMessage('DEVBX_FORMS_FORM_EDIT_USER_FIELDS'));
            echo '<td colspan="2">';
            $ENTITY_NAME = $entity->getFullName();

            require(static::getModulePath() . '/tools/devbx_form_fields_list.php');

            $edit->getTabControl()->EndCustomField($key);
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


    public static function showAdminSettings(AdminEdit $edit)
    {
        if (!$edit->isNewForm()) {
            $fields = [
                new EditFieldOld('USER_FIELDS', [
                    'showField' => array(__CLASS__, 'showFieldUserFields')
                ])
            ];

            $edit->addTab('tab_form_edit2', Loc::getMessage('DEVBX_FORMS_FORM_EDIT_TAB2'), Loc::getMessage('DEVBX_FORMS_FORM_EDIT_TAB2_TITLE'), $fields);
        }
    }

    public static function adminMenu(&$arItems)
    {
        $arFormResultItems = [];

        $dbRes = Forms\FormTable::getList(array(
            'filter' => array('=FORM_TYPE' => static::getType()),
            'select' => array('ID', 'NAME' => 'LANG_NAME.NAME'),
        ));

        while ($arRes = $dbRes->fetch()) {
            $arFormResultItems [] = [
                'text' => Loc::getMessage('DEVBX_FORMS_MENU_FORM_RESULT_LIST_ITEM', array('#NAME#' => $arRes['NAME'])),
                'page_icon' => 'default_page_icon',
                'url' => 'devbx_form_result_list.php?lang=' . LANGUAGE_ID . '&FORM_ID=' . $arRes['ID'],
                'more_url' => array('devbx_form_result_edit.php?lang=' . LANGUAGE_ID . '&FORM_ID=' . $arRes['ID']),
            ];
        }

        $arItems[] = [
            'text' => Loc::getMessage('DEVBX_FORMS_MENU_FORM_RESULT_LIST'),
            'page_icon' => 'default_page_icon',
            'items_id' => 'menu_devbx_form_result_list',
            'items' => $arFormResultItems,
        ];
    }

    public static function getType()
    {
        return 'WEB_FORM';
    }
}


<?php

use DevBx\Forms\WebForm\Components;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CBitrixComponent::includeComponentClass("devbx:form");

class CDevBxFormsWebForm extends CDevBxFormsForm
{

    protected function initWebForm()
    {
        $arResult = &$this->arResult;

        $obForm = new \DevBx\Forms\WebForm\WOForm();

        $remoteResult = $obForm->setValues($arResult['FORM']['SETTINGS']);
        if (!$remoteResult->isSuccess())
        {
            foreach ($remoteResult->getErrorMessages() as $err)
                $this->addResultError($err);

            return;
        }

        $wizard = \DevBx\Forms\Wizards\WebForm\Wizard::getInstance();

        $fieldManager = $wizard->getFieldManager();

        //$messages = Loc::loadLanguageFile(__FILE__);
        $messages = array();

        $messages = array_merge($messages, $wizard->getLangMessages());
        $messages = array_merge($messages, Components\Manager::getScopeLangMessages(Components\Manager::SCOPE_USER));

        $messages = array_merge($messages, \DevBx\Forms\WebForm\Fields\Base::getLangMessages());

        $arWebFormElements = array();

        foreach ($fieldManager->getWebFormFieldsGroup() as $group) {
            $arJSGroup = array(
                'NAME' => $group->getName(),
                'ITEMS' => array(),
            );

            foreach ($group->getFields() as $field) {
                $arJSGroup['ITEMS'][] = array(
                    'DATA' => $field::getFieldData(),
                );

                $messages = array_merge($messages, $field::getLangMessages());
            }

            $arWebFormElements[] = $arJSGroup;
        }

        $arWebFormFields = array();

        $fields = $obForm->getRegisteredFormFields();

        foreach ($fields as $field) {
            $field->includePublicJS();
            $arWebFormFields = array_merge($arWebFormFields, $field->getFormFields());
        }

        $arResult['WEB_FORM'] = array(
            'CONFIG' => $obForm->toArray(\DevBx\Forms\WebForm\WOValues::PUBLIC),
            'FORM_ELEMENTS' => $arWebFormElements,
            'FORM_FIELDS' => $arWebFormFields,
        );

        $arResult['JS_MESSAGES'] = $messages;

        $arCulture = array();

        $culture = \Bitrix\Main\Context::getCurrent()->getCulture();

        foreach ($culture->entity->getScalarFields() as $field) {
            if ($field->isPrimary())
                continue;

            $arCulture[$field->getName()] = $culture->get($field->getName());
        }

        $arResult['CULTURE'] = $arCulture;

        $remoteResult = \DevBx\Forms\DB\FormSessionTable::getNewSID($arResult['FORM']['ID'], SITE_ID);
        if (!$remoteResult->isSuccess()) {
            foreach ($remoteResult->getErrorMessages() as $err)
                $this->addResultError($err);

            return;
        }

        $formSID = $remoteResult->getData()['SID'];

        $arResult['WEB_FORM_SID'] = $formSID;
    }

    protected function showForm()
    {
        //define('VUEJS_DEBUG', true);

        \Bitrix\Main\UI\Extension::load("ui.vue3");
        \Bitrix\Main\UI\Extension::load("ui.vue3.vuex");

        $this->initWebForm();

        $this->includeComponentTemplate();
    }
}

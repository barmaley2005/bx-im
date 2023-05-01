<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arTemplateParameters['USE_BOOTSTRAP'] = array(
    'PARENT' => 'VISUAL',
    'NAME' => GetMessage("DEVBX_FORMS_COMPONENT_FORM_USE_BOOTSTRAP"),
    'TYPE' => 'CHECKBOX',
    'DEFAULT' => "Y",
);

$arTemplateParameters['MSG_BUTTON_SUBMIT_TEXT'] = array(
    'PARENT' => 'VISUAL',
    'NAME' => GetMessage("DEVBX_FORMS_COMPONENT_FORM_SUBMIT_BUTTON_NAME"),
    'TYPE' => 'STRING',
    'DEFAULT' => GetMessage("DEVBX_FORMS_COMPONENT_FORM_SUBMIT_BUTTON_NAME_DEFAULT"),
);

$arTemplateParameters['MSG_SUCCESS'] = array(
    'PARENT' => 'VISUAL',
    'NAME' => GetMessage("DEVBX_FORMS_COMPONENT_FORM_MSG_SUCCESS"),
    'TYPE' => 'STRING',
    'DEFAULT' => GetMessage("DEVBX_FORMS_COMPONENT_FORM_MSG_SUCCESS_DEFAULT"),
);


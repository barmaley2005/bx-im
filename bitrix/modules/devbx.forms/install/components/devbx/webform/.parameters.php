<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader;

/** @var array $arCurrentValues */

if(!Loader::includeModule("devbx.forms"))
    return;

$arFormList = array(
    '-' => '',
);

$iterator = \DevBx\Forms\FormTable::getList([
    'filter' => array(
        '=FORM_TYPE' => DevBx\Forms\Wizards\WebForm\Wizard::getTemplateId(),
    ),
    'select'=>array('ID','NAME'=>'LANG_NAME.NAME'),
]);

while ($arForm = $iterator->fetch())
{
    $arFormList[$arForm['ID']] = '['.$arForm['ID'].'] '.$arForm['NAME'];
}

$arComponentParameters = array(
    "GROUPS" => array(
    ),
    "PARAMETERS" => array(
        "FORM_ID" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("DEVBX_FORMS_COMPONENT_WEB_FORM_ID"),
            "TYPE" => "LIST",
            "VALUES" => $arFormList,
            "DEFAULT" => '',
            "REFRESH" => "Y",
        ),
    ),
);

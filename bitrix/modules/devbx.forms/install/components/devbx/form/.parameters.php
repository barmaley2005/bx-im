<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

global $USER_FIELD_MANAGER;

use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;

if(!Loader::includeModule("devbx.forms"))
    return;

$arFormList = array(
    '' => Loc::getMessage('DEVBX_FORMS_COMPONENT_FORM_NOT_SELECTED'),
);

$iterator = DevBx\Forms\FormTable::getList(array('select'=>array('ID','NAME'=>'LANG_NAME.NAME')));
while ($arForm = $iterator->fetch())
{
    $arFormList[$arForm['ID']] = '['.$arForm['ID'].'] '.$arForm['NAME'];
}

$arAvailableFields = array();

if ($arCurrentValues["FORM_ID"]>0)
{
    $entity = \DevBx\Forms\FormManager::getInstance()->getFormInstance($arCurrentValues["FORM_ID"]);

    if ($entity)
    {
        if ($entity->getDataClass()::getUfId())
        {
            $arFields = $USER_FIELD_MANAGER->GetUserFields($entity->getDataClass()::getUfId(), 0, LANGUAGE_ID);

            foreach ($arFields as $field)
            {
                $arAvailableFields[$field['FIELD_NAME']] = $field['EDIT_FORM_LABEL'] ? '['.$field['FIELD_NAME'].'] '.$field['EDIT_FORM_LABEL'] : $field['FIELD_NAME'];
            }
        }
    }
}

$arComponentParameters = array(
    "GROUPS" => array(
    ),
    "PARAMETERS" => array(
        "AJAX_MODE" => array(),
		"FORM_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("DEVBX_FORMS_COMPONENT_FORM_ID"),
			"TYPE" => "LIST",
            "VALUES" => $arFormList,
			"DEFAULT" => '',
            "REFRESH" => "Y",
		),
        "AJAX_LOAD_FORM" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("DEVBX_FORMS_COMPONENT_AJAX_LOAD_FORM"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ),
        "CHECK_AJAX_SESSID" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("DEVBX_FORMS_COMPONENT_CHECK_AJAX_SESSID"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ),
        "ACTION_VARIABLE" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("DEVBX_FORMS_COMPONENT_FORM_ACTION"),
            "TYPE" => "STRING",
            "DEFAULT" => "form-action",
        ),
        "READ_ONLY_FIELDS" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("DEVBX_FORMS_COMPONENT_READ_ONLY_FIELDS"),
            "TYPE" => "LIST",
            "VALUES" => $arAvailableFields,
            "DEFAULT" => '',
            "MULTIPLE" => "Y",
            "SIZE" => 8,
            "ADDITIONAL_VALUES" => "Y",
        ),
        "DEFAULT_FIELDS" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("DEVBX_FORMS_COMPONENT_DEFAULT_FIELDS"),
            "TYPE" => "LIST",
            "VALUES" => $arAvailableFields,
            "DEFAULT" => '',
            "MULTIPLE" => "Y",
            "SIZE" => 8,
            "ADDITIONAL_VALUES" => "Y",
            "REFRESH" => "Y",
        ),
    ),
);

if (is_array($arCurrentValues['DEFAULT_FIELDS']))
{
    foreach ($arCurrentValues['DEFAULT_FIELDS'] as $fieldName)
    {
        $fieldName = trim($fieldName);
        if (empty($fieldName))
            continue;

        if (array_key_exists($fieldName, $arAvailableFields))
        {
            $jsParams = array(
                'formId' => $arCurrentValues['FORM_ID'],
                'field' => $fieldName,
                'value' => $arCurrentValues['DEFAULT_FIELD_VALUE_'.$fieldName]
            );

            $jsData = array(
                'ajaxPath'=> $componentPath.'/settings/ajax.php',
                'params' => $jsParams,
            );

            $signer = new \Bitrix\Main\Security\Sign\Signer;
            $jsData['fieldParams'] = $signer->sign(base64_encode(serialize($jsParams)), 'devbx.form');

            $arComponentParameters['PARAMETERS']['DEFAULT_FIELD_VALUE_'.$fieldName] = array(
                'PARENT' => 'DATA_SOURCE',
                'NAME' => GetMessage('DEVBX_FORMS_COMPONENT_DEFAULT_FIELD_VALUE',array('#NAME#'=>$arAvailableFields[$fieldName])),
                'TYPE' => 'CUSTOM',
                'JS_FILE' => $componentPath.'/settings/script.js?r='.mt_rand(0,10000),
                'JS_EVENT' => 'initDevBxFormCustomFieldControl',
                'JS_DATA' => Json::encode($jsData),
                'DEFAULT' => '',
            );
        } else
        {
            $arComponentParameters['PARAMETERS']['DEFAULT_FIELD_VALUE_'.$fieldName] = array(
                'PARENT' => 'DATA_SOURCE',
                'NAME' => GetMessage('DEVBX_FORMS_COMPONENT_DEFAULT_FIELD_VALUE',array('#NAME#'=>$fieldName)),
                'TYPE' => 'STRING',
                'DEFAULT' => '',
            );
        }
    }
}


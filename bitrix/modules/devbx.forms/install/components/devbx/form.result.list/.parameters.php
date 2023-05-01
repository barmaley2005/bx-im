<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

use Bitrix\Main\Loader;
use Bitrix\Main\UserField\Internal\UserFieldHelper;
use DevBx\Forms\FormTable;

if(!Loader::includeModule("devbx.forms"))
    return;

if(!Loader::includeModule("iblock"))
    return;

$arFormList = array();

$dbRes = DevBx\Forms\FormTable::getList(array('select'=>array('ID','NAME'=>'LANG_NAME.NAME')));
while ($arRes = $dbRes->fetch())
{
    $arFormList[$arRes['ID']] = '['.$arRes['ID'].'] '.$arRes['NAME'];
}

$arSorts = array("ASC"=>GetMessage("C_DEVBX_FORM_RESULT_LIST_SORT_ASC"), "DESC"=>GetMessage("C_DEVBX_FORM_RESULT_LIST_SORT_DESC"));
$arSortFields = array(
    "ID"=>GetMessage("C_DEVBX_FORM_RESULT_LIST_FIELD_ID"),
    "ACTIVE"=>GetMessage("C_DEVBX_FORM_RESULT_LIST_FIELD_ACTIVE"),
    "CREATED_DATE"=>GetMessage("C_DEVBX_FORM_RESULT_LIST_FIELD_CREATED_DATE"),
    "MODIFIED_DATE"=>GetMessage("C_DEVBX_FORM_RESULT_LIST_FIELD_MODIFIED_DATE"),
);

$arFormFields = array(
    "ID"=>GetMessage("C_DEVBX_FORM_RESULT_LIST_FIELD_ID"),
    "ACTIVE"=>GetMessage("C_DEVBX_FORM_RESULT_LIST_FIELD_ACTIVE"),
    "CREATED_DATE"=>GetMessage("C_DEVBX_FORM_RESULT_LIST_FIELD_CREATED_DATE"),
    "MODIFIED_DATE"=>GetMessage("C_DEVBX_FORM_RESULT_LIST_FIELD_MODIFIED_DATE"),
);

if ($arCurrentValues["FORM_ID"]>0)
{
    $obForm = \DevBx\Forms\FormManager::getInstance()->getFormInstance($arCurrentValues["FORM_ID"]);

    if ($obForm)
    {
        $userFieldManager = UserFieldHelper::getInstance()->getManager();

        foreach ($userFieldManager->GetUserFields($obForm->getUfId(), 0, LANGUAGE_ID) as $arField)
        {
            $arFormFields[$arField['FIELD_NAME']] = '['.$arField['FIELD_NAME'].'] '.$arField['LIST_COLUMN_LABEL'];

            $arSortFields[$arField['FIELD_NAME']] = '['.$arField['FIELD_NAME'].'] '.$arField['LIST_COLUMN_LABEL'];
        }
    }
}
//$arFormFields

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
        "RESULTS_COUNT" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("C_DEVBX_FORM_RESULT_LIST_RESULTS_COUNT"),
            "TYPE" => "STRING",
            "DEFAULT" => "20",
        ),
        "ONLY_ACTIVE_RESULTS" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("C_DEVBX_FORM_RESULT_LIST_ONLY_ACTIVE_RESULTS"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        "SORT_BY1" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("C_DEVBX_FORM_RESULT_LIST_SORT_BY1"),
            "TYPE" => "LIST",
            "DEFAULT" => "CREATED_DATE",
            "VALUES" => $arSortFields,
            "ADDITIONAL_VALUES" => "Y",
        ),
        "SORT_ORDER1" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("C_DEVBX_FORM_RESULT_LIST_SORT_ORDER1"),
            "TYPE" => "LIST",
            "DEFAULT" => "DESC",
            "VALUES" => $arSorts,
            "ADDITIONAL_VALUES" => "Y",
        ),
        "SORT_BY2" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("C_DEVBX_FORM_RESULT_LIST_SORT_BY2"),
            "TYPE" => "LIST",
            "DEFAULT" => "ID",
            "VALUES" => $arSortFields,
            "ADDITIONAL_VALUES" => "Y",
        ),
        "SORT_ORDER2" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("C_DEVBX_FORM_RESULT_LIST_SORT_ORDER2"),
            "TYPE" => "LIST",
            "DEFAULT" => "ASC",
            "VALUES" => $arSorts,
            "ADDITIONAL_VALUES" => "Y",
        ),
        "FILTER_NAME" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("C_DEVBX_FORM_RESULT_LIST_FILTER_NAME"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ),
        "DISPLAY_FIELDS" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("C_DEVBX_FORM_RESULT_LIST_DISPLAY_FIELDS"),
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "VALUES" => $arFormFields,
            "DEFAULT" => array("ID"),
            "SIZE" => 8
        ),
        "CREATED_DATE_FORMAT" => CIBlockParameters::GetDateFormat(GetMessage("C_DEVBX_FORM_RESULT_LIST_FIELD_CREATED_DATE_FORMAT"), "DATA_SOURCE"),
        "MODIFIED_DATE_FORMAT" => CIBlockParameters::GetDateFormat(GetMessage("C_DEVBX_FORM_RESULT_LIST_FIELD_MODIFIED_DATE_FORMAT"), "DATA_SOURCE"),
    ),
);


CIBlockParameters::AddPagerSettings(
    $arComponentParameters,
    GetMessage("C_DEVBX_FORM_RESULT_LIST_PAGER_NAME"), //$pager_title
    true, //$bDescNumbering
    true, //$bShowAllParam
    true, //$bBaseLink
    $arCurrentValues["PAGER_BASE_LINK_ENABLE"]==="Y" //$bBaseLinkEnabled
);


<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use DevBx\Core\Admin\ListHelper;

Loc::loadMessages(__FILE__);

Loader::includeModule("devbx.core");
Loader::includeModule("devbx.forms");

$FORM_ID = intval($FORM_ID);
if ($FORM_ID<=0)
    return;

$obForm = \DevBx\Forms\FormManager::getInstance()->compileFormEntity($FORM_ID);
if (!$obForm)
    return;

$userFieldManager = \Bitrix\Main\UserField\Internal\UserFieldHelper::getInstance()->getManager();
$arUserFields = $userFieldManager->GetUserFields($obForm->getUfId(),0, LANGUAGE_ID);

if (!array_key_exists($FIELD_NAME, $arUserFields))
    return;

$ELEMENT_ID = intval($ELEMENT_ID);
if ($ELEMENT_ID<=0)
    return;

$strListAjaxPath = array(
    'LINK' => '/bitrix/admin/'.basename(__FILE__),
    'PARAMS' => array('lang'=>LANGUAGE_ID,'FORM_ID'=>$FORM_ID,'FIELD_NAME'=>$FIELD_NAME,'ELEMENT_ID'=>$ELEMENT_ID)
);

$arRowView = array(
    'CREATED_USER_ID' => array(ListHelper::class, 'viewUser'),
    'MODIFIED_USER_ID' => array(ListHelper::class, 'viewUser'),
    'SITE_ID' => array(ListHelper::class, 'viewSiteID'),
);


$list = new DevBx\Core\Admin\AdminList('devbx.forms', $obForm, array(
        //'TITLE' => Loc::getMessage("DEVBX_FORMS_FORM_RESULT_LIST_TITLE"),
        //'SUBLIST_ID' => 'DEVBX_FORM_IBLOCK_RESULT_LIST_'.$FORM_ID.'_'.$FIELD_NAME,
        'ALLOW_DELETE' => 'Y',
        'ALLOW_EDIT' => 'Y',
        'ALLOW_ADD' => 'Y',
        'ALLOW_ACTIVATE' => 'Y',
        'READ_ONLY_FIELDS' => array('SITE_ID', 'CREATED_USER_ID', 'CREATED_DATE', 'MODIFIED_USER_ID', 'MODIFIED_DATE'),
        'FILTER_HEADER' => '<input type=hidden name="FORM_ID" value="' . $FORM_ID . '">'.
            '<input type=hidden name="FIELD_NAME" value="'.$FIELD_NAME.'">'.
            '<input type=hidden name="ELEMENT_ID" value="'.$ELEMENT_ID.'">',
    )
);

$list->setTableId('DEVBX_FORM_IBLOCK_RESULT_LIST_'.$FORM_ID.'_'.$FIELD_NAME);

$list->addActionGroupQueryParams(array('FORM_ID'=>$formId,'FIELD_NAME'=>$FIELD_NAME,'ELEMENT_ID'=>$ELEMENT_ID));

$list->addRowView('SITE_ID', ListHelper::getViewSiteID());
$list->addRowView('CREATED_USER_ID', ListHelper::getViewUser());
$list->addRowView('MODIFIED_USER_ID', ListHelper::getViewUser());

$list->addFilterInput('SITE_ID', ListHelper::getFilterSiteID());


$list->setCallbackApplyFilter(function (&$arFilter, &$arFilterValues) use ($FIELD_NAME, $ELEMENT_ID) {
    $arFilter["=".$FIELD_NAME] = $ELEMENT_ID;
});

$list->setShowFilterForm(false);
$list->setFileEdit("devbx_form_result_edit.php");
$list->addFileEditParams(array('FORM_ID' => $FORM_ID, $FIELD_NAME=>$ELEMENT_ID, 'popupwindow'=>'y'));
$list->setOrder("ID", "desc");

$list->display(true, $strListAjaxPath);

?>


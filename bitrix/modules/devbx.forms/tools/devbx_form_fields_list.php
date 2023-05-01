<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

Loader::includeModule("devbx.core");
Loader::includeModule("devbx.forms");

DevBx\Forms\FormManager::getInstance()->loadAllForms();

$ENTITY_NAME = \Bitrix\Main\ORM\Entity::normalizeEntityClass($ENTITY_NAME);
if (!class_exists($ENTITY_NAME))
    return;

if (!is_subclass_of($ENTITY_NAME, \Bitrix\Main\Entity\DataManager::class))
    return;

if ($ENTITY_NAME::getEntity()->getModule() != 'devbx.forms')
    return;

$entityId = $ENTITY_NAME::getEntity()->getUfId();
if (empty($entityId))
    return;

$strListAjaxPath = '/bitrix/admin/'.basename(__FILE__).'?lang='.LANGUAGE_ID.'&ENTITY_NAME='.$ENTITY_NAME.'&';

$strListAjaxPath = array(
    'LINK' => '/bitrix/admin/'.basename(__FILE__),
    'PARAMS' => array('lang'=>LANGUAGE_ID,'ENTITY_NAME'=>urlencode($ENTITY_NAME))
);

\Bitrix\Main\UserFieldTable::getEntity()->addField(\Bitrix\Main\UserFieldTable::getLabelsReference('LANG', LANGUAGE_ID));
\Bitrix\Main\UserFieldTable::getEntity()->addField(
    new \Bitrix\Main\Entity\ExpressionField('LANG_NAME', '%s', ['LANG.LIST_COLUMN_LABEL'],['title'=>Loc::getMessage('DEVBX_FORMS_FIELDS_LIST_USER_FIELD_LANG_NAME')])
);

$list = new \DevBx\Core\Admin\AdminList('devbx.forms', '\Bitrix\Main\UserFieldTable', array(
        'TITLE' => Loc::getMessage("DEVBX_FORMS_FIELDS_LIST_TITLE"),
        'ALLOW_DELETE' => 'Y',
        'ALLOW_ADD' => 'Y',
        'ALLOW_EDIT' => 'Y',
        'HEADER_FIELDS' => array(
            'ID',
            'FIELD_NAME',
            'SORT',
            'LANG_NAME',
            'MULTIPLE',
            'MANDATORY',
            'SHOW_FILTER',
            'SHOW_IN_LIST',
            'EDIT_IN_LIST',
        ),
        'ROW_VIEW' => array(
        ),
        'EDIT_FIELDS' => array(
            'SHOW_FILTER' => function (CAdminListRow $row, $primary, $key, $arRes) {
                $arEnum = array(
                    'N' => Loc::getMessage('DEVBX_FORM_FIELDS_FILTER_SHOW_N'),
                    'I' => Loc::getMessage('DEVBX_FORM_FIELDS_FILTER_SHOW_I'),
                    'E' => Loc::getMessage('DEVBX_FORM_FIELDS_FILTER_SHOW_E'),
                    'S' => Loc::getMessage('DEVBX_FORM_FIELDS_FILTER_SHOW_S'),
                );

                $row->AddSelectField($key, $arEnum);
            }
        ),
        'READ_ONLY_FIELDS' => array(
            'FIELD_NAME',
            'MULTIPLE',
        ),
        'SUBLIST_ID' => $ENTITY_NAME,
    )
);

$list->setTableId("tbl_" . str_replace('\\', '_', $ENTITY_NAME));

$list->setCallbackApplyFilter(function (&$arFilter, &$arFilterValues) use ($entityId) {
    $arFilter["=ENTITY_ID"] = $entityId;
});

$list->setShowFilterForm(false);
$list->setFileEdit("devbx_form_fields_edit.php");
$list->addFileEditParams(array('ENTITY_NAME' => $ENTITY_NAME));
$list->setOrder("ID", "desc");

$list->setUpdateFunction(function ($ID, $arFields) {
    global $APPLICATION;

    $result = new \Bitrix\Main\Result();

    $ute = new CUserTypeEntity();

    if (!$ute->Update($ID, $arFields)) {
        $ex = $APPLICATION->GetException();
        if ($ex) {
            $result->addError(new \Bitrix\Main\Error($ex->GetString()));
        } else {
            $result->addError(new \Bitrix\Main\Error('Unknown error'));
        }
    }

    return $result;
});

$list->setCallbackAction('delete', function($lAdmin, $action, $arID) {

    global $APPLICATION;

    $ute = new CUserTypeEntity();

    foreach ($arID as $ID)
    {
        if (!$ute->Delete($ID))
        {
            $ex = $APPLICATION->GetException();

            if ($ex)
            {
                $lAdmin->AddGroupError($ex->GetString(), $ID);
            } else
            {
                $lAdmin->AddGroupError('unknown error', $ID);
            }
        }
    }

    return false;
});

if (\Bitrix\Main\Context::getCurrent()->getRequest()->isPost())
{
    $list->setEpilog(function() {
        ?>
        <script>
            top.BX.onCustomEvent('DevBxFormsReloadSettings');
        </script>
        <?
    });
}

$list->display(true, $strListAjaxPath);

?>


<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\GroupTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use DevBx\Forms\FormLangNameTable;

Loc::loadMessages(__FILE__);

Loader::includeModule("devbx.core");

$list = new DevBx\Core\Admin\AdminList('devbx.forms', '\DevBx\Forms\FormTable', array(
        'TITLE' => Loc::getMessage("DEVBX_FORMS_FORM_LIST_TITLE"),
        'ALLOW_DELETE' => 'Y',
        'ALLOW_EDIT' => 'Y',
        'ALLOW_ADD' => 'Y',
        'HEADER_FIELDS' => array(
            'ID',
            'NAME' => array('CUSTOM'=>true,'TITLE'=>Loc::getMessage('DEVBX_FORMS_FORM_LIST_FORM_NAME')),
            'VIEW_GROUPS',
            'WRITE_GROUPS',
        ),
        'ROW_VIEW' => array(
            'VIEW_GROUPS' => function(CAdminListRow $row, $primary, $key, $arRes) {
                $value = unserialize($arRes[$key]);

                if (!empty($value))
                {
                    $ar = GroupTable::getList(array('order'=>array('C_SORT'=>"ASC"),'filter'=>array('ID'=>$value)))->fetchAll();

                    $row->AddViewField($key, implode(', ', array_column($ar, 'NAME')));
                }
            },
            'WRITE_GROUPS' => function(CAdminListRow $row, $primary, $key, $arRes) {
                $value = unserialize($arRes[$key]);

                if (!empty($value))
                {
                    $ar = GroupTable::getList(array('order'=>array('C_SORT'=>"ASC"),'filter'=>array('ID'=>$value)))->fetchAll();

                    $row->AddViewField($key, implode(', ', array_column($ar, 'NAME')));
                }
            },
        ),
        'READ_ONLY_FIELDS' => array('VIEW_GROUPS','WRITE_GROUPS')
    )
);

$list->setCallbackGetSelectFields(function(&$arSelect, &$arRuntime) {
    $arSelect = array(
        'ID',
        'NAME' => 'LANG_NAME.NAME',
        'VIEW_GROUPS',
        'WRITE_GROUPS',
    );
});

$list->setCallbackApplyFilter(function(&$arFilter, &$arFilterValues) {
    //$arFilter['LANG_NAME.LANGUAGE_ID'] = LANGUAGE_ID;
});

$list->display();


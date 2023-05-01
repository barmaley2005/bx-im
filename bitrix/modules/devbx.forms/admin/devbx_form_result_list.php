<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use DevBx\Core\Admin\ListHelper;

Loc::loadMessages(__FILE__);

Loader::includeModule("devbx.core");
Loader::includeModule("devbx.forms");

$formId = intval($_REQUEST['FORM_ID']);

$arForm = \DevBx\Forms\FormTable::getRowById($formId);

if ($arForm) {
    $formResult = \DevBx\Forms\FormManager::getInstance()->getFormInstance($formId);

    $arRowView = array(
        'CREATED_USER_ID' => array(ListHelper::class, 'viewUser'),
        'MODIFIED_USER_ID' => array(ListHelper::class, 'viewUser'),
        'SITE_ID' => array(ListHelper::class, 'viewSiteID'),
    );


    $list = new DevBx\Core\Admin\AdminList('devbx.forms', $formResult, array(
            'TITLE' => Loc::getMessage("DEVBX_FORMS_FORM_RESULT_LIST_TITLE"),
            'ALLOW_DELETE' => 'Y',
            'ALLOW_EDIT' => 'Y',
            'ALLOW_ADD' => 'Y',
            'ALLOW_ACTIVATE' => 'Y',
            'READ_ONLY_FIELDS' => array('SITE_ID', 'CREATED_USER_ID', 'CREATED_DATE', 'MODIFIED_USER_ID', 'MODIFIED_DATE'),
            'FILTER_HEADER' => '<input type=hidden name="FORM_ID" value="' . $formId . '">',
        )
    );

    $list->addFileEditParams(array('FORM_ID' => $formId));
    $list->addActionGroupQueryParams(array('FORM_ID'=>$formId));

    $list->addRowView('SITE_ID', ListHelper::getViewSiteID());
    $list->addRowView('CREATED_USER_ID', ListHelper::getViewUser());
    $list->addRowView('MODIFIED_USER_ID', ListHelper::getViewUser());

    $list->addFilterInput('SITE_ID', ListHelper::getFilterSiteID());

    $list->display();
}


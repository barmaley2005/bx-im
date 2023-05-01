<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$aMenu = array();

if ('D' < $APPLICATION->GetGroupRight("devbx.forms")) {

    if (!\Bitrix\Main\Loader::includeModule("devbx.forms"))
        return false;

    $arItems [] = [
        "text" => Loc::getMessage('DEVBX_FORMS_MENU_FORM_LIST'),
        "page_icon" => "default_page_icon",
        "url" => "devbx_form_list.php?lang=" . LANGUAGE_ID,
        "more_url" => array("devbx_form_edit.php"),
        "items_id" => "menu_devbx_form_list",
    ];

    foreach (\DevBx\Forms\FormManager::getInstance()->getFormType() as $formType)
    {
        $formType::adminMenu($arItems);
    }

    foreach (\DevBx\Forms\WizardManager::getInstance()->getWizard() as $wizard)
    {
        $wizard::adminMenu($arItems);
    }

    $aMenu = array(
        "parent_menu" => "global_menu_services",
        "sort" => "500",
        "text" => Loc::getMessage('DEVBX_FORMS_MENU_TEXT'),
        "title" => Loc::getMessage('DEVBX_FORMS_MENU_TITLE'),
        "icon" => "default_menu_icon",
        "page_icon" => "default_page_icon",
        "items_id" => "menu_devbx_forms",
        "items" => $arItems,
    );
    return $aMenu;

}

return false;

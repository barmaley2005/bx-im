<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

Loader::includeModule("devbx.core");
Loader::includeModule("devbx.forms");

$formId = intval($_REQUEST['FORM_ID']);

$arForm = \DevBx\Forms\FormTable::getRowById($formId);

if ($arForm) {

    $formResult = \DevBx\Forms\FormManager::getInstance()->getFormInstance($formId);

    $userFieldManager = \Bitrix\Main\UserField\Internal\UserFieldHelper::getInstance()->getManager();

    $arUserFields = array();
    $arEditFields = array('ID','ACTIVE','SITE_ID','CREATED_USER_ID','CREATED_DATE','MODIFIED_USER_ID','MODIFIED_DATE');

    $arEditFields['ID'] = array(
        'showField' => function (\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) use ($arUserFields, $userFieldManager) {

            $edit->getTabControl()->BeginCustomField($key, $obField->getTitle());

            echo '<tr><td>' . $obField->getTitle() . '</td><td>' . $arValues[$key] . '</td></tr>';

            $edit->getTabControl()->EndCustomField($key);

        }
    );

    $arEditFields['SITE_ID'] = array(
        'showField' => function (\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) use ($arUserFields, $userFieldManager) {

            if ($edit->isNewForm())
            {
                $arSelect = [];

                $dbRes = \Bitrix\Main\SiteTable::getList();
                while ($arRes = $dbRes->Fetch())
                {
                    $arSelect[$arRes['LID']] = '['.$arRes['LID'].'] '.$arRes['NAME'];
                }

                $edit->getTabControl()->AddDropDownField($key, $obField->getTitle(), true, $arSelect);
            } else
            {
                $edit->getTabControl()->BeginCustomField($key, $obField->getTitle());

                echo '<tr><td>'.$obField->getTitle().'</td><td>';

                if ($arValues[$key])
                {
                    $arSite = \Bitrix\Main\SiteTable::getRowById($arValues[$key]);
                    if ($arSite)
                        echo htmlspecialcharsbx('['.$arSite['LID'].'] '.$arSite['NAME']);
                }
                echo '</td></tr>';

                $edit->getTabControl()->EndCustomField($key);
            }


        }
    );

    $arEditFields['CREATED_USER_ID'] = array(
        'showField' => function (\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) use ($arUserFields, $userFieldManager) {
            $arUser = \CUser::GetByID($arValues[$key])->Fetch();

            $edit->getTabControl()->BeginCustomField($key, $obField->getTitle());
            echo '<tr><td>'.htmlspecialcharsbx($obField->getTitle()).'</td><td>';
            if ($arUser)
            {
                echo '<a href="user_edit.php?lang=' . LANGUAGE_ID . '&ID=' . $arValues[$key] . '">[' . $arValues[$key] . '] ' . htmlspecialcharsbx($arUser["LOGIN"]);
            } else
            {
                echo htmlspecialcharsbx($arValues[$key]);
            }
            echo '</td></tr>';
            $edit->getTabControl()->EndCustomField($key);
        }
    );

    $arEditFields['CREATED_DATE'] = array(
        'showField' => function (\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) use ($arUserFields, $userFieldManager) {
            $arUser = \CUser::GetByID($arValues[$key])->Fetch();

            $edit->getTabControl()->BeginCustomField($key, $obField->getTitle());
            echo '<tr><td>'.htmlspecialcharsbx($obField->getTitle()).'</td><td>';
            echo htmlspecialcharsbx($arValues[$key]);
            echo '</td></tr>';
            $edit->getTabControl()->EndCustomField($key);
        }
    );

    $arEditFields['MODIFIED_USER_ID'] = array(
        'showField' => function (\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) use ($arUserFields, $userFieldManager) {
            $arUser = \CUser::GetByID($arValues[$key])->Fetch();

            $edit->getTabControl()->BeginCustomField($key, $obField->getTitle());
            echo '<tr><td>'.htmlspecialcharsbx($obField->getTitle()).'</td><td>';
            if ($arUser)
            {
                echo '<a href="user_edit.php?lang=' . LANGUAGE_ID . '&ID=' . $arValues[$key] . '">[' . $arValues[$key] . '] ' . htmlspecialcharsbx($arUser["LOGIN"]);
            } else
            {
                echo htmlspecialcharsbx($arValues[$key]);
            }
            echo '</td></tr>';
            $edit->getTabControl()->EndCustomField($key);
        }
    );

    $arEditFields['MODIFIED_DATE'] = array(
        'showField' => function (\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) use ($arUserFields, $userFieldManager) {
            $arUser = \CUser::GetByID($arValues[$key])->Fetch();

            $edit->getTabControl()->BeginCustomField($key, $obField->getTitle());
            echo '<tr><td>'.htmlspecialcharsbx($obField->getTitle()).'</td><td>';
            echo htmlspecialcharsbx($arValues[$key]);
            echo '</td></tr>';
            $edit->getTabControl()->EndCustomField($key);
        }
    );


    foreach ($userFieldManager->GetUserFields($formResult->getUfId(), 0, LANGUAGE_ID) as $arField) {

        $arUserFields[$arField['FIELD_NAME']] = $arField;

        $arEditFields[$arField['FIELD_NAME']] = array(
            'showField' => function(\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) use ($arUserFields, $userFieldManager) {

                $arUserField = $arUserFields[$key];
                $arUserField['VALUE'] = $arValues[$key];

                $edit->getTabControl()->BeginCustomField($key, $arUserField['EDIT_FORM_LABEL'] ? $arUserField['EDIT_FORM_LABEL'] : $arUserField['FIELD_NAME']);

                $arUserField['EDIT_IN_LIST'] = 'Y';

                echo $userFieldManager->GetEditFormHTML(false, $arValues[$key], $arUserField);

                $edit->getTabControl()->EndCustomField($key);

            }
        );
    }

    $edit = new \DevBx\Core\Admin\AdminEdit('devbx.forms', $formResult, array(
            'ALLOW_DELETE' => 'Y',
            'ALLOW_ADD' => 'Y',
            'TABS' => array(
                array(
                    'TAB' => Loc::getMessage("DEVBX_FORMS_FORM_RESULT_EDIT_TAB1"),
                    'TITLE' => Loc::getMessage("DEVBX_FORMS_FORM_RESULT_EDIT_TAB1_TITLE"),
                    'FIELDS' => $arEditFields,
                ),
        )
    ));

    if ($_REQUEST['popupwindow'] == 'y')
    {
        $edit->setHiddenFields(array('popupwindow'=>'y'));
    }

    $edit->setAddParamsFileList('&FORM_ID='.$formId);
    $edit->setAddParamsFile('&FORM_ID='.$formId);

    $edit->display(array('subwindow'=>$_REQUEST['popupwindow'] == 'y', 'adjustwindow'=>false));
}
<?
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use DevBx\Forms\FormLangNameTable;

Loc::loadMessages(__FILE__);

Loader::includeModule("devbx.core");
Loader::includeModule("devbx.forms");

$edit = new \DevBx\Core\Admin\AdminEdit('devbx.forms', '\DevBx\Forms\FormTable', array(
        'ALLOW_DELETE' => 'Y',
        'ALLOW_ADD' => 'Y',
        'TABS' => array(
            array(
                'DIV' => 'tab_form_edit1',
                'TAB' => Loc::getMessage("DEVBX_FORMS_FORM_EDIT_TAB1"),
                'TITLE' => Loc::getMessage("DEVBX_FORMS_FORM_EDIT_TAB1_TITLE"),
                'FIELDS' => array(
                    'ID',
                    'NAME' => array('showField' => function (\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) {

                        $edit->getTabControl()->BeginCustomField('NAME', Loc::getMessage('DEVBX_FORMS_FORM_EDIT_NAME'));

                        $value = array();

                        if (!$edit->isNewForm()) {
                            $dbRes = FormLangNameTable::getList(array('filter' => array('FORM_ID' => $_REQUEST['ID'])));
                            while ($arRes = $dbRes->fetch()) {
                                $value[$arRes['LANGUAGE_ID']] = $arRes['NAME'];
                            }
                        }

                        $dbRes = \Bitrix\Main\Localization\LanguageTable::getList(array('order' => array('SORT' => "ASC", 'NAME' => "ASC"), 'filter' => array('ACTIVE' => 'Y')));
                        while ($arRes = $dbRes->fetch()) {
                            ?>
                            <tr>
                                <td><?= Loc::getMessage('DEVBX_FORMS_FORM_EDIT_NAME') ?> <?= $arRes['NAME'] ?></td>
                                <td>
                                    <input type="text" name="NAME[<?= $arRes['LID'] ?>]"
                                           value="<?= $value[$arRes['LID']] ?>" size="40">
                                </td>
                            </tr>
                            <?
                        }

                        $edit->getTabControl()->EndCustomField('NAME');

                    }),
                    'CODE',
                    'FORM_TYPE' => array('showField' => function (\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) {

                        $ar = \DevBx\Forms\FormManager::getInstance()->getFormType();

                        if ($edit->isNewForm())
                        {
                            $arSelect = [
                                    '' => Loc::getMessage('DEVBX_FORMS_FORM_EDIT_FORM_TYPE_EMPTY_SELECT')
                            ];

                            foreach ($ar as $k=>$formType)
                            {
                                $arSelect[$k] = $formType::getName();
                            }

                            $edit->getTabControl()->AddDropDownField($key, $obField->getTitle(), true, $arSelect);
                        } else
                        {
                            if ($ar[$arValues[$key]])
                            {
                                $edit->getTabControl()->AddViewField($key, $obField->getTitle(), htmlspecialcharsbx($ar[$arValues[$key]]::getName()));
                            } else
                            {
                                $edit->getTabControl()->AddViewField($key, $obField->getTitle(), htmlspecialcharsbx($arValues[$key]));
                            }
                        }
                    }
                    ),
                    'VIEW_GROUPS' => array(
                        'showField' => function (\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) {

                            $edit->getTabControl()->BeginCustomField($key, $obField->getTitle());

                            $value = $arValues[$key];

                            if (!is_array($value))
                                $value = array();

                            if ($edit->isNewForm()) {
                                if (empty($value))
                                    $value = array(2);
                            }

                            $arGroups = \Bitrix\Main\GroupTable::getList(array('order' => array('C_SORT' => "ASC")))->fetchAll();

                            ?>
                            <tr>
                                <td>
                                    <?= $obField->getTitle() ?>
                                </td>
                                <td>
                                    <select name="<?= $key ?>[]" multiple>
                                        <?
                                        foreach ($arGroups as $ar): ?>
                                            <option value="<?= $ar['ID'] ?>"<?
                                            if (in_array($ar['ID'], $value)): ?> selected<? endif; ?>><?= htmlspecialcharsbx($ar['NAME']) ?></option>
                                        <? endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <?

                            $edit->getTabControl()->EndCustomField($key);

                        }
                    ),
                    'WRITE_GROUPS' => array(
                        'showField' => function (\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) {

                            $edit->getTabControl()->BeginCustomField($key, $obField->getTitle());

                            $value = $arValues[$key];

                            if (!is_array($value))
                                $value = array();

                            if ($edit->isNewForm()) {
                                if (empty($value))
                                    $value = array(2);
                            }

                            $arGroups = \Bitrix\Main\GroupTable::getList(array('order' => array('C_SORT' => "ASC")))->fetchAll();

                            ?>
                            <tr>
                                <td>
                                    <?= $obField->getTitle() ?>
                                </td>
                                <td>
                                    <select name="<?= $key ?>[]" multiple>
                                        <?
                                        foreach ($arGroups as $ar): ?>
                                            <option value="<?= $ar['ID'] ?>"<?
                                            if (in_array($ar['ID'], $value)): ?> selected<? endif; ?>><?= htmlspecialcharsbx($ar['NAME']) ?></option>
                                        <? endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <?

                            $edit->getTabControl()->EndCustomField($key);

                        }
                    ),
                ),
            ),
        )
    )
);

if ($_REQUEST['ID']>0)
{
    $row = \DevBx\Forms\FormTable::getRowById($_REQUEST['ID']);
    if ($row)
    {
        $formType = \DevBx\Forms\FormManager::getInstance()->getFormType($row['FORM_TYPE']);
        if ($formType)
        {
            $formType::showAdminSettings($edit);
        }
    }
}

$edit->setAfterSaveCallback(function ($edit, $primary) {
    if (isset($_REQUEST['NAME']) && is_array($_REQUEST['NAME'])) {
        foreach ($_REQUEST['NAME'] as $lid => $val) {
            $ar = FormLangNameTable::getList(array('filter' => array('FORM_ID' => $primary, 'LANGUAGE_ID' => $lid)))->fetch();
            if ($ar) {
                FormLangNameTable::update($ar['ID'], array('NAME' => $val));
            } else {
                FormLangNameTable::add(array(
                    'FORM_ID' => $primary,
                    'LANGUAGE_ID' => $lid,
                    'NAME' => $val,
                ));
            }
        }
    }

    $entityId = 'DEVBX_FORM_' . $primary;
    $dbRes = CUserTypeEntity::GetList(array(), array('ENTITY_ID' => $entityId, 'LANG' => LANGUAGE_ID));
    while ($arUserField = $dbRes->Fetch()) {
        $key = "SETTINGS_" . $arUserField['FIELD_NAME'];

        if (isset($_REQUEST[$key]) && !empty($_REQUEST[$key])) {
            $arFields = array(
                'SETTINGS' => $_REQUEST[$key],
            );

            $ob = new CUserTypeEntity();
            $ob->Update($arUserField['ID'], $arFields);
        }
    }

});

$edit->display();

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$subWindow = defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1;

if (!$subWindow && $ex = $APPLICATION->GetException()) {
    require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');
    ShowError($ex->GetString());
    require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
    die();
}

\Bitrix\Main\Loader::includeModule("devbx.core");
\Bitrix\Main\Loader::includeModule("devbx.forms");

DevBx\Forms\FormManager::getInstance()->loadAllForms();

$ENTITY_NAME = \Bitrix\Main\ORM\Entity::normalizeEntityClass($ENTITY_NAME);
if (!class_exists($ENTITY_NAME)) {

    echo $ENTITY_NAME.' not found';

    return;
}

if (!is_subclass_of($ENTITY_NAME, \Bitrix\Main\Entity\DataManager::class)) {
    return;
}

$entityId = $ENTITY_NAME::getEntity()->getUfId();
if (empty($entityId))
    return;

$arUserTypes = $USER_FIELD_MANAGER->GetUserType();

ob_start();

foreach ($arUserTypes as $arType) {
    if (is_callable(array($arType["CLASS_NAME"], "getsettingshtml")))
        call_user_func_array(array($arType["CLASS_NAME"], "getsettingshtml"), array(false, array("NAME" => "SETTINGS"), $bVarsFromForm));
}

ob_end_clean();

uasort($arUserTypes, function ($a, $b) {
    return strcmp(ToUpper($a['DESCRIPTION']), ToUpper($b['DESCRIPTION']));
});

$arTabs = array();

$arTabs[] = array(
    'DIV' => 'edit1',
    'TAB' => Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_TAB'),
    'TITLE' => Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_TAB_TITLE'),
    'TAB_CONTROL_NAME' => "devbx_" . str_replace('\\', '_', $ENTITY_NAME),
    'FIELDS' => array(
        'USER_TYPE_ID' => array(
            'showField' => function (\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) use ($arUserTypes) {
                global $USER_FIELD_MANAGER;
                if ($edit->isNewForm()) {


                    $arSelect = array();

                    foreach ($arUserTypes as $userType => $ar) {
                        $arSelect[$userType] = $ar['DESCRIPTION'];
                    }

                    $edit->getTabControl()->AddDropDownField($key, Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_FIELD_USER_TYPE_ID'), true, $arSelect, false, array('onchange="onUserTypeChange();"'));

                    ?>
                    <script>
                        function onUserTypeChange() {
                            let form = document.getElementById('<?=$edit->getTabControlName()?>_form');

                            let selUserType = form.querySelector('select[name=USER_TYPE_ID]');

                            BX.ajax({
                                url: '/bitrix/admin/devbx_form_get_uf_settings.php',
                                method: 'POST',
                                data: {type: selUserType.value, lang: BX.message('LANGUAGE_ID')},
                                dataType: 'html',
                                processData: false,
                                onsuccess: function (result) {

                                    document.post_form.MULTIPLE.parentNode.parentNode.style.display = 'block'; /* хак для типа "опрос" */

                                    let tbl = document.getElementById('SETTINGS_CONTAINER');

                                    tbl.innerHTML = '<tr><td width="40%"></td><td></td></tr>' + result;

                                    let ob = BX.processHTML(result, false);
                                    BX.ajax.processScripts(ob.SCRIPT, true);
                                    BX.ajax.processScripts(ob.SCRIPT, false);

                                    /* стилизация элементов */

                                    for (var k = 0; k < tbl.tBodies.length; k++) {
                                        var n = tbl.tBodies[k].rows.length;
                                        for (var i = 0; i < n; i++) {
                                            if (tbl.tBodies[k].rows[i].cells.length > 1) {
                                                BX.addClass(tbl.tBodies[k].rows[i].cells[0], 'adm-detail-content-cell-l');
                                                BX.addClass(tbl.tBodies[k].rows[i].cells[1], 'adm-detail-content-cell-r');
                                            }
                                        }
                                    }

                                    let elements = tbl.querySelectorAll('input, select');

                                    for (let k = 0; k < elements.length; k++) {
                                        BX.adminFormTools.modifyFormElement(elements[k], ['*']);
                                    }

                                    /* хак для типа "опрос" */

                                    let inpMultiple = form.querySelector('[type=checkbox][name=MULTIPLE]');

                                    if (document.post_form.MULTIPLE.parentNode.parentNode.style.display == 'none') {
                                        inpMultiple.checked = false;
                                        inpMultiple.parentNode.parentNode.style.display = 'none';
                                    } else {
                                        inpMultiple.parentNode.parentNode.style.display = '';
                                    }


                                }
                            });

                        }

                        BX.ready(function () {
                            onUserTypeChange();
                        });

                    </script>
                    <?

                } else {
                    $edit->getTabControl()->BeginCustomField($key, $obField->getTitle(), true);
                    ?>
                    <tr>
                        <td><b><?= Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_FIELD_USER_TYPE_ID') ?></b></td>
                        <td><?= $arUserTypes[$arValues[$key]]['DESCRIPTION'] ?></td>
                    </tr>
                    <?
                    $edit->getTabControl()->EndCustomField($key);
                }
            }
        ),
        'FIELD_NAME' => array('showField' => function (\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) {
            if ($edit->isNewForm()) {
                $edit->getTabControl()->AddEditField($key, Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_FIELD_NAME'), true, array(), 'UF_');
            } else {
                $edit->getTabControl()->AddViewField($key, Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_FIELD_NAME'), htmlspecialcharsbx($arValues[$key]), true);
            }
        }),
        'XML_ID',
        'SORT' => array(
            'showField' => function (\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) use ($arUserTypes, $entityId) {
                if ($edit->isNewForm())
                {
                    if (empty($arValues[$key]))
                    {
                        $arExistField = \CUserTypeEntity::GetList(array('SORT'=>'DESC'),array('ENTITY_ID'=>$entityId))->Fetch();

                        if ($arExistField)
                        {
                            $arValues[$key] = $arExistField['SORT']+100;
                        } else {
                            $arValues[$key] = 100;
                        }
                    }
                }

                $edit->getTabControl()->AddEditField($key, $obField->getTitle(), false, array(), $arValues[$key]);
            }),
        'MULTIPLE' => array(
            'showField' => function (\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) use ($arUserTypes) {

                if ($edit->isNewForm()) {
                    $edit->getTabControl()->AddCheckBoxField($key, $obField->getTitle(), false, array('Y', 'N'), $arValues[$key] == 'Y');
                } else {
                    $edit->getTabControl()->BeginCustomField($key, $obField->getTitle());
                    ?>
                    <tr>
                        <td><?= Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_FIELD_MULTIPLE') ?></td>
                        <td>
                            <? if ($arValues[$key] == 'Y'): ?>
                                <?= Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_FIELD_YES') ?>
                            <? else: ?>
                                <?= Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_FIELD_NO') ?>
                            <? endif ?>
                        </td>
                    </tr>
                    <?
                    $edit->getTabControl()->EndCustomField($key);
                }
            }
        ),
        'MANDATORY',
        'SHOW_FILTER' => array(
            'showField' => function (\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) use ($arUserTypes) {
                $arEnum = array(
                    'N' => Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_FILTER_SHOW_N'),
                    'I' => Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_FILTER_SHOW_I'),
                    'E' => Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_FILTER_SHOW_E'),
                    'S' => Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_FILTER_SHOW_S'),
                );

                $edit->getTabControl()->AddDropDownField($key, $obField->getTitle(), false, $arEnum, $arValues[$key]);
            }
        ),
        'SHOW_IN_LIST',
        'EDIT_IN_LIST' => array(
            'showField' => function (\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) use ($arUserTypes) {
                $edit->getTabControl()->AddCheckBoxField($key, $obField->getTitle(), false, array('Y', 'N'), $arValues[$key] != 'N');
            },
        ),
        'SETTINGS' => array(
            'showField' => function (\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) use ($arUserTypes) {

                global $USER_FIELD_MANAGER;

                $edit->getTabControl()->BeginCustomField($key, $obField->getTitle());

                ?>
                <tr class="heading">
                    <td colspan="2"><?= Loc::getMessage("DEVBX_FORMS_FIELDS_EDIT_SETTINGS_TITLE") ?></td>
                </tr>
                <?

                if ($edit->isNewForm()) {
                    ?>
                    <tr>
                        <td colspan="2">
                            <table id="SETTINGS_CONTAINER"
                                   class="adm-detail-content-table edit-table"></table>
                        </td>
                    </tr>
                    <?


                } else {
                    echo $USER_FIELD_MANAGER->GetSettingsHTML($arValues);
                }

                $edit->getTabControl()->EndCustomField($key);

            }
        ),
        'LANG_SETTINGS' => array(
            'showField' => function (\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) {
                $edit->getTabControl()->BeginCustomField($key, Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_LANG_SETTINGS_TITLE'));

                if ($arValues['ID']) {
                    $arValuesEx = \CUserTypeEntity::GetByID($arValues['ID']);
                    if (is_array($arValuesEx))
                        $arValues = array_merge($arValues, $arValuesEx);
                }

                $arLangList = array();

                $dbRes = \Bitrix\Main\Localization\LanguageTable::getList(array('order' => array('SORT' => "ASC", 'NAME' => "ASC"), 'filter' => array('ACTIVE' => 'Y')));
                while ($arRes = $dbRes->fetch()) {
                    $arLangList[$arRes['LID']] = $arRes;
                }

                ?>
                <tr class="heading">
                    <td colspan="2"><?= Loc::getMessage("DEVBX_FORMS_FIELDS_EDIT_LANG_SETTINGS_TITLE") ?></td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <table border="0" cellspacing="10" cellpadding="2">
                            <tbody>
                            <tr>
                                <td align="right"><?= Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_LANG_NAME') ?></td>
                                <td align="center"
                                    width="200"><?= Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_LANG_EDIT_FORM_LABEL') ?></td>
                                <td align="center"
                                    width="200"><?= Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_LANG_LIST_COLUMN_LABEL') ?></td>
                                <td align="center"
                                    width="200"><?= Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_LANG_LIST_FILTER_LABEL') ?></td>
                                <td align="center"
                                    width="200"><?= Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_LANG_ERROR_MESSAGE') ?></td>
                                <td align="center"
                                    width="200"><?= Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_LANG_HELP_MESSAGE') ?></td>
                            </tr>
                            <?

                            $labels = array('EDIT_FORM_LABEL', 'LIST_COLUMN_LABEL', 'LIST_FILTER_LABEL', 'ERROR_MESSAGE', 'HELP_MESSAGE');

                            foreach ($arLangList as $lid => $arLang) {
                                ?>
                                <tr>
                                    <td align="right"><?= $arLang['NAME'] ?>:</td>
                                    <?
                                    foreach ($labels as $label) {
                                        echo '<td align="center"><input type="text" name="' . $label . '[' . $lid . ']" size="20" maxlength="255" value="' . htmlspecialcharsbx($arValues[$label][$lid]) . '"></td>';
                                    }
                                    ?>
                                </tr>
                                <?
                            }
                            ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <?

                $edit->getTabControl()->EndCustomField($key);

            }
        ),
    )
);

$arTabs[] = array(
    'DIV' => "edit2",
    'TAB' => Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_TAB2'),
    'TITLE' => Loc::getMessage('DEVBX_FORMS_FIELDS_EDIT_TAB2_TITLE'),
    'FIELDS' => array(
        'ENUM_LIST' => array(
            'getValue' => function (\DevBx\Core\Admin\AdminEdit $adminEdit, $bVarsFromForm, $fieldName, $obField, &$arValues) {

                if ($bVarsFromForm)
                {
                    if (isset($_REQUEST['LIST']))
                        $arValues['LIST'] = $_REQUEST['LIST'];
                } else
                {

                }

            },
            'saveFieldValue' => function(\DevBx\Core\Admin\AdminEdit $adminEdit, &$arLoadFields, $primary, $fieldName, $obField) {
                global $USER_FIELD_MANAGER;

                if (!empty($primary))
                {

                    $arRow = \Bitrix\Main\UserFieldTable::getRowById($primary);
                    if (!$arRow)
                        return;

                    $arType = $USER_FIELD_MANAGER->GetUserType($arRow["USER_TYPE_ID"]);
                    if (!$arType)
                        return;

                    if($arType["BASE_TYPE"] != "enum")
                        return;

                    $obEnum = new CUserFieldEnum;

                    $LIST = $_REQUEST["LIST"];
                    if(is_array($LIST))
                    {
                        foreach($LIST as $id => $value)
                            if(is_array($value))
                                $LIST[$id]["DEF"] = "N";
                    }
                    if(is_array($LIST["DEF"]))
                    {
                        foreach($LIST["DEF"] as $value)
                            if(is_array($LIST[$value]))
                                $LIST[$value]["DEF"] = "Y";
                        unset($LIST["DEF"]);
                    }

                    $res = $obEnum->SetEnumValues($primary, $LIST);
                }
            },
            'showField' => function (\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues) {

                global $USER_FIELD_MANAGER;

                if ($edit->isNewForm())
                    return;

                $arType = $USER_FIELD_MANAGER->GetUserType($arValues["USER_TYPE_ID"]);
                if (!$arType)
                    return;

                if($arType["BASE_TYPE"] != "enum")
                    return;

                $obEnum = new CUserFieldEnum;

                $edit->getTabControl()->BeginCustomField('ENUM_LIST', Loc::getMessage('DEVBX_FORMS_FIELDS_FIELD_ENUM_LIST'));

                ?>
                <script>
                    function addNewRow(tableID)
                    {
                        var tbl = document.getElementById(tableID);
                        var cnt = tbl.rows.length;
                        var oRow = tbl.insertRow(cnt);
                        for(var i=0;i<6;i++)
                        {
                            var oCell = oRow.insertCell(i);
                            var sHTML=tbl.rows[cnt-1].cells[i].innerHTML;
                            var p = 0;
                            while(true)
                            {
                                var s = sHTML.indexOf('[n',p);
                                if(s<0)break;
                                var e = sHTML.indexOf(']',s);
                                if(e<0)break;
                                var n = parseInt(sHTML.substr(s+2,e-s));
                                sHTML = sHTML.substr(0, s)+'[n'+(++n)+']'+sHTML.substr(e+1);
                                p=s+1;
                            }
                            while(true)
                            {
                                s = sHTML.indexOf('\"n',p);
                                if(s<0)break;
                                e = sHTML.indexOf('\"',s+1);
                                if(e<0)break;
                                n = parseInt(sHTML.substr(s+2,e-s));
                                sHTML = sHTML.substr(0, s)+'\"n'+(++n)+'\"'+sHTML.substr(e+1);
                                p=s+1;
                            }
                            oCell.innerHTML = sHTML;
                        }

                        setTimeout(function() {
                            var r = BX.findChildren(oCell.parentNode, {tag: /^(input|select|textarea)$/i}, true);
                            if (r && r.length > 0)
                            {
                                for (var i=0,l=r.length;i<l;i++)
                                {
                                    if (r[i].form && r[i].form.BXAUTOSAVE)
                                        r[i].form.BXAUTOSAVE.RegisterInput(r[i]);
                                    else
                                        break;
                                }
                            }
                        }, 10);
                    }

                    BX.ready(function(){
                        BX.addCustomEvent(document.forms.post_form, 'onAutoSaveRestore', function(ob, data)
                        {
                            for(var i in data)
                            {
                                var r = /^LIST\[n([\d]+)\]\[XML_ID\]$/.exec(i);
                                if (r && r[1] > 0)
                                {
                                    addNewRow('list_table');
                                }
                            }

                        });

                    });
                </script>
                <tr>
                    <td class="adm-detail-valign-top"><?= GetMessage("USER_TYPE_LIST_LABEL") ?></td>
                    <td>
                        <table border="0" cellspacing="0" cellpadding="0" class="internal" id="list_table">
                            <tr class="heading">
                                <td><?= GetMessage("USER_TYPE_LIST_ID") ?></td>
                                <td><?= GetMessage("USER_TYPE_LIST_XML_ID") ?></td>
                                <td><?= GetMessage("USER_TYPE_LIST_VALUE") ?></td>
                                <td><?= GetMessage("USER_TYPE_LIST_SORT") ?></td>
                                <td><?= GetMessage("USER_TYPE_LIST_DEF") ?></td>
                                <td><?= GetMessage("USER_TYPE_LIST_DEL") ?></td>
                            </tr>
                            <? if ($arValues['MULTIPLE'] == "N"): ?>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td><?= GetMessage("USER_TYPE_LIST_NO_DEF") ?></td>
                                    <td>&nbsp;</td>
                                    <td><input type="radio" name="LIST[DEF][]" value="0"></td>
                                    <td>&nbsp;</td>
                                </tr>
                            <?endif ?>
                            <?
                            $rsEnum = $obEnum->GetList(array(), array("USER_FIELD_ID" => $arValues['ID']));
                            while ($arEnum = $rsEnum->GetNext()):

                                if (is_array($arValues['LIST'][$arEnum["ID"]]))
                                    foreach ($arValues['LIST'][$arEnum["ID"]] as $key => $val)
                                        $arEnum[$key] = htmlspecialcharsbx($val);
                                ?>
                                <tr>
                                    <td><?= $arEnum["ID"] ?></td>
                                    <td><input type="text" name="LIST[<?= $arEnum["ID"] ?>][XML_ID]"
                                               value="<?= $arEnum["XML_ID"] ?>" size="15" maxlength="255"></td>
                                    <td><input type="text" name="LIST[<?= $arEnum["ID"] ?>][VALUE]"
                                               value="<?= $arEnum["VALUE"] ?>" size="35" maxlength="255"></td>
                                    <td><input type="text" name="LIST[<?= $arEnum["ID"] ?>][SORT]"
                                               value="<?= $arEnum["SORT"] ?>" size="5" maxlength="10"></td>
                                    <td><input type="<?= ($arValues['MULTIPLE'] == "Y" ? "checkbox" : "radio") ?>"
                                               name="LIST[DEF][]"
                                               value="<?= $arEnum["ID"] ?>" <?= ($arEnum["DEF"] == "Y" ? "checked" : "") ?>>
                                    </td>
                                    <td><input type="checkbox" name="LIST[<?= $arEnum["ID"] ?>][DEL]"
                                               value="Y"<? if ($arEnum["DEL"] == "Y") echo " checked" ?>></td>
                                </tr>
                            <?
                            endwhile;
                            ?>
                            <?
                            if (isset($arValues['LIST'])):
                                $n = 0;
                                foreach ($arValues['LIST'] as $key => $val):
                                    if (strncmp($key, "n", 1) === 0):
                                        ?>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td><input type="text" name="LIST[n<?= $n ?>][XML_ID]"
                                                       value="<?= htmlspecialcharsbx($val["XML_ID"]) ?>" size="15"
                                                       maxlength="255"></td>
                                            <td><input type="text" name="LIST[n<?= $n ?>][VALUE]"
                                                       value="<?= htmlspecialcharsbx($val["VALUE"]) ?>" size="35"
                                                       maxlength="255"></td>
                                            <td><input type="text" name="LIST[n<?= $n ?>][SORT]"
                                                       value="<?= htmlspecialcharsbx($val["SORT"]) ?>" size="5"
                                                       maxlength="10"></td>
                                            <td><input type="<?= ($arValues['MULTIPLE'] == "Y" ? "checkbox" : "radio") ?>"
                                                       name="LIST[DEF][]" value="n<?= $n ?>"></td>
                                            <td><input type="checkbox" name="LIST[n<?= $n ?>][DEL]"
                                                       value="Y"<? if ($val["DEL"] == "Y") echo " checked" ?>></td>
                                        </tr>
                                        <?
                                        $n++;
                                    endif;
                                endforeach;
                            else:
                                ?>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td><input type="text" name="LIST[n0][XML_ID]" value="" size="15" maxlength="255">
                                    </td>
                                    <td><input type="text" name="LIST[n0][VALUE]" value="" size="35" maxlength="255">
                                    </td>
                                    <td><input type="text" name="LIST[n0][SORT]" value="500" size="5" maxlength="10">
                                    </td>
                                    <td><input type="<?= ($arValues['MULTIPLE'] == "Y" ? "checkbox" : "radio") ?>"
                                               name="LIST[DEF][]" value="n0"></td>
                                    <td><input type="checkbox" name="LIST[n0][DEL]" value="Y"></td>
                                </tr>
                            <?
                            endif;
                            ?>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><input type="button" value="<?= GetMessage("USER_TYPE_LIST_MORE") ?>"
                               OnClick="addNewRow('list_table')"></td>
                </tr>
                <?
                $edit->getTabControl()->EndCustomField('ENUM_FIELD');

            }
        ),
    ),
);


$edit = new \DevBx\Core\Admin\AdminEdit('devbx.forms', '\Bitrix\Main\UserFieldTable', array(
        'ALLOW_DELETE' => 'Y',
        'ALLOW_ADD' => 'Y',
        'TAB_CONTROL_NAME' => 'tabControl',
        'SUBLIST_ID' => $ENTITY_NAME,
        'TABS' => $arTabs
    )
);

//adm-info-message-close

$edit->setSaveCallback(function (\DevBx\Core\Admin\AdminEdit $edit, &$primary, $arLoadFields) use ($entityId) {

    global $APPLICATION;

    $result = new Bitrix\Main\Entity\Result();

    $ute = new \CUserTypeEntity();

    $arLoadFields['ENTITY_ID'] = $entityId;
    $arLoadFields['EDIT_FORM_LABEL'] = $_REQUEST['EDIT_FORM_LABEL'];
    $arLoadFields['LIST_COLUMN_LABEL'] = $_REQUEST['LIST_COLUMN_LABEL'];
    $arLoadFields['LIST_FILTER_LABEL'] = $_REQUEST['LIST_FILTER_LABEL'];
    $arLoadFields['ERROR_MESSAGE'] = $_REQUEST['ERROR_MESSAGE'];
    $arLoadFields['HELP_MESSAGE'] = $_REQUEST['HELP_MESSAGE'];

    if (empty($primary)) {
        $primary = $ute->Add($arLoadFields);
        if (!$primary) {
            $ex = $APPLICATION->GetException();
            if ($ex) {
                $result->addError(new \Bitrix\Main\Error($ex->GetString()));
            } else {
                $result->addError(new \Bitrix\Main\Error('unknown error'));
            }
        }
    } else {
        if (!$ute->Update($primary, $arLoadFields)) {
            $ex = $APPLICATION->GetException();
            if ($ex) {
                $result->addError(new \Bitrix\Main\Error($ex->GetString()));
            } else {
                $result->addError(new \Bitrix\Main\Error('Unknown error'));
            }
        }
    }

    return $result;
});

$edit->setHiddenFields(array('ENTITY_NAME' => $ENTITY_NAME));

/* фэйковая форма для типа "опрос" */

$edit->setFooterContent('    <form name="post_form" style="display:none;">
        <div>
            <div><input name="MULTIPLE"></div>
        </div>
    </form>
');

$edit->display($subWindow);

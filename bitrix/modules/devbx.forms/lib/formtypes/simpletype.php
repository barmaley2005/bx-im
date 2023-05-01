<?php

namespace DevBx\Forms\FormTypes;

use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail\Internal\EventMessageSiteTable;
use Bitrix\Main\Mail\Internal\EventMessageTable;
use Bitrix\Main\Mail\Internal\EventTypeTable;
use Bitrix\Main\Entity;
use DevBx\Core\Admin\AdminEdit;
use DevBx\Core\Admin\editFieldOld;
use DevBx\Forms;

Loc::loadMessages(__FILE__);

class SimpleType extends Forms\BaseFormType
{
    public static function registerFormType(Event $event)
    {
        $event->getParameter('manager')->registerFormType(__CLASS__);
    }

    public static function compileEntity(Forms\EO_Form $form)
    {
        $entity = parent::compileEntity($form);

        $eventName = $entity->getNamespace() . $entity->getName() . '::';

        EventManager::getInstance()->addEventHandler(
            $entity->getModule(),
            $eventName . $entity->getDataClass()::EVENT_ON_AFTER_ADD,
            array(__CLASS__, 'onAfterAddHandler')
        );

        return $entity;
    }

    public static function getName()
    {
        return Loc::getMessage('DEVBX_FORMS_SIMPLE_FORM_NAME');
    }

    public static function onAddForm(Forms\FormResultEntity $entity)
    {
        parent::onAddForm($entity);

        if ($entity->getForm()->getSettings()['SEND_EVENT'] == 'Y')
            static::createEventForForm($entity);

    }

    public static function getMessageEvents(Forms\FormResultEntity $entity, $langId)
    {
        global $USER_FIELD_MANAGER;

        if (!$langId)
            $langId = LANGUAGE_ID;

        $formName = Forms\FormLangNameTable::getFormName($entity->getFormId(), $langId);
        if (empty($formName))
            $formName = $entity->getFormId();

        //$userFieldManager = UserFieldHelper::getInstance()->getManager();

        $arFields = array(
            '#FORM_ID#' => Loc::getMessage('DEVBX_FORMS_EVENT_FORM_ID', null, $langId),
            '#FORM_NAME#' => Loc::getMessage('DEVBX_FORMS_EVENT_FORM_NAME', null, $langId),
            '#FORM_RESULT_ID#' => Loc::getMessage('DEVBX_FORMS_EVENT_FORM_RESULT_ID', null, $langId),
            '#FORM_DATE_CREATE#' => Loc::getMessage('DEVBX_FORMS_EVENT_FORM_DATE_CREATE', null, $langId),
            '#USER_ID#' => Loc::getMessage('DEVBX_FORMS_EVENT_USER_ID', null, $langId),
            '#USER_EMAIL#' => Loc::getMessage('DEVBX_FORMS_EVENT_USER_EMAIL', null, $langId),
            '#USER_NAME#' => Loc::getMessage('DEVBX_FORMS_EVENT_USER_NAME', null, $langId),
        );

        foreach ($USER_FIELD_MANAGER->GetUserFields($entity->getDataClass()::getUfId(), 0, $langId) as $arField) {

            if ($arField['USER_TYPE_ID'] == 'file')
                continue;

            $fieldLabel = $arField['LIST_COLUMN_LABEL'] ? $arField['LIST_COLUMN_LABEL'] : $arField['FIELD_NAME'];
            $arFields['#' . $arField['FIELD_NAME'] . '#'] = $fieldLabel;

            switch ($arField['USER_TYPE_ID']) {
                case 'iblock_element':
                case 'devbx_element_auto_complete':
                    $arFields['#' . $arField['FIELD_NAME'] . '_ID#'] = Loc::getMessage('DEVBX_FORMS_EVENT_ELEMENT_ID', array('#FIELD_LABEL#' => $fieldLabel), $langId);
                    $arFields['#' . $arField['FIELD_NAME'] . '_ADMIN_URL#'] = Loc::getMessage('DEVBX_FORMS_EVENT_ELEMENT_ADMIN_LINK', array('#FIELD_LABEL#' => $fieldLabel), $langId);
                    $arFields['#' . $arField['FIELD_NAME'] . '_DETAIL_PAGE_URL#'] = Loc::getMessage('DEVBX_FORMS_EVENT_ELEMENT_DETAIL_PAGE_URL', array('#FIELD_LABEL#' => $fieldLabel), $langId);
                    break;
                case 'iblock_section':
                    $arFields['#' . $arField['FIELD_NAME'] . '_ID#'] = Loc::getMessage('DEVBX_FORMS_EVENT_SECTION_ID', array('#FIELD_LABEL#' => $fieldLabel), $langId);
                    $arFields['#' . $arField['FIELD_NAME'] . '_ADMIN_URL#'] = Loc::getMessage('DEVBX_FORMS_EVENT_SECTION_ADMIN_LINK', array('#FIELD_LABEL#' => $fieldLabel), $langId);
                    $arFields['#' . $arField['FIELD_NAME'] . '_DETAIL_PAGE_URL#'] = Loc::getMessage('DEVBX_FORMS_EVENT_SECTION_DETAIL_PAGE_URL', array('#FIELD_LABEL#' => $fieldLabel), $langId);
                    break;
            }
        }


        $result = [
            'DEVBX_FORM_RESULT' => array(
                'NAME' => Loc::getMessage('DEVBX_FORMS_MAIL_EVENT_NAME', array('#FORM_NAME#' => $formName), $langId),
                'DESCRIPTION' => '',
                'EVENT_TYPE' => EventTypeTable::TYPE_EMAIL,
                'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
                'EMAIL_TO' => '#DEFAULT_EMAIL_FROM#',
                'SUBJECT' => Loc::getMessage('DEVBX_FORMS_MAIL_EVENT_SUBJECT', array(), $langId),
                'MESSAGE' => Loc::getMessage('DEVBX_FORMS_MAIL_EVENT_MESSAGE', array(), $langId),
                'BODY_TYPE' => 'text',
                'FIELDS' => $arFields,
            ),
        ];

        return $result;
    }

    public static function onUpdateForm(Forms\FormResultEntity $entity)
    {
        parent::onUpdateForm($entity);

        if ($entity->getForm()->getSettings()['SEND_EVENT'] == 'Y')
            static::createEventForForm($entity);
    }

    public static function onDeleteForm(Forms\FormResultEntity $entity)
    {
        parent::onDeleteForm($entity);

        $dbRes = Forms\FormLangNameTable::getList(array('filter' => array('FORM_ID' => $entity->getFormId()), 'select' => array('ID')));
        while ($arRes = $dbRes->fetch()) {
            Forms\FormLangNameTable::delete($arRes['ID']);
        }

        $eventName = 'DEVBX_FORM_RESULT_' . $entity->getFormId();

        $dbEventMsg = EventMessageTable::getList(array('filter' => array('EVENT_NAME' => $eventName)));
        while ($arEventMsg = $dbEventMsg->fetch()) {
            /*
            $dbEventMsgSite = EventMessageSiteTable::getList(array('filter'=>array('EVENT_MESSAGE_ID'=>$arEventMsg['ID'])));
            while ($arEventMsgSite = $dbEventMsgSite->fetch())
            {
                EventMessageSiteTable::delete(array('EVENT_MESSAGE_ID'=>$arEventMsgSite['EVENT_MESSAGE_ID'],'SITE_ID'=>$arEventMsgSite['SITE_ID']));
            }
            */

            EventMessageSiteTable::delete($arEventMsg['ID']);
            EventMessageTable::delete($arEventMsg['ID']);
        }

        $dbEventType = EventTypeTable::getList(array(
            'filter' => array('=EVENT_NAME' => $eventName),
            'select' => array('ID')
        ));
        while ($arEventType = $dbEventType->fetch()) {
            EventTypeTable::delete($arEventType['ID']);
        }
    }

    public static function onAfterAddHandler(Entity\Event $event)
    {
        $result = new Entity\EventResult;

        $primary = $event->getParameter('primary');
        if (is_array($primary))
            $primary = $primary['ID'];

        /* @var Forms\FormResultEntity $entity */
        $entity = $event->getEntity();

        $form = $entity->getForm();

        if ($form->getSettings()['SEND_EVENT'] == 'Y') {
            $eventName = 'DEVBX_FORM_RESULT_' . $entity->getFormId();

            if (static::getEventValues($entity, $primary, $arSendFields, $arSendFiles)) {

                $parameters = array(
                    'entity' => $entity,
                    'form' => $form,
                    'id' => $primary,
                    'eventName' => $eventName,
                    'fields' => $arSendFields,
                    'files' => $arSendFiles,
                );

                $event = new \Bitrix\Main\Event($entity->getModule(), 'BEFORE_FORM_SEND_EVENT_RESULT', $parameters);
                $event->send();

                $sendEvent = true;

                foreach ($event->getResults() as $eventResult) {
                    if ($eventResult->getType() == EventResult::SUCCESS) {
                        $parameters = $eventResult->getParameters();
                        if (isset($parameters['eventName'])) {
                            $eventName = $parameters['eventName'];
                        }

                        if (isset($parameters['fields'])) {
                            $arSendFields = $parameters['fields'];
                        }

                        if (isset($parameters['files'])) {
                            $arSendFiles = $parameters['files'];
                        }
                    }

                    if ($eventResult->getType() == EventResult::ERROR) {
                        $sendEvent = false;
                        break;
                    }
                }

                if ($sendEvent) {
                    \CEvent::SendImmediate(
                        $eventName, SITE_ID, $arSendFields, 'Y', '', $arSendFiles
                    );
                }
            }
        }

        return $result;
    }

    public static function getEventValues(Forms\FormResultEntity $entity, $primary, &$arSendFields, &$arSendFiles, $lang = false, $siteId = false)
    {
        global $USER_FIELD_MANAGER;

        if (!is_array($primary))
            $primary = ['=ID' => $primary];

        $arRow = $entity->getDataClass()::getList([
            'filter' => $primary,
            'select' => ['*', 'UF_*']
        ])->fetch();

        if (!$arRow)
            return false;

        $arSendFields = array();
        $arSendFiles = array();

        if ($lang == false)
            $lang = LANGUAGE_ID;

        if ($siteId == false) {

            if (!empty($arRow['SITE_ID'])) {
                $siteId = $arRow['SITE_ID'];
            } else {
                $siteId = SITE_ID;
            }
        }

        $formId = $entity->getFormId();

        $arFormLang = Forms\FormLangNameTable::getList(array('filter' => array('FORM_ID' => $formId, 'LANGUAGE_ID' => $lang)))->fetch();

        $arSendFields['FORM_RESULT_SITE_ID'] = $arRow['SITE_ID'];
        $arSendFields['FORM_ID'] = $formId;
        $arSendFields['FORM_NAME'] = $arFormLang ? $arFormLang['NAME'] : '';
        $arSendFields['FORM_RESULT_ID'] = $arRow['ID'];
        $arSendFields['FORM_DATE_CREATE'] = (string)$arRow['CREATED_DATE'];
        $arSendFields['USER_ID'] = $arRow['CREATED_USER_ID'];

        if ($arRow['CREATED_USER_ID'] > 0) {
            $arUser = \CUser::GetByID($arRow['CREATED_USER_ID'])->Fetch();
            if (!$arUser)
                $arUser = array();
        } else {
            $arUser = array();
        }

        $arSendFields['USER_EMAIL'] = $arUser['EMAIL'];
        $arSendFields['USER_NAME'] = \CUser::FormatName(\CSite::GetNameFormat(true, $siteId), $arUser);

        //$userFieldManager = UserFieldHelper::getInstance()->getManager();

        foreach ($USER_FIELD_MANAGER->GetUserFields($entity->getDataClass()::getUfId(), 0, $lang) as $arField) {

            $value = $arRow[$arField['FIELD_NAME']];

            $arField['VALUE'] = $value;

            if (!is_array($value)) {
                if (!empty($value)) {
                    $value = array($value);
                } else {
                    $value = array();
                }
            }

            if ($arField['USER_TYPE_ID'] == 'file') {
                foreach ($value as $singleValue) {
                    $arFile = \CFile::GetFileArray($singleValue);
                    if (is_array($arFile)) {
                        $arSendFiles[] = $singleValue;
                    }
                }

                continue;
            }

            //Bitrix\Main\UserField\Types\BaseType::getPublicText();
            if (is_callable(array($arField['USER_TYPE']['CLASS_NAME'], 'getPublicText'))) {
                $arSendFields[$arField['FIELD_NAME']] = call_user_func_array(array($arField['USER_TYPE']['CLASS_NAME'], 'getPublicText'), array($arField));
            } else {
                $arSendFields[$arField['FIELD_NAME']] = $USER_FIELD_MANAGER->GetPublicView($arField);
            }


            switch ($arField['USER_TYPE_ID']) {
                case 'iblock_element':
                    $arSendFields[$arField['FIELD_NAME'] . '_ID'] = implode(', ', $value);

                    $arSendFields[$arField['FIELD_NAME'] . '_ADMIN_URL'] = array();
                    $arSendFields[$arField['FIELD_NAME'] . '_DETAIL_PAGE_URL'] = array();

                    if (!empty($value)) {
                        $dbElement = \CIBlockElement::GetList(array(), array('ID' => $value));
                        while ($arElement = $dbElement->GetNext()) {

                            $arSendFields[$arField['FIELD_NAME'] . '_ADMIN_URL'][] = 'https://' . SITE_SERVER_NAME . '/bitrix/admin/' .
                                \CIBlock::GetAdminElementEditLink($arElement['IBLOCK_ID'], $arElement['ID']);
                            $arSendFields[$arField['FIELD_NAME'] . '_DETAIL_PAGE_URL'][] = $arElement['~DETAIL_PAGE_URL'];
                        }
                    }

                    $arSendFields[$arField['FIELD_NAME'] . '_ADMIN_URL'] = implode(', ', $arSendFields[$arField['FIELD_NAME'] . '_ADMIN_URL']);
                    $arSendFields[$arField['FIELD_NAME'] . '_DETAIL_PAGE_URL'] = implode(', ', $arSendFields[$arField['FIELD_NAME'] . '_DETAIL_PAGE_URL']);
                    break;
                case 'iblock_section':
                    $arSendFields[$arField['FIELD_NAME'] . '_ID'] = implode(', ', $value);

                    $arSendFields[$arField['FIELD_NAME'] . '_ADMIN_URL'] = array();
                    $arSendFields[$arField['FIELD_NAME'] . '_DETAIL_PAGE_URL'] = array();

                    if (!empty($value)) {
                        $dbSection = \CIBlockSection::GetList(array(), array('ID' => $value));
                        while ($arSection = $dbSection->GetNext()) {
                            $arSendFields[$arField['FIELD_NAME'] . '_ADMIN_URL'][] = 'https://' . SITE_SERVER_NAME . '/bitrix/admin/' .
                                \CIBlock::GetAdminSectionListLink($arSection['IBLOCK_ID'], array('ID' => $arSection['ID']));
                            $arSendFields[$arField['FIELD_NAME'] . '_DETAIL_PAGE_URL'][] = $arSection['~SECTION_PAGE_URL'];
                        }
                    }

                    $arSendFields[$arField['FIELD_NAME'] . '_ADMIN_URL'] = implode(', ', $arSendFields[$arField['FIELD_NAME'] . '_ADMIN_URL']);
                    $arSendFields[$arField['FIELD_NAME'] . '_DETAIL_PAGE_URL'] = implode(', ', $arSendFields[$arField['FIELD_NAME'] . '_DETAIL_PAGE_URL']);
                    break;
            }
        }

        return true;
    }

    public static function showFieldUserFields(\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues)
    {

        if ($edit->isNewForm()) {
            ?>
            <tr>
                <td colspan="2"><?= Loc::getMessage('DEVBX_FORMS_FORM_EDIT_USER_FIELDS_NEW_NOTICE') ?></td>
            </tr>
            <?
        } else {

            $entity = Forms\FormManager::getInstance()->getFormInstance($arValues['ID']);

            if ($entity) {
                $edit->getTabControl()->BeginCustomField($key, Loc::getMessage('DEVBX_FORMS_FORM_EDIT_USER_FIELDS'));
                echo '<td colspan="2">';
                $ENTITY_NAME = $entity->getFullName();

                require(static::getModulePath() . '/tools/devbx_form_fields_list.php');

                echo '</td>';

                $edit->getTabControl()->EndCustomField($key);
            }

        }
    }

    public static function getModulePath()
    {
        static $modulePath = false;

        if ($modulePath)
            return $modulePath;

        $modulePath = \Bitrix\Main\Loader::getLocal('modules/devbx.forms');

        return $modulePath;
    }

    public static function showFieldCustomHtmlForm(\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues)
    {
        global $APPLICATION, $USER_FIELD_MANAGER, $USER;

        $edit->getTabControl()->BeginCustomField($key, Loc::getMessage('DEVBX_FORMS_FORM_EDIT_TEMPLATE_FORM'));
        ?>
        <tr class="heading">
            <td colspan="2"><?= Loc::getMessage('DEVBX_FORMS_FORM_EDIT_TEMPLATE_TYPE_TITLE') ?></td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="radio" id="USE_DEFAULT_TEMPLATE_Y" name="USE_DEFAULT_TEMPLATE" value="Y"
                       onclick="BX.hide(BX('form_tpl_editor'))"<? if ($arValues['SETTINGS']['USE_DEFAULT_TEMPLATE'] != 'N'): ?> checked<? endif ?>>
                <label for="USE_DEFAULT_TEMPLATE_Y"><?= Loc::getMessage("DEVBX_FORMS_FORM_EDIT_TEMPLATE_TYPE_COMPONENT_TITLE") ?></label>
                <br>
                <input type="radio" id="USE_DEFAULT_TEMPLATE_N" name="USE_DEFAULT_TEMPLATE" value="N"
                       onclick="BX.show(BX('form_tpl_editor'))"<? if ($arValues['SETTINGS']['USE_DEFAULT_TEMPLATE'] == 'N'): ?> checked<? endif ?>>
                <label for="USE_DEFAULT_TEMPLATE_N"><?= Loc::getMessage("DEVBX_FORMS_FORM_USE_CUSTOM_TEMPLATE") ?></label>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <script src="/bitrix/js/devbx.forms/form.js?<?= @filemtime($_SERVER['DOCUMENT_ROOT'] . '/bitrix/js/devbx.forms/form.js') ?>"></script>
                <div id="form_tpl_editor"<? if ($arValues['SETTINGS']['USE_DEFAULT_TEMPLATE'] != 'N'): ?> style="display: none;" <? endif ?>>
                    <?

                    $entityId = 'DEVBX_FORM_' . intval($_REQUEST['ID']);

                    $arInputFields = [];

                    $dbRes = \CUserTypeEntity::GetList(array(), array('ENTITY_ID' => $entityId, 'LANG' => LANGUAGE_ID));
                    while ($arUserField = $dbRes->Fetch()) {
                        $displayName = $arUserField['EDIT_FORM_LABEL'] . ' [' . $arUserField['FIELD_NAME'] . ']';

                        $arInputFields[] = array(
                            'FIELD_NAME' => $arUserField['FIELD_NAME'],
                            'CAPTION' => $displayName,
                            'IMAGE' => '/bitrix/images/devbx.forms/form-input.png'
                        );
                    }

                    /*
                    $arParams = Array(
                        "site" => SITE_ID,
                        //"templateID" => $tpl,
                        "bUseOnlyDefinedStyles" => COption::GetOptionString("fileman", "show_untitled_styles", "N") != "Y",
                        "bWithoutPHP" => false,
                        "arToolbars" => Array("standart", "style", "formating", "source", "template", "table"),
                        "arTaskbars" => Array("DevBxFormElementsTaskbar", "BXPropertiesTaskbar"),
                        "toolbarConfig" => CFileman::GetEditorToolbarConfig("devbx_form_edit" . (defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1 ? "_public" : "")),
                        "sBackUrl" => "",
                        "fullscreen" => false,
                        'width' => '100%',
                        'height' => '500',
                        'use_editor_3' => 'Y'
                    );
                    \CFileMan::ShowHTMLEditControl("CUSTOM_HTML_FORM", "", $arParams);
                    */

                    ?>
                    <table style="width:100%;">
                        <tr class="heading">
                            <td colspan="2">
                                <?= Loc::getMessage('DEVBX_FORMS_FORM_EDIT_TPL_HTML_FORM') ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <?
                                $Editor = new \CHTMLEditor;
                                $Editor->Show(array(
                                    'name' => 'TPL_HTML_FORM',
                                    'id' => 'TPL_HTML_FORM',
                                    'siteId' => SITE_ID,
                                    'width' => '100%',
                                    'height' => '500',
                                    'content' => $arValues['TPL_HTML_FORM'],
                                    'bAllowPhp' => $USER->CanDoOperation('edit_php'),
                                    'limitPhpAccess' => false,
                                    'display' => true,
                                    'componentFilter' => false,
                                    'setFocusAfterShow' => true,
                                    'relPath' => '',
                                    //'templateId' => SITE_TEMPLATE_ID,
                                    'showComponents' => false,
                                ));
                                ?>
                            </td>
                        </tr>
                        <tr class="heading">
                            <td colspan="2">
                                <?= Loc::getMessage('DEVBX_FORMS_FORM_EDIT_TPL_HTML_SUCCESS_FORM') ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <?
                                $Editor = new \CHTMLEditor;
                                $Editor->Show(array(
                                    'name' => 'TPL_HTML_SUCCESS_FORM',
                                    'id' => 'TPL_HTML_SUCCESS_FORM',
                                    'siteId' => SITE_ID,
                                    'width' => '100%',
                                    'height' => '500',
                                    'content' => $arValues['TPL_HTML_SUCCESS_FORM'],
                                    'bAllowPhp' => $USER->CanDoOperation('edit_php'),
                                    'limitPhpAccess' => false,
                                    'display' => true,
                                    'componentFilter' => false,
                                    'setFocusAfterShow' => true,
                                    'relPath' => '',
                                    //'templateId' => SITE_TEMPLATE_ID,
                                    'showComponents' => false,
                                ));
                                ?>
                            </td>
                        </tr>
                    </table>
                    <?
                    $messages = Loc::loadLanguageFile(__FILE__);
                    ?>

                    <script>
                        BX.message(<?=\CUtil::PhpToJSObject($messages)?>);

                        let devbxformInputFields = <?=json_encode($arInputFields)?>;

                        function DevBxFormControl(editor) {
                            DevBxFormControl.superclass.constructor.apply(this, arguments);

                            this.id = 'devbx_form_control';
                            this.templateId = this.editor.templateId;
                            this.title = BX.message('DEVBX_FORMS_FORM_EDIT_TASKBAR_TITLE');
                            this.uniqueId = 'taskbar_' + this.editor.id + '_' + this.id;

                            this.Init();
                        }

                        BX.extend(DevBxFormControl, window.BXHtmlEditor.Taskbar);

                        DevBxFormControl.prototype.Init = function () {
                            this.BuildSceleton();

                            let section = [];

                            section.push({
                                path: '',
                                title: BX.message('DEVBX_FORMS_FORM_EDIT_TASKBAR_ELEMENTS_TITLE'),
                                name: BX.message('DEVBX_FORMS_FORM_EDIT_TASKBAR_ELEMENTS_TITLE'),

                            });

                            let elements = [];

                            for (let i = 0; i < devbxformInputFields.length; i++) {
                                let field = devbxformInputFields[i];
                                elements.push({
                                    code: '<\?\$FORM->ShowInput(\'' + field.FIELD_NAME + '\')?>',
                                    name: field.CAPTION,
                                    title: field.CAPTION,
                                    path: BX.message('DEVBX_FORMS_FORM_EDIT_TASKBAR_ELEMENTS_TITLE'),
                                });
                            }

                            elements.push({
                                code: '<\?$FORM->ShowErrors()?>',
                                name: BX.message('DEVBX_FORMS_FORM_EDIT_FUNC_SHOW_ERRORS_TITLE'),
                                title: BX.message('DEVBX_FORMS_FORM_EDIT_FUNC_SHOW_ERRORS_TITLE'),
                                path: BX.message('DEVBX_FORMS_FORM_EDIT_TASKBAR_ELEMENTS_TITLE'),
                            });

                            elements.push({
                                code: '<\?$FORM->SubmitButton()?>',
                                name: BX.message('DEVBX_FORMS_FORM_EDIT_FUNC_SUBMIT_BUTTON_TITLE'),
                                title: BX.message('DEVBX_FORMS_FORM_EDIT_FUNC_SUBMIT_BUTTON_TITLE'),
                                path: BX.message('DEVBX_FORMS_FORM_EDIT_TASKBAR_ELEMENTS_TITLE'),
                            });

                            this.BuildTree(section, elements);
                        };

                        DevBxFormControl.prototype.HandleElementEx = function (wrap, dd, params) {
                            this.editor.SetBxTag(dd, {tag: "snippet_icon", params: params});
                            wrap.title = params.description || params.title;
                        };

                        BX.addCustomEvent('OnEditorCreated', function (editor) {
                            if (editor.config.id == 'TPL_HTML_FORM' || editor.config.id == 'TPL_HTML_SUCCESS_FORM') {
                                editor.phpParser.AddCustomParser(function (content) {

                                    content = content.replace(/#BXPHP_(\d+)#/g, function (str, ind) {
                                        //let editor = BXHtmlEditor.editors["TPL_HTML_FORM"];
                                        let code = editor.phpParser.arScripts[ind];
                                        code = editor.phpParser.TrimPhpBrackets(code);
                                        code = editor.phpParser.CleanCode(code);
                                        let func = editor.phpParser.ParseFunction(code);
                                        if (func !== false) {
                                            let p = editor.phpParser.ParseParameters(func.params);
                                            switch (func.name) {
                                                case '$FORM->ShowInput':
                                                    if (p.length == 1) {

                                                        for (let i = 0; i < devbxformInputFields.length; i++) {
                                                            let field = devbxformInputFields[i];
                                                            if (field.FIELD_NAME == p[0]) {
                                                                str = editor.phpParser.GetSurrogateHTML("php", BX.message('DEVBX_FORMS_FORM_EDIT_FUNC_SHOW_INPUT_TITLE') + field.CAPTION, p.FIELD_NAME, {value: editor.phpParser.arScripts[ind]});
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    break;
                                                case '$FORM->ShowErrors':
                                                    if (p.length == 0) {
                                                        str = editor.phpParser.GetSurrogateHTML("php", BX.message('DEVBX_FORMS_FORM_EDIT_FUNC_SHOW_ERRORS_TITLE'), 'SHOW_ERRORS', {value: editor.phpParser.arScripts[ind]});
                                                    }
                                                    break;
                                                case '$FORM->SubmitButton':
                                                    if (p.length == 0) {
                                                        str = editor.phpParser.GetSurrogateHTML("php", BX.message('DEVBX_FORMS_FORM_EDIT_FUNC_SUBMIT_BUTTON_TITLE'), 'SUBMIT_BUTTON', {value: editor.phpParser.arScripts[ind]});
                                                    }
                                                    break;
                                            }
                                        }

                                        return str;
                                    });

                                    return content;
                                });

                                console.log(editor.taskbarManager);
                                let taskbar = new DevBxFormControl(editor, editor.taskbarManager);
                                editor.taskbarManager.AddTaskbar(taskbar);
                            }
                        });
                        /*
                        BXHtmlEditor.editors["CUSTOM_HTML_FORM"].AddCustomParser(function(content) {

                            console.log(content);

                            return content;
                        });
                         */

                        /*
                        var arrInputObjects = </?=json_encode($arInputFields)?>;
                        var oDevBxForm = new CDevBxFormInfo(arrInputObjects);

                        oBXEditorUtils.addPHPParser(BX.proxy(oDevBxForm.PHPParser, oDevBxForm));
                        oBXEditorUtils.addTaskBar('DevBxFormElementsTaskbar', 2, BX.message('DEVBX_FORMS_FORM_EDIT_TASKBARSET_TITLE'), []);
                        */
                    </script>
                </div>
            </td>
        </tr>
        <?

        $edit->getTabControl()->EndCustomField($key);
    }

    public static function getValueCustomHtmlForm(\DevBx\Core\Admin\AdminEdit $edit, $bVarsFromForm, $primary, $id, $field, &$arValues)
    {
        if ($bVarsFromForm) {
            $arValues['SETTINGS']['USE_DEFAULT_TEMPLATE'] = $_POST['USE_DEFAULT_TEMPLATE'];
            $arValues['TPL_HTML_FORM'] = $_POST['TPL_HTML_FORM'];
            $arValues['TPL_HTML_SUCCESS_FORM'] = $_POST['TPL_HTML_SUCCESS_FORM'];
        } else {
            if ($primary > 0) {
                $dbRes = Forms\SimpleFormTplTable::getList([
                    'filter' => ['=FORM_ID' => $primary]
                ]);

                while ($arRes = $dbRes->fetch()) {
                    $arValues['TPL_HTML_' . $arRes['NAME']] = $arRes['TEMPLATE'];
                }
            }
        }

        $arValues['SETTINGS']['USE_DEFAULT_TEMPLATE'] = $arValues['SETTINGS']['USE_DEFAULT_TEMPLATE'] === 'N' ? 'N' : 'Y';
    }

    public static function saveFieldCustomHtmlForm(\DevBx\Core\Admin\AdminEdit $edit, &$arLoadFields, $primary, $id, $field)
    {
        if (!is_array($arLoadFields['SETTINGS']))
            $arLoadFields['SETTINGS'] = array();

        $arLoadFields['SETTINGS']['USE_DEFAULT_TEMPLATE'] = $_POST['USE_DEFAULT_TEMPLATE'];

        if (!empty($primary)) {
            Forms\SimpleFormTplTable::updateExtended($primary, [
                'FORM' => $_POST['TPL_HTML_FORM'],
                'SUCCESS_FORM' => $_POST['TPL_HTML_SUCCESS_FORM'],
            ]);
        }
    }

    public static function showFieldSettings(\DevBx\Core\Admin\AdminEdit $edit, $key, $obField, $arValues)
    {
        \CJSCore::Init(['devbx_core_utils']);

        $edit->getTabControl()->AddCheckBoxField(
            'SETTINGS[SEND_EVENT]',
            Loc::getMessage('DEVBX_FORMS_FORM_SEND_EVENT'),
            false,
            array('Y', 'N'),
            $arValues['SETTINGS']['SEND_EVENT'] == 'Y'
        );

        $edit->getTabControl()->AddCheckBoxField(
            'SETTINGS[USE_CAPTCHA]',
            Loc::getMessage('DEVBX_FORMS_FORM_USE_CAPTCHA'),
            false,
            array('Y', 'N'),
            $arValues['SETTINGS']['USE_CAPTCHA'] == 'Y'
        );

        $edit->getTabControl()->BeginCustomField('IBLOCK_SETTINGS', Loc::getMessage('DEVBX_FORMS_FORM_IBLOCK_SETTINGS'));

        ?>
        <tr>
            <td colspan="2">
                <div id="form-additional-settings">
                    <?

                    $FORM_ID = $arValues['ID'];
                    $SETTINGS = $arValues['SETTINGS'];

                    include(\Bitrix\Main\Loader::getLocal("modules/devbx.forms/tools/devbx_form_additional_settings.php"));

                    ?>
                </div>
                <script>
                    BX.ready(function () {
                        BX.addCustomEvent('DevBxFormsReloadSettings', function () {

                            let data = {
                                FORM_ID: <?=$FORM_ID?>
                            };

                            data = DevBX.Utils.saveFormData(data, document.getElementById('form-additional-settings'));

                            BX.ajax({
                                url: '/bitrix/admin/devbx_form_additional_settings.php',
                                method: 'POST',
                                data: data,
                                processData: false,
                                onsuccess: function (result) {
                                    let node = document.getElementById('form-additional-settings');

                                    node.innerHTML = result;
                                    BX.adminFormTools.modifyFormElements(node);
                                },
                            });

                        });
                    });
                </script>
            </td>
        </tr>
        <?

        $edit->getTabControl()->EndCustomField('IBLOCK_SETTINGS');
    }

    public static function getValueSettings(\DevBx\Core\Admin\AdminEdit $edit, $bVarsFromForm, $primary, $id, $field, &$arValues)
    {
        if ($bVarsFromForm) {
            $arValues['SETTINGS']['SEND_EVENT'] = $_POST['SETTINGS']['SEND_EVENT'];
        }

        $arValues['SETTINGS']['SEND_EVENT'] = $arValues['SETTINGS']['SEND_EVENT'] === 'Y' ? 'Y' : 'N';
    }

    public static function saveFieldSettings(\DevBx\Core\Admin\AdminEdit $edit, &$arLoadFields, $primary, $id, $field)
    {
        $arLoadFields['SETTINGS'] = $_REQUEST['SETTINGS'];
        if (!is_array($arLoadFields['SETTINGS']))
            $arLoadFields['SETTINGS'] = array();

        $arLoadFields['SETTINGS']['SEND_EVENT'] = $_POST['SETTINGS']['SEND_EVENT'] === 'Y' ? 'Y' : 'N';
    }

    public static function showAdminSettings(AdminEdit $edit)
    {
        $edit->addTabField('tab_form_edit1', new EditFieldOld('SETTINGS', [
            'showField' => array(__CLASS__, 'showFieldSettings'),
            'getValue' => array(__CLASS__, 'getValueSettings'),
            'saveFieldValue' => array(__CLASS__, 'saveFieldSettings'),
        ]));

        $fields = [
            new EditFieldOld('USER_FIELDS', [
                'showField' => array(__CLASS__, 'showFieldUserFields')
            ])
        ];

        $edit->addTab('tab_form_edit2', Loc::getMessage('DEVBX_FORMS_FORM_EDIT_TAB2'), Loc::getMessage('DEVBX_FORMS_FORM_EDIT_TAB2_TITLE'), $fields);

        $fields = [
            new EditFieldOld('CUSTOM_HTML_FORM', [
                'showField' => array(__CLASS__, 'showFieldCustomHtmlForm'),
                'getValue' => array(__CLASS__, 'getValueCustomHtmlForm'),
                'saveFieldValue' => array(__CLASS__, 'saveFieldCustomHtmlForm'),
            ])
        ];

        $edit->addTab('tab_form_edit3', Loc::getMessage('DEVBX_FORMS_FORM_EDIT_TAB3'), Loc::getMessage('DEVBX_FORMS_FORM_EDIT_TAB3_TITLE'), $fields);
    }

    public static function adminMenu(&$arItems)
    {
        $arFormResultItems = [];

        $dbRes = Forms\FormTable::getList(array(
            'filter' => array('=FORM_TYPE' => static::getType()),
            'select' => array('ID', 'NAME' => 'LANG_NAME.NAME'),
        ));

        while ($arRes = $dbRes->fetch()) {
            $arFormResultItems [] = [
                'text' => Loc::getMessage('DEVBX_FORMS_MENU_FORM_RESULT_LIST_ITEM', array('#NAME#' => $arRes['NAME'])),
                'page_icon' => 'default_page_icon',
                'url' => 'devbx_form_result_list.php?lang=' . LANGUAGE_ID . '&FORM_ID=' . $arRes['ID'],
                'more_url' => array('devbx_form_result_edit.php?lang=' . LANGUAGE_ID . '&FORM_ID=' . $arRes['ID']),
            ];
        }

        $arItems[] = [
            'text' => Loc::getMessage('DEVBX_FORMS_MENU_FORM_RESULT_LIST'),
            'page_icon' => 'default_page_icon',
            'items_id' => 'menu_devbx_form_result_list',
            'items' => $arFormResultItems,
        ];
    }

    public static function getType()
    {
        return 'SIMPLE';
    }

    public static function OnAfterUserTypeAddHandler($arFields)
    {
        if (preg_match('#DEVBX_FORM_(\d+)#', $arFields['ENTITY_ID'], $matches)) {
            $entity = Forms\FormManager::getInstance()->getFormInstance($matches[1]);
            if (!$entity)
                return;

            if ($entity->getForm()->getFormType() == static::getType()) {
                static::createEventForForm($entity);
            }
        }
    }
}


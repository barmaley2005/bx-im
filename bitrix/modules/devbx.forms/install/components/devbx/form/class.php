<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\FileInputUtility;
use Bitrix\Main\UserField\Internal\UserFieldHelper;
use DevBx\Forms\FormControl;
use DevBx\Forms\FormTable;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class CDevBxFormsForm extends CBitrixComponent
{
    const defaultFormAction = 'devbx_form_show';
    const actionPostForm = 'devbx_form_post';

    /**
     * @var \DevBx\Forms\FormResultEntity
     */
    protected $entity = false;

    public function onPrepareComponentParams($arParams)
    {
        $arParams['FORM_ID'] = intval($arParams['FORM_ID']);

        $arParams['ACTION_VARIABLE'] = isset($arParams['ACTION_VARIABLE']) ? trim($arParams['ACTION_VARIABLE']) : '';
        if ($arParams['ACTION_VARIABLE'] == '') {
            $arParams['ACTION_VARIABLE'] = 'form-action';
        }

        $arParams['AJAX_LOAD_FORM'] = $arParams['AJAX_LOAD_FORM'] == 'Y' ? 'Y' : 'N';

        if (!isset($arParams['READ_ONLY_FIELDS']) || !is_array($arParams['READ_ONLY_FIELDS']))
        {
            $arParams['READ_ONLY_FIELDS'] = array();
        }

        $this->arResult['ORIGINAL_PARAMETERS'] = $arParams;

        return $arParams;
    }

    public function showAjaxLoadForm()
    {
        //CJSCore::Init(array('core','ajax'));

        //$containerId = $this->getEditAreaId('ajax_load');
        $containerId = \Bitrix\Main\Security\Random::getString(10).$this->getEditAreaId('ajax_load');

        if ($this->arResult['FORM']['SETTINGS']['USE_DEFAULT_TEMPLATE'] != 'N') {

            /* для подключения скриптов и стилей */

            ob_start();

            $this->includeComponentTemplate();
            ob_end_clean();
        }
        global $APPLICATION;
        //$APPLICATION->IncludeComponent()

        ?>
        <div id="<?=$containerId?>">

        </div>
        <script>

            (function() {

                function loadTemplate()
                {
                    let postData = {
                        'siteId': '<?=CUtil::JSEscape(SITE_ID)?>',
                        'siteTemplateId': '<?=CUtil::JSEscape(SITE_TEMPLATE_ID)?>',
                        'sessid': BX.bitrix_sessid(),
                        'parameters': '<?=CUtil::JSEscape($this->arResult['SIGNED_PARAMS'])?>',
                        'template': '<?=CUtil::JSEscape($this->arResult['SIGNED_TEMPLATE'])?>',
                        'form_id': '<?=$this->arParams['FORM_ID'];?>',
                        '<?=CUtil::JSEscape($this->arParams['ACTION_VARIABLE'])?>': '<?=CUtil::JSEscape(static::defaultFormAction)?>',
                    };

                    BX.ajax({
                        url: '<?=CUtil::JSEscape($this->getPath().'/ajax.php')?>',
                        method: 'POST',
                        data: postData,
                        onsuccess: function(result) {
                            let container = document.getElementById('<?=CUtil::JSEscape($containerId)?>');

                            if (container)
                            {
                                container.innerHTML = result;
                            }
                        }
                    });
                }

                function loadDevBxForm()
                {
                    console.log('laodDevBxForm');
                    if (typeof window.BX !== 'function')
                    {
                        let scriptElement;

                        if (typeof jsUtils !== 'function')
                        {
                            scriptElement = document.createElement('SCRIPT');
                            scriptElement.type = 'text/javascript';
                            scriptElement.src = '/bitrix/js/main/utils.min.js';
                            document.head.appendChild(scriptElement);
                        }

                        scriptElement = document.createElement('SCRIPT');
                        scriptElement.type = 'text/javascript';
                        scriptElement.src = '/bitrix/js/main/core/core.min.js';
                        scriptElement.onload = function() {
                            BX.ready(loadTemplate);
                        };

                        document.head.appendChild(scriptElement);
                    } else
                    {
                        console.log('loadTemplate');

                        loadTemplate();
                    }
                }

                if (typeof window.BX !== 'function')
                {
                    if (document.readyState === 'complete')
                    {
                        loadDevBxForm();
                    } else
                    {
                        //document.addEventListener('DOMContentLoaded', loadDevBxForm);
                        window.addEventListener('load', loadDevBxForm);
                    }
                } else
                {
                    loadDevBxForm();
                }

            })();
        </script>
        <?
    }

    protected function initEntity()
    {
        global $USER;

        $formId = $this->arParams['FORM_ID'];

        $this->arResult['FORM'] = FormTable::getRowById($formId);
        if (!$this->arResult['FORM']) {
            $this->addResultError(Loc::getMessage('DEVBX_FORMS_COMPONENT_FORM_NOT_FOUND', array('#FORM_ID#' => $formId)));
            return;
        }

        $this->entity = \DevBx\Forms\FormManager::getInstance()->getFormInstance($formId);
        if (!$this->entity) {
            $this->addResultError(Loc::getMessage('DEVBX_FORMS_COMPONENT_FORM_NOT_FOUND', array('#FORM_ID#' => $formId)));
            return;
        }

        $this->arResult['ALLOW_VIEW'] = !empty(array_intersect($USER->GetUserGroupArray(), $this->arResult['FORM']['VIEW_GROUPS']));
        $this->arResult['ALLOW_WRITE'] = !empty(array_intersect($USER->GetUserGroupArray(), $this->arResult['FORM']['WRITE_GROUPS']));

        if (!$this->arResult['ALLOW_VIEW']) {
            $this->addResultError(Loc::getMessage('DEVBX_FORMS_COMPONENT_FORM_ACCESS_DENIED_VIEW_FORM'));
            return;
        }
    }

    protected function initAction()
    {
        if ($this->request->isPost() && $this->request->getPost('form_id') == $this->arParams['FORM_ID']) {
            return $this->request->getPost($this->arParams['ACTION_VARIABLE']);
        }

        return false;
    }

    protected function showForm()
    {
        $arParams = &$this->arParams;
        $arResult = &$this->arResult;

        if ($this->arResult['FORM']['SETTINGS']['USE_DEFAULT_TEMPLATE'] != 'N') {
            $this->includeComponentTemplate();
        } else {
            if ($arResult['SUCCESS']) {

                $tpl = \DevBx\Forms\SimpleFormTplTable::getRowById([
                    'FORM_ID' => $arParams['FORM_ID'],
                    'NAME' => 'SUCCESS_FORM'
                ]);

                if ($tpl)
                {
                    FormControl::getFormControl($arResult, $arParams)->showTpl($tpl['TEMPLATE']);
                }
            } else {
                $tpl = \DevBx\Forms\SimpleFormTplTable::getRowById([
                    'FORM_ID' => $arParams['FORM_ID'],
                    'NAME' => 'FORM'
                ]);

                if ($tpl)
                {
                    ob_start();

                    FormControl::getFormControl($arResult, $arParams)->showTpl($tpl['TEMPLATE']);

                    $html = ob_get_clean();

                    $hiddenFields = '';

                    foreach ($arResult['HIDDEN_FIELDS'] as $ar)
                        $hiddenFields .= '<input type="hidden" name="' . $ar['NAME'] . '" value="' . $ar['VALUE'] . '">' . "\r\n";

                    if (strpos($html, '<form') === false) {
                        $formHeader = '<form action="' . POST_FORM_ACTION_URI . '" method="post" enctype="multipart/form-data">' . "\r\n";

                        $html = $formHeader . $hiddenFields . $html . "\r\n</form>";
                    } else {
                        $html = preg_replace_callback('#(<form.*>)#mi' . BX_UTF_PCRE_MODIFIER, function ($matches) use ($hiddenFields) {

                            return $matches[1] . $hiddenFields;

                        }, $html);
                    }

                    echo $html;
                }
            }
        }
    }

    protected function addResultError($text, $id = false)
    {
        $this->arResult['ERRORS'][] = ['id' => $id, 'text' => $text];
    }

    protected function validateAccessWriteForm()
    {
        if (!$this->arResult['ALLOW_WRITE'])
        {
            $this->addResultError('DEVBX_FORMS_COMPONENT_FORM_ACCESS_DENIED_WRITE_FORM');
            return false;
        }

        return true;
    }

    protected function getFormValuesFromPost(&$arFormValues)
    {
        $arResult = &$this->arResult;
        $arParams = &$this->arParams;

        foreach ($arResult['FIELDS'] as $arField) {

            if (in_array($arField['FIELD_NAME'], $arParams['READ_ONLY_FIELDS']))
            {
                continue;
            }

            if ($this->request->getFile($arField['INPUT_NAME'])) {
                $value = $this->request->getFile($arField['INPUT_NAME']);
            } else {
                $value = $this->request->getPost($arField['INPUT_NAME']);
            }

            if (empty($value)) {
                /*
                 обход бага компонента main.field.file
                использует имя ввода $arResult['userField']['FIELD_NAME'] вместо $arResult['fieldName']
                 */

                if ($this->request->getFile($arField['FIELD_NAME'])) {
                    $value = $this->request->getFile($arField['FIELD_NAME']);
                } else {
                    $value = $this->request->getPost($arField['FIELD_NAME']);
                }

            }

            $arFormValues[$arField['FIELD_NAME']] = $value;
        }
    }

    protected function setDefaultFormValues(&$arFormValues)
    {
        foreach ($this->arResult['FIELDS'] as $arField) {
            $defaultKey = '~DEFAULT_FIELD_VALUE_'.$arField['FIELD_NAME'];

            if (isset($this->arParams[$defaultKey]) && !isset($arFormValues[$arField['FIELD_NAME']]))
            {
                $arFormValues[$arField['FIELD_NAME']] = $this->arParams[$defaultKey];
            }
        }
    }

    protected function validateFormValues(&$arFormValues)
    {
        global $APPLICATION, $USER_FIELD_MANAGER;

        //$userFieldManager = UserFieldHelper::getInstance()->getManager();

        if (!$USER_FIELD_MANAGER->CheckFields($this->entity->getUfId(), null, $arFormValues)) {
            if ($ex = $APPLICATION->GetException()) {

                if ($ex instanceof CAdminException) {
                    $this->arResult['ERRORS'] = array_merge($this->arResult['ERRORS'], $ex->GetMessages());
                } else {
                    $this->arResult['ERRORS'][] = ['id' => false, 'text' => HTMLToTxt($ex->GetString())];
                }
            }
        }
    }

    protected function addNewFormResult($arFormValues)
    {
        global $USER;

        $arFormValues['ACTIVE'] = $this->arParams['CREATE_ACTIVATED'] != 'N' ? 'Y' : 'N'; //TODO добавить в .parameters.php
        $arFormValues['SITE_ID'] = SITE_ID;
        $arFormValues['CREATED_USER_ID'] = $USER->GetID();
        if (!$arFormValues['CREATED_USER_ID'])
            $arFormValues['CREATED_USER_ID'] = 0;
        $arFormValues['MODIFIED_USER_ID'] = $USER->GetID();
        if (!$arFormValues['MODIFIED_USER_ID'])
            $arFormValues['MODIFIED_USER_ID'] = 0;

        $arFileId = [];
        $fileInputUtility = FileInputUtility::instance();

        foreach ($this->arResult['FIELDS'] as $arField)
        {
            if ($arField['USER_TYPE_ID'] == 'file' && isset($arFormValues[$arField['FIELD_NAME']]) && !empty($arFormValues[$arField['FIELD_NAME']]))
            {
                $value = $arFormValues[$arField['FIELD_NAME']];

                $controlId = $fileInputUtility->getUserFieldCid($arField);
                $fileInputUtility->registerControl("", $controlId);

                /* @see \Bitrix\Main\UserField\Types\FileType::onBeforeSave */

                if (is_array($value))
                {
                    if (array_key_exists('name', $value))
                    {
                        $value['MODULE_ID'] = 'devbx.forms';
                        $value = \CFile::SaveFile($value, 'devbx.forms');
                        if ($value>0)
                        {
                            $cid = $fileInputUtility->registerControl("", $controlId);
                            $fileInputUtility->registerFile($cid, $value);
                            $arFileId[$cid][] = $value;
                        }
                    } else {

                        foreach ($value as $k=>&$singleValue)
                        {
                            if (is_array($singleValue) && array_key_exists('name', $singleValue))
                            {
                                $singleValue['MODULE_ID'] = 'devbx.forms';
                                $singleValue = \CFile::SaveFile($singleValue, 'devbx.forms');
                                if ($singleValue>0)
                                {
                                    $cid = $fileInputUtility->registerControl("", $controlId);
                                    $fileInputUtility->registerFile($cid, $singleValue);
                                    $arFileId[$cid][] = $singleValue;
                                }
                            }
                        }
                    }
                }

                if ($arField['MULTIPLE'] == 'Y' && !is_array($value))
                {
                    $value = array($value);
                }

                $arFormValues[$arField['FIELD_NAME']] = $value;
            }
        }

        $dataClass = $this->entity->getDataClass();
        $result = $dataClass::add($arFormValues);
        $deleteFile = !$result->isSuccess();

        foreach ($arFileId as $controlId=>$files)
        {
            foreach ($files as $fileId)
            {
                $fileInputUtility->unRegisterFile($controlId, $fileId);
                if ($deleteFile)
                    \CFile::Delete($fileId);
            }
        }

        return $result;
    }

    protected function prepareFieldsForForm($arFormValues, $bVarsFromForm)
    {
        global $USER_FIELD_MANAGER;

        $arResult = &$this->arResult;
        $arParams = &$this->arParams;

        foreach ($arResult['FIELDS'] as &$arField) {
            $defaultKey = '~DEFAULT_FIELD_VALUE_'.$arField['FIELD_NAME'];

            if (isset($arParams[$defaultKey]) && !isset($arFormValues[$arField['FIELD_NAME']]))
            {
                $value = $arParams[$defaultKey];

                switch($arField['USER_TYPE_ID'])
                {
                    case \CUserTypeDate::USER_TYPE_ID:
                    case \CUserTypeDateTime::USER_TYPE_ID:

                        $arField["SETTINGS"]["DEFAULT_VALUE"]['VALUE'] = $arParams[$defaultKey];

                        break;

                    case \CUserTypeEnum::USER_TYPE_ID:

                        foreach($arField['ENUM'] as &$enum)
                        {
                            if (is_array($value))
                            {
                                $enum['DEF'] = in_array($enum['ID'], $value) ? 'Y' : 'N';
                            } else
                            {
                                $enum['DEF'] = $value === $enum['ID'] ? 'Y' : 'N';
                            }
                        }
                        unset($enum);

                        break;
                    default:
                        $arField["SETTINGS"]["DEFAULT_VALUE"] = $arParams[$defaultKey];
                        break;
                }
            }
        }
        unset($arField);

        //$userFieldManager = UserFieldHelper::getInstance()->getManager();

        foreach ($arResult['FIELDS'] as &$arField) {
            $additionalParameters = array(
                'NAME' => $arField['INPUT_NAME'],
                'bVarsFromForm' => $bVarsFromForm,
                'VALUE' => $arFormValues[$arField['FIELD_NAME']],
            );

            $arField['HTML'] = $USER_FIELD_MANAGER->GetPublicEdit($arField, $additionalParameters);
            $arField['VALUE'] = htmlspecialcharsbx($arFormValues[$arField['FIELD_NAME']]);
            $arField['~VALUE'] = $arFormValues[$arField['FIELD_NAME']];
        }

        unset($arField);
    }

    protected function getFormFields()
    {
        global $USER_FIELD_MANAGER;

        $fields = [];

        //$userFieldManager = UserFieldHelper::getInstance()->getManager();

        foreach ($USER_FIELD_MANAGER->GetUserFields($this->entity->getUfId(), 0, LANGUAGE_ID) as $arField) {
            $arField['INPUT_NAME'] = ToLower(substr($arField['FIELD_NAME'], 3));

            $fields[$arField['FIELD_NAME']] = $arField;
        }

        return $fields;
    }

    protected function checkFormAction(&$arFormValues, &$bVarsFromForm)
    {
        global $APPLICATION;

        $arResult = &$this->arResult;
        $arParams = &$this->arParams;

        if ($arResult['ACTION'] == static::actionPostForm && check_bitrix_sessid()) {

            if ($this->validateAccessWriteForm())
            {
                $this->getFormValuesFromPost($arFormValues);
                $this->setDefaultFormValues($arFormValues);
                $this->validateFormValues($arFormValues);
            }

            if ($this->useCaptcha() && !$APPLICATION->CaptchaCheckCode($this->request["captcha_word"], $this->request["captcha_sid"]))
            {
                $this->addResultError(Loc::getMessage('DEVBX_FORMS_COMPONENT_FORM_INVALID_CAPTCHA'));
            }

            if (empty($arResult['ERRORS'])) {
                $result = $this->addNewFormResult($arFormValues);

                if (!$result->isSuccess()) {
                    foreach ($result->getErrorCollection() as $error) {
                        /* @var \Bitrix\Main\Error $error */

                        $arResult['ERRORS'][] = ['id' => $error->getCode(), 'text' => $error->getMessage()];
                    }
                } else {
                    $bVarsFromForm = false;
                    $arResult['SUCCESS'] = true;
                    $arResult['RESULT_ID'] = $result->getId();

                    $arSelectFields = array_keys($this->entity->getFields());

                    $arResult['RESULT'] = $this->entity->getDataClass()::getList(array(
                            'filter' => array('ID' => $arResult['RESULT_ID']),
                            'select' => $arSelectFields)
                    )->fetch();

                    $arFormValues = [];
                }
            }

            if (!empty($arResult['ERRORS']))
                $bVarsFromForm = true;
        }
    }

    protected function useCaptcha()
    {
        return $this->entity->getForm()->getSettings()['USE_CAPTCHA'] == 'Y';
    }

    public function executeComponent()
    {
        global $APPLICATION, $USER;

        if (!Loader::includeModule("devbx.forms")) {
            ShowError('devbx.forms not installed');
            return;
        }

        $arResult = &$this->arResult;
        $arParams = &$this->arParams;

        $arResult['ERRORS'] = [];

        $this->initEntity();
        if (!empty($arResult['ERRORS']))
        {
            $this->includeComponentTemplate();
            return;
        }

        $action = $this->initAction();
        $defaultAction = $action === false;

        if ($defaultAction)
        {
            $arResult['ACTION'] = static::defaultFormAction;
        } else
        {
            $arResult['ACTION'] = $action;
        }

        $salt = str_replace(':','.', $this->getName());

        $signer = new \Bitrix\Main\Security\Sign\Signer;
        $arResult['SIGNED_PARAMS'] = $signer->sign(base64_encode(serialize($this->arResult['ORIGINAL_PARAMETERS'])), $salt);
        $arResult['SIGNED_TEMPLATE'] = $signer->sign($this->getTemplateName(), $salt);

        //$userFieldManager = UserFieldHelper::getInstance()->getManager();

        $bVarsFromForm = false;

        $arResult['FIELDS'] = $this->getFormFields();

        $arFormValues = array();

        $this->checkFormAction($arFormValues, $bVarsFromForm);

        $this->prepareFieldsForForm($arFormValues, $bVarsFromForm);

        $arResult['HIDDEN_FIELDS'] = [];

        $ignoreQuery = [$arParams['ACTION_VARIABLE'], 'form_id', 'sessid', 'template', 'parameters', 'siteId', 'siteTemplateId'];

        $ignoreQuery = array_merge($ignoreQuery, \Bitrix\Main\HttpRequest::getSystemParameters());

        foreach ($this->request->getQueryList() as $key=>$val)
        {
            if (in_array($key, $ignoreQuery))
                continue;

            $arResult['HIDDEN_FIELDS'][] = ['NAME' => $key, 'VALUE' => $val];
        }

        $arResult['HIDDEN_FIELDS'][] = ['NAME' => $arParams['ACTION_VARIABLE'], 'VALUE' => static::actionPostForm];
        $arResult['HIDDEN_FIELDS'][] = ['NAME' => 'siteId', 'VALUE' => SITE_ID];
        $arResult['HIDDEN_FIELDS'][] = ['NAME' => 'siteTemplateId', 'VALUE' => SITE_TEMPLATE_ID];
        $arResult['HIDDEN_FIELDS'][] = ['NAME' => 'form_id', 'VALUE' => $arParams['FORM_ID']];
        $arResult['HIDDEN_FIELDS'][] = ['NAME' => 'sessid', 'VALUE' => bitrix_sessid()];

        if ($defaultAction && $arParams['AJAX_LOAD_FORM'] == 'Y')
        {
            $this->showAjaxLoadForm();
            return;
        }

        if ($arParams['AJAX_LOAD_FORM'] == 'Y')
        {
            $arResult['HIDDEN_FIELDS'][] = array(
                'NAME' => 'template',
                'VALUE' => $arResult['SIGNED_TEMPLATE']
            );

            $arResult['HIDDEN_FIELDS'][] = array(
                'NAME' => 'parameters',
                'VALUE' => $arResult['SIGNED_PARAMS']
            );
        }

        if ($this->useCaptcha())
        {
            $arResult["CAPTCHA_CODE"] = htmlspecialcharsbx($APPLICATION->CaptchaGetCode());
        }

        $this->showForm();
    }

}
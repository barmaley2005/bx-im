<?php

namespace DevBx\Forms\Controller;

use Bitrix\Iblock;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\ActionFilter;
use DevBx\Forms\DB\EO_FormSession;
use DevBx\Forms\DB\FormSessionDataTable;
use DevBx\Forms\DB\FormSessionTable;
use DevBx\Forms\FormManager;
use DevBx\Forms\FormTable;
use DevBx\Forms\FormTypes\WebFormType;
use DevBx\Forms\Internals\Utils;
use DevBx\Forms\WebForm\DataFields\DataHelper;
use DevBx\Forms\WebForm\Fields;
use DevBx\Forms\WebForm\WOBase;
use DevBx\Forms\WebForm\WOForm;
use DevBx\Forms\WebForm\WOFormConfig;
use DevBx\Forms\WebForm\WOFormPage;
use DevBx\Forms\WebForm\WOFormValue;
use DevBx\Forms\WebForm\WOValues;
use DevBx\Forms\Wizards\WebForm\Wizard;

class WebForm extends Main\Engine\Controller {

    public function configureActions()
    {
        return [
            'saveWebForm' => [
                '-prefilters' => [
                    ActionFilter\Authentication::class,
                    ActionFilter\Csrf::class,
                ]
            ],
            'getHtmlEditor' => [
                '-prefilters' => [
                    ActionFilter\Authentication::class,
                    ActionFilter\Csrf::class,
                ]
            ],
            'previewWebForm' => [
                '-prefilters' => [
                    ActionFilter\Authentication::class,
                    ActionFilter\Csrf::class,
                ]
            ],
            'fieldRequest' => [
                '-prefilters' => [
                    ActionFilter\Authentication::class,
                    ActionFilter\Csrf::class,
                ]
            ],
            'getIblockList' => [
                '-prefilters' => [
                    ActionFilter\Authentication::class,
                    ActionFilter\Csrf::class,
                ]
            ],
            'getIblockSectionFields' => [
                '-prefilters' => [
                    ActionFilter\Authentication::class,
                    ActionFilter\Csrf::class,
                ]
            ],
            'submitForm' => [
                '-prefilters' => [
                    ActionFilter\Authentication::class,
                    ActionFilter\Csrf::class,
                ]
            ],
        ];
    }

    public function saveWebFormAction($webFormId, $webForm, $webFormConfig)
    {
        $arWebForm = \Bitrix\Main\Web\Json::decode($webForm);
        if (!is_array($arWebForm))
        {
            $this->addError(new Main\Error('Failed decode WebForm'));
            return null;
        }

        $arWebFormConfig = Main\Web\Json::decode($webFormConfig);
        if (!is_array($arWebFormConfig))
        {
            $this->addError(new Main\Error('Failed decode WebFormConfig'));
            return null;
        }

        $obForm = new WOForm();

        $result = $obForm->setValues($arWebForm);
        if (!$result->isSuccess())
        {
            $this->addErrors($result->getErrors());
            return null;
        }

        $obFormConfig = new WOFormConfig();
        $result = $obFormConfig->setValues($arWebFormConfig);
        if (!$result->isSuccess())
        {
            $this->addErrors($result->getErrors());
            return null;
        }

        if ($webFormId>0)
        {
            $wizardConfig = FormTable::getList([
                'filter' => [
                    '=ID' => $webFormId,
                    '=FORM_TYPE' => Wizard::getTemplateId(),
                ]
            ])->fetchObject();

            if (!$wizardConfig)
            {
                $this->addError(new Main\Error(Loc::getMessage('DEVBX_FORMS_ERR_WEB_FORM_NOT_FOUND')));
                return null;
            }

            $config = $wizardConfig->getSettings();
            if (array_key_exists('USER_FIELDS', $config))
            {
                $result = $obForm->userFields->fillCollection($config['USER_FIELDS']);
                if (!$result->isSuccess())
                {
                    $this->addErrors($result->getErrors());
                    return null;
                }
            }

        } else {
            $wizardConfig = FormTable::createObject();
            $wizardConfig->setFormType(Wizard::getTemplateId());
        }

        $conn = Main\Application::getConnection();
        $conn->startTransaction();

        try {

            $result = $obForm->save($wizardConfig, $obFormConfig);

            if (!$result->isSuccess())
            {
                $this->addErrors($result->getErrors());
                return null;
            }

            if ($result->getId()>0)
            {
                $webFormId = $result->getId();
            }

            $conn->commitTransaction();

        } catch (\Exception $e)
        {
            $conn->rollbackTransaction();
            $this->addError(new Main\Error($e->getMessage()));
            return null;
        }

        $result = array(
            'webFormId' => $webFormId,
            'fields' => array(),
        );

        foreach ($obForm->getRegisteredFormObjects() as $fieldName=> $field)
        {
            if ($field->has('SYSTEM_ID'))
            {
                $result['fields'][] = array(
                    'config' => Utils::arrayToJSCamel($field->toArray()),
                    'fieldName' => $fieldName,
                );
            }
        }

        return $result;
    }

    public function getHtmlEditorAction($name, $config = 'simple', $content = '')
    {
        $htmlConfig = array(
            'simple' => array(
                'height' => 200,
                'minBodyWidth' => '260',
                'normalBodyWidth' => '260',
                'bAllowPhp' => false,
                'limitPhpAccess' => false,
                'showTaskbars' => false,
                'showNodeNavi' => false,
                'askBeforeUnloadPage' => false,
                'bbCode' => false,
                'siteId' => SITE_ID,
                'autoResize' => true,
                'autoResizeOffset' => 40,
                'saveOnBlur' => true,
                'controlsMap' => array(
                    array('id' => 'Bold',  'compact' => true, 'sort' => 80),
                    array('id' => 'Italic',  'compact' => true, 'sort' => 90),
                    array('id' => 'Underline',  'compact' => true, 'sort' => 100),
                    array('id' => 'Strikeout',  'compact' => false, 'sort' => 110),
                    array('id' => 'InsertImage',  'compact' => true, 'sort' => 220),
                    array('id' => 'FormFields', 'compact' => true, 'sort' => 220),
                    array('id' => 'Quote',  'compact' => false, 'sort' => 270),
                    array('separator' => true, 'compact' => false, 'sort' => 290),
                    array('id' => 'Fullscreen',  'compact' => true, 'sort' => 300),
                    array('id' => 'More',  'compact' => true, 'sort' => 310),
                    array('id' => 'AlignList', 'compact' => false, 'sort' => 320),
                )
            ),
            'full' => array(
                'height' => 250,
                'minBodyWidth' => '260',
                'normalBodyWidth' => '260',
                'bAllowPhp' => false,
                'limitPhpAccess' => false,
                'showTaskbars' => false,
                'showNodeNavi' => false,
                'askBeforeUnloadPage' => false,
                'bbCode' => false,
                'siteId' => SITE_ID,
                'autoResize' => true,
                'autoResizeOffset' => 40,
                'saveOnBlur' => true,
                'controlsMap' => array(
                    array('id' => 'Bold',  'compact' => true, 'sort' => 80),
                    array('id' => 'Italic',  'compact' => true, 'sort' => 90),
                    array('id' => 'Underline',  'compact' => true, 'sort' => 100),
                    array('id' => 'Strikeout',  'compact' => true, 'sort' => 110),
                    array('id' => 'RemoveFormat',  'compact' => true, 'sort' => 120),
                    array('id' => 'Color',  'compact' => true, 'sort' => 130),
                    array('id' => 'FontSelector',  'compact' => false, 'sort' => 135),
                    array('id' => 'FontSize',  'compact' => false, 'sort' => 140),
                    array('id' => 'FormFields', 'compact' => true, 'sort' => 142),
                    array('separator' => true, 'compact' => false, 'sort' => 145),
                    array('id' => 'OrderedList',  'compact' => true, 'sort' => 150),
                    array('id' => 'UnorderedList',  'compact' => true, 'sort' => 160),
                    array('id' => 'AlignList', 'compact' => false, 'sort' => 190),
                    array('separator' => true, 'compact' => false, 'sort' => 200),
                    array('id' => 'InsertLink',  'compact' => true, 'sort' => 210, 'wrap' => 'bx-b-link-'.$name),
                    array('id' => 'InsertImage',  'compact' => true, 'sort' => 220),
                    array('id' => 'InsertVideo',  'compact' => false, 'sort' => 230, 'wrap' => 'bx-b-video-'.$name),
                    array('id' => 'InsertTable',  'compact' => false, 'sort' => 250),
                    array('id' => 'Code',  'compact' => true, 'sort' => 260),
                    array('id' => 'Quote',  'compact' => true, 'sort' => 270, 'wrap' => 'bx-b-quote-'.$name),
                    array('id' => 'Smile',  'compact' => false, 'sort' => 280),
                    array('separator' => true, 'compact' => false, 'sort' => 290),
                    array('id' => 'Fullscreen',  'compact' => true, 'sort' => 310),
                    array('id' => 'BbCode',  'compact' => false, 'sort' => 340),
                    array('id' => 'More',  'compact' => true, 'sort' => 400)
                ),
            ),
        );

        if (!array_key_exists($config, $htmlConfig))
            $config = key($htmlConfig);

        Main\Loader::includeModule('fileman');

        ob_start();

        $editor = new \CHTMLEditor;
        $res = array_merge(
            $htmlConfig[$config ],
            array(
                'name' => $name,
                'id' => $name,
                'width' => '100%',
                'arSmilesSet' => array(),
                'arSmiles' => array(),
                'content' => $content,
                'fontSize' => '14px',
                'setFocusAfterShow' => false,
                //'iframeCss' =>  '.bx-spoiler {border:1px solid #C0C0C0;background-color:#fff4ca;padding: 4px 4px 4px 24px;color:#373737;border-radius:2px;min-height:1em;margin: 0;}',
            )
        );
        $editor->Show($res);

        return array(
            'content' => ob_get_clean(),
            'js' => Main\Page\Asset::getInstance()->getJs().\CJSCore::Init(['devbx_webform_htmleditor'], true),
            'css' => Main\Page\Asset::getInstance()->getCss(),
        );
    }

    public function previewWebFormAction($webForm)
    {
        define('VUEJS_DEBUG', true);

        $arWebForm = \Bitrix\Main\Web\Json::decode($webForm);
        if (!is_array($arWebForm))
        {
            $this->addError(new Main\Error('Failed decode WebForm'));
            return null;
        }

        $obForm = new WOForm();

        $result = $obForm->setValues($arWebForm);
        if (!$result->isSuccess())
        {
            $this->addErrors($result->getErrors());
            return null;
        }

        ob_start();

        //\Bitrix\Main\UI\Extension::load("ui.fontawesome4");
        \Bitrix\Main\UI\Extension::load("ui.vue3");
        \Bitrix\Main\UI\Extension::load("ui.vue3.vuex");

        \CJSCore::Init(['core','devbx_forms_vue_webform', 'devbx_core_utils']);

        $wizard = Wizard::getInstance();

        $fieldManager = $wizard->getFieldManager();

        $messages = Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/devbx.forms/install/components/devbx/webform/templates/.default/template.php');

        $messages = array_merge($messages, $wizard->getLangMessages());

        $arWebFormElements = array();

        foreach ($fieldManager->getWebFormFieldsGroup() as $group)
        {
            $arJSGroup = array(
                'NAME' => $group->getName(),
                'ITEMS' => array(),
            );

            foreach ($group->getFields() as $field)
            {
                $arJSGroup['ITEMS'][] = array(
                    'DATA' => $field::getFieldData(),
                );

                $messages = array_merge($messages, $field::getLangMessages());
            }

            $arWebFormElements[] = $arJSGroup;
        }

        $arWebFormFields = array();

        $fields = $obForm->getRegisteredFormFields();

        foreach ($fields as $field)
        {
            $field->initSystemId();
            $field->includePublicJS();
            $arWebFormFields = array_merge($arWebFormFields, $field->getFormFields());
        }

        $arJSWebFormFields = array();

        foreach ($arWebFormFields as $field)
        {
            if ($field instanceof WOBase)
                $field = $field->toArray(WOValues::PUBLIC);

            $arJSWebFormFields[] = $field;
        }

        $remoteResult = FormSessionTable::getNewSID();
        if (!$remoteResult->isSuccess())
        {
            $this->addErrors($remoteResult->getErrors());
            return null;
        }

        $sessionDbId = $remoteResult->getId();
        $formSID = $remoteResult->getData()['SID'];

        $arFormConfig = $obForm->toArray(WOValues::PUBLIC);

        FormSessionDataTable::add([
            'SESSION_ID' => $sessionDbId,
            'SYSTEM_ID' => 0,
            'NAME' => 'WEB_FORM',
            'VALUE_TYPE' => 'array',
            'VALUE_ARRAY' => $obForm->toArray(WOValues::ALL),
        ]);


        $arCulture = array();

        $culture = \Bitrix\Main\Context::getCurrent()->getCulture();

        foreach ($culture->entity->getScalarFields() as $field)
        {
            if ($field->isPrimary())
                continue;

            $arCulture[$field->getName()] = $culture->get($field->getName());
        }

        $arJSData = array(
            'WEB_FORM' => array(
                'CONFIG' => $arFormConfig,
                'FORM_ELEMENTS' => $arWebFormElements,
                'FORM_FIELDS' => $arJSWebFormFields,
            ),
            'CULTURE' => $arCulture,
            'FORM_CLASS' => 'devbx-webform-popup',
            'DEBUG' => true,
            'ADMIN' => true,
            'SID' => $formSID,
        );

        \CJSCore::Init(['window','ajax','date']);
        \CJSCore::GetCoreMessages();

        ?>
        <html>
        <head>
            <style>
                body {
                    height: 100%;
                    box-sizing: border-box;
                    margin: 0;
                    overscroll-behavior: none;
                }
            </style>
            <?
            $asset = Main\Page\Asset::getInstance();
            $asset->addString(\CJSCore::GetCoreMessagesScript(), false, Main\Page\AssetLocation::AFTER_CSS, Main\Page\AssetMode::STANDARD);
            $asset->addString(\CJSCore::GetCoreMessagesScript(true), false, Main\Page\AssetLocation::AFTER_CSS, Main\Page\AssetMode::COMPOSITE);

            echo $asset->getJs();
            echo $asset->getCss();
            echo $asset->getStrings();
            ?>
        </head>
        <body>

        <div id="preview-webform">
        </div>

        <script>

            BX.message(<?=Main\Web\Json::encode($messages)?>);

            vueApp = DevBX.Forms.WebForm.createWebFormApp(<?=\Bitrix\Main\Web\Json::encode(\DevBx\Forms\Internals\Utils::arrayToJSCamel($arJSData))?>);

            window.webForm = vueApp.mount('#preview-webform');
        </script>

        </body>
        </html>
        <?

        return array(
            'content' => ob_get_contents()
        );
    }

    public function getObjectFormSession(EO_FormSession $formSession)
    {
        $obForm = new \DevBx\Forms\WebForm\WOForm();

        if ($formSession->getWebFormId()>0)
        {
            $wizardConfig = \DevBx\Forms\FormTable::getList([
                'filter' => [
                    '=ID' => $formSession->getWebFormId(),
                    '=FORM_TYPE' => Wizard::getTemplateId(),
                ],
            ])->fetchObject();

            if (!$wizardConfig)
            {
                $this->addError(new Main\Error(Loc::getMessage('DEVBX_FORMS_ERR_WEB_FORM_NOT_FOUND')));
                return null;
            }

            $result = $obForm->setValues($wizardConfig->getSettings());
            if (!$result->isSuccess())
            {
                $this->addErrors($result->getErrors());
                return null;
            }
        } else {
            $sessionData = FormSessionDataTable::getList([
                'filter' => [
                    '=SESSION_ID' => $formSession->getId(),
                    '=SYSTEM_ID' => 0,
                    '=NAME' => 'WEB_FORM',
                    '=VALUE_TYPE' => 'array',
                ]
            ])->fetchObject();

            if (!$sessionData)
            {
                $this->addError(new Main\Error(Loc::getMessage('DEVBX_FORMS_ERR_WEB_FORM_NOT_FOUND')));
                return null;
            }

            $result = $obForm->setValues($sessionData->getValueArray());
            if (!$result->isSuccess())
            {
                $this->addErrors($result->getErrors());
                return null;
            }
        }

        return $obForm;
    }

    public function fieldRequestAction($sid, $fieldId, $params)
    {
        $formSession = FormSessionTable::getList([
            'filter' => [
                '=SID' => $sid
            ],
        ])->fetchObject();

        if (!$formSession)
        {
            $this->addError(new Main\Error(Loc::getMessage('DEVBX_FORMS_ERR_INVALID_FORM_SESSION_ID')));
            return null;
        }

        $obForm = $this->getObjectFormSession($formSession);
        if (!$obForm)
            return null;

        $obField = $obForm->getFormObjectBySystemId($fieldId);
        if (!$obField)
        {
            $this->addError(new Main\Error(Loc::getMessage('DEVBX_FORMS_ERR_WEB_FORM_FIELD_NOT_FOUND')));
            return null;
        }

        $result =  $obField->request($formSession, $params);

        if (!$result->isSuccess())
            $this->addErrors($result->getErrors());

        return $result->getData();
    }

    public function getIblockListAction()
    {
        if (!Main\Loader::includeModule('iblock'))
        {
            $this->addError(new Main\Error('iblock module not installed'));
            return false;
        }

        $result = array(
            'IBLOCK_TYPE' => array(),
            'IBLOCK_LIST' => array(),
        );

        $arIblockType = array();

        $iterator = \CIBlock::GetList(array('SORT'=>'ASC','NAME'=>'ASC','ID'=>'ASC'),array('CHECK_PERMISSIONS'=>'Y'));
        while ($ar = $iterator->Fetch())
        {
            $arIblockType[$ar['IBLOCK_TYPE_ID']] = true;

            $result['IBLOCK_LIST'][] = array(
                'ID' => $ar['ID'],
                'NAME' => $ar['NAME'],
                'IBLOCK_TYPE_ID' => $ar['IBLOCK_TYPE_ID'],
                'SORT' => $ar['SORT'],
            );
        }

        foreach (array_keys($arIblockType) as $iblockTypeId)
        {
            $arType = \CIBlockType::GetByIDLang($iblockTypeId, LANGUAGE_ID);

            if ($arType)
            {
                $result['IBLOCK_TYPE'][] = array(
                    'IBLOCK_TYPE_ID' => $arType['IBLOCK_TYPE_ID'],
                    'NAME' => $arType['NAME'],
                    'SORT' => $arType['SORT'],
                );
            }
        }

        usort($result['IBLOCK_TYPE'], function($a, $b) {
            if ($a['SORT'] == $b['SORT'])
                return 0;

            return $a['SORT']>$b['SORT'] ? 1 : -1;
        });

        return \DevBx\Forms\Internals\Utils::arrayToJSCamel($result);
    }

    public function getIblockSectionFieldsAction(int $iblockId)
    {
        if (!Main\Loader::includeModule('iblock'))
        {
            $this->addError(new Main\Error('iblock module not installed'));
            return false;
        }

        $arFilter = array(
            'ID' => $iblockId,
            'CHECK_PERMISSIONS'=>'Y',
        );

        $arIblock = \CIBlock::GetList(array('SORT'=>'ASC','NAME'=>'ASC','ID'=>'ASC'),$arFilter)->Fetch();
        if (!$arIblock)
        {
            $this->addError(new Main\Error(Loc::getMessage('DEVBX_FORMS_ERR_IBLOCK_NOT_FOUND')));
            return false;
        }

        $result = array(
            'FIELDS' => array_values(DataHelper::getIblockSectionFields($arIblock['ID'])),
        );

        return \DevBx\Forms\Internals\Utils::arrayToJSCamel($result);
    }

    /**
     * @param array $fields
     * @param string $varName
     * @return WOFormValue[]
     */
    public function objectFieldsByKeys(array $fields, string $varName = ''): array
    {
        $result = [];

        $prefix = $varName ? $varName.'.' : '';

        foreach ($fields as $formValue)
        {
            /* @var WOFormValue $formValue */

            if ($formValue->type == 'object')
            {
                $result = array_merge($result, $this->objectFieldsByKeys($formValue->value, $prefix.$formValue->name));
            } else {
                $result[$prefix.$formValue->name] = $formValue;
            }
        }

        return $result;
    }

    public function submitFormAction($sid, $fields)
    {
        $formSession = FormSessionTable::getList([
            'filter' => [
                '=SID' => $sid
            ],
        ])->fetchObject();

        if (!$formSession)
        {
            $this->addError(new Main\Error(Loc::getMessage('DEVBX_FORMS_ERR_INVALID_FORM_SESSION_ID')));
            return null;
        }

        $obForm = $this->getObjectFormSession($formSession);
        if (!$obForm)
            return null;

        $userFieldsData = Main\Web\Json::decode($fields);

        $arWebFormFields = array();

        $fields = $obForm->getRegisteredFormFields();

        foreach ($fields as $field)
        {
            $arWebFormFields = array_merge($arWebFormFields, $field->getFormFields());
        }

        $formValues = $this->objectFieldsByKeys($arWebFormFields);

        foreach ($userFieldsData as $fieldName=>$fieldValue)
        {
            if (!array_key_exists($fieldName, $formValues))
            {
                $this->addError(new Main\Error(Loc::getMessage('DEVBX_FORMS_ERR_UNKNOWN_FORM_FIELD',array('#NAME#'=>$fieldName))));
                return null;
            }

            $formValues[$fieldName]->value = $fieldValue;
        }

        $arDBFields = array();

        if ($formSession->getLid() !== null)
            $arDBFields['SITE_ID'] = $formSession->getLid();

        foreach ($obForm->pages as $obPage)
        {
            /* @var WOFormPage $obPage  */

            $showPage = $obPage->config->showPage->checkCondition($obForm);
            if (!$showPage)
                continue;

            $items = $obPage->rows->getVisibleItems();

            foreach ($items as $obField)
            {
                $remoteResult = $obField->validateFormValue($formSession);
                if ($remoteResult->isSuccess()) {
                    $arDBFields = array_merge($arDBFields, $obField->saveForDB($formSession));
                } else {
                    foreach ($remoteResult->getErrors() as $error)
                    {
                        $customData = $error->getCustomData();
                        if (!is_array($customData))
                            $customData = array();

                        $customData['fieldId'] = $obField->systemId;

                        $this->addError(new Main\Error($error->getMessage(), $error->getCode(), $customData));
                    }
                }
            }
        }

        if (count($this->errorCollection))
            return null;

        if ($formSession->getWebFormId()<=0)
            return array('DBFields'=>$arDBFields); //данные отправляются только если это предпросмотр в админке

        $obForm = FormTable::getList([
            'filter' => [
                '=ID' => $formSession->getWebFormId(),
                '=FORM_TYPE' => WebFormType::getType(),
            ],
        ])->fetchObject();

        if (!$obForm)
        {
            $this->addError(new Main\Error(Loc::getMessage('DEVBX_FORMS_ERR_FORM_NOT_FOUND_FOR_WIZARD',array('#ID#'=>$formSession->getWebFormId()))));
            return null;
        }

        $formInstance = FormManager::getInstance()->getFormInstance($obForm->getId());
        if (!$formInstance)
        {
            $this->addError(new Main\Error(Loc::getMessage('DEVBX_FORMS_ERR_FORM_NOT_FOUND_FOR_WIZARD',array('#ID#'=>$formSession->getWebFormId()))));
            return null;
        }

        $remoteResult = $formInstance->getDataClass()::add($arDBFields);

        if (!$remoteResult->isSuccess())
        {
            $this->addErrors($remoteResult->getErrors());
            return null;
        }

        return array('id'=>$remoteResult->getId());
    }

}
<?php

namespace DevBx\Forms\WebForm;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use DevBx\Forms\EO_Form;
use DevBx\Forms\FormLangNameTable;
use DevBx\Forms\FormManager;
use DevBx\Forms\FormTable;
use DevBx\Forms\WebForm\Fields;
use DevBx\Forms\Wizards\WebForm\Wizard;

/**
 * @property WOSettings $formSettings
 * @property WOFormActionCollection $formActions
 * @property WOFormPageCollection $pages
 * @property WOFormUserFieldCollection $userFields
 */
class WOForm extends WOBase
{
    protected $fieldNames = array();
    protected $formObjects = array();

    public function __construct()
    {
        parent::__construct(array(
            'FORM_SETTINGS' => (new WOSettings())->setParent($this),
            'FORM_ACTIONS' => (new WOFormActionCollection())->setParent($this),
            'PAGES' => (new WOFormPageCollection())->setParent($this),
            'FINISH_PAGE' => (new WOFinishPage)->setParent($this),
            'FINISH_PAGE_COND' => (new WOFinishPageCollection)->setParent($this),
            'USER_FIELDS' => (new WOFormUserFieldCollection())->setParent($this)
        ));
    }

    public function registerFieldName($fieldName, WOBase $obj)
    {
        $fieldName = static::sysMethodToFieldCase(trim($fieldName));

        if (preg_match('#\s#', $fieldName))
            throw new SystemException(Loc::getMessage('DEVBX_WEB_FORM_INVALID_FIELD_NAME',['#FIELD_NAME#'=>$fieldName]));

        if (!preg_match('#^[a-zA-Z_]#', $fieldName))
            throw new SystemException(Loc::getMessage('DEVBX_WEB_FORM_INVALID_FIELD_NAME',['#FIELD_NAME#'=>$fieldName]));

        if (!preg_match('#^[a-zA-Z0-9_]+$#', $fieldName))
            throw new SystemException(Loc::getMessage('DEVBX_WEB_FORM_INVALID_FIELD_NAME',['#FIELD_NAME#'=>$fieldName]));

        if (array_key_exists($fieldName, $this->fieldNames))
        {
            throw new SystemException(Loc::getMessage('DEVBX_WEB_FORM_FIELD_NAME_ALREADY_REGISTERED',['#FIELD_NAME#'=>$fieldName]));
        }

        $this->fieldNames[$fieldName] = $obj;
    }

    public function registerFormObject(WOBase $obj)
    {
        if (array_search($obj, $this->formObjects, true))
            throw new SystemException('Form Object already registered');

        $this->formObjects[] = $obj;
    }

    public function unRegisterFormObject(WOBase $obj)
    {
        $key = array_search($obj, $this->formObjects, true);
        if ($key === false)
            throw new SystemException('Form Object not registered');

        unset($this->formObjects[$key]);
    }

    /**
     * Возвращает все объекты на форме
     * @return WOBase[]|mixed
     */
    public function getRegisteredFormObjects()
    {
        return $this->fieldNames;
    }

    /**
     * Возвращает все поля для ввода формы
     * в отличие от getRegisteredFormObjects
     * @return Fields\Base[]|mixed
     */
    public function getRegisteredFormFields()
    {
        return $this->formObjects;
    }

    /**
     * Возвращает все пользовательский значения ввода формы
     * @return WOFormValue[]|mixed
     */
    public function getWebFormValues()
    {
        $result = [];

        foreach ($this->getRegisteredFormFields() as $ob)
        {
            array_push($result, ...$ob->getFormFields());
        }

        return $result;
    }

    public function getFormObjectBySystemId($id)
    {
        foreach ($this->getRegisteredFormFields() as $object)
        {
            /* @var Fields\Base $object */

            if ($object->systemId == $id)
                return $object;
        }

        return null;
    }

    public function getLanguageId()
    {
        return LANGUAGE_ID;
    }

    public function save(EO_Form $wizardConfig, WOFormConfig $formConfig)
    {
        global $USER_FIELD_MANAGER, $APPLICATION;

        $result = new Main\Entity\AddResult();

        $wizardConfig->setFormType(Wizard::getTemplateId());
        $wizardConfig->setViewGroups($formConfig->viewGroups);
        $wizardConfig->setWriteGroups($formConfig->writeGroups);

        $isNewForm = !$wizardConfig->isIdFilled();

        if ($isNewForm)
        {
            $obResult = $wizardConfig->save();
            if (!$obResult->isSuccess())
                return $result->addErrors($obResult->getErrors())->addError(new Main\Error(Loc::getMessage('DEVBX_WEB_FORM_ERR_CREATE_NEW_WEB_FORM')));

            $result->setId($obResult->getId());
        }

        /* название формы */

        $dbLangName = FormLangNameTable::getList([
            'filter' => [
                '=FORM_ID' => $wizardConfig->getId(),
                '=LANGUAGE_ID' => $this->getLanguageId(),
            ],
        ])->fetch();

        if (!$dbLangName)
        {
            FormLangNameTable::add([
                'FORM_ID' => $wizardConfig->getId(),
                'LANGUAGE_ID' => $this->getLanguageId(),
                'NAME' => $formConfig->name,
            ]);
        } else {
            if ($dbLangName['NAME'] != $formConfig->name)
            {
                FormLangNameTable::update($dbLangName['ID'], array('NAME'=>$formConfig->name));
            }
        }

        $formInstance = FormManager::getInstance()->getFormInstance($wizardConfig->getId());

        $arEntityFields = $USER_FIELD_MANAGER->GetUserFields($formInstance->getUfId());

        $arDelObjects = [];

        /* проверка пользователский полей, которые были ранее созданы для вебформы */
        foreach ($this->userFields as $woUserField)
        {
            /* @var WOFormUserField $woUserField */

            $arEntityField = $arEntityFields[$woUserField->userFieldName];

            $obWebFormField = $this->getFormObjectBySystemId($woUserField->systemId);

            if (!$obWebFormField)
            {
                /* если объекта больше нету на форме, удаляем пользовательское свойство из БД */

                if ($arEntityField)
                {
                    $ute = new \CUserTypeEntity();
                    if (!$ute->Delete($arEntityField['ID']))
                    {
                        $ex = $APPLICATION->GetException();
                        if ($ex) {
                            $result->addError(new \Bitrix\Main\Error($ex->GetString()));
                        } else {
                            $result->addError(new \Bitrix\Main\Error('unknown error'));
                        }

                        return $result;
                    }

                    unset($arEntityFields[$woUserField->userFieldName]);
                    $arDelObjects[] = $woUserField;
                }
            } else {
                if (!array_key_exists($woUserField->userFieldName, $arEntityFields))
                {
                    //пользовательское свойство было удалено, удаляем привязку у формы
                    $obWebFormField->systemId = 0;
                    $arDelObjects[] = $woUserField;
                }

            }
        }

        foreach ($arDelObjects as $collectionItem)
            $collectionItem->delete();

        unset($woUserField, $arEntityField, $obWebFormField, $woUserField, $arDelObjects, $collectionItem);

        if (!$isNewForm)
        {
            $obOldForm = new WOForm();

            WOBase::enableFieldCheck(false);
            $obResult = $obOldForm->setValues($wizardConfig->getSettings());
            WOBase::enableFieldCheck(true);

            if (!$obResult->isSuccess())
                return $result->addErrors($obResult->getErrors())->addError(new Main\Error(Loc::getMessage('DEVBX_WEB_FORM_ERR_LOAD_OLD_CONFIG')));

            unset($obResult);

            foreach ($obOldForm->getRegisteredFormFields() as $oldFormObject)
            {
                if ($oldFormObject->systemId>0 && !$this->getFormObjectBySystemId($oldFormObject->systemId))
                {
                    /* если у старого поля был установлен systemId и в новом гонфиге его нету, проверяем пользовательские
                    поля на удаление */

                    foreach ($obOldForm->userFields->getUserFieldsBySystemId($oldFormObject->systemId) as $woUserField)
                    {
                        if (array_key_exists($woUserField->userFieldName, $arEntityFields))
                        {
                            $ute = new \CUserTypeEntity();
                            if (!$ute->Delete($arEntityFields[$woUserField->userFieldName]['ID']))
                            {
                                $ex = $APPLICATION->GetException();
                                if ($ex) {
                                    $result->addError(new \Bitrix\Main\Error($ex->GetString()));
                                } else {
                                    $result->addError(new \Bitrix\Main\Error('unknown error'));
                                }

                                return $result;
                            }

                            unset($arEntityFields[$woUserField->userFieldName]);
                        }
                    }
                    unset($woUserField);
                }
            }

            unset($obOldForm, $oldFormObject);
        }

        $USER_FIELD_MANAGER->CleanCache();
        $arEntityFields = $USER_FIELD_MANAGER->GetUserFields($formInstance->getUfId());

        foreach ($this->getRegisteredFormFields() as $formObject)
        {
            if (!$formObject->systemId)
                $formObject->initSystemId();

            $formUserFields = $formObject->getUfFields();

            foreach ($formUserFields as $formUserField)
            {
                $findFormUserField = $this->userFields->getUserFieldByName($formUserField['FIELD_NAME']);
                if ($findFormUserField !== null && $findFormUserField->systemId != $formObject->systemId)
                {
                    return $result->addError(new Main\Error(Loc::getMessage(
                        'DEVBX_WEB_FORM_ERR_USER_FIELD_ALREADY_USED_BY_OTHER_WEB_FORM_FIELD',
                        ['#FIELD_NAME#'=>$formUserField['FIELD_NAME']])));
                }

                if (!$findFormUserField && array_key_exists($formUserField['FIELD_NAME'], $arEntityFields))
                {
                    return $result->addError(new Main\Error(Loc::getMessage(
                        'DEVBX_WEB_FORM_ERR_USER_FIELD_ALREADY_USED_BY_CUSTOM_USER_FIELD',
                        ['#FIELD_NAME#'=>$formUserField['FIELD_NAME']])));
                }

                $formUserField['ENTITY_ID'] = $formInstance->getUfId();

                if ($formObject->has('LABEL'))
                {
                    if (!isset($formUserField['EDIT_FORM_LABEL']) && !isset($formUserField['LIST_COLUMN_LABEL'])
                        && !isset($formUserField['LIST_FILTER_LABEL'])
                    )
                    {
                        $formUserField['EDIT_FORM_LABEL'][$this->getLanguageId()] = $formObject->get('LABEL');
                        $formUserField['LIST_COLUMN_LABEL'][$this->getLanguageId()] = $formObject->get('LABEL');
                        $formUserField['LIST_FILTER_LABEL'][$this->getLanguageId()] = $formObject->get('LABEL');
                    }
                }

                $ute = new \CUserTypeEntity();

                if (array_key_exists($formUserField['FIELD_NAME'], $arEntityFields))
                {
                    $uteId = $arEntityFields[$formUserField['FIELD_NAME']]['ID'];

                    if (!$ute->Update($uteId, $formUserField))
                    {
                        $ex = $APPLICATION->GetException();
                        if ($ex) {
                            $result->addError(new \Bitrix\Main\Error($ex->GetString()));
                        } else {
                            $result->addError(new \Bitrix\Main\Error('unknown error'));
                        }

                        return $result->addError(new Main\Error(Loc::getMessage(
                            'DEVBX_WEB_FORM_ERR_UPDATE_USER_FIELD',
                            ['#FIELD_NAME#'=>$formUserField['FIELD_NAME']])));
                    }
                } else {
                    $uteId = $ute->Add($formUserField);

                    if (!$uteId)
                    {
                        $ex = $APPLICATION->GetException();
                        if ($ex) {
                            $result->addError(new \Bitrix\Main\Error($ex->GetString()));
                        } else {
                            $result->addError(new \Bitrix\Main\Error('unknown error'));
                        }

                        return $result->addError(new Main\Error(Loc::getMessage(
                            'DEVBX_WEB_FORM_ERR_ADD_USER_FIELD',
                            ['#FIELD_NAME#'=>$formUserField['FIELD_NAME']])));
                    }
                }

                if ($formUserField['ENUM_VALUES'])
                {
                    $arType = $USER_FIELD_MANAGER->GetUserType($formUserField["USER_TYPE_ID"]);

                    if ($arType && $arType['BASE_TYPE'] == 'enum')
                    {
                        $enumValuesByXmlId = [];

                        foreach ($formUserField['ENUM_VALUES'] as $enumValue)
                        {
                            $enumValuesByXmlId[$enumValue['XML_ID']] = $enumValue;
                        }

                        $enumValues = array();

                        $iterator = \CUserFieldEnum::GetList([],['USER_FIELD_ID'=>$uteId]);
                        while ($arEnum = $iterator->Fetch())
                        {
                            if (array_key_exists($arEnum['XML_ID'], $enumValuesByXmlId))
                            {
                                $enumValues[$arEnum['ID']] = $enumValuesByXmlId[$arEnum['XML_ID']];
                                unset($enumValuesByXmlId[$arEnum['XML_ID']]);
                            } else {
                                $enumValues[$arEnum['ID']] = array(
                                    'DEL' => 'Y',
                                );
                            }
                        }

                        $n = 0;
                        foreach ($enumValuesByXmlId as $enumValue) {
                            $n++;
                            $enumValues['n'.$n] = $enumValue;
                        }

                        $ufe = new \CUserFieldEnum;
                        if (!$ufe->SetEnumValues($uteId, $enumValues))
                        {
                            $ex = $APPLICATION->GetException();
                            if ($ex) {
                                $result->addError(new \Bitrix\Main\Error($ex->GetString()));
                            } else {
                                $result->addError(new \Bitrix\Main\Error('unknown error'));
                            }
                            return $result->addError(new Main\Error(Loc::getMessage(
                                'DEVBX_WEB_FORM_ERR_USER_FIELD_FAILED_SET_ENUM_VALUES',
                                ['#FIELD_NAME#'=>$formUserField['FIELD_NAME']])));
                        }
                    }
                }

                if (!$findFormUserField)
                {
                    $this->userFields->createObject()->setValues([
                        'SYSTEM_ID' => $formObject->systemId,
                        'USER_FIELD_NAME' => $formUserField['FIELD_NAME'],
                    ]);
                }

                unset($findFormUserField);
            }
        }

        $wizardConfig->setSettings($this->toArray());
        $obResult = $wizardConfig->save();
        if (!$obResult->isSuccess())
            $result->addErrors($obResult->getErrorMessages());

        return $result;
    }

}
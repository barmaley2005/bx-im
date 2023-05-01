<?php

namespace DevBx\Forms\WebForm\Fields;

use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Result;
use Bitrix\Main\Text\StringHelper;
use Bitrix\Main\UI\FileInputUtility;
use Bitrix\Main\Web\MimeType;
use Bitrix\Main\Web\Uri;
use DevBx\Forms\DB\EO_FormSession;
use DevBx\Forms\DB\FormSessionDataTable;
use DevBx\Forms\FormManager;
use DevBx\Forms\FormTable;
use DevBx\Forms\WebForm\Types;
use DevBx\Forms\WebForm\WOFormValue;

/**
 * @property string $fieldName
 * @property string $label
 * @property boolean $labelHidden
 * @property string $allowedFileTypes
 * @property int $maximumFileSize
 * @property boolean $multiple
 * @property int $maximumNumberOfFiles
 * @property string $helpText
 * @property Types\ConditionType $showRule
 * @property Types\ConditionType $requireRule
 * @property Types\ConditionType $readOnlyRule
 * @property Types\ConditionType $showCustomError
 * @property string $customError
 */
class FileUploadField extends Base {

    protected $formValue = null;

    public function __construct()
    {
        parent::__construct(array(
            (new Types\StringType('FIELD_NAME'))->configureDefaultValue('File'),
            (new Types\StringType('LABEL'))->configureDefaultValue(static::getFieldUntitledName()),
            new Types\BooleanType('LABEL_HIDDEN'),
            new Types\StringType('ALLOWED_FILE_TYPES'),
            (new Types\IntegerType('MAXIMUM_FILE_SIZE'))->configureNullable(),
            (new Types\BooleanType('MULTIPLE'))->configureDefaultValue(false),
            (new Types\IntegerType('MAXIMUM_NUMBER_OF_FILES'))->configureNullable(),
            new Types\StringType('HELP_TEXT'),
            (new Types\ConditionType('SHOW_RULE'))->configureDefaultValue(array('VALUE'=>'always')),
            (new Types\ConditionType('REQUIRE_RULE'))->configureDefaultValue(array('VALUE'=>'never')),
            (new Types\ConditionType('READ_ONLY_RULE'))->configureDefaultValue(array('VALUE'=>'never')),
            (new Types\ConditionType('SHOW_CUSTOM_ERROR'))->configureDefaultValue(array('VALUE'=>'never')),
            new Types\StringType('CUSTOM_ERROR'),
        ));

        $this->formValue = new WOFormValue($this, '', 'files', array());
    }

    public function getUfFields(): array
    {
        $field = array(
            'USER_TYPE_ID' => 'file',
            'FIELD_NAME' => 'UF_' . StringHelper::strtoupper($this->fieldName),
            'MULTIPLE' => $this->multiple ? 'Y' : 'N',
            'MANDATORY' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'EXTENSIONS' => $this->allowedFileTypes,
        );

        return array($field);
    }

    public function sysSetValue($name, $value): Result
    {
        $result = parent::sysSetValue($name, $value);

        if ($result->isSuccess())
        {
            if ($name == 'FIELD_NAME')
            {
                $this->formValue->name = $value;
            }
        }

        return $result;
    }

    public function getFormFields(): array
    {
        return array($this->formValue);
    }

    public function uploadAction(EO_FormSession $formSession, $params): Result
    {
        $result = new Result();

        $request = Context::getCurrent()->getRequest();

        $arFile = $request->getFile('file');
        if (!is_array($arFile) || $arFile['error'] || $arFile['size'] <= 0) {
            return $result->addError(new Error(Loc::getMessage('DEVBX_FORMS_ERR_INVALID_UPLOAD_FILE')));
        }

        if ($this->maximumFileSize > 0 && $arFile['size'] > $this->maximumFileSize*1024*1024) {
            return $result->addError(new Error(Loc::getMessage('DEVBX_FORMS_ERR_UPLOAD_MAXIMUM_FILE_SIZE')));
        }

        $arTypes = explode(',', mb_strtolower($this->allowedFileTypes));

        $arTypes = array_map(function ($v) {
            return trim($v);
        }, $arTypes);

        $arTypes = array_filter($arTypes, function ($v) {
            return !empty($v);
        });

        if (!empty($arTypes)) {
            $fileExt = mb_strtolower(pathinfo($arFile['name'], PATHINFO_EXTENSION));

            if (!in_array($fileExt, $arTypes))
                return $result->addError(new Error(Loc::getMessage('DEVBX_FORMS_ERR_INVALID_FILE_EXT')));
        }

        $fileId = \CFile::SaveFile($arFile, 'devbx.forms');
        if (!$fileId)
            return $result->addError(new Error(Loc::getMessage('DEVBX_FORMS_ERR_FAILED_SAVE_FILE')));

        $remoteResult = FormSessionDataTable::add([
            'SESSION_ID' => $formSession->getId(),
            'SYSTEM_ID' => $this->systemId,
            'VALUE_TYPE' => 'file',
            'VALUE_INT' => $fileId
        ]);

        if (!$remoteResult->isSuccess()) {
            \CFile::Delete($fileId);

            $result->addErrors($remoteResult->getErrors());
            return $result;
        }

        $arData = array(
            'fileId' => $fileId,
            'type' => $arFile['type'],
            'name' => $arFile['name'],
            'size' => $arFile['size'],
        );

        $uri = new Uri('/bitrix/tools/devbx.forms/devbx_webform.php');
        $uri->addParams(array(
            'action' => 'download',
            'sid' => $formSession->getSid(),
            'systemId' => $this->systemId,
            'fileId' => $fileId,
        ));
        $arData['download'] = $uri->getUri();

        if (MimeType::isImage($arFile['type']))
        {
            $uri = new Uri('/bitrix/tools/devbx.forms/devbx_webform.php');
            $uri->addParams(array(
                'action' => 'thumbnail',
                'sid' => $formSession->getSid(),
                'systemId' => $this->systemId,
                'fileId' => $fileId,
            ));

            $arData['preview'] = $uri->getUri();
        }

        $result->setData($arData);

        return $result;
    }

    public function deleteFileAction(EO_FormSession $formSession, $params): Result
    {
        $result = new Result();

        $sessionData = FormSessionDataTable::getList([
            'filter' => [
                '=SESSION_ID' => $formSession->getId(),
                '=SYSTEM_ID' => $this->systemId,
                '=VALUE_TYPE' => 'file',
                '=VALUE_INT' => $params['fileId']
            ],
        ])->fetchObject();

        if (!$sessionData)
        {
            return $result->addError(new Error(Loc::getMessage('DEVBX_FORMS_ERR_FILE_NOT_FOUND')));
        }

        \CFile::Delete($sessionData['VALUE_INT']);

        $sessionData->delete();

        return $result;
    }

    public static function getFieldId()
    {
        return 'file_upload';
    }

    public static function getGroupId()
    {
        return 'input';
    }

    public static function getFieldData(): array
    {
        return array_merge(parent::getFieldData(), array(
            'ICON' => 'upload',
            'LAYOUT_TEMPLATE' => 'devbx-form-layout-field-file-upload',
            'MIN_SIZE' => 6,
        ));
    }

    public function validateFormValue(EO_FormSession $formSession): Result
    {
        $result = new Result();

        $value = $this->formValue->value;

        if (!empty($value))
        {
            $arFileId = array();

            $iterator = FormSessionDataTable::getList([
                'filter' => [
                    '=SESSION_ID' => $formSession->getId(),
                    '=SYSTEM_ID' => $this->systemId,
                    '=VALUE_TYPE' => 'file',
                ],
            ]);

            while ($obData = $iterator->fetchObject())
            {
                $arFileId[] = $obData->getValueInt();
            }

            foreach ($value as $fileId)
            {
                if (!in_array($fileId, $arFileId))
                {
                    return $result->addError(new Error(Loc::getMessage('DEVBX_FORMS_ERR_FILE_NOT_FOUND')));
                }
            }
        }

        if ($this->customError && $this->showCustomError->checkCondition($this->getForm()))
        {
            return $result->addError(new Error($this->customError));
        }

        if (empty($value) && $this->requireRule->checkCondition($this->getForm()))
        {
            return $result->addError(new Error(Loc::getMessage('DEVBX_WEB_FORM_ERR_FIELD_REQUIRED',
                array('#FIELD_NAME#'=>$this->label))));
        }

        return $result;
    }

    public function saveForDB(EO_FormSession $formSession): array
    {
        global $USER_FIELD_MANAGER;

        $value = $this->formValue->value;

        $dbFieldName = 'UF_'.StringHelper::strtoupper($this->fieldName);

        if (empty($value))
        {
            return array($dbFieldName=>null);
        }

        if ($formSession->getWebFormId()<=0)
        {
            return array();
        }

        $obForm = FormTable::getList([
            'filter' => [
                '=WIZARD_ID' => $formSession->getWebFormId()
            ],
        ])->fetchObject();

        if (!$obForm)
            return array();

        $formInstance = FormManager::getInstance()->getFormInstance($obForm->getId());

        if (!$formInstance)
            return array();

        $arEntityFields = $USER_FIELD_MANAGER->GetUserFields($formInstance->getUfId());

        if (!array_key_exists($dbFieldName, $arEntityFields))
            return array();

        $arUserField = $arEntityFields[$dbFieldName];

        $fileInputUtility = FileInputUtility::instance();
        $controlId = $fileInputUtility->getUserFieldCid($arUserField);
        $fileInputUtility->registerControl("", $controlId);

        foreach ($value as $singleValue)
        {
            $cid = $fileInputUtility->registerControl("", $controlId);
            $fileInputUtility->registerFile($cid, $singleValue);
        }

        if ($this->multiple)
            return array($dbFieldName=>$value);

        return array($dbFieldName=>reset($value));
    }

    public function includePublicJS()
    {
        Asset::getInstance()->addJs('/bitrix/js/devbx.forms/fields/field.file.upload.js');
        Asset::getInstance()->addCss('/bitrix/css/devbx.forms/fields/field.file.upload.css');
        \CJSCore::Init(['devbx_webform_file_upload']);
    }
}
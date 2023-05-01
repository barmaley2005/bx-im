<?php

namespace DevBx\Forms\WebForm\Fields;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Result;
use Bitrix\Main\Text\StringHelper;
use DevBx\Forms\DB\EO_FormSession;
use DevBx\Forms\WebForm\Types;
use DevBx\Forms\WebForm\WOFormValue;

/**
 * @property string $fieldName
 * @property string $label
 * @property boolean $labelHidden
 * @property string $defaultValue
 * @property string $placeholder
 * @property string $helpText
 * @property Types\ConditionType $showRule
 * @property Types\ConditionType $requireRule
 * @property Types\ConditionType $readOnlyRule
 * @property Types\ConditionType $showCustomError
 * @property string $customError
 */
class EmailField extends Base {
    protected $formValue = null;

    public function __construct()
    {
        parent::__construct(array(
            (new Types\StringType('FIELD_NAME'))->configureDefaultValue('Email'),
            (new Types\StringType('LABEL'))->configureDefaultValue(static::getFieldUntitledName()),
            new Types\BooleanType('LABEL_HIDDEN'),
            new Types\StringType('DEFAULT_VALUE'),
            new Types\StringType('PLACEHOLDER'),
            new Types\StringType('HELP_TEXT'),
            (new Types\ConditionType('SHOW_RULE'))->configureDefaultValue(array('VALUE'=>'always')),
            (new Types\ConditionType('REQUIRE_RULE'))->configureDefaultValue(array('VALUE'=>'never')),
            (new Types\ConditionType('READ_ONLY_RULE'))->configureDefaultValue(array('VALUE'=>'never')),
            (new Types\ConditionType('SHOW_CUSTOM_ERROR'))->configureDefaultValue(array('VALUE'=>'never')),
            new Types\StringType('CUSTOM_ERROR'),
        ));

        $this->formValue = new WOFormValue($this, '', 'string');
    }

    public function getUfFields(): array
    {
        $field = array(
            'USER_TYPE_ID' => 'string',
            'FIELD_NAME' => 'UF_'.StringHelper::strtoupper($this->fieldName),
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
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

    public static function getFieldId()
    {
        return 'email';
    }

    public static function getGroupId()
    {
        return 'input';
    }

    public static function getFieldData(): array
    {
        return array_merge(parent::getFieldData(), array(
            'ICON' => 'envelope-o',
            'LAYOUT_TEMPLATE' => 'devbx-form-layout-field-email',
        ));
    }

    public function validateFormValue(EO_FormSession $formSession): Result
    {
        $result =  new Result();

        $value = trim($this->formValue->value);

        if (!empty($value) && !check_email($value))
        {
            return $result->addError(new Error(Loc::getMessage('DEVBX_WEB_FORM_ERR_INVALID_EMAIL')));
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
        $value = $this->formValue->value;

        return array(
            'UF_' . StringHelper::strtoupper($this->fieldName) => $value
        );
    }

    public function includePublicJS()
    {
        Asset::getInstance()->addJs('/bitrix/js/devbx.forms/fields/field.email.js');
        Asset::getInstance()->addCss('/bitrix/css/devbx.forms/fields/field.email.css');
    }
}
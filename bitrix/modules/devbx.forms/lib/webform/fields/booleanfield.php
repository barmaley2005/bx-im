<?php

namespace DevBx\Forms\WebForm\Fields;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Result;
use Bitrix\Main\Text\StringHelper;
use DevBx\Forms\DB\EO_FormSession;
use DevBx\Forms\WebForm\Types;
use DevBx\Forms\WebForm\WOFormField;
use DevBx\Forms\WebForm\WOFormValue;

/**
 * @property string $fieldName
 * @property string $label
 * @property boolean $labelHidden
 * @property array $type
 * @property string $customLabels
 * @property string $customLabelYes
 * @property string $customLabelNo
 * @property boolean $defaultValue
 * @property string $helpText
 * @property Types\ConditionType $showRule
 * @property Types\ConditionType $requireRule
 * @property Types\ConditionType $readOnlyRule
 * @property Types\ConditionType $showCustomError
 * @property string $customError
 */
class BooleanField extends Base {
    const TYPE_RADIO = 'RADIO';
    const TYPE_CHECKBOX = 'CHECKBOX';

    protected $formValue = null;

    public function __construct()
    {
        parent::__construct(array(
            (new Types\StringType('FIELD_NAME'))->configureDefaultValue('YesNo'),
            (new Types\StringType('LABEL'))->configureDefaultValue(static::getFieldUntitledName()),
            new Types\BooleanType('LABEL_HIDDEN'),
            (new Types\EnumType('TYPE'))->configureValues(array(
                array('value'=>static::TYPE_RADIO),
                array('value'=>static::TYPE_CHECKBOX),
            ))->configureDefaultValue(static::TYPE_RADIO),
            (new Types\BooleanType('CUSTOM_LABELS'))->configureDefaultValue(false),
            new Types\StringType('CUSTOM_LABEL_YES'),
            new Types\StringType('CUSTOM_LABEL_NO'),
            (new Types\BooleanType('DEFAULT_VALUE'))->configureDefaultValue(false),
            new Types\StringType('HELP_TEXT'),
            (new Types\ConditionType('SHOW_RULE'))->configureDefaultValue(array('VALUE'=>'always')),
            (new Types\ConditionType('REQUIRE_RULE'))->configureDefaultValue(array('VALUE'=>'never')),
            (new Types\ConditionType('READ_ONLY_RULE'))->configureDefaultValue(array('VALUE'=>'never')),
            (new Types\ConditionType('SHOW_CUSTOM_ERROR'))->configureDefaultValue(array('VALUE'=>'never')),
            new Types\StringType('CUSTOM_ERROR'),
        ));

        $this->formValue = new WOFormValue($this,'', 'boolean');
    }

    public function getUfFields(): array
    {
        $field = array(
            'USER_TYPE_ID' => 'boolean',
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
        return 'boolean';
    }

    public static function getGroupId()
    {
        return 'input';
    }

    public static function getFieldData(): array
    {
        return array_merge(parent::getFieldData(), array(
            'ICON' => 'check-square-o ',
            'LAYOUT_TEMPLATE' => 'devbx-form-layout-field-boolean',
        ));
    }

    public function validateFormValue(EO_FormSession $formSession): Result
    {
        $result =  new Result();

        $value = $this->formValue->value;

        if ($this->customError && $this->showCustomError->checkCondition($this->getForm()))
        {
            return $result->addError(new Error($this->customError));
        }

        if ($value !== true && $this->requireRule->checkCondition($this->getForm()))
        {
            return $result->addError(new Error(Loc::getMessage('DEVBX_WEB_FORM_ERR_FIELD_REQUIRED',
                array('#FIELD_NAME#'=>$this->label))));
        }

        return $result;
    }

    public function saveForDB(EO_FormSession $formSession): array
    {
        $value = $this->formValue->value === true;

        $result['UF_'.StringHelper::strtoupper($this->fieldName)] = $value ? 1 : 0;

        return $result;
    }

    public function includePublicJS()
    {
        Asset::getInstance()->addJs('/bitrix/js/devbx.forms/fields/field.boolean.js');
        Asset::getInstance()->addCss('/bitrix/css/devbx.forms/fields/field.boolean.css');
    }
}
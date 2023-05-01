<?php

namespace DevBx\Forms\WebForm\Fields;

use Bitrix\Main;
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
 * @property array $type
 * @property string $timeStart
 * @property string $timeEnd
 * @property string $step
 * @property string $defaultValue
 * @property string $placeholder
 * @property string $helpText
 * @property Types\ConditionType $showRule
 * @property Types\ConditionType $requireRule
 * @property Types\ConditionType $readOnlyRule
 * @property Types\ConditionType $showCustomError
 * @property string $customError
 */
class DateField extends Base {

    protected $formValue = null;

    public function __construct()
    {
        parent::__construct(array(
            (new Types\StringType('FIELD_NAME'))->configureDefaultValue('Date'),
            (new Types\StringType('LABEL'))->configureDefaultValue(static::getFieldUntitledName()),
            new Types\BooleanType('LABEL_HIDDEN'),
            (new Types\EnumType('TYPE'))->configureValues(array(
                array('value'=>'DATE'),
                array('value'=>'DATETIME'),
                array('value'=>'TIME'),
            ))->configureDefaultValue('DATE'),
            new Types\StringType('TIME_START'),
            new Types\StringType('TIME_END'),
            new Types\StringType('STEP'),
            new Types\StringType('DEFAULT_VALUE'),
            new Types\StringType('PLACEHOLDER'),
            new Types\StringType('HELP_TEXT'),
            (new Types\ConditionType('SHOW_RULE'))->configureDefaultValue(array('VALUE'=>'always')),
            (new Types\ConditionType('REQUIRE_RULE'))->configureDefaultValue(array('VALUE'=>'never')),
            (new Types\ConditionType('READ_ONLY_RULE'))->configureDefaultValue(array('VALUE'=>'never')),
            (new Types\ConditionType('SHOW_CUSTOM_ERROR'))->configureDefaultValue(array('VALUE'=>'never')),
            new Types\StringType('CUSTOM_ERROR'),
        ));

        $this->formValue = new WOFormValue($this, '', 'date');
    }

    public function getUfFields(): array
    {
        $result = [];

        switch ($this->type)
        {
            case 'DATE':
                $result[] = array(
                    'USER_TYPE_ID' => 'date',
                    'FIELD_NAME' => 'UF_'.StringHelper::strtoupper($this->fieldName),
                    'MULTIPLE' => 'N',
                    'MANDATORY' => 'N',
                    'SHOW_IN_LIST' => 'Y',
                    'EDIT_IN_LIST' => 'Y',
                );
                break;
            case 'DATETIME':
                $result[] = array(
                    'USER_TYPE_ID' => 'datetime',
                    'FIELD_NAME' => 'UF_'.StringHelper::strtoupper($this->fieldName),
                    'MULTIPLE' => 'N',
                    'MANDATORY' => 'N',
                    'SHOW_IN_LIST' => 'Y',
                    'EDIT_IN_LIST' => 'Y',
                );
                break;
            case 'TIME':
                $result[] = array(
                    'USER_TYPE_ID' => 'integer',
                    'FIELD_NAME' => 'UF_'.StringHelper::strtoupper($this->fieldName),
                    'MULTIPLE' => 'N',
                    'MANDATORY' => 'N',
                    'SHOW_IN_LIST' => 'Y',
                    'EDIT_IN_LIST' => 'Y',
                );
                break;
        }

        return $result;
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

            if ($name == 'TYPE')
            {
                $this->formValue->type = mb_strtolower($value);
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
        return 'date';
    }

    public static function getGroupId()
    {
        return 'input';
    }

    public static function getFieldData(): array
    {
        return array_merge(parent::getFieldData(), array(
            'ICON' => 'calendar',
            'LAYOUT_TEMPLATE' => 'devbx-form-layout-field-date',
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

        if (empty($value) && $this->requireRule->checkCondition($this->getForm()))
        {
            return $result->addError(new Error(Loc::getMessage('DEVBX_WEB_FORM_ERR_FIELD_REQUIRED',
                array('#FIELD_NAME#'=>$this->label))));
        }

        return $result;
    }

    public function saveForDB(EO_FormSession $formSession): array
    {
        $dbFieldName = 'UF_' . StringHelper::strtoupper($this->fieldName);
        $value = $this->formValue->value;

        if (!strlen($value))
        {
            return array($dbFieldName=>null);
        }

        switch ($this->type)
        {
            case 'DATE':
                $value = Main\Type\Date::createFromTimestamp($value);
                break;
            case 'DATETIME':
                $value = Main\Type\DateTime::createFromTimestamp($value);
                break;
        }

        return array($dbFieldName=>$value);
    }

    public function includePublicJS()
    {
        Asset::getInstance()->addJs('/bitrix/js/devbx.forms/fields/field.date.js');
        Asset::getInstance()->addCss('/bitrix/css/devbx.forms/fields/field.date.css');

        \CJSCore::Init(['devbx_webform_datepicker']);
    }
}
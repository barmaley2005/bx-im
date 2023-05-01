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
 * @property array $type
 * @property int $decimalPlaces
 * @property string $defaultValue
 * @property array $visual
 * @property float $incrementValue
 * @property string $placeholder
 * @property float $minValue
 * @property float $maxValue
 * @property string $helpText
 * @property Types\ConditionType $showRule
 * @property Types\ConditionType $requireRule
 * @property Types\ConditionType $readOnlyRule
 * @property Types\ConditionType $showCustomError
 * @property string $customError
 */

class NumberField extends Base {
    const TYPE_INTEGER = 'INTEGER';
    const TYPE_DECIMAL = 'DECIMAL';

    const VISUAL_TEXT = 'TEXT';
    const VISUAL_SPINNER = 'SPINNER';

    protected $formValue = null;

    public function __construct()
    {
        parent::__construct(array(
            (new Types\StringType('FIELD_NAME'))->configureDefaultValue('Number'),
            (new Types\StringType('LABEL'))->configureDefaultValue(static::getFieldUntitledName()),
            new Types\BooleanType('LABEL_HIDDEN'),
            (new Types\EnumType('TYPE'))->configureValues(array(
                array('value'=>static::TYPE_INTEGER),
                array('value'=>static::TYPE_DECIMAL),
            ))->configureDefaultValue(static::TYPE_INTEGER),
            (new Types\IntegerType('DECIMAL_PLACES'))->configureDefaultValue(2),
            new Types\StringType('DEFAULT_VALUE'),
            (new Types\EnumType('VISUAL'))->configureValues(array(
                array('value'=>static::VISUAL_TEXT),
                array('value'=>static::VISUAL_SPINNER),
            ))->configureDefaultValue(static::VISUAL_TEXT),
            (new Types\FloatType('INCREMENT_VALUE'))->configureDefaultValue(1),
            new Types\StringType('PLACEHOLDER'),
            (new Types\FloatType('MIN_VALUE'))->configureNullable(),
            (new Types\FloatType('MAX_VALUE'))->configureNullable(),
            new Types\StringType('HELP_TEXT'),
            (new Types\ConditionType('SHOW_RULE'))->configureDefaultValue(array('VALUE'=>'always')),
            (new Types\ConditionType('REQUIRE_RULE'))->configureDefaultValue(array('VALUE'=>'never')),
            (new Types\ConditionType('READ_ONLY_RULE'))->configureDefaultValue(array('VALUE'=>'never')),
            (new Types\ConditionType('SHOW_CUSTOM_ERROR'))->configureDefaultValue(array('VALUE'=>'never')),
            new Types\StringType('CUSTOM_ERROR'),
        ));

        $this->formValue = new WOFormValue($this, '', 'number');
    }

    public function getUfFields(): array
    {
        $result = [];

        switch ($this->type)
        {
            case static::TYPE_INTEGER:
                $result[] = array(
                    'USER_TYPE_ID' => 'integer',
                    'FIELD_NAME' => 'UF_'.StringHelper::strtoupper($this->fieldName),
                    'MULTIPLE' => 'N',
                    'MANDATORY' => 'N',
                    'SHOW_IN_LIST' => 'Y',
                    'EDIT_IN_LIST' => 'Y',
                );
                break;
            case static::TYPE_DECIMAL:
                $result[] = array(
                    'USER_TYPE_ID' => 'double',
                    'FIELD_NAME' => 'UF_'.StringHelper::strtoupper($this->fieldName),
                    'MULTIPLE' => 'N',
                    'MANDATORY' => 'N',
                    'SHOW_IN_LIST' => 'Y',
                    'EDIT_IN_LIST' => 'Y',
                    'SETTINGS' => array(
                        'PRECISION' => $this->decimalPlaces,
                    ),
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
        }

        return $result;
    }

    public function getFormFields(): array
    {
        return array($this->formValue);
    }

    public static function getFieldId()
    {
        return 'number';
    }

    public static function getGroupId()
    {
        return 'input';
    }

    public static function getFieldData(): array
    {
        return array_merge(parent::getFieldData(), array(
            'ICON' => 'number',
            'LAYOUT_TEMPLATE' => 'devbx-form-layout-field-number',
        ));
    }

    public function validateFormValue(EO_FormSession $formSession): Result
    {
        $result =  new Result();

        $value = $this->formValue->value;

        if (!empty($value) && !is_numeric($value))
        {
            return $result->addError(new Error(Loc::getMessage('DEVBX_WEB_FORM_FIELD_NUMBER_ERR_VALUE')));
        }

        if ($this->customError && $this->showCustomError->checkCondition($this->getForm()))
        {
            return $result->addError(new Error($this->customError));
        }

        if (!strlen($value) && $this->requireRule->checkCondition($this->getForm()))
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

        return array($dbFieldName=>$value);
    }

    public function includePublicJS()
    {
        Asset::getInstance()->addJs('/bitrix/js/devbx.forms/fields/field.number.js');
        Asset::getInstance()->addCss('/bitrix/css/devbx.forms/fields/field.number.css');
    }
}
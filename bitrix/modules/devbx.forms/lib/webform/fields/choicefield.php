<?php

namespace DevBx\Forms\WebForm\Fields;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Text\StringHelper;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use DevBx\Forms\DB\EO_FormSession;
use DevBx\Forms\FormManager;
use DevBx\Forms\FormTable;
use DevBx\Forms\WebForm\Types;
use DevBx\Forms\WebForm\WOFormValue;

/**
 * @property string $fieldName
 * @property string $label
 * @property boolean $labelHidden
 * @property array $type
 * @property string $placeholder
 * @property string $helpText
 * @property array $choiceOptions
 * @property array $options
 * @property array $visual
 * @property Types\ConditionType $showRule
 * @property Types\ConditionType $requireRule
 * @property Types\ConditionType $readOnlyRule
 * @property Types\ConditionType $showCustomError
 * @property string $customError
 */
class ChoiceField extends Base {

    const TYPE_DROP_DOWN = 'DROP_DOWN';
    const TYPE_RADIO = 'RADIO';
    const TYPE_CHECKBOX = 'CHECKBOX';

    const CHOICE_OPTION_ASSIGN_VALUES = 'ASSIGN_VALUES';

    const VISUAL_ONE_COLUMN = 'ONE_COLUMN';
    const VISUAL_TWO_COLUMN = 'TWO_COLUMN';
    const VISUAL_SIDE_BY_SIDE = 'SIDE_BY_SIDE';

    protected $formValue = null;
    protected $formText = null;

    public function __construct()
    {
        parent::__construct(array(
            (new Types\StringType('FIELD_NAME'))->configureDefaultValue('Choice'),
            (new Types\StringType('LABEL'))->configureDefaultValue(static::getFieldUntitledName()),
            new Types\BooleanType('LABEL_HIDDEN'),
            (new Types\EnumType('TYPE'))->configureValues(array(
                array('value'=>static::TYPE_DROP_DOWN),
                array('value'=>static::TYPE_RADIO),
                array('value'=>static::TYPE_CHECKBOX),
            ))->configureDefaultValue(static::TYPE_DROP_DOWN),
            new Types\StringType('PLACEHOLDER'),
            new Types\StringType('HELP_TEXT'),
            (new Types\EnumType('CHOICE_OPTIONS'))->configureValues(array(
                array('value'=>static::CHOICE_OPTION_ASSIGN_VALUES),
            ))->configureMultiple()->configureDefaultValue([]),
            'OPTIONS' => new WOChoiceOptionCollection(),
            (new Types\EnumType('VISUAL'))->configureValues(array(
                array('value'=>static::VISUAL_ONE_COLUMN),
                array('value'=>static::VISUAL_TWO_COLUMN),
                array('value'=>static::VISUAL_SIDE_BY_SIDE),
            ))->configureDefaultValue(static::VISUAL_ONE_COLUMN),
            (new Types\ConditionType('SHOW_RULE'))->configureDefaultValue(array('VALUE'=>'always')),
            (new Types\ConditionType('REQUIRE_RULE'))->configureDefaultValue(array('VALUE'=>'never')),
            (new Types\ConditionType('READ_ONLY_RULE'))->configureDefaultValue(array('VALUE'=>'never')),
            (new Types\ConditionType('SHOW_CUSTOM_ERROR'))->configureDefaultValue(array('VALUE'=>'never')),
            new Types\StringType('CUSTOM_ERROR'),
        ));

        $this->formValue = new WOFormValue($this,'Value', 'string');
        $this->formText = new WOFormValue($this,'Text', 'string', true);
    }

    public function getUfFields(): array
    {
        $field = array(
            'USER_TYPE_ID' => 'enumeration',
            'FIELD_NAME' => 'UF_'.StringHelper::strtoupper($this->fieldName),
            'MULTIPLE' => $this->type == static::TYPE_CHECKBOX ? 'Y' : 'N',
            'MANDATORY' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'ENUM_VALUES' => array(),
        );

        $sort = 100;

        $arXmlId = array();

        $options = $this->options;

        foreach ($options as $option)
        {
            /* @var WOChoiceOption $option */

            $xmlId = trim($option->value);

            while (empty($xmlId) || array_key_exists($xmlId, $arXmlId))
            {
                $xmlId = md5(uniqid());
            }

            if ($option->value != $xmlId)
            {
                $option->value = $xmlId;
            }

            $arXmlId[$xmlId]++;

            $field['ENUM_VALUES'][] = array(
                'VALUE' => $option->text,
                'XML_ID' => $option->value,
                'DEF' => $option->selected ? 'Y' : 'N',
                'SORT' => $sort,
            );

            $sort+=100;
        }

        return array($field);
    }

    protected function checkOptions()
    {
        $arXmlId = array();
        $options = $this->options;

        foreach ($options as $option)
        {
            /* @var WOChoiceOption $option */

            $xmlId = trim($option->value);

            while (empty($xmlId) || array_key_exists($xmlId, $arXmlId))
            {
                $xmlId = md5(uniqid());
            }

            if ($option->value != $xmlId)
            {
                $option->value = $xmlId;
            }

            $arXmlId[$xmlId]++;
        }
    }

    public function sysSetValue($name, $value): Result
    {
        $result = parent::sysSetValue($name, $value);

        if ($result->isSuccess())
        {
            if ($name == 'TYPE')
            {
                if ($value == 'CHECKBOX')
                {
                    $this->formValue->type = 'array';
                    $this->formText->type = 'array';
                } else {
                    $this->formValue->type = 'string';
                    $this->formText->type = 'string';
                }
            }
        }

        return $result;
    }

    public function getFormFields(): array
    {
        $this->checkOptions();

        if ($this->formValue->value === null)
        {
            if ($this->type == 'CHECKBOX')
            {
                $this->formValue->value = array();
                $this->formText->value = array();
            } else {
                $this->formValue->value = '';
                $this->formText->value = '';
            }
        }

        $result = array();

        $result[] = new WOFormValue($this, $this->fieldName,'object', array(
            $this->formValue,
            $this->formText,
        ));

        return $result;
    }

    public function isExistsValue($value)
    {
        $options = $this->options;

        foreach ($options as $option) {
            /* @var WOChoiceOption $option */

            if ($option->value === $value)
                return true;
        }

        return false;
    }

    public function validateFormValue(EO_FormSession $formSession): Result
    {
        $result = new Result();

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

        if ($this->type == static::TYPE_CHECKBOX)
        {
            if (!is_array($value))
                return $result->addError(new Error(Loc::getMessage('DEVBX_WEB_FORM_ERR_FIELD_CHOICE_INVALID_VALUE')));

            foreach ($value as $singleValue)
            {
                if (!empty($singleValue) && !$this->isExistsValue($singleValue))
                    return $result->addError(new Error(Loc::getMessage('DEVBX_WEB_FORM_ERR_FIELD_CHOICE_INVALID_VALUE')));
            }
        } else {
            if (!empty($value) && !$this->isExistsValue($value))
                return $result->addError(new Error(Loc::getMessage('DEVBX_WEB_FORM_ERR_FIELD_CHOICE_INVALID_VALUE')));
        }

        return $result;
    }

    public function saveForDB(EO_FormSession $formSession): array
    {
        global $USER_FIELD_MANAGER;

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

        $dbFieldName = 'UF_'.StringHelper::strtoupper($this->fieldName);

        if (!array_key_exists($dbFieldName, $arEntityFields))
            return array();

        $arUserField = $arEntityFields[$dbFieldName];

        $arDBEnum = array();

        $iterator = \CUserFieldEnum::GetList([],['USER_FIELD_ID'=>$arUserField]);
        while ($ar = $iterator->Fetch())
        {
            $arDBEnum[$ar['XML_ID']] = $ar;
        }

        $value = $this->formValue->value;

        if (is_array($value))
        {
            $DBValue = array();

            foreach ($value as $singleValue)
            {
                if (array_key_exists($singleValue, $arDBEnum))
                {
                    $DBValue[] = $arDBEnum[$singleValue]['ID'];
                }
            }

        } else {
            $DBValue = $arDBEnum[$value]['ID'];
        }

        return array(
            $dbFieldName => $DBValue,
        );
    }

    public static function getFieldId()
    {
        return 'choice';
    }

    public static function getGroupId()
    {
        return 'input';
    }

    public static function getFieldData(): array
    {
        return array_merge(parent::getFieldData(), array(
            'ICON' => 'list-ul',
            'LAYOUT_TEMPLATE' => 'devbx-form-layout-field-choice',
        ));
    }

    public function includePublicJS()
    {
        Asset::getInstance()->addJs('/bitrix/js/devbx.forms/fields/field.choice.js');
        Asset::getInstance()->addCss('/bitrix/css/devbx.forms/fields/field.choice.css');

        \CJSCore::Init(['devbx_webform_multiselect']);
    }
}
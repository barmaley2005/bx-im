<?php

namespace DevBx\Forms\WebForm\DataFields;

use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Result;
use Bitrix\Main\Text\StringHelper;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;
use DevBx\Forms\DB\EO_FormSession;
use DevBx\Forms\WebForm\Fields\Base;
use DevBx\Forms\WebForm\WOFormValue;
use DevBx\Forms\WebForm\WOValues;
use DevBx\Forms\WebForm\Types;

/**
 * @property string $label
 * @property string $labelHidden
 * @property string $type
 * @property string|int $iblockId
 * @property WOFilterCollection $filter
 * @property string $sortField1
 * @property string $sortOrder1
 * @property string $sortField2
 * @property string $sortOrder2
 * @property boolean $showPicture
 * @property string $pictureField
 * @property string $placeHolder
 * @property string $helpText
 * @property Types\ConditionType $showRule
 * @property Types\ConditionType $requireRule
 * @property Types\ConditionType $readOnlyRule
 * @property Types\ConditionType $showCustomError
 * @property string $customError
 */
class IblockSectionField extends Base
{
    const TYPE_DROP_DOWN = 'DROP_DOWN';
    const TYPE_DROP_DOWN_PICTURE = 'DROP_DOWN_PICTURE';

    protected $formValue = null;
    protected $formValueSectionId = null;
    protected $formValueSectionName = null;
    protected $arValueSection = null;

    public function __construct()
    {
        parent::__construct(array(
            (new Types\StringType('FIELD_NAME'))->configureDefaultValue('IblockSection'),
            (new Types\StringType('LABEL'))->configureDefaultValue(static::getFieldUntitledName()),
            new Types\BooleanType('LABEL_HIDDEN'),
            (new Types\EnumType('TYPE'))->configureValues(array(
                array('value' => static::TYPE_DROP_DOWN),
                array('value' => static::TYPE_DROP_DOWN_PICTURE),
            ))->configureDefaultValue(static::TYPE_DROP_DOWN),
            (new Types\IntegerType('IBLOCK_ID'))->configurePrivate(),
            'FILTER' => new WOFilterCollection(),
            (new Types\StringType('SORT_FIELD1'))->configureDefaultValue('LEFT_MARGIN')->configurePrivate(),
            (new Types\StringType('SORT_ORDER1'))->configureDefaultValue('ASC')->configurePrivate(),
            (new Types\StringType('SORT_FIELD2'))->configurePrivate(),
            (new Types\StringType('SORT_ORDER2'))->configureDefaultValue('ASC')->configurePrivate(),
            (new Types\StringType('PICTURE_FIELD'))->configureDefaultValue('PICTURE'),
            new Types\StringType('PLACEHOLDER'),
            new Types\StringType('HELP_TEXT'),
            (new Types\ConditionType('SHOW_RULE'))->configureDefaultValue(array('VALUE' => 'always')),
            (new Types\ConditionType('REQUIRE_RULE'))->configureDefaultValue(array('VALUE' => 'never')),
            (new Types\ConditionType('READ_ONLY_RULE'))->configureDefaultValue(array('VALUE' => 'never')),
            (new Types\ConditionType('SHOW_CUSTOM_ERROR'))->configureDefaultValue(array('VALUE' => 'never')),
            new Types\StringType('CUSTOM_ERROR'),
        ));

        $this->formValueSectionId = (new WOFormValue($this, 'ID', 'number'))
            ->addSetValueModifier(array($this, 'setFormValueSectionId'));
        $this->formValueSectionName = (new WOFormValue($this, 'NAME', 'string'))
            ->addGetValueModifier(array($this, 'getFormValueSectionName'));

        $this->formValue = new WOFormValue($this, $this->fieldName, 'object', array(
            $this->formValueSectionId,
            $this->formValueSectionName,
        ));
    }

    public function getUfFields(): array
    {
        $field = array(
            'USER_TYPE_ID' => 'iblock_section',
            'FIELD_NAME' => 'UF_' . StringHelper::strtoupper($this->fieldName),
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'SETTINGS' => array(
                'IBLOCK_ID' => $this->iblockId,
            ),
        );

        return array($field);
    }

    public function sysSetValue($name, $value): Result
    {
        $result = parent::sysSetValue($name, $value);

        if ($result->isSuccess()) {
            if ($name == 'FIELD_NAME') {
                $this->formValue->name = $value;
            }
        }

        return $result;
    }

    public function getFormFields(): array
    {
        $result = array();

        $result[] = $this->formValue;

        return $result;
    }

    public function makeIblockFilter($jsValues)
    {
        $result = array(
            'IBLOCK_ID' => $this->iblockId,
        );

        $ibFields = DataHelper::getIblockSectionFields($this->iblockId);

        foreach ($this->filter as $filterItem) {
            /* @var WOFilterItem $filterItem */

            $filterField = $filterItem->field;

            if (!array_key_exists($filterField, $ibFields))
                continue;

            $ibField = $ibFields[$filterField];

            if ($filterField == 'IBLOCK_SECTION_ID')
                $filterField = 'SECTION_ID';

            $filterValue = null;

            switch ($filterItem->valueType) {
                case 'field':
                    $filterValue = $jsValues[$filterItem->value];
                    break;
                case 'number':
                    $filterValue = doubleval($filterItem->value);
                    break;
                case 'datetime':
                    $filterValue = DateTime::createFromTimestamp($filterItem->value);
                    break;
                case 'date':
                    $filterValue = Date::createFromTimestamp($filterItem->value);
                    break;
                case 'array':
                    $filterValue = [];

                    foreach ($filterItem->value as $singleValue)
                    {
                        switch ($singleValue['valueType'])
                        {
                            case 'field':
                                $filterValue[] = $jsValues[$singleValue['value']];
                                break;
                            case 'fieldValue':
                                $filterValue[] = $singleValue['value'];
                                break;
                        }
                    }

                    break;
                default:
                    $filterValue = $filterItem->value;
                    break;
            }

            $isUserField = substr($filterField, 0, 3) == 'UF_';

            switch ($ibField['COND_TYPE']) {
                case 'string':
                    switch ($filterItem->type) {
                        case 'isFilledOut':
                            $result['!=' . $filterField][] = false;
                            break;
                        case 'isNotFilledOut':
                            $result['=' . $filterField][] = false;
                            break;
                        case 'is':
                            $result['=' . $filterField][] = $filterValue;
                            break;
                        case 'isNot':
                            $result['!=' . $filterField][] = $filterValue;
                            break;
                        case 'Contains':
                            $result[$filterField][] = '%' . $filterValue . '%';
                            break;
                        case 'DoesNotContain':
                            $result['!' . $filterField][] = '%' . $filterValue . '%';
                            break;
                        case 'StartsWith':
                            $result[$filterField][] = $filterValue . '%';
                            break;
                        case 'DoesNotStartWith':
                            $result['!' . $filterField][] = $filterValue . '%';
                            break;
                        case 'EndsWith':
                            $result[$filterField][] = '%' . $filterValue;
                            break;
                        case 'DoesNotEndWith':
                            $result['!' . $filterField][] = '%' . $filterValue;
                            break;
                    }
                    break;
                case 'number':
                    switch ($filterItem->type) {
                        case 'isFilledOut':
                            $result['!=' . $filterField][] = false;
                            break;
                        case 'isNotFilledOut':
                            $result['=' . $filterField][] = false;
                            break;
                        case 'is':
                            $result['=' . $filterField][] = $filterValue;
                            break;
                        case 'isNot':
                            $result['!=' . $filterField][] = $filterValue;
                            break;
                        case 'isGreater':
                            $result['>' . $filterField][] = $filterValue;
                            break;
                        case 'isLess':
                            $result['<' . $filterField][] = $filterValue;
                            break;
                        case 'isGreaterOrEqual':
                            $result['>=' . $filterField][] = $filterValue;
                            break;
                        case 'isLessOrEqual':
                            $result['<=' . $filterField][] = $filterValue;
                            break;
                    }
                    break;
                case 'boolean':
                    if ($ibField['FIELD_TYPE'] == 'file') {
                        switch ($filterItem->type) {
                            case 'isTrue':
                                $result['!=' . $filterField] = false;
                                break;
                            case 'isFalse':
                                $result['=' . $filterField] = false;
                                break;
                        }
                    } else {
                        switch ($filterItem->type) {
                            case 'isTrue':
                                $result['=' . $filterField] = $isUserField ? 1 : 'Y';
                                break;
                            case 'isFalse':
                                $result['=' . $filterField] = $isUserField ? 0 : 'N';
                                break;
                        }
                    }
                    break;
                case 'datetime':
                case 'date':
                    switch ($filterItem->type) {
                        case 'isFilledOut':
                            $result['!=' . $filterField][] = false;
                            break;
                        case 'isNotFilledOut':
                            $result['=' . $filterField][] = false;
                            break;
                        case 'isAfter':
                            $result['>' . $filterField][] = $filterValue;
                            break;
                        case 'isBefore':
                            $result['<' . $filterField][] = $filterValue;
                            break;
                        case 'onOrAfter':
                            $result['>=' . $filterField][] = $filterValue;
                            break;
                        case 'onOrBefore':
                            $result['<=' . $filterField][] = $filterValue;
                            break;
                        case 'isNow':
                        case 'isToday':
                            if ($filterItem->type == 'date') {
                                $filterValue = new Date();
                            } else {
                                $filterValue = new DateTime();
                            }

                            $result['=' . $filterField][] = $filterValue;
                            break;
                        case 'isInFuture':
                            if ($filterItem->type == 'date') {
                                $filterValue = new Date();
                            } else {
                                $filterValue = new DateTime();
                            }

                            $result['>' . $filterField][] = $filterValue;
                        case 'isInPast':
                            if ($filterItem->type == 'date') {
                                $filterValue = new Date();
                            } else {
                                $filterValue = new DateTime();
                            }

                            $result['>' . $filterField][] = $filterValue;
                            break;
                    }

                    break;
                case 'enum':
                    switch ($filterItem->type) {
                        case 'isFilledOut':
                            $result['!=' . $filterField][] = false;
                            break;
                        case 'isNotFilledOut':
                            $result['=' . $filterField][] = false;
                            break;
                        case 'is':
                            if (!isset($result['=' . $filterField]))
                                $result['=' . $filterField] = array();

                            array_push($result['=' . $filterField], ...$filterValue);
                            break;
                        case 'isNot':
                            if (!isset($result['!=' . $filterField]))
                                $result['!=' . $filterField] = array();

                            array_push($result['!=' . $filterField], ...$filterValue);
                            break;
                    }
                    break;
            }
        }

        foreach ($result as $k => $v) {
            if (is_array($v) && count($v) == 1) {
                $result[$k] = reset($v);
            }
        }

        return $result;
    }

    public function prepareFormItemDBResult($item, $iblockFields, $formSession)
    {
        foreach ($item as $fieldName => $fieldValue) {
            $isUserField = substr($fieldName, 0, 3) == 'UF_';

            switch ($iblockFields[$fieldName]['FIELD_TYPE']) {
                case 'file':
                    //$fieldValue = DataHelper::makePublicFileArray($formSession, $this->systemId, $fieldValue);
                    $fieldValue = DataHelper::makePublicFileArray($formSession, 0, $fieldValue);
                    break;
                case 'number':

                    if (is_array($fieldValue)) {
                        $fieldValue = array_map(function ($value) {
                            return is_numeric($value) ? doubleval($value) : null;
                        }, $fieldValue);
                    } else {
                        $fieldValue = is_numeric($fieldValue) ? doubleval($fieldValue) : null;
                    }
                    break;
                case 'boolean':
                    if ($isUserField) {
                        $fieldValue = $fieldValue > 0;
                    } else {
                        $fieldValue = $fieldName == 'Y';
                    }
                    break;
                case 'datetime':
                case 'date':

                    if (is_array($fieldValue)) {
                        $fieldValue = array_map(function ($value) {
                            try {
                                return (new DateTime($value))->getTimestamp();
                            } catch (\Exception $e) {
                                return null;
                            }
                        }, $fieldValue);
                    } else {

                        try {
                            $fieldValue = (new DateTime($fieldValue))->getTimestamp();
                        } catch (\Exception $e) {
                            $fieldValue = null;
                        }
                    }

                    break;

            }

            $item[$fieldName] = $fieldValue;
        }

        return $item;
    }

    public function getListAction(EO_FormSession $formSession, $params): Result
    {
        $result = new Result();

        Loader::includeModule('iblock');

        $jsFilter = Json::decode($params['filter']);
        if (!is_array($jsFilter))
            $jsFilter = array();

        $arSort = array();

        if ($this->sortField1) {
            $arSort[$this->sortField1] = $this->sortOrder1 == 'ASC' ? 'ASC' : 'DESC';
        }

        if ($this->sortField2) {
            $arSort[$this->sortField2] = $this->sortOrder2 == 'ASC' ? 'ASC' : 'DESC';
        }

        $iblockFields = DataHelper::getIblockSectionFields($this->iblockId);

        $arFilter = $this->makeIblockFilter($jsFilter);

        $arSelect = array(
            'ID',
            'NAME',
        );

        if ($this->type == static::TYPE_DROP_DOWN_PICTURE) {
            if (array_key_exists($this->pictureField, $iblockFields)) {
                $arSelect[] = $this->pictureField;
            }
        }

        $items = array();

        $iterator = \CIBlockSection::GetList($arSort, $arFilter, false, $arSelect);
        while ($item = $iterator->Fetch()) {
            $items[] = $this->prepareFormItemDBResult($item, $iblockFields, $formSession);
        }

        $result->setData(array('items' => $items));

        return $result;
    }

    public function setFormValueSectionId(WOFormValue $obj, $value)
    {
        $result = new Result();

        if (empty($value))
            return $result;

        Loader::includeModule('iblock');

        $arFilter = array(
            'IBLOCK_ID' => $this->iblockId,
            '=ID' => intval($value)
        );

        $arSection = \CIBlockSection::GetList([], $arFilter, false, array('ID', 'NAME'))->Fetch();
        if (!$arSection)
            $result->addError(new Error(Loc::getMessage('DEVBX_WEB_FORM_FIELD_ERR_IBLOCK_SECTION_NOT_FOUND')));

        $this->arValueSection = $arSection;

        return $result;
    }

    public function getFormValueSectionName(WOFormValue $obj, $value)
    {
        $result = new Result();

        if ($this->arValueSection) {
            $result->setData(array('VALUE' => $this->arValueSection[$obj->name]));
        }

        return $result;
    }

    public static function getFieldId()
    {
        return 'iblock_section';
    }

    public static function getGroupId()
    {
        return 'data';
    }

    public static function getFieldData(): array
    {
        return array_merge(parent::getFieldData(), array(
            'ICON' => 'bars',
            'LAYOUT_TEMPLATE' => 'devbx-form-layout-field-iblock-section',
        ));
    }

    public function validateFormValue(EO_FormSession $formSession): Result
    {
        $result = new Result();

        $value = $this->formValueSectionId->value;

        if (!empty($value) && !is_numeric($value)) {
            return $result->addError(new Error(Loc::getMessage('DEVBX_WEB_FORM_FIELD_NUMBER_ERR_VALUE')));
        }

        if ($this->customError && $this->showCustomError->checkCondition($this->getForm())) {
            return $result->addError(new Error($this->customError));
        }

        if (empty($value) && $this->requireRule->checkCondition($this->getForm())) {
            return $result->addError(new Error(Loc::getMessage('DEVBX_WEB_FORM_ERR_FIELD_REQUIRED',
                array('#FIELD_NAME#' => $this->label))));
        }

        return $result;
    }

    public function saveForDB(EO_FormSession $formSession): array
    {
        $value = $this->formValueSectionId->value;

        $result['UF_' . StringHelper::strtoupper($this->fieldName)] = $value;

        return $result;
    }

    public function includePublicJS()
    {
        Asset::getInstance()->addJs('/bitrix/js/devbx.forms/fields/field.iblock.section.js');
        Asset::getInstance()->addCss('/bitrix/css/devbx.forms/fields/field.iblock.section.css');

        \CJSCore::Init(['devbx_webform_multiselect']);
    }
}
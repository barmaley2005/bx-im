<?php

namespace DevBx\Forms\WebForm\Fields;

use Bitrix\Main\Page\Asset;
use DevBx\Forms\WebForm\WOFormLayout;
use DevBx\Forms\WebForm\Types;

/**
 * @property string $fieldName
 * @property string $label
 * @property boolean $labelHidden
 * @property string $helpText
 * @property WOFormLayout $layout
 * @property Types\ConditionType $showRule
 */
class SectionField extends Base {
    public function __construct()
    {
        parent::__construct(array(
            (new Types\StringType('FIELD_NAME'))->configureDefaultValue('Section'),
            (new Types\StringType('LABEL'))->configureDefaultValue(static::getFieldUntitledName()),
            new Types\BooleanType('LABEL_HIDDEN'),
            new Types\StringType('HELP_TEXT'),
            'LAYOUT' => (new WOFormLayout())->setParent($this),
            (new Types\ConditionType('SHOW_RULE'))->configureDefaultValue(array('VALUE'=>'always')),
        ));
    }

    public static function getFieldId()
    {
        return 'section';
    }

    public static function getGroupId()
    {
        return 'layout';
    }

    public static function getFieldData(): array
    {
        return array_merge(parent::getFieldData(), array(
            'ICON' => 'file-o',
            'LAYOUT_TEMPLATE' => 'devbx-form-layout-section',
            'MIN_SIZE' => 8,
            'DEFAULT_SIZE' => 24,
        ));
    }

    public function includePublicJS()
    {
        Asset::getInstance()->addJs('/bitrix/js/devbx.forms/fields/layout.section.js');
        Asset::getInstance()->addCss('/bitrix/css/devbx.forms/fields/layout.section.css');
    }
}
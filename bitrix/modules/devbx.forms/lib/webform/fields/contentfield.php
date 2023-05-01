<?php

namespace DevBx\Forms\WebForm\Fields;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use DevBx\Forms\WebForm\Types;

/**
 * @property string $content
 * @property Types\ConditionType $showRule
 */
class ContentField extends Base {
    public function __construct()
    {
        parent::__construct(array(
            (new Types\StringType('CONTENT'))->configureDefaultValue(function() {
                return Loc::getMessage('DEVBX_WEB_FORM_FIELD_DEFAULT_CONTENT');
            }),
            (new Types\ConditionType('SHOW_RULE'))->configureDefaultValue(array('VALUE'=>'always')),
        ));
    }

    public static function getFieldId()
    {
        return 'content';
    }

    public static function getGroupId()
    {
        return 'layout';
    }

    public static function getFieldData(): array
    {
        return array_merge(parent::getFieldData(), array(
            'ICON' => 'edit',
            'LAYOUT_TEMPLATE' => 'devbx-form-layout-content',
            'MIN_SIZE' => 3,
            'DEFAULT_SIZE' => 12,
        ));
    }

    public function includePublicJS()
    {
        Asset::getInstance()->addJs('/bitrix/js/devbx.forms/fields/layout.content.js');
        Asset::getInstance()->addCss('/bitrix/css/devbx.forms/fields/layout.content.css');
    }
}
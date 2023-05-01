<?php
namespace DevBx\Forms\WebForm;

use Bitrix\Main\Localization\Loc;
use DevBx\Forms\WebForm\Types;

/**
 * @property string $pageTitle
 * @property string $pageHelpText
 * @property string $prevButtonText
 * @property string $nextButtonText
 * @property Types\ConditionType $showNextButton
 * @property Types\ConditionType $showPage
 */
class WOFormPageConfig extends WOBase
{
    public function __construct()
    {
        parent::__construct(array(
            'PAGE_TITLE' => '',
            'PAGE_DESCRIPTION' => '',
            'PREV_BUTTON_TEXT' => Loc::getMessage('DEVBX_WEB_FORM_PREV_PAGE_TITLE'),
            'NEXT_BUTTON_TEXT' => Loc::getMessage('DEVBX_WEB_FORM_NEXT_PAGE_TITLE'),
            (new Types\ConditionType('SHOW_NEXT_BUTTON'))->configureDefaultValue(array('VALUE'=>'always')),
            (new Types\ConditionType('SHOW_PAGE'))->configureDefaultValue(array('VALUE'=>'always')),
        ));
    }

}
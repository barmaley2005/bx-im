<?php

namespace DevBx\Forms\WebForm;

use Bitrix\Main\Error;
use Bitrix\Main\Result;

/**
* @property string $title
 * @property string $titleHidden
 * @property string $description
 * @property string $code
 * @property string $progressBar
 * @property boolean $showPageTitles
*/
class WOSettings extends WOBase {

    const PROGRESS_BAR_STEPS = 'STEPS';
    const PROGRESS_BAR_BAR = 'BAR';
    const PROGRESS_BAR_NONE = 'NONE';

    public function __construct()
    {
        parent::__construct(array(
            'TITLE' => '',
            'TITLE_HIDDEN' => false,
            'DESCRIPTION' => '',
            'CODE' => '',
            'PROGRESS_BAR' => 'STEPS',
            'SHOW_PAGE_TITLES' => true,
        ));
    }

    public static function getProgressBarEnum()
    {
        return array(static::PROGRESS_BAR_STEPS, static::PROGRESS_BAR_BAR, static::PROGRESS_BAR_NONE);
    }

    protected function sysSetValue($name, $value): Result
    {
        switch ($name)
        {
            case 'PROGRESS_BAR':
                if (!in_array($value, static::getProgressBarEnum()))
                    return (new Result())->addError(new Error('Invalid field PROGRESS_BAR value '.$value));
                break;
        }
        return parent::sysSetValue($name, $value);
    }


}
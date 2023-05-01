<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

global $APPLICATION;

/* @var string $FORM_ID */
/* @var array $SETTINGS */

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

Loader::includeModule("devbx.core");
Loader::includeModule("devbx.forms");

$FORM_ID = intval($FORM_ID);

$entity = \DevBx\Forms\FormManager::getInstance()->compileFormEntity($FORM_ID);
if (!$entity)
    return;

if ($APPLICATION->GetGroupRight("devbx.core") < 'R')
{
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
    return;
}

\DevBx\Forms\Iblock\Settings::showIblockSettings($entity, $SETTINGS);

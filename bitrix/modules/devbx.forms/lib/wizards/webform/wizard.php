<?php

namespace DevBx\Forms\Wizards\WebForm;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use DevBx\Forms\FormManager;
use DevBx\Forms\FormTable;
use DevBx\Forms\FormTypes\WebFormType;
use DevBx\Forms\Internals\BaseWizard;
use DevBx\Forms\WebForm\Fields;
use DevBx\Forms\WebForm\DataFields;
use DevBx\Forms\WebForm\WOForm;
use DevBx\Forms\WebForm\WOFormUserField;

Loc::getMessage(__FILE__);

class Wizard extends BaseWizard {
    private static $instance;

    protected $fieldManager = false;

    public static function getTemplateId()
    {
        return WebFormType::getType();
    }

    public static function adminMenu(&$aMenu)
    {
        $arItems = array();

        $arItems[] = array(
            "text" => Loc::getMessage('DEVBX_FORMS_WIZARD_MENU_NEW_WEB_FORM'),
            'url' => 'devbx_webform_edit.php?lang=' . LANGUAGE_ID ,
        );

        /*array_unshift($aMenu, [
            "text" => Loc::getMessage('DEVBX_FORMS_WIZARD_MENU_WEB_FORM_LIST'),
            "page_icon" => "default_page_icon",
            "items_id" => "menu_devbx_form_webform_wizard",
            "items" => $arItems,
        ]);*/
    }

    public static function registerWizard(Main\Event $event)
    {
        $event->getParameter('manager')->registerWizard(static::class);
    }

    public static function registerStandardFields(Main\Event $event)
    {
        /* @var FieldManager $manger */

        $manager = $event->getParameter('manager');

        $manager->addWebFormGroup('input', Loc::getMessage('DEVBX_WEB_FORM_ELEMENTS_GROUP_INPUT'), 100);
        $manager->addWebFormGroup('data', Loc::getMessage('DEVBX_WEB_FORM_ELEMENTS_GROUP_DATA'), 200);
        $manager->addWebFormGroup('layout', Loc::getMessage('DEVBX_WEB_FORM_ELEMENTS_GROUP_LAYOUT'), 300);

        $manager->addWebFormField(Fields\TextField::class);
        $manager->addWebFormField(Fields\ChoiceField::class);
        $manager->addWebFormField(Fields\DateField::class);
        $manager->addWebFormField(Fields\BooleanField::class);
        $manager->addWebFormField(Fields\NumberField::class);
        $manager->addWebFormField(Fields\EmailField::class);
        $manager->addWebFormField(Fields\FileUploadField::class);

        $manager->addWebFormField(DataFields\IblockSectionField::class);

        $manager->addWebFormField(Fields\SectionField::class);
        $manager->addWebFormField(Fields\ContentField::class);
    }

    public static function registerActions(Main\Event $event)
    {
        /* @var FieldManager $manger */

        $manager = $event->getParameter('manager');

    }

    public static function getInstance(): Wizard
    {
        if (!isset(self::$instance))
        {
            self::$instance = new Wizard();
        }

        return self::$instance;
    }

    public function getFieldManager()
    {
        if (!$this->fieldManager)
        {
            $this->fieldManager = new FieldManager();
        }

        return $this->fieldManager;
    }

    public function getLangMessages()
    {
        return Loc::loadLanguageFile(__FILE__);
    }
}
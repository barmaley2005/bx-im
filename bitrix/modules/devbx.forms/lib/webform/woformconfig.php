<?php
namespace DevBx\Forms\WebForm;

use Bitrix\Main\Localization\Loc;
use DevBx\Forms\EO_Form;
use DevBx\Forms\FormTable;

/**
 * @property string name
 * @property array viewGroups
 * @property array writeGroups
 */

class WOFormConfig extends WOBase {
    public function __construct()
    {
        parent::__construct(array(
            (new Types\StringType('NAME'))->configureDefaultValue(Loc::getMessage('DEVBX_WEB_FORM_DEFAULT_FORM_NAME')),
            (new Types\ArrayType('VIEW_GROUPS'))->configureDefaultValue(array(2)),
            (new Types\ArrayType('WRITE_GROUPS'))->configureDefaultValue(array(2)),
        ));
    }

    public function loadConfig(EO_Form $obForm)
    {
        if ($obForm->getId()<=0)
            return;

        $obForm->fillLangName();

        if ($obForm->getLangName())
        {
            $this->name = $obForm->getLangName()->getName();
        } else {
            $this->name = '';
        }

        $this->viewGroups = $obForm->getViewGroups();
        $this->writeGroups = $obForm->getWriteGroups();
    }
}
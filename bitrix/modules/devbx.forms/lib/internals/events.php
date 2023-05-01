<?php

namespace DevBx\Forms\Internals;

use DevBx\Forms\FormManager;
use DevBx\Forms\FormTable;
use DevBx\Forms\WizardManager;

class Events {

    public static function onVirtualClassBuildList()
    {
        WizardManager::getInstance()->getWizard();

        $arFormType = FormManager::getInstance()->getFormType();

        $iterator = FormTable::getList();
        while ($obForm = $iterator->fetchObject())
        {
            if (array_key_exists($obForm->getFormType(), $arFormType))
            {
                $entity = \DevBx\Forms\FormManager::getInstance()->compileFormEntity($obForm);
            }
        }

    }

}
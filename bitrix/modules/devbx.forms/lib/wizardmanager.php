<?php

namespace DevBx\Forms;

use Bitrix\Main;
use DevBx\Forms\Internals\BaseWizard;

class WizardManager
{
    private static $instance;

    protected $arWizard = false;

    public static function getInstance(): WizardManager
    {
        if (!isset(self::$instance))
        {
            self::$instance = new WizardManager();
        }

        return self::$instance;
    }

    function registerWizard($wizard)
    {
        if (!is_subclass_of($wizard, BaseWizard::class))
            throw new Main\SystemException('invalid registerWizard class '.$wizard);

        $this->arWizard[$wizard::getTemplateId()] = $wizard;
    }

    /**
     * @return BaseFormType[]|false|BaseFormType
     */
    function getWizard($wizardId = false)
    {
        if (!is_array($this->arWizard))
        {
            $this->arWizard = array();

            $event = new Main\Event('devbx.forms', 'OnWizardBuildList', array('manager'=>$this));
            $event->send();
        }

        if($wizardId !== false)
        {
            if(array_key_exists($wizardId, $this->arWizard))
                return $this->arWizard[$wizardId];
            else
                return false;
        }
        else
            return $this->arWizard;
    }

}

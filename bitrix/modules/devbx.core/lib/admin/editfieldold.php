<?php

namespace DevBx\Core\Admin;

use Bitrix\Main\Entity;

class EditFieldOld extends EditField
{
    private $settings;

    public function __construct($id, $settings)
    {
        parent::__construct($id);

        $this->settings = $settings;
    }

    public function showField(AdminEdit $edit, $arValues)
    {
        if ($edit->getDataClass()::getEntity()->hasField($this->id))
            $field = $edit->getDataClass()::getEntity()->getField($this->id);
        else
            $field = null;

        if (isset($this->settings["showFieldOld"])) {
            call_user_func_array($this->settings["showFieldOld"], array($edit->getTabControl(), $field, $arValues[$this->id], $arValues));
        } else {
            if (isset($this->settings["showField"]))
            {
                call_user_func_array($this->settings["showField"], array($edit, $this->id, $field, $arValues));
            } else
            {
                parent::showField($edit, $arValues);
            }
        }

    }

    public function saveField(AdminEdit $edit, &$arLoadFields, $primary)
    {
        if (isset($this->settings['saveFieldValue']))
        {
            if ($edit->getDataClass()::getEntity()->hasField($this->id))
                $field = $edit->getDataClass()::getEntity()->getField($this->id);
            else
                $field = null;

            call_user_func_array($this->settings["saveFieldValue"], array($edit, &$arLoadFields, $primary, $this->id, $field));
        } else
        {
            parent::saveField($edit, $arLoadFields, $primary);
        }
    }

    public function getValue(AdminEdit $edit, $bVarsFromForm, &$arValues, $primary)
    {
        if (isset($this->settings['getValue']))
        {
            if ($edit->getDataClass()::getEntity()->hasField($this->id))
                $field = $edit->getDataClass()::getEntity()->getField($this->id);
            else
                $field = null;

            call_user_func_array($this->settings["getValue"], array($edit, $bVarsFromForm, $primary, $this->id, $field, &$arValues));
        } else
        {
            parent::getValue($edit, $bVarsFromForm, $arValues, $primary);
        }
    }
}
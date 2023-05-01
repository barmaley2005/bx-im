<?php

namespace DevBx\Core\Admin;

use Bitrix\Main\Entity;

class EditUserTypeField extends EditField
{
    private $arField;

    public function __construct($id, $arField)
    {
        parent::__construct($id);

        $this->arField = $arField;
    }

    public function showField(AdminEdit $edit, $arValues)
    {
        global $USER_FIELD_MANAGER;

        $arUserField = $this->arField;
        $arUserField['ENTITY_VALUE_ID'] = 1;
        $arUserField['VALUE'] = $arValues[$this->id];

        $edit->getTabControl()->BeginCustomField($this->id, $this->arField['EDIT_FORM_LABEL']);

        echo $USER_FIELD_MANAGER->GetEditFormHTML(false, $arValues[$this->id], $arUserField);
        $edit->getTabControl()->EndCustomField($this->id);
    }

    public function saveField(AdminEdit $edit, &$arLoadFields, $primary)
    {
        $arLoadFields[$this->id] = $_POST[$this->id];
    }

    public function getValue(AdminEdit $edit, $bVarsFromForm, &$arValues, $primary)
    {
        if ($bVarsFromForm)
        {
            $arValues[$this->id] = $_REQUEST[$this->id];
        } else {
            if ($edit->isNewForm())
            {
                $arValues[$this->id] = $this->arField['SETTINGS']['DEFAULT_VALUE'];
            }
        }
    }
}
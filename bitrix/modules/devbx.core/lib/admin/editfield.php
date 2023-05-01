<?php

namespace DevBx\Core\Admin;

use Bitrix\Main\Entity;
use Bitrix\Main\Type;

abstract class EditField
{

    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function showField(AdminEdit $edit, $arValues)
    {
        if (!$edit->getDataClass()::getEntity()->hasField($this->id)) {
            $edit->getTabControl()->AddViewField($this->id, 'null', 'null field ' . $this->id);
            return;
        }

        $obField = $edit->getDataClass()::getEntity()->getField($this->id);

        if ($obField instanceof Entity\BooleanField) {
            $values = $obField->getValues();
            $edit->getTabControl()->AddCheckBoxField($this->id, $obField->getTitle(),
                $obField->isRequired(), array_reverse($values), $values[1] == $arValues[$this->id]);
        } elseif ($obField instanceof Entity\TextField) {
            $edit->getTabControl()->AddTextField($this->id, $obField->getTitle(),
                htmlspecialcharsbx($arValues[$this->id]), array(), $obField->isRequired());
        } elseif ($obField instanceof Entity\StringField) {
            $edit->getTabControl()->AddEditField($this->id, $obField->getTitle(),
                $obField->isRequired(), array(), htmlspecialcharsbx($arValues[$this->id]));
        } elseif ($obField instanceof Entity\IntegerField ||
            $obField instanceof Entity\FloatField) {

            if ($obField->isAutocomplete())
            {
                $edit->getTabControl()->AddViewField($this->id, $obField->getTitle(), htmlspecialcharsbx($arValues[$this->id]), $obField->isAutocomplete());
            } else
            {
                $edit->getTabControl()->AddEditField($this->id, $obField->getTitle(),
                    $obField->isRequired(), array(), htmlspecialcharsbx($arValues[$this->id]));
            }

        } elseif ($obField instanceof Entity\DateField || $obField instanceof Entity\DatetimeField) {
            $edit->getTabControl()->AddCalendarField($this->id, $obField->getTitle(), $arValues[$this->id], $obField->isRequired());
        } elseif ($obField instanceof Entity\EnumField) {
            $values = $obField->getValues();

            if (!empty($values) && is_numeric(key($values)))
            {
                $values = array_combine($values, $values);
            }

            $edit->getTabControl()->AddDropDownField($this->id, $obField->getTitle(), $obField->isRequired(),
                $values, $arValues[$this->id]);
        } else {
            $edit->getTabControl()->AddViewField($this->id, $obField ? $obField->getTitle() : 'null', 'unknown field type ' . $this->id);
        }
    }

    public function saveField(AdminEdit $edit, &$arLoadFields, $primary)
    {
        if (!$edit->getDataClass()::getEntity()->hasField($this->id))
            return;

        $obField = $edit->getDataClass()::getEntity()->getField($this->id);

        if (isset($_POST[$this->id])) {

            if ($obField instanceof Entity\DateField || $obField instanceof Entity\DatetimeField) {
                if (!empty($_POST[$this->id])) {
                    $arLoadFields[$this->id] = new Type\DateTime($_POST[$this->id]);
                } else {
                    $arLoadFields[$this->id] = false;
                }
            } else
                $arLoadFields[$this->id] = $_POST[$this->id];
        } elseif (isset($_FILES[$this->id])) {
            $arLoadFields[$this->id] = $_FILES[$this->id];

            if (!empty($primary) && $_POST[$this->id . '_del'] == 'Y') {
                if ($arRow = call_user_func(array($edit->getDataClass(), 'getRowById'), $primary)) {
                    $arLoadFields[$this->id]['del'] = 'Y';
                    $arLoadFields[$this->id]['old_file'] = $arRow[$this->id];
                }
            }
        }
    }

    public function getValue(AdminEdit $edit, $bVarsFromForm, &$arValues, $primary)
    {
        if (!$edit->getDataClass()::getEntity()->hasField($this->id))
            return;

        $obField = $edit->getDataClass()::getEntity()->getField($this->id);

        if (!$obField instanceof Entity\ScalarField)
            return;

        if ($bVarsFromForm && isset($_REQUEST[$this->id])) {
            $arValues[$this->id] = $_REQUEST[$this->id];
            /*
            if (is_array($_REQUEST[$key])) {
                $arValues[$key] = array();
                foreach ($_REQUEST[$key] as $rkey => $rval) {
                    $arValues[$key][$rkey] = htmlspecialcharsbx($rval);
                }
            } else
                $arValues[$key] = htmlspecialcharsbx($_REQUEST[$key]);
            */
        } else {
            if (!isset($arValues[$this->id]))
                $arValues[$this->id] = $obField->getDefaultValue();
        }

    }
}
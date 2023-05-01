<?php

namespace DevBx\Core\Admin;

class ListUserType {

    protected $arUserField;

    function __construct($arUserField)
    {
        $this->arUserField = $arUserField;
    }

    public function showFilter(AdminList $list, $id, $arFilterValues)
    {
        global $USER_FIELD_MANAGER;

        echo $USER_FIELD_MANAGER->GetFilterHTML($this->arUserField, 'find_' . $id, $arFilterValues[$id]);
    }

    public function rowView(\CAdminListRow $row, $primary, $key, $arRes)
    {
        global $USER_FIELD_MANAGER;

        $row->AddViewField($key, $USER_FIELD_MANAGER->getListView($this->arUserField, $arRes[$key]));
    }

    public function editField(\CAdminListRow $row, $primary, $key, $arRes)
    {
        $arField = $this->arUserField;
        $arField['VALUE'] = $arRes[$key];

        if (is_callable(array($arField['USER_TYPE']['CLASS_NAME'],'getAdminListEditHTML')))
        {
            $params = array(
                'NAME'=>'FIELDS['.$primary.']['.$key.']',
                'VALUE'=>$arRes[$key]
            );
            $row->AddEditField($key, call_user_func_array(array($arField['USER_TYPE']['CLASS_NAME'],'getAdminListEditHTML'), array($arField,$params)));
        }
    }
}
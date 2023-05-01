<?php

namespace DevBx\Forms\Iblock;

class Events {

    public static function OnAdminIBlockElementEditHandler()
    {
        return array(
            'TABSET' => 'devbx_forms_fields',
            'Check' => array('DevBx\Forms\Iblock\ElementTabEngine', 'checkFields'),
            'Action' => array('DevBx\Forms\Iblock\ElementTabEngine', 'saveData'),
            'GetTabs' => array('DevBx\Forms\Iblock\ElementTabEngine', 'getTabs'),
            'ShowTab' => array('DevBx\Forms\Iblock\ElementTabEngine', 'showTab'),
        );
    }

    public static function OnAdminIBlockSectionEditHandler()
    {
        return array(
            'TABSET' => 'devbx_forms_fields',
            'Check' => array('DevBx\Forms\Iblock\SectionTabEngine', 'checkFields'),
            'Action' => array('DevBx\Forms\Iblock\SectionTabEngine', 'saveData'),
            'GetTabs' => array('DevBx\Forms\Iblock\SectionTabEngine', 'getTabs'),
            'ShowTab' => array('DevBx\Forms\Iblock\SectionTabEngine', 'showTab'),
        );
    }
}
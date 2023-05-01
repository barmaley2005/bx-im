<?php

namespace DevBx\Core\IblockProperty;

use Bitrix\Main\Loader;
use Bitrix\Main\SiteTable;
use Bitrix\Main\Localization\Loc;

if (!Loader::includeModule('iblock'))
    return;

Loc::loadMessages(__FILE__);

class PropertySite {
    public static function GetUserTypeDescription() {
        return array(
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'DEVBX_PROPERTY_SITE',
            'DESCRIPTION' => Loc::getMessage('DEVBX_IBLOCK_PROPERTY_SITE'),
            "GetPublicViewHTML" => array(__CLASS__, "GetPublicViewHTML"),
            "GetPublicEditHTML" => array(__CLASS__, "GetPublicEditHTML"),
            "GetAdminListViewHTML" => array(__CLASS__, "GetAdminListViewHTML"),
            "GetPropertyFieldHtml" => array(__CLASS__, "GetPropertyFieldHtml"),
            "GetPropertyFieldHtmlMulty" => array(__CLASS__,'GetPropertyFieldHtmlMulty'),
            );
    }

    static function PrepareSettings($arProperty)
    {
        return array();
    }

    public static function getSiteById($id)
    {
        static $cache = [];

        if (isset($cache[$id]))
            return $cache[$id];

        $row = SiteTable::getRowById($id);
        if ($row)
        {
            $cache[$id] = $row;
        }

        return $row;
    }

    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        if ($value["VALUE"] <> '')
        {
            $ar = static::getSiteById($value['VALUE']);

            return $ar ? htmlspecialcharsbx($ar['NAME']) : '';
        }

        return '';
    }

    public static function GetPublicEditHTML($arProperty, $value, $strHTMLControlName)
    {
        $result = [];

        $dbRes = SiteTable::getList();
        while ($arRes = $dbRes->fetch())
        {
            $result['REFERENCE'][] = $arRes['NAME'];
            $result['REFERENCE_ID'][] = $arRes['LID'];
        }

        return SelectBoxFromArray($strHTMLControlName["VALUE"], $result, $value['VALUE'], Loc::getMessage('DEVBX_IBLOCK_PROPERTY_SITE_NOT_SELECTED'));
    }

    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        if ($value["VALUE"] <> '')
        {
            $ar = static::getSiteById($value['VALUE']);

            return $ar ? '['.$ar['LID'].'] '.htmlspecialcharsbx($ar['NAME']) : '';
        }

        return '';
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        $result = [];

        $dbRes = SiteTable::getList();
        while ($arRes = $dbRes->fetch())
        {
            $result['REFERENCE'][] = '['.$arRes['LID'].'] '.$arRes['NAME'];
            $result['REFERENCE_ID'][] = $arRes['LID'];
        }

        return SelectBoxFromArray($strHTMLControlName["VALUE"], $result, $value['VALUE'], Loc::getMessage('DEVBX_IBLOCK_PROPERTY_SITE_NOT_SELECTED'));
   }

    public static function GetPropertyFieldHtmlMulty($arProperty, $value, $strHTMLControlName)
    {
        $result = [];

        $dbRes = SiteTable::getList();
        while ($arRes = $dbRes->fetch())
        {
            $result['REFERENCE'][] = '['.$arRes['LID'].'] '.$arRes['NAME'];
            $result['REFERENCE_ID'][] = $arRes['LID'];
        }

        return SelectBoxMFromArray($strHTMLControlName["VALUE"], $result, $value['VALUE'], Loc::getMessage('DEVBX_IBLOCK_PROPERTY_SITE_NOT_SELECTED'));
    }

}
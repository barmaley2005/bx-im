<?php

namespace DevBx\Core\IblockProperty;

use Bitrix\Main\Loader;
use Bitrix\Main\SiteTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location\LocationTable;

Loader::includeModule('sale');

Loc::loadMessages(__FILE__);

class PropertyLocation {
    public static function GetUserTypeDescription() {
        return array(
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'DEVBX_PROPERTY_LOCATION',
            'DESCRIPTION' => Loc::getMessage('DEVBX_IBLOCK_PROPERTY_LOCATION'),
            "GetPublicViewHTML" => array(__CLASS__, "GetPublicViewHTML"),
            "GetPublicEditHTML" => array(__CLASS__, "GetPublicEditHTML"),
            "GetAdminListViewHTML" => array(__CLASS__, "GetAdminListViewHTML"),
            "GetPropertyFieldHtml" => array(__CLASS__, "GetPropertyFieldHtml"),
            //"GetPropertyFieldHtmlMulty" => array(__CLASS__,'GetPropertyFieldHtmlMulty'),
        );
    }

    public static function isSupported()
    {
        return Loader::includeModule('sale');
    }

    static function PrepareSettings($arProperty)
    {
        return array();
    }

    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        if (!Loader::includeModule('sale'))
            return $value["VALUE"];

        if (empty($value["VALUE"]))
            return '&nbsp;';

        $path = [];

        $dbPath = LocationTable::getPathToNodeByCode(
            $value["VALUE"],
            ['select'=>['ID','CODE','DISPLAY_NAME'=>'NAME.NAME'],'filter'=>['=NAME.LANGUAGE_ID'=>LANGUAGE_ID]]
        );

        while ($arPath = $dbPath->fetch())
        {
            $path[] = $arPath['DISPLAY_NAME'];
        }

        if (count($path))
            return htmlspecialchars(implode(', ',$path));

        return htmlspecialcharsbx($value["VALUE"]);
    }

    public static function GetPublicEditHTML($arProperty, $value, $strHTMLControlName)
    {
        global $APPLICATION;

        return $APPLICATION->IncludeComponent("bitrix:sale.location.selector.search", "", array(
            "ID" => "",
            "CODE" => $strHTMLControlName["VALUE"],
            "INPUT_NAME" => $value["VALUE"],
            "PROVIDE_LINK_BY" => "code",
            "SHOW_ADMIN_CONTROLS" => 'Y',
            "SELECT_WHEN_SINGLE" => 'N',
            "FILTER_BY_SITE" => 'N',
            "FILTER_SITE_ID" => '',
            "SHOW_DEFAULT_LOCATIONS" => 'N',
            "SEARCH_BY_PRIMARY" => 'Y',
            "UI_FILTER" => true,

            "INITIALIZE_BY_GLOBAL_EVENT" => 'onAdminFilterInited',
            "GLOBAL_EVENT_SCOPE" => 'window'
        ),
            false
        );
    }

    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return self::GetPublicViewHTML($arProperty, $value, $strHTMLControlName);
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        global $APPLICATION;

        ob_start();

        $APPLICATION->IncludeComponent("bitrix:sale.location.selector.search", "", array(
            "ID" => "",
            "CODE" => $value["VALUE"],
            "INPUT_NAME" => $strHTMLControlName["VALUE"],
            "PROVIDE_LINK_BY" => "code",
            "SHOW_ADMIN_CONTROLS" => 'Y',
            "SELECT_WHEN_SINGLE" => 'N',
            "FILTER_BY_SITE" => 'N',
            "FILTER_SITE_ID" => '',
            "SHOW_DEFAULT_LOCATIONS" => 'N',
            "SEARCH_BY_PRIMARY" => 'Y',

            //"INITIALIZE_BY_GLOBAL_EVENT" => 'onAjaxSuccess', // this allows js logic to be initialized after admin filter
            //"GLOBAL_EVENT_SCOPE" => 'window'
        ),
            false
        );

        return ob_get_clean();
    }
}
<?php

namespace DevBx\Core\UserType;

use Bitrix\Catalog;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loader::includeModule('catalog');

Loc::loadMessages(__FILE__);

class CatalogPrice extends BaseType
{

    const USER_TYPE_ID = "devbx_catalog_price";

    public static function GetUserTypeDescription()
    {
        return array(
            "USER_TYPE_ID" => self::USER_TYPE_ID,
            "CLASS_NAME" => __CLASS__,
            "DESCRIPTION" => Loc::getMessage('DEVBX_USER_TYPE_CATALOG_PRICE_DESCRIPTION'),
            "BASE_TYPE" => 'int',
            "EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'),
            "VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
        );
    }

    public static function isSupported()
    {
        return Loader::includeModule('catalog');
    }

    public static function GetDBColumnType($arUserField)
    {
        return 'int(18)';
    }

    public static function CheckFields($arUserField, $value)
    {
        $aMsg = array();
        return $aMsg;
    }

    public static function PrepareSettings($arUserField)
    {
        return array(
            "DEFAULT_VALUE" => $arUserField["SETTINGS"]["DEFAULT_VALUE"],
        );
    }

    public static function GetSettingsHTML($arUserField, $arHtmlControl, $bVarsFromForm)
    {
        global $APPLICATION;

        if($bVarsFromForm)
            $value = $GLOBALS[$arHtmlControl["NAME"]]["DEFAULT_VALUE"];
        elseif(is_array($arUserField))
            $value = $arUserField["SETTINGS"]["DEFAULT_VALUE"];
        else
            $value = "";

        $result = [];

        $dbRes = Catalog\GroupTable::getList();
        while ($arRes = $dbRes->fetch())
        {
            $result['REFERENCE'][] = '['.$arRes['LD'].'] '.$arRes['NAME'];
            $result['REFERENCE_ID'][] = $arRes['ID'];
        }

        ob_start();
        ?>
        <tr>
            <td>
                <?=Loc::getMessage('DEVBX_USER_TYPE_CATALOG_PRICE_DEFAULT_VALUE_TITLE')?>
            </td>
            <td>
                <?
                echo SelectBoxFromArray($arHtmlControl["NAME"].'[DEFAULT_VALUE]', $result, $value, Loc::getMessage('DEVBX_USER_TYPE_CATALOG_PRICE_NOT_SELECTED'))
                ?>
            </td>
        </tr>
        <?

        return ob_get_clean();
    }

    public static function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        if($arUserField["ENTITY_VALUE_ID"]<1 && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
            $arHtmlControl["VALUE"] = $arUserField["SETTINGS"]["DEFAULT_VALUE"];

        $result = [];

        $dbRes = Catalog\GroupTable::getList();
        while ($arRes = $dbRes->fetch())
        {
            $result['REFERENCE'][] = '['.$arRes['ID'].'] '.$arRes['NAME'];
            $result['REFERENCE_ID'][] = $arRes['ID'];
        }

        return SelectBoxFromArray($arHtmlControl["NAME"], $result, $arHtmlControl["VALUE"], Loc::getMessage('DEVBX_USER_TYPE_CATALOG_PRICE_NOT_SELECTED'));
    }

    public static function GetFilterHTML($arUserField, $arHtmlControl)
    {
        $result = [];

        $dbRes = Catalog\GroupTable::getList();
        while ($arRes = $dbRes->fetch())
        {
            $result['REFERENCE'][] = '['.$arRes['ID'].'] '.$arRes['NAME'];
            $result['REFERENCE_ID'][] = $arRes['ID'];
        }

        return SelectBoxFromArray($arHtmlControl["NAME"], $result, $arHtmlControl["VALUE"], Loc::getMessage('DEVBX_USER_TYPE_CATALOG_PRICE_NOT_SELECTED'));
    }

    public static function GetFilterData($arUserField, $arHtmlControl)
    {
        return array(
            "id" => $arHtmlControl["ID"],
            "name" => $arHtmlControl["NAME"],
            "filterable" => ""
        );
    }

    public static function getCatalogGroupById($id)
    {
        static $cache = [];

        if (isset($cache[$id]))
            return $cache[$id];

        $row = Catalog\GroupTable::getRowById($id);
        if ($row)
        {
            $cache[$id] = $row;
        }

        return $row;
    }

    public static function GetAdminListViewHTML($arUserField, $arHtmlControl)
    {
        if ($arHtmlControl["VALUE"] <> '')
        {
            $ar = static::getCatalogGroupById($arHtmlControl['VALUE']);

            return $ar ? '['.$ar['ID'].'] '.htmlspecialcharsbx($ar['NAME']) : '';
        }

        return '&nbsp;';
    }

    public static function GetAdminListEditHTML($arUserField, $arHtmlControl)
    {
        if($arUserField["ENTITY_VALUE_ID"]<1 && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
            $arHtmlControl["VALUE"] = $arUserField["SETTINGS"]["DEFAULT_VALUE"];

        $result = [];

        $dbRes = Catalog\GroupTable::getList();
        while ($arRes = $dbRes->fetch())
        {
            $result['REFERENCE'][] = '['.$arRes['ID'].'] '.$arRes['NAME'];
            $result['REFERENCE_ID'][] = $arRes['ID'];
        }

        return SelectBoxFromArray($arHtmlControl["NAME"], $result, $arHtmlControl["VALUE"], Loc::getMessage('DEVBX_USER_TYPE_CATALOG_PRICE_NOT_SELECTED'));
    }

    public static function GetAdminListEditHTMLMulty($arUserField, $arHtmlControl)
    {
        if($arUserField["ENTITY_VALUE_ID"]<1 && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
            $arHtmlControl["VALUE"] = $arUserField["SETTINGS"]["DEFAULT_VALUE"];

        if (!is_array($arHtmlControl["VALUE"]))
            $arHtmlControl["VALUE"] = array($arHtmlControl["VALUE"]);

        $result = [];

        $dbRes = Catalog\GroupTable::getList();
        while ($arRes = $dbRes->fetch())
        {
            $result['REFERENCE'][] = '['.$arRes['ID'].'] '.$arRes['NAME'];
            $result['REFERENCE_ID'][] = $arRes['ID'];
        }

        return SelectBoxMFromArray($arHtmlControl["NAME"], $result, $arHtmlControl["VALUE"], Loc::getMessage('DEVBX_USER_TYPE_CATALOG_PRICE_NOT_SELECTED'));
    }


    public static function GetPublicView($arUserField, $arAdditionalParameters = array())
    {
        $ar = static::getCatalogGroupById($arUserField["VALUE"]);
        if ($ar)
            return htmlspecialcharsbx($ar['NAME']);

        return $arUserField["VALUE"];
    }

    public static function GetPublicEdit($arUserField, $arAdditionalParameters = array())
    {
        $fieldName = static::getFieldName($arUserField, $arAdditionalParameters);
        $value = static::getFieldValue($arUserField, $arAdditionalParameters);

        if (!is_array($value))
            $value = array($value);

        $result = [];

        $dbRes = Catalog\GroupTable::getList();
        while ($arRes = $dbRes->fetch())
        {
            $result['REFERENCE'][] = '['.$arRes['ID'].'] '.$arRes['NAME'];
            $result['REFERENCE_ID'][] = $arRes['ID'];
        }

        if ($arUserField["MULTIPLE"] == "Y")
        {
            return SelectBoxMFromArray($fieldName, $result, $value, Loc::getMessage('DEVBX_USER_TYPE_CATALOG_PRICE_NOT_SELECTED'));
        } else
        {
            return SelectBoxFromArray($fieldName, $result, reset($value), Loc::getMessage('DEVBX_USER_TYPE_CATALOG_PRICE_NOT_SELECTED'));
        }
    }

    public static function OnSearchIndex($arUserField)
    {
        $value = $arUserField["VALUE"];
        if (!is_array($value))
        {
            $value = array($value);
        }

        $res = [];

        foreach ($value as $singleValue)
        {
            $arUserField['VALUE'] = $singleValue;
            $singleValue = self::GetPublicView($arUserField);
            if ($singleValue)
                $res[] = $singleValue;
        }

        return implode(', ', $res);
    }

}
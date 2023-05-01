<?php

namespace DevBx\Core\UserType;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SiteTable;

Loc::loadMessages(__FILE__);

class Site extends BaseType
{

    const USER_TYPE_ID = "devbx_site";

    public static function GetUserTypeDescription()
    {
        return array(
            "USER_TYPE_ID" => self::USER_TYPE_ID,
            "CLASS_NAME" => __CLASS__,
            "DESCRIPTION" => Loc::getMessage('DEVBX_USER_TYPE_SITE_DESCRIPTION'),
            "BASE_TYPE" => 'string',
            "EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'),
            "VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
        );
    }

    public static function GetDBColumnType($arUserField)
    {
        return "varchar(255)";
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

        $dbRes = SiteTable::getList();
        while ($arRes = $dbRes->fetch())
        {
            $result['REFERENCE'][] = '['.$arRes['LID'].'] '.$arRes['NAME'];
            $result['REFERENCE_ID'][] = $arRes['LID'];
        }

        ob_start();
        ?>
        <tr>
            <td>
                <?=Loc::getMessage('DEVBX_USER_TYPE_SITE_DEFAULT_VALUE_TITLE')?>
            </td>
            <td>
                <?
                echo SelectBoxFromArray($arHtmlControl["NAME"].'[DEFAULT_VALUE]', $result, $value, Loc::getMessage('DEVBX_USER_TYPE_SITE_NOT_SELECTED'))
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

        $dbRes = SiteTable::getList();
        while ($arRes = $dbRes->fetch())
        {
            $result['REFERENCE'][] = '['.$arRes['LID'].'] '.$arRes['NAME'];
            $result['REFERENCE_ID'][] = $arRes['LID'];
        }

        return SelectBoxFromArray($arHtmlControl["NAME"], $result, $arHtmlControl["VALUE"], Loc::getMessage('DEVBX_USER_TYPE_SITE_NOT_SELECTED'));
    }

    public static function GetFilterHTML($arUserField, $arHtmlControl)
    {
        $result = [];

        $dbRes = SiteTable::getList();
        while ($arRes = $dbRes->fetch())
        {
            $result['REFERENCE'][] = '['.$arRes['LID'].'] '.$arRes['NAME'];
            $result['REFERENCE_ID'][] = $arRes['LID'];
        }

        return SelectBoxFromArray($arHtmlControl["NAME"], $result, $arHtmlControl["VALUE"], Loc::getMessage('DEVBX_USER_TYPE_SITE_NOT_SELECTED'));
    }

    public static function GetFilterData($arUserField, $arHtmlControl)
    {
        return array(
            "id" => $arHtmlControl["ID"],
            "name" => $arHtmlControl["NAME"],
            "filterable" => ""
        );
    }

    public static function getSiteById($id)
    {
        static $cache = [];

        if (empty($id))
            return false;

        if (isset($cache[$id]))
            return $cache[$id];

        $row = SiteTable::getRowById($id);
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
            $ar = static::getSiteById($arHtmlControl['VALUE']);

            return $ar ? '['.$ar['LID'].'] '.htmlspecialcharsbx($ar['NAME']) : '';
        }

        return '&nbsp;';
    }

    public static function GetAdminListEditHTML($arUserField, $arHtmlControl)
    {
        if($arUserField["ENTITY_VALUE_ID"]<1 && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
            $arHtmlControl["VALUE"] = $arUserField["SETTINGS"]["DEFAULT_VALUE"];

        $result = [];

        $dbRes = SiteTable::getList();
        while ($arRes = $dbRes->fetch())
        {
            $result['REFERENCE'][] = '['.$arRes['LID'].'] '.$arRes['NAME'];
            $result['REFERENCE_ID'][] = $arRes['LID'];
        }

        return SelectBoxFromArray($arHtmlControl["NAME"], $result, $arHtmlControl["VALUE"], Loc::getMessage('DEVBX_USER_TYPE_SITE_NOT_SELECTED'));
    }

    public static function GetAdminListEditHTMLMulty($arUserField, $arHtmlControl)
    {
        if($arUserField["ENTITY_VALUE_ID"]<1 && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
            $arHtmlControl["VALUE"] = $arUserField["SETTINGS"]["DEFAULT_VALUE"];

        if (!is_array($arHtmlControl["VALUE"]))
            $arHtmlControl["VALUE"] = array($arHtmlControl["VALUE"]);

        $result = [];

        $dbRes = SiteTable::getList();
        while ($arRes = $dbRes->fetch())
        {
            $result['REFERENCE'][] = '['.$arRes['LID'].'] '.$arRes['NAME'];
            $result['REFERENCE_ID'][] = $arRes['LID'];
        }

        return SelectBoxMFromArray($arHtmlControl["NAME"], $result, $arHtmlControl["VALUE"], Loc::getMessage('DEVBX_USER_TYPE_SITE_NOT_SELECTED'));
    }


    public static function GetPublicView($arUserField, $arAdditionalParameters = array())
    {
        $ar = static::getSiteById($arUserField["VALUE"]);
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

        $dbRes = SiteTable::getList();
        while ($arRes = $dbRes->fetch())
        {
            $result['REFERENCE'][] = '['.$arRes['LID'].'] '.$arRes['NAME'];
            $result['REFERENCE_ID'][] = $arRes['LID'];
        }

        if ($arUserField["MULTIPLE"] == "Y")
        {
            return SelectBoxMFromArray($fieldName, $result, reset($value), Loc::getMessage('DEVBX_USER_TYPE_SITE_NOT_SELECTED'));
        } else
        {
            return SelectBoxFromArray($fieldName, $result, reset($value), Loc::getMessage('DEVBX_USER_TYPE_SITE_NOT_SELECTED'));
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
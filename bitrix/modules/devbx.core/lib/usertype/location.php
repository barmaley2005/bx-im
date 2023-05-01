<?php

namespace DevBx\Core\UserType;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UserField\HtmlBuilder;
use Bitrix\Sale\Location\LocationTable;

Loader::includeModule('sale');

Loc::loadMessages(__FILE__);

class Location extends BaseType
{

    const USER_TYPE_ID = "devbx_sale_location";

    public static function GetUserTypeDescription()
    {
        return array(
            "USER_TYPE_ID" => self::USER_TYPE_ID,
            "CLASS_NAME" => __CLASS__,
            "DESCRIPTION" => Loc::getMessage('DEVBX_USER_TYPE_SALE_LOCATION_DESCRIPTION'),
            "BASE_TYPE" => 'string',
            "EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'),
            "VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
        );
    }

    public static function isSupported()
    {
        return Loader::includeModule('sale');
    }

    public static function GetDBColumnType($arUserField)
    {
        return "varchar(255)";
    }

    public static function GetPublicEdit($arUserField, $arAdditionalParameters = array())
    {
        $fieldName = static::getFieldName($arUserField, $arAdditionalParameters);
        $value = static::getFieldValue($arUserField, $arAdditionalParameters);

        if ($arUserField['MULTIPLE'] !== 'Y') {
            if (is_array($value))
                $value = reset($value);

            return self::getSingleEditHtml($arUserField, $fieldName, $value);
        } else {
            return self::getMultipleEditHtml($arUserField, $fieldName, $value);
        }
    }

    public static function getSingleEditHtml($arUserField, $inputName, $value)
    {
        global $APPLICATION;

        ob_start();

        $APPLICATION->IncludeComponent("bitrix:sale.location.selector.search", "", array(
            "ID" => "",
            "CODE" => $value,
            "INPUT_NAME" => $inputName,
            "PROVIDE_LINK_BY" => "code",
            "SHOW_ADMIN_CONTROLS" => 'Y',
            "SELECT_WHEN_SINGLE" => 'N',
            "FILTER_BY_SITE" => 'N',
            "FILTER_SITE_ID" => '',
            "SHOW_DEFAULT_LOCATIONS" => 'N',
            "SEARCH_BY_PRIMARY" => 'Y',

            "INITIALIZE_BY_GLOBAL_EVENT" => defined('ADMIN_SECTION') && ADMIN_SECTION ? 'onAjaxSuccess' : '',
            "GLOBAL_EVENT_SCOPE" => 'window'
        ),
            false
        );

        return ob_get_clean();
    }

    public static function getMultipleEditHtml($arUserField, $inputName, $value)
    {
        global $APPLICATION;

        if (!is_array($value))
            $value = array();

        ob_start();

        $containerId = 'devbx_location_' . $arUserField['ENTITY_ID'] . '_' . $arUserField["ENTITY_VALUE_ID"];

        echo '<div id="' . $containerId . '" style="min-width:300px;">';

        foreach ($value as $singleValue) {
            $APPLICATION->IncludeComponent("bitrix:sale.location.selector.search", "", array(
                "ID" => "",
                "CODE" => $singleValue,
                "INPUT_NAME" => $inputName,
                "PROVIDE_LINK_BY" => "code",
                "SHOW_ADMIN_CONTROLS" => 'Y',
                "SELECT_WHEN_SINGLE" => 'N',
                "FILTER_BY_SITE" => 'N',
                "FILTER_SITE_ID" => '',
                "SHOW_DEFAULT_LOCATIONS" => 'N',
                "SEARCH_BY_PRIMARY" => 'Y',

                "INITIALIZE_BY_GLOBAL_EVENT" => defined('ADMIN_SECTION') && ADMIN_SECTION ? 'onAjaxSuccess' : '',
                "GLOBAL_EVENT_SCOPE" => 'window'
            ),
                false
            );

        }

        $arParams = array(
            "ID" => "",
            "CODE" => "",
            "INPUT_NAME" => $inputName,
            "PROVIDE_LINK_BY" => "code",
            "SHOW_ADMIN_CONTROLS" => 'Y',
            "SELECT_WHEN_SINGLE" => 'N',
            "FILTER_BY_SITE" => 'N',
            "FILTER_SITE_ID" => '',
            "SHOW_DEFAULT_LOCATIONS" => 'N',
            "SEARCH_BY_PRIMARY" => 'Y',
            "ADMIN_MODE" => "Y",
        );

        $signer = new \Bitrix\Main\Security\Sign\Signer;

        $arPostData = array(
            'component' => 'bitrix:sale.location.selector.search',
            'parameters' => $signer->sign(base64_encode(serialize($arParams)), 'sale.location.selector.search'),
            'template' => $signer->sign(base64_encode(''), 'sale.location.selector.search'),
        );

        ?>
        <script>

            console.log('set addNewRow');

            top.addNewRow<?=$containerId?> = function (button) {
                BX.ajax({
                    url: '/bitrix/tools/devbx.core/ajax_component.php',
                    method: 'POST',
                    data: <?=json_encode($arPostData)?>,
                    processData: false,
                    onsuccess: function (result) {
                        let ob = BX.processHTML(result, false),
                            container = document.getElementById('<?=\CUtil::JSEscape($containerId)?>'),
                            i;

                        //container.insertAdjacentHTML('beforeend', '<br>'+ob.HTML);

                        button.insertAdjacentHTML('beforebegin', ob.HTML);

                        for (i = 0; i < ob.SCRIPT.length; i++) {
                            BX.evalGlobal(ob.SCRIPT[i].JS);
                        }
                    }
                });
            }

        </script>

        <input type="button" value="<?= htmlspecialcharsbx(Loc::getMessage('USER_TYPE_PROP_ADD')) ?>"
               onclick="addNewRow<?= $containerId ?>(this);">
        <?

        echo '</div>';

        return ob_get_clean();
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

        if ($bVarsFromForm)
            $value = $GLOBALS[$arHtmlControl["NAME"]]["DEFAULT_VALUE"];
        elseif (is_array($arUserField))
            $value = $arUserField["SETTINGS"]["DEFAULT_VALUE"];
        else
            $value = "";

        ob_start();
        ?>
        <tr>
            <td>
                <?= Loc::getMessage('DEVBX_USER_TYPE_SALE_LOCATION_DEFAULT_VALUE_TITLE') ?>
            </td>
            <td>
                <?
                $APPLICATION->IncludeComponent("bitrix:sale.location.selector.search", "", array(
                    "ID" => "",
                    "CODE" => $value,
                    "INPUT_NAME" => $arHtmlControl["NAME"] . '[DEFAULT_VALUE]',
                    "PROVIDE_LINK_BY" => "code",
                    "SHOW_ADMIN_CONTROLS" => 'Y',
                    "SELECT_WHEN_SINGLE" => 'N',
                    "FILTER_BY_SITE" => 'N',
                    "FILTER_SITE_ID" => '',
                    "SHOW_DEFAULT_LOCATIONS" => 'N',
                    "SEARCH_BY_PRIMARY" => 'Y',

                    /*
                    "INITIALIZE_BY_GLOBAL_EVENT" => 'onAdminFilterInited', // this allows js logic to be initialized after admin filter
                    "GLOBAL_EVENT_SCOPE" => 'window'
                    */
                ),
                    false
                );
                ?>
            </td>
        </tr>
        <?

        return ob_get_clean();
    }

    public static function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        global $APPLICATION;

        if ($arUserField["ENTITY_VALUE_ID"] < 1 && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"]) > 0)
            $arHtmlControl["VALUE"] = htmlspecialcharsbx($arUserField["SETTINGS"]["DEFAULT_VALUE"]);

        if (!Loader::includeModule("sale"))
            return '<input type="text" name="' . $arHtmlControl["NAME"] . '" value="' . $arHtmlControl["VALUE"] . '">';

        ob_start();

        $APPLICATION->IncludeComponent("bitrix:sale.location.selector.search", "", array(
            "ID" => "",
            "CODE" => $arHtmlControl["VALUE"],
            "INPUT_NAME" => $arHtmlControl["NAME"],
            "PROVIDE_LINK_BY" => "code",
            "SHOW_ADMIN_CONTROLS" => 'Y',
            "SELECT_WHEN_SINGLE" => 'N',
            "FILTER_BY_SITE" => 'N',
            "FILTER_SITE_ID" => '',
            "SHOW_DEFAULT_LOCATIONS" => 'N',
            "SEARCH_BY_PRIMARY" => 'Y',

            /*
            "INITIALIZE_BY_GLOBAL_EVENT" => 'onAdminFilterInited', // this allows js logic to be initialized after admin filter
            "GLOBAL_EVENT_SCOPE" => 'window'
            */
        ),
            false
        );

        return ob_get_clean();
    }

    public static function GetEditFormHTMLMulty($arUserField, $arHtmlControl)
    {
        global $APPLICATION;

        if (($arUserField["ENTITY_VALUE_ID"] < 1) && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"]) > 0)
            $arHtmlControl["VALUE"] = array($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
        elseif (!is_array($arHtmlControl["VALUE"]))
            $arHtmlControl["VALUE"] = array();

        if (empty($arHtmlControl["VALUE"]))
            $arHtmlControl["VALUE"][] = '';

        if (!Loader::includeModule("sale")) {


            $html = '';

            foreach ($arHtmlControl["VALUE"] as $singleValue) {
                $attrList = array();

                $attrList['name'] = $arHtmlControl["NAME"];
                $attrList['type'] = 'text';
                $attrList['value'] = $singleValue;

                $html .= static::getHelper()->wrapSingleField('<input ' . static::getHelper()->buildTagAttributes($attrList) . '/>');

            }

            $html .= static::getHelper()->getCloneButton($arHtmlControl["NAME"]);

            return $html;
        }

        ob_start();

        $containerId = 'devbx_location_' . $arUserField['ENTITY_ID'] . '_' . $arUserField["ENTITY_VALUE_ID"];

        echo '<div id="' . $containerId . '">';

        foreach ($arHtmlControl["VALUE"] as $singleValue) {
            $APPLICATION->IncludeComponent("bitrix:sale.location.selector.search", "", array(
                "ID" => "",
                "CODE" => $singleValue,
                "INPUT_NAME" => $arHtmlControl["NAME"],
                "PROVIDE_LINK_BY" => "code",
                "SHOW_ADMIN_CONTROLS" => 'Y',
                "SELECT_WHEN_SINGLE" => 'N',
                "FILTER_BY_SITE" => 'N',
                "FILTER_SITE_ID" => '',
                "SHOW_DEFAULT_LOCATIONS" => 'N',
                "SEARCH_BY_PRIMARY" => 'Y',

                /*
                "INITIALIZE_BY_GLOBAL_EVENT" => 'onAdminFilterInited', // this allows js logic to be initialized after admin filter
                "GLOBAL_EVENT_SCOPE" => 'window'
                */
            ),
                false
            );

        }

        $arParams = array(
            "ID" => "",
            "CODE" => "",
            "INPUT_NAME" => $arHtmlControl["NAME"],
            "PROVIDE_LINK_BY" => "code",
            "SHOW_ADMIN_CONTROLS" => 'Y',
            "SELECT_WHEN_SINGLE" => 'N',
            "FILTER_BY_SITE" => 'N',
            "FILTER_SITE_ID" => '',
            "SHOW_DEFAULT_LOCATIONS" => 'N',
            "SEARCH_BY_PRIMARY" => 'Y',
            "ADMIN_MODE" => "Y",

            /*
            "INITIALIZE_BY_GLOBAL_EVENT" => 'onAdminFilterInited', // this allows js logic to be initialized after admin filter
            "GLOBAL_EVENT_SCOPE" => 'window'
            */
        );

        $signer = new \Bitrix\Main\Security\Sign\Signer;

        $arPostData = array(
            'component' => 'bitrix:sale.location.selector.search',
            'parameters' => $signer->sign(base64_encode(serialize($arParams)), 'sale.location.selector.search'),
            'template' => $signer->sign(base64_encode(''), 'sale.location.selector.search'),
        );

        ?>
        <script>

            function addNewRow<?=$containerId?>(button) {
                BX.ajax({
                    url: '/bitrix/tools/devbx.core/ajax_component.php',
                    method: 'POST',
                    data: <?=json_encode($arPostData)?>,
                    processData: false,
                    onsuccess: function (result) {
                        let ob = BX.processHTML(result, false),
                            container = document.getElementById('<?=\CUtil::JSEscape($containerId)?>'),
                            i;

                        //container.insertAdjacentHTML('beforeend', '<br>'+ob.HTML);

                        button.insertAdjacentHTML('beforebegin', ob.HTML);

                        for (i = 0; i < ob.SCRIPT.length; i++) {
                            BX.evalGlobal(ob.SCRIPT[i].JS);
                        }
                    }
                });
            }

        </script>

        <input type="button" value="<?= htmlspecialcharsbx(Loc::getMessage('USER_TYPE_PROP_ADD')) ?>"
               onclick="addNewRow<?= $containerId ?>(this);">
        <?

        echo '</div>';

        return ob_get_clean();
    }

    public static function GetFilterHTML($arUserField, $arHtmlControl)
    {
        global $APPLICATION;

        if (!Loader::includeModule("sale"))
            return '<input type="text" name="' . $arHtmlControl["NAME"] . '" value="' . $arHtmlControl["VALUE"] . '">';

        if ($arUserField["ENTITY_VALUE_ID"] < 1 && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"]) > 0)
            $arHtmlControl["VALUE"] = htmlspecialcharsbx($arUserField["SETTINGS"]["DEFAULT_VALUE"]);

        ob_start();

        echo '<div style="width:100%;padding: 0px 0px 0px 10px;">';

        $APPLICATION->IncludeComponent("bitrix:sale.location.selector.search", "", array(
            "ID" => "",
            "CODE" => $arHtmlControl["VALUE"],
            "INPUT_NAME" => $arHtmlControl["NAME"],
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

        return ob_get_clean() . '</div>';
    }

    public static function GetFilterData($arUserField, $arHtmlControl)
    {
        return array(
            "id" => $arHtmlControl["ID"],
            "name" => $arHtmlControl["NAME"],
            "filterable" => ""
        );
    }

    public static function GetAdminListViewHTML($arUserField, $arHtmlControl)
    {
        static $bInit = false;

        if (!$bInit) {
            \CJSCore::Init();

            Asset::getInstance()->addJs('/bitrix/js/sale/core_ui_widget.js');
            Asset::getInstance()->addJs('/bitrix/js/sale/core_ui_etc.js');
            Asset::getInstance()->addJs('/bitrix/js/sale/core_ui_autocomplete.js');
            Asset::getInstance()->addJs('/bitrix/components/bitrix/sale.location.selector.search/templates/.default/script.js');

            Asset::getInstance()->addString('<link rel="stylesheet" type="text/css" href="/bitrix/components/bitrix/sale.location.selector.search/templates/.default/style.css">');

            $bInit = true;
        }

        if (strlen($arHtmlControl["VALUE"]) > 0) {

            if (!Loader::includeModule("sale"))
                return $arHtmlControl["VALUE"];

            $path = [];

            try {

                $dbPath = LocationTable::getPathToNodeByCode(
                    $arHtmlControl["VALUE"],
                    ['select' => ['ID', 'CODE', 'DISPLAY_NAME' => 'NAME.NAME'], 'filter' => ['=NAME.LANGUAGE_ID' => LANGUAGE_ID]]
                );

                while ($arPath = $dbPath->fetch()) {
                    $path[] = $arPath['DISPLAY_NAME'];
                }

                if (count($path))
                    return htmlspecialchars(implode(', ', $path));
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            return $arHtmlControl["VALUE"];
        } else
            return '&nbsp;';
    }

    public static function GetAdminListEditHTML($arUserField, $arHtmlControl)
    {
        return self::getSingleEditHtml($arUserField, $arHtmlControl["NAME"], $arHtmlControl["VALUE"]);
    }

    public static function GetAdminListEditHTMLMulty($arUserField, $arHtmlControl)
    {
        return self::getMultipleEditHtml($arUserField, $arHtmlControl["NAME"], $arHtmlControl["VALUE"]);
    }

    public static function OnSearchIndex($arUserField)
    {
        $value = $arUserField["VALUE"];
        if (!is_array($value)) {
            $value = array($value);
        }

        $res = [];

        foreach ($value as $singleValue) {
            $arUserField['VALUE'] = $singleValue;
            $singleValue = self::GetPublicView($arUserField);
            if ($singleValue)
                $res[] = $singleValue;
        }

        return implode(', ', $res);
    }

    public static function GetPublicView($arUserField, $arAdditionalParameters = array())
    {
        if (empty($arUserField["VALUE"]) || !Loader::includeModule('sale'))
            return $arUserField["VALUE"];

        $path = [];

        try {

            $dbPath = LocationTable::getPathToNodeByCode(
                $arUserField["VALUE"],
                ['select' => ['ID', 'CODE', 'DISPLAY_NAME' => 'NAME.NAME'], 'filter' => ['=NAME.LANGUAGE_ID' => LANGUAGE_ID]]
            );

            while ($arPath = $dbPath->fetch()) {
                $path[] = $arPath['DISPLAY_NAME'];
            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }

        if (count($path))
            return htmlspecialchars(implode(', ', $path));

        return $arUserField["VALUE"];
    }

    public static function getHelper()
    {
        return new HtmlBuilder(static::USER_TYPE_ID);
    }
}
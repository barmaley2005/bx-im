<?php

namespace DevBx\Core\UserType;

use Bitrix\Iblock;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;

Loader::includeModule('iblock');

Loc::loadMessages(__FILE__);

define('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_REP_SYM_OTHER', 'other');

class ElementAutoComplete extends BaseType
{
    const USER_TYPE_ID = 'devbx_element_auto_complete';

    public static function GetUserTypeDescription()
    {
        return array(
            "USER_TYPE_ID" => self::USER_TYPE_ID,
            "CLASS_NAME" => __CLASS__,
            "DESCRIPTION" => Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_DESCRIPTION'),
            "BASE_TYPE" => 'int',
            "EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'),
            "VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
        );
    }

    public static function isSupported()
    {
        return Loader::includeModule('iblock');
    }

    public static function GetDBColumnType($arUserField)
    {
        global $DB;
        switch (strtolower($DB->type)) {
            case "mysql":
                return "int(18)";
            case "oracle":
                return "number(18)";
            case "mssql":
                return "int";
        }

        return "int";
    }

    public static function CheckFields($arUserField, $value)
    {
        $aMsg = array();
        return $aMsg;
    }

    public static function GetSettingsHTML($arUserField, $arHtmlControl, $bVarsFromForm)
    {
        if ($bVarsFromForm) {
            $arSettings = self::PrepareSettings(array("SETTINGS" => $GLOBALS[$arHtmlControl["NAME"]]));
        } elseif (is_array($arUserField)) {
            $arSettings = self::PrepareSettings($arUserField);
        } else {
            $arSettings = array();
        }

        return
            '<tr>
<td>
' . Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_SETTING_IBLOCK') . '
</td>
            <td>' . GetIBlockDropDownList(
                $arSettings['IBLOCK_ID'],
                $arHtmlControl["NAME"] . '[IBLOCK_TYPE_ID]',
                $arHtmlControl["NAME"] . '[IBLOCK_ID]',
                false,
                'class="adm-detail-iblock-types"',
                'class="adm-detail-iblock-list" onchange="showUsertypeElementNote(this);"'
            ) .
            '</td>
            </tr>' .
            '<tr>
		<td>' . Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_SETTING_VIEW') . '</td>
		<td>' . SelectBoxFromArray($arHtmlControl["NAME"] . '[VIEW]', static::GetPropertyViewsList(true), htmlspecialcharsbx($arSettings['VIEW'])) . '</td>
		</tr>
		<tr>
		<td>' . Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_SETTING_SHOW_ADD') . '</td>
		<td>' . InputType('checkbox', $arHtmlControl["NAME"] . '[SHOW_ADD]', 'Y', htmlspecialcharsbx($arSettings["SHOW_ADD"])) . '</td>
		</tr>
		<tr>
		<td>' . Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_SETTING_IBLOCK_MESS') . '</td>
		<td>' . InputType('checkbox', $arHtmlControl["NAME"] . '[IBLOCK_MESS]', 'Y', htmlspecialcharsbx($arSettings["IBLOCK_MESS"])) . '</td>
		</tr>
		<tr>
		<td>' . Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_SETTING_MAX_WIDTH') . '</td>
		<td><input type="text" name="' . $arHtmlControl["NAME"] . '[MAX_WIDTH]" value="' . (int)$arSettings['MAX_WIDTH'] . '">&nbsp;' . Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_SETTING_COMMENT_MAX_WIDTH') . '</td>
		</tr>
		<tr>
		<td>' . Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_SETTING_MIN_HEIGHT') . '</td>
		<td><input type="text" name="' . $arHtmlControl["NAME"] . '[MIN_HEIGHT]" value="' . (int)$arSettings['MIN_HEIGHT'] . '">&nbsp;' . Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_SETTING_COMMENT_MIN_HEIGHT') . '</td>
		</tr>
		<tr>
		<td>' . Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_SETTING_MAX_HEIGHT') . '</td>
		<td><input type="text" name="' . $arHtmlControl["NAME"] . '[MAX_HEIGHT]" value="' . (int)$arSettings['MAX_HEIGHT'] . '">&nbsp;' . Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_SETTING_COMMENT_MAX_HEIGHT') . '</td>
		</tr>
		<tr>
		<td>' . Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_SETTING_BAN_SYMBOLS') . '</td>
		<td><input type="text" name="' . $arHtmlControl["NAME"] . '[BAN_SYM]" value="' . htmlspecialcharsbx($arSettings['BAN_SYM']) . '"></td>
		</tr>
		<tr>
		<td>' . Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_SETTING_REP_SYMBOL') . '</td>
		<td>' . SelectBoxFromArray($arHtmlControl["NAME"] . '[REP_SYM]', static::GetReplaceSymList(true), htmlspecialcharsbx($arSettings['REP_SYM'])) . '&nbsp;<input type="text" name="' . $arHtmlControl["NAME"] . '[OTHER_REP_SYM]" size="1" maxlength="1" value="' . htmlspecialcharsbx($arSettings['OTHER_REP_SYM']) . '"></td>
		</tr>
		';

    }

    public static function PrepareSettings($arUserField)
    {
        return array(
            "IBLOCK_ID" => $arUserField["SETTINGS"]["IBLOCK_ID"],
            "VIEW" => $arUserField["SETTINGS"]["VIEW"],
            "SHOW_ADD" => $arUserField["SETTINGS"]["SHOW_ADD"] == "Y" ? "Y" : "N",
            "IBLOCK_MESS" => $arUserField["SETTINGS"]["IBLOCK_MESS"] == "Y" ? "Y" : "N",
            "MAX_WIDTH" => intval($arUserField["SETTINGS"]["MAX_WIDTH"]),
            "MIN_HEIGHT" => intval($arUserField["SETTINGS"]["MIN_HEIGHT"]),
            "MAX_HEIGHT" => intval($arUserField["SETTINGS"]["MAX_HEIGHT"]),
            "BAN_SYM" => intval($arUserField["SETTINGS"]["BAN_SYM"]),
            "REP_SYM" => intval($arUserField["SETTINGS"]["REP_SYM"]),
            "DEFAULT_VALUE" => intval($arUserField["SETTINGS"]["DEFAULT_VALUE"]) > 0 ? ($arUserField["SETTINGS"]["DEFAULT_VALUE"]) : '',
        );
    }

    protected static function GetPropertyViewsList($boolFull)
    {
        $boolFull = ($boolFull === true);
        if ($boolFull) {
            return array(
                'REFERENCE' => array(
                    Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_VIEW_AUTO'),
                    Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_VIEW_TREE'),
                    Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_VIEW_ELEMENT'),
                ),
                'REFERENCE_ID' => array(
                    'A', 'T', 'E'
                ),
            );
        }
        return array('A', 'T', 'E');
    }

    protected static function GetReplaceSymList($boolFull = false)
    {
        $boolFull = ($boolFull === true);
        if ($boolFull) {
            return array(
                'REFERENCE' => array(
                    Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_SYM_SPACE'),
                    Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_SYM_GRID'),
                    Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_SYM_STAR'),
                    Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_SYM_UNDERLINE'),
                    Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_SYM_OTHER'),

                ),
                'REFERENCE_ID' => array(
                    ' ',
                    '#',
                    '*',
                    '_',
                    DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_REP_SYM_OTHER,
                ),
            );
        }
        return array(' ', '#', '*', '_');
    }

    protected static function GetSymbols($arSettings)
    {
        $strBanSym = $arSettings['BAN_SYM'];
        $strRepSym = (DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_REP_SYM_OTHER == $arSettings['REP_SYM'] ? $arSettings['OTHER_REP_SYM'] : $arSettings['REP_SYM']);
        $arBanSym = str_split($strBanSym, 1);
        $arRepSym = array_fill(0, sizeof($arBanSym), $strRepSym);
        $arResult = array(
            'BAN_SYM' => $arBanSym,
            'REP_SYM' => $arRepSym,
            'BAN_SYM_STRING' => $strBanSym,
            'REP_SYM_STRING' => $strRepSym,
        );
        return $arResult;
    }

    public static function GetValueForAutoComplete($arSettings, $arValue, $arBanSym = "", $arRepSym = "")
    {
        $strResult = '';
        $mxResult = static::GetPropertyValue($arSettings, $arValue);
        if (is_array($mxResult)) {
            $strResult = htmlspecialcharsbx(str_replace($arBanSym, $arRepSym, $mxResult['~NAME'])) . ' [' . $mxResult['ID'] . ']';
        }
        return $strResult;
    }

    protected static function GetPropertyValue($arProperty, $arValue)
    {
        $mxResult = false;
        if ((int)$arValue['VALUE'] > 0) {
            $mxResult = static::GetLinkElement($arValue['VALUE'], $arProperty['LINK_IBLOCK_ID']);
        }
        return $mxResult;
    }

    protected static function GetLinkElement($intElementID, $intIBlockID)
    {
        static $cache = array();

        $intIBlockID = (int)$intIBlockID;
        if ($intIBlockID <= 0)
            $intIBlockID = 0;
        $intElementID = (int)$intElementID;
        if ($intElementID <= 0)
            return false;
        if (!isset($cache[$intElementID])) {
            $arFilter = array();
            if ($intIBlockID > 0)
                $arFilter['IBLOCK_ID'] = $intIBlockID;
            $arFilter['ID'] = $intElementID;
            $arFilter['SHOW_HISTORY'] = 'Y';
            $rsElements = \CIBlockElement::GetList(array(), $arFilter, false, false, array('IBLOCK_ID', 'ID', 'NAME'));
            if ($arElement = $rsElements->GetNext()) {
                $arResult = array(
                    'ID' => $arElement['ID'],
                    'NAME' => $arElement['NAME'],
                    '~NAME' => $arElement['~NAME'],
                    'IBLOCK_ID' => $arElement['IBLOCK_ID'],
                );
                $cache[$intElementID] = $arResult;
            } else {
                $cache[$intElementID] = false;
            }
        }
        return $cache[$intElementID];
    }

    public static function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        global $APPLICATION;

        if (($arUserField["ENTITY_VALUE_ID"] < 1) && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"]) > 0)
            $arHtmlControl["VALUE"] = intval($arUserField["SETTINGS"]["DEFAULT_VALUE"]);

        if (!Loader::includeModule("iblock")) {
            return '<input type="text" name="' . $arHtmlControl["NAME"] . '" value="' . htmlspecialchars($arHtmlControl["VALUE"]) . '">';
        }

        $arSettings = static::PrepareSettings($arUserField);
        $arSymbols = static::GetSymbols($arSettings);

        $fixIBlock = $arSettings["IBLOCK_ID"] > 0;
        $windowTableId = 'iblockprop-' . Iblock\PropertyTable::TYPE_ELEMENT . '-' . $arUserField["ENTITY_VALUE_ID"] . '-' . $arSettings['IBLOCK_ID'];

        ob_start();
        ?><?
        $control_id = $APPLICATION->IncludeComponent(
            "bitrix:main.lookup.input",
            "iblockedit",
            array(
                "CONTROL_ID" => preg_replace(
                    "/[^a-zA-Z0-9_]/i",
                    "x",
                    $arHtmlControl["NAME"] . '_' . mt_rand(0, 10000)
                ),
                "INPUT_NAME" => $arHtmlControl["NAME"],
                "INPUT_NAME_STRING" => "inp_" . $arHtmlControl["NAME"],
                "INPUT_VALUE_STRING" => htmlspecialcharsback(static::GetValueForAutoComplete(
                    $arSettings,
                    $arHtmlControl,
                    $arSymbols['BAN_SYM'],
                    $arSymbols['REP_SYM']
                )),
                "START_TEXT" => Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_INVITE'),
                "MULTIPLE" => "N",
                "MAX_WIDTH" => $arSettings['MAX_WIDTH'],
                "IBLOCK_ID" => $arSettings["IBLOCK_ID"],
                'WITHOUT_IBLOCK' => (!$fixIBlock ? 'Y' : 'N'),
                'BAN_SYM' => $arSymbols['BAN_SYM_STRING'],
                'REP_SYM' => $arSymbols['REP_SYM_STRING'],
                'FILTER' => 'Y'
            ), null, array("HIDE_ICONS" => "Y")
        );
        ?>
        <script>
            BX.ready(
                BX.defer(function() {
                    top.jsMLI_<?=$control_id?> = window.jsMLI_<?=$control_id?>;
                    console.log(top.jsMLI_<?=$control_id?>);
                }));
        </script>
        <?
        if ($arSettings['VIEW'] == 'T') {
            $name = $APPLICATION->IncludeComponent(
                'bitrix:main.tree.selector',
                'iblockedit',
                array(
                    "INPUT_NAME" => $arHtmlControl["NAME"],
                    'ONSELECT' => 'jsMLI_' . $control_id . '.SetValue',
                    'MULTIPLE' => "N",
                    'SHOW_INPUT' => 'N',
                    'SHOW_BUTTON' => 'Y',
                    'GET_FULL_INFO' => 'Y',
                    "START_TEXT" => Loc::getMessage("DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_LIST_INVITE"),
                    'BUTTON_CAPTION' => Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_CHOOSE_ELEMENT'),
                    'BUTTON_TITLE' => Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_CHOOSE_ELEMENT_DESCR'),
                    "NO_SEARCH_RESULT_TEXT" => Loc::getMessage("DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_NO_SEARCH_RESULT_TEXT"),
                    "IBLOCK_ID" => $arSettings["IBLOCK_ID"],
                    'WITHOUT_IBLOCK' => (!$fixIBlock ? 'Y' : 'N'),
                    'BAN_SYM' => $arSymbols['BAN_SYM_STRING'],
                    'REP_SYM' => $arSymbols['REP_SYM_STRING']
                ), null, array("HIDE_ICONS" => "Y")
            );
            ?>
              <script>
                  top.<?=$name?> = window.<?=$name?>;
              </script>
            <?
        } elseif ($arSettings['VIEW'] == 'E') {
            ?><input style="float: left; margin-right: 10px; margin-top: 5px;"
                     type="button"
                     value="<? echo Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_SEARCH_ELEMENT'); ?>"
                     title="<? echo Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_SEARCH_ELEMENT_DESCR'); ?>"
                     onclick="jsUtils.OpenWindow('/bitrix/admin/iblock_element_search.php?lang=<? echo LANGUAGE_ID; ?>&IBLOCK_ID=<? echo $arSettings["IBLOCK_ID"]; ?>&n=&k=&lookup=<? echo 'jsMLI_' . $control_id; ?><?= ($fixIBlock ? '&iblockfix=y' : '') . '&tableId=' . $windowTableId; ?>', 900, 700);"><?
        }
        if ($arSettings['SHOW_ADD'] == 'Y' && $fixIBlock) {
            if ($arSettings['IBLOCK_MESS'] == 'Y') {
                $arLangMess = \CIBlock::GetMessages($arSettings["IBLOCK_ID"]);
                $strButtonCaption = $arLangMess['ELEMENT_ADD'];
                if ($strButtonCaption == '') {
                    $strButtonCaption = Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_NEW_ELEMENT');
                }
            } else {
                $strButtonCaption = Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_NEW_ELEMENT');
            }
            ?><input type="button" style="margin-top: 5px;"
                     value="<? echo htmlspecialcharsbx($strButtonCaption); ?>"
                     title="<? echo Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_NEW_ELEMENT_DESCR'); ?>"
                     onclick="jsUtils.OpenWindow('<? echo '/bitrix/admin/' . \CIBlock::GetAdminElementEditLink(
                             $arSettings["IBLOCK_ID"],
                             null,
                             array(
                                 'menu' => null,
                                 'IBLOCK_SECTION_ID' => -1,
                                 'find_section_section' => -1,
                                 'lookup' => 'jsMLI_' . $control_id,
                                 'tableId' => $windowTableId
                             ),
                             ($fixIBlock ? '&iblockfix=y' : '')
                         ); ?>', 900, 700);"
            ><?
        }
        $strResult = ob_get_contents();
        ob_end_clean();

        return $strResult;
    }

    public static function GetEditFormHTMLMulty($arUserField, $arHtmlControl)
    {
        global $APPLICATION;

        $controlName = $arHtmlControl["NAME"];

        if (substr($controlName, -2) == '[]')
            $controlName = substr($controlName, 0, -2);

        if (($arUserField["ENTITY_VALUE_ID"] < 1) && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"]) > 0)
            $arHtmlControl["VALUE"] = array(intval($arUserField["SETTINGS"]["DEFAULT_VALUE"]));

        $arSettings = static::PrepareSettings($arUserField);
        $arSymbols = static::GetSymbols($arSettings);

        if (!Loader::includeModule("iblock")) {
            ob_start();

            foreach ($arHtmlControl['VALUE'] as $i => $v)
                echo '<input type="text" name="' . $controlName . '[' . $i . ']" value="' . htmlspecialchars($v) . '">';

            return ob_get_clean();
        }


        $fixIBlock = $arSettings["IBLOCK_ID"] > 0;
        $windowTableId = 'iblockprop-' . Iblock\PropertyTable::TYPE_ELEMENT . '-' . $arUserField["ENTITY_VALUE_ID"] . '-' . $arSettings['IBLOCK_ID'];

        $mxResultValue = static::GetValueForAutoCompleteMulti($arSettings, $arHtmlControl["VALUE"], $arSymbols['BAN_SYM'], $arSymbols['REP_SYM']);
        $strResultValue = (is_array($mxResultValue) ? htmlspecialcharsback(implode("\n", $mxResultValue)) : '');

        ob_start();
        ?><?
        $control_id = $APPLICATION->IncludeComponent(
            "bitrix:main.lookup.input",
            "iblockedit",
            array(
                "CONTROL_ID" => preg_replace(
                    "/[^a-zA-Z0-9_]/i",
                    "x",
                    $controlName . '_' . mt_rand(0, 10000)
                ),
                "INPUT_NAME" => $controlName . '[]',
                "INPUT_NAME_STRING" => "inp_" . $controlName,
                "INPUT_VALUE_STRING" => $strResultValue,
                "START_TEXT" => Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_INVITE'),
                "MULTIPLE" => "Y",
                "MAX_WIDTH" => $arSettings['MAX_WIDTH'],
                "MIN_HEIGHT" => $arSettings['MIN_HEIGHT'],
                "MAX_HEIGHT" => $arSettings['MAX_HEIGHT'],
                "IBLOCK_ID" => $arSettings["IBLOCK_ID"],
                'WITHOUT_IBLOCK' => (!$fixIBlock ? 'Y' : 'N'),
                'BAN_SYM' => $arSymbols['BAN_SYM_STRING'],
                'REP_SYM' => $arSymbols['REP_SYM_STRING'],
                'FILTER' => 'Y'
            ), null, array("HIDE_ICONS" => "Y")
        );
        ?>
        <script>
            BX.ready(
                BX.defer(function() {
                    top.jsMLI_<?=$control_id?> = window.jsMLI_<?=$control_id?>;
                    console.log(top.jsMLI_<?=$control_id?>);
                }));
        </script>
        <?
        if ($arSettings['VIEW'] == 'T') {
            $name = $APPLICATION->IncludeComponent(
                'bitrix:main.tree.selector',
                'iblockedit',
                array(
                    "INPUT_NAME" => $controlName,
                    'ONSELECT' => 'jsMLI_' . $control_id . '.SetValue',
                    'MULTIPLE' => "Y",
                    'SHOW_INPUT' => 'N',
                    'SHOW_BUTTON' => 'Y',
                    'GET_FULL_INFO' => 'Y',
                    "START_TEXT" => Loc::getMessage("DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_LIST_INVITE"),
                    'BUTTON_CAPTION' => Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_CHOOSE_ELEMENT'),
                    'BUTTON_TITLE' => Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_CHOOSE_ELEMENT_MULTI_DESCR'),
                    "NO_SEARCH_RESULT_TEXT" => Loc::getMessage("DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_NO_SEARCH_RESULT_TEXT"),
                    "IBLOCK_ID" => $arSettings["IBLOCK_ID"],
                    'WITHOUT_IBLOCK' => (!$fixIBlock ? 'Y' : 'N'),
                    'BAN_SYM' => $arSymbols['BAN_SYM_STRING'],
                    'REP_SYM' => $arSymbols['REP_SYM_STRING']
                ), null, array("HIDE_ICONS" => "Y")
            );
            ?><?
        } elseif ($arSettings['VIEW'] == 'E') {
            ?><input style="float: left; margin-right: 10px; margin-top: 5px;" type="button"
                     value="<? echo Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_SEARCH_ELEMENT'); ?>"
                     title="<? echo Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_SEARCH_ELEMENT_MULTI_DESCR'); ?>"
                     onclick="jsUtils.OpenWindow('/bitrix/admin/iblock_element_search.php?lang=<? echo LANGUAGE_ID; ?>&IBLOCK_ID=<? echo $arSettings["IBLOCK_ID"]; ?>&n=&k=&m=y&lookup=<? echo 'jsMLI_' . $control_id; ?><?= ($fixIBlock ? '&iblockfix=y' : '') . '&tableId=' . $windowTableId; ?>', 900, 700);"><?
        }
        if ($arSettings['SHOW_ADD'] == 'Y' && $fixIBlock) {
            if ($arSettings['IBLOCK_MESS'] == 'Y') {
                $arLangMess = \CIBlock::GetMessages($arSettings["IBLOCK_ID"]);
                $strButtonCaption = $arLangMess['ELEMENT_ADD'];
                if ('' == $strButtonCaption) {
                    $strButtonCaption = Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_NEW_ELEMENT');
                }
            } else {
                $strButtonCaption = Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_NEW_ELEMENT');
            }
            ?><input type="button" style="margin-top: 5px;"
                     value="<? echo htmlspecialcharsbx($strButtonCaption); ?>"
                     title="<? echo Loc::getMessage('DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_NEW_ELEMENT_DESCR'); ?>"
                     onclick="jsUtils.OpenWindow('<? echo '/bitrix/admin/' . \CIBlock::GetAdminElementEditLink(
                             $arSettings["IBLOCK_ID"],
                             null,
                             array(
                                 'menu' => null,
                                 'IBLOCK_SECTION_ID' => -1,
                                 'find_section_section' => -1,
                                 'lookup' => 'jsMLI_' . $control_id,
                                 'tableId' => $windowTableId
                             ),
                             ($fixIBlock ? '&iblockfix=y' : '')
                         ); ?>', 900, 700);"
            ><?
        }
        $strResult = ob_get_contents();
        ob_end_clean();

        return $strResult;
    }

    public static function GetValueForAutoCompleteMulti($arSettings, $arValues, $arBanSym = "", $arRepSym = "")
    {
        $arResult = false;

        if (is_array($arValues)) {
            foreach ($arValues as $intPropertyValueID => $arOneValue) {
                if (!is_array($arOneValue)) {
                    $strTmp = $arOneValue;
                    $arOneValue = array(
                        'VALUE' => $strTmp,
                    );
                }
                $mxResult = static::GetPropertyValue($arSettings, $arOneValue);
                if (is_array($mxResult)) {
                    $arResult[$intPropertyValueID] = htmlspecialcharsbx(str_replace($arBanSym, $arRepSym, $mxResult['~NAME'])) . ' [' . $mxResult['ID'] . ']';
                }
            }
        }
        return $arResult;
    }

    public static function GetFilterHTML($arUserField, $arHtmlControl)
    {
        $arUserField["SETTINGS"]["SHOW_ADD"] = 'N';

        return '<div style="width:100%;">'.self::GetEditFormHTMLMulty($arUserField, $arHtmlControl).'</div>';
    }

    public static function GetAdminListViewHTML($arUserField, $arHtmlControl)
    {
        static $bInit = false;
        
        if (!$bInit)
        {
            Asset::getInstance()->addJs('/bitrix/components/bitrix/main.lookup.input/script.js');
            Asset::getInstance()->addJs('/bitrix/components/bitrix/main.lookup.input/templates/iblockedit/script2.js');

            Asset::getInstance()->addJs('/bitrix/components/bitrix/main.tree.selector/script.js');


            $bInit = true;
        }

        $strResult = '';
        $mxResult = static::GetPropertyValue($arUserField,$arHtmlControl);
        if (is_array($mxResult))
        {
            $strResult = $mxResult['NAME'].' [<a href="/bitrix/admin/'.
                \CIBlock::GetAdminElementEditLink(
                    $mxResult['IBLOCK_ID'],
                    $mxResult['ID'],
                    array(
                        'WF' => 'Y'
                    )
                ).'" title="'.Loc::getMessage("DEVBX_USER_TYPE_ELEMENT_AUTO_COMPLETE_MESS_ELEMENT_EDIT").'">'.$mxResult['ID'].'</a>]';
        }

        return $strResult;
    }

    public static function GetAdminListEditHTML($arUserField, $arHtmlControl)
    {
        $arUserField["SETTINGS"]["SHOW_ADD"] = 'N';

        ob_start();
        ?>
        <script>
            if (!window.JCMainLookupAdminSelector && top.JCMainLookupAdminSelector)
                window.JCMainLookupAdminSelector = top.JCMainLookupAdminSelector;

            if (!window.JCTreeSelectControl && top.JCTreeSelectControl)
                window.JCTreeSelectControl = top.JCTreeSelectControl;

        </script>
        <?

        $html = ob_get_clean();

        return $html.self::GetEditFormHTML($arUserField, $arHtmlControl);
    }

    public static function GetAdminListEditHTMLMulty($arUserField, $arHtmlControl)
    {
        $arUserField["SETTINGS"]["SHOW_ADD"] = 'N';

        ob_start();
        ?>
        <script>
            window = top;

            if (!window.JCMainLookupAdminSelector && top.JCMainLookupAdminSelector)
                window.JCMainLookupAdminSelector = top.JCMainLookupAdminSelector;

            if (!window.JCTreeSelectControl && top.JCTreeSelectControl)
                window.JCTreeSelectControl = top.JCTreeSelectControl;

        </script>
        <?

        $html = ob_get_clean();


        return $html.self::GetEditFormHTMLMulty($arUserField, $arHtmlControl);
    }

    public static function GetPublicEdit($arUserField, $arAdditionalParameters = array())
    {
        $fieldName = static::getFieldName($arUserField, $arAdditionalParameters);
        $value = static::getFieldValue($arUserField, $arAdditionalParameters);

        if (!is_array($value))
            $value = array($value);

        if ($arUserField["MULTIPLE"] == "Y")
        {
            return '';
        } else
        {
            return '';
        }
    }

    public static function GetPublicView($arUserField, $arAdditionalParameters = array())
    {
        $value = $arUserField["VALUE"];
        if (!is_array($value))
            $value = array($value);

        $value = array_filter($value, function($v) {
            return intval($v)>0;
        });

        if (empty($value))
            return '';

        if (!Loader::includeModule('iblock'))
            return implode(', ', $value);

        $ar = [];

        $dbObject = \CIBlockElement::GetList([], ['=ID'=>$value], false, false, ['ID','NAME']);
        while ($dbResult = $dbObject->Fetch())
        {
            $ar[] = $dbResult['NAME'];
        }

        if ($arAdditionalParameters['ESCAPE'] === false)
            return implode(', ', $ar);

        return htmlspecialcharsbx(implode(', ', $ar));
    }

    public static function OnSearchIndex($arUserField)
    {
        return self::GetPublicView($arUserField, ['ESCAPE'=>false]);
    }
}
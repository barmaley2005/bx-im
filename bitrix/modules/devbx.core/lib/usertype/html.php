<?php

namespace DevBx\Core\UserType;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;
use Bitrix\Main\Type;

Loc::loadMessages(__FILE__);

class Html extends BaseType
{

    const USER_TYPE_ID = "devbx_html";

    public static function GetUserTypeDescription()
    {
        return array(
            "USER_TYPE_ID" => self::USER_TYPE_ID,
            "CLASS_NAME" => __CLASS__,
            "DESCRIPTION" => Loc::getMessage('DEVBX_USER_TYPE_HTML_DESCRIPTION'),
            "BASE_TYPE" => 'string',
            /*"EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'),
            "VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),*/
        );
    }

    public static function GetDBColumnType($arUserField)
    {
        return "text";
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
        return '';
    }

    public static function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        if($arUserField["ENTITY_VALUE_ID"]<1 && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
            $arHtmlControl["VALUE"] = $arUserField["SETTINGS"]["DEFAULT_VALUE"];

        return '</td></tr><tr><td colspan="2" align="center">'.self::getEditRowHtml($arUserField, $arHtmlControl);
    }

    public static function GetFilterHTML($arUserField, $arHtmlControl)
    {
        return FindUserID($arHtmlControl["NAME"], $arHtmlControl["VALUE"]);
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
        if ($arHtmlControl["VALUE"] <> '')
        {
            return htmlspecialcharsbx($arHtmlControl["VALUE"]);
        }

        return '&nbsp;';
    }

    public static function getEditRowHtml($arUserField, $arHtmlControl)
    {
        global $APPLICATION;

        $settings = self::PrepareSettings($arUserField);

        ob_start();
        ?><table style="width:100%;"><?
        if(\Bitrix\Main\Loader::includeModule("fileman")):
            ?><tr>
            <td colspan="2" align="center">
                <?

                ob_start();

                $Editor = new \CHTMLEditor;
                $Editor->Show(array(
                    'name' => 'DEVBX_HTML_EDITOR',
                    'id' => 'DEVBX_HTML_EDITOR',
                    'siteId' => SITE_ID,
                    'width' => "100%",
                    'height' => $settings["height"] < 450 ? 450 : $settings["height"],
                    'content' => htmlspecialcharsBack($arHtmlControl["VALUE"]),
                    'bAllowPhp' => false,
                    "limitPhpAccess" => false,
                    "display" => true,
                    "componentFilter" => false,
                    "setFocusAfterShow" => false,
                    "relPath" => '/',
                    "templateId" => SITE_TEMPLATE_ID
                ));

                $html = ob_get_clean();

                echo str_replace('DEVBX_HTML_EDITOR', $arHtmlControl["NAME"], $html);
                ?>
            </td>
            </tr>
        <?else:?>
            <tr>
                <td colspan="2" align="center"><textarea cols="60" rows="10" name="<?=$arHtmlControl["NAME"]?>" style="width:100%"><?=$arHtmlControl["VALUE"]?></textarea></td>
            </tr>
        <?endif;?>
        </table>
        <?
        $return = ob_get_contents();
        ob_end_clean();
        return  $return;
    }

    public static function GetAdminListEditHTML($arUserField, $arHtmlControl)
    {
        if($arUserField["ENTITY_VALUE_ID"]<1 && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
            $arHtmlControl["VALUE"] = $arUserField["SETTINGS"]["DEFAULT_VALUE"];

        return self::getEditRowHtml($arUserField, $arHtmlControl);
    }

    public static function GetEditFormHTMLMulty($arUserField, $arHtmlControl)
    {
        if (($arUserField["ENTITY_VALUE_ID"] < 1) && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"]) > 0)
            $value = array($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
        else {
            $value = $arHtmlControl["VALUE"];
        }

        if (!is_array($value))
            $value = array($value);

        if (empty($value))
            $value[] = '';

        $controlName = substr($arHtmlControl["NAME"],0,-2);

        $rnd = new Type\RandomSequence(serialize(func_get_args()));
        $id = $rnd->randString(10);

        $html = '<div id="container_'.$id.'">';

        $idx = 0;
        foreach ($value as $singleValue)
        {
            $arHtmlControl["VALUE"] = $singleValue;
            $arHtmlControl["NAME"] = $controlName.'['.$idx.']';
            $html .= '<div>'.self::GetAdminListEditHTML($arUserField, $arHtmlControl).'</div>';
            $idx++;
        }

        $arHtmlControl["VALUE"] = '';
        $arHtmlControl["NAME"] = 'FIELD_TEMPLATE';

        $tpl = \CUtil::JSEscape(json_encode(self::GetAdminListEditHTML($arUserField, $arHtmlControl)));
        $jsControlname = \CUtil::JSEscape($controlName);

        $html .= <<<JS
<script type="text/javascript">

(function() {
    
    let id = '$id',
        tpl = '$tpl',
        idx = $idx,
        controlName = '$jsControlname';
    
    tpl = JSON.parse(tpl);
    
    window['addNewRow'+id] = function() {
        
        let container = document.getElementById('container_'+id),
            fieldName = controlName+'['+idx+']';
        
        idx++;
        
        ob = BX.processHTML(tpl.replaceAll('FIELD_TEMPLATE',fieldName));

    console.log(ob.HTML);
    
        
        container.insertAdjacentHTML('beforeend', '<div>'+ob.HTML+'</div>');
        
        ob.SCRIPT.forEach(s => {
            console.log(s.JS);
            BX.evalGlobal(s.JS);            
        });
        
    };
    
})();
    
</script>
JS;

        $html .= '</div>';

        $html .= '<input type="button" value="'.htmlspecialcharsbx(Loc::getMessage('USER_TYPE_PROP_ADD')).'" onclick="addNewRow'.$id.'(this);">';

        return $html;
    }

    public static function GetPublicView($arUserField, $arAdditionalParameters = array())
    {
        return $arUserField["VALUE"];
    }

    public static function GetPublicEdit($arUserField, $arAdditionalParameters = array())
    {
        $fieldName = static::getFieldName($arUserField, $arAdditionalParameters);
        $value = static::getFieldValue($arUserField, $arAdditionalParameters);

        return 'Not available';
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
<?php

namespace DevBx\Core\UserType;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;
use Bitrix\Main\UserTable;

Loc::loadMessages(__FILE__);

class User extends BaseType
{

    const USER_TYPE_ID = "devbx_user";

    public static function GetUserTypeDescription()
    {
        return array(
            "USER_TYPE_ID" => self::USER_TYPE_ID,
            "CLASS_NAME" => __CLASS__,
            "DESCRIPTION" => Loc::getMessage('DEVBX_USER_TYPE_USER_DESCRIPTION'),
            "BASE_TYPE" => 'int',
            "EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'),
            "VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
        );
    }

    public static function GetDBColumnType($arUserField)
    {
        return "int(18)";
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

    public static function FindUserID($tag_name, $tag_value, $user_name="", $form_name = "form1", $tag_size = "3", $tag_maxlength="", $button_value = "...", $tag_class="typeinput", $button_class="tablebodybutton", $search_page="/bitrix/admin/user_search.php")
    {
        /** @global \CMain $APPLICATION */
        global $APPLICATION;

        \CJSCore::Init(array('devbx_core_admin'));

        /*
        $rnd = new Type\RandomSequence(serialize(func_get_args()));
        $jsId = 'finduserid_'.$rnd->randString(10);
        */

        $jsId = $tag_name;

        return '
        <div data-devbx-usertype-user-id="'.$jsId.'">
            <input data-entity="input" type="text" name="'.$tag_name.'" value="'.htmlspecialcharsbx($tag_value).'" size="'.$tag_size.'" maxlength="'.$tag_maxlength.'" class="'.$tag_class.'">
            <input data-entity="button" class="'.$button_class.'" type="button" name="FindUser" value="'.$button_value.'">
            <span data-entity="view"></span>
            <script type="text/javascript">
                devbx.admin.bindUserSelect(\''.$jsId.'\');
            </script>
        </div>
        ';
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

        ob_start();
        ?>
        <tr>
            <td>
                <?=Loc::getMessage('DEVBX_USER_TYPE_USER_DEFAULT_VALUE_TITLE')?>
            </td>
            <td>
                <?
                echo self::FindUserID($arHtmlControl["NAME"].'[DEFAULT_VALUE]', $value);
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

        return self::FindUserID($arHtmlControl["NAME"], $arHtmlControl["VALUE"]);
    }

    public static function GetFilterHTML($arUserField, $arHtmlControl)
    {
        return self::FindUserID($arHtmlControl["NAME"], $arHtmlControl["VALUE"]);
    }

    public static function GetFilterData($arUserField, $arHtmlControl)
    {
        return array(
            "id" => $arHtmlControl["ID"],
            "name" => $arHtmlControl["NAME"],
            "filterable" => ""
        );
    }

    public static function getUserById($id)
    {
        static $cache = [];

        if (isset($cache[$id]))
            return $cache[$id];

        $row = UserTable::getRowById($id);
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
            if ($arHtmlControl['VALUE']>0)
            {
                $ar = static::getUserById($arHtmlControl['VALUE']);

                return $ar ? '['.$ar['ID'].'] '.htmlspecialcharsbx($ar['NAME']) : '&nbsp;';
            } else
            {
                return '&nbsp;';
            }
        }

        return '&nbsp;';
    }

    public static function GetAdminListEditHTML($arUserField, $arHtmlControl)
    {
        if($arUserField["ENTITY_VALUE_ID"]<1 && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
            $arHtmlControl["VALUE"] = $arUserField["SETTINGS"]["DEFAULT_VALUE"];

        return self::FindUserID($arHtmlControl["NAME"], $arHtmlControl["VALUE"]);
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
        $ar = static::getUserById($arUserField["VALUE"]);
        if ($ar)
            return htmlspecialcharsbx($ar['NAME']);

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
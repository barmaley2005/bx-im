<?php

namespace DevBx\Core\UserType;

abstract class BaseType {

    protected static function getFieldName($arUserField, $arAdditionalParameters = array())
    {
        $fieldName = $arUserField["FIELD_NAME"];
        if($arUserField["MULTIPLE"] == "Y")
        {
            $fieldName .= "[]";
        }

        return $fieldName;
    }

    protected static function normalizeFieldValue($value)
    {
        if(!is_array($value))
        {
            $value = array($value);
        }
        if(empty($value))
        {
            $value = array(null);
        }

        return $value;
    }

    protected static function getFieldValue($arUserField, $arAdditionalParameters = array())
    {
        if(!$arAdditionalParameters["bVarsFromForm"])
        {
            if($arUserField["ENTITY_VALUE_ID"] <= 0)
            {
                $value = $arUserField["SETTINGS"]["DEFAULT_VALUE"];
            }
            else
            {
                $value = $arUserField["VALUE"];
            }
        }
        else
        {
            $value = $_REQUEST[$arUserField["FIELD_NAME"]];
        }

        return static::normalizeFieldValue($value);
    }

}
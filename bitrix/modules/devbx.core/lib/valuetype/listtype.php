<?php

namespace DevBx\Core\ValueType;

use Bitrix\Main\Localization\Loc;

class ListType extends BaseType {
    public static function getType() {
        return 'LIST';
    }

    public static function showValue($params)
    {
        $htmlStr = '';

        $name = $params['VARIABLE_NAME'];

        if ($params['MULTIPLE'] == 'Y')
            $name .= '[]';

        $value = $params["VALUE"];

        if (empty($value))
            $value = $params['DEFAULT'];

        if (!is_array($value))
            $value = array($value);

        ?>
        <select name="<?= $name ?>" <?if ($params['MULTIPLE'] == 'Y'):?> multiple<?endif?><?=$htmlStr?>>
            <?/*
            <option value=""><?= Loc::getMessage("DEVBX_CORE_VALUE_TYPE_LIST_NOT_SELECTED") ?></option>
            */?>
            <?
            foreach ($params["VALUES"] as $optValue => $optName):?>
                <option value="<?= htmlspecialcharsbx($optValue) ?>"<?
                if (in_array($optValue, $value)):?> selected<? endif ?>><?= htmlspecialcharsbx($optName) ?></option>
            <? endforeach; ?>
        </select>
        <?
    }

}
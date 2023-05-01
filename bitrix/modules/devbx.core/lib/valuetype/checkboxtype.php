<?php

namespace DevBx\Core\ValueType;

class CheckBoxType extends BaseType {

    public static function getType() {
        return 'CHECKBOX';
    }

    public static function showValue($params)
    {
        $htmlStr = '';

        ?>
        <input type="hidden" name="<?= $params['VARIABLE_NAME'] ?>" value="N">
        <input type="checkbox" name="<?= $params['VARIABLE_NAME'] ?>"
               value="Y" <?=$htmlStr?>
	<?if ($params["VALUE"] == "Y"):?> checked="checked" <? endif ?>>
        <?
    }

    public static function convertToDB($value, $settings)
    {
        return $value == 'Y' ? 'Y' : 'N';
    }

    public static function convertFromDB($value, $settings)
    {
        return $value == 'Y' ? 'Y' : 'N';
    }
}
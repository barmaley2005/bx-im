<?php

namespace DevBx\Core\ValueType;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

class LocalFileType extends BaseType {

    public static function getType() {
        return 'LOCAL_FILE';
    }

    public static function showValue($params)
    {
        static $num = 0;

        $num++;

        $name = $params['VARIABLE_NAME'];
        $value = $params['VALUE'];

        $elementId = 'local_file_'.md5($name).'_'.$num;
        $eventName = 'local_file_'.md5($name).'_'.$num;

        ?>
        <input id="<?=$elementId?>" type="text" name="<?=htmlspecialcharsbx($name)?>" value="<?echo htmlspecialcharsbx($value); ?>" size="30">
        <input type="button" value="<?echo Loc::getMessage("DEVBX_CORE_VALUE_TYPE_LOCAL_FILE_BUTTON_TILE"); ?>" onclick="window.<?=$eventName?>()">
        <?\CAdminFileDialog::ShowScript(array(
        "event" => "$eventName",
        "arResultDest" => array(
            "ELEMENT_ID" => $elementId,
        ) ,
        "arPath" => array(
            "SITE" => SITE_ID,
            "PATH" => "/".Option::get("main", "upload_dir", "upload"),
        ) ,
        "select" => 'F', // F - file only, D - folder only
        "operation" => 'O', // O - open, S - save
        "showUploadTab" => true,
        "showAddToMenuTab" => false,
        "fileFilter" => $params['EXTENSIONS'],
        "allowAllFiles" => true,
        "SaveConfig" => true,
    ));
}

}
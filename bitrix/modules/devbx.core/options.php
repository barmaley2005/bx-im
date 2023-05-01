<?php

/* @global $APPLICATION  */

use Bitrix\Main\Localization\Loc;

$module_id = 'devbx.core';
$moduleAccessLevel = $APPLICATION->GetGroupRight($module_id);

if ($moduleAccessLevel >= 'R') {
    \Bitrix\Main\Loader::includeModule("devbx.core");
    \Bitrix\Main\Loader::includeModule($module_id);
    IncludeModuleLangFile(__FILE__);

    $aTabs = array(
        array("DIV" => "edit1", "TAB" => Loc::getMessage("DEVBX_CORE_TAB_SETTINGS"), "TITLE" => Loc::getMessage("DEVBX_CORE_TAB_SETTINGS_TITLE")),
    );

    $tabControl = new CAdminTabControl("TabControl" . md5($module_id), $aTabs, true, true);

    $options = new \DevBx\Core\ModuleOptions($module_id);

    $arSettings = array(
    );

    if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['RestoreDefaults']) && !empty($_GET['RestoreDefaults']) && $moduleAccessLevel == "W" && check_bitrix_sessid()) {
        $options->setDefaultValues();
        LocalRedirect($APPLICATION->GetCurPage() . '?lang=' . LANGUAGE_ID . '&mid=' . $module_id . '&' . $tabControl->ActiveTabParam());
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $moduleAccessLevel == "W" && check_bitrix_sessid()) {
        if (isset($_POST['Update']) && $_POST['Update'] === 'Y') {
            $options->saveSettings($arSettings);
            LocalRedirect($APPLICATION->GetCurPage() . '?lang=' . LANGUAGE_ID . '&mid=' . $module_id . '&' . $tabControl->ActiveTabParam());
        }
    }

    $tabControl->Begin();
    ?>
    <form method="POST" action="<?
    echo $APPLICATION->GetCurPage() ?>?lang=<?
    echo LANGUAGE_ID ?>&mid=<?= $module_id ?>"
          name="module_settings">
        <? echo bitrix_sessid_post();

        $tabControl->BeginNextTab();

        echo '<tr><td colspan="2">';

        $options->showSettings($arSettings);

        echo '</td></tr>';

        $tabControl->Buttons(); ?>
        <script type="text/javascript">
            function RestoreDefaults() {
                if (confirm('<? echo CUtil::JSEscape(Loc::getMessage("DEVBX_CORE_OPTIONS_BTN_HINT_RESTORE_DEFAULT_WARNING")); ?>'))
                    window.location = "<?echo $APPLICATION->GetCurPage()?>?lang=<? echo LANGUAGE_ID; ?>&mid=<? echo $module_id; ?>&RestoreDefaults=Y&<?=bitrix_sessid_get()?>";
            }

        </script>
        <input type="submit" <?
        if ($moduleAccessLevel < "W") echo "disabled" ?> name="Update"
               value="<?
               echo Loc::getMessage("DEVBX_CORE_OPTIONS_BTN_SAVE") ?>">
        <input type="hidden" name="Update" value="Y">
        <input type="button" <?
        if ($moduleAccessLevel < "W") echo "disabled" ?>
               title="<?
               echo Loc::getMessage("DEVBX_CORE_OPTIONS_BTN_HINT_RESTORE_DEFAULT") ?>" onclick="RestoreDefaults();"
               value="<?
               echo Loc::getMessage("DEVBX_CORE_OPTIONS_BTN_RESTORE_DEFAULT") ?>">
    </form>
    <?
    $tabControl->End();
}
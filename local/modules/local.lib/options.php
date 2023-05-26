<?php

use Bitrix\Main\Localization\Loc;

global $USER_FIELD_MANAGER;

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$module_id = $request->get('mid');
$moduleAccessLevel = $APPLICATION->GetGroupRight($module_id);

if ($moduleAccessLevel >= 'R') {
    \Bitrix\Main\Loader::includeModule("devbx.core");
    \Bitrix\Main\Loader::includeModule($module_id);
    IncludeModuleLangFile(__FILE__);

    $aTabs = array(
        array("DIV" => "edit1", "TAB" => Loc::getMessage("LOCAL_LIB_TAB_SETTINGS"), "TITLE" => Loc::getMessage("LOCAL_LIB_TAB_SETTINGS_TITLE")),
    );

    $tabControl = new CAdminTabControl("TabControl" . md5($module_id), $aTabs, true, true);

    $options = new \DevBx\Core\Admin\Options($module_id);

    $arSiteValues = [];

    $iterator = \Bitrix\Main\SiteTable::getList();
    while ($ar = $iterator->fetch())
    {
        $arSiteValues[$ar['LID']] = '['.$ar['LID'].'] '.$ar['NAME'];
    }

    $arSettings = array(
        "YANDEX_CLOUD" => array(
            "TITLE" => Loc::getMessage("LOCAL_LIB_YANDEX_CLOUD_TITLE"),
            "ITEMS" => array(
                "YANDEX_CLOUD_TOKEN" => array(
                    "TYPE" => "STRING",
                    "TITLE" => Loc::getMessage("LOCAL_LIB_YANDEX_CLOUD_TOKEN_TITLE"),
                    "SIZE" => 40,
                ),
                "YANDEX_CLOUD_FOLDER_ID" => array(
                    "TYPE" => "STRING",
                    "TITLE" => Loc::getMessage("LOCAL_LIB_YANDEX_CLOUD_FOLDER_ID_TITLE"),
                    "SIZE" => 40,
                ),
            )
        ),
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
                if (confirm('<? echo CUtil::JSEscape(Loc::getMessage("LOCAL_LIB_OPTIONS_BTN_HINT_RESTORE_DEFAULT_WARNING")); ?>'))
                    window.location = "<?echo $APPLICATION->GetCurPage()?>?lang=<? echo LANGUAGE_ID; ?>&mid=<? echo $module_id; ?>&RestoreDefaults=Y&<?=bitrix_sessid_get()?>";
            }

        </script>
        <input type="submit" <?
        if ($moduleAccessLevel < "W") echo "disabled" ?> name="Update"
               value="<?
               echo Loc::getMessage("LOCAL_LIB_OPTIONS_BTN_SAVE") ?>">
        <input type="hidden" name="Update" value="Y">
        <input type="button" <?
        if ($moduleAccessLevel < "W") echo "disabled" ?>
               title="<?
               echo Loc::getMessage("LOCAL_LIB_OPTIONS_BTN_HINT_RESTORE_DEFAULT") ?>"
               onclick="RestoreDefaults();"
               value="<?
               echo Loc::getMessage("LOCAL_LIB_OPTIONS_BTN_RESTORE_DEFAULT") ?>">
    </form>
    <?
    $tabControl->End();
}
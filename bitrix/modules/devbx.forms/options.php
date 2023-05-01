<?php

/* @global $APPLICATION */
/* @global $USER */

$module_id = "devbx.forms";

$RIGHT_R = $USER->IsAdmin();
$RIGHT_W = $USER->IsAdmin();

if ($RIGHT_W)
{
    IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
    IncludeModuleLangFile(__FILE__);

    $aTabs = array(
        /*array(
            "DIV" => "edit1",
            "TAB" => GetMessage("MAIN_TAB_SET"),
            "ICON" => "bitrixcloud_settings",
            "TITLE" => GetMessage("MAIN_TAB_TITLE_SET"),
        ),*/
        array(
            "DIV" => "edit2",
            "TAB" => GetMessage("MAIN_TAB_RIGHTS"),
            "ICON" => "bitrixcloud_settings",
            "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS"),
        ),
    );
    $tabControl = new CAdminTabControl("tabControl", $aTabs);

    CModule::IncludeModule($module_id);

    if (
        $_SERVER["REQUEST_METHOD"] === "POST"
        && (
            isset($_REQUEST["Update"])
            || isset($_REQUEST["Apply"])
            || isset($_REQUEST["RestoreDefaults"])
        )
        && $RIGHT_W
        && check_bitrix_sessid()
    )
    {
        if (isset($_REQUEST["RestoreDefaults"]))
        {
            COption::RemoveOption($module_id);
        }
        else
        {
            /*
             * set options
             */
        }

        ob_start();
        $Update = $Update.$Apply;
        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
        ob_end_clean();

        if (isset($_REQUEST["back_url_settings"]))
        {
            if(
                isset($_REQUEST["Apply"])
                || isset($_REQUEST["RestoreDefaults"])
            )
                LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
            else
                LocalRedirect($_REQUEST["back_url_settings"]);
        }
        else
        {
            LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&".$tabControl->ActiveTabParam());
        }
    }

    ?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=LANGUAGE_ID?>">
    <?
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    ?>
    <?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
    <?$tabControl->Buttons();?>
    <input <?if(!$RIGHT_W) echo "disabled" ?> type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>" class="adm-btn-save">
    <input <?if(!$RIGHT_W) echo "disabled" ?> type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
    <?if($_REQUEST["back_url_settings"] <> ''):?>
        <input <?if(!$RIGHT_W) echo "disabled" ?> type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
        <input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
    <?endif?>
    <input <?if(!$RIGHT_W) echo "disabled" ?> type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" onclick="return confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
    <?=bitrix_sessid_post();?>
    <?$tabControl->End();?>
</form>
<?
}
<?php

namespace DevBx\Core\Admin;

class EditHelper {
    public static function imageFieldEdit(\CAdminForm $tabControl, $arField, $value)
    {
        \Bitrix\Main\Loader::includeModule("fileman");

        $strHtml = \CFileInput::Show($arField->getName(), $value, array(
            "IMAGE" => "Y",
        ), array(
            'upload' => true,
            'medialib' => true,
            'file_dialog' => true,
            'cloud' => true,
            'del' => true,
            'description' => false,
        ));
        $tabControl->AddViewField($arField->getName(), $arField->getTitle(), $strHtml, $arField->isRequired);
    }

    public static function fileFieldEdit(\CAdminForm $tabControl, $arField, $value)
    {
        \Bitrix\Main\Loader::includeModule("fileman");

        $strHtml = \CFileInput::Show($arField->getName(), $value, array(
            "IMAGE" => "N",
        ), array(
            'upload' => true,
            'medialib' => true,
            'file_dialog' => true,
            'cloud' => true,
            'del' => true,
            'description' => false,
        ));
        $tabControl->AddViewField($arField->getName(), $arField->getTitle(), $strHtml, $arField->isRequired);
    }

    public static function userFieldEdit($tabControl, $arField, $value)
    {
        $strHtml = FindUserID($arField->getName(), $value, "", $tabControl->GetFormName());
        $tabControl->AddViewField($arField->getName(), $arField->getTitle(), $strHtml, $arField->isRequired);

    }

    public static function htmlAndTextFieldEdit(\CAdminForm $tabControl, $arField, $value, $arValues)
    {
        \Bitrix\Main\Loader::includeModule("fileman");

        $tabControl->BeginCustomField($arField->getName(), $arField->getTitle(), $arField->isRequired);

        ?>
        <tr>
            <td colspan="2">
                <table width="100%" class="internal">
                    <tr class="heading">
                        <td colspan="2"><?=htmlspecialcharsbx($arField->getTitle())?></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <?

                            \CFileMan::AddHTMLEditorFrame(
                                $arField->getName(),
                                $value,
                                $arField->getName()."_TYPE",
                                $arValues[$arField->getName()."_TYPE"],
                                array(
                                    'height' => 250,
                                    'width' => '100%'
                                ),
                                "N",
                                0,
                                "",
                                "",
                                SITE_ID,
                                true,
                                false,
                                array('toolbarConfig' => 'admin', 'saveEditorKey' => 'local_lib')
                            );

                            ?>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <?

        $tabControl->EndCustomField($arField->getName());
    }

    public static function htmlFieldEdit(\CAdminForm $tabControl, $arField, $value, $arValues)
    {
        \Bitrix\Main\Loader::includeModule("fileman");

        $tabControl->BeginCustomField($arField->getName(), $arField->getTitle(), $arField->isRequired);

        ?>
        <tr>
            <td colspan="2">
                <table width="100%" class="internal">
                    <tr class="heading">
                        <td colspan="2"><?=htmlspecialcharsbx($arField->getTitle())?></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <?

                            $arParams = Array(
                                "bUseOnlyDefinedStyles"=>\COption::GetOptionString("fileman", "show_untitled_styles", "N")!="Y",
                                "bDisplay" => true,
                                "bWithoutPHP" => true,
                                "light_mode" => "Y",
                                "arTaskbars" => Array("BXPropertiesTaskbar", "BXSnippetsTaskbar"),
                                "height" => 400,
                                "width" => "100%",
                                "toolbarConfig" => "",
                                "site" => SITE_ID,
                            );
                            \CFileMan::ShowHTMLEditControl($arField->getName(), $value, $arParams);

                            ?>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <?

        $tabControl->EndCustomField($arField->getName());
    }

    public static function yandexMapFieldEdit(\CAdminForm $tabControl, $arField, $value, $arValues)
    {
        \Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/iblock/iblock_edit.js');

        $tabControl->BeginCustomField($arField->getName(), $arField->getTitle(), $arField->isRequired);

        ?>
        <tr>
            <td colspan="2">
                <table width="100%" class="internal">
                    <tr class="heading">
                        <td colspan="2"><?=htmlspecialcharsbx($arField->getTitle())?></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <?
                            \Bitrix\Main\Loader::includeModule("fileman");

                            echo \CIBlockPropertyMapYandex::GetPropertyFieldHtml(
                                \CIBlockPropertyMapYandex::PrepareSettings(array("CODE"=>$arField->getName(),"ID"=>1,"MULTIPLE"=>"N")),
                                array("VALUE"=>$value),
                                array("VALUE"=>$arField->getName(),"MODE"=>"FORM_FILL")
                            );

                            ?>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <?

        $tabControl->EndCustomField($arField->getName());
    }
}
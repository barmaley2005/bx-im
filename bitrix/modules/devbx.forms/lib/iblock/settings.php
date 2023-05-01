<?php

namespace DevBx\Forms\Iblock;

class Settings {

    public static function showIblockSettings($entity, $arSettings)
    {
        global $USER_FIELD_MANAGER;

        //$userFieldManager = \Bitrix\Main\UserField\Internal\UserFieldHelper::getInstance()->getManager();

        $arUserFields = $USER_FIELD_MANAGER->GetUserFields($entity->getUfId(),0, LANGUAGE_ID);

        $arIblockElementFields = [];
        $arIblockSectionFields = [];

        foreach ($arUserFields as $arUserField)
        {
            if ($arUserField['USER_TYPE_ID'] == 'iblock_element' || $arUserField['USER_TYPE_ID'] == 'devbx_element_auto_complete')
            {
                $arIblockElementFields[] = $arUserField;
            }

            if ($arUserField['USER_TYPE_ID'] == 'iblock_section')
            {
                $arIblockSectionFields[] = $arUserField;
            }
        }

        if (empty($arIblockElementFields) && empty($arIblockSectionFields))
            return;

        ?>
        <table class="adm-detail-content-table edit-table">
            <tr>
                <td colspan="2" class="heading">Настройки для инфоблоков</td>
            </tr>
            <?

            if (!empty($arIblockElementFields))
        {
            ?>
            <tr>
            <td width="40%" class="adm-detail-content-cell-l">Показывать в форме редактирования элемента,<br>вкладку со списком записей формы для выбранных полей</td>
            <td class="adm-detail-content-cell-r">
            <?
            foreach ($arIblockElementFields as $arUserField)
            {
                $displayName = $arUserField['LIST_COLUMN_LABEL'] ? $arUserField['LIST_COLUMN_LABEL'] : $arUserField['FIELD_NAME'];

                $inputName = 'SETTINGS[FIELD_IBLOCK_ELEMENT_TAB]['.$arUserField['FIELD_NAME'].']';
                $checked = $arSettings['FIELD_IBLOCK_ELEMENT_TAB'][$arUserField['FIELD_NAME']] == 'Y';
                ?>
                    <label>
                        <input type="hidden" name="<?=$inputName?>" value="N">
                        <input type="checkbox" name="<?=$inputName?>" value="Y"<?if ($checked):?> checked<?endif?>>
                        <?=htmlspecialcharsbx($displayName)?>
                    </label><br>
                <?
            }
            ?>
            </td>
            </tr>
                <?
        }

            if (!empty($arIblockSectionFields))
            {
                ?>
                <tr>
                    <td width="40%" class="adm-detail-content-cell-l">Показывать в форме редактирования раздела,<br>вкладку со списком записей формы для выбранных полей</td>
                    <td class="adm-detail-content-cell-r">
                        <?
                        foreach ($arIblockSectionFields as $arUserField)
                        {
                            $displayName = $arUserField['LIST_COLUMN_LABEL'] ? $arUserField['LIST_COLUMN_LABEL'] : $arUserField['FIELD_NAME'];

                            $inputName = 'SETTINGS[FIELD_IBLOCK_SECTION_TAB]['.$arUserField['FIELD_NAME'].']';
                            $checked = $arSettings['FIELD_IBLOCK_SECTION_TAB'][$arUserField['FIELD_NAME']] == 'Y';
                            ?>
                            <label>
                                <input type="hidden" name="<?=$inputName?>" value="N">
                                <input type="checkbox" name="<?=$inputName?>" value="Y"<?if ($checked):?> checked<?endif?>>
                                <?=htmlspecialcharsbx($displayName)?>
                            </label><br>
                            <?
                        }
                        ?>
                    </td>
                </tr>
                <?
            }

        ?>
        </table>
        <?
    }

}
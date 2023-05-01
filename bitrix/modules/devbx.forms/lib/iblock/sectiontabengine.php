<?php

namespace DevBx\Forms\Iblock;

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use DevBx\Forms\FormManager;
use DevBx\Forms\FormTable;

class SectionTabEngine
{

    public static function checkFields()
    {
        return true;
    }

    public static function saveData($iblockSectionInfo)
    {
        return true;
    }

    public static function getTabs($iblockSectionInfo)
    {
        global $USER_FIELD_MANAGER;

        $request = Context::getCurrent()->getRequest();

        if ($iblockSectionInfo["ID"] > 0 && (!isset($request['action']) || $request['action'] != 'copy')) {
            $arTabs = array();

            //$userFieldManager = \Bitrix\Main\UserField\Internal\UserFieldHelper::getInstance()->getManager();

            $dbForm = FormTable::getList([
                'select' => [
                    '*',
                    'FROM_LANG_NAME' => 'LANG_NAME.NAME',
                ],
                'order' => array('ID' => 'DESC')
            ]);

            while ($arForm = $dbForm->fetch()) {

                $formUserFields = array();

                if (is_array($arForm['SETTINGS']['FIELD_IBLOCK_SECTION_TAB'])) {
                    foreach ($arForm['SETTINGS']['FIELD_IBLOCK_SECTION_TAB'] as $field => $value) {
                        if ($value == 'Y')
                        {
                            $formUserFields[] = $field;
                        }
                    }
                }

                if (!empty($formUserFields))
                {
                    $obForm = FormManager::getInstance()->compileFormEntity($arForm);

                    $arUserFields = $USER_FIELD_MANAGER->GetUserFields($obForm->getUfId(),0, LANGUAGE_ID);

                    foreach ($formUserFields as $fieldName)
                    {
                        if (!array_key_exists($fieldName, $arUserFields))
                            continue;

                        $addTab = false;

                        $arUserField = $arUserFields[$fieldName];

                        if ($arUserField['USER_TYPE_ID'] != 'iblock_section')
                            continue;

                        if ($arUserField['SETTINGS']['IBLOCK_ID'] == $iblockSectionInfo['IBLOCK']['ID'])
                        {
                            $addTab = true;
                        } elseif (empty($arUserField['SETTINGS']['IBLOCK_ID']))
                        {
                            $arResult = $obForm->getDataClass()::getList([
                                'filter' => [
                                    '='.$fieldName => $iblockSectionInfo['ID']
                                ],
                                'select' => [
                                    'ID'
                                ],
                                'limit' => 1
                            ])->fetch();

                            if ($arResult)
                            {
                                $addTab = true;
                            }
                        }

                        if ($addTab)
                        {
                            $arReplace = array(
                                '#FORM_ID#' => $arForm['ID'],
                                '#FORM_NAME#' => $arForm['FROM_LANG_NAME'],
                                '#FIELD_NAME#' => $arUserField['LIST_COLUMN_LABEL'] ?: $arUserField['FIELD_NAME'],
                            );

                            $arTabs[] = array(
                                "DIV" => "devbx_form_".$arForm['ID'].'_'.$fieldName,
                                "SORT" => 4,
                                "TAB" => Loc::getMessage('DEVBX_FORMS_IBLOCK_SECTION_TAB', $arReplace),
                                "TITLE" => Loc::getMessage('DEVBX_FORMS_IBLOCK_SECTION_TAB_TITLE', $arReplace),
                            );
                        }
                    }
                }

            }

            return $arTabs;
        }

        return null;
    }

    public static function showTab($div, $iblockSectionInfo)
    {
        ?>
        <tr>
            <td colspan="2">
                <?
                $id = substr($div, strlen('devbx_form_'));

                $FORM_ID = substr($id, 0, strpos($id, '_'));
                $FIELD_NAME = substr($id, strpos($id, '_')+1);
                $ELEMENT_ID = $iblockSectionInfo['ID'];

                $modulePath = \Bitrix\Main\Loader::getLocal("modules/devbx.forms");

                require($modulePath.'/tools/devbx_form_iblock_result_list.php');

                ?>
            </td>
        </tr>
        <?

    }

}
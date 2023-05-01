<?php

namespace DevBx\Core\Admin;

class ListHelper
{
    public static function viewUser($row, $primary, $key, $arRes)
    {
        if (intval($arRes[$key])>0)
        {
            $arUser = \CUser::GetByID($arRes[$key])->Fetch();

            if ($arUser)
                $row->AddViewField($key, '<a href="user_edit.php?lang=' . LANGUAGE_ID . '&ID=' . $arRes[$key] . '">[' . $arRes[$key] . '] ' . htmlspecialcharsbx($arUser["LOGIN"]));
        } else {
            $row->AddViewField($key, '');
        }
    }

    public static function getViewUser()
    {
        return array(__CLASS__, 'viewUser');
    }

    public static function editUser($row, $primary, $key, $arRes)
    {
        $arUser = \CUser::GetByID($arRes[$key])->Fetch();

        $row->AddViewField($key, '<a href="user_edit.php?lang=' . LANGUAGE_ID . '&ID=' . $arRes[$key] . '">[' . $arRes[$key] . '] ' . htmlspecialcharsbx($arUser["LOGIN"]));

        $strHtml = FindUserID($key, $arRes[$key], "", "form_" . $row->pList->table_id);
        $row->AddEditField($key, $strHtml);
    }

    public static function getEditUser()
    {
        return array(__CLASS__, 'editUser');
    }

    public static function viewSiteID($row, $primary, $key, $arRes)
    {
        static $arCache = [];

        $val = $arRes[$key];

        if (strlen($val))
        {
            if (!isset($arCache[$val])) {
                $arCache[$val] = \Bitrix\Main\SiteTable::getById($val)->fetch();
            }

            if (!is_array($arCache[$val]))
                return;

            $row->AddViewField($key, '<a href="/bitrix/admin/site_edit.php?lang=' . LANGUAGE_ID . '&LID=' . $val . '">[' . $val . '] ' . htmlspecialcharsbx($arCache[$val]['NAME']));
        } else
        {
            $row->AddViewField($key, '');
        }
    }

    public static function getViewSiteID()
    {
        return array(__CLASS__, 'viewSiteID');
    }

    public static function filterSiteID(AdminList $adminList, $id, $arFilterValues)
    {
        $adminList->getFilterConfig();

        $value = $arFilterValues[$id];
        if (!is_array($value))
            $value = array($value);

        ?>
        <tr>
            <td><?= $adminList->getFilterFieldTitle($id) ?></td>
            <td>
                <select name="filter_<?= $id ?>[]" multiple>
                    <?php

                    $iterator = \Bitrix\Main\SiteTable::getList(array('order' => array('SORT' => 'ASC', 'LID' => 'ASC')));
                    while ($ar = $iterator->fetch()) {
                        ?>
                        <option value="<?= $ar['LID'] ?>"<? if (in_array($ar['LID'], $value)):?> selected<?endif ?>><?= htmlspecialchars('['.$ar['LID'].'] '.$ar['NAME']) ?></option>
                        <?
                    }
                    ?>

                </select>

            </td>
        </tr>
        <?
    }

    public static function getFilterSiteID()
    {
        return array(__CLASS__, 'filterSiteID');
    }

    public static function editFile($row, $primary, $key, $arRes)
    {
        $row->AddFileField($key,array(
            "IMAGE" => "Y",
            "PATH" => "Y",
            "FILE_SIZE" => "Y",
            "DIMENSIONS" => "Y",
            "IMAGE_POPUP" => "Y",
            "MAX_SIZE" => 200,
            "MIN_SIZE" => 200,
        ), array(
                'upload' => true,
                'medialib' => false,
                'file_dialog' => false,
                'cloud' => true,
                'del' => true,
                'description' => false,
            )
        );
    }

    public static function getEditFile()
    {
        return array(__CLASS__, 'editFile');
    }

    public static function viewFile(\CAdminListRow $row, $primary, $key, $arRes)
    {
        $row->AddViewFileField($key,array(
            "IMAGE" => "Y",
            "PATH" => "Y",
            "FILE_SIZE" => "Y",
            "DIMENSIONS" => "Y",
            "IMAGE_POPUP" => "Y",
            "MAX_SIZE" => 200,
            "MIN_SIZE" => 200,
        ));
    }

    public static function getViewFile()
    {
        return array(__CLASS__, 'viewFile');
    }
}
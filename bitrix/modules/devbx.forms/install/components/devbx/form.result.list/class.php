<?

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use DevBx\Forms\FormTable;
use Bitrix\Main\Entity;
use Bitrix\Main\UserField\Internal\UserFieldHelper;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class CDevBxFormsFormResultList extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        global $DB;

        $arParams['FORM_ID'] = intval($arParams['FORM_ID']);

        $arParams['ONLY_ACTIVE_RESULTS'] = $arParams['ONLY_ACTIVE_RESULTS'] === 'N' ? 'N' : 'Y';

        $arParams['RESULTS_COUNT'] = intval($arParams['RESULTS_COUNT']);

        if ($arParams['RESULTS_COUNT'] < 0)
            $arParams['RESULTS_COUNT'] = 20;

        $arParams["CREATED_DATE_FORMAT"] = trim($arParams["CREATED_DATE_FORMAT"]);
        if ($arParams["CREATED_DATE_FORMAT"] == '')
            $arParams["CREATED_DATE_FORMAT"] = $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT"));

        $arParams["MODIFIED_DATE_FORMAT"] = trim($arParams["MODIFIED_DATE_FORMAT"]);
        if ($arParams["MODIFIED_DATE_FORMAT"] == '')
            $arParams["MODIFIED_DATE_FORMAT"] = $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT"));

        return $arParams;
    }

    public function executeComponent()
    {
        global $APPLICATION, $USER, $DB;

        if (!Loader::includeModule("devbx.forms")) {
            ShowError('devbx.forms not installed');
            return;
        }

        $arResult = &$this->arResult;
        $arParams = &$this->arParams;

        CPageOption::SetOptionString("main", "nav_page_in_session", "N");

        $formId = $arParams['FORM_ID'];

        $arResult['FORM_SETTINGS'] = FormTable::getRowById($formId);
        if (!$arResult['FORM_SETTINGS']) {
            ShowError(Loc::getMessage('DEVBX_FORMS_COMPONENT_FORM_NOT_FOUND', array('#FORM_ID#' => $formId)));
            return;
        }

        $obForm = \DevBx\Forms\FormManager::getInstance()->getFormInstance($formId);
        if (!$obForm) {
            ShowError(Loc::getMessage('DEVBX_FORMS_COMPONENT_FORM_NOT_FOUND', array('#FORM_ID#' => $formId)));
            return;
        }

        $arResult['ALLOW_VIEW'] = !empty(array_intersect($USER->GetUserGroupArray(), $arResult['FORM_SETTINGS']['VIEW_GROUPS']));

        if (!$arResult['ALLOW_VIEW']) {
            ShowError(Loc::getMessage('DEVBX_FORMS_COMPONENT_FORM_ACCESS_DENIED_VIEW_FORM'));
            return;
        }

        $arParams["DISPLAY_TOP_PAGER"] = $arParams["DISPLAY_TOP_PAGER"] == "Y";
        $arParams["DISPLAY_BOTTOM_PAGER"] = $arParams["DISPLAY_BOTTOM_PAGER"] != "N";
        $arParams["PAGER_TITLE"] = trim($arParams["PAGER_TITLE"]);
        $arParams["PAGER_SHOW_ALWAYS"] = $arParams["PAGER_SHOW_ALWAYS"] == "Y";
        $arParams["PAGER_TEMPLATE"] = trim($arParams["PAGER_TEMPLATE"]);
        $arParams["PAGER_DESC_NUMBERING"] = $arParams["PAGER_DESC_NUMBERING"] == "Y";
        $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"] = intval($arParams["PAGER_DESC_NUMBERING_CACHE_TIME"]);
        $arParams["PAGER_SHOW_ALL"] = $arParams["PAGER_SHOW_ALL"] == "Y";
        $arParams["CHECK_PERMISSIONS"] = ($arParams["CHECK_PERMISSIONS"] ?? '') != "N";


        $mainQuery = new Entity\Query($obForm);
        $mainQuery->setSelect(array('*', 'UF_*'));
        $mainQuery->setOrder(array($arParams['SORT_BY1'] => $arParams['SORT_ORDER1'], $arParams['SORT_BY2'] => $arParams['SORT_ORDER2']));

        $arFilter = $this->getResultFilter([]);

        $mainQuery->setFilter($arFilter);

        $userFieldManager = UserFieldHelper::getInstance()->getManager();

        $arFields = $userFieldManager->GetUserFields($obForm->getUfId(), 0, LANGUAGE_ID);

        $arResult['COLUMN_NAME'] = array();
        $arResult['~COLUMN_NAME'] = array();

        foreach ($arParams['DISPLAY_FIELDS'] as $fieldName) {
            if (array_key_exists($fieldName, $arFields)) {
                $arResult['~COLUMN_NAME'][$fieldName] = $arFields[$fieldName]['LIST_COLUMN_LABEL'];
            } else {
                $arResult['~COLUMN_NAME'][$fieldName] = Loc::getMessage('C_DEVBX_FORM_RESULT_LIST_FIELD_' . $fieldName);
            }

            if (empty($arResult['~COLUMN_NAME'][$fieldName]))
                $arResult['~COLUMN_NAME'][$fieldName] = $fieldName;

            $arResult['COLUMN_NAME'][$fieldName] = htmlspecialcharsbx($arResult['~COLUMN_NAME'][$fieldName]);
        }

        $arNavParams = array();

        if ($arParams["DISPLAY_TOP_PAGER"] || $arParams["DISPLAY_BOTTOM_PAGER"]) {
            $arNavParams = array(
                "nPageSize" => $arParams["RESULTS_COUNT"],
                "bDescPageNumbering" => $arParams["PAGER_DESC_NUMBERING"] == 'Y',
                "bShowAll" => $arParams["PAGER_SHOW_ALL"] == 'Y',
            );
            $arNavigation = CDBResult::GetNavParams($arNavParams);
            if ($arNavigation["PAGEN"] == 0 && $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"] > 0)
                $arParams["CACHE_TIME"] = $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"];
        } else {
            $arNavigation = false;
        }

        if (empty($arParams["PAGER_PARAMS_NAME"]) || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["PAGER_PARAMS_NAME"])) {
            $pagerParameters = array();
        } else {
            $pagerParameters = $GLOBALS[$arParams["PAGER_PARAMS_NAME"]];
            if (!is_array($pagerParameters))
                $pagerParameters = array();
        }

        if ($arNavigation) {
            $result = new \CDBResult();
            $result->NavQuery($mainQuery->getQuery(), $mainQuery->queryCountTotal(), $arNavParams);
        } else {
            $mainQuery->setLimit($arParams["RESULTS_COUNT"]);

            $result = $DB->Query($mainQuery->getQuery());
        }

        $rows = array();

        while ($row = $result->GetNext()) {
            $row['EDIT_LINK'] = '/bitrix/admin/devbx_form_result_edit.php?ID=' . $row['ID'] . '&FORM_ID=' . $formId . '&lang=' . LANGUAGE_ID . '&bxpublic=Y';

            $row["DISPLAY_CREATED_DATE"] = FormatDate($arParams["CREATED_DATE_FORMAT"], MakeTimeStamp($row["CREATED_DATE"], 'YYYY-MM-DD HH:MI:SS'));
            $row["DISPLAY_MODIFIED_DATE"] = FormatDate($arParams["MODIFIED_DATE_FORMAT"], MakeTimeStamp($row["MODIFIED_DATE"], 'YYYY-MM-DD HH:MI:SS'));

            $row['DISPLAY_FIELDS'] = array();

            foreach ($arParams['DISPLAY_FIELDS'] as $fieldName) {
                if (array_key_exists($fieldName, $row)) {
                    if (array_key_exists($fieldName, $arFields)) {
                        $arField = $arFields[$fieldName];
                        $arField['VALUE'] = $row[$fieldName];

                        if (is_callable(array($arField['USER_TYPE']['CLASS_NAME'], 'getPublicText'))) {
                            $row['DISPLAY_FIELDS'][$fieldName] = call_user_func_array(array($arField['USER_TYPE']['CLASS_NAME'], 'getPublicText'), array($arField));
                        } else {
                            $row['DISPLAY_FIELDS'][$fieldName] = $userFieldManager->GetPublicView($arField);
                        }
                    } else {
                        $row['DISPLAY_FIELDS'][$fieldName] = $row[$fieldName];
                    }
                }
            }

            $rows[] = $row;
        }

        $arResult['ITEMS'] = $rows;

        $navComponentParameters = array();

        if ($arNavigation) {
            $arResult["NAV_STRING"] = $result->GetPageNavStringEx(
                $navComponentObject,
                $arParams["PAGER_TITLE"],
                $arParams["PAGER_TEMPLATE"],
                $arParams["PAGER_SHOW_ALWAYS"] === "Y",
                $this,
                $navComponentParameters
            );
            $arResult["NAV_CACHED_DATA"] = null;
        }

        $arResult["NAV_RESULT"] = $result;
        $arResult["NAV_PARAM"] = $navComponentParameters;

        $this->includeComponentTemplate();
    }

    protected function getResultFilter($arFilter)
    {
        $arParams = &$this->arParams;

        if ($arParams['ONLY_ACTIVE_RESULTS'] != 'N')
            $arFilter['ACTIVE'] = 'Y';

        if (
            isset($arParams['FILTER_NAME']) &&
            !empty($arParams['FILTER_NAME']) &&
            preg_match('/^[A-Za-z_][A-Za-z01-9_]*$/', $arParams['FILTER_NAME'])) {
            global ${$arParams['FILTER_NAME']};
            $filter = ${$arParams['FILTER_NAME']};
            if (is_array($filter)) {
                $arFilter = array_merge($arFilter, $filter);
            }
        }

        return $arFilter;
    }
}


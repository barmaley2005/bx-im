<?

namespace DevBx\Core\Admin;

IncludeModuleLangFile(__FILE__);

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;
use Bitrix\Main\Web;

class AdminList
{
    private $file = false;
    private $file_edit = false;
    private $arOptions = array();
    private $entity = false;
    private $module = false;
    private $entityAccess = false;
    /**
     * @var \CAdminList $lAdmin
     */
    private $lAdmin = null;
    private $arFilter = array();
    private $allFields = array();
    private $arFilterFieldsTitle = array();
    private $arFilterConfig = array();
    private $arFilterInput = array();
    private $sTableID = false;
    private $cbGetSelectFields = false;
    private $cbApplyFilter = false;
    private $arCbActions = array();
    private $arGroupAction = array();
    private $groupActionParams = array();
    private $epilog = false;
    private $actionGroupQueryParams = false; //$this->lAdmin->ActionDoGroup($arRes[$PRIMARY_KEY], "delete", $this->actionGroupAddParams)
    private $bShowFilterForm = true;
    private $defSortField = false;
    private $defSortOrder = "desc";
    private $cbUpdateFunction = false;
    private $userFields = array();

    public function __construct($module, $entity, $arOptions = array())
    {
        global $APPLICATION, $USER_FIELD_MANAGER;

        //__NAMESPACE__

        $debug_backtrace = debug_backtrace();

        $this->module = $module;

        if (!\Bitrix\Main\Loader::includeModule($this->module))
            throw new Main\SystemException('module not installed ' . $this->module);

        $file = basename($debug_backtrace[0]['file']);
        $file = preg_replace('#\(\d+\).+:.+eval\(\).+#', '', $file);

        $this->file = new Web\Uri($file);
        $this->file_edit = new Web\Uri(str_replace('list.php', 'edit.php', $file));

        $this->actionGroupAddParams = new Web\Uri('');

        if (is_a($entity, Entity\DataManager::class, true)) {
            $this->entity = $entity::getEntity();
        } else {
            if (!$entity instanceof Entity\Base) {
                throw new Main\SystemException('invalid class ' . $entity);
            }
            $this->entity = $entity;
        }

        $this->arOptions = $arOptions;

        $this->sTableID = "tbl_" . str_replace('\\', '_', $this->entity->getFullName());

        $this->defSortField = $this->entity->getPrimary();

        $this->entityAccess = $this->getOption('ENTITY_ACCESS', $APPLICATION->GetGroupRight($this->module));

        if ($this->entity->getUfId())
            $this->userFields = $USER_FIELD_MANAGER->GetUserFields($this->entity->getUfId(), 0, LANGUAGE_ID);

        $this->allFields = $this->getAllFields('', $this->entity);

        foreach ($this->allFields as $id => $arField)
            $this->arFilterFieldsTitle[$id] = $arField["TITLE"];

        $callbackActions = $this->getOption("ACTIONS");
        if (is_array($callbackActions))
            $this->arCbActions = $callbackActions;

        $groupActions = $this->getOption("GROUP_ACTION");
        if (is_array($groupActions))
            $this->arGroupAction = $groupActions;

        $this->arFilterConfig = $this->getFilterConfig();

        $this->initVisualFields();

        $this->setCallbackAction("delete", array($this, '__deleteElement'));
        $this->setCallbackAction("activate", array($this, '__activateElement'));
        $this->setCallbackAction("deactivate", array($this, '__activateElement'));
    }

    function getAllFields($prefix, $entity, $depth = 1, $refField = null, $parentFields = null, $maxDepth = 1)
    {
        $arResult = array();

        $arFields = $entity->getFields();

        $arIgnoreFields = array();

        if ($refField instanceof \Bitrix\Main\Entity\ReferenceField) {
            $reference = $refField->getReference();
            foreach ($reference as $key => $val) {
                if (is_string($val) && strpos($val, 'ref.') === 0 && strpos($key, '=this.') === 0) {
                    $arIgnoreFields[] = substr($val, 4);
                }
            }
        }

        $endFields = array();

        foreach ($this->userFields as $userField) {
            if ($userField['MULTIPLE'] == 'Y') {
                $arIgnoreFields[] = $userField['FIELD_NAME'] . '_SINGLE';
            }
        }

        foreach ($arFields as $key => $obField) {
            if ($obField instanceof \Bitrix\Main\Entity\ExpressionField) {
                if (strpos($obField->getExpression(), "count") !== false)
                    continue;
            }
            if ($obField instanceof \Bitrix\Main\Entity\ReferenceField) {
                if ($depth < $maxDepth)
                    $arResult = array_merge($arResult, $this->getAllFields($obField->getName() . '.', $obField->getRefEntityName(), $depth + 1, $obField, $arFields));
            } else {
                /*if ($prefix && $obField->isPrimary())
                    continue;*/

                if (in_array($obField->getName(), $arIgnoreFields))
                    continue;

                $arField = array(
                    'NAME' => $obField->getName(),
                    'TITLE' => $obField->getTitle(),
                    'SELECT' => $prefix . $key,
                    'EDITABLE' => strlen($prefix) == 0,
                    //'REFERENCE' => in_array($key, $arReference),
                    'FIELD' => $obField,
                );

                if ($entity->getFullName() == $this->entity->getFullName() && $depth == 1 && array_key_exists($key, $this->userFields)) {
                    $endFields[$key] = $arField;
                } else {
                    $arResult[str_replace('.', '_', $prefix . $key)] = $arField;
                }
            }
        }

        $arResult = array_merge($arResult, $endFields);

        return $arResult;
    }

    function getOption($key, $defaultValue = false)
    {
        if (isset($this->arOptions[$key]))
            return $this->arOptions[$key];
        return $defaultValue;
    }

    function getFilterConfig()
    {
        $arFilterFields = array();
        foreach ($this->allFields as $id => $arField) {
            if (
                $arField["FIELD"] instanceof Entity\IntegerField ||
                $arField["FIELD"] instanceof Entity\FloatField ||
                $arField["FIELD"] instanceof Entity\DateField ||
                $arField["FIELD"] instanceof Entity\DateTimeField
            ) {
                $arFilterFields[$id] = "RANGE";
            } else {
                $arFilterFields[$id] = "TEXT";
            }
        }

        return $arFilterFields;
    }

    protected function initVisualFields()
    {
        $arHeaderFields = [];
        if (!is_array($this->arOptions['HEADER_FIELDS']))
            $this->arOptions['HEADER_FIELDS'] = array_keys($this->allFields);

        foreach ($this->arOptions['HEADER_FIELDS'] as $key => $val) {
            if (is_numeric($key)) {
                $key = $val;
                $val = array();
            } else {
                if (!is_array($val)) {
                    $val = array();
                }
            }

            if (empty($val['TITLE'])) {
                if (array_key_exists($key, $this->userFields)) {
                    $arUserField = $this->userFields[$key];
                    $val['TITLE'] = $arUserField['LIST_COLUMN_LABEL'] ? $arUserField['LIST_COLUMN_LABEL'] : $arUserField['FIELD_NAME'];
                } elseif (array_key_exists($key, $this->allFields)) {
                    $val['TITLE'] = $this->allFields[$key]['TITLE'];
                }
            }

            $arHeaderFields[$key] = $val;
        }

        $this->arOptions['HEADER_FIELDS'] = $arHeaderFields;
        unset($arHeaderFields);

        foreach ($this->arFilterConfig as $id => $filterType) {

            $arField = $this->allFields[$id];

            if ($filterType == "RANGE" &&
                $arField["FIELD"] instanceof Entity\DateField) {
                $this->addFilterInput($id, array($this, 'showFilterDate'));
            } elseif ($filterType == "RANGE") {
                $this->addFilterInput($id, array($this, 'showFilterRange'));
            } elseif ($arField["FIELD"] instanceof Entity\BooleanField) {
                $this->addFilterInput($id, array($this, 'showFilterBoolean'));
            } elseif ($arField["FIELD"] instanceof Entity\EnumField) {
                $this->addFilterInput($id, array($this, 'showFilterEnum'));
            } else {
                if (array_key_exists($id, $this->userFields)) {
                    $arUserField = $this->userFields[$id];

                    if ($arUserField['SHOW_FILTER'] != 'N') {
                        $this->setFilterFieldTitle($id, $arUserField['LIST_FILTER_LABEL'] ? $arUserField['LIST_FILTER_LABEL'] : $arUserField['FIELD_NAME']);

                        $cb = new ListUserType($arUserField);

                        $this->addFilterInput($id, array($cb, 'showFilter'));
                    } else {
                        unset($this->arFilterConfig[$id]);
                    }
                } else {
                    $this->addFilterInput($id, array($this, 'showFilterString'));
                }
            }
        }

        foreach ($this->userFields as $id => $arUserField) {
            if (isset($this->arOptions['ROW_VIEW'][$id])) {
                continue;
            }

            $cb = new ListUserType($arUserField);

            if (!isset($this->arOptions['HEADER_FIELDS'][$id])) {
                if (!in_array($id, $this->arOptions['HEADER_FIELDS'])) {
                    $this->arOptions['HEADER_FIELDS'][$id] = array(
                        'TITLE' => $arUserField['LIST_COLUMN_LABEL'] ? $arUserField['LIST_COLUMN_LABEL'] : $arUserField['FIELD_NAME']
                    );
                }
            }

            $this->arOptions['ROW_VIEW'][$id] = array($cb, 'rowView');

            if (is_callable(array($arUserField['USER_TYPE']['CLASS_NAME'], 'getAdminListEditHTML')) && $arUserField['EDIT_IN_LIST'] == 'Y') {
                $this->arOptions['EDIT_FIELDS'][$id] = array($cb, 'editField');
            }

            if ($arUserField['EDIT_IN_LIST'] == 'N') {
                $this->arOptions['READ_ONLY_FIELDS'][] = $id;
            }
        }
    }

    public function addFilterInput($id, $callback)
    {
        if (!is_string($callback) && is_callable($callback))
            $this->arFilterInput[$id] = $callback;
    }

    public function setFilterFieldTitle($id, $title)
    {
        $this->arFilterFieldsTitle[$id] = $title;
        return $this;
    }

    public function setCallbackAction($action, $callback)
    {
        if (!is_string($callback) && is_callable($callback))
            $this->arCbActions[$action] = $callback;
        return $this;
    }

    function getFile()
    {
        return $this->file;
    }

    function getFileEdit()
    {
        return $this->file_edit;
    }

    function setFileEdit($file_edit)
    {
        if ($file_edit instanceof Web\Uri) {
            $this->file_edit = clone $file_edit;
        } else {
            $this->file_edit = new \Bitrix\Main\Web\Uri($file_edit);
        }

        return $this;
    }

    function getD7Class()
    {
        return $this->entity->getDataClass();
    }

    public function removeFilterField($name)
    {
        if (array_key_exists($name, $this->arFilterConfig))
            unset($this->arFilterConfig[$name]);
        return $this;
    }

    public function setFilterFieldType($name, $type)
    {
        if (array_key_exists($name, $this->arFilterConfig))
            $this->arFilterConfig[$name] = $type;
        return $this;
    }

    public function setFilterFields($arFields)
    {
        $arConfig = array();
        foreach ($arFields as $key => $val) {
            if (is_numeric($key)) {
                $key = $val;
                $val = "TEXT";
            }
            $arConfig[$key] = $val;
        }
        $this->arFilterConfig = $arConfig;
        return $this;
    }

    public function getFilterFieldTitle($id)
    {
        return $this->arFilterFieldsTitle[$id];
    }

    public function getFilter()
    {
        return $this->arFilter;
    }

    public function setTableId($id)
    {
        $this->sTableID = $id;
        return $this;
    }

    public function setCallbackGetSelectFields($callback)
    {
        if (!is_string($callback) && is_callable($callback))
            $this->cbGetSelectFields = $callback;
        return $this;
    }

    public function setCallbackApplyFilter($callback)
    {
        if (!is_string($callback) && is_callable($callback))
            $this->cbApplyFilter = $callback;
        return $this;
    }

    public function addGroupAction($arGroupAction)
    {
        $this->arGroupAction = array_merge($this->arGroupAction, $arGroupAction);
        return $this;
    }

    public function setGroupActionParams($arParams)
    {
        $this->groupActionParams = $arParams;
        return $this;
    }

    public function setEpilog($epilog)
    {
        $this->epilog = $epilog;
        return $this;
    }

    public function getActionGroupQueryParams()
    {
        return $this->actionGroupAddParams;
    }

    public function addActionGroupQueryParams($add_params)
    {
        if (!is_array($add_params)) {
            parse_str($add_params, $add_params);
        }

        $this->actionGroupAddParams->addParams($add_params);

        return $this;
    }

    public function addFileEditParams($add_params)
    {
        if (!is_array($add_params)) {
            parse_str($add_params, $add_params);
        }

        $this->file_edit->addParams($add_params);
    }

    public function getEntityAccess()
    {
        return $this->entityAccess;
    }

    public function setShowFilterForm($val)
    {
        $this->bShowFilterForm = $val;
    }

    public function setOrder($field, $order)
    {
        $this->defSortField = $field;
        $this->defSortOrder = $order;
    }

    public function setUpdateFunction($callback)
    {
        if (!is_string($callback) && is_callable($callback))
            $this->cbUpdateFunction = $callback;
    }

    function showFilterDate(AdminList $list, $id, $arFilterValues)
    {
        $arField = $this->allFields[$id];

        ?>
        <tr>
            <td><?= $this->arFilterFieldsTitle[$id] ?></td>
            <td>
                <input name="filter_<?= $id ?>_FROM"
                       value="<?= htmlspecialcharsbx($arFilterValues[$id . "_FROM"]) ?>">
                <?= \CAdminCalendar::Calendar('filter_' . $id . '_FROM', '', '', $arField["FIELD"] instanceof Entity\DatetimeField) ?>
                <input name="filter_<?= $id ?>_TO"
                       value="<?= htmlspecialcharsbx($arFilterValues[$id . "_TO"]) ?>">
                <?= \CAdminCalendar::Calendar('filter_' . $id . '_TO', '', '', $arField["FIELD"] instanceof Entity\DatetimeField) ?>
            </td>
        </tr>
        <?
    }

    function showFilterRange(AdminList $list, $id, $arFilterValues)
    {
        $arField = $this->allFields[$id];

        ?>
        <tr>
            <td><?= $this->arFilterFieldsTitle[$id] ?></td>
            <td>
                <input name="filter_<?= $id ?>_FROM"
                       value="<?= htmlspecialcharsbx($arFilterValues[$id . "_FROM"]) ?>">
                <input name="filter_<?= $id ?>_TO"
                       value="<?= htmlspecialcharsbx($arFilterValues[$id . "_TO"]) ?>">
            </td>
        </tr>
        <?
    }

    function showFilterBoolean(AdminList $list, $id, $arFilterValues)
    {
        $arField = $this->allFields[$id];
        $values = $arField["FIELD"]->getValues();

        ?>
        <tr>
            <td><?= $this->arFilterFieldsTitle[$id] ?></td>
            <td>
                <select name="filter_<?= $id ?>">
                    <option
                            value=""><?= GetMessage("DEVBX_ADMIN_LIST_FILTER_VALUE_NOT_SETTED") ?></option>
                    <option
                            value="<?= $values[0] ?>"<? if ($values[0] == $arFilterValues[$id]): ?> selected="selected" <? endif ?>><?= GetMessage("DEVBX_ADMIN_LIST_BOOL_FALSE") ?></option>
                    <option
                            value="<?= $values[1] ?>"<? if ($values[1] == $arFilterValues[$id]): ?> selected="selected" <? endif ?>><?= GetMessage("DEVBX_ADMIN_LIST_BOOL_TRUE") ?></option>
                </select>
            </td>
        </tr>
        <?
    }

    function showFilterEnum(AdminList $list, $id, $arFilterValues)
    {
        $arField = $this->allFields[$id];
        $values = $arField["FIELD"]->getValues();

        if (is_numeric(key($values)))
        {
            $values = array_combine($values, $values);
        }

        ?>
        <tr>
            <td><?= $this->arFilterFieldsTitle[$id] ?></td>
            <td>
                <select name="filter_<?= $id ?>">
                    <option
                            value=""><?= GetMessage("DEVBX_ADMIN_LIST_FILTER_VALUE_NOT_SETTED") ?></option>
                    <? foreach ($values as $key => $val): ?>
                        <option
                                value="<?= htmlspecialcharsbx($key) ?>"<? if ($key == $arFilterValues[$id]): ?> selected="selected" <? endif ?>><?= htmlspecialcharsbx($val) ?></option>
                    <? endforeach ?>
                </select>
            </td>
        </tr>
        <?
    }

    function showFilterString(AdminList $list, $id, $arFilterValues)
    {
        $arField = $this->allFields[$id];

        ?>
        <tr>
            <td><?= $this->arFilterFieldsTitle[$id] ?></td>
            <td>
                <select name="filter_type_<?= $id ?>">
                    <option
                            value="substring"<? if ($arFilterValues["type_" . $id] == "substring"): ?> selected="selected" <? endif ?>><?= Loc::getMessage("DEVBX_ADMIN_LIST_FILTER_SUBSTRING") ?></option>
                    <option
                            value="exact"<? if ($arFilterValues["type_" . $id] == "exact"): ?> selected="selected" <? endif ?>><?= Loc::getMessage("DEVBX_ADMIN_LIST_FILTER_EXACT") ?></option>
                    <option
                            value="null"<? if ($arFilterValues["type_" . $id] == "null"): ?> selected="selected" <? endif ?>><?= Loc::getMessage("DEVBX_ADMIN_LIST_FILTER_NULL") ?></option>
                </select>
                <input name="filter_<?= $id ?>" value="<?= htmlspecialcharsbx($arFilterValues[$id]) ?>">
            </td>
        </tr>
        <?
    }

    public function display($subList = false, $subListUrl = '')
    {
        global $APPLICATION, $adminPage, $USER, $adminMenu, $adminChain, $by, $order, $USER_FIELD_MANAGER;

        if ($this->entityAccess < "R") {
            $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
            return;
        }

        $request = \Bitrix\Main\Context::getCurrent()->getRequest();

        if ($subList)
            require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/iblock/classes/general/subelement.php');

        if ($request->get("show_fields_key") == "y") {
            (new \CAdminMessage(array(
                "MESSAGE" => print_r(array_keys($this->allFields), true),
                "TYPE" => "OK",
            )))->Show();
        }

        \CAdminCalendar::ShowScript();

        $PRIMARY_KEY = $this->entity->getPrimary();

        if ($this->newInterface()) {
            $oSort = new \CAdminUiSorting($this->sTableID, $this->defSortField, $this->defSortOrder);
            $this->lAdmin = new \CAdminUiList($this->sTableID, $oSort);
        } else {
            if ($subList)
                $oSort = new \CAdminSubSorting($this->sTableID, $this->defSortField, $this->defSortOrder, "by", "order", $subListUrl); else
                $oSort = new \CAdminSorting($this->sTableID, $this->defSortField, $this->defSortOrder);

            if ($subList)
                $this->lAdmin = new \CAdminSubList($this->sTableID, $oSort, $subListUrl); else
                $this->lAdmin = new \CAdminList($this->sTableID, $oSort);
        }

        $this->lAdmin->bMultipart = true;

        $arFilterFields = array();
        $arFilterValues = array();
        foreach ($this->arFilterConfig as $id => $val) {
            if ($val == "RANGE") {
                $arFilterFields[] = "filter_" . $id . '_FROM';
                $arFilterFields[] = "filter_" . $id . '_TO';
            } elseif (is_array($val)) {
                foreach ($val as $v)
                    $arFilterFields[] = "filter_" . $v;
            } else {
                $arFilterFields[] = "filter_" . $id;
                $arFilterFields[] = "filter_type_" . $id;
            }
        }

        if ($this->newInterface()) {
            $this->lAdmin->AddFilter($this->getFilterFields(), $this->arFilter);
        } else {
            $this->lAdmin->InitFilter($arFilterFields);

            foreach ($this->arFilterConfig as $id => $val) {
                if ($val == "RANGE") {
                    $arFilterValues[$id . '_FROM'] = $GLOBALS["filter_" . $id . '_FROM'];
                    $arFilterValues[$id . '_TO'] = $GLOBALS["filter_" . $id . '_TO'];
                } else {
                    $arFilterValues[$id] = $GLOBALS["filter_" . $id];
                    $arFilterValues["type_" . $id] = $GLOBALS["filter_type_" . $id];
                }
            }

            /* ˜˜˜˜˜˜˜˜ ˜˜˜˜˜˜˜˜ ˜˜˜˜˜˜˜, ˜˜˜˜˜ Unescape ˜ ˜˜˜˜˜˜˜ InitFilter */


            if (!isset($GLOBALS["del_filter"])) {

                foreach ($this->arFilterConfig as $id => $filterType) {

                    if (array_key_exists($id, $this->userFields))
                        continue;

                    if (!isset($this->allFields[$id]))
                        continue;

                    $arField = $this->allFields[$id];

                    $filterName = $arField["SELECT"];

                    if ($filterType == "RANGE") {
                        if ($arField["FIELD"] instanceof Entity\DateField) {
                            $filter_from = $GLOBALS["filter_" . $id . '_FROM'];
                            $filter_to = $GLOBALS["filter_" . $id . '_TO'];

                            if (strlen($filter_from) > 0 && Type\DateTime::isCorrect($filter_from))
                                $this->arFilter[">=" . $filterName] = $filter_from;

                            if (strlen($filter_to) > 0 && Type\DateTime::isCorrect($filter_to))
                                $this->arFilter["<=" . $filterName] = $filter_to;
                        } else {
                            $filter_from = $GLOBALS["filter_" . $id . '_FROM'];
                            $filter_to = $GLOBALS["filter_" . $id . '_TO'];

                            if (strlen($filter_from) > 0)
                                $this->arFilter[">=" . $filterName] = $filter_from;

                            if (strlen($filter_to) > 0)
                                $this->arFilter["<=" . $filterName] = $filter_to;
                        }
                    } else {
                        if ($GLOBALS["filter_type_" . $id] == "null") {
                            $this->arFilter["=" . $filterName] = false;
                            continue;
                        }
                        $filter = $GLOBALS["filter_" . $id];
                        if (!is_array($filter) && strlen($filter) <= 0)
                            continue;

                        if ($arField["FIELD"] instanceof Entity\DateField) {
                            $this->arFilter["=" . $filterName] = $filter;
                        } elseif ($arField["FIELD"] instanceof Entity\StringField) {

                            if ($GLOBALS["filter_type_" . $id] == "exact")
                                $this->arFilter["=" . $filterName] = $filter;
                            else
                                $this->arFilter["%" . $filterName] = $filter;
                        } else
                            $this->arFilter["=" . $filterName] = $filter;
                    }
                }

                if ($this->entity->getUfId())
                    $USER_FIELD_MANAGER->AdminListAddFilter($this->entity->getUfId(), $this->arFilter);
            }
        }

        if (!is_string($this->cbApplyFilter) && is_callable($this->cbApplyFilter))
            call_user_func_array($this->cbApplyFilter, array(&$this->arFilter, &$arFilterValues));

        $this->processActions();

        $arHeader = array();

        $arHeaderFields = $this->getOption('HEADER_FIELDS');
        if (!is_array($arHeaderFields))
            $arHeaderFields = array_keys($this->allFields);

        $event = new \Bitrix\Main\Event("devbx.core", "OnAdminListGetHeader", array("ENTITY" => $this, "HEADER" => &$arHeaderFields));
        $event->send();

        foreach ($arHeaderFields as $key => $val) {
            if (is_numeric($key))
                $key = $val;

            if (is_array($val) && $val["CUSTOM"]) {
                $arHeader[] = array(
                    "id" => $key,
                    "content" => $val['TITLE'],
                    "default" => true,
                );
            } else {
                $arField = $this->allFields[$key];

                $header = array(
                    "id" => $key,
                );

                $header["content"] = (is_array($val) && !empty($val["TITLE"]) ? $val["TITLE"] : ($arField ? $arField["TITLE"] : ''));
                $header["default"] = (is_array($val) && isset($val["DEFAULT"]) ? $val["DEFAULT"] : true);
                $header["sort"] = $key;

                $arHeader[] = $header;
            }
        }

        $this->lAdmin->AddHeaders($arHeader);

        $arSelect = array();

        foreach ($this->allFields as $key => $arField)
            $arSelect[$key] = $arField["SELECT"];

        $arRuntime = array();

        if (!is_string($this->cbGetSelectFields) && is_callable($this->cbGetSelectFields))
            call_user_func_array($this->cbGetSelectFields, array(&$arSelect, &$arRuntime));

        $event = new \Bitrix\Main\Event("devbx.core", "OnAdminListGetSelect", array("ENTITY" => $this, "SELECT" => &$arSelect, "RUNTIME" => &$arRuntime));
        $event->send();


        /* @var \Bitrix\Main\Entity\Query $query */
        $query = $this->entity->getDataClass()::query();

        foreach ($arRuntime as $name => $fieldInfo)
            $query->registerRuntimeField($name, $fieldInfo);

        $query->setSelect($arSelect);

        $query->setFilter($this->arFilter);

        $arOrder = array();

        if (strlen($by) > 1 && strlen($order) > 1) {
            try {
                if (!$query->getRegisteredChain($by, true)) {
                    $by = $this->defSortField;
                }
            } catch (Main\SystemException $e)
            {
                $by = $this->defSortField;
            }

            if ($by) {
                $arOrder = array($by => $order);
            }
        }

        $query->setOrder($arOrder);

        if ($request->get("ext_excel_export") == "Y") {
            $APPLICATION->RestartBuffer();

            $dbRes = $query->exec();

            $fname = basename($APPLICATION->GetCurPage(), ".php");
            $fname = str_replace(array("\r", "\n"), "", $fname);
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: filename=" . $fname . ".xls");

            echo '
		<html>
		<head>
		<title>' . $this->getOption("TITLE") . '</title>
		<meta http-equiv="Content-Type" content="text/html; charset=' . LANG_CHARSET . '">
		<style>
			td {mso-number-format:\@;}
			.number0 {mso-number-format:0;}
			.number2 {mso-number-format:Fixed;}
		</style>
		</head>
		<body>';

            echo "<table border=\"1\">";
            echo "<tr>";

            foreach ($this->lAdmin->aVisibleHeaders as $header) {
                echo '<td>';
                echo $header["content"];
                echo '</td>';
            }
            echo "</tr>";

            while ($arRes = $dbRes->fetch()) {
                echo "<tr>";
                foreach ($this->lAdmin->aVisibleHeaders as $id => $header_props) {
                    echo '<td>';
                    echo htmlspecialcharsex($arRes[$id]);
                    echo '</td>';
                    /*
                    $field = $row->aFields[$id];
                    if(!is_array($row->arRes[$id]))
                        $val = trim($row->arRes[$id]);
                    else
                        $val = $row->arRes[$id];

                    switch($field["view"]["type"])
                    {
                        case "checkbox":
                            if($val=='Y')
                                $val = htmlspecialcharsex(GetMessage("admin_lib_list_yes"));
                            else
                                $val = htmlspecialcharsex(GetMessage("admin_lib_list_no"));
                            break;
                        case "select":
                            if($field["edit"]["values"][$val])
                                $val = htmlspecialcharsex($field["edit"]["values"][$val]);
                            break;
                        case "file":
                            $arFile = \CFile::GetFileArray($val);
                            if(is_array($arFile))
                                $val = htmlspecialcharsex(\CHTTP::URN2URI($arFile["SRC"]));
                            else
                                $val = "";
                            break;
                        case "html":
                            $val = trim(strip_tags($field["view"]['value'], "<br>"));
                            break;
                        default:
                            $val = htmlspecialcharsex($val);
                            break;
                    }

                    echo '<td';
                    if ($header_props['align'])
                        echo ' align="'.$header_props['align'].'"';
                    if ($header_props['valign'])
                        echo ' valign="'.$header_props['valign'].'"';
                    if (preg_match("/^([0-9]+|[0-9]+[.,][0-9]+)\$/", $val))
                        echo ' style="mso-number-format:0"';
                    echo '>';
                    echo ($val<>""? $val: '&nbsp;');
                    echo '</td>';*/
                }
                echo "</tr>";
            }

            echo "</table>";
            echo '</body></html>';

            require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin_after.php");
            die();
        }

        /*
        $dbRes = call_user_func(array($this->d7class, 'getList'), array(
            'select' => $arSelect,
            'runtime' => $arRuntime,
            'filter' => $this->arFilter,
            'order' => $arOrder)
        );
        */

        if ($this->newInterface()) {
            $navResult = new \CAdminUiResult(null, '');
        } else {
            $navResult = new \CAdminResult(null, '');
        }

        $arNavParams = array("nPageSize" => $navResult->GetNavSize($this->sTableID,
            array('nPageSize' => 20)));
        unset($navResult);

        if (method_exists($query, 'queryCountTotal')) {
            $cnt = $query->queryCountTotal();
        } else {
            $cQuery = clone $query;
            $cQuery->registerRuntimeField('', new Entity\ExpressionField('DEVBX_QUERY_TOTAL', 'count(1)'));
            $cQuery->setSelect(['DEVBX_QUERY_TOTAL']);
            $cQuery->setOrder([]);
            $cnt = intval($cQuery->exec()->fetch()['DEVBX_QUERY_TOTAL']);
        }

        $dbRes = new \CDBResult();
        $dbRes->NavQuery($query->getQuery(), $cnt, $arNavParams);

        if ($this->newInterface()) {
            $rsData = new \CAdminUiResult($dbRes, $this->sTableID);
        } else {
            if ($subList)
                $rsData = new \CAdminSubResult($dbRes, $this->sTableID, $this->lAdmin->GetListUrl(true)); else
                $rsData = new \CAdminResult($dbRes, $this->sTableID);
        }

        $rsData->NavStart();

        $this->lAdmin->NavText($rsData->GetNavPrint($this->getOption('NAV_TEXT', GetMessage('DEVBX_ADMIN_LIST_DEFAULT_NAV_TEXT'))));

        $arRowView = $this->getOption('ROW_VIEW', array());
        $rowActions = $this->getOption('ROW_ACTIONS');
        $rowActionsEvents = array(); //˜˜˜˜˜˜˜˜˜ ˜˜ ˜˜˜˜˜˜˜ OnAdminListGetActions

        $event = new \Bitrix\Main\Event("devbx.core", "OnAdminListGetRowView", array("ENTITY" => $this));
        $event->send();
        if ($event->getResults()) {
            foreach ($event->getResults() as $eventResult) {
                if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                    $handlerRes = $eventResult->getParameters();
                    $arRowView = array_merge($arRowView, $handlerRes);
                }
            }
        }

        foreach ($arRowView as $key => $value) {
            if (is_numeric($key) && is_array($value) && count($value) > 1) {
                $cb = end($value);
                if (!is_callable($cb)) {
                    throw new Main\SystemException('last element in ROW_VIEW not callable');
                }

                foreach ($value as $field) {
                    if ($field == $cb)
                        break;

                    $arRowView[$field] = $cb;
                }

                unset($arRowView[$key]);
            }
        }

        $arEditFields = $this->getOption("EDIT_FIELDS");

        foreach ($arEditFields as $key => $value) {
            if (is_numeric($key) && is_array($value) && count($value) > 1) {
                $cb = end($value);
                if (!is_callable($cb)) {
                    throw new Main\SystemException('last element in EDIT_FIELDS not callable');
                }

                foreach ($value as $field) {
                    if ($field == $cb)
                        break;

                    $arEditFields[$field] = $cb;
                }

                unset($arEditFields[$key]);
            }
        }


        $arReadOnly = $this->getOption("READ_ONLY_FIELDS");
        if (!is_array($arReadOnly))
            $arReadOnly = array();


        $event = new \Bitrix\Main\Event("devbx.core", "OnAdminListGetActions", array("ENTITY" => $this));
        $event->send();
        if ($event->getResults()) {
            foreach ($event->getResults() as $eventResult) {
                if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                    $handlerRes = $eventResult->getParameters();
                    foreach ($handlerRes as $val) {
                        $rowActionsEvents[] = $val;
                    }
                }
            }
        }

        $bAllowEdit = $this->getOption("ALLOW_EDIT", "N") == "Y";
        $bAllowDelete = $this->getOption("ALLOW_DELETE", "N") == "Y";
        $bAllowActivate = $this->getOption("ALLOW_ACTIVATE", "N") == "Y";

        $fileEditUri = clone $this->file_edit;
        $showSubListFuncName = 'ShowSubListEditDialog';

        $fileEditUri->addParams(array('lang' => LANG));
        if ($subList) {
            $subListId = $this->getOption('SUBLIST_ID', $this->sTableID);
            $fileEditUri->addParams(array('SUBLIST_ID' => $subListId));
            $showSubListFuncName = 'ShowSubListEditDialog' . md5($subListId);
        }

        $cnt = 0;

        while ($arRes = $rsData->NavNext()) {
            $cnt++;
            $row = $this->lAdmin->AddRow($arRes[$PRIMARY_KEY], $arRes);

            //˜˜˜˜˜˜˜ ˜˜˜˜˜˜ ˜˜ ˜˜˜˜˜˜˜˜˜˜˜˜˜ ˜˜˜˜˜˜˜˜˜˜
            if (is_array($arRowView)) {
                $arSearch = array();
                $arReplace = array();
                foreach ($arRes as $key => $val) {
                    $arSearch[] = '#' . $key . '#';
                    $arReplace[] = $val;
                }

                foreach ($arRowView as $key => $view) {
                    if (!is_string($view) && is_callable($view)) {
                        call_user_func($view, $row, $arRes[$PRIMARY_KEY], $key, $arRes); // function ($row,$primary,$key,$arRes)
                    } else {
                        $row->AddViewField($key, str_replace($arSearch, $arReplace, $view));
                    }
                }
            }

            //˜˜˜˜˜˜˜ ˜˜˜˜˜˜ ˜˜˜ ˜˜˜˜˜˜˜ ˜˜ ˜˜˜˜˜˜˜ ˜ options->ROW_VIEW
            foreach ($arRes as $key => $val) {
                if (is_array($arRowView) && array_key_exists($key, $arRowView))
                    continue;
                if (array_key_exists($key, $this->allFields)) {
                    $arField = $this->allFields[$key];
                    if ($arField["FIELD"] instanceof Entity\BooleanField) {
                        $values = $arField["FIELD"]->getValues();

                        if (empty($val)) {
                            $defValue = $arField["FIELD"]->getDefaultValue();
                            if (!empty($defValue))
                                $val = $defValue;
                        }

                        if ($val == $values[0])
                            $row->AddViewField($key, GetMessage("DEVBX_ADMIN_LIST_BOOL_FALSE"));
                        if ($val == $values[1])
                            $row->AddViewField($key, GetMessage("DEVBX_ADMIN_LIST_BOOL_TRUE"));
                    } elseif ($arField["FIELD"] instanceof Entity\EnumField) {
                        $arEnumValues = $arField["FIELD"]->getValues();
                        if (is_numeric(key($arEnumValues)))
                        {
                            $arEnumValues = array_combine($arEnumValues, $arEnumValues);
                        }

                        if (array_key_exists($val, $arEnumValues))
                            $row->AddViewField($key, $arEnumValues[$val]);
                    }
                }
            }

            if ($bAllowEdit) {

                //˜˜˜˜˜˜˜ ˜˜˜˜˜˜ ˜˜˜ ˜˜˜˜˜˜˜˜˜˜˜˜˜˜
                foreach ($arRes as $key => $val) {
                    if (in_array($key, $arReadOnly))
                        continue;

                    if (array_key_exists($key, $this->allFields)) {
                        $arField = $this->allFields[$key];

                        if ($arField["FIELD"]->isPrimary() || !$arField["EDITABLE"])
                            continue;

                        $attributes = array();
                        if ($arEditFields && array_key_exists($key, $arEditFields)) {
                            $customEdit = $arEditFields[$key];
                            if (!is_string($customEdit) && is_callable($customEdit)) {
                                call_user_func($customEdit, $row, $arRes[$PRIMARY_KEY], $key, $arRes);
                                continue;
                            }
                            if (is_array($customEdit))
                                $attributes = $customEdit;
                        }

                        if ($arField["FIELD"] instanceof Entity\BooleanField) {
                            $row->AddCheckField($key);
                        } elseif ($arField["FIELD"] instanceof Entity\DateField ||
                            $arField["FIELD"] instanceof Entity\DatetimeField
                        ) {
                            $row->AddCalendarField($key);
                        } elseif ($arField["FIELD"] instanceof Entity\TextField) {
                            if (!isset($attributes["rows"]))
                                $attributes["rows"] = 5;
                            if (!isset($attributes["cols"]))
                                $attributes["cols"] = 30;
                            $row->AddEditField($key, '<textarea rows="' . $attributes["rows"] . '" cols="' . $attributes["cols"] . '" name="FIELDS[' . $arRes["$PRIMARY_KEY"] . '][' . $key . ']">' . htmlspecialcharsbx($row->arRes[$key]) . '</textarea>');
                        } elseif ($arField["FIELD"] instanceof Entity\StringField) {
                            if (!isset($attributes["size"]))
                                $attributes["size"] = 20;
                            $row->AddInputField($key, array("size" => $attributes["size"]));
                        } elseif ($arField["FIELD"] instanceof Entity\IntegerField ||
                            $arField["FIELD"] instanceof Entity\FloatField
                        ) {
                            if (!isset($attributes["size"]))
                                $attributes["size"] = 10;
                            $row->AddInputField($key, array("size" => $attributes["size"]));
                        } elseif ($arField["FIELD"] instanceof Entity\EnumField) {
                            $arEnumValues = $arField["FIELD"]->getValues();
                            if (is_numeric(key($arEnumValues)))
                            {
                                $arEnumValues = array_combine($arEnumValues, $arEnumValues);

                            }
                            $row->AddSelectField($key, $arEnumValues);
                        }
                    }
                }
            }


            $arActions = array();


            if ($bAllowEdit && $this->entityAccess >= "W") {

                if ($subList) {

                    /*
                    $arActions[] = array(
                        'ICON' => 'edit',
                        'TEXT' => GetMessage("MAIN_ADMIN_MENU_EDIT"),
                        'ACTION' => "(new CAdminDialog({
				'content_url': '" . \CUtil::JSEscape((clone $this->file_edit)->addParams(array($PRIMARY_KEY => $arRes[$PRIMARY_KEY]))->getUri()) . "',
				'content_post': 'bxpublic=Y',
				'draggable': true,
				'resizable': true,
				'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
			})).Show();",
                        'DEFAULT' => true
                    );*/

                    $arActions['edit'] = array(
                        'ICON' => 'edit',
                        'TEXT' => GetMessage("MAIN_ADMIN_MENU_EDIT"),
                        'ACTION' => $showSubListFuncName . "('" . \CUtil::JSEscape($arRes[$PRIMARY_KEY]) . "');",
                        'DEFAULT' => true
                    );
                } else {
                    $arActions['edit'] = array(
                        "ICON" => "edit",
                        "DEFAULT" => true,
                        "TEXT" => GetMessage("MAIN_ADMIN_MENU_EDIT"),
                        "ACTION" => $this->lAdmin->ActionRedirect((clone($fileEditUri))->addParams(array($PRIMARY_KEY => $arRes[$PRIMARY_KEY]))->getUri())
                    );
                }
            }

            if ($bAllowDelete && $this->entityAccess >= "W") {
                $arActions['delete'] = array(
                    "ICON" => "delete",
                    "TEXT" => GetMessage("MAIN_ADMIN_MENU_DELETE"),
                    "ACTION" => "if(confirm('" . GetMessage('DEVBX_ADMIN_LIST_DELETE_CONF') . "')) " . $this->lAdmin->ActionDoGroup($arRes[$PRIMARY_KEY], "delete", $this->actionGroupAddParams->getQuery())
                );
            }

            if (!is_string($rowActions) && is_callable($rowActions)) {
                call_user_func_array($rowActions, array($this, $arRes, &$arActions));
            }

            foreach ($rowActionsEvents as $action) {
                if (!is_string($action) && is_callable($action)) {
                    call_user_func_array($rowActions, array($this, $arRes, &$arActions));
                } else {
                    if (!empty($action))
                        $arActions[] = $action;
                }
            }

            $row->AddActions($arActions);
        }

        $this->lAdmin->AddFooter(
            array(
                array(
                    "title" => $this->getOption("FOOTER_TOTAL_COUNT", GetMessage("MAIN_ADMIN_LIST_SELECTED")),
                    "value" => $rsData->SelectedRowsCount()
                ),
                array(
                    "counter" => true,
                    "title" => $this->getOption("FOOTER_SELECTED", GetMessage("MAIN_ADMIN_LIST_CHECKED")),
                    "value" => "0"
                ),
            )
        );

        $arDefaultGroupAction = array();

        if ($bAllowEdit)
            $arDefaultGroupAction["edit"] = GetMessage("MAIN_ADMIN_LIST_EDIT");

        if ($bAllowDelete)
            $arDefaultGroupAction["delete"] = GetMessage("MAIN_ADMIN_LIST_DELETE");

        if ($bAllowActivate) {
            $arDefaultGroupAction["activate"] = GetMessage("MAIN_ADMIN_LIST_ACTIVATE");
            $arDefaultGroupAction["deactivate"] = GetMessage("MAIN_ADMIN_LIST_DEACTIVATE");
        }

        $this->lAdmin->AddGroupActionTable(array_merge($arDefaultGroupAction, $this->arGroupAction), $this->groupActionParams);

        $arDefaultContext = array();
        if ($this->getOption("ALLOW_ADD", "N") == "Y") {

            if ($subList) {
                $addUrl = "javascript:" . $showSubListFuncName . "()";
            } else {
                $addUrl = (clone $fileEditUri)->addParams(array('lang' => LANG))->getUri();
            }

            $arDefaultContext['add'] = array(
                "TEXT" => GetMessage('MAIN_ADD'),
                "ICON" => "btn_new",
                "LINK" => $addUrl,
            );
        }

        $arContextMenu = array_merge($arDefaultContext, $this->getOption("CONTEXT_MENU", array()));
        foreach ($arContextMenu as $key => $val) {
            if (!is_string($val) && is_callable($val))
                $arContextMenu[$key] = call_user_func($val, $this);
        }

        if ($this->getOption("ALLOW_SMART_EXCEL", "N") == "Y") {
            $arContextMenu[] = array(
                "TEXT" => Loc::getMessage("DEVBX_ADMIN_LIST_EXCEL_EXPORT"),
                "NEW_BAR" => true,
                "ICON" => "btn_desktop_gadgets",
                "LINK" => $APPLICATION->GetCurPageParam("ext_excel_export=Y", array("ext_excel_export")),
            );
        }

        $this->lAdmin->AddAdminContextMenu($arContextMenu);

        $ikey = EventManager::getInstance()->addEventHandler('main', 'OnAdminListDisplay', function () {
            ?>
            <script type="text/javascript">
                var topWindow = (window.BX || window.parent.BX).PageObject.getRootWindow();
                var BX = topWindow.BX;
            </script>
            <?
        });

        $this->lAdmin->BeginEpilogContent();

        ?>
        <input type="hidden" name="table_id" value="<?= htmlspecialchars($this->lAdmin->table_id) ?>">
        <?

        if ($this->epilog !== false) {
            if (!is_string($this->epilog) && is_callable($this->epilog)) {
                call_user_func($this->epilog);
            } else {
                echo $this->epilog;
            }
        }

        ?>
        <script type="text/javascript">

            function <?=$showSubListFuncName?>(id) {
                let url = '<?=\CUtil::JSEscape($fileEditUri->getUri())?>',
                    params = {};

                if (typeof id !== 'undefined') {
                    params['<?=\CUtil::JSEscape($PRIMARY_KEY)?>'] = id;
                }

                console.log(url);

                (new BX.CAdminDialog({
                    content_url: BX.util.add_url_param(url, params),
                    content_post: 'bxpublic=Y',
                    draggable: true,
                    resiable: true,
                    buttons: [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
                })).Show();
            }

            <?
            if ($subList && (!$request->offsetExists('ajaxreload') && !$request->isPost()))
            {
            ?>
            BX.addCustomEvent('DevBxReloadSubList', function (params) {

                if (params.data.SUBLIST_ID === '<?=\CUtil::JSEscape($this->getOption('SUBLIST_ID', $this->sTableID))?>' ||
                    params.data.ENTITY_NAME === '<?=\CUtil::JSEscape($this->entity->getFullName())?>'
                ) {
                    <?
                    echo $this->lAdmin->ActionAjaxReload((new Web\Uri($this->lAdmin->GetListUrl(true)))->addParams(['ajaxreload' => 'y'])->getUri());
                    ?>
                }
            });
            <?
            }
            ?>

        </script>
        <?

        $this->lAdmin->EndEpilogContent();

        if ($subList) {
            $this->lAdmin->__AddListUrlParams('table_id', $this->lAdmin->table_id);
        }

        if ($request->isPost() && $request->get('table_id') == $this->lAdmin->table_id) {
            $this->lAdmin->CheckListMode();
        }

        if (!$request->isPost()) {
            $this->lAdmin->CheckListMode();
        }

        EventManager::getInstance()->removeEventHandler('main', 'OnAdminListDisplay', $ikey);

        if (!$subList) {
            $APPLICATION->SetTitle($this->getOption("TITLE"));
            require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
        }

        if ($this->bShowFilterForm) {

            $this->displayFilter($arFilterValues);

        }

        $this->lAdmin->DisplayList();


        if (!$subList) {
            require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
        }
    }

    public function newInterface()
    {
        // TODO ˜˜˜˜˜ ˜˜˜˜˜˜˜˜˜ ˜ ˜˜˜˜˜˜˜˜ ˜ ˜˜˜˜˜˜˜˜˜˜ ˜˜˜˜˜˜˜˜˜˜
        return false;
    }

    protected function getFilterFields()
    {
        $filterFields = [];

        foreach ($this->arFilterConfig as $id => $filterType) {
            if (array_key_exists($id, $this->arFilterInput)) {

                /*
                $filterField = array(
                    'id' => $id,
                    'name' => $this->arFilterFieldsTitle[$id],
                    'filterable' => '',
                    'value' => '',
                    'type' => 'custom_entity',
                    'property' => array(),
                    'customRender' => array($this, 'customFilterRender'),
                    'customFilter' => array($this, 'customFilterAdd'),
                    'operators' => array(
                        "default" => "=",
                        "exact" => "=",
                        "enum" => "@"
                    ),
                );

                $filterFields[] = $filterField;

                $this->lAdmin->BeginEpilogContent();

                $this->lAdmin->EndEpilogContent();
                */
                continue;
            }

            $arField = $this->allFields[$id];

            $filterField = array(
                'id' => $id,
                'name' => $this->arFilterFieldsTitle[$id]
            );

            if ($arField["FIELD"] instanceof Entity\DateField) {
                $filterField['type'] = 'date';
            } elseif ($filterType == "RANGE") {
                $filterField['type'] = 'number';
            } elseif ($arField["FIELD"] instanceof Entity\BooleanField) {
                $values = $arField["FIELD"]->getValues();

                $filterField['type'] = 'list';
                $filterField['items'] = array(
                    '' => GetMessage("DEVBX_ADMIN_LIST_FILTER_VALUE_NOT_SETTED"),
                    $values[0] => GetMessage("DEVBX_ADMIN_LIST_BOOL_FALSE"),
                    $values[1] => GetMessage("DEVBX_ADMIN_LIST_BOOL_TRUE"),
                );
            } elseif ($arField["FIELD"] instanceof Entity\EnumField) {
                $values = $arField["FIELD"]->getValues();
                if (is_numeric(key($values)))
                {
                    $values = array_combine($values, $values);
                }

                $filterField['type'] = 'list';
                $filterField['items'] = array(
                        '' => GetMessage("DEVBX_ADMIN_LIST_FILTER_VALUE_NOT_SETTED"),
                    ) + $values;

            } else {
                $filterField['filterable'] = '?';
                $filterField['quickSearch'] = '?';
            }

            $filterFields[] = $filterField;
        }

        return $filterFields;
    }

    public function processActions()
    {
        global $APPLICATION;

        $request = \Bitrix\Main\Context::getCurrent()->getRequest();

// ******************************************************************** //
//                ˜˜˜˜˜˜˜˜˜ ˜˜˜˜˜˜˜˜ ˜˜˜ ˜˜˜˜˜˜˜˜˜˜ ˜˜˜˜˜˜              //
// ******************************************************************** //
        if ($request->isPost() && $request->get('table_id') != $this->lAdmin->table_id)
            return;

        $primaryKey = $this->entity->getPrimaryArray();

// ˜˜˜˜˜˜˜˜˜˜ ˜˜˜˜˜˜˜˜˜˜˜˜˜˜˜˜˜ ˜˜˜˜˜˜˜˜˜
        if ($this->lAdmin->EditAction() && $this->entityAccess == "W") {
            // ˜˜˜˜˜˜˜ ˜˜ ˜˜˜˜˜˜ ˜˜˜˜˜˜˜˜˜˜ ˜˜˜˜˜˜˜˜˜
            foreach ($request->get('FIELDS') as $ID => $arFields) {
                if (!$this->lAdmin->IsUpdated($ID))
                    continue;

                // ˜˜˜˜˜˜˜˜ ˜˜˜˜˜˜˜˜˜ ˜˜˜˜˜˜˜ ˜˜˜˜˜˜˜˜

                foreach ($arFields as $key => $val) {
                    if (array_key_exists($key, $this->allFields)) {
                        if ($this->allFields[$key]["FIELD"] instanceof Entity\DateTimeField) {
                            if (Type\DateTime::isCorrect($val))
                                $arFields[$key] = new Type\DateTime($val);
                            elseif (empty($val))
                                $arFields[$key] = false;
                        } else
                            if ($this->allFields[$key]["FIELD"] instanceof Entity\DateField) {
                                if (Type\Date::isCorrect($val))
                                    $arFields[$key] = new Type\Date($val);
                                elseif (empty($val))
                                    $arFields[$key] = false;
                            }
                    }
                }

                //$PRIMARY = $_REQUEST[$primaryKey];

                //$arRow = call_user_func(array($this->d7class, 'getRowById'), $ID);
                $arRow = $this->entity->getDataClass()::getRowById($ID);
                if ($arRow) {
                    foreach ($arFields as $key => $val) {
                        if (isset($arRow[$key])) {
                            if (is_array($val) || is_array($arRow[$key]))
                                continue;

                            if ($arRow[$key] == $val)
                                unset($arFields[$key]);
                        }
                    }
                }

                if (!empty($arFields)) {
                    if (!is_string($this->cbUpdateFunction) && is_callable($this->cbUpdateFunction)) {
                        $result = call_user_func($this->cbUpdateFunction, $ID, $arFields);
                    } else {
                        //$result = call_user_func(array($this->d7class, 'update'), $ID, $arFields);
                        $result = $this->entity->getDataClass()::update($ID, $arFields);
                    }

                    if (!$result->isSuccess()) {
                        $errors = $result->getErrorMessages();
                        $this->lAdmin->AddGroupError(implode("\n", $errors), $ID);
                    }
                }
            }
        }

        if (($arID = $this->lAdmin->GroupAction()) && $this->entityAccess == "W" && check_bitrix_sessid()) {

            $action = $request->get('action');
            if (empty($action)) {
                $action = $request->get('action_button');
            }

            if ($request->get('ajax_action') != 'y') {
                $event = new \Bitrix\Main\Event("devbx.core", "OnAdminListProcessAction", array("ENTITY" => $this, "ACTION" => $action, "ID" => $arID));
                $event->send();
                if ($event->getResults()) {
                    foreach ($event->getResults() as $eventResult) {
                        if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                            return;
                        }
                    }
                }
            }

            $arFormParams = $request->toArray();
            if (!isset($arFormParams["ACTION_AJAX_ID"])) {
                if ($request->get('action_target') == 'selected') {

                    $arSelect = $primaryKey;

                    $arFilter = $this->arFilter;

                    foreach ($arFilter as $filterKey => $filterVal) {
                        $field = preg_replace('/^[=<>!%@]+/', '', $filterKey);

                        if (array_key_exists($field, $this->allFields)) {
                            $arField = $this->allFields[$field];
                            $op = substr($filterKey, 0, strlen($filterKey) - strlen($field));
                            unset($arFilter[$filterKey]);
                            $arFilter[$op . $arField["SELECT"]] = $filterVal;
                        }
                    }

                    $arOrder = array();
                    foreach ($primaryKey as $key)
                        $arOrder[$key] = "ASC";

                    //$rsData = call_user_func(array($this->d7class, 'getList'), array('order' => $arOrder, 'select' => $primaryKey, 'filter' => $arFilter));
                    $rsData = $this->entity->getDataClass()::getList(array('order' => $arOrder, 'select' => $primaryKey, 'filter' => $arFilter));
                    while ($arRes = $rsData->fetch()) {
                        $arID[] = count($arRes) > 1 ? $arRes : reset($arRes);
                    }
                }
            } else {
                $arID = $_SESSION[$arFormParams["ACTION_AJAX_ID"]]["DEVBX_ACTION_ID_LIST"];
            }

            if (array_key_exists($action, $this->arCbActions) &&
                !is_string($this->arCbActions[$action]) && is_callable($this->arCbActions[$action])
            ) {
                if ($request->get('ajax_action') == 'y') {
                    $offset = intval($request->get('offset'));
                    if ($offset < 0)
                        $offset = 0;
                } else {
                    $offset = 0;
                }

                $arRes = call_user_func_array($this->arCbActions[$action], array($this, $action, $arID, &$offset, $arFormParams));
                if ($arRes !== false && $offset < count($arID)) {

                    if (is_array($arRes))
                        $arFormParams = array_merge($arFormParams, $arRes);

                    $arFormParams["ajax_action"] = "y";
                    $arFormParams["offset"] = $offset;
                    if (!isset($arFormParams["ACTION_AJAX_ID"]))
                        $arFormParams["ACTION_AJAX_ID"] = md5(uniqid());
                    $_SESSION[$arFormParams["ACTION_AJAX_ID"]]["DEVBX_ACTION_ID_LIST"] = $arID;

                    $msg = new \CAdminMessage(array(
                        "TYPE" => "PROGRESS",
                        "MESSAGE" => Loc::getMessage('DEVBX_ADMIN_LIST_PROGRESS_MESSAGE'),
                        "DETAILS" => Loc::getMessage('DEVBX_ADMIN_LIST_PROGRESS_DETAIL'),
                        "HTML" => true,
                        "PROGRESS_TOTAL" => count($arID),
                        "PROGRESS_VALUE" => $arFormParams["offset"],
                    ));

                    if ($request->get("ajax_action") == "y") {
                        $APPLICATION->RestartBuffer();
                        $arFormParams['ajax_progress'] = $msg->Show();
                        echo \CUtil::PhpToJSObject($arFormParams);
                        die();
                    }

                    $APPLICATION->RestartBuffer();

                    echo '<div id="ajax_progress">' . $msg->Show() . '</div>';

                    ?>
                    <script>

                        var ajaxRetry = 0;

                        function ajaxProcessAction(arData) {
                            top.BX.ajax({
                                url: '<?=$APPLICATION->GetCurPage(false)?>',
                                data: arData,
                                method: 'POST',
                                dataType: 'json',
                                timeout: 60,
                                async: true,
                                processData: true,
                                scriptsRunFirst: true,
                                emulateOnload: true,
                                start: true,
                                cache: false,
                                onsuccess: function (data) {
                                    ajaxRetry = 0;
                                    if (!data.finished) {
                                        top.BX('ajax_progress').innerHTML = data.ajax_progress;
                                        ajaxProcessAction(data);
                                    } else {
                                        top.BX.findChild(top.BX('adm-workarea'), {
                                            tag: 'input',
                                            attribute: {name: 'set_filter'}
                                        }, true).click();
                                    }
                                },
                                onfailure: function () {
                                    ajaxRetry++;
                                    if (ajaxRetry > 5) {
                                        if (confirm('<?=Loc::getMessage("DEVBX_ADMIN_AJAX_FAILED_WITH_RETRY")?>')) {
                                            console.log('retry');
                                            console.log(arData);
                                            ajaxProcessAction(arData);
                                        } else {
                                            console.log('failure');
                                            alert('<?=Loc::getMessage('DEVBX_ADMIN_LIST_PROGRESS_AJAX_ERROR')?>');
                                            top.BX.findChild(top.BX('adm-workarea'), {
                                                tag: 'input',
                                                attribute: {name: 'set_filter'}
                                            }, true).click();
                                        }
                                    } else {
                                        console.log('retry');
                                        console.log(arData);
                                        ajaxProcessAction(arData);
                                    }
                                }
                            });
                        }

                        ajaxProcessAction(<?=json_encode($arFormParams)?>);

                    </script>
                    <?
                    die();

                }

                if ($request->get("ajax_action") == "y") {
                    $APPLICATION->RestartBuffer();
                    if (isset($arFormParams["ACTION_AJAX_ID"]))
                        unset($_SESSION[$arFormParams["ACTION_AJAX_ID"]]);
                    $arFormParams["finished"] = true;
                    echo json_encode($arFormParams);
                    die();
                }
            }
        }

    }

    protected function displayFilter($arFilterValues)
    {
        if ($this->newInterface()) {
            $this->displayNewFilter($arFilterValues);
        } else {
            $this->displayOldFilter($arFilterValues);
        }
    }

    protected function displayNewFilter($arFilterValues)
    {
        $this->lAdmin->DisplayFilter($this->getFilterFields());
    }

    protected function displayOldFilter($arFilterValues)
    {
        global $APPLICATION;

        ?>
        <form name="find_form" method="GET" action="<? echo $APPLICATION->GetCurPage() ?>?">
            <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
            <?
            $arFilterPopup = array();
            foreach ($this->arFilterConfig as $id => $filterType) {
                $arFilterPopup[] = $this->arFilterFieldsTitle[$id];
            }

            $oFilter = new \CAdminFilter($this->sTableID . "_filter", $arFilterPopup);

            $customFilterHeader = $this->getOption('FILTER_HEADER', false);
            if ($customFilterHeader !== false) {
                if (!is_string($customFilterHeader) && is_callable($customFilterHeader)) {
                    call_user_func($customFilterHeader, $oFilter);
                } else {
                    echo $customFilterHeader;
                }
            }

            $oFilter->Begin();
            ?>
            <?
            foreach ($this->arFilterConfig as $id => $filterType) {

                if (array_key_exists($id, $this->arFilterInput)) {
                    call_user_func($this->arFilterInput[$id], $this, $id, $arFilterValues);
                    continue;
                }

                $arField = $this->allFields[$id];
                ?>
                <tr>
                    <td><?= $this->arFilterFieldsTitle[$id] ?>:</td>
                    <td>
                        <? if ($filterType == "RANGE" &&
                            $arField["FIELD"] instanceof Entity\DateField
                        ) {
                            ?>
                            <input name="filter_<?= $id ?>_FROM"
                                   value="<?= htmlspecialcharsbx($arFilterValues[$id . "_FROM"]) ?>">
                            <?= \CAdminCalendar::Calendar('filter_' . $id . '_FROM', '', '', $arField["FIELD"] instanceof Entity\DatetimeField) ?>
                            <input name="filter_<?= $id ?>_TO"
                                   value="<?= htmlspecialcharsbx($arFilterValues[$id . "_TO"]) ?>">
                            <?= \CAdminCalendar::Calendar('filter_' . $id . '_TO', '', '', $arField["FIELD"] instanceof Entity\DatetimeField) ?>

                            <?
                        } elseif ($filterType == "RANGE") {
                            ?>
                            <input name="filter_<?= $id ?>_FROM"
                                   value="<?= htmlspecialcharsbx($arFilterValues[$id . "_FROM"]) ?>">
                            <input name="filter_<?= $id ?>_TO"
                                   value="<?= htmlspecialcharsbx($arFilterValues[$id . "_TO"]) ?>">
                            <?

                        } elseif ($arField["FIELD"] instanceof Entity\BooleanField) {
                            $values = $arField["FIELD"]->getValues();
                            ?>
                            <select name="filter_<?= $id ?>">
                                <option
                                        value=""><?= GetMessage("DEVBX_ADMIN_LIST_FILTER_VALUE_NOT_SETTED") ?></option>
                                <option
                                        value="<?= $values[0] ?>"<? if ($values[0] == $arFilterValues[$id]): ?> selected="selected" <? endif ?>><?= GetMessage("DEVBX_ADMIN_LIST_BOOL_FALSE") ?></option>
                                <option
                                        value="<?= $values[1] ?>"<? if ($values[1] == $arFilterValues[$id]): ?> selected="selected" <? endif ?>><?= GetMessage("DEVBX_ADMIN_LIST_BOOL_TRUE") ?></option>
                            </select>
                            <?

                        } elseif ($arField["FIELD"] instanceof Entity\EnumField) {
                            $values = $arField["FIELD"]->getValues();
                            if (is_numeric(key($values)))
                            {
                                $values = array_combine($values, $values);
                            }
                            ?>
                            <select name="filter_<?= $id ?>">
                                <option
                                        value=""><?= GetMessage("DEVBX_ADMIN_LIST_FILTER_VALUE_NOT_SETTED") ?></option>
                                <? foreach ($values as $key => $val): ?>
                                    <option
                                            value="<?= htmlspecialcharsbx($key) ?>"<? if ($key == $arFilterValues[$id]): ?> selected="selected" <? endif ?>><?= htmlspecialcharsbx($val) ?></option>
                                <? endforeach ?>
                            </select>

                        <? } else { ?>
                            <select name="filter_type_<?= $id ?>">
                                <option
                                        value="substring"<? if ($arFilterValues["type_" . $id] == "substring"): ?> selected="selected" <? endif ?>><?= Loc::getMessage("DEVBX_ADMIN_LIST_FILTER_SUBSTRING") ?></option>
                                <option
                                        value="exact"<? if ($arFilterValues["type_" . $id] == "exact"): ?> selected="selected" <? endif ?>><?= Loc::getMessage("DEVBX_ADMIN_LIST_FILTER_EXACT") ?></option>
                                <option
                                        value="null"<? if ($arFilterValues["type_" . $id] == "null"): ?> selected="selected" <? endif ?>><?= Loc::getMessage("DEVBX_ADMIN_LIST_FILTER_NULL") ?></option>
                            </select>
                            <input name="filter_<?= $id ?>" value="<?= htmlspecialcharsbx($arFilterValues[$id]) ?>">
                        <? } ?>
                    </td>
                </tr>
            <? } ?>
            <?
            $oFilter->Buttons(
                array(
                    "table_id" => $this->sTableID,
                    "url" => $APPLICATION->GetCurPage(),
                    "form" => "find_form"
                )
            );
            $oFilter->End();
            ?>
        </form>
        <?
    }

    public function addRowView($id, $callback)
    {
        $this->arOptions['ROW_VIEW'][$id] = $callback;

        return $this;
    }

    public function addEditField($id, $callback)
    {
        $this->arOptions['EDIT_FIELDS'][$id] = $callback;

        return $this;
    }

    public function addHeaderField($id, $title = '', $default = true)
    {
        $this->arOptions['HEADER_FIELDS'][$id] = array(
            'TITLE' => $title,
            'DEFAULT' => $default,
        );

        return $this;
    }

    private function __activateElement(AdminList $list, $action, $arID, &$offset)
    {
        if ($list->getOption("ALLOW_ACTIVATE", "N") != 'Y')
            return false;

        $max_execution_time = 10;

        $cnt = count($arID);
        for (; $offset < $cnt; $offset++) {
            $ID = $arID[$offset];

            if (!is_string($this->cbUpdateFunction) && is_callable($this->cbUpdateFunction)) {
                $result = call_user_func($this->cbUpdateFunction, $ID, array("ACTIVE" => ($action == "activate" ? "Y" : "N")));
            } else {
                //$result = call_user_func(array($this->d7class, 'update'), $ID, array("ACTIVE" => ($action == "activate" ? "Y" : "N")));
                $result = $this->entity->getDataClass()::update($ID, array("ACTIVE" => ($action == "activate" ? "Y" : "N")));
            }
            if (!$result->isSuccess()) {
                $errors = $result->getErrorMessages();
                $this->lAdmin->AddGroupError(implode("\n", $errors), $ID);
            }

            if ($max_execution_time > 0 && (microtime(true) - START_EXEC_TIME) > $max_execution_time)
                return true;
        }

        return false;
    }

    private function __deleteElement(AdminList $list, $action, $arID, &$offset)
    {
        if ($list->getOption("ALLOW_DELETE", "N") != 'Y')
            return false;

        $max_execution_time = 10;

        $cnt = count($arID);
        for (; $offset < $cnt; $offset++) {
            $ID = $arID[$offset];

            //$result = call_user_func(array($this->d7class, 'delete'), $ID);
            $result = $this->entity->getDataClass()::delete($ID);
            if (!$result->isSuccess()) {
                $errors = $result->getErrorMessages();

                $list->getAdminList()->AddGroupError(implode( "\n", $errors), $ID);
            }

            if ($max_execution_time > 0 && (microtime(true) - START_EXEC_TIME) > $max_execution_time)
                return true;
        }

        return false;
    }

    function getAdminList()
    {
        return $this->lAdmin;
    }
}

?>
<?
namespace DevBx\Core\Admin;

IncludeModuleLangFile(__FILE__);

use Bitrix\Main;
use Bitrix\Main\Entity;
use DevBx\Core\Assert;

class AdminEdit
{
    /**
     * @var Entity\Base $entity
     */
    private $entity = false;
    private $arOptions = array();
    private $module = false;
    private $entityAccess = false;
    private $file = false;
    private $file_list = false;
    private $cbGetRowById = false;
    private $cbBeforeSave = array();
    private $cbSave = false;
    private $cbAfterSave = array();
    private $adminFormParams = array();
    private $tabControlName = false;
    /**
     * @var \CAdminForm
     */
    private $tabControl = null;
    private $addParamsFile = "";
    private $addParamsFileList = "";
    private $bNewForm = false;
    private $footerContent = array();
    private $hiddenFields = false;
    private $formAllowAdd = false;
    private $formAllowDelete = false;

    private $tabs = false;
    private $subWindow = false;

    public function __construct($module, $entity, $arOptions = array())
    {
        global $APPLICATION;

        $debug_backtrace = debug_backtrace();

        $this->module = $module;

        if (!Main\Loader::includeModule($this->module))
            throw new \Bitrix\Main\SystemException('module not installed ' . $this->module);

        /*
        $this->file = basename($debug_backtrace[0]['file']);
        $this->file = preg_replace('#\(\d+\).+:.+eval\(\).+#', '', $this->file);
        $this->file_list = str_replace('edit.php', 'list.php', $this->file);
        */

        $this->file = $APPLICATION->GetCurPage();
        $this->file_list = str_replace('edit.php', 'list.php', $this->file);

        if (is_a($entity, Entity\DataManager::class, true)) {
            $this->entity = $entity::getEntity();
        } else {
            if (!$entity instanceof Entity\Base) {
                throw new Main\SystemException('invalid class ' . $entity);
            }
            $this->entity = $entity;
        }

        $this->arOptions = $arOptions;

        $this->tabControlName = $this->getOption('TAB_CONTROL_NAME', "devbx_" . str_replace('\\', '_', $this->entity->getDataClass()));

        $this->formAllowAdd = $this->getOption('ALLOW_ADD', 'N') == 'Y';
        $this->formAllowDelete = $this->getOption('ALLOW_DELETE', 'N') == 'Y';

        if (array_key_exists('TABS', $arOptions) && is_array($arOptions['TABS'])) {
            $tabNum = 0;

            foreach ($arOptions['TABS'] as $arTab) {
                $tabNum++;

                if ($arTab['DIV']) {
                    $tabId = $arTab['DIV'];
                } else {
                    $tabId = 'edit_' . $tabNum;
                }

                $this->addTab($tabId, $arTab['TAB'], $arTab['TITLE']);

                if (is_array($arTab["FIELDS"])) {
                    foreach ($arTab["FIELDS"] as $key => $val) {
                        $oldKey = $key;

                        if (!$val instanceof EditField) {
                            if (!is_string($val) && is_callable($val)) {
                                $val = new EditFieldOld($key, array("showFieldOld" => $val));
                            } else {
                                if (is_numeric($key))
                                    $key = $val;

                                if (!is_array($val))
                                    $val = array();

                                $val = new EditFieldOld($key, $val);
                            }
                        }

                        $this->addTabField($tabId, $val);
                    }
                }
            }
        }

        $this->entityAccess = $this->getOption('ENTITY_ACCESS', $APPLICATION->GetGroupRight($this->module));
    }

    /**
     * @return \CAdminForm
     */
    public function getTabControl()
    {
        return $this->tabControl;
    }

    public function getTabControlName()
    {
        return $this->tabControlName;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function getDataClass()
    {
        return $this->entity->getDataClass();
    }

    public function isNewForm()
    {
        return $this->bNewForm;
    }

    public function addTab($tabId, $name = '', $title = '', $fields = [])
    {
        $this->tabs[$tabId] = [
            'NAME' => $name,
            'TITLE' => $title,
            'FIELDS' => [],
        ];

        foreach ($fields as $field) {
            $this->addTabField($tabId, $field);
        }

        return $this;
    }

    public function addTabField($tabId, EditField $field)
    {
        if (!array_key_exists($tabId, $this->tabs))
            throw new Main\SystemException('tab not exists ' . $tabId);

        $this->tabs[$tabId]['FIELDS'][$field->getId()] = $field;

        return $this;
    }

    public function setBeforeSaveCallback($callback) //function(&$arLoadFields)
    {
        if (!is_string($callback) && is_callable($callback))
            $this->cbBeforeSave[] = $callback;
        return $this;
    }

    public function setGetRowByIdCallback($callback)
    {
        if (!is_string($callback) && is_callable($callback))
            $this->cbGetRowById = $callback;
    }

    public function setSaveCallback($callback) //function($primary,$arLoadFields) return object Result
    {
        if (!is_string($callback) && is_callable($callback))
            $this->cbSave = $callback;
        return $this;
    }

    public function setAfterSaveCallback($callback) //function()
    {
        if (!is_string($callback) && is_callable($callback))
            $this->cbAfterSave[] = $callback;
        return $this;
    }

    public function setAdminFormParams($arParams)
    {
        $this->adminFormParams = $arParams;
        return $this;
    }

    public function setAddParamsFile($params)
    {
        if (is_array($params)) {
            $this->file = (new Main\Web\Uri($this->file))->addParams($params)->getUri();
        } else {
            //deprecated OLD STYLE
            $this->addParamsFile = $params;
        }
    }

    public function setAddParamsFileList($params)
    {
        if (is_array($params)) {
            $this->file_list = (new Main\Web\Uri($this->file_list))->addParams($params)->getUri();
        } else {
            //deprecated OLD STYLE
            $this->addParamsFileList = $params;
        }
    }

    function setFooterContent($content)
    {
        $this->footerContent[] = $content;
    }

    function setHiddenFields($fields)
    {
        $this->hiddenFields = $fields;
    }

    function display($options = false, $fileList = false)
    {
        global $APPLICATION, $adminPage, $USER, $adminMenu, $adminChain;

        if ($this->entityAccess < "R") {
            $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
            return;
        }

        $request = \Bitrix\Main\Context::getCurrent()->getRequest();

        if ($options === true) {
            $options = array(
                'subwindow' => true,
                'adjustwindow' => true,
            );
        }

        $this->subWindow = $options['subwindow'];

        if ($options['subwindow']) {
            require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/iblock/classes/general/subelement.php');
        }

        \CJSCore::Init(array("admin_interface"));

        if ($request->get('bxpublic') == 'Y')
            $APPLICATION->ShowHead();

        if ($fileList !== false)
            $this->file_list = $fileList;

        if ($request->offsetExists('SUBLIST_ID')) {
            $subListId = $request->get('SUBLIST_ID');
        } else {
            $subListId = $this->entity->getFullName();
        }

        $subListId = $this->getOption('SUBLIST_ID', $subListId);

        $primaryKey = $this->entity->getPrimary();

        if (!$request->offsetExists("bNewElement") && $request->get("bNewElement") != 'Y') {
            if (is_array($primaryKey)) {
                $primary = array();
                foreach ($primaryKey as $k) {
                    $v = trim($request->get($k));
                    if (!empty($v))
                        $primary[$k] = $v;
                }
            } else {
                $primary = trim($request->get($primaryKey));
            }
        } else {
            $primary = false;
        }

        $message = null; // сообщение об ошибке
        $bVarsFromForm = false; // флаг "Данные получены с формы", обозначающий, что выводимые данные получены с формы, а не из БД.

        $arFields = $this->entity->getFields();
        $aTabs = array();

        if ($options['subwindow'])
            $divPrefix = "sw_"; else
            $divPrefix = "";

        foreach ($this->tabs as $tabId => $arTab) {

            $aTabs[] = array(
                "DIV" => $divPrefix . $tabId,
                "TAB" => $arTab["NAME"],
                "ICON" => $arTab["ICON"],
                "TITLE" => $arTab["TITLE"],
            );

        }

        unset($arTab);

        if ($options['subwindow']) {
            $arPostParams = array(
                'bxpublic' => 'Y',
                'sessid' => bitrix_sessid()
            );
            $listUrl = array(
                'LINK' => $APPLICATION->GetCurPageParam(),
                'POST_PARAMS' => $arPostParams,
            );

            $this->tabControl = new \CAdminSubForm($this->tabControlName, $aTabs, false, true, $listUrl, false);
        } else
            $this->tabControl = new \CAdminForm($this->tabControlName, $aTabs);

        $this->tabControl->Begin($this->adminFormParams);

        $reloadForm = false;

// ******************************************************************** //
//                ОБРАБОТКА ИЗМЕНЕНИЙ ФОРМЫ                             //
// ******************************************************************** //

        if (
            $_SERVER['REQUEST_METHOD'] == "POST" // проверка метода вызова страницы
            &&
            check_bitrix_sessid() // проверка идентификатора сессии
        ) {
            $arLoadFields = array();

            foreach ($this->tabs as $tab) {
                foreach ($tab['FIELDS'] as $field) {
                    $field->saveField($this, $arLoadFields, $primary);
                }
            }

            if ($_REQUEST['reload'] == 'Y') {
                $bVarsFromForm = true;
                $reloadForm = true;
            } elseif (($request->get("save") != "" || $request->get("apply") != "") // проверка нажатия кнопок "Сохранить" и "Применить"
                &&
                $this->entityAccess == "W" // проверка наличия прав на запись для модуля
            ) {
                $result = new Main\Result();

                foreach ($this->cbBeforeSave as $cb) {
                    if (!is_string($cb) && is_callable($cb)) {
                        $cbresult = call_user_func_array($cb, array($this, $primary, &$arLoadFields));


                        if ($cbresult instanceof Main\Result) {
                            $result = $cbresult;
                            if (!$result->isSuccess())
                                break;
                        }
                    }
                }

                if ($result->isSuccess()) {
                    /* @var \Bitrix\Main\ORM\Data\Result $result */

                    if (!is_string($this->cbSave) && is_callable($this->cbSave)) {
                        $result = call_user_func_array($this->cbSave, array($this, &$primary, $arLoadFields));
                    } else {
                        if (!empty($primary)) {
                            $result = $this->entity->getDataClass()::update($primary, $arLoadFields);
                        } else {
                            $result = $this->entity->getDataClass()::add($arLoadFields);
                            if ($result->isSuccess())
                                $primary = $result->getId();
                        }
                    }
                }

                if ($result->isSuccess()) {

                    foreach ($this->cbAfterSave as $cb) {
                        if (!is_string($cb) && is_callable($cb))
                            call_user_func($cb, $this, $primary);
                    }

                    if ($options['subwindow']) {

                        $arJSParams = array(
                            'SUBLIST_ID' => $subListId,
                            'ENTITY_NAME' => $this->getEntity()->getFullName(),
                        );

                        ?>
                        <script type="text/javascript">
                            top.BX.closeWait();
                            top.BX.WindowManager.Get().AllowClose();
                            top.BX.WindowManager.Get().Close();

                            top.BX.onCustomEvent('DevBxReloadSubList', <?=json_encode($arJSParams)?>);

                        </script><?
                        die();
                    }


                    if ($request->get("apply") != "") {
                        // если была нажата кнопка "Применить" - отправляем обратно на форму.

                        $uri = (new Main\Web\Uri($this->file))->addParams(array('mess' => 'ok', 'lang' => LANG));

                        if (is_array($primary)) {
                            $uri->addParams($primary);
                        } else {
                            $uri->addParams(array($primaryKey => $primary));
                        }

                        LocalRedirect($uri->getUri() . "&" . $this->tabControl->ActiveTabParam() . $this->addParamsFile);
                    } else
                        // если была нажата кнопка "Сохранить" - отправляем к списку элементов.
                        LocalRedirect((new Main\Web\Uri($this->file_list))->addParams(array('lang' => LANG))->getUri() . $this->addParamsFileList);
                } else {
                    /*$primary = false;*/
                    $message = new \CAdminMessage(implode("<br>", $result->getErrorMessages()));
                    $bVarsFromForm = true;
                }
            } elseif ($options['subwindow']) {
                if (!empty($request->get('dontsave'))) {
                    ?>
                    <script type="text/javascript">
                        top.BX.closeWait();
                        top.BX.WindowManager.Get().AllowClose();
                        top.BX.WindowManager.Get().Close();
                    </script>
                    <?
                    die();
                }
            }
        }

        $arValues = array();

//получаем из базы данные если указаны в запросе
        $this->bNewForm = true;
        if (!empty($primary)) {

            if ($this->cbGetRowById) {

                $arRes = call_user_func($this->cbGetRowById, $this, $primary);

            } else {
                if (is_array($primary)) {
                    $arFilter = $primary;
                } else {
                    $arFilter = array($primaryKey => $primary);
                }

                $arRes = $this->entity->getDataClass()::getList(array('filter' => $arFilter, 'select' => array('*', 'UF_*')))->fetch();
            }

            if ($arRes) {
                $arValues = $arRes;
                if ($request->get("copy") != 'Y')
                    $this->bNewForm = false;
            }
        } else {
            $bVarsFromForm = true;
        }

//получаем данные из формы если требуется или устанавливаем значения по умолчанию


        foreach ($this->tabs as $tab) {
            foreach ($tab['FIELDS'] as $field) {
                $field->getValue($this, $bVarsFromForm, $arValues, $primary);
            }
        }

        if ($this->bNewForm)
            $APPLICATION->SetTitle($this->getOption("ADD_TITLE", GetMessage("DEVBX_ADMIN_EDIT_ADD_TITLE"))); else
            $APPLICATION->SetTitle($this->getOption("EDIT_TITLE", GetMessage("DEVBX_ADMIN_EDIT_EDIT_TITLE")));

// не забудем разделить подготовку данных и вывод
        require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

        $aMenu = $this->getOption("CONTEXT_MENU");
        if (!$aMenu) {
// конфигурация административного меню
            $aMenu = array(
                array(
                    "TEXT" => GetMessage("DEVBX_ADMIN_DEFAULT_LIST_TEXT"),
                    "TITLE" => GetMessage("DEVBX_ADMIN_DEFAULT_LIST_TITLE"),
                    "LINK" => (new Main\Web\Uri($this->file_list))->addParams(array('lang' => LANG))->getUri() . $this->addParamsFileList,
                    "ICON" => "btn_list",
                )
            );

            if (!$this->bNewForm && ($this->formAllowAdd || $this->formAllowDelete)) {
                $aMenu[] = array("SEPARATOR" => "Y");

                if ($this->formAllowAdd) {
                    $aMenu[] = array(
                        "TEXT" => GetMessage("DEVBX_ADMIN_DEFAULT_ADD_TEXT"),
                        "TITLE" => GetMessage("DEVBX_ADMIN_DEFAULT_ADD_TITLE"),
                        "LINK" => (new Main\Web\Uri($this->file))->addParams(array('lang' => LANG))->getUri() . $this->addParamsFile,
                        "ICON" => "btn_new",
                    );

                    $uri = (new Main\Web\Uri($this->file))->addParams(array('lang' => LANG, "copy" => "Y"));

                    if (is_array($primary)) {
                        $uri->addParams($primary);
                    } else {
                        $uri->addParams(array($primaryKey => $primary));
                    }

                    $aMenu[] = array(
                        "TEXT" => GetMessage("DEVBX_ADMIN_DEFAULT_COPY_TEXT"),
                        "TITLE" => GetMessage("DEVBX_ADMIN_DEFAULT_COPY_TITLE"),
                        "LINK" => $uri->getUri() . $this->addParamsFile,
                        "ICON" => "btn_copy",
                    );
                }

                if ($this->formAllowDelete) {

                    $uri = (new Main\Web\Uri($this->file_list))->addParams(array('lang' => LANG, "action" => "delete"));

                    if (is_array($primary)) {
                        $uri->addParams($primary);
                    } else {
                        $uri->addParams(array($primaryKey => $primary));
                    }

                    $aMenu[] = array(
                        "TEXT" => GetMessage("DEVBX_ADMIN_DEFAULT_DEL_TEXT"),
                        "TITLE" => GetMessage("DEVBX_ADMIN_DEFAULT_DEL_TITLE"),
                        "LINK" => "javascript:if(confirm('" . GetMessage("DEVBX_ADMIN_DEFAULT_DEL_CONF") . "'))window.location='" .
                            $uri->getUri() . "&" . bitrix_sessid_get() . $this->addParamsFileList . "';",
                        "ICON" => "btn_delete",
                    );
                }
            }
        } else {
            if (!is_string($aMenu) && is_callable($aMenu))
                $aMenu = call_user_func($aMenu, $this);
            if (!is_array($aMenu))
                $aMenu = array();
        }

        if ($options['subwindow'])
            $aMenu = array();

// создание экземпляра класса административного меню
        $context = new \CAdminContextMenu($aMenu);

// вывод административного меню
        $context->Show();

// если есть сообщения об ошибках или об успешном сохранении - выведем их.
        if ($request->get("mess") == "ok" && !$this->bNewForm)
            $message = new \CAdminMessage(array("MESSAGE" => GetMessage("DEVBX_ADMIN_EDIT_SAVED"), "TYPE" => "OK"));

        if ($message)
            echo $message->Show();

        $this->tabControl->BeginEpilogContent();
        echo bitrix_sessid_post();

        if (!$this->bNewForm):?>
            <?
            if (is_array($primary)) {
                foreach ($primary as $k => $v) {
                    ?>
                    <input type="hidden" name="<?= $k ?>" value="<?= htmlspecialcharsbx($v) ?>">
                    <?
                }
            } else {
                ?>
                <input type="hidden" name="<?= $primaryKey ?>" value="<?= htmlspecialcharsbx($primary) ?>">
                <?
            }
            ?>

        <? else: ?>
            <input type="hidden" name="bNewElement" value="Y">
        <?endif;

        if ($subListId) {
            ?>
            <input type="hidden" name="SUBLIST_ID" value="<?= htmlspecialcharsbx($subListId) ?>">
            <?
        }

        if ($this->hiddenFields !== false) {
            if (is_array($this->hiddenFields)) {

                foreach ($this->hiddenFields as $key => $val) {
                    echo '<input type="hidden" name="' . htmlspecialcharsbx($key) . '" value="' . htmlspecialcharsbx($val) . '">';
                }

            } elseif (!is_string($this->hiddenFields) && is_callable($this->hiddenFields)) {

                call_user_func($this->hiddenFields, $this);

            } else {

                echo $this->hiddenFields;
            }
        }

        $this->tabControl->EndEpilogContent();

        $this->tabControl->Begin(array_merge(array(
            "FORM_ACTION" => (new Main\Web\Uri($this->file))->addParams(array("lang" => LANGUAGE_ID))->getUri() . $this->addParamsFile,
        ), $this->adminFormParams));
        ?>
        <?

        foreach ($this->tabs as $arTab): ?>
            <? $this->tabControl->BeginNextFormTab() ?>
            <?
            foreach ($arTab["FIELDS"] as $field) {
                $field->showField($this, $arValues);
            }
            ?>
        <? endforeach ?>
        <?
        // завершение формы - вывод кнопок сохранения изменений
        if ($options['subwindow']) {
            $this->tabControl->Buttons(false, '');
        } else {
            $this->tabControl->Buttons(
                array(
                    "disabled" => ($this->entityAccess < "W"),
                    "back_url" => (new Main\Web\Uri($this->file_list))->addParams(array("lang" => LANG))->getUri() . $this->addParamsFileList,
                )
            );
        }

        if ($reloadForm && $this->subWindow) {
            ob_start();
        }

        // завершаем интерфейс закладок
        $this->tabControl->Show();
        ?>

        <?
        // дополнительное уведомление об ошибках - вывод иконки около поля, в котором возникла ошибка
        $this->tabControl->ShowWarnings($this->tabControl->name, $message);

        foreach ($this->footerContent as $content)
        {
            if (!is_string($content) && is_callable($content))
            {
                call_user_func($content, $this);
            } else {
                echo $content;
            }
        }

        ?>

        <?
        // информационная подсказка
        echo BeginNote();
        ?>

        <span class="required">*</span><? echo GetMessage("REQUIRED_FIELDS") ?>
        <? echo EndNote(); ?>
        <?

        if ($reloadForm && $this->subWindow) {
            $content = ob_get_clean();
            ?>
            <script>
                top.BX.closeWait();
                top.window.reloadAfterClose = true;

                var ob = BX.processHTML('<?=\CUtil::JSEscape($content)?>', false),
                    html = ob.HTML;
                    scripts = ob.SCRIPT;
                    styles = ob.STYLE;

                if (styles.length > 0)
                    BX.loadCSS(styles);

                top.BX.WindowManager.Get().SetContent(html);

                top.BX.ajax.processScripts(scripts, true);
                top.BX.ajax.processScripts(scripts, false);

                top.BX.adminFormTools.modifyFormElements(top.BX.WindowManager.Get().GetForm().name);
            </script>
            <?
            require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin_js.php");
            die();
        }

        if ($options['adjustwindow']) {
            ?>
            <script type="text/javascript">
                top.BX.WindowManager.Get().adjustSizeEx();
            </script>
            <?
        }

        // завершение страницы
        require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
    }

    function getOption($key, $defaultValue = false)
    {
        if (isset($this->arOptions[$key]))
            return $this->arOptions[$key];
        return $defaultValue;
    }

    function getRealodActionJS()
    {
        static $init = false;

        $funcName = $this->tabControl->name . '_reload';

        if (!$init) {
            ob_start();
            if ($this->subWindow) {
                ?>
                <script>
                    function <?=$funcName?>() {
                        BX.WindowManager.Get().GetForm().insertAdjacentHTML('beforeend', '<input type="hidden" name="reload" value="Y">');
                        BX.WindowManager.Get().Submit();
                    }
                </script>
                <?
            } else {

                ?>
                <script>
                    function <?=$funcName?>() {
                        console.log('not implemented');
                    }
                </script>
                <?
            }

            $this->tabControl->sEpilogContent .= ob_get_clean();

            $init = true;
        }

        return $funcName . '();';
    }
}

?>

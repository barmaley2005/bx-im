<?

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main;

IncludeModuleLangFile(__FILE__);

Class devbx_forms extends CModule
{
    const MODULE_ID = 'devbx.forms';
    var $MODULE_ID = 'devbx.forms';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $strError = '';

    function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__) . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = GetMessage("devbx.forms_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("devbx.forms_MODULE_DESC");

        $this->PARTNER_NAME = GetMessage("devbx.forms_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("devbx.forms_PARTNER_URI");
    }

    function getEvents()
    {
        $arEvents = array();

        $arEvents[] = array(
            'main', 'OnAfterUserTypeAdd',
            self::MODULE_ID,
            '\DevBx\Forms\FormTypes\SimpleType',
            'OnAfterUserTypeAddHandler'
        );

        $arEvents[] = array(
            'main',
            'OnAdminIBlockElementEdit',
            self::MODULE_ID,
            'DevBx\Forms\Iblock\Events',
            'OnAdminIBlockElementEditHandler'
        );

        $arEvents[] = array(
            'main',
            'OnAdminIBlockSectionEdit',
            self::MODULE_ID,
            'DevBx\Forms\Iblock\Events',
            'OnAdminIBlockSectionEditHandler'
        );

        $arEvents[] = array(
            'main',
            'onVirtualClassBuildList',
            self::MODULE_ID,
            'DevBx\Forms\Internals\Events',
            'onVirtualClassBuildList'
        );

        $arEvents[] = array(
            self::MODULE_ID,
            'OnFormTypeBuildList',
            self::MODULE_ID,
            '\DevBx\Forms\FormTypes\SimpleType',
            'registerFormType'
        );

        $arEvents[] = array(
            self::MODULE_ID,
            'OnFormTypeBuildList',
            self::MODULE_ID,
            '\DevBx\Forms\FormTypes\ReplyType',
            'registerFormType'
        );

        $arEvents[] = array(
            self::MODULE_ID,
            'OnFormTypeBuildList',
            self::MODULE_ID,
            '\DevBx\Forms\FormTypes\WebFormType',
            'registerFormType'
        );

        $arEvents[] = array(
            self::MODULE_ID,
            'OnWizardBuildList',
            self::MODULE_ID,
            '\DevBx\Forms\Wizards\WebForm\Wizard',
            'registerWizard'
        );

        $arEvents[] = array(
            self::MODULE_ID,
            'OnWebFormWizardRegisterFields',
            self::MODULE_ID,
            '\DevBx\Forms\Wizards\WebForm\Wizard',
            'registerStandardFields'
        );

        return $arEvents;
    }

    public function __setEvents($install = true)
    {
        $arEvents = $this->getEvents();

        $eventManager = \Bitrix\Main\EventManager::getInstance();

        $callback = $install ? array($eventManager, 'registerEventHandler') : array($eventManager, 'unRegisterEventHandler');

        foreach ($arEvents as $ar) {
            call_user_func_array($callback, $ar);
        }
    }

    function DoInstall()
    {
        global $APPLICATION, $CACHE_MANAGER;

        RegisterModule(self::MODULE_ID);

        $this->InstallEvents();
        $this->InstallDB();
        $this->InstallFiles();

        $CACHE_MANAGER->CleanDir("fileman_component_tree");
    }

    function InstallEvents()
    {
        $this->__setEvents(true);

        return true;
    }

    function InstallDB()
    {
        Main\Loader::includeModule('devbx.core');

        Main\Loader::includeModule(self::MODULE_ID);

        \DevBx\Core\Admin\Utils::installModuleDB(self::MODULE_ID, true);

        return true;
    }

    function InstallFiles()
    {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/css", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/css/" . self::MODULE_ID, true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/js", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/" . self::MODULE_ID, true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/images", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/images/" . self::MODULE_ID, true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/components", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/tools", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/".self::MODULE_ID, true, true);

        return true;
    }

    function UnInstallEvents()
    {
        $this->__setEvents(false);

        return true;
    }

    function UnInstallDB()
    {
        Main\Loader::includeModule(self::MODULE_ID);

        Main\Loader::includeModule('devbx.core');

        //\DevBx\Core\Admin\Utils::installModuleDB(self::MODULE_ID, false);

        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");
        DeleteDirFilesEx("/bitrix/css/" . self::MODULE_ID);
        DeleteDirFilesEx("/bitrix/js/" . self::MODULE_ID);
        DeleteDirFilesEx("/bitrix/images/" . self::MODULE_ID);
        DeleteDirFilesEx("/bitrix/tools/" . self::MODULE_ID);

        return true;
    }

    function DoUninstall()
    {
        $this->UnInstallEvents();
        $this->UnInstallDB();
        $this->UnInstallFiles();

        UnRegisterModule(self::MODULE_ID);
    }
}



?>

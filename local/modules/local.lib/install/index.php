<?

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main;

IncludeModuleLangFile(__FILE__);

Class local_lib extends CModule
{
	const MODULE_ID = 'local.lib';
	var $MODULE_ID = 'local.lib'; 
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';

	function __construct()
	{
		$arModuleVersion = array();
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("local.lib_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("local.lib_MODULE_DESC");

		$this->PARTNER_NAME = GetMessage("local.lib_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("local.lib_PARTNER_URI");
	}

	function DoInstall()
	{
		global $APPLICATION;

		if (!Main\Loader::includeModule('devbx.core'))
			return false;

		$this->InstallFiles();
		$this->InstallDB();

		return true;
	}

	function InstallFiles()
	{
		global $CACHE_MANAGER;

		CopyDirFiles(
			\Bitrix\Main\Loader::getLocal('modules/' . self::MODULE_ID . "/install/admin"),
			$_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", true, true);

		CopyDirFiles(
			\Bitrix\Main\Loader::getLocal('modules/' . self::MODULE_ID . "/install/css"),
			$_SERVER["DOCUMENT_ROOT"] . "/bitrix/css", true, true);

		CopyDirFiles(
			\Bitrix\Main\Loader::getLocal('modules/' . self::MODULE_ID . "/install/js"),
			$_SERVER["DOCUMENT_ROOT"] . "/bitrix/js", true, true);

		CopyDirFiles(
			\Bitrix\Main\Loader::getLocal('modules/' . self::MODULE_ID . "/install/components"),
			$_SERVER["DOCUMENT_ROOT"] . "/bitrix/components", true, true);

		CopyDirFiles(
			\Bitrix\Main\Loader::getLocal('modules/' . self::MODULE_ID . "/install/tools"),
			$_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools", true, true);

		$CACHE_MANAGER->CleanDir("fileman_component_tree");

		return true;
	}

	function InstallDB()
	{
		RegisterModule(self::MODULE_ID);

		Main\Loader::includeModule(self::MODULE_ID);

		\DevBx\Core\Admin\Utils::installModuleDB(self::MODULE_ID, true);

		$this->InstallEvents();
	}

	function getEvents()
	{
		$arEvents = array();

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

	public function InstallEvents()
	{
		$this->__setEvents(true);

		return true;
	}

	public function UnInstallEvents()
	{
		$this->__setEvents(false);

		return true;
	}

	public function UnInstallDB()
	{
		if (!\Bitrix\Main\Loader::includeModule(self::MODULE_ID))
			return;

		$this->UnInstallEvents();

		$savedata = $_REQUEST['savedata'];

		if ($savedata != 'Y') {
			if (\Bitrix\Main\Loader::includeModule('devbx.core')) {
				\DevBx\Core\Admin\Utils::installModuleDB(self::MODULE_ID, false);
			}

			\Bitrix\Main\Config\Option::delete(self::MODULE_ID);
		}

		CAgent::RemoveModuleAgents(self::MODULE_ID);

		UnRegisterModule(self::MODULE_ID);

	}

	public function UnInstallFiles()
	{
		$path = \Bitrix\Main\Loader::getLocal('modules/' . self::MODULE_ID . "/install/admin");

		if ($path)
		{
			DeleteDirFiles($path, $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", true);
		}

		DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/" . self::MODULE_ID);
		DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/" . self::MODULE_ID);
		DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"] . "/bitrix/css/" . self::MODULE_ID);

		return true;
	}

	function DoUninstall()
	{
		global $APPLICATION, $USER_FIELD_MANAGER;

		Main\Loader::includeModule(self::MODULE_ID);

		$this->UnInstallDB();
		$this->UnInstallFiles();
	}
}
?>

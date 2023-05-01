<?php
IncludeModuleLangFile(__FILE__);

class devbx_core extends CModule
{
    const MODULE_ID = 'devbx.core';
    var $MODULE_ID = 'devbx.core';
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
        $this->MODULE_NAME = GetMessage("devbx.core_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("devbx.core_MODULE_DESC");

        $this->PARTNER_NAME = GetMessage("devbx.core_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("devbx.core_PARTNER_URI");
    }

    function DoInstall()
    {
        global $APPLICATION;
        RegisterModule(self::MODULE_ID);

        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/tools",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools", true, true);

        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/js/",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js", true, true);

        $eventManager = \Bitrix\Main\EventManager::getInstance();

        $bIblockModule = \Bitrix\Main\ModuleManager::isModuleInstalled('iblock');
        $bSaleModule = \Bitrix\Main\ModuleManager::isModuleInstalled('sale');
        $bCatalogModule = \Bitrix\Main\ModuleManager::isModuleInstalled('catalog');
        $bHighloadblockModule = \Bitrix\Main\ModuleManager::isModuleInstalled('highloadblock');

        if ($bSaleModule) {
            $eventManager->registerEventHandlerCompatible('main', 'OnUserTypeBuildList', 'devbx.core', 'DevBx\Core\UserType\Location', 'GetUserTypeDescription', 1000);
        }

        if ($bCatalogModule) {
            $eventManager->registerEventHandlerCompatible('main', 'OnUserTypeBuildList', 'devbx.core', 'DevBx\Core\UserType\CatalogPrice', 'GetUserTypeDescription', 1000);
        }

        if ($bIblockModule)
        {
            $eventManager->registerEventHandlerCompatible('main', 'OnUserTypeBuildList', 'devbx.core', 'DevBx\Core\UserType\ElementAutoComplete', 'GetUserTypeDescription', 1000);
        }

        $eventManager->registerEventHandlerCompatible('main', 'OnUserTypeBuildList', 'devbx.core', 'DevBx\Core\UserType\MapYandex', 'GetUserTypeDescription', 1000);
        $eventManager->registerEventHandlerCompatible('main', 'OnUserTypeBuildList', 'devbx.core', 'DevBx\Core\UserType\Site', 'GetUserTypeDescription', 1000);
        $eventManager->registerEventHandlerCompatible('main', 'OnUserTypeBuildList', 'devbx.core', 'DevBx\Core\UserType\User', 'GetUserTypeDescription', 1000);
        $eventManager->registerEventHandlerCompatible('main', 'OnUserTypeBuildList', 'devbx.core', 'DevBx\Core\UserType\Html', 'GetUserTypeDescription', 1000);

        if ($bHighloadblockModule)
        {
            $eventManager->registerEventHandlerCompatible('main', 'OnUserTypeBuildList', 'devbx.core', 'DevBx\Core\UserType\HLBlockXmlId', 'GetUserTypeDescription', 1000);
        }

        if ($bIblockModule)
        {
            $eventManager->registerEventHandlerCompatible("iblock", "OnIBlockPropertyBuildList", "devbx.core", "DevBx\Core\IblockProperty\PropertySite", "GetUserTypeDescription", 1000);
            if ($bSaleModule)
            {
                $eventManager->registerEventHandlerCompatible("iblock", "OnIBlockPropertyBuildList", "devbx.core", "DevBx\Core\IblockProperty\PropertyLocation", "GetUserTypeDescription", 1000);
            }
        }

        $eventManager->registerEventHandler('devbx.core', 'OnRegisterValueType', 'devbx.core', 'DevBx\Core\ValueType\StringType', 'registerEvent');
        $eventManager->registerEventHandler('devbx.core', 'OnRegisterValueType', 'devbx.core', 'DevBx\Core\ValueType\CheckBoxType', 'registerEvent');
        $eventManager->registerEventHandler('devbx.core', 'OnRegisterValueType', 'devbx.core', 'DevBx\Core\ValueType\ListType', 'registerEvent');
        $eventManager->registerEventHandler('devbx.core', 'OnRegisterValueType', 'devbx.core', 'DevBx\Core\ValueType\LocalFileType', 'registerEvent');
    }

    function DoUninstall()
    {
        global $APPLICATION;

        DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/".self::MODULE_ID);
        DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/".self::MODULE_ID);

        $eventManager = \Bitrix\Main\EventManager::getInstance();

        $eventManager->unRegisterEventHandler('main', 'OnUserTypeBuildList', 'devbx.core', 'DevBx\Core\UserType\Location', 'GetUserTypeDescription');
        $eventManager->unRegisterEventHandler('main', 'OnUserTypeBuildList', 'devbx.core', 'DevBx\Core\UserType\ElementAutoComplete', 'GetUserTypeDescription');
        $eventManager->unRegisterEventHandler('main', 'OnUserTypeBuildList', 'devbx.core', 'DevBx\Core\UserType\MapYandex', 'GetUserTypeDescription');
        $eventManager->unRegisterEventHandler('main', 'OnUserTypeBuildList', 'devbx.core', 'DevBx\Core\UserType\Site', 'GetUserTypeDescription');
        $eventManager->unRegisterEventHandler('main', 'OnUserTypeBuildList', 'devbx.core', 'DevBx\Core\UserType\CatalogPrice', 'GetUserTypeDescription');
        $eventManager->unRegisterEventHandler('main', 'OnUserTypeBuildList', 'devbx.core', 'DevBx\Core\UserType\User', 'GetUserTypeDescription');
        $eventManager->unRegisterEventHandler('main', 'OnUserTypeBuildList', 'devbx.core', 'DevBx\Core\UserType\Html', 'GetUserTypeDescription');
        $eventManager->unRegisterEventHandler('main', 'OnUserTypeBuildList', 'devbx.core', 'DevBx\Core\UserType\HLBlockXmlId', 'GetUserTypeDescription');

        $eventManager->unRegisterEventHandler("iblock", "OnIBlockPropertyBuildList", "devbx.core", "DevBx\Core\IblockProperty\PropertySite", "GetUserTypeDescription");
        $eventManager->unRegisterEventHandler("iblock", "OnIBlockPropertyBuildList", "devbx.core", "DevBx\Core\IblockProperty\PropertyLocation", "GetUserTypeDescription");

        $eventManager->unRegisterEventHandler('devbx.core', 'OnRegisterValueType', 'devbx.core', 'DevBx\Core\ValueType\StringType', 'registerEvent');
        $eventManager->unRegisterEventHandler('devbx.core', 'OnRegisterValueType', 'devbx.core', 'DevBx\Core\ValueType\CheckBoxType', 'registerEvent');
        $eventManager->unRegisterEventHandler('devbx.core', 'OnRegisterValueType', 'devbx.core', 'DevBx\Core\ValueType\ListType', 'registerEvent');
        $eventManager->unRegisterEventHandler('devbx.core', 'OnRegisterValueType', 'devbx.core', 'DevBx\Core\ValueType\LocalFileType', 'registerEvent');


        UnRegisterModule(self::MODULE_ID);
    }
}

?>

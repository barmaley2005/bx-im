<?php

namespace DevBx\Core;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use DevBx\Core\Admin\Options;

class ModuleOptions extends Options {

    protected function getSettingsGroups($arSettings)
    {
        $arSettings['IBLOCK_PROPERTY'] = array(
            'TITLE' => Loc::getMessage('DEVBX_CORE_MODULE_OPTIONS_IBLOCK_PROPERTY'),
            'ITEMS' => array(),
        );

        $items = \DevBx\Core\Admin\Utils::getClassesByPath('devbx.core','iblockproperty',['abstract'=>false]);
        foreach ($items as $className)
        {
            if (is_callable(array($className, 'isSupported')) && !$className::isSupported())
                continue;

            if (is_callable(array($className, 'GetUserTypeDescription')))
            {
                $arDescription = $className::GetUserTypeDescription();

                $arSettings['IBLOCK_PROPERTY']['ITEMS'][$arDescription['USER_TYPE']] = array(
                    'ID' => $arDescription['USER_TYPE'],
                    'TITLE' => $arDescription['DESCRIPTION'],
                    'TYPE' => 'CHECKBOX',
                );
            }
        }

        $arSettings['USER_TYPE'] = array(
            'TITLE' => Loc::getMessage('DEVBX_CORE_MODULE_OPTIONS_USER_TYPE'),
            'ITEMS' => array(),
        );

        $items = \DevBx\Core\Admin\Utils::getClassesByPath('devbx.core','usertype',['abstract'=>false]);

        foreach ($items as $className)
        {
            /* @var \Bitrix\Main\UserField\TypeBase $className */

            if (is_callable(array($className, 'isSupported')) && !$className::isSupported())
                continue;

            if (is_callable($className, 'GetUserTypeDescription'))
            {
                $arDescription = $className::GetUserTypeDescription();
                if (isset($arDescription['USER_TYPE_ID']))
                {
                    $arSettings['USER_TYPE']['ITEMS'][$arDescription['USER_TYPE_ID']] = array(
                        'ID' => $arDescription['USER_TYPE_ID'],
                        'TITLE' => $arDescription['DESCRIPTION'],
                        'TYPE' => 'CHECKBOX',
                    );
                }
            }
        }

        return parent::getSettingsGroups($arSettings);
    }

    protected function getGroupValues(&$arGroup, $siteId = false)
    {
        switch ($arGroup['ID'])
        {
            case 'IBLOCK_PROPERTY':
                $arIblockPropertyClass = array();
                $arRegistered = array();

                $items = \DevBx\Core\Admin\Utils::getClassesByPath('devbx.core','iblockproperty',['abstract'=>false]);
                foreach ($items as $className)
                {
                    if (is_callable(array($className, 'GetUserTypeDescription')))
                    {
                        $arDescription = $className::GetUserTypeDescription();

                        $arIblockPropertyClass[$arDescription['USER_TYPE']] = $className;
                    }
                }

                $handlers = Main\EventManager::getInstance()->findEventHandlers('iblock', 'OnIBlockPropertyBuildList', array('TO_MODULE_ID'=>'devbx.core'));

                foreach ($handlers as $ar)
                {
                    $userTypeId = array_search($ar['TO_CLASS'], $arIblockPropertyClass);
                    if ($userTypeId !== false) {
                        $arRegistered[] = $userTypeId;
                    }
                }

                foreach ($arGroup['ITEMS'] as &$arOption)
                {
                    $arOption['VALUE'] = in_array($arOption['ID'], $arRegistered) ? 'Y' : 'N';
                }

                break;
            case 'USER_TYPE':
                $arUserTypeClass = array();
                $arRegistered = array();

                $items = \DevBx\Core\Admin\Utils::getClassesByPath('devbx.core','usertype',['abstract'=>false]);

                foreach ($items as $className) {
                    /* @var \Bitrix\Main\UserField\TypeBase $className */

                    $arDescription = $className::GetUserTypeDescription();

                    $arUserTypeClass[$arDescription['USER_TYPE_ID']] = $className;
                }

                $handlers = Main\EventManager::getInstance()->findEventHandlers('main', 'OnUserTypeBuildList', array('TO_MODULE_ID'=>'devbx.core'));

                foreach ($handlers as $ar)
                {
                    $userTypeId = array_search($ar['TO_CLASS'], $arUserTypeClass);
                    if ($userTypeId !== false) {
                        $arRegistered[] = $userTypeId;
                    }
                }

                foreach ($arGroup['ITEMS'] as &$arOption)
                {
                    $arOption['VALUE'] = in_array($arOption['ID'], $arRegistered) ? 'Y' : 'N';
                }

                break;
        }
    }

    protected function setGroupValues($arGroup, $siteId = false)
    {
        switch ($arGroup['ID']) {
            case 'IBLOCK_PROPERTY':
                $arIblockPropertyClass = array();
                $arRegistered = array();

                $items = \DevBx\Core\Admin\Utils::getClassesByPath('devbx.core','iblockproperty',['abstract'=>false]);
                foreach ($items as $className)
                {
                    if (is_callable(array($className, 'GetUserTypeDescription')))
                    {
                        $arDescription = $className::GetUserTypeDescription();

                        $arIblockPropertyClass[$arDescription['USER_TYPE']] = $className;
                    }
                }

                $handlers = Main\EventManager::getInstance()->findEventHandlers('iblock', 'OnIBlockPropertyBuildList', array('TO_MODULE_ID'=>'devbx.core'));

                foreach ($handlers as $ar)
                {
                    $userTypeId = array_search($ar['TO_CLASS'], $arIblockPropertyClass);
                    if ($userTypeId !== false) {
                        $arRegistered[] = $userTypeId;
                    }
                }

                foreach ($arGroup['ITEMS'] as &$arOption)
                {
                    $oldValue = in_array($arOption['ID'], $arRegistered) ? 'Y' : 'N';

                    if ($oldValue != $arOption['VALUE'])
                    {
                        if ($arOption['VALUE'] == 'Y')
                        {
                            Main\EventManager::getInstance()->registerEventHandlerCompatible('iblock',
                                'OnIBlockPropertyBuildList', 'devbx.core', $arIblockPropertyClass[$arOption['ID']], 'GetUserTypeDescription');
                        } else
                        {
                            Main\EventManager::getInstance()->unRegisterEventHandler('iblock',
                                'OnIBlockPropertyBuildList', 'devbx.core', $arIblockPropertyClass[$arOption['ID']], 'GetUserTypeDescription');
                        }
                    }
                }


                break;
            case 'USER_TYPE':

                $arUserTypeClass = array();
                $arRegistered = array();

                $items = \DevBx\Core\Admin\Utils::getClassesByPath('devbx.core','usertype',['abstract'=>false]);

                foreach ($items as $className) {
                    /* @var \Bitrix\Main\UserField\TypeBase $className */

                    $arDescription = $className::GetUserTypeDescription();

                    $arUserTypeClass[$arDescription['USER_TYPE_ID']] = $className;
                }

                $handlers = Main\EventManager::getInstance()->findEventHandlers('main', 'OnUserTypeBuildList', array('TO_MODULE_ID'=>'devbx.core'));

                foreach ($handlers as $ar)
                {
                    $userTypeId = array_search($ar['TO_CLASS'], $arUserTypeClass);
                    if ($userTypeId !== false) {
                        $arRegistered[] = $userTypeId;
                    }
                }

                foreach ($arGroup['ITEMS'] as &$arOption)
                {
                    $oldValue = in_array($arOption['ID'], $arRegistered) ? 'Y' : 'N';

                    if ($oldValue != $arOption['VALUE'])
                    {
                        if ($arOption['VALUE'] == 'Y')
                        {
                            Main\EventManager::getInstance()->registerEventHandlerCompatible('main',
                            'OnUserTypeBuildList', 'devbx.core', $arUserTypeClass[$arOption['ID']], 'GetUserTypeDescription');
                        } else
                        {
                            Main\EventManager::getInstance()->unRegisterEventHandler('main',
                                'OnUserTypeBuildList', 'devbx.core', $arUserTypeClass[$arOption['ID']], 'GetUserTypeDescription');
                        }
                    }
                }

                break;
        }
    }

    public function setDefaultValues($multiSite = false)
    {
        $arGroups = $this->getSettingsGroups(array());

        foreach ($arGroups as $groupdId=>$arGroup)
        {
            switch ($groupdId)
            {
                case 'IBLOCK_PROPERTY':
                case 'USER_TYPE':

                    foreach ($arGroup['ITEMS'] as &$arOption)
                    {
                        $arOption['VALUE'] = 'Y';
                    }

                    $this->setGroupValues($arGroup);

                    break;
            }
        }

        parent::setDefaultValues($multiSite);
    }
}
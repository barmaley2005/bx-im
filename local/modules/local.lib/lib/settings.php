<?php

namespace Local\Lib;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\StringHelper;

/**
 * @property string $headerLogo
 * @property string $vk
 * @property string $telegram
 * @property string $phone
 * @property string $email
 * @property string $whatsapp
 * @property string $ourHistoryYoutube
 */
class Settings {

    private static $instance;

    private static $_camelToSnakeCache = array();
    private $id = false;
    private $arArrayOptions = array();
    private $entity = false;
    private $userFields = false;

    private function __construct()
    {
        global $USER_FIELD_MANAGER;

        Loader::includeModule('highloadblock');

        $this->entity = HighloadBlockTable::compileEntity('SiteSettings');

        $this->userFields = $USER_FIELD_MANAGER->GetUserFields($this->entity->getUfId(), 0, LANGUAGE_ID);

        $row = $this->entity->getDataClass()::getList([
            'filter' => array(
                '=UF_XML_ID' => SITE_ID
            ),
        ])->fetch();

        if (!$row)
        {
            $row = $this->entity->getDataClass()::getList([
                'filter' => array(
                    '=UF_XML_ID' => \CSite::GetDefSite()
                ),
            ])->fetch();
        }

        if ($row)
        {
            $this->id = $row['ID'];

            $this->arArrayOptions = $row;
        }

        static::$instance = $this;
    }

    public static function getInstance()
    {
        if (static::$instance)
            return static::$instance;

        return new static();
    }

    public static function sysMethodToFieldCase($methodName)
    {
        if (!isset(static::$_camelToSnakeCache[$methodName]))
        {
            static::$_camelToSnakeCache[$methodName] = StringHelper::strtoupper(
                StringHelper::camel2snake($methodName)
            );
        }

        return static::$_camelToSnakeCache[$methodName];
    }

    public function __get($name)
    {
        if (StringHelper::strtoupper($name) != $name)
        {
            $name = static::sysMethodToFieldCase($name);
        }

        $name = 'UF_'.$name;

        if (!array_key_exists($name, $this->arArrayOptions))
            throw new SystemException('Unknown settings option "'.$name.'"');

        if ($this->userFields[$name]['USER_TYPE_ID'] == 'file')
        {
            if (is_array($this->arArrayOptions[$name]))
            {
                $value = [];

                foreach ($this->arArrayOptions[$name] as $fileId)
                {
                    $arFile = \CFile::GetFileArray($fileId);
                    if (is_array($arFile))
                        $value[] = $arFile;
                }

                return $value;
            } else
            {
                return \CFile::GetFileArray($this->arArrayOptions[$name]);
            }
        }

        return $this->arArrayOptions[$name];
    }

    public function __set($name, $value)
    {
        if (!$this->id)
            throw new SystemException('Settings not found');

        if (StringHelper::strtoupper($name) != $name)
        {
            $name = static::sysMethodToFieldCase($name);
        }

        if (!array_key_exists($name, $this->arArrayOptions))
            throw new SystemException('Unknown site setting "'.$name.'"');

        $this->entity->getDataClass()::update($this->id, array('UF_'.$name=>$value));
    }

}

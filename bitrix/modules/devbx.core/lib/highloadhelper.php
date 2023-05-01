<?

namespace DevBx\Core;

use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;

/**
 * Class HighloadHelper
 * @package DevBx\Core
 */
class HighloadHelper
{
    private static $arCacheClassTableName = array();
    private static $arCacheClassName = array();
    private static $arXmlIdCache = array();
    private static $bModuleLoaded = false;

    /**
     * @param $tableName
     * @return bool|\Bitrix\Main\ORM\Data\DataManager
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getTableClass($tableName)
    {
        if (!static::$bModuleLoaded) {
            static::$bModuleLoaded = Loader::includeModule('highloadblock');
            if (!static::$bModuleLoaded)
                return false;
        }

        if (!array_key_exists($tableName, static::$arCacheClassTableName)) {
            $arHLBlock = HighloadBlockTable::getList(array("filter" => array("=TABLE_NAME" => $tableName)))->fetch();
            if (!is_array($arHLBlock))
                return false;

            $obEntity = HighloadBlockTable::compileEntity($arHLBlock);
            static::$arCacheClassTableName[$tableName] = $obEntity->getDataClass();
        }

        return static::$arCacheClassTableName[$tableName];
    }

    /**
     * @param $name
     * @return bool|mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getTableClassByName($name)
    {
        if (!static::$bModuleLoaded) {
            static::$bModuleLoaded = Loader::includeModule('highloadblock');
            if (!static::$bModuleLoaded)
                return false;
        }

        if (!array_key_exists($name, static::$arCacheClassName)) {
            $arHLBlock = HighloadBlockTable::getList(array("filter" => array("=NAME" => $name)))->fetch();
            if (!is_array($arHLBlock))
                return false;

            $obEntity = HighloadBlockTable::compileEntity($arHLBlock);
            static::$arCacheClassName[$name] = $obEntity->getDataClass();
        }

        return static::$arCacheClassName[$name];
    }

    /**
     * @param $IBLOCK_ID
     * @param $property
     * @return bool|mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getTableClassByProperty($IBLOCK_ID, $property)
    {
        if (!$arProp = \DevBx\Core\Iblock::getPropertyByCode($IBLOCK_ID, $property))
            return false;

        return static::getTableClass($arProp["USER_TYPE_SETTINGS"]["TABLE_NAME"]);
    }

    /**
     * @param $tableName
     * @param $xmlId
     * @return bool|array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getByXmlId($tableName, $xmlId)
    {
        if (empty($xmlId))
            return false;

        if (!isset(static::$arXmlIdCache[$tableName]))
            static::$arXmlIdCache[$tableName] = array();

        if (array_key_exists($xmlId, static::$arXmlIdCache[$tableName]))
            return static::$arXmlIdCache[$tableName][$xmlId];

        if (!$strEntityDataClass = static::getTableClass($tableName))
            return false;

        $arRes = $strEntityDataClass::getList(array(
                "filter" => array("=UF_XML_ID" => $xmlId),
            )
        )->fetch();

        if ($arRes)
            static::$arXmlIdCache[$tableName][$xmlId] = $arRes;

        return $arRes;
    }

    /**
     * @param $iblockId
     * @param $property
     * @param $xmlId
     * @return bool|mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getByPropertyXmlId($iblockId, $property, $xmlId)
    {
        if (!$arProperty = \DevBx\Core\Iblock::getPropertyByCode($iblockId, $property)) {
            return false;
        }

        return static::getByXmlId($arProperty["USER_TYPE_SETTINGS"]["TABLE_NAME"], $xmlId);
    }

    /**
     * @param $tableName
     * @param $value
     * @return bool|mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getXmlIdByName($tableName, $value)
    {
        if (!isset(static::$arXmlIdCache[$tableName]))
            static::$arXmlIdCache[$tableName] = array();

        if (array_key_exists($value, static::$arXmlIdCache[$tableName]))
            return static::$arXmlIdCache[$tableName][$value];

        if (!$strEntityDataClass = static::getTableClass($tableName))
            return false;

        $dbRes = $strEntityDataClass::getList(array(
                "filter" => array("=UF_NAME" => $value),
            )
        );

        $result = array();

        while ($arRes = $dbRes->Fetch())
            $result[] = $arRes["UF_XML_ID"];

        if (!empty($result))
            static::$arXmlIdCache[$tableName][$value] = $result;

        return $result;
    }

    /**
     * @param $iblockId
     * @param $property
     * @param $name
     * @return bool|mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getPropertyXmlIdByName($iblockId, $property, $name)
    {
        if (!$arProperty = Iblock::getPropertyByCode($iblockId, $property))
            return false;

        return static::getXmlIdByName($arProperty["USER_TYPE_SETTINGS"]["TABLE_NAME"], $name);
    }

    /**
     * @param $iblockId
     * @param $prop
     * @param array $arOrder
     * @param array $arFilter
     * @param bool $bTranslit
     * @return array|bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getDistinctValuesByProperty($iblockId, $prop, $arOrder = array(), $arFilter = array(), $bTranslit = true)
    {
        global $CACHE_MANAGER;

        $cacheId = implode('|', array($iblockId, $prop, $bTranslit, serialize($arOrder), serialize($arFilter)));

        $cache = new \CPHPCache;
        if ($cache->StartDataCache(3600, $cacheId, "iblock_find")) {
            if (!Loader::includeModule("iblock"))
                return false;

            if (!static::$bModuleLoaded) {
                static::$bModuleLoaded = Loader::includeModule('highloadblock');
                if (!static::$bModuleLoaded)
                    return false;
            }

            $arProperty = \CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $iblockId, "CODE" => $prop))->Fetch();
            if (!$arProperty)
                return false;

            $propCode = $arProperty["CODE"];

            if (!$strEntityDataClass = static::getTableClass($arProperty["USER_TYPE_SETTINGS"]["TABLE_NAME"]))
                return false;


            if (defined("BX_COMP_MANAGED_CACHE")) {
                $CACHE_MANAGER->StartTagCache("iblock_find");
                \CIBlock::registerWithTagCache($iblockId);
            }

            if (!empty($arOrder))
                $arOrder = array("PROPERTY_".$propCode,"ASC","SORT"=>"ASC");

            $arFilter["IBLOCK_ID"] = $iblockId;
            $arFilter["!PROPERTY_" . $propCode] = false;

            $arXmlId = array();

            $rsElement = \CIBlockElement::GetList($arOrder, $arFilter, array("PROPERTY_" . $propCode));
            while ($arElement = $rsElement->Fetch()) {
                $arXmlId[$arElement["PROPERTY_" . $propCode . "_VALUE"]] = $arElement;
            }

            $arResult = array();

            if (!empty($arXmlId)) {
                $dbRes = $strEntityDataClass::getList(array(
                        "filter" => array("=UF_XML_ID" => array_keys($arXmlId)),
                    )
                );

                while ($arRes = $dbRes->fetch()) {
                    $arRes["COUNT"] = $arXmlId[$arRes["UF_XML_ID"]]["CNT"];

                    if ($bTranslit) {
                        $key = \Cutil::translit($arRes["UF_NAME"], "ru", array("replace_space" => "-", "replace_other" => "-"));
                        $arRes["CODE"] = $key;
                        $arResult[$key] = $arRes;
                    } else {
                        $arResult[] = $arRes;
                    }
                }
            }

            if (defined("BX_COMP_MANAGED_CACHE"))
                $CACHE_MANAGER->EndTagCache();
            $cache->EndDataCache($arResult);
        } else {
            $arResult = $cache->GetVars();
        }

        return $arResult;
    }
}

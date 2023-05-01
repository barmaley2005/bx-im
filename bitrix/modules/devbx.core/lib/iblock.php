<?

namespace DevBx\Core;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Collection;

class Iblock
{
    const cacheTime = 3600;
    static $sectionCache = array();

    static $arSectionCache = array();
    static $arPropCache = array();

    /**
     * @param $IBLOCK_ID
     * @param $propCode
     * @return bool
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getPropertyByCode($IBLOCK_ID, $propCode)
    {
        static $arIblockProp = array();

        $IBLOCK_ID = intval($IBLOCK_ID);

        if (!array_key_exists($IBLOCK_ID, $arIblockProp)) {

            if (!Loader::includeModule('iblock'))
                return false;

            $cacheKey = "getPropertyByCode_" . $IBLOCK_ID;

            $obCache = new \CPHPCache();
            if ($obCache->InitCache(36000, $cacheKey, "/iblock")) {
                $arIblockProp[$IBLOCK_ID] = $obCache->GetVars();
            } else {
                $rsProp = \CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $IBLOCK_ID));
                while ($arProp = $rsProp->Fetch()) {
                    $arIblockProp[$IBLOCK_ID][$arProp["CODE"]] = $arProp;
                    $arIblockProp[$IBLOCK_ID][$arProp["ID"]] = $arProp;
                }

                if ($obCache->StartDataCache()) {
                    if (defined("BX_COMP_MANAGED_CACHE")) {
                        global $CACHE_MANAGER;
                        $CACHE_MANAGER->StartTagCache("/iblock");
                        $CACHE_MANAGER->RegisterTag("iblock_id_" . $IBLOCK_ID);
                        $CACHE_MANAGER->EndTagCache();
                    }

                    $obCache->EndDataCache($arIblockProp[$IBLOCK_ID]);
                }
            }
        }

        if (array_key_exists($propCode, $arIblockProp[$IBLOCK_ID]))
            return $arIblockProp[$IBLOCK_ID][$propCode];

        return false;
    }

    public static function getEnumValuesForProperty($propId, $IBLOCK_ID = false, $key = 'ID')
    {
        static $arCache = array();

        if ($IBLOCK_ID) {
            $cacheId = $propId . '_' . $IBLOCK_ID . '_' . $key;
        } else {
            $cacheId = $propId . '_' . $key;
        }

        if (!array_key_exists($cacheId, $arCache)) {
            $arCache[$cacheId] = array();

            $cacheKey = "getEnumValuesForProperty_" . $cacheId;

            $obCache = new \CPHPCache();
            if ($obCache->InitCache(36000, $cacheKey, "/iblock")) {
                $arCache[$cacheId] = $obCache->GetVars();
            } else {
                if (!Loader::includeModule('iblock'))
                    return array();

                if (!$arProp = \CIBlockProperty::GetByID($propId, $IBLOCK_ID)->Fetch()) {
                    return array();
                }

                $IBLOCK_ID = $arProp["IBLOCK_ID"];

                $rsEnum = \CIBlockPropertyEnum::GetList(array("SORT" => "ASC", "VALUE" => "ASC"), array("PROPERTY_ID" => $arProp["ID"]));
                while ($arEnum = $rsEnum->Fetch()) {
                    $arCache[$cacheId][$arEnum[$key]] = $arEnum;
                }

                if ($obCache->StartDataCache()) {
                    if (defined("BX_COMP_MANAGED_CACHE")) {
                        global $CACHE_MANAGER;
                        $CACHE_MANAGER->StartTagCache("/iblock");
                        $CACHE_MANAGER->RegisterTag("iblock_id_" . $IBLOCK_ID);
                        $CACHE_MANAGER->EndTagCache();
                    }

                    $obCache->EndDataCache($arCache[$cacheId]);
                }
            }
        }
        return $arCache[$cacheId];
    }

    public static function addPropertyIfNotExists($arFields)
    {
        if (!Loader::includeModule('iblock'))
            return false;

        if (!empty($arFields["CODE"])) {
            if ($arProp = \CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $arFields["IBLOCK_ID"], "CODE" => $arFields["CODE"]))->Fetch()) {
                return $arProp["ID"];
            }

        }

        $ibp = new \CIBlockProperty;
        return $ibp->Add($arFields);
    }

    public static function getElement($elementId, $bGetProperties = false)
    {
        static $cache = array();

        if (!Loader::includeModule('iblock'))
            return false;

        $cacheId = $elementId . '_' . ($bGetProperties ? '1' : '0');

        if (array_key_exists($cacheId, $cache))
            return $cache[$cacheId];

        if ($bGetProperties) {
            if (!$obElement = \CIBlockElement::GetByID($elementId)->GetNextElement())
                return false;

            $cache[$cacheId] = $obElement->GetFields();
            $cache[$cacheId]["PROPERTIES"] = $obElement->GetProperties();
        } else {
            $cache[$cacheId] = \CIBlockElement::GetByID($elementId)->GetNext();
        }

        return $cache[$cacheId];
    }

    public static function getSection($sectionId)
    {
        if (array_key_exists($sectionId, static::$sectionCache))
            return static::$sectionCache[$sectionId];

        if (!Loader::includeModule('iblock'))
            return false;

        $arSection = \CIBlockSection::GetByID($sectionId)->GetNext();

        if ($arSection)
            static::$sectionCache[$sectionId] = $arSection;

        return static::$sectionCache[$sectionId];
    }

    /**
     * Возвращает массив разделов по их ID
     *
     * @param array $arSectionId
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function getSectionsArray($arSectionId)
    {
        if (!is_array($arSectionId)) {
            throw new ArgumentException('arSectionId must be array');
        }

        $arResult = array();

        if (!Loader::includeModule('iblock'))
            return $arResult;

        Collection::normalizeArrayValuesByInt($arSectionId);

        foreach ($arSectionId as $key => $sectionId) {
            if (array_key_exists($sectionId, static::$sectionCache)) {
                $arResult[$sectionId] = static::$sectionCache[$sectionId];
                unset($arSectionId[$key]);
            }
        }

        if (!empty($arSectionId)) {
            $dbRes = \CIBlockSection::GetList(array(), array("ID" => $arSectionId));
            while ($arRes = $dbRes->GetNext()) {
                static::$sectionCache[$arRes["ID"]] = $arRes;
                $arResult[$arRes["ID"]] = $arRes;
            }
        }

        return $arResult;
    }

    public static function getRootSection($iblockId, $sectionId)
    {
        static $cache = array();

        if (array_key_exists($sectionId, $cache))
            return $cache[$sectionId];

        if (!Loader::includeModule('iblock'))
            return false;

        $arSection = \CIBlockSection::GetNavChain($iblockId, $sectionId)->GetNext();
        if (!$arSection)
        {
            $arSection = \CIBlockSection::GetByID($sectionId)->GetNext();
        }

        if (!$arSection)
            return false;

        return $cache[$sectionId];
    }

    public static function geSectionChildIDArray($sectionId)
    {
        static $cache = array();

        $sectionId = intval($sectionId);

        if (array_key_exists($sectionId, $cache))
            return $cache[$sectionId];

        if (!Loader::includeModule('iblock'))
            return false;

        if (!$arSection = \CIBlockSection::GetList(array(), array("ID" => $sectionId), false, array("IBLOCK_ID", "LEFT_MARGIN", "RIGHT_MARGIN"))->Fetch())
            return false;

        $arFilter = array(
            "ACTIVE" => "Y",
            "GLOBAL_ACTIVE" => "Y",
            "IBLOCK_ID" => $arSection["IBLOCK_ID"],
            "LEFT_MARGIN" => $arSection["LEFT_MARGIN"] + 1,
            "RIGHT_MARGIN" => $arSection["RIGHT_MARGIN"],
        );

        $arResult = array();

        $rsSubSection = \CIBlockSection::GetList(array("left_margin" => "asc"), $arFilter, false, array("ID"));
        while ($arSubSection = $rsSubSection->Fetch()) {
            $arResult[] = $arSubSection["ID"];
        }

        $cache[$sectionId] = $arResult;
        return $cache[$sectionId];
    }

    public static function getIblockItems($arSort, $arFilter, $arSelect, $limit = 0, $arGroup = false, $bSkipCache = false, $keyField = "ID")
    {
        global $CACHE_MANAGER;

        if (!is_array($arSort))
            $arSort = array();

        if (!is_array($arFilter))
            $arFilter = array();

        if (!is_array($arSelect))
            $arSelect = array();

        if (!is_array($arGroup))
            $arGroup = false;

        if (isset($arFilter["ID"]) && is_array($arFilter["ID"])) {
            Collection::normalizeArrayValuesByInt($arFilter["ID"]);
        }

        if (isset($arFilter["=ID"]) && is_array($arFilter["=ID"])) {
            Collection::normalizeArrayValuesByInt($arFilter["=ID"]);
        }

        if (!$bSkipCache) {
            $cacheId = serialize(array($arSort, $arFilter, $arSelect, $limit, $arGroup, $keyField));

            $cache = new \CPHPCache;
            if (!$cache->StartDataCache(static::cacheTime, $cacheId, "iblock_catalog")) {
                return $cache->GetVars();
            }
        }

        if (!Loader::includeModule('iblock'))
            return false;

        $arItems = array();

        $arNav = false;
        if ($limit > 0)
            $arNav = array("nTopCount" => $limit);

        $arSelect = array_merge(array("IBLOCK_ID"), $arSelect);

        $arIBlockRes = array();

        $rsElement = \CIBlockElement::GetList($arSort, $arFilter, $arGroup, $arNav, $arSelect);
        if (is_object($rsElement)) {

            while ($arElement = $rsElement->Fetch()) {
                if (empty($arElement[$keyField])) {
                    $arItems[] = $arElement;
                } else {
                    $arItems[$arElement[$keyField]] = $arElement;
                }

                $arIBlockRes[$arElement["IBLOCK_ID"]] = true;
            }

            $arIBlockId = array_keys($arIBlockRes);

        } else {
            if (isset($arFilter["IBLOCK_ID"])) {
                if (!is_array($arFilter["IBLOCK_ID"]))
                    $arIBlockId = array($arFilter["IBLOCK_ID"]); else
                    $arIBlockId = $arFilter["IBLOCK_ID"];
            } else {
                $arIBlockId = array();
            }

            $arItems = $rsElement;
        }

        if (!$bSkipCache) {
            if (defined("BX_COMP_MANAGED_CACHE") && !empty($arIBlockRes)) {
                $CACHE_MANAGER->StartTagCache("iblock_catalog");

                foreach ($arIBlockId as $iblockId) {
                    \CIBlock::registerWithTagCache($iblockId);
                }

                $CACHE_MANAGER->EndTagCache();
            }
            $cache->EndDataCache($arItems);
        }

        return $arItems;
    }

    public static function getSectionValueRecursive($iblockId, $sectionId, $valueName, $defaultValue = false)
    {
        if (!Loader::includeModule('iblock'))
            return false;

        $arSelect = array(
            "IBLOCK_ID",
            "ID",
            "IBLOCK_SECTION_ID",
        );

        if (!in_array($valueName, $arSelect))
            $arSelect[] = $valueName;

        if (!$iblockId) {
            $arSection = \CIBlockSection::GetList(array(), array("ID" => $sectionId), false, array("IBLOCK_ID"))->Fetch();
            if (!$arSection)
                return $defaultValue;

            $iblockId = $arSelect["IBLOCK_ID"];
        }

        while (true) {
            $arSection = \CIBlockSection::GetList(array(), array("IBLOCK_ID" => $iblockId, "ID" => $sectionId), false, $arSelect)->Fetch();
            if ($arSection[$valueName])
                return $arSection[$valueName];

            $sectionId = $arSection["IBLOCK_SECTION_ID"];
            if ($sectionId <= 0)
                break;
        }

        return $defaultValue;
    }

    public static function getSectionByName($IBLOCK_ID, $NAME, $PARENT_SECTION_ID = 0)
    {
        $NAME = trim($NAME);

        $cacheKey = $IBLOCK_ID . '-' . $PARENT_SECTION_ID;

        if (array_key_exists($cacheKey, static::$arSectionCache)) {
            if (array_key_exists($NAME, static::$arSectionCache[$cacheKey])) {
                return static::$arSectionCache[$cacheKey][$NAME];
            }
        } else {
            static::$arSectionCache[$cacheKey] = array();
        }

        if (!Loader::includeModule('iblock'))
            return false;

        $arFilter = array(
            "IBLOCK_ID" => $IBLOCK_ID,
            "=NAME" => $NAME,
        );

        if ($PARENT_SECTION_ID > 0)
            $arFilter["SECTION_ID"] = $PARENT_SECTION_ID; else
            $arFilter["DEPTH_LEVEL"] = 1;

        $rsSection = \CIBlockSection::GetList(array(), $arFilter, false, array("ID"));
        if (!$arSection = $rsSection->Fetch())
            return false;

        static::$arSectionCache[$cacheKey][$NAME] = $arSection["ID"];

        return $arSection["ID"];
    }

    public static function getSectionByXmlId($IBLOCK_ID, $XML_ID, $PARENT_SECTION_ID = 0)
    {
        if (!Loader::includeModule('iblock'))
            return false;

        $arFilter = array(
            "IBLOCK_ID" => $IBLOCK_ID,
            "=XML_ID" => $XML_ID,
        );

        if ($PARENT_SECTION_ID > 0)
            $arFilter["SECTION_ID"] = $PARENT_SECTION_ID; else
            $arFilter["DEPTH_LEVEL"] = 1;

        $rsSection = \CIBlockSection::GetList(array(), $arFilter, false, array("ID"));
        if (!$arSection = $rsSection->Fetch())
            return false;
        return $arSection["ID"];
    }

    /**
     * @param $arFields
     * @return bool|int
     * @throws SystemException
     */
    public static function addSection($arFields)
    {
        if (!Loader::includeModule('iblock'))
            return false;

        if (!isset($arFields["CODE"])) {
            $arFields["CODE"] = static::getCodeForSection($arFields["IBLOCK_ID"], $arFields["NAME"]);
            $CODE = $arFields["CODE"];
        } else {
            $CODE = $arFields["CODE"];
        }
        $ORIG_CODE = $CODE;
        $num = 0;

        $bs = new \CIBlockSection();

        while (true) {
            if (!$bs->GetList(array(), array("IBLOCK_ID" => $arFields["IBLOCK_ID"], "=CODE" => $CODE), false, array("ID"))->Fetch())
                break;
            $num++;
            $CODE = $ORIG_CODE . '_' . $num;
        }
        $arFields["CODE"] = $CODE;

        $sectionId = $bs->Add($arFields);
        if (!$sectionId)
            throw new SystemException($bs->LAST_ERROR);

        return $sectionId;
    }

    public static function addSectionByName($IBLOCK_ID, $NAME, $PARENT_SECTION_ID = 0)
    {
        if (!Loader::includeModule('iblock'))
            return false;

        $sectionId = static::getSectionByName($IBLOCK_ID, $NAME, $PARENT_SECTION_ID);
        if ($sectionId !== false)
            return $sectionId;

        $arFields = array(
            "IBLOCK_ID" => $IBLOCK_ID,
            "NAME" => $NAME,
            "SORT" => 100,
        );

        $arFilter = array(
            "IBLOCK_ID" => $IBLOCK_ID
        );

        if ($PARENT_SECTION_ID != 0) {
            $arFilter["SECTION_ID"] = $PARENT_SECTION_ID;
            $arFields["IBLOCK_SECTION_ID"] = $PARENT_SECTION_ID;
        } else {
            $arFilter["DEPTH_LEVEL"] = 1;
        }

        if ($arSection = \CIBlockSection::GetList(array("SORT" => "DESC"), $arFilter)->Fetch()) {
            $arFields["SORT"] += $arSection["SORT"];
        }

        return static::addSection($arFields);
    }

    private static function readIblockProperties($IBLOCK_ID)
    {
        if (!array_key_exists($IBLOCK_ID, static::$arPropCache)) {
            static::$arPropCache[$IBLOCK_ID]["PROPERTIES"] = array();

            $arProperties = &static::$arPropCache[$IBLOCK_ID]["PROPERTIES"];

            if (!Loader::includeModule('iblock'))
                return false;

            $rsProp = \CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $IBLOCK_ID));
            while ($arProp = $rsProp->Fetch()) {
                if ($arProp["PROPERTY_TYPE"] == "E") {
                    $arProp["VALUES"] = array();
                    if ($arProp["LINK_IBLOCK_ID"] > 0) {
                        $rsElement = \CIBlockElement::GetList(array(), array("IBLOCK_ID" => $arProp["LINK_IBLOCK_ID"]), false, false, array("ID", "NAME"));
                        while ($arElement = $rsElement->Fetch())
                            $arProp["VALUES"][ToLower($arElement["NAME"])] = $arElement["ID"];
                    }
                } elseif ($arProp["PROPERTY_TYPE"] == "L") {
                    $rsEnum = \CIBlockPropertyEnum::GetList(array(), array("IBLOCK_ID" => $IBLOCK_ID, "PROPERTY_ID" => $arProp["ID"]));
                    while ($arEnum = $rsEnum->Fetch())
                        $arProp["VALUES"][ToLower($arEnum["VALUE"])] = $arEnum["ID"];
                }
                $arProperties[$arProp["ID"]] = $arProp;
            }

            if (isset($arProp))
                unset($arProp);
        }

        return static::$arPropCache[$IBLOCK_ID]["PROPERTIES"];
    }

    /**
     * @param $IBLOCK_ID
     * @param $PROP_CODE
     * @param $VALUE
     * @return array|bool|mixed|string|null
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public static function convertPropertyValue($IBLOCK_ID, $PROP_CODE, $VALUE)
    {
        global $APPLICATION;

        if (!Loader::includeModule('iblock'))
            return false;

        $VALUE = trim($VALUE);

        if (strlen($VALUE) == 0)
            return false;

        static::readIblockProperties($IBLOCK_ID);

        if (!$arProp = static::findPropByCode($IBLOCK_ID, $PROP_CODE))
            return $VALUE;

        $lowVALUE = ToLower($VALUE);

        if ($arProp["PROPERTY_TYPE"] == "F") {
            if (substr($VALUE, 0, 4) == 'http') {
                return \CFile::MakeFileArray($VALUE);
            } else {
                return \CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . $VALUE);
            }
        } elseif ($arProp["PROPERTY_TYPE"] == "E") {
            if (array_key_exists($lowVALUE, $arProp["VALUES"]))
                return $arProp["VALUES"][$lowVALUE];

            if (intval($arProp["LINK_IBLOCK_ID"]) <= 0) {
                throw new SystemException("LINK_IBLOCK_ID is null");
            }

            $el = new \CIBlockElement;

            $arFields = array(
                "IBLOCK_ID" => $arProp["LINK_IBLOCK_ID"],
                "NAME" => $VALUE,
                "ACTIVE" => "Y",
                "CODE" => static::getCodeForElement($arProp["LINK_IBLOCK_ID"], $VALUE),
            );

            $ID = $el->Add($arFields);
            if (!$ID) {
                $err = $el->LAST_ERROR;
                $ex = $APPLICATION->GetException();
                if (is_object($ex))
                    $err .= ': ' . $ex->GetString();

                throw new SystemException($err);
            }

            static::$arPropCache[$IBLOCK_ID]["PROPERTIES"][$arProp["ID"]]["VALUES"][$lowVALUE] = $ID;

            //$arProp["VALUES"][$lowVALUE] = $ID;

            return $ID;

        } elseif ($arProp["PROPERTY_TYPE"] == "L") {
            if (array_key_exists($lowVALUE, $arProp["VALUES"]))
                return $arProp["VALUES"][$lowVALUE];

            if ($arEnum = \CIBlockPropertyEnum::GetList(array(), array("PROPERTY_ID" => $arProp["ID"], "VALUE" => $VALUE))->Fetch()) {
                return $arEnum["ID"];
            }

            $pe = new \CIBlockPropertyEnum;
            $arFields = array(
                "PROPERTY_ID" => $arProp["ID"],
                "VALUE" => $VALUE,
            );

            $ID = $pe->Add($arFields);
            if (!$ID) {
                $ex = $APPLICATION->GetException();
                throw new SystemException(is_object($ex) ? $ex->GetString() : "unknown error");
            }

            //TODO закэшировать

            return $ID;
        } elseif ($arProp["PROPERTY_TYPE"] == "S" && $arProp["USER_TYPE"] == "directory") {
            $arRes = HighloadHelper::getXmlIdByName($arProp["USER_TYPE_SETTINGS"]["TABLE_NAME"], $VALUE);
            if (!empty($arRes)) {
                return reset($arRes);
            }

            $xmlId = \Cutil::translit($VALUE, "ru", array("replace_space" => "_", "replace_other" => "_"));
            $saveXmlId = $xmlId;
            $n = 1;
            while (true) {
                if (!HighloadHelper::getByXmlId($arProp["USER_TYPE_SETTINGS"]["TABLE_NAME"], $xmlId))
                    break;

                $n++;
                $xmlId = $saveXmlId . '_' . $n;
            }

            $arFields = array(
                "UF_NAME" => $VALUE,
                "UF_XML_ID" => $xmlId,
            );

            $cls = HighloadHelper::getTableClass($arProp["USER_TYPE_SETTINGS"]["TABLE_NAME"]);
            if (!class_exists($cls)) {
                throw new \Bitrix\Main\SystemException("INVALID Highload class '" . $cls . "', property:\n" . print_r($arProp, true));
            }
            $cls::add($arFields);

            return $xmlId;
        } else
            return $VALUE;
    }

    public static function findPropByName($IBLOCK_ID, $NAME)
    {
        $arProperties = static::readIblockProperties($IBLOCK_ID);

        $NAME = ToLower(trim($NAME));
        foreach ($arProperties as &$prop) {
            if (ToLower($prop["NAME"]) == $NAME)
                return $prop;
        }

        return false;
    }

    public static function findPropByCode($IBLOCK_ID, $CODE)
    {
        $arProperties = static::readIblockProperties($IBLOCK_ID);

        if (is_numeric($CODE)) {
            if (isset($arProperties[$CODE]))
                return $arProperties[$CODE];
            return false;
        }

        $CODE = ToLower($CODE);
        foreach ($arProperties as $findProp) {
            if (ToLower($findProp["CODE"]) == $CODE) {
                return $findProp;
            }
        }

        return false;
    }

    public static function addProperty($arFields)
    {
        if (!Loader::includeModule("iblock"))
            return false;

        if (!isset($arFields['IBLOCK_ID']) || intval($arFields['IBLOCK_ID'])<=0)
            throw new SystemException('addProperty invalid IBLOCK_ID');

        if (!isset($arFields['NAME']))
            throw new SystemException('addProperty without name');

        $arFields['NAME'] = trim($arFields['NAME']);

        if (empty($arFields['NAME']))
            throw new SystemException('addProperty with empty name');

        if (!isset($arFields["CODE"])) {
            $arFields["CODE"] = \Cutil::translit($arFields['NAME'], "ru", array("replace_space" => "_", "replace_other" => "_"));
        }

        $arFields["CODE"] = ToUpper($arFields["CODE"]);

        if (preg_match('/^[0-9]+$/', $arFields["CODE"])) {
            $arFields["CODE"] = "P_" . $arFields["CODE"];
        }

        if (strlen($arFields["CODE"] > 50))
            $arFields["CODE"] = substr($arFields["CODE"], 0, 50);

        if ($arProp = static::findPropByCode($arFields["IBLOCK_ID"], $arFields["CODE"])) {
            return $arProp;
        }

        if (!isset($arFields["PROPERTY_TYPE"]))
            $arFields["PROPERTY_TYPE"] = "S";

        if (!isset($arFields["SORT"])) {
            $rsProp = \CIBlockProperty::GetList(array("SORT" => "DESC"), array("IBLOCK_ID" => $arFields["IBLOCK_ID"]));
            if ($arProp = $rsProp->Fetch())
                $SORT = $arProp["SORT"]; else
                $SORT = 500;

            $SORT += 100;
            $arFields["SORT"] = $SORT;
        }

        if (!isset($arFields["ACTIVE"])) {
            $arFields["ACTIVE"] = "Y";
        }

        /*
        if (!isset($arFields["SMART_FILTER"])) {
            $arFields["SMART_FILTER"] = "Y";
        }
        */

        $ibp = new \CIBlockProperty;
        $PropertyID = $ibp->Add($arFields);

        if (!$PropertyID) {
            throw new \Bitrix\Main\SystemException($ibp->LAST_ERROR);
        }

        $rsProp = \CIBlockProperty::GetByID($PropertyID);
        if (!$arProp = $rsProp->Fetch()) {
            throw new \Bitrix\Main\SystemException('failed get, created property ' . $arFields["NAME"]);
        }

        static::$arPropCache[$arFields["IBLOCK_ID"]]["PROPERTIES"][$arProp["ID"]] = $arProp;

        return $arProp;
    }

    public static function getCodeForElement($IBLOCK_ID, $name)
    {
        if (!Loader::includeModule("iblock"))
            return false;

        $code = \CUtil::translit($name, "ru", static::getIblockTranslitElement($IBLOCK_ID));

        $saveCode = $code;
        $n = 1;

        while (true) {
            if (!\CIBlockElement::GetList(array(), array("IBLOCK_ID" => $IBLOCK_ID, "=CODE" => $code), false, false, array("ID"))->Fetch())
                break;

            $n++;
            $code = $saveCode . '_' . $n;
        }

        return $code;
    }

    public static function getIblockTranslitSection($IBLOCK_ID)
    {
        $ar = \CIBlock::GetArrayByID($IBLOCK_ID);
        $arTransSettings = $ar['FIELDS']['SECTION_CODE']['DEFAULT_VALUE'];
        return array(
            "max_len" => $arTransSettings['TRANS_LEN'],
            "change_case" => $arTransSettings['TRANS_CASE'],
            "replace_space" => $arTransSettings['TRANS_SPACE'],
            "replace_other" => $arTransSettings['TRANS_OTHER'],
            "delete_repeat_replace" => ('Y' == $arTransSettings['TRANS_EAT'] ? true : false),
            "use_google" => ('Y' == $arTransSettings['USE_GOOGLE'] ? true : false),
        );
    }

    public static function getIblockTranslitElement($IBLOCK_ID)
    {
        $ar = \CIBlock::GetArrayByID($IBLOCK_ID);
        $arTransSettings = $ar['FIELDS']['CODE']['DEFAULT_VALUE'];
        return array(
            "max_len" => $arTransSettings['TRANS_LEN'],
            "change_case" => $arTransSettings['TRANS_CASE'],
            "replace_space" => $arTransSettings['TRANS_SPACE'],
            "replace_other" => $arTransSettings['TRANS_OTHER'],
            "delete_repeat_replace" => ('Y' == $arTransSettings['TRANS_EAT'] ? true : false),
            "use_google" => ('Y' == $arTransSettings['USE_GOOGLE'] ? true : false),
        );
    }

    public static function getCodeForSection($IBLOCK_ID, $name)
    {
        if (!Loader::includeModule("iblock"))
            return false;

        $code = \CUtil::translit($name, "ru", static::getIblockTranslitSection($IBLOCK_ID));
        $saveCode = $code;
        $n = 1;

        while (true) {
            if (!\CIBlockSection::GetList(array(), array("IBLOCK_ID" => $IBLOCK_ID, "=CODE" => $code), false, array("ID"))->Fetch())
                break;

            $n++;
            $code = $saveCode . '_' . $n;
        }

        return $code;
    }

    public static function getPropertyValues($IBLOCK_ID, $ELEMENT_ID, $PROP_CODE)
    {
        if (!Loader::includeModule("iblock"))
            return false;

        $res = array();

        if (!is_numeric($PROP_CODE)) {
            if (!$arProp = static::findPropByCode($IBLOCK_ID, $PROP_CODE))
                return false;

            $PROP_CODE = $arProp["ID"];
        }

        $dbRes = \CIBlockElement::GetPropertyValues($IBLOCK_ID, array("ID" => $ELEMENT_ID), true, array("ID" => $PROP_CODE));
        while ($arRes = $dbRes->Fetch()) {
            if (is_array($arRes[$PROP_CODE])) {
                $res = $arRes[$PROP_CODE];
            } else {
                $res[] = $arRes[$PROP_CODE];
            }

        }

        return $res;
    }

    public static function getItemsWithProperties($IBLOCK_ID, $arFilter)
    {
        if (!Loader::includeModule("iblock"))
            return false;

        $arFilter["IBLOCK_ID"] = $IBLOCK_ID;

        $arItems = array();
        $arItemLink = array();

        $dbElement = \CIBlockElement::GetList(array(), $arFilter, false, false, array("ID", "NAME", "PREVIEW_PICTURE", "PREVIEW_TEXT", "DETAIL_TEXT", "DETAIL_PICTURE"));
        while ($arElement = $dbElement->Fetch()) {
            $arElement["PREVIEW_PICTURE"] = \CFIle::GetFileArray($arElement["PREVIEW_PICTURE"]);
            $arElement["DETAIL_PICTURE"] = \CFIle::GetFileArray($arElement["DETAIL_PICTURE"]);

            $arElement["PROPERTIES"] = array();

            $arItems[$arElement["ID"]] = $arElement;
            $arItemLink[$arElement['ID']] = &$arItems[$arElement["ID"]];
        }

        if (!empty($arItems)) {
            $arPropFilter = array(
                'ID' => array_keys($arItems),
                'IBLOCK_ID' => $IBLOCK_ID,
            );
            \CIBlockElement::GetPropertyValuesArray($arItemLink, $IBLOCK_ID, $arPropFilter);

            foreach ($arItems as $key => &$arElement) {
                foreach ($arElement["PROPERTIES"] as &$arProp) {
                    if ($arProp["USER_TYPE"] == "directory" && !empty($arProp["VALUE"])) {
                        $arProp["VALUE"] = HighloadHelper::getByXmlId($arProp["USER_TYPE_SETTINGS"]["TABLE_NAME"], $arProp["VALUE"]);
                    }
                }
                unset($arProp);
            }

            unset($arElement);
        }

        return $arItems;
    }

    public static function copyIblockProperties($iblockSrc, $iblockDest, $cmpField = 'CODE', $propFilter = array())
    {
        if (!Loader::includeModule("iblock"))
            return false;

        $arIblock = \CIBlock::GetByID($iblockDest)->Fetch();
        if (!is_array($arIblock))
            throw new SystemException('iblock ' . $iblockDest . ' not found');

        if (!is_array($cmpField))
            $cmpField = array($cmpField);

        if (empty($cmpField))
            $cmpField = array('CODE');

        $arNeedProp = array();

        $arSrcFilter = array_merge($propFilter, array('IBLOCK_ID' => $iblockSrc));

        $dbRes = \Bitrix\Iblock\PropertyTable::getList(array('filter' => $arSrcFilter));
        while ($arRes = $dbRes->Fetch()) {

            $arPropDest = false;

            foreach ($cmpField as $field) {
                if (empty($arRes[$field]))
                    continue;

                $arDestFilter = array(
                    'IBLOCK_ID' => $iblockDest,
                    $field => $arRes[$field],
                );

                $arPropDest = \Bitrix\Iblock\PropertyTable::getList(array('filter' => $arDestFilter))->fetch();
                if ($arPropDest)
                    break;
            }

            if (!$arPropDest) {

                $arPropDest = \Bitrix\Iblock\PropertyTable::getList(array('filter' => array('IBLOCK_ID' => $iblockDest, '=CODE' => $arRes['COPDE'])))->fetch();
                if ($arPropDest) {
                    throw new SystemException('duplicate property code ' . $arRes['CODE']);
                }

                $arFields = array(
                    'IBLOCK_ID' => $iblockDest,
                    'NAME' => $arRes['NAME'],
                    'ACTIVE' => $arRes['ACTIVE'],
                    'SORT' => $arRes['SORT'],
                    'CODE' => $arRes['CODE'],
                    'XML_ID' => $arRes['XML_ID'],
                    'DEFAULT_VALUE' => $arRes['DEFAULT_VALUE'],
                    'PROPERTY_TYPE' => $arRes['PROPERTY_TYPE'],
                    'ROW_COUNT' => $arRes['ROW_COUNT'],
                    'COL_COUNT' => $arRes['COL_COUNT'],
                    'LIST_TYPE' => $arRes['LIST_TYPE'],
                    'MULTIPLE' => $arRes['MULTIPLE'],
                    'FILE_TYPE' => $arRes['FILE_TYPE'],
                    'MULTIPLE_CNT' => $arRes['MULTIPLE_CNT'],
                    'LINK_IBLOCK_ID' => $arRes['LINK_IBLOCK_ID'],
                    'WITH_DESCRIPTION' => $arRes['WITH_DESCRIPTION'],
                    'SEARCHABLE' => $arRes['SEARCHABLE'],
                    'FILTRABLE' => $arRes['FILTRABLE'],
                    'IS_REQUIRED' => $arRes['IS_REQUIRED'],
                    'VERSION' => $arIblock['VERSION'],
                    'USER_TYPE' => $arRes['USER_TYPE'],
                    'USER_TYPE_SETTINGS_LIST' => $arRes['USER_TYPE_SETTINGS_LIST'],
                    'HINT' => $arRes['HINT'],
                );

                $ibp = new \CIBlockProperty();
                $destPropId = $ibp->Add($arFields);
                if (!$destPropId)
                    throw new \Bitrix\Main\SystemException($ibp->LAST_ERROR);
            } else {
                $destPropId = $arPropDest['ID'];
            }

            if ($arRes['PROPERTY_TYPE'] == 'L') {
                $dbEnum = \CIBlockPropertyEnum::GetList(array(), array('PROPERTY_ID' => $arRes['ID']));
                while ($arEnum = $dbEnum->Fetch()) {
                    $arEnumDest = \CIBlockPropertyEnum::GetList(array(), array('PROPERTY_ID' => $destPropId, 'XML_ID' => $arEnum['XML_ID']))->Fetch();
                    if (!$arEnumDest) {
                        $arFields = array(
                            'PROPERTY_ID' => $destPropId,
                            'VALUE' => $arEnum['VALUE'],
                            'DEF' => $arEnum['DEF'],
                            'SORT' => $arEnum['SORT'],
                            'XML_ID' => $arEnum['XML_ID'],
                        );

                        \CIBlockPropertyEnum::Add($arFields);
                    }
                }
            }
        }

        return true;
    }
}

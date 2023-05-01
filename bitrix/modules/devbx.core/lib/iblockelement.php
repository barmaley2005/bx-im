<?php

namespace DevBx\Core;

use Bitrix\Main\DB\ArrayResult;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

class IBlockElement
{

    protected static $bModule = null;

    public static function getDisplayElement($elementId)
    {
        $elementId = intval($elementId);
        if ($elementId <= 0)
            return false;

        $cache = new \CPHPCache();

        if ($cache->InitCache(60 * 60 * 24, 'iblockelement_' . $elementId, "/iblock")) {
            return $cache->GetVars();
        } else {
            if (self::$bModule === null) {
                self::$bModule = Loader::includeModule("iblock");
            }

            if (!self::$bModule)
                return false;

            $obElement = \CIBlockElement::GetByID($elementId)->GetNextElement();
            if (!$obElement)
                return false;

            $arElement = $obElement->GetFields();

            $arElement['PREVIEW_PICTURE'] = \CFile::GetFileArray($arElement['PREVIEW_PICTURE']);
            $arElement['DETAIL_PICTURE'] = \CFile::GetFileArray($arElement['DETAIL_PICTURE']);

            $arElement['PROPERTIES'] = $obElement->GetProperties();
            $arElement['DISPLAY_PROPERTIES'] = [];

            foreach ($arElement["PROPERTIES"] as $pid => &$prop) {
                if (
                    (is_array($prop["VALUE"]) && count($prop["VALUE"]) > 0)
                    || (!is_array($prop["VALUE"]) && $prop["VALUE"] <> '')
                ) {
                    $arElement["DISPLAY_PROPERTIES"][$pid] = \CIBlockFormatProperties::GetDisplayValue($arElement, $prop, "catalog_out");
                }
            }
            unset($prop);

            if ($cache->StartDataCache()) {
                if (defined("BX_COMP_MANAGED_CACHE")) {
                    global $CACHE_MANAGER;
                    $CACHE_MANAGER->StartTagCache("/iblock");

                    $CACHE_MANAGER->RegisterTag("iblock_id_" . $arElement["IBLOCK_ID"]);

                    $CACHE_MANAGER->EndTagCache();
                }

                $cache->EndDataCache($arElement);;
            }

            return $arElement;
        }
    }

    public static function GetByID($ID)
    {
        if (self::$bModule === null) {
            self::$bModule = Loader::includeModule("iblock");
        }

        if (!self::$bModule)
            return false;

        return static::GetList([], ['=ID' => $ID], false, false, ['*']);
    }

    public static function GetList($arOrder = array("SORT" => "ASC"), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
    {
        if (self::$bModule === null) {
            self::$bModule = Loader::includeModule("iblock");
        }

        if (!self::$bModule)
            return false;

        $cache = new \CPHPCache();

        if ($cache->InitCache(60 * 60 * 24, 'iblockelement_' . serialize(array($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields)), "/iblock")) {
            $ar = $cache->GetVars();

            if (!is_array($ar))
                return $ar;

            $r = new \CIBlockResult(new ArrayResult($ar['ITEMS']));

            $r->arIBlockMultProps = $ar['arIBlockMultProps'];
            $r->arIBlockConvProps = $ar['arIBlockConvProps'];
            $r->arIBlockAllProps = $ar['arIBlockAllProps'];
            $r->arIBlockNumProps = $ar['arIBlockNumProps'];
            $r->arIBlockLongProps = $ar['arIBlockLongProps'];

            if ($arNavStartParams)
                $r->InitNavStartVars($arNavStartParams);
            return $r;
        } else {
            $ibResult = \CIBlockElement::GetList($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields);

            if ($ibResult instanceof \CIBlockResult) {
                $ar = [];
                $arIblockId = [];

                while ($fetchResult = $ibResult->Fetch()) {
                    if (isset($fetchResult['IBLOCK_ID']))
                        $arIblockId[$fetchResult['IBLOCK_ID']] = true;

                    $ar[] = $fetchResult;
                }

                if ($cache->StartDataCache()) {

                    if (defined("BX_COMP_MANAGED_CACHE")) {
                        global $CACHE_MANAGER;
                        $CACHE_MANAGER->StartTagCache("/iblock");

                        foreach ($arIblockId as $iblockId => $tmp) {
                            $CACHE_MANAGER->RegisterTag("iblock_id_" . $iblockId);
                        }


                        $CACHE_MANAGER->EndTagCache();
                    }

                    $cache->EndDataCache(array(
                        'ITEMS' => $ar,
                        'arIBlockMultProps' => $ibResult->arIBlockMultProps,
                        'arIBlockConvProps' => $ibResult->arIBlockConvProps,
                        'arIBlockAllProps' => $ibResult->arIBlockAllProps,
                        'arIBlockNumProps' => $ibResult->arIBlockNumProps,
                        'arIBlockLongProps' => $ibResult->arIBlockLongProps,
                    ));
                }

                $r = new \CIBlockResult(new ArrayResult($ar));
                $r->arIBlockMultProps = $ibResult->arIBlockMultProps;
                $r->arIBlockConvProps = $ibResult->arIBlockConvProps;
                $r->arIBlockAllProps = $ibResult->arIBlockAllProps;
                $r->arIBlockNumProps = $ibResult->arIBlockNumProps;
                $r->arIBlockLongProps = $ibResult->arIBlockLongProps;

                if ($arNavStartParams)
                    $r->InitNavStartVars($arNavStartParams);

                return $r;
            } else {
                if (is_object($ibResult))
                    throw new SystemException('unknown object ' . get_class($ibResult));


                if ($cache->StartDataCache()) {
                    $cache->EndDataCache($ibResult);
                }

                return $ibResult;
            }

        }
    }

}
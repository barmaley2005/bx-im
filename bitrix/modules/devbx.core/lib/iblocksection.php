<?php

namespace DevBx\Core;

use Bitrix\Main\DB\ArrayResult;
use Bitrix\Main\Loader;

class IBlockSection {

    protected static $bModule = null;

    public static function GetList($arOrder=array("SORT"=>"ASC"), $arFilter=array(), $bIncCnt = false, $arSelect = array(), $arNavStartParams=false)
    {
        if (self::$bModule === null)
        {
            self::$bModule = Loader::includeModule("iblock");
        }

        if (!self::$bModule)
            return false;

        $cache = new \CPHPCache();

        if ($cache->InitCache(60*60*24, 'iblocksection_'.serialize(array($arOrder, $arFilter, $bIncCnt, $arSelect, $arNavStartParams)), "/iblock"))
        {
            $ar = $cache->GetVars();

            if (!is_array($ar))
                return $ar;

            $r = new \CIBlockResult(new ArrayResult($ar));
            if ($arNavStartParams !== false)
                $r->InitNavStartVars($arNavStartParams);
            return $r;
        } else
        {
            $r = \CIBlockSection::GetList($arOrder, $arFilter, $bIncCnt, $arSelect, $arNavStartParams);

            if ($r instanceof \CIblockResult) {
                $ar = [];

                while ($fetchResult = $r->Fetch())
                {
			$ar[] = $fetchResult;
                }

                if ($cache->StartDataCache())
                    $cache->EndDataCache($ar);

                $r = new \CIblockResult(new ArrayResult($ar));
                if ($arNavStartParams !== false)
                    $r->InitNavStartVars($arNavStartParams);

                return $r;
            } else
            {
                if ($cache->StartDataCache())
                    $cache->EndDataCache($r);

                return $r;
            }

        }
    }

}
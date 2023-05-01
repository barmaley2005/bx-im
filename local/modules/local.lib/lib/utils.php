<?php

namespace Local\Lib;

use Bitrix\Main\File\Internal\FileHashTable;

class Utils
{

    public static function includePageFile($filename = 'index.php')
    {
        global $APPLICATION, $USER, $DB;

        $page = $APPLICATION->GetCurPage(false);

        $includePage = substr($page, strlen(SITE_DIR));

        $includePage = trim($includePage, '/');

        if (empty($includePage)) {
            include($_SERVER['DOCUMENT_ROOT'] . SITE_DIR . 'include/home/' . $filename);
            return;
        }

        $arPage = explode('/', $includePage);

        while (count($arPage)) {
            $includePage = $_SERVER['DOCUMENT_ROOT'] . SITE_DIR . 'include/' . implode('/', $arPage) . '/' . $filename;

            if (file_exists($includePage)) {
                include($includePage);
                return;
            }

            array_pop($arPage);
        }
    }

    public static function getCatalogIblockId()
    {
        static $iblockId = false;

        if ($iblockId)
            return $iblockId;

        \Bitrix\Main\Loader::includeModule('iblock');

        $arIblock = \Bitrix\Iblock\IblockTable::getList([
            'filter' => [
                '=LIST_PAGE_URL' => '#SITE_DIR#/catalog/',
                '=LID' => SITE_ID,
            ],
        ])->fetch();

        if (!$arIblock)
            return false;

        $iblockId = $arIblock['ID'];

        return $iblockId;
    }

    public static function getIblockIdByCode($code)
    {
        static $arCache = array();

        if (isset($arCache[$code]))
            return $arCache[$code];

        \Bitrix\Main\Loader::includeModule('iblock');

        $arIblock = \CIBlock::GetList([], ['CODE' => $code, 'LID' => SITE_ID])->Fetch();

        if (!$arIblock)
            return false;

        $arCache[$code] = $arIblock['ID'];
        return $arCache[$code];
    }

    protected static function addImageToArray(array &$ar, array $arImage)
    {
        $row = FileHashTable::getRowById($arImage['ID']);
        if ($row) {
            $hash = $row['FILE_HASH'];
        } else {
            $hash = hash_file("md5", $_SERVER['DOCUMENT_ROOT'].$arImage['SRC']);

            FileHashTable::add(array(
                'FILE_ID' => $arImage['ID'],
                'FILE_SIZE' => $arImage['FILE_SIZE'],
                'FILE_HASH' => $hash,
            ));
        }

        if (!array_key_exists($hash, $ar))
        {
            $ar[$hash] = $arImage;
        }
    }

    public static function getCatalogElementImages(array $arResult, $useOffers = true): array
    {
        $arImages = array();

        if (is_array($arResult['DETAIL_PICTURE']) && $arResult['DETAIL_PICTURE']['ID']>0)
        {
            static::addImageToArray($arImages, $arResult['DETAIL_PICTURE']);
        }

        if (is_array($arResult['PROPERTIES']['MORE_PHOTO']['VALUE']))
        {
            foreach ($arResult['PROPERTIES']['MORE_PHOTO']['VALUE'] as $fileId)
            {
                $arFile = \CFile::GetFileArray($fileId);
                if (is_array($arFile))
                {
                    static::addImageToArray($arImages, $arFile);
                }
            }
        }

        if ($useOffers && is_array($arResult['OFFERS']))
        {
            foreach ($arResult['OFFERS'] as $arOffer)
            {
                if (is_array($arOffer['DETAIL_PICTURE']) && $arOffer['DETAIL_PICTURE']['ID']>0)
                {
                    static::addImageToArray($arImages, $arOffer['DETAIL_PICTURE']);
                }

                if (is_array($arOffer['PROPERTIES']['MORE_PHOTO']['VALUE']))
                {
                    foreach ($arOffer['PROPERTIES']['MORE_PHOTO']['VALUE'] as $fileId)
                    {
                        $arFile = \CFile::GetFileArray($fileId);
                        if (is_array($arFile))
                        {
                            static::addImageToArray($arImages, $arFile);
                        }
                    }
                }
            }
        }

        return $arImages;
    }
}
<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var CBitrixComponentTemplate $this */

foreach ($arResult['ITEMS'] as &$arItem)
{
    $arPhoto = array();

    if (is_array($arItem['PREVIEW_PICTURE']))
    {
        $arPhoto[] = $arItem['PREVIEW_PICTURE'];
    }

    if (is_array($arItem['PROPERTIES']['MORE_PHOTO']['VALUE']) && !empty($arItem['PROPERTIES']['MORE_PHOTO']['VALUE']))
    {
        foreach ($arItem['PROPERTIES']['MORE_PHOTO']['VALUE'] as $value)
        {
            $arFile = \CFile::GetFileArray($value);
            if (is_array($arFile))
            {
                $arPhoto[] = $arFile;
            }
        }
    }

    $arItem['PHOTO'] = $arPhoto;
}
unset($arItem);

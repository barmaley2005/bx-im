<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var CBitrixComponentTemplate $this */

$arItem = &$arResult['ITEM'];

$colorEntity = false;

if (is_array($arItem['OFFERS']) && !empty($arItem['OFFERS'])) {
    $arResult['OFFER_COLOR'] = array();

    foreach ($arItem['OFFERS'] as &$arOffer) {
        \Local\Lib\Image::resizeArray(
            array('width' => 260, 'height' => 450),
            array(
                &$arOffer,
                'PREVIEW_PICTURE',
                &$arOffer,
                'DETAIL_PICTURE',
            )
        );

        if (!empty($arOffer['PROPERTIES']['COLOR_REF']['VALUE'])) {
            if (!$colorEntity) {
                $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getList([
                    'filter' => [
                        '=TABLE_NAME' => $arOffer['PROPERTIES']['COLOR_REF']['USER_TYPE_SETTINGS']['TABLE_NAME']
                    ],
                ])->fetch();

                if (!$hlblock)
                    throw new \Bitrix\Main\SystemException('Failed get highload block');

                $colorEntity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
            }

            $color = $arOffer['PROPERTIES']['COLOR_REF']['~VALUE'];

            if (!isset($arItem['OFFER_COLOR'][$color])) {
                $row = $colorEntity->getDataClass()::getList([
                    'filter' => [
                        '=UF_XML_ID' => $color
                    ],
                ])->fetch();

                if (!$row)
                    continue;

                $row['UF_FILE'] = \CFile::GetFileArray($row['UF_FILE']);

                $row['OFFERS'] = array();
                $row['DETAIL_PAGE_URL'] = $arItem['DETAIL_PAGE_URL'] . '?oid=' . $arOffer['ID'];

                $arResult['OFFER_COLOR'][$color] = $row;
            }

            $arResult['OFFER_COLOR'][$color]['OFFERS'][] = &$arOffer;
        }
    }
}

\Local\Lib\Image::resizeArray(
    array('width' => 260, 'height' => 450),
    array(
        &$arItem,
        'PREVIEW_PICTURE',
        &$arItem,
        'DETAIL_PICTURE',
        &$arItem,
        'MORE_PHOTO',
    )
);

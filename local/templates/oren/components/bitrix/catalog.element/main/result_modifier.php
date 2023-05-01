<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var CBitrixComponentTemplate $this */

\Bitrix\Main\Loader::includeModule('highloadblock');

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

if ($arParams['OID']) {
    $arResult['OFFER_ID_SELECTED'] = $arParams['OID'];
}

if (!empty($arResult['OFFERS']))
{
    $intSelected = -1;

    $arTreeProp = array();
    $arTreePropValues = array();
    $arHLProp = array();

    foreach ($arResult['OFFERS'] as $keyOffer=>&$arOffer)
    {
        if ($arResult['OFFER_ID_SELECTED'] > 0)
            $foundOffer = ($arResult['OFFER_ID_SELECTED'] == $arOffer['ID']);
        else
            $foundOffer = $arOffer['CAN_BUY'];

        if ($foundOffer)
            $intSelected = $keyOffer;

        foreach ($arParams['OFFER_TREE_PROPS'] as $propCode)
        {
            $arProp = $arOffer['PROPERTIES'][$propCode];

            if ($arProp['VALUE'])
            {
                $value = $arOffer['PROPERTIES'][$propCode]['VALUE'];

                if ($arTreePropValues[$propCode][$value])
                    continue;

                $arTreeProp[$propCode] = array(
                    'NAME' => $arProp['NAME'],
                    'USER_TYPE' => $arProp['USER_TYPE'],
                );

                if ($arProp['USER_TYPE'] == 'directory')
                {
                    $arHLProp[$propCode] = $arProp['USER_TYPE_SETTINGS'];
                }

                $arTreePropValues[$propCode][$value] = $value;
            }
        }

        $arOffer['DISPLAY_PRICE'] = $arOffer['ITEM_PRICES'][$arOffer['ITEM_PRICE_SELECTED']];
    }

    foreach ($arHLProp as $propCode=>$settings)
    {
        $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getList([
            'filter' => [
                '=TABLE_NAME' => $settings['TABLE_NAME']
            ],
        ])->fetch();

        if ($hlblock)
        {
            $hlEntity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);

            $iterator = $hlEntity->getDataClass()::getList([
                'filter' => [
                    '=UF_XML_ID' => $arTreePropValues[$propCode]
                ],
            ]);

            while ($row = $iterator->fetch())
            {
                $row['UF_FILE'] = \CFile::GetFileArray($row['UF_FILE']);
                $arTreePropValues[$propCode][$row['UF_XML_ID']] = $row;
            }
        }
    }

    if (-1 == $intSelected){
        $intSelected = 0;
    }

    $arResult['OFFERS_SELECTED'] = $intSelected;
    $arResult['DISPLAY_PRICE'] = $arResult['OFFERS'][$intSelected]['DISPLAY_PRICE'];

    $arResult['TREE_PROP'] = $arTreeProp;
    $arResult['TREE_PROP_VALUES'] = $arTreePropValues;
} else {
    $arResult['DISPLAY_PRICE'] = $arResult['ITEM_PRICES'][$arResult['ITEM_PRICE_SELECTED']];
}

$arResult['MORE_PHOTO'] = \Local\Lib\Utils::getCatalogElementImages($arResult);

foreach ($arResult['MORE_PHOTO'] as $k=>$arFile)
{
    $arPreview = \CFile::ResizeImageGet($arFile, array('width'=>95,'height'=>124),BX_RESIZE_IMAGE_PROPORTIONAL);

    $arResult['MORE_PHOTO'][$k]['PREVIEW_SRC'] = $arPreview['src'];
}

$iblockId = \Local\Lib\Utils::getIblockIdByCode('STYLIST_ADVICE');

if ($iblockId)
{
    $arFilter = array(
        'IBLOCK_ID' => $iblockId,
        'ACTIVE' => 'Y',
        '=PROPERTY_PRODUCT' => $arResult['ID'],
    );

    $obElement = \CIBlockElement::GetList([],$arFilter)->GetNextElement();
    if ($obElement)
    {
        $arResult['STYLIST'] = $obElement->GetFields();
        $arResult['STYLIST']['PROPERTIES'] = $obElement->GetProperties();

        if ($arResult['STYLIST']['PROPERTIES']['USER']['VALUE'])
        {
            $arUser = \CUser::GetByID($arResult['STYLIST']['PROPERTIES']['USER']['VALUE'])->Fetch();

            $arResult['STYLIST']['STYLIST_NAME'] = \CUser::FormatName(CSite::GetNameFormat(), $arUser);
            $arResult['STYLIST']['AVATAR'] = \CFile::GetFileArray($arUser['PERSONAL_PHOTO']);
            $arResult['STYLIST']['STYLIST_POSITION'] = $arUser['WORK_POSITION'];
        }
    }
}

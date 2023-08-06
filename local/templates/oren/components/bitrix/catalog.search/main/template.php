<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var \CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var array $templateData */
/** @var \CBitrixComponent $component */
$this->setFrameMode(true);

use Bitrix\Main\Loader;

$this->setFrameMode(true);

global $searchFilter;

$elementOrder = [];
if ($arParams['USE_SEARCH_RESULT_ORDER'] === 'N')
{
    $elementOrder = [
        "ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
        "ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
        "ELEMENT_SORT_FIELD2" => $arParams["ELEMENT_SORT_FIELD2"],
        "ELEMENT_SORT_ORDER2" => $arParams["ELEMENT_SORT_ORDER2"],
    ];
}

if (Loader::includeModule('search'))
{
    $arElements = $APPLICATION->IncludeComponent(
        "bitrix:search.page",
        ".default",
        [
            "RESTART" => $arParams["RESTART"],
            "NO_WORD_LOGIC" => $arParams["NO_WORD_LOGIC"],
            "USE_LANGUAGE_GUESS" => $arParams["USE_LANGUAGE_GUESS"],
            "CHECK_DATES" => $arParams["CHECK_DATES"],
            "arrFILTER" => [
                "iblock_".$arParams["IBLOCK_TYPE"],
            ],
            "arrFILTER_iblock_".$arParams["IBLOCK_TYPE"] => [
                $arParams["IBLOCK_ID"],
            ]	,
            "USE_TITLE_RANK" => $arParams['USE_TITLE_RANK'],
            "DEFAULT_SORT" => "rank",
            "FILTER_NAME" => "",
            "SHOW_WHERE" => "N",
            "arrWHERE" => [],
            "SHOW_WHEN" => "N",
            "PAGE_RESULT_COUNT" => ($arParams["PAGE_RESULT_COUNT"] ?? 50),
            "DISPLAY_TOP_PAGER" => "N",
            "DISPLAY_BOTTOM_PAGER" => "N",
            "PAGER_TITLE" => "",
            "PAGER_SHOW_ALWAYS" => "N",
            "PAGER_TEMPLATE" => "N",
        ],
        $component,
        [
            'HIDE_ICONS' => 'Y',
        ]
    );
    if (!empty($arElements) && is_array($arElements))
    {
        $searchFilter = [
            "ID" => $arElements,
        ];
        if ($arParams['USE_SEARCH_RESULT_ORDER'] === 'Y')
        {
            $elementOrder = [
                "ELEMENT_SORT_FIELD" => "ID",
                "ELEMENT_SORT_ORDER" => $arElements,
            ];
        }
    }
    else
    {
        if (is_array($arElements))
        {
            ?>
            <div class="elected-container _empty">
                <div class="elected-content">
                    <div class="elected-content__icon">
                        <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                    d="M20.432 35.2353C27.3808 34.1178 37.6379 20.824 37.6379 13.8825C37.6379 8.52974 33.8879 4.70605 28.8143 4.70605C24.5132 4.70605 20.9832 8.17649 19.9908 10.8827C18.9985 8.17649 15.4685 4.70605 11.1673 4.70605C6.09375 4.70605 2.34375 8.52974 2.34375 13.8825C2.34375 20.824 12.6014 34.1178 19.5496 35.2353C19.6951 35.2667 19.8426 35.2864 19.9908 35.2943C20.135 35.255 20.2832 35.2352 20.432 35.2353V35.2353Z"
                                    stroke="#877569" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>

                    <p class="elected-content__text"><?=GetMessage('SEARCH_NOT_FOUND')?></p>

                    <div class="elected-content__button">
                        <div class="button-box">
                            <a href="<?=SITE_DIR?>catalog/" class="button"><?=GetMessage('SEARCH_OPEN_CATALOG')?></a>
                            <svg class="button-bg" width="238" height="68" viewBox="0 0 238 68" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path
                                        d="M63.8598 11.0954C63.8598 11.0954 87.0187 6.81025 136.7 6.81025C166.972 6.81025 237 14.3733 237 37.169C237 61.644 171.494 67 117.487 67C65.1837 67 0.999788 62.4177 1 37.169C1.00032 -0.818731 136.7 1.0142 136.7 1.0142"
                                        stroke-linecap="round" class="button-bg__elem" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <?
            return;
        }
    }
}
else
{
    $searchQuery = '';
    if (isset($_REQUEST['q']) && is_string($_REQUEST['q']))
        $searchQuery = trim($_REQUEST['q']);
    if ($searchQuery !== '')
    {
        $searchFilter = [
            '*SEARCHABLE_CONTENT' => $searchQuery
        ];
    }
    unset($searchQuery);
}

if (!empty($searchFilter) && is_array($searchFilter))
{

    $componentParams = [
            "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
            "IBLOCK_ID" => $arParams["IBLOCK_ID"],
            "PAGE_ELEMENT_COUNT" => $arParams["PAGE_ELEMENT_COUNT"],
            "LINE_ELEMENT_COUNT" => $arParams["LINE_ELEMENT_COUNT"],
            "PROPERTY_CODE" => $arParams["PROPERTY_CODE"],
            "PROPERTY_CODE_MOBILE" => ($arParams["PROPERTY_CODE_MOBILE"] ?? []),
            "OFFERS_CART_PROPERTIES" => $arParams["OFFERS_CART_PROPERTIES"],
            "OFFERS_FIELD_CODE" => $arParams["OFFERS_FIELD_CODE"],
            "OFFERS_PROPERTY_CODE" => $arParams["OFFERS_PROPERTY_CODE"],
            "OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
            "OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
            "OFFERS_SORT_FIELD2" => $arParams["OFFERS_SORT_FIELD2"],
            "OFFERS_SORT_ORDER2" => $arParams["OFFERS_SORT_ORDER2"],
            "OFFERS_LIMIT" => $arParams["OFFERS_LIMIT"],
            "SECTION_URL" => $arParams["SECTION_URL"],
            "DETAIL_URL" => $arParams["DETAIL_URL"],
            "BASKET_URL" => $arParams["BASKET_URL"],
            "ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
            "PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
            "PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
            "PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
            "SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
            "CACHE_TYPE" => $arParams["CACHE_TYPE"],
            "CACHE_TIME" => $arParams["CACHE_TIME"],
            "DISPLAY_COMPARE" => $arParams["DISPLAY_COMPARE"],
            "PRICE_CODE" => $arParams["~PRICE_CODE"],
            "USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
            "SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
            "PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
            "PRODUCT_PROPERTIES" => $arParams["PRODUCT_PROPERTIES"],
            "USE_PRODUCT_QUANTITY" => $arParams["USE_PRODUCT_QUANTITY"],
            "ADD_PROPERTIES_TO_BASKET" => ($arParams["ADD_PROPERTIES_TO_BASKET"] ?? ''),
            "PARTIAL_PRODUCT_PROPERTIES" => ($arParams["PARTIAL_PRODUCT_PROPERTIES"] ?? ''),
            "CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
            "CURRENCY_ID" => $arParams["CURRENCY_ID"],
            "HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
            "HIDE_NOT_AVAILABLE_OFFERS" => $arParams["HIDE_NOT_AVAILABLE_OFFERS"],
            "DISPLAY_TOP_PAGER" => $arParams["DISPLAY_TOP_PAGER"],
            "DISPLAY_BOTTOM_PAGER" => $arParams["DISPLAY_BOTTOM_PAGER"],
            "PAGER_TITLE" => $arParams["PAGER_TITLE"],
            "PAGER_SHOW_ALWAYS" => $arParams["PAGER_SHOW_ALWAYS"],
            "PAGER_TEMPLATE" => $arParams["PAGER_TEMPLATE"],
            "PAGER_DESC_NUMBERING" => $arParams["PAGER_DESC_NUMBERING"],
            "PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
            "PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],
            "LAZY_LOAD" => ($arParams["LAZY_LOAD"] ?? 'N'),
            "MESS_BTN_LAZY_LOAD" => ($arParams["~MESS_BTN_LAZY_LOAD"] ?? ''),
            "LOAD_ON_SCROLL" => ($arParams["LOAD_ON_SCROLL"] ?? 'N'),
            "FILTER_NAME" => "searchFilter",
            "SECTION_ID" => "",
            "SECTION_CODE" => "",
            "SECTION_USER_FIELDS" => [],
            "INCLUDE_SUBSECTIONS" => "Y",
            "SHOW_ALL_WO_SECTION" => "Y",
            "META_KEYWORDS" => "",
            "META_DESCRIPTION" => "",
            "BROWSER_TITLE" => "",
            "ADD_SECTIONS_CHAIN" => "N",
            "SET_TITLE" => "N",
            "SET_STATUS_404" => "N",
            "CACHE_FILTER" => "N",
            "CACHE_GROUPS" => "N",

            'LABEL_PROP' => ($arParams['LABEL_PROP'] ?? ''),
            'LABEL_PROP_MOBILE' => ($arParams['LABEL_PROP_MOBILE'] ?? ''),
            'LABEL_PROP_POSITION' => ($arParams['LABEL_PROP_POSITION'] ?? ''),
            'ADD_PICT_PROP' => ($arParams['ADD_PICT_PROP'] ?? ''),
            'PRODUCT_DISPLAY_MODE' => ($arParams['PRODUCT_DISPLAY_MODE'] ?? ''),
            'PRODUCT_BLOCKS_ORDER' => ($arParams['PRODUCT_BLOCKS_ORDER'] ?? ''),
            'PRODUCT_ROW_VARIANTS' => ($arParams['PRODUCT_ROW_VARIANTS'] ?? ''),
            'ENLARGE_PRODUCT' => ($arParams['ENLARGE_PRODUCT'] ?? ''),
            'ENLARGE_PROP' => ($arParams['ENLARGE_PROP'] ?? ''),
            'SHOW_SLIDER' => ($arParams['SHOW_SLIDER'] ?? 'Y'),
            'SLIDER_INTERVAL' => ($arParams['SLIDER_INTERVAL'] ?? '3000'),
            'SLIDER_PROGRESS' => ($arParams['SLIDER_PROGRESS'] ?? 'N'),

            'OFFER_ADD_PICT_PROP' => ($arParams['OFFER_ADD_PICT_PROP'] ?? ''),
            'OFFER_TREE_PROPS' => ($arParams['OFFER_TREE_PROPS'] ?? []),
            'PRODUCT_SUBSCRIPTION' => ($arParams['PRODUCT_SUBSCRIPTION'] ?? ''),
            'SHOW_DISCOUNT_PERCENT' => ($arParams['SHOW_DISCOUNT_PERCENT'] ?? ''),
            'SHOW_OLD_PRICE' => ($arParams['SHOW_OLD_PRICE'] ?? ''),
            'SHOW_MAX_QUANTITY' => ($arParams['SHOW_MAX_QUANTITY'] ?? ''),
            'MESS_SHOW_MAX_QUANTITY' => ($arParams['~MESS_SHOW_MAX_QUANTITY'] ?? ''),
            'RELATIVE_QUANTITY_FACTOR' => ($arParams['RELATIVE_QUANTITY_FACTOR'] ?? ''),
            'MESS_RELATIVE_QUANTITY_MANY' => ($arParams['~MESS_RELATIVE_QUANTITY_MANY'] ?? ''),
            'MESS_RELATIVE_QUANTITY_FEW' => ($arParams['~MESS_RELATIVE_QUANTITY_FEW'] ?? ''),
            'MESS_BTN_BUY' => ($arParams['~MESS_BTN_BUY'] ?? ''),
            'MESS_BTN_ADD_TO_BASKET' => ($arParams['~MESS_BTN_ADD_TO_BASKET'] ?? ''),
            'MESS_BTN_SUBSCRIBE' => ($arParams['~MESS_BTN_SUBSCRIBE'] ?? ''),
            'MESS_BTN_DETAIL' => ($arParams['~MESS_BTN_DETAIL'] ?? ''),
            'MESS_NOT_AVAILABLE' => ($arParams['~MESS_NOT_AVAILABLE'] ?? ''),
            'MESS_BTN_COMPARE' => ($arParams['~MESS_BTN_COMPARE'] ?? ''),

            'USE_ENHANCED_ECOMMERCE' => ($arParams['USE_ENHANCED_ECOMMERCE'] ?? ''),
            'DATA_LAYER_NAME' => ($arParams['DATA_LAYER_NAME'] ?? ''),
            'BRAND_PROPERTY' => ($arParams['BRAND_PROPERTY'] ?? ''),

            'TEMPLATE_THEME' => ($arParams['TEMPLATE_THEME'] ?? ''),
            'ADD_TO_BASKET_ACTION' => ($arParams['ADD_TO_BASKET_ACTION'] ?? ''),
            'SHOW_CLOSE_POPUP' => ($arParams['SHOW_CLOSE_POPUP'] ?? ''),
            'COMPARE_PATH' => ($arParams['COMPARE_PATH'] ?? ''),
            'COMPARE_NAME' => ($arParams['COMPARE_NAME'] ?? ''),
            'USE_COMPARE_LIST' => ($arParams['USE_COMPARE_LIST'] ?? ''),
        ] + $elementOrder;

    $APPLICATION->IncludeComponent(
        "bitrix:catalog.section",
        "main",
        $componentParams,
        $arResult["THEME_COMPONENT"],
        [
            'HIDE_ICONS' => 'Y',
        ]
    );
} elseif ($_REQUEST['q']) {
    ?>
    <div class="elected-container _empty">
        <div class="elected-content">
            <div class="elected-content__icon">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M20.432 35.2353C27.3808 34.1178 37.6379 20.824 37.6379 13.8825C37.6379 8.52974 33.8879 4.70605 28.8143 4.70605C24.5132 4.70605 20.9832 8.17649 19.9908 10.8827C18.9985 8.17649 15.4685 4.70605 11.1673 4.70605C6.09375 4.70605 2.34375 8.52974 2.34375 13.8825C2.34375 20.824 12.6014 34.1178 19.5496 35.2353C19.6951 35.2667 19.8426 35.2864 19.9908 35.2943C20.135 35.255 20.2832 35.2352 20.432 35.2353V35.2353Z"
                        stroke="#877569" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>

            <p class="elected-content__text"><?=GetMessage('SEARCH_NOT_FOUND')?></p>

            <div class="elected-content__button">
                <div class="button-box">
                    <a href="<?=SITE_DIR?>catalog/" class="button"><?=GetMessage('SEARCH_OPEN_CATALOG')?></a>
                    <svg class="button-bg" width="238" height="68" viewBox="0 0 238 68" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M63.8598 11.0954C63.8598 11.0954 87.0187 6.81025 136.7 6.81025C166.972 6.81025 237 14.3733 237 37.169C237 61.644 171.494 67 117.487 67C65.1837 67 0.999788 62.4177 1 37.169C1.00032 -0.818731 136.7 1.0142 136.7 1.0142"
                            stroke-linecap="round" class="button-bg__elem" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
    <?
}

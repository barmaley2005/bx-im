<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

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

\Bitrix\Main\Loader::includeModule('devbx.core');
\Bitrix\Main\Loader::includeModule('currency');

CJSCore::Init(['currency','devbx_core_utils']);

\Bitrix\Main\UI\Extension::load("ui.vue3");

$this->addExternalJs(SITE_TEMPLATE_PATH.'/js/theia-sticky-sidebar.min.js');

$containerId = $this->GetEditAreaId('basket');
?>
<section class="section basket" id="<?=$containerId?>">
</section>

<script id="devBxSaleBasket" type="text/html">
    <div class="container">
        <h1 class="title text-left"><?=GetMessage('BASKET_TITLE')?></h1>

        <div class="basket-container">
            <div class="goods">
                <div class="goods-container">
                    <div class="goods-item" v-for="item in basketItems" :key="ID">
                        <button class="goods-item__close" @click.stop.prevent="removeItem(item)">
                            <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3.71875 3.71875L13.2812 13.2812" stroke="#C5A994" stroke-linecap="round"
                                      stroke-linejoin="round" />
                                <path d="M3.71875 13.2812L13.2812 3.71875" stroke="#C5A994" stroke-linecap="round"
                                      stroke-linejoin="round" />
                            </svg>
                        </button>
                        <div class="goods-item__left">
                            <div class="bestseller-head__like" :data-item-id="item.PARENT_PRODUCT_ID ? item.PARENT_PRODUCT_ID : item.PRODUCT_ID" data-action="favorite">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M8.1767 14.094C10.9562 13.647 15.0591 8.32951 15.0591 5.55291C15.0591 3.4118 13.5591 1.88232 11.5296 1.88232C9.80917 1.88232 8.39717 3.2705 8.00023 4.35298C7.60329 3.2705 6.19129 1.88232 4.47082 1.88232C2.44141 1.88232 0.941406 3.4118 0.941406 5.55291C0.941406 8.32951 5.04447 13.647 7.82376 14.094C7.88195 14.1066 7.94094 14.1145 8.00023 14.1176C8.05789 14.1019 8.11718 14.094 8.1767 14.094Z" />
                                </svg>
                            </div>
                            <div class="goods-item__img">
                                <img :src="item.IMAGE_URL"  alt="">
                            </div>

                            <div class="goods-item__content">
                                <a :href="item.DETAIL_PAGE_URL" class="goods-item__article" v-if="item.PROPS_ALL.ARTNUMBER">
                                    <?=GetMessage('BASKET_ARTICLE')?> {{item.PROPS_ALL.ARTNUMBER.VALUE}}
                                </a>
                                <a :href="item.DETAIL_PAGE_URL" class="goods-item__title">{{item.NAME}}</a>

                                <div class="goods-item__box">

                                    <basket-item-sku :item="item">
                                        <template v-slot:default="slotProps">
                                            <div class="goods-item__row">
                                                <div class="goods-item__color _white" v-if="slotProps.sku.IS_IMAGE">
                                                    <img :src=slotProps.sku.VALUE.PICT>
                                                </div>

                                                <div class="goods-item__icon" v-else>
                                                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <rect x="0.5" y="0.5" width="14" height="14" rx="7" stroke="#D2C7BC" />
                                                        <g clip-path="url(#clip0_516_18503)">
                                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                                  d="M4.49959 5.04791V6.375C4.49959 6.58211 4.33179 6.75 4.1248 6.75C3.9178 6.75 3.75 6.58211 3.75 6.375V3.75H6.37358C6.58057 3.75 6.74838 3.91789 6.74838 4.125C6.74838 4.33211 6.58057 4.5 6.37358 4.5H5.01207L6.92147 6.41044C7.06784 6.55689 7.06784 6.79432 6.92147 6.94077C6.77511 7.08722 6.5378 7.08722 6.39143 6.94077L4.49959 5.04791ZM10.5004 9.95233V8.62548C10.5004 8.41841 10.6682 8.25055 10.8752 8.25055C11.0822 8.25055 11.25 8.41841 11.25 8.62548V11.25H8.62642C8.41943 11.25 8.25162 11.0821 8.25162 10.8751C8.25162 10.668 8.41943 10.5001 8.62642 10.5001H9.98793L8.07853 8.59005C7.93216 8.44363 7.93216 8.20623 8.07853 8.05981C8.22489 7.91339 8.4622 7.91339 8.60857 8.05981L10.5004 9.95233Z"
                                                                  fill="#D2C7BC" />
                                                        </g>
                                                        <defs>
                                                            <clipPath id="clip0_516_18503">
                                                                <rect width="9" height="9" fill="white" transform="translate(3 3)" />
                                                            </clipPath>
                                                        </defs>
                                                    </svg>
                                                </div>

                                                <p class="goods-item__name">{{slotProps.sku.VALUE.NAME}}</p>
                                            </div>
                                        </template>
                                    </basket-item-sku>
                                </div>
                            </div>

                            <div class="goods-item__footer">
                                <div class="goods-item__price">
                                    <div class="price-box">
                                        <div class="price-minus" @click.stop.prevent="incrementQuantity(item, -1)">
                                            <svg width="6" height="3" viewBox="0 0 6 3" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0.2 2.2V0.96H5.44V2.2H0.2Z" />
                                            </svg>
                                        </div>
                                        <input class="price-input" type="text" :value="item.QUANTITY">
                                        <div class="price-plus" @click.stop.prevent="incrementQuantity(item, 1)">
                                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none"
                                                                      xmlns="http://www.w3.org/2000/svg">
                                                <path d="M4.12 9.24V0.759999H5.38V9.24H4.12ZM0.4 5.6V4.42H9.1V5.6H0.4Z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <p class="goods-item__divider">x</p>

                                    <div class="goods-item__count" :class="{'_discount': item.SUM_DISCOUNT_PRICE>0}">
                                        <p class="goods-item__new" v-html="item.PRICE_FORMATED"></p>
                                        <p class="goods-item__old" v-html="item.FULL_PRICE_FORMATED"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="goods-item__right">
                            <p class="goods-item__total" v-html="item.SUM_PRICE_FORMATED"></p>
                            <p class="goods-item__points" v-html="item.BONUS_FORMATED"></p>
                        </div>
                    </div>
                </div>

                <div class="present" v-if="postCardItems.length">
                    <h2 class="present-title"><?=GetMessage('BASKET_PRESENT_TITLE')?></h2>
                    <h3 class="present-subtitle"><?=GetMessage('BASKET_PRESENT_SUB_TITLE')?></h3>

                    <div v-for="item in postCardItems" :key="item.card.ID">
                        <div class="goods-item">
                            <button class="goods-item__close" v-if="item.basket" @click.stop.prevent="removeItem(item.basket)">
                                <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.71875 3.71875L13.2812 13.2812" stroke="#C5A994" stroke-linecap="round"
                                          stroke-linejoin="round" />
                                    <path d="M3.71875 13.2812L13.2812 3.71875" stroke="#C5A994" stroke-linecap="round"
                                          stroke-linejoin="round" />
                                </svg>
                            </button>
                            <div class="goods-item__left">
                                <div class="goods-item__img">
                                    <img :src="item.card.PREVIEW_PICTURE.SRC" alt="">
                                </div>

                                <div class="goods-item__content">

                                    <a href="#" class="goods-item__title" @click.stop.prevent>{{item.card.NAME}}</a>

                                    <div class="goods-item__box">
                                        <div class="goods-item__row">
                                            <div class="goods-item__icon">
                                                <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <rect x="0.5" y="0.5" width="14" height="14" rx="7" stroke="#D2C7BC" />
                                                    <g clip-path="url(#clip0_516_18503)">
                                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                                              d="M4.49959 5.04791V6.375C4.49959 6.58211 4.33179 6.75 4.1248 6.75C3.9178 6.75 3.75 6.58211 3.75 6.375V3.75H6.37358C6.58057 3.75 6.74838 3.91789 6.74838 4.125C6.74838 4.33211 6.58057 4.5 6.37358 4.5H5.01207L6.92147 6.41044C7.06784 6.55689 7.06784 6.79432 6.92147 6.94077C6.77511 7.08722 6.5378 7.08722 6.39143 6.94077L4.49959 5.04791ZM10.5004 9.95233V8.62548C10.5004 8.41841 10.6682 8.25055 10.8752 8.25055C11.0822 8.25055 11.25 8.41841 11.25 8.62548V11.25H8.62642C8.41943 11.25 8.25162 11.0821 8.25162 10.8751C8.25162 10.668 8.41943 10.5001 8.62642 10.5001H9.98793L8.07853 8.59005C7.93216 8.44363 7.93216 8.20623 8.07853 8.05981C8.22489 7.91339 8.4622 7.91339 8.60857 8.05981L10.5004 9.95233Z"
                                                              fill="#D2C7BC" />
                                                    </g>
                                                    <defs>
                                                        <clipPath id="clip0_516_18503">
                                                            <rect width="9" height="9" fill="white" transform="translate(3 3)" />
                                                        </clipPath>
                                                    </defs>
                                                </svg>
                                            </div>
                                            <p class="goods-item__size" v-if="item.card.DISPLAY_PROPERTIES.SIZE"
                                               v-html="item.card.DISPLAY_PROPERTIES.SIZE.DISPLAY_VALUE">
                                            </p>
                                        </div>
                                        <div class="goods-item__right" v-if="item.basket">
                                            <p class="goods-item__total" v-html="item.basket.SUM_PRICE_FORMATED"></p>
                                            <p class="goods-item__points" v-html="item.basket.BONUS_FORMATED"></p>
                                        </div>
                                        <div class="goods-item__right" v-else>
                                            <p class="goods-item__total" v-html="item.card.PRICE.PRINT_PRICE"></p>
                                            <p class="goods-item__points"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="goods-item__footer">
                                    <div class="goods-item__price" :style="getOpacityStyle(!!item.basket)">
                                        <div class="price-box">
                                            <div class="price-minus" @click.stop.prevent="incrementQuantity(item.basket, -1)">
                                                <svg width="6" height="3" viewBox="0 0 6 3" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0.2 2.2V0.96H5.44V2.2H0.2Z" />
                                                </svg>
                                            </div>
                                            <input v-if="item.basket" class="price-input" type="text" :value="item.basket.QUANTITY">

                                            <div class="price-plus" @click.stop.prevent="incrementQuantity(item.basket, 1)">
                                                <svg width="10" height="10" viewBox="0 0 10 10" fill="none"
                                                                          xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M4.12 9.24V0.759999H5.38V9.24H4.12ZM0.4 5.6V4.42H9.1V5.6H0.4Z" />
                                                </svg>
                                            </div>
                                        </div>

                                        <p class="goods-item__divider">x</p>

                                        <div v-if="item.basket" class="goods-item__count" :class="{'_discount': item.basket.SUM_DISCOUNT_PRICE>0}">
                                            <p class="goods-item__new" v-html="item.basket.PRICE_FORMATED"></p>
                                            <p class="goods-item__old" v-html="item.basket.FULL_PRICE_FORMATED"></p>
                                        </div>
                                    </div>
                                    <div class="present-button" :style="getOpacityStyle(!item.basket)">
                                        <button class="submit" data-action="add2basket"
                                                data-follow-basket="false"
                                                :data-product-id="item.card.ID"><?=GetMessage('BASKET_ADD_TITLE')?></button>
                                    </div>
                                </div>
                            </div>

                            <div class="goods-item__right">
                                <p class="goods-item__total" v-html="item.basket ? item.basket.SUM_PRICE_FORMATED : item.card.PRICE.PRINT_PRICE"></p>
                                <p class="goods-item__points" v-html="item.basket ? item.basket.BONUS_FORMATED : ''"></p>
                            </div>
                        </div>

                        <div class="present-box" v-if="item.basket" style="height:auto;">
                            <div class="present-textarea">
                                <textarea placeholder="<?=GetMessage('BASKET_GIFT_COMMENT')?>"
                                          :value="getBasketItemComment(item.basket)"
                                          @change="basketItemComment(item.basket, $event.target.value)"
                                ></textarea>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
            <div class="design">
                <div class="design-container">
                    <h2 class="design-title"><?=GetMessage('BASKET_YOUR_ORDER')?></h2>

                    <div class="design-list">
                        <div class="design-list__container">
                            <div class="design-list__row">
                                <p class="design-list__name">{{$Bitrix.Loc.getMessage('BASKET_TOTAL_PRODUCTS',{'#NUM#': basketItems.length})}}</p>
                                <p class="design-list__count" v-html="result.PRICE_WITHOUT_DISCOUNT"></p>
                            </div>
                            <div class="design-list__row _discount" v-if="result.DISCOUNT_PRICE_ALL>0">
                                <p class="design-list__name"><?=GetMessage('BASKET_DISCOUNT')?></p>
                                <p class="design-list__count"> - <span v-html="result.DISCOUNT_PRICE_ALL_FORMATED"></span></p>
                            </div>
                            <div class="design-list__row" v-if="basketPostCardItems.length">
                                <p class="design-list__name">{{$Bitrix.Loc.getMessage('BASKET_GIFT',{'#NUM#': postCardItems.length})}}</p>
                                <p class="design-list__count" v-html="basketPostCardItemsPrice"></p>
                            </div>
                            <div class="design-list__row _cashback">
                                <p class="design-list__name"><?=GetMessage('BASKET_CASHBACK')?></p>
                                <p class="design-list__count" v-html="result.TOTAL_BONUS_FORMATED"></p>
                            </div>
                        </div>
                        <div class="design-list__footer">
                            <p class="design-list__text"><?=GetMessage('BASKET_SUM')?></p>
                            <p class="design-list__total" v-html="result.allSum_FORMATED"></p>
                        </div>
                    </div>

                    <div id="design-button" class="design-button">
                        <a :href="params.PATH_TO_ORDER" class="submit"><?=GetMessage('BASKET_ORDER')?></a>
                    </div>

                    <p class="design-info"><?=GetMessage('BASKET_INFO')?></p>
                </div>
            </div>
        </div>


    </div>
</script>

<?$APPLICATION->IncludeComponent(
    "bitrix:catalog.section",
    "postcard",
    Array(
        "ACTION_VARIABLE" => "action",
        "ADD_PROPERTIES_TO_BASKET" => "Y",
        "ADD_SECTIONS_CHAIN" => "N",
        "AJAX_MODE" => "N",
        "AJAX_OPTION_ADDITIONAL" => "",
        "AJAX_OPTION_HISTORY" => "N",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "Y",
        "BACKGROUND_IMAGE" => "-",
        "BASKET_URL" => "/personal/basket.php",
        "BROWSER_TITLE" => "-",
        "CACHE_FILTER" => "N",
        "CACHE_GROUPS" => "Y",
        "CACHE_TIME" => "36000000",
        "CACHE_TYPE" => "A",
        "COMPATIBLE_MODE" => "N",
        "CONVERT_CURRENCY" => "N",
        "CUSTOM_FILTER" => "{\"CLASS_ID\":\"CondGroup\",\"DATA\":{\"All\":\"AND\",\"True\":\"True\"},\"CHILDREN\":[]}",
        "DETAIL_URL" => "",
        "DISABLE_INIT_JS_IN_COMPONENT" => "N",
        "DISPLAY_BOTTOM_PAGER" => "N",
        "DISPLAY_COMPARE" => "N",
        "DISPLAY_TOP_PAGER" => "N",
        "ELEMENT_SORT_FIELD" => "sort",
        "ELEMENT_SORT_FIELD2" => "id",
        "ELEMENT_SORT_ORDER" => "asc",
        "ELEMENT_SORT_ORDER2" => "desc",
        "FILTER_NAME" => "arrFilter",
        "HIDE_NOT_AVAILABLE" => "N",
        "HIDE_NOT_AVAILABLE_OFFERS" => "N",
        "IBLOCK_ID" => "18",
        "IBLOCK_TYPE" => "catalog",
        "INCLUDE_SUBSECTIONS" => "Y",
        "LINE_ELEMENT_COUNT" => "3",
        "MESSAGE_404" => "",
        "META_DESCRIPTION" => "-",
        "META_KEYWORDS" => "-",
        "OFFERS_LIMIT" => "5",
        "PAGER_BASE_LINK_ENABLE" => "N",
        "PAGER_DESC_NUMBERING" => "N",
        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
        "PAGER_SHOW_ALL" => "N",
        "PAGER_SHOW_ALWAYS" => "N",
        "PAGER_TEMPLATE" => ".default",
        "PAGE_ELEMENT_COUNT" => "18",
        "PARTIAL_PRODUCT_PROPERTIES" => "N",
        "PRICE_CODE" => array("BASE"),
        "PRICE_VAT_INCLUDE" => "Y",
        "PRODUCT_ID_VARIABLE" => "id",
        "PRODUCT_PROPS_VARIABLE" => "prop",
        "PRODUCT_QUANTITY_VARIABLE" => "quantity",
        "SECTION_CODE" => "",
        "SECTION_ID" => "",
        "SECTION_ID_VARIABLE" => "SECTION_ID",
        "SECTION_URL" => "",
        "SECTION_USER_FIELDS" => array("", ""),
        "SEF_MODE" => "N",
        "SET_BROWSER_TITLE" => "N",
        "SET_LAST_MODIFIED" => "N",
        "SET_META_DESCRIPTION" => "N",
        "SET_META_KEYWORDS" => "N",
        "SET_STATUS_404" => "N",
        "SET_TITLE" => "N",
        "SHOW_404" => "N",
        "SHOW_ALL_WO_SECTION" => "N",
        "SHOW_PRICE_COUNT" => "1",
        "USE_MAIN_ELEMENT_SECTION" => "N",
        "USE_PRICE_COUNT" => "N",
        "USE_PRODUCT_QUANTITY" => "N"
    )
);?>

<?
$signer = new \Bitrix\Main\Security\Sign\Signer;
$signedTemplate = $signer->sign($templateName, 'sale.basket.basket');
$signedParams = $signer->sign(base64_encode(serialize($arParams)), 'sale.basket.basket');
$messages = Loc::loadLanguageFile(__FILE__);

$arJSParams = array(
    'container' => '#'.$containerId,
    'result' => $arResult,
    'params' => $arParams,
    'template' => $signedTemplate,
    'signedParamsString' => $signedParams,
    'siteId' => $component->getSiteId(),
    'siteTemplateId' => $component->getSiteTemplateId(),
    'templateFolder' => $templateFolder,
    'vueTemplate' => '#devBxSaleBasket'
);

?>

<script>
    BX.message(<?=CUtil::PhpToJSObject($messages)?>);

    (function() {

        BX.Currency.setCurrencies(<?=\Bitrix\Main\Web\Json::encode($arResult['CURRENCIES'])?>);

        let params = <?=\Bitrix\Main\Web\Json::encode($arJSParams)?>;
        params.postCard = arPostCard;

        window.saleBasket = createVueSaleBasket(params);

    })();

</script>
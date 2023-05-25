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

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

if ($request->isPost() && $request->get('json') == 'y')
{
    $arJS = array(
        'errors' => $arResult['ERRORS'],
        'success' => $arResult['SUCCESS'],
    );

    $APPLICATION->RestartBuffer();;
    echo \Bitrix\Main\Web\Json::encode($arJS);
    die();
}

ob_start();
?>
<?$ElementID = $APPLICATION->IncludeComponent(
    "bitrix:catalog.element",
    "scarf",
    Array(
        "ACTION_VARIABLE" => "action",
        "ADDITIONAL_FILTER_NAME" => "elementFilter",
        "ADD_DETAIL_TO_SLIDER" => "N",
        "ADD_ELEMENT_CHAIN" => "N",
        "ADD_PICT_PROP" => "-",
        "ADD_PROPERTIES_TO_BASKET" => "Y",
        "ADD_SECTIONS_CHAIN" => "N",
        "ADD_TO_BASKET_ACTION" => array("BUY"),
        "ADD_TO_BASKET_ACTION_PRIMARY" => array("BUY"),
        "BACKGROUND_IMAGE" => "-",
        "BASKET_URL" => "/personal/basket.php",
        "BRAND_USE" => "N",
        "BROWSER_TITLE" => "-",
        "CACHE_GROUPS" => "Y",
        "CACHE_TIME" => "36000000",
        "CACHE_TYPE" => "A",
        "CHECK_SECTION_ID_VARIABLE" => "N",
        "COMPATIBLE_MODE" => "N",
        "CONVERT_CURRENCY" => "N",
        "DETAIL_PICTURE_MODE" => array("POPUP", "MAGNIFIER"),
        "DETAIL_URL" => "",
        "DISABLE_INIT_JS_IN_COMPONENT" => "N",
        "DISPLAY_COMPARE" => "N",
        "DISPLAY_NAME" => "Y",
        "DISPLAY_PREVIEW_TEXT_MODE" => "E",
        "ELEMENT_CODE" => "scarf",
        "ELEMENT_ID" => "",
        "GIFTS_DETAIL_BLOCK_TITLE" => "Выберите один из подарков",
        "GIFTS_DETAIL_HIDE_BLOCK_TITLE" => "N",
        "GIFTS_DETAIL_PAGE_ELEMENT_COUNT" => "4",
        "GIFTS_DETAIL_TEXT_LABEL_GIFT" => "Подарок",
        "GIFTS_MAIN_PRODUCT_DETAIL_BLOCK_TITLE" => "Выберите один из товаров, чтобы получить подарок",
        "GIFTS_MAIN_PRODUCT_DETAIL_HIDE_BLOCK_TITLE" => "N",
        "GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT" => "4",
        "GIFTS_MESS_BTN_BUY" => "Выбрать",
        "GIFTS_SHOW_DISCOUNT_PERCENT" => "Y",
        "GIFTS_SHOW_IMAGE" => "Y",
        "GIFTS_SHOW_NAME" => "Y",
        "GIFTS_SHOW_OLD_PRICE" => "Y",
        "HIDE_NOT_AVAILABLE_OFFERS" => "N",
        "IBLOCK_ID" => "22",
        "IBLOCK_TYPE" => "catalog",
        "IMAGE_RESOLUTION" => "16by9",
        "LABEL_PROP" => array(),
        "LINK_ELEMENTS_URL" => "link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#",
        "LINK_IBLOCK_ID" => "",
        "LINK_IBLOCK_TYPE" => "",
        "LINK_PROPERTY_SID" => "",
        "MAIN_BLOCK_OFFERS_PROPERTY_CODE" => array(),
        "MAIN_BLOCK_PROPERTY_CODE" => array(),
        "MESSAGE_404" => "",
        "MESS_BTN_ADD_TO_BASKET" => "В корзину",
        "MESS_BTN_BUY" => "Купить",
        "MESS_BTN_SUBSCRIBE" => "Подписаться",
        "MESS_COMMENTS_TAB" => "Комментарии",
        "MESS_DESCRIPTION_TAB" => "Описание",
        "MESS_NOT_AVAILABLE" => "Нет в наличии",
        "MESS_NOT_AVAILABLE_SERVICE" => "Недоступно",
        "MESS_PRICE_RANGES_TITLE" => "Цены",
        "MESS_PROPERTIES_TAB" => "Характеристики",
        "META_DESCRIPTION" => "-",
        "META_KEYWORDS" => "-",
        "OFFERS_FIELD_CODE" => array("", ""),
        "OFFERS_LIMIT" => "0",
        "OFFERS_SORT_FIELD" => "sort",
        "OFFERS_SORT_FIELD2" => "id",
        "OFFERS_SORT_ORDER" => "asc",
        "OFFERS_SORT_ORDER2" => "desc",
        "OFFER_ADD_PICT_PROP" => "-",
        "PARTIAL_PRODUCT_PROPERTIES" => "N",
        "PRICE_CODE" => array("BASE"),
        "PRICE_VAT_INCLUDE" => "Y",
        "PRICE_VAT_SHOW_VALUE" => "N",
        "PRODUCT_ID_VARIABLE" => "id",
        "PRODUCT_INFO_BLOCK_ORDER" => "sku,props",
        "PRODUCT_PAY_BLOCK_ORDER" => "rating,price,priceRanges,quantityLimit,quantity,buttons",
        "PRODUCT_PROPS_VARIABLE" => "prop",
        "PRODUCT_QUANTITY_VARIABLE" => "quantity",
        "PRODUCT_SUBSCRIPTION" => "Y",
        "SECTION_CODE" => "",
        "SECTION_ID" => "",
        "SECTION_ID_VARIABLE" => "SECTION_ID",
        "SECTION_URL" => "",
        "SEF_MODE" => "N",
        "SET_BROWSER_TITLE" => "N",
        "SET_CANONICAL_URL" => "N",
        "SET_LAST_MODIFIED" => "N",
        "SET_META_DESCRIPTION" => "N",
        "SET_META_KEYWORDS" => "N",
        "SET_STATUS_404" => "N",
        "SET_TITLE" => "N",
        "SET_VIEWED_IN_COMPONENT" => "N",
        "SHOW_404" => "N",
        "SHOW_CLOSE_POPUP" => "N",
        "SHOW_DEACTIVATED" => "N",
        "SHOW_DISCOUNT_PERCENT" => "N",
        "SHOW_MAX_QUANTITY" => "N",
        "SHOW_OLD_PRICE" => "N",
        "SHOW_PRICE_COUNT" => "1",
        "SHOW_SKU_DESCRIPTION" => "N",
        "SHOW_SLIDER" => "N",
        "STRICT_SECTION_CHECK" => "N",
        "TEMPLATE_THEME" => "blue",
        "USE_COMMENTS" => "N",
        "USE_ELEMENT_COUNTER" => "N",
        "USE_ENHANCED_ECOMMERCE" => "N",
        "USE_GIFTS_DETAIL" => "Y",
        "USE_GIFTS_MAIN_PR_SECTION_LIST" => "Y",
        "USE_MAIN_ELEMENT_SECTION" => "N",
        "USE_PRICE_COUNT" => "N",
        "USE_PRODUCT_QUANTITY" => "N",
        "USE_RATIO_IN_RANGES" => "N",
        "USE_VOTE_RATING" => "N"
    ), false, array("HIDE_ICONS"=>"Y")
);?>
<?
$script = ob_get_clean();

if (!$ElementID)
{
    ShowError("Element not found");
    return;
}

\CJSCore::Init(array('ajax', 'currency'));

\Bitrix\Main\UI\Extension::load("ui.vue3");

$arJSParams = array(
    'ajaxUrl' => '/bitrix/components/devbx/form/ajax.php',
    'hiddenFields' => $arResult['HIDDEN_FIELDS'],
    'fields' => $arResult['FIELDS'],
);

?>
<section class="section cost" id="vue-scarf-app">

</section>

<script id="vue-scarf-tpl" type="text/html">
    <div class="container">
        <div class="cost-title d-none d-lg-block">
            <h2 class="title">Рассчитайте стоимость уникального платка и закажите прямо сейчас</h2>
        </div>

        <div class="cost-container" v-if="!finished">
            <div class="calculation">
                <div class="calculation-item">
                    <div class="cost-title d-lg-none">
                        <h2 class="title">Рассчитайте стоимость уникального платка и закажите прямо сейчас</h2>
                    </div>
                    <h4 class="calculation-title">Выберите размер</h4>
                    <div class="calculation-size">
                        <label class="radio" v-for="item in skuByCode['SIZE'].VALUES" :key="item.ID" :style="{'display': item.NA ? 'none' : ''}">
                            <input class="radio__input" type="radio" value="100х100"
                                   :checked="item.ID == selectedSku.SIZE"
                                   name="calculation-size" @click="selectSkuProp(skuByCode['SIZE'].ID, item.ID)">
                            <div class="radio__box">
                                <p class="radio__text">{{item.NAME}}</p>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="calculation-item">
                    <h4 class="calculation-title">Выберите цвет</h4>
                    <div class="calculation-color">
                        <label class="radio" v-for="item in skuByCode['COLOR'].VALUES" :key="item.ID" :style="{'display': item.NA ? 'none' : ''}">
                            <input class="radio__input" type="radio" :value="item.ID"
                                   :checked="item.ID == selectedSku.COLOR"
                                   name="calculation-color" @click="selectSkuProp(skuByCode['COLOR'].ID, item.ID)">
                            <div class="radio__box">
                                <img :src="item.PICT.SRC">
                            </div>
                        </label>
                    </div>

                    <p class="calculation-color__text">
                        Цвет: <span class="calculation-color__name" v-if="selectedSkuValues.COLOR">{{selectedSkuValues.COLOR.NAME}}</span>
                    </p>
                </div>
                <div class="calculation-item">
                    <h4 class="calculation-title">Количество</h4>
                    <div class="calculation-number">
                        <input type="text" placeholder="Укажите в шт" v-model.trim="quantity" ref="quantity" required>
                    </div>
                </div>
                <div class="calculation-button">
                    <button class="submit" @click.stop.prevent="calculate()">Рассчитать стоимость</button>
                    <div class="calculation-price">
                        <div class="calculation-total">
                            <p class="calculation-total__text">Цена:</p>
                            <span v-html="calculatedPrice" class="calculation-total__count"></span>
                        </div>
                        <div class="calculation-description">
                            <div class="calculation-description__icon" @mouseenter="showTip = true" @mouseleave="showTip = false">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_507_29137)">
                                        <path
                                            d="M9 17C11.1217 17 13.1566 16.1571 14.6569 14.6569C16.1571 13.1566 17 11.1217 17 9C17 6.87827 16.1571 4.84344 14.6569 3.34315C13.1566 1.84285 11.1217 1 9 1C6.87827 1 4.84344 1.84285 3.34315 3.34315C1.84285 4.84344 1 6.87827 1 9C1 11.1217 1.84285 13.1566 3.34315 14.6569C4.84344 16.1571 6.87827 17 9 17V17Z" />
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                              d="M7.74187 10.54V9.96401C7.74187 9.64401 7.80987 9.35201 7.94587 9.08801C8.08187 8.82401 8.24587 8.60401 8.43787 8.42801C8.62987 8.25201 8.82188 8.08201 9.01388 7.91801C9.20587 7.75401 9.36987 7.57001 9.50587 7.36601C9.64187 7.16201 9.70988 6.94401 9.70988 6.71201C9.71109 6.57627 9.68149 6.44202 9.62328 6.31938C9.56508 6.19674 9.4798 6.08891 9.37387 6.00401C9.14271 5.80941 8.84791 5.70687 8.54587 5.71601C8.10587 5.71601 7.68187 5.88401 7.27387 6.22001L6.42188 5.17601C7.02987 4.60001 7.78187 4.31201 8.67787 4.31201C9.38987 4.31201 10.0059 4.51801 10.5259 4.93001C11.0459 5.34201 11.3059 5.88801 11.3059 6.56801C11.3059 6.93601 11.2339 7.26801 11.0899 7.56401C10.9459 7.86001 10.7699 8.10401 10.5619 8.29601C10.3539 8.48801 10.1479 8.67001 9.94388 8.84201C9.74536 9.00802 9.56982 9.1997 9.42188 9.41201C9.28299 9.60419 9.20748 9.8349 9.20588 10.072V10.54H7.74187ZM7.72988 13V11.572H9.20588V13H7.72988Z" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_507_29137">
                                            <rect width="18" height="18" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </div>
                            <div class="calculation-description__text" :class="{'_show': showTip}">
                                <p>Цена включает в себя разработку индивидуального дизайна командой художников-дессинаторов и
                                    создание уникального изделия</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="request">
                <form action="" ref="form" @submit.stop.prevent="submit()">
                    <h2 class="request-title">Оформление заказа</h2>
                    <div class="request-inputs">
                        <div class="placement-inputs__col">
                            <label class="placement-inputs__label" for="">Ваше имя</label>
                            <input type="text" class="input" placeholder="Ваше имя" v-model="form.name" ref="name">
                            <span class="placement-inputs__info">Поле заполнено некорректно</span>
                        </div>
                        <div class="placement-inputs__col">
                            <label class="placement-inputs__label" for="">Ваш номер телефона</label>
                            <input type="text" class="input phone" placeholder="Ваш номер телефона" v-model="form.phone" required ref="phone">
                            <span class="placement-inputs__info">Поле заполнено некорректно</span>
                        </div>
                        <div class="placement-inputs__col">
                            <label class="placement-inputs__label" for="">Ваш E-mail</label>
                            <input type="email" class="input" placeholder="Ваш E-mail" v-model="form.email" required ref="email">
                            <span class="placement-inputs__info">Поле заполнено некорректно</span>
                        </div>
                    </div>

                    <div class="request-load">
                        <label class="input-file">
                            <svg width="7" height="15" viewBox="0 0 7 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_512_18509)">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M3.5 15.0001C5.43455 15.0001 7 13.3228 7 11.2501V3.40921H6.04545V11.2501C6.04545 12.7569 4.90636 13.9774 3.5 13.9774C2.09364 13.9774 0.954545 12.7569 0.954545 11.2501V2.72739C0.954545 1.78649 1.66727 1.02285 2.54545 1.02285C3.42364 1.02285 4.13636 1.78649 4.13636 2.72739V9.88649C4.13636 10.2615 3.85 10.5683 3.5 10.5683C3.15 10.5683 2.86364 10.2615 2.86364 9.88649V3.40921H1.90909V9.88649C1.90909 10.8274 2.62182 11.591 3.5 11.591C4.37818 11.591 5.09091 10.8274 5.09091 9.88649V2.72739C5.09091 1.22058 3.95182 0.00012207 2.54545 0.00012207C1.13909 0.00012207 0 1.22058 0 2.72739V11.2501C0 13.3228 1.56545 15.0001 3.5 15.0001Z"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_512_18509">
                                        <rect width="7" height="15" fill="white" />
                                    </clipPath>
                                </defs>
                            </svg>
                            <input type="file" name="file" ref="file">
                            <span class="input-file__text">Прикрепить изображение с дизайном</span>
                        </label>
                    </div>

                    <div class="request-button">
                        <button class="submit" @click.stop.prevent="submit()">Отправить заявку</button>
                    </div>

                    <div class="request-check">
                        <label class="check">
                            <input class="check__input" type="checkbox" checked>
                            <span class="check__box"></span>
                            <div class="check-text">
                                <p>
                                    Я даю своё согласие на <a href="<?=SITE_DIR?>customers/policy/">обработку персональных данных</a> и согласен с
                                    <a href="<?=SITE_DIR?>customers/policy/">условиями политики конфиденциальности</a>
                                </p>
                            </div>
                        </label>
                    </div>
                </form>
            </div>
        </div>
        <div class="cost-container" v-else>
            <h2 class="title">Форма отправлена</h2>
        </div>
    </div>
</script>

<script>
    (function() {

        BX.Currency.setCurrencies(<? echo CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true); ?>);

        let params = <?=\Bitrix\Main\Web\Json::encode($arJSParams)?>;

        params.catalog = <?=$script?>;

        window.vueScarf = createVueScarfApp('#vue-scarf-app', '#vue-scarf-tpl', params);

    })();
</script>

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

\Bitrix\Main\UI\Extension::load("ui.vue3");

$containerId = 'devbx_delivery_calc_' . md5(uniqid());

$messages = Loc::loadLanguageFile(__FILE__);

if ($arResult['ERROR']) {
    ShowError($arResult['ERROR']);
    return;
}

$arResult['SITE_ID'] = $component->getSiteId();
?>

<div id="<?= $containerId ?>">
</div>

<script id="delivery-calc-tpl" type="text/html">
    <div class="product-city" v-if="loaded">
        <div class="product-city__head">
            <p><?=GetMessage('DELIVERY_CALC_TO')?><span class="product-city__name" @click.stop.prevent="this.showLocationSearch = !this.showLocationSearch">{{cityName}}</span></p>
        </div>
        <div class="product-city__box" :class="{'_show': showLocationSearch}">
            <div class="product-city__container">
                <div class="product-city__search">
                    <input type="text" placeholder="<?=GetMessage('DELIVERY_CALC_FIND_YOUR_CITY')?>" v-model="searchCity">
                    <button class="product-city__icon">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.7646 12.9412L16.4705 17.647" stroke="#877569" stroke-miterlimit="10"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                            <path
                                d="M8.23489 14.1172C11.4836 14.1172 14.1172 11.4836 14.1172 8.23489C14.1172 4.98616 11.4836 2.35254 8.23489 2.35254C4.98616 2.35254 2.35254 4.98616 2.35254 8.23489C2.35254 11.4836 4.98616 14.1172 8.23489 14.1172Z"
                                stroke="#877569" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <ul class="product-city__list">
                    <li v-for="item in locationItems" :key="item.CODE" @click.stop.prevent="changeLocation(item.CODE)">{{item.DISPLAY}}</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="information">
        <div class="information-row" v-for="item in allDelivery" :key="item.id">
            <div class="information-col">
                <svg width="38" height="38" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_507_23131)">
                        <path
                            d="M0.808594 13.4406C0.808594 13.4511 0.808594 13.4511 0.808594 13.4406C0.830302 15.4794 2.02425 17.2449 3.78262 18.1172V36.4033C3.78262 36.8342 4.15166 37.1915 4.59668 37.1915H22.1912H28.7471H33.4035C33.8485 37.1915 34.2175 36.8342 34.2175 36.4033V18.1172C35.9651 17.2344 37.1699 15.4689 37.1916 13.4406C37.1916 13.4301 37.1916 13.4091 37.1916 13.3986V13.3881C37.1916 13.367 37.1916 13.3565 37.1916 13.3355C37.1916 13.325 37.1916 13.3145 37.1916 13.304C37.1916 13.2935 37.1916 13.2725 37.1807 13.2619C37.1807 13.2514 37.1807 13.2409 37.1699 13.2199C37.1699 13.2094 37.159 13.1989 37.159 13.1884C37.159 13.1779 37.1482 13.1569 37.1482 13.1463C37.1482 13.1358 37.1373 13.1253 37.1373 13.1148C37.1264 13.0938 37.1264 13.0833 37.1156 13.0728V13.0623L33.6531 5.95803V3.25715C33.6531 1.91197 32.5243 0.808502 31.1241 0.808502H6.84348C5.45415 0.808502 4.31447 1.90146 4.31447 3.25715V6.02108L0.884572 13.0623V13.0728C0.873718 13.0833 0.873718 13.1043 0.862864 13.1148C0.862864 13.1253 0.85201 13.1358 0.85201 13.1463C0.85201 13.1569 0.841156 13.1779 0.841156 13.1884C0.841156 13.1989 0.830302 13.2094 0.830302 13.2199C0.830302 13.2304 0.830302 13.2409 0.819448 13.2619C0.819448 13.2725 0.819448 13.2935 0.808594 13.304C0.808594 13.3145 0.808594 13.325 0.808594 13.3355C0.808594 13.3565 0.808594 13.367 0.808594 13.3881V13.3986C0.808594 13.4196 0.808594 13.4301 0.808594 13.4406ZM5.68209 6.92488H11.7061L10.3494 12.6104H2.90344L5.68209 6.92488ZM24.6225 6.92488L25.9793 12.6104H19.8141V6.92488H24.6225ZM35.0859 12.6104H27.64L26.294 6.92488H32.3181L35.0859 12.6104ZM26.598 15.2377C25.9142 16.4042 24.6225 17.1504 23.2223 17.1504C21.7896 17.1504 20.4762 16.3727 19.8033 15.1536V14.1973H26.3375L26.598 15.2377ZM11.6519 14.1868H18.186V15.1431C17.5239 16.3622 16.1997 17.1399 14.767 17.1399C13.3668 17.1399 12.0752 16.3937 11.3914 15.2272L11.6519 14.1868ZM18.186 12.6104H12.0209L13.3777 6.92488H18.186V12.6104ZM2.52354 14.1868H9.96946L9.76323 15.0695C9.11199 16.3306 7.76608 17.1504 6.30077 17.1504C4.45557 17.1504 2.90344 15.8787 2.52354 14.1868ZM16.6882 35.6256V25.7364C16.6882 25.6418 16.7641 25.5683 16.8618 25.5683H21.4531C21.5508 25.5683 21.6268 25.6418 21.6268 25.7364V35.6256H16.6882ZM32.5894 35.6256H23.244V25.7364C23.244 24.7801 22.4408 23.9919 21.4423 23.9919H16.851C15.8632 23.9919 15.0492 24.7696 15.0492 25.7364V35.6256H5.41074V18.6532C5.7038 18.6952 5.99686 18.7268 6.31163 18.7268C7.96145 18.7268 9.51359 18.0016 10.5447 16.7931C11.5759 17.9911 13.128 18.7268 14.7778 18.7268C16.4277 18.7268 17.9798 18.0016 19.0109 16.7931C20.0421 17.9911 21.5942 18.7268 23.244 18.7268C24.8939 18.7268 26.446 18.0016 27.4771 16.7931C28.5083 17.9911 30.0604 18.7268 31.7102 18.7268C32.0142 18.7268 32.3181 18.7057 32.6111 18.6532V35.6256H32.5894ZM31.6885 17.1504C30.2232 17.1504 28.8773 16.3412 28.2261 15.0695L28.0199 14.1868H35.4658C35.0967 15.8787 33.5446 17.1504 31.6885 17.1504ZM5.95344 3.26766C5.95344 2.79475 6.35504 2.3954 6.85433 2.3954H31.135C31.6234 2.3954 32.0359 2.78424 32.0359 3.26766V5.34849H5.95344V3.26766Z"
                            fill="#D2C7BC"/>
                    </g>
                    <defs>
                        <clipPath id="clip0_507_23131">
                            <rect width="38" height="38" fill="white"/>
                        </clipPath>
                    </defs>
                </svg>
            </div>
            <div class="information-col">
                <p class="information-text" v-if="item.delivery.DELIVERY_PRICE>>0"><?=GetMessage('DELIVERY_CALC_DELIVERY')?> {{item.name}} - <span v-html="item.delivery.DELIVERY_PRICE_FORMATTED"></span></p>
                <p class="information-text" v-else><?=GetMessage('DELIVERY_CALC_DELIVERY')?> {{item.name}} - <?=GetMessage('DELIVERY_CALC_FREE_DELIVERY')?></p>
                <?/*
                <a href="" class="information-link">Посмотреть пункты выдачи</a>
                */?>
            </div>

            <div class="information-description" v-if="item.delivery.DESCRIPTION.length>0">
                <div class="information-description__content" v-html="item.delivery.DESCRIPTION">
                </div>
                <div class="information-description__icon">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_507_26757)">
                            <path class="_stroke"
                                  d="M9 17C11.1217 17 13.1566 16.1571 14.6569 14.6569C16.1571 13.1566 17 11.1217 17 9C17 6.87827 16.1571 4.84344 14.6569 3.34315C13.1566 1.84285 11.1217 1 9 1C6.87827 1 4.84344 1.84285 3.34315 3.34315C1.84285 4.84344 1 6.87827 1 9C1 11.1217 1.84285 13.1566 3.34315 14.6569C4.84344 16.1571 6.87827 17 9 17V17Z"/>
                            <path class="_fill" fill-rule="evenodd" clip-rule="evenodd"
                                  d="M7.74187 10.54V9.96401C7.74187 9.64401 7.80987 9.35201 7.94587 9.08801C8.08187 8.82401 8.24587 8.60401 8.43787 8.42801C8.62987 8.25201 8.82188 8.08201 9.01388 7.91801C9.20587 7.75401 9.36987 7.57001 9.50587 7.36601C9.64187 7.16201 9.70988 6.94401 9.70988 6.71201C9.71109 6.57627 9.68149 6.44202 9.62328 6.31938C9.56508 6.19674 9.4798 6.08891 9.37387 6.00401C9.14271 5.80941 8.84791 5.70687 8.54587 5.71601C8.10587 5.71601 7.68187 5.88401 7.27387 6.22001L6.42188 5.17601C7.02987 4.60001 7.78187 4.31201 8.67787 4.31201C9.38987 4.31201 10.0059 4.51801 10.5259 4.93001C11.0459 5.34201 11.3059 5.88801 11.3059 6.56801C11.3059 6.93601 11.2339 7.26801 11.0899 7.56401C10.9459 7.86001 10.7699 8.10401 10.5619 8.29601C10.3539 8.48801 10.1479 8.67001 9.94388 8.84201C9.74536 9.00802 9.56982 9.1997 9.42188 9.41201C9.28299 9.60419 9.20748 9.8349 9.20588 10.072V10.54H7.74187ZM7.72988 13V11.572H9.20588V13H7.72988Z"/>
                        </g>
                        <defs>
                            <clipPath id="clip0_507_26757">
                                <rect width="18" height="18" fill="white"/>
                            </clipPath>
                        </defs>
                    </svg>
                </div>
            </div>
        </div>

        <div class="information-row">
            <div class="information-col">
                <svg width="38" height="38" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M19.0138 36.4168C23.6671 36.4168 28.0406 34.6055 31.3362 31.314C34.3935 28.2605 36.1968 24.2031 36.4144 19.8869C36.4352 19.4625 36.1035 19.0899 35.6786 19.0692C35.2537 19.0485 34.8806 19.3797 34.8599 19.8041C34.663 23.7269 33.0151 27.4221 30.2273 30.2064C27.2322 33.1978 23.2422 34.8539 19.0034 34.8539C14.7647 34.8539 10.7746 33.2081 7.77953 30.2064C1.59239 24.0271 1.59239 13.9663 7.77953 7.78696C13.0547 2.51848 21.2627 1.63868 27.5017 5.60297L25.7502 7.74555L30.7766 7.23837L30.2688 2.21831L28.4966 4.39195C21.6254 -0.07953 12.5157 0.862378 6.68097 6.68979C-0.117617 13.4798 -0.117617 24.5136 6.68097 31.3036C9.987 34.6055 14.3605 36.4168 19.0138 36.4168Z"
                        fill="#D2C7BC"/>
                    <path
                        d="M27.2283 15.426C27.2283 15.4156 27.2283 15.4053 27.2179 15.4053C27.2179 15.3846 27.2076 15.3742 27.2076 15.3535C27.2076 15.3432 27.1972 15.3225 27.1972 15.3121C27.1972 15.3018 27.1868 15.2914 27.1868 15.2811C27.1661 15.2293 27.1454 15.1879 27.1143 15.1465L23.8186 10.3128C23.6735 10.0954 23.4352 9.97119 23.1761 9.97119H14.8229C14.5638 9.97119 14.3255 10.0954 14.1804 10.3128L10.8847 15.1362C10.8536 15.1776 10.8329 15.2293 10.8122 15.2707C10.8122 15.2811 10.8018 15.2914 10.8018 15.3018C10.7915 15.3121 10.7915 15.3328 10.7915 15.3432C10.7915 15.3639 10.7811 15.3742 10.7811 15.3949C10.7811 15.4053 10.7811 15.4156 10.7707 15.4156C10.7604 15.4674 10.75 15.5191 10.75 15.5709V27.2568C10.75 27.6811 11.1024 28.0331 11.5273 28.0331H26.4614C26.8863 28.0331 27.2387 27.6811 27.2387 27.2568V15.5812C27.2387 15.5191 27.2387 15.4674 27.2283 15.426ZM15.2375 11.5238H22.7615L24.9897 14.7946H12.9989L15.2375 11.5238ZM25.6841 26.4908H12.3149V16.3575H25.6945V26.4908H25.6841Z"
                        fill="#D2C7BC"/>
                </svg>
            </div>
            <div class="information-col">
                <p class="information-text"><?=GetMessage('DELIVERY_CALC_FREE_EXCHANGE_TITLE')?></p>
                <a href="" class="information-link"><?=GetMessage('DELIVERY_CALC_FREE_EXCHANGE_LINK_TITLE')?></a>
            </div>

            <div class="information-description">
                <div class="information-description__content">
                    <?=GetMessage('DELIVERY_CALC_FREE_EXCHANGE_TEXT')?>
                </div>
                <div class="information-description__icon">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_507_26757)">
                            <path class="_stroke"
                                  d="M9 17C11.1217 17 13.1566 16.1571 14.6569 14.6569C16.1571 13.1566 17 11.1217 17 9C17 6.87827 16.1571 4.84344 14.6569 3.34315C13.1566 1.84285 11.1217 1 9 1C6.87827 1 4.84344 1.84285 3.34315 3.34315C1.84285 4.84344 1 6.87827 1 9C1 11.1217 1.84285 13.1566 3.34315 14.6569C4.84344 16.1571 6.87827 17 9 17V17Z"/>
                            <path class="_fill" fill-rule="evenodd" clip-rule="evenodd"
                                  d="M7.74187 10.54V9.96401C7.74187 9.64401 7.80987 9.35201 7.94587 9.08801C8.08187 8.82401 8.24587 8.60401 8.43787 8.42801C8.62987 8.25201 8.82188 8.08201 9.01388 7.91801C9.20587 7.75401 9.36987 7.57001 9.50587 7.36601C9.64187 7.16201 9.70988 6.94401 9.70988 6.71201C9.71109 6.57627 9.68149 6.44202 9.62328 6.31938C9.56508 6.19674 9.4798 6.08891 9.37387 6.00401C9.14271 5.80941 8.84791 5.70687 8.54587 5.71601C8.10587 5.71601 7.68187 5.88401 7.27387 6.22001L6.42188 5.17601C7.02987 4.60001 7.78187 4.31201 8.67787 4.31201C9.38987 4.31201 10.0059 4.51801 10.5259 4.93001C11.0459 5.34201 11.3059 5.88801 11.3059 6.56801C11.3059 6.93601 11.2339 7.26801 11.0899 7.56401C10.9459 7.86001 10.7699 8.10401 10.5619 8.29601C10.3539 8.48801 10.1479 8.67001 9.94388 8.84201C9.74536 9.00802 9.56982 9.1997 9.42188 9.41201C9.28299 9.60419 9.20748 9.8349 9.20588 10.072V10.54H7.74187ZM7.72988 13V11.572H9.20588V13H7.72988Z"/>
                        </g>
                        <defs>
                            <clipPath id="clip0_507_26757">
                                <rect width="18" height="18" fill="white"/>
                            </clipPath>
                        </defs>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</script>

<script>
    BX.message(<?=\Bitrix\Main\Web\Json::encode($messages)?>);

    (function () {

        let app = BX.Vue3.BitrixVue.createApp({
            data() {
                return {
                    params: <?=\Bitrix\Main\Web\Json::encode($arResult)?>,
                    showMoreDelivery: false,
                    locationPath: [],
                    deliveryItems: {},
                    loaded: false,
                    showLocationSearch: false,
                    searchCity: '',
                    searchResult: false,
                    userLocationCode: false,
                }
            },
            mounted() {
                this.ajaxGetDelivery('A');
            },

            computed: {
                cityName()
                {
                    if (!this.locationPath || !this.locationPath.length)
                        return '';

                    return this.locationPath[this.locationPath.length-1]['LANG_NAME'];
                },

                allDelivery()
                {
                    let result = [];

                    if (!this.deliveryItems)
                        return result;

                    Object.values(this.deliveryItems).forEach(item => {

                        Object.values(item.ITEMS).forEach(subItem => {

                            result.push({
                                id: item.ID+'-'+subItem.ID,
                                name: item.ID == subItem.ID ? subItem.NAME : item.NAME+' - '+subItem.NAME,
                                parent: item,
                                delivery: subItem
                            });

                        });

                    });

                    return result;
                },

                locationItems()
                {
                    let result = [];

                    if (!this.searchResult || !Array.isArray(this.searchResult.ITEMS))
                        return result;


                    this.searchResult.ITEMS.forEach(item => {

                        let data = {
                            CODE: item.CODE,
                            DISPLAY: item.DISPLAY,
                            PATH: [],
                            DISPLAY_PATH: '',
                        };

                        let arPathName = [];

                        item.PATH.forEach(id => {
                            data.PATH.push(this.searchResult.ETC.PATH_ITEMS[id]);
                            arPathName.push(this.searchResult.ETC.PATH_ITEMS[id].DISPLAY);
                        });

                        data.DISPLAY_PATH = arPathName.join(', ');

                        result.push(data);
                    });

                    return result;
                },
            },
            watch: {
                searchCity(val)
                {
                    if (val.length>1)
                    {
                        BX.ajax({
                            url: '/bitrix/components/bitrix/sale.location.selector.search/get.php',
                            method: 'POST',
                            data: {
                                select: {1: 'CODE', 2: 'TYPE_ID', VALUE: 'ID', DISPLAY: 'NAME.NAME'},
                                additionals: ['PATH'],
                                filter: {
                                    '=PHRASE': val,
                                    '=NAME.LANGUAGE_ID': BX.message['LANGUAGE_ID'],
                                    '=TYPE_ID': 5,
                                    '=SITE_ID': BX.message['SITE_ID'],
                                },
                                version: 2,
                                PAGE: 0,
                                PAGE_SIZE: 10,
                            },
                            dataType: 'json',
                            onsuccess: BX.delegate(this.locationResult, this)
                        });
                    }
                },
            },
            methods: {
                changeLocation(value)
                {
                    this.showLocationSearch = false;
                    this.userLocationCode = value;

                    this.ajaxGetDelivery('A');
                },

                locationResult(response)
                {
                    if (response.errors.length)
                        return;

                    this.searchResult = response.data;
                },

                ajaxGetDelivery(type) {
                    let request = {
                        SITE_ID: this.params.SITE_ID,
                        parameters: this.params.SIGNED_PARAMS,
                        template: this.params.SIGNED_TEMPLATE,
                        type: type,
                        user_location_code: this.userLocationCode ? this.userLocationCode : ''
                    };

                    request[this.params.ACTION_VARIABLE] = 'deliveryCalc';

                    BX.ajax({
                        url: this.params.AJAX_PATH,
                        data: request,
                        method: 'POST',
                        dataType: 'json',
                        onsuccess: BX.proxy(this.deliveryCalcResult, this),
                    });
                },

                deliveryCalcResult(response) {
                    this.locationPath = response.LOCATION_PATH;
                    this.deliveryItems = response.DELIVERY;

                    this.loaded = true;
                },
            },

            template: '#delivery-calc-tpl'
        });

        window.calcDelivery = app.mount('#<?=$containerId?>');

    })();
</script>

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

\Bitrix\Main\UI\Extension::load("ui.vue");
//\Bitrix\Main\UI\Extension::load("ui.vue.vuex");

$id = 'devbx_delivery_calc_'.md5(uniqid());


$messages = Loc::loadLanguageFile(__FILE__);

if ($arResult['ERROR'])
{
    ShowError($arResult['ERROR']);
    return;
}

$arResult['SITE_ID'] = $component->getSiteId();

?>
<div id="<?=$id?>"  class="sidebar-prod__block">
</div>

<script>
    BX.message(<?=\Bitrix\Main\Web\Json::encode($messages)?>);

    myApp = BX.Vue.create({
        el: '#<?=$id?>',
        data() {
            return {
                params: <?=\Bitrix\Main\Web\Json::encode($arResult)?>,
                showMoreDelivery: false,
                locationPath: [],
                simpleDeliveryItems: [],
                deliveryItems: [],
                loaded: false,
            }
        },

        mounted() {
            this.getSimpleDelivery();
        },

        computed: {

            lastLocationName()
            {
                if (!this.locationPath.length)
                    return '';

                return this.locationPath[this.locationPath.length-1].LANG_NAME;
            },

            simpleDelivery()
            {
                let items = [];

                this.simpleDeliveryItems.forEach(item => {
                    items.push(...Object.values(item.ITEMS));
                });

                return items;
            },

            computedDelivery()
            {
                let items = [];

                this.deliveryItems.forEach(item => {

                    Object.values(item.ITEMS).forEach(subItem => {

                        subItem = Object.assign({}, subItem);
                        subItem.NAME += ' ('+item.NAME+')';
                        subItem.PARENT_ID = item.ID;
                        subItem.PARENT_NAME = item.NAME;

                        items.push(subItem);
                    });
                });

                return items;
            }

        },

        methods: {
            showMoreDeliveryClick()
            {
                this.showMoreDelivery = true;

                this.calcDelivery();
            },

            getSimpleDelivery()
            {
                let request = {
                    SITE_ID: this.params.SITE_ID,
                    parameters: this.params.SIGNED_PARAMS,
                    template: this.params.SIGNED_TEMPLATE,
                    type: 'I'
                };

                request[this.params.ACTION_VARIABLE] = 'deliveryCalc';

                BX.ajax({
                    url: this.params.AJAX_PATH,
                    data: request,
                    method: 'POST',
                    dataType: 'json',
                    onsuccess: BX.proxy(this.simpleDeliveryResult, this),
                });
            },

            simpleDeliveryResult(result)
            {
                if (!!result.DELIVERY)
                {
                    this.locationPath = result.LOCATION_PATH;
                    this.simpleDeliveryItems = Object.values(result.DELIVERY);
                    this.loaded = true;
                }
            },

            calcDelivery()
            {
                let request = {
                    SITE_ID: this.params.SITE_ID,
                    parameters: this.params.SIGNED_PARAMS,
                    template: this.params.SIGNED_TEMPLATE,
                    type: 'C'
                };

                request[this.params.ACTION_VARIABLE] = 'deliveryCalc';

                BX.ajax({
                    url: this.params.AJAX_PATH,
                    data: request,
                    method: 'POST',
                    dataType: 'json',
                    onsuccess: BX.proxy(this.calcDeliveryResult, this),
                });
            },

            calcDeliveryResult(result)
            {
                if (!!result.DELIVERY)
                {
                    this.deliveryItems = Object.values(result.DELIVERY);
                }
            },

            plainText(value)
            {
                let tmp = document.createElement('DIV');

                tmp.innerHTML = value;

                tmp.querySelectorAll('select').forEach(item => {
                    item.remove();
                });

                return tmp.innerText;
            },

            getDeliveryIcon(item)
            {
                let name = item.PARENT_NAME,
                    iconClass = {
                        'sdek': 'СДЭК',
                        'vozovoz': 'VOZOVOZ',
                        'boxberry': 'BOXBERRY',
                    },
                    puckupWords = ['САМОВЫВОЗ','ПОСТАМАТ','ТЕРМИНАЛ','ПУНКТ ВЫДАЧИ']

                let cssClass = 'delivery-icon-';

                if (typeof name === 'undefined')
                    name = '';

                let idx = Object.values(iconClass).indexOf(name.toUpperCase());

                if (idx>-1)
                {
                    cssClass+=Object.keys(iconClass)[idx];
                } else {
                    cssClass+='default';
                }

                name = item.NAME.toUpperCase();

                puckupWords.every(value => {
                    if (name.indexOf(value)>-1)
                    {
                        cssClass+='-pickup';
                        return false;
                    }

                    return true;
                });

                return cssClass;
            }
        },

        template: `
    <div class="ah-block-delivery" v-if="loaded">
        <div class="ah-block-delivery__top">
            <a href="#search-city" data-fancybox>{{lastLocationName}}</a> - Доставка:
        </div>
        <table class="ah-block-delivery-table">
            <tr v-for="item in simpleDelivery" :key="item.ID">
                <th>
                    <div class="ah-block-delivery-table__block">
                        <div class="ah-block-delivery-table__icon" :class="getDeliveryIcon(item)" :data-delivery-parent-id="item.PARENT_ID" :data-delivery-id="item.ID"></div>
                        <div class="ah-block-delivery-table__name">{{item.NAME}}</div>
                    </div>
                </th>
                <th>
                    <strong>{{plainText(item.PERIOD_DESCRIPTION)}}</strong>
                </th>
                <th>
                    <strong v-if="item.PRICE>0" v-html="item.PRICE_FORMATTED"></strong><strong v-else="item.PRICE<=0">Бесплатно</strong>
                </th>
            </tr>

            <tr v-for="item in computedDelivery" :key="item.ID">
                <th>
                    <div class="ah-block-delivery-table__block">
                        <div class="ah-block-delivery-table__icon" :class="getDeliveryIcon(item)" :data-delivery-parent-id="item.PARENT_ID" :data-delivery-id="item.ID"></div>
                        <div class="ah-block-delivery-table__name">{{item.NAME}}</div>
                    </div>
                </th>
                <th>
                    <strong>{{plainText(item.PERIOD_DESCRIPTION)}}</strong>
                </th>
                <th>
                    <strong v-if="item.PRICE>0" v-html="item.PRICE_FORMATTED"></strong><strong v-else="item.PRICE<=0">Бесплатно</strong>
                </th>
            </tr>
        </table>

        <div class="ah-block-delivery__top" v-if="!showMoreDelivery">
            <a href="#" @click.stop.prevent="showMoreDeliveryClick">Больше способов доставки / самовывоза</a>
        </div>
    </div>
        `
    });

</script>
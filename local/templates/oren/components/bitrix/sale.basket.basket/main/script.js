function createVueSaleBasket(params) {
    let app = BX.Vue3.BitrixVue.createApp({
        data() {
            return Object.assign({}, params, {
                poolData: {},
                ajaxWork: false,
                timer: false,
            });
        },
        mounted() {
            BX.addCustomEvent("onBasketAdd", BX.delegate(this.onBasketAdd, this));
            BX.addCustomEvent("onBasketResult", BX.delegate(this.onBasketResult, this));
        },
        updated() {
            orenShop.renderFavorite();
        },
        methods: {
            onBasketAdd()
            {
                this.sendRequest();
            },

            removeItem(item) {
                this.poolData['DELETE_'+item.ID] = 'Y';
                this.sendRequest();
            },

            incrementQuantity(item, value)
            {
                if (this.ajaxWork)
                    return;

                let quantity = item.QUANTITY;

                quantity += value;
                if (quantity<=0)
                    quantity = 1;

                if (item.QUANTITY == quantity)
                    return;

                item.QUANTITY = quantity;

                this.poolData['QUANTITY_' + item.ID] = quantity;
                this.sendPoolData();
            },

            sendPoolData() {
                if (this.timer) {
                    clearTimeout(this.timer);
                    this.timer = false;
                }

                this.timer = setTimeout(BX.delegate(this.sendRequest, this), 300);
            },

            sendRequest() {
                if (this.timer) {
                    clearTimeout(this.timer);
                    this.timer = false;
                }

                /*
                if (!Object.values(this.poolData).length)
                    return;
                 */

                let data = {};

                data[this.params.ACTION_VARIABLE] = 'recalculateAjax';
                data.via_ajax = 'Y';
                data.site_id = this.siteId;
                data.site_template_id = this.siteTemplateId;
                data.sessid = BX.bitrix_sessid();
                data.template = this.template;
                data.signedParamsString = this.signedParamsString;
                data.basket = this.poolData;

                this.poolData = {};

                this.ajaxWork = true;

                $.ajax({
                    method: 'POST',
                    dataType: 'json',
                    url: this.params.AJAX_PATH,
                    data: data,
                    context: this,
                    success: function(response) {
                        BX.onCustomEvent('onBasketResult', [response]);
                    },
                    complete: this.ajaxComplete,
                });
            },

            onBasketResult(response) {
                this.result = response.BASKET_DATA;
            },

            ajaxComplete()
            {
                this.ajaxWork = false;
            },

            getOpacityStyle(value)
            {
                return {
                    opacity: value ? 1 : 0,
                    'pointer-events': value ? 'auto' : 'none',
                }
            },

            getBasketItemComment(item)
            {
                if (!item)
                    return '';

                if (!item.PROPS_ALL.COMMENT)
                    return '';

                return item.PROPS_ALL.COMMENT.VALUE;
            },

            basketItemComment(item, comment)
            {
                console.log(item, comment);

                BX.ajax.runAction('local:lib.api.shop.basketItemComment', {
                    data: {
                        basketId: item.ID,
                        comment: comment,
                    }
                }).then(
                    BX.delegate(this.sendRequest, this),
                );
            }
        },
        computed: {
            basketItems() {
                let result = [];

                this.result.BASKET_ITEM_RENDER_DATA.forEach(item => {
                    if (item.DELAY || item.IBLOCK_CODE == 'postcard')
                        return;

                    result.push(item);
                });

                return result;
            },

            basketPostCardItems() {
                let result = [];

                this.result.BASKET_ITEM_RENDER_DATA.forEach(item => {
                    if (item.DELAY || item.IBLOCK_CODE != 'postcard')
                        return;

                    result.push(item);
                });

                return result;
            },

            basketPostCardItemsPrice() {
                let result = 0;

                this.basketPostCardItems.forEach(item => {
                    result += item.SUM_FULL_PRICE;
                });

                return BX.Currency.currencyFormat(result, 'RUB', true);
            },

            postCardItems() {
                let result = [];

                if (!this.postCard || !Array.isArray(this.postCard.ITEMS))
                    return [];

                let cardById = {}

                this.basketPostCardItems.forEach(item => {
                    cardById[item.PRODUCT_ID] = item;
                });

                this.postCard.ITEMS.forEach(item => {
                    let data = {
                        card: item,
                        basket: cardById[item.ID],
                    };

                    result.push(data);
                });

                return result;
            }
        },
        template: params.vueTemplate
    });

    app.component('basket-item-sku', {
        props: {
            item: {
                type: Object,
                required: true,
            }
        },
        computed: {
            activeSku()
            {
                let result = [];

                this.item.SKU_BLOCK_LIST.forEach(item => {

                    let activeSku = {
                        ID: item.ID,
                        CODE: item.CODE,
                        IS_IMAGE: item.IS_IMAGE,
                        NAME: item.NAME,
                        VALUE: false,
                    };

                    item.SKU_VALUES_LIST.forEach(value => {
                        if (value.SELECTED)
                        {
                            activeSku.VALUE = value;
                        }
                    });

                    if (activeSku.VALUE)
                        result.push(activeSku);
                });

                return result;
            },
        },
        template: `
        <slot v-for="sku in activeSku" :key="sku.ID" :sku="sku">
        </slot>
        `
    });

    return app.mount(params.container);
}
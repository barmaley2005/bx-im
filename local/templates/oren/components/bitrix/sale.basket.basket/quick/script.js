class DevBxQuickSaleBasket {

    constructor(params) {

        this.result = params.result;
        this.params = params.params;
        this.template = params.template;
        this.signedParamsString = params.signedParamsString;
        this.siteId = params.siteId;
        this.siteTemplateId = params.siteTemplateId;
        this.templateFolder = params.templateFolder;
        this.poolData = {};
        this.ajaxWork = false;

        this.container = document.getElementById(params.containerId);

        $(this.container).on('click', '[data-action]', $.proxy(this.action, this));

        this.entity = {
            buyCount: false,
            totalBuyCount: false,
            total: false,
            totalSum: false,
            totalDiscount: false,
            totalDiscountSum: false,
            bonus: false,
            bonusSum: false,
            fullTotal: false,
            fullTotalSum: false,
        };

        DevBX.Utils.getNodeEntities(this.container, this.entity);

        this.elRows = {};

        this.container.querySelectorAll('[data-entity="basket-item"]').forEach(item => {

            let data = {
                el: item,
                entity: {
                    quantity: true,
                    basketItemPrice: true,
                    basketItemPriceNew: true,
                    basketItemPriceOld: true,
                    basketItemFullPrice: true,
                    basketItemBonus: true,
                },
            };

            DevBX.Utils.getNodeEntities(item, data.entity);

            this.elRows[item.dataset.itemId] = data;
        });

        orenShop.renderFavorite();

        BX.addCustomEvent("onBasketAdd", BX.delegate(this.onBasketAdd, this));
        BX.addCustomEvent("onBasketResult", BX.delegate(this.onBasketResult, this));
    }

    action(e) {
        let el = e.currentTarget || e.target;

        if (typeof this[el.dataset.action + 'Action'] === 'function') {
            this[el.dataset.action + 'Action'](e);
        }
    }

    removeBasketItemAction(e) {
        e.preventDefault();

        if (this.ajaxWork)
            return;

        let el = e.currentTarget || e.target,
            itemId = el.dataset.itemId;

        this.poolData['DELETE_' + itemId] = 'Y';

        this.sendRequest();
    }

    quantityChangeAction(e) {
        e.preventDefault();

        if (this.ajaxWork)
            return;

        let el = e.currentTarget || e.target,
            changeValue = parseFloat(el.dataset.value),
            row = el.closest('[data-entity="basket-item"]'),
            elQuantity = row.querySelector('[data-entity="quantity"]'),
            value = parseFloat(elQuantity.value),
            basketId = row.dataset.itemId;

        if (isNaN(value))
            value = 0;

        value += changeValue;
        if (value <= 0)
            value = 1;

        if (elQuantity.value == value)
            return;

        elQuantity.value = value;

        this.poolData['QUANTITY_' + basketId] = value;
        this.sendPoolData();
    }

    sendPoolData() {
        if (this.timer) {
            clearTimeout(this.timer);
            this.timer = false;
        }

        this.timer = setTimeout(BX.delegate(this.sendRequest, this), 300);
    }

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
    }

    onBasketResult(response) {
        this.result = response.BASKET_DATA;

        let availableItemId = {};

        this.result.BASKET_ITEM_RENDER_DATA.forEach(item => {
            availableItemId[item.ID] = item;
        });

        let updateCounter = false;

        Object.keys(this.elRows).forEach(id => {

            if (availableItemId.hasOwnProperty(id)) {
                let item = availableItemId[id];

                if (item.SUM_DISCOUNT_PRICE > 0) {
                    this.elRows[id].entity.basketItemPrice.classList.add('_discount');
                } else {
                    this.elRows[id].entity.basketItemPrice.classList.remove('_discount');
                }

                this.elRows[id].entity.quantity.value = item.QUANTITY;
                this.elRows[id].entity.basketItemPriceNew.innerHTML = item.PRICE_FORMATED;
                this.elRows[id].entity.basketItemPriceOld.innerHTML = item.FULL_PRICE_FORMATED;
                this.elRows[id].entity.basketItemFullPrice.innerHTML = item.SUM_PRICE_FORMATED;
                this.elRows[id].entity.basketItemBonus.innerHTML = item.BONUS_FORMATED;

            } else {
                this.elRows[id].el.remove();
                delete this.elRows[id];
                updateCounter = true;
            }
        });

        if (this.entity.buyCount)
            this.entity.buyCount.innerHTML = this.result.BUY_COUNT;

        if (this.entity.totalBuyCount)
            this.entity.totalBuyCount.innerHTML = this.result.BUY_COUNT;

        if (this.entity.totalSum)
            this.entity.totalSum.innerHTML = this.result.PRICE_WITHOUT_DISCOUNT;

        if (this.entity.totalDiscount)
        {
            this.entity.totalDiscount.style.display = this.result.DISCOUNT_PRICE_ALL>0 ? '' : 'none';
        }

        if (this.entity.totalDiscountSum)
            this.entity.totalDiscountSum.innerHTML = ' - '+this.result.DISCOUNT_PRICE_ALL_FORMATED;

        if (this.entity.bonusSum)
            this.entity.bonusSum.innerHTML = this.result.TOTAL_BONUS_FORMATED;

        if (this.entity.fullTotalSum)
            this.entity.fullTotalSum.innerHTML = this.result.allSum_FORMATED;

        if (updateCounter) {
            orenShop.updateBasketCounter();
        }
    }

    ajaxComplete()
    {
        this.ajaxWork = false;
    }

    submitOrderAction()
    {
        window.location.href = this.params.PATH_TO_ORDER;
    }

    onBasketAdd()
    {
        this.sendRequest();
    }

}
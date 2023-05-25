function createVueScarfApp(container, template, params)
{
    let app = BX.Vue3.BitrixVue.createApp({
        data() {
            return Object.assign({}, params, {
                showTip: false,
                offerId: params.catalog.OFFERS[0].ID,
                quantity: '',
                calculatedPrice: '',
                form: {
                    name: '',
                    phone: '',
                    email: '',
                },
                waitAjax: false,
                finished: false,
            });
        },
        mounted()
        {
            $(this.$refs.phone).mask('+7(000) 000-00-00');
        },
        computed: {
            skuByCode()
            {
                let result = {};

                this.catalog.SKU_PROPS.forEach(item => {
                    result[item.CODE] = item;
                });

                return result;
            },

            selectedOffer()
            {
                let result = false;

                this.catalog.OFFERS.every(item => {
                    if (item.ID == this.offerId)
                        result = item;

                    return !result;
                });

                return result;
            },

            selectedSku()
            {
                let result = {};

                if (!this.selectedOffer)
                    return result;

                this.catalog.SKU_PROPS.forEach(prop => {
                    result[prop.CODE] = this.selectedOffer.TREE['PROP_'+prop.ID];

                });

                return result;
            },

            selectedSkuValues()
            {
                let result = {};

                if (!this.selectedOffer)
                    return result;

                this.catalog.SKU_PROPS.forEach(prop => {
                    let valueId = this.selectedOffer.TREE['PROP_'+prop.ID];
                    result[prop.CODE] = prop.VALUES[valueId];
                });

                return result;
            },
        },
        methods: {
            getOfferByTree(tree)
            {
                let result = false;

                this.catalog.OFFERS.every(item => {

                    let found = true;

                    Object.keys(tree).forEach(treeId => {
                        if (item.TREE[treeId] !== tree[treeId])
                            found = false;
                    });

                    if (found)
                    {
                        result = item;
                    }

                    return !result;
                });

                return result;
            },

            selectSkuProp(propId, value)
            {
                let tree = {},
                    offer;

                if (this.selectedOffer)
                {
                    tree = JSON.parse(JSON.stringify(this.selectedOffer.TREE));
                }

                tree['PROP_'+propId] = value;

                offer = this.getOfferByTree(tree);
                if (!offer)
                {
                    tree = {};
                    tree['PROP_'+propId] = value;
                    offer = this.getOfferByTree(tree);
                    if (!offer)
                        return;
                }

                this.offerId = offer.ID;
            },

            calculate()
            {
                if (!this.$refs.quantity.reportValidity())
                    return;

                this.calculatedPrice = '';

                if (!this.selectedOffer)
                    return;

                let qnt = parseInt(this.quantity);

                if (isNaN(qnt) || qnt<=0)
                    return;

                let price = this.selectedOffer.DISPLAY_PRICE.PRICE*qnt;

                this.calculatedPrice = BX.Currency.currencyFormat(price, 'RUB', true);
            },

            submit()
            {
                if (this.waitAjax)
                    return;

                if (!this.$refs.quantity.reportValidity())
                    return;

                if (!this.$refs.form.reportValidity())
                    return;

                let formData = new FormData();

                this.hiddenFields.forEach(item => {
                    formData.append(item.NAME, item.VALUE);
                });

                formData.append('UF_NAME', this.form.name);
                formData.append('UF_PHONE', this.form.phone);
                formData.append('UF_EMAIL', this.form.email);
                formData.append('UF_PRODUCT_ID', this.offerId);
                formData.append('UF_QUANTITY', this.quantity);

                if (this.$refs.file.files.length)
                {
                    formData.append('UF_DESIGN', this.$refs.file.files[0]);
                }

                formData.append('json', 'y');

                this.waitAjax = true;

                $.ajax({
                    url: this.ajaxUrl,
                    method: 'POST',
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: BX.delegate(this.formResult, this),
                    error: BX.delegate(this.formError, this)
                });
            },

            formResult(response)
            {
                this.waitAjax = false;

                console.log(response);

                if (response.success)
                {
                    this.finished = true;
                }
            },

            formError()
            {
                this.waitAjax = false;
            }
        },
        watch: {
            quantity(val, oldVal)
            {
                if (!val.length)
                    return;

                let val2 = parseInt(val);

                if (isNaN(val2) || val2<=0)
                {
                    this.quantity = oldVal;
                    return;
                }

                if (val != val2)
                    this.quantity = val2;
            }
        },
        template: template
    });

    return app.mount(container);
}
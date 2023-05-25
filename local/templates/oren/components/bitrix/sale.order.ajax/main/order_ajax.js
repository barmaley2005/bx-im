function createVueOrderAjaxComponent(pContainer, pTemplate, parameters)
{
    let app = BX.Vue3.BitrixVue.createApp({
        data() {
            return {
                result: parameters.result,
                locations: {},
                cleanLocations: {},
                locationsInitialized: false,
                params: parameters.params,

                signedParamsString: parameters.signedParamsString,
                siteId: parameters.siteID,
                ajaxUrl: parameters.ajaxUrl,

                signedBasketParamsString: parameters.signedBasketParamsString,
                signedBasketTemplate: parameters.signedBasketTemplate,

                storeList: parameters.storeList,

                deliveryLocationInfo: {
                    city: false,
                    loc: false,
                    zip: false,
                },

                orderBlockNode: false,

                orderWatcher: [],
                personTypeOld: false,

                policyAgreement: true,

                showModal: false,
                modalTitle: false,
                modalContent: false,

                useBonus: false,

                groupRefs: [],
            };
        },
        mounted() {
            this.prepareLocations(parameters.locations);

            this.editOrder();

            this.personTypeOld = this.personTypeId;
        },
        computed: {

            propGroups() {

                let groups = {};

                this.result.ORDER_PROP.groups.forEach(group => {
                    groups[group.ID] = Object.assign({}, group, {props: {}});
                });

                this.result.ORDER_PROP.properties.forEach(prop => {
                    prop.component = 'order-property-'+prop.TYPE.toLowerCase();

                    if (parseInt(prop.INPUT_FIELD_LOCATION)>0)
                    {
                        if (this.propertyById[prop.INPUT_FIELD_LOCATION])
                        {
                            this.propertyById[prop.INPUT_FIELD_LOCATION].isAltLocation = true;
                        }
                    }

                    groups[prop.PROPS_GROUP_ID].props[prop.ID] = prop;
                });

                let result = {};

                Object.keys(groups).forEach(groupId => {
                    let group = groups[groupId];

                    if (!Object.values(group.props).length)
                        return;

                    result[group.CODE ? group.CODE : group.ID] = group;
                });

                return result;
            },

            propertyById() {
                let result = {};

                this.result.ORDER_PROP.properties.forEach(property => {
                    result[property.ID] = property;
                });

                return result;
            },

            personTypeId() {
                let id = false;

                Object.values(this.result.PERSON_TYPE).forEach(item => {
                    if (item.CHECKED == 'Y')
                        id = item.ID;
                });

                return id;
            },
        },
        methods: {
            prepareLocations: function (locations) {
                this.locationsInitialized = false;
                this.locations = {};
                this.cleanLocations = {};

                var temporaryLocations,
                    i, k, output;

                if (BX.util.object_keys(locations).length) {
                    for (i in locations) {
                        if (!locations.hasOwnProperty(i))
                            continue;

                        this.locationsTemplate = locations[i].template || '';
                        temporaryLocations = [];
                        output = locations[i].output;

                        if (output.clean) {
                            this.cleanLocations[i] = BX.processHTML(output.clean, false);
                            delete output.clean;
                        }

                        for (k in output) {
                            if (output.hasOwnProperty(k)) {
                                temporaryLocations.push({
                                    output: BX.processHTML(output[k], false),
                                    showAlt: locations[i].showAlt,
                                    lastValue: locations[i].lastValue,
                                    coordinates: locations[i].coordinates || false
                                });
                            }
                        }

                        this.locations[i] = temporaryLocations;
                    }
                }
            },

            initPropsListForLocation: function () {

                if (BX.saleOrderAjax && this.result.ORDER_PROP && this.result.ORDER_PROP.properties) {
                    var i, k, curProp, attrObj;

                    BX.saleOrderAjax.cleanUp();

                    for (i = 0; i < this.result.ORDER_PROP.properties.length; i++) {
                        curProp = this.result.ORDER_PROP.properties[i];

                        if (curProp.TYPE == 'LOCATION' && curProp.MULTIPLE == 'Y' && curProp.IS_LOCATION != 'Y') {
                            for (k = 0; k < this.locations[curProp.ID].length; k++) {
                                BX.saleOrderAjax.addPropertyDesc({
                                    id: curProp.ID + '_' + k,
                                    attributes: {
                                        id: curProp.ID + '_' + k,
                                        type: curProp.TYPE,
                                        valueSource: curProp.SOURCE == 'DEFAULT' ? 'default' : 'form'
                                    }
                                });
                            }
                        } else {
                            attrObj = {
                                id: curProp.ID,
                                type: curProp.TYPE,
                                valueSource: curProp.SOURCE == 'DEFAULT' ? 'default' : 'form'
                            };

                            if (!this.deliveryLocationInfo.city && parseInt(curProp.INPUT_FIELD_LOCATION) > 0) {
                                attrObj.altLocationPropId = parseInt(curProp.INPUT_FIELD_LOCATION);
                                this.deliveryLocationInfo.city = curProp.INPUT_FIELD_LOCATION;
                            }

                            if (!this.deliveryLocationInfo.loc && curProp.IS_LOCATION == 'Y')
                                this.deliveryLocationInfo.loc = curProp.ID;

                            if (!this.deliveryLocationInfo.zip && curProp.IS_ZIP == 'Y') {
                                attrObj.isZip = true;
                                this.deliveryLocationInfo.zip = curProp.ID;
                            }

                            BX.saleOrderAjax.addPropertyDesc({
                                id: curProp.ID,
                                attributes: attrObj
                            });
                        }
                    }
                }
            },

            locationsCompletion: function () {
                this.locationsInitialized = true;
            },

            setValueByPath(obj, name, value) {
                let param = obj, i, j, subparam, multiple;

                multiple = name.substring(name.length - 2) === '[]';
                if (multiple)
                    name = name.substring(0, name.length - 2);

                while (true) {
                    i = name.indexOf('[');

                    if (i === -1) {
                        if (multiple)
                        {
                            if (!Array.isArray(param[name]))
                                param[name] = [];
                            param[name].push(value);
                        } else {
                            param[name] = value;
                        }
                        return obj;
                    }

                    j = name.indexOf(']', i);

                    subparam = name.substring(0, i);
                    if (subparam.length > 0) {
                        if (!param.hasOwnProperty(subparam))
                            param[subparam] = {};
                        param = param[subparam];
                    }

                    subparam = name.substring(i + 1, j);
                    name = name.substring(j + 1);

                    if (name.length == 0) {

                        if (multiple)
                        {
                            if (!Array.isArray(param[subparam]))
                                param[subparam] = [];

                            param[subparam].push(value);
                        } else {
                            param[subparam] = value;
                        }

                        return obj;
                    }

                    if (!param.hasOwnProperty(subparam))
                        param[subparam] = {};

                    param = param[subparam];
                }
            },

            getFormData(node)
            {
                let result = {};

                node.querySelectorAll('input, textarea').forEach(item => {

                    if (!item.name.length)
                        return;

                    if ((item.type == 'radio' || item.type == 'checkbox') && !item.checked)
                        return;

                    this.setValueByPath(result, item.name, item.value);
                });

                return result;
            },

            getOrderData(data)
            {
                let ignoreProperties = {};

                if (!data.PERSON_TYPE && !data.PERSON_TYPE_OLD)
                {
                    data.PERSON_TYPE = this.personTypeId;
                    data.PERSON_TYPE_OLD = this.personTypeOld;
                }

                Object.keys(this.locations).forEach(propertyId => {

                    let location = this.locations[propertyId],
                        prop = this.propertyById[propertyId];

                    if (!location[0].showAlt && prop)
                    {
                        ignoreProperties[prop.INPUT_FIELD_LOCATION] = true;
                    }
                });

                Object.values(this.result.ORDER_PROP.properties).forEach(property => {
                    if (ignoreProperties[property.ID])
                        return;

                    let name = 'ORDER_PROP_'+property.ID,
                        value = property.VALUE;

                    if (!Array.isArray(value))
                        value = [value];

                    if (!value.length)
                        return;

                    if (property.MULTIPLE == 'Y')
                    {
                        name+='[]';

                        if (data.hasOwnProperty(name))
                            return;

                        data[name] = value;
                    } else {
                        if (data.hasOwnProperty(name))
                            return;

                        data[name] = value[0];
                    }

                    if (property.DISPLAY_MANUAL)
                    {
                        name = 'LOCATION_ALT_PROP_DISPLAY_MANUAL['+prop.ID+']';

                        if (!data.hasOwnProperty(name))
                        {
                            data[name] = property.VALUE;
                        }
                    }
                });

                Object.values(this.locations).forEach(location => {
                    let node = document.createElement('DIV');

                    node.innerHTML = location[0].output.HTML;

                    let locationData = this.getFormData(node);

                    Object.keys(locationData).forEach(k => {
                        if (!data[k])
                        {
                            data[k] = locationData[k];
                        }
                    });
                });

                if (!data.DELIVERY_ID)
                {
                    Object.values(this.result.DELIVERY).forEach(item => {
                        if (item.CHECKED == 'Y')
                        {
                            data.DELIVERY_ID = item.ID;
                        }
                    });
                }

                if (!data.PAY_SYSTEM_ID)
                {
                    Object.values(this.result.PAY_SYSTEM).forEach(item => {
                        if (item.CHECKED == 'Y')
                        {
                            data.PAY_SYSTEM_ID = item.ID;
                        }
                    });
                }

                if (!data.ORDER_DESCRIPTION)
                {
                    data.ORDER_DESCRIPTION = this.result.ORDER_DESCRIPTION !== false ? this.result.ORDER_DESCRIPTION : '';
                }

                return data;
            },

            sendRequest(action, exData) {
                action = BX.type.isString(action) ? action : 'refreshOrderAjax';

                let request = this.getFormData(this.$el);

                this.getOrderData(request);

                if (this.useBonus && this.result.LOGICTIM_BONUS)
                {
                    request['ORDER_PROP_'+this.result.LOGICTIM_BONUS.ORDER_PROP_PAYMENT_BONUS_ID] = this.result.LOGICTIM_BONUS.PAY_BONUS;
                }

                if (action != 'saveOrderAjax')
                {
                    request = {order: request};
                }

                Object.assign(request, {
                    via_ajax: 'Y',
                    sessid: BX.bitrix_sessid(),
                    SITE_ID: this.siteId,
                    signedParamsString: this.signedParamsString
                });

                request[this.params.ACTION_VARIABLE] = action;

                if (typeof exData === 'object') {
                    Object.assign(request, exData);
                }

                //console.log(request);

                this.result.ERROR = {};

                $.ajax({
                    method: 'POST',
                    url: this.ajaxUrl,
                    data: request,
                    dataType: 'json',
                    context: this,
                    success: function(response) {
                        this.componentAjaxResponse(action, response)
                    },
                });
            },

            componentAjaxResponse(action, response)
            {
                if (typeof response === 'string')
                {
                    if (action === 'enterCoupon')
                    {
                        console.log('invalid coupon');

                        this.modalTitle = BX.message('ORDER_INVALID_PROMO');
                        this.modalContent = '';
                        this.showModal = true;
                    }

                    return;
                }

                if (response.locations)
                {
                    this.deliveryLocationInfo = {};
                    this.prepareLocations(response.locations);
                }

                if (response.order)
                {
                    Object.keys(response.order).forEach(k => {
                        this.result[k] = response.order[k];
                    });

                    if (this.result.REDIRECT_URL)
                    {
                        document.location.href = response.order.REDIRECT_URL;
                        return;
                    }

                    if (response.ID)
                    {
                        let url = window.location.href;

                        if (parseInt(response.ID) > 0) {
                            url = BX.util.add_url_param(url, {ORDER_ID: response.ID});
                        }

                        console.log('redirect to success page, v2 '+url);

                        window.location.href = url;
                        return;
                    }

                    this.personTypeOld = this.personTypeId;
                }

                this.editOrder();

                setTimeout(function() {
                    BX.saleOrderAjax.initDeferredControl();
                }, 0);
            },

            saveOrder()
            {
                if (!this.policyAgreement)
                    return;

                this.sendRequest('saveOrderAjax');
            },

            editOrder()
            {
                this.initPropsListForLocation();
            },

            setGroupRef(instance)
            {
                this.groupRefs.push(instance);
            }

        },
        beforeUpdate()
        {
            this.groupRefs = [];
        },
        template: pTemplate
    });

    app.component('order-prop-group', {
        props: {
            group: {
                type: Object,
                required: true,
            }
        },
        computed: {
            visibleProps()
            {
                let result = {};

                Object.keys(this.group.props).forEach(id => {
                    if (this.group.props[id].isAltLocation)
                        return;

                    result[id] = this.group.props[id];
                });

                return result;
            },
        },
        template: '#order-prop-group-tpl'
    });

    app.component('order-property-string', {
        props: {
            property: {
                type: Object,
                required: true,
            }
        },
        mounted() {
            if (this.$refs.label && this.$refs.input)
            {
                if (this.$refs.input.value.trim().length)
                {
                    this.$refs.label.classList.add('_show');
                }
            }

            if (this.$refs.input && this.property.IS_PHONE == 'Y')
            {
                $(this.$refs.input).mask('+7(000) 000-00-00');
            }
        },
        updated() {
            if (this.$refs.input && this.property.IS_PHONE == 'Y')
            {
                let self = this;

                setTimeout(function() {
                    $(self.$refs.input).mask('+7(000) 000-00-00');
                    $(self.$refs.input).trigger('input');
                }, 0);
            }
        },
        computed: {
            value: {
                get() {
                    if (Array.isArray(this.property.VALUE) && this.property.VALUE.length)
                        return this.property.VALUE[0];

                    return '';
                },
                set(value) {
                    this.property.VALUE = [value];
                },
            },
            htmlId() {
                return 'soa-property-'+this.property.ID;
            },
        },
        methods: {
            onFocus()
            {
                if (!this.$refs.label || !this.$refs.input)
                    return;

                this.$refs.label.classList.add('_show');
            },

            onBlur()
            {
                if (!this.$refs.label || !this.$refs.input)
                    return;

                if (!this.$refs.input.value.trim().length)
                    this.$refs.label.classList.remove('_show');
            },
        },
        template: '#order-property-string-tpl'
    });

    app.component('order-property-location', {
        props: {
            property: {
                type: Object,
                required: true,
            }
        },
        computed: {
            locationsInitialized()
            {
                return this.$root.locationsInitialized;
            },

            currentLocation() {
                if (!this.$root.locations[this.property.ID])
                    return false;

                return this.$root.locations[this.property.ID][0];
            },

            value: {
                get() {
                    if (Array.isArray(this.property.VALUE) && this.property.VALUE.length)
                        return this.property.VALUE[0];

                    return '';
                },
                set(value) {
                    this.property.VALUE = [value];
                },
            },
            htmlId() {
                return 'soa-property-'+this.property.ID;
            },

            propAltLocation()
            {
                if (!this.$root.locations[this.property.ID])
                    return false;

                return this.$root.propertyById[this.property.INPUT_FIELD_LOCATION];
            },
        },
        watch: {
            locationsInitialized(value)
            {
                if (value)
                {
                    let self = this;

                    setTimeout(function() {
                        self.initLocation();
                    }, 0);
                }
            },
        },
        methods: {
            onFocus()
            {
                console.log(this.$refs);
            },

            onBlur()
            {
                console.log(this.$refs);
            },

            initLocation()
            {
                if (!this.$refs.row)
                {
                    console.error('location row not found');
                    return;
                }

                Object.values(this.currentLocation.output.SCRIPT).forEach(item => {
                    BX.evalGlobal(item.JS);
                });

                BX.saleOrderAjax.initDeferredControl();

                /*
                let locationNode = this.$refs.row;

                let clearButton = locationNode.querySelector('div.bx-ui-sls-clear'),
                    inputStep = locationNode.querySelector('div.bx-ui-slst-pool'),
                    inputSearch = locationNode.querySelector('input.bx-ui-sls-fake[type=text]');

                console.log(clearButton, inputStep, inputSearch);

                 */
            },
        },
        template: '#order-property-location-tpl'
    });

    app.component('order-delivery', {
        props: {
            result: {
                type: Object,
                required: true,
            }
        },
        template: '#order-delivery-tpl'
    });

    app.component('order-payment', {
        props: {
            result: {
                type: Object,
                required: true,
            }
        },
        template: '#order-payment-tpl'
    });

    app.component('order-comment', {
        props: {
            result: {
                type: Object,
                required: true,
            }
        },
        computed: {
            description: {
                get()
                {
                    if (this.result.ORDER_DESCRIPTION !== false)
                        return this.result.ORDER_DESCRIPTION;

                    return '';
                },
                set(value)
                {
                    this.result.ORDER_DESCRIPTION = value;
                }
            }
        },
        template: '#order-comment-tpl'
    });

    app.component('order-total', {
        props: {
            result: {
                type: Object,
                required: true,
            }
        },
        computed: {

            activeCoupon()
            {
                if (!Array.isArray(this.result.COUPON_LIST))
                    return false;

                let result = false;

                this.result.COUPON_LIST.every(item => {

                    if (item.JS_STATUS == 'APPLIED')
                        result = item;

                    return !result;
                });
            },

            enteredCouponValue() {
                if (!Array.isArray(this.result.COUPON_LIST))
                    return '';

                let result = false;

                this.result.COUPON_LIST.every(item => {

                    if (item.JS_STATUS == 'APPLIED' || item.JS_STATUS == 'ENTERED')
                        result = item;

                    return !result;
                });

                if (result)
                    return result.COUPON;

                return '';
            },
        },
        methods: {
            enterCoupon(value)
            {
                value = value.trim();
                if (!value.length)
                    return;

                if (this.enteredCouponValue.length)
                {
                    this.$root.sendRequest('removeCoupon', {
                        coupon: this.enteredCouponValue,
                    });
                }

                this.$root.sendRequest('enterCoupon', {
                    coupon: value
                });
            },

            couponChange(value)
            {
                if (this.enteredCouponValue.length)
                {
                    this.$root.sendRequest('removeCoupon', {
                        coupon: this.enteredCouponValue,
                    });
                }
            },
        },
        template: '#order-total-tpl'
    });

    app.component('modal-dialog', {
        props: {
            title: {
                type: String,
            },
            content: {
                type: String,
            }
        },
        emits: ['close'],
        mounted() {
            document.body.appendChild(this.$el);

            $(this.$el).modal('show');

            let self = this;

            $(this.$el).on('hidden.bs.modal', function() {
                console.log('modal close');
                self.$emit('close');
            });

        },
        template: '#order-modal-dialog-tpl'
    });

    app.component('error-block', {
        props: {
            result: {
                type: Object,
                required: true,
            },
            block: {
                type: String,
            }
        },
        computed: {
            errors() {
                if (this.block.length)
                    return this.result.ERROR[this.block] ?? [];

                let result = [];

                Object.keys(this.result.ERROR).forEach(id => {

                    if (Array.isArray(this.result.ERROR[id]))
                    {
                        result.push(...this.result.ERROR[id]);
                    }

                });

                return result;
            },
        },
        template: '#order-error-block-tpl'
    });

    return app.mount(pContainer);
}
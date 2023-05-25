function createVueGiftOrder(params) {
    let app = BX.Vue3.BitrixVue.createApp({
        data() {
            return Object.assign({}, params, {
                form: {
                    senderName: '',
                    senderSecondName: '',
                    senderLastName: '',
                    senderEmail: '',
                    senderPhone: '',
                    receiverName: '',
                    receiverSecondName: '',
                    receiverLastName: '',
                    receiverEmail: '',
                    receiverPhone: '',
                    message: '',
                },
                formErrors: {
                    senderName: false,
                    senderSecondName: false,
                    senderLastName: false,
                    senderEmail: false,
                    senderPhone: false,
                    receiverName: false,
                    receiverSecondName: false,
                    receiverLastName: false,
                    receiverEmail: false,
                    receiverPhone: false,
                },
                requiredFields: [
                    'senderName',
                    'senderSecondName',
                    'senderLastName',
                    'senderEmail',
                    'senderPhone',
                    'receiverName',
                    'receiverSecondName',
                    'receiverLastName',
                    'receiverEmail',
                    'receiverPhone'
                ],
                waitAjax: false,
            });
        },
        mounted() {
            this.requiredFields.forEach(fieldName => {
                    this.$watch(function () {
                        return this.form[fieldName]
                    }, function(val, oldVal)
                    {
                        if (val.length>0)
                        {
                            this.formErrors[fieldName] = false;
                        }
                    });
            });
        },
        methods: {
            submitOrder()
            {
                let errors = 0;

                if (this.waitAjax)
                    return;

                if (!this.$refs.policy.checked)
                    return;

                this.requiredFields.forEach(fieldName => {
                    if (!this.form[fieldName].length)
                    {
                        this.formErrors[fieldName] = true;
                    }

                    if (this.formErrors[fieldName])
                        errors++;
                });

                if (errors)
                    return;

                BX.ajax.runAction('local:lib.api.oren.orderGift', {
                    data: {
                        siteId: BX.message('SITE_ID'),
                        offerId: this.offerId,
                        nominal: this.nominal,
                        customNominal: this.customNominal,
                        form: this.form,
                    }
                }).then(
                    BX.delegate(this.orderGiftResult, this),
                    BX.delegate(this.orderGiftError, this),
                );
            },

            orderGiftResult(response)
            {
                this.waitAjax = false;

                window.location.href = this.orderPath+'?ORDER_ID='+response.data.id;
            },

            orderGiftError(response)
            {
                this.waitAjax = false;
            }
        },
        template: params.template
    });

    app.component('field-input', {
        props: {
            modelValue: {
                type: String,
            },
            inputType: {
                type: String,
                default: 'text'
            },
            label: {
                type: String,
                required: true,
            },
            error: {
                type: Boolean,
                default: false,
            }
        },
        data() {
            return {
                focused: false,
            }
        },
        emits: ['update:modelValue', 'update:error'],
        computed: {
            showLabel()
            {
                return this.focused || this.modelValue.length>0;
            }
        },
        mounted()
        {
            if (this.inputType === 'tel')
                $(this.$refs.input).mask('+7(000) 000-00-00');
        },
        methods: {
            onChange(value)
            {
                this.$emit('update:modelValue', value);

                //this.$refs.input.checkValidity()

                if (!this.$refs.input.reportValidity())
                {
                    let self = this;

                    setTimeout(function() {
                        self.$emit('update:error', true);
                    }, 0);
                }
            }
        },
        template: `
                    <div class="placement-inputs__col" :class="{'_error': error}">
                        <label class="placement-inputs__label"
                            :class="{'_show': showLabel}" 
                            for="" ref="label">{{label}}</label>
                        <input :type="inputType" class="input" :placeholder="label" 
                            :value="modelValue"
                            @focus="focused = true" 
                            @blur="focused = false"
                            @change="onChange($event.target.value)"
                            ref="input"
                            >
                        <span class="placement-inputs__info">'+BX.message('GIFT_ORDER_INVALID_FIELD')+'</span>
                    </div>
        `
    });

    return app.mount(params.container);
}
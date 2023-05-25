function createVueAuthForm(pContainer)
{
    let app = BX.Vue3.BitrixVue.createApp({
        data() {
            return {
                phone: '',
                code: '',
                showTimer: false,
                countDown: 0,
                waitAjax: false,
                errors: {
                    phone: false,
                    code: false,
                },
            }
        },
        mounted() {
            $(this.$refs.phone).mask('+7(000) 000-00-00');
        },
        watch: {
            showTimer(value)
            {
                if (value)
                {
                    setTimeout(BX.delegate(this.countDownTimer, this), 1000);
                }
            },

            phone(value)
            {
                if (value.length === 17)
                {
                    this.$refs.rowPhone.classList.add('_ok');
                } else {
                    this.$refs.rowPhone.classList.remove('_ok');
                }
            },

            code(value)
            {
                value = value.trim();

                if (value.length === 4)
                    this.verifySMSCode(value);
            },
        },
        computed: {
            countDownLabel()
            {
                return this.countDown+' '+BX.message('AUTH_SECOND');
            },
        },
        methods: {
            resetErrors()
            {
                Object.keys(this.errors).forEach(key => {
                    this.errors[key] = false;
                });
            },

            sendSMSCode()
            {
                if (this.waitAjax)
                    return;

                let phone = this.$refs.phone.value;

                if (phone.length<17)
                    return;

                this.resetErrors();

                this.waitAjax = true;

                BX.ajax.runAction('local:lib.api.shop.sendAuthSMS', {
                    data: {
                        phone: this.$refs.phone.value,
                        siteId: BX.message['SITE_ID'],
                    }
                }).then(
                    BX.delegate(this.sendAuthSMSResult, this),
                    BX.delegate(this.sendAuthSMSError, this),
                );
            },

            verifySMSCode(code)
            {
                this.resetErrors();

                BX.ajax.runAction('local:lib.api.shop.verifyAuthCode', {
                    data: {
                        phone: this.$refs.phone.value,
                        code: code,
                        siteId: BX.message['SITE_ID'],
                    }
                }).then(
                    BX.delegate(this.verifyAuthCodeResult, this),
                    BX.delegate(this.verifyAuthCodeError, this),
                );
            },

            sendAuthSMSResult(response)
            {
                this.waitAjax = false;

                this.countDown = response.data.countdown;
                this.showTimer = true;

                this.$refs.code.focus();
            },

            sendAuthSMSError(response)
            {
                this.waitAjax = false;

                this.processErrors(response, 'phone');
            },

            verifyAuthCodeResult(response)
            {
                this.waitAjax = false;

                window.location.reload();
            },

            verifyAuthCodeError(response)
            {
                this.waitAjax = false;
                this.processErrors(response, 'code');
            },

            processErrors(response, defaultBlock)
            {
                let errors = {};

                response.errors.forEach(item => {
                    let code = defaultBlock;

                    if (typeof item.code === 'string')
                    {
                        code = item.code.toLowerCase();
                        if (!this.errors.hasOwnProperty(code))
                            code = defaultBlock;
                    }

                    if (!errors[code])
                        errors[code] = [];

                    errors[code].push(item.message);

                    if (!!item.customData && item.customData.countdown)
                    {
                        this.countDown = parseInt(item.customData.countdown);
                        this.showTimer = true;
                    }
                });

                Object.keys(errors).forEach(key => {
                    this.errors[key] = errors[key].join('<br>');
                });
            },

            countDownTimer()
            {
                this.countDown--;
                if (this.countDown<=0) {
                    this.showTimer = false;
                    return;
                }

                setTimeout(BX.delegate(this.countDownTimer, this), 1000);
            },
        },
        template: '#vue-auth-form-tpl'
    });

    return app.mount(pContainer);
}
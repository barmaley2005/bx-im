function createVueQuiz(params)
{
    let app = BX.Vue3.BitrixVue.createApp({
        data()
        {
            return Object.assign({}, params, {
                step: 1,
                config: {},
                form: {
                    wrapType: false,
                    wrapForm: false,
                    wrapSize: false,
                    wrapColor: false,
                    minimumPrice: false,
                    maximumPrice: false,
                },
                loaded: false,
                showResult: false,
            });
        },

        mounted()
        {
            this.ajax('getConfig', {}, this.configResult);
        },

        methods: {

            ajax(action, data, onsuccess)
            {
                data[this.actionVariable] = action;
                data['siteId'] = BX.message('SITE_ID');
                data['template'] = this.signedTemplate;
                data['parameters'] = this.signedParams;

                BX.ajax({
                    url: this.ajaxPath,
                    method: 'POST',
                    data: data,
                    dataType: 'json',
                    onsuccess: BX.delegate(onsuccess, this)
                });
            },

            configResult(response)
            {
                this.config = response;
                this.loaded = true;
            }
        },

        template: params.template
    });

    app.component('quiz-step-1', {
        props: {
            config: {
                type: Object,
                required: true,
            }
        },
        template: '#vue-quiz-step-1-tpl'
    });

    app.component('quiz-step-2', {
        props: {
            config: {
                type: Object,
                required: true,
            }
        },
        template: '#vue-quiz-step-2-tpl'
    });

    app.component('quiz-step-3', {
        props: {
            config: {
                type: Object,
                required: true,
            }
        },
        template: '#vue-quiz-step-3-tpl'
    });

    app.component('quiz-step-4', {
        props: {
            config: {
                type: Object,
                required: true,
            }
        },
        template: '#vue-quiz-step-4-tpl'
    });

    app.component('quiz-step-5', {
        props: {
            config: {
                type: Object,
                required: true,
            }
        },
        data() {
            return {
                minimumPrice: false,
                maximumPrice: false,
                loaded: false,
            };
        },
        mounted() {
            this.$root.ajax('getPriceRange', {values: this.$root.form}, this.getPriceRangeResult)
        },
        methods: {
            getPriceRangeResult(response)
            {
                this.minimumPrice = response.minimumPrice;
                this.maximumPrice = response.maximumPrice;
                this.$root.form.minimumPrice = response.minimumPrice;
                this.$root.form.maximumPrice = response.maximumPrice;
                this.loaded = true;

                window.step5 = this;

                let self = this;

                setTimeout(function() {
                        self.initSlider();
                }, 0);
            },

            initSlider()
            {
                if (!this.$refs.rangeSlider)
                    return;

                noUiSlider.create(this.$refs.rangeSlider, {
                    start: [this.minimumPrice, this.maximumPrice],
                    connect: true,
                    step: 1,
                    range: {
                        'min': [this.$root.form.minimumPrice],
                        'max': [this.$root.form.maximumPrice]
                    }
                });

                this.$refs.rangeSlider.noUiSlider.on('update', BX.delegate(this.updateSlider, this));
            },

            updateSlider(values, handle)
            {
                switch (handle)
                {
                    case 0:
                        this.$root.form.minimumPrice = values[handle];
                        break;
                    case 1:
                        this.$root.form.maximumPrice = values[handle];
                        break;
                }
            },

            setMinimumPrice(value)
            {
                value = parseFloat(value);
                if (isNaN(value))
                {
                    value = this.$root.form.minimumPrice;
                }

                if (this.$refs.rangeSlider)
                    this.$refs.rangeSlider.noUiSlider.set([value, null]);
            },

            setMaximumPrice(value)
            {
                value = parseFloat(value);
                if (isNaN(value))
                {
                    value = this.$root.form.maximumPrice;
                }

                if (this.$refs.rangeSlider)
                    this.$refs.rangeSlider.noUiSlider.set([null, value]);
            }
        },
        template: '#vue-quiz-step-5-tpl'
    });

    app.component('quiz-show-result', {
        mounted() {
            this.$root.ajax('getQuizItems', {values: this.$root.form}, this.getQuizItemsResult)
        },
        methods: {
            resetTest()
            {
                Object.keys(this.$root.form).forEach(key => {
                    this.$root.form[key] = false;
                });

                this.$root.step = 1;
                this.$root.showResult = false;
            },

            getQuizItemsResult(response)
            {
                let ob = BX.processHTML(response.content + response.css + response.js);

                this.$refs.swiper.innerHTML = ob.HTML;

                if (ob.STYLE.length > 0)
                    BX.loadCSS(ob.STYLE);

                BX.ajax.processScripts(ob.SCRIPT, true);
                BX.ajax.processScripts(ob.SCRIPT, false);

                orenShop.renderFavorite();
            },
        },
        template: '#vue-quiz-show-result-tpl'
    });

    return app.mount(params.container);
}
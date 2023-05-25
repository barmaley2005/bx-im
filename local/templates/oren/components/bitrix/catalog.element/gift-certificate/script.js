class DevBxCatalogElementGift {

    constructor(params) {
        this.params = params;

        this.container = document.getElementById(this.params.CONTAINER_ID);

        this.elements = {
            nominal: this.container.querySelectorAll('[data-entity="nominal"]'),
            customNominal: this.container.querySelector('[data-entity="custom-nominal"]'),
            btnAdd2Basket: this.container.querySelector('[data-entity="gift-order"]'),
            bonus: this.container.querySelector('[data-entity="bonus"]'),
            reviews: this.container.querySelector('[data-entity="reviews"]'),
        };

        this.skuProps = this.params.SKU_PROPS;

        this.elements.nominal.forEach(el => {
            BX.bind(el, 'click', BX.delegate(function() {
                var skuValues = this.getSelectedSkuValue(),
                    offer = this.getOfferBySkuProps(skuValues);

                if (offer)
                    this.selectOffer(offer);
                }, this));

        });

        this.skuProps.forEach(skuProp => {
            skuProp.el = this.container.querySelector('[data-entity="tree_prop"][data-value="'+skuProp.ID+'"]');

            skuProp.el.querySelectorAll('input[type=radio]').forEach(input => {

                BX.bind(input, 'click', BX.delegate(function() {
                    this.skuPropClick(skuProp, input);
                }, this));

            });
        });

        BX.bind(this.elements.btnAdd2Basket, 'click', BX.delegate(this.buyGift, this));

        this.updateViewCounter();
        this.initSlider();
        this.initGallery();

        orenShop.renderFavorite();
    }

    getSelectedSkuValue()
    {
        let result = {};

        this.skuProps.forEach(skuProp => {

            let input = skuProp.el.querySelector('input:checked');

            result[skuProp.ID] = input.value;

        });

        return result;
    }

    __getOfferBySkuProps(values, nominal)
    {
        let result = false;

        this.params.OFFERS.every(offer => {

            if (nominal>0 && offer.DISPLAY_PRICE.PRICE !== nominal)
                return true;

            let found = true;

            Object.keys(values).every(id => {

                if (offer.TREE['PROP_'+id] != values[id])
                    found = false;

                return found;
            });

            if (found)
                result = offer;

            return !result;
        });

        return result;
    }

    getOfferBySkuProps(values)
    {
        let result,
            nominal = false;

        this.elements.nominal.forEach(node => {
            if (node.checked) {
                nominal = parseFloat(node.value);
                return false;
            }

            return true;
        });

        result = this.__getOfferBySkuProps(values, nominal);
        if (!result)
            result = this.__getOfferBySkuProps(values, false);

        return result;
    }

    selectOffer(offer)
    {
        if (!offer)
            return;

        this.elements.btnAdd2Basket.dataset.productId = offer.ID;

        this.skuProps.forEach(skuProp => {

            let value = offer.TREE['PROP_'+skuProp.ID];

            let input = skuProp.el.querySelector('input[value="'+value+'"]');
            if (input) {
                if (skuProp.USER_TYPE === 'directory')
                {
                    let elText = skuProp.el.querySelector('.product-box__name');
                    if (elText)
                    {
                        elText.innerHTML = skuProp.VALUES[value].NAME;
                    }
                }
                input.checked = true;
            }
        });

        this.updateViewCounter();
    }

    skuPropClick(skuProp, el)
    {
        var skuValues = this.getSelectedSkuValue(),
            offer = this.getOfferBySkuProps(skuValues);

        if (offer)
        {
            this.selectOffer(offer);
            return;
        }

        skuValues = {}
        skuValues[skuProp.ID] = el.value;
        offer = this.getOfferBySkuProps(skuValues);
        if (offer)
        {
            this.selectOffer(offer);
        }
    }

    updateViewCounter()
    {
        let postData = {
            'AJAX': 'Y',
            'SITE_ID': this.params.SITE_ID,
            'PARENT_ID': 0,
            'PRODUCT_ID': 0,
        };

        if (this.params.OFFERS.length)
        {
            let offer = this.getOfferBySkuProps(this.getSelectedSkuValue());
            if (!offer)
                return;

            postData.PARENT_ID = this.params.ID;
            postData.PRODUCT_ID = offer.ID;
        } else {
            postData.PRODUCT_ID = this.params.ID;
        }

        BX.ajax.post(
            '/bitrix/components/bitrix/catalog.element/ajax.php',
            postData
        );
    }

    initSlider()
    {
        const swiper = new Swiper(this.container.querySelector('.gallery-thumbs-swiper'), {
            direction: 'vertical', // вертикальная прокрутка
            slidesPerView: 5, // показывать по 3 превью
            spaceBetween: 16, // расстояние между слайдами
            navigation: { // задаем кнопки навигации
                nextEl: '.gallery-next', // кнопка Next
                prevEl: '.gallery-prev' // кнопка Prev
            },
            //freeMode: true,
            watchSlidesProgress: true,
            breakpoints: {
                // when window width is >= 320px
                320: {
                    direction: 'horizontal',
                    slidesPerView: 4,
                    spaceBetween: 6,
                },
                576: {
                    direction: 'horizontal',
                    slidesPerView: 5,
                    spaceBetween: 6,
                },
                // when window width is >= 480px
                1200: {
                    direction: 'vertical',
                    slidesPerView: 5,
                    spaceBetween: 16,
                }
            }
        });
        const swiper2 = new Swiper(this.container.querySelector('.gallery-top-swiper'), {
            direction: 'horizontal', // вертикальная прокрутка
            slidesPerView: 1, // показывать по 1 изображению
            spaceBetween: 16, // расстояние между слайдами
            thumbs: {
                swiper: swiper,
                autoScrollOffset: 1,
            },
        });
    }

    initGallery()
    {
        const block = this.container.querySelector('[data-entity="gallery"]');

        if (block) {
            let
                zoomContainer = document.querySelector('.zoom-container'),
                slideAll = block.querySelectorAll('.swiper-slide');

            slideAll.forEach(slide => {
                slide.addEventListener('mousemove', (e) => {
                    let
                        x = e.offsetX == undefined ? e.layerX : e.offsetX,
                        y = e.offsetY == undefined ? e.layerY : e.offsetY,
                        img = slide.querySelector('img');

                    zoomContainer.style.cssText = `
            background-image: url('${img.getAttribute('src')}');
            background-position: ${Math.round((x / slide.offsetWidth * 100))}% ${y / slide.offsetHeight * 100}%;
            visibility: visible;
            opacity: 1
          `
                })

                slide.addEventListener('mouseout', () => {
                    zoomContainer.style.cssText = `
            visibility: hidden;
            opacity: 0;
          `
                })
            })
        }
    }

    buyGift()
    {
        let urlParams = {
            giftId: this.elements.btnAdd2Basket.dataset.productId,
        };

        let nominal = parseFloat(this.elements.customNominal.value);

        if (nominal>0)
        {
            if (nominal<5000)
                return;

            urlParams['nominal'] = nominal;
        }

        window.location.href = BX.util.add_url_param(this.params.ORDER_PAGE, urlParams);
    }
}
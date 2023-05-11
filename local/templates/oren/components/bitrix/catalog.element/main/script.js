class DevBxCatalogElement {

    constructor(params) {
        this.params = params;

        this.container = document.getElementById(this.params.CONTAINER_ID);

        this.elements = {
            price: this.container.querySelector('[data-entity="price"]'),
            priceNew: this.container.querySelector('[data-entity="price-new"]'),
            priceOld: this.container.querySelector('[data-entity="price-old"]'),
            inpQuantity: this.container.querySelector('[data-entity="product-quantity"]'),
            btnAdd2Basket: this.container.querySelector('[data-entity="add2basket"]'),
            bonus: this.container.querySelector('[data-entity="bonus"]'),
            reviews: this.container.querySelector('[data-entity="reviews"]'),
        };

        this.bonusCache = {};

        this.skuProps = this.params.SKU_PROPS;

        this.skuProps.forEach(skuProp => {
            skuProp.el = this.container.querySelector('[data-entity="tree_prop"][data-value="'+skuProp.ID+'"]');

            skuProp.el.querySelectorAll('input[type=radio]').forEach(input => {

                BX.bind(input, 'click', BX.delegate(function() {
                    this.skuPropClick(skuProp, input);
                }, this));

            });
        });

        this.updateBonus();
        this.updateViewCounter();
        this.loadReviews();
        this.initJS();
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

    getOfferBySkuProps(values)
    {
        let result = false;

        this.params.OFFERS.every(offer => {

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

    selectOffer(offer)
    {
        if (offer.DISPLAY_PRICE.DISCOUNT>0)
        {
            this.elements.price.classList.add('_new');
        } else {
            this.elements.price.classList.remove('_new');
        }

        this.elements.priceNew.innerHTML = offer.DISPLAY_PRICE.PRINT_PRICE;
        this.elements.priceOld.innerHTML = offer.DISPLAY_PRICE.PRINT_BASE_PRICE;
        this.elements.btnAdd2Basket.dataset.productId = offer.ID;

        this.skuProps.forEach(skuProp => {

            let value = offer.TREE['PROP_'+skuProp.ID];

            let input = skuProp.el.querySelector('input[value="'+value+'"]');
            if (input)
                input.checked = true;
        });

        this.updateBonus();
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

    updateBonus()
    {
        let data = {
            siteId: this.params.SITE_ID,
        };

        if (this.params.OFFERS.length)
        {
            let offer = this.getOfferBySkuProps(this.getSelectedSkuValue());
            if (!offer)
                return;

            data.productId = offer.ID;
        } else {
            data.productId = this.params.ID;
        }

        if (this.bonusCache[data.productId])
        {
            this.elements.bonus.innerHTML = this.bonusCache[data.productId];
            return;
        }

        this.elements.bonus.innerHTML = '';

        BX.ajax.runAction('local:lib.api.bonus.getProductBonus', {
            data: data
        }).then(
            BX.delegate(this.bonusResult, this),
        );
    }

    bonusResult(response)
    {
        this.bonusCache[response.data.productId] = response.data.value;

        this.elements.bonus.innerHTML = response.data.value;
    }

    loadReviews()
    {
        if (!this.elements.reviews)
            return;

        BX.ajax.runAction('local:lib.api.reviews.getProductReviews', {
            data: {
                productId: this.params.ID
            }
        }).then(
            BX.delegate(this.productReviewsResult, this),
        );
    }

    productReviewsResult(response)
    {
        let ob = BX.processHTML(response.data.content + response.data.css + response.data.js);

        this.elements.reviews.innerHTML = ob.HTML;
        if (ob.STYLE.length > 0)
            BX.loadCSS(ob.STYLE);

        BX.ajax.processScripts(ob.SCRIPT, true);
        BX.ajax.processScripts(ob.SCRIPT, false);
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

    initJS()
    {
        let
            boxAll = this.container.querySelectorAll('.price-box');

        boxAll.forEach((box) => {

            box.addEventListener('click', (e) => {
                let
                    input = box.querySelector('.price-input'),
                    inputValue = +input.getAttribute('value'),
                    target = e.target;

                if (target.closest('.price-plus')) {
                    input.setAttribute('value', inputValue + 1)
                }

                if (target.closest('.price-minus')) {

                    if (inputValue <= 1) {
                        input.setAttribute('value', 1)

                    } else {
                        input.setAttribute('value', inputValue - 1)

                    }
                }

            })
        });
    }

}
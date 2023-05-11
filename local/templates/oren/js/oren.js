(function() {

    let ajaxLoading = false;

    $(document).on('click', '[data-ajax-nav-button]', function(e) {
        e.preventDefault();

        if (ajaxLoading)
            return;

        let container = this.closest('[data-ajax-container]');

        if (!container)
            return;

        let elItems = container.querySelector('[data-ajax-items]'),
            elNav = container.querySelector('[data-ajax-navigation]');

        ajaxLoading = true;

        $.ajax({
            url: this.dataset.ajaxUrl,
            method: 'POST',
            data: {
                ajaxCatalog: 'y',
            },
            success(response) {
                let tmp = document.createElement('DIV');

                let obHtml = BX.processHTML(response, false);

                tmp.innerHTML = obHtml.HTML;

                let ajaxItems = tmp.querySelector('[data-ajax-items]'),
                    ajaxNav = tmp.querySelector('[data-ajax-navigation]');

                if (ajaxItems)
                {
                    elItems.insertAdjacentHTML('beforeend', ajaxItems.innerHTML);
                }

                if (ajaxNav)
                {
                    elNav.innerHTML = ajaxNav.innerHTML;
                } else {
                    elNav.remove();
                }

                BX.ajax.processScripts(obHtml.SCRIPT, false);
            },
            complete() {
                ajaxLoading = false;
            }
        });
    });

    class OrenShop {
        basketCounter = {
            ready: 0,
            delay: 0,
        }

        favoriteItems = []

        constructor() {
            $(document).on('click', '[data-action]', $.proxy(this.elementAction, this));

            this.updateBasketCounter();
            this.updateFavoriteCounter();
        }

        elementAction(e)
        {
            let el = e.currentTarget || e.target,
                action = el.dataset.action;

            if (typeof this[action] === 'function')
            {
                this[action](e);
            }
        }
        updateBasketCounter()
        {
            BX.ajax.runAction('local:lib.api.shop.getBasketCount', {
                data: {}
            }).then(
                BX.delegate(this.basketCountResult, this),
            );
        }

        basketCountResult(response)
        {
            this.basketCounter = response.data;
            this.renderBasketCount();
        }

        renderBasketCount()
        {
            document.querySelectorAll('[data-entity="basket-ready-count"]').forEach(el => {

                if (this.basketCounter.ready>0)
                {
                    el.style.display = '';
                    el.innerHTML = '<span>'+this.basketCounter.ready+'</span>';
                } else {
                    el.style.display = 'none';
                }
            });
        }

        updateFavoriteCounter()
        {
            BX.ajax.runAction('local:lib.api.shop.getFavoriteList', {
                data: {}
            }).then(
                BX.delegate(this.favoriteResult, this),
            );
        }

        favoriteResult(response)
        {
            this.favoriteItems = Object.values(response.data.items).map(v => parseInt(v));
            this.renderFavorite();
        }

        renderFavorite()
        {
            document.querySelectorAll('[data-entity="favorite-count"]').forEach(el => {

                if (this.favoriteItems.length>0)
                {
                    el.style.display = '';
                    el.innerHTML = '<span>'+this.favoriteItems.length+'</span>';
                } else {
                    el.style.display = 'none';
                }
            });

            document.querySelectorAll('[data-action="favorite"]').forEach(el => {

                let productId = parseInt(el.dataset.itemId);

                if (this.favoriteItems.indexOf(productId)>=0)
                {
                    el.classList.add('_add');
                } else {
                    el.classList.remove('_add');
                }
            });
        }

        favorite(e)
        {
            e.preventDefault();

            let el = e.currentTarget || e.target,
                itemId = el.dataset.itemId

            if (!itemId)
                return;

            el.classList.toggle('_add');

            BX.ajax.runAction('local:lib.api.shop.toggleFavorite', {
                data: {
                    id: itemId
                }
            }).then(
                BX.delegate(function() {
                    this.updateFavoriteCounter();
                }, this),
            );
        }

        add2basket(e)
        {
            e.preventDefault();

            if (e.target.dataset.inBasket)
            {
                window.location.href = '/personal/cart/';
                return;
            }

            let productId = parseInt(e.target.dataset.productId),
                quantity = 1;

            if (isNaN(productId))
                return;

            let container = e.target.closest('.product-button');

            if (container)
            {
                let elQuantity = container.querySelector('[data-entity="product-quantity"]');

                if (elQuantity)
                {
                    quantity = parseInt(elQuantity.value);
                    if (isNaN(quantity) || quantity<=0)
                        quantity = 1;
                }
            }

            BX.ajax.runAction('local:lib.api.shop.addBasket', {
                data: {
                    productId: productId,
                    quantity: quantity,
                }
            }).then(
                BX.delegate(this.addBasketResult, this),
            );

            let elNotify = document.createElement('DIV');
            elNotify.classList.add('product-added');
            elNotify.innerHTML = '<p>Товар добавлен в корзину</p>';

            if (e.target.dataset.followBasket !== 'false')
            {
                e.target.textContent = `Перейти в корзину`;
                e.target.dataset.inBasket = true;
            }

            document.body.appendChild(elNotify);

            setTimeout(function() {

                elNotify.classList.add('_show');

                setTimeout(function() {
                    elNotify.classList.remove('_show');

                    setTimeout(function() {
                        elNotify.remove();
                    }, 1000);
                }, 1000)

            }, 0);
        }

        addBasketResult(response)
        {
            BX.onCustomEvent('onBasketAdd', [response.data]);

            this.updateBasketCounter();
        }

        quickView(e)
        {
            e.preventDefault();

            if (this.waitQuickView)
                return;

            let el = e.currentTarget || e.target,
                productId = el.dataset.productId;

            this.waitQuickView = true;

            BX.ajax.runAction('local:lib.api.shop.quickView', {
                data: {
                    productId: productId
                }
            }).then(
                BX.delegate(this.quickViewResult, this),
                BX.delegate(this.quickViewError, this),
            );
        }

        quickViewResult(response)
        {
            this.waitQuickView = false;

            let ob = BX.processHTML(response.data.content + response.data.css + response.data.js);

            let tmp = document.createElement('DIV');
            tmp.innerHTML = '<div class="modal quick-view fade" id="quick-view" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">'+ob.HTML+'</div>';

            let container = tmp.firstElementChild;

            document.body.appendChild(container);

            if (ob.STYLE.length > 0)
                BX.loadCSS(ob.STYLE);

            BX.ajax.processScripts(ob.SCRIPT, true);
            BX.ajax.processScripts(ob.SCRIPT, false);

            $(container).modal('show');
            $(container).on('hidden.bs.modal', function() {
                container.remove();
            });
        }

        quickViewError(response)
        {
            this.waitQuickView = false;
        }

        showBasket(e)
        {
            e.preventDefault();

            if (this.waitBasket)
                return;

            let el = e.currentTarget || e.target,
                productId = el.dataset.productId;

            this.waitBasket = true;

            BX.ajax.runAction('local:lib.api.shop.basketView', {
                data: {
                    productId: productId
                }
            }).then(
                BX.delegate(this.basketViewResult, this),
                BX.delegate(this.basketViewError, this),
            );
        }

        basketViewResult(response)
        {
            this.waitBasket = false;

            let ob = BX.processHTML(response.data.content + response.data.css + response.data.js);

            let tmp = document.createElement('DIV');
            tmp.innerHTML = '<div class="modal fade cart" id="cart" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">'+ob.HTML+'</div>';

            let container = tmp.firstElementChild;

            document.body.appendChild(container);

            if (ob.STYLE.length > 0)
                BX.loadCSS(ob.STYLE);

            BX.ajax.processScripts(ob.SCRIPT, true);
            BX.ajax.processScripts(ob.SCRIPT, false);

            $(container).modal('show');
            $(container).on('hidden.bs.modal', function() {
                container.remove();
            });
        }

        basketViewError(response)
        {
            this.waitBasket = false;
        }
    }

    window.orenShop = new OrenShop();

})();
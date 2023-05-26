class OrenShopModal {

    constructor(id, modalClass) {
        this.id = id;
        this.modalClass = modalClass;
        this.ajaxMethod = false;
        this.ajaxResponse = false;
        this.container = false;
        this.destroyed = false;

        if (orenShop.modals[this.id])
        {
            orenShop.modals[this.id].destroy();

            console.error('modal window '+this.id+' already registered');
        }

        let tmp = document.createElement('DIV');
        tmp.innerHTML = '<div id="' + this.id + '" class="modal ' +
            this.modalClass +
            '" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"></div>';

        this.container = tmp.firstElementChild;

        orenShop.modals[this.id] = this;
    }

    ajaxRunAction(method, postData) {
        this.ajaxMethod = method;

        BX.ajax.runAction(method, {
            data: postData
        }).then(
            BX.delegate(this.ajaxResult, this),
            BX.delegate(this.ajaxError, this),
        );
    }

    ajaxResult(response) {
        if (this.destroyed)
            return;

        this.ajaxResponse = response;

        let el = document.getElementById(this.id);

        if (el) {
            $(el).on('hidden.bs.modal', BX.delegate(this.showModal, this));
            $(el).modal('hide');
        } else {
            this.showModal();
        }
    }

    showModal() {
        let el = document.getElementById(this.id);

        if (el) {
            el.remove();
        }

        if (this.ajaxResponse)
        {
            let ob = BX.processHTML(this.ajaxResponse.data.content + this.ajaxResponse.data.css + this.ajaxResponse.data.js);

            this.container.innerHTML = ob.HTML;

            document.body.appendChild(this.container);

            if (ob.STYLE.length > 0)
                BX.loadCSS(ob.STYLE);

            BX.ajax.processScripts(ob.SCRIPT, true);
            BX.ajax.processScripts(ob.SCRIPT, false);
        } else {
            document.body.appendChild(this.container);
        }

        $(this.container).modal('show');
        $(this.container).on('hidden.bs.modal', BX.delegate(this.destroy, this));
    }

    ajaxError(response) {
        console.log('failed load modal window, ajax method ' + this.ajaxMethod);
        this.destroy();
    }

    destroy()
    {
        this.destroyed = true;

        if (this.container)
            this.container.remove();

        if (orenShop.modals[this.id] === this)
        {
            delete orenShop.modals[this.id];
        }
    }
}

class OrenShop {
    basketCounter = {
        ready: 0,
        delay: 0,
    }

    favoriteItems = []

    modals = {}

    constructor() {
        $(document).on('click', '[data-action]', $.proxy(this.elementAction, this));

        this.updateBasketCounter();
        this.updateFavoriteCounter();

        BX.addCustomEvent("onBasketResult", BX.delegate(this.updateBasketCounter, this));
    }

    elementAction(e) {
        let el = e.currentTarget || e.target,
            action = el.dataset.action;

        if (typeof this[action] === 'function') {
            this[action](e);
        }
    }

    updateBasketCounter() {
        BX.ajax.runAction('local:lib.api.shop.getBasketCount', {
            data: {
                siteId: BX.message['SITE_ID'],
            }
        }).then(
            BX.delegate(this.basketCountResult, this),
        );
    }

    basketCountResult(response) {
        this.basketCounter = response.data;
        this.renderBasketCount();
    }

    renderBasketCount() {
        document.querySelectorAll('[data-entity="basket-ready-count"]').forEach(el => {

            if (this.basketCounter.ready > 0) {
                el.style.display = '';
                el.innerHTML = '<span>' + this.basketCounter.ready + '</span>';
            } else {
                el.style.display = 'none';
            }
        });
    }

    updateFavoriteCounter() {
        BX.ajax.runAction('local:lib.api.shop.getFavoriteList', {
            data: {
                siteId: BX.message['SITE_ID'],
            }
        }).then(
            BX.delegate(this.favoriteResult, this),
        );
    }

    favoriteResult(response) {
        this.favoriteItems = Object.values(response.data.items).map(v => parseInt(v));
        this.renderFavorite();
    }

    renderFavorite() {
        document.querySelectorAll('[data-entity="favorite-count"]').forEach(el => {

            if (this.favoriteItems.length > 0) {
                el.style.display = '';
                el.innerHTML = '<span>' + this.favoriteItems.length + '</span>';
            } else {
                el.style.display = 'none';
            }
        });

        document.querySelectorAll('[data-action="favorite"]').forEach(el => {

            let productId = parseInt(el.dataset.itemId);

            if (this.favoriteItems.indexOf(productId) >= 0) {
                el.classList.add('_add');
            } else {
                el.classList.remove('_add');
            }
        });
    }

    favorite(e) {
        e.preventDefault();

        let el = e.currentTarget || e.target,
            itemId = el.dataset.itemId

        if (!itemId)
            return;

        el.classList.toggle('_add');

        BX.ajax.runAction('local:lib.api.shop.toggleFavorite', {
            data: {
                id: itemId,
                siteId: BX.message['SITE_ID'],
            }
        }).then(
            BX.delegate(function () {
                this.updateFavoriteCounter();
            }, this),
        );
    }

    add2basket(e) {
        e.preventDefault();

        if (e.target.dataset.inBasket) {
            window.location.href = BX.message('SITE_DIR')+'personal/cart/';
            return;
        }

        let productId = parseInt(e.target.dataset.productId),
            quantity = 1;

        if (isNaN(productId))
            return;

        let container = e.target.closest('.product-button');

        if (container) {
            let elQuantity = container.querySelector('[data-entity="product-quantity"]');

            if (elQuantity) {
                quantity = parseInt(elQuantity.value);
                if (isNaN(quantity) || quantity <= 0)
                    quantity = 1;
            }
        }

        BX.ajax.runAction('local:lib.api.shop.addBasket', {
            data: {
                productId: productId,
                quantity: quantity,
                siteId: BX.message['SITE_ID'],
            }
        }).then(
            BX.delegate(this.addBasketResult, this),
        );

        let elNotify = document.createElement('DIV');
        elNotify.classList.add('product-added');
        elNotify.innerHTML = '<p>'+BX.message('PUBLIC_PRODUCT_ADDED_TO_BASKET')+'</p>';

        if (e.target.dataset.followBasket !== 'false') {
            e.target.textContent = BX.message('PUBLIC_GO_TO_BASKET');
            e.target.dataset.inBasket = true;
        }

        document.body.appendChild(elNotify);

        setTimeout(function () {

            elNotify.classList.add('_show');

            setTimeout(function () {
                elNotify.classList.remove('_show');

                setTimeout(function () {
                    elNotify.remove();
                }, 1000);
            }, 1000)

        }, 0);
    }

    addBasketResult(response) {
        BX.onCustomEvent('onBasketAdd', [response.data]);

        this.updateBasketCounter();
    }

    quickView(e) {
        e.preventDefault();

        if (this.waitAjax)
            return;

        let el = e.currentTarget || e.target,
            productId = el.dataset.productId;

        this.waitAjax = true;

        BX.ajax.runAction('local:lib.api.shop.quickView', {
            data: {
                productId: productId,
                siteId: BX.message['SITE_ID'],
            }
        }).then(
            BX.delegate(this.quickViewResult, this),
            BX.delegate(this.quickViewError, this),
        );
    }

    quickViewResult(response) {
        this.waitAjax = false;

        let ob = BX.processHTML(response.data.content + response.data.css + response.data.js);

        let tmp = document.createElement('DIV');
        tmp.innerHTML = '<div class="modal quick-view fade" id="quick-view" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">' + ob.HTML + '</div>';

        let container = tmp.firstElementChild;

        document.body.appendChild(container);

        if (ob.STYLE.length > 0)
            BX.loadCSS(ob.STYLE);

        BX.ajax.processScripts(ob.SCRIPT, true);
        BX.ajax.processScripts(ob.SCRIPT, false);

        $(container).modal('show');
        $(container).on('hidden.bs.modal', function () {
            container.remove();
        });
    }

    quickViewError(response) {
        this.waitAjax = false;
    }

    showBasket(e) {
        e.preventDefault();

        if (this.waitAjax)
            return;

        let el = e.currentTarget || e.target,
            productId = el.dataset.productId;

        this.waitAjax = true;

        BX.ajax.runAction('local:lib.api.shop.basketView', {
            data: {
                productId: productId,
                siteId: BX.message['SITE_ID'],
            }
        }).then(
            BX.delegate(this.basketViewResult, this),
            BX.delegate(this.basketViewError, this),
        );
    }

    basketViewResult(response) {
        this.waitAjax = false;

        let ob = BX.processHTML(response.data.content + response.data.css + response.data.js);

        let tmp = document.createElement('DIV');
        tmp.innerHTML = '<div class="modal fade cart" id="cart" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">' + ob.HTML + '</div>';

        let container = tmp.firstElementChild;

        document.body.appendChild(container);

        if (ob.STYLE.length > 0)
            BX.loadCSS(ob.STYLE);

        BX.ajax.processScripts(ob.SCRIPT, true);
        BX.ajax.processScripts(ob.SCRIPT, false);

        $(container).modal('show');
        $(container).on('hidden.bs.modal', function () {
            container.remove();
        });
    }

    basketViewError(response) {
        this.waitAjax = false;
    }

    showRegistrationForm(e) {
        e.preventDefault();

        $('#registration, #authorization').modal('hide');

        if (this.waitAjax)
            return;

        this.waitAjax = true;

        BX.ajax.runAction('local:lib.api.shop.showRegistrationForm', {
            data: {
                siteId: BX.message['SITE_ID'],
            }
        }).then(
            BX.delegate(this.showRegistrationFormResult, this),
            BX.delegate(this.showRegistrationFormError, this),
        );
    }

    showRegistrationFormResult(response) {
        this.waitAjax = false;

        let ob = BX.processHTML(response.data.content + response.data.css + response.data.js);

        let tmp = document.createElement('DIV');
        tmp.innerHTML = '<div class="modal fade registration" id="registration" tabindex="-1" aria-labelledby="registrationLabel" aria-hidden="true">' + ob.HTML + '</div>';

        let container = tmp.firstElementChild;

        document.body.appendChild(container);

        if (ob.STYLE.length > 0)
            BX.loadCSS(ob.STYLE);

        BX.ajax.processScripts(ob.SCRIPT, true);
        BX.ajax.processScripts(ob.SCRIPT, false);

        $(container).modal('show');
        $(container).on('hidden.bs.modal', function () {
            container.remove();
        });
    }

    showRegistrationFormError() {
        this.waitAjax = false;
    }

    showAuthForm(e) {
        e.preventDefault();

        if (this.waitAjax)
            return;

        $('#registration, #authorization').modal('hide');

        this.waitAjax = true;

        BX.ajax.runAction('local:lib.api.shop.showAuthForm', {
            data: {
                siteId: BX.message['SITE_ID'],
            }
        }).then(
            BX.delegate(this.showAuthFormResult, this),
            BX.delegate(this.showAuthFormError, this),
        );
    }

    showAuthFormResult(response) {
        this.waitAjax = false;

        let ob = BX.processHTML(response.data.content + response.data.css + response.data.js);

        let tmp = document.createElement('DIV');
        tmp.innerHTML = '<div class="modal fade registration" id="authorization" tabindex="-1" aria-labelledby="registrationLabel" aria-hidden="true">' + ob.HTML + '</div>';

        let container = tmp.firstElementChild;

        document.body.appendChild(container);

        if (ob.STYLE.length > 0)
            BX.loadCSS(ob.STYLE);

        BX.ajax.processScripts(ob.SCRIPT, true);
        BX.ajax.processScripts(ob.SCRIPT, false);

        $(container).modal('show');
        $(container).on('hidden.bs.modal', function () {
            container.remove();
        });
    }

    showAuthFormError() {
        this.waitAjax = false;
    }

    logout(e) {
        e.preventDefault();

        BX.ajax.runAction('local:lib.api.user.logout', {
            data: {
                siteId: BX.message['SITE_ID'],
            }
        }).then(
            BX.delegate(this.logoutResult, this),
            BX.delegate(this.logoutError, this),
        );
    }

    logoutResult() {
        window.location.reload();
    }

    logoutError() {

    }

    showDolyameForm(e) {
        e.preventDefault();

        if (this.modals['dolyamy'])
            return;

        (new OrenShopModal('dolyamy', 'fade shares'))
            .ajaxRunAction('local:lib.api.oren.showDolyameForm', {siteId: BX.message['SITE_ID']});
    }

    showPromoRegistrationForm(e) {
        e.preventDefault();

        $('#registration, #authorization').modal('hide');

        (new OrenShopModal('registration', 'fade modalMy registration advertizing-modal'))
            .ajaxRunAction('local:lib.api.oren.showPromoRegistrationForm', {siteId: BX.message['SITE_ID']});
    }

    writeReview(e) {
        e.preventDefault();

        if (this.modals['reviewForm'])
            return;

        (new OrenShopModal('reviewForm', 'fade modalMy comment-modal'))
            .ajaxRunAction('local:lib.api.reviews.getReviewForm',
                {productId: e.target.dataset.productId, siteId: BX.message['SITE_ID']});
    }

    showAuthQuestionForm(e) {
        e.preventDefault();

        if (this.modals['question'])
            return;

        $('#registration, #authorization').modal('hide');

        (new OrenShopModal('question', 'modalMy2 fade authorize'))
            .ajaxRunAction('local:lib.api.oren.showAuthQuestionForm', {siteId: BX.message['SITE_ID']});
    }

    showQuestionForm(e) {
        e.preventDefault();

        if (this.modals['question'])
            return;

        (new OrenShopModal('question', 'fade modalMy comment-modal'))
            .ajaxRunAction('local:lib.api.oren.showQuestionForm', {siteId: BX.message['SITE_ID']});
    }

    switchLang()
    {
        if (BX.message('SITE_ID') == 's1')
        {
            window.location.href = '/en/';
        } else {
            window.location.href = '/';
        }
    }
}

window.orenShop = new OrenShop();

(function () {

    let ajaxLoading = false;

    $(document).on('click', '[data-ajax-nav-button]', function (e) {
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

                if (ajaxItems) {
                    elItems.insertAdjacentHTML('beforeend', ajaxItems.innerHTML);
                }

                if (ajaxNav) {
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

})();
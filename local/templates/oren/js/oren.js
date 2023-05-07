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
        constructor() {

            $(document).on('click', '[data-action="add2basket"]', $.proxy(this.add2basket, this));
        }

        add2basket(e)
        {
            e.preventDefault();

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

            console.log(productId, quantity);

            let el = document.createElement('DIV');
            el.classList.add('product-added');
            el.innerHTML = '<p>Товар добавлен в корзину</p>';

            document.body.appendChild(el);

            setTimeout(function() {

                el.classList.add('_show');

                setTimeout(function() {
                    el.classList.remove('_show');

                    setTimeout(function() {
                        el.remove();
                    }, 1000);
                }, 1000)

            }, 0);
        }
    }

    window.orenShop = new OrenShop();

})();
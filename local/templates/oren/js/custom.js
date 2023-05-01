$(document).ready(function() {

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

});
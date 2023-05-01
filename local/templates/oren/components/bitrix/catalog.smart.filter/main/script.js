class DevBxCatalogFilter {

    constructor(params) {
        this.formAction = params.formAction;
        this.items = Object.values(params.items);

        this.elItems = {};
        this.idItems = {};
        this.timer = false;
        this.ajaxRequest = false;

        this.elCatalogContent = document.querySelector('.catalog-content');

        this.initItems(true);
    }

    initItems(setEvents)
    {
        this.items.forEach(item => {

            if (parseInt(item.PRICE) || item.DISPLAY_TYPE == 'A')
                return;

            let values = Object.values(item.VALUES);

            values.forEach(value => {

                let id, el;

                id = 'mob_'+value.CONTROL_ID;
                el = document.getElementById(id);
                if (el)
                {
                    this.elItems[id] = el;
                    el.checked = !!value.CHECKED;

                    if (setEvents === true) {
                        el.addEventListener('click', BX.delegate(this.itemClick, this));
                    }
                }

                this.idItems[id] = {
                    item: item,
                    value: value,
                };

                id = value.CONTROL_ID;
                el = document.getElementById(id);
                if (el)
                {
                    this.elItems[id] = el;
                    el.checked = !!value.CHECKED;

                    if (setEvents === true) {
                        el.addEventListener('click', BX.delegate(this.itemClick, this));
                    }
                }

                this.idItems[id] = {
                    item: item,
                    value: value,
                };
            });
        });
    }

    itemClick(e)
    {
        let data = this.idItems[e.target.id];

        if (!data)
            return;

        console.log('item click');

        data.value.CHECKED = e.target.checked;

        let id = 'mob_'+data.value.CONTROL_ID;

        if (this.elItems[id])
            this.elItems[id].checked = data.value.CHECKED;

        if (this.elItems[data.value.CONTROL_ID])
            this.elItems[data.value.CONTROL_ID].checked = data.value.CHECKED;

        if (this.timer)
        {
            clearTimeout(this.timer);
        }

        this.timer = setTimeout(BX.delegate(this.updateFilter, this), 500);
    }

    getFilterData()
    {
        let formData = {};

        this.items.forEach(item => {
            if (parseInt(item.PRICE) || item.DISPLAY_TYPE == 'A')
                return;

            let values = Object.values(item.VALUES);

            values.forEach(value => {
                if (value.CHECKED)
                {
                    formData[value.CONTROL_NAME] = value.HTML_VALUE;
                }
            });
        });

        return formData;
    }

    updateFilter()
    {
        if (this.timer)
        {
            clearTimeout(this.timer);
            this.timer = false;
        }

        if (this.ajaxRequest)
        {
            this.ajaxRequest.abort();
            this.ajaxRequest = false;
        }

        let data = this.getFilterData();

        data.ajax = 'y';

        this.ajaxRequest = $.ajax({
            url: this.formAction,
            method: 'POST',
            data: data,
            dataType: 'json',
            context: this,
            success: this.filterResult,
            complete() {
                this.ajaxRequest = false;
            },
        });
    }

    filterResult(response)
    {
        this.items = Object.values(response.ITEMS);
        this.initItems(false);

        $.ajax({
            url: response.FILTER_URL,
            method: 'POST',
            data: {
                ajaxCatalog: 'y',
            },
            context: this,
            success: this.catalogResult,
        })
    }

    catalogResult(response)
    {
        let tmp = document.createElement('DIV');
        let obHtml = BX.processHTML(response, false);

        let elItems = this.elCatalogContent.querySelector('[data-ajax-items]'),
            elNav = this.elCatalogContent.querySelector('[data-ajax-navigation]');

        tmp.innerHTML = obHtml.HTML;

        let ajaxItems = tmp.querySelector('[data-ajax-items]'),
            ajaxNav = tmp.querySelector('[data-ajax-navigation]');

        if (ajaxItems)
        {
            elItems.innerHTML = ajaxItems.innerHTML;
        }

        if (ajaxNav)
        {
            if (elNav)
            {
                elNav.innerHTML = ajaxNav.innerHTML;
            } else {

                elItems.insertAdjacentHTML('afterend', ajaxNav.outerHTML);
            }
        } else {
            if (elNav)
                elNav.remove();
        }

        BX.ajax.processScripts(obHtml.SCRIPT, false);
    }
}
class DevBxTitleSearch {

    constructor(params) {
        this.params = params;

        this.container = document.getElementById(params.CONTAINER_ID);
        this.input = document.getElementById(params.INPUT_ID);
        this.elSearchResult = document.querySelector('[data-entity="quick-search"]');
        this.showResult = false;
        this.opened = false;

        this.timer = false;

        BX.bind(this.input, 'input', BX.delegate(this.onInput, this));
        BX.bind(this.input, 'change', BX.delegate(this.onInput, this));

        BX.bind(this.input, 'blur', BX.delegate(this.onBlur, this));
        BX.bind(this.input, 'focus', BX.delegate(this.onFocus, this));

        BX.bind(document.body, 'click', BX.delegate(this.bodyClick, this));
    }

    onInput()
    {
        if (this.timer)
            clearTimeout(this.timer);

        this.timer = setTimeout(BX.delegate(this.ajaxSearch, this));
    }

    ajaxSearch()
    {
        this.timer = false;

        let query = this.input.value.trim();

        if (query.length<this.params.MIN_QUERY_LEN)
            return;

        BX.ajax({
            url: this.params.AJAX_PAGE,
            method: 'POST',
            data: {
                ajax_call: 'y',
                INPUT_ID: this.params.INPUT_ID,
                q: query,
                l: this.params.MIN_QUERY_LEN,
            },
            onsuccess: BX.delegate(this.ajaxSearchResult, this)
        })
    }

    ajaxSearchResult(response)
    {
        response = response.trim();

        this.showResult = response.length>0;

        if (this.showResult && this.opened)
        {
            this.elSearchResult.innerHTML = response;
            this.elSearchResult.classList.add('_show');
        } else {
            this.elSearchResult.classList.remove('_show');
        }
    }

    onBlur()
    {
        let self = this;

        if (document.activeElement === document.body)
            return;

        setTimeout(function() {
            self.elSearchResult.classList.remove('_show');
        }, 100);
    }

    onFocus()
    {
        if (this.showResult)
            this.elSearchResult.classList.add('_show');
    }

    bodyClick(e)
    {
        if (e.target.closest('.search-container'))
            return;

        if (e.target.closest('.header-desctop__btn') || e.target.closest('.header-desctop__input')) {

            if (this.opened && e.target.closest('.header-desctop__btn'))
            {
                this.opened = false;

                this.container.classList.remove('_show');
                let self = this;

                setTimeout(function() {
                    self.elSearchResult.classList.remove('_show');
                }, 100);

                return;
            }

            this.opened = true;

            this.container.classList.add('_show');
            if (this.showResult)
                this.elSearchResult.classList.add('_show');
        } else {
            this.opened = false;

            this.container.classList.remove('_show');

            let self = this;

            setTimeout(function() {
                self.elSearchResult.classList.remove('_show');
            }, 100);
        }
    }

}
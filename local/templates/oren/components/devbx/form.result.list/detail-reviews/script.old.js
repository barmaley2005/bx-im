class ProductReviews {

    constructor(params) {
        this.params = params;
        this.container = document.getElementById(this.params.CONTAINER_ID);
        this.waitForm = false;

        this.container.querySelectorAll('[data-action="writeReview"]').forEach(item => {
           BX.bind(item, 'click',  BX.delegate(this.writeReviewClick, this))
        });

        console.log(this);
    }

    writeReviewClick(e)
    {
        e.preventDefault();

        if (this.waitForm)
            return;

        BX.ajax.runAction('local:lib.api.reviews.getReviewForm', {
            data: {
                productId: this.params.PRODUCT_ID,
                siteId: this.params.SITE_ID,
                templateId: this.params.TEMPLATE_ID,
            }
        }).then(
            BX.delegate(this.reviewFormResult, this),
        );
    }

    reviewFormResult(response)
    {
        let ob = BX.processHTML(response.data.content + response.data.css + response.data.js);

        let tmp = document.createElement('DIV');
        tmp.innerHTML = '<div class="modal fade modalMy comment-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">'+ob.HTML+'</div>';

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

}
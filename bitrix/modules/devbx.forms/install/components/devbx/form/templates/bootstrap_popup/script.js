function JCDevBxPopupForm(params) {
    this.params = params;
    BX.ready(BX.proxy(this.init, this));
}

JCDevBxPopupForm.prototype.init = function () {
    this.modalFormId = this.params.uniqueId + '_form';

    this.modalFormContentNode = BX.create('DIV', {attrs: {'class': 'modal-content'}});

    this.modalFormNode = BX.create('DIV', {
        attrs: {id: this.modalFormId, 'class': 'modal fade', tabindex: '-1', role: 'dialog', 'aria-hidden': 'true'}, children: [
            BX.create('DIV', {attrs: {'class': 'modal-dialog', 'role': 'document'}, children: [this.modalFormContentNode]})
        ]
    });
    document.body.append(this.modalFormNode);

    BX.bind(BX(this.params.uniqueId), 'click', BX.proxy(this.openModal, this));
};

JCDevBxPopupForm.prototype.openModal = function (e) {
    e.preventDefault();

    var data = {};

    for (var i = 0; i < this.params.hiddenFields.length; i++) {
        console.log(this.params.hiddenFields[i]);
        data[this.params.hiddenFields[i].NAME] = this.params.hiddenFields[i].VALUE;
    }

    data[this.params.actionVariable] = 'SHOW_POPUP';
    data['template'] = this.params.signedTemplate;
    data['parameters'] = this.params.signedParamsString;
    data['SITE_ID'] = this.params.siteId;

    BX.ajax({
        url: this.params.ajaxUrl,
        method: 'POST',
        data: data,
        dataType: 'html',
        processData: false,
        onsuccess: BX.proxy(this.ajaxResult, this)
    });
};

JCDevBxPopupForm.prototype.ajaxResult = function (result) {
    var ob = BX.processHTML(result, false);
    result = ob.HTML;
    scripts = ob.SCRIPT;
    styles = ob.STYLE;

    if (styles.length > 0)
        BX.loadCSS(styles);

    this.modalFormContentNode.innerHTML = result;

    BX.ajax.processScripts(scripts, true);
    BX.ajax.processScripts(scripts, false);

    //this.bindFormEvents();

    $(this.modalFormNode).modal('show');
};

JCDevBxPopupForm.prototype.bindFormEvents = function () {
    var submitBtn = this.modalFormNode.querySelectorAll('[data-submit]');

    for (i = 0; i < submitBtn.length; i++) {
        BX.bind(submitBtn[i], 'click', BX.proxy(this.submitModalForm, this));
    }
};

JCDevBxPopupForm.prototype.submitModalForm = function (e) {
    e.preventDefault();

    var form = this.modalFormNode.querySelector('form');

    var formData = new FormData;

    var items = form.querySelectorAll('input[type=text], input[type=hidden]'),
        i, j, subItems;

    for (i = 0; i < items.length; i++) {
        formData.append(items[i].name, items[i].value);
    }

    items = form.querySelectorAll('select');
    for (i = 0; i < items.length; i++) {
        subItems = items[i].querySelectorAll('option');
        for (j = 0; j < subItems.length; j++) {
            if (subItems[j].selected) {
                formData.append(items[i].name, subItems[j].value);
            }
        }
    }

    items = form.querySelectorAll('input[type=radio], input[type=checkbox]');
    for (i = 0; i < items.length; i++) {
        if (items[i].checked) {
            formData.append(items[i].name, items[i].value);
        }
    }

    items = form.querySelectorAll('input[type=file]');
    for (i = 0; i < items.length; i++) {
        for (j = 0; j < items[i].files.length; j++) {
            formData.append(items[i].name, items[i].files[j]);
        }
    }

    BX.ajax({
        url: this.params.ajaxUrl,
        method: 'POST',
        data: formData,
        dataType: 'html',
        processData: false,
        preparePost: false,
        onsuccess: BX.proxy(this.ajaxSubmitResult, this)
    });
};

JCDevBxPopupForm.prototype.ajaxSubmitResult = function (result) {
    var ob = BX.processHTML(result, false);
    result = ob.HTML;
    scripts = ob.SCRIPT;
    styles = ob.STYLE;

    var tmp = BX.create('DIV');
    tmp.innerHTML = result;


    this.modalFormNode.querySelector('.modal-content').innerHTML = tmp.querySelector('.modal-content').innerHTML;

    BX.ajax.processScripts(scripts, true);
    BX.ajax.processScripts(scripts, false);

    this.bindFormEvents();
};
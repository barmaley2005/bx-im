function initDevBxFormCustomFieldControl(params) {
    var data = JSON.parse(params.data);

    if (data) {
        window['devbx_form_custom_field_' + params.propertyID] = Object.create(DevBxFormCustomFieldControl);
        window['devbx_form_custom_field_' + params.propertyID].init(data, params);
    }

}

console.log(typeof DevBxFormCustomFieldControl);

if (typeof DevBxFormCustomFieldControl == 'undefined') {

    console.log('declare');

    window.DevBxFormCustomFieldControl = {

        init: function (data, params) {
            this.params = params || {};
            this.data = data || {};

            let postData = Object.assign({}, data.params);
            postData.fieldParams = data.fieldParams;
            postData.sessid = BX.bitrix_sessid();
            postData.propertyID = params.propertyID;

            BX.ajax({
                    url: data.ajaxPath,
                    method: 'POST',
                    data: postData,
                    onsuccess: BX.proxy(this.fieldResult, this)
                }
            );
        },

        fieldResult: function (result) {

            let node = BX.create('DIV');

            node.innerHTML = '<table>'+result+'</table>';

            this.params.oCont.innerHTML = node.querySelectorAll('td')[1].innerHTML;
        },
    };


}

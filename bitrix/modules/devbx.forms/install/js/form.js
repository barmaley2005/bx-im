console.log('loaded');

function CDevBxFormInfo(arInput) {
    this.arInput = arInput;

}

CDevBxFormInfo.prototype.getInputByFieldName = function (fieldName) {
    for (let i = 0; i < this.arInput.length; i++) {
        if (this.arInput[i].FIELD_NAME === fieldName) {
            return this.arInput[i];
        }
    }

    return false;
};

CDevBxFormInfo.prototype.PHPParser = function (str) {
    var code = oBXEditorUtils.PHPParser.trimPHPTags(str);
    var pMainObj = GLOBAL_pMainObj["CUSTOM_HTML_FORM"];

    if (code.substring(0, 1) === "=") code = code.substring(1);

    code = oBXEditorUtils.PHPParser.cleanCode(code);
    func = oBXEditorUtils.PHPParser.parseFunction(code);

    console.log(code, func);

    if (func.name.substr(0, 7) == '$FORM->') {
        func.name = func.name.substr(7);

        switch (func.name) {
            case 'ShowInput':
                var params = func.params.split(','),
                    fieldName = oBXEditorUtils.PHPParser.trimQuotes(params.shift()),
                    obField = this.getInputByFieldName(fieldName);

                console.log(params,fieldName,obField);

                if (obField)
                {
                    var funcstr = '<img id="' + pMainObj.SetBxTag(false, {tag: "form_field", params: {name:'field_name', value: obField.FIELD_NAME}}) + '" src="'+obField.IMAGE+'"  border="0" />';
                    return funcstr;
                }

                return '';
        }
    }


    return false;
};

function DevBxFormElementsTaskbar() {
    var oTaskbar = this;

    console.log('create taskbar');

    DevBxFormElementsTaskbar.prototype.OnTaskbarCreate = function () {
        oTaskbar.pCellData = oTaskbar.CreateScrollableArea(oTaskbar.pWnd);

        console.log('register form_field');
        oBXEditorUtils.addPropertyBarHandler('form_field', BX.proxy(this.ShowProperties, this));

        this.DisplayTree();
    };

    DevBxFormElementsTaskbar.prototype.DisplayTree = function () {
        var arElements = [];
        var _arElement = {
            name: 'cformfields',
            title: BX.message('DEBX_FORMS_FORM_EDIT_TASKBARSET_TITLE'),
            tagname: 'cformfields',
            isGroup: true,
            childElements: []
        };

        if (oDevBxForm.arInput.length > 0) {
            for (let i = 0; i < oDevBxForm.arInput.length; i++) {
                _arElement.childElements.push({
                    group: 'cformfields',
                    tagname: 'form_field',
                    name: oDevBxForm.arInput[i].FIELD_NAME,
                    title: oDevBxForm.arInput[i].CAPTION,
                    icon: oDevBxForm.arInput[i].IMAGE,
                    isGroup: false,
                    params: {name: 'field_name', value: oDevBxForm.arInput[i].FIELD_NAME}
                });
            }
        }

        if (_arElement['childElements'].length > 0)
            arElements.push(_arElement);

        oTaskbar.DisplayElementList(arElements, oTaskbar.pCellData);

        console.log('DisplayTree');
    };

    DevBxFormElementsTaskbar.prototype.ShowProperties = function (_bNew, _pTaskbar, _pElement) {
        var oTag = _pTaskbar.pMainObj.GetBxTag(_pElement);
        var fieldName= oTag.params.value;

    };

    DevBxFormElementsTaskbar.prototype.OnElementDragEnd = function (oEl) {
        if (!oEl)
            return;

        // Run it only when dropped into editor doc
        if (oEl.ownerDocument != oTaskbar.pMainObj.pEditorDocument)
            return oTaskbar.OnElementDragEnd(oTaskbar.pMainObj.pEditorDocument.body.appendChild(oEl.cloneNode(false)));

        var oTag = oTaskbar.pMainObj.GetBxTag(oEl);

        oTag.id = null;
        delete oTag.id;
        oEl.id = '';
        oEl.removeAttribute('id');

        oTag = copyObj(oTag);
        var draggedElId = oTaskbar.pMainObj.SetBxTag(oEl, oTag);

        // Hack for safari
        if (BX.browser.IsSafari()) {
            if (oEl && oEl.parentNode)
                oEl.parentNode.removeChild(oEl);
            oTaskbar.pMainObj.insertHTML('<img src="' + oEl.src + '" id="' + draggedElId + '">');

            setTimeout(function () {
                oTaskbar.pMainObj.SelectElement(oTaskbar.pMainObj.pEditorDocument.getElementById(draggedElId))
            }, 20);
        }

        this.nLastDragNDropElement = null;
        this.nLastDragNDropElementFire = false;

        if (oTag.tag == 'form_field') {

            let obField = oDevBxForm.getInputByFieldName(oTag.params.value);

            if (obField)
                oEl.setAttribute('src', obField.IMAGE);
            oEl.removeAttribute("height");
            oEl.removeAttribute("width");
            oEl.style.width = null;
            oEl.style.height = null;
        }
    };

    DevBxFormElementsTaskbar.prototype.UnParseElement = function (node, pMainObj) {
        var id = node.arAttributes["id"];
        if (!id)
            return false;

        var bxTag = pMainObj.GetBxTag(id);

        console.log(bxTag);

        if (bxTag.tag == 'form_field') {
            return '<' + '?=$FORM->ShowInput(\'' + bxTag.params.value + '\')?' + '>';
        }

        return false;
    };
}
;(function () {
    function __run() {
        function FormFieldsButton(editor, wrap) {

            FormFieldsButton.superclass.constructor.apply(this, arguments);

            this.id = 'devbx_webform_fields';
            this.title = BX.message('DEVBX_WEB_FORM_HTML_EDITOR_INSERT_FIELD_TITLE');
            //this.className += ' bxhtmled-button-fontsize';
            this.activeClassName = 'bxhtmled-top-bar-btn-active bxhtmled-button-fontsize-active';
            this.disabledClassName = 'bxhtmled-top-bar-btn-disabled bxhtmled-button-fontsize-disabled';
            this.action = 'webFormField';
            this.zIndex = 3007;
            this.disabledForTextarea = !editor.bbCode;

            this.arValues = [{
                id: 'field-10',
                className: 'devbx-webform-field',
                name: 'name',
                title: 'title',
                topName: 'topName',
                action: this.action,
                value: '<b>test</b>',
                defaultValue: true
            }];

            this.Create();

            if (wrap)
                wrap.appendChild(this.pCont_);

            BX.addCustomEvent(this, "OnPopupClose", BX.proxy(this.OnPopupClose, this));

            let _this = this;

            this.editor.phpParser.AddCustomParser(function (content) {

                let webFormFields = [],
                    fieldByName = {};

                BX.onCustomEvent(_this.editor, 'GetDevBxFormFiles', [webFormFields]);

                webFormFields.forEach(item => {
                    fieldByName[item.name] = item;
                });

                content = content.replace(/#([a-zA-Z0-9_\.]+)#/g, function (str, ind) {

                    if (!fieldByName[ind])
                        return str;

                    /*
                    let name = BX.util.htmlspecialchars(fieldByName[ind].label),
                        title = fieldByName[ind].name;

                    name = name.replace('"', '\"');

                    let params =  {value: '#'+ind+'#'},
                        id = _this.editor.SetBxTag(false, {tag: 'object', name: name, params: params}),
                        surrogateId = _this.editor.SetBxTag(false, {tag: "surrogate_dd", params: {origParams: params, origId: id}});

                    _this.editor.SetBxTag({id: id}, {tag: 'object', name: name, params: params, title: title, surrogateId: surrogateId});

                    return '<span class="bxhtmled-surrogate devbx-webform-macros-field" title="'+title+'" id="'+id+'">'+name+'</span>'
                     */

                    return _this.editor.phpParser.GetSurrogateHTML(
                        'object',
                        ind,
                        fieldByName[ind].label,
                        {value: '#'+ind+'#'}
                    );
                });

                return content;

            });
        }

        BX.extend(FormFieldsButton, window.BXHtmlEditor.DropDownList);

        FormFieldsButton.prototype.Create = function () {
            this.pCont_ = BX.create("SPAN", {
                props: {
                    className: 'bxhtmled-button-fontsize-wrap',
                    title: this.title
                }
            });
            this.pCont = this.pButCont = this.pCont_.appendChild(BX.create("SPAN", {
                props: {className: this.className},
                html: BX.message('DEVBX_WEB_FORM_HTML_EDITOR_INSERT_FIELD_TEXT')
            }));
            this.pListCont = this.pCont_.appendChild(BX.create("SPAN", {
                props: {
                    className: 'bxhtmled-top-bar-select',
                    title: this.title
                }, attrs: {unselectable: 'on'}, text: '', style: {display: 'none'}
            }));

            this.pValuesCont = BX.create("DIV", {
                props: {className: "bxhtmled-popup bxhtmled-dropdown-cont"},
                html: '<div class="bxhtmled-popup-corner"></div>'
            });
            this.pValuesCont.style.zIndex = this.zIndex;

            this.pValuesContWrap = this.pValuesCont.appendChild(BX.create("DIV", {props: {className: "bxhtmled-dropdown-cont"}}));
            this.valueIndex = {};
            var but, value, _this = this, i,
                itemClass = 'bxhtmled-dd-list-item',
                webFormFields = [];

            BX.onCustomEvent(this.editor, 'GetDevBxFormFiles', [webFormFields]);

            console.log(webFormFields);

            for (i = 0; i < webFormFields.length; i++) {
                value = webFormFields[i];
                but = this.pValuesContWrap.appendChild(BX.create("SPAN", {
                    props: {className: value.className || itemClass},
                    html: '['+value.name+'] '+value.label,
                    style: value.style || {}
                }));

                but.setAttribute('data-bx-dropdown-value', value.name);
                this.valueIndex[value.id] = i;

                if (value.action) {
                    but.setAttribute('data-bx-type', 'action');
                    but.setAttribute('data-bx-action', value.action);
                    if (value.value)
                        but.setAttribute('data-bx-value', value.value);
                }

                BX.bind(but, 'mousedown', function (e) {
                    _this.SelectItem(this.getAttribute('data-bx-dropdown-value'));
                    _this.editor.CheckCommand(this);
                    _this.Close();
                });
            }

            this.editor.RegisterCheckableAction(this.action, {
                action: this.action,
                control: this
            });

            BX.bind(this.pCont_, 'click', BX.proxy(this.OnClick, this));
            BX.bind(this.pCont, "mousedown", BX.delegate(this.OnMouseDown, this));
        };

        /*
        FormFieldsButton.prototype.SetValue = function (active, state) {
            if (state && state[0]) {
                var element = state[0];
                var value = element.style.fontSize;

                this.SelectItem(false, {value: parseInt(value, 10), title: value});
            } else {
                this.SelectItem(false, {value: 0});
            }
        };*/

        FormFieldsButton.prototype.SelectItem = function (valDropdown) {
            let _this = this;

            this.editor.action.Exec('insertHTML', '#'+valDropdown+'#');

            setTimeout(function () {
                _this.editor.synchro.FullSyncFromIframe();
            }, 50);


            /*
            let html = this.editor.phpParser.GetSurrogateHTML(
                'devbxwebformfield',
                BX.message('DEVBX_WEB_FORM_HTML_EDITOR_FIELD_CODE'),
                val.title || val.value,
                val
            );

            this.editor.InsertHtml(html);

             */

            /*
            if (val.value) {
                this.pListCont.innerHTML = val.value;
                this.pListCont.title = this.title + ': ' + (val.title || val.value);
                this.pListCont.style.display = '';
                this.pButCont.style.display = 'none';
            } else {
                this.pListCont.title = this.title;
                this.pButCont.style.display = '';
                this.pListCont.style.display = 'none';
            }*/
        };

        FormFieldsButton.prototype.GetPopupBindCont = function () {
            return this.pCont_;
        };

        FormFieldsButton.prototype.Open = function () {
            FormFieldsButton.superclass.Open.apply(this, arguments);

            // Show or hide first value of the list
            this.pValuesContWrap.firstChild.style.display = this.editor.bbCode && this.editor.synchro.IsFocusedOnTextarea() ? 'none' : '';

            BX.addClass(this.pListCont, 'bxhtmled-top-bar-btn-active');
        };

        FormFieldsButton.prototype.Close = function () {
            FormFieldsButton.superclass.Close.apply(this, arguments);
            BX.removeClass(this.pListCont, 'bxhtmled-top-bar-btn-active');
        };

        FormFieldsButton.prototype.OnPopupClose = function () {
            var more = this.editor.toolbar.controls.More;
            setTimeout(function () {
                if (more && more.bOpened) {
                    more.CheckOverlay();
                }
            }, 100);
        };

        window.BXHtmlEditor.Controls['FormFields'] = FormFieldsButton;
    }

    if (window.BXHtmlEditor && window.BXHtmlEditor.Button && window.BXHtmlEditor.Dialog)
        __run();
    else
        BX.addCustomEvent(window, "OnEditorBaseControlsDefined", __run);


})();

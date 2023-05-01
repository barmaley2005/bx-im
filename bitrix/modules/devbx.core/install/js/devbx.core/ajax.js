this.DevBX = this.DevBX || {};
(function (exports) {
    'use strict';

    var AjaxComponent = /*#__PURE__*/function () {
      function AjaxComponent() {
        babelHelpers.classCallCheck(this, AjaxComponent);
      }
      babelHelpers.createClass(AjaxComponent, [{
        key: "getTemplate",
        value: function getTemplate(templateName) {
          if (typeof this.templates === 'undefined') {
            this.templates = {};
          }
          if (!this.templates.hasOwnProperty(templateName)) {
            var template = BX(templateName);
            if (!BX.type.isDomNode(template)) {
              console.log('failed find template ' + templateName);
            }
            this.templates[templateName] = BX.type.isDomNode(template) ? template.innerHTML : '';
          }
          return this.templates[templateName];
        }
      }, {
        key: "loadTemplates",
        value: function loadTemplates(callback, context) {
          var data = {
            method: 'loadTemplates',
            callback: BX.proxy(function (result) {
              this.loadTemplatesResult(result);
              if (typeof callback === 'function') {
                if (typeof context === 'undefined') context = window;
                callback.apply(context);
              }
            }, this)
          };
          this.sendRequest(data);
        }
      }, {
        key: "loadTemplatesResult",
        value: function loadTemplatesResult(result) {
          if (!!result.ERROR) {
            commonApi.showError(result.ERROR);
            return;
          }
          if (typeof this.templates === 'undefined') {
            this.templates = {};
          }
          if (!!result.JS_TEMPLATES) {
            this.templates = Object.assign(this.templates, result.JS_TEMPLATES);
          }
        }
      }, {
        key: "sendRequest",
        value: function sendRequest(data) {
          console.log(this.ajaxUrl, 'ajaxUrl');
          if (!this.ajaxUrl) return;
          var postData = {
            sessid: BX.bitrix_sessid(),
            parameters: this.signedParams,
            template: this.signedTemplate
          };
          Object.assign(postData, data);
          delete postData['callback'];
          if (!!postData.method) {
            postData[this.actionVariable] = postData.method;
            delete postData.method;
          }
          $.ajax({
            url: this.ajaxUrl,
            method: 'POST',
            data: postData,
            dataType: 'json',
            context: {
              self: this,
              requestData: data
            },
            success: this.singleRequestResult
          });
        }
      }, {
        key: "singleRequestResult",
        value: function singleRequestResult(result) {
          if (typeof this.requestData.callback === 'function') {
            this.requestData.callback.apply(this.self, [result]);
            return;
          }
          var callback = this.requestData.hasOwnProperty('callback') ? this.requestData.callback : this.requestData.method + 'Result';
          if (typeof this.self[callback] === 'function') {
            this.self[callback].apply(this.self, [result]);
          }
        }
      }, {
        key: "sendPackageRequest",
        value: function sendPackageRequest(items) {
          if (!this.ajaxUrl) return;
          var postData = {
            sessid: BX.bitrix_sessid(),
            parameters: this.signedParams,
            template: this.signedTemplate,
            items: items
          };
          postData[this.actionVariable] = 'package';
          $.ajax({
            url: this.ajaxUrl,
            method: 'POST',
            data: postData,
            dataType: 'json',
            context: this,
            success: this.packageResult
          });
        }
      }, {
        key: "packageResult",
        value: function packageResult(result) {
          var _this = this;
          if (result.hasOwnProperty('ERROR')) {
            commonApi.showError(result.ERROR);
            return;
          }
          result.forEach(function (item) {
            var callback = item.hasOwnProperty('callback') ? item.callback : item.method + 'Result';
            if (typeof _this[callback] === 'function') {
              _this[callback](item);
            }
          });
        }
      }]);
      return AjaxComponent;
    }();

    exports.AjaxComponent = AjaxComponent;

}((this.DevBX.Ajax = this.DevBX.Ajax || {})));
//# sourceMappingURL=ajax.js.map

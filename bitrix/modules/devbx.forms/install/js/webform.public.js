this.DevBX = this.DevBX || {};
this.DevBX.Forms = this.DevBX.Forms || {};
(function (exports) {
  'use strict';

  var tmpId = 1;
  var WebForm = function WebForm(config) {
    babelHelpers.classCallCheck(this, WebForm);
    this.actions = config.formActions;
    this.settings = config.formSettings;
    this.pages = config.pages = new WebFormPages(config.pages);
    this.finishPage = config.finishPage;
    this.finishPageCond = config.finishPageCond;
  };
  var WebFormPages = /*#__PURE__*/function () {
    function WebFormPages(pages) {
      var _this = this;
      babelHelpers.classCallCheck(this, WebFormPages);
      this.items = [];
      pages.forEach(function (item, index) {
        pages[index] = new WebFormPage(item);
        _this.items.push(pages[index]);
      });
    }
    babelHelpers.createClass(WebFormPages, [{
      key: "getAllFormFields",
      value: function getAllFormFields() {
        var result = [];
        this.items.forEach(function (item) {
          result.push.apply(result, babelHelpers.toConsumableArray(item.getAllFormFields()));
        });
        return result;
      }
    }]);
    return WebFormPages;
  }();
  var WebFormCollection = /*#__PURE__*/function () {
    function WebFormCollection() {
      babelHelpers.classCallCheck(this, WebFormCollection);
      this.id = tmpId++;
      this.items = [];
      this._parent = null;
    }
    babelHelpers.createClass(WebFormCollection, [{
      key: "getItemById",
      value: function getItemById(id) {
        var result = null;
        this.items.every(function (item) {
          if (item.id === id) result = item;
          return result === null;
        });
        return result;
      }
    }, {
      key: "parent",
      get: function get() {
        return this._parent;
      },
      set: function set(value) {
        this._parent = value;
      }
    }, {
      key: "root",
      get: function get() {
        var result = this._parent;
        if (!result) return result;
        while (result.parent) {
          result = result.parent;
        }
        return result;
      }
    }]);
    return WebFormCollection;
  }();
  var WebFormLayout = /*#__PURE__*/function (_WebFormCollection) {
    babelHelpers.inherits(WebFormLayout, _WebFormCollection);
    function WebFormLayout(config, rows) {
      var _this2;
      babelHelpers.classCallCheck(this, WebFormLayout);
      _this2 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WebFormLayout).call(this));
      _this2.config = config;
      rows.forEach(function (item, index) {
        rows[index] = new WebFormPageRow(item);
        rows[index].parent = babelHelpers.assertThisInitialized(_this2);
        _this2.items.push(rows[index]);
      });
      return _this2;
    }
    babelHelpers.createClass(WebFormLayout, [{
      key: "_getItemsRecursive",
      value: function _getItemsRecursive(items) {
        this.items.forEach(function (row) {
          row.items.forEach(function (item) {
            if (item.childLayout) {
              item.childLayout._getItemsRecursive(items);
            }
            items.push(item);
          });
        });
      }
    }, {
      key: "getAllFormFields",
      value: function getAllFormFields() {
        var result = [];
        this._getItemsRecursive(result);
        return result;
      }
    }]);
    return WebFormLayout;
  }(WebFormCollection);
  var WebFormPage = /*#__PURE__*/function (_WebFormLayout) {
    babelHelpers.inherits(WebFormPage, _WebFormLayout);
    function WebFormPage(page) {
      babelHelpers.classCallCheck(this, WebFormPage);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WebFormPage).call(this, page.config, page.rows));
    }
    return WebFormPage;
  }(WebFormLayout);
  var WebFormPageRow = /*#__PURE__*/function (_WebFormCollection2) {
    babelHelpers.inherits(WebFormPageRow, _WebFormCollection2);
    function WebFormPageRow(row) {
      var _this3;
      babelHelpers.classCallCheck(this, WebFormPageRow);
      _this3 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WebFormPageRow).call(this));
      row.items.forEach(function (item, index) {
        row.items[index] = new WebFormFieldItem(item);
        row.items[index].parent = babelHelpers.assertThisInitialized(_this3);
        _this3.items.push(row.items[index]);
      });
      return _this3;
    }
    return WebFormPageRow;
  }(WebFormCollection);
  var WebFormFieldItem = /*#__PURE__*/function () {
    function WebFormFieldItem(item) {
      babelHelpers.classCallCheck(this, WebFormFieldItem);
      this.id = tmpId++;
      this.size = item.size;
      this.config = item.config;
      this.fieldId = item.fieldId;
      this.component = null;
      this.childLayout = null;
      this.errorMessage = false;
      this._parent = null;
      if (item.config.layout) {
        this.childLayout = new WebFormLayout(this.config.layout.config, this.config.layout.rows);
        this.childLayout.parent = this;
      }
    }
    babelHelpers.createClass(WebFormFieldItem, [{
      key: "parent",
      get: function get() {
        return this._parent;
      },
      set: function set(value) {
        this._parent = value;
      }
    }, {
      key: "root",
      get: function get() {
        var result = this._parent;
        if (!result) return result;
        while (result.parent) {
          result = result.parent;
        }
        return result;
      }
    }, {
      key: "rootPage",
      get: function get() {
        var result = this._parent;
        if (!result) return result;
        while (!(result instanceof WebFormPage) && result.parent) {
          result = result.parent;
        }
        return result;
      }
    }]);
    return WebFormFieldItem;
  }();

  var WebFormStackVariableString = /*#__PURE__*/function (_DevBX$MSLang$StackVa) {
    babelHelpers.inherits(WebFormStackVariableString, _DevBX$MSLang$StackVa);
    function WebFormStackVariableString(isConst, formField) {
      var _this;
      babelHelpers.classCallCheck(this, WebFormStackVariableString);
      _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WebFormStackVariableString).call(this, DevBX.MSLang.VariableType.vtString, isConst));
      _this._formField = formField;
      return _this;
    }
    babelHelpers.createClass(WebFormStackVariableString, [{
      key: "functions",
      get: function get() {
        return DevBX.MSLang.StackVariableString.prototype.functions;
      }
    }, {
      key: "value",
      get: function get() {
        return this._formField.value;
      },
      set: function set(value) {
        if (typeof value !== 'string') throw new Error('variable type ' + babelHelpers["typeof"](value) + ' expected string');
        this._formField.value = value;
      }
    }]);
    return WebFormStackVariableString;
  }(DevBX.MSLang.StackVariable);
  var WebFormStackVariableArray = /*#__PURE__*/function (_DevBX$MSLang$StackVa2) {
    babelHelpers.inherits(WebFormStackVariableArray, _DevBX$MSLang$StackVa2);
    function WebFormStackVariableArray(isConst, formField) {
      var _this2;
      babelHelpers.classCallCheck(this, WebFormStackVariableArray);
      _this2 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WebFormStackVariableArray).call(this, DevBX.MSLang.VariableType.vtArray, isConst));
      _this2._formField = formField;
      return _this2;
    }
    babelHelpers.createClass(WebFormStackVariableArray, [{
      key: "functions",
      get: function get() {
        return DevBX.MSLang.StackVariableArray.prototype.functions;
      }
    }, {
      key: "value",
      get: function get() {
        return this._formField.value;
      },
      set: function set(value) {
        if (!Array.isArray(value)) throw new Error('variable type ' + babelHelpers["typeof"](value) + ' expected array');
        this._formField.value = value;
      }
    }]);
    return WebFormStackVariableArray;
  }(DevBX.MSLang.StackVariable);
  var WebFormStackVariableDateTimeObject = /*#__PURE__*/function (_DevBX$MSLang$StackVa3) {
    babelHelpers.inherits(WebFormStackVariableDateTimeObject, _DevBX$MSLang$StackVa3);
    function WebFormStackVariableDateTimeObject(formField) {
      var _this3;
      babelHelpers.classCallCheck(this, WebFormStackVariableDateTimeObject);
      _this3 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WebFormStackVariableDateTimeObject).call(this, null));
      _this3._formField = formField;
      return _this3;
    }
    babelHelpers.createClass(WebFormStackVariableDateTimeObject, [{
      key: "value",
      get: function get() {
        return this._formField.value;
      }
    }]);
    return WebFormStackVariableDateTimeObject;
  }(DevBX.MSLang.StackVariableDateTimeObject);
  var WebFormStackVariableBoolean = /*#__PURE__*/function (_DevBX$MSLang$StackVa4) {
    babelHelpers.inherits(WebFormStackVariableBoolean, _DevBX$MSLang$StackVa4);
    function WebFormStackVariableBoolean(formField) {
      var _this4;
      babelHelpers.classCallCheck(this, WebFormStackVariableBoolean);
      _this4 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WebFormStackVariableBoolean).call(this, DevBX.MSLang.VariableType.vtBoolean, true));
      _this4._formField = formField;
      return _this4;
    }
    babelHelpers.createClass(WebFormStackVariableBoolean, [{
      key: "value",
      get: function get() {
        return this._formField.value;
      }
    }]);
    return WebFormStackVariableBoolean;
  }(DevBX.MSLang.StackVariable);
  var WebFormStackVariableNumber = /*#__PURE__*/function (_DevBX$MSLang$StackVa5) {
    babelHelpers.inherits(WebFormStackVariableNumber, _DevBX$MSLang$StackVa5);
    function WebFormStackVariableNumber(formField) {
      var _this5;
      babelHelpers.classCallCheck(this, WebFormStackVariableNumber);
      _this5 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WebFormStackVariableNumber).call(this, DevBX.MSLang.VariableType.vtNumber, true));
      _this5._formField = formField;
      return _this5;
    }
    babelHelpers.createClass(WebFormStackVariableNumber, [{
      key: "value",
      get: function get() {
        if (typeof this._formField.value !== 'number') return null;
        return this._formField.value;
      }
    }]);
    return WebFormStackVariableNumber;
  }(DevBX.MSLang.StackVariable);
  var WebFormStackVariableFieldsObject = /*#__PURE__*/function (_DevBX$MSLang$StackVa6) {
    babelHelpers.inherits(WebFormStackVariableFieldsObject, _DevBX$MSLang$StackVa6);
    function WebFormStackVariableFieldsObject(formFields) {
      var _this6;
      babelHelpers.classCallCheck(this, WebFormStackVariableFieldsObject);
      _this6 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WebFormStackVariableFieldsObject).call(this, DevBX.MSLang.VariableType.vtObject, true));
      _this6._formFields = formFields;
      return _this6;
    }
    babelHelpers.createClass(WebFormStackVariableFieldsObject, [{
      key: "getProperty",
      value: function getProperty(name) {
        var formField = undefined;
        this._formFields.every(function (search) {
          if (search.name !== name) return true;
          formField = search;
          return false;
        });
        if (!formField) return undefined;
        switch (formField.type) {
          case 'string':
            return new WebFormStackVariableString(true, formField);
          case 'array':
          case 'files':
            return new WebFormStackVariableArray(true, formField);
          case 'object':
            return new WebFormStackVariableFieldsObject(formField.value);
          case 'datetime':
          case 'date':
          case 'time':
            return new WebFormStackVariableDateTimeObject(formField);
          case 'boolean':
            return new WebFormStackVariableBoolean(formField);
          case 'number':
            return new WebFormStackVariableNumber(formField);
        }
        return undefined;
      }
    }]);
    return WebFormStackVariableFieldsObject;
  }(DevBX.MSLang.StackVariable);

  function createWebFormApp(_data) {
    var app = BX.Vue3.BitrixVue.createApp({
      data: function data() {
        _data.webForm.formFields.forEach(function (item) {
          item.changed = false;
        });
        return {
          admin: _data.admin,
          debug: true,
          sid: _data.sid,
          webForm: new WebForm(_data.webForm.config),
          formClass: !!_data.formClass ? _data.formClass : '',
          formElements: _data.webForm.formElements,
          formFields: _data.webForm.formFields,
          formReadonly: false,
          culture: _data.culture,
          activePageId: false,
          activeId: false,
          visiblePages: [],
          beforeTransitionCheckErrors: false,
          pageChange: false,
          submitAjax: false,
          validation: true,
          mode: 'init',
          errors: []
        };
      },
      mounted: function mounted() {
        //let time = (new Date()).getTime();

        var items = Object.values(this.pages);
        if (items.length) {
          this.visiblePages.push(items[0]);
          this.activePageId = items[0].id;
        }
        this.mode = 'pages';

        /*
        if (this.debug)
        {
            console.log('init time ',(new Date()).getTime()-time);
        }*/
      },

      methods: {
        formatString: function formatString(str, search, replace) {
          if (typeof str === 'undefined') return '';
          if (typeof str !== 'string') str = str.toString();
          if (!Array.isArray(search)) {
            search = [search];
            replace = [replace];
          }
          search.forEach(function (s, i) {
            str = str.replaceAll(s, replace[i]);
          });
          return str;
        },
        getConditionVariables: function getConditionVariables() {
          var result = {};
          result['Fields'] = new WebFormStackVariableFieldsObject(this.formFields);
          return result;
        },
        executeScriptCode: function executeScriptCode(cond) {
          cond.error = false;
          if (!cond.code) return false;
          if (!cond.context) {
            var nodeList = [];
            try {
              var lexer = new DevBX.MSLang.CodeLexer(cond.code);
              var parser = new DevBX.MSLang.CodeParser(lexer);
              parser.parseCode(nodeList, true, true, DevBX.MSLang.LexerTypeArray.one(DevBX.MSLang.LexerType.ltEof));
            } catch (e) {
              cond.error = true;
              cond.message = e.message;
              return false;
            }
            var interpreter = new DevBX.MSLang.Interpreter();
            interpreter.registerHandlers();
            cond.context = new DevBX.MSLang.ContextInterpreter(nodeList, interpreter);
          }
          try {
            cond.context.reset();
            cond.context.registerConst();
            cond.context.setVariable('DateTime', new DevBX.MSLang.StackVariableDateTimeObject());
            var conditionVariables = this.getConditionVariables();
            Object.keys(conditionVariables).forEach(function (key) {
              cond.context.setVariable(key, conditionVariables[key]);
            });
            cond.returnVal = cond.context.exec(true);
            if (!cond.returnVal) return false;
            if (cond.returnVal.value === undefined) {
              cond.error = 'return undefined value';
              return false;
            }
            return true;
          } catch (e) {
            cond.error = 'failed execute code: ' + e.message;
            return false;
          }
        },
        checkCondition: function checkCondition(objCondition, props, fieldName) {
          if (props === undefined) debugger;
          var cond = props[fieldName];
          switch (cond.value) {
            case 'always':
              return true;
            case 'never':
              return false;
            case 'when':
              break;
            default:
              return null;
          }
          if (!this.executeScriptCode(cond)) {
            if (cond.error) {
              console.error('execute condition error, ' + cond.error, objCondition, props, fieldName);
            }
            return null;
          }
          return !!cond.returnVal.value;
        },
        getComponentsByName: function getComponentsByName(name, childItems) {
          var _this = this;
          if (typeof childItems === "undefined") childItems = this.$children;
          var component;
          if (name !== false) {
            component = this.$.appContext.components[name];
            if (!component) {
              console.error('Unknown component: ' + name);
              return;
            }
          }
          var result = [];
          childItems.forEach(function (item) {
            if (name === false || item.$.type === component) {
              result.push(item);
            }
            result.push.apply(result, babelHelpers.toConsumableArray(_this.getComponentsByName(name, item.$children)));
          });
          return result;
        },
        getFormElementById: function getFormElementById(id) {
          var result = false;
          this.formElements.forEach(function (group) {
            group.items.forEach(function (item) {
              if (item.data.fieldId === id) {
                result = item;
                return false;
              }
              return true;
            });
            return result === false;
          });
          return result;
        },
        htmlFormatFields: function htmlFormatFields(html) {
          if (!html) return '';
          var fields = this.formFieldByName,
            self = this;
          html = html.replace(/#([a-zA-Z0-9_\.]+)#/g, function (str, ind) {
            var field = fields[ind];
            if (!field) {
              if (self.debug) {
                return '<b style="color:red;">form field not found ' + BX.util.htmlspecialchars(ind) + '</b>';
              }
            }
            var value = field.value;
            if (value === null || value === undefined) return '';
            switch (field.type) {
              case 'array':
                value = value.join(', ');
                break;
              case 'date':
                value = parseInt(value);
                if (!isNaN(value)) {
                  value = new Date(value * 1000).toLocaleDateString();
                } else value = '';
                break;
              case 'datetime':
                value = parseInt(value);
                if (!isNaN(value)) {
                  value = new Date(value * 1000).toLocaleString();
                } else value = '';
                break;
              case 'time':
                value = parseInt(value);
                if (!isNaN(value)) {
                  var hours = Math.floor(value / (60 * 60)),
                    minutes = Math.floor(value % (60 * 60) / 60);
                  value = hours.toString().padStart(2, '0') + ':' + minutes.toString().padStart(2, '0');
                } else value = '';
                break;
              case 'boolean':
                value = value ? BX.message('DEVBX_WEB_FORM_FIELD_BOOLEAN_VALUE_TRUE') : BX.message('DEVBX_WEB_FORM_FIELD_BOOLEAN_VALUE_FALSE');
                break;
              case 'files':
                var fileNames = [];
                value.forEach(function (file) {
                  fileNames.push(file.name);
                });
                value = fileNames.join(', ');
                break;
            }
            return BX.util.htmlspecialchars(value);
          });
          return html;
        },
        checkPageFields: function checkPageFields(forceCheck) {
          var result = [];
          this.activePageWebFormItems.forEach(function (item) {
            if (!item.component) return;
            if (!forceCheck) {
              var field = item.component.field;
              if (field && !field.changed) return;
            }
            if (typeof item.component.checkErrors === 'function') {
              if (item.component.checkErrors()) {
                result.push(item.component);
              }
            }
          });
          return result;
        },
        showPrevPage: function showPrevPage() {
          this.setPageNumber(this.curPageNumber - 1);
        },
        showNextPage: function showNextPage() {
          this.setPageNumber(this.curPageNumber + 1);
        },
        checkPageErrors: function checkPageErrors(forceCheck, setFocus) {
          var errComponents = this.checkPageFields(forceCheck);
          if (!errComponents.length) return false;
          errComponents.forEach(function (component) {
            if (component.item) ;
          });
          if (setFocus) {
            errComponents[0].$el.scrollIntoView({
              behavior: "smooth"
            });
            if (typeof errComponents[0].setFocus === 'function') errComponents[0].setFocus();
          }
          return true;
        },
        getFieldsByFieldId: function getFieldsByFieldId(systemId, children) {
          var _this2 = this;
          var result = [];
          if (!children) children = this.formFields;
          children.forEach(function (field) {
            if (field.fieldId !== systemId) return;
            if (field.type === 'object') {
              result.push.apply(result, babelHelpers.toConsumableArray(_this2.getFieldsByFieldId(systemId, field.value)));
            } else {
              result.push(field);
            }
          });
          return result;
        },
        checkPageObjectErrors: function checkPageObjectErrors(page) {
          var _this3 = this;
          var hasErrors = false;
          page.getAllFormFields().every(function (obField) {
            if (obField.config.showRule && _this3.checkCondition(obField, obField.config, 'showRule') === false) return true;
            if (obField.config.customError && _this3.checkCondition(obField, obField.config, 'showCustomError') === true) {
              hasErrors = true;
              return false;
            }
            if (obField.config.requireRule && _this3.checkCondition(obField, obField.config, 'requireRule') === true) {
              var fields = _this3.getFieldsByFieldId(obField.config.systemId);
              fields.forEach(function (field) {
                if (field.virtual) return;
                if (typeof field.value === "number") return; //ok

                if (typeof field.value === "boolean" && field.value) return; //ok

                if (field.value === "undefined" || field.value === null || !field.value.length) hasErrors = true; //not filled
              });
            }
          });

          return hasErrors;
        },
        setPageNumber: function setPageNumber(pageNum) {
          if (this.pageChange) return;
          if (pageNum < 0 || pageNum >= Object.values(this.pages).length) {
            console.error('invalid page number ' + pageNum);
            return;
          }
          var curPageNum = this.curPageNumber;
          if (curPageNum === pageNum) return;
          if (pageNum < curPageNum) {
            this.activePageId = Object.values(this.pages)[pageNum].id;
            this.visiblePages.splice(0, 0, Object.values(this.pages)[pageNum]);
            var self = this;
            setTimeout(function () {
              self.visiblePages.splice(1, 1);
            }, 0);
            return;
          }
          if (this.checkPageErrors(true, true)) return;
          this.beforeTransitionCheckErrors = false;
          if (pageNum - curPageNum > 1) {
            var hasErrors = false;
            while (curPageNum < pageNum) {
              curPageNum++;
              var page = Object.values(this.pages)[curPageNum];
              hasErrors = this.checkPageObjectErrors(page);
              if (hasErrors) {
                this.beforeTransitionCheckErrors = true;
                break;
              }
              curPageNum++;
            }
            if (hasErrors) pageNum = curPageNum;
          }
          this.activePageId = Object.values(this.pages)[pageNum].id;
          this.visiblePages.push(Object.values(this.pages)[pageNum]);
          this.visiblePages.splice(0, 1);
        },
        getObjectFieldTree: function getObjectFieldTree(varName, fields) {
          var _this4 = this;
          var result = {},
            prefix = varName.length ? varName + '.' : '';
          fields.forEach(function (field) {
            if (field.type === 'object') {
              Object.assign(result, _this4.getObjectFieldTree(prefix + field.name, field.value));
            } else {
              result[prefix + field.name] = field;
            }
          });
          return result;
        },
        showFormError: function showFormError(message) {
          //conso
        }
      },
      computed: {
        pages: function pages() {
          var _this5 = this;
          var result = {};
          this.webForm.pages.items.forEach(function (page, index) {
            if (_this5.checkCondition(page, page.config, 'showPage') !== false) {
              result[page.id] = page;
            }
          });
          return result;
        },
        curPageNumber: function curPageNumber() {
          return Object.values(this.pages).indexOf(this.activePage);
        },
        activePage: function activePage() {
          return this.pages[this.activePageId];
        },
        formFieldByName: function formFieldByName() {
          return this.getObjectFieldTree('Fields', this.formFields);
        },
        isPopup: function isPopup() {
          return this.formClass.indexOf('popup') > 0;
        },
        activePageWebFormItems: function activePageWebFormItems() {
          var result = [],
            page = this.activePage;
          if (!page) return result;
          return page.getAllFormFields();
        },
        webFormComponent: function webFormComponent() {
          return 'devbx-webform-mode-' + this.mode;
        }
      },
      watch: {
        validation: function validation(val) {
          this.checkPageFields(true);
        }
      },
      template: "\n        <div class=\"devbx-webform-clean-css\">\n            <component :is=\"webFormComponent\"></component>\n            \n            <devbx-webform-popup-window v-if=\"errors.length\" @close=\"errors = [];\">\n                <div class=\"devbx-webform-error\" v-for=\"error in errors\">\n                    <span v-if=\"error.message\">{{error.message}}</span>\n                    <span v-else>{{error}}</span>                                \n                </div>                            \n            </devbx-webform-popup-window>\n        </div>\n        "
    });
    BX.onCustomEvent('DevBxWebFormCreated', [app]);
    return app;
  }

  BX.addCustomEvent("DevBxWebFormCreated", function (app) {
    app.component('devbx-webform-mode-init', {
      template: "\n        <div style=\"display: none\">\n            <devbx-webform-page\n                v-for=\"page in $root.webForm.pages.items\" \n                :key=\"page.id\"\n                :page=\"page\" \n                :is-active=\"false\"\n                :disable-actions=\"true\"\n            ></devbx-webform-page>            \n        </div>\n        "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreated", function (app) {
    app.component('devbx-webform-mode-pages', {
      data: function data() {
        return {};
      },
      computed: {
        webFormItemsBySid: function webFormItemsBySid() {
          var result = {};
          this.$root.webForm.pages.getAllFormFields().forEach(function (item) {
            if (item.config && item.config.systemId) result[item.config.systemId] = item;
          });
          return result;
        }
      },
      methods: {
        beforeEnter: function beforeEnter(el) {
          this.$root.pageChange = true;
          if (this.$root.beforeTransitionCheckErrors) {
            this.$root.checkPageErrors(false, false);
          }
          el.style.width = this.$refs.content.clientWidth + 'px';
          var height = this.calcChildNodesHeight(this.$refs.pagesTransition.$el.querySelector('.devbx-webform-page').childNodes);
          this.$refs.pagesTransition.$el.style.minHeight = height + 'px';
        },
        calcChildNodesHeight: function calcChildNodesHeight(childNodes) {
          var height = 0;
          childNodes.forEach(function (node) {
            if (node instanceof Element) height += node.getBoundingClientRect().height;
          });
          return height;
        },
        enter: function enter(el) {
          var r1 = this.$el.getBoundingClientRect(),
            r2 = el.getBoundingClientRect();

          //let setHeight = getComputedStyle(el.closest('.devbx-webform-content')).display === 'flex';
          var setHeight = !this.isPopup;
          if (r1.y < 0) {
            this.$el.scrollIntoView({
              behavior: "smooth"
            });
          }
          var height = this.calcChildNodesHeight(this.$refs.pagesTransition.$el.querySelector('.devbx-webform-page-transition-enter-active').childNodes);
          this.$refs.pagesTransition.$el.style.minHeight = height + 'px';

          //el.parentNode.style.overflow = 'hidden';

          /*
          let height = 0;
            this.$refs.pagesTransition.$el.childNodes.forEach(node => {
                if (node instanceof Element)
              {
                  height = Math.max(height, this.calcChildNodesHeight(node.childNodes));
              }
          });
            el.parentNode.style.position = 'relative';
          el.parentNode.style.overflow = 'hidden';
          el.parentNode.style.display = 'block';
            if (!setHeight)
              el.parentNode.style.height = height + 'px';
           */
        },
        afterEnter: function afterEnter(el) {
          el.style.width = '';
          el.parentNode.removeAttribute('style');
          this.$refs.pagesTransition.$el.childNodes.forEach(function (node) {
            if (node instanceof Element) {
              node.removeAttribute('style');
            }
          });
          this.$root.pageChange = false;
          if (this.$root.beforeTransitionCheckErrors) {
            this.$root.beforeTransitionCheckErrors = false;
            this.$root.checkPageErrors(true, true);
          } else {
            this.$root.activePageWebFormItems.every(function (item) {
              if (item.component) {
                if (typeof item.component.setFocus === 'function') {
                  item.component.setFocus();
                  return false;
                }
              }
              return true;
            });
          }
        },
        submitForm: function submitForm(action) {
          if (this.$root.submitAjax) return;
          var tree = this.$root.getObjectFieldTree('', this.$root.formFields);
          var webFormData = {};
          Object.keys(tree).forEach(function (fieldName) {
            var field = tree[fieldName],
              value = field.value;
            if (field.type === 'files') {
              var newValue = [];
              value.forEach(function (fileInfo) {
                newValue.push(fileInfo.fileId);
              });
              value = newValue;
            }
            webFormData[fieldName] = value;
          });
          this.$root.submitAjax = true;
          BX.ajax.runAction('devbx:forms.api.webform.submitForm', {
            data: {
              lang: BX.message('LANGUAGE_ID'),
              sid: this.$root.sid,
              fields: JSON.stringify(webFormData)
            }
          }).then(BX.delegate(this.submitFormSuccess, this), BX.delegate(this.submitFormError, this));
        },
        submitFormSuccess: function submitFormSuccess(response) {
          this.$root.submitAjax = false;
          this.$root.mode = 'finish-page';
        },
        submitFormError: function submitFormError(response) {
          var _this = this;
          this.$root.submitAjax = false;
          var firstErrItem = false;
          response.errors.forEach(function (error) {
            if (error.customData && error.customData.fieldId) {
              var obField = _this.webFormItemsBySid[error.customData.fieldId];
              if (obField) {
                if (!firstErrItem) firstErrItem = obField;
                obField.errorMessage = error.message;
                return;
              }
            }
            _this.$root.errors.push(error);
          });
          if (firstErrItem) {
            if (firstErrItem.rootPage) {
              var pageNum = Object.values(this.$root.pages).indexOf(firstErrItem.rootPage);
              if (pageNum >= 0) {
                this.$root.setPageNumber(pageNum);
              }
            }
          } else {
            if (!this.$root.errors.length) {
              //TODO заменить на сообщений о неизвестной ошибке
              this.$root.errors.push('UNKNOWN ERROR');
            }
          }
        }
      },
      template: "\n        <form class=\"devbx-webform-theme devbx-webform\"\n            :class=\"$root.formClass\" \n            @submit.stop.prevent>\n            <div class=\"devbx-webform-container\">\n                <div ref=\"content\" class=\"devbx-webform-content\">\n                    <devbx-webform-header :form-settings=\"$root.webForm.settings\" :pages=\"$root.pages\" :active-page=\"$root.activePage\"></devbx-webform-header>\n                    <div class=\"devbx-webform-body\">\n                    <transition-group name=\"devbx-webform-page-transition\" tag=\"div\" \n                        class=\"devbx-webform-pages-container\"\n                        :class=\"{'devbx-webform-pages-transition': $root.pageChange}\"\n                        ref=\"pagesTransition\"\n                          @before-enter=\"beforeEnter\"\n                          @enter=\"enter\"\n                          @after-enter=\"afterEnter\"\n                    >\n                        <devbx-webform-page\n                            v-for=\"page in $root.visiblePages\" \n                            :key=\"page.id\"\n                            :page=\"page\" \n                            :is-active=\"$root.activePageId == page.id\"\n                            :disable-actions=\"$root.submitAjax\"\n                            @submit-form=\"submitForm()\"\n                            >\n                        </devbx-webform-page>\n                    </transition-group>\n                    </div>\n                </div>\n            </div>\n        </form>\n        "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreated", function (app) {
    app.component('devbx-webform-mode-finish-page', {
      computed: {
        finishPage: function finishPage() {
          var _this = this;
          var page = false;
          this.$root.webForm.finishPageCond.every(function (pageCond) {
            if (_this.$root.checkCondition(pageCond, pageCond, 'showRule') === true) {
              page = pageCond;
            }
            return page === false;
          });
          if (!page) page = this.$root.webForm.finishPage;
          return page;
        }
      },
      template: "\n        <div class=\"devbx-webform-theme devbx-webform devbx-webform-finish-page\"\n            :class=\"$root.formClass\">\n            <div class=\"devbx-webform-container\">\n                <div class=\"devbx-webform-content\">\n                    <div class=\"devbx-webform-finish-page-content\" v-html=\"finishPage.content\">\n                    </div>\n                </div>\n            </div>\n        </div>\n        "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreated", function (app) {
    app.component('devbx-webform-header', {
      props: ['formSettings', 'pages', 'activePage'],
      computed: {
        titleFormatted: function titleFormatted() {
          return this.$root.htmlFormatFields(BX.util.htmlspecialchars(this.formSettings.title));
        },
        descriptionFormatted: function descriptionFormatted() {
          return this.$root.htmlFormatFields(this.formSettings.description);
        },
        pageDescriptionFormatted: function pageDescriptionFormatted() {
          if (this.activePage) return this.$root.htmlFormatFields(this.activePage.config.pageDescription);
          return '';
        },
        progressPercent: function progressPercent() {
          var idx = Object.values(this.pages).indexOf(this.activePage);
          if (!this.activePage || idx < 0) return 0;
          if (idx === this.pageItems.length - 1) return 100;
          return 100 / this.pageItems.length * (idx + 1);
        },
        pageItems: function pageItems() {
          var _this = this;
          var result = [],
            passed = true;
          Object.values(this.pages).forEach(function (page, index) {
            var title;
            if (page.config.pageTitle) {
              title = page.config.pageTitle;
            } else {
              title = _this.$root.formatString(_this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_NAVIGATION_PAGE_TITLE'), '#NUM#', index + 1);
            }
            if (page.id == _this.activePage.id) passed = false;
            result.push({
              title: title,
              page: page,
              passed: passed
            });
          });
          return result;
        }
      },
      template: "\n    <div class=\"devbx-webform-header\">\n        <h1 v-if=\"!formSettings.titleHidden\" v-html=\"titleFormatted\"></h1>\n        <div class=\"devbx-webform-description\" v-if=\"descriptionFormatted.length\" v-html=\"descriptionFormatted\"></div>\n        \n            <div class=\"devbx-webform-page-steps\"\n                :class=\"{'devbx-webform-page-steps-no-title': !formSettings.showPageTitles}\" \n                v-if=\"pageItems.length>1 && formSettings.progressBar == 'STEPS'\">\n                <ul>\n                    <li v-for=\"(item, index) in pageItems\" :class=\"{'devbx-webform-page-step-active': item.page.id == activePage.id}\">\n                        <a href @click.stop.prevent=\"$root.setPageNumber(index)\"><span v-if=\"formSettings.showPageTitles\">{{item.title}}</span></a>\n                    </li>\n                </ul>\n            </div>\n        \n            <div class=\"devbx-webform-page-progress-bar\" v-if=\"pageItems.length>1 && formSettings.progressBar == 'BAR'\">\n                <ul>\n                    <li class=\"devbx-webform-progress-bar-line\"></li>\n                    <li class=\"devbx-webform-progress-bar-pos\" :style=\"{'width': progressPercent+'%'}\"></li>\n                    <li v-for=\"(item, index) in pageItems\" :style=\"{'width': (100/pageItems.length)+'%'}\" :class=\"{\n                    'devbx-webform-page-step-active': item.page.id == activePage.id && formSettings.showPageTitles,\n                    'devbx-webform-page-step-passed': item.passed || (item.page.id == activePage.id && !formSettings.showPageTitles),\n                    }\">\n                        <a href @click.stop.prevent=\"$root.setPageNumber(index)\"><span v-if=\"formSettings.showPageTitles\">{{item.title}}</span></a>\n                    </li>\n                </ul>\n            </div>\n            \n            <div class=\"devbx-webform-description\" v-if=\"pageDescriptionFormatted.length\" v-html=\"pageDescriptionFormatted\"></div>\n        \n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreated", function (app) {
    app.component('devbx-webform-page', {
      props: ['page', 'isActive', 'disableActions'],
      data: function data() {
        return {
          rows: []
        };
      },
      computed: {
        isLoading: function isLoading() {
          var loading = false;
          this.page.getAllFormFields().forEach(function (item) {
            if (item.component && item.component.isLoading) loading = true;
          });
          return loading;
        }
      },
      directives: {
        row: {
          inserted: function inserted(el, binding, vnode) {
            vnode.componentInstance.$parent.rows.push(vnode.componentInstance);
          },
          unbind: function unbind(el, binding, vnode) {
            var idx = vnode.componentInstance.$parent.rows.indexOf(vnode.componentInstance);
            if (idx >= 0) vnode.componentInstance.$parent.rows.splice(idx, 1);
          }
        }
      },
      template: "\n            <div class=\"devbx-webform-page\" :class=\"{'devbx-webform-page-active': isActive}\">\n                    <devbx-webform-form-row ref=\"rows\" v-row v-for=\"(row, index) in page.items\" :key=\"row.id\" v-bind:row=\"row\"/>\n                    <devbx-webform-form-actions :page=\"page\" :form-actions=\"$root.webForm.actions\" :disable-actions=\"disableActions\"\n                    @submit-form=\"$emit('submit-form')\"\n                    ></devbx-webform-form-actions>\n                    <div v-if=\"isLoading\" class=\"devbx-webform-page-loading\">\n                        <div class=\"devbx-webform-page-lds-roller\"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>                    \n                    </div>\n            </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreated", function (app) {
    app.component('devbx-webform-form-actions', {
      props: {
        formActions: {
          required: true
        },
        page: {
          required: true
        },
        disableActions: {
          "default": false
        }
      },
      emits: ['submit-form'],
      computed: {
        showPrevButton: function showPrevButton() {
          var pages = Object.values(this.$root.pages);
          return pages.indexOf(this.page) > 0;
        },
        hasNextButton: function hasNextButton() {
          var pages = Object.values(this.$root.pages);
          return pages.indexOf(this.page) < pages.length - 1;
        },
        showNextButton: function showNextButton() {
          return this.hasNextButton && this.$root.checkCondition(this.page, this.page.config, 'showNextButton') !== false;
        },
        submitActions: function submitActions() {
          var result = [];
          if (this.hasNextButton) return result;
          this.formActions.forEach(function (item) {
            if (item.action === 'SUBMIT') {
              result.push(item);
            }
          });
          return result;
        }
      },
      template: "\n    <div class=\"devbx-webform-navigation\">\n        <button class=\"devbx-webform-button\"\n            :class=\"{'devbx-webform-button-disabled': disableActions}\" \n            v-if=\"showPrevButton\" @click.stop.prevent=\"$root.showPrevPage\">\n            <span>{{page.config.prevButtonText}}</span>        \n        </button>    \n\n        <button class=\"devbx-webform-button devbx-webform-button-primary\" \n            :class=\"{'devbx-webform-button-disabled': disableActions}\" \n            v-if=\"showNextButton\" @click.stop.prevent=\"$root.showNextPage\">\n            <span>{{page.config.nextButtonText}}</span>        \n        </button>\n\n        <button v-for=\"(action, index) in submitActions\" :key=\"index\" \n            class=\"devbx-webform-button devbx-webform-button-primary\" \n            :class=\"{'devbx-webform-button-disabled': disableActions}\" \n           @click.stop.prevent=\"$emit('submit-form', action)\">\n            <span v-if=\"!$root.submitAjax\">{{action.title}}</span>        \n            <span v-else class=\"devbx-webform-button-svg-icon devbx-webform-animation-rotate\">\n                    <svg viewBox=\"0 0 118.04 122.88\">\n                        <path d=\"M16.08,59.26A8,8,0,0,1,0,59.26a59,59,0,0,1,97.13-45V8a8,8,0,1,1,16.08,0V33.35a8,8,0,0,1-8,8L80.82,43.62a8,8,0,1,1-1.44-15.95l8-.73A43,43,0,0,0,16.08,59.26Zm22.77,19.6a8,8,0,0,1,1.44,16l-10.08.91A42.95,42.95,0,0,0,102,63.86a8,8,0,0,1,16.08,0A59,59,0,0,1,22.3,110v4.18a8,8,0,0,1-16.08,0V89.14h0a8,8,0,0,1,7.29-8l25.31-2.3Z\"/>\n                    </svg>            \n            </span>\n        </button>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreated", function (app) {
    app.component('devbx-webform-form-row', {
      props: ['row'],
      data: function data() {
        return {
          hoverId: false
        };
      },
      methods: {
        elementClick: function elementClick(itemId) {
          this.$root.activeId = itemId;
        },
        mouseEnter: function mouseEnter(item) {
          this.hoverId = item.id;
        },
        mouseLeave: function mouseLeave(item) {
          if (this.hoverId == item.id) {
            this.hoverId = false;
          }
        },
        beforeEnter: function beforeEnter(el) {
          el.style.opacity = 0;
          el.style.height = 0;
        },
        afterEnter: function afterEnter(el) {
          el.style.height = '';
          el.style.opacity = '';
        },
        enter: function enter(el, done) {
          el.style.width = getComputedStyle(el).width;
          el.style.position = 'absolute';
          el.style.visibility = 'hidden';
          el.style.height = 'auto';
          var height = getComputedStyle(el).height;
          el.style.width = null;
          el.style.position = null;
          el.style.visibility = null;
          el.style.height = 0;
          setTimeout(function () {
            Velocity(el, {
              opacity: 1,
              height: height
            }, {
              complete: done
            });
          }, 100);
        },
        leave: function leave(el, done) {
          setTimeout(function () {
            Velocity(el, {
              opacity: 0,
              height: 0
            }, {
              complete: done
            });
          }, 100);
        }
      },
      computed: {
        items: function items() {
          var _this = this;
          var result = [];
          this.row.items.forEach(function (item) {
            var formElement = _this.$root.getFormElementById(item.fieldId);
            if (!formElement) {
              if (_this.$root.debug) {
                console.error('form element not found ' + item.fieldId);
                return;
              }
            }
            var visible = true;
            if (item.config.showRule) {
              visible = !(_this.$root.checkCondition(item, item.config, 'showRule') === false);
            }
            if (parseInt(item.config.systemId) <= 0) {
              console.error('item.config.systemId', item.config.systemId);
            }
            if (!visible) return;
            var data = {
              id: item.config.systemId,
              template: formElement.data.layoutTemplate,
              data: item,
              cssClass: 'devbx-webform--col devbx-webform--col--' + item.size,
              visible: visible
            };
            result.push(data);
          });
          return result;
        },
        activeId: function activeId() {
          return this.$root.activeId;
        }
      },
      template: "\n      <transition-group name=\"devbx-webform-field\" tag=\"div\" class=\"devbx-webform-row devbx-webform-transition\"\n      v-bind:css=\"false\"\n      @before-enter=\"beforeEnter\"\n      @after-enter=\"afterEnter\"\n      @enter=\"enter\"\n      @leave=\"leave\">\n          <component\n              v-for=\"(item,index) of items\" \n              :key=\"item.id\" \n              :class=\"item.cssClass\"\n              @click=\"elementClick(item.id)\"\n              @mouseenter=\"mouseEnter(item)\"\n              @mouseleave=\"mouseLeave(item)\"                             \n              :is=\"item.template\"\n              :item=\"item.data\" \n              :config=\"item.data.config\" \n              :row=\"row\" \n              :active=\"activeId == item.id\"\n              />\n      </transition-group>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreated", function (app) {
    app.component('devbx-webform-popup-window', {
      props: {
        contentCenter: {
          type: Boolean,
          "default": true
        }
      },
      data: function data() {
        return {
          elOverlay: false,
          eventKeyup: false
        };
      },
      mounted: function mounted() {
        this.elOverlay = document.createElement('div');
        this.elOverlay.classList.add('devbx-webform-popup-overlay');
        this.elOverlay.setAttribute('style', 'z-index: 1050 !important');
        this.elOverlay.addEventListener('click', BX.delegate(this.overlayClick, this));
        document.body.appendChild(this.elOverlay);
        document.body.appendChild(this.$el);
        this.eventKeyup = BX.delegate(this.keyUp, this);
        document.addEventListener('keyup', this.eventKeyup);
      },
      beforeUnmount: function beforeUnmount() {
        this.elOverlay.remove();
        document.removeEventListener('keyup', this.eventKeyup);
      },
      methods: {
        overlayClick: function overlayClick() {
          this.$emit('close');
        },
        popupWindowClick: function popupWindowClick(event) {
          if (event.target == this.$el) this.$emit('close');
        },
        keyUp: function keyUp(event) {
          if (event.keyCode == 27) this.$emit('close');
        }
      },
      template: "\n        <div class=\"devbx-webform-theme devbx-webform-popup-window\" style=\"z-index: 1055 !important;\" @click=\"popupWindowClick($event)\">\n            <div class=\"devbx-webform-popup-container\">\n                <div class=\"devbx-webform-popup-close\" @click.stop.prevent=\"$emit('close')\"></div>\n                <div class=\"devbx-webform-popup-content\" :class=\"{'devbx-webform-popup-content-center': contentCenter}\">\n                    <slot></slot>\n                </div>\n                <div class=\"devbx-webform-popup-buttons\">\n                    <div class=\"devbx-webform-popup-button devbx-webform-popup-button-primary\" @click.stop.prevent=\"$emit('close')\">\n                        <span>Close</span>\n                    </div>                \n                </div>\n            </div>\n        </div>\n        "
    });
  });

  exports.createWebFormApp = createWebFormApp;

}((this.DevBX.Forms.WebForm = this.DevBX.Forms.WebForm || {})));
//# sourceMappingURL=webform.public.js.map

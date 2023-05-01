this.DevBX = this.DevBX || {};
(function (exports) {
    'use strict';

    function bindUserSelect(node, lang, adminSection) {
      if (!(node instanceof HTMLElement)) {
        node = document.querySelector('[data-devbx-usertype-user-id="' + node + '"]');
        if (node) {
          node.removeAttribute('data-devbx-usertype-user-id');
        }
      }
      console.assert(node, 'node');
      if (!node) return;
      var params = {
        prevValue: false,
        form: node.closest('form'),
        inp: node.querySelector('[data-entity="input"]'),
        view: node.querySelector('[data-entity="view"]'),
        button: node.querySelector('[data-entity="button"]'),
        lang: lang,
        admin: adminSection === true
      };
      console.assert(params.inp, 'params.inp');
      console.assert(params.view, 'params.view');
      console.assert(params.button, 'params.button');
      if (!params.inp || !params.view || !params.button) return;
      BX.bind(params.inp, 'change', BX.delegate(function () {
        updateUserView(this);
      }, params));
      BX.bind(params.button, 'click', BX.delegate(function () {
        showUserSelectModal(this);
      }, params));
      updateUserView(params);
    }
    function updateUserView(params) {
      if (params.prevValue === params.inp.value) return;
      params.prevValue = params.inp.value;
      if (params.prevValue == '') {
        params.view.innerHTML = '';
        return;
      }
      var id;
      while (true) {
        id = Math.floor(Math.random() * 10000000);
        if (!document.getElementById('div_' + id)) break;
      }
      params.view.innerHTML = '<span id="div_' + id + '"><i>' + BX.message('DEVBX_WAIT') + '</i></span>' + '<iframe style="width:0px;height:0px;border:0px;" src="javascript:\'\'" name="hiddenframe"></iframe>';
      params.view.querySelector('iframe').src = 'get_user.php?ID=' + params.prevValue + '&strName=' + id + '&lang=' + params.lang + (params.admin ? '&admin_section=Y' : '');
    }
    function showUserSelectModal(params) {
      w.open('/bitrix/admin/user_search.php?lang="' + params.lang + '"&FN=' + params.form.name + '&FC=' + params.inp.name, '', 'scrollbars=yes,resizable=yes,width=760,height=500,top=' + Math.floor((screen.height - 560) / 2 - 14) + ',left=' + Math.floor((screen.width - 760) / 2 - 5));
    }
    function popupHint(element, hint) {
      var popup = new BX.Main.Popup({
        autoHide: false,
        offsetLeft: -20,
        offsetTop: 11,
        angle: {
          offset: 50
        }
      });
      var elHint = document.createElement('IMG');
      elHint.src = '/bitrix/js/main/core/images/hint.gif';
      elHint.style.marginLeft = '5px';
      element.parentNode.insertBefore(elHint, element);
      element.remove();
      popup.setBindElement(elHint);
      popup.setContent(hint);
      BX.bind(elHint, 'mouseenter', function () {
        popup.show();
      });
      BX.bind(elHint, 'mouseleave', function () {
        popup.close();
      });
    }
    var OptionItems = /*#__PURE__*/function (_Array) {
      babelHelpers.inherits(OptionItems, _Array);
      function OptionItems() {
        babelHelpers.classCallCheck(this, OptionItems);
        return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(OptionItems).apply(this, arguments));
      }
      babelHelpers.createClass(OptionItems, [{
        key: "setVisible",
        value: function setVisible(value) {
          this.forEach(function (item) {
            item.setVisible(value);
          });
        }
      }, {
        key: "setVisibleExclude",
        value: function setVisibleExclude(excludeItems, value) {
          value = value !== false;
          this.forEach(function (item) {
            if (excludeItems.indexOf(item) > -1) {
              item.setVisible(!value);
            } else {
              item.setVisible(value);
            }
          });
        }
      }, {
        key: "getByGroup",
        value: function getByGroup(value) {
          var result = new this.constructor();
          if (!Array.isArray(value)) value = [value];
          this.forEach(function (item) {
            if (value.indexOf(item.params.GROUP) !== -1) {
              result.push(item);
            }
          });
          return result;
        }
      }, {
        key: "getById",
        value: function getById(value) {
          var result = new this.constructor();
          if (!Array.isArray(value)) value = [value];
          this.forEach(function (item) {
            if (value.indexOf(item.params.ID) !== -1) {
              result.push(item);
            }
          });
          return result;
        }
      }]);
      return OptionItems;
    }( /*#__PURE__*/babelHelpers.wrapNativeSuper(Array));
    var Options = /*#__PURE__*/function () {
      function Options(params) {
        babelHelpers.classCallCheck(this, Options);
        this.params = params;
        this.interpreter = new DevBX.MSLang.Interpreter();
        this.interpreter.registerHandlers();
        BX.ready(BX.proxy(this.ready, this));
      }
      babelHelpers.createClass(Options, [{
        key: "ready",
        value: function ready() {
          var _this = this;
          this.valueType = this.params.VALUE_TYPE;
          this.items = new OptionItems();
          var node = document.getElementById(this.params.CONTAINER_ID);
          if (node) {
            this.container = node.closest('table');
          } else {
            this.container = document;
          }
          this.params.OPTIONS.forEach(function (option) {
            option = new (eval(_this.valueType[option.TYPE]))(_this, option);
            _this.items.push(option);
          });
          this.checkVisible();
          BX.onCustomEvent('DevBx.Core.Options:init', [this]);
        }
      }, {
        key: "checkVisible",
        value: function checkVisible() {
          var _this2 = this;
          this.items.forEach(function (item) {
            if (item.scripts.VISIBLE) {
              try {
                var context = new OptionsContextInterpreter(item.scripts.VISIBLE, _this2.interpreter);
                context.options = _this2;
                context.registerConst();
                var returnVal = context.exec(true);
                if (returnVal.type !== DevBX.MSLang.VariableType.vtBoolean) return;
                if (returnVal.value) {
                  item.node.style.display = '';
                } else {
                  item.node.style.display = 'none';
                }
              } catch (e) {
                console.log(e.message);
              }
            }
          });
        }
      }, {
        key: "onChangeValue",
        value: function onChangeValue(objOption) {
          BX.onCustomEvent(this, 'DevBx.Core.Options:onChangeValue', [objOption]);
          this.checkVisible();
        }
      }]);
      return Options;
    }();
    var OptionValueString = /*#__PURE__*/function (_DevBX$MSLang$StackVa) {
      babelHelpers.inherits(OptionValueString, _DevBX$MSLang$StackVa);
      function OptionValueString(option) {
        var _this3;
        babelHelpers.classCallCheck(this, OptionValueString);
        _this3 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(OptionValueString).call(this, DevBX.MSLang.VariableType.vtString, false));
        _this3._option = option;
        return _this3;
      }
      babelHelpers.createClass(OptionValueString, [{
        key: "value",
        get: function get() {
          return this._option.getValue();
        },
        set: function set(value) {
          console.log(this._option, value);
          this._option.setValue(value);
        }
      }]);
      return OptionValueString;
    }(DevBX.MSLang.StackVariable);
    var OptionObject = /*#__PURE__*/function (_DevBX$MSLang$StackVa2) {
      babelHelpers.inherits(OptionObject, _DevBX$MSLang$StackVa2);
      function OptionObject(option) {
        var _this4;
        babelHelpers.classCallCheck(this, OptionObject);
        _this4 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(OptionObject).call(this, DevBX.MSLang.VariableType.vtObject, true));
        _this4._option = option;
        return _this4;
      }
      babelHelpers.createClass(OptionObject, [{
        key: "getProperty",
        value: function getProperty(name) {
          switch (name) {
            case 'value':
              var value = this._option.getValue();
              if (typeof value === 'string') {
                return new OptionValueString(this._option);
              }
              break;
          }
          return undefined;
        }
      }]);
      return OptionObject;
    }(DevBX.MSLang.StackVariable);
    var OptionsContextInterpreter = /*#__PURE__*/function (_DevBX$MSLang$Context) {
      babelHelpers.inherits(OptionsContextInterpreter, _DevBX$MSLang$Context);
      function OptionsContextInterpreter() {
        babelHelpers.classCallCheck(this, OptionsContextInterpreter);
        return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(OptionsContextInterpreter).apply(this, arguments));
      }
      babelHelpers.createClass(OptionsContextInterpreter, [{
        key: "getVariable",
        value: function getVariable(name) {
          var option = DevBX.Utils.filterObjectsArray({
            entityName: name
          }, this.options.items, true);
          if (option) return new OptionObject(option);
          return babelHelpers.get(babelHelpers.getPrototypeOf(OptionsContextInterpreter.prototype), "getVariable", this).call(this, e);
        }
      }]);
      return OptionsContextInterpreter;
    }(DevBX.MSLang.ContextInterpreter);

    exports.bindUserSelect = bindUserSelect;
    exports.updateUserView = updateUserView;
    exports.showUserSelectModal = showUserSelectModal;
    exports.popupHint = popupHint;
    exports.OptionItems = OptionItems;
    exports.Options = Options;
    exports.OptionsContextInterpreter = OptionsContextInterpreter;

}((this.DevBX.Admin = this.DevBX.Admin || {})));



this.DevBX = this.DevBX || {};
this.DevBX.Admin = this.DevBX.Admin || {};
(function (exports) {
    'use strict';

    var BaseType = /*#__PURE__*/function () {
      function BaseType(options, params) {
        babelHelpers.classCallCheck(this, BaseType);
        this.options = options;
        this.params = params;
        this.node = this.options.container.querySelector('[data-entity="option"][data-id="' + params.ID + '"]');
        this.entityName = DevBX.Utils.normalizeEntityName(params.ID);
        this.scripts = {};
        if (params.VISIBLE_CONDITION) {
          var lexer = new DevBX.MSLang.CodeLexer('return ' + params.VISIBLE_CONDITION + ';');
          var parser = new DevBX.MSLang.CodeParser(lexer);
          var nodeList = [];
          try {
            parser.parseCode(nodeList, true, false, DevBX.MSLang.LexerTypeArray.one(DevBX.MSLang.LexerType.ltEof));
            this.scripts.VISIBLE = nodeList;
          } catch (e) {
            console.log('compile failed: ' + e.message);
          }
        }
        return this;
      }
      babelHelpers.createClass(BaseType, [{
        key: "setVisible",
        value: function setVisible(value) {
          this.node.style.display = value ? '' : 'none';
        }
      }, {
        key: "onChange",
        value: function onChange() {
          this.options.onChangeValue(this);
        }
      }]);
      return BaseType;
    }();
    var CheckBoxType = /*#__PURE__*/function (_BaseType) {
      babelHelpers.inherits(CheckBoxType, _BaseType);
      function CheckBoxType(options, params) {
        var _this;
        babelHelpers.classCallCheck(this, CheckBoxType);
        _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CheckBoxType).call(this, options, params));
        _this.entity = _this.node.querySelector('input[type="checkbox"]');
        BX.bind(_this.entity, 'change', BX.proxy(_this.onChange, babelHelpers.assertThisInitialized(_this)));
        return _this;
      }
      babelHelpers.createClass(CheckBoxType, [{
        key: "getValue",
        value: function getValue() {
          return this.entity.checked ? 'Y' : 'N';
        }
      }, {
        key: "setValue",
        value: function setValue(value) {
          this.node.querySelector('input[type="checkbox"]').checked = value === 'Y';
        }
      }]);
      return CheckBoxType;
    }(BaseType);
    var StringType = /*#__PURE__*/function (_BaseType2) {
      babelHelpers.inherits(StringType, _BaseType2);
      function StringType(options, params) {
        var _this2;
        babelHelpers.classCallCheck(this, StringType);
        _this2 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(StringType).call(this, options, params));
        _this2.entity = _this2.node.querySelector('input');
        BX.bind(_this2.entity, 'change', BX.proxy(_this2.onChange, babelHelpers.assertThisInitialized(_this2)));
        return _this2;
      }
      babelHelpers.createClass(StringType, [{
        key: "getValue",
        value: function getValue() {
          return this.entity.value;
        }
      }, {
        key: "setValue",
        value: function setValue(value) {
          this.entity.value = value;
        }
      }]);
      return StringType;
    }(BaseType);
    var ListType = /*#__PURE__*/function (_BaseType3) {
      babelHelpers.inherits(ListType, _BaseType3);
      function ListType(options, params) {
        var _this3;
        babelHelpers.classCallCheck(this, ListType);
        _this3 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ListType).call(this, options, params));
        _this3.entity = _this3.node.querySelector('select');
        BX.bind(_this3.entity, 'change', BX.proxy(_this3.onChange, babelHelpers.assertThisInitialized(_this3)));
        return _this3;
      }
      babelHelpers.createClass(ListType, [{
        key: "getValue",
        value: function getValue() {
          if (!this.entity.multiple) return this.entity.value;
          var values = [];
          Object.values(this.entity.selectedOptions).forEach(function (item) {
            values.push(item.value);
          });
          return values;
        }
      }, {
        key: "setValue",
        value: function setValue(value) {
          if (this.entity.multiple) {
            if (!Array.isArray(value)) value = [value];
            value.forEach(function (singleValue, idx) {
              value[idx] = singleValue.toString();
            });
            Object.values(this.entity.options).forEach(function (item) {
              item.selected = value.indexOf(item.value) !== -1;
            });
          }
        }
      }]);
      return ListType;
    }(BaseType);

    exports.BaseType = BaseType;
    exports.CheckBoxType = CheckBoxType;
    exports.StringType = StringType;
    exports.ListType = ListType;

}((this.DevBX.Admin.ValueType = this.DevBX.Admin.ValueType || {})));


//# sourceMappingURL=admin.js.map
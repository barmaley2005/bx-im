this.DevBX = this.DevBX || {};
this.DevBX.Forms = this.DevBX.Forms || {};
(function (exports) {
  'use strict';

  var fieldMixin = {
    props: ['page', 'item', 'row', 'active', 'config'],
    data: function data() {
      return {};
    },
    mounted: function mounted() {
      this.item.component = this;
    },
    unmounted: function unmounted() {
      this.item.component = false;
    },
    computed: {
      labelFormatted: function labelFormatted() {
        return this.$root.htmlFormatFields(BX.util.htmlspecialchars(this.config.label));
      },
      formLabelFormatted: function formLabelFormatted() {
        return this.labelFormatted + (this.required ? ' <span class="devbx-webform-asterisk">*</span>' : '');
      },
      helpTextFormatted: function helpTextFormatted() {
        return this.$root.htmlFormatFields(this.config.helpText);
      },
      id: function id() {
        return 'devbx-webform-' + this.$.uid;
      },
      readonly: function readonly() {
        return this.$root.formReadonly || this.$root.checkCondition(this, this.config, 'readOnlyRule') === true;
      },
      required: function required() {
        return this.$root.checkCondition(this, this.config, 'requireRule') === true;
      },
      hasCustomError: function hasCustomError() {
        return this.$root.checkCondition(this, this.config, 'showCustomError') === true;
      },
      errorMessage: {
        get: function get() {
          return this.item.errorMessage;
        },
        set: function set(value) {
          this.item.errorMessage = value;
        }
      }
    },
    methods: {
      checkRequired: function checkRequired() {
        if (!this.$root.validation) return false;
        if (typeof this.field === "undefined") {
          console.error('computed field not defined');
          return false;
        }
        if (this.required) {
          if (typeof this.field.value === "number") return false;
          if (typeof this.field.value === "boolean" && this.field.value) return false;
          if (this.field.value === undefined || this.field.value === null || !this.field.value.length) {
            this.errorMessage = this.$root.formatString(this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_ERR_FIELD_REQUIRED'), '#FIELD_NAME#', this.labelFormatted);
            return true;
          }
        }
        return false;
      },
      checkCustomError: function checkCustomError() {
        if (!this.$root.validation) return false;
        if (this.config.customError && this.hasCustomError) {
          this.errorMessage = this.config.customError;
          return true;
        }
        return false;
      },
      checkRuleErrors: function checkRuleErrors() {
        this.errorMessage = '';
        if (this.checkRequired()) return true;
        return this.checkCustomError();
      }
    }
  };

  BX.addCustomEvent("DevBxWebFormCreated", function (app) {
    var store = new BX.Vue3.Vuex.createStore({
      state: {
        sectionResult: []
      },
      mutations: {
        setIblockSectionItems: function setIblockSectionItems(state, data) {
          var result = false;
          state.sectionResult.every(function (item) {
            if (item.sid === data.sid && item.fieldId === data.fieldId && item.filter === data.filter) result = item;
            return !result;
          });
          if (result) {
            if (data.hasOwnProperty('items')) result.data = data.items;
            if (data.hasOwnProperty('errors')) result.errors = data.errors;
            return;
          }
          state.sectionResult.push(BX.Vue3.reactive({
            sid: data.sid,
            fieldId: data.fieldId,
            filter: data.filter,
            data: data.items,
            errors: data.errors
          }));
        }
      },
      actions: {
        getIblockSectionItems: function getIblockSectionItems(context, data) {
          context.commit('setIblockSectionItems', Object.assign({
            items: false
          }, data));
          BX.ajax.runAction('devbx:forms.api.webform.fieldRequest', {
            data: {
              lang: BX.message('LANGUAGE_ID'),
              sid: data.sid,
              fieldId: data.fieldId,
              params: {
                action: 'getList',
                filter: data.filter
              }
            }
          }).then(function (response) {
            context.commit('setIblockSectionItems', Object.assign({}, data, response.data));
          }, function (response) {
            context.commit('setIblockSectionItems', Object.assign({}, data, {
              errors: response.errors
            }));
          });
        }
      },
      getters: {
        iblockSectionItems: function iblockSectionItems(state) {
          return function (sid, fieldId, filter) {
            var result = false;
            state.sectionResult.every(function (item) {
              if (item.sid === sid && item.fieldId === fieldId && item.filter === filter) result = item;
              return !result;
            });
            if (!result) return null;
            return result;
          };
        }
      }
    });
    app.component('devbx-form-layout-field-iblock-section', {
      mixins: [fieldMixin],
      mounted: function mounted() {},
      data: function data() {
        return {
          isLoading: false
        };
      },
      watch: {
        'field.value': function fieldValue(val) {
          var _this = this;
          var valueFields = ['NAME'];
          var option = this.selectedOption;
          valueFields.forEach(function (fieldName) {
            var formField = _this.$root.formFieldByName['Fields.' + _this.config.fieldName + '.' + fieldName];
            if (formField) {
              formField.value = option ? option.item[fieldName] : null;
              formField.changed = true;
            }
          });
        }
      },
      computed: {
        field: function field() {
          return this.$root.formFieldByName['Fields.' + this.config.fieldName + '.ID'];
        },
        value: {
          get: function get() {
            return this.field.value;
          },
          set: function set(value) {
            value = parseInt(value);
            value = isNaN(value) ? null : value;
            if (this.field.value !== value) {
              this.field.value = value;
              this.field.changed = true;
              this.$root.checkPageErrors(false, false);
            }
          }
        },
        filterValues: function filterValues() {
          var _this2 = this;
          var result = {};
          this.config.filter.forEach(function (item) {
            if (item.valueType === 'field') {
              item.code = 'return ' + item.value + ';';
              if (_this2.$root.executeScriptCode(item)) {
                result[item.value] = item.returnVal.value;
              } else {
                result[item.value] = null;
              }
            }
            if (item.valueType === 'array') {
              item.value.forEach(function (singleValue) {
                switch (singleValue.valuetype) {
                  case 'field':
                    var tmp = {
                      code: 'return ' + singleValue.value + ';'
                    };
                    if (_this2.$root.executeScriptCode(tmp)) {
                      result[singleValue.value] = tmp.returnVal.value;
                    } else {
                      result[singleValue.value] = null;
                    }
                    break;
                }
              });
            }
          });
          return JSON.stringify(result);
        },
        options: function options() {
          var _this3 = this;
          this.isLoading = true;
          var items = store.getters.iblockSectionItems(this.$root.sid, this.config.systemId, this.filterValues);
          if (items === null) {
            store.dispatch('getIblockSectionItems', {
              sid: this.$root.sid,
              fieldId: this.config.systemId,
              filter: this.filterValues
            });
            items = store.getters.iblockSectionItems(this.$root.sid, this.config.systemId, this.filterValues);
            if (items) items.data; //for watching

            return [];
          }
          if (items.data === false) return [];
          var result = [],
            valueValid = false;
          items.data.forEach(function (item) {
            var option = {
              value: item.ID,
              text: item.NAME,
              item: item
            };
            valueValid |= item.ID === _this3.value;
            if (_this3.config.type == 'DROP_DOWN_PICTURE') {
              option.picture = item[_this3.config.pictureField];
            }
            result.push(option);
          });
          if (!valueValid) {
            this.value = null;
          }
          this.isLoading = false;
          return result;
        },
        selectedOption: function selectedOption() {
          var _this4 = this;
          var result = false;
          this.options.every(function (item) {
            if (item.value === _this4.value) result = item;
            return !result;
          });
          return result;
        }
      },
      methods: {
        checkErrors: function checkErrors() {
          return this.checkRuleErrors();
        },
        getFilterValues: function getFilterValues() {
          var _this5 = this;
          var result = {};
          this.config.filter.forEach(function (item) {
            if (item.valueType === 'field') {
              item.code = 'return ' + item.value + ';';
              if (_this5.$root.executeScriptCode(item)) {
                result[item.value] = item.returnVal.value;
              } else {
                result[item.value] = null;
              }
            }
          });
          return JSON.stringify(result);
        },
        setFocus: function setFocus() {
          if (this.$refs.multiselect) {
            this.$refs.multiselect.activate();
          }
        }
      },
      template: "\n    <div class=\"devbx-webform-field\" :class=\"{'devbx-webform-required': required, 'devbx-webform--is-error': errorMessage.length}\">\n        <span class=\"devbx-webform-label\" v-html=\"formLabelFormatted\" v-if=\"!config.labelHidden\">\n        </span>\n        \n        <div class=\"devbx-webform-choice-drop-down\" v-if=\"config.type == 'DROP_DOWN' || config.type == 'DROP_DOWN_PICTURE'\">\n            <vue-multiselect\n                ref=\"multiselect\"\n                :options=\"options\"\n                :placeholder=\"config.placeholder\"\n                :no-elements-found-text=\"$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_CHOICE_NO_ELEMENTS_FOUND')\"\n                :list-is-empty=\"$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_LIST_IS_EMPTY')\"\n                @select=\"value = $event.value\"\n                :value=\"selectedOption\"\n                track-by=\"value\"\n                label=\"text\"\n                key=\"value\"\n                :show-labels=\"false\"\n                :disabled=\"readonly\"\n            >\n            \n            <template v-if=\"config.type == 'DROP_DOWN_PICTURE'\" v-slot:singleLabel=\"props\">\n                <div class=\"option__image\" v-if=\"config.type == 'DROP_DOWN_PICTURE'\">\n                    <img v-if=\"props.option.picture\" :src=\"props.option.picture.thumbnail\" :alt=\"props.option.text\">\n                </div>\n                <span class=\"option__desc\"><span class=\"option__title\">{{ props.option.text }}</span></span>\n            </template>\n            \n            <template v-if=\"config.type == 'DROP_DOWN_PICTURE'\" v-slot:option=\"props\">\n                <div class=\"option__image\" v-if=\"config.type == 'DROP_DOWN_PICTURE'\">\n                    <img v-if=\"props.option.picture\" :src=\"props.option.picture.thumbnail\" :alt=\"props.option.text\">\n                </div>\n                <div class=\"option__desc\"><span class=\"option__title\">{{ props.option.text }}</span></div>\n            </template>\n            </vue-multiselect>\n        </div>    \n    \n        <div class=\"devbx-webform-helptext\" v-if=\"helpTextFormatted.length>0\" v-html=\"helpTextFormatted\"></div>\n\n        <transition name=\"devbx-webform-field\">\n            <div class=\"devbx-webform-error-message\" v-if=\"errorMessage\" v-html=\"errorMessage\"></div>\n        </transition>\n    </div>\n    "
    });
  });

}((this.DevBX.Forms.WebForm = this.DevBX.Forms.WebForm || {})));
//# sourceMappingURL=field.iblock.section.js.map

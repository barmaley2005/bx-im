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
    app.component('devbx-form-layout-field-choice', {
      mixins: [fieldMixin],
      mounted: function mounted() {
        if (!this.fieldText.changed) {
          var optText = [],
            optValue = [];
          this.config.options.forEach(function (item) {
            if (item.selected) {
              optText.push(item.text);
              optValue.push(item.value);
            }
          });
          if (this.config.type == 'CHECKBOX') {
            this.fieldText.value = optText;
            this.field.value = optValue;
          } else {
            this.fieldText.value = optText.length ? optText[0] : '';
            this.field.value = optValue.length ? optValue[0] : '';
          }
        }
      },
      computed: {
        assignValues: function assignValues() {
          return this.choiceOptions.indexOf('ASSIGN_VALUES') >= 0;
        },
        fieldText: function fieldText() {
          return this.$root.formFieldByName['Fields.' + this.config.fieldName + '.Text'];
        },
        field: function field() {
          return this.$root.formFieldByName['Fields.' + this.config.fieldName + '.Value'];
        },
        options: function options() {
          var _this = this;
          var result = [];
          if (this.config.type == 'DROP_DOWN') {
            result.push({
              text: '',
              value: ''
            });
          }
          this.config.options.forEach(function (item) {
            var option = {
              text: item.text,
              value: item.value
            };
            if (_this.config.type == 'CHECKBOX') {
              option.selected = _this.field.value.indexOf(item.value) >= 0;
            } else {
              option.selected = _this.field.value === item.value;
            }
            result.push(option);
          });
          return result;
        },
        selectedOption: function selectedOption() {
          var result = false;
          this.options.every(function (option) {
            if (option.selected) {
              result = option;
              return false;
            }
            return true;
          });
          return result;
        },
        selectedValue: function selectedValue() {
          return this.field.value;
        }
      },
      methods: {
        toggleValue: function toggleValue(value, checked) {
          var _this2 = this;
          this.options.every(function (item) {
            if (item.value !== value) return true;
            if (_this2.config.type == 'CHECKBOX') {
              var idxText = _this2.fieldText.value.indexOf(item.text),
                idxValue = _this2.field.value.indexOf(item.value);
              if (checked) {
                if (idxText < 0) _this2.fieldText.value.push(item.text);
                if (idxValue < 0) _this2.field.value.push(item.value);
              } else {
                if (idxText >= 0) _this2.fieldText.value.splice(idxText, 1);
                if (idxValue >= 0) _this2.field.value.splice(idxValue, 1);
              }
            } else {
              if (checked) {
                _this2.fieldText.value = item.text;
                _this2.field.value = item.value;
              } else {
                _this2.fieldText.value = '';
                _this2.field.value = '';
              }
            }
            _this2.fieldText.changed = true;
            _this2.field.changed = true;
            _this2.$root.checkPageErrors(false, false);
            return false;
          });
        },
        updateSelectValue: function updateSelectValue(option) {
          this.fieldText.value = option.text;
          this.field.value = option.value;
          this.fieldText.changed = true;
          this.field.changed = true;
          if (this.errorMessage) this.checkErrors();
        },
        checkErrors: function checkErrors() {
          return this.checkRuleErrors();
        },
        setFocus: function setFocus() {
          if (this.$refs.multiselect) {
            this.$refs.multiselect.activate();
          }
        }
      },
      template: "\n    <div class=\"devbx-webform-field\" :class=\"{'devbx-webform-required': required, 'devbx-webform--is-error': errorMessage.length}\">\n        <span class=\"devbx-webform-label\" v-html=\"formLabelFormatted\" v-if=\"!config.labelHidden\">\n        </span>\n        \n        <div class=\"devbx-webform-choice-drop-down\" v-if=\"config.type == 'DROP_DOWN'\">\n            <vue-multiselect\n                ref=\"multiselect\"\n                :options=\"options\"\n                :placeholder=\"config.placeholder\"\n                :no-elements-found-text=\"$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_CHOICE_NO_ELEMENTS_FOUND')\"\n                :list-is-empty=\"$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_LIST_IS_EMPTY')\"\n                @select=\"updateSelectValue\"\n                :value=\"selectedOption\"\n                track-by=\"value\"\n                label=\"text\"\n                key=\"value\"\n                :show-labels=\"false\"\n                :disabled=\"readonly\"\n            ></vue-multiselect>\n        </div>    \n        <div class=\"devbx-webform-choice-radio\" v-if=\"config.type == 'RADIO'\">\n                <div :class=\"{\n                    'devbx-webform-choice-column-1': config.visual == 'ONE_COLUMN',\n                    'devbx-webform-choice-column-2': config.visual == 'TWO_COLUMN',\n                    'devbx-webform-choice-column-0': config.visual == 'SIDE_BY_SIDE',\n                    }\">\n                     <div class=\"devbx-webform-choice-option\" v-for=\"option in options\">\n                        <label>\n                            <input class=\"devbx-webform-radio\" \n                                tabindex=\"-1\" \n                                @click=\"toggleValue(option.value, $event.target.checked)\" \n                                type=\"radio\" \n                                :disabled=\"readonly\"\n                                :checked=\"option.selected\">\n                            <span class=\"devbx-webform-radio-label\">{{option.text}}</span>                    \n                        </label>                 \n                     </div>\n                </div>\n        </div>\n\n        <div class=\"devbx-webform-choice-checkbox\" v-if=\"config.type == 'CHECKBOX'\">\n                <div :class=\"{\n                    'devbx-webform-choice-column-1': config.visual == 'ONE_COLUMN',\n                    'devbx-webform-choice-column-2': config.visual == 'TWO_COLUMN',\n                    'devbx-webform-choice-column-0': config.visual == 'SIDE_BY_SIDE',\n                    }\">\n                     <div class=\"devbx-webform-choice-option\" v-for=\"option in options\">\n                        <label>\n                            <input class=\"devbx-webform-checkbox\" \n                            tabindex=\"-1\" \n                            @click=\"toggleValue(option.value, $event.target.checked)\" \n                            type=\"checkbox\" \n                            :disabled=\"readonly\"\n                            :checked=\"option.selected\"\n                            >\n                            <span class=\"devbx-webform-checkbox-label\">{{option.text}}</span>                    \n                        </label>                 \n                     </div>\n                </div>\n        </div>\n        \n        <div class=\"devbx-webform-helptext\" v-if=\"helpTextFormatted.length>0\" v-html=\"helpTextFormatted\"></div>\n\n        <transition name=\"devbx-webform-field\">\n            <div class=\"devbx-webform-error-message\" v-if=\"errorMessage\" v-html=\"errorMessage\"></div>\n        </transition>\n    </div>\n    "
    });
  });

}((this.DevBX.Forms.WebForm = this.DevBX.Forms.WebForm || {})));
//# sourceMappingURL=field.choice.js.map

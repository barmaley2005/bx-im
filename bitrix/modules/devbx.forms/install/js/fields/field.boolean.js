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
    app.component('devbx-form-layout-field-boolean', {
      mixins: [fieldMixin],
      watch: {
        'defaultValue': {
          immediate: true,
          handler: function handler(val) {
            if (!this.field.changed) {
              this.field.value = val;
            }
          }
        }
      },
      methods: {
        checkErrors: function checkErrors() {
          if (this.checkRuleErrors()) return true;
          return false;
        },
        setFocus: function setFocus() {
          //this.$el.querySelector('input, textarea').focus();
        }
      },
      computed: {
        defaultValue: function defaultValue() {
          return this.config.defaultValue;
        },
        field: function field() {
          return this.$root.formFieldByName['Fields.' + this.config.fieldName];
        },
        value: {
          get: function get() {
            return this.field.value;
          },
          set: function set(value) {
            if (this.field.value !== value) {
              this.field.value = value;
              this.field.changed = true;
              this.$root.checkPageErrors(false, false);
            }
          }
        },
        labelValueYes: function labelValueYes() {
          if (this.config.customLabels && this.config.customLabelYes) return this.config.customLabelYes;
          return this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_BOOLEAN_VALUE_TRUE');
        },
        labelValueNo: function labelValueNo() {
          if (this.config.customLabels && this.config.customLabelNo) return this.config.customLabelNo;
          return this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_BOOLEAN_VALUE_FALSE');
        }
      },
      template: "\n    <div class=\"devbx-webform-field\" :class=\"{'devbx-webform-required': required, 'devbx-webform--is-error': errorMessage.length}\">\n        <label class=\"devbx-webform-label\" :for=\"id\" v-html=\"formLabelFormatted\" v-if=\"!config.labelHidden && config.type == 'RADIO'\"></label>\n        <div class=\"devbx-webform-boolean-radio-group\" v-if=\"config.type == 'RADIO'\">\n            <label class=\"devbx-webform-boolean-radio\">\n                <input class=\"devbx-webform-boolean-radio-input\" \n                    tabindex=\"-1\" \n                    @click=\"value = true\" \n                    type=\"radio\" \n                    :disabled=\"readonly\"\n                    :checked=\"value\">\n                        <span class=\"devbx-webform-boolean-radio-label\">{{labelValueYes}}</span>                    \n            </label>                 \n\n            <label class=\"devbx-webform-boolean-radio\">\n                <input class=\"devbx-webform-boolean-radio-input\" \n                    tabindex=\"-1\" \n                    @click=\"value = false\" \n                    type=\"radio\" \n                    :disabled=\"readonly\"\n                    :checked=\"!value\">\n                        <span class=\"devbx-webform-boolean-radio-label\">{{labelValueNo}}</span>                    \n            </label>                 \n        </div>\n        <div class=\"devbx-webform-boolean-checkbox-inline\" v-else>\n            <label class=\"devbx-webform-boolean-checkbox\">\n                <input class=\"devbx-webform-boolean-checkbox-input\" \n                    tabindex=\"-1\" \n                    type=\"checkbox\" \n                    :disabled=\"readonly\"\n                    v-model=\"value\">\n                    <span class=\"devbx-webform-boolean-checkbox-label\" v-html=\"formLabelFormatted\"></span>                    \n            </label>                 \n        </div>\n        \n        <div class=\"devbx-webform-helptext\" v-if=\"helpTextFormatted.length>0\" v-html=\"helpTextFormatted\"></div>\n\n        <transition name=\"devbx-webform-field\">\n            <div class=\"devbx-webform-error-message\" v-if=\"errorMessage\" v-html=\"errorMessage\"></div>\n        </transition>\n    </div>\n    "
    });
  });

}((this.DevBX.Forms.WebForm = this.DevBX.Forms.WebForm || {})));
//# sourceMappingURL=field.boolean.js.map

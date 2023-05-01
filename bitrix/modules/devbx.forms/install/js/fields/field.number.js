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
    app.component('devbx-form-layout-field-number', {
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
        roundValue: function roundValue(value) {
          if (this.config.type === 'INTEGER' || this.decimalPlaces <= 0) {
            value = parseInt(value, 10);
            return isNaN(value) ? null : value;
          }
          value = parseFloat(value);
          if (isNaN(value) || value === Infinity) return null;
          value = value.toFixed(this.decimalPlaces).split('.');
          return parseFloat(value[0] + '.' + value[1].substring(0, this.decimalPlaces));
        },
        onChange: function onChange(event) {
          var value = event.target.value.trim();
          value = value.replaceAll(this.$root.culture.numberThousandsSeparator, '');
          value = value.replaceAll(this.$root.culture.numberDecimalSeparator, '.');
          value = this.roundValue(value);
          if (value !== null) {
            if (this.maxValue !== null && value > this.maxValue) value = this.maxValue;
            if (this.minValue !== null && value < this.minValue) value = this.minValue;
          } else {
            value = event.target.value.trim();
          }
          this.value = value;
          event.target.value = this.displayValue;
        },
        checkErrors: function checkErrors() {
          if (this.value.length && this.roundValue(this.value) === null) {
            this.errorMessage = this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_NUMBER_ERR_VALUE');
            return true;
          }
          if (this.checkRuleErrors()) return true;
          return false;
        },
        setFocus: function setFocus() {
          this.$refs.input.focus();
        },
        clickMinus: function clickMinus() {
          if (this.readonly) return;
          var newValue = this.roundValue(this.value);
          if (newValue === null) {
            this.value = this.minValue !== null ? this.minValue : 1;
            return;
          }
          newValue -= this.incrementValue;
          if (this.minValue !== null && newValue < this.minValue) newValue = this.minValue;
          this.value = newValue;
        },
        clickPlus: function clickPlus() {
          if (this.readonly) return;
          var newValue = this.roundValue(this.value);
          if (newValue === null) {
            this.value = this.minValue !== null ? this.minValue : 1;
            return;
          }
          newValue += this.incrementValue;
          if (this.maxValue !== null && newValue > this.maxValue) newValue = this.maxValue;
          this.value = newValue;
        },
        formatNumber: function formatNumber(str) {
          var value = this.roundValue(str);
          if (value === null) return str;
          if (this.config.type === 'INTEGER' || this.decimalPlaces <= 0) {
            value = value.toString();
          } else {
            value = value.toFixed(this.decimalPlaces);
          }
          var parts = value.toString().split(".");
          return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, this.$root.culture.numberThousandsSeparator) + (parts[1] ? this.$root.culture.numberDecimalSeparator + parts[1] : "");
        }
      },
      computed: {
        defaultValue: function defaultValue() {
          return this.config.defaultValue;
        },
        decimalPlaces: function decimalPlaces() {
          var value = parseInt(this.config.decimalPlaces, 10);
          return isNaN(value) ? 0 : value;
        },
        minValue: function minValue() {
          return this.roundValue(this.config.minValue);
        },
        maxValue: function maxValue() {
          return this.roundValue(this.config.maxValue);
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
        displayValue: function displayValue() {
          return this.formatNumber(this.field.value);
        },
        cssMaxWidth: function cssMaxWidth() {
          var length = Math.max(this.config.placeholder.length, this.displayValue.length, this.formatNumber(this.defaultValue).length);
          return Math.max(6 + length * 0.5, 9).toString() + 'em';
        },
        incrementValue: function incrementValue() {
          var value = this.roundValue(this.config.incrementValue);
          if (value === null) value = 1;
          value = Math.abs(value);
          return value;
        }
      },
      template: "\n    <div class=\"devbx-webform-field\" :class=\"{'devbx-webform-required': required, 'devbx-webform--is-error': errorMessage.length}\">\n        <label class=\"devbx-webform-label\" :for=\"id\" v-html=\"formLabelFormatted\" v-if=\"!config.labelHidden\"></label>\n        <div class=\"devbx-webform-field-input\" v-if=\"config.visual == 'TEXT'\">\n            <input ref=\"input\"\n                autocomplete=\"off\" \n                class=\"devbx-webform-input\" \n                :id=\"id\" \n                type=\"text\" \n                :value=\"displayValue\"\n                :placeholder=\"config.placeholder\" \n                :readonly=\"readonly\"\n                @change=\"onChange\"\n                >\n        </div>\n        <div class=\"devbx-webform-field-input\" v-else>\n            <div class=\"devbx-webform-spinner-number\" :style=\"{'max-width': cssMaxWidth}\">\n                <span :title=\"$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_NUMBER_DECREASE_TITLE')\"\n                    class=\"devbx-webform-number-decrease\" @click=\"clickMinus\"\n                    :class=\"{'devbx-webform-disabled': readonly}\"\n                    >\n                    <i class=\"devbx-webform-icon-minus\"></i>                \n                </span>                    \n                <span :title=\"$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_NUMBER_INCREASE_TITLE')\" \n                    class=\"devbx-webform-number-increase\" @click=\"clickPlus\"\n                    :class=\"{'devbx-webform-disabled': readonly}\"\n                    >\n                    <i class=\"devbx-webform-icon-plus\"></i>                \n                </span>\n            <input ref=\"input\"\n                autocomplete=\"off\" \n                class=\"devbx-webform-input\" \n                :id=\"id\" \n                type=\"text\" \n                :value=\"displayValue\"\n                :placeholder=\"config.placeholder\" \n                :readonly=\"readonly\"\n                @change=\"onChange\"\n                >\n            </div>\n        </div>\n        \n        <div class=\"devbx-webform-helptext\" v-if=\"helpTextFormatted.length>0\" v-html=\"helpTextFormatted\"></div>\n\n        <transition name=\"devbx-webform-field\">\n            <div class=\"devbx-webform-error-message\" v-if=\"errorMessage\" v-html=\"errorMessage\"></div>\n        </transition>\n    </div>\n    "
    });
  });

}((this.DevBX.Forms.WebForm = this.DevBX.Forms.WebForm || {})));
//# sourceMappingURL=field.number.js.map

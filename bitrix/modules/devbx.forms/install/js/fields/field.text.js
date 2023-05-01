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
    app.component('devbx-form-layout-field-text', {
      mixins: [fieldMixin],
      data: function data() {
        return {
          inputTimer: false
        };
      },
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
        onInput: function onInput(event) {
          var value = event.target.value;
          if (this.maximumLength) {
            value = value.substring(0, this.maximumLength);
            event.target.value = value;
          }
          var self = this;
          if (this.inputTimer) clearTimeout(this.inputTimer);
          this.inputTimer = setTimeout(function () {
            self.inputTimer = false;
            self.onChange();
          }, 300);
        },
        onChange: function onChange() {
          if (!this.$refs.input) return;
          var value = this.$refs.input.value.trim();
          if (this.maximumLength) {
            value = value.substring(0, this.maximumLength);
          }
          this.$refs.input.value = value;
          this.field.value = value;
          this.field.changed = true;
          this.checkErrors();
        },
        checkErrors: function checkErrors() {
          if (this.checkRuleErrors()) return true;
          if (this.minimumLength) {
            if (this.minimumLength > this.field.value.length) {
              this.errorMessage = this.$root.formatString(this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_ERR_FIELD_REQUIRED_MIN_LENGTH'), ['#FIELD_NAME#', '#MIN_LENGTH#'], [this.labelFormatted, this.minimumLength]);
              return true;
            }
          }
          return false;
        },
        setFocus: function setFocus() {
          this.$refs.input.focus();
        }
      },
      computed: {
        defaultValue: function defaultValue() {
          return this.config.defaultValue;
        },
        minimumLength: function minimumLength() {
          var value = parseInt(this.config.lengthMin);
          if (isNaN(value) || value <= 0) return false;
          return value;
        },
        maximumLength: function maximumLength() {
          var value = parseInt(this.config.lengthMax);
          if (isNaN(value) || value <= 0) return false;
          return value;
        },
        field: function field() {
          return this.$root.formFieldByName['Fields.' + this.config.fieldName];
        },
        value: {
          get: function get() {
            return this.field.value;
          },
          set: function set(value) {
            this.field.value = value.trim();
            this.field.changed = true;
          }
        }
      },
      template: "\n    <div class=\"devbx-webform-field\" :class=\"{'devbx-webform-required': required, 'devbx-webform--is-error': errorMessage.length}\">\n        <label class=\"devbx-webform-label\" :for=\"id\" v-html=\"formLabelFormatted\" v-if=\"!config.labelHidden\"></label>\n        <div class=\"devbx-webform-field-input\" v-if=\"config.type == 'SINGLE_LINE'\">\n            <input ref=\"input\"\n                autocomplete=\"off\" \n                class=\"devbx-webform-input\" \n                :id=\"id\" \n                type=\"text\" \n                :value=\"value\"\n                :placeholder=\"config.placeholder\" \n                :readonly=\"readonly\"\n                @input=\"onInput\"\n                @change=\"onChange\"\n                >\n        </div>\n        <div class=\"devbx-webform-field-input\" v-if=\"config.type == 'MULTI_LINE'\">\n            <textarea ref=\"input\" \n                autocomplete=\"off\" \n                class=\"devbx-webform-textarea\" \n                style=\"resize:both;\"\n                :id=\"id\" \n                :value=\"value\"\n                :placeholder=\"config.placeholder\" \n                :readonly=\"readonly\"\n                @input=\"onInput\"\n                @change=\"onChange\"\n                >\n            </textarea>\n        </div>\n        \n        <div class=\"devbx-webform-helptext\" v-if=\"helpTextFormatted.length>0\" v-html=\"helpTextFormatted\"></div>\n\n        <transition name=\"devbx-webform-field\">\n            <div class=\"devbx-webform-error-message\" v-if=\"errorMessage\" v-html=\"errorMessage\"></div>\n        </transition>\n    </div>\n    "
    });
  });

}((this.DevBX.Forms.WebForm = this.DevBX.Forms.WebForm || {})));
//# sourceMappingURL=field.text.js.map

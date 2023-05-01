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
    app.component('devbx-form-layout-field-email', {
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
          if (this.inputTimer) clearTimeout(this.inputTimer);

          /*
          this.inputTimer = setTimeout(function () {
                self.inputTimer = false;
              self.onChange();
            }, 300);
           */
        },
        onChange: function onChange() {
          if (!this.$refs.input) return;
          var value = this.$refs.input.value.trim();
          this.$refs.input.value = value;
          this.field.value = value;
          this.field.changed = true;
          this.checkErrors();
        },
        validateEmail: function validateEmail(email) {
          return String(email).toLowerCase().match(/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
        },
        checkErrors: function checkErrors() {
          if (!this.$root.validation) return false;
          if (this.field.value && !this.validateEmail(this.field.value)) {
            this.errorMessage = this.$root.formatString(this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_ERR_INVALID_EMAIL'), ['#FIELD_NAME#'], [this.labelFormatted]);
            return true;
          }
          return this.checkRuleErrors();
        },
        setFocus: function setFocus() {
          if (this.$refs.input) this.$refs.input.focus();
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
            this.field.value = value.trim();
            this.field.changed = true;
          }
        }
      },
      template: "\n    <div class=\"devbx-webform-field\" :class=\"{'devbx-webform-required': required, 'devbx-webform--is-error': errorMessage.length}\">\n        <label class=\"devbx-webform-label\" :for=\"id\" v-html=\"formLabelFormatted\" v-if=\"!config.labelHidden\"></label>\n        <div class=\"devbx-webform-field-input\">\n            <input ref=\"input\"\n                autocomplete=\"off\" \n                class=\"devbx-webform-input devbx-webform-email\" \n                :id=\"id\" \n                type=\"text\" \n                :value=\"value\"\n                :placeholder=\"config.placeholder\" \n                :readonly=\"readonly\"\n                @input=\"onInput\"\n                @change=\"onChange\"\n                >\n        </div>\n        \n        <div class=\"devbx-webform-helptext\" v-if=\"helpTextFormatted.length>0\" v-html=\"helpTextFormatted\"></div>\n\n        <transition name=\"devbx-webform-field\">\n            <div class=\"devbx-webform-error-message\" v-if=\"errorMessage\" v-html=\"errorMessage\"></div>\n        </transition>\n    </div>\n    "
    });
  });

}((this.DevBX.Forms.WebForm = this.DevBX.Forms.WebForm || {})));
//# sourceMappingURL=field.email.js.map

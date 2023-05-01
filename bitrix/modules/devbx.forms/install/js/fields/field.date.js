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
      DatePicker.install(app);
      app.component('devbx-form-layout-field-date', {
        mixins: [fieldMixin],
        data: function data() {
          return {
            popupStyle: {},
            timer: false
          };
        },
        computed: {
          field: function field() {
            return this.$root.formFieldByName['Fields.' + this.config.fieldName];
          },
          value: {
            get: function get() {
              var i = parseInt(this.field.value);
              if (isNaN(i)) return null;
              if (this.config.type === 'TIME') return new Date(2000, 0, 1).getTime() + i * 1000;
              return i * 1000;
            },
            set: function set(value) {
              if (value !== null) {
                if (this.config.type === 'TIME') {
                  var d = new Date(value);
                  value = d.getHours() * 60 * 60 + d.getMinutes() * 60 + d.getSeconds();
                } else {
                  value = Math.floor(value / 1000);
                }
              }
              if (this.field.value !== value) {
                this.field.value = value;
                this.field.changed = true;
                this.$root.checkPageErrors(false, false);
              }
            }
          },
          dateType: function dateType() {
            return this.config.type.toLowerCase();
          },
          dateFormat: function dateFormat() {
            switch (this.config.type) {
              case 'DATE':
                return BX.message['FORMAT_DATE'];
              case 'DATETIME':
                var value = BX.message['FORMAT_DATETIME'];
                value = value.replaceAll('MI', 'mm');
                value = value.replaceAll('SS', '');
                if (value.slice(-1) === ':') {
                  value = value.slice(0, -1);
                }
                return value;
              case 'TIME':
                return 'HH:mm';
            }
            return '';
          },
          locale: function locale() {
            var data = {
              formatLocale: {
                months: [],
                monthsShort: [],
                weekdays: [],
                weekdaysShort: [],
                weekdaysMin: [],
                firstDayOfWeek: 1,
                firstWeekContainsDate: 1
              },
              yearFormat: 'YYYY',
              monthFormat: 'MMM',
              monthBeforeYear: true
            };
            for (var i = 0; i < 7; i++) {
              data.formatLocale.weekdays.push(BX.message('DAY_OF_WEEK_' + i));
              data.formatLocale.weekdaysShort.push(BX.message('DOW_' + i));
              data.formatLocale.weekdaysMin.push(BX.message('DOW_' + i));
            }
            for (var _i = 1; _i <= 12; _i++) {
              data.formatLocale.months.push(BX.message('MONTH_' + _i));
              data.formatLocale.monthsShort.push(BX.message('MON_' + _i));
            }
            return data;
          },
          timePickerOptions: function timePickerOptions() {
            return {
              start: this.config.timeStart ? this.config.timeStart : '00:00',
              end: this.config.timeEnd ? this.config.timeEnd : '23:45',
              step: this.config.step ? this.config.step : '00:15',
              format: 'HH:mm'
            };
          }
        },
        methods: {
          updatePosition: function updatePosition() {
            var elRect = this.$el.querySelector('input').getBoundingClientRect();
            this.popupStyle.top = window.scrollY + elRect.y + elRect.height + 'px';
            this.popupStyle.left = window.scrollX + elRect.x + 'px';
          },
          onOpen: function onOpen() {
            /*
            this.updatePosition();
              if (!this.timer)
            {
                this.timer = setInterval(BX.delegate(this.updatePosition, this), 100);
            }*/
          },
          onClose: function onClose() {
            //clearInterval(this.timer);
          },
          checkErrors: function checkErrors() {
            return this.checkRuleErrors();
          },
          setFocus: function setFocus() {
            this.$children[0].focus();
          }
        },
        template: "\n    <div class=\"devbx-webform-field\" :class=\"{'devbx-webform-required': required, 'devbx-webform--is-error': errorMessage.length}\">\n        <span class=\"devbx-webform-label\" v-html=\"formLabelFormatted\" v-if=\"!config.labelHidden\">\n        </span>\n\n        <date-picker \n            v-model:value=\"value\" \n            value-type=\"timestamp\"\n            :type=\"dateType\" \n            :placeholder=\"config.placeholder\"\n            :format=\"dateFormat\"\n            :time-picker-options=\"timePickerOptions\"\n            :disabled=\"readonly\"\n            @open=\"onOpen\"\n            @close=\"onClose\"\n            :lang=\"locale\"\n            popup-class=\"devbx-webform-theme\"\n            >\n        </date-picker>\n\n        <div class=\"devbx-webform-helptext\" v-if=\"helpTextFormatted.length>0\" v-html=\"helpTextFormatted\"></div>\n\n        <transition name=\"devbx-webform-field\">\n            <div class=\"devbx-webform-error-message\" v-if=\"errorMessage\" v-html=\"errorMessage\"></div>\n        </transition>\n    </div>    \n    "
      });
    });

}((this.DevBX.Forms.WebForm = this.DevBX.Forms.WebForm || {})));
//# sourceMappingURL=field.date.js.map

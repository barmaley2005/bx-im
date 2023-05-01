this.DevBX = this.DevBX || {};
this.DevBX.Forms = this.DevBX.Forms || {};
(function (exports) {
    'use strict';

    BX.addCustomEvent("DevBxWebFormCreated", function (app) {
      app.component('devbx-form-layout-section', {
        props: ['page', 'item', 'row', 'active', 'config'],
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
          helpTextFormatted: function helpTextFormatted() {
            return this.$root.htmlFormatFields(this.config.helpText);
          }
        },
        template: "\n    <div>\n        <h2 class=\"devbx-webform-col-heading\"  v-if=\"!config.labelHidden\" v-html=\"labelFormatted\"></h2>\n        <div class=\"devbx-webform-col-content\" v-if=\"config.helpText.length>0\" v-html=\"helpTextFormatted\"></div>\n        <devbx-webform-form-row v-for=\"(row, index) in config.layout.rows\" :key=\"index\" v-bind:row=\"row\"/>\n    </div>\n    "
      });
    });

}((this.DevBX.Forms.WebForm = this.DevBX.Forms.WebForm || {})));
//# sourceMappingURL=layout.section.js.map

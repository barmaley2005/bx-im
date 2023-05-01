this.DevBX = this.DevBX || {};
this.DevBX.Forms = this.DevBX.Forms || {};
(function (exports) {
  'use strict';

  BX.addCustomEvent("DevBxWebFormCreated", function (app) {
    app.component('devbx-form-layout-content', {
      props: ['page', 'item', 'row', 'active', 'config'],
      mounted: function mounted() {
        this.item.component = this;
      },
      unmounted: function unmounted() {
        this.item.component = false;
      },
      computed: {
        contentFormatted: function contentFormatted() {
          return this.$root.htmlFormatFields(this.config.content);
        }
      },
      template: "\n    <div class=\"devbx-webform-col-content\" v-html=\"contentFormatted\">\n        \n    </div>\n    "
    });
  });

}((this.DevBX.Forms.WebForm = this.DevBX.Forms.WebForm || {})));
//# sourceMappingURL=layout.content.js.map

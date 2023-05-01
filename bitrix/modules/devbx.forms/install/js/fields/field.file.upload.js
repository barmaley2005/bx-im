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
    app.component('file-upload', VueUploadComponent);
    app.component('devbx-form-layout-field-file-upload', {
      mixins: [fieldMixin],
      data: function data() {
        return {
          files: [],
          disableAdd: false
        };
      },
      methods: {
        formatSize: function formatSize(bytes) {
          var units = ['B', 'kB', 'MB', 'GB'],
            thresh = 1024;
          if (Math.abs(bytes) < thresh) {
            return bytes + ' ' + units[0];
          }
          var u = 0;
          var dp = 2;
          var r = Math.pow(10, dp);
          do {
            bytes /= thresh;
            ++u;
          } while (Math.round(Math.abs(bytes) * r) / r >= thresh && u < units.length - 1);
          return bytes.toFixed(dp) + ' ' + units[u];
        },
        inputFile: function inputFile(newFile, oldFile) {
          //response
          if (newFile && oldFile && !newFile.active && oldFile.active) {
            if (newFile.response.status === 'success') {
              if (!Array.isArray(this.field.value)) this.field.value = [];
              var data = newFile.response.data;
              data.uploaded = true;
              this.field.value.push(data);
              this.field.changed = true;
              this.$root.checkPageErrors(false, false);
            } else {
              if (newFile.response.errors) {
                var messages = [];
                newFile.response.errors.forEach(function (error) {
                  messages.push(error.message);
                });
                this.$refs.upload.update(newFile, {
                  error: messages.join("\n")
                });
              }
            }
            return;
          }
          if (!newFile && oldFile) {
            if (oldFile.success && oldFile.response.data && oldFile.response.data.fileId) ;
            return;
          }
          if (Boolean(newFile) !== Boolean(oldFile) || oldFile.error !== newFile.error) {
            if (!this.$refs.upload.active) {
              if (this.disableAdd) {
                this.$refs.upload.remove(newFile);
                return;
              }
              if (this.config.maximumNumberOfFiles > 0 && this.allFiles.length > this.config.maximumNumberOfFiles) {
                this.disableAdd = true;
                this.$refs.upload.remove(newFile);
                this.errorMessage = this.$root.formatString(this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FILE_UPLOAD_ERR_NUMBER_OF_FILES'), '#VALUE#', this.config.maximumNumberOfFiles);
                var self = this;
                setTimeout(function () {
                  self.disableAdd = false;
                }, 100);
                return;
              }
              if (this.config.maximumFileSize > 0 && newFile.size > this.config.maximumFileSize * 1024 * 1024) {
                newFile.error = this.$root.formatString(this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FILE_UPLOAD_ERR_FILE_SIZE'), '#VALUE#', this.formatSize(this.config.maximumFileSize * 1024 * 1024));
                return;
              }
              if (this.allowedFileTypes.length > 0 && this.allowedFileTypes.indexOf(this.getFileExt(newFile.name)) < 0) {
                newFile.error = this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FILE_UPLOAD_ERR_INVALID_FILE_TYPE');
                return;
              }
              var URL = window.URL || window.webkitURL;
              if (URL) {
                this.$refs.upload.update(newFile, {
                  blob: URL.createObjectURL(newFile.file)
                });
              }
              this.$refs.upload.active = true;
            }
            return;
          }
          console.log(newFile, oldFile);
        },
        getFileExt: function getFileExt(filename) {
          var ext = filename.split('.').pop();
          if (ext === filename) return '?';
          return ext;
        },
        addFile: function addFile() {
          window.upload = this.$refs.upload;
          this.$refs.upload.$refs.input.click();

          //this.$refs.upload.$el.querySelector('input').click();
        },
        removeFile: function removeFile(file) {
          var _this = this;
          if (file.instance) {
            this.$refs.upload.remove(file.instance);
          }
          this.value.every(function (item, index) {
            if (item.fileId !== file.fileId) return true;
            if (item.uploaded) {
              BX.ajax.runAction('devbx:forms.api.webform.fieldRequest', {
                data: {
                  sid: _this.$root.sid,
                  fieldId: _this.config.systemId,
                  params: {
                    action: 'deleteFile',
                    fileId: item.fileId
                  }
                }
              });
            }
            _this.value.splice(index, 1);
            _this.checkErrors();
            return false;
          });
        },
        checkErrors: function checkErrors() {
          if (this.checkRuleErrors()) return true;
          return false;
        }
      },
      computed: {
        postHeaders: function postHeaders() {
          var result = {};
          result['X-Bitrix-Csrf-Token'] = BX.bitrix_sessid();
          return result;
        },
        postData: function postData() {
          var result = {
            sid: this.$root.sid,
            fieldId: this.config.systemId
          };
          result['params[action]'] = 'upload';
          return result;
        },
        allFiles: function allFiles() {
          var result = JSON.parse(JSON.stringify(this.value)),
            fileMap = {};
          result.forEach(function (file) {
            file.id = file.fileId;
            fileMap[file.fileId] = file;
          });
          this.files.forEach(function (file) {
            if (file.response.data && file.response.data.fileId && fileMap[file.response.data.fileId]) {
              fileMap[file.response.data.fileId].instance = file;
              return;
            }
            if (!file.success || file.error) {
              result.push({
                id: file.id,
                name: file.name,
                size: file.size,
                instance: file
              });
            }
          });
          return result;
        },
        allowedFileTypes: function allowedFileTypes() {
          return this.config.allowedFileTypes.split(',').map(function (v) {
            return v.trim();
          }).filter(function (v) {
            return v.length > 0;
          });
        },
        field: function field() {
          return this.$root.formFieldByName['Fields.' + this.config.fieldName];
        },
        value: {
          get: function get() {
            if (!Array.isArray(this.field.value)) this.field.value = [];
            return this.field.value;
          },
          set: function set(value) {
            this.field.value;
            this.field.changed = true;
            this.$root.checkPageErrors(false, false);
          }
        },
        allowMultiple: function allowMultiple() {
          return this.config.multiple && this.config.maximumNumberOfFiles != 1;
        },
        allowUpload: function allowUpload() {
          if (this.config.multiple) return !(this.config.maximumNumberOfFiles > 0 && this.allFiles.length >= this.config.maximumNumberOfFiles);
          return !this.allFiles.length;
        },
        inputName: function inputName() {
          return 'file-upload-' + this.config.systemId;
        }
      },
      template: "\n    <div class=\"devbx-webform-field\" :class=\"{'devbx-webform-required': required, 'devbx-webform--is-error': errorMessage.length}\">\n        <span class=\"devbx-webform-label\" v-html=\"formLabelFormatted\" v-if=\"!config.labelHidden\">\n        </span>\n        \n        <div>\n            <div class=\"devbx-webform-file-upload-container\">\n                <div>                \n                    <button v-if=\"allowUpload\" \n                        class=\"devbx-webform-button\" \n                        :disabled=\"readonly\"\n                        @click=\"addFile\"\n                        >\n                        <span>{{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FILE_UPLOAD_BUTTON')}}</span>\n                    </button>\n                    \n                    <file-upload\n                        class=\"devbx-webform-file-upload-zone\"\n                        post-action=\"/bitrix/services/main/ajax.php?action=devbx:forms.api.webform.fieldRequest\"\n                        v-model=\"files\"\n                        :headers=\"postHeaders\"\n                        :data=\"postData\"\n                        :multiple=\"allowMultiple\"\n                        :drop=\"allowUpload\"\n                        :drop-directory=\"true\"\n                        :disabled=\"readonly\"\n                        ref=\"upload\"\n                        @input-file=\"inputFile\"\n                        >\n                        <span v-if=\"allowUpload\">{{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FILE_UPLOAD_DROPZONE')}}</span>\n                    </file-upload>\n                </div>                    \n            </div>\n            \n            <ul v-if=\"allFiles.length\" class=\"devbx-webform-file-upload-list\">\n                <li v-for=\"file in allFiles\" :key=\"file.id\">\n                <div>\n                    <img class=\"devbx-webform-file-upload-file-thumb\" v-if=\"file.preview\" :src=\"file.preview\">\n\n                    <svg class=\"devbx-webform-file-upload-file-icon\" viewBox=\"0 0 32 32\" style=\"width: 3em;height: 3em;vertical-align: middle;fill: #bdcccc;overflow: hidden;\">\n                        <path d=\"M 20.37 1.126 L 4.791 1.126 L 4.791 31.562 L 27.509 31.562 L 27.509 8.911 L 20.37 1.126 Z M 21.019 3.837 L 25.023 8.204 L 21.019 8.204 L 21.019 3.837 Z M 26.212 30.147 L 6.089 30.147 L 6.089 2.542 L 19.72 2.542 L 19.72 9.619 L 26.212 9.619 L 26.212 30.147 Z\" style=\"\"/>\n                        <rect x=\"7.474\" y=\"17.684\" width=\"17.264\" height=\"11.626\" style=\"fill: rgb(208, 219, 219);\"/>\n                        <text style=\"white-space: pre;fill: rgb(0, 0, 0);font-family: Arial, sans-serif;font-size: 5px;font-weight: bold;\" x=\"10\" y=\"25\">{{getFileExt(file.name)}}</text>\n                    </svg>\n\n                    <div class=\"devbx-webform-file-info\">\n                        <a class=\"devbx-webform-file-upload-link\" \n                            v-if=\"file.download\" \n                            :href=\"file.download\" target=\"_blank\">\n                            {{file.name}}\n                        </a>\n                        <a class=\"devbx-webform-file-upload-link\" \n                            v-else \n                            :href=\"file.instance.blob\" target=\"_blank\">\n                            {{file.name}}\n                        </a>\n                    \n                        <div class=\"devbx-webform-file-size\">{{formatSize(file.size)}}</div>\n                    </div>\n                    \n                    <button class=\"devbx-webform-button-download\" :title=\"file.name\" v-if=\"file.instance && file.instance.active\">\n                        <progress max=\"100\" :value=\"file.instance.progress\"></progress>\n                    </button>\n\n                    <button class=\"devbx-webform-button-upload-delete\" @click.stop.prevent=\"removeFile(file)\">\n<svg viewBox=\"0 0 500 500\" xml:space=\"preserve\" style=\"width:16px;height:16px;\">\n    <circle cx=\"249.9\" cy=\"250.4\" r=\"204.7\" stroke=\"#000000\" stroke-miterlimit=\"10\"/>\n    <circle cx=\"249.9\" cy=\"247.4\" fill=\"#FFFFFF\" r=\"181.8\" stroke=\"#000000\" stroke-miterlimit=\"10\"/>\n    <line fill=\"none\" stroke=\"#000000\" stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-miterlimit=\"10\" stroke-width=\"22\" x1=\"162\" x2=\"337.8\" y1=\"159.5\" y2=\"335.3\"/>\n    <line fill=\"none\" stroke=\"#000000\" stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-miterlimit=\"10\" stroke-width=\"22\" x1=\"337.8\" x2=\"162\" y1=\"159.5\" y2=\"335.3\"/>\n</svg>\n                    </button>\n                    </div>\n                    \n        <transition name=\"devbx-webform-field\">\n            <div class=\"devbx-webform-error-message\" v-if=\"file.instance && file.instance.error\">{{file.instance.error}}</div>\n        </transition>\n                    \n                </li>\n            </ul>                    \n        </div>\n    \n        <div class=\"devbx-webform-helptext\" v-if=\"helpTextFormatted.length>0\" v-html=\"helpTextFormatted\"></div>\n\n        <transition name=\"devbx-webform-field\">\n            <div class=\"devbx-webform-error-message\" v-if=\"errorMessage\" v-html=\"errorMessage\"></div>\n        </transition>\n    </div>\n    "
    });
  });

}((this.DevBX.Forms.WebForm = this.DevBX.Forms.WebForm || {})));
//# sourceMappingURL=field.file.upload.js.map

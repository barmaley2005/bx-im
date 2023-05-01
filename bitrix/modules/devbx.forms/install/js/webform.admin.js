this.DevBX = this.DevBX || {};
this.DevBX.Forms = this.DevBX.Forms || {};
(function (exports) {
  'use strict';

  var WebFormLayoutItems = /*#__PURE__*/function () {
    function WebFormLayoutItems(app) {
      babelHelpers.classCallCheck(this, WebFormLayoutItems);
      this.app = app;
      this.items = [];
    }
    babelHelpers.createClass(WebFormLayoutItems, [{
      key: "addLayout",
      value: function addLayout(maxRowSize) {
        var config = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        var layout = new WebFormLayout(this.app, maxRowSize, config);
        this.items.push(layout);
        return layout;
      }
    }, {
      key: "deleteLayout",
      value: function deleteLayout(layout) {
        var idx = this.items.indexOf(layout);
        if (idx < 0) {
          console.error('invalid layout index');
          return;
        }
        this.deleteLayoutByIndex(idx);
      }
    }, {
      key: "deleteLayoutByIndex",
      value: function deleteLayoutByIndex(index) {
        if (index < 0 || index >= this.items.length) {
          console.error('invalid layout index');
          return;
        }
        var layout = this.items[index];
        this.items.splice(index, 1);
        layout.beforeDestroy();
      }
    }, {
      key: "getAllItems",
      value: function getAllItems() {
        var result = [];
        this.items.forEach(function (page, pageNum) {
          page.rows.forEach(function (row, rowNum) {
            result.push.apply(result, babelHelpers.toConsumableArray(row.items));
          });
        });
        return result;
      }
    }, {
      key: "getItemById",
      value: function getItemById(id) {
        var full = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
        var result = false;
        this.items.every(function (page, pageNum) {
          page.rows.every(function (row, rowNum) {
            row.items.every(function (item, index) {
              if (item.id === id) {
                result = full ? {
                  item: item,
                  index: index
                } : item;
              }
              return result === false;
            });
            if (result !== false && full) {
              result.row = row;
              result.rowNum = rowNum;
            }
            return result === false;
          });
          if (result !== false && full) {
            result.page = page;
            result.pageNum = pageNum;
          }
          return result === false;
        });
        return result;
      }
    }, {
      key: "getFields",
      value: function getFields() {
        var result = [];
        this.items.forEach(function (page) {
          result.push.apply(result, babelHelpers.toConsumableArray(page.getFields()));
        });
        return result;
      }
    }]);
    return WebFormLayoutItems;
  }();
  var WebFormPages = /*#__PURE__*/function () {
    function WebFormPages(app) {
      babelHelpers.classCallCheck(this, WebFormPages);
      this.app = app;
      this.items = [];
    }
    babelHelpers.createClass(WebFormPages, [{
      key: "addLayout",
      value: function addLayout(maxRowSize) {
        var config = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
        var layout = this.app.layoutItems.addLayout(maxRowSize, config);
        this.items.push(layout);
        return layout;
      }
    }, {
      key: "getLayoutById",
      value: function getLayoutById(id) {
        var result = false;
        this.items.every(function (page) {
          if (page.id == id) {
            result = page;
            return false;
          }
          return true;
        });
        return result;
      }
    }, {
      key: "deleteLayout",
      value: function deleteLayout(layout) {
        var index = this.items.indexOf(layout);
        if (index < 0) {
          console.error('invalid layout index');
          return;
        }
        this.deleteLayoutByIndex(index);
      }
    }, {
      key: "deleteLayoutByIndex",
      value: function deleteLayoutByIndex(index) {
        if (index < 0 || index >= this.items.length) {
          console.error('invalid layout index');
          return;
        }
        var layout = this.items[index];
        this.items.splice(index, 1);
        this.app.layoutItems.deleteLayout(layout);
      }
    }]);
    return WebFormPages;
  }();
  var WebFormLayout = /*#__PURE__*/function () {
    function WebFormLayout(app, maxRowSize, config) {
      babelHelpers.classCallCheck(this, WebFormLayout);
      this._app = app;
      this._id = this.app.getNewItemId();
      this._rows = [];
      this._maxRowSize = maxRowSize;
      this._config = config;
      this._parent = false;
      this._children = [];
    }
    babelHelpers.createClass(WebFormLayout, [{
      key: "beforeDestroy",
      value: function beforeDestroy() {
        this._rows.forEach(function (row) {
          row.beforeDestroy();
        });
        this._children.forEach(function (child) {
          child.parent = false;
        });
        this._rows = [];
        this._app = false;
        this._id = false;
        this._maxRowSize = false;
        this._config = false;
        this._parent = false;
        this._children = [];
      }
    }, {
      key: "addChild",
      value: function addChild(child) {
        if (this._children.indexOf(child) >= 0) throw new Error('Already have this child');
        this._children.push(child);
      }
    }, {
      key: "removeChild",
      value: function removeChild(child) {
        var idx = this._children.indexOf(child);
        if (idx === -1) throw new Error('Not have this child');
        this._children.splice(idx, 1);
      }
    }, {
      key: "addRow",
      value: function addRow() {
        var index = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : -1;
        var row = new WebFormRow(this.app, this);
        if (index === -1) {
          this.rows.push(row);
        } else {
          this.rows.splice(index, 0, row);
        }
        return row;
      }
    }, {
      key: "checkAlignmentItems",
      value: function checkAlignmentItems() {
        var _this = this;
        var _loop = function _loop(_i) {
          var row = _this.rows[_i];

          /* если в ряду нету элементов и это не последний ряд то удаляем его */

          if (row.items.length === 0 && _i < _this.rows.length - 1) {
            _this.deleteRowByIndex(_i);
            i = _i;
            return "continue";
          }
          var leftCell = _this.app.maxRowSize,
            itemEmptyCell = false;
          row.items.forEach(function (item) {
            if (item.template === _this.app.emptyCellTemplate) {
              if (itemEmptyCell && itemEmptyCell.size > 0) {
                itemEmptyCell.size = 0;
                _this.app.delayRemoveItem(itemEmptyCell.id, 300);
              }
              itemEmptyCell = item;
            } else {
              var newSize = item.size;
              if (item.minSize > newSize) {
                newSize = item.minSize;
              }
              if (newSize > leftCell) {
                newSize = leftCell;
              }
              item.size = newSize;
              leftCell -= item.size;
            }
          });
          if (!itemEmptyCell) {
            itemEmptyCell = _this.app.getNewEmptyCell(leftCell);
            itemEmptyCell.size = leftCell;
            itemEmptyCell.setParent(row);
            row.items.push(itemEmptyCell);
          } else {
            itemEmptyCell.size = leftCell;
          }
          if (_i < _this.rows.length - 1 && row.items.length === 1 && row.items[0].template === _this.app.emptyCellTemplate) {
            _this.deleteRowByIndex(_i);
            i = _i;
            return "continue";
          }
          _i++;
          i = _i;
        };
        for (var i = 0; i < this.rows.length;) {
          var _ret = _loop(i);
          if (_ret === "continue") continue;
        }
        if (!this.rows.length) this.addRow();
        var lastRow = this.rows[this.rows.length - 1];
        if (!lastRow.items.length) {
          var itemEmptyCell = this.app.getNewEmptyCell(this.app.maxRowSize);
          itemEmptyCell.setParent(lastRow);
          lastRow.items.push(itemEmptyCell);
          this.app.activeId = itemEmptyCell.id;
        } else {
          if (lastRow.items[0].template !== this.app.emptyCellTemplate) {
            var _itemEmptyCell = this.app.getNewEmptyCell(this.app.maxRowSize),
              row = this.addRow();
            _itemEmptyCell.setParent(row);
            row.items.push(_itemEmptyCell);
            this.app.activeId = _itemEmptyCell.id;
          }
        }
      }
    }, {
      key: "getItemById",
      value: function getItemById(id) {
        var full = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
        var result = false;
        this.rows.every(function (row, rowNum) {
          row.items.every(function (item, index) {
            if (item.id === id) {
              result = full ? {
                item: item,
                index: index
              } : item;
            }
            return result === false;
          });
          if (result !== false && full) {
            result.row = row;
            result.rowNum = rowNum;
          }
          return result === false;
        });
        return result;
      }
    }, {
      key: "containsItemId",
      value: function containsItemId(id) {
        var result = false;
        this.rows.every(function (row) {
          row.items.every(function (item) {
            result = item.id === id;
            if (result) return false;

            /*
            если у компонента есть свойтсво layout
            смотри devbx.form.layout.section.js
             */

            if (item.component && item.component.childLayout) {
              result = item.component.childLayout.containsItemId(id);
            }
            return result === false;
          });
          return result === false;
        });
        return result;
      }
    }, {
      key: "haveUserFieldItems",
      value: function haveUserFieldItems() {
        var _this2 = this;
        var result = false;
        this.rows.every(function (row) {
          row.items.every(function (item) {
            result = item.template !== _this2.app.emptyCellTemplate;
            return !result;
          });
          return !result;
        });
        return result;
      }
    }, {
      key: "getData",
      value: function getData() {
        var _this3 = this;
        var data = {
          config: JSON.parse(JSON.stringify(this.config)),
          rows: []
        };
        this.rows.forEach(function (row) {
          var rowData = {
            items: []
          };
          row.items.forEach(function (item) {
            if (item.template === _this3.app.emptyCellTemplate) return;
            if (item.component && typeof item.component.saveConfig === 'function') {
              item.component.saveConfig();
            }
            rowData.items.push({
              fieldId: item.fieldId,
              size: item.size,
              config: JSON.parse(JSON.stringify(item.config))
            });
          });
          if (rowData.items.length) {
            data.rows.push(rowData);
          }
        });
        return data;
      }
    }, {
      key: "setData",
      value: function setData(data) {
        var _this4 = this;
        this.config = data.config || {};
        this._rows.forEach(function (row) {
          row.beforeDestroy();
        });
        this._rows = [];
        data.rows = data.rows || [];
        data.rows.forEach(function (rowData) {
          var row = _this4.addRow();
          rowData.items.forEach(function (item) {
            var formElement = _this4.app.getFormElementById(item.fieldId);
            if (!formElement) {
              console.error('form element not registered ' + item.fieldId);
              return;
            }
            var props = item.props ? JSON.parse(JSON.stringify(item.props)) : {},
              config = item.config ? JSON.parse(JSON.stringify(item.config)) : {};
            config = Object.assign({}, formElement.data.defaultConfig(), config);
            var rowItem = row.createItem({
              component: false,
              id: _this4.app.getNewItemId(),
              fieldId: item.fieldId,
              template: formElement.data.layoutTemplate,
              size: item.size,
              minSize: formElement.data.minSize,
              props: props,
              config: config
            });
            row.items.push(rowItem);
            rowItem.checkFieldName();
          });
        });
      }
    }, {
      key: "deleteRow",
      value: function deleteRow(row) {
        var idx = this._rows.indexOf(row);
        if (idx < 0) {
          console.error('invalid row index');
          return;
        }
        this.deleteRowByIndex(idx);
      }
    }, {
      key: "deleteRowByIndex",
      value: function deleteRowByIndex(index) {
        if (index < 0 || index >= this._rows.length) {
          console.error('invalid row index');
          return;
        }
        var row = this._rows[index];
        this._rows.splice(index, 1);
        row.beforeDestroy();
      }
    }, {
      key: "getAllItems",
      value: function getAllItems() {
        var result = [];
        this._rows.forEach(function (row) {
          row.items.forEach(function (item) {
            result.push(item);
          });
        });
        return result;
      }
    }, {
      key: "_getItemsRecursive",
      value: function _getItemsRecursive(items) {
        this._rows.forEach(function (row) {
          row.items.forEach(function (item) {
            if (item.childLayout) {
              item.childLayout._getItemsRecursive(items);
            }
            items.push(item);
          });
        });
      }
    }, {
      key: "getAllItemsWithParent",
      value: function getAllItemsWithParent() {
        if (this.parent) return this.parent.getAllItemsWithParent();
        var result = [];
        this._getItemsRecursive(result);
        return result;
      }
    }, {
      key: "getFields",
      value: function getFields() {
        var result = [];
        this.getAllItems().forEach(function (item) {
          if (item.component && typeof item.component.getFields === 'function') {
            result.push.apply(result, babelHelpers.toConsumableArray(item.component.getFields()));
          }
        });
        return result;
      }
    }, {
      key: "app",
      get: function get() {
        return this._app;
      }
    }, {
      key: "id",
      get: function get() {
        return this._id;
      }
    }, {
      key: "rows",
      get: function get() {
        return this._rows;
      }
    }, {
      key: "maxRowSize",
      get: function get() {
        return this._maxRowSize;
      },
      set: function set(value) {
        this._maxRowSize = value;
      }
    }, {
      key: "config",
      get: function get() {
        return this._config;
      },
      set: function set(value) {
        this._config = value;
      }
    }, {
      key: "parent",
      get: function get() {
        return this._parent;
      },
      set: function set(value) {
        if (this._parent) {
          this._parent.removeChild(this);
        }
        this._parent = value;
        if (this._parent) {
          this._parent.addChild(this);
        }
      }
    }]);
    return WebFormLayout;
  }();
  var WebFormRow = /*#__PURE__*/function () {
    function WebFormRow(app, layout) {
      babelHelpers.classCallCheck(this, WebFormRow);
      this.app = app;
      this.layout = layout;
      this.id = this.app.getNewItemId();
      this.items = [];
    }
    babelHelpers.createClass(WebFormRow, [{
      key: "beforeDestroy",
      value: function beforeDestroy() {
        this.items.forEach(function (item) {
          item.beforeDestroy();
        });
        this.items = [];
        this.app = false;
        this.layout = false;
        this.id = false;
      }
    }, {
      key: "deleteRow",
      value: function deleteRow() {
        this.layout.deleteRow(this);
      }
    }, {
      key: "getRowMaxReleaseSpace",
      value: function getRowMaxReleaseSpace() {
        var excludeIndex = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : -1;
        var minSize = 0;
        this.items.forEach(function (item, index) {
          if (index === excludeIndex) {
            minSize += item.size;
          } else {
            minSize += item.minSize;
          }
        });
        return Math.max(0, this.layout.maxRowSize - minSize);
      }
    }, {
      key: "getRowFreeSpace",
      value: function getRowFreeSpace() {
        var _this5 = this;
        var excludeIndex = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : -1;
        var size = 0;
        this.items.forEach(function (item, index) {
          if (index === excludeIndex || item.template === _this5.app.emptyCellTemplate) return;
          size += item.size;
        });
        return Math.max(0, this.layout.maxRowSize - size);
      }
    }, {
      key: "rowReleaseSpace",
      value: function rowReleaseSpace(fromItemIndex, needSpace) {
        var left = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
        var releasedSpace = 0;
        if (!left) {
          var item = this.items[this.items.length - 1];
          if (item.template === this.app.emptyCellTemplate) {
            var value = Math.min(needSpace, item.size);
            if (value > 0) {
              item.size = item.size - value;
              needSpace -= value;
              releasedSpace += value;
            }
          }
        }
        while (fromItemIndex >= 0 && fromItemIndex < this.items.length && needSpace > 0) {
          var _item = this.items[fromItemIndex];
          if (_item.size > _item.minSize) {
            var newSize = _item.size - needSpace;
            if (_item.minSize > newSize) newSize = _item.minSize;
            releasedSpace += _item.size - newSize;
            needSpace -= _item.size - newSize;
            _item.size = newSize;
          }
          if (left) {
            fromItemIndex--;
          } else {
            fromItemIndex++;
          }
        }
        return releasedSpace;
      }
    }, {
      key: "rowReleaseSpaceBoth",
      value: function rowReleaseSpaceBoth(fromItemIndex, needSpace) {
        var releasedSpace = this.rowReleaseSpace(fromItemIndex, needSpace);
        if (releasedSpace < needSpace) {
          releasedSpace += this.rowReleaseSpace(fromItemIndex, needSpace - releasedSpace, true);
        }
        return releasedSpace;
      }
    }, {
      key: "replaceCell",
      value: function replaceCell(idx, item) {
        var id = item.id;
        this.items[idx].beforeDestroy();
        item.setParent(this);
        this.items[idx] = item;
        this.app.checkPageItems();
        this.app.activeId = id;
        if (item.minSize > item.size) {
          var self = this;
          this.app.$nextTick(function () {
            var field = self.app.getLayoutItemById(id, true);
            if (!field) return;
            field.item.size += self.rowReleaseSpaceBoth(field.index, field.item.minSize - field.item.size);
          });
        }
      }
    }, {
      key: "insertFreeSpace",
      value: function insertFreeSpace(index) {
        var size = Math.min(3, this.getRowMaxReleaseSpace()),
          emptyCell = this.app.getNewEmptyCell(0),
          id = emptyCell.id;
        emptyCell.setParent(this);
        emptyCell.checkItems = true;
        size = this.rowReleaseSpaceBoth(index, size);
        this.items.splice(index, 0, emptyCell);
        this.app.activeId = emptyCell.id;
        var self = this;
        this.app.$nextTick(function () {
          setTimeout(function () {
            self.app.getLayoutItemById(id).size = size;
          }, 0);
        });
      }
    }, {
      key: "getItemByCellOffset",
      value: function getItemByCellOffset(offset) {
        var pos = 0,
          index = 0;
        while (index < this.items.length) {
          if (offset >= pos && offset < pos + this.items[index].size) {
            return this.items[index];
          }
          pos += this.items[index].size;
          index++;
        }
        return false;
      }
    }, {
      key: "createItem",
      value: function createItem(data) {
        var item = BX.Vue3.reactive(new WebFormItem(this.app, data));
        item.setParent(this);
        return item;
      }
    }, {
      key: "deleteItem",
      value: function deleteItem(item) {
        var idx = this.items.indexOf(item);
        if (idx < 0) {
          console.trace('invalid delete item');
          return;
        }
        this.deleteItemByIndex(idx);
      }
    }, {
      key: "deleteItemByIndex",
      value: function deleteItemByIndex(index) {
        if (index < 0 || index >= this.items.length) {
          console.trace('invalid delete index ' + index);
          return;
        }
        var item = this.items[index];
        this.items.splice(index, 1);
        item.beforeDestroy();
      }
    }]);
    return WebFormRow;
  }();
  var WebFormItem = /*#__PURE__*/function () {
    function WebFormItem(app, data) {
      babelHelpers.classCallCheck(this, WebFormItem);
      this.app = app;
      this.component = data.component || false;
      this.id = data.id || false;
      this.fieldId = data.fieldId || false;
      this.template = data.template || false;
      this.size = data.size || 0;
      this.minSize = data.minSize || 0;
      this.props = data.props || {};
      this.config = data.config;
      this.parent = false;
      this.childLayout = false;
      this.fields = false;
    }
    babelHelpers.createClass(WebFormItem, [{
      key: "beforeDestroy",
      value: function beforeDestroy() {
        this.app = false;
        this.component = false;
        this.id = false;
        this.fieldId = false;
        this.template = false;
        this.size = false;
        this.minSize = false;
        this.props = false;
        this.config = false;
        this.parent = false;
        this.childLayout = false;
      }
    }, {
      key: "deleteItem",
      value: function deleteItem() {
        this.parent.deleteItem(this);
      }
    }, {
      key: "getParent",
      value: function getParent() {
        return this.parent;
      }
    }, {
      key: "setParent",
      value: function setParent(parent) {
        this.parent = parent;
      }
    }, {
      key: "getCellSelectItem",
      value: function getCellSelectItem(direction) {
        direction = parseInt(direction);
        if (isNaN(direction) || direction === 0) return this;
        var items = this.parent.layout.getAllItemsWithParent(),
          idx = items.indexOf(this);
        if (idx < 0) return false;
        while (true) {
          idx += direction;
          if (idx < 0 || idx >= items.length) return false;
          if (items[idx].size !== 0) break;
        }
        return this.app.getLayoutItemById(items[idx].id, true);
      }
    }, {
      key: "getCellAbove",
      value: function getCellAbove() {
        var cell = this.app.getLayoutItemById(this.id, true);
        if (!cell) return false;
        var offset = 0;
        for (var i = 0; i < cell.index; i++) {
          offset += cell.row.items[i].size;
        }
        cell.rowNum--;
        if (cell.rowNum < 0) {
          /*
          cell.pageNum--;
          if (cell.pageNum < 0)
              return false;
            cell.page = this.app.pages.items[cell.pageNum];
          cell.rowNum = cell.page.rows.length - 1;
           */

          return false;
        }
        cell.row = cell.page.rows[cell.rowNum];
        cell.item = cell.row.getItemByCellOffset(offset);
        if (!cell.item) return false;
        return cell;
      }
    }, {
      key: "getCellBelow",
      value: function getCellBelow() {
        var cell = this.app.getLayoutItemById(this.id, true);
        if (!cell) return false;
        var offset = 0;
        for (var i = 0; i < cell.index; i++) {
          offset += cell.row.items[i].size;
        }
        cell.rowNum++;
        if (cell.rowNum === cell.page.rows.length) {
          cell.rowNum = 0;

          /*
          cell.pageNum++;
          if (cell.pageNum === this.app.pages.items.length)
              return false;
            cell.page = this.app.pages.items[cell.pageNum];
           */

          return false;
        }
        cell.row = cell.page.rows[cell.rowNum];
        cell.item = cell.row.getItemByCellOffset(offset);
        if (!cell.item) return false;
        return cell;
      }
    }, {
      key: "prevSibling",
      value: function prevSibling() {
        var idx = this.parent.items.indexOf(this);
        if (idx === -1) throw new Error('Item not found in parent');
        if (!idx) return false;
        return this.parent.items[idx - 1];
      }
    }, {
      key: "nextSibling",
      value: function nextSibling() {
        var idx = this.parent.items.indexOf(this);
        if (idx === -1) throw new Error('Item not found in parent');
        if (idx === this.parent.items.length - 1) return false;
        return this.parent.items[idx + 1];
      }
    }, {
      key: "checkFieldName",
      value: function checkFieldName() {
        if (this.template === this.app.emptyCellTemplate) return;
        if (!this.config.hasOwnProperty('fieldName')) return;
        var fieldName = this.config.fieldName.replace(/[^0-9a-z_]/gi, "");
        if (fieldName.charAt(0).match(/[a-z_]/i) === null) {
          fieldName = '_' + fieldName;
        }
        if (!fieldName.length) {
          fieldName = this.fieldId.charAt(0).toUpperCase() + this.fieldId.slice(1);
        }
        var fieldNameTpl = fieldName,
          fieldNum = fieldName.match(/[0-9]+$/);
        if (fieldNum) {
          fieldNameTpl = fieldNameTpl.slice(0, -fieldNum[0].length);
          fieldNum = parseInt(fieldNum[0]);
        } else {
          fieldNum = 1;
        }
        var obFields = this.app.getAllFieldsByFieldName([this]);
        while (obFields.hasOwnProperty(fieldName)) {
          fieldNum++;
          fieldName = fieldNameTpl + fieldNum;
        }
        this.config.fieldName = fieldName;
      }
    }, {
      key: "saveConfig",
      value: function saveConfig() {
        if (this.component && typeof this.component.saveConfig === 'function') {
          this.component.saveConfig();
        }
      }
    }, {
      key: "index",
      get: function get() {
        return this.parent.items.indexOf(this);
      }
    }]);
    return WebFormItem;
  }();

  function createWebFormMaster(params) {
    params.formElements.forEach(function (group) {
      group.items.forEach(function (item) {
        var defaultConfig = JSON.stringify(item.data.defaultConfig);
        item.data.defaultConfig = function () {
          return JSON.parse(defaultConfig);
        };
      });
    });
    var app = BX.Vue3.BitrixVue.createApp({
      data: function data() {
        return {
          culture: params.culture,
          formElements: params.formElements,
          defaultEntity: params.defaultEntity,
          config: params.config,
          userGroups: params.userGroups,
          formSettings: {},
          formActions: [],
          finishPage: false,
          finishPageCond: [],
          systemWebFormItem: {},
          resizeItemId: false,
          pages: new WebFormPages(this),
          layoutItems: new WebFormLayoutItems(this),
          activePageId: 0,
          tmpId: 1,
          activeId: '',
          dragItemId: '',
          resizeEvents: [],
          maxRowSize: 24,
          emptyCellTemplate: 'devbx-form-empty-cell',
          clipboardItemId: false,
          clipboardOperation: false,
          content: '',
          webFormId: params.webFormId,
          dragData: false,
          webFormComponent: 'devbx-webform-constructor'
        };
      },
      watch: {
        activeId: function activeId(val) {
          var item = this.getLayoutItemById(val);
          if (item && item.component) {
            item.component.$el.parentNode.focus();
          }
        },
        activePageId: function activePageId(val, oldVal) {
          var oldPage = this.pages.getLayoutById(oldVal),
            newPage = this.pages.getLayoutById(val);
          if (oldPage && newPage) {
            if (oldPage.containsItemId(this.activeId)) {
              oldPage.pageItemActiveId = this.activeId;
              //BX.Vue.set(oldPage, 'pageItemActiveId', this.activeId);
            }

            if (newPage.pageItemActiveId && newPage.containsItemId(newPage.pageItemActiveId)) {
              this.activeId = newPage.pageItemActiveId;
            } else {
              this.activeId = newPage.rows[0].items[0].id;
            }
          }
        },
        resizeItemId: function resizeItemId(val) {
          if (val) {
            var self = this,
              mouseUp = function mouseUp(event) {
                self.mouseUp(event);
              },
              mouseMove = function mouseMove(event) {
                self.mouseMove(event);
              };
            document.addEventListener('mouseup', mouseUp);
            document.addEventListener('mousemove', mouseMove);
            this.resizeEvents.push({
              name: 'mouseup',
              func: mouseUp
            });
            this.resizeEvents.push({
              name: 'mousemove',
              func: mouseMove
            });
          } else {
            this.resizeEvents.forEach(function (item) {
              document.removeEventListener(item.name, item.func);
            });
            this.resizeEvents = [];
          }
        },
        activeFormComponent: function activeFormComponent(val) {
          if (val) {
            if (typeof val.showPanel === 'function') {
              val.showPanel();
            }
          }
        },
        storeErrors: {
          handler: function handler(val) {
            if (val.length) {
              this.$store.commit('clearErrors');
              this.showPopupError(val);
            }
          },
          deep: true
        }
      },
      mounted: function mounted() {
        this.formSettings = this.getDefaultFormSettings();
        this.formActions = this.getDefaultFormActions();
        this.finishPage = this.getDefaultFinishPage();
        this.activePageId = this.addPage().id;
      },
      methods: {
        getNewItemId: function getNewItemId() {
          return this.tmpId++;
        },
        getDefaultFormSettings: function getDefaultFormSettings() {
          return {
            title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_UNTITLED_NAME'),
            titleHidden: false,
            description: '',
            code: '',
            progressBar: 'STEPS',
            showPageTitles: true
          };
        },
        getDefaultFormActions: function getDefaultFormActions() {
          return [{
            action: 'SUBMIT',
            title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_SUBMIT_ACTION_TITLE')
          }];
        },
        getDefaultPageConfig: function getDefaultPageConfig() {
          return JSON.parse(JSON.stringify(this.defaultEntity.page));
        },
        getDefaultFinishPage: function getDefaultFinishPage() {
          return JSON.parse(JSON.stringify(this.defaultEntity.finishPage));
        },
        addPage: function addPage() {
          var emptyItem = this.getNewEmptyCell(this.maxRowSize),
            page = this.pages.addLayout(this.maxRowSize, this.getDefaultPageConfig()),
            row = page.addRow();
          emptyItem.setParent(row);
          row.items.push(emptyItem);
          this.activeId = emptyItem.id;
          return page;
        },
        getNewEmptyCell: function getNewEmptyCell(size) {
          return BX.Vue3.reactive(new WebFormItem(this, {
            component: false,
            id: this.tmpId++,
            fieldId: 'empty',
            template: this.emptyCellTemplate,
            size: size,
            minSize: 0,
            config: {
              checkItems: false,
              deleteBlur: false
            }
          }));
        },
        mouseMove: function mouseMove(event) {
          var info = this.getLayoutItemById(this.resizeItemId, true);
          if (!info || !info.item.component) {
            this.resizeItemId = false;
            return;
          }
          var elRow = info.item.component.$el.closest('.devbx-webform-row'),
            rowRect = elRow.getBoundingClientRect(),
            cellWidth = rowRect.width / this.maxRowSize,
            size = Math.floor((event.clientX - rowRect.x) / cellWidth),
            leftCell = 0,
            rightCell = 0,
            leftItem = info.row.items[info.index - 1],
            rightItem = info.row.items[info.index];
          if (size < 0) size = 0;
          if (size > this.maxRowSize) size = this.maxRowSize;
          for (var i = 0; i < info.index - 1; i++) {
            leftCell += info.row.items[i].size;
          }
          for (var _i = info.index + 1; _i < info.row.items.length; _i++) {
            rightCell += info.row.items[_i].size;
          }
          var newLeftSize = size - leftCell,
            newRightSize = this.maxRowSize - size - rightCell;
          if (newLeftSize + leftCell + rightCell > this.maxRowSize) newLeftSize = this.maxRowSize - (leftCell + rightCell);
          if (newLeftSize < leftItem.minSize) newLeftSize = leftItem.minSize;
          if (leftItem.size === newLeftSize) return;
          if (newRightSize < rightItem.minSize) return;
          leftItem.size = newLeftSize;
          rightItem.size = newRightSize;
        },
        mouseUp: function mouseUp(event) {
          this.resizeItemId = false;
          this.checkPageItems();
        },
        getFormElementById: function getFormElementById(id) {
          var result = false;
          this.formElements.forEach(function (group) {
            group.items.forEach(function (item) {
              if (item.data.fieldId === id) {
                result = item;
                return false;
              }
              return true;
            });
            return result === false;
          });
          return result;
        },
        getLayoutItemById: function getLayoutItemById(id) {
          var full = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
          return this.layoutItems.getItemById(id, full);
        },
        getPageById: function getPageById(id) {
          return this.pages.getLayoutById(id);
        },
        checkPageItems: function checkPageItems() {
          this.layoutItems.items.forEach(function (page) {
            page.checkAlignmentItems();
          });
          if (!this.getLayoutItemById(this.activeId)) {
            this.activeId = this.pages.items[0].rows[0].items[0].id;
          }
        },
        delayRemoveItem: function delayRemoveItem(itemId, timeout) {
          var self = this;
          setTimeout(function () {
            var cell = self.getLayoutItemById(itemId, true);
            if (!cell) return;
            cell.row.items.splice(cell.index, 1);
          }, timeout);
        },
        deleteItemById: function deleteItemById(itemId) {
          var cell = this.getLayoutItemById(itemId, true);
          if (!cell) return;
          if (cell.item.config.systemId > 0) {
            var popupId;
            popupId = this.createPopupWindow({
              titleBar: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_DIALOG_DELETE_FIELD_TITLE'),
              content: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_DIALOG_DELETE_EXISTS_FIELD_MESSAGE_TEXT'),
              buttons: [new BX.PopupWindowButton({
                text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_DIALOG_BTN_DELETE_TITLE'),
                className: "popup-window-button-accept",
                events: {
                  click: BX.delegate(function () {
                    this.closePopup(popupId);
                    this.deleteItemByIdConfirmed(itemId);
                  }, this)
                }
              }), new BX.PopupWindowButton({
                text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_DIALOG_BTN_CANCEL_TITLE'),
                className: "popup-window-button-link popup-window-button-link-cancel",
                events: {
                  click: BX.delegate(function () {
                    this.closePopup(popupId);
                  }, this)
                }
              })]
            });
            return;
          }
          this.deleteItemByIdConfirmed(itemId);
        },
        deleteItemByIdConfirmed: function deleteItemByIdConfirmed(itemId) {
          var cell = this.getLayoutItemById(itemId, true);
          if (!cell) return;
          cell.row.items.splice(cell.index, 1);
          if (cell.index > 0) {
            this.activeId = cell.row.items[cell.index - 1].id;
          } else {
            if (this.rowNum > 0) {
              this.activeId = cell.page[cell.pageNum].rows[this.rowNum - 1].items[0].id;
            }
          }
          this.checkPageItems();
        },
        pasteItemFromClipboard: function pasteItemFromClipboard(destCell) {
          var clipboardCell = this.layoutItems.getItemById(this.clipboardItemId, true);
          if (!clipboardCell || !destCell) return;
          var item = clipboardCell.item;
          if (this.clipboardOperation === 'COPY') {
            if (item.component && typeof item.component.saveConfig === 'function') {
              item.component.saveConfig();
            }
            var props = JSON.parse(JSON.stringify(item.props)),
              config = JSON.parse(JSON.stringify(item.config));
            if (config.systemId) config.systemId = 0;
            item = destCell.row.createItem({
              component: false,
              id: this.tmpId++,
              fieldId: item.fieldId,
              template: item.template,
              size: item.size,
              minSize: item.minSize,
              props: props,
              config: config
            });
          } else {
            clipboardCell.row.items.splice(clipboardCell.index, 1);
          }
          if (item.size > destCell.item.size) item.size = destCell.item.size;
          destCell.row.items.splice(destCell.index, 1, item);
          item.setParent(destCell.row);
          item.checkFieldName();
          this.clipboardItemId = false;
          this.clipboardOperation = false;
          this.checkPageItems();
          this.activeId = item.id;
        },
        getAllFieldsByFieldName: function getAllFieldsByFieldName() {
          var _this = this;
          var excludeItems = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
          var result = {};
          this.layoutItems.getAllItems().forEach(function (item) {
            if (item.template === _this.emptyCellTemplate || excludeItems.indexOf(item) > -1) return;
            if (item.config.fieldName) {
              result[item.config.fieldName] = item;
            }
          });
          return result;
        },
        registerSystemWebFormItem: function registerSystemWebFormItem(id, component) {
          this.systemWebFormItem[id] = component;
        },
        unRegisterSystemWebFormItem: function unRegisterSystemWebFormItem(id) {
          delete this.systemWebFormItem[id];
          /*
          let idx = Object.values(this.systemWebFormItem).indexOf(component);
            if (idx > -1) {
              delete this.systemWebFormItem[Object.keys(this.systemWebFormItem)[idx]];
          }*/
        },
        getWebFormData: function getWebFormData() {
          var data = {
            formSettings: JSON.parse(JSON.stringify(this.formSettings)),
            formActions: JSON.parse(JSON.stringify(this.formActions)),
            pages: [],
            finishPage: this.finishPage,
            finishPageCond: this.finishPageCond
          };
          this.pages.items.forEach(function (page) {
            data.pages.push(page.getData());
          });
          return data;
        },
        setWebFormData: function setWebFormData(data) {
          var _this2 = this;
          if (!!data.formSettings) this.formSettings = data.formSettings;
          if (!!data.formActions) this.formActions = data.formActions;
          if (Array.isArray(data.pages)) {
            while (this.pages.items.length > 0) {
              this.pages.deleteLayoutByIndex(0);
            }
            data.pages.forEach(function (dataPage) {
              _this2.pages.addLayout(_this2.maxRowSize).setData(dataPage);
            });
            this.checkPageItems();
            this.activePageId = this.pages.items[0].id;
          }
          if (!!data.finishPage) {
            this.finishPage = data.finishPage;
          }
          if (!!data.finishPageCond) {
            this.finishPageCond = data.finishPageCond;
          }
        },
        formatString: function formatString(str, search, replace) {
          if (typeof str === 'undefined') return '';
          if (typeof str !== 'string') str = str.toString();
          if (!Array.isArray(search)) {
            search = [search];
            replace = [replace];
          }
          search.forEach(function (s, i) {
            str = str.replaceAll(s, replace[i]);
          });
          return str;
        },
        getDefaultPopupButtons: function getDefaultPopupButtons(popupId) {
          return [new BX.PopupWindowButton({
            text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_DIALOG_BTN_CLOSE_TITLE'),
            className: "popup-window-button-accept",
            events: {
              click: BX.delegate(function () {
                this.closePopup(popupId);
              }, this)
            }
          })];
        },
        createPopupWindow: function createPopupWindow(options) {
          var popupId = 'DevBxFormsWizardPopup';
          var popup = BX.PopupWindowManager.getPopupById(popupId);
          if (popup) popup.close();
          if (this.popup) {
            this.popup.close();
          }
          var defOptions = {
            autoHide: false,
            draggable: false,
            closeByEsc: true,
            overlay: {
              backgroundColor: 'black',
              opacity: '80'
            },
            offsetLeft: 0,
            offsetTop: 0,
            bindOptions: {
              forceBindPosition: false
            },
            //bindOnResize: true,
            content: options.content || '',
            events: {
              onPopupClose: BX.delegate(this.onPopupClose, this),
              onPopupDestroy: BX.delegate(this.onPopupDestroy, this)
            }
          };
          options = Object.assign({}, defOptions, options);
          if (!options.buttons) options.buttons = this.getDefaultPopupButtons(popupId);
          popup = new BX.PopupWindow(popupId, null, options);
          popup.show();
          return popupId;
        },
        closePopup: function closePopup(popupId) {
          var popup = BX.PopupWindowManager.getPopupById(popupId);
          if (popup) popup.close();
        },
        onPopupClose: function onPopupClose(popup) {
          popup.destroy();
        },
        onPopupDestroy: function onPopupDestroy() {},
        showPopupError: function showPopupError(error) {
          var content = [];
          if (!Array.isArray(error)) {
            error = [error];
          }
          error.forEach(function (e) {
            content.push(!!e.message ? e.message : e);
          });
          this.createPopupWindow({
            titleBar: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_DIALOG_ERROR_PAGE_TITLE'),
            content: content.join('<br>'),
            closeIcon: true,
            minWidth: 500
          });
        },
        htmlFormatFields: function htmlFormatFields(html) {
          if (!html) return '';
          var fields = this.formFieldByName;
          html = html.replace(/#([a-zA-Z0-9_\.]+)#/g, function (str, ind) {
            var field = fields[ind];
            if (!field) return str;
            var title = BX.util.htmlspecialchars(field.label);
            title = title.replace('"', '\"');
            return '<span class="bxhtmled-surrogate devbx-webform-macros-field" title="' + title + '">' + ind + '</span>';
          });
          return html;
        },
        getComponentsByName: function getComponentsByName(name, childItems) {
          var _this3 = this;
          if (typeof childItems === "undefined") childItems = this.$children;
          var result = [];
          childItems.forEach(function (item) {
            if (item.$options.name == name) {
              result.push(item);
            }
            result.push.apply(result, babelHelpers.toConsumableArray(_this3.getComponentsByName(name, item.$children)));
          });
          return result;
        }
      },
      computed: {
        activeFormComponent: function activeFormComponent() {
          if (this.systemWebFormItem[this.activeId]) return this.systemWebFormItem[this.activeId];
          var item = this.getLayoutItemById(this.activeId);
          if (item) return item.component;
          return false;
        },
        activeCellSize: function activeCellSize() {
          var item = this.getLayoutItemById(this.activeId);
          if (!item) return 0;
          return item.size;
        },
        dragItemIsActive: function dragItemIsActive() {
          return this.dragItemId.length > 0;
        },
        formFields: function formFields() {
          var result = [];
          /*
          this.layoutItems.getFields().forEach(item => {
              item.name = 'Fields.'+item.name;
              result.push(item);
          });
           */

          this.layoutItems.getAllItems().forEach(function (item) {
            if (Array.isArray(item.fields)) {
              item.fields.forEach(function (field) {
                field = JSON.parse(JSON.stringify(field));
                field.name = 'Fields.' + field.name;
                result.push(field);
              });
            }
          });
          return result;
        },
        formFieldByName: function formFieldByName() {
          var result = {};
          this.formFields.forEach(function (item) {
            result[item.name] = item;
          });
          return result;
        },
        storeErrors: function storeErrors() {
          return this.$store.state.errors;
        }
      },
      template: "\n            <div class=\"devbx-webform-builder\">\n\n                <devbx-webform-master-menu></devbx-webform-master-menu>                    \n            \n                <component :is=\"webFormComponent\"></component>\n            </div>\n"
    });
    BX.onCustomEvent('DevBxWebFormCreatedAdminMaster', [app]);
    return app;
  }

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    var store = new BX.Vue3.Vuex.createStore({
      state: {
        iblockType: [],
        iblockList: [],
        iblockSectionFields: {},
        errors: []
      },
      mutations: {
        setIblockData: function setIblockData(state, data) {
          state.iblockType = data.iblockType;
          state.iblockList = data.iblockList;
        },
        setIblockSectionFields: function setIblockSectionFields(state, data) {
          state.iblockSectionFields[data.iblockId] = data.fields;
        },
        addError: function addError(state, val) {
          var _state$errors;
          if (Array.isArray(val)) (_state$errors = state.errors).push.apply(_state$errors, babelHelpers.toConsumableArray(val));else state.errors.push(val);
        },
        clearErrors: function clearErrors(state) {
          state.errors = [];
        }
      },
      actions: {
        getIblockList: function getIblockList(context) {
          var postData = {
            lang: BX.message('LANGUAGE_ID')
          };
          BX.ajax.runAction('devbx:forms.api.webform.getIblockList', {
            data: postData
          }).then(function (response) {
            context.commit('setIblockData', response.data);
          }, function (response) {
            if (response.errors) context.commit('addError', response.errors);
          });
        },
        getIblockSectionFields: function getIblockSectionFields(context, iblockId) {
          var postData = {
            lang: BX.message('LANGUAGE_ID'),
            iblockId: iblockId
          };
          BX.ajax.runAction('devbx:forms.api.webform.getIblockSectionFields', {
            data: postData
          }).then(function (response) {
            context.commit('setIblockSectionFields', {
              iblockId: iblockId,
              fields: response.data.fields
            });
          }, function (response) {
            if (response.errors) context.commit('addError', response.errors);
          });
        }
      },
      getters: {
        iblockSectionFields: function iblockSectionFields(state) {
          return function (iblockId) {
            if (state.iblockSectionFields[iblockId] === undefined) state.iblockSectionFields[iblockId] = false;
            //BX.Vue.set(state.iblockSectionFields, iblockId, false);

            return state.iblockSectionFields[iblockId];
          };
        }
      }
    });
    app.use(store);
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-constructor', {
      props: {},
      template: "\n        <div>\n                <devbx-webform-pages-actions :pages=\"$root.pages\"></devbx-webform-pages-actions>\n\n                <div class=\"devbx-webform-main\">\n\n                    <devbx-webform-left-panel :active-component=\"$root.activeFormComponent\"></devbx-webform-left-panel>\n\n                    <div class=\"devbx-webform-content-layout\" :class=\"{'item-resize': $root.resizeItemId>0}\">\n                        <div class=\"devbx-webform-content\">\n                            <devbx-webform-settings :form-settings=\"$root.formSettings\" :pages=\"$root.pages\" :active-page-id=\"$root.activePageId\"></devbx-webform-settings>\n\n                            <transition>\n                                <devbx-form-layout-toolbar :active-component=\"$root.activeFormComponent\"\n                                                       :active-cell-size=\"$root.activeCellSize\"\n                                                       :drag-item-id=\"$root.dragItemId\"\n                                ></devbx-form-layout-toolbar>\n                            </transition>\n                            <devbx-webform-form-page v-for=\"page of $root.pages.items\" :page=\"page\" :key=\"page.id\"/>\n                        </div>\n                    </div>\n                </div>\n        </div>\n        "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-master-menu', {
      props: {},
      computed: {
        actions: function actions() {
          var result = [];
          result.push({
            component: 'devbx-webform-constructor',
            title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_MENU_ITEM_CONSTRUCTOR')
          });
          result.push({
            component: 'devbx-webform-form-settings',
            title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_MENU_ITEM_SETTINGS')
          });
          return result;
        }
      },
      methods: {
        onAction: function onAction(action) {
          this.$root.webFormComponent = action.component;
        }
      },
      template: "\n        <div class=\"devbx-webform-admin-menu\">\n            <div class=\"devbx-webform-menu-items\">\n                <span v-for=\"action in actions\" \n                    class=\"devbx-webform-admin-menu-item\" \n                    :class=\"{active: action.component == $root.webFormComponent}\"\n                    @click=\"onAction(action)\">{{action.title}}\n                    </span>\n            </div>\n                \n            <devbx-form-admin-actions></devbx-form-admin-actions>                                            \n        </div>\n        "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-settings', {
      props: ['formSettings', 'pages', 'activePageId'],
      data: function data() {
        return {
          isHover: false
        };
      },
      mounted: function mounted() {
        this.$root.registerSystemWebFormItem('settings', this);
      },
      beforeUnmount: function beforeUnmount() {
        this.$root.unRegisterSystemWebFormItem('settings');
      },
      methods: {
        onClick: function onClick() {
          this.$root.activeId = 'settings';
        },
        mouseEnter: function mouseEnter() {
          this.isHover = true;
        },
        mouseLeave: function mouseLeave() {
          this.isHover = false;
        },
        getPageTitle: function getPageTitle(page) {
          if (page.config.pageTitle) return page.config.pageTitle;
          var idx = this.pages.items.indexOf(page);
          return this.$root.formatString(this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_NAVIGATION_PAGE_TITLE'), '#NUM#', idx + 1);
        },
        pagePassed: function pagePassed(page) {
          var activePage = this.pages.getLayoutById(this.activePageId);
          return activePage.index > page.index;
        }
      },
      computed: {
        isSelected: function isSelected() {
          return this.$root.activeId == 'settings';
        },
        activeId: function activeId() {
          return this.$root.activeId;
        },
        progressPercent: function progressPercent() {
          var activePage = this.pages.getLayoutById(this.activePageId),
            idx = this.pages.items.indexOf(activePage);
          if (!activePage || idx < 0) return 0;
          if (idx === this.pages.items.length - 1) return 100;
          return 100 / this.pages.items.length * (idx + 1);
        },
        titleFormatted: function titleFormatted() {
          return this.$root.htmlFormatFields(BX.util.htmlspecialchars(this.formSettings.title));
        },
        activePage: function activePage() {
          return this.$root.pages.getLayoutById(this.activePageId);
        },
        descriptionFormatted: function descriptionFormatted() {
          return this.$root.htmlFormatFields(this.formSettings.description);
        },
        pageDescriptionFormatted: function pageDescriptionFormatted() {
          if (!this.activePage) return '';
          return this.$root.htmlFormatFields(this.activePage.config.pageDescription);
        },
        panelItems: function panelItems() {
          var panel = {
            data: this.formSettings,
            items: []
          };
          panel.items.push({
            name: 'devbx-webform-caption',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FORM_SETTINGS_TITLE')
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_SETTINGS_WEB_FORM_TITLE'),
              fieldName: 'title',
              fieldVisibleName: 'titleHidden',
              trim: true,
              defaultValue: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_UNTITLED_NAME')
            }
          });
          panel.items.push({
            name: 'devbx-webform-html-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_SETTINGS_WEB_FORM_DESCRIPTION_TITLE'),
              fieldName: 'description'
            }
          });
          if (this.pages.items.length > 1) {
            panel.items.push({
              name: 'devbx-webform-selectbox-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_SETTINGS_WEB_FORM_PAGE_NAVIGATION_TYPE_TITLE'),
                type: 'radio',
                values: [{
                  value: 'STEPS',
                  text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_SETTINGS_WEB_FORM_PAGE_NAVIGATION_TYPE_STEPS')
                }, {
                  value: 'BAR',
                  text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_SETTINGS_WEB_FORM_PAGE_NAVIGATION_TYPE_BAR')
                }, {
                  value: 'NONE',
                  text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_SETTINGS_WEB_FORM_PAGE_NAVIGATION_TYPE_NONE')
                }],
                fieldName: 'progressBar'
              }
            });
            if (this.formSettings.progressBar != 'NONE') {
              panel.items.push({
                name: 'devbx-webform-bool-field',
                props: {
                  title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_SETTINGS_WEB_FORM_SHOW_PAGE_TITLES'),
                  fieldName: 'showPageTitles'
                }
              });
            }
          }
          return panel;
        }
      },
      template: "\n        <div class=\"devbx-webform-heading\" \n        :class=\"{'devbx-webform-layout-element-hover': (isHover && activeId != 'settings'), 'devbx-webform-layout-element-active': isSelected}\" \n        @click=\"onClick\" @mouseenter=\"mouseEnter\" @mouseleave=\"mouseLeave\">\n            <h2 v-if=\"!formSettings.titleHidden\" v-html=\"titleFormatted\"></h2>\n            <div class=\"devbx-webform-description\" v-if=\"descriptionFormatted.length\" v-html=\"descriptionFormatted\"></div>\n             \n            <div class=\"devbx-webform-page-steps\"\n                :class=\"{'devbx-webform-page-steps-no-title': !formSettings.showPageTitles}\" \n                v-if=\"pages.items.length>1 && formSettings.progressBar == 'STEPS'\">\n                <ul>\n                    <li v-for=\"page in pages.items\" :class=\"{'devbx-webform-page-step-active': page.id == activePageId}\">\n                        <a><span v-if=\"formSettings.showPageTitles\">{{getPageTitle(page)}}</span></a>\n                    </li>\n                </ul>\n            </div>\n            \n            <div class=\"devbx-webform-page-progress-bar\" v-if=\"pages.items.length>1 && formSettings.progressBar == 'BAR'\">\n                <ul>\n                    <li class=\"devbx-webform-progress-bar-line\"></li>\n                    <li class=\"devbx-webform-progress-bar-pos\" :style=\"{'width': progressPercent+'%'}\"></li>\n                    <li v-for=\"page in pages.items\" :style=\"{'width': (100/pages.items.length)+'%'}\" :class=\"{\n                    'devbx-webform-page-step-active': page.id == activePageId && formSettings.showPageTitles,\n                    'devbx-webform-page-step-passed': pagePassed(page) || (page.id == activePageId && !formSettings.showPageTitles),\n                    }\">\n                        <a><span v-if=\"formSettings.showPageTitles\">{{getPageTitle(page)}}</span></a>\n                    </li>\n                </ul>\n            </div>\n            \n            <div class=\"devbx-webform-description\" v-if=\"pageDescriptionFormatted.length\" v-html=\"pageDescriptionFormatted\"></div>\n        </div>    \n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-settings-general', {
      computed: {
        userGroupsById: function userGroupsById() {
          var result = [];
          this.$root.userGroups.forEach(function (item) {
            result[item.id] = item;
          });
          return result;
        },
        viewUserGroupsValue: function viewUserGroupsValue() {
          var _this = this;
          var result = [];
          this.$root.config.viewGroups.forEach(function (id) {
            if (_this.userGroupsById[id]) {
              result.push(_this.userGroupsById[id]);
            } else {
              result.push({
                id: id,
                name: '???'
              });
            }
          });
          return result;
        },
        writeUserGroupsValue: function writeUserGroupsValue() {
          var _this2 = this;
          var result = [];
          this.$root.config.writeGroups.forEach(function (id) {
            if (_this2.userGroupsById[id]) {
              result.push(_this2.userGroupsById[id]);
            } else {
              result.push({
                id: id,
                name: '???'
              });
            }
          });
          return result;
        }
      },
      methods: {
        selectValue: function selectValue(items, item) {
          items.push(parseInt(item.id));
        },
        removeValue: function removeValue(items, item) {
          var idx = items.indexOf(parseInt(item.id));
          if (idx < 0) return;
          items.splice(idx, 1);
        }
      },
      template: "\n        <div>\n            <div class=\"devbx-webform-settings-field-3\">\n                <devbx-webform-text-field \n                    :form-data=\"$root.config\" \n                    field-name=\"name\" \n                    :title=\"$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_SETTINGS_FORM_NAME_TITLE')\" \n                    :trim=\"true\" \n                    :allow-form-fields=\"false\">\n                </devbx-webform-text-field>\n            </div>\n            \n            <div class=\"devbx-webform-settings-row\">\n                <div class=\"devbx-webform-settings-field-2\">\n                    <div class=\"devbx-webform-label\">{{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_SETTINGS_USER_VIEW_GROUP_TITLE')}}</div>            \n                    <devbx-webform-multiselect\n                        :value=\"viewUserGroupsValue\"\n                        :options=\"$root.userGroups\"\n                        label=\"name\"\n                        @select=\"selectValue($root.config.viewGroups, $event)\"\n                        @remove=\"removeValue($root.config.viewGroups, $event)\"\n                        >\n                    </devbx-webform-multiselect>            \n                </div>\n\n                <div class=\"devbx-webform-settings-field-2\">\n                    <div class=\"devbx-webform-label\">{{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_SETTINGS_USER_WRITE_GROUP_TITLE')}}</div>            \n                    <devbx-webform-multiselect\n                        :value=\"writeUserGroupsValue\"\n                        :options=\"$root.userGroups\"\n                        label=\"name\"\n                        @select=\"selectValue($root.config.writeGroups, $event)\"\n                        @remove=\"removeValue($root.config.writeGroups, $event)\"\n                        >\n                    </devbx-webform-multiselect>            \n                </div>\n            </div>\n        </div>"
    });
  });
  BX.addCustomEvent("DevBxWebFormGetSettings", function (app, items) {
    items.push({
      sort: 100,
      title: BX.message('DEVBX_WEB_FORM_SETTINGS_GENERAL_ITEM'),
      component: 'devbx-webform-settings-general'
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-finish-page-cond', {
      props: {
        page: {
          type: Object,
          required: true
        },
        pageNum: {
          type: Number
        }
      },
      computed: {
        label: function label() {
          return this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FINISH_PAGE_COND', {
            '#NUM#': this.pageNum
          });
        }
      },
      template: "\n        <div class=\"devbx-webform-finish-page-cond\">\n            <slot></slot>\n            <span class=\"devbx-webform-settings-label\">{{label}}</span>\n            \n            <div class=\"devbx-webform-settings-field-3\">\n                <devbx-webform-condition\n                    :title=\"$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FINISH_PAGE_COND_TITLE')\"\n                    :form-data=\"page\"\n                    field-name=\"showRule\"\n                    wizard-title=\"test\"             \n                    default-value=\"never\"\n                    :options=\"['when','never']\" \n                    :cond-fields=\"$root.formFields\">\n                </devbx-webform-condition>\n            </div>\n            \n            <devbx-webform-html-field :form-data=\"page\" field-name=\"content\" editor-config=\"full\"></devbx-webform-html-field>            \n        </div>\n        "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-finish-page', {
      methods: {
        addFinishPage: function addFinishPage() {
          this.$root.finishPageCond.push(this.$root.getDefaultFinishPage());
        }
      },
      template: "<div>\n            <span class=\"devbx-webform-settings-label\">{{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_DEFAULT_FINISH_PAGE')}}</span>\n            <devbx-webform-html-field :form-data=\"$root.finishPage\" field-name=\"content\" editor-config=\"full\"></devbx-webform-html-field>\n            \n            <devbx-webform-finish-page-cond v-for=\"(page, index) in $root.finishPageCond\" :page=\"page\" :page-num=\"index+1\" :key=\"index\">\n                <div class=\"devbx-webform-settings-separator\"></div>\n            </devbx-webform-finish-page-cond>\n            \n            <div class=\"devbx-webform-settings-content-center\">\n                <span class=\"ui-btn ui-btn-primary\" @click=\"addFinishPage\">{{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_ADD_FINISH_PAGE_WITH_CONDITION')}}</span>\n            </div>\n        </div>"
    });
  });
  BX.addCustomEvent("DevBxWebFormGetSettings", function (app, items) {
    items.push({
      sort: 200,
      title: BX.message('DEVBX_WEB_FORM_SETTINGS_FINISH_PAGE_ITEM'),
      component: 'devbx-webform-finish-page'
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-form-settings', {
      props: {},
      data: function data() {
        return {
          items: [],
          activeItemId: false
        };
      },
      mounted: function mounted() {
        BX.onCustomEvent('DevBxWebFormGetSettings', [this.$root, this.items]);
        this.activeItemId = this.sortedItems[0].id;
      },
      computed: {
        sortedItems: function sortedItems() {
          var result = [];
          this.items.forEach(function (item) {
            item = JSON.parse(JSON.stringify(item));
            if (!item.id) item.id = item.component;
            result.push(item);
          });
          result.sort(function (a, b) {
            if (a.sort === b.sort) return 0;
            return a.sort > b.sort ? 1 : -1;
          });
          return result;
        },
        activeItem: function activeItem() {
          var _this = this;
          var result = false;
          this.sortedItems.every(function (item) {
            if (item.id === _this.activeItemId) result = item;
            return !result;
          });
          return result;
        }
      },
      methods: {
        clickItem: function clickItem(item) {
          this.activeItemId = item.id;
        }
      },
      template: "\n        <div class=\"devbx-webform-form-settings\">\n            <div class=\"devbx-webform-form-settings-panel\">\n                <div v-for=\"item in sortedItems\" \n                    class=\"devbx-webform-form-settings-panel-item\" \n                    :class=\"{active: item.id == activeItemId}\"\n                    @click=\"clickItem(item)\"\n                    >\n                    <span>{{item.title}}</span>                \n                </div>\n            </div>\n            \n            <div class=\"devbx-webform-form-settings-content\">\n                <component v-if=\"activeItem\" :is=\"activeItem.component\" v-bind=\"activeItem.props\"></component>               \n            </div>\n        </div>\n        "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-left-panel', {
      props: ['activeComponent'],
      /*
      render(createElement, context) {
            let child = [];
            if (Array.isArray(this.items)) {
              this.items.forEach(item => {
                  child.push(createElement(item.name, {props: item.props}));
              });
          }
            return createElement('div', {
              class: {
                  'devbx-webform-left-panel': true,
              }
          }, child);
        },*/
      data: function data() {
        return {
          panelTop: '0px',
          navigationTop: '0px',
          navigationHidden: true
        };
      },
      methods: {
        hashCode: function hashCode(str) {
          var hash = 0,
            i,
            chr;
          if (str.length === 0) return hash;
          for (i = 0; i < str.length; i++) {
            chr = str.charCodeAt(i);
            hash = (hash << 5) - hash + chr;
            hash |= 0; // Convert to 32bit integer
          }

          return hash;
        }
      },
      computed: {
        panelItems: function panelItems() {
          var _this = this;
          if (!this.activeComponent) {
            return {
              data: {},
              items: []
            };
          }
          var panel = this.activeComponent.panelItems;
          if (typeof panel === "undefined" || !Array.isArray(panel.items)) {
            return {
              data: {},
              items: []
            };
          }
          panel.items.forEach(function (item) {
            if (!item.id) {
              if (item.props && item.props.fieldName) {
                item.id = _this.activeComponent.$.uid + '-' + item.props.fieldName;
              } else {
                item.id = _this.hashCode(JSON.stringify(item));
              }
            }
          });
          return panel;
        }
      },
      watch: {
        activeComponent: function activeComponent(val) {
          if (!val || !val.$el) {
            this.navigationHidden = true;
            return;
          }
          this.$nextTick(function () {
            var webFormRect = this.$el.parentNode.getBoundingClientRect(),
              componentRect = val.$el.getBoundingClientRect();
            var panelTop = 0;
            if (webFormRect.y < 0) {
              panelTop = Math.abs(webFormRect.y);
            }
            this.panelTop = panelTop + 'px';
            this.navigationHidden = false;
            this.navigationTop = Math.ceil(componentRect.y - webFormRect.y + componentRect.height / 2 - 25 - panelTop) + 'px';
          });
        }
      },
      templateOld: "\n                    <div class=\"devbx-webform-left-panel\" :style=\"{'margin-top': panelTop}\">\n                        <div class=\"devbx-webform-navigation fa fa-long-arrow-right\" \n                            :class=\"{'devbx-webform-navigation-hidden': navigationHidden}\" :style=\"{top: navigationTop}\"></div>\n\n                        <transition-group name=\"list\">\n                            <component v-for=\"item of panelItems.items\" :is=\"item.name\" v-bind=\"item.props\"\n                                       :key=\"item.id\" :form-data=\"panelItems.data\"/>\n                        </transition-group>\n                    </div>\n    ",
      template: "\n                    <div class=\"devbx-webform-left-panel\" :style=\"{'margin-top': panelTop}\">\n                        <div class=\"devbx-webform-navigation fa fa-long-arrow-right\" \n                            :class=\"{'devbx-webform-navigation-hidden': navigationHidden}\" :style=\"{top: navigationTop}\"></div>\n\n                            <component v-for=\"item of panelItems.items\" :is=\"item.name\" v-bind=\"item.props\"\n                                       :key=\"item.id\" :form-data=\"panelItems.data\"/>\n                    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-form-page', {
      props: ['page'],
      updated: function updated() {
        var item = this.page.getItemById(this.$root.activeId);
        if (item && item.component && item.component.$el) {
          item.component.$el.parentNode.focus();
        }
      },
      mounted: function mounted() {
        this.$root.registerSystemWebFormItem(this.page.id, this);
        if (this.isActive) {
          this.$nextTick(function () {
            if (this.page.rows.length > 0 && this.page.rows[0].items.length > 0) {
              this.$root.activeId = this.page.rows[0].items[0].id;
            }
          });
        }
      },
      beforeUnmount: function beforeUnmount() {
        this.$root.unRegisterSystemWebFormItem(this.page.id);
      },
      watch: {
        isActive: function isActive(val) {
        }
      },
      computed: {
        isActive: function isActive() {
          return this.$root.activePageId == this.page.id;
        }
      },
      template: "\n            <div class=\"devbx-webform-layout\" :class=\"{'devbx-webform-layout-active': isActive}\">\n                    <devbx-webform-form-row v-for=\"(row, index) in page.rows\" :key=\"row.id\" v-bind:page=\"page\" v-bind:row=\"row\"/>\n                    <devbx-webform-form-actions :page=\"page\" :form-actions=\"$root.formActions\"></devbx-webform-form-actions>\n            </div>\n            "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-form-row', {
      props: ['page', 'row'],
      data: function data() {
        return {
          hoverId: false
        };
      },
      methods: {
        elementClick: function elementClick(id) {
          this.$root.activeId = id;
        },
        mouseDown: function mouseDown(event) {
          var el = event.target.closest('.devbx-webform-layout-element').firstChild;

          //this.$root.resizeItemId = el.__vue__.$props.item.id; //vue 2
          //this.$root.resizeItemId = el.__vueParentComponent.props.item.id; //vue 3
          this.$root.resizeItemId = el.__vueParentComponent.proxy.item.id;
        },
        clickAddSpace: function clickAddSpace(event) {
          event.preventDefault();
          event.stopPropagation();
          if (this.row.getRowMaxReleaseSpace() <= 0) return;

          /* //vue 2
          let itemId = event.target.closest('.devbx-webform-layout-element').firstChild.__vue__.$props.item.id,
              cell = this.$root.getLayoutItemById(itemId, true);
             */

          var itemId = event.target.closest('.devbx-webform-layout-element').firstChild.__vueParentComponent.proxy.item.id,
            cell = this.$root.getLayoutItemById(itemId, true);
          if (!cell) return;
          cell.row.insertFreeSpace(cell.index);
        },
        dblClick: function dblClick(item) {
          item = item.prevSibling();
          if (!item) return;
          var freeSize = item.parent.rowReleaseSpace(item.index, this.$root.maxRowSize);
          item.size += freeSize;
        },
        allowAddSpace: function allowAddSpace() {
          return this.row.getRowMaxReleaseSpace() > 0;
        },
        clickAddRowSpace: function clickAddRowSpace() {
          var index = this.page.rows.indexOf(this.row);
          if (index > -1) {
            var emptyItem = this.$root.getNewEmptyCell(this.$root.maxRowSize);
            emptyItem.props.deleteBlur = true;
            this.page.addRow(index).items.push(emptyItem);
            this.$root.activeId = emptyItem.id;
          }
        },
        deleteItem: function deleteItem(item) {
          if (item.template === this.$root.emptyCellTemplate) return;
          this.$root.deleteItemById(item.id);
        },
        dragStart: function dragStart(event, item) {
          this.$root.dragData = {
            type: 'item',
            id: item.id
          };
          this.$root.dragItemId = item.id;
        },
        dragEnd: function dragEnd(event, item) {
          this.$root.dragData = false;
          this.$root.dragItemId = '';
        },
        keydown: function keydown(event, item) {
          var code = event.code.toLowerCase(),
            cell = this.$root.getLayoutItemById(item.id, true),
            nextCell;
          if (!cell) return;
          switch (code) {
            case 'delete':
              this.deleteItem(item);
              break;
            case 'arrowleft':
              nextCell = cell.item.getCellSelectItem(-1);
              if (nextCell) {
                this.$root.activeId = nextCell.item.id;
              }
              break;
            case 'arrowright':
              nextCell = cell.item.getCellSelectItem(1);
              if (nextCell) {
                this.$root.activeId = nextCell.item.id;
              }
              break;
            case 'arrowup':
              nextCell = cell.item.getCellAbove();
              if (nextCell) {
                this.$root.activeId = nextCell.item.id;
              }
              break;
            case 'arrowdown':
              nextCell = cell.item.getCellBelow();
              if (nextCell) {
                this.$root.activeId = nextCell.item.id;
              }
              break;
            default:
              return;
          }
          event.preventDefault();
          event.stopPropagation();
        },
        mouseEnter: function mouseEnter(item) {
          this.hoverId = item.id;
        },
        mouseLeave: function mouseLeave(item) {
          if (this.hoverId == item.id) {
            this.hoverId = false;
          }
        }
      },
      computed: {
        activeId: function activeId() {
          return this.$root.activeId;
        }
      },
      directives: {
        selectable: {
          mounted: function mounted(el, binding, vnode, old) {
            var component = el.__vueParentComponent.proxy;
            component.item.component = component;
            if (binding.instance.activeId === component.item.id) {
              el.parentNode.focus();
            }

            /*
            vnode.componentInstance.item.component = vnode.componentInstance;
              if (vnode.context.activeId === vnode.componentInstance.item.id) {
                el.parentNode.focus();
            }
             */
          },
          unmounted: function unmounted(el, binding, vnode, old) {
            var component = el.__vueParentComponent.proxy;
            if (component.item.component === component) {
              component.item.component = false;
            }

            /*
            if (vnode.componentInstance.item.component === vnode.componentInstance) {
                vnode.componentInstance.item.component = false;
            }
             */
          }
        }
      },

      template: "\n                <div class=\"devbx-webform-row\">\n                    <div class=\"devbx-webform-layout-quick-insert-row\">\n                            <div class=\"devbx-webform-plus\" @click.stop.prevent=\"clickAddRowSpace\"><i class=\"fa fa-plus\"></i></div>\n                            <div class=\"devbx-webform-line\" @click.stop.prevent=\"clickAddRowSpace\"></div>\n                    </div>\n                    \n                    <div class=\"devbx-webform-fields\">\n                        <div :class=\"{\n                            'devbx-webform-layout-element': true, \n                            'devbx-webform-layout-element-hover': (hoverId == item.id && activeId != item.id), \n                            'devbx-webform-layout-element-active': activeId == item.id\n                            }\" \n                        v-for=\"(item,index) of row.items\" \n                        :key=\"item.id\" \n                        :data-colspan=\"item.size\" \n                        tabindex=\"0\" \n                        :draggable=\"item.fieldId != 'empty'\" \n                        @click.stop.prevent=\"elementClick(item.id)\"\n                        @keydown=\"keydown($event, item)\"\n                        @dragstart.stop=\"dragStart($event, item)\"\n                        @dragend=\"dragEnd($event, item)\"\n                        @mouseenter=\"mouseEnter(item)\"\n                        @mouseleave=\"mouseLeave(item)\"\n                        >\n                            <component \n                            :is=\"item.template\" \n                            v-selectable \n                            v-bind=\"item.props\" \n\t\t\t                :config=\"item.config\"\n                            :page=\"page\" \n                            :row=\"row\" \n                            :item=\"item\" \n                            :active=\"activeId == item.id\"\n                            />\n                            <div class=\"devbx-webform-layout-resizer\" @mousedown.stop.prevent=\"mouseDown($event)\" @dblclick.stop.prevent=\"dblClick(item)\"></div>\n                            <div class=\"devbx-webform-layout-quick-insert\">\n                                <div class=\"devbx-webform-plus\" @click.stop.prevent=\"clickAddSpace\" v-if=\"item.template != 'devbx-form-empty-cell' && allowAddSpace()\"><i class=\"fa fa-plus\"></i></div>\n                                <div class=\"devbx-webform-line\"></div>\n                            </div>\n                        </div>\n                    </div>\n                </div>\n"
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-caption', {
      props: ['title', 'formData'],
      template: "\n<div class=\"devbx-webform-caption\">\n    <h5>{{title}}</h5>\n</div>\n"
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-separator', {
      props: ['title', 'formData'],
      template: "\n<div class=\"devbx-webform-separator\">\n    {{title}}\n</div>\n"
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-text-field', {
      props: ['formData', 'fieldName', 'title', 'multiline', 'fieldVisibleName', 'live', 'trim', 'defaultValue', 'allowFormFields', 'readonly'],
      methods: {
        onInput: function onInput(value) {
          if (this.live === false) return;
          this.formData[this.fieldName] = value;
        },
        onChange: function onChange(value) {
          if (this.trim === true) {
            value = value.trim();
          }
          if (!value.length && this.defaultValue !== undefined) {
            value = this.defaultValue;
          }
          this.formData[this.fieldName] = value;
        }
      },
      template: "\n<div class=\"devbx-webform-field\">\n    <div class=\"devbx-webform-label\">{{title}}</div>\n    <div class=\"devbx-webform-edit\" :class=\"{'devbx-webform-edit-with-checkbox': !!fieldVisibleName}\">\n        <div class=\"devbx-webform-field-hide-value\" v-if=\"fieldVisibleName\">\n            <input type=\"checkbox\" v-model=\"formData[fieldVisibleName]\" id=\"visibleCheckbox\">\n            <label class=\"fa\" for=\"visibleCheckbox\"></label>               \n        </div>\n        \n        <textarea v-if=\"multiline\" \n            :readonly=\"readonly\"\n            @input=\"onInput($event.target.value)\" \n            @change=\"onChange($event.target.value)\"\n        >{{formData[fieldName]}}</textarea>\n        <input v-else\n            type=\"text\" \n            :value=\"formData[fieldName]\"\n            :readonly=\"readonly\"\n            @input=\"onInput($event.target.value)\" @change=\"onChange($event.target.value)\">\n    </div>\n</div>\n"
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-selectbox-field', {
      props: ['formData', 'title', 'type', 'values', 'fieldName'],
      methods: {
        setValue: function setValue(value, remove) {
          var formValue = this.formData[this.fieldName];
          if (Array.isArray(formValue)) {
            var idx = formValue.indexOf(value);
            if (remove === true) {
              if (idx > -1) {
                formValue.splice(idx, 1);
              }
            } else {
              if (idx < 0) {
                formValue.push(value);
              }
            }
          } else {
            if (remove === true) {
              formValue = '';
            } else {
              formValue = value;
            }
          }
          this.formData[this.fieldName] = formValue;
        }
      },
      computed: {
        value: function value() {
          return this.formData[this.fieldName];
        }
      },
      template: "\n<div class=\"devbx-webform-field\">\n    <div class=\"devbx-webform-label\">{{title}}</div>\n    \n    <div class=\"devbx-webform-radio-group\" v-if=\"type == 'radio'\">\n        <div class=\"devbx-webform-radio-item\" v-for=\"variant in values\">\n            <label>\n            <input \n                type=\"radio\" \n                :disabled=\"variant.disabled\" \n                :checked=\"value == variant.value\" \n                @change=\"setValue(variant.value, !$event.target.checked)\">\n            {{variant.text}}\n            </label>\n        </div>\n    </div>\n    <div class=\"devbx-webform-checkbox-group\" v-if=\"type == 'checkbox'\">\n        <div class=\"devbx-webform-checkbox-item\" v-for=\"variant in values\">\n            <label>\n            <input \n                type=\"checkbox\" \n                :checked=\"value == variant.value\"\n                :disabled=\"variant.disabled\" \n                @change=\"setValue(variant.value, !$event.target.checked)\">\n            {{variant.text}}\n            </label>\n        </div>\n    </div>\n    <div class=\"devbx-webform-edit\" v-else-if=\"type == 'select'\">\n        <div class=\"devbx-webform-select\">\n            <select @change=\"setValue($event.target.value, false)\" :value=\"value\">\n                <option  v-for=\"variant in values\" :value=\"variant.value\" :disabled=\"variant.disabled\">{{variant.text}}</option>        \n            </select>\n        </div>\n    </div>\n</div>\n"
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    var htmlEditorObj = {},
      regHtmlEditorId = {};
    app.component('devbx-webform-html-field', {
      props: ['title', 'formData', 'fieldName', 'editorConfig'],
      data: function data() {
        return {
          blurTimeout: false,
          editorInitedBeforeHandler: false,
          editorCreatedHandler: false,
          changeContentHandler: false,
          getControlsMapHandler: false,
          getDevBxFormFieldsHandler: false,
          unwatchHandler: false,
          htmlEditorId: false
        };
      },
      mounted: function mounted() {
        this.editorInitedBeforeHandler = BX.delegate(this.onEditorInitedBefore, this);
        this.editorCreatedHandler = BX.delegate(this.editorCreated, this);
        this.changeContentHandler = BX.delegate(this.contentChanged, this);
        this.getControlsMapHandler = BX.delegate(this.getControlsMap, this);
        this.getDevBxFormFieldsHandler = BX.delegate(this.getDevBxFormFields, this);
        this.htmlEditorId = this.fieldName;
        var nextId = 0;
        while (regHtmlEditorId[this.htmlEditorId]) {
          nextId++;
          this.htmlEditorId = this.fieldName + '_' + nextId;
        }
        regHtmlEditorId[this.htmlEditorId] = true;
        BX.addCustomEvent(window, "OnEditorInitedBefore", this.editorInitedBeforeHandler);
        BX.addCustomEvent(window, "OnEditorCreated", this.editorCreatedHandler);
        BX.ajax.runAction('devbx:forms.api.webform.getHtmlEditor', {
          data: {
            name: this.htmlEditorId,
            config: this.editorConfig ? this.editorConfig : 'simple'
          }
        }).then(BX.delegate(this.getHtmlEditorSuccess, this), BX.delegate(this.getHtmlEditorError, this));
      },
      beforeUnmount: function beforeUnmount() {
        regHtmlEditorId[this.htmlEditorId] = false;
        if (htmlEditorObj[this.htmlEditorId]) {
          BX.removeCustomEvent(htmlEditorObj[this.htmlEditorId], "OnContentChanged", this.changeContentHandler);
          BX.removeCustomEvent(htmlEditorObj[this.htmlEditorId], "GetControlsMap", this.getControlsMapHandler);
          BX.removeCustomEvent(htmlEditorObj[this.htmlEditorId], "GetDevBxFormFiles", this.getDevBxFormFieldsHandler);
          this.formData[this.fieldName] = htmlEditorObj[this.htmlEditorId].GetContent();
          htmlEditorObj[this.htmlEditorId].Destroy();
          htmlEditorObj[this.htmlEditorId] = false;
        }
        BX.removeCustomEvent(window, "OnEditorInitedBefore", this.editorInitedBeforeHandler);
        BX.removeCustomEvent(window, "OnEditorCreated", this.editorCreatedHandler);
        this.setWatch(false);
      },
      methods: {
        getHtmlEditorSuccess: function getHtmlEditorSuccess(response) {
          var ob = BX.processHTML(response.data.content + response.data.css + response.data.js);
          if (this.$refs.edit) {
            this.$refs.edit.innerHTML = ob.HTML;
            if (ob.STYLE.length > 0) BX.loadCSS(ob.STYLE);
            BX.ajax.processScripts(ob.SCRIPT, true);
            BX.ajax.processScripts(ob.SCRIPT, false);
          }
        },
        getHtmlEditorError: function getHtmlEditorError(response) {},
        onEditorInitedBefore: function onEditorInitedBefore(editor) {
          if (editor.id == this.htmlEditorId) {
            htmlEditorObj[this.htmlEditorId] = editor;
            BX.addCustomEvent(htmlEditorObj[this.htmlEditorId], "OnContentChanged", this.changeContentHandler);
            BX.addCustomEvent(htmlEditorObj[this.htmlEditorId], "GetControlsMap", this.getControlsMapHandler);
            BX.addCustomEvent(htmlEditorObj[this.htmlEditorId], "GetDevBxFormFiles", this.getDevBxFormFieldsHandler);
            this.setWatch(true);
          }
        },
        getDevBxFormFields: function getDevBxFormFields(items) {
          items.push.apply(items, babelHelpers.toConsumableArray(this.$root.formFields));
        },
        getControlsMap: function getControlsMap(controlsMap) {},
        editorCreated: function editorCreated(editor) {
          if (editor.id == this.htmlEditorId) {
            var iframe = editor.dom.iframeCont;
            iframe.firstChild.contentDocument.head.insertAdjacentHTML('beforeend', '<link rel="stylesheet" href="/bitrix/css/devbx.forms/htmleditor.iframe.css">');
            editor.SetContent(this.formData[this.fieldName]);
            var self = this;
            setTimeout(function () {
              if (htmlEditorObj[self.htmlEditorId]) {
                htmlEditorObj[self.htmlEditorId].synchro.FullSyncFromIframe();
              }
            }, 100);
          }
        },
        contentChanged: function contentChanged(html, editorContent) {
          console.log('contentChanged');
          this.setWatch(false);
          this.formData[this.fieldName] = html;
          this.setWatch(true);
        },
        getEditorClass: function getEditorClass() {
          return 'html-editor-config-' + (this.editorConfig ? this.editorConfig : 'simple');
        },
        setWatch: function setWatch(value) {
          if (value) {
            if (!this.unwatchHandler) {
              this.unwatchHandler = this.$watch(function () {
                return this.formData[this.fieldName];
              }, function (val, oldVal) {
                if (htmlEditorObj[this.htmlEditorId]) {
                  htmlEditorObj[this.htmlEditorId].SetContent(val);
                  var self = this;
                  setTimeout(function () {
                    self.htmlEditor.synchro.FullSyncFromIframe();
                  }, 50);
                }
              });
            }
          } else {
            if (this.unwatchHandler) {
              this.unwatchHandler();
              this.unwatchHandler = false;
            }
          }
        }
      },
      template: "\n<div class=\"devbx-webform-field\">\n    <div class=\"devbx-webform-label\">{{title}}</div>\n    <div ref=\"edit\" class=\"devbx-webform-edit\" :class=\"getEditorClass()\">\n    \n</div>\n</div>\n"
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-range-field', {
      props: {
        'formData': {
          type: Object,
          required: true
        },
        'title': {
          type: String,
          "default": ''
        },
        'readonly': {
          type: Boolean,
          "default": false
        },
        'fieldMinName': {
          type: String,
          required: true
        },
        'fieldMaxName': {
          type: String,
          required: true
        },
        'decimalPlaces': {
          "default": null
        }
      },
      computed: {
        displayValueMin: function displayValueMin() {
          return this.getDisplayValue(this.fieldMinName);
        },
        displayValueMax: function displayValueMax() {
          return this.getDisplayValue(this.fieldMaxName);
        }
      },
      methods: {
        onChange: function onChange(fieldName, value) {
          value = value.replaceAll(this.$root.culture.numberThousandsSeparator, '');
          value = value.replaceAll(this.$root.culture.numberDecimalSeparator, '.');
          if (this.decimalPlaces <= 0) {
            value = parseInt(value.trim(), 10);
          } else {
            value = parseFloat(value);
            if (!isNaN(value)) value = parseFloat(value.toFixed(this.decimalPlaces));
          }
          if (!isNaN(value)) {
            if (this.maxValue !== null && value > this.maxValue) value = this.maxValue;
            if (this.minValue !== null && value < this.minValue) value = this.minValue;
          } else {
            if (this.allowEmpty || this.minValue === null) {
              value = '';
            } else {
              value = this.minValue;
            }
          }
          this.formData[fieldName] = value;
        },
        getDisplayValue: function getDisplayValue(fieldName) {
          var value = parseFloat(this.formData[fieldName]);
          if (isNaN(value)) return this.formData[fieldName];
          value = value.toFixed(this.decimalPlaces > 0 ? this.decimalPlaces : 0);
          var parts = value.toString().split(".");
          return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, this.$root.culture.numberThousandsSeparator) + (parts[1] ? this.$root.culture.numberDecimalSeparator + parts[1] : "");
        }
      },
      template: "\n<div class=\"devbx-webform-field\">\n    <div class=\"devbx-webform-label\">{{title}}</div>\n    <div class=\"devbx-webform-edit devbx-webform-range-field\">\n    <span>\n        <input\n            type=\"text\"\n            :readonly=\"readonly\"\n            :style=\"{'width':'120px', 'display': 'inline-block'}\"\n            :placeholder=\"$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_RANGE_MINIMUM')\"\n            :value=\"displayValueMin\"\n            @change=\"onChange(fieldMinName, $event.target.value)\">\n    </span>\n    {{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_RANGE_TO')}}\n    <span>\n        <input\n            type=\"text\"\n            :readonly=\"readonly\"\n            :style=\"{'width':'120px', 'display': 'inline-block'}\"\n            :placeholder=\"$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_RANGE_MAXIMUM')\"\n            :value=\"displayValueMax\"\n            @change=\"onChange(fieldMaxName, $event.target.value)\">\n    </span>\n            \n    </div>\n</div>\n"
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-select-options-field', {
      props: ['formData', 'title', 'assignValues', 'fieldName', 'multiple'],
      data: function data() {
        return {
          dragItem: false
        };
      },
      methods: {
        selectItem: function selectItem(targetIndex, checked) {
          if (!checked) {
            this.formData[this.fieldName][targetIndex].selected = false;
            return;
          }
          if (this.multiple) {
            this.formData[this.fieldName][targetIndex].selected = true;
          } else {
            this.formData[this.fieldName].forEach(function (item, index) {
              item.selected = index === targetIndex;
            });
          }
        },
        setItemText: function setItemText(index, text) {
          this.formData[this.fieldName][index].text = text;
        },
        deleteItem: function deleteItem(index) {
          if (this.formData[this.fieldName].length <= 1) return;
          this.formData[this.fieldName].splice(index, 1);
        },
        addItem: function addItem(index) {
          this.formData[this.fieldName].splice(index + 1, 0, {
            text: '',
            value: '',
            selected: false
          });
        },
        setItemValue: function setItemValue(index, value) {
          this.formData[this.fieldName][index].value = value;
        },
        dragStart: function dragStart(event, item) {
          this.dragItem = item;
        },
        dragEnter: function dragEnter(event, item) {
          if (!this.dragItem) return;
          event.preventDefault();
          if (this.dragItem === item) return;
          var oldIndex = this.formData[this.fieldName].indexOf(this.dragItem),
            newIndex = this.formData[this.fieldName].indexOf(item);
          this.formData[this.fieldName].splice(oldIndex, 1);
          this.formData[this.fieldName].splice(newIndex, 0, this.dragItem);
        },
        dragOver: function dragOver(event, item) {
          if (!this.dragItem) return;
          event.preventDefault();
        },
        dragEnd: function dragEnd(event, item) {
          this.dragItem = false;
        },
        drop: function drop(event) {}
      },
      computed: {
        value: function value() {
          return this.formData[this.fieldName];
        }
      },
      template: "\n<div class=\"devbx-webform-field\">\n    <div class=\"devbx-webform-label\">{{title}}</div>\n    \n    <table :class=\"{'devbx-webform-selection-values-tbl': true, 'devbx-webform-selection-values-del-disabled': value.length<=1}\">\n        <tr v-for=\"(item, index) in value\" draggable=\"true\" \n        @dragstart=\"dragStart($event, item)\" \n        @dragenter=\"dragEnter($event, item)\"\n        @dragover=\"dragOver\" \n        @dragend=\"dragEnd($event, item)\" \n        @drop=\"drop\">\n            <td class=\"devbx-webform-selection-values-select\">\n                   <span class=\"devbx-webform-selection-values-drag\"><i class=\"fa fa-ellipsis-v\"></i></span>            \n                   <label><input type=\"checkbox\" :checked=\"item.selected\" @change=\"selectItem(index, $event.target.checked)\"></label>\n            </td>\n            <td class=\"devbx-webform-selection-values-text\">\n                    <input type=\"text\" :value=\"item.text\" @input=\"setItemText(index, $event.target.value)\">            \n            </td>\n            <td class=\"devbx-webform-selection-values-value\" v-if=\"assignValues\">\n                    <input type=\"text\" :value=\"item.value\" @input=\"setItemValue(index, $event.target.value)\">            \n            </td>\n            <td class=\"devbx-webform-selection-values-actions\">\n                <a class=\"devbx-webform-selection-values-action-remove\" href=\"#\" @click.prevent=\"deleteItem(index)\"><i class=\"fa fa-trash\"></i></a>            \n                <a href=\"#\" @click.prevent=\"addItem(index)\"><i class=\"fa fa-plus\"></i></a>\n            </td>        \n        </tr>\n    </table>\n</div>\n"
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-bool-field', {
      props: {
        formData: {
          required: true
        },
        title: {
          type: String
        },
        fieldName: {
          type: String,
          required: true
        },
        readonly: {
          "default": false
        }
      },
      template: "\n<div class=\"devbx-webform-field\">\n    <div class=\"devbx-webform-label\"></div>\n    <div class=\"devbx-webform-edit\">\n        <label>\n            <input type=\"checkbox\" v-model=\"formData[fieldName]\" :disabled=\"readonly\">\n        {{title}}\n        </label>\n    </div>\n</div>\n"
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-number-field', {
      props: {
        'formData': {
          type: Object,
          required: true
        },
        'fieldName': {
          type: String,
          required: true
        },
        'title': {
          type: String,
          "default": ''
        },
        'readonly': {
          type: Boolean,
          "default": false
        },
        'minValue': {
          "default": null
        },
        'maxValue': {
          "default": null
        },
        'allowEmpty': {
          type: Boolean,
          "default": true
        },
        'decimalPlaces': {
          "default": null
        }
      },
      methods: {
        onChange: function onChange(value) {
          value = value.replaceAll(this.$root.culture.numberThousandsSeparator, '');
          value = value.replaceAll(this.$root.culture.numberDecimalSeparator, '.');
          if (this.decimalPlaces <= 0) {
            value = parseInt(value.trim(), 10);
          } else {
            value = parseFloat(value);
            if (!isNaN(value)) value = parseFloat(value.toFixed(this.decimalPlaces));
          }
          if (!isNaN(value)) {
            if (this.maxValue !== null && value > this.maxValue) value = this.maxValue;
            if (this.minValue !== null && value < this.minValue) value = this.minValue;
          } else {
            if (this.allowEmpty || this.minValue === null) {
              value = '';
            } else {
              value = this.minValue;
            }
          }
          this.formData[this.fieldName] = value;
        }
      },
      computed: {
        displayValue: function displayValue() {
          var value = parseFloat(this.formData[this.fieldName]);
          if (isNaN(value)) return this.formData[this.fieldName];
          value = value.toFixed(this.decimalPlaces > 0 ? this.decimalPlaces : 0);
          var parts = value.toString().split(".");
          return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, this.$root.culture.numberThousandsSeparator) + (parts[1] ? this.$root.culture.numberDecimalSeparator + parts[1] : "");
        }
      },
      template: "\n<div class=\"devbx-webform-field\">\n    <div class=\"devbx-webform-label\">{{title}}</div>\n    <div class=\"devbx-webform-edit\">\n        <input\n            type=\"text\" \n            :value=\"displayValue\"\n            :readonly=\"readonly\"\n            @change=\"onChange($event.target.value)\">\n    </div>\n</div>\n"
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-multiselect', {
      props: {
        value: {
          type: null,
          "default": function _default() {
            return [];
          }
        },
        label: {
          type: String,
          "default": 'label'
        },
        options: {
          type: null,
          "default": function _default() {
            return [];
          }
        }
      },
      data: function data() {
        return {
          showSearch: false,
          searchValue: '',
          events: [],
          dropDownPosition: false,
          timer: false
        };
      },
      mounted: function mounted() {
        this.bindEvent(document, 'focusin', BX.delegate(this.focusIn, this));
        this.bindEvent(document, 'focusout', BX.delegate(this.focusOut, this));
        this.bindEvent(document, 'click', BX.delegate(this.documentClick, this));
        this.bindEvent(window, 'resize', BX.delegate(this.onResize, this));
        this.bindEvent(document, 'scroll', BX.delegate(this.boundsCheck, this));
        this.timer = setInterval(BX.delegate(this.boundsCheck, this), 100);
      },
      beforeUnmount: function beforeUnmount() {
        clearInterval(this.timer);
        this.events.forEach(function (item) {
          item.el.removeEventListener(item.type, item.callback);
        });
      },
      computed: {
        searchOptions: function searchOptions() {
          var _this = this;
          var result = [],
            searchText = this.searchValue.toUpperCase();
          Object.values(this.options).forEach(function (option) {
            if (searchText.length > 0 && option[_this.label].toUpperCase().indexOf(searchText) < 0) return;
            result.push({
              id: option.id,
              option: option,
              selected: _this.value.indexOf(option) >= 0
            });
          });
          return result;
        },
        inputWidth: function inputWidth() {
          return 2 + this.searchValue.length + 'ch';
        }
      },
      methods: {
        boundsCheck: function boundsCheck() {
          var rect = this.$refs.selValues.getBoundingClientRect();
          this.$refs.fakeInput.style.height = rect.height + 'px';
          var minWidth = 0;
          this.$el.querySelectorAll('.devbx-webform-select-input-value-tag').forEach(function (item) {
            minWidth = Math.max(minWidth, item.getBoundingClientRect().width);
          });

          //this.$el.style.width = (minWidth + 15) + 'px';

          this.getDropDownPosition();
        },
        focusInput: function focusInput() {
          this.$refs.inputSearch.focus();
        },
        getDropDownPosition: function getDropDownPosition() {
          var inp = this.$refs.fakeInput,
            topY;
          if (!inp) {
            this.dropDownPosition = false;
            return;
          }
          var rect = inp.getBoundingClientRect();
          var elList = this.$refs.valueList;
          if (elList) {
            var listRect = elList.getBoundingClientRect();
            if (rect.y + rect.height + listRect.height >= document.body.clientHeight) {
              topY = rect.y - listRect.height;
              if (topY < 0) topY = 0;
              this.dropDownPosition = {
                position: 'fixed',
                top: topY + 'px',
                width: rect.width + 'px',
                'transform-origin': 'center bottom'
              };
              return;
            }
          }
          if (!elList && this.dropDownPosition && Object.values(this.dropDownPosition).length) return;
          this.dropDownPosition = {
            position: 'fixed',
            top: rect.y + rect.height + 'px',
            width: rect.width + 'px'
          };
        },
        selectValue: function selectValue(id) {
          var _this2 = this;
          Object.values(this.options).forEach(function (option) {
            if (option.id === id) _this2.$emit('select', option);
          });
        },
        removeValue: function removeValue(id) {
          var _this3 = this;
          Object.values(this.options).forEach(function (option) {
            if (option.id === id) _this3.$emit('remove', option);
          });
        },
        toggleValue: function toggleValue(id) {
          var _this4 = this;
          var result = false;
          this.value.every(function (item) {
            if (item.id !== id) return true;
            _this4.$emit('remove', item);
            result = true;
            return false;
          });
          if (result) return;
          Object.values(this.options).forEach(function (item) {
            if (item.id === id) {
              _this4.$emit('select', item);
            }
          });
        },
        bindEvent: function bindEvent(el, type, callback) {
          this.events.push({
            el: el,
            type: type,
            callback: callback
          });
          el.addEventListener(type, callback);
        },
        focusIn: function focusIn() {
          if (!this.showSearch && this.$el.contains(document.activeElement)) {
            this.showSearch = true;
          } else if (this.showSearch && !this.$el.contains(document.activeElement)) {
            this.showSearch = false;
          }
        },
        focusOut: function focusOut() {
          if (document.activeElement === document.body) return;
          if (this.showSearch && !this.$el.contains(document.activeElement)) {
            this.showSearch = false;
          }
        },
        documentClick: function documentClick(e) {
          if (!this.showSearch) return;
          if (!this.$el.contains(e.target)) {
            this.showSearch = false;
          }
        },
        onResize: function onResize() {
          if (this.showSearch) this.getDropDownPosition();
        },
        keydown: function keydown(event) {
          var options = Object.values(this.options);
          if (event.keyCode === 8 && !this.$refs.inputSearch.value.length && options.length) {
            this.removeValue(options[options.length - 1].id);
          }
        }
      },
      watch: {
        searchValue: function searchValue() {
          this.boundsCheck();
        },
        showSearch: function showSearch(val) {
          if (val) {
            this.getDropDownPosition();
          }
        }
      },
      template: "\n    <div class=\"devbx-webform-select-value devbx-webform-select-value-multiple\" @click=\"focusInput\">\n        <div class=\"devbx-webform-select-input-value\">\n\n           <div class=\"devbx-webform-select-input-multiple\">\n                <div ref=\"selValues\" class=\"devbx-webform-select-input-sel-values\">\n                    <span>\n                        <span v-for=\"option in value\" class=\"devbx-webform-select-input-value-tag\" :class=\"option.cssClass\" :key=\"option.id\">\n                            <span>{{option[label]}}</span>\n                            <i class=\"devbx-webform-select-remove-value fa fa-close\" @click.stop.prevent=\"removeValue(option.id)\"></i>\n                        </span>                                       \n                        <input ref=\"inputSearch\" type=\"text\" v-model=\"searchValue\" :style=\"{width: inputWidth}\" @keydown=\"keydown($event)\">\n                    </span>\n                </div>\n                <div class=\"devbx-webform-select-input-fake\">\n                    <input ref=\"fakeInput\" type=\"text\" autocomplete=\"off\">\n                    <span></span>\n                </div>\n           </div>        \n                    \n           <transition name=\"devbx-dropdown\">\n           <div class=\"devbx-webform-select-dropdown\" v-if=\"showSearch && searchOptions.length\" :style=\"dropDownPosition\">\n                <ul ref=\"valueList\" class=\"devbx-webform-select-value-list\">\n                    <li v-for=\"searchValue in searchOptions\" \n                        :key=\"searchValue.id\" \n                        :class=\"searchValue.option.cssClass + (searchValue.selected ? ' devbx-webform-select-value-dropdown-selected': '')\"\n                        @click.stop.prevent=\"toggleValue(searchValue.id)\"><span>{{searchValue.option[label]}}</span></li>\n                </ul>\n            </div>\n            </transition>\n        </div>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-customselect', {
      props: {
        value: {
          type: null,
          "default": function _default() {
            return '';
          }
        },
        valueType: {
          type: String,
          "default": function _default() {
            return 'string';
          }
        },
        valueKey: {
          type: String,
          "default": 'value'
        },
        valueTypeKey: {
          type: String,
          "default": 'valueType'
        },
        labelKey: {
          type: String,
          "default": 'label'
        },
        options: {
          type: Array,
          required: true
        },
        defaultValueType: {
          type: String,
          "default": 'string'
        }
      },
      data: function data() {
        return {
          showSearch: false,
          events: [],
          dropDownPosition: false
        };
      },
      mounted: function mounted() {
        this.bindEvent(document, 'focusin', BX.delegate(this.focusIn, this));
        this.bindEvent(document, 'focusout', BX.delegate(this.focusOut, this));
        this.bindEvent(document, 'click', BX.delegate(this.documentClick, this));
        this.bindEvent(window, 'resize', BX.delegate(this.onResize, this));
      },
      beforeUnmount: function beforeUnmount() {
        this.events.forEach(function (item) {
          item.el.removeEventListener(item.type, item.callback);
        });
      },
      computed: {
        curValueDisplayName: function curValueDisplayName() {
          var _this = this;
          if (this.valueType === this.defaultValueType) return this.value;
          var result = '?';
          this.options.every(function (item) {
            if (item[_this.valueTypeKey] === _this.valueType && item[_this.valueKey] === _this.value) {
              result = item.label;
              return false;
            }
            return true;
          });
          return result;
        },
        searchValueList: function searchValueList() {
          var _this2 = this;
          if (this.valueType !== this.defaultValueType) return [];
          var result = [],
            searchText = this.value.toString().toUpperCase();
          this.options.forEach(function (item) {
            var label = item.label;
            if (searchText.length > 0 && label.toUpperCase().indexOf(searchText) < 0) return;
            result.push({
              value: item[_this2.valueKey],
              valueType: item[_this2.valueTypeKey],
              label: label,
              cssClass: item.cssClass
            });
          });
          return result;
        }
      },
      methods: {
        getDropDownPosition: function getDropDownPosition() {
          var inp = this.$refs.input;
          if (!inp) {
            this.dropDownPosition = false;
            return;
          }
          var rect = inp.getBoundingClientRect();
          this.dropDownPosition = {
            position: 'fixed',
            top: rect.y + rect.height + 'px',
            width: rect.width + 'px'
          };
        },
        selectValue: function selectValue(item) {
          this.$emit('select', {
            value: item.value,
            valueType: item.valueType
          });
        },
        removeValue: function removeValue() {
          this.$emit('select', {
            value: '',
            valueType: this.defaultValueType
          });
        },
        bindEvent: function bindEvent(el, type, callback) {
          this.events.push({
            el: el,
            type: type,
            callback: callback
          });
          el.addEventListener(type, callback);
        },
        focusIn: function focusIn() {
          if (!this.showSearch && this.$el.contains(document.activeElement)) {
            this.showSearch = true;
          } else if (this.showSearch && !this.$el.contains(document.activeElement)) {
            this.showSearch = false;
          }
        },
        focusOut: function focusOut() {
          if (document.activeElement === document.body) return;
          if (this.showSearch && !this.$el.contains(document.activeElement)) {
            this.showSearch = false;
          }
        },
        documentClick: function documentClick(e) {
          if (!this.showSearch) return;
          if (!this.$el.contains(e.target)) {
            this.showSearch = false;
          }
        },
        onResize: function onResize() {
          if (this.showSearch) this.getDropDownPosition();
        },
        onInput: function onInput(event) {
          this.$emit('select', {
            value: event.target.value,
            valueType: this.defaultValueType
          });
        }
      },
      watch: {
        showSearch: function showSearch(val) {
          if (val) {
            this.getDropDownPosition();
          }
        }
      },
      template: "\n    <div class=\"devbx-webform-select-value\">\n        <div v-if=\"valueType == defaultValueType\" class=\"devbx-webform-select-input-value\">\n           <input ref=\"input\" class=\"devbx-webform-select-value-input\" type=\"text\" :value=\"value\" @input=\"onInput\">\n           <transition name=\"devbx-dropdown\">\n           <div class=\"devbx-webform-select-dropdown\" v-if=\"showSearch && searchValueList.length\" :style=\"dropDownPosition\">\n                <ul class=\"devbx-webform-select-value-list\">\n                    <li v-for=\"searchValue in searchValueList\" :key=\"searchValue.id\" @click.stop.prevent=\"selectValue(searchValue)\">\n                        <span :class=\"searchValue.cssClass\">{{searchValue.label}}</span>\n                    </li>\n                </ul>\n            </div>\n            </transition>\n        </div>\n        <span v-else>\n            <span class=\"devbx-webform-select-value-selected\">\n                {{curValueDisplayName}} \n                <i class=\"devbx-webform-select-remove-value fa fa-close\" @click.stop.prevent=\"removeValue\"></i>\n            </span>        \n        </span>    \n    </div>\n    "
    });
  });

  var MSLang = DevBX.MSLang;
  var WebFormCond = /*#__PURE__*/function () {
    function WebFormCond(type) {
      babelHelpers.classCallCheck(this, WebFormCond);
      this._type = type;
    }
    babelHelpers.createClass(WebFormCond, [{
      key: "quoteStr",
      value: function quoteStr(str) {
        return '"' + str.replace(/"/g, "\\\"") + '"';
      }
    }, {
      key: "getCode",
      value: function getCode(formField, itemCond) {
        throw new Error('override getCond function');
      }
    }, {
      key: "getParseFunctions",
      value: function getParseFunctions() {
        return [];
      }
    }, {
      key: "getFieldFromNodeList",
      value: function getFieldFromNodeList(pos, nodeList) {
        if (nodeList[pos].nType !== MSLang.NodeType.ntContextVariable) return false;
        var name = [nodeList[pos].nValue];
        pos++;
        while (pos < nodeList.length) {
          if (nodeList[pos].nType !== MSLang.NodeType.ntObjProp) break;
          name.push(nodeList[pos].nValue);
          pos++;
        }
        return [pos, name.join('.')];
      }
    }, {
      key: "type",
      get: function get() {
        return this._type;
      }
    }, {
      key: "conditions",
      get: function get() {
        return [];
      }
    }]);
    return WebFormCond;
  }();

  var MSLang$1 = DevBX.MSLang;
  var WebFormCondString = /*#__PURE__*/function (_WebFormCond) {
    babelHelpers.inherits(WebFormCondString, _WebFormCond);
    function WebFormCondString() {
      babelHelpers.classCallCheck(this, WebFormCondString);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WebFormCondString).call(this, 'string'));
    }
    babelHelpers.createClass(WebFormCondString, [{
      key: "getCode",
      value: function getCode(formField, itemCond) {
        var value;
        switch (itemCond.valueType) {
          case 'string':
            value = this.quoteStr(itemCond.value);
            break;
          case 'field':
            value = itemCond.value;
            break;
          default:
            value = false;
            break;
        }
        switch (itemCond.type) {
          case 'isFilledOut':
            return itemCond.field + ' != ""';
          case 'isNotFilledOut':
            return itemCond.field + ' == ""';
          case 'is':
            return itemCond.field + ' == ' + value;
          case 'isNot':
            return itemCond.field + ' != ' + value;
        }
        var func = WebFormCondString.getCondFunction(itemCond);
        if (!func) return;
        if (func.negative) {
          return '!' + itemCond.field + '.' + func.name + '(' + value + ')';
        } else {
          return itemCond.field + '.' + func.name + '(' + value + ')';
        }
      }
    }, {
      key: "isCompareField",
      value: function isCompareField(pos, nodeList, fieldsByName) {
        var fieldResult = this.getFieldFromNodeList(pos, nodeList),
          fieldName;
        if (!fieldResult) return false;
        var _fieldResult = fieldResult;
        var _fieldResult2 = babelHelpers.slicedToArray(_fieldResult, 2);
        pos = _fieldResult2[0];
        fieldName = _fieldResult2[1];
        if (!fieldsByName[fieldName] || fieldsByName[fieldName].type !== 'string') return false;
        if (nodeList[pos].nType !== MSLang$1.NodeType.ntExpressionCompare) return false;
        if ([MSLang$1.CompareType.ctEqual, MSLang$1.CompareType.ctNotEqual].indexOf(nodeList[pos].nValue) < 0) return false;
        if (nodeList[pos + 1].nType !== MSLang$1.NodeType.ntIFValue) return false;
        fieldResult = this.getFieldFromNodeList(0, nodeList[pos + 1].childItems);
        if (!fieldResult) return false;
        var data = {
          field: fieldName,
          type: nodeList[pos].nValue === MSLang$1.CompareType.ctEqual ? 'is' : 'isNot',
          valueType: 'field',
          value: fieldResult[1]
        };
        return [pos + 2, data];
      }
    }, {
      key: "isCompareString",
      value: function isCompareString(pos, nodeList, fieldsByName) {
        var fieldResult = this.getFieldFromNodeList(pos, nodeList),
          fieldName;
        if (!fieldResult) return false;
        var _fieldResult3 = babelHelpers.slicedToArray(fieldResult, 2);
        pos = _fieldResult3[0];
        fieldName = _fieldResult3[1];
        if (!fieldsByName[fieldName] || fieldsByName[fieldName].type !== 'string') return false;
        if (nodeList[pos].nType !== MSLang$1.NodeType.ntExpressionCompare) return false;
        if ([MSLang$1.CompareType.ctEqual, MSLang$1.CompareType.ctNotEqual].indexOf(nodeList[pos].nValue) < 0) return false;
        if (nodeList[pos + 1].nType !== MSLang$1.NodeType.ntIFValue) return false;
        if (nodeList[pos + 1].childItems.length !== 1) return false;
        if (nodeList[pos + 1].childItems[0].nType !== MSLang$1.NodeType.ntString) return false;
        var data;
        if (!nodeList[pos + 1].childItems[0].nValue.length) {
          data = {
            field: fieldName,
            type: nodeList[pos].nValue === MSLang$1.CompareType.ctEqual ? 'isNotFilledOut' : 'isFilledOut'
          };
        } else {
          data = {
            field: fieldName,
            type: nodeList[pos].nValue === MSLang$1.CompareType.ctEqual ? 'is' : 'isNot',
            valueType: 'string',
            value: nodeList[pos + 1].childItems[0].nValue
          };
        }
        return [pos + 2, data];
      }
    }, {
      key: "isCompareFunction",
      value: function isCompareFunction(pos, nodeList, fieldsByName) {
        var negative = false,
          funcMap = {
            Contains: 'DoesNotContain',
            StartsWith: 'DoesNotStartWith',
            EndsWith: 'DoesNotEndWith'
          };
        if (nodeList[pos].nType === MSLang$1.NodeType.ntNegativeIf) {
          negative = true;
          pos++;
        }
        var fieldResult = this.getFieldFromNodeList(pos, nodeList),
          fieldName;
        if (!fieldResult) return false;
        var _fieldResult4 = fieldResult;
        var _fieldResult5 = babelHelpers.slicedToArray(_fieldResult4, 2);
        pos = _fieldResult5[0];
        fieldName = _fieldResult5[1];
        if (!fieldsByName[fieldName] || fieldsByName[fieldName].type !== 'string') return false;
        if (nodeList[pos].nType !== MSLang$1.NodeType.ntSelfFuncCall) {
          return false;
        }
        var functionName = nodeList[pos].nValue,
          data;
        if (Object.keys(funcMap).indexOf(functionName) < 0) return false;
        if (!nodeList[pos].childItems || nodeList[pos].childItems.length !== 1) return false;
        if (nodeList[pos].childItems[0].nType !== MSLang$1.NodeType.ntFuncParam) return false;
        if (!nodeList[pos].childItems[0].childItems) return false;
        if (nodeList[pos].childItems[0].childItems[0].nType === MSLang$1.NodeType.ntString) {
          data = {
            field: fieldName,
            type: negative ? funcMap[functionName] : functionName,
            valueType: 'string',
            value: nodeList[pos].childItems[0].childItems[0].nValue,
            negative: negative
          };
          pos++;
          return [pos, data];
        }
        fieldResult = this.getFieldFromNodeList(0, nodeList[pos].childItems[0].childItems);
        if (!fieldResult) return false;
        data = {
          field: fieldName,
          type: negative ? funcMap[functionName] : functionName,
          valueType: 'field',
          value: fieldResult[1],
          negative: negative
        };
        pos++;
        return [pos, data];
      }
    }, {
      key: "getParseFunctions",
      value: function getParseFunctions() {
        return [this.isCompareField.bind(this), this.isCompareString.bind(this), this.isCompareFunction.bind(this)];
      }
    }, {
      key: "conditions",
      get: function get() {
        var items = [];
        items.push({
          value: 'isFilledOut',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_FILLED_OUT'),
          comp: false
        });
        items.push({
          value: 'isNotFilledOut',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_NOT_FILLED_OUT'),
          comp: false
        });
        items.push({
          value: 'is',
          label: BX.message('DEVBX_WEB_FORM_COND_IS'),
          comp: 'devbx-webform-cond-string'
        });
        items.push({
          value: 'isNot',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_NOT'),
          comp: 'devbx-webform-cond-string'
        });
        items.push({
          value: 'Contains',
          label: BX.message('DEVBX_WEB_FORM_COND_CONTAINS'),
          comp: 'devbx-webform-cond-string'
        });
        items.push({
          value: 'DoesNotContain',
          label: BX.message('DEVBX_WEB_FORM_COND_DOES_NOT_CONTAIN'),
          comp: 'devbx-webform-cond-string'
        });
        items.push({
          value: 'StartsWith',
          label: BX.message('DEVBX_WEB_FORM_COND_STARTS_WITH'),
          comp: 'devbx-webform-cond-string'
        });
        items.push({
          value: 'DoesNotStartWith',
          label: BX.message('DEVBX_WEB_FORM_COND_DOES_NOT_START_WITH'),
          comp: 'devbx-webform-cond-string'
        });
        items.push({
          value: 'EndsWith',
          label: BX.message('DEVBX_WEB_FORM_COND_ENDS_WITH'),
          comp: 'devbx-webform-cond-string'
        });
        items.push({
          value: 'DoesNotEndWith',
          label: BX.message('DEVBX_WEB_FORM_COND_DOES_NOT_END_WITH'),
          comp: 'devbx-webform-cond-string'
        });
        return items;
      }
    }], [{
      key: "getCondFunction",
      value: function getCondFunction(item) {
        var funcMap = {
            'Contains': 'DoesNotContain',
            'StartsWith': 'DoesNotStartWith',
            'EndsWith': 'DoesNotEndWith'
          },
          idx;
        idx = Object.values(funcMap).indexOf(item.type);
        if (idx >= 0) {
          return {
            name: Object.keys(funcMap)[idx],
            negative: true
          };
        }
        if (Object.keys(funcMap).indexOf(item.type) < 0) return false;
        return {
          name: item.type,
          negative: false
        };
      }
    }]);
    return WebFormCondString;
  }(WebFormCond);

  var MSLang$2 = DevBX.MSLang;
  var WebFormCondArray = /*#__PURE__*/function (_WebFormCond) {
    babelHelpers.inherits(WebFormCondArray, _WebFormCond);
    function WebFormCondArray() {
      babelHelpers.classCallCheck(this, WebFormCondArray);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WebFormCondArray).call(this, 'array'));
    }
    babelHelpers.createClass(WebFormCondArray, [{
      key: "convertArrayCondition",
      value: function convertArrayCondition(item) {
        var _this = this;
        if (!item.value.length) return '';
        var arCond = [],
          func = WebFormCondString.getCondFunction(item);
        if (!func) return '';
        item.value.forEach(function (arValue) {
          var value;
          switch (arValue.valueType) {
            case 'fieldValue':
              value = _this.quoteStr(arValue.value);
              break;
            case 'field':
              value = arValue.value;
              break;
            default:
              return;
          }
          if (func.negative) {
            arCond.push('!' + item.field + '.' + func.name + '(' + value + ')');
          } else {
            arCond.push(item.field + '.' + func.name + '(' + value + ')');
          }
        });
        if (!arCond.length) return '';
        return '(' + arCond.join(func.negative ? ' && ' : ' || ') + ')';
      }
    }, {
      key: "getCode",
      value: function getCode(formField, itemCond) {
        switch (itemCond.type) {
          case 'isFilledOut':
            return itemCond.field + '.Count() > 0';
          case 'isNotFilledOut':
            return itemCond.field + '.Count() == 0';
        }
        switch (itemCond.valueType) {
          case 'array':
            var condition = this.convertArrayCondition(itemCond);
            if (!condition.length) return;
            return condition;
        }
      }
    }, {
      key: "isArrayCompare",
      value: function isArrayCompare(pos, nodeList, fieldsByName) {
        var fieldResult = this.getFieldFromNodeList(pos, nodeList),
          fieldName;
        if (!fieldResult) return false;
        var _fieldResult = babelHelpers.slicedToArray(fieldResult, 2);
        pos = _fieldResult[0];
        fieldName = _fieldResult[1];
        if (!fieldsByName[fieldName] || fieldsByName[fieldName].type !== 'array') return false;
        if (nodeList[pos].nType !== MSLang$2.NodeType.ntSelfFuncCall) {
          //Form.Field.Choice.Function()
          return false;
        }
        if (nodeList[pos + 1].nType !== MSLang$2.NodeType.ntExpressionCompare) {
          //Form.Field.Choice.Function() ==
          return false;
        }
        if (nodeList[pos + 2].nType !== MSLang$2.NodeType.ntIFValue) {
          //Form.Field.Choice.Function() == ?
          return false;
        }
        if (nodeList[pos + 2].childItems.length !== 1 || nodeList[pos + 2].childItems[0].nType !== MSLang$2.NodeType.ntNumeric) {
          //Form.Field.Choice.Function() == 0
          return false;
        }
        var functionName = nodeList[pos].nValue,
          data;
        switch (functionName) {
          case 'Count':
            if (nodeList[pos + 1].nValue === MSLang$2.CompareType.ctEqual && nodeList[pos + 2].childItems[0].nValue === 0) {
              data = {
                field: fieldName,
                type: 'isNotFilledOut'
              };
              return [pos + 3, data];
            }
            if (nodeList[pos + 1].nValue === MSLang$2.CompareType.ctGreat && nodeList[pos + 2].childItems[0].nValue === 0) {
              data = {
                field: fieldName,
                type: 'isFilledOut'
              };
              return [pos + 3, data];
            }
            break;
          default:
            return false;
        }
        return false;
      }
    }, {
      key: "isCompareFunction",
      value: function isCompareFunction(pos, nodeList, fieldsByName) {
        var negative = false,
          funcMap = {
            Contains: 'DoesNotContain',
            StartsWith: 'DoesNotStartWith',
            EndsWith: 'DoesNotEndWith'
          };
        if (nodeList[pos].nType === MSLang$2.NodeType.ntNegativeIf) {
          negative = true;
          pos++;
        }
        var fieldResult = this.getFieldFromNodeList(pos, nodeList),
          fieldName;
        if (!fieldResult) return false;
        var _fieldResult2 = fieldResult;
        var _fieldResult3 = babelHelpers.slicedToArray(_fieldResult2, 2);
        pos = _fieldResult3[0];
        fieldName = _fieldResult3[1];
        if (!fieldsByName[fieldName] || fieldsByName[fieldName].type !== 'array') return false;
        if (nodeList[pos].nType !== MSLang$2.NodeType.ntSelfFuncCall) {
          return false;
        }
        var functionName = nodeList[pos].nValue,
          data;
        if (Object.keys(funcMap).indexOf(functionName) < 0) return false;
        if (!nodeList[pos].childItems || nodeList[pos].childItems.length !== 1) return false;
        if (nodeList[pos].childItems[0].nType !== MSLang$2.NodeType.ntFuncParam) return false;
        if (!nodeList[pos].childItems[0].childItems) return false;
        if (nodeList[pos].childItems[0].childItems[0].nType === MSLang$2.NodeType.ntString) {
          data = {
            field: fieldName,
            type: negative ? funcMap[functionName] : functionName,
            valueType: 'string',
            value: nodeList[pos].childItems[0].childItems[0].nValue,
            negative: negative
          };
          pos++;
          return [pos, data];
        }
        fieldResult = this.getFieldFromNodeList(0, nodeList[pos].childItems[0].childItems);
        if (!fieldResult) return false;
        data = {
          field: fieldName,
          type: negative ? funcMap[functionName] : functionName,
          valueType: 'field',
          value: fieldResult[1],
          negative: negative
        };
        pos++;
        return [pos, data];
      }
    }, {
      key: "isArrayCondition",
      value: function isArrayCondition(pos, nodeList, fieldsByName) {
        if (!nodeList[pos].nType === MSLang$2.NodeType.ntSubExpression) return false;
        var subExpression = nodeList[pos].childItems;
        if (!Array.isArray(subExpression)) return false;
        var result = [],
          subPos = 0,
          functions = [this.isCompareFunction.bind(this)],
          remoteResult,
          data;
        while (subPos < subExpression.length) {
          remoteResult = false;
          functions.every(function (f) {
            remoteResult = f.call(null, subPos, subExpression, fieldsByName);
            return remoteResult === false;
          });
          if (remoteResult === false) return false;
          subPos = remoteResult[0];
          data = remoteResult[1];
          if (result.length) {
            if (data.type !== result[0].type || data.field !== result[0].field) return false;
          }
          result.push(data);
          if (subPos >= subExpression.length) break;
          if (data.negative) {
            if (subExpression[subPos].nType !== MSLang$2.NodeType.ntCompareAnd) return false;
          } else {
            if (subExpression[subPos].nType !== MSLang$2.NodeType.ntCompareOr) return false;
          }
          subPos++;
        }
        if (!result.length) return false;
        var group = {
          field: result[0].field,
          type: result[0].type,
          valueType: 'array',
          value: []
        };
        result.forEach(function (item) {
          switch (item.valueType) {
            case 'string':
              group.value.push({
                value: item.value,
                valueType: 'fieldValue'
              });
              break;
            case 'field':
              group.value.push({
                value: item.value,
                valueType: 'field'
              });
              break;
          }
        });
        return [pos + 1, group];
      }
    }, {
      key: "getParseFunctions",
      value: function getParseFunctions() {
        return [this.isArrayCompare.bind(this), this.isArrayCondition.bind(this)];
      }
    }, {
      key: "conditions",
      get: function get() {
        var items = [];
        items.push({
          value: 'isFilledOut',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_FILLED_OUT'),
          comp: false
        });
        items.push({
          value: 'isNotFilledOut',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_NOT_FILLED_OUT'),
          comp: false
        });
        items.push({
          value: 'Contains',
          label: BX.message('DEVBX_WEB_FORM_COND_CONTAINS'),
          comp: 'devbx-webform-cond-array'
        });
        items.push({
          value: 'DoesNotContain',
          label: BX.message('DEVBX_WEB_FORM_COND_DOES_NOT_CONTAIN'),
          comp: 'devbx-webform-cond-array'
        });
        return items;
      }
    }]);
    return WebFormCondArray;
  }(WebFormCond);

  var MSLang$3 = DevBX.MSLang;
  var WebFormCondDate = /*#__PURE__*/function (_WebFormCond) {
    babelHelpers.inherits(WebFormCondDate, _WebFormCond);
    function WebFormCondDate() {
      babelHelpers.classCallCheck(this, WebFormCondDate);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WebFormCondDate).call(this, 'date'));
    }
    babelHelpers.createClass(WebFormCondDate, [{
      key: "getCode",
      value: function getCode(formField, itemCond) {
        switch (itemCond.type) {
          case 'isFilledOut':
            return itemCond.field + ' != null';
          case 'isNotFilledOut':
            return itemCond.field + ' == null';
          case 'isToday':
            return itemCond.field + ' == DateTime.Today';
          case 'isInFuture':
            return itemCond.field + ' > DateTime.Today';
          case 'isInPast':
            return itemCond.field + ' < DateTime.Today';
        }
        if (itemCond.valueType !== 'date') return '';
        var value = parseInt(itemCond.value);
        if (isNaN(value)) return '';
        var date = new Date(value * 1000);
        var formatted = date.getFullYear() + '-' + (date.getMonth() + 1).toString().padStart(2, '0') + '-' + date.getDate().toString().padStart(2, '0');
        switch (itemCond.type) {
          case 'is':
            return itemCond.field + ' == ' + this.quoteStr(formatted);
          case 'isNot':
            return itemCond.field + ' != ' + this.quoteStr(formatted);
          case 'isAfter':
            return itemCond.field + ' > ' + this.quoteStr(formatted);
          case 'isBefore':
            return itemCond.field + ' < ' + this.quoteStr(formatted);
          case 'onOrAfter':
            return itemCond.field + ' >= ' + this.quoteStr(formatted);
          case 'onOrBefore':
            return itemCond.field + ' <= ' + this.quoteStr(formatted);
        }
        return '';
      }
    }, {
      key: "isCompareField",
      value: function isCompareField(pos, nodeList, fieldsByName) {
        var fieldResult = this.getFieldFromNodeList(pos, nodeList),
          fieldName,
          data;
        if (!fieldResult) return false;
        var _fieldResult = fieldResult;
        var _fieldResult2 = babelHelpers.slicedToArray(_fieldResult, 2);
        pos = _fieldResult2[0];
        fieldName = _fieldResult2[1];
        if (!fieldsByName[fieldName] || fieldsByName[fieldName].type !== 'date') return false;
        if (nodeList[pos].nType !== MSLang$3.NodeType.ntExpressionCompare) return false;
        if (nodeList[pos + 1].nType !== MSLang$3.NodeType.ntIFValue) return false;
        if (nodeList[pos + 1].childItems[0].nType === MSLang$3.NodeType.ntContextVariable) {
          fieldResult = this.getFieldFromNodeList(0, nodeList[pos + 1].childItems);
          if (!fieldResult) return false;
          switch (fieldResult[1]) {
            case 'null':
              data = {
                field: fieldName
              };
              switch (nodeList[pos].nValue) {
                case MSLang$3.CompareType.ctEqual:
                  data.type = 'isNotFilledOut';
                  break;
                case MSLang$3.CompareType.ctNotEqual:
                  data.type = 'isFilledOut';
                  break;
                default:
                  return false;
              }
              return [pos + 2, data];
            case 'DateTime.Today':
              data = {
                field: fieldName
              };
              switch (nodeList[pos].nValue) {
                case MSLang$3.CompareType.ctEqual:
                  data.type = 'isToday';
                  break;
                case MSLang$3.CompareType.ctGreat:
                  data.type = 'isInFuture';
                  break;
                case MSLang$3.CompareType.ctLess:
                  data.type = 'isInPast';
                  break;
                default:
                  return false;
              }
              return [pos + 2, data];
          }
        }
        if (nodeList[pos + 1].childItems[0].nType === MSLang$3.NodeType.ntString) {
          var dateTime = BX.parseDate(nodeList[pos + 1].childItems[0].nValue, false, "YYYY.MM.DD", "YYYY.MM.DD");
          if (!dateTime) return false;
          var value = Math.floor(dateTime.getTime() / 1000);
          data = {
            field: fieldName,
            value: value,
            valueType: 'date'
          };
          switch (nodeList[pos].nValue) {
            case MSLang$3.CompareType.ctEqual:
              data.type = 'is';
              break;
            case MSLang$3.CompareType.ctNotEqual:
              data.type = 'isNot';
              break;
            case MSLang$3.CompareType.ctGreat:
              data.type = 'isAfter';
              break;
            case MSLang$3.CompareType.ctLess:
              data.type = 'isBefore';
              break;
            case MSLang$3.CompareType.ctGreat | MSLang$3.CompareType.ctEqual:
              data.type = 'onOrAfter';
              break;
            case MSLang$3.CompareType.ctLess | MSLang$3.CompareType.ctEqual:
              data.type = 'onOrBefore';
              break;
            default:
              return false;
          }
          return [pos + 2, data];
        }
        return false;
      }
    }, {
      key: "getParseFunctions",
      value: function getParseFunctions() {
        return [this.isCompareField.bind(this)];
      }
    }, {
      key: "conditions",
      get: function get() {
        var items = [];
        items.push({
          value: 'isFilledOut',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_FILLED_OUT'),
          comp: false
        });
        items.push({
          value: 'isNotFilledOut',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_NOT_FILLED_OUT'),
          comp: false
        });
        items.push({
          value: 'isAfter',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_AFTER'),
          comp: 'devbx-webform-cond-date'
        });
        items.push({
          value: 'isBefore',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_BEFORE'),
          comp: 'devbx-webform-cond-date'
        });
        items.push({
          value: 'onOrAfter',
          label: BX.message('DEVBX_WEB_FORM_COND_ON_OR_AFTER'),
          comp: 'devbx-webform-cond-date'
        });
        items.push({
          value: 'onOrBefore',
          label: BX.message('DEVBX_WEB_FORM_COND_ON_OR_BEFORE'),
          comp: 'devbx-webform-cond-date'
        });
        items.push({
          value: 'isToday',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_TODAY'),
          comp: false
        });
        items.push({
          value: 'isInFuture',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_IN_FUTURE'),
          comp: false
        });
        items.push({
          value: 'isInPast',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_IN_PAST'),
          comp: false
        });
        return items;
      }
    }]);
    return WebFormCondDate;
  }(WebFormCond);

  var MSLang$4 = DevBX.MSLang;
  var WebFormCondDateTime = /*#__PURE__*/function (_WebFormCond) {
    babelHelpers.inherits(WebFormCondDateTime, _WebFormCond);
    function WebFormCondDateTime() {
      babelHelpers.classCallCheck(this, WebFormCondDateTime);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WebFormCondDateTime).call(this, 'datetime'));
    }
    babelHelpers.createClass(WebFormCondDateTime, [{
      key: "getCode",
      value: function getCode(formField, itemCond) {
        switch (itemCond.type) {
          case 'isFilledOut':
            return itemCond.field + ' != null';
          case 'isNotFilledOut':
            return itemCond.field + ' == null';
          case 'isNow':
            return itemCond.field + ' == DateTime.Now';
          case 'isInFuture':
            return itemCond.field + ' > DateTime.Now';
          case 'isInPast':
            return itemCond.field + ' < DateTime.Now';
        }
        if (itemCond.valueType !== 'datetime') return '';
        var value = parseInt(itemCond.value);
        if (isNaN(value)) return '';
        var date = new Date(value * 1000);
        var formatted = date.getFullYear() + '-' + (date.getMonth() + 1).toString().padStart(2, '0') + '-' + date.getDate().toString().padStart(2, '0') + ' ' + date.getHours().toString().padStart(2, '0') + ':' + date.getMinutes().toString().padStart(2, '0') + ':' + date.getSeconds().toString().padStart(2, '0');
        switch (itemCond.type) {
          case 'is':
            return itemCond.field + ' == ' + this.quoteStr(formatted);
          case 'isNot':
            return itemCond.field + ' != ' + this.quoteStr(formatted);
          case 'isAfter':
            return itemCond.field + ' > ' + this.quoteStr(formatted);
          case 'isBefore':
            return itemCond.field + ' < ' + this.quoteStr(formatted);
          case 'onOrAfter':
            return itemCond.field + ' >= ' + this.quoteStr(formatted);
          case 'onOrBefore':
            return itemCond.field + ' <= ' + this.quoteStr(formatted);
        }
        return '';
      }
    }, {
      key: "isCompareField",
      value: function isCompareField(pos, nodeList, fieldsByName) {
        var fieldResult = this.getFieldFromNodeList(pos, nodeList),
          fieldName,
          data;
        if (!fieldResult) return false;
        var _fieldResult = fieldResult;
        var _fieldResult2 = babelHelpers.slicedToArray(_fieldResult, 2);
        pos = _fieldResult2[0];
        fieldName = _fieldResult2[1];
        if (!fieldsByName[fieldName] || fieldsByName[fieldName].type !== 'datetime') return false;
        if (nodeList[pos].nType !== MSLang$4.NodeType.ntExpressionCompare) return false;
        if (nodeList[pos + 1].nType !== MSLang$4.NodeType.ntIFValue) return false;
        if (nodeList[pos + 1].childItems[0].nType === MSLang$4.NodeType.ntContextVariable) {
          fieldResult = this.getFieldFromNodeList(0, nodeList[pos + 1].childItems);
          if (!fieldResult) return false;
          switch (fieldResult[1]) {
            case 'null':
              data = {
                field: fieldName
              };
              switch (nodeList[pos].nValue) {
                case MSLang$4.CompareType.ctEqual:
                  data.type = 'isNotFilledOut';
                  break;
                case MSLang$4.CompareType.ctNotEqual:
                  data.type = 'isFilledOut';
                  break;
                default:
                  return false;
              }
              return [pos + 2, data];
            case 'DateTime.Now':
              data = {
                field: fieldName
              };
              switch (nodeList[pos].nValue) {
                case MSLang$4.CompareType.ctEqual:
                  data.type = 'isNow';
                  break;
                case MSLang$4.CompareType.ctGreat:
                  data.type = 'isInFuture';
                  break;
                case MSLang$4.CompareType.ctLess:
                  data.type = 'isInPast';
                  break;
                default:
                  return false;
              }
              return [pos + 2, data];
          }
        }
        if (nodeList[pos + 1].childItems[0].nType === MSLang$4.NodeType.ntString) {
          var dateTime = BX.parseDate(nodeList[pos + 1].childItems[0].nValue, false, "YYYY.MM.DD", "YYYY.MM.DD HH:MI:SS");
          if (!dateTime) return false;
          var value = Math.floor(dateTime.getTime() / 1000);
          data = {
            field: fieldName,
            value: value,
            valueType: 'datetime'
          };
          switch (nodeList[pos].nValue) {
            case MSLang$4.CompareType.ctEqual:
              data.type = 'is';
              break;
            case MSLang$4.CompareType.ctNotEqual:
              data.type = 'isNot';
              break;
            case MSLang$4.CompareType.ctGreat:
              data.type = 'isAfter';
              break;
            case MSLang$4.CompareType.ctLess:
              data.type = 'isBefore';
              break;
            case MSLang$4.CompareType.ctGreat | MSLang$4.CompareType.ctEqual:
              data.type = 'onOrAfter';
              break;
            case MSLang$4.CompareType.ctLess | MSLang$4.CompareType.ctEqual:
              data.type = 'onOrBefore';
              break;
            default:
              return false;
          }
          return [pos + 2, data];
        }
        return false;
      }
    }, {
      key: "getParseFunctions",
      value: function getParseFunctions() {
        return [this.isCompareField.bind(this)];
      }
    }, {
      key: "conditions",
      get: function get() {
        var items = [];
        items.push({
          value: 'isFilledOut',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_FILLED_OUT'),
          comp: false
        });
        items.push({
          value: 'isNotFilledOut',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_NOT_FILLED_OUT'),
          comp: false
        });
        items.push({
          value: 'isAfter',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_AFTER'),
          comp: 'devbx-webform-cond-date',
          props: {
            time: true
          }
        });
        items.push({
          value: 'isBefore',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_BEFORE'),
          comp: 'devbx-webform-cond-date',
          props: {
            time: true
          }
        });
        items.push({
          value: 'onOrAfter',
          label: BX.message('DEVBX_WEB_FORM_COND_ON_OR_AFTER'),
          comp: 'devbx-webform-cond-date',
          props: {
            time: true
          }
        });
        items.push({
          value: 'onOrBefore',
          label: BX.message('DEVBX_WEB_FORM_COND_ON_OR_BEFORE'),
          comp: 'devbx-webform-cond-date',
          props: {
            time: true
          }
        });
        items.push({
          value: 'isNow',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_NOW'),
          comp: false
        });
        items.push({
          value: 'isInFuture',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_IN_FUTURE'),
          comp: false
        });
        items.push({
          value: 'isInPast',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_IN_PAST'),
          comp: false
        });
        return items;
      }
    }]);
    return WebFormCondDateTime;
  }(WebFormCond);

  var MSLang$5 = DevBX.MSLang;
  var WebFormCondTime = /*#__PURE__*/function (_WebFormCond) {
    babelHelpers.inherits(WebFormCondTime, _WebFormCond);
    function WebFormCondTime() {
      babelHelpers.classCallCheck(this, WebFormCondTime);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WebFormCondTime).call(this, 'time'));
    }
    babelHelpers.createClass(WebFormCondTime, [{
      key: "getCode",
      value: function getCode(formField, itemCond) {
        switch (itemCond.type) {
          case 'isFilledOut':
            return itemCond.field + ' != null';
          case 'isNotFilledOut':
            return itemCond.field + ' == null';
        }
        if (itemCond.valueType !== 'time') return '';
        var value = parseInt(itemCond.value);
        if (isNaN(value)) return '';
        var formatted = Math.floor(value / 60 * 60).toString().padStart(2, '0') + ':' + Math.floor(value / 60).toString().padStart(2, '0') + ':' + (value % 60).toString().padStart(2, '0');
        switch (itemCond.type) {
          case 'is':
            return itemCond.field + ' == ' + this.quoteStr(formatted);
          case 'isNot':
            return itemCond.field + ' != ' + this.quoteStr(formatted);
          case 'isAfter':
            return itemCond.field + ' > ' + this.quoteStr(formatted);
          case 'isBefore':
            return itemCond.field + ' < ' + this.quoteStr(formatted);
          case 'onOrAfter':
            return itemCond.field + ' >= ' + this.quoteStr(formatted);
          case 'onOrBefore':
            return itemCond.field + ' <= ' + this.quoteStr(formatted);
        }
        return '';
      }
    }, {
      key: "isCompareField",
      value: function isCompareField(pos, nodeList, fieldsByName) {
        var fieldResult = this.getFieldFromNodeList(pos, nodeList),
          fieldName,
          data;
        if (!fieldResult) return false;
        var _fieldResult = fieldResult;
        var _fieldResult2 = babelHelpers.slicedToArray(_fieldResult, 2);
        pos = _fieldResult2[0];
        fieldName = _fieldResult2[1];
        if (!fieldsByName[fieldName] || fieldsByName[fieldName].type !== 'time') return false;
        if (nodeList[pos].nType !== MSLang$5.NodeType.ntExpressionCompare) return false;
        if (nodeList[pos + 1].nType !== MSLang$5.NodeType.ntIFValue) return false;
        if (nodeList[pos + 1].childItems[0].nType === MSLang$5.NodeType.ntContextVariable) {
          fieldResult = this.getFieldFromNodeList(0, nodeList[pos + 1].childItems);
          if (!fieldResult) return false;
          switch (fieldResult[1]) {
            case 'null':
              data = {
                field: fieldName
              };
              switch (nodeList[pos].nValue) {
                case MSLang$5.CompareType.ctEqual:
                  data.type = 'isNotFilledOut';
                  break;
                case MSLang$5.CompareType.ctNotEqual:
                  data.type = 'isFilledOut';
                  break;
                default:
                  return false;
              }
              return [pos + 2, data];
          }
        }
        return false;
      }
    }, {
      key: "getParseFunctions",
      value: function getParseFunctions() {
        return [this.isCompareField.bind(this)];
      }
    }, {
      key: "conditions",
      get: function get() {
        var items = [];
        items.push({
          value: 'isFilledOut',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_FILLED_OUT'),
          comp: false
        });
        items.push({
          value: 'isNotFilledOut',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_NOT_FILLED_OUT'),
          comp: false
        });
        return items;
      }
    }]);
    return WebFormCondTime;
  }(WebFormCond);

  var MSLang$6 = DevBX.MSLang;
  var WebFormCondFiles = /*#__PURE__*/function (_WebFormCond) {
    babelHelpers.inherits(WebFormCondFiles, _WebFormCond);
    function WebFormCondFiles() {
      babelHelpers.classCallCheck(this, WebFormCondFiles);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WebFormCondFiles).call(this, 'files'));
    }
    babelHelpers.createClass(WebFormCondFiles, [{
      key: "getCode",
      value: function getCode(formField, itemCond) {
        switch (itemCond.type) {
          case 'isFilledOut':
            return itemCond.field + '.Count() > 0';
          case 'isNotFilledOut':
            return itemCond.field + '.Count() == 0';
          case 'isEqual':
            var value = parseInt(itemCond.value);
            if (!isNaN(value)) return itemCond.field + '.Count()  == ' + value;
        }
        return false;
      }
    }, {
      key: "isFilesCompare",
      value: function isFilesCompare(pos, nodeList, fieldsByName) {
        var fieldResult = this.getFieldFromNodeList(pos, nodeList),
          fieldName;
        if (!fieldResult) return false;
        var _fieldResult = babelHelpers.slicedToArray(fieldResult, 2);
        pos = _fieldResult[0];
        fieldName = _fieldResult[1];
        if (!fieldsByName[fieldName] || fieldsByName[fieldName].type !== 'files') return false;
        if (nodeList[pos].nType !== MSLang$6.NodeType.ntSelfFuncCall) {
          //Form.Field.Choice.Function()
          return false;
        }
        if (nodeList[pos + 1].nType !== MSLang$6.NodeType.ntExpressionCompare) {
          //Form.Field.Choice.Function() ==
          return false;
        }
        if (nodeList[pos + 2].nType !== MSLang$6.NodeType.ntIFValue) {
          //Form.Field.Choice.Function() == ?
          return false;
        }
        if (nodeList[pos + 2].childItems.length !== 1 || nodeList[pos + 2].childItems[0].nType !== MSLang$6.NodeType.ntNumeric) {
          //Form.Field.Choice.Function() == 0
          return false;
        }
        var functionName = nodeList[pos].nValue,
          data;
        switch (functionName) {
          case 'Count':
            if (nodeList[pos + 1].nValue === MSLang$6.CompareType.ctEqual) {
              if (nodeList[pos + 2].childItems[0].nValue === 0) {
                data = {
                  field: fieldName,
                  type: 'isNotFilledOut'
                };
                return [pos + 3, data];
              }
              debugger;
              data = {
                field: fieldName,
                type: 'isEqual',
                value: nodeList[pos + 2].childItems[0].nValue.toString(),
                valueType: 'string'
              };
              return [pos + 3, data];
            }
            if (nodeList[pos + 1].nValue === MSLang$6.CompareType.ctGreat && nodeList[pos + 2].childItems[0].nValue === 0) {
              data = {
                field: fieldName,
                type: 'isFilledOut'
              };
              return [pos + 3, data];
            }
            break;
          default:
            return false;
        }
        return false;
      }
    }, {
      key: "getParseFunctions",
      value: function getParseFunctions() {
        return [this.isFilesCompare.bind(this)];
      }
    }, {
      key: "conditions",
      get: function get() {
        var items = [];
        items.push({
          value: 'isFilledOut',
          label: BX.message('DEVBX_WEB_FORM_COND_HAS_AT_LEAST_ONE_FILE'),
          comp: false
        });
        items.push({
          value: 'isNotFilledOut',
          label: BX.message('DEVBX_WEB_FORM_COND_HAS_NO_FILES'),
          comp: false
        });
        items.push({
          value: 'isEqual',
          label: BX.message('DEVBX_WEB_FORM_COND_NUMBER_OF_FILES_IS'),
          comp: 'devbx-webform-cond-number'
        });
        return items;
      }
    }]);
    return WebFormCondFiles;
  }(WebFormCond);

  var MSLang$7 = DevBX.MSLang;
  var WebFormCondBoolean = /*#__PURE__*/function (_WebFormCond) {
    babelHelpers.inherits(WebFormCondBoolean, _WebFormCond);
    function WebFormCondBoolean() {
      babelHelpers.classCallCheck(this, WebFormCondBoolean);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WebFormCondBoolean).call(this, 'boolean'));
    }
    babelHelpers.createClass(WebFormCondBoolean, [{
      key: "getCode",
      value: function getCode(formField, itemCond) {
        switch (itemCond.type) {
          case 'isTrue':
            return itemCond.field;
          case 'isFalse':
            return '!' + itemCond.field;
        }
        return false;
      }
    }, {
      key: "isBooleanCompare",
      value: function isBooleanCompare(pos, nodeList, fieldsByName) {
        var negative = false,
          fieldResult,
          fieldName;
        if (nodeList[pos].nType === MSLang$7.NodeType.ntNegativeIf) {
          negative = true;
          pos++;
        }
        fieldResult = this.getFieldFromNodeList(pos, nodeList);
        if (!fieldResult) return false;
        var _fieldResult = fieldResult;
        var _fieldResult2 = babelHelpers.slicedToArray(_fieldResult, 2);
        pos = _fieldResult2[0];
        fieldName = _fieldResult2[1];
        if (!fieldsByName[fieldName] || fieldsByName[fieldName].type !== 'boolean') return false;
        if (pos === nodeList.length || nodeList[pos].nType === MSLang$7.NodeType.ntCompareAnd) {
          var data = {
            field: fieldName,
            type: negative ? 'isFalse' : 'isTrue'
          };
          return [pos, data];
        }
        return false;
      }
    }, {
      key: "getParseFunctions",
      value: function getParseFunctions() {
        return [this.isBooleanCompare.bind(this)];
      }
    }, {
      key: "conditions",
      get: function get() {
        var items = [];
        items.push({
          value: 'isTrue',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_TRUE'),
          comp: false
        });
        items.push({
          value: 'isFalse',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_FALSE'),
          comp: false
        });
        return items;
      }
    }]);
    return WebFormCondBoolean;
  }(WebFormCond);

  var MSLang$8 = DevBX.MSLang;
  var WebFormCondNumber = /*#__PURE__*/function (_WebFormCond) {
    babelHelpers.inherits(WebFormCondNumber, _WebFormCond);
    function WebFormCondNumber() {
      babelHelpers.classCallCheck(this, WebFormCondNumber);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WebFormCondNumber).call(this, 'number'));
    }
    babelHelpers.createClass(WebFormCondNumber, [{
      key: "getCode",
      value: function getCode(formField, itemCond) {
        switch (itemCond.type) {
          case 'isFilledOut':
            return itemCond.field + ' != null';
          case 'isNotFilledOut':
            return itemCond.field + ' == null';
        }
        var value = itemCond.value;
        switch (itemCond.valueType) {
          case 'number':
            value = parseFloat(value);
            if (isNaN(value)) return '';
            break;
          case 'field':
            break;
          default:
            return;
        }
        switch (itemCond.type) {
          case 'is':
            return itemCond.field + ' == ' + value;
          case 'isNot':
            return itemCond.field + ' != ' + value;
          case 'isGreater':
            return itemCond.field + ' > ' + value;
          case 'isLess':
            return itemCond.field + ' < ' + value;
          case 'isGreaterOrEqual':
            return itemCond.field + ' >= ' + value;
          case 'isLessOrEqual':
            return itemCond.field + ' <= ' + value;
        }
        return '';
      }
    }, {
      key: "isCompareField",
      value: function isCompareField(pos, nodeList, fieldsByName) {
        var fieldResult = this.getFieldFromNodeList(pos, nodeList),
          fieldName;
        if (!fieldResult) return false;
        var _fieldResult = fieldResult;
        var _fieldResult2 = babelHelpers.slicedToArray(_fieldResult, 2);
        pos = _fieldResult2[0];
        fieldName = _fieldResult2[1];
        if (!fieldsByName[fieldName] || fieldsByName[fieldName].type !== 'number') return false;
        if (nodeList[pos].nType !== MSLang$8.NodeType.ntExpressionCompare) return false;
        if (nodeList[pos + 1].nType !== MSLang$8.NodeType.ntIFValue) return false;
        var rightNodeType = nodeList[pos + 1].childItems[0].nType,
          data;
        if (rightNodeType !== MSLang$8.NodeType.ntContextVariable && rightNodeType !== MSLang$8.NodeType.ntNumeric) return false;
        if (rightNodeType === MSLang$8.NodeType.ntContextVariable) {
          fieldResult = this.getFieldFromNodeList(0, nodeList[pos + 1].childItems);
          if (!fieldResult) return false;
          switch (fieldResult[1]) {
            case 'null':
              data = {
                field: fieldName
              };
              switch (nodeList[pos].nValue) {
                case MSLang$8.CompareType.ctEqual:
                  data.type = 'isNotFilledOut';
                  break;
                case MSLang$8.CompareType.ctNotEqual:
                  data.type = 'isFilledOut';
                  break;
                default:
                  return false;
              }
              return [pos + 2, data];
          }
          data = {
            field: fieldName,
            value: fieldResult[1],
            valueType: 'field'
          };
        } else {
          data = {
            field: fieldName,
            value: nodeList[pos + 1].childItems[0].nValue,
            valueType: 'number'
          };
        }
        switch (nodeList[pos].nValue) {
          case MSLang$8.CompareType.ctEqual:
            data.type = 'is';
            break;
          case MSLang$8.CompareType.ctNotEqual:
            data.type = 'isNot';
            break;
          case MSLang$8.CompareType.ctGreat:
            data.type = 'isGreater';
            break;
          case MSLang$8.CompareType.ctLess:
            data.type = 'isLess';
            break;
          case MSLang$8.CompareType.ctGreat | MSLang$8.CompareType.ctEqual:
            data.type = 'isGreaterOrEqual';
            break;
          case MSLang$8.CompareType.ctLess | MSLang$8.CompareType.ctEqual:
            data.type = 'isLessOrEqual';
            break;
          default:
            return false;
        }
        return [pos + 2, data];
      }
    }, {
      key: "getParseFunctions",
      value: function getParseFunctions() {
        return [this.isCompareField.bind(this)];
      }
    }, {
      key: "conditions",
      get: function get() {
        var items = [];
        items.push({
          value: 'isFilledOut',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_FILLED_OUT'),
          comp: false
        });
        items.push({
          value: 'isNotFilledOut',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_NOT_FILLED_OUT'),
          comp: false
        });
        items.push({
          value: 'is',
          label: BX.message('DEVBX_WEB_FORM_COND_IS'),
          comp: 'devbx-webform-cond-number'
        });
        items.push({
          value: 'isNot',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_NOT'),
          comp: 'devbx-webform-cond-number'
        });
        items.push({
          value: 'isGreater',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_GREATER'),
          comp: 'devbx-webform-cond-number'
        });
        items.push({
          value: 'isLess',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_LESS'),
          comp: 'devbx-webform-cond-number'
        });
        items.push({
          value: 'isGreaterOrEqual',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_GREATER_OR_EQUAL'),
          comp: 'devbx-webform-cond-number'
        });
        items.push({
          value: 'isLessOrEqual',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_LESS_OR_EQUAL'),
          comp: 'devbx-webform-cond-number'
        });
        return items;
      }
    }]);
    return WebFormCondNumber;
  }(WebFormCond);

  var MSLang$9 = DevBX.MSLang;
  var WebFormCondArray$1 = /*#__PURE__*/function (_WebFormCond) {
    babelHelpers.inherits(WebFormCondArray, _WebFormCond);
    function WebFormCondArray() {
      babelHelpers.classCallCheck(this, WebFormCondArray);
      return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WebFormCondArray).call(this, 'enum'));
    }
    babelHelpers.createClass(WebFormCondArray, [{
      key: "getCode",
      value: function getCode(formField, itemCond) {
        //в феб-форме не используется enum тип данных
        return '';
      }
    }, {
      key: "getParseFunctions",
      value: function getParseFunctions() {
        //в феб-форме не используется enum тип данных
        return [];
      }
    }, {
      key: "conditions",
      get: function get() {
        var items = [];
        items.push({
          value: 'isFilledOut',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_FILLED_OUT'),
          comp: false
        });
        items.push({
          value: 'isNotFilledOut',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_NOT_FILLED_OUT'),
          comp: false
        });
        items.push({
          value: 'is',
          label: BX.message('DEVBX_WEB_FORM_COND_IS'),
          comp: 'devbx-webform-cond-enum'
        });
        items.push({
          value: 'isNot',
          label: BX.message('DEVBX_WEB_FORM_COND_IS_NOT'),
          comp: 'devbx-webform-cond-enum'
        });
        return items;
      }
    }]);
    return WebFormCondArray;
  }(WebFormCond);

  var MSLang$a = DevBX.MSLang;
  var WebFormCondConfig = /*#__PURE__*/function () {
    function WebFormCondConfig() {
      babelHelpers.classCallCheck(this, WebFormCondConfig);
      this._fieldsType = {};
      this.register(new WebFormCondString());
      this.register(new WebFormCondArray());
      this.register(new WebFormCondDate());
      this.register(new WebFormCondDateTime());
      this.register(new WebFormCondTime());
      this.register(new WebFormCondFiles());
      this.register(new WebFormCondBoolean());
      this.register(new WebFormCondNumber());

      /* для инфоблоков */
      this.register(new WebFormCondArray$1());
    }
    babelHelpers.createClass(WebFormCondConfig, [{
      key: "register",
      value: function register(obj) {
        this._fieldsType[obj.type] = obj;
      }
    }, {
      key: "formatString",
      value: function formatString(str, search, replace) {
        if (typeof str === 'undefined') return '';
        if (typeof str !== 'string') str = str.toString();
        if (!Array.isArray(search)) {
          search = [search];
          replace = [replace];
        }
        search.forEach(function (s, i) {
          str = str.replaceAll(s, replace[i]);
        });
        return str;
      }
    }, {
      key: "checkGroupCond",
      value: function checkGroupCond(fieldsByName, groups) {
        var _this = this;
        var errors = [];
        groups.forEach(function (group) {
          group.forEach(function (item) {
            if (!fieldsByName[item.field]) {
              var msg = BX.message('DEVBX_WEB_FORM_COND_FIELD_NOT_FOUND');
              errors.push(_this.formatString(msg, '#FIELD_NAME#', item.field));
              return;
            }
            var formField = fieldsByName[item.field];
            if (item.valueType === 'array') {
              if (formField.type !== 'array') {
                var _msg = BX.message('DEVBX_WEB_FORM_COND_INVALID_FIELD_TYPE');
                errors.push(_this.formatString(_msg, '#FIELD_NAME#', item.field));
                return;
              }
              item.value.forEach(function (value) {
                switch (value.valueType) {
                  case 'fieldValue':
                    if (formField.values.indexOf(value.value) < 0) {
                      var _msg2 = BX.message('DEVBX_WEB_FORM_COND_FIELD_VALUE_NOT_FOUND');
                      errors.push(_this.formatString(_msg2, ['#FIELD_NAME#', '#FIELD_VALUE#'], [item.field, value.value]));
                      return;
                    }
                    break;
                  case 'field':
                    if (!fieldsByName[value.value]) {
                      var _msg3 = BX.message('DEVBX_WEB_FORM_COND_FIELD_NOT_FOUND');
                      errors.push(_this.formatString(_msg3, '#FIELD_NAME#', value.value));
                      return;
                    }
                    break;
                }
              });
            }
          });
        });
        return errors;
      }
    }, {
      key: "compileGroupCond",
      value: function compileGroupCond(fieldsByName, groups) {
        var _this2 = this;
        var arCode = [];
        groups.forEach(function (group) {
          if (!group.length) return;
          var arCond = [];
          group.forEach(function (item) {
            var formField = fieldsByName[item.field];
            if (!formField) return;
            var fieldCond = _this2.fieldsType[formField.type]; //string, array, e.t.c.

            if (!fieldCond) return;
            var code = fieldCond.getCode(formField, item);
            if (code && code.length) {
              arCond.push(code);
            }
          });
          if (arCond.length) {
            arCode.push('(' + arCond.join(' && ') + ')');
          }
        });
        if (!arCode.length) return '';
        return 'return ' + arCode.join("\n\t || ");
      }
    }, {
      key: "parseSubExpression",
      value: function parseSubExpression(nodeList, fieldsByName) {
        var pos = 0,
          result = [],
          remoteResult;
        while (pos < nodeList.length) {
          remoteResult = false;
          this.parseFunctions.every(function (f) {
            remoteResult = f.call(null, pos, nodeList, fieldsByName);
            return remoteResult === false;
          });
          if (remoteResult === false) return false;
          pos = remoteResult[0];
          result.push(remoteResult[1]);
          if (pos >= nodeList.length) break;
          if (nodeList[pos].nType !== MSLang$a.NodeType.ntCompareAnd) return false;
          pos++;
        }
        return result;
      }
    }, {
      key: "codeToWizardBuilder",
      value: function codeToWizardBuilder(nodeList, fieldsByName) {
        if (nodeList.length != 1) return false;
        var pos = 0,
          items = nodeList[0].childItems,
          result = [];
        while (pos < items.length) {
          if (items[pos].nType !== MSLang$a.NodeType.ntSubExpression) return false;
          var group = this.parseSubExpression(items[pos].childItems, fieldsByName);
          if (!group) return false;
          result.push(group);
          pos++;
          if (pos === items.length) break;
          if (items[pos].nType !== MSLang$a.NodeType.ntCompareOr) return false;
          pos++;
        }
        return result;
      }
    }, {
      key: "fieldsType",
      get: function get() {
        return this._fieldsType;
      }
    }, {
      key: "parseFunctions",
      get: function get() {
        var result = [];
        Object.values(this.fieldsType).forEach(function (fieldType) {
          result.push.apply(result, babelHelpers.toConsumableArray(fieldType.getParseFunctions()));
        });
        return result;
      }
    }]);
    return WebFormCondConfig;
  }();

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-cond', {
      props: {
        'item': {
          type: Object,
          required: true
        },
        'condConfig': {
          type: Object,
          required: true
        },
        'condFields': {
          type: Array,
          required: true
        }
      },
      computed: {
        selectedField: function selectedField() {
          var _this = this;
          var result = false;
          debugger;
          this.condFields.every(function (field) {
            if (field.name === _this.item.field) result = field;
            return !result;
          });
          return result;
        },
        selectedFieldDataType: function selectedFieldDataType() {
          if (!this.selectedField) return null;
          return this.selectedField.type;
        },
        operationType: function operationType() {
          if (!this.selectedField) return [];
          var fieldCond = this.condConfig.fieldsType[this.selectedField.type];
          if (!fieldCond) return [];
          return fieldCond.conditions;
        },
        selectedCondition: function selectedCondition() {
          var _this2 = this;
          var result = false;
          this.operationType.every(function (item) {
            if (item.value === _this2.item.type) result = item;
            return !result;
          });
          return result;
        },
        condHasComponent: function condHasComponent() {
          return this.selectedCondition && this.selectedCondition.comp;
        }
      },
      watch: {
        'selectedFieldDataType': function selectedFieldDataType(val, oldVal) {
          if (this.operationType.length) this.item.type = this.operationType[0].value;
        }
      },
      template: "\n    <div class=\"devbx-webform-cond-container\">\n        <div class=\"devbx-webform-cond-dropdown devbx-webform-cond-field\">\n            <select v-model=\"item.field\">\n                <option v-for=\"field in condFields\" :key=\"field.name\" :value=\"field.name\">{{field.name}} ({{field.label}})</option>        \n            </select>\n        </div>\n        \n        <div class=\"devbx-webform-cond-dropdown devbx-webform-cond-type\">\n            <select v-model=\"item.type\">\n                <option v-for=\"type in operationType\" :key=\"type.value\" :value=\"type.value\">{{type.label}}</option>\n            </select>\n        </div>\n        \n        <component v-if=\"condHasComponent\" \n            :is=\"selectedCondition.comp\" \n            :item=\"item\" \n            :field=\"selectedField\" \n            :cond-config=\"condConfig\" \n            :cond-fields=\"condFields\"\n            v-bind=\"selectedCondition.props\"\n            >\n        </component>\n        \n        <slot>\n        </slot>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-cond-group', {
      props: {
        'group': {
          type: Array,
          required: true
        },
        'condConfig': {
          type: Object,
          required: true
        },
        'condFields': {
          type: Array,
          required: true
        }
      },
      methods: {
        addCond: function addCond() {
          this.group.push({
            field: this.$root.formFields[0].name,
            type: 'isFilledOut'
          });
        },
        removeCond: function removeCond(index) {
          this.group.splice(index, 1);
          if (!this.group.length) this.$emit('removegroup', this.group);
        }
      },
      template: "\n<div class=\"devbx-webform-cond-group\">\n    <devbx-webform-cond v-for=\"(item,index) in group\" :item=\"item\" :key=\"index\" :cond-config=\"condConfig\" :cond-fields=\"condFields\">\n        <span v-if=\"index == group.length-1\" class=\"devbx-webform-cond-add-and\">\n            <a href=\"#\" @click.stop.prevent=\"addCond\">\n                <i class=\"fa fa-plus\"></i>\n                <span class=\"devbx-webform-cond-add-and-label\">{{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_LOGIN_AND')}}</span>\n            </a>\n        </span>\n        \n        <a href=\"#\" @click.stop.prevent=\"removeCond(index)\" class=\"devbx-webform-cond-remove\">\n            <i class=\"fa fa-trash\"></i>            \n        </a>\n    </devbx-webform-cond>\n</div>\n"
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-cond-string', {
      props: {
        'item': {
          type: Object,
          required: true
        },
        'field': {
          type: Object,
          required: true
        },
        'condConfig': {
          type: Object
        },
        'condFields': {
          type: Array,
          required: true
        }
      },
      computed: {
        options: function options() {
          var result = [];
          if (Array.isArray(this.field.values)) {
            this.field.values.forEach(function (value) {
              result.push({
                value: value,
                valueType: 'string',
                label: value,
                cssClass: 'cond-field-value'
              });
            });
          }
          this.condFields.forEach(function (item) {
            if (item.type === 'string') {
              result.push({
                value: item.name,
                valueType: 'field',
                label: item.name + ' (' + item.label + ')',
                cssClass: 'cond-field'
              });
            }
          });
          return result;
        },
        value: {
          get: function get() {
            if (typeof this.item.value === 'string') return this.item.value;
            return '';
          },
          set: function set(val) {
            this.item.value = val;
          }
        },
        valueType: {
          get: function get() {
            if (!!this.item.valueType) return this.item.valueType;
            return 'string';
          },
          set: function set(val) {
            this.item.valueType = val;
          }
        }
      },
      watch: {
        item: {
          handler: function handler(val) {
            if (val.valueType !== 'string' && val.valueType !== 'field') {
              this.item.valueType = 'string';
              this.item.value = '';
            }
            if (typeof this.item.value !== 'string') {
              this.item.value = '';
            }
          },
          immediate: true,
          deep: true
        }
      },
      methods: {
        onSelect: function onSelect(item) {
          this.value = item.value;
          this.valueType = item.valueType;
        }
      },
      template: "\n    <devbx-webform-customselect\n        class=\"devbx-webform-cond-string\"\n        :value=\"value\"\n        :value-type=\"valueType\"\n        :options=\"options\"\n        @select=\"onSelect\"\n        >\n    \n    </devbx-webform-customselect>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-cond-array', {
      props: {
        'item': {
          type: Object,
          required: true
        },
        'field': {
          type: Object,
          required: true
        },
        'condFields': {
          type: Array,
          required: true
        }
      },
      data: function data() {
        return {};
      },
      computed: {
        fields: function fields() {
          var result = [];
          this.condFields.forEach(function (item) {
            if (item.type === 'string') result.push(item);
          });
          return result;
        },
        fieldsByName: function fieldsByName() {
          var result = {};
          this.fields.forEach(function (item) {
            result[item.name] = item;
          });
          return result;
        },
        options: function options() {
          var result = [];
          if (this.field.values) {
            this.field.values.forEach(function (value) {
              result.push({
                value: value,
                valueType: 'fieldValue',
                label: value,
                cssClass: 'field-value'
              });
            });
          }
          this.fields.forEach(function (item) {
            if (item.type == 'string') {
              var label = item.name + ' (' + item.label + ')';
              result.push({
                value: item.name,
                valueType: 'field',
                label: label,
                cssClass: 'field'
              });
            }
          });
          var map = {};
          result.forEach(function (item) {
            item.id = item.valueType + ':' + item.value;
            item.selected = false;
            map[item.id] = item;
          });
          if (Array.isArray(this.item.value)) {
            this.item.value.forEach(function (item) {
              var id = item.valueType + ':' + item.value;
              if (map[id]) {
                map[id].selected = true;
              }
            });
          }
          return map;
        },
        selectedOptions: function selectedOptions() {
          var result = [];
          Object.values(this.options).forEach(function (item) {
            if (item.selected) result.push(item);
          });
          return result;
        }
      },
      methods: {
        onSelect: function onSelect(option) {
          this.item.value.push({
            value: option.value,
            valueType: option.valueType
          });
        },
        onRemove: function onRemove(option) {
          var _this = this;
          this.item.value.every(function (item, index) {
            var itemKey = item.valueType + ':' + item.value;
            if (itemKey === option.id) {
              _this.item.value.splice(index, 1);
              return false;
            }
            return true;
          });
        }
      },
      watch: {
        item: {
          handler: function handler(val) {
            if (val.valueType !== 'array') {
              this.item.valueType = 'array';
              this.item.value = [];
            }
            if (!Array.isArray(this.item.value)) {
              this.item.value = [];
            }
          },
          immediate: true,
          deep: true
        }
      },
      template: "\n        <devbx-webform-multiselect \n                :value=\"selectedOptions\"\n                :options=\"options\"\n                @select=\"onSelect($event)\"\n                @remove=\"onRemove($event)\"\n                >\n        </devbx-webform-multiselect>    \n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-cond-date', {
      props: {
        'item': {
          type: Object,
          required: true
        },
        'field': {
          type: Object,
          required: true
        },
        'condConfig': {
          type: Object
        },
        'condFields': {
          type: Array,
          required: true
        },
        'time': {
          type: Boolean,
          "default": false
        }
      },
      computed: {
        value: {
          get: function get() {
            if (typeof this.item.value === 'string') return this.item.value;
            if (typeof this.item.value === 'number') {
              var dateTime = new Date(this.item.value * 1000);
              return BX.date.format(BX.date.convertBitrixFormat(this.time ? BX.message['FORMAT_DATETIME'] : BX.message['FORMAT_DATE']), dateTime);
            }
            return '';
          },
          set: function set(val) {
            //let dateTime = BX.date.parse(val);
            var dateTime = BX.parseDate(val, false);
            if (dateTime) {
              this.item.value = Math.floor(dateTime.getTime() / 1000);
            } else {
              this.item.value = val;
            }
          }
        }
      },
      watch: {
        item: {
          handler: function handler(val) {
            var needValueType = this.time ? 'datetime' : 'date';
            if (val.valueType !== needValueType) {
              this.item.valueType = needValueType;
              this.item.value = '';
            }
            if (typeof this.item.value !== 'string' && typeof this.item.value !== 'number') {
              this.item.value = '';
            }
          },
          immediate: true,
          deep: true
        }
      },
      methods: {
        showCalendar: function showCalendar() {
          BX.calendar({
            node: this.$refs.icon,
            field: this.$refs.input,
            bTime: this.time,
            bHideTime: !this.time
          });
        },
        onChange: function onChange() {
          console.log('change');
        }
      },
      template: "\n    <div class=\"devbx-webform-date-value\">\n        <input ref=\"input\" type=\"text\" v-model=\"value\" @change=\"value = $event.target.value\">\n        <span ref=\"icon\" class=\"devbx-webform-date-icon\" @click.stop.prevent=\"showCalendar\"><i class=\"fa fa-calendar\"></i></span>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-cond-number', {
      props: {
        'item': {
          type: Object,
          required: true
        },
        'field': {
          type: Object,
          required: true
        },
        'condConfig': {
          type: Object
        },
        'condFields': {
          type: Array,
          required: true
        }
      },
      mounted: function mounted() {
        if (this.item.valueType === 'number') {
          var value = parseFloat(this.item.value);
          if (isNaN(value)) value = 0;
          this.item.value = value;
        }
      },
      computed: {
        options: function options() {
          var result = [];
          this.condFields.forEach(function (item) {
            if (item.type === 'number') {
              result.push({
                value: item.name,
                valueType: 'field',
                label: item.name + ' (' + item.label + ')',
                cssClass: 'cond-field'
              });
            }
          });
          return result;
        },
        value: {
          get: function get() {
            if (typeof this.item.value === 'number' || typeof this.item.value === 'string') return this.item.value;
            return '';
          },
          set: function set(val) {
            this.item.value = val;
          }
        },
        valueType: {
          get: function get() {
            if (!!this.item.valueType) return this.item.valueType;
            return 'number';
          },
          set: function set(val) {
            this.item.valueType = val;
          }
        }
      },
      watch: {
        item: {
          handler: function handler(val) {
            if (val.valueType !== 'number' && val.valueType !== 'field') {
              this.item.valueType = 'number';
              this.item.value = '';
            }
            if (typeof this.item.value !== 'number' && typeof this.item.value !== 'string') {
              this.item.value = 0;
            }
          },
          immediate: true,
          deep: true
        }
      },
      methods: {
        onSelect: function onSelect(item) {
          this.value = item.value;
          this.valueType = item.valueType;
        }
      },
      template: "\n    <devbx-webform-customselect\n        class=\"devbx-webform-cond-number\"\n        :value=\"value\"\n        :value-type=\"valueType\"\n        :options=\"options\"\n        default-value-type=\"number\"\n        @select=\"onSelect\"\n        >\n    \n    </devbx-webform-customselect>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-cond-basic', {
      props: {
        'groups': {
          type: Array,
          required: true
        },
        'condConfig': {
          type: Object,
          required: true
        },
        'condFields': {
          type: Array,
          required: true
        }
      },
      methods: {
        removeGroup: function removeGroup(index) {
          this.groups.splice(index, 1);
        }
      },
      template: "\n    <div>\n        <div  v-for=\"(group, index) in groups\" :key=\"index\">\n            <div class=\"devbx-webform-cond-or\" v-if=\"index>0\">\n                <hr class=\"devbx-webform-cond-line\">\n                <span class=\"devbx-webform-cond-label\">\n                    {{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_LOGIN_OR')}}\n                </span>\n                <hr class=\"devbx-webform-cond-line\">                        \n            </div>\n\n            <devbx-webform-cond-group :group=\"group\" :cond-config=\"condConfig\" :cond-fields=\"condFields\" @removegroup=\"removeGroup(index)\">\n            </devbx-webform-cond-group>\n        </div>        \n        \n        <a href=\"#\" @click.stop.prevent=\"$emit('addgroup')\" class=\"devbx-webform-cond-add-group\">\n            <hr class=\"devbx-webform-cond-line\">        \n            <span class=\"devbx-webform-cond-label\">\n                <i class=\"fa fa-plus\"></i>\n                <span>{{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_LOGIN_OR')}}</span>\n            </span>            \n            <hr class=\"devbx-webform-cond-line\">\n        </a>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-cond-code', {
      props: ['code'],
      data: function data() {
        return {
          view: false
        };
      },
      mounted: function mounted() {
        this.view = new DevBX.Forms.CodeMirror.EditorView({
          doc: this.code,
          extensions: [DevBX.Forms.CodeMirror.basicSetup, DevBX.Forms.CodeMirror.javascript()],
          parent: this.$refs.condCode,
          dispatch: BX.delegate(this.editorTr, this)
        });
      },
      beforeUnmount: function beforeUnmount() {
        this.view.destroy();
      },
      methods: {
        editorTr: function editorTr(tr) {
          this.view.update([tr]);
          if (!tr.changes.empty) {
            var code = this.view.state.doc.text.join("\n");
            this.$emit('input', code);
          }
        }
      },
      template: "\n    <div style=\"height: calc(100% - 50px);\">\n        <div ref=\"condCode\" class=\"devbx-webform-cond-code\"></div> \n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-cond-enum', {
      props: {
        'item': {
          type: Object,
          required: true
        },
        'field': {
          type: Object,
          required: true
        },
        'condFields': {
          type: Array,
          required: true
        }
      },
      data: function data() {
        return {};
      },
      computed: {
        fields: function fields() {
          var result = [];
          this.condFields.forEach(function (item) {
            if (item.type === 'string') result.push(item);
          });
          return result;
        },
        fieldsByName: function fieldsByName() {
          var result = {};
          this.fields.forEach(function (item) {
            result[item.name] = item;
          });
          return result;
        },
        options: function options() {
          var result = [];
          debugger;
          if (this.field.values) {
            this.field.values.forEach(function (value) {
              result.push({
                value: value.value,
                valueType: 'fieldValue',
                label: value.title,
                cssClass: 'field-value'
              });
            });
          }
          this.fields.forEach(function (item) {
            if (item.type == 'string') {
              var label = item.name + ' (' + item.label + ')';
              result.push({
                value: item.name,
                valueType: 'field',
                label: label,
                cssClass: 'field'
              });
            }
          });
          var map = {};
          result.forEach(function (item) {
            item.id = item.valueType + ':' + item.value;
            item.selected = false;
            map[item.id] = item;
          });
          if (Array.isArray(this.item.value)) {
            this.item.value.forEach(function (item) {
              var id = item.valueType + ':' + item.value;
              if (map[id]) {
                map[id].selected = true;
              }
            });
          }
          return map;
        },
        selectedOptions: function selectedOptions() {
          var result = [];
          Object.values(this.options).forEach(function (item) {
            if (item.selected) result.push(item);
          });
          return result;
        }
      },
      methods: {
        onSelect: function onSelect(option) {
          this.item.value.push({
            value: option.value,
            valueType: option.valueType
          });
        },
        onRemove: function onRemove(option) {
          var _this = this;
          this.item.value.every(function (item, index) {
            var itemKey = item.valueType + ':' + item.value;
            if (itemKey === option.id) {
              _this.item.value.splice(index, 1);
              return false;
            }
            return true;
          });
        }
      },
      watch: {
        item: {
          handler: function handler(val) {
            if (val.valueType !== 'array') {
              this.item.valueType = 'array';
              this.item.value = [];
            }
            if (!Array.isArray(this.item.value)) {
              this.item.value = [];
            }
          },
          immediate: true,
          deep: true
        }
      },
      template: "\n        <devbx-webform-multiselect \n                :value=\"selectedOptions\"\n                :options=\"options\"\n                @select=\"onSelect($event)\"\n                @remove=\"onRemove($event)\"\n                >\n        </devbx-webform-multiselect>    \n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-popup-condition-wizard', {
      props: {
        'code': {
          type: String,
          required: true
        },
        'condFields': {
          type: Array,
          required: true
        }
      },
      data: function data() {
        return {
          groups: [],
          //визуальные группы кода
          editCode: '',
          //редактируемый код правила
          mode: 'basic',
          //режим редактирования (basic - визуальный конструктор, code - визуальный редактор кода)
          error: '',
          //текст ошибки которая выводится в подвале
          condConfig: false
        };
      },
      created: function created() {
        this.condConfig = new WebFormCondConfig();
      },
      mounted: function mounted() {
        var popup = BX.PopupWindowManager.getPopupById(this.$parent.popupId);
        if (popup) {
          popup.contentContainer.appendChild(this.$el);
          popup.show();
        }
        if (this.code.length) {
          var parsedGroups = this.parseCodeToGroup(this.code);
          if (parsedGroups && !this.condConfig.checkGroupCond(this.$root.formFieldByName, parsedGroups).length) {
            this.groups = parsedGroups;
          } else {
            this.editCode = this.code;
            this.mode = 'code';
          }
        }
        if (!this.groups.length) this.addGroup();
      },
      methods: {
        parseCode: function parseCode(code) {
          this.error = false;
          try {
            var lexer = new DevBX.MSLang.CodeLexer(code);
            var parser = new DevBX.MSLang.CodeParser(lexer);
            var nodeList = [];
            parser.parseCode(nodeList, true, true, DevBX.MSLang.LexerTypeArray.one(DevBX.MSLang.LexerType.ltEof));
            return nodeList;
          } catch (e) {}
          return false;
        },
        parseCodeToGroup: function parseCodeToGroup(code) {
          var nodeList = this.parseCode(code);
          if (!nodeList) return false;
          return this.condConfig.codeToWizardBuilder(nodeList, this.fieldsByName);
        },
        addGroup: function addGroup() {
          var items = [];
          if (this.$root.formFields.length) {
            var field = this.$root.formFields[0],
              obCond = this.condConfig.fieldsType[field.type];
            if (obCond) {
              items.push({
                field: field.name,
                type: obCond.conditions[0].value
              });
            }
          }
          this.groups.push(items);
        },
        convertGroupToCode: function convertGroupToCode() {
          return this.condConfig.compileGroupCond(this.$root.formFieldByName, this.groups);
        },
        setMode: function setMode(mode) {
          if (this.mode == mode) return;
          if (this.mode == 'basic') {
            this.editCode = this.convertGroupToCode();
          } else {
            if (!this.editCode.trim().length) {
              this.groups = [];
              this.mode = mode;
              return;
            }
            var nodeList = this.parseCode(this.editCode);
            if (!nodeList) {
              this.error = this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_PARSE_COND_ERROR');
              return;
            }
            var parsedGroups = this.condConfig.codeToWizardBuilder(nodeList, this.fieldsByName);
            if (!parsedGroups) {
              this.$root.showPopupError(this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_ERR_FAILED_CONVERT_CODE'));
              return;
            }
            var errors = this.condConfig.checkGroupCond(this.$root.formFieldByName, parsedGroups);
            if (errors.length) {
              this.$root.showPopupError(errors.join('<br>'));
              return;
            } else {
              this.groups = parsedGroups;
            }
          }
          this.mode = mode;
        },
        getCodeResult: function getCodeResult() {
          this.error = false;
          if (this.mode == 'basic') return this.convertGroupToCode();
          if (this.parseCode(this.editCode) === false) {
            this.error = this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_PARSE_COND_ERROR');
            return false;
          }
          return this.editCode;
        }
      },
      computed: {
        fieldsByName: function fieldsByName() {
          var result = {};
          this.condFields.forEach(function (item) {
            result[item.name] = item;
          });
          return result;
        }
      },
      template: "\n    <div class=\"devbx-webform-cond-wizard\">\n        <div class=\"devbx-webform-cond-tabs\">\n            <span class=\"devbx-webform-page-button\" \n            :class=\"{'devbx-webform-page-button-active': mode=='basic'}\" \n            @click.stop.prevent=\"setMode('basic')\">{{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_BASE_EDITOR_TITLE')}}</span>\n            <span class=\"devbx-webform-page-button\" \n            :class=\"{'devbx-webform-page-button-active': mode=='code'}\" \n            @click.stop.prevent=\"setMode('code')\">{{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_CODE_EDITOR_TITLE')}}</span>\n        </div>\n        \n        <devbx-webform-cond-basic v-if=\"mode == 'basic'\" \n            :groups=\"groups\" \n            :cond-config=\"condConfig\"\n            :cond-fields=\"condFields\"\n            @addgroup=\"addGroup()\" \n            ></devbx-webform-cond-basic>\n        <devbx-webform-cond-code v-else :code=\"editCode\" @input=\"editCode = $event\"></devbx-webform-cond-code>\n        \n        <div class=\"devbx-webform-cond-error\" v-if=\"error\">\n            <i class=\"fa fa-exclamation-triangle\"></i>\n            <span class=\"devbx-webform-message\">{{error}}</span>\n        </div>\n            \n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-condition', {
      props: {
        'title': {
          type: String,
          "default": ''
        },
        'formData': {
          type: Object,
          required: true
        },
        'fieldName': {
          type: String,
          required: true
        },
        'wizardTitle': {
          type: String,
          "default": ''
        },
        'defaultValue': {
          type: String,
          required: true
        },
        'condFields': {
          type: Array,
          required: true
        },
        'options': {
          type: Array,
          "default": function _default() {
            var result = [];
            result.push({
              value: 'always',
              title: BX.message('DEVBX_WEB_FORM_FIELD_RULE_ALWAYS')
            });
            result.push({
              value: 'when',
              title: BX.message('DEVBX_WEB_FORM_FIELD_RULE_WHEN')
            });
            result.push({
              value: 'never',
              title: BX.message('DEVBX_WEB_FORM_FIELD_RULE_NEVER')
            });
            return result;
          }
        }
      },
      data: function data() {
        return {
          popupId: false
        };
      },
      beforeUnmount: function beforeUnmount() {
        if (this.popupId) {
          var popup = BX.PopupWindowManager.getPopupById(this.popupId);
          if (popup) popup.close();
        }
      },
      computed: {
        rule: function rule() {
          return this.formData[this.fieldName];
        },
        selectOptions: function selectOptions() {
          var result = [];
          this.options.forEach(function (item) {
            if (typeof item === 'string') {
              result.push({
                value: item,
                title: BX.message('DEVBX_WEB_FORM_FIELD_RULE_' + item.toUpperCase())
              });
            } else {
              result.push(item);
            }
          });
          return result;
        }
      },
      methods: {
        editRule: function editRule() {
          var options = {
            autoHide: false,
            draggable: false,
            closeByEsc: true,
            closeIcon: true,
            fixed: true,
            overlay: {
              backgroundColor: 'black',
              opacity: '80'
            },
            offsetLeft: 0,
            offsetTop: 0,
            bindOptions: {
              forceBindPosition: false
            },
            //bindOnResize: true,
            titleBar: this.wizardTitle,
            content: '',
            className: 'devbx-webform-builder devbx-webform-condition-popup',
            maxWidth: 800,
            maxHeight: 580,
            events: {
              onPopupClose: BX.delegate(this.onPopupClose, this),
              onPopupDestroy: BX.delegate(this.onPopupDestroy, this)
            },
            buttons: [new BX.PopupWindowButton({
              text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_DIALOG_BTN_SAVE_TITLE'),
              className: "popup-window-button-accept",
              events: {
                click: BX.delegate(this.saveCode, this)
              }
            }), new BX.PopupWindowButton({
              text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_DIALOG_BTN_CLOSE_TITLE'),
              className: "popup-window-button-link popup-window-button-link-cancel",
              events: {
                click: BX.delegate(this.closePopup, this)
              }
            })]
          };
          var id = "DevBxFormsConditionWizardPopup";
          new BX.PopupWindow(id, null, options);
          this.popupId = id;
        },
        saveCode: function saveCode() {
          var result = this.$refs.wizard.getCodeResult();
          if (result === false) return;
          this.rule.code = result;
          this.closePopup();
        },
        closePopup: function closePopup() {
          var popup = BX.PopupWindowManager.getPopupById(this.popupId);
          if (popup) popup.close();
        },
        onPopupClose: function onPopupClose(popup) {
          if (!this.rule.code.length && this.defaultValue) {
            this.rule.value = this.defaultValue;
          }
          popup.destroy();
        },
        onPopupDestroy: function onPopupDestroy() {
          this.popupId = false;
        }
      },
      watch: {
        'rule.value': function ruleValue(val) {
          if (val === 'when') {
            this.editRule();
          }
        }
      },
      template: "\n<div class=\"devbx-webform-field\">\n    <div class=\"devbx-webform-label\">{{title}}</div>\n    <div class=\"devbx-webform-edit\">\n        <select v-model=\"rule.value\">\n            <option v-for=\"option in selectOptions\" :key=\"option.value\" :value=\"option.value\">{{option.title}}</option>                    \n        </select>\n        \n        <div v-if=\"rule.value == 'when'\" class=\"devbx-webform-edit-rule\">\n            <span class=\"devbx-webform-edit-rule-link\" @click.stop.prevent=\"editRule()\">{{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_EDIT_CONDITION')}}</span>\n        </div>        \n        \n        <devbx-webform-popup-condition-wizard ref=\"wizard\" v-if=\"popupId\" :code=\"rule.code\" :cond-fields=\"condFields\">\n        </devbx-webform-popup-condition-wizard>\n    </div>\n</div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-form-empty-cell', {
      props: ['page', 'row', 'item', 'active', 'checkItems', 'deleteBlur'],
      watch: {
        active: function active(val) {
          if (!val) {
            if (this.checkItems) this.$root.checkPageItems();
            if (this.deleteBlur) {
              var cell = this.$root.getLayoutItemById(this.item.id, true);
              if (cell) {
                cell.row.items.splice(cell.index, 1);
              }
            }
          }
        }
      },
      computed: {
        panelItems: function panelItems() {
          var panel = {
            data: {},
            items: []
          };
          panel.items.push({
            name: 'devbx-webform-caption',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_PANEL_ADD_NEW_FIELD_CAPTION')
            }
          });
          this.$root.formElements.forEach(function (group) {
            panel.items.push({
              name: 'devbx-webform-separator',
              props: {
                title: group.name
              }
            });
            group.items.forEach(function (item) {
              panel.items.push({
                name: 'devbx-form-panel-item',
                props: {
                  fieldId: item.data.fieldId,
                  icon: item.data.icon
                }
              });
            });
          });
          return panel;
        }
      },
      methods: {
        drag: function drag(event) {
          if (this.$root.dragItemId) {
            var item = this.$root.getLayoutItemById(this.$root.dragItemId);
            if (item && item.component && item.component.childLayout) {
              if (item.component.childLayout.containsItemId(this.item.id)) {
                return;
              }
            }
          }
          event.preventDefault();
        },
        dragOver: function dragOver(event) {
          if (this.$root.dragItemId) {
            var item = this.$root.getLayoutItemById(this.$root.dragItemId);
            if (item && item.component && item.component.childLayout) {
              if (item.component.childLayout.containsItemId(this.item.id)) {
                return;
              }
            }
          }
          event.preventDefault();
        },
        drop: function drop(event) {
          var data = this.$root.dragData,
            newItem,
            idx;
          this.$root.dragData = false;
          switch (data.type) {
            case 'new':
              var formElement = this.$root.getFormElementById(data.id);
              if (!formElement) return;
              idx = this.row.items.indexOf(this.item);
              newItem = this.row.createItem({
                component: false,
                id: this.$root.getNewItemId(),
                fieldId: data.id,
                template: formElement.data.layoutTemplate,
                size: formElement.data.defaultSize,
                minSize: formElement.data.minSize,
                props: {},
                config: formElement.data.defaultConfig()
              });
              if (newItem.size >= this.item.size) {
                newItem.size = this.item.size;
              }
              newItem.checkFieldName();
              this.row.replaceCell(idx, newItem);
              break;
            case 'item':
              var cell = this.$root.getLayoutItemById(data.id, true);
              if (!cell) return;
              cell.item.saveConfig();
              newItem = this.row.createItem({
                component: false,
                id: this.$root.getNewItemId(),
                fieldId: cell.item.fieldId,
                template: cell.item.template,
                size: cell.item.size,
                minSize: cell.item.minSize,
                props: JSON.parse(JSON.stringify(cell.item.props)),
                config: JSON.parse(JSON.stringify(cell.item.config))
              });
              cell.row.deleteItem(cell.item);
              var freeSpace = this.row.getRowFreeSpace();
              if (newItem.size > freeSpace) {
                newItem.size = freeSpace;
              }
              this.row.replaceCell(this.row.items.indexOf(this.item), newItem);
              break;
          }
        }
      },
      template: "\n    <div class=\"devbx-webform-layout-element-empty\" @dragenter=\"drag\" @drop=\"drop\" @dragover=\"dragOver\">\n        <i class=\"fa fa-plus\"></i>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-form-layout-toolbar', {
      props: ['activeComponent', 'activeCellSize', 'dragItemId'],
      data: function data() {
        return {
          position: {
            top: 0,
            left: 0
          },
          selectedSubMenuId: false
        };
      },
      watch: {
        activeComponent: function activeComponent(val) {
          this.selectedSubMenuId = false;
          var self = this;
          setTimeout(function () {
            self.updatePosition();
          }, 0);
        },
        activeCellSize: function activeCellSize(val) {},
        dragItemId: function dragItemId(val) {},
        panelActive: function panelActive(val) {
          if (val) {
            var self = this;
            setTimeout(function () {
              self.updatePosition();
            }, 0);
          }
        }
      },
      methods: {
        updatePosition: function updatePosition() {
          if (this.panelOptions.length > 0) {
            var appRect = this.$root.$el.getBoundingClientRect(),
              itemRect = this.activeComponent.$el.getBoundingClientRect();
            this.position.top = itemRect.top - appRect.top;
            this.position.left = itemRect.left - appRect.left + itemRect.width / 2;
            var panelWidth = this.panelOptions.length * 46;

            //this.position.top -= this.$el.clientHeight;
            //this.position.left -= this.$el.clientWidth/2;
            this.position.top -= 48;
            this.position.left -= panelWidth / 2;
            if (this.position.left + panelWidth > appRect.width) {
              this.position.left = appRect.width - panelWidth;
            }
          }
        },
        deleteItem: function deleteItem() {
          if (!this.activeComponent) return;
          this.$root.deleteItemById(this.$root.activeId);
        },
        insertFieldAbove: function insertFieldAbove() {
          var cell = this.$root.getLayoutItemById(this.$root.activeId, true);
          if (!cell) return;
          var emptyItem = this.$root.getNewEmptyCell(12);
          emptyItem.props.deleteBlur = true;
          cell.page.addRow(cell.rowNum).items.push(emptyItem);
          this.$root.activeId = emptyItem.id;
        },
        insertFieldBelow: function insertFieldBelow() {
          var cell = this.$root.getLayoutItemById(this.$root.activeId, true);
          if (!cell) return;
          var emptyItem = this.$root.getNewEmptyCell(12);
          emptyItem.props.deleteBlur = true;
          cell.page.addRow(cell.rowNum + 1).items.push(emptyItem);
          this.$root.activeId = emptyItem.id;
        },
        insertFieldBefore: function insertFieldBefore() {
          var cell = this.$root.getLayoutItemById(this.$root.activeId, true);
          if (!cell) return;
          cell.row.insertFreeSpace(cell.index);
        },
        insertFieldAfter: function insertFieldAfter() {
          var cell = this.$root.getLayoutItemById(this.$root.activeId, true);
          if (!cell) return;
          var nextItem = cell.row.items[cell.index + 1];
          if (nextItem && nextItem.template === this.$root.emptyCellTemplate) {
            if (nextItem.size === 0) {
              nextItem.size = cell.row.rowReleaseSpaceBoth(cell.index, 3);
            }
            this.$root.activeId = nextItem.id;
            return;
          }
          cell.row.insertFreeSpace(cell.index + 1);
        },
        makeSmaller: function makeSmaller() {
          if (!this.activeComponent) return;
          this.activeComponent.item.size--;
          this.$root.checkPageItems();
        },
        makeBigger: function makeBigger() {
          if (!this.activeComponent) return;
          var cell = this.$root.getLayoutItemById(this.activeComponent.item.id, true);
          if (!cell) return;
          if (cell.row.rowReleaseSpace(cell.index + 1, 1) > 0) {
            this.activeComponent.item.size++;
            this.$root.checkPageItems();
          } else if (cell.row.rowReleaseSpace(cell.index - 1, 1, true) > 0) {
            this.activeComponent.item.size++;
            this.$root.checkPageItems();
          }
        },
        allowJustifyRow: function allowJustifyRow() {
          var _this = this;
          if (!this.activeComponent) return false;
          var cell = this.$root.getLayoutItemById(this.activeComponent.item.id, true);
          if (!cell) return false;
          var newItems = [];
          cell.row.items.forEach(function (item) {
            if (item.template !== _this.$root.emptyCellTemplate) newItems.push(item);
          });
          if (!newItems.length) return false;
          var result = false,
            leftSize = this.$root.maxRowSize,
            leftCnt = newItems.length;
          newItems.forEach(function (item) {
            var newSize = Math.ceil(leftSize / leftCnt);
            if (item.minSize > newSize) newSize = item.minSize;
            if (newSize > leftSize) newSize = leftSize;
            if (item.size !== newSize) {
              result = true;
            }
            leftSize -= newSize;
            leftCnt--;
          });
          return result;
        },
        justifyRow: function justifyRow() {
          var _this2 = this;
          if (!this.activeComponent) return;
          var cell = this.$root.getLayoutItemById(this.activeComponent.item.id, true);
          if (!cell) return;
          var newItems = [];
          cell.row.items.forEach(function (item) {
            if (item.template !== _this2.$root.emptyCellTemplate) newItems.push(item);
          });
          if (!newItems.length) return;
          var leftSize = this.$root.maxRowSize,
            leftCnt = newItems.length;
          newItems.forEach(function (item) {
            var newSize = Math.ceil(leftSize / leftCnt);
            if (item.minSize > newSize) newSize = item.minSize;
            if (newSize > leftSize) newSize = leftSize;
            item.size = newSize;
            leftSize -= newSize;
            leftCnt--;
          });
          newItems.push(this.$root.getNewEmptyCell(0));
          cell.row.items = newItems;
        },
        cutItem: function cutItem() {
          if (this.$root.clipboardItemId === this.$root.activeId && this.$root.clipboardOperation === 'CUT') {
            this.$root.clipboardItemId = false;
            this.$root.clipboardOperation = false;
            return;
          }
          this.$root.clipboardItemId = this.$root.activeId;
          this.$root.clipboardOperation = 'CUT';
        },
        copyItem: function copyItem() {
          if (this.$root.clipboardItemId === this.$root.activeId && this.$root.clipboardOperation === 'COPY') {
            this.$root.clipboardItemId = false;
            this.$root.clipboardOperation = false;
            return;
          }
          this.$root.clipboardItemId = this.$root.activeId;
          this.$root.clipboardOperation = 'COPY';
        },
        itemAction: function itemAction(event, item) {
          if (item.disabled) return;
          this.selectedSubMenuId = false;
          this[item.action](item);
        },
        selectSubMenu: function selectSubMenu(item) {
          if (this.selectedSubMenuId === item.id) {
            this.selectedSubMenuId = false;
            return;
          }
          this.selectedSubMenuId = item.id;
        },
        pasteItem: function pasteItem() {
          this.$root.pasteItemFromClipboard(this.$root.getLayoutItemById(this.activeComponent.item.id, true));
        },
        pasteItemAbove: function pasteItemAbove() {
          var cell = this.$root.getLayoutItemById(this.activeComponent.item.id, true);
          if (!cell) return;
          var row = cell.page.addRow(cell.rowNum),
            emptyItem = this.$root.getNewEmptyCell(cell.item.size);
          row.items.push(emptyItem);
          var destCell = this.$root.getLayoutItemById(emptyItem.id, true);
          this.$root.pasteItemFromClipboard(destCell);
        },
        pasteItemBelow: function pasteItemBelow() {
          var cell = this.$root.getLayoutItemById(this.activeComponent.item.id, true);
          if (!cell) return;
          var row = cell.page.addRow(cell.rowNum + 1),
            emptyItem = this.$root.getNewEmptyCell(cell.item.size);
          row.items.push(emptyItem);
          var destCell = this.$root.getLayoutItemById(emptyItem.id, true);
          this.$root.pasteItemFromClipboard(destCell);
        },
        pasteItemBefore: function pasteItemBefore() {
          var cell = this.$root.getLayoutItemById(this.activeComponent.item.id, true),
            copyCell = this.$root.getLayoutItemById(this.$root.clipboardItemId, true);
          if (!cell || !copyCell) return;
          var freeSpace = cell.row.getRowFreeSpace(),
            size = copyCell.item.size;
          if (size > freeSpace && cell.item.id === copyCell.item.id) {
            size = Math.max(3, Math.floor(size / 2));
          }
          if (size > freeSpace) {
            size = cell.row.rowReleaseSpace(cell.index, size, true);
          } else if (size > copyCell.item.size) {
            size = copyCell.item.size;
          }
          var emptyItem = this.$root.getNewEmptyCell(size);
          cell.row.items.splice(cell.index, 0, emptyItem);
          var destCell = this.$root.getLayoutItemById(emptyItem.id, true);
          this.$root.pasteItemFromClipboard(destCell);
        },
        pasteItemAfter: function pasteItemAfter() {
          var cell = this.$root.getLayoutItemById(this.activeComponent.item.id, true),
            copyCell = this.$root.getLayoutItemById(this.$root.clipboardItemId, true);
          if (!cell || !copyCell) return;
          var freeSpace = cell.row.getRowFreeSpace(),
            size = copyCell.item.size;
          if (size > freeSpace && cell.item.id === copyCell.item.id) {
            size = Math.max(3, Math.floor(size / 2));
          }
          if (size > freeSpace) {
            size = cell.row.rowReleaseSpace(cell.index + 1, size, true);
          } else if (size > copyCell.item.size) {
            size = copyCell.item.size;
          }
          var emptyItem = this.$root.getNewEmptyCell(size);
          cell.row.items.splice(cell.index + 1, 0, emptyItem);
          var destCell = this.$root.getLayoutItemById(emptyItem.id, true);
          this.$root.pasteItemFromClipboard(destCell);
        }
      },
      computed: {
        panelActive: function panelActive() {
          return this.panelOptions.length > 0 && !this.$root.resizeItemId && this.dragItemId == '';
        },
        panelOptions: function panelOptions() {
          var _this3 = this;
          var items = [];
          if (!this.activeComponent || !this.activeComponent.item) return items;
          var cell = this.$root.layoutItems.getItemById(this.activeComponent.item.id, true),
            copyCell = this.$root.layoutItems.getItemById(this.$root.clipboardItemId);
          if (!cell) return items;
          var disableInsertClipboard = false;
          if (copyCell && copyCell.component && copyCell.component.childLayout) {
            disableInsertClipboard = !!copyCell.component.childLayout.containsItemId(this.activeComponent.item.id);
          }
          var t = this.activeComponent.$.type,
            name = false;
          Object.keys(this.$.appContext.components).every(function (k) {
            if (_this3.$.appContext.components[k] === t) name = k;
            return name === false;
          });
          if (name === this.$root.emptyCellTemplate) {
            if (!disableInsertClipboard && copyCell) {
              items.push({
                id: 'pasteItem',
                icon: 'paste',
                action: 'pasteItem',
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_TOOLBAR_PASTE')
              });
            }
            return items;
          }
          items.push({
            id: 'cutItem',
            icon: 'cut',
            action: 'cutItem',
            title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_TOOLBAR_CUT'),
            selected: this.$root.clipboardItemId === cell.item.id && this.$root.clipboardOperation === 'CUT'
          });
          items.push({
            id: 'copyItem',
            icon: 'copy',
            action: 'copyItem',
            title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_TOOLBAR_COPY'),
            selected: this.$root.clipboardItemId === cell.item.id && this.$root.clipboardOperation === 'COPY'
          });
          if (!disableInsertClipboard && this.$root.getLayoutItemById(this.$root.clipboardItemId)) {
            var children = [{
              id: 'pasteItemAbove',
              icon: 'arrow-up',
              action: 'pasteItemAbove',
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_TOOLBAR_PASTE_ABOVE')
            }, {
              id: 'pasteItemBelow',
              icon: 'arrow-down',
              action: 'pasteItemBelow',
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_TOOLBAR_PASTE_BELOW')
            }];
            if (cell.row.getRowMaxReleaseSpace() >= 3) {
              children.push({
                id: 'pasteItemBefore',
                icon: 'arrow-left',
                action: 'pasteItemBefore',
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_TOOLBAR_PASTE_BEFORE')
              });
              children.push({
                id: 'pasteItemAfter',
                icon: 'arrow-right',
                action: 'pasteItemAfter',
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_TOOLBAR_PASTE_AFTER')
              });
            }
            items.push({
              id: 'pasteItem',
              icon: 'paste',
              action: 'selectSubMenu',
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_TOOLBAR_PASTE'),
              selected: this.selectedSubMenuId === 'pasteItem',
              children: children
            });
          }
          items.push({
            id: 'deleteItem',
            icon: 'trash',
            action: 'deleteItem',
            title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_TOOLBAR_DELETE')
          });
          if (!disableInsertClipboard) {
            var subItems = [{
              id: 'insertFieldAbove',
              icon: 'arrow-up',
              action: 'insertFieldAbove',
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_TOOLBAR_INSERT_FIELD_ABOVE')
            }, {
              id: 'insertFieldBelow',
              icon: 'arrow-down',
              action: 'insertFieldBelow',
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_TOOLBAR_INSERT_FIELD_BELOW')
            }];
            if (cell.row.getRowMaxReleaseSpace() >= 3) {
              subItems.push({
                id: 'insertFieldBefore',
                icon: 'arrow-left',
                action: 'insertFieldBefore',
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_TOOLBAR_INSERT_FIELD_BEFORE')
              });
              subItems.push({
                id: 'insertFieldAfter',
                icon: 'arrow-right',
                action: 'insertFieldAfter',
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_TOOLBAR_INSERT_FIELD_AFTER')
              });
            }
            items.push({
              id: 'insertField',
              icon: 'plus',
              action: 'selectSubMenu',
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_TOOLBAR_INSERT_FIELD'),
              selected: this.selectedSubMenuId === 'insertField',
              children: subItems
            });
          }
          items.push({
            id: 'makeSmaller',
            icon: 'arrow-left',
            action: 'makeSmaller',
            title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_TOOLBAR_MAKE_SMALLER'),
            disabled: cell.item.minSize >= cell.item.size
          });
          items.push({
            id: 'makeBigger',
            icon: 'arrow-right',
            action: 'makeBigger',
            title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_TOOLBAR_MAKE_BIGGER'),
            disabled: cell.row.getRowMaxReleaseSpace(cell.index) === 0
          });
          items.push({
            id: 'justifyRow',
            icon: 'table',
            action: 'justifyRow',
            title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_TOOLBAR_JUSTIFY_ROW'),
            disabled: !this.allowJustifyRow()
          });
          return items;
        }
      },
      templateOld: "\n<div :class=\"{'devbx-webform-layout-toolbar': true, 'devbx-webform-layout-toolbar-active': panelActive}\" :style=\"{position: 'absolute', top: position.top+'px', left: position.left+'px'}\">\n<transition-group name=\"devbx-webform-lauout-toolbar-items\">\n    <div :class=\"{'devbx-webform-layout-toolbar-option': true, disabled: item.disabled, selected: item.selected}\" v-for=\"item of panelOptions\" @click=\"itemAction($event, item)\" :key=\"item.id\">\n        <div :class=\"['fa', 'fa-'+item.icon]\"></div>\n        <div class=\"devbx-webform-layout-toolbar-option-title\">{{item.title}}</div>\n        \n        <div class=\"devbx-webform-layout-toolbar-option-children\" v-if=\"item.children\">\n            <div :class=\"{'devbx-webform-layout-toolbar-option': true, disabled: child.disabled, selected: child.selected}\" v-for=\"child of item.children\" @click=\"itemAction($event, child)\" :key=\"child.id\">\n                <div :class=\"['fa', 'fa-'+child.icon]\"></div>\n                <div class=\"devbx-webform-layout-toolbar-option-title\">{{child.title}}</div>\n            </div>\n        </div>\n    </div>\n    </transition-group>\n</div>",
      template: "\n<div :class=\"{'devbx-webform-layout-toolbar': true, 'devbx-webform-layout-toolbar-active': panelActive}\" :style=\"{position: 'absolute', top: position.top+'px', left: position.left+'px'}\">\n    <div :class=\"{'devbx-webform-layout-toolbar-option': true, disabled: item.disabled, selected: item.selected}\" v-for=\"item of panelOptions\" @click=\"itemAction($event, item)\" :key=\"item.id\">\n        <div :class=\"['fa', 'fa-'+item.icon]\"></div>\n        <div class=\"devbx-webform-layout-toolbar-option-title\">{{item.title}}</div>\n        \n        <div class=\"devbx-webform-layout-toolbar-option-children\" v-if=\"item.children\">\n            <div :class=\"{'devbx-webform-layout-toolbar-option': true, disabled: child.disabled, selected: child.selected}\" v-for=\"child of item.children\" @click=\"itemAction($event, child)\" :key=\"child.id\">\n                <div :class=\"['fa', 'fa-'+child.icon]\"></div>\n                <div class=\"devbx-webform-layout-toolbar-option-title\">{{child.title}}</div>\n            </div>\n        </div>\n    </div>\n</div>        \n        "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-form-panel-item', {
      props: {
        formData: {
          type: Object
        },
        fieldId: {
          type: String,
          required: true
        },
        icon: {
          type: String,
          "default": ''
        }
      },
      data: function data() {
        return {
          hover: false,
          dragged: false
        };
      },
      computed: {
        title: function title() {
          return this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_PANEL_ITEM_FIELD_' + this.fieldId.toUpperCase() + '_NAME');
        },
        hint: function hint() {
          return this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_' + this.fieldId.toUpperCase() + '_HINT');
        },
        iconClass: function iconClass() {
          return 'fa fa-' + this.icon;
        }
      },
      methods: {
        dragStart: function dragStart(event) {
          this.$root.dragData = {
            type: 'new',
            id: this.fieldId
          };
          this.dragged = true;
        },
        dragEnd: function dragEnd() {
          this.dragged = false;
          this.$root.dragData = false;
        },
        clickItem: function clickItem() {
          var formElement = this.$root.getFormElementById(this.fieldId),
            emptyCell = this.$root.getLayoutItemById(this.$root.activeId, true);
          if (!formElement || !emptyCell || emptyCell.item.fieldId !== 'empty') return;
          var item = emptyCell.row.createItem({
            component: false,
            id: this.$root.getNewItemId(),
            fieldId: this.fieldId,
            template: formElement.data.layoutTemplate,
            size: formElement.data.defaultSize,
            minSize: formElement.data.minSize,
            props: {},
            config: formElement.data.defaultConfig()
          });
          item.checkFieldName();
          if (item.size >= emptyCell.item.size) {
            item.size = emptyCell.item.size;
          }
          emptyCell.row.replaceCell(emptyCell.index, item);
        }
      },
      template: "\n<div class=\"devbx-form-element\">\n    <div class=\"devbx-form-element-content\" draggable=\"true\" @click=\"clickItem\" @dragstart=\"dragStart\" @dragend=\"dragEnd\" @mouseenter=\"hover = true\" @mouseleave=\"hover = false\">\n        <span class=\"devbx-form-element-label\"><i :class=\"iconClass\"></i><span>{{title}}</span></span>\n    </div>\n    <transition name=\"devbx-hint\">\n        <div v-if=\"hover && !dragged\" class=\"devbx-webform-element-hint\">\n            {{hint}}    \n        </div>\n    </transition>\n</div>\n"
    });
  });

  var fieldMixin = {
    props: ['page', 'item', 'row', 'active', 'config'],
    mounted: function mounted() {
      if (typeof this.getFields === 'function') {
        this.item.fields = this.getFields();
      }
    },
    computed: {
      labelFormatted: function labelFormatted() {
        return this.$root.htmlFormatFields(BX.util.htmlspecialchars(this.config.label));
      },
      helpTextFormatted: function helpTextFormatted() {
        return this.$root.htmlFormatFields(this.config.helpText);
      },
      fields: function fields() {
        if (typeof this.getFields === 'function') return this.getFields();
        return [];
      }
    },
    watch: {
      'config.fieldName': function configFieldName() {
        this.item.checkFieldName();
      },
      'fields': function fields(val) {
        this.item.fields = val;
      }
    }
  };

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-form-layout-field-text', {
      mixins: [fieldMixin],
      computed: {
        defaultPlaceholder: function defaultPlaceholder() {
          return this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_TEXT_PLACEHOLDER_' + this.config.type);
        },
        panelItems: function panelItems() {
          var panel = {
            data: this.config,
            items: []
          };
          panel.items.push({
            name: 'devbx-webform-caption',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SETTINGS_TITLE') + ' - ' + this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_TEXT_NAME')
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            allowFormFields: true,
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_LABEL_TITLE'),
              fieldName: 'label',
              fieldVisibleName: 'labelHidden'
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_FIELD_NAME_TITLE'),
              fieldName: 'fieldName',
              live: false,
              readonly: this.config.systemId > 0
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_PLACEHOLDER_TITLE'),
              fieldName: 'placeholder'
            }
          });
          panel.items.push({
            name: 'devbx-webform-selectbox-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_TEXT_TYPE_TITLE'),
              type: 'radio',
              values: [{
                value: 'SINGLE_LINE',
                text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_TEXT_TYPE_SINGLE_LINE')
              }, {
                value: 'MULTI_LINE',
                text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_TEXT_TYPE_MULTI_LINE')
              }],
              fieldName: 'type'
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_DEFAULT_VALUE_TITLE'),
              fieldName: 'defaultValue'
            }
          });
          panel.items.push({
            name: 'devbx-webform-range-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_LENGTH_MIN_MAX_TITLE'),
              fieldMinName: 'lengthMin',
              fieldMaxName: 'lengthMax'
            }
          });
          panel.items.push({
            name: 'devbx-webform-html-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_HELP_TEXT_TITLE'),
              fieldName: 'helpText'
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SHOW_THIS_FIELD_TITLE'),
              fieldName: 'showRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_THIS_FIELD_TITLE'),
              defaultValue: 'always',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_REQUIRE_THIS_FIELD_TITLE'),
              fieldName: 'requireRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_REQUIRE_THIS_FIELD_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_READ_ONLY_THIS_FIELD_TITLE'),
              fieldName: 'readOnlyRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_READ_ONLY_THIS_FIELD_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SHOW_CUSTOM_ERROR_TITLE'),
              fieldName: 'showCustomError',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_CUSTOM_ERROR_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields,
              options: ['never', 'when']
            }
          });
          if (this.config.showCustomError.value === 'when' && this.config.showCustomError.code) {
            panel.items.push({
              name: 'devbx-webform-text-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CUSTOM_ERROR_TITLE'),
                fieldName: 'customError',
                live: false
              }
            });
          }
          return panel;
        }
      },
      methods: {
        getFields: function getFields() {
          return [{
            name: this.config.fieldName,
            label: this.config.label,
            type: 'string'
          }];
        }
      },
      template: "\n    <div class=\"devbx-webform-layout-field\">\n        <div class=\"devbx-webform-layout-label\" :class=\"{'devbx-webform-layout-label-hidden': config.labelHidden}\">\n            <p v-html=\"labelFormatted\"></p>\n        </div>\n        <div :class=\"{\n                'devbx-webform-layout-control':true, \n                'devbx-webform-layout-input-single-line': config.type == 'SINGLE_LINE',\n                'devbx-webform-layout-input-multi-line': config.type == 'MULTI_LINE',\n                }\">\n            <span>\n                <i class=\"fa fa-font\"></i>\n                    <span v-if=\"config.defaultValue\" class=\"devbx-webform-layout-default-value\">\n                        {{config.defaultValue}}\n                    </span>\n                    <span v-else-if=\"config.placeholder\" class=\"devbx-webform-layout-placeholder\">\n                        {{config.placeholder}}                \n                    </span>\n                    <span v-else class=\"devbx-webform-layout-placeholder\">\n                        {{defaultPlaceholder}}                \n                    </span>\n            </span>\n        </div>\n        <div class=\"devbx-webform-layout-helptext\" v-if=\"config.helpText.length>0\">\n            <p v-html=\"helpTextFormatted\"></p>\n        </div>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-form-layout-field-choice', {
      mixins: [fieldMixin],
      computed: {
        defaultPlaceholder: function defaultPlaceholder() {
          return this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_TEXT_PLACEHOLDER_' + this.config.type);
        },
        panelItems: function panelItems() {
          var panel = {
            data: this.config,
            items: []
          };
          panel.items.push({
            name: 'devbx-webform-caption',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SETTINGS_TITLE') + ' - ' + this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CHOICE_NAME')
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            allowFormFields: true,
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_LABEL_TITLE'),
              fieldName: 'label',
              fieldVisibleName: 'labelHidden'
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_FIELD_NAME_TITLE'),
              fieldName: 'fieldName',
              live: false,
              readonly: this.config.systemId > 0
            }
          });
          panel.items.push({
            name: 'devbx-webform-selectbox-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CHOICE_TYPE_TITLE'),
              type: 'radio',
              values: [{
                value: 'DROP_DOWN',
                text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CHOICE_TYPE_DROP_DOWN'),
                disabled: this.config.systemId > 0 && this.config.type === 'CHECKBOX'
              }, {
                value: 'RADIO',
                text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CHOICE_TYPE_RADIO'),
                disabled: this.config.systemId > 0 && this.config.type === 'CHECKBOX'
              }, {
                value: 'CHECKBOX',
                text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CHOICE_TYPE_CHECKBOX'),
                disabled: this.config.systemId > 0 && this.config.type !== 'CHECKBOX'
              }],
              fieldName: 'type'
            }
          });
          if (this.config.type === 'DROP_DOWN') {
            panel.items.push({
              name: 'devbx-webform-text-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_PLACEHOLDER_TITLE'),
                fieldName: 'placeholder'
              }
            });
          } else {
            panel.items.push({
              name: 'devbx-webform-selectbox-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CHOICE_TYPE_TITLE'),
                type: 'select',
                values: [{
                  value: 'ONE_COLUMN',
                  text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CHOICE_VISUAL_ONE_COLUMN')
                }, {
                  value: 'TWO_COLUMN',
                  text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CHOICE_VISUAL_TWO_COLUMN')
                }, {
                  value: 'SIDE_BY_SIDE',
                  text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CHOICE_VISUAL_SIDE_BY_SIDE')
                }],
                fieldName: 'visual'
              }
            });
          }
          panel.items.push({
            name: 'devbx-webform-html-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_HELP_TEXT_TITLE'),
              fieldName: 'helpText'
            }
          });
          panel.items.push({
            name: 'devbx-webform-selectbox-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CHOICE_OPTIONS_TITLE'),
              type: 'checkbox',
              values: [{
                value: 'ASSIGN_VALUES',
                text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CHOICE_OPTIONS_ASSIGN_VALUES')
              }],
              fieldName: 'choiceOptions'
            }
          });
          panel.items.push({
            name: 'devbx-webform-select-options-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SELECT_OPTIONS_TITLE'),
              assignValues: this.config.choiceOptions.indexOf('ASSIGN_VALUES') > -1,
              fieldName: 'options',
              multiple: this.config.type == 'CHECKBOX'
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SHOW_THIS_FIELD_TITLE'),
              fieldName: 'showRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_THIS_FIELD_TITLE'),
              defaultValue: 'always',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_REQUIRE_THIS_FIELD_TITLE'),
              fieldName: 'requireRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_REQUIRE_THIS_FIELD_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_READ_ONLY_THIS_FIELD_TITLE'),
              fieldName: 'readOnlyRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_READ_ONLY_THIS_FIELD_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SHOW_CUSTOM_ERROR_TITLE'),
              fieldName: 'showCustomError',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_CUSTOM_ERROR_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields,
              options: ['never', 'when']
            }
          });
          if (this.config.showCustomError.value === 'when' && this.config.showCustomError.code) {
            panel.items.push({
              name: 'devbx-webform-text-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CUSTOM_ERROR_TITLE'),
                fieldName: 'customError',
                live: false
              }
            });
          }
          return panel;
        }
      },
      watch: {
        'config.type': function configType(val) {
          if (this.config.type != 'CHECKBOX') {
            var sel = false;
            this.config.options.forEach(function (opt) {
              if (opt.selected) {
                if (sel) opt.selected = false;
                sel = true;
              }
            });
          }
        }
      },
      methods: {
        getFields: function getFields() {
          var result = [],
            data;
          data = {
            name: this.config.fieldName + '.Text',
            label: this.config.label,
            type: this.config.type === 'CHECKBOX' ? 'array' : 'string',
            values: []
          };
          this.config.options.forEach(function (item) {
            data.values.push(item.text);
          });
          result.push(data);
          if (this.config.choiceOptions.indexOf('ASSIGN_VALUES') >= 0) {
            data = {
              name: this.config.fieldName + '.Value',
              label: this.config.label,
              type: this.config.type === 'CHECKBOX' ? 'array' : 'string',
              values: []
            };
            this.config.options.forEach(function (item) {
              data.values.push(item.value);
            });
            result.push(data);
          }
          return result;
        }
      },
      template: "\n    <div class=\"devbx-webform-layout-field\">\n        <div class=\"devbx-webform-layout-label\" :class=\"{'devbx-webform-layout-label-hidden': config.labelHidden}\">\n            <p v-html=\"labelFormatted\"></p>\n        </div>\n            <div class=\"devbx-webform-layout-control devbx-webform-layout-choice-drop-down\" v-if=\"config.type == 'DROP_DOWN'\">\n                <span>\n                    <i class=\"fa fa-list-ul\"></i>\n                    <span v-if=\"config.defaultValue\" class=\"devbx-webform-layout-default-value\">\n                        {{config.defaultValue}}\n                    </span>\n                    <span v-else-if=\"config.placeholder\" class=\"devbx-webform-layout-placeholder\">\n                        {{config.placeholder}}                \n                    </span>\n                    <span v-else class=\"devbx-webform-layout-placeholder\">\n                        {{defaultPlaceholder}}                \n                    </span>\n                </span>\n                <div class=\"devbx-webform-layout-dropdown-button\"><i class=\"fa fa-chevron-down\"></i></div>\n            </div>\n            <div class=\"devbx-webform-layout-control devbx-webform-layout-choice-radio\" v-else-if=\"config.type == 'RADIO'\">\n                <div :class=\"{\n                    'devbx-webform-layout-choice-column-1': config.visual == 'ONE_COLUMN',\n                    'devbx-webform-layout-choice-column-2': config.visual == 'TWO_COLUMN',\n                    'devbx-webform-layout-choice-column-0': config.visual == 'SIDE_BY_SIDE',\n                    }\">\n                     <div class=\"devbx-webform-layout-choice-option\" v-for=\"option in config.options\">\n                        <label>\n                            <input tabindex=\"-1\" @click.prevent type=\"radio\" :checked=\"option.selected\">\n                            <span>{{option.text}}</span>                    \n                        </label>                 \n                     </div>\n                </div>\n            </div>\n            <div class=\"devbx-webform-layout-control devbx-webform-layout-choice-radio\" v-else-if=\"config.type == 'CHECKBOX'\">\n                <div :class=\"{\n                    'devbx-webform-layout-choice-column-1': config.visual == 'ONE_COLUMN',\n                    'devbx-webform-layout-choice-column-2': config.visual == 'TWO_COLUMN',\n                    'devbx-webform-layout-choice-column-0': config.visual == 'SIDE_BY_SIDE',\n                    }\">\n                     <div class=\"devbx-webform-layout-choice-option\" v-for=\"option in config.options\">\n                        <label>\n                            <input tabindex=\"-1\" @click.prevent type=\"checkbox\" :checked=\"option.selected\">\n                            <span>{{option.text}}</span>                    \n                        </label>                 \n                     </div>\n                </div>\n            </div>\n        <div class=\"devbx-webform-layout-helptext\" v-if=\"config.helpText.length>0\">\n            <p v-html=\"helpTextFormatted\"></p>\n        </div>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-form-layout-field-date', {
      mixins: [fieldMixin],
      computed: {
        defaultPlaceholder: function defaultPlaceholder() {
          return this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_DATE_PLACEHOLDER_' + this.config.type);
        },
        panelItems: function panelItems() {
          var panel = {
            data: this.config,
            items: []
          };
          panel.items.push({
            name: 'devbx-webform-caption',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SETTINGS_TITLE') + ' - ' + this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_TEXT_NAME')
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            allowFormFields: true,
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_LABEL_TITLE'),
              fieldName: 'label',
              fieldVisibleName: 'labelHidden'
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_FIELD_NAME_TITLE'),
              fieldName: 'fieldName',
              live: false,
              readonly: this.config.systemId > 0
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_PLACEHOLDER_TITLE'),
              fieldName: 'placeholder'
            }
          });
          panel.items.push({
            name: 'devbx-webform-selectbox-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_DATE_TYPE_TITLE'),
              type: 'radio',
              values: [{
                value: 'DATE',
                text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_TEXT_TYPE_DATE'),
                disabled: this.config.systemId > 0
              }, {
                value: 'DATETIME',
                text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_TEXT_TYPE_DATETIME'),
                disabled: this.config.systemId > 0
              }, {
                value: 'TIME',
                text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_TEXT_TYPE_TIME'),
                disabled: this.config.systemId > 0
              }],
              fieldName: 'type'
            }
          });
          if (this.config.type === 'DATETIME' || this.config.type === 'TIME') {
            var timeRangeValues = [];
            for (var time = 0; time < 24 * 60; time += 15) {
              var value = Math.floor(time / 60).toString().padStart(2, '0') + ':' + (time % 60).toString().padStart(2, '0');
              timeRangeValues.push({
                value: value,
                text: value
              });
            }
            panel.items.push({
              name: 'devbx-webform-selectbox-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_DATE_TIME_START_TITLE'),
                type: 'select',
                values: timeRangeValues,
                fieldName: 'timeStart'
              }
            });
            panel.items.push({
              name: 'devbx-webform-selectbox-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_DATE_TIME_END_TITLE'),
                type: 'select',
                values: timeRangeValues,
                fieldName: 'timeEnd'
              }
            });
            panel.items.push({
              name: 'devbx-webform-selectbox-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_DATE_STEP_TITLE'),
                type: 'select',
                values: [{
                  value: '00:01',
                  text: '00:01'
                }, {
                  value: '00:05',
                  text: '00:05'
                }, {
                  value: '00:10',
                  text: '00:10'
                }, {
                  value: '00:15',
                  text: '00:15'
                }, {
                  value: '00:30',
                  text: '00:30'
                }, {
                  value: '01:00',
                  text: '01:00'
                }],
                fieldName: 'step'
              }
            });
          }
          panel.items.push({
            name: 'devbx-webform-html-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_HELP_TEXT_TITLE'),
              fieldName: 'helpText'
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SHOW_THIS_FIELD_TITLE'),
              fieldName: 'showRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_THIS_FIELD_TITLE'),
              defaultValue: 'always',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_REQUIRE_THIS_FIELD_TITLE'),
              fieldName: 'requireRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_REQUIRE_THIS_FIELD_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_READ_ONLY_THIS_FIELD_TITLE'),
              fieldName: 'readOnlyRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_READ_ONLY_THIS_FIELD_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SHOW_CUSTOM_ERROR_TITLE'),
              fieldName: 'showCustomError',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_CUSTOM_ERROR_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields,
              options: ['never', 'when']
            }
          });
          if (this.config.showCustomError.value === 'when' && this.config.showCustomError.code) {
            panel.items.push({
              name: 'devbx-webform-text-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CUSTOM_ERROR_TITLE'),
                fieldName: 'customError',
                live: false
              }
            });
          }
          return panel;
        }
      },
      methods: {
        getFields: function getFields() {
          switch (this.config.type) {
            case 'DATE':
              return [{
                name: this.config.fieldName,
                label: this.config.label,
                type: 'date'
              }];
            case 'DATETIME':
              return [{
                name: this.config.fieldName,
                label: this.config.label,
                type: 'datetime'
              }];
            case 'TIME':
              return [{
                name: this.config.fieldName,
                label: this.config.label,
                type: 'time'
              }];
          }
        }
      },
      template: "\n    <div class=\"devbx-webform-layout-field\">\n        <div class=\"devbx-webform-layout-label\" :class=\"{'devbx-webform-layout-label-hidden': config.labelHidden}\">\n            <p v-html=\"labelFormatted\"></p>\n        </div>\n        \n        <div class=\"devbx-webform-layout-control devbx-webform-layout-date\" v-if=\"config.type == 'DATE'\">\n            <span>\n                    <span v-if=\"config.placeholder\" class=\"devbx-webform-layout-placeholder\">\n                        {{config.placeholder}}                \n                    </span>\n                    <span v-else class=\"devbx-webform-layout-placeholder\">\n                        {{defaultPlaceholder}}                \n                    </span>\n            </span>\n            <div><i class=\"fa fa-calendar\"></i></div>\n        </div>    \n        <div class=\"devbx-webform-layout-control devbx-webform-layout-datetime\" v-if=\"config.type == 'DATETIME'\">\n            <span>\n                    <span v-if=\"config.placeholder\" class=\"devbx-webform-layout-placeholder\">\n                        {{config.placeholder}}                \n                    </span>\n                    <span v-else class=\"devbx-webform-layout-placeholder\">\n                        {{defaultPlaceholder}}                \n                    </span>\n            </span>\n            <div><i class=\"fa fa-calendar\"></i></div>\n        </div>    \n        <div class=\"devbx-webform-layout-control devbx-webform-layout-time\" v-if=\"config.type == 'TIME'\">\n            <span>\n                    <span v-if=\"config.placeholder\" class=\"devbx-webform-layout-placeholder\">\n                        {{config.placeholder}}                \n                    </span>\n                    <span v-else class=\"devbx-webform-layout-placeholder\">\n                        {{defaultPlaceholder}}                \n                    </span>\n            </span>\n            <div><i class=\"fa fa-clock-o\"></i></div>\n        </div>    \n\n        <div class=\"devbx-webform-layout-helptext\" v-if=\"config.helpText.length>0\">\n            <p v-html=\"helpTextFormatted\"></p>\n        </div>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-form-layout-field-boolean', {
      mixins: [fieldMixin],
      computed: {
        labelValueYes: function labelValueYes() {
          if (this.config.customLabels && this.config.customLabelYes) return this.config.customLabelYes;
          return this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_BOOLEAN_VALUE_TRUE');
        },
        labelValueNo: function labelValueNo() {
          if (this.config.customLabels && this.config.customLabelNo) return this.config.customLabelNo;
          return this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_BOOLEAN_VALUE_FALSE');
        },
        panelItems: function panelItems() {
          var panel = {
            data: this.config,
            items: []
          };
          panel.items.push({
            name: 'devbx-webform-caption',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SETTINGS_TITLE') + ' - ' + this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_TEXT_NAME')
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            allowFormFields: true,
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_LABEL_TITLE'),
              fieldName: 'label',
              fieldVisibleName: this.config.type == 'RADIO' ? 'labelHidden' : ''
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_FIELD_NAME_TITLE'),
              fieldName: 'fieldName',
              live: false,
              readonly: this.config.systemId > 0
            }
          });
          panel.items.push({
            name: 'devbx-webform-selectbox-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_BOOLEAN_TYPE_TITLE'),
              type: 'radio',
              values: [{
                value: 'RADIO',
                text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_TEXT_BOOLEAN_RADIO')
              }, {
                value: 'CHECKBOX',
                text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_TEXT_BOOLEAN_CHECKBOX')
              }],
              fieldName: 'type'
            }
          });
          if (this.config.type == 'RADIO') {
            panel.items.push({
              name: 'devbx-webform-bool-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_TEXT_BOOLEAN_CUSTOM_LABELS'),
                fieldName: 'customLabels'
              }
            });
            if (this.config.customLabels) {
              panel.items.push({
                name: 'devbx-webform-text-field',
                props: {
                  title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_TEXT_BOOLEAN_CUSTOM_LABEL_YES_TITLE'),
                  fieldName: 'customLabelYes',
                  live: true
                }
              });
              panel.items.push({
                name: 'devbx-webform-text-field',
                props: {
                  title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_TEXT_BOOLEAN_CUSTOM_LABEL_NO_TITLE'),
                  fieldName: 'customLabelNo',
                  live: true
                }
              });
            }
          }
          panel.items.push({
            name: 'devbx-webform-selectbox-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_DEFAULT_VALUE_TITLE'),
              type: 'radio',
              values: [{
                value: true,
                text: this.labelValueYes
              }, {
                value: false,
                text: this.labelValueNo
              }],
              fieldName: 'defaultValue'
            }
          });
          panel.items.push({
            name: 'devbx-webform-html-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_HELP_TEXT_TITLE'),
              fieldName: 'helpText'
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SHOW_THIS_FIELD_TITLE'),
              fieldName: 'showRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_THIS_FIELD_TITLE'),
              defaultValue: 'always',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_REQUIRE_THIS_FIELD_TITLE'),
              fieldName: 'requireRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_REQUIRE_THIS_FIELD_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_READ_ONLY_THIS_FIELD_TITLE'),
              fieldName: 'readOnlyRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_READ_ONLY_THIS_FIELD_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SHOW_CUSTOM_ERROR_TITLE'),
              fieldName: 'showCustomError',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_CUSTOM_ERROR_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields,
              options: ['never', 'when']
            }
          });
          if (this.config.showCustomError.value === 'when' && this.config.showCustomError.code) {
            panel.items.push({
              name: 'devbx-webform-text-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CUSTOM_ERROR_TITLE'),
                fieldName: 'customError',
                live: false
              }
            });
          }
          return panel;
        }
      },
      methods: {
        getFields: function getFields() {
          return [{
            name: this.config.fieldName,
            label: this.config.label,
            type: 'boolean'
          }];
        }
      },
      template: "\n    <div class=\"devbx-webform-layout-field\">\n        <div v-if=\"config.type == 'RADIO'\" class=\"devbx-webform-layout-label\" :class=\"{'devbx-webform-layout-label-hidden': config.labelHidden}\">\n            <p v-html=\"labelFormatted\"></p>\n        </div>\n        \n        <div v-if=\"config.type == 'RADIO'\">\n            <label>\n                <input tabindex=\"-1\" @click.prevent type=\"radio\" :checked=\"config.defaultValue\">\n                <span>{{labelValueYes}}</span>                    \n            </label>                 \n\n            <label>\n                <input tabindex=\"-1\" @click.prevent type=\"radio\" :checked=\"!config.defaultValue\">\n                <span>{{labelValueNo}}</span>                    \n            </label>                 \n        </div>\n        <div v-else class=\"devbx-webform-layout-boolean-checkbox\">\n            <label class=\"devbx-webform-layout-boolean-checkbox-label\">\n                <input tabindex=\"-1\" @click.prevent type=\"checkbox\" :checked=\"config.defaultValue\">\n                <span v-html=\"labelFormatted\"></span>                    \n            </label>                 \n        </div>\n        \n        <div class=\"devbx-webform-layout-helptext\" v-if=\"config.helpText.length>0\">\n            <p v-html=\"helpTextFormatted\"></p>\n        </div>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-form-layout-field-number', {
      mixins: [fieldMixin],
      computed: {
        defaultPlaceholder: function defaultPlaceholder() {
          return this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_NUMBER_PLACEHOLDER_' + this.config.type);
        },
        panelItems: function panelItems() {
          var panel = {
            data: this.config,
            items: []
          };
          panel.items.push({
            name: 'devbx-webform-caption',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SETTINGS_TITLE') + ' - ' + this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_TEXT_NAME')
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            allowFormFields: true,
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_LABEL_TITLE'),
              fieldName: 'label',
              fieldVisibleName: 'labelHidden'
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_FIELD_NAME_TITLE'),
              fieldName: 'fieldName',
              live: false,
              readonly: this.config.systemId > 0
            }
          });
          panel.items.push({
            name: 'devbx-webform-selectbox-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_NUMBER_TYPE_TITLE'),
              type: 'radio',
              values: [{
                value: 'INTEGER',
                text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_NUMBER_TYPE_INTEGER')
              }, {
                value: 'DECIMAL',
                text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_NUMBER_TYPE_DECIMAL')
              }],
              fieldName: 'type'
            }
          });
          if (this.config.type === 'DECIMAL') {
            panel.items.push({
              name: 'devbx-webform-selectbox-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_NUMBER_DECIMAL_PLACES_TITLE'),
                type: 'select',
                values: [{
                  value: 0,
                  text: 0
                }, {
                  value: 1,
                  text: 1
                }, {
                  value: 2,
                  text: 2
                }, {
                  value: 3,
                  text: 3
                }, {
                  value: 4,
                  text: 4
                }, {
                  value: 5,
                  text: 5
                }, {
                  value: 6,
                  text: 6
                }, {
                  value: 7,
                  text: 7
                }, {
                  value: 8,
                  text: 8
                }, {
                  value: 9,
                  text: 9
                }, {
                  value: 10,
                  text: 10
                }],
                fieldName: 'decimalPlaces'
              }
            });
          }
          panel.items.push({
            name: 'devbx-webform-selectbox-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_NUMBER_STYLE_TITLE'),
              type: 'radio',
              values: [{
                value: 'TEXT',
                text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_NUMBER_STYLE_TEXT')
              }, {
                value: 'SPINNER',
                text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_NUMBER_STYLE_SPINNER')
              }],
              fieldName: 'visual'
            }
          });
          if (this.config.visual === 'SPINNER') {
            panel.items.push({
              name: 'devbx-webform-number-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_NUMBER_INCREMENT_VALUE_TITLE'),
                fieldName: 'incrementValue',
                decimalPlaces: this.config.type === 'INTEGER' ? 0 : parseInt(this.config.decimalPlaces, 10)
              }
            });
          }
          panel.items.push({
            name: 'devbx-webform-number-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_DEFAULT_VALUE_TITLE'),
              fieldName: 'defaultValue',
              decimalPlaces: this.config.type === 'INTEGER' ? 0 : parseInt(this.config.decimalPlaces, 10)
            }
          });
          panel.items.push({
            name: 'devbx-webform-range-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_NUMBER_RANGE_MIN_MAX_TITLE'),
              fieldMinName: 'minValue',
              fieldMaxName: 'maxValue',
              decimalPlaces: this.config.type === 'INTEGER' ? 0 : parseInt(this.config.decimalPlaces, 10)
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_PLACEHOLDER_TITLE'),
              fieldName: 'placeholder'
            }
          });
          panel.items.push({
            name: 'devbx-webform-html-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_HELP_TEXT_TITLE'),
              fieldName: 'helpText'
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SHOW_THIS_FIELD_TITLE'),
              fieldName: 'showRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_THIS_FIELD_TITLE'),
              defaultValue: 'always',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_REQUIRE_THIS_FIELD_TITLE'),
              fieldName: 'requireRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_REQUIRE_THIS_FIELD_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_READ_ONLY_THIS_FIELD_TITLE'),
              fieldName: 'readOnlyRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_READ_ONLY_THIS_FIELD_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SHOW_CUSTOM_ERROR_TITLE'),
              fieldName: 'showCustomError',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_CUSTOM_ERROR_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields,
              options: ['never', 'when']
            }
          });
          if (this.config.showCustomError.value === 'when' && this.config.showCustomError.code) {
            panel.items.push({
              name: 'devbx-webform-text-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CUSTOM_ERROR_TITLE'),
                fieldName: 'customError',
                live: false
              }
            });
          }
          return panel;
        }
      },
      methods: {
        getFields: function getFields() {
          return [{
            name: this.config.fieldName,
            label: this.config.label,
            type: 'number'
          }];
        }
      },
      template: "\n    <div class=\"devbx-webform-layout-field\">\n        <div class=\"devbx-webform-layout-label\" :class=\"{'devbx-webform-layout-label-hidden': config.labelHidden}\">\n            <p v-html=\"labelFormatted\"></p>\n        </div>\n        <div class=\"devbx-webform-layout-control\" v-if=\"config.visual == 'TEXT'\">\n            <span>\n                <i class=\"fa fa-number\"></i>\n                    <span class=\"devbx-webform-layout-placeholder\">\n                        {{defaultPlaceholder}}                \n                    </span>\n            </span>\n        </div>\n        <div class=\"devbx-webform-layout-number-spinner\" v-else>\n            <button class=\"spinner-decrease\"></button>\n                <div class=\"devbx-webform-layout-control devbx-webform-layout-control-spinner\">\n                    <span>\n                        <i class=\"fa fa-number\"></i>\n                            <span class=\"devbx-webform-layout-placeholder\">\n                            {{defaultPlaceholder}}                \n                            </span>\n                    </span>\n                </div>\n            <button class=\"spinner-increase\"></button>\n        </div>\n        \n        <div class=\"devbx-webform-layout-helptext\" v-if=\"config.helpText.length>0\">\n            <p v-html=\"helpTextFormatted\"></p>\n        </div>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-form-layout-field-email', {
      mixins: [fieldMixin],
      computed: {
        defaultPlaceholder: function defaultPlaceholder() {
          return this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_EMAIL_PLACEHOLDER');
        },
        panelItems: function panelItems() {
          var panel = {
            data: this.config,
            items: []
          };
          panel.items.push({
            name: 'devbx-webform-caption',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SETTINGS_TITLE') + ' - ' + this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_EMAIL_NAME')
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            allowFormFields: true,
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_LABEL_TITLE'),
              fieldName: 'label',
              fieldVisibleName: 'labelHidden'
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_FIELD_NAME_TITLE'),
              fieldName: 'fieldName',
              live: false,
              readonly: this.config.systemId > 0
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_PLACEHOLDER_TITLE'),
              fieldName: 'placeholder'
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_DEFAULT_VALUE_TITLE'),
              fieldName: 'defaultValue'
            }
          });
          panel.items.push({
            name: 'devbx-webform-html-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_HELP_TEXT_TITLE'),
              fieldName: 'helpText'
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SHOW_THIS_FIELD_TITLE'),
              fieldName: 'showRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_THIS_FIELD_TITLE'),
              defaultValue: 'always',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_REQUIRE_THIS_FIELD_TITLE'),
              fieldName: 'requireRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_REQUIRE_THIS_FIELD_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_READ_ONLY_THIS_FIELD_TITLE'),
              fieldName: 'readOnlyRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_READ_ONLY_THIS_FIELD_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SHOW_CUSTOM_ERROR_TITLE'),
              fieldName: 'showCustomError',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_CUSTOM_ERROR_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields,
              options: ['never', 'when']
            }
          });
          if (this.config.showCustomError.value === 'when' && this.config.showCustomError.code) {
            panel.items.push({
              name: 'devbx-webform-text-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CUSTOM_ERROR_TITLE'),
                fieldName: 'customError',
                live: false
              }
            });
          }
          return panel;
        }
      },
      methods: {
        getFields: function getFields() {
          return [{
            name: this.config.fieldName,
            label: this.config.label,
            type: 'string'
          }];
        }
      },
      template: "\n    <div class=\"devbx-webform-layout-field\">\n        <div class=\"devbx-webform-layout-label\" :class=\"{'devbx-webform-layout-label-hidden': config.labelHidden}\">\n            <p v-html=\"labelFormatted\"></p>\n        </div>\n        <div :class=\"{\n                'devbx-webform-layout-control':true, \n                }\">\n            <span>\n                <i class=\"fa fa-envelope-o\"></i>\n                    <span v-if=\"config.defaultValue\" class=\"devbx-webform-layout-default-value\">\n                        {{config.defaultValue}}\n                    </span>\n                    <span v-else-if=\"config.placeholder\" class=\"devbx-webform-layout-placeholder\">\n                        {{config.placeholder}}                \n                    </span>\n                    <span v-else class=\"devbx-webform-layout-placeholder\">\n                        {{defaultPlaceholder}}                \n                    </span>\n            </span>\n        </div>\n        <div class=\"devbx-webform-layout-helptext\" v-if=\"config.helpText.length>0\">\n            <p v-html=\"helpTextFormatted\"></p>\n        </div>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-form-layout-field-file-upload', {
      mixins: [fieldMixin],
      computed: {
        panelItems: function panelItems() {
          var panel = {
            data: this.config,
            items: []
          };
          panel.items.push({
            name: 'devbx-webform-caption',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SETTINGS_TITLE') + ' - ' + this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_TEXT_NAME')
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            allowFormFields: true,
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_LABEL_TITLE'),
              fieldName: 'label',
              fieldVisibleName: 'labelHidden'
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_FIELD_NAME_TITLE'),
              fieldName: 'fieldName',
              live: false,
              readonly: this.config.systemId > 0
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FILE_UPLOAD_FIELD_ALLOWED_FILE_TYPES_TITLE'),
              fieldName: 'allowedFileTypes',
              live: false
            }
          });
          panel.items.push({
            name: 'devbx-webform-number-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FILE_UPLOAD_FIELD_MAXIMUM_FILE_SIZE_TITLE'),
              fieldName: 'maximumFileSize',
              allowEmpty: true,
              minValue: 0
            }
          });
          panel.items.push({
            name: 'devbx-webform-bool-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FILE_UPLOAD_MULTIPLE_TITLE'),
              fieldName: 'multiple',
              readonly: this.config.systemId > 0
            }
          });
          if (this.config.multiple) {
            panel.items.push({
              name: 'devbx-webform-number-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FILE_UPLOAD_FIELD_MAXIMUM_NUMBER_OF_FILES_TITLE'),
                fieldName: 'maximumNumberOfFiles',
                allowEmpty: true,
                minValue: 0
              }
            });
          }
          panel.items.push({
            name: 'devbx-webform-html-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_HELP_TEXT_TITLE'),
              fieldName: 'helpText'
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SHOW_THIS_FIELD_TITLE'),
              fieldName: 'showRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_THIS_FIELD_TITLE'),
              defaultValue: 'always',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_REQUIRE_THIS_FIELD_TITLE'),
              fieldName: 'requireRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_REQUIRE_THIS_FIELD_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_READ_ONLY_THIS_FIELD_TITLE'),
              fieldName: 'readOnlyRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_READ_ONLY_THIS_FIELD_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SHOW_CUSTOM_ERROR_TITLE'),
              fieldName: 'showCustomError',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_CUSTOM_ERROR_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields,
              options: ['never', 'when']
            }
          });
          if (this.config.showCustomError.value === 'when' && this.config.showCustomError.code) {
            panel.items.push({
              name: 'devbx-webform-text-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CUSTOM_ERROR_TITLE'),
                fieldName: 'customError',
                live: false
              }
            });
          }
          return panel;
        }
      },
      methods: {
        getFields: function getFields() {
          return [{
            name: this.config.fieldName,
            label: this.config.label,
            type: 'files'
          }];
        }
      },
      template: "\n    <div class=\"devbx-webform-layout-field\">\n        <div class=\"devbx-webform-layout-label\" :class=\"{'devbx-webform-layout-label-hidden': config.labelHidden}\">\n            <p v-html=\"labelFormatted\"></p>\n        </div>\n        \n        <div class=\"devbx-webform-layout-file-upload\">\n            <div class=\"decbx-webform-layouy-file-upload-dropzone\">\n                <button class=\"devbx-webform-form-button\" @click.stop.prevent>{{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FILE_UPLOAD_BUTTON')}}</button>\n                {{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FILE_UPLOAD_DROPZONE')}}            \n            </div>\n        </div>\n        \n        <div class=\"devbx-webform-layout-helptext\" v-if=\"config.helpText.length>0\">\n            <p v-html=\"helpTextFormatted\"></p>\n        </div>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-iblock-filter-cond', {
      props: {
        item: {
          type: Object,
          requited: true
        },
        iblockFields: {
          type: Array,
          required: true
        },
        condFields: {
          type: Array,
          required: true
        },
        condConfig: {
          type: Object,
          required: true
        }
      },
      computed: {
        selectedField: function selectedField() {
          var _this = this;
          var result = false;
          this.iblockFields.forEach(function (field) {
            if (field.name === _this.item.field) result = field;
            return result === false;
          });
          return result;
        },
        availableIblockFields: function availableIblockFields() {
          var result = [];
          this.iblockFields.forEach(function (field) {
            if (field.name === 'IBLOCK_ID') return;
            result.push(field);
          });
          return result;
        },
        operationType: function operationType() {
          if (!this.selectedField) return [];
          var fieldCond = this.condConfig.fieldsType[this.selectedField.condType];
          if (!fieldCond) return [];
          return fieldCond.conditions;
        },
        selectedCondition: function selectedCondition() {
          var _this2 = this;
          var result = false;
          this.operationType.every(function (item) {
            if (item.value === _this2.item.type) result = item;
            return !result;
          });
          return result;
        },
        selectedFieldDataType: function selectedFieldDataType() {
          if (!this.selectedField) return null;
          return this.selectedField.type;
        },
        condHasComponent: function condHasComponent() {
          return this.selectedCondition && this.selectedCondition.comp;
        },
        fieldError: function fieldError() {
          if (this.item.field.length && !this.selectedField) return this.$root.formatString(this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_ERR_UNKNOWN_FIELD'), '#NAME#', this.item.field);
          return false;
        }
      },
      watch: {
        'selectedFieldDataType': {
          immediate: true,
          handler: function handler(val, oldVal) {
            var _this3 = this;
            var valid = false;
            this.operationType.every(function (item) {
              valid = item.value === _this3.item.type;
              return !valid;
            });
            if (!valid && this.operationType.length) this.item.type = this.operationType[0].value;
          }
        }
      },
      template: "\n    <div class=\"devbx-webform-cond-container\" v-if=\"!fieldError\">\n        <div class=\"devbx-webform-cond-dropdown devbx-webform-cond-field\">\n            <select v-model=\"item.field\">\n                <option v-for=\"field in availableIblockFields\" :key=\"field.name\" :value=\"field.name\">{{field.name}} ({{field.label}})</option>        \n            </select>\n        </div>\n\n        <div class=\"devbx-webform-cond-dropdown devbx-webform-cond-type\">\n            <select v-model=\"item.type\">\n                <option v-for=\"type in operationType\" :key=\"type.value\" :value=\"type.value\">{{type.label}}</option>\n            </select>\n        </div>\n\n        <component v-if=\"condHasComponent\" \n            :is=\"selectedCondition.comp\" \n            :item=\"item\" \n            :field=\"selectedField\" \n            :cond-config=\"condConfig\" \n            :cond-fields=\"condFields\"\n            v-bind=\"selectedCondition.props\"\n            >\n        </component>\n        <slot></slot>            \n    </div>\n    <div class=\"devbx-webform-cond-container\" v-else>\n    \n        <span class=\"devbx-webform-error-label\">{{fieldError}}</span>\n        <slot></slot>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-popup-iblock-section-filter', {
      props: {
        'condConfig': {
          type: Object,
          required: true
        },
        'condFields': {
          type: Array,
          required: true
        }
      },
      data: function data() {
        return {
          iblockId: 0,
          filter: []
        };
      },
      mounted: function mounted() {
        this.iblockId = this.$parent.iblockId;
        this.filter = JSON.parse(JSON.stringify(this.$parent.filter));
        if (!this.$store.state.iblockType.length) this.$store.dispatch('getIblockList');
        var popup = BX.PopupWindowManager.getPopupById(this.$parent.popupId);
        if (popup) {
          popup.contentContainer.appendChild(this.$el);
          popup.show();
        }
      },
      watch: {
        iblockId: function iblockId(val) {
          val = parseInt(val);
          if (isNaN(val)) val = 0;
          if (this.iblockId !== val) this.iblockId = val;
        },
        filter: function filter(val) {
          if (!Array.isArray(val)) {
            this.filter = [];
          }
        }
      },
      methods: {
        getIblockItemsByType: function getIblockItemsByType(type) {
          var result = [];
          this.iblockList.forEach(function (item) {
            if (item.iblockTypeId === type) {
              result.push(item);
            }
          });
          return result;
        },
        removeCond: function removeCond(index) {
          this.filter.splice(index, 1);
        },
        addCond: function addCond() {
          this.filter.push({
            field: this.iblockFields[0].name,
            type: false
          });
        }
      },
      computed: {
        iblockType: function iblockType() {
          return this.$store.state.iblockType;
        },
        iblockList: function iblockList() {
          return this.$store.state.iblockList;
        },
        iblockOptions: function iblockOptions() {
          var _this = this;
          var result = [];
          this.iblockType.forEach(function (type) {
            var items = _this.getIblockItemsByType(type.iblockTypeId);
            if (items.length) {
              result.push({
                iblockTypeId: type.iblockTypeId,
                name: type.name,
                items: items
              });
            }
          });
          return result;
        },
        iblockFields: function iblockFields() {
          var result = this.$store.getters.iblockSectionFields(this.iblockId);
          if (Array.isArray(result)) return result;
          this.$store.dispatch('getIblockSectionFields', this.iblockId);
          return [];
        }
      },
      template: "\n    <div class=\"devbx-webform-iblock-section-filter-wizard\">\n        <div class=\"devbx-webform-iblock-select\">\n            <span class=\"select-iblock-label\">{{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_SELECT_IBLOCK_LABEL')}}</span>\n            \n            <div class=\"devbx-webform-cond-dropdown\">\n                <select v-model=\"iblockId\">\n                    <option value=\"0\">{{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_NOT_SELECTED_LABEL')}}</option>\n                    <optgroup v-for=\"iblockType in iblockOptions\" :key=\"iblockType.iblockTypeId\" :label=\"iblockType.name\">\n                        <option v-for=\"iblock in iblockType.items\" :value=\"iblock.id\">{{iblock.name}}</option>\n                    </optgroup>        \n                </select>\n            </div>\n        </div>\n        \n        <div class=\"devbx-webform-cond-items\" v-if=\"iblockId>0 && iblockFields.length>0\">\n            <devbx-webform-iblock-filter-cond v-for=\"(item, index) in filter\" :key=\"index\" :item=\"item\"\n                ref=\"conditions\"\n                :iblock-fields=\"iblockFields\" :cond-fields=\"condFields\" :cond-config=\"condConfig\">\n\n                <a href=\"#\" @click.stop.prevent=\"removeCond(index)\" class=\"devbx-webform-cond-remove\">\n                    <i class=\"fa fa-trash\"></i>            \n                </a>\n            </devbx-webform-iblock-filter-cond>\n            \n            <a href=\"#\" @click.stop.prevent=\"addCond\" class=\"devbx-webform-cond-add-group\">\n                <hr class=\"devbx-webform-cond-line\">        \n                <span class=\"devbx-webform-cond-label\">\n                    <i class=\"fa fa-plus\"></i>\n                </span>            \n                <hr class=\"devbx-webform-cond-line\">\n            </a>\n            \n        </div>\n\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-iblock-section-filter-field', {
      props: {
        formData: {
          type: Object,
          required: true
        },
        title: {
          type: String,
          "default": ''
        },
        wizardTitle: {
          type: String,
          "default": ''
        },
        iblockIdFieldName: {
          type: String,
          "default": ''
        },
        filterFieldName: {
          type: String,
          "default": ''
        },
        condFields: {
          type: Array,
          required: true
        }
      },
      data: function data() {
        return {
          popupId: false,
          condConfig: false
        };
      },
      created: function created() {
        this.condConfig = new WebFormCondConfig();
      },
      beforeUnmount: function beforeUnmount() {
        if (this.popupId) {
          var popup = BX.PopupWindowManager.getPopupById(this.popupId);
          if (popup) popup.close();
        }
      },
      computed: {
        iblockId: {
          get: function get() {
            return this.formData[this.iblockIdFieldName];
          },
          set: function set(val) {
            this.formData[this.iblockIdFieldName] = val;
          }
        },
        filter: {
          get: function get() {
            return this.formData[this.filterFieldName];
          },
          set: function set(val) {
            this.formData[this.filterFieldName] = val;
          }
        }
      },
      methods: {
        editFilter: function editFilter() {
          var options = {
            autoHide: false,
            draggable: false,
            closeByEsc: true,
            closeIcon: true,
            fixed: true,
            overlay: {
              backgroundColor: 'black',
              opacity: '80'
            },
            offsetLeft: 0,
            offsetTop: 0,
            bindOptions: {
              forceBindPosition: false
            },
            //bindOnResize: true,
            titleBar: this.wizardTitle,
            content: '',
            className: 'devbx-webform-builder devbx-webform-filter-popup',
            maxWidth: 800,
            maxHeight: 580,
            events: {
              onPopupClose: BX.delegate(this.onPopupClose, this),
              onPopupDestroy: BX.delegate(this.onPopupDestroy, this)
            },
            buttons: [new BX.PopupWindowButton({
              text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_DIALOG_BTN_SAVE_TITLE'),
              className: "popup-window-button-accept",
              events: {
                click: BX.delegate(this.saveFilter, this)
              }
            }), new BX.PopupWindowButton({
              text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_DIALOG_BTN_CLOSE_TITLE'),
              className: "popup-window-button-link popup-window-button-link-cancel",
              events: {
                click: BX.delegate(this.closePopup, this)
              }
            })]
          };
          var id = "DevBxFormsIblockSectionFilterWizardPopup";
          new BX.PopupWindow(id, null, options);
          this.popupId = id;
        },
        saveFilter: function saveFilter() {
          var hasErrors = false;
          this.$refs.wizard.$refs.conditions.forEach(function (cond) {
            if (cond.fieldError) hasErrors = true;
          });
          if (hasErrors) {
            this.$root.showPopupError(this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_ERR_FIX_INVALID_FIELDS'));
            return;
          }
          this.iblockId = this.$refs.wizard.iblockId;
          this.filter = this.$refs.wizard.filter;
          this.closePopup();
        },
        closePopup: function closePopup() {
          /*
          if (!this.rule.code.length && this.defaultValue)
          {
              this.rule.value = this.defaultValue;
          }
           */

          var popup = BX.PopupWindowManager.getPopupById(this.popupId);
          if (popup) popup.close();
        },
        onPopupClose: function onPopupClose(popup) {
          popup.destroy();
        },
        onPopupDestroy: function onPopupDestroy() {
          this.popupId = false;
        }
      },
      template: "\n<div class=\"devbx-webform-field\">\n    <div class=\"devbx-webform-label\">{{title}}</div>\n    \n    <div class=\"devbx-webform-edit\">\n        <div class=\"devbx-webform-edit-filter\">\n            <span class=\"devbx-webform-edit-filter-link\" @click.stop.prevent=\"editFilter()\">{{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_EDIT_FILTER')}}</span>\n        </div>        \n\n        <devbx-webform-popup-iblock-section-filter ref=\"wizard\" v-if=\"popupId\" :cond-fields=\"condFields\" :cond-config=\"condConfig\">\n        </devbx-webform-popup-iblock-section-filter>\n    </div>\n    \n    <div class=\"devbx-webform-error-label\" v-if=\"iblockId<=0\">\n        {{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_ERR_FILTER_NOT_CONFIGURED')}}           \n    </div>\n</div>\n"
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-form-layout-field-iblock-section', {
      mixins: [fieldMixin],
      computed: {
        defaultPlaceholder: function defaultPlaceholder() {
          return this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_IBLOCK_SECTION_PLACEHOLDER');
        },
        availableFields: function availableFields() {
          if (this.config.iblockId <= 0) return [];
          var fields = this.$store.getters.iblockSectionFields(this.config.iblockId);
          if (Array.isArray(fields)) return fields;
          this.$store.dispatch('getIblockSectionFields', this.config.iblockId);
          return [];
        },
        sortFields: function sortFields() {
          if (!this.availableFields.length) return [];
          var result = [];
          result.push({
            value: '',
            text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_IBLOCK_SECTION_SORT_NO_VALUE')
          });
          this.availableFields.forEach(function (field) {
            if (field.name === 'IBLOCK_ID') return;
            result.push({
              value: field.name,
              text: field.name + (field.label ? ' (' + field.label + ')' : '')
            });
          });
          return result;
        },
        pictureFields: function pictureFields() {
          var result = [];
          this.availableFields.forEach(function (field) {
            if (field.fieldType === 'file') {
              result.push({
                value: field.name,
                text: field.name + (field.label ? ' (' + field.label + ')' : '')
              });
            }
          });
          return result;
        },
        sortOrderValues: function sortOrderValues() {
          var result = [];
          result.push({
            value: 'ASC',
            text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_IBLOCK_SECTION_SORT_ORDER_ASC_TITLE')
          });
          result.push({
            value: 'DESC',
            text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_IBLOCK_SECTION_SORT_ORDER_DESC_TITLE')
          });
          return result;
        },
        panelItems: function panelItems() {
          var panel = {
            data: this.config,
            items: []
          };
          panel.items.push({
            name: 'devbx-webform-caption',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SETTINGS_TITLE') + ' - ' + this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_TEXT_NAME')
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            allowFormFields: true,
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_LABEL_TITLE'),
              fieldName: 'label',
              fieldVisibleName: 'labelHidden'
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_FIELD_NAME_TITLE'),
              fieldName: 'fieldName',
              live: false,
              readonly: this.config.systemId > 0
            }
          });
          panel.items.push({
            name: 'devbx-webform-selectbox-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_IBLOCK_SECTION_TYPE_TITLE'),
              type: 'radio',
              values: [{
                value: 'DROP_DOWN',
                text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_IBLOCK_SECTION_DROP_DOWN')
              }, {
                value: 'DROP_DOWN_PICTURE',
                text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_IBLOCK_SECTION_DROP_DOWN_WITH_PICTURE')
              }],
              fieldName: 'type'
            }
          });
          panel.items.push({
            id: 'filter',
            name: 'devbx-webform-iblock-section-filter-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_IBLOCK_SECTION_FILTER_TITLE'),
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_IBLOCK_SECTION_POPUP_FILTER_TITLE'),
              iblockIdFieldName: 'iblockId',
              filterFieldName: 'filter',
              condFields: this.$root.formFields
            }
          });
          if (this.sortFields.length) {
            panel.items.push({
              name: 'devbx-webform-selectbox-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_IBLOCK_SECTION_SORT_FIELD1_TITLE'),
                type: 'select',
                values: this.sortFields,
                fieldName: 'sortField1'
              }
            });
            panel.items.push({
              name: 'devbx-webform-selectbox-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_IBLOCK_SECTION_SORT_ORDER1_TITLE'),
                type: 'select',
                values: this.sortOrderValues,
                fieldName: 'sortOrder1'
              }
            });
            panel.items.push({
              name: 'devbx-webform-selectbox-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_IBLOCK_SECTION_SORT_FIELD2_TITLE'),
                type: 'select',
                values: this.sortFields,
                fieldName: 'sortField2'
              }
            });
            panel.items.push({
              name: 'devbx-webform-selectbox-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_IBLOCK_SECTION_SORT_ORDER2_TITLE'),
                type: 'select',
                values: this.sortOrderValues,
                fieldName: 'sortOrder2'
              }
            });
          }
          if (this.config.type === 'DROP_DOWN_PICTURE' && this.pictureFields.length) {
            panel.items.push({
              name: 'devbx-webform-selectbox-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_IBLOCK_SECTION_PICTURE_FIELD_TITLE'),
                type: 'select',
                values: this.pictureFields,
                fieldName: 'pictureField'
              }
            });
          }
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_PLACEHOLDER_TITLE'),
              fieldName: 'placeholder'
            }
          });
          panel.items.push({
            name: 'devbx-webform-html-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_HELP_TEXT_TITLE'),
              fieldName: 'helpText'
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SHOW_THIS_FIELD_TITLE'),
              fieldName: 'showRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_THIS_FIELD_TITLE'),
              defaultValue: 'always',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_REQUIRE_THIS_FIELD_TITLE'),
              fieldName: 'requireRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_REQUIRE_THIS_FIELD_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_READ_ONLY_THIS_FIELD_TITLE'),
              fieldName: 'readOnlyRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_READ_ONLY_THIS_FIELD_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SHOW_CUSTOM_ERROR_TITLE'),
              fieldName: 'showCustomError',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_CUSTOM_ERROR_TITLE'),
              defaultValue: 'never',
              condFields: this.$root.formFields,
              options: ['never', 'when']
            }
          });
          if (this.config.showCustomError.value === 'when' && this.config.showCustomError.code) {
            panel.items.push({
              name: 'devbx-webform-text-field',
              props: {
                title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CUSTOM_ERROR_TITLE'),
                fieldName: 'customError',
                live: false
              }
            });
          }
          return panel;
        }
      },
      methods: {
        getFieldLabel: function getFieldLabel(name) {
          if (this.config.iblockId <= 0) return name;
          var fields = this.$store.getters.iblockSectionFields(this.config.iblockId),
            field = false;
          if (!Array.isArray(fields)) {
            this.$store.dispatch('getIblockSectionFields', this.config.iblockId);
            return name;
          }
          fields.every(function (f) {
            if (f.name === name) field = f;
            return !field;
          });
          if (!field || !field.label) return name;
          return field.label;
        },
        getFields: function getFields() {
          return [{
            name: this.config.fieldName + '.ID',
            label: this.config.label + ' - ' + this.getFieldLabel('ID'),
            type: 'number'
          }, {
            name: this.config.fieldName + '.NAME',
            label: this.config.label + ' - ' + this.getFieldLabel('NAME'),
            type: 'string'
          }];
        }
      },
      template: "\n    <div class=\"devbx-webform-layout-field\">\n        <div class=\"devbx-webform-layout-label\" :class=\"{'devbx-webform-layout-label-hidden': config.labelHidden}\">\n            <p v-html=\"labelFormatted\"></p>\n        </div>\n        \n            <div class=\"devbx-webform-layout-control devbx-webform-layout-choice-drop-down\" \n                v-if=\"config.type == 'DROP_DOWN' || config.type == 'DROP_DOWN_PICTURE'\">\n                <span>\n                    <i class=\"fa fa-list-ul\"></i>\n                    <span v-if=\"config.placeholder\" class=\"devbx-webform-layout-placeholder\">\n                        {{config.placeholder}}                \n                    </span>\n                    <span v-else class=\"devbx-webform-layout-placeholder\">\n                        {{defaultPlaceholder}}                \n                    </span>\n                </span>\n                <div class=\"devbx-webform-layout-dropdown-button\"><i class=\"fa fa-chevron-down\"></i></div>\n            </div>\n        \n        \n        <div class=\"devbx-webform-layout-helptext\" v-if=\"config.helpText.length>0\">\n            <p v-html=\"helpTextFormatted\"></p>\n        </div>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-form-layout-section', {
      mixins: [fieldMixin],
      data: function data() {
        return {
          childLayout: false
        };
      },
      computed: {
        panelItems: function panelItems() {
          var panel = {
            data: this.config,
            items: []
          };
          panel.items.push({
            name: 'devbx-webform-caption',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SETTINGS_TITLE') + ' - ' + this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SECTION_NAME')
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_FIELD_NAME_TITLE'),
              fieldName: 'fieldName',
              live: false
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            allowFormFields: true,
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_LABEL_TITLE'),
              fieldName: 'label',
              fieldVisibleName: 'labelHidden'
            }
          });
          panel.items.push({
            name: 'devbx-webform-html-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_HELP_TEXT_TITLE'),
              fieldName: 'helpText'
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SHOW_THIS_FIELD_TITLE'),
              fieldName: 'showRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_THIS_FIELD_TITLE'),
              defaultValue: 'always',
              condFields: this.$root.formFields
            }
          });
          return panel;
        }
      },
      watch: {
        'item.size': function itemSize(val) {
          this.childLayout.maxRowSize = val;
          this.childLayout.checkAlignmentItems();
        }
      },
      methods: {
        saveConfig: function saveConfig() {
          this.config.layout = this.childLayout.getData();
        }
      },
      created: function created() {
        this.childLayout = this.$root.layoutItems.addLayout(this.item.size);
        this.childLayout.parent = this.page;
        this.item.childLayout = this.childLayout;
        this.childLayout.setData(this.config.layout);
        this.childLayout.checkAlignmentItems();
      },
      unmounted: function unmounted() {
        this.saveConfig();
        this.$root.layoutItems.deleteLayout(this.childLayout);
      },
      template: "\n    <div class=\"devbx-webform-layout-section\">\n        <div class=\"devbx-webform-layout-label\" :class=\"{'devbx-webform-layout-label-hidden': config.labelHidden}\">\n            <p v-html=\"labelFormatted\"></p>\n        </div>\n        <div class=\"devbx-webform-layout-helptext\" v-if=\"config.helpText.length>0\">\n            <p v-html=\"helpTextFormatted\"></p>\n        </div>\n        <div class=\"devbx-webform-layout-section-container\">\n            <devbx-webform-form-row v-for=\"(row, index) in childLayout.rows\" :key=\"row.id\" v-bind:page=\"childLayout\" v-bind:row=\"row\"/>\n        </div>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-form-layout-content', {
      props: ['page', 'item', 'row', 'active', 'config'],
      data: function data() {
        return {};
      },
      computed: {
        contentFormatted: function contentFormatted() {
          return this.$root.htmlFormatFields(this.config.content);
        },
        panelItems: function panelItems() {
          var panel = {
            data: this.config,
            items: []
          };
          panel.items.push({
            name: 'devbx-webform-caption',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SETTINGS_TITLE') + ' - ' + this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CONTENT_NAME')
            }
          });
          panel.items.push({
            name: 'devbx-webform-html-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_CONTENT_TITLE'),
              fieldName: 'content',
              'editorConfig': 'full'
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SHOW_THIS_FIELD_TITLE'),
              fieldName: 'showRule',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_THIS_FIELD_TITLE'),
              defaultValue: 'always',
              condFields: this.$root.formFields
            }
          });
          return panel;
        }
      },
      template: "\n    <div class=\"devbx-webform-layout-section\">\n        <div class=\"devbx-webform-layout-content\" v-html=\"contentFormatted\">\n        </div>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-pages-actions', {
      props: ['pages'],
      data: function data() {
        return {
          dragPage: false,
          settingsPageId: false,
          systemId: 'pages-actions'
        };
      },
      mounted: function mounted() {
        this.$root.registerSystemWebFormItem(this.systemId, this);
      },
      beforeUnmount: function beforeUnmount() {
        this.$root.unRegisterSystemWebFormItem(this.systemId);
        if (this.popup) {
          this.popup.destroy();
        }
      },
      methods: {
        saveWebForm: function saveWebForm() {
          alert('save');
        },
        getPageTitle: function getPageTitle(page) {
          var index = this.pages.items.indexOf(page);
          return this.$root.formatString(this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_NAVIGATION_PAGE_TITLE'), '#NUM#', index + 1);
        },
        addNewPage: function addNewPage() {
          this.$root.activePageId = this.$root.addPage().id;
        },
        deletePage: function deletePage(page) {
          var idx = this.pages.items.indexOf(page);
          if (idx < 0) return;
          if (page.haveUserFieldItems()) {
            this.askPageDelete(page);
          } else {
            this.deleteConfirmedPage(page);
          }
        },
        askPageDelete: function askPageDelete(page) {
          if (this.popup) {
            this.popup.close();
            return;
          }
          this.$root.createPopupWindow({
            titleBar: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_DIALOG_DELETE_PAGE_TITLE'),
            content: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_DIALOG_CONFIRM_DELETE_NON_EMPTY_PAGE'),
            buttons: [new BX.PopupWindowButton({
              text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_DIALOG_BTN_DELETE_TITLE'),
              className: "popup-window-button-accept",
              events: {
                click: BX.delegate(function () {
                  this.$root.closePopup();
                  this.deleteConfirmedPage(page);
                }, this)
              }
            }), new BX.PopupWindowButton({
              text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_DIALOG_BTN_CANCEL_TITLE'),
              className: "popup-window-button-link popup-window-button-link-cancel",
              events: {
                click: BX.delegate(function () {
                  this.$root.closePopup();
                }, this)
              }
            })]
          });
        },
        deleteConfirmedPage: function deleteConfirmedPage(page) {
          var idx = this.pages.items.indexOf(page);
          if (idx < 0) return;
          this.pages.deleteLayout(page);
          var self = this;
          setTimeout(function () {
            if (self.pages.items.length >= idx) {
              self.$root.activePageId = self.pages.items[self.pages.items.length - 1].id;
            } else {
              self.$root.activePageId = self.pages.items[idx].id;
            }
            this.settingsPageId = self.$root.activePageId;
          }, 0);
        },
        setActivePage: function setActivePage(pageId) {
          this.$root.activePageId = pageId;
        },
        dragEnter: function dragEnter(event, page) {
          if (this.dragPage) {
            event.preventDefault();
            if (this.dragPage === page) return;
            var oldIndex = this.pages.items.indexOf(this.dragPage),
              newIndex = this.pages.items.indexOf(page);
            this.pages.items.splice(oldIndex, 1);
            this.pages.items.splice(newIndex, 0, this.dragPage);
          } else {
            this.$root.activePageId = page.id;
          }
        },
        dragOver: function dragOver(event, page) {
          if (this.dragPage) {
            event.preventDefault();
          } else {
            this.$root.activePageId = page.id;
          }
        },
        dragStart: function dragStart(event, page) {
          this.dragPage = page;
        },
        dragEnd: function dragEnd(event, page) {
          this.$root.activePageId = page.id;
        },
        drop: function drop() {},
        showPageSettings: function showPageSettings(page) {
          this.settingsPageId = page.id;
          this.$root.activeId = this.systemId;
        }
      },
      computed: {
        activePageId: function activePageId() {
          return this.$root.activePageId;
        },
        panelItems: function panelItems() {
          var page = this.$root.pages.getLayoutById(this.settingsPageId);
          if (!page) return false;
          var panel = {
            data: page.config,
            items: []
          };
          panel.items.push({
            name: 'devbx-webform-caption',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FORM_PAGE_SETTINGS_TITLE')
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_SETTINGS_WEB_FORM_TITLE'),
              fieldName: 'pageTitle',
              trim: true
            }
          });
          panel.items.push({
            name: 'devbx-webform-html-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_SETTINGS_WEB_FORM_DESCRIPTION_TITLE'),
              fieldName: 'pageDescription'
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_SHOW_PAGE_TITLE'),
              fieldName: 'showPage',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_PAGE_TITLE'),
              defaultValue: 'always',
              condFields: this.$root.formFields
            }
          });
          return panel;
        }
      },
      template: "\n    <div class=\"devbx-webform-pages-actions\">\n        \n        <div class=\"devbx-webform-page-actions\">\n            <span class=\"devbx-webform-page-button\"\n            draggable=\"true\"\n            @dragstart=\"dragStart($event, page)\" @dragend=\"dragEnd($event, page)\" @drop=\"drop\"\n            @dragenter=\"dragEnter($event, page)\" @dragover=\"dragOver($event, page)\"\n            \n            :class=\"{'devbx-webform-page-button-active': activePageId == page.id}\" \n            v-for=\"page in pages.items\" @click=\"setActivePage(page.id)\" :key=\"page.id\">\n                <span class=\"devbx-webform-page-settings\" @click.stop.prevent=\"showPageSettings(page)\"><i class=\"fa fa-cog\"></i></span>\n                <span class=\"devbx-webform-page-title\">{{getPageTitle(page)}}</span>\n                <span class=\"devbx-webform-page-delete\" v-if=\"pages.items.length>1\" @click.stop.prevent=\"deletePage(page)\"><i class=\"fa fa-close\"></i></span>            \n            </span>\n            \n            <span class=\"devbx-webform-page-button\" @click=\"addNewPage\">\n                &nbsp;<span class=\"devbx-webform-page-add\"><i class=\"fa fa-plus\"></i></span>            \n            </span>\n        </div>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-form-actions', {
      props: ['page', 'formActions'],
      computed: {
        hasPrevPage: function hasPrevPage() {
          return this.$root.pages.items.indexOf(this.page) > 0;
        },
        hasNextPage: function hasNextPage() {
          return this.$root.pages.items.indexOf(this.page) < this.$root.pages.items.length - 1;
        },
        isFinishPage: function isFinishPage() {
          return this.$root.pages.items.indexOf(this.page) === this.$root.pages.items.length - 1;
        }
      },
      template: "\n    <div class=\"devbx-webform-form-actions\">\n           <devbx-webform-form-prev-page-button v-if=\"hasPrevPage\" :page=\"page\" :button-id=\"'prev-page-'+page.id\"></devbx-webform-form-prev-page-button>\n           <devbx-webform-form-next-page-button v-if=\"hasNextPage\" :page=\"page\" :button-id=\"'next-page-'+page.id\"></devbx-webform-form-next-page-button>\n           <devbx-webform-form-submit-button v-if=\"isFinishPage\" v-for=\"(formAction, index) in formActions\"\n           :key=\"index\" \n           :button-id=\"'form-action-'+index\"\n           :form-action=\"formAction\"></devbx-webform-form-submit-button>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-form-prev-page-button', {
      props: ['page', 'buttonId'],
      mounted: function mounted() {
        this.$root.registerSystemWebFormItem(this.buttonId, this);
      },
      beforeUnmount: function beforeUnmount() {
        this.$root.unRegisterSystemWebFormItem(this.buttonId);
      },
      methods: {
        onClick: function onClick() {
          this.$root.activeId = this.buttonId;
        }
      },
      computed: {
        isActive: function isActive() {
          return this.$root.activeId == this.buttonId;
        },
        panelItems: function panelItems() {
          var panel = {
            data: this.page.config,
            items: []
          };
          panel.items.push({
            name: 'devbx-webform-caption',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FORM_ACTION_SETTINGS_TITLE')
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FORM_ACTION_BUTTON_TITLE'),
              fieldName: 'prevButtonText',
              trim: true,
              defaultValue: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_PREV_PAGE_TITLE')
            }
          });
          return panel;
        }
      },
      template: "\n    <div class=\"devbx-webform-form-action\" :class=\"{'devbx-webform-form-action-active': isActive}\" @click=\"onClick\">\n        <div class=\"devbx-webform-form-button\">\n            <div class=\"devbx-webform-form-button-text\">{{page.config.prevButtonText}}</div>\n        </div>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-form-next-page-button', {
      props: ['page', 'buttonId'],
      mounted: function mounted() {
        this.$root.registerSystemWebFormItem(this.buttonId, this);
      },
      beforeUnmount: function beforeUnmount() {
        this.$root.unRegisterSystemWebFormItem(this.buttonId);
      },
      methods: {
        onClick: function onClick() {
          this.$root.activeId = this.buttonId;
        }
      },
      computed: {
        isActive: function isActive() {
          return this.$root.activeId == this.buttonId;
        },
        panelItems: function panelItems() {
          var panel = {
            data: this.page.config,
            items: []
          };
          panel.items.push({
            name: 'devbx-webform-caption',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FORM_ACTION_SETTINGS_TITLE')
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FORM_ACTION_BUTTON_TITLE'),
              fieldName: 'nextButtonText',
              trim: true,
              defaultValue: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_NEXT_PAGE_TITLE')
            }
          });
          panel.items.push({
            name: 'devbx-webform-condition',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FIELD_SHOW_NEXT_PAGE_TITLE'),
              fieldName: 'showNextButton',
              wizardTitle: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_COND_WIZARD_SHOW_NEXT_PAGE_TITLE'),
              defaultValue: 'always'
            }
          });
          return panel;
        }
      },
      template: "\n    <div class=\"devbx-webform-form-action\" :class=\"{'devbx-webform-form-action-active': isActive}\" @click=\"onClick\">\n        <div class=\"devbx-webform-form-button\">\n            <div class=\"devbx-webform-form-button-text\">{{page.config.nextButtonText}}</div>\n        </div>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-form-submit-button', {
      props: ['formAction', 'buttonId'],
      mounted: function mounted() {
        this.$root.registerSystemWebFormItem(this.buttonId, this);
      },
      beforeUnmount: function beforeUnmount() {
        this.$root.unRegisterSystemWebFormItem(this.buttonId);
      },
      methods: {
        onClick: function onClick() {
          this.$root.activeId = this.buttonId;
        }
      },
      computed: {
        isActive: function isActive() {
          return this.$root.activeId == this.buttonId;
        },
        panelItems: function panelItems() {
          var panel = {
            data: this.formAction,
            items: []
          };
          panel.items.push({
            name: 'devbx-webform-caption',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FORM_ACTION_SETTINGS_TITLE')
            }
          });
          panel.items.push({
            name: 'devbx-webform-text-field',
            props: {
              title: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_FORM_ACTION_BUTTON_TITLE'),
              fieldName: 'title',
              trim: true,
              defaultValue: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_SUBMIT_ACTION_TITLE')
            }
          });
          return panel;
        }
      },
      template: "\n    <div class=\"devbx-webform-form-action\" :class=\"{'devbx-webform-form-action-active': isActive}\" @click=\"onClick\">\n        <div class=\"devbx-webform-form-button\">\n            <div class=\"devbx-webform-form-button-text\">{{formAction.title}}</div>\n        </div>\n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-webform-public-admin-panel', {
      data: function data() {
        return {
          validation: true
        };
      },
      mounted: function mounted() {
        var popup = BX.PopupWindowManager.getPopupById(this.$parent.popupId);
        popup.contentContainer.appendChild(this.$el);
      },
      watch: {
        validation: function validation(val) {
          this.$parent.previewIframe.contentWindow.webForm.validation = val;
        }
      },
      template: "\n    <div class=\"devbx-webform-public-admin-panel\">\n        <div class=\"devbx-webform-public-admin-panel-items\">\n            <label>\n                {{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_PUBLIC_ADMIN_JS_VALIDATION')}}\n                <input type=\"checkbox\" v-model=\"validation\">\n            </label>\n        </div>            \n    </div>\n    "
    });
  });

  BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", function (app) {
    app.component('devbx-form-admin-actions', {
      data: function data() {
        return {
          popupId: false,
          previewIframe: false,
          waitResponse: false
        };
      },
      beforeUnmount: function beforeUnmount() {
        this.closePopup();
      },
      methods: {
        saveForm: function saveForm() {
          if (this.waitResponse) return;
          var postData = {
            lang: BX.message('LANGUAGE_ID'),
            webForm: JSON.stringify(this.$root.getWebFormData()),
            webFormConfig: JSON.stringify(this.$root.config)
          };
          postData.webFormId = this.$root.webFormId ? this.$root.webFormId : 0;
          this.waitResponse = true;
          BX.ajax.runAction('devbx:forms.api.webform.saveWebForm', {
            data: postData
          }).then(BX.delegate(this.saveWebFormSuccess, this), BX.delegate(this.responseError, this));
        },
        saveWebFormSuccess: function saveWebFormSuccess(response) {
          this.waitResponse = false;
          if (response.data.webFormId) {
            this.$root.webFormId = response.data.webFormId;
          }
          if (response.data.fields) {
            var formFields = this.$root.getAllFieldsByFieldName();
            response.data.fields.forEach(function (field) {
              var formField = formFields[field.config.fieldName];
              if (formField) {
                Object.keys(field.config).forEach(function (key) {
                  formField.config[key] = field.config[key];
                });
              }
            });
          }
        },
        responseError: function responseError(response) {
          this.waitResponse = false;
          if (response.errors) {
            this.$root.showPopupError(response.errors);
          }
        },
        previewForm: function previewForm() {
          if (this.waitResponse) return;
          var postData = {
            lang: BX.message('LANGUAGE_ID'),
            webForm: JSON.stringify(this.$root.getWebFormData())
          };
          postData.webFormId = this.$root.webFormId ? this.$root.webFormId : 0;
          this.waitResponse = true;
          BX.ajax.runAction('devbx:forms.api.webform.previewWebForm', {
            data: postData
          }).then(BX.delegate(this.previewWebFormSuccess, this), BX.delegate(this.responseError, this));
        },
        previewWebFormSuccess: function previewWebFormSuccess(response) {
          this.waitResponse = false;
          if (this.popupId) {
            this.closePopup();
          }

          /** @file /bitrix/js/main/popup/src/popup/popup.js */

          var options = {
            autoHide: false,
            draggable: false,
            closeByEsc: false,
            closeIcon: true,
            fixed: true,
            overlay: {
              backgroundColor: 'black',
              opacity: '80'
            },
            offsetLeft: 0,
            offsetTop: 0,
            bindOptions: {
              forceBindPosition: false
            },
            //bindOnResize: true,
            titleBar: this.wizardTitle,
            content: '',
            className: 'devbx-webform-preview',
            minWidth: 820,
            minHeight: 680,
            maxWidth: 820,
            maxHeight: 680,
            events: {
              onPopupClose: BX.delegate(this.onPopupClose, this),
              onPopupDestroy: BX.delegate(this.onPopupDestroy, this)
            },
            buttons: [new BX.PopupWindowButton({
              text: this.$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_DIALOG_BTN_CLOSE_TITLE'),
              className: "popup-window-button-link popup-window-button-link-cancel",
              events: {
                click: BX.delegate(this.closePopup, this)
              }
            })]
          };
          var popupId = "DevBxFormsPreviewWebFormPopup";
          var popup = new BX.PopupWindow(popupId, null, options);
          var container = document.createElement('div');
          this.previewIframe = document.createElement('iframe');
          container.classList.add('devbx-webform-preview-container');
          popup.contentContainer.appendChild(container);
          container.appendChild(this.previewIframe);
          this.previewIframe.name = 'preview';
          this.previewIframe.style.width = '100%';
          this.previewIframe.style.height = '100%';
          this.previewIframe.style.overflowX = 'hidden';
          this.previewIframe.style.overflowY = 'hidden';
          this.previewIframe.contentWindow.document.open();
          this.previewIframe.contentWindow.document.write(response.data.content);
          this.previewIframe.contentWindow.document.close();
          popup.show();
          this.popupId = popupId;
        },
        closePopup: function closePopup() {
          if (this.popupId) {
            var popup = BX.PopupWindowManager.getPopupById(this.popupId);
            if (popup) {
              popup.close();
            }
            this.popupId = false;
          }
        },
        onPopupClose: function onPopupClose(popup) {
          this.previewIframe = false;
          popup.destroy();
        },
        onPopupDestroy: function onPopupDestroy() {
          this.popupId = false;
        }
      },
      template: "\n    <div class=\"devbx-webform-admin-actions\">\n        <button class=\"ui-btn ui-btn-secondary\"\n            :class=\"{'ui-btn-disabled': waitResponse}\" \n            type=\"button\" @click.prevent.stop=\"previewForm\">{{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_BTN_FORM_PREVIEW')}}</button>\n        <button class=\"ui-btn ui-btn-primary\"\n            :class=\"{'ui-btn-disabled': waitResponse}\"\n            type=\"button\" @click.prevent.stop=\"saveForm\">{{$Bitrix.Loc.getMessage('DEVBX_WEB_FORM_BTN_FORM_SAVE')}}</button>\n            \n            <devbx-webform-public-admin-panel v-if=\"popupId\"></devbx-webform-public-admin-panel>\n    </div>\n    "
    });
  });

  exports.WebFormLayoutItems = WebFormLayoutItems;
  exports.WebFormPages = WebFormPages;
  exports.WebFormLayout = WebFormLayout;
  exports.WebFormRow = WebFormRow;
  exports.WebFormItem = WebFormItem;
  exports.createWebFormMaster = createWebFormMaster;

}((this.DevBX.Forms.Admin = this.DevBX.Forms.Admin || {})));
//# sourceMappingURL=webform.admin.js.map

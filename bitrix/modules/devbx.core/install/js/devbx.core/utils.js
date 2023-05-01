this.DevBX = this.DevBX || {};
(function (exports) {
    'use strict';

    function filterObjectsArray(filter, items, options) {
      var i,
        j,
        result,
        found,
        item,
        paramName,
        op,
        negative,
        cmp,
        callback = false;
      var operations = ['=', '!', '~', '!~', '[]', '![]', '<', '>', '<=', '>=', '!>', '!<', '!>=', '!<=', '@', '!@'];
      if (options === true) {
        options = {
          single: true
        };
      } else if (babelHelpers["typeof"](options) !== 'object') {
        options = {};
      }
      var single = options.single === true,
        byKey = options.byKey === true,
        excludeKeys = [];
      if (options.hasOwnProperty('excludeKeys')) excludeKeys = Object.values(options.excludeKeys);
      if (options.hasOwnProperty('result')) {
        result = options.result;
      } else {
        if (byKey) {
          result = {};
        } else {
          result = [];
        }
      }
      if (typeof options.callback === 'function') callback = options.callback;
      for (i in items) {
        if (excludeKeys.indexOf(i) !== -1) continue;
        if (!items.hasOwnProperty(i)) continue;
        item = items[i];
        found = true;
        for (j in filter) {
          if (!filter.hasOwnProperty(j)) continue;
          paramName = j;
          if (operations.indexOf(paramName.substring(0, 3)) !== -1) {
            op = paramName.substring(0, 3);
            paramName = paramName.substring(3);
          } else if (operations.indexOf(paramName.substring(0, 2)) !== -1) {
            op = paramName.substring(0, 2);
            paramName = paramName.substring(2);
          } else if (operations.indexOf(paramName.substring(0, 1)) !== -1) {
            op = paramName.substring(0, 1);
            paramName = paramName.substring(1);
          } else {
            op = '=';
          }
          negative = false;
          if (op.substring(0, 1) == '!') {
            negative = true;
            op = op.substring(1);
          }
          if (babelHelpers["typeof"](filter[j]) == 'object' && op !== '@' && !Array.isArray(filter[j])) {
            if (op === '[]') {
              cmp = filterObjectsArray(filter[j], item[paramName], true) === false;
              cmp ^= negative;
              if (cmp) {
                found = false;
                break;
              }
            } else {
              cmp = filterObjectsArray(filter[j], [item[paramName]], true) === false;
              cmp ^= negative;
              if (cmp) {
                found = false;
                break;
              }
            }
            continue;
            //break;
          }

          try {
            if (!item.hasOwnProperty(paramName)) {
              found = negative;
              break;
            }
          } catch (e) {
            console.log(item, paramName, j, items);
            throw e;
          }
          switch (op) {
            case '~':
              cmp = item[paramName].indexOf(filter[j]) !== -1;
              break;
            case '<':
              cmp = parseFloat(item[paramName]) < filter[j];
              break;
            case '<=':
              cmp = parseFloat(item[paramName]) <= filter[j];
              break;
            case '>':
              cmp = parseFloat(item[paramName]) > filter[j];
              break;
            case '>=':
              cmp = parseFloat(item[paramName]) >= filter[j];
              break;
            case '@':
              cmp = filter[j].indexOf(item[paramName]) !== -1;
              break;
            default:
              cmp = item[paramName] == filter[j];
              break;
          }
          if (negative) cmp = !cmp;
          found = cmp;
          if (!found) break;
        }
        if (found) {
          if (callback) callback.call(window, item, i, items);
          if (!!single) return item;
          if (byKey) {
            result[i] = item;
          } else {
            result.push(item);
          }
        }
      }
      if (!!single) return false;
      return result;
    }
    function objectDiff(obj1, obj2) {
      var i, result, val;
      result = {};
      if (typeof obj1 === 'undefined' || typeof obj2 === 'undefined') return result;
      for (i in obj1) {
        if (!obj1.hasOwnProperty(i)) continue;
        if (!obj2.hasOwnProperty(i)) {
          result[i] = obj1[i];
          continue;
        }
        if (babelHelpers["typeof"](obj1[i]) !== babelHelpers["typeof"](obj2[i])) {
          result[i] = obj1[i];
          continue;
        }
        if (babelHelpers["typeof"](obj1[i]) == 'object' && obj1[i] !== null) {
          if (obj2[i] == null) {
            result[i] = obj1[i];
            continue;
          }
          val = objectDiff(obj1[i], obj2[i]);
          if (Object.keys(val).length === 0) continue;
          result[i] = val;
          continue;
        }
        if (obj1[i] !== obj2[i]) {
          result[i] = obj1[i];
        }
      }
      return result;
    }
    function numWord(value, words, show) {
      value = parseInt(value);
      show = show !== false;
      var num = value % 100;
      if (num > 19) {
        num = num % 10;
      }
      var out = show ? value + ' ' : '';
      switch (num) {
        case 1:
          out += words[0];
          break;
        case 2:
        case 3:
        case 4:
          out += words[1];
          break;
        default:
          out += words[2];
          break;
      }
      return out;
    }
    function pushToObject(obj, value) {
      var i,
        val,
        key = 0;
      for (i in obj) {
        if (!obj.hasOwnProperty(i)) continue;
        key++;
        val = parseInt(i);
        if (val > key) key = val + 1;
      }
      while (obj.hasOwnProperty(key)) {
        key++;
      }
      obj[key] = value;
    }
    function getValueByPath(obj, name) {
      var param = obj,
        i,
        j,
        subparam,
        multiple;
      multiple = name.substring(name.length - 2) === '[]';
      if (multiple) name = name.substring(0, name.length - 2);
      while (true) {
        i = name.indexOf('[');
        if (i === -1) {
          return param[name];
        }
        j = name.indexOf(']', i);
        subparam = name.substring(0, i);
        if (subparam.length > 0) {
          if (!param.hasOwnProperty(subparam)) return undefined;
          param = param[subparam];
        }
        subparam = name.substring(i + 1, j);
        name = name.substring(j + 1);
        if (name.length == 0) {
          return param[subparam];
        }
        if (!param.hasOwnProperty(subparam)) return undefined;
        param = param[subparam];
      }
    }
    function setValueByPath(obj, name, value) {
      var param = obj,
        i,
        j,
        subparam,
        multiple;
      multiple = name.substring(name.length - 2) === '[]';
      if (multiple) name = name.substring(0, name.length - 2);
      while (true) {
        i = name.indexOf('[');
        if (i === -1) {
          param[name] = value;
          return obj;
        }
        j = name.indexOf(']', i);
        subparam = name.substring(0, i);
        if (subparam.length > 0) {
          if (!param.hasOwnProperty(subparam)) param[subparam] = {};
          param = param[subparam];
        }
        subparam = name.substring(i + 1, j);
        name = name.substring(j + 1);
        if (name.length == 0) {
          param[subparam] = value;
          return obj;
        }
        if (!param.hasOwnProperty(subparam)) param[subparam] = {};
        param = param[subparam];
      }
    }
    function saveFormDataObj(obj, node) {
      var items,
        item,
        i,
        j,
        value,
        name,
        multiple,
        values = {};
      items = node.querySelectorAll('input, textarea');
      for (i = 0; i < items.length; i++) {
        item = items[i];
        name = item.name.trim();
        if (name.length === 0) continue;
        multiple = name.substring(name.length - 2) === '[]';
        if (multiple) name = name.substring(0, name.length - 2);
        if (item.tagName != 'TEXTAREA') {
          switch (item.type) {
            case 'file':
              //TODO
              break;
            case 'radio':
            case 'checkbox':
              if (!item.checked) {
                if (multiple) {
                  if (!values.hasOwnProperty(name)) {
                    values[name] = [];
                  }
                }
                continue;
              }
              value = item.value;
              break;
            default:
              value = item.value;
              break;
          }
        } else {
          value = item.value;
        }
        if (multiple) {
          if (!values.hasOwnProperty(name)) {
            values[name] = [];
          }
          values[name].push(value);
        } else {
          values[name] = value;
        }
      }
      items = node.querySelectorAll('select');
      for (i = 0; i < items.length; i++) {
        item = items[i];
        name = item.name.trim();
        if (name.length === 0) continue;
        multiple = name.substring(name.length - 2) === '[]';
        if (multiple) name = name.substring(0, name.length - 2);
        if (multiple) {
          if (!values.hasOwnProperty(name)) {
            values[name] = [];
          }
        }
        if (item.selectedOptions.length == 0) {
          if (!multiple) {
            values[name] = '';
          }
          continue;
        }
        if (multiple) {
          for (j = 0; j < item.selectedOptions.length; j++) {
            values[name].push(item.selectedOptions[j].value);
          }
        } else {
          values[name] = item.selectedOptions[0].value;
        }
      }
      for (i in values) {
        if (!values.hasOwnProperty(i)) continue;
        setValueByPath(obj, i, values[i]);
      }
      return obj;
    }
    function bindObjEvents(node, obj, prefix, events) {
      if (!Array.isArray(events)) events = ['click', 'change', 'input'];
      var data = {
        obj: obj,
        prefix: typeof prefix === 'string' ? prefix : ''
      };
      if (typeof window.jQuery === 'function') {
        events.forEach(function (name) {
          $(node).on(name, BX.proxy(nodeEvent, data));
        });
      } else {
        events.forEach(function (name) {
          BX.bindDelegate(node, name, {}, BX.proxy(nodeEvent, data));
        });
      }
    }
    function nodeEvent(e) {
      var target = e.target || e.srcElement,
        action,
        type = e.type.charAt(0).toUpperCase() + e.type.slice(1).toLowerCase(),
        clickEvent = type === 'Click';
      while (target) {
        if (target.nodeType != Node.ELEMENT_NODE) break;
        if (clickEvent && target.hasAttribute('data-action')) {
          action = normalizeEntityName(target.getAttribute('data-action'), this.prefix) + 'Action';
          if (window.debugEvent) console.log(this, action);
          if (typeof this.obj[action] == 'function') {
            BX.proxy_context = target;
            try {
              this.obj[action](e, target);
            } finally {
              BX.proxy_context = null;
            }
            return;
          }
        }
        if (target.hasAttribute('data-entity')) {
          action = normalizeEntityName(target.getAttribute('data-entity'), this.prefix) + type;
          if (window.debugEvent) console.log(this, action);
          if (typeof this.obj[action] == 'function') {
            BX.proxy_context = target;
            try {
              this.obj[action](e, target);
            } finally {
              BX.proxy_context = null;
            }
            return;
          }
        }
        if (!clickEvent) break;
        target = target.parentNode;
      }
    }
    function normalizeEntityName(value, prefix) {
      var parts = value.split(/[-_]/),
        result = '';
      if (typeof prefix !== 'string') prefix = '';
      if (parts.length > 1) {
        parts.forEach(function (s) {
          if (result.length > 0) {
            result += s.charAt(0).toUpperCase() + s.slice(1).toLowerCase();
          } else {
            result = s.toLowerCase();
          }
        });
      } else {
        if (value.toUpperCase() == value) {
          result = value.toLowerCase();
        } else {
          result = value;
        }
      }
      if (prefix.length > 0) {
        result = prefix + result.charAt(0).toUpperCase() + result.slice(1);
      }
      return result;
    }
    var nodeSingleEntity = /*#__PURE__*/function () {
      function nodeSingleEntity(node, entityName) {
        babelHelpers.classCallCheck(this, nodeSingleEntity);
        this.node = node;
        this.entityName = entityName;
        this.entity = false;
      }
      babelHelpers.createClass(nodeSingleEntity, [{
        key: "getEntity",
        value: function getEntity() {
          var _this = this;
          var node;
          if (typeof this.node === 'function') {
            node = this.node();
          } else {
            node = this.node;
          }
          if (this.entity) {
            if (node.contains(this.entity)) return this.entity;
            console.log('invalid entity');
          }
          if (!node) return null;
          this.entity = null;
          Object.values(node.querySelectorAll('[data-entity]')).every(function (item) {
            if (!item.devbxEntityName) {
              item.devbxEntityName = normalizeEntityName(item.dataset.entity);
            }
            if (item.devbxEntityName === _this.entityName) {
              _this.entity = item;
              return false;
            }
            return true;
          });
          return this.entity;
        }
      }]);
      return nodeSingleEntity;
    }();
    var nodeArrayEntity = /*#__PURE__*/function () {
      function nodeArrayEntity(node, entityName) {
        babelHelpers.classCallCheck(this, nodeArrayEntity);
        this.node = node;
        this.entityName = entityName;
      }
      babelHelpers.createClass(nodeArrayEntity, [{
        key: "getEntity",
        value: function getEntity() {
          var _this2 = this;
          var result = [],
            node;
          if (typeof this.node === 'function') {
            node = this.node();
          } else {
            node = this.node;
          }
          if (!node) return [];
          node.querySelectorAll('[data-entity]').forEach(function (item) {
            if (!item.devbxEntityName) {
              item.devbxEntityName = normalizeEntityName(item.dataset.entity);
            }
            if (item.devbxEntityName === _this2.entityName) {
              result.push(item);
            }
          });
          return result;
        }
      }]);
      return nodeArrayEntity;
    }();
    var nodSubEntity = /*#__PURE__*/function () {
      function nodSubEntity(node, entityName, data) {
        babelHelpers.classCallCheck(this, nodSubEntity);
        this.node = node;
        this.entityName = entityName;
        this.data = data;
        this.entity = null;
        this.entities = null;
      }
      babelHelpers.createClass(nodSubEntity, [{
        key: "getEntity",
        value: function getEntity() {
          var _this3 = this;
          var node;
          if (typeof this.node === 'function') {
            node = this.node();
          } else {
            node = this.node;
          }
          if (!node) return null;
          if (this.entity) {
            if (node.contains(this.entity)) return this.entities;
            console.log('invalid entity');
          }
          if (!node) return null;
          this.entity = null;
          this.entities = null;
          Object.values(node.querySelectorAll('[data-entity]')).every(function (item) {
            if (!item.devbxEntityName) {
              item.devbxEntityName = normalizeEntityName(item.dataset.entity);
            }
            if (item.devbxEntityName === _this3.entityName) {
              _this3.entity = item;
              return false;
            }
            return true;
          });
          this.entities = Object.assign({}, this.data);
          getNodeEntities(node, this.entities);
          return this.entities;
        }
      }]);
      return nodSubEntity;
    }();
    function getNodeEntities(node, data) {
      Object.keys(data).forEach(function (entityName) {
        var obj;
        if (Array.isArray(data[entityName])) {
          obj = new nodeArrayEntity(node, entityName);
        } else if (babelHelpers["typeof"](data[entityName]) === 'object') {
          obj = new nodSubEntity(node, entityName, data[entityName]);
        } else {
          obj = new nodeSingleEntity(node, entityName);
        }
        Object.defineProperty(data, entityName, {
          get: function get() {
            return obj.getEntity();
          }
        });
      });
    }
    function deepClone(obj) {
      var mapData = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var result;
      if (Array.isArray(obj)) {
        var _result;
        result = [];
        (_result = result).push.apply(_result, babelHelpers.toConsumableArray(obj));
      } else {
        result = Object.assign({}, obj);
      }
      if (!mapData.hasOwnProperty('old')) mapData.old = [];
      if (!mapData.hasOwnProperty('new')) mapData["new"] = [];
      Object.keys(result).forEach(function (k) {
        if (babelHelpers["typeof"](result[k]) === 'object' && result[k] !== null) {
          var found = false;
          for (var i = 0; i < mapData.old.length; i++) {
            if (mapData.old[i] === result[k]) {
              result[k] = mapData["new"][i];
              found = true;
            }
          }
          if (!found) {
            mapData.old.push(result[k]);
            result[k] = deepClone(result[k], mapData);
            mapData["new"].push(result[k]);
          }
        }
      });
      return result;
    }

    exports.filterObjectsArray = filterObjectsArray;
    exports.objectDiff = objectDiff;
    exports.numWord = numWord;
    exports.pushToObject = pushToObject;
    exports.getValueByPath = getValueByPath;
    exports.setValueByPath = setValueByPath;
    exports.saveFormDataObj = saveFormDataObj;
    exports.bindObjEvents = bindObjEvents;
    exports.normalizeEntityName = normalizeEntityName;
    exports.nodeArrayEntity = nodeArrayEntity;
    exports.nodSubEntity = nodSubEntity;
    exports.getNodeEntities = getNodeEntities;
    exports.deepClone = deepClone;

}((this.DevBX.Utils = this.DevBX.Utils || {})));
//# sourceMappingURL=utils.js.map

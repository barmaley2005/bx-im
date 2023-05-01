this.DevBX = this.DevBX || {};
this.DevBX.Forms = this.DevBX.Forms || {};
(function (exports) {
  'use strict';

  function isEmpty(opt) {
    if (opt === 0) return false;
    if (Array.isArray(opt) && opt.length === 0) return true;
    return !opt;
  }
  function not(fun) {
    return function () {
      return !fun.apply(void 0, arguments);
    };
  }
  function includes(str, query) {
    /* istanbul ignore else */
    if (str === undefined) str = 'undefined';
    if (str === null) str = 'null';
    if (str === false) str = 'false';
    var text = str.toString().toLowerCase();
    return text.indexOf(query.trim()) !== -1;
  }
  function filterOptions(options, search, label, customLabel) {
    return options.filter(function (option) {
      return includes(customLabel(option, label), search);
    });
  }
  function stripGroups(options) {
    return options.filter(function (option) {
      return !option.$isLabel;
    });
  }
  function flattenOptions(values, label) {
    return function (options) {
      return options.reduce(function (prev, curr) {
        /* istanbul ignore else */
        if (curr[values] && curr[values].length) {
          prev.push({
            $groupLabel: curr[label],
            $isLabel: true
          });
          return prev.concat(curr[values]);
        }
        return prev;
      }, []);
    };
  }
  function filterGroups(search, label, values, groupLabel, customLabel) {
    return function (groups) {
      return groups.map(function (group) {
        var _ref;
        /* istanbul ignore else */
        if (!group[values]) {
          console.warn("Options passed to vue-multiselect do not contain groups, despite the config.");
          return [];
        }
        var groupOptions = filterOptions(group[values], search, label, customLabel);
        return groupOptions.length ? (_ref = {}, babelHelpers.defineProperty(_ref, groupLabel, group[groupLabel]), babelHelpers.defineProperty(_ref, values, groupOptions), _ref) : [];
      });
    };
  }
  var flow = function flow() {
    for (var _len = arguments.length, fns = new Array(_len), _key = 0; _key < _len; _key++) {
      fns[_key] = arguments[_key];
    }
    return function (x) {
      return fns.reduce(function (v, f) {
        return f(v);
      }, x);
    };
  };
  var multiselectMixin = {
    data: function data() {
      return {
        search: '',
        isOpen: false,
        preferredOpenDirection: 'below',
        optimizedHeight: this.maxHeight
      };
    },
    props: {
      /**
       * Decide whether to filter the results based on search query.
       * Useful for async filtering, where we search through more complex data.
       * @type {Boolean}
       */
      internalSearch: {
        type: Boolean,
        "default": true
      },
      /**
       * Array of available options: Objects, Strings or Integers.
       * If array of objects, visible label will default to option.label.
       * If `labal` prop is passed, label will equal option['label']
       * @type {Array}
       */
      options: {
        type: Array,
        required: true
      },
      /**
       * Equivalent to the `multiple` attribute on a `<select>` input.
       * @default false
       * @type {Boolean}
       */
      multiple: {
        type: Boolean,
        "default": false
      },
      /**
       * Presets the selected options value.
       * @type {Object||Array||String||Integer}
       */
      value: {
        type: null,
        "default": function _default() {
          return [];
        }
      },
      /**
       * Key to compare objects
       * @default 'id'
       * @type {String}
       */
      trackBy: {
        type: String
      },
      /**
       * Label to look for in option Object
       * @default 'label'
       * @type {String}
       */
      label: {
        type: String
      },
      /**
       * Enable/disable search in options
       * @default true
       * @type {Boolean}
       */
      searchable: {
        type: Boolean,
        "default": true
      },
      /**
       * Clear the search input after `)
       * @default true
       * @type {Boolean}
       */
      clearOnSelect: {
        type: Boolean,
        "default": true
      },
      /**
       * Hide already selected options
       * @default false
       * @type {Boolean}
       */
      hideSelected: {
        type: Boolean,
        "default": false
      },
      /**
       * Equivalent to the `placeholder` attribute on a `<select>` input.
       * @default 'Select option'
       * @type {String}
       */
      placeholder: {
        type: String,
        "default": 'Select option'
      },
      /**
       * Allow to remove all selected values
       * @default true
       * @type {Boolean}
       */
      allowEmpty: {
        type: Boolean,
        "default": true
      },
      /**
       * Reset this.internalValue, this.search after this.internalValue changes.
       * Useful if want to create a stateless dropdown.
       * @default false
       * @type {Boolean}
       */
      resetAfter: {
        type: Boolean,
        "default": false
      },
      /**
       * Enable/disable closing after selecting an option
       * @default true
       * @type {Boolean}
       */
      closeOnSelect: {
        type: Boolean,
        "default": true
      },
      /**
       * Function to interpolate the custom label
       * @default false
       * @type {Function}
       */
      customLabel: {
        type: Function,
        "default": function _default(option, label) {
          if (isEmpty(option)) return '';
          return label ? option[label] : option;
        }
      },
      /**
       * Disable / Enable tagging
       * @default false
       * @type {Boolean}
       */
      taggable: {
        type: Boolean,
        "default": false
      },
      /**
       * String to show when highlighting a potential tag
       * @default 'Press enter to create a tag'
       * @type {String}
      */
      tagPlaceholder: {
        type: String,
        "default": 'Press enter to create a tag'
      },
      /**
       * By default new tags will appear above the search results.
       * Changing to 'bottom' will revert this behaviour
       * and will proritize the search results
       * @default 'top'
       * @type {String}
      */
      tagPosition: {
        type: String,
        "default": 'top'
      },
      /**
       * Number of allowed selected options. No limit if 0.
       * @default 0
       * @type {Number}
      */
      max: {
        type: [Number, Boolean],
        "default": false
      },
      /**
       * Will be passed with all events as second param.
       * Useful for identifying events origin.
       * @default null
       * @type {String|Integer}
      */
      id: {
        "default": null
      },
      /**
       * Limits the options displayed in the dropdown
       * to the first X options.
       * @default 1000
       * @type {Integer}
      */
      optionsLimit: {
        type: Number,
        "default": 1000
      },
      /**
       * Name of the property containing
       * the group values
       * @default 1000
       * @type {String}
      */
      groupValues: {
        type: String
      },
      /**
       * Name of the property containing
       * the group label
       * @default 1000
       * @type {String}
      */
      groupLabel: {
        type: String
      },
      /**
       * Allow to select all group values
       * by selecting the group label
       * @default false
       * @type {Boolean}
       */
      groupSelect: {
        type: Boolean,
        "default": false
      },
      /**
       * Array of keyboard keys to block
       * when selecting
       * @default 1000
       * @type {String}
      */
      blockKeys: {
        type: Array,
        "default": function _default() {
          return [];
        }
      },
      /**
       * Prevent from wiping up the search value
       * @default false
       * @type {Boolean}
      */
      preserveSearch: {
        type: Boolean,
        "default": false
      },
      /**
       * Select 1st options if value is empty
       * @default false
       * @type {Boolean}
      */
      preselectFirst: {
        type: Boolean,
        "default": false
      },
      /**
       * Prevent autofocus
       * @default false
       * @type {Boolean}
      */
      preventAutofocus: {
        type: Boolean,
        "default": false
      }
    },
    mounted: function mounted() {
      /* istanbul ignore else */
      if (!this.multiple && this.max) {
        console.warn('[Vue-Multiselect warn]: Max prop should not be used when prop Multiple equals false.');
      }
      if (this.preselectFirst && !this.internalValue.length && this.options.length) {
        this.select(this.filteredOptions[0]);
      }
    },
    computed: {
      internalValue: function internalValue() {
        return this.value || this.value === 0 ? Array.isArray(this.value) ? this.value : [this.value] : [];
      },
      filteredOptions: function filteredOptions() {
        var search = this.search || '';
        var normalizedSearch = search.toLowerCase().trim();
        var options = this.options.concat();

        /* istanbul ignore else */
        if (this.internalSearch) {
          options = this.groupValues ? this.filterAndFlat(options, normalizedSearch, this.label) : filterOptions(options, normalizedSearch, this.label, this.customLabel);
        } else {
          options = this.groupValues ? flattenOptions(this.groupValues, this.groupLabel)(options) : options;
        }
        options = this.hideSelected ? options.filter(not(this.isSelected)) : options;

        /* istanbul ignore else */
        if (this.taggable && normalizedSearch.length && !this.isExistingOption(normalizedSearch)) {
          if (this.tagPosition === 'bottom') {
            options.push({
              isTag: true,
              label: search
            });
          } else {
            options.unshift({
              isTag: true,
              label: search
            });
          }
        }
        return options.slice(0, this.optionsLimit);
      },
      valueKeys: function valueKeys() {
        var _this = this;
        if (this.trackBy) {
          return this.internalValue.map(function (element) {
            return element[_this.trackBy];
          });
        } else {
          return this.internalValue;
        }
      },
      optionKeys: function optionKeys() {
        var _this2 = this;
        var options = this.groupValues ? this.flatAndStrip(this.options) : this.options;
        return options.map(function (element) {
          return _this2.customLabel(element, _this2.label).toString().toLowerCase();
        });
      },
      currentOptionLabel: function currentOptionLabel() {
        return this.multiple ? this.searchable ? '' : this.placeholder : this.internalValue.length ? this.getOptionLabel(this.internalValue[0]) : this.searchable ? '' : this.placeholder;
      }
    },
    watch: {
      internalValue: function internalValue() {
        /* istanbul ignore else */
        if (this.resetAfter && this.internalValue.length) {
          this.search = '';
          this.$emit('input', this.multiple ? [] : null);
        }
      },
      search: function search() {
        this.$emit('search-change', this.search, this.id);
      }
    },
    methods: {
      /**
       * Returns the internalValue in a way it can be emited to the parent
       * @returns {Object||Array||String||Integer}
       */
      getValue: function getValue() {
        return this.multiple ? this.internalValue : this.internalValue.length === 0 ? null : this.internalValue[0];
      },
      /**
       * Filters and then flattens the options list
       * @param  {Array}
       * @returns {Array} returns a filtered and flat options list
       */
      filterAndFlat: function filterAndFlat(options, search, label) {
        return flow(filterGroups(search, label, this.groupValues, this.groupLabel, this.customLabel), flattenOptions(this.groupValues, this.groupLabel))(options);
      },
      /**
       * Flattens and then strips the group labels from the options list
       * @param  {Array}
       * @returns {Array} returns a flat options list without group labels
       */
      flatAndStrip: function flatAndStrip(options) {
        return flow(flattenOptions(this.groupValues, this.groupLabel), stripGroups)(options);
      },
      /**
       * Updates the search value
       * @param  {String}
       */
      updateSearch: function updateSearch(query) {
        this.search = query;
      },
      /**
       * Finds out if the given query is already present
       * in the available options
       * @param  {String}
       * @returns {Boolean} returns true if element is available
       */
      isExistingOption: function isExistingOption(query) {
        return !this.options ? false : this.optionKeys.indexOf(query) > -1;
      },
      /**
       * Finds out if the given element is already present
       * in the result value
       * @param  {Object||String||Integer} option passed element to check
       * @returns {Boolean} returns true if element is selected
       */
      isSelected: function isSelected(option) {
        var opt = this.trackBy ? option[this.trackBy] : option;
        return this.valueKeys.indexOf(opt) > -1;
      },
      /**
       * Finds out if the given option is disabled
       * @param  {Object||String||Integer} option passed element to check
       * @returns {Boolean} returns true if element is disabled
       */
      isOptionDisabled: function isOptionDisabled(option) {
        return !!option.$isDisabled;
      },
      /**
       * Returns empty string when options is null/undefined
       * Returns tag query if option is tag.
       * Returns the customLabel() results and casts it to string.
       *
       * @param  {Object||String||Integer} Passed option
       * @returns {Object||String}
       */
      getOptionLabel: function getOptionLabel(option) {
        if (isEmpty(option)) return '';
        /* istanbul ignore else */
        if (option.isTag) return option.label;
        /* istanbul ignore else */
        if (option.$isLabel) return option.$groupLabel;
        var label = this.customLabel(option, this.label);
        /* istanbul ignore else */
        if (isEmpty(label)) return '';
        return label;
      },
      /**
       * Add the given option to the list of selected options
       * or sets the option as the selected option.
       * If option is already selected -> remove it from the results.
       *
       * @param  {Object||String||Integer} option to select/deselect
       * @param  {Boolean} block removing
       */
      select: function select(option, key) {
        /* istanbul ignore else */
        if (option.$isLabel && this.groupSelect) {
          this.selectGroup(option);
          return;
        }
        if (this.blockKeys.indexOf(key) !== -1 || this.disabled || option.$isDisabled || option.$isLabel) return;
        /* istanbul ignore else */
        if (this.max && this.multiple && this.internalValue.length === this.max) return;
        /* istanbul ignore else */
        if (key === 'Tab' && !this.pointerDirty) return;
        if (option.isTag) {
          this.$emit('tag', option.label, this.id);
          this.search = '';
          if (this.closeOnSelect && !this.multiple) this.deactivate();
        } else {
          var isSelected = this.isSelected(option);
          if (isSelected) {
            if (key !== 'Tab') this.removeElement(option);
            return;
          }
          if (this.multiple) {
            this.$emit('input', this.internalValue.concat([option]), this.id);
          } else {
            this.$emit('input', option, this.id);
          }
          this.$emit('select', option, this.id);

          /* istanbul ignore else */
          if (this.clearOnSelect) this.search = '';
        }
        /* istanbul ignore else */
        if (this.closeOnSelect) this.deactivate();
      },
      /**
       * Add the given group options to the list of selected options
       * If all group optiona are already selected -> remove it from the results.
       *
       * @param  {Object||String||Integer} group to select/deselect
       */
      selectGroup: function selectGroup(selectedGroup) {
        var _this3 = this;
        var group = this.options.find(function (option) {
          return option[_this3.groupLabel] === selectedGroup.$groupLabel;
        });
        if (!group) return;
        if (this.wholeGroupSelected(group)) {
          this.$emit('remove', group[this.groupValues], this.id);
          var newValue = this.internalValue.filter(function (option) {
            return group[_this3.groupValues].indexOf(option) === -1;
          });
          this.$emit('input', newValue, this.id);
        } else {
          var optionsToAdd = group[this.groupValues].filter(function (option) {
            return !(_this3.isOptionDisabled(option) || _this3.isSelected(option));
          });

          // if max is defined then just select options respecting max
          if (this.max) {
            optionsToAdd.splice(this.max - this.internalValue.length);
          }
          this.$emit('select', optionsToAdd, this.id);
          this.$emit('input', this.internalValue.concat(optionsToAdd), this.id);
        }
        if (this.closeOnSelect) this.deactivate();
      },
      /**
       * Helper to identify if all values in a group are selected
       *
       * @param {Object} group to validated selected values against
       */
      wholeGroupSelected: function wholeGroupSelected(group) {
        var _this4 = this;
        return group[this.groupValues].every(function (option) {
          return _this4.isSelected(option) || _this4.isOptionDisabled(option);
        });
      },
      /**
       * Helper to identify if all values in a group are disabled
       *
       * @param {Object} group to check for disabled values
       */
      wholeGroupDisabled: function wholeGroupDisabled(group) {
        return group[this.groupValues].every(this.isOptionDisabled);
      },
      /**
       * Removes the given option from the selected options.
       * Additionally checks this.allowEmpty prop if option can be removed when
       * it is the last selected option.
       *
       * @param  {type} option description
       * @returns {type}        description
       */
      removeElement: function removeElement(option) {
        var shouldClose = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
        /* istanbul ignore else */
        if (this.disabled) return;
        /* istanbul ignore else */
        if (option.$isDisabled) return;
        /* istanbul ignore else */
        if (!this.allowEmpty && this.internalValue.length <= 1) {
          this.deactivate();
          return;
        }
        var index = babelHelpers["typeof"](option) === 'object' ? this.valueKeys.indexOf(option[this.trackBy]) : this.valueKeys.indexOf(option);
        if (this.multiple) {
          var newValue = this.internalValue.slice(0, index).concat(this.internalValue.slice(index + 1));
          this.$emit('input', newValue, this.id);
        } else {
          this.$emit('input', null, this.id);
        }
        this.$emit('remove', option, this.id);

        /* istanbul ignore else */
        if (this.closeOnSelect && shouldClose) this.deactivate();
      },
      /**
       * Calls this.removeElement() with the last element
       * from this.internalValue (selected element Array)
       *
       * @fires this#removeElement
       */
      removeLastElement: function removeLastElement() {
        /* istanbul ignore else */
        if (this.blockKeys.indexOf('Delete') !== -1) return;
        /* istanbul ignore else */
        if (this.search.length === 0 && Array.isArray(this.internalValue) && this.internalValue.length) {
          this.removeElement(this.internalValue[this.internalValue.length - 1], false);
        }
      },
      /**
       * Opens the multiselect’s dropdown.
       * Sets this.isOpen to TRUE
       */
      activate: function activate() {
        var _this5 = this;
        /* istanbul ignore else */
        if (this.isOpen || this.disabled) return;
        this.adjustPosition();
        /* istanbul ignore else  */
        if (this.groupValues && this.pointer === 0 && this.filteredOptions.length) {
          this.pointer = 1;
        }
        this.isOpen = true;
        /* istanbul ignore else  */
        if (this.searchable) {
          if (!this.preserveSearch) this.search = '';
          if (!this.preventAutofocus) this.$nextTick(function () {
            return _this5.$refs.search && _this5.$refs.search.focus();
          });
        } else if (!this.preventAutofocus) {
          if (typeof this.$el !== 'undefined') this.$el.focus();
        }
        this.$emit('open', this.id);
      },
      /**
       * Closes the multiselect’s dropdown.
       * Sets this.isOpen to FALSE
       */
      deactivate: function deactivate() {
        /* istanbul ignore else */
        if (!this.isOpen) return;
        this.isOpen = false;
        /* istanbul ignore else  */
        if (this.searchable) {
          if (typeof this.$refs.search !== 'undefined') this.$refs.search.blur();
        } else {
          if (typeof this.$el !== 'undefined') this.$el.blur();
        }
        if (!this.preserveSearch) this.search = '';
        this.$emit('close', this.getValue(), this.id);
      },
      /**
       * Call this.activate() or this.deactivate()
       * depending on this.isOpen value.
       *
       * @fires this#activate || this#deactivate
       * @property {Boolean} isOpen indicates if dropdown is open
       */
      toggle: function toggle() {
        this.isOpen ? this.deactivate() : this.activate();
      },
      /**
       * Updates the hasEnoughSpace variable used for
       * detecting where to expand the dropdown
       */
      adjustPosition: function adjustPosition() {
        if (typeof window === 'undefined') return;
        var spaceAbove = this.$el.getBoundingClientRect().top;
        var spaceBelow = window.innerHeight - this.$el.getBoundingClientRect().bottom;
        var hasEnoughSpaceBelow = spaceBelow > this.maxHeight;
        if (hasEnoughSpaceBelow || spaceBelow > spaceAbove || this.openDirection === 'below' || this.openDirection === 'bottom') {
          this.preferredOpenDirection = 'below';
          this.optimizedHeight = Math.min(spaceBelow - 40, this.maxHeight);
        } else {
          this.preferredOpenDirection = 'above';
          this.optimizedHeight = Math.min(spaceAbove - 40, this.maxHeight);
        }
      }
    }
  };

  var pointerMixin = {
    data: function data() {
      return {
        pointer: 0,
        pointerDirty: false
      };
    },
    props: {
      /**
       * Enable/disable highlighting of the pointed value.
       * @type {Boolean}
       * @default true
       */
      showPointer: {
        type: Boolean,
        "default": true
      },
      optionHeight: {
        type: Number,
        "default": 40
      }
    },
    computed: {
      pointerPosition: function pointerPosition() {
        return this.pointer * this.optionHeight;
      },
      visibleElements: function visibleElements() {
        return this.optimizedHeight / this.optionHeight;
      }
    },
    watch: {
      filteredOptions: function filteredOptions() {
        this.pointerAdjust();
      },
      isOpen: function isOpen() {
        this.pointerDirty = false;
      },
      pointer: function pointer() {
        this.$refs.search && this.$refs.search.setAttribute('aria-activedescendant', this.id + '-' + this.pointer.toString());
      }
    },
    methods: {
      optionHighlight: function optionHighlight(index, option) {
        return {
          'multiselect__option--highlight': index === this.pointer && this.showPointer,
          'multiselect__option--selected': this.isSelected(option)
        };
      },
      groupHighlight: function groupHighlight(index, selectedGroup) {
        var _this = this;
        if (!this.groupSelect) {
          return ['multiselect__option--disabled', {
            'multiselect__option--group': selectedGroup.$isLabel
          }];
        }
        var group = this.options.find(function (option) {
          return option[_this.groupLabel] === selectedGroup.$groupLabel;
        });
        return group && !this.wholeGroupDisabled(group) ? ['multiselect__option--group', {
          'multiselect__option--highlight': index === this.pointer && this.showPointer
        }, {
          'multiselect__option--group-selected': this.wholeGroupSelected(group)
        }] : 'multiselect__option--disabled';
      },
      addPointerElement: function addPointerElement() {
        var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'Enter',
          key = _ref.key;
        /* istanbul ignore else */
        if (this.filteredOptions.length > 0) {
          this.select(this.filteredOptions[this.pointer], key);
        }
        this.pointerReset();
      },
      pointerForward: function pointerForward() {
        /* istanbul ignore else */
        if (this.pointer < this.filteredOptions.length - 1) {
          this.pointer++;
          /* istanbul ignore next */
          if (this.$refs.list.scrollTop <= this.pointerPosition - (this.visibleElements - 1) * this.optionHeight) {
            this.$refs.list.scrollTop = this.pointerPosition - (this.visibleElements - 1) * this.optionHeight;
          }
          /* istanbul ignore else */
          if (this.filteredOptions[this.pointer] && this.filteredOptions[this.pointer].$isLabel && !this.groupSelect) this.pointerForward();
        }
        this.pointerDirty = true;
      },
      pointerBackward: function pointerBackward() {
        if (this.pointer > 0) {
          this.pointer--;
          /* istanbul ignore else */
          if (this.$refs.list.scrollTop >= this.pointerPosition) {
            this.$refs.list.scrollTop = this.pointerPosition;
          }
          /* istanbul ignore else */
          if (this.filteredOptions[this.pointer] && this.filteredOptions[this.pointer].$isLabel && !this.groupSelect) this.pointerBackward();
        } else {
          /* istanbul ignore else */
          if (this.filteredOptions[this.pointer] && this.filteredOptions[0].$isLabel && !this.groupSelect) this.pointerForward();
        }
        this.pointerDirty = true;
      },
      pointerReset: function pointerReset() {
        /* istanbul ignore else */
        if (!this.closeOnSelect) return;
        this.pointer = 0;
        /* istanbul ignore else */
        if (this.$refs.list) {
          this.$refs.list.scrollTop = 0;
        }
      },
      pointerAdjust: function pointerAdjust() {
        /* istanbul ignore else */
        if (this.pointer >= this.filteredOptions.length - 1) {
          this.pointer = this.filteredOptions.length ? this.filteredOptions.length - 1 : 0;
        }
        if (this.filteredOptions.length > 0 && this.filteredOptions[this.pointer].$isLabel && !this.groupSelect) {
          this.pointerForward();
        }
      },
      pointerSet: function pointerSet(index) {
        this.pointer = index;
        this.pointerDirty = true;
      }
    }
  };

  (function () {
    function registerMultiselect(app) {
      app.component('vue-multiselect', {
        mixins: [multiselectMixin, pointerMixin],
        props: {
          /**
           * name attribute to match optional label element
           * @default ''
           * @type {String}
           */
          name: {
            type: String,
            "default": ''
          },
          /**
           * String to show when pointing to an option
           * @default 'Press enter to select'
           * @type {String}
           */
          selectLabel: {
            type: String,
            "default": 'Press enter to select'
          },
          /**
           * String to show when pointing to an option
           * @default 'Press enter to select'
           * @type {String}
           */
          selectGroupLabel: {
            type: String,
            "default": 'Press enter to select group'
          },
          /**
           * String to show next to selected option
           * @default 'Selected'
           * @type {String}
           */
          selectedLabel: {
            type: String,
            "default": 'Selected'
          },
          /**
           * String to show when pointing to an already selected option
           * @default 'Press enter to remove'
           * @type {String}
           */
          deselectLabel: {
            type: String,
            "default": 'Press enter to remove'
          },
          /**
           * String to show when pointing to an already selected option
           * @default 'Press enter to remove'
           * @type {String}
           */
          deselectGroupLabel: {
            type: String,
            "default": 'Press enter to deselect group'
          },
          noElementsFoundText: {
            type: String,
            "default": 'No elements found. Consider changing the search query.'
          },
          listIsEmpty: {
            type: String,
            "default": 'List is empty.'
          },
          /**
           * Decide whether to show pointer labels
           * @default true
           * @type {Boolean}
           */
          showLabels: {
            type: Boolean,
            "default": true
          },
          /**
           * Limit the display of selected options. The rest will be hidden within the limitText string.
           * @default 99999
           * @type {Integer}
           */
          limit: {
            type: Number,
            "default": 99999
          },
          /**
           * Sets maxHeight style value of the dropdown
           * @default 300
           * @type {Integer}
           */
          maxHeight: {
            type: Number,
            "default": 300
          },
          /**
           * Function that process the message shown when selected
           * elements pass the defined limit.
           * @default 'and * more'
           * @param {Int} count Number of elements more than limit
           * @type {Function}
           */
          limitText: {
            type: Function,
            "default": function _default(count) {
              return "and ".concat(count, " more");
            }
          },
          /**
           * Set true to trigger the loading spinner.
           * @default False
           * @type {Boolean}
           */
          loading: {
            type: Boolean,
            "default": false
          },
          /**
           * Disables the multiselect if true.
           * @default false
           * @type {Boolean}
           */
          disabled: {
            type: Boolean,
            "default": false
          },
          /**
           * Fixed opening direction
           * @default ''
           * @type {String}
           */
          openDirection: {
            type: String,
            "default": ''
          },
          /**
           * Shows slot with message about empty options
           * @default true
           * @type {Boolean}
           */
          showNoOptions: {
            type: Boolean,
            "default": true
          },
          showNoResults: {
            type: Boolean,
            "default": true
          },
          tabindex: {
            type: Number,
            "default": 0
          }
        },
        computed: {
          hasOptionGroup: function hasOptionGroup() {
            return this.groupValues && this.groupLabel && this.groupSelect;
          },
          isSingleLabelVisible: function isSingleLabelVisible() {
            return (this.singleValue || this.singleValue === 0) && (!this.isOpen || !this.searchable) && !this.visibleValues.length;
          },
          isPlaceholderVisible: function isPlaceholderVisible() {
            return !this.internalValue.length && (!this.searchable || !this.isOpen);
          },
          visibleValues: function visibleValues() {
            return this.multiple ? this.internalValue.slice(0, this.limit) : [];
          },
          singleValue: function singleValue() {
            return this.internalValue[0];
          },
          deselectLabelText: function deselectLabelText() {
            return this.showLabels ? this.deselectLabel : '';
          },
          deselectGroupLabelText: function deselectGroupLabelText() {
            return this.showLabels ? this.deselectGroupLabel : '';
          },
          selectLabelText: function selectLabelText() {
            return this.showLabels ? this.selectLabel : '';
          },
          selectGroupLabelText: function selectGroupLabelText() {
            return this.showLabels ? this.selectGroupLabel : '';
          },
          selectedLabelText: function selectedLabelText() {
            return this.showLabels ? this.selectedLabel : '';
          },
          inputStyle: function inputStyle() {
            if (this.searchable || this.multiple && this.value && this.value.length) {
              // Hide input by setting the width to 0 allowing it to receive focus
              return this.isOpen ? {
                width: '100%'
              } : {
                width: '0',
                position: 'absolute',
                padding: '0'
              };
            }
            return '';
          },
          contentStyle: function contentStyle() {
            return this.options.length ? {
              display: 'inline-block'
            } : {
              display: 'block'
            };
          },
          isAbove: function isAbove() {
            if (this.openDirection === 'above' || this.openDirection === 'top') {
              return true;
            } else if (this.openDirection === 'below' || this.openDirection === 'bottom') {
              return false;
            } else {
              return this.preferredOpenDirection === 'above';
            }
          },
          showSearchInput: function showSearchInput() {
            return this.searchable && (this.hasSingleSelectedSlot && (this.visibleSingleValue || this.visibleSingleValue === 0) ? this.isOpen : true);
          }
        },
        data: function data() {
          return {
            elDropDown: false,
            timer: false
          };
        },
        mounted: function mounted() {
          this.bodyContainer = document.createElement('DIV');
          this.bodyContainer.classList.add('devbx-webform-clean-css');
          this.bodyContainer.classList.add('devbx-webform-theme');
          this.bodyContainer.classList.add('multiselect');
          this.bodyContainer.style.position = 'absolute';
          document.body.appendChild(this.bodyContainer);
          this.bodyContainer.appendChild(this.$refs.list);
        },
        beforeDestroy: function beforeDestroy() {
          if (this.timer) {
            clearInterval(this.timer);
          }
          this.bodyContainer.remove();
        },
        methods: {
          updatePosition: function updatePosition() {
            var elRect = this.$el.getBoundingClientRect();
            if (this.isAbove) {
              this.bodyContainer.style.position = 'absolute';
              this.$refs.list.style.display = 'block';
              var dropDownRect = this.$refs.list.getBoundingClientRect();
              this.bodyContainer.style.top = window.scrollY + elRect.y - dropDownRect.height + 'px';
              this.bodyContainer.style.left = window.scrollX + elRect.x + 'px';
              this.bodyContainer.style.width = elRect.width + 'px';
            } else {
              this.bodyContainer.style.position = 'absolute';
              this.bodyContainer.style.top = window.scrollY + elRect.y + elRect.height + 'px';
              this.bodyContainer.style.left = window.scrollX + elRect.x + 'px';
              this.bodyContainer.style.width = elRect.width + 'px';
            }
          }
        },
        watch: {
          'isOpen': function isOpen(val) {
            if (val) {
              this.bodyContainer.style.display = 'block';
              this.updatePosition();
              if (!this.timer) {
                this.timer = setInterval(BX.delegate(this.updatePosition, this), 100);
              }
            } else {
              this.bodyContainer.style.display = 'none';
              clearInterval(this.timer);
              this.timer = false;
            }
          }
        },
        template: "\n  <div\n    :tabindex=\"searchable ? -1 : tabindex\"\n    :class=\"{ 'multiselect--active': isOpen, 'multiselect--disabled': disabled, 'multiselect--above': isAbove, 'multiselect--has-options-group': hasOptionGroup }\"\n    @focus=\"activate()\"\n    @blur=\"searchable ? false : deactivate()\"\n    @keydown.self.down.prevent=\"pointerForward()\"\n    @keydown.self.up.prevent=\"pointerBackward()\"\n    @keypress.enter.tab.stop.self=\"addPointerElement($event)\"\n    @keyup.esc=\"deactivate()\"\n    class=\"multiselect\"\n    role=\"combobox\"\n    :aria-owns=\"'listbox-'+id\">\n      <slot name=\"caret\" :toggle=\"toggle\">\n        <div @mousedown.prevent.stop=\"toggle()\" class=\"multiselect__select\"></div>\n      </slot>\n      <slot name=\"clear\" :search=\"search\"></slot>\n      <div ref=\"tags\" class=\"multiselect__tags\">\n        <slot\n          name=\"selection\"\n          :search=\"search\"\n          :remove=\"removeElement\"\n          :values=\"visibleValues\"\n          :is-open=\"isOpen\"\n        >\n          <div class=\"multiselect__tags-wrap\" v-show=\"visibleValues.length > 0\">\n            <template v-for=\"(option, index) of visibleValues\" @mousedown.prevent>\n              <slot name=\"tag\" :option=\"option\" :search=\"search\" :remove=\"removeElement\">\n                <span class=\"multiselect__tag\" :key=\"index\">\n                  <span v-text=\"getOptionLabel(option)\"></span>\n                  <i tabindex=\"1\" @keypress.enter.prevent=\"removeElement(option)\"  @mousedown.prevent=\"removeElement(option)\" class=\"multiselect__tag-icon\"></i>\n                </span>\n              </slot>\n            </template>\n          </div>\n          <template v-if=\"internalValue && internalValue.length > limit\">\n            <slot name=\"limit\">\n              <strong class=\"multiselect__strong\" v-text=\"limitText(internalValue.length - limit)\"/>\n            </slot>\n          </template>\n        </slot>\n        <transition name=\"multiselect__loading\">\n          <slot name=\"loading\">\n            <div v-show=\"loading\" class=\"multiselect__spinner\"/>\n          </slot>\n        </transition>\n        <input\n          ref=\"search\"\n          v-if=\"searchable\"\n          :name=\"name\"\n          :id=\"id\"\n          type=\"text\"\n          autocomplete=\"off\"\n          spellcheck=\"false\"\n          :placeholder=\"placeholder\"\n          :style=\"inputStyle\"\n          :value=\"search\"\n          :disabled=\"disabled\"\n          :tabindex=\"tabindex\"\n          @input=\"updateSearch($event.target.value)\"\n          @focus.prevent=\"activate()\"\n          @blur.prevent=\"deactivate()\"\n          @keyup.esc=\"deactivate()\"\n          @keydown.down.prevent=\"pointerForward()\"\n          @keydown.up.prevent=\"pointerBackward()\"\n          @keypress.enter.prevent.stop.self=\"addPointerElement($event)\"\n          @keydown.delete.stop=\"removeLastElement()\"\n          class=\"multiselect__input\"\n          :aria-controls=\"'listbox-'+id\"\n        />\n        <span\n          v-if=\"isSingleLabelVisible\"\n          class=\"multiselect__single\"\n          @mousedown.prevent=\"toggle\"\n        >\n          <slot name=\"singleLabel\" :option=\"singleValue\">\n            <template>{{ currentOptionLabel }}</template>\n          </slot>\n        </span>\n        <span\n          v-if=\"isPlaceholderVisible\"\n          class=\"multiselect__placeholder\"\n          @mousedown.prevent=\"toggle\"\n        >\n          <slot name=\"placeholder\">\n            {{ placeholder }}\n          </slot>\n        </span>\n      </div>\n      <transition name=\"multiselect\">\n        <div\n          class=\"multiselect__content-wrapper\"\n          v-show=\"isOpen\"\n          @focus=\"activate\"\n          tabindex=\"-1\"\n          @mousedown.prevent\n          :style=\"{ maxHeight: optimizedHeight + 'px' }\"\n          ref=\"list\"\n        >\n          <ul class=\"multiselect__content\" :style=\"contentStyle\" role=\"listbox\" :id=\"'listbox-'+id\">\n            <slot name=\"beforeList\"></slot>\n            <li v-if=\"multiple && max === internalValue.length\">\n              <span class=\"multiselect__option\">\n                <slot name=\"maxElements\">Maximum of {{ max }} options selected. First remove a selected option to select another.</slot>\n              </span>\n            </li>\n            <template v-if=\"!max || internalValue.length < max\">\n              <li class=\"multiselect__element\"\n                v-for=\"(option, index) of filteredOptions\"\n                :key=\"index\"\n                v-bind:id=\"id + '-' + index\"\n                v-bind:role=\"!(option && (option.$isLabel || option.$isDisabled)) ? 'option' : null\">\n                <span\n                  v-if=\"!(option && (option.$isLabel || option.$isDisabled))\"\n                  :class=\"optionHighlight(index, option)\"\n                  @click.stop=\"select(option)\"\n                  @mouseenter.self=\"pointerSet(index)\"\n                  :data-select=\"option && option.isTag ? tagPlaceholder : selectLabelText\"\n                  :data-selected=\"selectedLabelText\"\n                  :data-deselect=\"deselectLabelText\"\n                  class=\"multiselect__option\">\n                    <slot name=\"option\" :option=\"option\" :search=\"search\" :index=\"index\">\n                      <span>{{ getOptionLabel(option) }}</span>\n                    </slot>\n                </span>\n                <span\n                  v-if=\"option && (option.$isLabel || option.$isDisabled)\"\n                  :data-select=\"groupSelect && selectGroupLabelText\"\n                  :data-deselect=\"groupSelect && deselectGroupLabelText\"\n                  :class=\"groupHighlight(index, option)\"\n                  @mouseenter.self=\"groupSelect && pointerSet(index)\"\n                  @mousedown.prevent=\"selectGroup(option)\"\n                  class=\"multiselect__option\">\n                    <slot name=\"option\" :option=\"option\" :search=\"search\" :index=\"index\">\n                      <span>{{ getOptionLabel(option) }}</span>\n                    </slot>\n                </span>\n              </li>\n            </template>\n            <li v-show=\"showNoResults && (filteredOptions.length === 0 && search && !loading)\">\n              <span class=\"multiselect__option\">\n                <slot name=\"noResult\" :search=\"search\">{{noElementsFoundText}}</slot>\n              </span>\n            </li>\n            <li v-show=\"showNoOptions && ((options.length === 0 || (hasOptionGroup === true && filteredOptions.length === 0)) && !search && !loading)\">\n              <span class=\"multiselect__option\">\n                <slot name=\"noOptions\">{{listIsEmpty}}</slot>\n              </span>\n            </li>\n            <slot name=\"afterList\"></slot>\n          </ul>\n        </div>\n      </transition>\n  </div>\n  "
      });
    }
    BX.addCustomEvent("DevBxWebFormCreated", registerMultiselect);
    BX.addCustomEvent("DevBxWebFormCreatedAdminMaster", registerMultiselect);
  })();

}((this.DevBX.Forms.WebForm = this.DevBX.Forms.WebForm || {})));
//# sourceMappingURL=vue-multiselect.js.map

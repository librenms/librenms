(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["/js/app"],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Accordion.vue?vue&type=script&lang=js&":
/*!********************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/Accordion.vue?vue&type=script&lang=js& ***!
  \********************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/* harmony default export */ __webpack_exports__["default"] = ({
  name: "Accordion",
  props: {
    multiple: {
      type: Boolean,
      "default": false
    }
  },
  methods: {
    setActive: function setActive(name) {
      this.$children.forEach(function (item) {
        if (item.slug() === name) {
          item.isActive = true;
        }
      });
    },
    activeChanged: function activeChanged(name) {
      if (!this.multiple) {
        this.$children.forEach(function (item) {
          if (item.slug() !== name) {
            item.isActive = false;
          }
        });
      }
    }
  },
  mounted: function mounted() {
    this.$on('expanded', this.activeChanged);
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/AccordionItem.vue?vue&type=script&lang=js&":
/*!************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/AccordionItem.vue?vue&type=script&lang=js& ***!
  \************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/* harmony default export */ __webpack_exports__["default"] = ({
  name: "AccordionItem",
  props: {
    name: {
      type: String,
      required: true
    },
    text: String,
    active: Boolean,
    icon: String
  },
  data: function data() {
    return {
      isActive: this.active
    };
  },
  mounted: function mounted() {
    if (window.location.hash === this.hash()) {
      this.isActive = true;
    }
  },
  watch: {
    active: function active(_active) {
      this.isActive = _active;
    },
    isActive: function isActive(active) {
      this.$parent.$emit(active ? 'expanded' : 'collapsed', this.slug());
    }
  },
  methods: {
    slug: function slug() {
      return this.name.toString().toLowerCase().replace(/\s+/g, '-');
    },
    hash: function hash() {
      return '#' + this.slug();
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/BaseSetting.vue?vue&type=script&lang=js&":
/*!**********************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/BaseSetting.vue?vue&type=script&lang=js& ***!
  \**********************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/* harmony default export */ __webpack_exports__["default"] = ({
  name: "BaseSetting",
  props: {
    name: {
      type: String,
      required: true
    },
    value: {
      required: true
    },
    disabled: Boolean,
    required: Boolean,
    pattern: String,
    options: {}
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/ExampleComponent.vue?vue&type=script&lang=js&":
/*!***************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/ExampleComponent.vue?vue&type=script&lang=js& ***!
  \***************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/* harmony default export */ __webpack_exports__["default"] = ({
  mounted: function mounted() {
    console.log('Component mounted.');
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/LibrenmsSetting.vue?vue&type=script&lang=js&":
/*!**************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/LibrenmsSetting.vue?vue&type=script&lang=js& ***!
  \**************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/* harmony default export */ __webpack_exports__["default"] = ({
  name: "LibrenmsSetting",
  props: {
    'setting': {
      type: Object,
      required: true
    }
  },
  data: function data() {
    return {
      value: this.setting.value,
      feedback: ''
    };
  },
  methods: {
    persistValue: function persistValue(value) {
      var _this = this;

      axios.put(route('settings.update', this.setting.name), {
        value: value
      }).then(function (response) {
        _this.value = response.data.value;

        _this.$emit('setting-updated', {
          name: _this.setting.name,
          value: _this.value
        });

        _this.feedback = 'has-success';
        setTimeout(function () {
          return _this.feedback = '';
        }, 3000);
      })["catch"](function (error) {
        _this.value = error.response.data.value;

        _this.$emit('setting-updated', {
          name: _this.setting.name,
          value: _this.value
        });

        _this.feedback = 'has-error';
        setTimeout(function () {
          return _this.feedback = '';
        }, 3000);
        toastr.error(error.response.data.message);
      });
    },
    debouncePersistValue: _.debounce(function (value) {
      this.persistValue(value);
    }, 500),
    changeValue: function changeValue(value) {
      if (['select', 'boolean'].includes(this.setting.type)) {
        // no need to debounce
        this.persistValue(value);
      } else {
        this.debouncePersistValue(value);
      }

      this.value = value;
    },
    getDescription: function getDescription() {
      var key = 'settings.settings.' + this.setting.name + '.description';
      return this.$te(key) || this.$te(key, this.$i18n.fallbackLocale) ? this.$t(key) : this.setting.name;
    },
    getHelp: function getHelp() {
      var help = this.$t('settings.settings.' + this.setting.name + '.help');

      if (this.setting.overridden) {
        help += "</p><p>" + this.$t('settings.readonly');
      }

      return help;
    },
    hasHelp: function hasHelp() {
      var key = 'settings.settings.' + this.setting.name + '.help';
      return this.$te(key) || this.$te(key, this.$i18n.fallbackLocale);
    },
    resetToDefault: function resetToDefault() {
      var _this2 = this;

      axios["delete"](route('settings.destroy', this.setting.name)).then(function (response) {
        _this2.value = response.data.value;
        _this2.feedback = 'has-success';
        setTimeout(function () {
          return _this2.feedback = '';
        }, 3000);
      })["catch"](function (error) {
        _this2.feedback = 'has-error';
        setTimeout(function () {
          return _this2.feedback = '';
        }, 3000);
        toastr.error(error.response.data.message);
      });
    },
    resetToInitial: function resetToInitial() {
      this.changeValue(this.setting.value);
    },
    showResetToDefault: function showResetToDefault() {
      return this.setting["default"] !== null && !this.setting.overridden && !_.isEqual(this.value, this.setting["default"]);
    },
    showUndo: function showUndo() {
      return !_.isEqual(this.setting.value, this.value);
    },
    getComponent: function getComponent() {
      // snake to studly
      var component = 'Setting' + this.setting.type.toString().replace(/(-[a-z]|^[a-z])/g, function (group) {
        return group.toUpperCase().replace('-', '');
      });
      return typeof Vue.options.components[component] !== 'undefined' ? component : 'SettingNull';
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/LibrenmsSettings.vue?vue&type=script&lang=js&":
/*!***************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/LibrenmsSettings.vue?vue&type=script&lang=js& ***!
  \***************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance"); }

function _iterableToArrayLimit(arr, i) { if (!(Symbol.iterator in Object(arr) || Object.prototype.toString.call(arr) === "[object Arguments]")) { return; } var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/* harmony default export */ __webpack_exports__["default"] = ({
  name: "LibrenmsSettings",
  props: {
    prefix: String,
    initialTab: {
      type: String,
      "default": 'alerting'
    },
    initialSection: String
  },
  data: function data() {
    return {
      tab: this.initialTab,
      section: this.initialSection,
      search_phrase: '',
      settings: {}
    };
  },
  methods: {
    tabChanged: function tabChanged(group) {
      if (this.tab !== group) {
        this.tab = group;
        this.section = null;
        this.updateUrl();
      }
    },
    sectionExpanded: function sectionExpanded(section) {
      this.section = section;
      this.updateUrl();
    },
    sectionCollapsed: function sectionCollapsed(section) {
      if (this.section === section) {
        // don't do anything if section was changed instead of just closed
        this.section = null;
        this.updateUrl();
      }
    },
    updateUrl: function updateUrl() {
      var slug = this.tab;

      if (this.section) {
        slug += '/' + this.section;
      }

      window.history.pushState(slug, '', this.prefix + '/' + slug);
    },
    handleBack: function handleBack(event) {
      var _event$state$split = event.state.split('/');

      var _event$state$split2 = _slicedToArray(_event$state$split, 2);

      this.tab = _event$state$split2[0];
      this.section = _event$state$split2[1];
    },
    loadData: function loadData(response) {
      this.settings = response.data;
    },
    updateSetting: function updateSetting(name, value) {
      this.$set(this.settings[name], 'value', value);
    },
    settingShown: function settingShown(setting_name) {
      var setting = this.settings[setting_name];

      if (setting.when === null) {
        return true;
      }

      switch (setting.when.operator) {
        case 'equals':
          return this.settings[setting.when.setting].value === setting.when.value;

        case 'in':
          return setting.when.value.includes(this.settings[setting.when.setting].value);

        default:
          return true;
      }
    }
  },
  mounted: function mounted() {
    var _this = this;

    window.onpopstate = this.handleBack; // handle back button

    axios.get(route('settings.list')).then(function (response) {
      return _this.settings = response.data;
    });
  },
  computed: {
    groups: function groups() {
      // populate layout data
      var groups = {};

      for (var _i2 = 0, _Object$keys = Object.keys(this.settings); _i2 < _Object$keys.length; _i2++) {
        var key = _Object$keys[_i2];
        var setting = this.settings[key]; // filter

        if (!setting.name.includes(this.search_phrase)) {
          continue;
        }

        if (setting.group) {
          if (!(setting.group in groups)) {
            groups[setting.group] = {};
          }

          if (setting.section) {
            if (!(setting.section in groups[setting.group])) {
              groups[setting.group][setting.section] = [];
            } // insert based on order


            groups[setting.group][setting.section].splice(setting.order, 0, setting.name);
          }
        }
      } // sort groups


      return Object.keys(groups).sort().reduce(function (a, c) {
        return a[c] = groups[c], a;
      }, {});
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingArray.vue?vue&type=script&lang=js&":
/*!***********************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingArray.vue?vue&type=script&lang=js& ***!
  \***********************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _BaseSetting__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./BaseSetting */ "./resources/js/components/BaseSetting.vue");
/* harmony import */ var vuedraggable__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! vuedraggable */ "./node_modules/vuedraggable/dist/vuedraggable.common.js");
/* harmony import */ var vuedraggable__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(vuedraggable__WEBPACK_IMPORTED_MODULE_1__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//


/* harmony default export */ __webpack_exports__["default"] = ({
  name: "SettingArray",
  mixins: [_BaseSetting__WEBPACK_IMPORTED_MODULE_0__["default"]],
  components: {
    draggable: vuedraggable__WEBPACK_IMPORTED_MODULE_1___default.a
  },
  data: function data() {
    return {
      localList: this.value,
      newItem: ""
    };
  },
  methods: {
    addItem: function addItem() {
      this.localList.push(this.newItem);
      this.$emit('input', this.localList);
      this.newItem = "";
    },
    removeItem: function removeItem(index) {
      this.localList.splice(index, 1);
      this.$emit('input', this.localList);
    },
    updateItem: function updateItem(index, value) {
      this.localList[index] = value;
      this.$emit('input', this.localList);
    },
    dragged: function dragged() {
      this.$emit('input', this.localList);
    }
  },
  watch: {
    value: function value(updated) {
      // careful to avoid loops with this
      this.localList = updated;
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingBoolean.vue?vue&type=script&lang=js&":
/*!*************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingBoolean.vue?vue&type=script&lang=js& ***!
  \*************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _BaseSetting__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./BaseSetting */ "./resources/js/components/BaseSetting.vue");
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  name: "SettingBoolean",
  mixins: [_BaseSetting__WEBPACK_IMPORTED_MODULE_0__["default"]]
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingDashboardSelect.vue?vue&type=script&lang=js&":
/*!*********************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingDashboardSelect.vue?vue&type=script&lang=js& ***!
  \*********************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _BaseSetting__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./BaseSetting */ "./resources/js/components/BaseSetting.vue");
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  name: "SettingDashboardSelect",
  mixins: [_BaseSetting__WEBPACK_IMPORTED_MODULE_0__["default"]],
  data: function data() {
    return {
      ajaxData: {
        results: []
      },
      "default": {
        id: 0,
        text: this.$t('No Default Dashboard')
      }
    };
  },
  mounted: function mounted() {
    var _this = this;

    axios.get(route('ajax.select.dashboard')).then(function (response) {
      return _this.ajaxData = response.data;
    });
  },
  computed: {
    localOptions: function localOptions() {
      return [this["default"]].concat(this.ajaxData.results);
    },
    selected: function selected() {
      var _this2 = this;

      return this.value === 0 ? this["default"] : this.ajaxData.results.find(function (dash) {
        return dash.id === _this2.value;
      });
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingEmail.vue?vue&type=script&lang=js&":
/*!***********************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingEmail.vue?vue&type=script&lang=js& ***!
  \***********************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _BaseSetting__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./BaseSetting */ "./resources/js/components/BaseSetting.vue");
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  name: "SettingEmail",
  mixins: [_BaseSetting__WEBPACK_IMPORTED_MODULE_0__["default"]]
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingInteger.vue?vue&type=script&lang=js&":
/*!*************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingInteger.vue?vue&type=script&lang=js& ***!
  \*************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _BaseSetting__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./BaseSetting */ "./resources/js/components/BaseSetting.vue");
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  name: "SettingInteger",
  mixins: [_BaseSetting__WEBPACK_IMPORTED_MODULE_0__["default"]],
  methods: {
    parseNumber: function parseNumber(number) {
      var value = parseFloat(number);
      return isNaN(value) ? number : value;
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingLdapGroups.vue?vue&type=script&lang=js&":
/*!****************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingLdapGroups.vue?vue&type=script&lang=js& ***!
  \****************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _BaseSetting__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./BaseSetting */ "./resources/js/components/BaseSetting.vue");
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  name: "SettingLdapGroups",
  mixins: [_BaseSetting__WEBPACK_IMPORTED_MODULE_0__["default"]],
  data: function data() {
    return {
      localList: this.value,
      newItem: "",
      newItemLevel: 1
    };
  },
  methods: {
    addItem: function addItem() {
      this.$set(this.localList, this.newItem, {
        level: this.newItemLevel
      });
      this.newItem = "";
      this.newItemLevel = 1;
    },
    removeItem: function removeItem(index) {
      this.$delete(this.localList, index);
    },
    updateItem: function updateItem(oldValue, newValue) {
      var _this = this;

      this.localList = Object.keys(this.localList).reduce(function (newList, current) {
        var key = current === oldValue ? newValue : current;
        newList[key] = _this.localList[current];
        return newList;
      }, {});
    },
    updateLevel: function updateLevel(group, level) {
      this.$set(this.localList, group, {
        level: level
      });
    }
  },
  watch: {
    localList: function localList() {
      this.$emit('input', this.localList);
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingNull.vue?vue&type=script&lang=js&":
/*!**********************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingNull.vue?vue&type=script&lang=js& ***!
  \**********************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/* harmony default export */ __webpack_exports__["default"] = ({
  name: "SettingNull",
  props: ['name']
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingPassword.vue?vue&type=script&lang=js&":
/*!**************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingPassword.vue?vue&type=script&lang=js& ***!
  \**************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _BaseSetting__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./BaseSetting */ "./resources/js/components/BaseSetting.vue");
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  name: "SettingPassword",
  mixins: [_BaseSetting__WEBPACK_IMPORTED_MODULE_0__["default"]]
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingSelect.vue?vue&type=script&lang=js&":
/*!************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingSelect.vue?vue&type=script&lang=js& ***!
  \************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _BaseSetting__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./BaseSetting */ "./resources/js/components/BaseSetting.vue");
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  name: "SettingSelect",
  mixins: [_BaseSetting__WEBPACK_IMPORTED_MODULE_0__["default"]],
  methods: {
    getText: function getText(name, text) {
      var key = "settings.settings.".concat(name, ".options.").concat(text);
      return this.$te(key) ? this.$t(key) : text;
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingSnmp3auth.vue?vue&type=script&lang=js&":
/*!***************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingSnmp3auth.vue?vue&type=script&lang=js& ***!
  \***************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _BaseSetting__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./BaseSetting */ "./resources/js/components/BaseSetting.vue");
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  name: "SettingSnmp3auth",
  mixins: [_BaseSetting__WEBPACK_IMPORTED_MODULE_0__["default"]],
  data: function data() {
    return {
      localList: this.value
    };
  },
  methods: {
    addItem: function addItem() {
      this.localList.push({
        authlevel: 'noAuthNoPriv',
        authalgo: 'MD5',
        authname: '',
        authpass: '',
        cryptoalgo: 'AES',
        cryptopass: ''
      });
      this.$emit('input', this.localList);
    },
    removeItem: function removeItem(index) {
      this.localList.splice(index, 1);
      this.$emit('input', this.localList);
    },
    updateItem: function updateItem(index, key, value) {
      this.localList[index][key] = value;
      this.$emit('input', this.localList);
    },
    dragged: function dragged() {
      this.$emit('input', this.localList);
    }
  },
  watch: {
    value: function value($value) {
      this.localList = $value;
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingText.vue?vue&type=script&lang=js&":
/*!**********************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingText.vue?vue&type=script&lang=js& ***!
  \**********************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _BaseSetting__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./BaseSetting */ "./resources/js/components/BaseSetting.vue");
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  name: "SettingText",
  mixins: [_BaseSetting__WEBPACK_IMPORTED_MODULE_0__["default"]]
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Tab.vue?vue&type=script&lang=js&":
/*!**************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/Tab.vue?vue&type=script&lang=js& ***!
  \**************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/* harmony default export */ __webpack_exports__["default"] = ({
  name: "Tab",
  props: {
    name: {
      required: true
    },
    text: String,
    selected: {
      type: Boolean,
      "default": false
    },
    icon: String
  },
  data: function data() {
    return {
      isActive: this.selected
    };
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Tabs.vue?vue&type=script&lang=js&":
/*!***************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/Tabs.vue?vue&type=script&lang=js& ***!
  \***************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/* harmony default export */ __webpack_exports__["default"] = ({
  name: "Tabs",
  props: {
    selected: String
  },
  data: function data() {
    return {
      tabs: [],
      activeTab: null
    };
  },
  created: function created() {
    this.tabs = this.$children;
  },
  mounted: function mounted() {
    this.activeTab = this.selected;
  },
  watch: {
    selected: function selected(name) {
      this.activeTab = name;
    },
    activeTab: function activeTab(name) {
      this.tabs.forEach(function (tab) {
        return tab.isActive = tab.name === name;
      });
      this.$emit('tab-selected', name);
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/TransitionCollapseHeight.vue?vue&type=script&lang=js&":
/*!***********************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/TransitionCollapseHeight.vue?vue&type=script&lang=js& ***!
  \***********************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/* harmony default export */ __webpack_exports__["default"] = ({
  name: "TransitionCollapseHeight",
  methods: {
    beforeEnter: function beforeEnter(el) {
      requestAnimationFrame(function () {
        if (!el.style.height) {
          el.style.height = '0px';
        }

        el.style.display = null;
      });
    },
    enter: function enter(el) {
      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          el.style.height = el.scrollHeight + 'px';
        });
      });
    },
    afterEnter: function afterEnter(el) {
      el.style.height = null;
    },
    beforeLeave: function beforeLeave(el) {
      requestAnimationFrame(function () {
        if (!el.style.height) {
          el.style.height = el.offsetHeight + 'px';
        }
      });
    },
    leave: function leave(el) {
      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          el.style.height = '0px';
        });
      });
    },
    afterLeave: function afterLeave(el) {
      el.style.height = null;
    }
  }
});

/***/ }),

/***/ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/AccordionItem.vue?vue&type=style&index=0&id=bf6d92c0&scoped=true&lang=css&":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/AccordionItem.vue?vue&type=style&index=0&id=bf6d92c0&scoped=true&lang=css& ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(/*! ../../../node_modules/css-loader/lib/css-base.js */ "./node_modules/css-loader/lib/css-base.js")(false);
// imports


// module
exports.push([module.i, "\n.accordion-item-trigger-icon[data-v-bf6d92c0] {\n    transition: transform 0.2s ease;\n}\n.accordion-item-trigger.collapsed .accordion-item-trigger-icon[data-v-bf6d92c0] {\n    transform: rotate(-90deg);\n}\n", ""]);

// exports


/***/ }),

/***/ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/LibrenmsSettings.vue?vue&type=style&index=0&id=d702796c&scoped=true&lang=css&":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/LibrenmsSettings.vue?vue&type=style&index=0&id=d702796c&scoped=true&lang=css& ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(/*! ../../../node_modules/css-loader/lib/css-base.js */ "./node_modules/css-loader/lib/css-base.js")(false);
// imports


// module
exports.push([module.i, "\n#settings-search[data-v-d702796c] {\n    border-radius: 4px\n}\n#settings-search[data-v-d702796c]::-webkit-search-cancel-button {\n    -webkit-appearance: searchfield-cancel-button;\n}\n", ""]);

// exports


/***/ }),

/***/ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingArray.vue?vue&type=style&index=0&id=2cf33d54&scoped=true&lang=css&":
/*!******************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingArray.vue?vue&type=style&index=0&id=2cf33d54&scoped=true&lang=css& ***!
  \******************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(/*! ../../../node_modules/css-loader/lib/css-base.js */ "./node_modules/css-loader/lib/css-base.js")(false);
// imports


// module
exports.push([module.i, "\n.input-group[data-v-2cf33d54] {\n    margin-bottom: 3px;\n}\n.input-group-addon[data-v-2cf33d54]:not(.disabled) {\n    cursor: move;\n}\n", ""]);

// exports


/***/ }),

/***/ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingLdapGroups.vue?vue&type=style&index=0&id=67b77e70&scoped=true&lang=css&":
/*!***********************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingLdapGroups.vue?vue&type=style&index=0&id=67b77e70&scoped=true&lang=css& ***!
  \***********************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(/*! ../../../node_modules/css-loader/lib/css-base.js */ "./node_modules/css-loader/lib/css-base.js")(false);
// imports


// module
exports.push([module.i, "\n.input-group[data-v-67b77e70] {\n    padding-bottom: 3px;\n}\n", ""]);

// exports


/***/ }),

/***/ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingNull.vue?vue&type=style&index=0&id=37a8c75c&scoped=true&lang=css&":
/*!*****************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingNull.vue?vue&type=style&index=0&id=37a8c75c&scoped=true&lang=css& ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(/*! ../../../node_modules/css-loader/lib/css-base.js */ "./node_modules/css-loader/lib/css-base.js")(false);
// imports


// module
exports.push([module.i, "\ndiv[data-v-37a8c75c] {\n    color: red;\n}\n", ""]);

// exports


/***/ }),

/***/ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingSnmp3auth.vue?vue&type=style&index=0&id=4a841d50&scoped=true&lang=css&":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingSnmp3auth.vue?vue&type=style&index=0&id=4a841d50&scoped=true&lang=css& ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(/*! ../../../node_modules/css-loader/lib/css-base.js */ "./node_modules/css-loader/lib/css-base.js")(false);
// imports


// module
exports.push([module.i, "\n.authlevel[data-v-4a841d50] {\n    font-size: 18px;\n    text-align: left;\n}\n.fa-minus-circle[data-v-4a841d50] {\n    cursor: pointer;\n}\n.snmp3-add-button[data-v-4a841d50] {\n    margin-top: 5px;\n}\n", ""]);

// exports


/***/ }),

/***/ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Tabs.vue?vue&type=style&index=0&id=6e9bbb69&scoped=true&lang=css&":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/Tabs.vue?vue&type=style&index=0&id=6e9bbb69&scoped=true&lang=css& ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(/*! ../../../node_modules/css-loader/lib/css-base.js */ "./node_modules/css-loader/lib/css-base.js")(false);
// imports


// module
exports.push([module.i, "\n.panel.with-nav-tabs .panel-heading[data-v-6e9bbb69]{\n    padding: 5px 5px 0 5px;\n}\n.panel.with-nav-tabs .nav-tabs[data-v-6e9bbb69]{\n    border-bottom: none;\n}\n.panel.with-nav-tabs .nav-justified[data-v-6e9bbb69]{\n    margin-bottom: -1px;\n}\n.with-nav-tabs.panel-default .nav-tabs > li > a[data-v-6e9bbb69],\n.with-nav-tabs.panel-default .nav-tabs > li > a[data-v-6e9bbb69]:hover,\n.with-nav-tabs.panel-default .nav-tabs > li > a[data-v-6e9bbb69]:focus {\n    color: #777;\n}\n.with-nav-tabs.panel-default .nav-tabs > .open > a[data-v-6e9bbb69],\n.with-nav-tabs.panel-default .nav-tabs > .open > a[data-v-6e9bbb69]:hover,\n.with-nav-tabs.panel-default .nav-tabs > .open > a[data-v-6e9bbb69]:focus,\n.with-nav-tabs.panel-default .nav-tabs > li > a[data-v-6e9bbb69]:hover,\n.with-nav-tabs.panel-default .nav-tabs > li > a[data-v-6e9bbb69]:focus {\n    color: #777;\n    background-color: #ddd;\n    border-color: transparent;\n}\n.with-nav-tabs.panel-default .nav-tabs > li.active > a[data-v-6e9bbb69],\n.with-nav-tabs.panel-default .nav-tabs > li.active > a[data-v-6e9bbb69]:hover,\n.with-nav-tabs.panel-default .nav-tabs > li.active > a[data-v-6e9bbb69]:focus {\n    color: #555;\n    background-color: #fff;\n    border-color: #ddd;\n    border-bottom-color: transparent;\n}\n.with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu[data-v-6e9bbb69] {\n    background-color: #f5f5f5;\n    border-color: #ddd;\n}\n.with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu > li > a[data-v-6e9bbb69] {\n    color: #777;\n}\n.with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu > li > a[data-v-6e9bbb69]:hover,\n.with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu > li > a[data-v-6e9bbb69]:focus {\n    background-color: #ddd;\n}\n.with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu > .active > a[data-v-6e9bbb69],\n.with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu > .active > a[data-v-6e9bbb69]:hover,\n.with-nav-tabs.panel-default .nav-tabs > li.dropdown .dropdown-menu > .active > a[data-v-6e9bbb69]:focus {\n    color: #fff;\n    background-color: #555;\n}\n", ""]);

// exports


/***/ }),

/***/ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/TransitionCollapseHeight.vue?vue&type=style&index=0&id=41d51ed4&scoped=true&lang=css&":
/*!******************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/TransitionCollapseHeight.vue?vue&type=style&index=0&id=41d51ed4&scoped=true&lang=css& ***!
  \******************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(/*! ../../../node_modules/css-loader/lib/css-base.js */ "./node_modules/css-loader/lib/css-base.js")(false);
// imports


// module
exports.push([module.i, "\n.enter-active[data-v-41d51ed4],\n.leave-active[data-v-41d51ed4] {\n    overflow: hidden;\n    transition: height 0.2s linear;\n}\n", ""]);

// exports


/***/ }),

/***/ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/AccordionItem.vue?vue&type=style&index=0&id=bf6d92c0&scoped=true&lang=css&":
/*!***********************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader!./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/AccordionItem.vue?vue&type=style&index=0&id=bf6d92c0&scoped=true&lang=css& ***!
  \***********************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {


var content = __webpack_require__(/*! !../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./AccordionItem.vue?vue&type=style&index=0&id=bf6d92c0&scoped=true&lang=css& */ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/AccordionItem.vue?vue&type=style&index=0&id=bf6d92c0&scoped=true&lang=css&");

if(typeof content === 'string') content = [[module.i, content, '']];

var transform;
var insertInto;



var options = {"hmr":true}

options.transform = transform
options.insertInto = undefined;

var update = __webpack_require__(/*! ../../../node_modules/style-loader/lib/addStyles.js */ "./node_modules/style-loader/lib/addStyles.js")(content, options);

if(content.locals) module.exports = content.locals;

if(false) {}

/***/ }),

/***/ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/LibrenmsSettings.vue?vue&type=style&index=0&id=d702796c&scoped=true&lang=css&":
/*!**************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader!./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/LibrenmsSettings.vue?vue&type=style&index=0&id=d702796c&scoped=true&lang=css& ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {


var content = __webpack_require__(/*! !../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./LibrenmsSettings.vue?vue&type=style&index=0&id=d702796c&scoped=true&lang=css& */ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/LibrenmsSettings.vue?vue&type=style&index=0&id=d702796c&scoped=true&lang=css&");

if(typeof content === 'string') content = [[module.i, content, '']];

var transform;
var insertInto;



var options = {"hmr":true}

options.transform = transform
options.insertInto = undefined;

var update = __webpack_require__(/*! ../../../node_modules/style-loader/lib/addStyles.js */ "./node_modules/style-loader/lib/addStyles.js")(content, options);

if(content.locals) module.exports = content.locals;

if(false) {}

/***/ }),

/***/ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingArray.vue?vue&type=style&index=0&id=2cf33d54&scoped=true&lang=css&":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader!./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingArray.vue?vue&type=style&index=0&id=2cf33d54&scoped=true&lang=css& ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {


var content = __webpack_require__(/*! !../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingArray.vue?vue&type=style&index=0&id=2cf33d54&scoped=true&lang=css& */ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingArray.vue?vue&type=style&index=0&id=2cf33d54&scoped=true&lang=css&");

if(typeof content === 'string') content = [[module.i, content, '']];

var transform;
var insertInto;



var options = {"hmr":true}

options.transform = transform
options.insertInto = undefined;

var update = __webpack_require__(/*! ../../../node_modules/style-loader/lib/addStyles.js */ "./node_modules/style-loader/lib/addStyles.js")(content, options);

if(content.locals) module.exports = content.locals;

if(false) {}

/***/ }),

/***/ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingLdapGroups.vue?vue&type=style&index=0&id=67b77e70&scoped=true&lang=css&":
/*!***************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader!./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingLdapGroups.vue?vue&type=style&index=0&id=67b77e70&scoped=true&lang=css& ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {


var content = __webpack_require__(/*! !../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingLdapGroups.vue?vue&type=style&index=0&id=67b77e70&scoped=true&lang=css& */ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingLdapGroups.vue?vue&type=style&index=0&id=67b77e70&scoped=true&lang=css&");

if(typeof content === 'string') content = [[module.i, content, '']];

var transform;
var insertInto;



var options = {"hmr":true}

options.transform = transform
options.insertInto = undefined;

var update = __webpack_require__(/*! ../../../node_modules/style-loader/lib/addStyles.js */ "./node_modules/style-loader/lib/addStyles.js")(content, options);

if(content.locals) module.exports = content.locals;

if(false) {}

/***/ }),

/***/ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingNull.vue?vue&type=style&index=0&id=37a8c75c&scoped=true&lang=css&":
/*!*********************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader!./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingNull.vue?vue&type=style&index=0&id=37a8c75c&scoped=true&lang=css& ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {


var content = __webpack_require__(/*! !../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingNull.vue?vue&type=style&index=0&id=37a8c75c&scoped=true&lang=css& */ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingNull.vue?vue&type=style&index=0&id=37a8c75c&scoped=true&lang=css&");

if(typeof content === 'string') content = [[module.i, content, '']];

var transform;
var insertInto;



var options = {"hmr":true}

options.transform = transform
options.insertInto = undefined;

var update = __webpack_require__(/*! ../../../node_modules/style-loader/lib/addStyles.js */ "./node_modules/style-loader/lib/addStyles.js")(content, options);

if(content.locals) module.exports = content.locals;

if(false) {}

/***/ }),

/***/ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingSnmp3auth.vue?vue&type=style&index=0&id=4a841d50&scoped=true&lang=css&":
/*!**************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader!./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingSnmp3auth.vue?vue&type=style&index=0&id=4a841d50&scoped=true&lang=css& ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {


var content = __webpack_require__(/*! !../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingSnmp3auth.vue?vue&type=style&index=0&id=4a841d50&scoped=true&lang=css& */ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingSnmp3auth.vue?vue&type=style&index=0&id=4a841d50&scoped=true&lang=css&");

if(typeof content === 'string') content = [[module.i, content, '']];

var transform;
var insertInto;



var options = {"hmr":true}

options.transform = transform
options.insertInto = undefined;

var update = __webpack_require__(/*! ../../../node_modules/style-loader/lib/addStyles.js */ "./node_modules/style-loader/lib/addStyles.js")(content, options);

if(content.locals) module.exports = content.locals;

if(false) {}

/***/ }),

/***/ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Tabs.vue?vue&type=style&index=0&id=6e9bbb69&scoped=true&lang=css&":
/*!**************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader!./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/Tabs.vue?vue&type=style&index=0&id=6e9bbb69&scoped=true&lang=css& ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {


var content = __webpack_require__(/*! !../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./Tabs.vue?vue&type=style&index=0&id=6e9bbb69&scoped=true&lang=css& */ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Tabs.vue?vue&type=style&index=0&id=6e9bbb69&scoped=true&lang=css&");

if(typeof content === 'string') content = [[module.i, content, '']];

var transform;
var insertInto;



var options = {"hmr":true}

options.transform = transform
options.insertInto = undefined;

var update = __webpack_require__(/*! ../../../node_modules/style-loader/lib/addStyles.js */ "./node_modules/style-loader/lib/addStyles.js")(content, options);

if(content.locals) module.exports = content.locals;

if(false) {}

/***/ }),

/***/ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/TransitionCollapseHeight.vue?vue&type=style&index=0&id=41d51ed4&scoped=true&lang=css&":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader!./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/TransitionCollapseHeight.vue?vue&type=style&index=0&id=41d51ed4&scoped=true&lang=css& ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {


var content = __webpack_require__(/*! !../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./TransitionCollapseHeight.vue?vue&type=style&index=0&id=41d51ed4&scoped=true&lang=css& */ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/TransitionCollapseHeight.vue?vue&type=style&index=0&id=41d51ed4&scoped=true&lang=css&");

if(typeof content === 'string') content = [[module.i, content, '']];

var transform;
var insertInto;



var options = {"hmr":true}

options.transform = transform
options.insertInto = undefined;

var update = __webpack_require__(/*! ../../../node_modules/style-loader/lib/addStyles.js */ "./node_modules/style-loader/lib/addStyles.js")(content, options);

if(content.locals) module.exports = content.locals;

if(false) {}

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Accordion.vue?vue&type=template&id=17d98b6d&scoped=true&":
/*!************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/Accordion.vue?vue&type=template&id=17d98b6d&scoped=true& ***!
  \************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    { staticClass: "panel-group", attrs: { role: "tablist" } },
    [_vm._t("default")],
    2
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/AccordionItem.vue?vue&type=template&id=bf6d92c0&scoped=true&":
/*!****************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/AccordionItem.vue?vue&type=template&id=bf6d92c0&scoped=true& ***!
  \****************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    { staticClass: "panel panel-default" },
    [
      _c(
        "div",
        {
          staticClass: "panel-heading",
          attrs: { role: "tab", id: _vm.slug() }
        },
        [
          _c("h4", { staticClass: "panel-title" }, [
            _c(
              "a",
              {
                staticClass: "accordion-item-trigger",
                class: { collapsed: !_vm.isActive },
                attrs: {
                  role: "button",
                  "data-parent": "#accordion",
                  "data-href": _vm.hash()
                },
                on: {
                  click: function($event) {
                    _vm.isActive = !_vm.isActive
                  }
                }
              },
              [
                _c("i", {
                  staticClass: "fa fa-chevron-down accordion-item-trigger-icon"
                }),
                _vm._v(" "),
                _vm.icon
                  ? _c("i", { class: ["fa", "fa-fw", _vm.icon] })
                  : _vm._e(),
                _vm._v(
                  "\n                " +
                    _vm._s(_vm.text || _vm.name) +
                    "\n            "
                )
              ]
            )
          ])
        ]
      ),
      _vm._v(" "),
      _c("transition-collapse-height", [
        _vm.isActive
          ? _c(
              "div",
              {
                class: ["panel-collapse", "collapse", { in: _vm.isActive }],
                attrs: { id: _vm.slug() + "-content", role: "tabpanel" }
              },
              [_c("div", { staticClass: "panel-body" }, [_vm._t("default")], 2)]
            )
          : _vm._e()
      ])
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/ExampleComponent.vue?vue&type=template&id=299e239e&":
/*!*******************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/ExampleComponent.vue?vue&type=template&id=299e239e& ***!
  \*******************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _vm._m(0)
}
var staticRenderFns = [
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("div", { staticClass: "container" }, [
      _c("div", { staticClass: "row justify-content-center" }, [
        _c("div", { staticClass: "col-md-8" }, [
          _c("div", { staticClass: "card" }, [
            _c("div", { staticClass: "card-header" }, [
              _vm._v("Example Component")
            ]),
            _vm._v(" "),
            _c("div", { staticClass: "card-body" }, [
              _vm._v(
                "\n                    I'm an example component.\n                "
              )
            ])
          ])
        ])
      ])
    ])
  }
]
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/LibrenmsSetting.vue?vue&type=template&id=46bc7cf9&scoped=true&":
/*!******************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/LibrenmsSetting.vue?vue&type=template&id=46bc7cf9&scoped=true& ***!
  \******************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    { class: ["form-group", "has-feedback", _vm.setting.class, _vm.feedback] },
    [
      _c(
        "label",
        {
          directives: [
            {
              name: "tooltip",
              rawName: "v-tooltip",
              value: _vm.setting.name,
              expression: "setting.name"
            }
          ],
          staticClass: "col-sm-5 control-label",
          attrs: { for: _vm.setting.name }
        },
        [
          _vm._v("\n        " + _vm._s(_vm.getDescription()) + "\n        "),
          _vm.setting.units !== null
            ? _c("span", [_vm._v("(" + _vm._s(_vm.setting.units) + ")")])
            : _vm._e()
        ]
      ),
      _vm._v(" "),
      _c(
        "div",
        {
          directives: [
            {
              name: "tooltip",
              rawName: "v-tooltip",
              value: _vm.setting.disabled ? _vm.$t("settings.readonly") : false,
              expression: "setting.disabled ? $t('settings.readonly') : false"
            }
          ],
          staticClass: "col-sm-5"
        },
        [
          _c(_vm.getComponent(), {
            tag: "component",
            attrs: {
              value: _vm.value,
              name: _vm.setting.name,
              pattern: _vm.setting.pattern,
              disabled: _vm.setting.overridden,
              required: _vm.setting.required,
              options: _vm.setting.options
            },
            on: {
              input: function($event) {
                return _vm.changeValue($event)
              },
              change: function($event) {
                return _vm.changeValue($event)
              }
            }
          }),
          _vm._v(" "),
          _c("span", { staticClass: "form-control-feedback" })
        ],
        1
      ),
      _vm._v(" "),
      _c("div", { staticClass: "col-sm-2" }, [
        _c(
          "button",
          {
            directives: [
              {
                name: "show",
                rawName: "v-show",
                value: _vm.showUndo(),
                expression: "showUndo()"
              },
              {
                name: "tooltip",
                rawName: "v-tooltip",
                value: _vm.$t("Undo"),
                expression: "$t('Undo')"
              }
            ],
            staticClass: "btn btn-primary",
            attrs: { type: "button" },
            on: { click: _vm.resetToInitial }
          },
          [_c("i", { staticClass: "fa fa-undo" })]
        ),
        _vm._v(" "),
        _c(
          "button",
          {
            directives: [
              {
                name: "show",
                rawName: "v-show",
                value: _vm.showResetToDefault(),
                expression: "showResetToDefault()"
              },
              {
                name: "tooltip",
                rawName: "v-tooltip",
                value: _vm.$t("Reset to default"),
                expression: "$t('Reset to default')"
              }
            ],
            staticClass: "btn btn-default",
            attrs: { type: "button" },
            on: { click: _vm.resetToDefault }
          },
          [_c("i", { staticClass: "fa fa-refresh" })]
        ),
        _vm._v(" "),
        _vm.hasHelp()
          ? _c("div", {
              directives: [
                {
                  name: "tooltip",
                  rawName: "v-tooltip",
                  value: { content: _vm.getHelp(), trigger: "hover click" },
                  expression: "{content: getHelp(), trigger: 'hover click'}"
                }
              ],
              staticClass: "fa fa-fw fa-lg fa-question-circle"
            })
          : _vm._e()
      ])
    ]
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/LibrenmsSettings.vue?vue&type=template&id=d702796c&scoped=true&":
/*!*******************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/LibrenmsSettings.vue?vue&type=template&id=d702796c&scoped=true& ***!
  \*******************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "tabs",
    {
      attrs: { selected: this.tab },
      on: { "tab-selected": _vm.tabChanged },
      scopedSlots: _vm._u([
        {
          key: "header",
          fn: function() {
            return [
              _c(
                "form",
                {
                  staticClass: "form-inline",
                  on: {
                    submit: function($event) {
                      $event.preventDefault()
                    }
                  }
                },
                [
                  _c("div", { staticClass: "input-group" }, [
                    _c("input", {
                      directives: [
                        {
                          name: "model",
                          rawName: "v-model.trim",
                          value: _vm.search_phrase,
                          expression: "search_phrase",
                          modifiers: { trim: true }
                        }
                      ],
                      staticClass: "form-control",
                      attrs: {
                        id: "settings-search",
                        type: "search",
                        placeholder: "Filter Settings"
                      },
                      domProps: { value: _vm.search_phrase },
                      on: {
                        input: function($event) {
                          if ($event.target.composing) {
                            return
                          }
                          _vm.search_phrase = $event.target.value.trim()
                        },
                        blur: function($event) {
                          return _vm.$forceUpdate()
                        }
                      }
                    })
                  ])
                ]
              )
            ]
          },
          proxy: true
        }
      ])
    },
    [
      _vm._v(" "),
      _c(
        "tab",
        {
          attrs: {
            name: "global",
            selected: "global" === _vm.tab,
            text: _vm.$t("settings.groups.global")
          }
        },
        [_vm._v("Global tab")]
      ),
      _vm._v(" "),
      _vm._l(_vm.groups, function(sections, group) {
        return _c(
          "tab",
          {
            key: group,
            attrs: {
              name: group,
              selected: group === _vm.tab,
              text: _vm.$t("settings.groups." + group)
            }
          },
          [
            _c(
              "accordion",
              {
                on: {
                  expanded: _vm.sectionExpanded,
                  collapsed: _vm.sectionCollapsed
                }
              },
              _vm._l(_vm.groups[group], function(items, item) {
                return _c(
                  "accordion-item",
                  {
                    key: item,
                    attrs: {
                      name: item,
                      text: _vm.$t("settings.sections." + group + "." + item),
                      active: item === _vm.section
                    }
                  },
                  [
                    _c(
                      "form",
                      {
                        staticClass: "form-horizontal",
                        on: {
                          submit: function($event) {
                            $event.preventDefault()
                          }
                        }
                      },
                      _vm._l(items, function(setting) {
                        return _c("librenms-setting", {
                          directives: [
                            {
                              name: "show",
                              rawName: "v-show",
                              value: _vm.settingShown(setting),
                              expression: "settingShown(setting)"
                            }
                          ],
                          key: setting,
                          attrs: { setting: _vm.settings[setting] },
                          on: {
                            "setting-updated": function($event) {
                              return _vm.updateSetting(
                                $event.name,
                                $event.value
                              )
                            }
                          }
                        })
                      }),
                      1
                    )
                  ]
                )
              }),
              1
            )
          ],
          1
        )
      })
    ],
    2
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingArray.vue?vue&type=template&id=2cf33d54&scoped=true&":
/*!***************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingArray.vue?vue&type=template&id=2cf33d54&scoped=true& ***!
  \***************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    {
      directives: [
        {
          name: "tooltip",
          rawName: "v-tooltip",
          value: _vm.disabled ? _vm.$t("settings.readonly") : false,
          expression: "disabled ? $t('settings.readonly') : false"
        }
      ]
    },
    [
      _c(
        "draggable",
        {
          attrs: { disabled: _vm.disabled },
          on: {
            end: function($event) {
              return _vm.dragged()
            }
          },
          model: {
            value: _vm.localList,
            callback: function($$v) {
              _vm.localList = $$v
            },
            expression: "localList"
          }
        },
        _vm._l(_vm.localList, function(item, index) {
          return _c("div", { staticClass: "input-group" }, [
            _c(
              "span",
              { class: ["input-group-addon", _vm.disabled ? "disabled" : ""] },
              [_vm._v(_vm._s(index + 1) + ".")]
            ),
            _vm._v(" "),
            _c("input", {
              staticClass: "form-control",
              attrs: { type: "text", readonly: _vm.disabled },
              domProps: { value: item },
              on: {
                blur: function($event) {
                  return _vm.updateItem(index, $event.target.value)
                },
                keyup: function($event) {
                  if (
                    !$event.type.indexOf("key") &&
                    _vm._k($event.keyCode, "enter", 13, $event.key, "Enter")
                  ) {
                    return null
                  }
                  return _vm.updateItem(index, $event.target.value)
                }
              }
            }),
            _vm._v(" "),
            _c("span", { staticClass: "input-group-btn" }, [
              !_vm.disabled
                ? _c(
                    "button",
                    {
                      staticClass: "btn btn-danger",
                      attrs: { type: "button" },
                      on: {
                        click: function($event) {
                          return _vm.removeItem(index)
                        }
                      }
                    },
                    [_c("i", { staticClass: "fa fa-minus-circle" })]
                  )
                : _vm._e()
            ])
          ])
        }),
        0
      ),
      _vm._v(" "),
      !_vm.disabled
        ? _c("div", [
            _c("div", { staticClass: "input-group" }, [
              _c("input", {
                directives: [
                  {
                    name: "model",
                    rawName: "v-model",
                    value: _vm.newItem,
                    expression: "newItem"
                  }
                ],
                staticClass: "form-control",
                attrs: { type: "text" },
                domProps: { value: _vm.newItem },
                on: {
                  keyup: function($event) {
                    if (
                      !$event.type.indexOf("key") &&
                      _vm._k($event.keyCode, "enter", 13, $event.key, "Enter")
                    ) {
                      return null
                    }
                    return _vm.addItem($event)
                  },
                  input: function($event) {
                    if ($event.target.composing) {
                      return
                    }
                    _vm.newItem = $event.target.value
                  }
                }
              }),
              _vm._v(" "),
              _c("span", { staticClass: "input-group-btn" }, [
                _c(
                  "button",
                  {
                    staticClass: "btn btn-primary",
                    attrs: { type: "button" },
                    on: { click: _vm.addItem }
                  },
                  [_c("i", { staticClass: "fa fa-plus-circle" })]
                )
              ])
            ])
          ])
        : _vm._e()
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingBoolean.vue?vue&type=template&id=637b0ae3&scoped=true&":
/*!*****************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingBoolean.vue?vue&type=template&id=637b0ae3&scoped=true& ***!
  \*****************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("toggle-button", {
    attrs: {
      name: _vm.name,
      value: _vm.value,
      sync: true,
      required: _vm.required,
      disabled: _vm.disabled
    },
    on: {
      change: function($event) {
        return _vm.$emit("change", $event.value)
      }
    }
  })
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingDashboardSelect.vue?vue&type=template&id=27b9146b&scoped=true&":
/*!*************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingDashboardSelect.vue?vue&type=template&id=27b9146b&scoped=true& ***!
  \*************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("v-select", {
    attrs: {
      options: _vm.localOptions,
      label: "text",
      clearable: false,
      value: _vm.selected,
      required: _vm.required,
      disabled: _vm.disabled
    },
    on: {
      input: function($event) {
        return _vm.$emit("input", $event.id)
      }
    }
  })
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingEmail.vue?vue&type=template&id=0044bd17&scoped=true&":
/*!***************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingEmail.vue?vue&type=template&id=0044bd17&scoped=true& ***!
  \***************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("input", {
    staticClass: "form-control",
    attrs: {
      type: "email",
      name: _vm.name,
      pattern: _vm.pattern,
      required: _vm.required,
      disabled: _vm.disabled
    },
    domProps: { value: _vm.value },
    on: {
      input: function($event) {
        return _vm.$emit("input", $event.target.value)
      }
    }
  })
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingInteger.vue?vue&type=template&id=0078a4f9&scoped=true&":
/*!*****************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingInteger.vue?vue&type=template&id=0078a4f9&scoped=true& ***!
  \*****************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("input", {
    staticClass: "form-control",
    attrs: {
      type: "number",
      name: _vm.name,
      required: _vm.required,
      disabled: _vm.disabled
    },
    domProps: { value: _vm.value },
    on: {
      input: function($event) {
        _vm.$emit("input", _vm.parseNumber($event.target.value))
      }
    }
  })
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingLdapGroups.vue?vue&type=template&id=67b77e70&scoped=true&":
/*!********************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingLdapGroups.vue?vue&type=template&id=67b77e70&scoped=true& ***!
  \********************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    {
      directives: [
        {
          name: "tooltip",
          rawName: "v-tooltip",
          value: _vm.disabled ? _vm.$t("settings.readonly") : false,
          expression: "disabled ? $t('settings.readonly') : false"
        }
      ],
      staticClass: "form-inline"
    },
    [
      _vm._l(_vm.localList, function(data, group) {
        return _c("div", { staticClass: "input-group" }, [
          _c("input", {
            staticClass: "form-control",
            attrs: { type: "text", readonly: _vm.disabled },
            domProps: { value: group },
            on: {
              blur: function($event) {
                return _vm.updateItem(group, $event.target.value)
              },
              keyup: function($event) {
                if (
                  !$event.type.indexOf("key") &&
                  _vm._k($event.keyCode, "enter", 13, $event.key, "Enter")
                ) {
                  return null
                }
                return _vm.updateItem(group, $event.target.value)
              }
            }
          }),
          _vm._v(" "),
          _c("span", {
            staticClass: "input-group-btn",
            staticStyle: { width: "0" }
          }),
          _vm._v(" "),
          _c(
            "select",
            {
              staticClass: "form-control",
              on: {
                change: function($event) {
                  return _vm.updateLevel(group, $event.target.value)
                }
              }
            },
            [
              _c(
                "option",
                {
                  attrs: { value: "1" },
                  domProps: { selected: data.level === 1 }
                },
                [_vm._v(_vm._s(_vm.$t("Normal")))]
              ),
              _vm._v(" "),
              _c(
                "option",
                {
                  attrs: { value: "5" },
                  domProps: { selected: data.level === 5 }
                },
                [_vm._v(_vm._s(_vm.$t("Global Read")))]
              ),
              _vm._v(" "),
              _c(
                "option",
                {
                  attrs: { value: "10" },
                  domProps: { selected: data.level === 10 }
                },
                [_vm._v(_vm._s(_vm.$t("Admin")))]
              )
            ]
          ),
          _vm._v(" "),
          _c("span", { staticClass: "input-group-btn" }, [
            !_vm.disabled
              ? _c(
                  "button",
                  {
                    staticClass: "btn btn-danger",
                    attrs: { type: "button" },
                    on: {
                      click: function($event) {
                        return _vm.removeItem(group)
                      }
                    }
                  },
                  [_c("i", { staticClass: "fa fa-minus-circle" })]
                )
              : _vm._e()
          ])
        ])
      }),
      _vm._v(" "),
      !_vm.disabled
        ? _c("div", [
            _c("div", { staticClass: "input-group" }, [
              _c("input", {
                directives: [
                  {
                    name: "model",
                    rawName: "v-model",
                    value: _vm.newItem,
                    expression: "newItem"
                  }
                ],
                staticClass: "form-control",
                attrs: { type: "text" },
                domProps: { value: _vm.newItem },
                on: {
                  input: function($event) {
                    if ($event.target.composing) {
                      return
                    }
                    _vm.newItem = $event.target.value
                  }
                }
              }),
              _vm._v(" "),
              _c("span", {
                staticClass: "input-group-btn",
                staticStyle: { width: "0" }
              }),
              _vm._v(" "),
              _c(
                "select",
                {
                  directives: [
                    {
                      name: "model",
                      rawName: "v-model",
                      value: _vm.newItemLevel,
                      expression: "newItemLevel"
                    }
                  ],
                  staticClass: "form-control",
                  on: {
                    change: function($event) {
                      var $$selectedVal = Array.prototype.filter
                        .call($event.target.options, function(o) {
                          return o.selected
                        })
                        .map(function(o) {
                          var val = "_value" in o ? o._value : o.value
                          return val
                        })
                      _vm.newItemLevel = $event.target.multiple
                        ? $$selectedVal
                        : $$selectedVal[0]
                    }
                  }
                },
                [
                  _c("option", { attrs: { value: "1" } }, [
                    _vm._v(_vm._s(_vm.$t("Normal")))
                  ]),
                  _vm._v(" "),
                  _c("option", { attrs: { value: "5" } }, [
                    _vm._v(_vm._s(_vm.$t("Global Read")))
                  ]),
                  _vm._v(" "),
                  _c("option", { attrs: { value: "10" } }, [
                    _vm._v(_vm._s(_vm.$t("Admin")))
                  ])
                ]
              ),
              _vm._v(" "),
              _c("span", { staticClass: "input-group-btn" }, [
                _c(
                  "button",
                  {
                    staticClass: "btn btn-primary",
                    attrs: { type: "button" },
                    on: { click: _vm.addItem }
                  },
                  [_c("i", { staticClass: "fa fa-plus-circle" })]
                )
              ])
            ])
          ])
        : _vm._e()
    ],
    2
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingNull.vue?vue&type=template&id=37a8c75c&scoped=true&":
/*!**************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingNull.vue?vue&type=template&id=37a8c75c&scoped=true& ***!
  \**************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", [_vm._v("Invalid type for: " + _vm._s(_vm.name))])
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingPassword.vue?vue&type=template&id=6e386bf0&scoped=true&":
/*!******************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingPassword.vue?vue&type=template&id=6e386bf0&scoped=true& ***!
  \******************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("input", {
    staticClass: "form-control",
    attrs: {
      type: "password",
      name: _vm.name,
      pattern: _vm.pattern,
      required: _vm.required,
      disabled: _vm.disabled
    },
    domProps: { value: _vm.value },
    on: {
      input: function($event) {
        return _vm.$emit("input", $event.target.value)
      }
    }
  })
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingSelect.vue?vue&type=template&id=59a5b911&scoped=true&":
/*!****************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingSelect.vue?vue&type=template&id=59a5b911&scoped=true& ***!
  \****************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "select",
    {
      staticClass: "form-control",
      attrs: { name: _vm.name, required: _vm.required, disabled: _vm.disabled },
      domProps: { value: _vm.value },
      on: {
        input: function($event) {
          return _vm.$emit("input", $event.target.value)
        }
      }
    },
    _vm._l(_vm.options, function(text, option) {
      return _c("option", {
        domProps: {
          value: Array.isArray(_vm.options) ? text : option,
          selected: _vm.value === option,
          textContent: _vm._s(_vm.getText(_vm.name, text))
        }
      })
    }),
    0
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingSnmp3auth.vue?vue&type=template&id=4a841d50&scoped=true&":
/*!*******************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingSnmp3auth.vue?vue&type=template&id=4a841d50&scoped=true& ***!
  \*******************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    [
      _c(
        "draggable",
        {
          attrs: { disabled: _vm.disabled },
          on: {
            end: function($event) {
              return _vm.dragged()
            }
          },
          model: {
            value: _vm.localList,
            callback: function($$v) {
              _vm.localList = $$v
            },
            expression: "localList"
          }
        },
        _vm._l(_vm.localList, function(item, id) {
          return _c("div", [
            _c("div", { staticClass: "panel panel-default" }, [
              _c("div", { staticClass: "panel-heading" }, [
                _c("h3", { staticClass: "panel-title" }, [
                  _vm._v(_vm._s(id + 1) + ". "),
                  _c(
                    "span",
                    {
                      staticClass: "pull-right text-danger",
                      on: {
                        click: function($event) {
                          return _vm.removeItem(id)
                        }
                      }
                    },
                    [_c("i", { staticClass: "fa fa-minus-circle" })]
                  )
                ])
              ]),
              _vm._v(" "),
              _c("div", { staticClass: "panel-body" }, [
                _c(
                  "form",
                  {
                    attrs: { disabled: _vm.disabled },
                    on: {
                      onsubmit: function($event) {
                        $event.preventDefault()
                      }
                    }
                  },
                  [
                    _c("div", { staticClass: "form-group" }, [
                      _c("div", { staticClass: "col-sm-12" }, [
                        _c(
                          "select",
                          {
                            directives: [
                              {
                                name: "model",
                                rawName: "v-model",
                                value: item.authlevel,
                                expression: "item.authlevel"
                              }
                            ],
                            staticClass: "form-control",
                            attrs: { id: "authlevel", disabled: _vm.disabled },
                            on: {
                              change: [
                                function($event) {
                                  var $$selectedVal = Array.prototype.filter
                                    .call($event.target.options, function(o) {
                                      return o.selected
                                    })
                                    .map(function(o) {
                                      var val =
                                        "_value" in o ? o._value : o.value
                                      return val
                                    })
                                  _vm.$set(
                                    item,
                                    "authlevel",
                                    $event.target.multiple
                                      ? $$selectedVal
                                      : $$selectedVal[0]
                                  )
                                },
                                function($event) {
                                  return _vm.updateItem(
                                    id,
                                    $event.target.id,
                                    $event.target.value
                                  )
                                }
                              ]
                            }
                          },
                          [
                            _c("option", {
                              attrs: { value: "noAuthNoPriv" },
                              domProps: {
                                textContent: _vm._s(
                                  _vm.$t(
                                    "settings.settings.snmp.v3.level.noAuthNoPriv"
                                  )
                                )
                              }
                            }),
                            _vm._v(" "),
                            _c("option", {
                              attrs: { value: "authNoPriv" },
                              domProps: {
                                textContent: _vm._s(
                                  _vm.$t(
                                    "settings.settings.snmp.v3.level.authNoPriv"
                                  )
                                )
                              }
                            }),
                            _vm._v(" "),
                            _c("option", {
                              attrs: { value: "authPriv" },
                              domProps: {
                                textContent: _vm._s(
                                  _vm.$t(
                                    "settings.settings.snmp.v3.level.authPriv"
                                  )
                                )
                              }
                            })
                          ]
                        )
                      ])
                    ]),
                    _vm._v(" "),
                    _c(
                      "fieldset",
                      {
                        directives: [
                          {
                            name: "show",
                            rawName: "v-show",
                            value:
                              item.authlevel.toString().substring(0, 4) ===
                              "auth",
                            expression:
                              "item.authlevel.toString().substring(0, 4) === 'auth'"
                          }
                        ],
                        attrs: { name: "algo", disabled: _vm.disabled }
                      },
                      [
                        _c("legend", {
                          staticClass: "h4",
                          domProps: {
                            textContent: _vm._s(
                              _vm.$t("settings.settings.snmp.v3.auth")
                            )
                          }
                        }),
                        _vm._v(" "),
                        _c("div", { staticClass: "form-group" }, [
                          _c("label", {
                            staticClass: "col-sm-3 control-label",
                            attrs: { for: "authalgo" },
                            domProps: {
                              textContent: _vm._s(
                                _vm.$t(
                                  "settings.settings.snmp.v3.fields.authalgo"
                                )
                              )
                            }
                          }),
                          _vm._v(" "),
                          _c("div", { staticClass: "col-sm-9" }, [
                            _c(
                              "select",
                              {
                                directives: [
                                  {
                                    name: "model",
                                    rawName: "v-model",
                                    value: item.authalgo,
                                    expression: "item.authalgo"
                                  }
                                ],
                                staticClass: "form-control",
                                attrs: { id: "authalgo", name: "authalgo" },
                                on: {
                                  change: [
                                    function($event) {
                                      var $$selectedVal = Array.prototype.filter
                                        .call($event.target.options, function(
                                          o
                                        ) {
                                          return o.selected
                                        })
                                        .map(function(o) {
                                          var val =
                                            "_value" in o ? o._value : o.value
                                          return val
                                        })
                                      _vm.$set(
                                        item,
                                        "authalgo",
                                        $event.target.multiple
                                          ? $$selectedVal
                                          : $$selectedVal[0]
                                      )
                                    },
                                    function($event) {
                                      return _vm.updateItem(
                                        id,
                                        $event.target.id,
                                        $event.target.value
                                      )
                                    }
                                  ]
                                }
                              },
                              [
                                _c("option", { attrs: { value: "MD5" } }, [
                                  _vm._v("MD5")
                                ]),
                                _vm._v(" "),
                                _c("option", { attrs: { value: "AES" } }, [
                                  _vm._v("AES")
                                ])
                              ]
                            )
                          ])
                        ]),
                        _vm._v(" "),
                        _c("div", { staticClass: "form-group" }, [
                          _c("label", {
                            staticClass: "col-sm-3 control-label",
                            attrs: { for: "authname" },
                            domProps: {
                              textContent: _vm._s(
                                _vm.$t(
                                  "settings.settings.snmp.v3.fields.authname"
                                )
                              )
                            }
                          }),
                          _vm._v(" "),
                          _c("div", { staticClass: "col-sm-9" }, [
                            _c("input", {
                              staticClass: "form-control",
                              attrs: { type: "text", id: "authname" },
                              domProps: { value: item.authname },
                              on: {
                                input: function($event) {
                                  return _vm.updateItem(
                                    id,
                                    $event.target.id,
                                    $event.target.value
                                  )
                                }
                              }
                            })
                          ])
                        ]),
                        _vm._v(" "),
                        _c("div", { staticClass: "form-group" }, [
                          _c("label", {
                            staticClass: "col-sm-3 control-label",
                            attrs: { for: "authpass" },
                            domProps: {
                              textContent: _vm._s(
                                _vm.$t(
                                  "settings.settings.snmp.v3.fields.authpass"
                                )
                              )
                            }
                          }),
                          _vm._v(" "),
                          _c("div", { staticClass: "col-sm-9" }, [
                            _c("input", {
                              staticClass: "form-control",
                              attrs: { type: "text", id: "authpass" },
                              domProps: { value: item.authpass },
                              on: {
                                input: function($event) {
                                  return _vm.updateItem(
                                    id,
                                    $event.target.id,
                                    $event.target.value
                                  )
                                }
                              }
                            })
                          ])
                        ])
                      ]
                    ),
                    _vm._v(" "),
                    _c(
                      "fieldset",
                      {
                        directives: [
                          {
                            name: "show",
                            rawName: "v-show",
                            value: item.authlevel === "authPriv",
                            expression: "item.authlevel === 'authPriv'"
                          }
                        ],
                        attrs: { name: "crypt", disabled: _vm.disabled }
                      },
                      [
                        _c("legend", {
                          staticClass: "h4",
                          domProps: {
                            textContent: _vm._s(
                              _vm.$t("settings.settings.snmp.v3.crypto")
                            )
                          }
                        }),
                        _vm._v(" "),
                        _c("div", { staticClass: "form-group" }, [
                          _c(
                            "label",
                            {
                              staticClass: "col-sm-3 control-label",
                              attrs: { for: "cryptoalgo" }
                            },
                            [_vm._v("Cryptoalgo")]
                          ),
                          _vm._v(" "),
                          _c("div", { staticClass: "col-sm-9" }, [
                            _c(
                              "select",
                              {
                                directives: [
                                  {
                                    name: "model",
                                    rawName: "v-model",
                                    value: item.cryptoalgo,
                                    expression: "item.cryptoalgo"
                                  }
                                ],
                                staticClass: "form-control",
                                attrs: { id: "cryptoalgo" },
                                on: {
                                  change: [
                                    function($event) {
                                      var $$selectedVal = Array.prototype.filter
                                        .call($event.target.options, function(
                                          o
                                        ) {
                                          return o.selected
                                        })
                                        .map(function(o) {
                                          var val =
                                            "_value" in o ? o._value : o.value
                                          return val
                                        })
                                      _vm.$set(
                                        item,
                                        "cryptoalgo",
                                        $event.target.multiple
                                          ? $$selectedVal
                                          : $$selectedVal[0]
                                      )
                                    },
                                    function($event) {
                                      return _vm.updateItem(
                                        id,
                                        $event.target.id,
                                        $event.target.value
                                      )
                                    }
                                  ]
                                }
                              },
                              [
                                _c("option", { attrs: { value: "AES" } }, [
                                  _vm._v("AES")
                                ]),
                                _vm._v(" "),
                                _c("option", { attrs: { value: "DES" } }, [
                                  _vm._v("DES")
                                ])
                              ]
                            )
                          ])
                        ]),
                        _vm._v(" "),
                        _c("div", { staticClass: "form-group" }, [
                          _c("label", {
                            staticClass: "col-sm-3 control-label",
                            attrs: { for: "cryptopass" },
                            domProps: {
                              textContent: _vm._s(
                                _vm.$t(
                                  "settings.settings.snmp.v3.fields.authpass"
                                )
                              )
                            }
                          }),
                          _vm._v(" "),
                          _c("div", { staticClass: "col-sm-9" }, [
                            _c("input", {
                              staticClass: "form-control",
                              attrs: { type: "text", id: "cryptopass" },
                              domProps: { value: item.cryptopass },
                              on: {
                                input: function($event) {
                                  return _vm.updateItem(
                                    id,
                                    $event.target.id,
                                    $event.target.value
                                  )
                                }
                              }
                            })
                          ])
                        ])
                      ]
                    )
                  ]
                )
              ])
            ])
          ])
        }),
        0
      ),
      _vm._v(" "),
      _c("div", { staticClass: "row snmp3-add-button" }, [
        _c("div", { staticClass: "col-sm-12" }, [
          _c(
            "button",
            {
              staticClass: "btn btn-primary",
              on: {
                click: function($event) {
                  return _vm.addItem()
                }
              }
            },
            [
              _c("i", { staticClass: "fa fa-plus-circle" }),
              _vm._v(" " + _vm._s(_vm.$t("New")))
            ]
          )
        ])
      ])
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingText.vue?vue&type=template&id=6d770402&scoped=true&":
/*!**************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/SettingText.vue?vue&type=template&id=6d770402&scoped=true& ***!
  \**************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("input", {
    staticClass: "form-control",
    attrs: {
      type: "text",
      name: _vm.name,
      pattern: _vm.pattern,
      required: _vm.required,
      disabled: _vm.disabled
    },
    domProps: { value: _vm.value },
    on: {
      input: function($event) {
        return _vm.$emit("input", $event.target.value)
      }
    }
  })
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Tab.vue?vue&type=template&id=8dbef60c&scoped=true&":
/*!******************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/Tab.vue?vue&type=template&id=8dbef60c&scoped=true& ***!
  \******************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    {
      directives: [
        {
          name: "show",
          rawName: "v-show",
          value: _vm.isActive,
          expression: "isActive"
        }
      ],
      staticClass: "tab-pane",
      attrs: { role: "tabpanel", id: _vm.name }
    },
    [_vm._t("default")],
    2
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Tabs.vue?vue&type=template&id=6e9bbb69&scoped=true&":
/*!*******************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/Tabs.vue?vue&type=template&id=6e9bbb69&scoped=true& ***!
  \*******************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", [
    _c("div", { staticClass: "panel with-nav-tabs panel-default" }, [
      _c("div", { staticClass: "panel-heading" }, [
        _c(
          "ul",
          { staticClass: "nav nav-tabs", attrs: { role: "tablist" } },
          [
            _vm._l(_vm.tabs, function(tab) {
              return _c(
                "li",
                {
                  key: tab.name,
                  class: { active: tab.isActive },
                  attrs: { role: "presentation" }
                },
                [
                  _c(
                    "a",
                    {
                      attrs: { role: "tab", "aria-controls": tab.name },
                      on: {
                        click: function($event) {
                          _vm.activeTab = tab.name
                        }
                      }
                    },
                    [
                      tab.icon
                        ? _c("i", { class: ["fa", "fa-fw", tab.icon] })
                        : _vm._e(),
                      _vm._v(
                        "\n                        " +
                          _vm._s(tab.text || tab.name) +
                          " \n                    "
                      )
                    ]
                  )
                ]
              )
            }),
            _vm._v(" "),
            _c("li", { staticClass: "pull-right" }, [_vm._t("header")], 2)
          ],
          2
        )
      ]),
      _vm._v(" "),
      _c("div", { staticClass: "panel-body" }, [_vm._t("default")], 2)
    ])
  ])
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/TransitionCollapseHeight.vue?vue&type=template&id=41d51ed4&scoped=true&":
/*!***************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/TransitionCollapseHeight.vue?vue&type=template&id=41d51ed4&scoped=true& ***!
  \***************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "transition",
    {
      attrs: {
        "enter-active-class": "enter-active",
        "leave-active-class": "leave-active"
      },
      on: {
        "before-enter": _vm.beforeEnter,
        enter: _vm.enter,
        "after-enter": _vm.afterEnter,
        "before-leave": _vm.beforeLeave,
        leave: _vm.leave,
        "after-leave": _vm.afterLeave
      }
    },
    [_vm._t("default")],
    2
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./resources/js sync recursive \\.vue$/":
/*!***********************************!*\
  !*** ./resources/js sync \.vue$/ ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var map = {
	"./components/Accordion.vue": "./resources/js/components/Accordion.vue",
	"./components/AccordionItem.vue": "./resources/js/components/AccordionItem.vue",
	"./components/BaseSetting.vue": "./resources/js/components/BaseSetting.vue",
	"./components/ExampleComponent.vue": "./resources/js/components/ExampleComponent.vue",
	"./components/LibrenmsSetting.vue": "./resources/js/components/LibrenmsSetting.vue",
	"./components/LibrenmsSettings.vue": "./resources/js/components/LibrenmsSettings.vue",
	"./components/SettingArray.vue": "./resources/js/components/SettingArray.vue",
	"./components/SettingBoolean.vue": "./resources/js/components/SettingBoolean.vue",
	"./components/SettingDashboardSelect.vue": "./resources/js/components/SettingDashboardSelect.vue",
	"./components/SettingEmail.vue": "./resources/js/components/SettingEmail.vue",
	"./components/SettingInteger.vue": "./resources/js/components/SettingInteger.vue",
	"./components/SettingLdapGroups.vue": "./resources/js/components/SettingLdapGroups.vue",
	"./components/SettingNull.vue": "./resources/js/components/SettingNull.vue",
	"./components/SettingPassword.vue": "./resources/js/components/SettingPassword.vue",
	"./components/SettingSelect.vue": "./resources/js/components/SettingSelect.vue",
	"./components/SettingSnmp3auth.vue": "./resources/js/components/SettingSnmp3auth.vue",
	"./components/SettingText.vue": "./resources/js/components/SettingText.vue",
	"./components/Tab.vue": "./resources/js/components/Tab.vue",
	"./components/Tabs.vue": "./resources/js/components/Tabs.vue",
	"./components/TransitionCollapseHeight.vue": "./resources/js/components/TransitionCollapseHeight.vue"
};


function webpackContext(req) {
	var id = webpackContextResolve(req);
	return __webpack_require__(id);
}
function webpackContextResolve(req) {
	if(!__webpack_require__.o(map, req)) {
		var e = new Error("Cannot find module '" + req + "'");
		e.code = 'MODULE_NOT_FOUND';
		throw e;
	}
	return map[req];
}
webpackContext.keys = function webpackContextKeys() {
	return Object.keys(map);
};
webpackContext.resolve = webpackContextResolve;
module.exports = webpackContext;
webpackContext.id = "./resources/js sync recursive \\.vue$/";

/***/ }),

/***/ "./resources/js/app.js":
/*!*****************************!*\
  !*** ./resources/js/app.js ***!
  \*****************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _plugins_i18n_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./plugins/i18n.js */ "./resources/js/plugins/i18n.js");
/* harmony import */ var vue_js_toggle_button__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! vue-js-toggle-button */ "./node_modules/vue-js-toggle-button/dist/index.js");
/* harmony import */ var vue_js_toggle_button__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(vue_js_toggle_button__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var v_tooltip__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! v-tooltip */ "./node_modules/v-tooltip/dist/v-tooltip.esm.js");
/* harmony import */ var vue_select__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! vue-select */ "./node_modules/vue-select/dist/vue-select.js");
/* harmony import */ var vue_select__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(vue_select__WEBPACK_IMPORTED_MODULE_3__);
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
__webpack_require__(/*! ./bootstrap */ "./resources/js/bootstrap.js");

window.Vue = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.common.js");
 // translation

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

var files = __webpack_require__("./resources/js sync recursive \\.vue$/");

files.keys().map(function (key) {
  return Vue.component(key.split('/').pop().split('.')[0], files(key)["default"]);
});

Vue.use(vue_js_toggle_button__WEBPACK_IMPORTED_MODULE_1___default.a);

Vue.use(v_tooltip__WEBPACK_IMPORTED_MODULE_2__["default"]);

Vue.component('v-select', vue_select__WEBPACK_IMPORTED_MODULE_3___default.a);
Vue.mixin({
  methods: {
    route: route
  }
});
Vue.filter('ucfirst', function (value) {
  if (!value) return '';
  value = value.toString();
  return value.charAt(0).toUpperCase() + value.slice(1);
});
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

var app = new Vue({
  el: '#app',
  i18n: _plugins_i18n_js__WEBPACK_IMPORTED_MODULE_0__["i18n"]
});

/***/ }),

/***/ "./resources/js/bootstrap.js":
/*!***********************************!*\
  !*** ./resources/js/bootstrap.js ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

window._ = __webpack_require__(/*! lodash */ "./node_modules/lodash/lodash.js");
/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
  window.Popper = __webpack_require__(/*! popper.js */ "./node_modules/popper.js/dist/esm/popper.js")["default"]; // window.$ = window.jQuery = require('jquery');
  // require('bootstrap');
} catch (e) {}
/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */


window.axios = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

var token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
  console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */
// import Echo from 'laravel-echo'
// window.Pusher = require('pusher-js');
// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     encrypted: true
// });

/***/ }),

/***/ "./resources/js/components/Accordion.vue":
/*!***********************************************!*\
  !*** ./resources/js/components/Accordion.vue ***!
  \***********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Accordion_vue_vue_type_template_id_17d98b6d_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Accordion.vue?vue&type=template&id=17d98b6d&scoped=true& */ "./resources/js/components/Accordion.vue?vue&type=template&id=17d98b6d&scoped=true&");
/* harmony import */ var _Accordion_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Accordion.vue?vue&type=script&lang=js& */ "./resources/js/components/Accordion.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _Accordion_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _Accordion_vue_vue_type_template_id_17d98b6d_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _Accordion_vue_vue_type_template_id_17d98b6d_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "17d98b6d",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/Accordion.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/Accordion.vue?vue&type=script&lang=js&":
/*!************************************************************************!*\
  !*** ./resources/js/components/Accordion.vue?vue&type=script&lang=js& ***!
  \************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Accordion_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./Accordion.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Accordion.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Accordion_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/Accordion.vue?vue&type=template&id=17d98b6d&scoped=true&":
/*!******************************************************************************************!*\
  !*** ./resources/js/components/Accordion.vue?vue&type=template&id=17d98b6d&scoped=true& ***!
  \******************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Accordion_vue_vue_type_template_id_17d98b6d_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./Accordion.vue?vue&type=template&id=17d98b6d&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Accordion.vue?vue&type=template&id=17d98b6d&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Accordion_vue_vue_type_template_id_17d98b6d_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Accordion_vue_vue_type_template_id_17d98b6d_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/AccordionItem.vue":
/*!***************************************************!*\
  !*** ./resources/js/components/AccordionItem.vue ***!
  \***************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _AccordionItem_vue_vue_type_template_id_bf6d92c0_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./AccordionItem.vue?vue&type=template&id=bf6d92c0&scoped=true& */ "./resources/js/components/AccordionItem.vue?vue&type=template&id=bf6d92c0&scoped=true&");
/* harmony import */ var _AccordionItem_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AccordionItem.vue?vue&type=script&lang=js& */ "./resources/js/components/AccordionItem.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _AccordionItem_vue_vue_type_style_index_0_id_bf6d92c0_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./AccordionItem.vue?vue&type=style&index=0&id=bf6d92c0&scoped=true&lang=css& */ "./resources/js/components/AccordionItem.vue?vue&type=style&index=0&id=bf6d92c0&scoped=true&lang=css&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");






/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__["default"])(
  _AccordionItem_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _AccordionItem_vue_vue_type_template_id_bf6d92c0_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _AccordionItem_vue_vue_type_template_id_bf6d92c0_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "bf6d92c0",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/AccordionItem.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/AccordionItem.vue?vue&type=script&lang=js&":
/*!****************************************************************************!*\
  !*** ./resources/js/components/AccordionItem.vue?vue&type=script&lang=js& ***!
  \****************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AccordionItem_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./AccordionItem.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/AccordionItem.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_AccordionItem_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/AccordionItem.vue?vue&type=style&index=0&id=bf6d92c0&scoped=true&lang=css&":
/*!************************************************************************************************************!*\
  !*** ./resources/js/components/AccordionItem.vue?vue&type=style&index=0&id=bf6d92c0&scoped=true&lang=css& ***!
  \************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_AccordionItem_vue_vue_type_style_index_0_id_bf6d92c0_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/style-loader!../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./AccordionItem.vue?vue&type=style&index=0&id=bf6d92c0&scoped=true&lang=css& */ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/AccordionItem.vue?vue&type=style&index=0&id=bf6d92c0&scoped=true&lang=css&");
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_AccordionItem_vue_vue_type_style_index_0_id_bf6d92c0_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_AccordionItem_vue_vue_type_style_index_0_id_bf6d92c0_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_AccordionItem_vue_vue_type_style_index_0_id_bf6d92c0_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_AccordionItem_vue_vue_type_style_index_0_id_bf6d92c0_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__[key]; }) }(__WEBPACK_IMPORT_KEY__));
 /* harmony default export */ __webpack_exports__["default"] = (_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_AccordionItem_vue_vue_type_style_index_0_id_bf6d92c0_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "./resources/js/components/AccordionItem.vue?vue&type=template&id=bf6d92c0&scoped=true&":
/*!**********************************************************************************************!*\
  !*** ./resources/js/components/AccordionItem.vue?vue&type=template&id=bf6d92c0&scoped=true& ***!
  \**********************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_AccordionItem_vue_vue_type_template_id_bf6d92c0_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./AccordionItem.vue?vue&type=template&id=bf6d92c0&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/AccordionItem.vue?vue&type=template&id=bf6d92c0&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_AccordionItem_vue_vue_type_template_id_bf6d92c0_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_AccordionItem_vue_vue_type_template_id_bf6d92c0_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/BaseSetting.vue":
/*!*************************************************!*\
  !*** ./resources/js/components/BaseSetting.vue ***!
  \*************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _BaseSetting_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./BaseSetting.vue?vue&type=script&lang=js& */ "./resources/js/components/BaseSetting.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");
var render, staticRenderFns




/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_1__["default"])(
  _BaseSetting_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"],
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/BaseSetting.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/BaseSetting.vue?vue&type=script&lang=js&":
/*!**************************************************************************!*\
  !*** ./resources/js/components/BaseSetting.vue?vue&type=script&lang=js& ***!
  \**************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_BaseSetting_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./BaseSetting.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/BaseSetting.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_BaseSetting_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/ExampleComponent.vue":
/*!******************************************************!*\
  !*** ./resources/js/components/ExampleComponent.vue ***!
  \******************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _ExampleComponent_vue_vue_type_template_id_299e239e___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ExampleComponent.vue?vue&type=template&id=299e239e& */ "./resources/js/components/ExampleComponent.vue?vue&type=template&id=299e239e&");
/* harmony import */ var _ExampleComponent_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ExampleComponent.vue?vue&type=script&lang=js& */ "./resources/js/components/ExampleComponent.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _ExampleComponent_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _ExampleComponent_vue_vue_type_template_id_299e239e___WEBPACK_IMPORTED_MODULE_0__["render"],
  _ExampleComponent_vue_vue_type_template_id_299e239e___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/ExampleComponent.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/ExampleComponent.vue?vue&type=script&lang=js&":
/*!*******************************************************************************!*\
  !*** ./resources/js/components/ExampleComponent.vue?vue&type=script&lang=js& ***!
  \*******************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ExampleComponent_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./ExampleComponent.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/ExampleComponent.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ExampleComponent_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/ExampleComponent.vue?vue&type=template&id=299e239e&":
/*!*************************************************************************************!*\
  !*** ./resources/js/components/ExampleComponent.vue?vue&type=template&id=299e239e& ***!
  \*************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ExampleComponent_vue_vue_type_template_id_299e239e___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./ExampleComponent.vue?vue&type=template&id=299e239e& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/ExampleComponent.vue?vue&type=template&id=299e239e&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ExampleComponent_vue_vue_type_template_id_299e239e___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ExampleComponent_vue_vue_type_template_id_299e239e___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/LibrenmsSetting.vue":
/*!*****************************************************!*\
  !*** ./resources/js/components/LibrenmsSetting.vue ***!
  \*****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _LibrenmsSetting_vue_vue_type_template_id_46bc7cf9_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./LibrenmsSetting.vue?vue&type=template&id=46bc7cf9&scoped=true& */ "./resources/js/components/LibrenmsSetting.vue?vue&type=template&id=46bc7cf9&scoped=true&");
/* harmony import */ var _LibrenmsSetting_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./LibrenmsSetting.vue?vue&type=script&lang=js& */ "./resources/js/components/LibrenmsSetting.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _LibrenmsSetting_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _LibrenmsSetting_vue_vue_type_template_id_46bc7cf9_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _LibrenmsSetting_vue_vue_type_template_id_46bc7cf9_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "46bc7cf9",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/LibrenmsSetting.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/LibrenmsSetting.vue?vue&type=script&lang=js&":
/*!******************************************************************************!*\
  !*** ./resources/js/components/LibrenmsSetting.vue?vue&type=script&lang=js& ***!
  \******************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_LibrenmsSetting_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./LibrenmsSetting.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/LibrenmsSetting.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_LibrenmsSetting_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/LibrenmsSetting.vue?vue&type=template&id=46bc7cf9&scoped=true&":
/*!************************************************************************************************!*\
  !*** ./resources/js/components/LibrenmsSetting.vue?vue&type=template&id=46bc7cf9&scoped=true& ***!
  \************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_LibrenmsSetting_vue_vue_type_template_id_46bc7cf9_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./LibrenmsSetting.vue?vue&type=template&id=46bc7cf9&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/LibrenmsSetting.vue?vue&type=template&id=46bc7cf9&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_LibrenmsSetting_vue_vue_type_template_id_46bc7cf9_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_LibrenmsSetting_vue_vue_type_template_id_46bc7cf9_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/LibrenmsSettings.vue":
/*!******************************************************!*\
  !*** ./resources/js/components/LibrenmsSettings.vue ***!
  \******************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _LibrenmsSettings_vue_vue_type_template_id_d702796c_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./LibrenmsSettings.vue?vue&type=template&id=d702796c&scoped=true& */ "./resources/js/components/LibrenmsSettings.vue?vue&type=template&id=d702796c&scoped=true&");
/* harmony import */ var _LibrenmsSettings_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./LibrenmsSettings.vue?vue&type=script&lang=js& */ "./resources/js/components/LibrenmsSettings.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _LibrenmsSettings_vue_vue_type_style_index_0_id_d702796c_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./LibrenmsSettings.vue?vue&type=style&index=0&id=d702796c&scoped=true&lang=css& */ "./resources/js/components/LibrenmsSettings.vue?vue&type=style&index=0&id=d702796c&scoped=true&lang=css&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");






/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__["default"])(
  _LibrenmsSettings_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _LibrenmsSettings_vue_vue_type_template_id_d702796c_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _LibrenmsSettings_vue_vue_type_template_id_d702796c_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "d702796c",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/LibrenmsSettings.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/LibrenmsSettings.vue?vue&type=script&lang=js&":
/*!*******************************************************************************!*\
  !*** ./resources/js/components/LibrenmsSettings.vue?vue&type=script&lang=js& ***!
  \*******************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_LibrenmsSettings_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./LibrenmsSettings.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/LibrenmsSettings.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_LibrenmsSettings_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/LibrenmsSettings.vue?vue&type=style&index=0&id=d702796c&scoped=true&lang=css&":
/*!***************************************************************************************************************!*\
  !*** ./resources/js/components/LibrenmsSettings.vue?vue&type=style&index=0&id=d702796c&scoped=true&lang=css& ***!
  \***************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_LibrenmsSettings_vue_vue_type_style_index_0_id_d702796c_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/style-loader!../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./LibrenmsSettings.vue?vue&type=style&index=0&id=d702796c&scoped=true&lang=css& */ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/LibrenmsSettings.vue?vue&type=style&index=0&id=d702796c&scoped=true&lang=css&");
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_LibrenmsSettings_vue_vue_type_style_index_0_id_d702796c_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_LibrenmsSettings_vue_vue_type_style_index_0_id_d702796c_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_LibrenmsSettings_vue_vue_type_style_index_0_id_d702796c_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_LibrenmsSettings_vue_vue_type_style_index_0_id_d702796c_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__[key]; }) }(__WEBPACK_IMPORT_KEY__));
 /* harmony default export */ __webpack_exports__["default"] = (_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_LibrenmsSettings_vue_vue_type_style_index_0_id_d702796c_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "./resources/js/components/LibrenmsSettings.vue?vue&type=template&id=d702796c&scoped=true&":
/*!*************************************************************************************************!*\
  !*** ./resources/js/components/LibrenmsSettings.vue?vue&type=template&id=d702796c&scoped=true& ***!
  \*************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_LibrenmsSettings_vue_vue_type_template_id_d702796c_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./LibrenmsSettings.vue?vue&type=template&id=d702796c&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/LibrenmsSettings.vue?vue&type=template&id=d702796c&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_LibrenmsSettings_vue_vue_type_template_id_d702796c_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_LibrenmsSettings_vue_vue_type_template_id_d702796c_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/SettingArray.vue":
/*!**************************************************!*\
  !*** ./resources/js/components/SettingArray.vue ***!
  \**************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _SettingArray_vue_vue_type_template_id_2cf33d54_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./SettingArray.vue?vue&type=template&id=2cf33d54&scoped=true& */ "./resources/js/components/SettingArray.vue?vue&type=template&id=2cf33d54&scoped=true&");
/* harmony import */ var _SettingArray_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./SettingArray.vue?vue&type=script&lang=js& */ "./resources/js/components/SettingArray.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _SettingArray_vue_vue_type_style_index_0_id_2cf33d54_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./SettingArray.vue?vue&type=style&index=0&id=2cf33d54&scoped=true&lang=css& */ "./resources/js/components/SettingArray.vue?vue&type=style&index=0&id=2cf33d54&scoped=true&lang=css&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");






/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__["default"])(
  _SettingArray_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _SettingArray_vue_vue_type_template_id_2cf33d54_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _SettingArray_vue_vue_type_template_id_2cf33d54_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "2cf33d54",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/SettingArray.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/SettingArray.vue?vue&type=script&lang=js&":
/*!***************************************************************************!*\
  !*** ./resources/js/components/SettingArray.vue?vue&type=script&lang=js& ***!
  \***************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingArray_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingArray.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingArray.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingArray_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/SettingArray.vue?vue&type=style&index=0&id=2cf33d54&scoped=true&lang=css&":
/*!***********************************************************************************************************!*\
  !*** ./resources/js/components/SettingArray.vue?vue&type=style&index=0&id=2cf33d54&scoped=true&lang=css& ***!
  \***********************************************************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingArray_vue_vue_type_style_index_0_id_2cf33d54_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/style-loader!../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingArray.vue?vue&type=style&index=0&id=2cf33d54&scoped=true&lang=css& */ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingArray.vue?vue&type=style&index=0&id=2cf33d54&scoped=true&lang=css&");
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingArray_vue_vue_type_style_index_0_id_2cf33d54_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingArray_vue_vue_type_style_index_0_id_2cf33d54_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingArray_vue_vue_type_style_index_0_id_2cf33d54_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingArray_vue_vue_type_style_index_0_id_2cf33d54_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__[key]; }) }(__WEBPACK_IMPORT_KEY__));
 /* harmony default export */ __webpack_exports__["default"] = (_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingArray_vue_vue_type_style_index_0_id_2cf33d54_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "./resources/js/components/SettingArray.vue?vue&type=template&id=2cf33d54&scoped=true&":
/*!*********************************************************************************************!*\
  !*** ./resources/js/components/SettingArray.vue?vue&type=template&id=2cf33d54&scoped=true& ***!
  \*********************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingArray_vue_vue_type_template_id_2cf33d54_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingArray.vue?vue&type=template&id=2cf33d54&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingArray.vue?vue&type=template&id=2cf33d54&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingArray_vue_vue_type_template_id_2cf33d54_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingArray_vue_vue_type_template_id_2cf33d54_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/SettingBoolean.vue":
/*!****************************************************!*\
  !*** ./resources/js/components/SettingBoolean.vue ***!
  \****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _SettingBoolean_vue_vue_type_template_id_637b0ae3_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./SettingBoolean.vue?vue&type=template&id=637b0ae3&scoped=true& */ "./resources/js/components/SettingBoolean.vue?vue&type=template&id=637b0ae3&scoped=true&");
/* harmony import */ var _SettingBoolean_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./SettingBoolean.vue?vue&type=script&lang=js& */ "./resources/js/components/SettingBoolean.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _SettingBoolean_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _SettingBoolean_vue_vue_type_template_id_637b0ae3_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _SettingBoolean_vue_vue_type_template_id_637b0ae3_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "637b0ae3",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/SettingBoolean.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/SettingBoolean.vue?vue&type=script&lang=js&":
/*!*****************************************************************************!*\
  !*** ./resources/js/components/SettingBoolean.vue?vue&type=script&lang=js& ***!
  \*****************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingBoolean_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingBoolean.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingBoolean.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingBoolean_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/SettingBoolean.vue?vue&type=template&id=637b0ae3&scoped=true&":
/*!***********************************************************************************************!*\
  !*** ./resources/js/components/SettingBoolean.vue?vue&type=template&id=637b0ae3&scoped=true& ***!
  \***********************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingBoolean_vue_vue_type_template_id_637b0ae3_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingBoolean.vue?vue&type=template&id=637b0ae3&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingBoolean.vue?vue&type=template&id=637b0ae3&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingBoolean_vue_vue_type_template_id_637b0ae3_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingBoolean_vue_vue_type_template_id_637b0ae3_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/SettingDashboardSelect.vue":
/*!************************************************************!*\
  !*** ./resources/js/components/SettingDashboardSelect.vue ***!
  \************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _SettingDashboardSelect_vue_vue_type_template_id_27b9146b_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./SettingDashboardSelect.vue?vue&type=template&id=27b9146b&scoped=true& */ "./resources/js/components/SettingDashboardSelect.vue?vue&type=template&id=27b9146b&scoped=true&");
/* harmony import */ var _SettingDashboardSelect_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./SettingDashboardSelect.vue?vue&type=script&lang=js& */ "./resources/js/components/SettingDashboardSelect.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _SettingDashboardSelect_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _SettingDashboardSelect_vue_vue_type_template_id_27b9146b_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _SettingDashboardSelect_vue_vue_type_template_id_27b9146b_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "27b9146b",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/SettingDashboardSelect.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/SettingDashboardSelect.vue?vue&type=script&lang=js&":
/*!*************************************************************************************!*\
  !*** ./resources/js/components/SettingDashboardSelect.vue?vue&type=script&lang=js& ***!
  \*************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingDashboardSelect_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingDashboardSelect.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingDashboardSelect.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingDashboardSelect_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/SettingDashboardSelect.vue?vue&type=template&id=27b9146b&scoped=true&":
/*!*******************************************************************************************************!*\
  !*** ./resources/js/components/SettingDashboardSelect.vue?vue&type=template&id=27b9146b&scoped=true& ***!
  \*******************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingDashboardSelect_vue_vue_type_template_id_27b9146b_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingDashboardSelect.vue?vue&type=template&id=27b9146b&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingDashboardSelect.vue?vue&type=template&id=27b9146b&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingDashboardSelect_vue_vue_type_template_id_27b9146b_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingDashboardSelect_vue_vue_type_template_id_27b9146b_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/SettingEmail.vue":
/*!**************************************************!*\
  !*** ./resources/js/components/SettingEmail.vue ***!
  \**************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _SettingEmail_vue_vue_type_template_id_0044bd17_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./SettingEmail.vue?vue&type=template&id=0044bd17&scoped=true& */ "./resources/js/components/SettingEmail.vue?vue&type=template&id=0044bd17&scoped=true&");
/* harmony import */ var _SettingEmail_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./SettingEmail.vue?vue&type=script&lang=js& */ "./resources/js/components/SettingEmail.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _SettingEmail_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _SettingEmail_vue_vue_type_template_id_0044bd17_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _SettingEmail_vue_vue_type_template_id_0044bd17_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "0044bd17",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/SettingEmail.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/SettingEmail.vue?vue&type=script&lang=js&":
/*!***************************************************************************!*\
  !*** ./resources/js/components/SettingEmail.vue?vue&type=script&lang=js& ***!
  \***************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingEmail_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingEmail.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingEmail.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingEmail_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/SettingEmail.vue?vue&type=template&id=0044bd17&scoped=true&":
/*!*********************************************************************************************!*\
  !*** ./resources/js/components/SettingEmail.vue?vue&type=template&id=0044bd17&scoped=true& ***!
  \*********************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingEmail_vue_vue_type_template_id_0044bd17_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingEmail.vue?vue&type=template&id=0044bd17&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingEmail.vue?vue&type=template&id=0044bd17&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingEmail_vue_vue_type_template_id_0044bd17_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingEmail_vue_vue_type_template_id_0044bd17_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/SettingInteger.vue":
/*!****************************************************!*\
  !*** ./resources/js/components/SettingInteger.vue ***!
  \****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _SettingInteger_vue_vue_type_template_id_0078a4f9_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./SettingInteger.vue?vue&type=template&id=0078a4f9&scoped=true& */ "./resources/js/components/SettingInteger.vue?vue&type=template&id=0078a4f9&scoped=true&");
/* harmony import */ var _SettingInteger_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./SettingInteger.vue?vue&type=script&lang=js& */ "./resources/js/components/SettingInteger.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _SettingInteger_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _SettingInteger_vue_vue_type_template_id_0078a4f9_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _SettingInteger_vue_vue_type_template_id_0078a4f9_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "0078a4f9",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/SettingInteger.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/SettingInteger.vue?vue&type=script&lang=js&":
/*!*****************************************************************************!*\
  !*** ./resources/js/components/SettingInteger.vue?vue&type=script&lang=js& ***!
  \*****************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingInteger_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingInteger.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingInteger.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingInteger_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/SettingInteger.vue?vue&type=template&id=0078a4f9&scoped=true&":
/*!***********************************************************************************************!*\
  !*** ./resources/js/components/SettingInteger.vue?vue&type=template&id=0078a4f9&scoped=true& ***!
  \***********************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingInteger_vue_vue_type_template_id_0078a4f9_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingInteger.vue?vue&type=template&id=0078a4f9&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingInteger.vue?vue&type=template&id=0078a4f9&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingInteger_vue_vue_type_template_id_0078a4f9_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingInteger_vue_vue_type_template_id_0078a4f9_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/SettingLdapGroups.vue":
/*!*******************************************************!*\
  !*** ./resources/js/components/SettingLdapGroups.vue ***!
  \*******************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _SettingLdapGroups_vue_vue_type_template_id_67b77e70_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./SettingLdapGroups.vue?vue&type=template&id=67b77e70&scoped=true& */ "./resources/js/components/SettingLdapGroups.vue?vue&type=template&id=67b77e70&scoped=true&");
/* harmony import */ var _SettingLdapGroups_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./SettingLdapGroups.vue?vue&type=script&lang=js& */ "./resources/js/components/SettingLdapGroups.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _SettingLdapGroups_vue_vue_type_style_index_0_id_67b77e70_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./SettingLdapGroups.vue?vue&type=style&index=0&id=67b77e70&scoped=true&lang=css& */ "./resources/js/components/SettingLdapGroups.vue?vue&type=style&index=0&id=67b77e70&scoped=true&lang=css&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");






/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__["default"])(
  _SettingLdapGroups_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _SettingLdapGroups_vue_vue_type_template_id_67b77e70_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _SettingLdapGroups_vue_vue_type_template_id_67b77e70_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "67b77e70",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/SettingLdapGroups.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/SettingLdapGroups.vue?vue&type=script&lang=js&":
/*!********************************************************************************!*\
  !*** ./resources/js/components/SettingLdapGroups.vue?vue&type=script&lang=js& ***!
  \********************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingLdapGroups_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingLdapGroups.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingLdapGroups.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingLdapGroups_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/SettingLdapGroups.vue?vue&type=style&index=0&id=67b77e70&scoped=true&lang=css&":
/*!****************************************************************************************************************!*\
  !*** ./resources/js/components/SettingLdapGroups.vue?vue&type=style&index=0&id=67b77e70&scoped=true&lang=css& ***!
  \****************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingLdapGroups_vue_vue_type_style_index_0_id_67b77e70_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/style-loader!../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingLdapGroups.vue?vue&type=style&index=0&id=67b77e70&scoped=true&lang=css& */ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingLdapGroups.vue?vue&type=style&index=0&id=67b77e70&scoped=true&lang=css&");
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingLdapGroups_vue_vue_type_style_index_0_id_67b77e70_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingLdapGroups_vue_vue_type_style_index_0_id_67b77e70_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingLdapGroups_vue_vue_type_style_index_0_id_67b77e70_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingLdapGroups_vue_vue_type_style_index_0_id_67b77e70_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__[key]; }) }(__WEBPACK_IMPORT_KEY__));
 /* harmony default export */ __webpack_exports__["default"] = (_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingLdapGroups_vue_vue_type_style_index_0_id_67b77e70_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "./resources/js/components/SettingLdapGroups.vue?vue&type=template&id=67b77e70&scoped=true&":
/*!**************************************************************************************************!*\
  !*** ./resources/js/components/SettingLdapGroups.vue?vue&type=template&id=67b77e70&scoped=true& ***!
  \**************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingLdapGroups_vue_vue_type_template_id_67b77e70_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingLdapGroups.vue?vue&type=template&id=67b77e70&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingLdapGroups.vue?vue&type=template&id=67b77e70&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingLdapGroups_vue_vue_type_template_id_67b77e70_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingLdapGroups_vue_vue_type_template_id_67b77e70_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/SettingNull.vue":
/*!*************************************************!*\
  !*** ./resources/js/components/SettingNull.vue ***!
  \*************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _SettingNull_vue_vue_type_template_id_37a8c75c_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./SettingNull.vue?vue&type=template&id=37a8c75c&scoped=true& */ "./resources/js/components/SettingNull.vue?vue&type=template&id=37a8c75c&scoped=true&");
/* harmony import */ var _SettingNull_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./SettingNull.vue?vue&type=script&lang=js& */ "./resources/js/components/SettingNull.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _SettingNull_vue_vue_type_style_index_0_id_37a8c75c_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./SettingNull.vue?vue&type=style&index=0&id=37a8c75c&scoped=true&lang=css& */ "./resources/js/components/SettingNull.vue?vue&type=style&index=0&id=37a8c75c&scoped=true&lang=css&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");






/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__["default"])(
  _SettingNull_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _SettingNull_vue_vue_type_template_id_37a8c75c_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _SettingNull_vue_vue_type_template_id_37a8c75c_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "37a8c75c",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/SettingNull.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/SettingNull.vue?vue&type=script&lang=js&":
/*!**************************************************************************!*\
  !*** ./resources/js/components/SettingNull.vue?vue&type=script&lang=js& ***!
  \**************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingNull_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingNull.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingNull.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingNull_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/SettingNull.vue?vue&type=style&index=0&id=37a8c75c&scoped=true&lang=css&":
/*!**********************************************************************************************************!*\
  !*** ./resources/js/components/SettingNull.vue?vue&type=style&index=0&id=37a8c75c&scoped=true&lang=css& ***!
  \**********************************************************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingNull_vue_vue_type_style_index_0_id_37a8c75c_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/style-loader!../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingNull.vue?vue&type=style&index=0&id=37a8c75c&scoped=true&lang=css& */ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingNull.vue?vue&type=style&index=0&id=37a8c75c&scoped=true&lang=css&");
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingNull_vue_vue_type_style_index_0_id_37a8c75c_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingNull_vue_vue_type_style_index_0_id_37a8c75c_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingNull_vue_vue_type_style_index_0_id_37a8c75c_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingNull_vue_vue_type_style_index_0_id_37a8c75c_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__[key]; }) }(__WEBPACK_IMPORT_KEY__));
 /* harmony default export */ __webpack_exports__["default"] = (_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingNull_vue_vue_type_style_index_0_id_37a8c75c_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "./resources/js/components/SettingNull.vue?vue&type=template&id=37a8c75c&scoped=true&":
/*!********************************************************************************************!*\
  !*** ./resources/js/components/SettingNull.vue?vue&type=template&id=37a8c75c&scoped=true& ***!
  \********************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingNull_vue_vue_type_template_id_37a8c75c_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingNull.vue?vue&type=template&id=37a8c75c&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingNull.vue?vue&type=template&id=37a8c75c&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingNull_vue_vue_type_template_id_37a8c75c_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingNull_vue_vue_type_template_id_37a8c75c_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/SettingPassword.vue":
/*!*****************************************************!*\
  !*** ./resources/js/components/SettingPassword.vue ***!
  \*****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _SettingPassword_vue_vue_type_template_id_6e386bf0_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./SettingPassword.vue?vue&type=template&id=6e386bf0&scoped=true& */ "./resources/js/components/SettingPassword.vue?vue&type=template&id=6e386bf0&scoped=true&");
/* harmony import */ var _SettingPassword_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./SettingPassword.vue?vue&type=script&lang=js& */ "./resources/js/components/SettingPassword.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _SettingPassword_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _SettingPassword_vue_vue_type_template_id_6e386bf0_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _SettingPassword_vue_vue_type_template_id_6e386bf0_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "6e386bf0",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/SettingPassword.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/SettingPassword.vue?vue&type=script&lang=js&":
/*!******************************************************************************!*\
  !*** ./resources/js/components/SettingPassword.vue?vue&type=script&lang=js& ***!
  \******************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingPassword_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingPassword.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingPassword.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingPassword_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/SettingPassword.vue?vue&type=template&id=6e386bf0&scoped=true&":
/*!************************************************************************************************!*\
  !*** ./resources/js/components/SettingPassword.vue?vue&type=template&id=6e386bf0&scoped=true& ***!
  \************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingPassword_vue_vue_type_template_id_6e386bf0_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingPassword.vue?vue&type=template&id=6e386bf0&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingPassword.vue?vue&type=template&id=6e386bf0&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingPassword_vue_vue_type_template_id_6e386bf0_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingPassword_vue_vue_type_template_id_6e386bf0_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/SettingSelect.vue":
/*!***************************************************!*\
  !*** ./resources/js/components/SettingSelect.vue ***!
  \***************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _SettingSelect_vue_vue_type_template_id_59a5b911_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./SettingSelect.vue?vue&type=template&id=59a5b911&scoped=true& */ "./resources/js/components/SettingSelect.vue?vue&type=template&id=59a5b911&scoped=true&");
/* harmony import */ var _SettingSelect_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./SettingSelect.vue?vue&type=script&lang=js& */ "./resources/js/components/SettingSelect.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _SettingSelect_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _SettingSelect_vue_vue_type_template_id_59a5b911_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _SettingSelect_vue_vue_type_template_id_59a5b911_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "59a5b911",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/SettingSelect.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/SettingSelect.vue?vue&type=script&lang=js&":
/*!****************************************************************************!*\
  !*** ./resources/js/components/SettingSelect.vue?vue&type=script&lang=js& ***!
  \****************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingSelect_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingSelect.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingSelect.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingSelect_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/SettingSelect.vue?vue&type=template&id=59a5b911&scoped=true&":
/*!**********************************************************************************************!*\
  !*** ./resources/js/components/SettingSelect.vue?vue&type=template&id=59a5b911&scoped=true& ***!
  \**********************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingSelect_vue_vue_type_template_id_59a5b911_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingSelect.vue?vue&type=template&id=59a5b911&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingSelect.vue?vue&type=template&id=59a5b911&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingSelect_vue_vue_type_template_id_59a5b911_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingSelect_vue_vue_type_template_id_59a5b911_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/SettingSnmp3auth.vue":
/*!******************************************************!*\
  !*** ./resources/js/components/SettingSnmp3auth.vue ***!
  \******************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _SettingSnmp3auth_vue_vue_type_template_id_4a841d50_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./SettingSnmp3auth.vue?vue&type=template&id=4a841d50&scoped=true& */ "./resources/js/components/SettingSnmp3auth.vue?vue&type=template&id=4a841d50&scoped=true&");
/* harmony import */ var _SettingSnmp3auth_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./SettingSnmp3auth.vue?vue&type=script&lang=js& */ "./resources/js/components/SettingSnmp3auth.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _SettingSnmp3auth_vue_vue_type_style_index_0_id_4a841d50_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./SettingSnmp3auth.vue?vue&type=style&index=0&id=4a841d50&scoped=true&lang=css& */ "./resources/js/components/SettingSnmp3auth.vue?vue&type=style&index=0&id=4a841d50&scoped=true&lang=css&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");






/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__["default"])(
  _SettingSnmp3auth_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _SettingSnmp3auth_vue_vue_type_template_id_4a841d50_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _SettingSnmp3auth_vue_vue_type_template_id_4a841d50_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "4a841d50",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/SettingSnmp3auth.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/SettingSnmp3auth.vue?vue&type=script&lang=js&":
/*!*******************************************************************************!*\
  !*** ./resources/js/components/SettingSnmp3auth.vue?vue&type=script&lang=js& ***!
  \*******************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingSnmp3auth_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingSnmp3auth.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingSnmp3auth.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingSnmp3auth_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/SettingSnmp3auth.vue?vue&type=style&index=0&id=4a841d50&scoped=true&lang=css&":
/*!***************************************************************************************************************!*\
  !*** ./resources/js/components/SettingSnmp3auth.vue?vue&type=style&index=0&id=4a841d50&scoped=true&lang=css& ***!
  \***************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingSnmp3auth_vue_vue_type_style_index_0_id_4a841d50_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/style-loader!../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingSnmp3auth.vue?vue&type=style&index=0&id=4a841d50&scoped=true&lang=css& */ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingSnmp3auth.vue?vue&type=style&index=0&id=4a841d50&scoped=true&lang=css&");
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingSnmp3auth_vue_vue_type_style_index_0_id_4a841d50_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingSnmp3auth_vue_vue_type_style_index_0_id_4a841d50_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingSnmp3auth_vue_vue_type_style_index_0_id_4a841d50_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingSnmp3auth_vue_vue_type_style_index_0_id_4a841d50_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__[key]; }) }(__WEBPACK_IMPORT_KEY__));
 /* harmony default export */ __webpack_exports__["default"] = (_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingSnmp3auth_vue_vue_type_style_index_0_id_4a841d50_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "./resources/js/components/SettingSnmp3auth.vue?vue&type=template&id=4a841d50&scoped=true&":
/*!*************************************************************************************************!*\
  !*** ./resources/js/components/SettingSnmp3auth.vue?vue&type=template&id=4a841d50&scoped=true& ***!
  \*************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingSnmp3auth_vue_vue_type_template_id_4a841d50_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingSnmp3auth.vue?vue&type=template&id=4a841d50&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingSnmp3auth.vue?vue&type=template&id=4a841d50&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingSnmp3auth_vue_vue_type_template_id_4a841d50_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingSnmp3auth_vue_vue_type_template_id_4a841d50_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/SettingText.vue":
/*!*************************************************!*\
  !*** ./resources/js/components/SettingText.vue ***!
  \*************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _SettingText_vue_vue_type_template_id_6d770402_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./SettingText.vue?vue&type=template&id=6d770402&scoped=true& */ "./resources/js/components/SettingText.vue?vue&type=template&id=6d770402&scoped=true&");
/* harmony import */ var _SettingText_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./SettingText.vue?vue&type=script&lang=js& */ "./resources/js/components/SettingText.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _SettingText_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _SettingText_vue_vue_type_template_id_6d770402_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _SettingText_vue_vue_type_template_id_6d770402_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "6d770402",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/SettingText.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/SettingText.vue?vue&type=script&lang=js&":
/*!**************************************************************************!*\
  !*** ./resources/js/components/SettingText.vue?vue&type=script&lang=js& ***!
  \**************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingText_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingText.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingText.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingText_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/SettingText.vue?vue&type=template&id=6d770402&scoped=true&":
/*!********************************************************************************************!*\
  !*** ./resources/js/components/SettingText.vue?vue&type=template&id=6d770402&scoped=true& ***!
  \********************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingText_vue_vue_type_template_id_6d770402_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./SettingText.vue?vue&type=template&id=6d770402&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/SettingText.vue?vue&type=template&id=6d770402&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingText_vue_vue_type_template_id_6d770402_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_SettingText_vue_vue_type_template_id_6d770402_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/Tab.vue":
/*!*****************************************!*\
  !*** ./resources/js/components/Tab.vue ***!
  \*****************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Tab_vue_vue_type_template_id_8dbef60c_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Tab.vue?vue&type=template&id=8dbef60c&scoped=true& */ "./resources/js/components/Tab.vue?vue&type=template&id=8dbef60c&scoped=true&");
/* harmony import */ var _Tab_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Tab.vue?vue&type=script&lang=js& */ "./resources/js/components/Tab.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _Tab_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _Tab_vue_vue_type_template_id_8dbef60c_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _Tab_vue_vue_type_template_id_8dbef60c_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "8dbef60c",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/Tab.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/Tab.vue?vue&type=script&lang=js&":
/*!******************************************************************!*\
  !*** ./resources/js/components/Tab.vue?vue&type=script&lang=js& ***!
  \******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Tab_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./Tab.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Tab.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Tab_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/Tab.vue?vue&type=template&id=8dbef60c&scoped=true&":
/*!************************************************************************************!*\
  !*** ./resources/js/components/Tab.vue?vue&type=template&id=8dbef60c&scoped=true& ***!
  \************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Tab_vue_vue_type_template_id_8dbef60c_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./Tab.vue?vue&type=template&id=8dbef60c&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Tab.vue?vue&type=template&id=8dbef60c&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Tab_vue_vue_type_template_id_8dbef60c_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Tab_vue_vue_type_template_id_8dbef60c_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/Tabs.vue":
/*!******************************************!*\
  !*** ./resources/js/components/Tabs.vue ***!
  \******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Tabs_vue_vue_type_template_id_6e9bbb69_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Tabs.vue?vue&type=template&id=6e9bbb69&scoped=true& */ "./resources/js/components/Tabs.vue?vue&type=template&id=6e9bbb69&scoped=true&");
/* harmony import */ var _Tabs_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Tabs.vue?vue&type=script&lang=js& */ "./resources/js/components/Tabs.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _Tabs_vue_vue_type_style_index_0_id_6e9bbb69_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Tabs.vue?vue&type=style&index=0&id=6e9bbb69&scoped=true&lang=css& */ "./resources/js/components/Tabs.vue?vue&type=style&index=0&id=6e9bbb69&scoped=true&lang=css&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");






/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__["default"])(
  _Tabs_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _Tabs_vue_vue_type_template_id_6e9bbb69_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _Tabs_vue_vue_type_template_id_6e9bbb69_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "6e9bbb69",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/Tabs.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/Tabs.vue?vue&type=script&lang=js&":
/*!*******************************************************************!*\
  !*** ./resources/js/components/Tabs.vue?vue&type=script&lang=js& ***!
  \*******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Tabs_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./Tabs.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Tabs.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Tabs_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/Tabs.vue?vue&type=style&index=0&id=6e9bbb69&scoped=true&lang=css&":
/*!***************************************************************************************************!*\
  !*** ./resources/js/components/Tabs.vue?vue&type=style&index=0&id=6e9bbb69&scoped=true&lang=css& ***!
  \***************************************************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_Tabs_vue_vue_type_style_index_0_id_6e9bbb69_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/style-loader!../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./Tabs.vue?vue&type=style&index=0&id=6e9bbb69&scoped=true&lang=css& */ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Tabs.vue?vue&type=style&index=0&id=6e9bbb69&scoped=true&lang=css&");
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_Tabs_vue_vue_type_style_index_0_id_6e9bbb69_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_Tabs_vue_vue_type_style_index_0_id_6e9bbb69_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_Tabs_vue_vue_type_style_index_0_id_6e9bbb69_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_Tabs_vue_vue_type_style_index_0_id_6e9bbb69_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__[key]; }) }(__WEBPACK_IMPORT_KEY__));
 /* harmony default export */ __webpack_exports__["default"] = (_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_Tabs_vue_vue_type_style_index_0_id_6e9bbb69_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "./resources/js/components/Tabs.vue?vue&type=template&id=6e9bbb69&scoped=true&":
/*!*************************************************************************************!*\
  !*** ./resources/js/components/Tabs.vue?vue&type=template&id=6e9bbb69&scoped=true& ***!
  \*************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Tabs_vue_vue_type_template_id_6e9bbb69_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./Tabs.vue?vue&type=template&id=6e9bbb69&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/Tabs.vue?vue&type=template&id=6e9bbb69&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Tabs_vue_vue_type_template_id_6e9bbb69_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Tabs_vue_vue_type_template_id_6e9bbb69_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/TransitionCollapseHeight.vue":
/*!**************************************************************!*\
  !*** ./resources/js/components/TransitionCollapseHeight.vue ***!
  \**************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _TransitionCollapseHeight_vue_vue_type_template_id_41d51ed4_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./TransitionCollapseHeight.vue?vue&type=template&id=41d51ed4&scoped=true& */ "./resources/js/components/TransitionCollapseHeight.vue?vue&type=template&id=41d51ed4&scoped=true&");
/* harmony import */ var _TransitionCollapseHeight_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TransitionCollapseHeight.vue?vue&type=script&lang=js& */ "./resources/js/components/TransitionCollapseHeight.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _TransitionCollapseHeight_vue_vue_type_style_index_0_id_41d51ed4_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./TransitionCollapseHeight.vue?vue&type=style&index=0&id=41d51ed4&scoped=true&lang=css& */ "./resources/js/components/TransitionCollapseHeight.vue?vue&type=style&index=0&id=41d51ed4&scoped=true&lang=css&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");






/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__["default"])(
  _TransitionCollapseHeight_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _TransitionCollapseHeight_vue_vue_type_template_id_41d51ed4_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _TransitionCollapseHeight_vue_vue_type_template_id_41d51ed4_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "41d51ed4",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/TransitionCollapseHeight.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/TransitionCollapseHeight.vue?vue&type=script&lang=js&":
/*!***************************************************************************************!*\
  !*** ./resources/js/components/TransitionCollapseHeight.vue?vue&type=script&lang=js& ***!
  \***************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TransitionCollapseHeight_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./TransitionCollapseHeight.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/TransitionCollapseHeight.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TransitionCollapseHeight_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/TransitionCollapseHeight.vue?vue&type=style&index=0&id=41d51ed4&scoped=true&lang=css&":
/*!***********************************************************************************************************************!*\
  !*** ./resources/js/components/TransitionCollapseHeight.vue?vue&type=style&index=0&id=41d51ed4&scoped=true&lang=css& ***!
  \***********************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_TransitionCollapseHeight_vue_vue_type_style_index_0_id_41d51ed4_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/style-loader!../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./TransitionCollapseHeight.vue?vue&type=style&index=0&id=41d51ed4&scoped=true&lang=css& */ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/TransitionCollapseHeight.vue?vue&type=style&index=0&id=41d51ed4&scoped=true&lang=css&");
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_TransitionCollapseHeight_vue_vue_type_style_index_0_id_41d51ed4_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_TransitionCollapseHeight_vue_vue_type_style_index_0_id_41d51ed4_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_TransitionCollapseHeight_vue_vue_type_style_index_0_id_41d51ed4_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_TransitionCollapseHeight_vue_vue_type_style_index_0_id_41d51ed4_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__[key]; }) }(__WEBPACK_IMPORT_KEY__));
 /* harmony default export */ __webpack_exports__["default"] = (_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_TransitionCollapseHeight_vue_vue_type_style_index_0_id_41d51ed4_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "./resources/js/components/TransitionCollapseHeight.vue?vue&type=template&id=41d51ed4&scoped=true&":
/*!*********************************************************************************************************!*\
  !*** ./resources/js/components/TransitionCollapseHeight.vue?vue&type=template&id=41d51ed4&scoped=true& ***!
  \*********************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_TransitionCollapseHeight_vue_vue_type_template_id_41d51ed4_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./TransitionCollapseHeight.vue?vue&type=template&id=41d51ed4&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/TransitionCollapseHeight.vue?vue&type=template&id=41d51ed4&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_TransitionCollapseHeight_vue_vue_type_template_id_41d51ed4_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_TransitionCollapseHeight_vue_vue_type_template_id_41d51ed4_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/plugins/i18n.js":
/*!**************************************!*\
  !*** ./resources/js/plugins/i18n.js ***!
  \**************************************/
/*! exports provided: i18n */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "i18n", function() { return i18n; });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.common.js");
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var vue_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! vue-i18n */ "./node_modules/vue-i18n/dist/vue-i18n.esm.js");
/* harmony import */ var _vue_i18n_locales_generated_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../vue-i18n-locales.generated.js */ "./resources/js/vue-i18n-locales.generated.js");
/*
 * i18n.js
 *
 * Load vue.js i18n support
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

 // import en from '../lang/en.js'


vue__WEBPACK_IMPORTED_MODULE_0___default.a.use(vue_i18n__WEBPACK_IMPORTED_MODULE_1__["default"]);
var i18n = new vue_i18n__WEBPACK_IMPORTED_MODULE_1__["default"]({
  locale: document.querySelector('html').getAttribute('lang'),
  fallbackLocale: 'en',
  silentFallbackWarn: true,
  silentTranslationWarn: true,
  messages: _vue_i18n_locales_generated_js__WEBPACK_IMPORTED_MODULE_2__["default"]
}); // re-enable after vue-i8ln-generator is working for split locales

/*
const loadedLanguages = ['en']; // our default language that is preloaded

function setI18nLanguage (lang) {
    i18n.locale = lang
    axios.defaults.headers.common['Accept-Language'] = lang
    document.querySelector('html').setAttribute('lang', lang)
    return lang
}

export function loadLanguageAsync(lang) {
    // If the same language
    if (i18n.locale === lang) {
        return Promise.resolve(setI18nLanguage(lang))
    }

    // If the language was already loaded
    if (loadedLanguages.includes(lang)) {
        return Promise.resolve(setI18nLanguage(lang))
    }

    // If the language hasn't been loaded yet
    return import(`../lang/${lang}.js`).then(
        messages => {
            i18n.setLocaleMessage(lang, messages.default)
            loadedLanguages.push(lang)
            return setI18nLanguage(lang)
        }
    )
}
*/

/***/ }),

/***/ "./resources/js/vue-i18n-locales.generated.js":
/*!****************************************************!*\
  !*** ./resources/js/vue-i18n-locales.generated.js ***!
  \****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ({
  "de": {
    "Docs": "Dokumentation",
    "Close": "Schließen",
    "LibreNMS is an autodiscovering PHP/MySQL-based network monitoring system": "LibreNMS ist ein selbstdetektierendes PHP/MySQL basierendes Netzwerkmonitoring System",
    "Packages": "Pakete",
    "Disabled": "gesperrt",
    "Version": "Version",
    "Database Schema": "Datenbank Schema",
    "Web Server": "Webserver",
    "LibreNMS is a community-based project": "LibreNMS ist ein Open Source Projekt",
    "Please feel free to join us and contribute code, documentation, and bug reports:": "Sie sind herzlich dazu eingeladen Quellcode, Dokumentation und Fehlerreports hinzuzufügen",
    "Web site": "Webseite",
    "Bug tracker": "Bugtracker",
    "Community Forum": "Community Forum",
    "Changelog": "Changelog",
    "Local git log": "lokales Git Log",
    "Contributors": "Mitwirkende",
    "Acknowledgements": "Anerkennungen",
    "Opt in to send anonymous usage statistics to LibreNMS?": "Senden von anonymen Statistiken zu LibreNMS",
    "Online stats:": "Online Statistiken",
    "Clear remote stats": "lösche entfernte Statistiken",
    "IPv4 Addresses": "IPv4 Adressen",
    "IPv4 Networks": "IPv4 Netzwerke",
    "IPv6 Addresses": "IPv6 Adressen",
    "IPv6 Networks": "IPv6 Netzwerke",
    "Processors": "Prozessoren",
    "Applications": "Anwendungen",
    "Disk I/O": "Festplatten I/O",
    "Syslog Entries": "Syslog Einträge",
    "Eventlog Entries": "Eventlog Einträge",
    "Sensors": "Sensoren",
    "Wireless Sensors": "WLAN Sensoren",
    "Toner": "Toner",
    "License": "Lizenz",
    "Shutdown": "abschalten",
    "Select Devices": "ausgewählte Geräte",
    "Dynamic": "dynamisch",
    "Static": "statisch",
    "Define Rules": "definiere Regeln",
    "Create Device Group": "erstelle Gerätegruppen",
    "Edit Device Group": "bearbeite Gerätegruppen",
    "New Device Group": "neue Gerätegruppen",
    "Pattern": "Pattern",
    "Type": "Typ",
    "Name": "Name",
    "User Preferences": "Benutzereinstellungen",
    "Global Administrative Access": "administrativer Zugang",
    "Device Permissions": "Geräterechte",
    "Preferences": "Einstellungen",
    "Language": "Sprache",
    "Change Password": "ändere Passwort",
    "Verify New Password": "bestätige neues Passwort",
    "Peering + Transit": "Peering und Transit",
    "FDB Tables": "FDB Tabellen",
    "ARP Tables": "ARP Tabellen",
    "MAC Address": "MAC Adresse",
    "IPv6 Address": "IPv6 Adresse",
    "IPv4 Address": "IPv4 Adresse",
    "Package": "Package",
    "Virtual Machines": "virtuelle Maschine",
    "Device Groups": "Gerätegruppe",
    "Register": "Registrierung",
    "Overview": "Übersicht",
    "Maps": "Karten",
    "Availability": "Verfügbarkeit",
    "Device Groups Maps": "Gerätegruppenkarte",
    "Geographical": "geographisch",
    "Plugins": "Plugins",
    "Plugin Admin": "Plugin Administrator",
    "Tools": "Werkzeuge",
    "Eventlog": "Ereignisanzeige",
    "Inventory": "Inhaltsverzeichnis",
    "MIB definitions": "MIB Definitionen",
    "No devices": "keine Geräte",
    "MIB associations": "MIB Zuordnungen",
    "Manage Groups": "verwalte Gruppen",
    "Device Dependencies": "Geräteabhängigkeiten",
    "Add Device": "Gerät hinzufügen",
    "Delete Device": "Geräte entfernen",
    "All Services": "Alle Dienste",
    "Add Service": "Dienst hinzufügen",
    "Traffic Bills": "Traffic Rechnungen",
    "Pseudowires": "Pseudokabel",
    "Customers": "Kunden",
    "Transit": "Transit",
    "Core": "Kern",
    "Alerts": "Alarme",
    "Deleted": "gelöscht",
    "Health": "Gesundheit",
    "Memory": "Arbeitsspeicher",
    "Processor": "Prozessoren",
    "Storage": "Speicher",
    "Wireless": "WLAN",
    "Apps": "Software",
    "Routing": "Routing",
    "Alerted": "alarmiert",
    "Notifications": "Benachrichtigungen",
    "Alert History": "Alarm Geschichte",
    "Statistics": "Statistiken",
    "Alert Rules": "Alarm Regeln",
    "Scheduled Maintenance": "geplante Wartung",
    "Alert Templates": "Alarm Vorlagen",
    "Alert Transports": "Alarm Übertragungswege",
    "My Settings": "Meine Einstellungen",
    "Settings": "Einstellungen",
    "Global Settings": "globale Einstellungen",
    "Validate Config": "überprüfe Konfiguration",
    "Auth History": "Authentifizierungsgeschichte",
    "Peering": "Peering",
    "API Settings": "API Einstellungen",
    "API Docs": "API Dokumentation",
    "The {attribute} must a valid IP address/network or hostname.": "{attribute} muss eine gültige IP Adresse/Netzwerk oder Hostname sein",
    "Never polled": "nie geprüft",
    "This indicates the most likely endpoint switchport": "Dies indiziert den höchstwahrscheinlichsen Endpunkt des Switchports",
    "Two-Factor unlocked.": "Zweifaktor entsperrt",
    "Failed to unlock Two-Factor.": "Entsperrung von Zweifaktor schlug fehl",
    "Two-Factor removed.": "Zweifaktor wurde entfernt",
    "Failed to remove Two-Factor.": "Das Entfernen von Zweifaktor schlug fehl",
    "TwoFactor auth removed.": "Zweifaktor Authentifizierung entfernt",
    "Too many two-factor failures, please contact administrator.": "Zu viele Zweifaktor Fehler, bitte kontaktieren Sie den Administrator",
    "Too many two-factor failures, please wait {time} seconds": "Zu viele Zweifaktor Fehler, bitte warten Sie {time} Sekunden",
    "No Two-Factor Token entered.": "Keine Zweifaktor Token verbucht",
    "No Two-Factor settings, how did you get here?": "Keine Zweifaktor Einstellungen, wie sind sie hierher gekommen",
    "Wrong Two-Factor Token.": "falsche Zweifaktor Token",
    "TwoFactor auth added.": "Zweifaktor Authentifizierung hinzugefügt",
    "User {username} created": "Benutzer {username} erstellt",
    "Failed to create user": "Erstellung des Benutzers schlug fehl",
    "Updated dashboard for {username}": "Dashboard für {username} upgedated",
    "User {username} updated": "Benutzer {username} upgedated",
    "Failed to update user {username}": "Update von Benutzer {username} fehlgeschlagen",
    "User {username} deleted.": "Benutzer {username} gelöscht",
    "Device does not exist": "Gerät existiert nicht",
    "Port does not exist": "Port existiert nicht",
    "App does not exist": "Anwendung existiert nicht",
    "Bill does not exist": "Rechnung existiert nicht",
    "Munin plugin does not exist": "Munin Erweiterung existiert nicht",
    "Ok": "OK",
    "Warning": "Warnung",
    "Critical": "kritisch",
    "Existing password did not match": "existierendes Passwort stimmt nicht überein",
    "The {attribute} field is required.": "{attribute} ist benötigt",
    "Edit User": "bearbeite Benutzer",
    "Unlock": "entsperrend",
    "User exceeded failures": "Benutzer überschreitet die Fehler",
    "Disable TwoFactor": "sperre Zweifaktor",
    "No TwoFactor key generated for this user, Nothing to do.": "keine Zweifaktor Schlüssel erstellt für Benutzer, nichts zu tun.",
    "Save": "Speichern",
    "Cancel": "Abbrechen",
    "Unlocked Two Factor.": "entsperre Zweifaktor",
    "Failed to unlock Two Factor": "entsperren von Zweifaktor schlug fehl",
    "Removed Two Factor.": "entferne Zweifaktor",
    "Failed to remove Two Factor": "entfernen von Zweifaktor schlug fehl",
    "Real Name": "Realname",
    "Email": "E-Mail",
    "Description": "Beschreibung",
    "Level": "Level",
    "Normal": "Normal",
    "Global Read": "globales Lesen",
    "Admin": "Admin",
    "Demo": "Demo",
    "Dashboard": "Dashboard",
    "Password": "Password",
    "Current Password": "momentanes Passwort",
    "New Password": "neues Passwort",
    "Confirm Password": "bestätige Passwort",
    "Can Modify Password": "darf Passwort ändern",
    "Create User": "erstelle Benutzer",
    "Username": "Benutzername",
    "Manage Users": "verwalte Benutzer",
    "ID": "ID",
    "Access": "Zugriff",
    "Auth": "Authentifizierung",
    "Actions": "Aktionen",
    "Edit": "Bearbeitung",
    "Delete": "Löschen",
    "Manage Access": "verwalte Zugriff",
    "Add User": "Benutzer hinzufügen",
    "Are you sure you want to delete ": "Sind sie sicher zu löschen",
    "The user could not be deleted": "Der Benutzer kann nicht gelöscht werden",
    "Whoops, the web server could not write required files to the filesystem.": "Ooops, der Webserver konnte die benötigten Dateien nicht auf das Dateisystem schreiben",
    "Running the following commands will fix the issue most of the time:": "Ausführen der folgenden Befehle löst meistens die Probleme:",
    "Whoops, looks like something went wrong. Check your librenms.log.": "Ooops, sieht so aus als wäre etwas schief gegangen. Bitte prüfen Sie die librenms.log",
    "Public Devices": "öffentliche Geräte",
    "System Status": "System Status",
    "Logon": "Anmeldung",
    "Device": "Geräte",
    "Platform": "Plattform",
    "Uptime": "Laufzeit",
    "Location": "Standort",
    "Status": "Status",
    "Remember Me": "merken",
    "Login": "Anmeldung",
    "Please enter auth token": "Bitte geben Sie das Authentifizierungstoken ein",
    "Submit": "Bestätigen",
    "Logout": "Abmelden",
    "Locations": "Standorte",
    "Coordinates": "Koordinaten",
    "Devices": "Geräte",
    "Network": "Netzwerk",
    "Servers": "Server",
    "Firewalls": "Firewalls",
    "Down": "aus",
    "Save changes": "Änderungen speichern",
    "N/A": "k.a.",
    "Location must have devices to show graphs": "Der Standort muss Geräte haben um Graphen anzuzeigen",
    "Traffic": "Datendurchsatz",
    "Cannot delete locations used by devices": "Kann keinen Standort löschen, welcher von Geräten genutzt wird",
    "Location deleted": "Standort gelöscht",
    "Failed to delete location": "Löschen des Standorts schlug fehl",
    "Timestamp": "Zeitstempel",
    "Source": "Quelle",
    "Message": "Nachricht",
    "Facility": "Standort",
    "Total hosts": "Gesamtzahl Geräte",
    "ignored": "ignoriert",
    "disabled": "gesperrt",
    "up": "erreichbar",
    "warn": "Warnung",
    "down": "unerreichbar",
    "Total services": "Gesamtzahl Anwendungen",
    "Widget title": "Widget Titel",
    "Default Title": "Standardtitel",
    "Columns": "Spalten",
    "Markers": "Markierungen",
    "Ports": "Ports",
    "Resolution": "Auflösung",
    "Countries": "Länder",
    "Provinces": "Gebiete",
    "Metros": "Metros",
    "Region": "Region",
    "Help": "Hilfe",
    "Stream": "Stream",
    "All Messages": "alle Meldungen",
    "All Devices": "alle Geräte",
    "Page Size": "Seitegröße",
    "Time Range": "Zeitbereich",
    "Search all time": "kein Zeitlimit",
    "Search last 5 minutes": "letzten 5 Minuten",
    "Search last 15 minutes": "letzten 15 Minuten",
    "Search last 30 minutes": "letzten 30 Minuten",
    "Search last 1 hour": "letzte 1 Stunde",
    "Search last 2 hours": "letzten 2 Stunden",
    "Search last 8 hours": "letzten 8 Stunden",
    "Search last 1 day": "letzten 1 Tag",
    "Search last 2 days": "letzten 2 Tage",
    "Search last 5 days": "letzten 5 Tage",
    "Search last 7 days": "letzten 7 Tage",
    "Search last 14 days": "letzten 14 Tage",
    "Search last 30 days": "letzten 30 Tage",
    "Custom title": "individueller Titel",
    "Initial Latitude": "initialer Latitude",
    "ie. 51.4800 for Greenwich": "z.B. 51.4800 für Greenwich",
    "Initial Longitude": "initialer Longitude",
    "ie. 0 for Greenwich": "z.B. 0 für Greenwich",
    "Initial Zoom": "initialer Zoom",
    "Grouping radius": "Guppierungsradius",
    "default 80": "Standard 80",
    "Show devices": "Zeige Geräte",
    "Up + Down": "erreichbar und nicht erreichbar",
    "Up": "erreichbar",
    "Show Services": "zeige Anwendungen",
    "no": "nein",
    "yes": "ja",
    "Show Port Errors": "zeige Portfehler",
    "Notes": "Notizen",
    "Custom title for widget": "angepassert Titel für Widget",
    "Display type": "Anzeigetyp",
    "boxes": "Box",
    "compact": "Kompakt",
    "Uniform Tiles": "gleichmässige Kacheln",
    "Tile size": "Kachelgröße",
    "Disabled/ignored": "gesperrt/ignoriert",
    "Show": "Anzeigen",
    "Hide": "Verbergen",
    "Mode select": "Modus Auswahl",
    "only devices": "Nur Geräte",
    "only services": "Nur Anwendungen",
    "devices and services": "Geräte und Anwendungen",
    "Order By": "sortieren nach",
    "Hostname": "Hostname",
    "Device group": "Gerätegruppe",
    "Automatic Title": "automatischer Titel",
    "Graph type": "Graphentyp",
    "Select a graph": "wähle einen Graph",
    "Show legend": "zeige Legende an",
    "Date range": "Datumsbereich",
    "One Hour": "eine Stunde",
    "Four Hours": "vier Stunden",
    "Six Hours": "sechs Stunden",
    "Twelve Hours": "zwölf Stunden",
    "One Day": "ein Tag",
    "One Week": "eine Woche",
    "Two Weeks": "zwei Wochen",
    "One Month": "ein Monat",
    "Two Months": "zwei Monate",
    "Three Months": "drei Monate",
    "One Year": "ein Jahr",
    "Two Years": "zwei Jahre",
    "Select a device": "wähle ein Gerät",
    "Port": "Port",
    "Select a port": "wählen einen Port",
    "Application": "Anwendung",
    "Select an application": "wähle einen Port",
    "Munin plugin": "Munin 外掛程式",
    "Select a Munin plugin": "wähle ein Munin Plugin",
    "Bill": "Rechnung",
    "Select a bill": "wähle eine Rechnung",
    "Custom Aggregator(s)": "angepasster Aggregator(s)",
    "Select or add one or more": "wähle oder hinzufüge einen oder mehrere",
    "Select one or more": "wähle einen oder mehrere",
    "Top query": "Top Auswahl",
    "Response time": "Antwortzeit",
    "Poller duration": "Poller Laufzeit",
    "Processor load": "Prozessorlast",
    "Memory usage": "Speichernutzung",
    "Disk usage": "Festplattennutzung",
    "Sort order": "Sortierreihenfolge",
    "Ascending": "aufsteigend",
    "Descending": "absteigend",
    "Number of Devices": "Anzahl an Geräten",
    "Last Polled (minutes)": "zuletzt geprüft (Minuten)",
    "Image URL": "Bild URL",
    "Target URL": "Ziel URL",
    "Show acknowledged": "zeige bestätigte",
    "not filtered": "nicht gefiltert",
    "show only acknowledged": "zeige nur bestätigte",
    "hide acknowledged": "verstecke bestätigte",
    "Show only fired": "zeige nur abgeschossene",
    "show only fired alerts": "zeige nur abgeschossene Alarme",
    "Displayed severity": "zeige Dringlichkeit",
    "any severity": "jede Dringlichkeit",
    "or higher": "oder höher",
    "State": "Status",
    "any state": "jeder Status",
    "All alerts": "alle Alarme",
    "Show Procedure field": "zeige Prozedurfeld",
    "show": "zeige",
    "hide": "verstecke",
    "Sort alerts by": "sortiere Alarme nach",
    "timestamp, descending": "Zeitstempel, absteigend",
    "severity, descending": "Dringlichkeit, absteigend",
    "All devices": "Alle Geräte",
    "Event type": "Ereignistyp",
    "All types": "Alle Typen",
    "Number of interfaces": "Anzahl an Schnittstellen",
    "Last polled (minutes)": "zuletzt geprüft (Minuten)",
    "Interface type": "Schnittstellentyp",
    "All Ports": "Alle Ports",
    "Total": "Alle",
    "Ignored": "ignoriert",
    "Errored": "fehlerhalt",
    "Services": "Anwendungen",
    "No devices found within interval.": "Keine Geräte gefunden innerhalb dieses Intervals",
    "Summary": "Zusammenfassung",
    "Interface": "Schnittstelle",
    "Total traffic": "Gesamtdurchsatz",
    "Check your log for more details.": "Prüfen Sie das Logfile für Details",
    "If you need additional help, you can find how to get help at": "Wenn Sie zusätzliche Hilfe benötigen finde Sie diese bei",
    "Geo Locations": "geographischer Standort",
    "All Locations": "alle Standorte",
    "Pollers": "Poller",
    "Groups": "Gruppen",
    "Performance": "Performance",
    "History": "Geschichte",
    "syslog": {
      "severity": ["Notfall", "Alarm", "Kritisch", "Fehler", "Warnung", "Notiz", "Information", "Debug"],
      "facility": ["kernel Meldung", "user-level Meldung", "mail-system", "system daemons", "security/authorization Meldung", "messages generated internally by syslogd", "line printer subsystem", "network news subsystem", "UUCP subsystem", "clock daemon", "security/authorization Meldung", "FTP daemon", "NTP subsystem", "log audit", "log alert", "clock daemon (note 2)", "local use 0  (local0)", "local use 1  (local1)", "local use 2  (local2)", "local use 3  (local3)", "local use 4  (local4)", "local use 5  (local5)", "local use 6  (local6)", "local use 7  (local7)"]
    },
    "passwords": {
      "password": "Das Passwort muss aus mindestens acht Zeichen bestehen",
      "reset": "Ihr Passwort wurde zurückgesetzt!",
      "sent": "Sie haben via E-Mail einen Link bekommen um das Passwort zurückzusetzen!",
      "token": "Das Passwort Token zum zurücksetzen ist fehlerhaft.",
      "user": "Es konnte kein Benutzer mit der angegebenen E-Mailadresse gefunden werden"
    },
    "sensors": {
      "airflow": {
        "short": "Luftfluß",
        "long": "Luftfluß",
        "unit": "cfm",
        "unit_long": "Kubikfuß pro Minute"
      },
      "ber": {
        "short": "BER",
        "long": "Bitfehlerrate",
        "unit": "",
        "unit_long": ""
      },
      "charge": {
        "short": "Ladung",
        "long": "Ladung Prozent",
        "unit": "%",
        "unit_long": "Prozent"
      },
      "chromatic_dispersion": {
        "short": "Chromatische Dispersion",
        "long": "Chromatische Dispersion",
        "unit": "ps/nm/km",
        "unit_long": "Picosekunden pro Nanometer per Kilometer"
      },
      "cooling": {
        "short": "Kühlung",
        "long": "",
        "unit": "W",
        "unit_long": "Watt"
      },
      "count": {
        "short": "Zähler",
        "long": "Zähler",
        "unit": "",
        "unit_long": ""
      },
      "current": {
        "short": "Strom",
        "long": "Strom",
        "unit": "A",
        "unit_long": "Ampere"
      },
      "dbm": {
        "short": "dBm",
        "long": "dBm",
        "unit": "dBm",
        "unit_long": "Decibel-Milliwatt"
      },
      "delay": {
        "short": "Verzögerung",
        "long": "Verzögerung",
        "unit": "s",
        "unit_long": "Sekunden"
      },
      "eer": {
        "short": "EER",
        "long": "Energy Effizienz Rate",
        "unit": "",
        "unit_long": ""
      },
      "fanspeed": {
        "short": "Lüfterdrehzahl",
        "long": "Lüfterdrehzahl",
        "unit": "RPM",
        "unit_long": "Umdrehungen pro Minute"
      },
      "frequency": {
        "short": "Frequenz",
        "long": "Frequenz",
        "unit": "Hz",
        "unit_long": "Hertz"
      },
      "humidity": {
        "short": "Luftfeuchtigkeit",
        "long": "Luftfeuchtigkeit Prozent",
        "unit": "%",
        "unit_long": "Prozent"
      },
      "load": {
        "short": "Last",
        "long": "Last Prozent",
        "unit": "%",
        "unit_long": "Prozent"
      },
      "power": {
        "short": "Leistung",
        "long": "Leistung",
        "unit": "W",
        "unit_long": "Watt"
      },
      "power_consumed": {
        "short": "Verbrauchsleistung",
        "long": "Verbrauchsleistung",
        "unit": "kWh",
        "unit_long": "Killowatt-Stunden"
      },
      "power_factor": {
        "short": "Leistungsfaktor",
        "long": "Leistungsfaktor",
        "unit": "",
        "unit_long": ""
      },
      "pressure": {
        "short": "Luftdruck",
        "long": "Luftdruck",
        "unit": "kPa",
        "unit_long": "Kilopascal"
      },
      "quality_factor": {
        "short": "Qualitätsfaktor",
        "long": "Qualitätsfaktor",
        "unit": "",
        "unit_long": ""
      },
      "runtime": {
        "short": "Laufzeit",
        "long": "Leufzeit",
        "unit": "Min",
        "unit_long": "Minuten"
      },
      "signal": {
        "short": "Signalstärke",
        "long": "Signalstärke",
        "unit": "dBm",
        "unit_long": "Decibel-Milliwatt"
      },
      "snr": {
        "short": "SNR",
        "long": "Signal zu Rausch Verhältnis",
        "unit": "dB",
        "unit_long": "Decibel"
      },
      "state": {
        "short": "Status",
        "long": "Status",
        "unit": ""
      },
      "temperature": {
        "short": "Temperatur",
        "long": "Temperatur",
        "unit": "°C",
        "unit_long": "° Celsius"
      },
      "voltage": {
        "short": "Spannung",
        "long": "Spannung",
        "unit": "V",
        "unit_long": "Volt"
      },
      "waterflow": {
        "short": "Wasserdurchfluß",
        "long": "Wasserdurchfluß",
        "unit": "l/m",
        "unit_long": "Liter pro Minute"
      }
    },
    "wireless": {
      "ap-count": {
        "short": "APs",
        "long": "AP Anzahl",
        "unit": ""
      },
      "clients": {
        "short": "Clienten",
        "long": "Client Anzahl",
        "unit": ""
      },
      "capacity": {
        "short": "Kapazität",
        "long": "Kapazität",
        "unit": "%"
      },
      "ccq": {
        "short": "CCQ",
        "long": "Client Verbindungsqualität",
        "unit": "%"
      },
      "errors": {
        "short": "Fehler",
        "long": "Fehleranzahl",
        "unit": ""
      },
      "error-ratio": {
        "short": "Fehlerrate",
        "long": "Bit/Packet Fehlerrate",
        "unit": "%"
      },
      "error-rate": {
        "short": "BER",
        "long": "Bitfehlerrate",
        "unit": "bps"
      },
      "frequency": {
        "short": "Frequenz",
        "long": "Frequenz",
        "unit": "MHz"
      },
      "distance": {
        "short": "Distanz",
        "long": "Distanz",
        "unit": "km"
      },
      "mse": {
        "short": "MSE",
        "long": "mittlere quadratische Abweichung",
        "unit": "dB"
      },
      "noise-floor": {
        "short": "Grundrauschen",
        "long": "Grundrauschen",
        "unit": "dBm/Hz"
      },
      "power": {
        "short": "Signalstärke",
        "long": "TX/RX Signalstärke",
        "unit": "dBm"
      },
      "quality": {
        "short": "Qualität",
        "long": "Qualität",
        "unit": "%"
      },
      "rate": {
        "short": "Rate",
        "long": "TX/RX Rate",
        "unit": "bps"
      },
      "rssi": {
        "short": "RSSI",
        "long": "Empfangsfeldstärke",
        "unit": "dBm"
      },
      "snr": {
        "short": "SNR",
        "long": "Signal-zu-Rausch Verhältnis",
        "unit": "dB"
      },
      "ssr": {
        "short": "SSR",
        "long": "Signalstärke Rate",
        "unit": "dB"
      },
      "utilization": {
        "short": "Nutzrate",
        "long": "Nutzungsrate",
        "unit": "%"
      },
      "xpi": {
        "short": "XPI",
        "long": "Kreuzpolarisation Interferenz",
        "unit": "dB"
      }
    },
    "auth": {
      "failed": "Login fehlgeschlagen.",
      "throttle": "Zuviele Loginversuche. Versuchen Sie es in {seconds} Sekunden noch einmal."
    },
    "pagination": {
      "previous": "&laquo; zurück",
      "next": "weiter &raquo;"
    },
    "validation": {
      "accepted": "{attribute} muss akzeptiert werden.",
      "active_url": "{attribute} ist keine gültige URL.",
      "after": "{attribute} muss später als {date} sein.",
      "after_or_equal": "{attribute} muss dem {date} oder später entsprechen.",
      "alpha": "{attribute} darf nur Buchstaben enthalten.",
      "alpha_dash": "{attribute} darf nur Buchstaben, Ziffern, Striche und Unterlinien enthalten.",
      "alpha_num": "{attribute} darf nur Buchstaben und Ziffern enthalten.",
      "alpha_space": "{attribute} darf nur Buchstaben, Ziffern Unterlinien und Leerzeichen enthalten.",
      "array": "{attribute} muss ein Array sein.",
      "before": "Th{attribute} muss ein Datum vor dem {date} sein.",
      "before_or_equal": "{attribute} muss ein Datum vor oder gleich dem {date} sein.",
      "between": {
        "numeric": "{attribute} muss zwischen {min} und {max} sein.",
        "file": "{attribute} muss zwischen {min} und {max} kilobytes sein.",
        "string": "{attribute} muss zwischen {min} und {max} Zeichen haben.",
        "array": "{attribute} muss mindestens {min} und maximal {max} Elemente haben."
      },
      "boolean": "das {attribute} Feld darf nur true oder false sein.",
      "confirmed": "Die Prüfung von {attribute} schlug fehl.",
      "date": "{attribute} enthält kein gültiges Datum.",
      "date_equals": "{attribute} muss ein gültiges Datum gleich dem {date} sein.",
      "date_format": "{attribute} entpricht nicht dem Format {format}.",
      "different": "{attribute} und {other} müssen sich unterscheiden.",
      "digits": "{attribute} muss aus aus {digits} Ziffern bestehen.",
      "digits_between": "{attribute} muss mindestens {min} und maximal {max} Ziffern enthalten.",
      "dimensions": "{attribute} hat ungültige Bild Abmessungen.",
      "distinct": "{attribute} enthält doppelte Werte.",
      "email": "{attribute} muss eine gültige E-Mailadresse enthalten.",
      "ends_with": "{attribute} muss mit einem der folgenden Werte enden: {values}",
      "exists": "Die Auswahl {attribute} ist ungültig.",
      "file": "{attribute} muss eine Datei sein.",
      "filled": "{attribute} darf nicht leer sein.",
      "gt": {
        "numeric": "{attribute} muss zwischen {min} und {max} sein.",
        "file": "{attribute} muss zwischen {min} und {max} kilobytes sein.",
        "string": "{attribute} muss zwischen {min} und {max} Zeichen haben.",
        "array": "{attribute} muss mindestens {min} und maximal {max} Elemente haben."
      },
      "gte": {
        "numeric": "{attribute} muss zwischen {min} und {max} sein.",
        "file": "{attribute} muss zwischen {min} und {max} kilobytes sein.",
        "string": "{attribute} muss zwischen {min} und {max} Zeichen haben.",
        "array": "{attribute} muss mindestens {min} und maximal {max} Elemente haben."
      },
      "image": "{attribute} muss ein Bild sein.",
      "in": "{attribute} ist ungültig.",
      "in_array": "{attribute} existiert nicht in {other}.",
      "integer": "{attribute} muss ein Integer sein.",
      "ip": "{attribute} muss eine gültige IP address enthalten.",
      "ipv4": "{attribute} muss eine gültige IPv4 Addresse enthalten.",
      "ipv6": "{attribute} muss eine gültige IPv6 Addresse enthalten.",
      "json": "{attribute} muss einen gültigen JSON String enthalten.",
      "lt": {
        "numeric": "{attribute} muss kleiner als {value} sein.",
        "file": "{attribute} muss kleiner als {value} kilobytes sein.",
        "string": "{attribute} muss weniger als {value} Zeichen enthalten.",
        "array": "{attribute} muss weniger als {value} Elemente haben."
      },
      "lte": {
        "numeric": "{attribute} muss kleiner oder gleich {value} sein.",
        "file": "{attribute} muss kleiner oder gleich {value} kilobytes sein.",
        "string": "{attribute} muss weniger oder gleich {value} Zeichen enthalten.",
        "array": "{attribute} muss weniger oder gleich {value} Elemente haben."
      },
      "max": {
        "numeric": "{attribute} darf nicht größer als {max} sein.",
        "file": "{attribute} darf nicht größer als {max} kilobytes sein.",
        "string": "{attribute} darf nicht mehr als {max} Zeichen haben.",
        "array": "{attribute} darf nicht mehr als {max} Elemente haben."
      },
      "mimes": "{attribute} muss eine Datei des Types {values} sein.",
      "mimetypes": "{attribute} muss eine Datei des Types {values} sein.",
      "min": {
        "numeric": "{attribute} muss mindestens {min} sein.",
        "file": "{attribute} muss mindestens {min} kilobytes groß sein.",
        "string": "{attribute} muss mindestens {min} Zeichen lang sein.",
        "array": "{attribute} muss mindestens {min} Elemente enthalten."
      },
      "not_in": "Die Auswahl von {attribute} ist ungültig.",
      "not_regex": "Das Format von {attribute} ist ungültig.",
      "numeric": "{attribute} muss eine Ziffer sein.",
      "present": "{attribute} darf nicht leer sein.",
      "regex": "Das Format von {attribute} ist ungültig.",
      "required": "{attribute} wird benötigt.",
      "required_if": "{attribute} wird benötigt wenn {other} den Wert {value} enthält.",
      "required_unless": "{attribute} wird benötigt außer {other} enthält{values}.",
      "required_with": "{attribute} wird benötigt wenn {values} existiert.",
      "required_with_all": "{attribute} wird benötigt wenn {values} existieren.",
      "required_without": "{attribute} wird benötigt wenn {values} nicht existieren.",
      "required_without_all": "{attribute} wird benötigt wenn keines von {values} existieren.",
      "same": "{attribute} und {other} müssen passen.",
      "size": {
        "numeric": "{attribute} muss {size} groß sein.",
        "file": "{attribute} muss {size} kilobyte groß sein.",
        "string": "{attribute} muss {size} Zeichen groß sein.",
        "array": "{attribute} muss {size} Elemente enthalten."
      },
      "starts_with": "{attribute} muss mit einem von diesen beginnen: {values}",
      "string": "{attribute} muss einen Zeichenkette sein.",
      "timezone": "{attribute} muss eine gültige Zeitzone sein.",
      "unique": "{attribute} wird schon verwendet.",
      "uploaded": "Der Upload von {attribute} schlug fehl.",
      "url": "Das Format von {attribute} ist ungültig.",
      "uuid": "{attribute} muss eine gültige UUID sein.",
      "custom": {
        "attribute-name": {
          "rule-name": "custom-message"
        }
      },
      "attributes": []
    },
    "preferences": {
      "lang": "Deutsch"
    },
    "commands": {
      "user{add}": {
        "description": "Füge einen lokalen Benutzer hinzu. Sie können sich nur einloggen wenn die Authentifizierung auf MySQL gesetzt ist",
        "arguments": {
          "username": "Die Authentifizierung mit der sich der Benutzer einloggt"
        },
        "options": {
          "descr": "Beschreibung",
          "email": "E-Mail",
          "password": "Passwort des Benutzers. Wenn nicht angegeben werden Sie danach gefragt",
          "full-name": "Voller Name des Benutzers",
          "role": "Deklariere dem Benutzer die Rolle {roles}"
        },
        "password-request": "Definieren Sie ein Benutzerpasswort",
        "success": "Benutzer {username} erfolgreich hinzugefügt",
        "wrong-auth": "Achtung! Sie können sich nicht mit diesem Benutzernamen einloggen wenn die Authentifizierung nicht auf MySQL gesetzt ist"
      }
    }
  },
  "en": {
    "syslog": {
      "severity": ["Emergency", "Alert", "Critical", "Error", "Warning", "Notice", "Informational", "Debug"],
      "facility": ["kernel messages", "user-level messages", "mail-system", "system daemons", "security/authorization messages", "messages generated internally by syslogd", "line printer subsystem", "network news subsystem", "UUCP subsystem", "clock daemon", "security/authorization messages", "FTP daemon", "NTP subsystem", "log audit", "log alert", "clock daemon (note 2)", "local use 0  (local0)", "local use 1  (local1)", "local use 2  (local2)", "local use 3  (local3)", "local use 4  (local4)", "local use 5  (local5)", "local use 6  (local6)", "local use 7  (local7)"]
    },
    "settings": {
      "readonly": "Set in config.php, remove from config.php to enable.",
      "groups": {
        "alerting": "Alerting",
        "auth": "Authentication",
        "external": "External",
        "global": "Global",
        "os": "OS",
        "poller": "Poller",
        "system": "System",
        "webui": "Web UI"
      },
      "sections": {
        "alerting": {
          "general": "General Alert Settings",
          "email": "Email Options"
        },
        "auth": {
          "general": "General Authentication Settings",
          "ad": "Active Directory Settings",
          "ldap": "LDAP Settings"
        },
        "external": {
          "location": "Location Settings",
          "oxidized": "Oxidized Integration",
          "binaries": "Binary Locations",
          "peeringdb": "PeeringDB Integration",
          "unix-agent": "Unix-Agent Integration"
        },
        "poller": {
          "ping": "Ping",
          "rrdtool": "RRDTool Setup",
          "snmp": "SNMP"
        },
        "system": {
          "cleanup": "Cleanup",
          "server": "Server",
          "updates": "Updates"
        },
        "webui": {
          "availability-map": "Availability Map Settings",
          "graph": "Graph Settings",
          "dashboard": "Dashboard Settings",
          "search": "Search Settings",
          "style": "Style"
        }
      },
      "settings": {
        "active_directory": {
          "users_purge": {
            "description": "Keep inactive users for",
            "help": "Delete users from LibreNMS after this may days of not logging in. 0 means never and users will be recreated if the user logs back in."
          }
        },
        "addhost_alwayscheckip": {
          "description": "Check for duplicate IP when adding devices",
          "help": "If a host is added as an ip address it is checked to ensure the ip is not already present. If the ip is present the host is not added. If host is added by hostname this check is not performed. If the setting is true hostnames are resolved and the check is also performed. This helps prevents accidental duplicate hosts."
        },
        "alert": {
          "ack_until_clear": {
            "description": "Default acknowledge until alert clears option",
            "help": "Default acknowledge until alert clears"
          },
          "admins": {
            "description": "Issue alerts to admins",
            "help": "Alert administrators"
          },
          "default_copy": {
            "description": "Copy all email alerts to default contact",
            "help": "Copy all email alerts to default contact"
          },
          "default_if_none": {
            "description": "cannot set in webui?",
            "help": "Send mail to default contact if no other contacts are found"
          },
          "default_mail": {
            "description": "Default contact",
            "help": "The default mail contact"
          },
          "default_only": {
            "description": "Send alerts to default contact only",
            "help": "Only alert default mail contact"
          },
          "disable": {
            "description": "Disable alerting",
            "help": "Stop alerts being generated"
          },
          "fixed-contacts": {
            "description": "Updates to contact email addresses not honored",
            "help": "If TRUE any changes to sysContact or users emails will not be honoured whilst alert is active"
          },
          "globals": {
            "description": "Issue alerts to read only users",
            "help": "Alert read only administrators"
          },
          "syscontact": {
            "description": "Issue alerts to sysContact",
            "help": "Send alert to email in SNMP sysContact"
          },
          "transports": {
            "mail": {
              "description": "Enable email alerting",
              "help": "Mail alerting transport"
            }
          },
          "tolerance_window": {
            "description": "Tolerance window for cron",
            "help": "Tolerance window in seconds"
          },
          "users": {
            "description": "Issue alerts to normal users",
            "help": "Alert normal users"
          }
        },
        "alert_log_purge": {
          "description": "Alert log entries older than",
          "help": "Cleanup done by daily.sh"
        },
        "allow_unauth_graphs": {
          "description": "Allow unauthenticated graph access",
          "help": "Allows any one to access graphs without login"
        },
        "allow_unauth_graphs_cidr": {
          "description": "Allow the given networks graph access",
          "help": "Allow the given networks unauthenticated graph access (does not apply when unauthenticated graphs is enabled)"
        },
        "api_demo": {
          "description": "This is the demo"
        },
        "apps": {
          "powerdns-recursor": {
            "api-key": {
              "description": "API key for PowerDNS Recursor",
              "help": "API key for the PowerDNS Recursor app when connecting directly"
            },
            "https": {
              "description": "PowerDNS Recursor use HTTPS?",
              "help": "Use HTTPS instead of HTTP for the PowerDNS Recursor app when connecting directly"
            },
            "port": {
              "description": "PowerDNS Recursor port",
              "help": "TCP port to use for the PowerDNS Recursor app when connecting directly"
            }
          }
        },
        "astext": {
          "description": "Key to hold cache of autonomous systems descriptions"
        },
        "auth_ad_base_dn": {
          "description": "Base DN",
          "help": "groups and users must be under this dn. Example: dc=example,dc=com"
        },
        "auth_ad_check_certificates": {
          "description": "Check certificate",
          "help": "Check certificates for validity. Some servers use self signed certificates, disabling this allows those."
        },
        "auth_ad_group_filter": {
          "description": "Group LDAP filter",
          "help": "Active Directory LDAP filter for selecting groups"
        },
        "auth_ad_groups": {
          "description": "Group access",
          "help": "Define groups that have access and level"
        },
        "auth_ad_user_filter": {
          "description": "User LDAP filter",
          "help": "Active Directory LDAP filter for selecting users"
        },
        "auth_ldap_attr": {
          "uid": {
            "description": "Attribute to check username against",
            "help": "Attribute used to identify users by username"
          }
        },
        "auth_ldap_binddn": {
          "description": "Bind DN (overrides bind username)",
          "help": "Full DN of bind user"
        },
        "auth_ldap_bindpassword": {
          "description": "Bind password",
          "help": "Password for bind user"
        },
        "auth_ldap_binduser": {
          "description": "Bind username",
          "help": "Used to query the LDAP server when no user is logged in (alerts, API, etc)"
        },
        "auth_ad_binddn": {
          "description": "Bind DN (overrides bind username)",
          "help": "Full DN of bind user"
        },
        "auth_ad_bindpassword": {
          "description": "Bind password",
          "help": "Password for bind user"
        },
        "auth_ad_binduser": {
          "description": "Bind username",
          "help": "Used to query the AD server when no user is logged in (alerts, API, etc)"
        },
        "auth_ldap_cache_ttl": {
          "description": "LDAP cache expiration",
          "help": "Temporarily stores LDAP query results.  Improves speeds, but the data may be stale."
        },
        "auth_ldap_debug": {
          "description": "Show debug",
          "help": "Shows debug information.  May expose private information, do not leave enabled."
        },
        "auth_ldap_emailattr": {
          "description": "Mail attribute"
        },
        "auth_ldap_group": {
          "description": "Access group DN",
          "help": "Distinguished name for a group to give normal level access. Example: cn=groupname,ou=groups,dc=example,dc=com"
        },
        "auth_ldap_groupbase": {
          "description": "Group base DN",
          "help": "Distinguished name to search for groups Example: ou=group,dc=example,dc=com"
        },
        "auth_ldap_groupmemberattr": {
          "description": "Group member attribute"
        },
        "auth_ldap_groupmembertype": {
          "description": "Find group members by",
          "options": {
            "username": "Username",
            "fulldn": "Full DN (using prefix and suffix)",
            "puredn": "DN Search (search using uid attribute)"
          }
        },
        "auth_ldap_groups": {
          "description": "Group access",
          "help": "Define groups that have access and level"
        },
        "auth_ldap_port": {
          "description": "LDAP port",
          "help": "Port to connect to servers on. For LDAP it should be 389, for LDAPS it should be 636"
        },
        "auth_ldap_prefix": {
          "description": "User prefix",
          "help": "Used to turn a username into a distinguished name"
        },
        "auth_ldap_server": {
          "description": "LDAP Server(s)",
          "help": "Set server(s), space separated. Prefix with ldaps:// for ssl"
        },
        "auth_ldap_starttls": {
          "description": "Use STARTTLS",
          "help": "Use STARTTLS to secure the connection.  Alternative to LDAPS.",
          "options": {
            "disabled": "Disabled",
            "optional": "Optional",
            "required": "Required"
          }
        },
        "auth_ldap_suffix": {
          "description": "User suffix",
          "help": "Used to turn a username into a distinguished name"
        },
        "auth_ldap_timeout": {
          "description": "Connection timeout",
          "help": "If one or more servers are unresponsive, higher timeouts will cause slow access. To low may cause connection failures in some cases"
        },
        "auth_ldap_uid_attribute": {
          "description": "Unique ID attribute",
          "help": "LDAP attribute to use to identify users, must be numeric"
        },
        "auth_ldap_userdn": {
          "description": "Use full user DN",
          "help": "Uses a user's full DN as the value of the member attribute in a group instead of member: username using the prefix and suffix. (it’s member: uid=username,ou=groups,dc=domain,dc=com)"
        },
        "auth_ldap_version": {
          "description": "LDAP version",
          "help": "LDAP version to use to talk to the server.  Usually this should be v3",
          "options": {
            "2": "2",
            "3": "3"
          }
        },
        "auth_mechanism": {
          "description": "Authorization Method (Caution!)",
          "help": "Authorization method.  Caution, you may lose the ability to log in. You can override this back to mysql by setting $config['auth_mechanism'] = 'mysql'; in your config.php",
          "options": {
            "mysql": "MySQL (default)",
            "active_directory": "Active Directory",
            "ldap": "LDAP",
            "radius": "Radius",
            "http-auth": "HTTP Authentication",
            "ad-authorization": "Externally authenticated AD",
            "ldap-authorization": "Externally authenticated LDAP",
            "sso": "Single Sign On"
          }
        },
        "auth_remember": {
          "description": "Remember me duration",
          "help": "Number of days to keep a user logged in when checking the remember me checkbox at log in."
        },
        "authlog_purge": {
          "description": "Auth log entries older than (days)",
          "help": "Cleanup done by daily.sh"
        },
        "device_perf_purge": {
          "description": "Device performance entries older than (days)",
          "help": "Cleanup done by daily.sh"
        },
        "email_auto_tls": {
          "description": "Enable / disable Auto TLS support",
          "options": {
            "true": "Yes",
            "false": "No"
          }
        },
        "email_backend": {
          "description": "How to deliver mail",
          "help": "The backend to use for sending email, can be mail, sendmail or SMTP",
          "options": {
            "mail": "mail",
            "sendmail": "sendmail",
            "smtp": "SMTP"
          }
        },
        "email_from": {
          "description": "From email address",
          "help": "Email address used for sending emails (from)"
        },
        "email_html": {
          "description": "Use HTML emails",
          "help": "Send HTML emails"
        },
        "email_sendmail_path": {
          "description": "Location of sendmail if using this option"
        },
        "email_smtp_auth": {
          "description": "Enable / disable smtp authentication"
        },
        "email_smtp_host": {
          "description": "SMTP Host for sending email if using this option"
        },
        "email_smtp_password": {
          "description": "SMTP Auth password"
        },
        "email_smtp_port": {
          "description": "SMTP port setting"
        },
        "email_smtp_secure": {
          "description": "Enable / disable encryption (use tls or ssl)",
          "options": {
            "": "Disabled",
            "tls": "TLS",
            "ssl": "SSL"
          }
        },
        "email_smtp_timeout": {
          "description": "SMTP timeout setting"
        },
        "email_smtp_username": {
          "description": "SMTP Auth username"
        },
        "email_user": {
          "description": "From name",
          "help": "Name used as part of the from address"
        },
        "eventlog_purge": {
          "description": "Event log entries older than (days)",
          "help": "Cleanup done by daily.sh"
        },
        "favicon": {
          "description": "Favicon",
          "help": "Overrides the default favicon."
        },
        "fping": {
          "description": "Path to fping"
        },
        "fping6": {
          "description": "Path to fping6"
        },
        "fping_options": {
          "count": {
            "description": "fping count",
            "help": "The number of pings to send when checking if a host is up or down via icmp"
          },
          "interval": {
            "description": "fping interval",
            "help": "The amount of milliseconds to wait between pings"
          },
          "timeout": {
            "description": "fping timeout",
            "help": "The amount of milliseconds to wait for an echo response before giving up"
          }
        },
        "geoloc": {
          "api_key": {
            "description": "Geocoding API Key",
            "help": "Geocoding API Key (Required to function)"
          },
          "engine": {
            "description": "Geocoding Engine",
            "options": {
              "google": "Google Maps",
              "openstreetmap": "OpenStreetMap",
              "mapquest": "MapQuest",
              "bing": "Bing Maps"
            }
          }
        },
        "ipmitool": {
          "description": "Path to ipmtool"
        },
        "login_message": {
          "description": "Logon Message",
          "help": "Displayed on the login page"
        },
        "mono_font": {
          "description": "Monospaced Font"
        },
        "mtr": {
          "description": "Path to mtr"
        },
        "nmap": {
          "description": "Path to nmap"
        },
        "own_hostname": {
          "description": "LibreNMS hostname",
          "help": "Should be set to the hostname/ip the librenms server is added as"
        },
        "oxidized": {
          "default_group": {
            "description": "Set the default group returned"
          },
          "enabled": {
            "description": "Enable Oxidized support"
          },
          "features": {
            "versioning": {
              "description": "Enable config versioning access",
              "help": "Enable Oxidized config versioning (requires git backend)"
            }
          },
          "group_support": {
            "description": "Enable the return of groups to Oxidized"
          },
          "reload_nodes": {
            "description": "Reload Oxidized nodes list, each time a device is added"
          },
          "url": {
            "description": "URL to your Oxidized API",
            "help": "Oxidized API url (For example: http://127.0.0.1{8888})"
          }
        },
        "peeringdb": {
          "enabled": {
            "description": "Enable PeeringDB lookup",
            "help": "Enable PeeringDB lookup (data is downloaded with daily.sh)"
          }
        },
        "perf_times_purge": {
          "description": "Poller performance log entries older than (days)",
          "help": "Cleanup done by daily.sh"
        },
        "ping": {
          "description": "Path to ping"
        },
        "public_status": {
          "description": "Show status publicly",
          "help": "Shows the status of some devices on the logon page without authentication."
        },
        "rrd": {
          "heartbeat": {
            "description": "Change the rrd heartbeat value (default 600)"
          },
          "step": {
            "description": "Change the rrd step value (default 300)"
          }
        },
        "rrd_dir": {
          "description": "RRD Location",
          "help": "Location of rrd files.  Default is rrd inside the LibreNMS directory.  Changing this setting does not move the rrd files."
        },
        "rrd_rra": {
          "description": "RRD Format Settings",
          "help": "These cannot be changed without deleting your existing RRD files. Though one could conceivably increase or decrease the size of each RRA if one had performance problems or if one had a very fast I/O subsystem with no performance worries."
        },
        "rrdcached": {
          "description": "Enable rrdcached (socket)",
          "help": "Enables rrdcached by setting the location of the rrdcached socket. Can be unix or network socket (unix:/run/rrdcached.sock or localhost{42217})"
        },
        "rrdtool": {
          "description": "Path to rrdtool"
        },
        "rrdtool_tune": {
          "description": "Tune all rrd port files to use max values",
          "help": "Auto tune maximum value for rrd port files"
        },
        "sfdp": {
          "description": "Path to sfdp"
        },
        "site_style": {
          "description": "Set the site css style",
          "options": {
            "blue": "Blue",
            "dark": "Dark",
            "light": "Light",
            "mono": "Mono"
          }
        },
        "snmp": {
          "transports": {
            "description": "Transport (priority)",
            "help": "Select enabled transports and order them as you want them to be tried."
          },
          "version": {
            "description": "Version (priority)",
            "help": "Select enabled versions and order them as you want them to be tried."
          },
          "community": {
            "description": "Communities (priority)",
            "help": "Enter community strings for v1 and v2c and order them as you want them to be tried"
          },
          "port": {
            "description": "Port",
            "help": "Set the tcp/udp port to be used for SNMP"
          },
          "v3": {
            "description": "SNMP v3 Authentication (priority)",
            "help": "Set up v3 authentication variables and order them as you want them to be tried",
            "auth": "Auth",
            "crypto": "Crypto",
            "fields": {
              "authalgo": "Algorithm",
              "authlevel": "Level",
              "authname": "Username",
              "authpass": "Password",
              "cryptoalgo": "Algorithm",
              "cryptopass": "Password"
            },
            "level": {
              "noAuthNoPriv": "No Authentication, No Privacy",
              "authNoPriv": "Authentication, No Privacy",
              "authPriv": "Authentication and Privacy"
            }
          }
        },
        "snmpbulkwalk": {
          "description": "Path to snmpbulkwalk"
        },
        "snmpget": {
          "description": "Path to snmpget"
        },
        "snmpgetnext": {
          "description": "Path to snmpgetnext"
        },
        "snmptranslate": {
          "description": "Path to snmptranslate"
        },
        "snmpwalk": {
          "description": "Path to snmpwalk"
        },
        "syslog_filter": {
          "description": "Filter syslog messages containing"
        },
        "syslog_purge": {
          "description": "Syslog entries older than (days)",
          "help": "Cleanup done by daily.sh"
        },
        "traceroute": {
          "description": "Path to traceroute"
        },
        "traceroute6": {
          "description": "Path to traceroute6"
        },
        "unix-agent": {
          "connection-timeout": {
            "description": "Unix-agent connection timeout"
          },
          "port": {
            "description": "Default unix-agent port",
            "help": "Default port for the unix-agent (check_mk)"
          },
          "read-timeout": {
            "description": "Unix-agent read timeout"
          }
        },
        "update": {
          "description": "Enable updates in ./daily.sh"
        },
        "update_channel": {
          "description": "Set update Channel",
          "options": {
            "master": "master",
            "release": "release"
          }
        },
        "virsh": {
          "description": "Path to virsh"
        },
        "webui": {
          "availability_map_box_size": {
            "description": "Availability box width",
            "help": "Input desired tile width in pixels for box size in full view"
          },
          "availability_map_compact": {
            "description": "Availability map compact view",
            "help": "Availability map view with small indicators"
          },
          "availability_map_sort_status": {
            "description": "Sort by status",
            "help": "Sort devices and services by status"
          },
          "availability_map_use_device_groups": {
            "description": "Use device groups filter",
            "help": "Enable usage of device groups filter"
          },
          "default_dashboard_id": {
            "description": "Default dashboard",
            "help": "Global default dashboard_id for all users who do not have their own default set"
          },
          "dynamic_graphs": {
            "description": "Enable dynamic graphs",
            "help": "Enable dynamic graphs, enables zooming and panning on graphs"
          },
          "global_search_result_limit": {
            "description": "Set the max search result limit",
            "help": "Global search results limit"
          },
          "graph_stacked": {
            "description": "Use stacked graphs",
            "help": "Display stacked graphs instead of inverted graphs"
          },
          "graph_type": {
            "description": "Set the graph type",
            "help": "Set the default graph type",
            "options": {
              "png": "PNG",
              "svg": "SVG"
            }
          },
          "min_graph_height": {
            "description": "Set the minimum graph height",
            "help": "Minimum Graph Height (default: 300)"
          }
        },
        "whois": {
          "description": "Path to whois"
        }
      },
      "units": {
        "days": "days",
        "ms": "ms",
        "seconds": "seconds"
      },
      "validate": {
        "boolean": "{value} is not a valid boolean",
        "email": "{value} is not a valid email",
        "integer": "{value} is not an integer",
        "password": "The password is incorrect",
        "select": "{value} is not an allowed value",
        "text": "{value} is not allowed",
        "array": "Invalid format"
      }
    },
    "passwords": {
      "password": "Passwords must be at least eight characters and match the confirmation.",
      "reset": "Your password has been reset!",
      "sent": "We have e-mailed your password reset link!",
      "token": "This password reset token is invalid.",
      "user": "We can't find a user with that e-mail address."
    },
    "sensors": {
      "airflow": {
        "short": "Airflow",
        "long": "Airflow",
        "unit": "cfm",
        "unit_long": "Cubic Feet per Minute"
      },
      "ber": {
        "short": "BER",
        "long": "Bit Error Rate",
        "unit": "",
        "unit_long": ""
      },
      "charge": {
        "short": "Charge",
        "long": "Charge Percent",
        "unit": "%",
        "unit_long": "Percent"
      },
      "chromatic_dispersion": {
        "short": "Chromatic Dispersion",
        "long": "Chromatic Dispersion",
        "unit": "ps/nm/km",
        "unit_long": "Picoseconds per Nanometer per Kilometer"
      },
      "cooling": {
        "short": "Cooling",
        "long": "",
        "unit": "W",
        "unit_long": "Watts"
      },
      "count": {
        "short": "Count",
        "long": "Count",
        "unit": "",
        "unit_long": ""
      },
      "current": {
        "short": "Current",
        "long": "Current",
        "unit": "A",
        "unit_long": "Amperes"
      },
      "dbm": {
        "short": "dBm",
        "long": "dBm",
        "unit": "dBm",
        "unit_long": "Decibel-Milliwatts"
      },
      "delay": {
        "short": "Delay",
        "long": "Delay",
        "unit": "s",
        "unit_long": "Seconds"
      },
      "eer": {
        "short": "EER",
        "long": "Energy Efficient Ratio",
        "unit": "",
        "unit_long": ""
      },
      "fanspeed": {
        "short": "Fanspeed",
        "long": "Fan Speed",
        "unit": "RPM",
        "unit_long": "Rotations per Minute"
      },
      "frequency": {
        "short": "Frequency",
        "long": "Frequency",
        "unit": "Hz",
        "unit_long": "Hertz"
      },
      "humidity": {
        "short": "Humidity",
        "long": "Humidity Percent",
        "unit": "%",
        "unit_long": "Percent"
      },
      "load": {
        "short": "Load",
        "long": "Load Percent",
        "unit": "%",
        "unit_long": "Percent"
      },
      "power": {
        "short": "Power",
        "long": "Power",
        "unit": "W",
        "unit_long": "Watts"
      },
      "power_consumed": {
        "short": "Power Consumed",
        "long": "Power Consumed",
        "unit": "kWh",
        "unit_long": "Killowatt-Hours"
      },
      "power_factor": {
        "short": "Power Factor",
        "long": "Power Factor",
        "unit": "",
        "unit_long": ""
      },
      "pressure": {
        "short": "Pressure",
        "long": "Pressure",
        "unit": "kPa",
        "unit_long": "Kilopascals"
      },
      "quality_factor": {
        "short": "Quality Factor",
        "long": "Quality Factor",
        "unit": "",
        "unit_long": ""
      },
      "runtime": {
        "short": "Runtime",
        "long": "Runtime",
        "unit": "Min",
        "unit_long": "Minutes"
      },
      "signal": {
        "short": "Signal",
        "long": "Signal",
        "unit": "dBm",
        "unit_long": "Decibal-Milliwatts"
      },
      "snr": {
        "short": "SNR",
        "long": "Signal to Noise Ratio",
        "unit": "dB",
        "unit_long": "Decibels"
      },
      "state": {
        "short": "State",
        "long": "State",
        "unit": ""
      },
      "temperature": {
        "short": "Temperature",
        "long": "Temperature",
        "unit": "°C",
        "unit_long": "° Celsius"
      },
      "voltage": {
        "short": "Voltage",
        "long": "voltage",
        "unit": "V",
        "unit_long": "Volts"
      },
      "waterflow": {
        "short": "Waterflow",
        "long": "Water Flow",
        "unit": "l/m",
        "unit_long": "Liters Per Minute"
      }
    },
    "wireless": {
      "ap-count": {
        "short": "APs",
        "long": "AP Count",
        "unit": ""
      },
      "clients": {
        "short": "Clients",
        "long": "Client Count",
        "unit": ""
      },
      "capacity": {
        "short": "Capacity",
        "long": "Capacity",
        "unit": "%"
      },
      "ccq": {
        "short": "CCQ",
        "long": "Client Connection Quality",
        "unit": "%"
      },
      "errors": {
        "short": "Errors",
        "long": "Error Count",
        "unit": ""
      },
      "error-ratio": {
        "short": "Error Ratio",
        "long": "Bit/Packet Error Ratio",
        "unit": "%"
      },
      "error-rate": {
        "short": "BER",
        "long": "Bit Error Rate",
        "unit": "bps"
      },
      "frequency": {
        "short": "Frequency",
        "long": "Frequency",
        "unit": "MHz"
      },
      "distance": {
        "short": "Distance",
        "long": "Distance",
        "unit": "km"
      },
      "mse": {
        "short": "MSE",
        "long": "Mean Square Error",
        "unit": "dB"
      },
      "noise-floor": {
        "short": "Noise Floor",
        "long": "Noise Floor",
        "unit": "dBm/Hz"
      },
      "power": {
        "short": "Power/Signal",
        "long": "TX/RX Power or Signal",
        "unit": "dBm"
      },
      "quality": {
        "short": "Quality",
        "long": "Quality",
        "unit": "%"
      },
      "rate": {
        "short": "Rate",
        "long": "TX/RX Rate",
        "unit": "bps"
      },
      "rssi": {
        "short": "RSSI",
        "long": "Received Signal Strength Indicator",
        "unit": "dBm"
      },
      "snr": {
        "short": "SNR",
        "long": "Signal-to-Noise Ratio",
        "unit": "dB"
      },
      "ssr": {
        "short": "SSR",
        "long": "Signal Strength Ratio",
        "unit": "dB"
      },
      "utilization": {
        "short": "Utilization",
        "long": "utilization",
        "unit": "%"
      },
      "xpi": {
        "short": "XPI",
        "long": "Cross Polar Interference",
        "unit": "dB"
      }
    },
    "auth": {
      "failed": "These credentials do not match our records.",
      "throttle": "Too many login attempts. Please try again in {seconds} seconds."
    },
    "pagination": {
      "previous": "&laquo; Previous",
      "next": "Next &raquo;"
    },
    "validation": {
      "accepted": "The {attribute} must be accepted.",
      "active_url": "The {attribute} is not a valid URL.",
      "after": "The {attribute} must be a date after {date}.",
      "after_or_equal": "The {attribute} must be a date after or equal to {date}.",
      "alpha": "The {attribute} may only contain letters.",
      "alpha_dash": "The {attribute} may only contain letters, numbers, dashes and underscores.",
      "alpha_num": "The {attribute} may only contain letters and numbers.",
      "alpha_space": "The {attribute} may only contain letters, numbers, underscores and spaces.",
      "array": "The {attribute} must be an array.",
      "before": "The {attribute} must be a date before {date}.",
      "before_or_equal": "The {attribute} must be a date before or equal to {date}.",
      "between": {
        "numeric": "The {attribute} must be between {min} and {max}.",
        "file": "The {attribute} must be between {min} and {max} kilobytes.",
        "string": "The {attribute} must be between {min} and {max} characters.",
        "array": "The {attribute} must have between {min} and {max} items."
      },
      "boolean": "The {attribute} field must be true or false.",
      "confirmed": "The {attribute} confirmation does not match.",
      "date": "The {attribute} is not a valid date.",
      "date_equals": "The {attribute} must be a date equal to {date}.",
      "date_format": "The {attribute} does not match the format {format}.",
      "different": "The {attribute} and {other} must be different.",
      "digits": "The {attribute} must be {digits} digits.",
      "digits_between": "The {attribute} must be between {min} and {max} digits.",
      "dimensions": "The {attribute} has invalid image dimensions.",
      "distinct": "The {attribute} field has a duplicate value.",
      "email": "The {attribute} must be a valid email address.",
      "ends_with": "The {attribute} must end with one of the following: {values}",
      "exists": "The selected {attribute} is invalid.",
      "file": "The {attribute} must be a file.",
      "filled": "The {attribute} field must have a value.",
      "gt": {
        "numeric": "The {attribute} must be greater than {value}.",
        "file": "The {attribute} must be greater than {value} kilobytes.",
        "string": "The {attribute} must be greater than {value} characters.",
        "array": "The {attribute} must have more than {value} items."
      },
      "gte": {
        "numeric": "The {attribute} must be greater than or equal {value}.",
        "file": "The {attribute} must be greater than or equal {value} kilobytes.",
        "string": "The {attribute} must be greater than or equal {value} characters.",
        "array": "The {attribute} must have {value} items or more."
      },
      "image": "The {attribute} must be an image.",
      "in": "The selected {attribute} is invalid.",
      "in_array": "The {attribute} field does not exist in {other}.",
      "integer": "The {attribute} must be an integer.",
      "ip": "The {attribute} must be a valid IP address.",
      "ipv4": "The {attribute} must be a valid IPv4 address.",
      "ipv6": "The {attribute} must be a valid IPv6 address.",
      "json": "The {attribute} must be a valid JSON string.",
      "lt": {
        "numeric": "The {attribute} must be less than {value}.",
        "file": "The {attribute} must be less than {value} kilobytes.",
        "string": "The {attribute} must be less than {value} characters.",
        "array": "The {attribute} must have less than {value} items."
      },
      "lte": {
        "numeric": "The {attribute} must be less than or equal {value}.",
        "file": "The {attribute} must be less than or equal {value} kilobytes.",
        "string": "The {attribute} must be less than or equal {value} characters.",
        "array": "The {attribute} must not have more than {value} items."
      },
      "max": {
        "numeric": "The {attribute} may not be greater than {max}.",
        "file": "The {attribute} may not be greater than {max} kilobytes.",
        "string": "The {attribute} may not be greater than {max} characters.",
        "array": "The {attribute} may not have more than {max} items."
      },
      "mimes": "The {attribute} must be a file of type: {values}.",
      "mimetypes": "The {attribute} must be a file of type: {values}.",
      "min": {
        "numeric": "The {attribute} must be at least {min}.",
        "file": "The {attribute} must be at least {min} kilobytes.",
        "string": "The {attribute} must be at least {min} characters.",
        "array": "The {attribute} must have at least {min} items."
      },
      "not_in": "The selected {attribute} is invalid.",
      "not_regex": "The {attribute} format is invalid.",
      "numeric": "The {attribute} must be a number.",
      "present": "The {attribute} field must be present.",
      "regex": "The {attribute} format is invalid.",
      "required": "The {attribute} field is required.",
      "required_if": "The {attribute} field is required when {other} is {value}.",
      "required_unless": "The {attribute} field is required unless {other} is in {values}.",
      "required_with": "The {attribute} field is required when {values} is present.",
      "required_with_all": "The {attribute} field is required when {values} are present.",
      "required_without": "The {attribute} field is required when {values} is not present.",
      "required_without_all": "The {attribute} field is required when none of {values} are present.",
      "same": "The {attribute} and {other} must match.",
      "size": {
        "numeric": "The {attribute} must be {size}.",
        "file": "The {attribute} must be {size} kilobytes.",
        "string": "The {attribute} must be {size} characters.",
        "array": "The {attribute} must contain {size} items."
      },
      "starts_with": "The {attribute} must start with one of the following: {values}",
      "string": "The {attribute} must be a string.",
      "timezone": "The {attribute} must be a valid zone.",
      "unique": "The {attribute} has already been taken.",
      "uploaded": "The {attribute} failed to upload.",
      "url": "The {attribute} format is invalid.",
      "uuid": "The {attribute} must be a valid UUID.",
      "custom": {
        "attribute-name": {
          "rule-name": "custom-message"
        }
      },
      "attributes": []
    },
    "preferences": {
      "lang": "English"
    },
    "commands": {
      "user{add}": {
        "description": "Add a local user, you can only log in with this user if auth is set to mysql",
        "arguments": {
          "username": "The username the user will log in with"
        },
        "options": {
          "descr": "User description",
          "email": "Email to use for the user",
          "password": "Password for the user, if not given, you will be prompted",
          "full-name": "Full name for the user",
          "role": "Set the user to the desired role {roles}"
        },
        "password-request": "Please enter the user's password",
        "success": "Successfully added user: {username}",
        "wrong-auth": "Warning! You will not be able to log in with this user because you are not using MySQL auth"
      }
    }
  },
  "fr": {
    "Authlog": "Journal d'authentification",
    "User": "Utilisateur",
    "IP Address": "Adresse IP",
    "Result": "Résultat",
    "Translation not fully supported": "La traduction n'est pas entièrement prise en charge",
    "Warning {service_count}": "Attention {service_count}",
    "Critical {service_count}": "Critique {service_count}",
    "Disabled {port_count}": "Désactivé {port_count}",
    "Errored {port_count}": "Erreurs {port_count}",
    "Ignored {port_count}": "Ignoré {port_count}",
    "Down {port_count}": "Down {port_count}",
    "Deleted {port_count}": "Supprimé {port_count}",
    "About {project_name}": "À propos {project_name}",
    "See the <a href=\"{url}\">list of contributors</a> on GitHub.": "Consultez la <a href=\"{url}\"> liste des contributeurs </a> sur GitHub.",
    "Docs": "Documentations",
    "Close": "Fermer",
    "LibreNMS is an autodiscovering PHP/MySQL-based network monitoring system": "LibreNMS est un système de surveillance réseau basé sur PHP / MySQL à découverte automatique.",
    "Packages": "Paquets",
    "Disabled": "Désactiver",
    "Version": "Version",
    "Database Schema": "Schéma de base de données",
    "Web Server": "Serveur Web",
    "LibreNMS is a community-based project": "LibreNMS est un projet basé sur la communauté",
    "Please feel free to join us and contribute code, documentation, and bug reports:": "N'hésitez pas à nous rejoindre et à contribuer au code, à la documentation et aux rapports de bug:",
    "Web site": "Site Web",
    "Bug tracker": "Traqueur de bug",
    "Community Forum": "Forum de la communauté",
    "Changelog": "Journal des changements",
    "Local git log": "Local git log",
    "Contributors": "Contributeurs",
    "Acknowledgements": "Remerciements",
    "Opt in to send anonymous usage statistics to LibreNMS?": "Activer pour envoyer des statistiques d'utilisation anonymes à LibreNMS?",
    "Online stats:": "Statistiques en ligne",
    "Clear remote stats": "Effacer les statistiques à distance",
    "IPv4 Addresses": "Adresses IPv4",
    "IPv4 Networks": "Réseaux IPv4",
    "IPv6 Addresses": "Adresses IPv6",
    "IPv6 Networks": "Réseaux IPv6",
    "Processors": "Processeurs",
    "Applications": "Applications",
    "Disk I/O": "E/S Disque",
    "Syslog Entries": "Entrées Syslog",
    "Eventlog Entries": "Entrées du journal des événements",
    "Sensors": "Capteurs",
    "Wireless Sensors": "Capteurs sans fil",
    "Toner": "Toner",
    "License": "License",
    "Select Devices": "Sélectionner les périphériques",
    "Dynamic": "Dynamique",
    "Static": "Statique",
    "Define Rules": "Définir les règles",
    "Create Device Group": "Créer un groupe de périphériques",
    "Edit Device Group": "Modifier le groupe de périphériques",
    "New Device Group": "Nouveau groupe de périphérique",
    "Pattern": "Modèle",
    "Type": "Type",
    "Name": "Prénom",
    "User Preferences": "Préférences de l'utilisateur",
    "Global Administrative Access": "Accès administratif global",
    "Device Permissions": "Autorisations de périphérique",
    "Preferences": "Préférences",
    "Language": "Langue",
    "Change Password": "Changer le mot de passe",
    "Verify New Password": "Vérifier le nouveau mot de passe",
    "Peering + Transit": "Peering + Transit",
    "FDB Tables": "Tables FDB",
    "ARP Tables": "Tables ARP",
    "MAC Address": "Adresse MAC",
    "IPv6 Address": "Adresse IPv6",
    "IPv4 Address": "Adresse IPv4",
    "Package": "Paquet",
    "Virtual Machines": "Machines virtuelles",
    "Device Groups": "Groupes de périphériques",
    "Register": "S'enregistrer",
    "Overview": "Vue d'ensemble",
    "Maps": "Cartes",
    "Availability": "Disponibilité",
    "Device Groups Maps": "Cartes de groupes de périphériques",
    "Geographical": "Géographique",
    "Plugins": "Plugins",
    "Plugin Admin": "Plugin Admin",
    "Tools": "Outils",
    "Eventlog": "Journal des événements",
    "Inventory": "Inventaire",
    "MIB definitions": "Définitions MIB",
    "No devices": "Aucun périphérique ",
    "MIB associations": "Associations MIB",
    "Manage Groups": "Gérer les groupes",
    "Device Dependencies": "Dépendances du périphérique",
    "Add Device": "Ajouter un périphérique",
    "Delete Device": "Supprimer le périphérique",
    "All Services": "Tous les services",
    "Add Service": "Ajouter un service",
    "Traffic Bills": "Factures de traffic",
    "Pseudowires": "Pseudowires",
    "Customers": "Clients",
    "Transit": "Transit",
    "Core": "Coeur",
    "Alerts": "Alertes",
    "Deleted": "Supprimé",
    "Health": "Santé",
    "Memory": "Mémoire",
    "Processor": "Processeur",
    "Storage": "Stockage",
    "Wireless": "Sans-fil",
    "Apps": "Applications",
    "Routing": "Routage",
    "Alerted": "Alerte",
    "Notifications": "Notifications",
    "Alert History": "Historique d'alerte",
    "Statistics": "Statistiques",
    "Alert Rules": "Règles d'alerte",
    "Scheduled Maintenance": "Maintenance prévue",
    "Alert Templates": "Modèles d'alerte",
    "Alert Transports": "Alert Transports",
    "My Settings": "Mes Paramètres",
    "Settings": "Paramètres",
    "Global Settings": "Paramètres globaux",
    "Validate Config": "Valider la configuration",
    "Auth History": "Historique d'authentification",
    "Peering": "Peering",
    "API Settings": "Paramètres de l'API",
    "API Docs": "Documentation API",
    "Shutdown": "Arrêt",
    "The {attribute} must a valid IP address/network or hostname.": "{attribute} doit avoir une adresse IP valide ou un nom d'hôte valide.",
    "Never polled": "Jamais sondé",
    "This indicates the most likely endpoint switchport": "Ceci indique le port de commutation le plus probable",
    "Two-Factor unlocked.": "Double facteur déverrouillé.",
    "Failed to unlock Two-Factor.": "Échec de déverrouilliage double facteur.",
    "Two-Factor removed.": "Double facteur supprimé.",
    "Failed to remove Two-Factor.": "Échec de la suppression du double facteur  .",
    "TwoFactor auth removed.": "Authentification à deux facteurs supprimée.",
    "Too many two-factor failures, please contact administrator.": "Trop d'échecs à deux facteurs, veuillez contacter l'administrateur.",
    "Too many two-factor failures, please wait {time} seconds": "Trop d'échecs à deux facteurs, veuillez patienter {time} secondes",
    "No Two-Factor Token entered.": "Aucun jeton à deux facteurs n'est entré.",
    "No Two-Factor settings, how did you get here?": "Pas de réglages à deux facteurs, comment êtes-vous arrivé ici ?",
    "Wrong Two-Factor Token.": "Mauvais jeton à deux facteurs.",
    "TwoFactor auth added.": "Authentification à deux facteurs ajoutée.",
    "User {username} created": "Utilisateur {username} créé",
    "Failed to create user": "Échec de création de l'utilisateur",
    "Updated dashboard for {username}": "Mise à jour du tableau de bord pour {username}",
    "User {username} updated": "Utilisateur {username} mise à jour",
    "Failed to update user {username}": "Échec de mise à jour de l'utilisateur {username}",
    "User {username} deleted.": "Utilisateur {username} supprimé.",
    "Device does not exist": "L'appareil n'existe pas",
    "Port does not exist": "Le port n'existe pas",
    "App does not exist": "L'application n'existe pas",
    "Bill does not exist": "La facture n'existe pas",
    "Munin plugin does not exist": "Le module d'extension Munin n'existe pas",
    "Ok": "Ok",
    "Warning": "Attention",
    "Critical": "Critique",
    "Existing password did not match": "Le mot de passe existant ne correspond pas",
    "The {attribute} field is required.": "l'{attribute} champ est obligatoire.",
    "Edit User": "Modifier l'utilisateur",
    "Unlock": "Déverrouiller",
    "User exceeded failures": "L'utilisateur à dépassé le nombre de tentative",
    "Disable TwoFactor": "Désactiver le double facteur",
    "No TwoFactor key generated for this user, Nothing to do.": "Pas de clé à deux facteurs générée pour cet utilisateur, rien à faire.",
    "Save": "Enregistrer",
    "Cancel": "Annuler",
    "Unlocked Two Factor.": "Déverrouillé le double facteur.",
    "Failed to unlock Two Factor": "N'a pas réussi à déverrouiller le double facteur",
    "Removed Two Factor.": "Supprimé le double-facteurs.",
    "Failed to remove Two Factor": "N'a pas réussi à supprimer le double facteur",
    "Real Name": "Nom réel",
    "Email": "E-mail",
    "Description": "Description",
    "Level": "Rôle",
    "Normal": "Normal",
    "Global Read": "Lecture globale",
    "Admin": "Admin",
    "Demo": "Démonstration",
    "Dashboard": "Tableau de bord",
    "Password": "Mot de passe",
    "Current Password": "Mot de passe actuel",
    "New Password": "Nouveau mot de passe",
    "Confirm Password": "Mot de passe confirmer",
    "Can Modify Password": "Peut modifier le mot de passe",
    "Create User": "Créer un utilisateur",
    "Username": "Nom d'utilisateur",
    "Manage Users": "Gestion des utilisateurs",
    "ID": "ID",
    "Access": "Accès",
    "Auth": "Auth",
    "Actions": "Actions",
    "Edit": "Modifier",
    "Delete": "Supprimer",
    "Manage Access": "Gérer les accès",
    "Add User": "Ajouter un utilisateur",
    "Are you sure you want to delete ": "Êtes-vous sûr de vouloir supprimer ",
    "The user could not be deleted": "L’utilisateur ne peut être supprimé",
    "Whoops, the web server could not write required files to the filesystem.": "Oups, le serveur web ne peut pas écrire les fichiers nécessaires au système de fichiers.",
    "Running the following commands will fix the issue most of the time:": "Exécuter les commandes suivantes corrigera le problème la plupart du temps:",
    "Whoops, looks like something went wrong. Check your librenms.log.": "Oups, on dirait que quelque chose a mal tourné. Vérifiez le fichier librenms.log.",
    "Public Devices": "Appareils publics",
    "System Status": "Status du système",
    "Logon": "Connexion",
    "Device": "Appareils",
    "Platform": "Plateforme",
    "Uptime": "Temps de fonctionnement",
    "Location": "Emplacement",
    "Status": "Status",
    "Remember Me": "Se souvenir de moi",
    "Login": "Connexion",
    "Please enter auth token": "Veuillez entrer le jeton d’autorisation",
    "Submit": "Submit",
    "Logout": "Déconnexion",
    "Locations": "Emplacements",
    "Coordinates": "Coordonnées",
    "Devices": "Appareils",
    "Network": "Réseau",
    "Servers": "Serveurs",
    "Firewalls": "Pare-feux",
    "Down": "Down",
    "Save changes": "Sauvegarder les changements",
    "N/A": "N/A",
    "Location must have devices to show graphs": "L’emplacement doit avoir des appareils pour montrer des graphiques",
    "Traffic": "Traffic",
    "Cannot delete locations used by devices": "Impossible de supprimer les emplacements utilisés par les appareils",
    "Location deleted": "Emplacement supprimé",
    "Failed to delete location": "Echec de suppression de l'emplacement",
    "Timestamp": "Horodatage",
    "Source": "Source",
    "Message": "Message",
    "Facility": "Etablissement",
    "Total hosts": "Nombre total d’hôtes",
    "ignored": "ignoré",
    "disabled": "desactiver",
    "up": "up",
    "warn": "warn",
    "down": "down",
    "Total services": "Total services",
    "Widget title": "Titre du widget",
    "Default Title": "Titre par défaut",
    "Columns": "Colonnes",
    "Markers": "Markers",
    "Ports": "Ports",
    "Resolution": "Résolution",
    "Countries": "Pays",
    "Provinces": "Provinces",
    "Metros": "Metros",
    "Region": "Région",
    "Help": "Aide",
    "Stream": "Stream",
    "All Messages": "Tous les Messages",
    "All Devices": "Tous les appareils",
    "Page Size": "Taille de la page",
    "Time Range": "Plage de temps",
    "Search all time": "Rechercher tout le temps",
    "Search last 5 minutes": "Rechercher les 5 dernières minutes",
    "Search last 15 minutes": "Rechercher les 15 dernières minutes",
    "Search last 30 minutes": "Rechercherles 30 dernières minutess",
    "Search last 1 hour": "Rechercher la dernière heure",
    "Search last 2 hours": "Rechercher les deux dernières heures",
    "Search last 8 hours": "Rechercher les huit dernières heures",
    "Search last 1 day": "Rechercher le dernier jour",
    "Search last 2 days": "Rechercher les deux derniers jours",
    "Search last 5 days": "Rechercher les cinq derniers jours",
    "Search last 7 days": "Rechercher la dernière semaine",
    "Search last 14 days": "Rechercher les deux dernières semaines",
    "Search last 30 days": "Rechercher le mois dernier",
    "Custom title": "Titre personnalisé",
    "Initial Latitude": "Latitude initiale",
    "ie. 51.4800 for Greenwich": "ie. 51.4800 pour Greenwich",
    "Initial Longitude": "Longitude initiale",
    "ie. 0 for Greenwich": "ie. 0 pour Greenwich",
    "Initial Zoom": "Zoom initial",
    "Grouping radius": "Rayon de regroupement",
    "default 80": "defaut 80",
    "Show devices": "Afficher les appareils",
    "Up + Down": "Up + Down",
    "Up": "Up",
    "Show Services": "Afficher les Services",
    "no": "non",
    "yes": "oui",
    "Show Port Errors": "Afficher les erreurs de port",
    "Notes": "Notes",
    "Custom title for widget": "Titre personnalisé pour widget",
    "Display type": "Type d'affichage",
    "boxes": "boîtes",
    "compact": "compact",
    "Uniform Tiles": "Tuiles uniformes",
    "Tile size": "Taille des carreaux",
    "Disabled/ignored": "Désactivé/Ignoré",
    "Show": "Afficher",
    "Hide": "Cacher",
    "Mode select": "Sélection du mode",
    "only devices": "uniquement les appareils",
    "only services": "uniquement les services",
    "devices and services": "appareils et services",
    "Order By": "Classé par",
    "Hostname": "Nom d'hôte",
    "Device group": "Groupe d'appareils",
    "Automatic Title": "Titre automatique",
    "Graph type": "Type de graphique",
    "Select a graph": "Selectionner un graphique",
    "Show legend": "Afficher la légende",
    "Date range": "Plage de date",
    "One Hour": "Une Heure",
    "Four Hours": "Quatre Heures",
    "Six Hours": "Six Heures",
    "Twelve Hours": "Douze Heures",
    "One Day": "Un jour",
    "One Week": "Une Semaine",
    "Two Weeks": "Deux Semaines",
    "One Month": "Un Mois",
    "Two Months": "Deux Mois",
    "Three Months": "Trois Mois",
    "One Year": "Un An",
    "Two Years": "Deux Ans",
    "Select a device": "Selectionner un appareil",
    "Port": "Port",
    "Select a port": "Selectionner un port",
    "Application": "Application",
    "Select an application": "Selectionner une application",
    "Munin plugin": "Plugin Munin",
    "Select a Munin plugin": "Selectionner un plugin Munin",
    "Bill": "Facture",
    "Select a bill": "Selectionner une facture",
    "Custom Aggregator(s)": "Agrégateur personnalisé(s)",
    "Select or add one or more": "Selectionner ou ajouter un ou plus",
    "Select one or more": "Selectionner un ou plus",
    "Top query": "Top requête",
    "Response time": "Temps de réponse",
    "Poller duration": "Poller duration",
    "Processor load": "Charge du processeur",
    "Memory usage": "Utilisation de la mémoire",
    "Disk usage": "Utilisation du disque",
    "Sort order": "Ordre de tri",
    "Ascending": "Croissant",
    "Descending": "Décroissant",
    "Number of Devices": "Nombre d'appareils",
    "Last Polled (minutes)": "Dernier sondage (minutes)",
    "Image URL": "Image URL",
    "Target URL": "URL cible",
    "Show acknowledged": "Afficher les acquittées",
    "not filtered": "non filtré",
    "show only acknowledged": "voir seulement les acquittées",
    "hide acknowledged": "cacher les acquittées",
    "Show only fired": "Show only fired",
    "show only fired alerts": "show only fired alerts",
    "Displayed severity": "Afficher la sévérité",
    "any severity": "toutes les sévérités",
    "or higher": "ou plus",
    "State": "Status",
    "any state": "tous les status",
    "All alerts": "Toutes les alertes",
    "Show Procedure field": "Afficher les procédures",
    "show": "afficher",
    "hide": "cacher",
    "Sort alerts by": "Trier les alertes par",
    "timestamp, descending": "horodatage, décroissant",
    "severity, descending": "sévérité, décroissant",
    "All devices": "Tous les appareils",
    "Event type": "Type d'évenement",
    "All types": "Tous types",
    "Number of interfaces": "Nombre d'interfaces",
    "Interface type": "Type d'interface",
    "All Ports": "Tous les ports",
    "Total": "Total",
    "Ignored": "Ignoré",
    "Errored": "Erreurs",
    "Services": "Services",
    "No devices found within interval.": "Aucun appareil découvert dans l’intervalle.",
    "Summary": "Sommaire",
    "Interface": "Interface",
    "Total traffic": "Trafic total",
    "Check your log for more details.": "Regarder vos log pour plus de détails",
    "If you need additional help, you can find how to get help at": "si vous avez besoin d’aide supplémentaire, vous pouvez trouver comment l'obtenir sur:",
    "Geo Locations": "Emplacements Géographiques",
    "All Locations": "Tous les Emplacements",
    "Pollers": "Sondeurs",
    "Groups": "Groupes",
    "Performance": "Performance",
    "History": "Historique",
    "passwords": {
      "password": "Le mot de passe doit comporter au moins six caractères et doit être le même que la confirmation.",
      "reset": "Votre mot de passe a été réinitialisé !",
      "sent": "Nous vous avons envoyé un lien pour mettre à jour votre mot de passe",
      "token": "Le jeton de mise à jour du mot de passe est valide.",
      "user": "Nous ne trouvons pas d'utilisateur avec cette adresse e-mail."
    },
    "sensors": {
      "airflow": {
        "short": "Flux d'air",
        "long": "Flux d'air",
        "unit": "cfm",
        "unit_long": "mètre cube par minute"
      },
      "ber": {
        "short": "BER",
        "long": "Taux d'erreurs",
        "unit": "",
        "unit_long": ""
      },
      "charge": {
        "short": "Charge",
        "long": "Pourcentage de charge",
        "unit": "%",
        "unit_long": "Pourcent"
      },
      "chromatic_dispersion": {
        "short": "Dispersion chromatique",
        "long": "Dispersion chromatique",
        "unit": "ps/nm/km",
        "unit_long": "Picosecondes par nanomètre par kilomètre"
      },
      "cooling": {
        "short": "Refroidissement",
        "long": "",
        "unit": "W",
        "unit_long": "Watts"
      },
      "count": {
        "short": "Count",
        "long": "Count",
        "unit": "",
        "unit_long": ""
      },
      "current": {
        "short": "Courant",
        "long": "Courant",
        "unit": "A",
        "unit_long": "Ampères"
      },
      "dbm": {
        "short": "dBm",
        "long": "dBm",
        "unit": "dBm",
        "unit_long": "Décibel-Milliwatts"
      },
      "delay": {
        "short": "Delay",
        "long": "Delay",
        "unit": "s",
        "unit_long": "Seconds"
      },
      "eer": {
        "short": "EER",
        "long": "Ratio d'efficacité énergétique",
        "unit": "",
        "unit_long": ""
      },
      "fanspeed": {
        "short": "Vitesse du ventilateur",
        "long": "Vitesse du ventilateur",
        "unit": "RPM",
        "unit_long": "Rotations par Minute"
      },
      "frequency": {
        "short": "Fréquence",
        "long": "Fréquence",
        "unit": "Hz",
        "unit_long": "Hertz"
      },
      "humidity": {
        "short": "Humidité",
        "long": "Pourcentage d'humidité",
        "unit": "%",
        "unit_long": "Pourcent"
      },
      "load": {
        "short": "Charge",
        "long": "Pourcentage de charge",
        "unit": "%",
        "unit_long": "Pourcent"
      },
      "power": {
        "short": "Puissance",
        "long": "Puissance",
        "unit": "W",
        "unit_long": "Watts"
      },
      "power_consumed": {
        "short": "Puissance consommée",
        "long": "Puissance consommée",
        "unit": "kWh",
        "unit_long": "Kilowatt-heure"
      },
      "power_factor": {
        "short": "Facteur de puissance",
        "long": "Facteur de puissance",
        "unit": "",
        "unit_long": ""
      },
      "pressure": {
        "short": "Pression",
        "long": "Pression",
        "unit": "kPa",
        "unit_long": "Kilopascal"
      },
      "quality_factor": {
        "short": "Facteur de qualité",
        "long": "Facteur de qualité",
        "unit": "",
        "unit_long": ""
      },
      "runtime": {
        "short": "Temps d'éxécution",
        "long": "Temps d'éxécution",
        "unit": "Min",
        "unit_long": "Minutes"
      },
      "signal": {
        "short": "Signal",
        "long": "Signal",
        "unit": "dBm",
        "unit_long": "Decibel-milliwatt"
      },
      "snr": {
        "short": "SNR",
        "long": "Rapport signal sur bruit",
        "unit": "dB",
        "unit_long": "Decibel"
      },
      "state": {
        "short": "Etat",
        "long": "Etat",
        "unit": ""
      },
      "temperature": {
        "short": "Température",
        "long": "Température",
        "unit": "°C",
        "unit_long": "° Celsius"
      },
      "voltage": {
        "short": "Voltage",
        "long": "voltage",
        "unit": "V",
        "unit_long": "Volts"
      },
      "waterflow": {
        "short": "Ecoulement de l'eau",
        "long": "Ecoulement de l'eau",
        "unit": "l/m",
        "unit_long": "Litres par minute"
      }
    },
    "wireless": {
      "ap-count": {
        "short": "APs",
        "long": "Nombre de points d'accès",
        "unit": ""
      },
      "clients": {
        "short": "Clients",
        "long": "Nombre de clients",
        "unit": ""
      },
      "capacity": {
        "short": "Capacité",
        "long": "Capacité",
        "unit": "%"
      },
      "ccq": {
        "short": "CCQ",
        "long": "Qualité de la connexion client",
        "unit": "%"
      },
      "errors": {
        "short": "Erreurs",
        "long": "Nombre d'erreurs",
        "unit": ""
      },
      "error-ratio": {
        "short": "Ratio d'erreur",
        "long": "Bit/Paquets ratio d'erreur",
        "unit": "%"
      },
      "error-rate": {
        "short": "BER",
        "long": "Taux d'erreur",
        "unit": "bps"
      },
      "frequency": {
        "short": "Fréquence",
        "long": "Fréquence",
        "unit": "MHz"
      },
      "distance": {
        "short": "Distance",
        "long": "Distance",
        "unit": "km"
      },
      "mse": {
        "short": "MSE",
        "long": "Erreur quadratique moyenne",
        "unit": "dB"
      },
      "noise-floor": {
        "short": "Bruit de fond",
        "long": "Bruit de fond",
        "unit": "dBm/Hz"
      },
      "power": {
        "short": "Puissance/Signal",
        "long": "TX/RX Puissance ou Signal",
        "unit": "dBm"
      },
      "quality": {
        "short": "Qualité",
        "long": "Qualité",
        "unit": "%"
      },
      "rate": {
        "short": "Taux",
        "long": "TX/RX Taux",
        "unit": "bps"
      },
      "rssi": {
        "short": "RSSI",
        "long": "Indicateur d'intensité du signal reçu",
        "unit": "dBm"
      },
      "snr": {
        "short": "SNR",
        "long": "Rapport signal sur bruit",
        "unit": "dB"
      },
      "ssr": {
        "short": "SSR",
        "long": "Rapport de force du signal",
        "unit": "dB"
      },
      "utilization": {
        "short": "Utilisation",
        "long": "utilisation",
        "unit": "%"
      },
      "xpi": {
        "short": "XPI",
        "long": "Interférence polaire croisée",
        "unit": "dB"
      }
    },
    "auth": {
      "failed": "Échec d'authentification",
      "throttle": "Trop de tentatives. Essaie dans quelques secondes."
    },
    "pagination": {
      "previous": "&laquo; Précédent",
      "next": "Suivant &raquo;"
    },
    "validation": {
      "accepted": "{attribute} doit être accepté.",
      "active_url": "{attribute} n'est pas une URL valide.",
      "after": "{attribute} doit être une date après {date}.",
      "after_or_equal": "{attribute} doit être une date après ou égale à {date}.",
      "alpha": "{attribute} ne peut contenir que des lettres.",
      "alpha_dash": "{attribute} ne peut contenir que des lettres, des chiffres, des tirets et des underscores.",
      "alpha_num": "{attribute} ne peut contenir que des lettres et des chiffres.",
      "alpha_space": "{attribute} ne peut contenir que des lettres, des chiffres, des underscores et des espaces.",
      "array": "{attribute} doit être un tableau.",
      "before": "{attribute} doit être une date avant {date}.",
      "before_or_equal": "{attribute} doit être une date antérieure ou égale à {date}.",
      "between": {
        "numeric": "{attribute} doit être entre {min} et {max}.",
        "file": "{attribute} doit être entre {min} et {max} kilobytes.",
        "string": "{attribute} doit être entre {min} et {max} caractères.",
        "array": "{attribute} doit être entre {min} et {max} objets."
      },
      "boolean": "{attribute} le champ doit être vrai ou faux.",
      "confirmed": "{attribute} la confirmation ne correspond pas.",
      "date": "{attribute} n'est pas une date valide.",
      "date_equals": "{attribute} doit être une date égale à {date}.",
      "date_format": "{attribute} ne correspond pas au format {format}.",
      "different": "{attribute} et {other} doit être différent.",
      "digits": "{attribute} doit être {digits} chiffres.",
      "digits_between": "{attribute} doit être entre {min} et {max} chiffres.",
      "dimensions": "{attribute} a des dimensions d'image non valides.",
      "distinct": "{attribute} le champ a une valeur en double.",
      "email": "{attribute} doit être une adresse e-mail valide.",
      "ends_with": "{attribute} doit se terminer par l’un des éléments suivants: {values}",
      "exists": "La sélection {attribute} est invalide.",
      "file": "{attribute} doit être un fichier.",
      "filled": "Le champ {attribute} doit avoir une valeur.",
      "gt": {
        "numeric": "{attribute} doit être supérieur à {value}.",
        "file": "{attribute} doit être supérieur à {value} kilobytes.",
        "string": "{attribute} doit être supérieur à {value} characters.",
        "array": "{attribute} doit avoir plus de {value} objets."
      },
      "gte": {
        "numeric": "{attribute} doit être supérieur ou égal {value}.",
        "file": "{attribute} doit être supérieur ou égal {value} kilobytes.",
        "string": "{attribute} doit être supérieur ou égal {value} caractères.",
        "array": "{attribute} doit avoir {value} objets ou plus."
      },
      "image": "{attribute} doit être une image.",
      "in": "La sélection {attribute} est invalide.",
      "in_array": "Le champ {attribute} n'existe pas dans {other}.",
      "integer": "{attribute} doit être un entier.",
      "ip": "{attribute} doit être une adresse IP valide.",
      "ipv4": "{attribute} doit être une adresse IPv4 valide.",
      "ipv6": "{attribute} doit être une adresse IPv6 valide.",
      "json": "{attribute} doit être une chaîne JSON valide.",
      "lt": {
        "numeric": "{attribute} doit être inférieur à {value}.",
        "file": "{attribute} doit être inférieur à {value} kilobytes.",
        "string": "{attribute} doit être inférieur à {value} caractères.",
        "array": "{attribute} doit avoir moins de {value} objets."
      },
      "lte": {
        "numeric": "{attribute} doit être inférieur ou égal {value}.",
        "file": "{attribute} doit être inférieur ou égal {value} kilobytes.",
        "string": "{attribute} doit être inférieur ou égal {value} caractères.",
        "array": "{attribute} ne doit pas avoir plus de {value} objets."
      },
      "max": {
        "numeric": "{attribute} ne peut pas être supérieur à {max}.",
        "file": "{attribute} ne peut pas être supérieur à {max} kilobytes.",
        "string": "{attribute} ne peut pas être supérieur à {max} caractères.",
        "array": "{attribute} peut ne pas avoir plus de {max} objets."
      },
      "mimes": "{attribute} doit être un fichier de type: {values}.",
      "mimetypes": "{attribute} doit être un fichier de type: {values}.",
      "min": {
        "numeric": "{attribute} doit être au moins {min}.",
        "file": "{attribute} doit être au moins {min} kilobytes.",
        "string": "{attribute} doit être au moins {min} caractères.",
        "array": "{attribute} doit avoir au moins {min} objets."
      },
      "not_in": "La sélection {attribute} est invalide.",
      "not_regex": "Le format {attribute} est invalide.",
      "numeric": "{attribute} must be a number.",
      "present": "Le champ {attribute} doit être présent.",
      "regex": "Le format {attribute} est invalide.",
      "required": "Le champ {attribute} est requis.",
      "required_if": "Le champ {attribute} est requis quand {other} est {value}.",
      "required_unless": "Le champ {attribute} est requis sauf {other} est dans {values}.",
      "required_with": "Le champ {attribute} est requis lorsque {values} est présent.",
      "required_with_all": "Le champ {attribute} est requis quand {values} est présent.",
      "required_without": "Le champ {attribute} est requis quand {values} n'est pas present.",
      "required_without_all": "Le champ {attribute} est requis quand aucun des {values} est présent.",
      "same": "{attribute} et {other} doit correspondre.",
      "size": {
        "numeric": "{attribute} doit être {size}.",
        "file": "{attribute} doit être {size} kilobytes.",
        "string": "{attribute} doit être {size} caractères.",
        "array": "{attribute} doit contenir {size} objets."
      },
      "starts_with": "{attribute} doit commencer par l’un des éléments suivants: {values}",
      "string": "{attribute} doit être une chaîne.",
      "timezone": "{attribute} doit être une zone valide.",
      "unique": "{attribute} a déjà été pris.",
      "uploaded": "{attribute} échec du téléchargement.",
      "url": "Le format {attribute} est invalide.",
      "uuid": "{attribute} doit être un UUID valide.",
      "custom": {
        "attribute-name": {
          "rule-name": "custom-message"
        }
      },
      "attributes": []
    },
    "preferences": {
      "lang": "Français"
    },
    "commands": {
      "user{add}": {
        "description": "Création d'un utilisateur local. Vous pourrez utiliser les identifiants créés si vous utilisez l'authentification mysql",
        "arguments": {
          "username": "Le nom d'utilisateur avec lequel l'utilisateur se connectera"
        },
        "options": {
          "descr": "Description de l'utilisateur",
          "email": "Email à utiliser pour l'utilisateur",
          "password": "Mot de passe de l'utilisateur, s'il n'est pas donné, il vous sera demandé de saisir un mot de passe.",
          "full-name": "Nom complet de l'utilisateur",
          "role": "Définir le rôle de l'utilisateur {roles}"
        },
        "password-request": "Veuillez entrer le mot de passe de l'utilisateur",
        "success": "Utilisateur ajouté avec succès : {username}",
        "wrong-auth": "Attention !  Vous ne pourrez pas vous connecter avec cet utilisateur car vous n'utilisez pas les auth MySQL."
      }
    }
  },
  "ru": {
    "Shutdown": "Shutdown",
    "The {attribute} must a valid IP address/network or hostname.": "{attribute} должен быть правильным IP адресом/подсетью или именем устройства.",
    "Never polled": "Никогда не опрашивался",
    "This indicates the most likely endpoint switchport": "Это указывает на наиболее вероятый порт устройства",
    "Two-Factor unlocked.": "Второй этап аутентификации разблокирован.",
    "Failed to unlock Two-Factor.": "Не удалось проверить второй этап аутентификации",
    "Two-Factor removed.": "Второй этап аутентификации удален.",
    "Failed to remove Two-Factor.": "Не удалось удалить второй этап аутентификации.",
    "TwoFactor auth removed.": "Двуфакторная аутентификация удалена.",
    "Too many two-factor failures, please contact administrator.": "Слишком много не удачных попыток, свяжитесь с администратором.",
    "Too many two-factor failures, please wait {time} seconds": "Слишком много неудачных попыток, подождите {time} секунд",
    "No Two-Factor Token entered.": "Не введен проверочный токен второго этап аутентификации.",
    "No Two-Factor settings, how did you get here?": "Второй этап аутентификации не настроен, как вы сюда смогли попасть?",
    "Wrong Two-Factor Token.": "Не правильный токен второго этап аутентификации.",
    "TwoFactor auth added.": "Второй этап аутентификации добавлен.",
    "User {username} created": "Пользователь {username} создан",
    "Failed to create user": "Не удалось создать пользователя",
    "Updated dashboard for {username}": "Обновлен главный жкран пользователя {username}",
    "User {username} updated": "Пользователь {username} обновлен",
    "Failed to update user {username}": "Не удалось обновить пользователя{username}",
    "User {username} deleted.": "Пользователь {username} удален.",
    "Device does not exist": "Устройство не существует",
    "Port does not exist": "Порт не существует",
    "App does not exist": "Приложение не существует",
    "Bill does not exist": "Счет не существует",
    "Munin plugin does not exist": "Munin плагин не существует",
    "Ok": "Ok",
    "Warning": "Предупреждение",
    "Critical": "Критический",
    "Existing password did not match": "Не совпадает с существующим паролем",
    "The {attribute} field is required.": "Поле {attribute} обязательно.",
    "Edit User": "Изменить пользователя",
    "Unlock": "Разблокировать",
    "User exceeded failures": "Пользователь допустил много ошибок",
    "Disable TwoFactor": "Отключить двуфакторную аутентификацию",
    "No TwoFactor key generated for this user, Nothing to do.": "Не сгенерирован ключ для двуфакторной аутентификации.",
    "Save": "Сохранить",
    "Cancel": "Отмена",
    "Unlocked Two Factor.": "Разблокирован двуфакторная аутентификация.",
    "Failed to unlock Two Factor": "Не удалось разблокировать двухфакторную аутентификацию",
    "Removed Two Factor.": "Убрана второй фактор.",
    "Failed to remove Two Factor": "Не удалось убрать второй фактор",
    "Real Name": "Настоящее имя",
    "Email": "Email",
    "Description": "Описание",
    "Level": "Уровень",
    "Normal": "Нормально",
    "Global Read": "Глобальное Чтение",
    "Admin": "Admin",
    "Demo": "Demo",
    "Dashboard": "Панель",
    "Password": "Пароль",
    "Current Password": "Действующий пароль",
    "New Password": "Новый пароль",
    "Confirm Password": "Подтверждение пароля",
    "Can Modify Password": "Можно изменить пароль",
    "Create User": "Создать пользователя",
    "Username": "Имя пользователя",
    "Manage Users": "Управление пользователями",
    "ID": "ID",
    "Access": "Доступ",
    "Auth": "Аутентификация",
    "Actions": "Действие",
    "Edit": "Изменить",
    "Delete": "Удалить",
    "Manage Access": "Управление доступом",
    "Add User": "Создать пользователя",
    "Are you sure you want to delete ": "Вы уверены, что хотите удалить ",
    "The user could not be deleted": "Пользователь не может быть удален",
    "Whoops, the web server could not write required files to the filesystem.": "Ой, вэб сервер не может записать файл в файловую систему.",
    "Running the following commands will fix the issue most of the time:": "Выполнение данной команды исправить ошибку в четении time:",
    "Whoops, looks like something went wrong. Check your librenms.log.": "Ой, что-то сломалось, проверьте librenms.log.",
    "Public Devices": "Общее устройство",
    "System Status": "Статус Системы",
    "Logon": "Вход",
    "Device": "Устройство",
    "Platform": "Платформа",
    "Uptime": "Аптайм",
    "Location": "Расположение",
    "Status": "Статус",
    "Remember Me": "Запомнить меня",
    "Login": "Логин",
    "Please enter auth token": "Пожалуйста введите токен авторизации",
    "Submit": "Выполнить",
    "Logout": "Выйти",
    "Locations": "Расположения",
    "Coordinates": "Координаты",
    "Devices": "Устройства",
    "Network": "Сеть",
    "Servers": "Сервера",
    "Firewalls": "Фаерволы",
    "Down": "Лежит",
    "Save changes": "Сохранить изменения",
    "N/A": "N/A",
    "Location must have devices to show graphs": "Расположение должно иметь устройства для отображения графиков",
    "Traffic": "Траффик",
    "Cannot delete locations used by devices": "Не могу удалить расположение, применяемое устройством",
    "Location deleted": "Расположение удалено",
    "Failed to delete location": "Не удалось удалить расположение",
    "Timestamp": "Метка времени",
    "Source": "Источник",
    "Message": "Сообщение",
    "Facility": "Объект",
    "Total hosts": "Всего хостов",
    "ignored": "Игнорируемо",
    "disabled": "Откючено",
    "up": "работает",
    "warn": "внимание",
    "down": "лежит",
    "Total services": "Всего сервисов",
    "Widget title": "Заготовок виджета",
    "Default Title": "Заголовок по умолчанию",
    "Columns": "Колонка",
    "Markers": "Маркеры",
    "Ports": "Порты",
    "Resolution": "Разрешение",
    "Countries": "Страны",
    "Provinces": "Провинции",
    "Metros": "Метро",
    "Region": "Регион",
    "Help": "Помощь",
    "Stream": "Поток",
    "All Messages": "Все сообщения",
    "All Devices": "Все устройства",
    "Page Size": "Размер страницы",
    "Time Range": "Интервал времени",
    "Search all time": "Искать постоянно",
    "Search last 5 minutes": "Искать последние 5 минут",
    "Search last 15 minutes": "Искать последние 15 минут",
    "Search last 30 minutes": "Искать последние 30 минут",
    "Search last 1 hour": "Искать последний 1 час",
    "Search last 2 hours": "Искать последние 2 часа",
    "Search last 8 hours": "Искать последние 8 часов",
    "Search last 1 day": "Искать последний 1 день",
    "Search last 2 days": "Искать последние 2 дня",
    "Search last 5 days": "Искать последние 5 дней",
    "Search last 7 days": "Искать последние 7 дней",
    "Search last 14 days": "Искать последние 14 дней",
    "Search last 30 days": "Искать последние 30 дней",
    "Custom title": "Изменяемое название",
    "Initial Latitude": "Начальная широта",
    "ie. 51.4800 for Greenwich": "51.4800 по Гринвичу",
    "Initial Longitude": "Начальная долгота",
    "ie. 0 for Greenwich": "0 по Гринвичу",
    "Initial Zoom": "Начальное увеличение",
    "Grouping radius": "Радиус группировки",
    "default 80": "по умолчанию 80",
    "Show devices": "показать устройства",
    "Up + Down": "подняты + лежат",
    "Up": "Поднято",
    "Show Services": "Показать сервисы",
    "no": "нет",
    "yes": "да",
    "Show Port Errors": "Показать ошибки портов",
    "Notes": "Заметки",
    "Custom title for widget": "Изменяемый заголовок для виджета",
    "Display type": "Отображаемый тип",
    "boxes": "боксы",
    "compact": "компактный",
    "Uniform Tiles": "Равномерная плитка",
    "Tile size": "Размер заголовка",
    "Disabled/ignored": "Выключенно/игнорированно",
    "Show": "Показать",
    "Hide": "Скрыть",
    "Mode select": "Выюбр режима",
    "only devices": "только устройства",
    "only services": "только сервисы",
    "devices and services": "устройства и сервисы",
    "Order By": "Группировать по",
    "Hostname": "Имя устройства",
    "Device group": "Группа устройств",
    "Automatic Title": "Автоматическое наименование",
    "Graph type": "Тип графика",
    "Select a graph": "Выберите граф",
    "Show legend": "Показать пояснение",
    "Date range": "Интервал дат",
    "One Hour": "Один час",
    "Four Hours": "Четыре часа",
    "Six Hours": "Шесть часов",
    "Twelve Hours": "Двенадцать часов",
    "One Day": "Один день",
    "One Week": "Одна неделя",
    "Two Weeks": "Две недели",
    "One Month": "Один месяц",
    "Two Months": "Два месяца",
    "Three Months": "Три месяца",
    "One Year": "Один Год",
    "Two Years": "Два Года",
    "Select a device": "Выберите устройство",
    "Port": "Порт",
    "Select a port": "Выберите порт",
    "Application": "Приложение",
    "Select an application": "Выберите приложение",
    "Munin plugin": "Munin плагин",
    "Select a Munin plugin": "Выберите Munin плагин",
    "Bill": "Счет",
    "Select a bill": "Выберите счйт",
    "Custom Aggregator(s)": "Пользовательский аггрегатор(ций)",
    "Select or add one or more": "Выберите или добавьте один или более",
    "Select one or more": "Выберите один или более",
    "Top query": "Основной запрос",
    "Response time": "Время ответа",
    "Poller duration": "Длительнось обработки",
    "Processor load": "Загрузка процессора",
    "Memory usage": "Использвание памяти",
    "Disk usage": "Использование диска",
    "Sort order": "Сортировка",
    "Ascending": "по возрастанию",
    "Descending": "по убыванию",
    "Number of Devices": "Номер устройсва",
    "Last Polled (minutes)": "Последняя обработка (минут)",
    "Image URL": "URL изображения",
    "Target URL": "URL цели",
    "Show acknowledged": "показать подтвержденные",
    "not filtered": "не отфильтрованно",
    "show only acknowledged": "показать только подтвержденные",
    "hide acknowledged": "скрыть подтвержденные",
    "Show only fired": "показать пропущенные",
    "show only fired alerts": "показать только пропущенные",
    "Displayed severity": "Отобразить важные",
    "any severity": "любая важность",
    "or higher": "или высокая",
    "State": "Состояние",
    "any state": "любое состояние",
    "All alerts": "Все предупреждения",
    "Show Procedure field": "Показать процедуру",
    "show": "показать",
    "hide": "скрыть",
    "Sort alerts by": "Отсортировать предупреждения по",
    "timestamp, descending": "метка времени, по возрастанию",
    "severity, descending": "важность, по возрастанию",
    "All devices": "Все устройства",
    "Event type": "Тип события",
    "All types": "Все типы",
    "Number of interfaces": "Количество интерфейсов",
    "Last polled (minutes)": "Последня обработка (минут)",
    "Interface type": "Тип интерфейса",
    "All Ports": "Все порты",
    "Total": "Всего",
    "Ignored": "Игнорированно",
    "Disabled": "Отключено",
    "Errored": "Ошибок",
    "Services": "Сервисы",
    "No devices found within interval.": "В этот интервал не попало ни одного устройсктва.",
    "Summary": "Суммарно",
    "Interface": "Интерфейс",
    "Total traffic": "Общий трафик",
    "Check your log for more details.": "Проверьте лог для большей информации..",
    "If you need additional help, you can find how to get help at": "Если вам нужна дополнительная помощь, вы можете узнать как ее получить",
    "Geo Locations": "Гео расположение",
    "All Locations": "Все расположения",
    "Pollers": "Обработчики",
    "Groups": "Группы",
    "Performance": "Производительность",
    "History": "История",
    "passwords": {
      "password": "Пароль должен содержать не менее шести символов и должен совпадать с подтверждением.",
      "reset": "Ваш пароль был сброшен!",
      "sent": "Мы отправили вам ссылку на email для обновления пароля",
      "token": "Токен обновления пароля валиден.",
      "user": "Мы не можем найти пользователя с таким адресом email."
    },
    "auth": {
      "failed": "Таких учетных данных нет",
      "throttle": "Слишком большое количество попыток. Попробуйте через {seconds} секунд."
    },
    "pagination": {
      "previous": "&laquo; Предидущая",
      "next": "Следующая &raquo;"
    },
    "validation": {
      "accepted": "Вы подтвердить {attribute}.",
      "active_url": "Данная {attribute} ссылка не является валидным URL.",
      "after": "{attribute} должна быть дата больше чем {date}.",
      "after_or_equal": "{attribute} должна быть дата больше или равная чем {date}.",
      "alpha": "{attribute} может содержать только буквы.",
      "alpha_dash": "{attribute} может содержать только буквы, числа тире и подчекивания.",
      "alpha_num": "{attribute} ожет содержать только буквыи числа.",
      "array": "{attribute} должен быть массивом.",
      "before": "{attribute} должна быть дыты меньше чем  {date}.",
      "before_or_equal": "{attribute} должна быть меньше или равно to {date}.",
      "between": {
        "numeric": "{attribute} должен быть в интервале между {min} и {max}.",
        "file": "{attribute} размер должен составлять от {min} до {max} kilobytes.",
        "string": "{attribute} должно быть от {min} до {max} символов.",
        "array": "{attribute} долже содержать от  {min} до {max} элементов."
      },
      "boolean": "{attribute} поле может иметь значения true или false.",
      "confirmed": "{attribute} не совпадает с подтверждением.",
      "date": "{attribute} даиа не валидна.",
      "date_equals": "{attribute} дата должна соответствовать {date}.",
      "date_format": "{attribute} не совпадает формат с {format}.",
      "different": "{attribute} и {other} должны отличаться друг от друга.",
      "digits": "{attribute} должен содержать {digits} чисел.",
      "digits_between": "{attribute} должен содержать не менее {min} и  не более {max} чисел.",
      "dimensions": "{attribute} не рисунок.",
      "distinct": "{attribute} поле содержит повторения.",
      "email": "{attribute} должен быть валидным email адресом.",
      "exists": "выбран не верный параметр: {attribute}.",
      "file": "{attribute} должен быть файлом.",
      "filled": "{attribute} поле должно иметь значения.",
      "gt": {
        "numeric": "{attribute} должно быть больше {value}.",
        "file": "{attribute} должен быть больше {value} kilobytes.",
        "string": "{attribute} должен иметь больше {value} символов.",
        "array": "{attribute} должен содержать не менее{value} элементов."
      },
      "gte": {
        "numeric": "{attribute} должно быть больше или равно {value}.",
        "file": "{attribute} должно быть больше или равно {value} kilobytes.",
        "string": "{attribute} должно быть больше или равно {value} символов.",
        "array": "{attribute} должно быть больше или равно {value} элементов."
      },
      "image": "{attribute} должно быть рисунком.",
      "in": "Вы выбрали не верный {attribute}.",
      "in_array": "{attribute} не относиться {other}.",
      "integer": "{attribute} должен быть цислом.",
      "ip": "{attribute} должен быть правильным IP адресом.",
      "ipv4": "{attribute} должен быть правильным IPv4 адресом.",
      "ipv6": "{attribute} должен быть правильным IPv6 адресом.",
      "json": "{attribute} должен быть правильным JSON.",
      "lt": {
        "numeric": "{attribute} должен быть меньше чем {value}.",
        "file": "{attribute} должен быть меньше чем  {value} kilobytes.",
        "string": "{attribute} должен быть меньше чем {value} символов.",
        "array": "{attribute} должен быть меньше чем  {value} элементов."
      },
      "lte": {
        "numeric": "{attribute} должен быть меньше или равен {value}.",
        "file": "{attribute} должен быть меньше или равен  {value} kilobytes.",
        "string": "{attribute} должен быть меньше или равен  {value} символам.",
        "array": "{attribute} должен быть меньше или равен  {value} элементам."
      },
      "max": {
        "numeric": "{attribute} не может быть больше {max}.",
        "file": "{attribute} не может быть больше {max} kilobytes.",
        "string": "{attribute} не может быть больше {max} символов.",
        "array": "{attribute} не может быть больше {max} элементов."
      },
      "mimes": "{attribute} должен соответствовать типу: {values}.",
      "mimetypes": "{attribute} должны соответствовать типу: {values}.",
      "min": {
        "numeric": "{attribute} должен быть меньше {min}.",
        "file": "{attribute} должен быть меньше {min} kilobytes.",
        "string": "{attribute} должен быть меньше {min} символов.",
        "array": "{attribute} должен быть меньше {min} элементов."
      },
      "not_in": "Выбран не верный {attribute}.",
      "not_regex": "{attribute} имеет не верный формат.",
      "numeric": "{attribute} должен быть числом.",
      "present": "{attribute} поле должно быть заполнено.",
      "regex": "{attribute} не верный формат.",
      "required": "{attribute} поля обязательно к заполнению.",
      "required_if": "{attribute} обязательно к заполнению {other} если {value}.",
      "required_unless": "{attribute} обязательно к заполнению если {other} содержить {values}.",
      "required_with": "{attribute} обязательно к заполению если {values} присутствует.",
      "required_with_all": "{attribute} надо заполнить если {values} заполнены.",
      "required_without": "{attribute} надо заполнить если {values} отсутствуют.",
      "required_without_all": "{attribute} надо заполнить если нет ни одного {values} ",
      "same": "The {attribute} and {other} must match.",
      "size": {
        "numeric": "{attribute} должен иметь {size}.",
        "file": "{attribute} должен иметь {size} kilobytes.",
        "string": "{attribute} должен иметь {size} символов.",
        "array": "{attribute} должен иметь {size} элементов."
      },
      "starts_with": "{attribute} должен начинаться с: {values}",
      "string": "{attribute} должен быть строкой.",
      "timezone": "{attribute} не верный часовой пояс.",
      "unique": "{attribute} уже используется.",
      "uploaded": "{attribute} не удалось загрузить.",
      "url": "{attribute} не верный фомат URL.",
      "uuid": "{attribute} должен иметь правильный UUID.",
      "custom": {
        "attribute-name": {
          "rule-name": "custom-message"
        }
      },
      "attributes": []
    },
    "preferences": {
      "lang": "русский"
    },
    "commands": {
      "user{add}": {
        "description": "Создание локального пользователя. Вы сможете воспользоваться созданными учетными данными если вы используете авторизацию mysql",
        "arguments": {
          "username": "Имя пользователя с которым вы будете проходить авторизацию"
        },
        "options": {
          "descr": "Описание пользователя",
          "email": "Email пользователя",
          "password": "Пароль пользователя, если не введен, то будет предложенно",
          "full-name": "Полное имя пользователя",
          "role": "Пользователю будет назначена роль {roles}"
        },
        "password-request": "Пожалуйста введите пароль",
        "success": "Успешно создан пользователь: {username}",
        "wrong-auth": "Внимание! вы не смогли пройти авторизация, так как вы не используете  MySQL авторизацию"
      }
    }
  },
  "uk": {
    "Shutdown": "Shutdown",
    "Login": "Вхід",
    "Register": "Реєстрація",
    "Check your log for more details": "Для отримання детальної інформації перегляньте лог-файл",
    "If you need additional help, you can find how to get help at": "За додатковою допомогою можливо звернутися до",
    "Overview": "Огляд",
    "Dashboard": "Дашборд",
    "Maps": "Мапи",
    "Availability": "Доступність",
    "Network": "Мережа",
    "Device Groups Maps": "Мапа груп пристроїв",
    "Geographical": "Географічна",
    "Plugins": "Плагіни",
    "Plugin Admin": "Управління плагінами",
    "Tools": "Інструменти",
    "Eventlog": "Лог подій",
    "Inventory": "Інвентар",
    "MIB definitions": "Визначення MIB",
    "Devices": "Пристрої",
    "All Devices": "Усі пристрої",
    "No devices": "Пристрої відсутні",
    "Geo Locations": "Місцезнаходження",
    "All Locations": "Усі місця",
    "MIB associations": "Визначені MIB",
    "Manage Groups": "Управління групами",
    "Device Dependencies": "Залежності пристроїв",
    "Add Device": "Додати пристрій",
    "Delete Device": "Видалити пристрій",
    "Services": "Сервіси",
    "All Services": "Усі сервіси",
    "Warning": "Попередження",
    "Critical": "Критичний стан",
    "Add Service": "Додати сервіс",
    "Ports": "Порти",
    "All Ports": "Усі порти",
    "Traffic Bills": "Білінг трафіку",
    "Pseudowires": "Псевдовайри",
    "Customers": "Споживачі",
    "Transit": "Транзит",
    "Core": "Ядро",
    "Alerts": "Сповіщення",
    "Down": "Недоступно",
    "Disabled": "Вимкнено",
    "Deleted": "Видалено",
    "Health": "Стан систем",
    "Memory": "Пам'ять",
    "Processor": "Процесор",
    "Storage": "Накопичувачі",
    "Wireless": "Бездротові",
    "Apps": "Застосунки",
    "Routing": "Маршрутизація",
    "Alerted": "Сповіщено",
    "Notifications": "Оповіщення",
    "Alert History": "Історія сповіщень",
    "Statistics": "Статистика",
    "Alert Rules": "Правила сповіщень",
    "Scheduled Maintenance": "Заплановане обслуговування",
    "Alert Templates": "Шаблони сповіщень",
    "Alert Transports": "Транспорт сповіщень",
    "My Settings": "Мої налаштування",
    "Settings": "Налаштування",
    "Global Settings": "Глобальні налаштування",
    "Validate Config": "Перевірка конфігурації",
    "Manage Users": "Управління користувачами",
    "Auth History": "Історія авторизації",
    "Peering": "Піринг",
    "Pollers": "Опитувачі",
    "API Settings": "Налаштування API",
    "API Docs": "Документація API",
    "The {attribute} must a valid IP address/network or hostname.": "{attribute} має бути правильною IP адресою/підмережею або ім'ям пристрою.",
    "Never polled": "Ніколи не опитувався",
    "This indicates the most likely endpoint switchport": "Це вказує на найбільш імовірний кінцевий switchport",
    "Two-Factor unlocked.": "Двофакторну автентифікацію пройдено.",
    "Failed to unlock Two-Factor.": "Не вдалося пройти двофакторну автентифікацію",
    "Two-Factor removed.": "Другий етап прибрано.",
    "Failed to remove Two-Factor.": "Не вдалося прибрати другий етап.",
    "TwoFactor auth removed.": "Двофакторну автентифікацію прибрано.",
    "Too many two-factor failures, please contact administrator.": "Забагато невдалих спроб двофакторної автентифікації, зв'яжіться з адміністратором.",
    "Too many two-factor failures, please wait {time} seconds": "Забагато невдалих спроб двофакторної автентифікації, зачекайте {time} секунд",
    "No Two-Factor Token entered.": "Не введено токен двофакторної автентифікації.",
    "No Two-Factor settings, how did you get here?": "Налаштування двофакторної автентифікації відсутні, як ви тут опинилися?",
    "Wrong Two-Factor Token.": "Невірний токен двофакторної автентифікації.",
    "TwoFactor auth added.": "Додано двофакторну автентифікацію.",
    "User {username} created": "Створено користувача {username}",
    "Failed to create user": "Не вдалося створити користувача",
    "Updated dashboard for {username}": "Оновлено дашборд користувача {username}",
    "User {username} updated": "Оновлено користувача {username}",
    "Failed to update user {username}": "Не вдалося оновити користувача {username}",
    "User {username} deleted.": "Видалено користувача {username}.",
    "Device does not exist": "Пристрою не існує",
    "Port does not exist": "Порта не існує",
    "App does not exist": "Застосунку не існує",
    "Bill does not exist": "Рахунку не існує",
    "Munin plugin does not exist": "Плагіну для Munin не існує",
    "Ok": "Ок",
    "Existing password did not match": "Не співпадає з існуючим паролем",
    "The {attribute} field is required.": "Поле {attribute} є обов'язковим.",
    "Edit User": "Редагувати користувача",
    "Unlock": "Розблокувати",
    "User exceeded failures": "Користувач допустив забагато помилок",
    "Disable TwoFactor": "Вимкнути другий етап",
    "No TwoFactor key generated for this user, Nothing to do.": "Не сгенерирован ключ для двуфакторной аутентификации.",
    "Save": "Сохранить",
    "Cancel": "Отмена",
    "Unlocked Two Factor.": "Разблокирован двуфакторная аутентификация.",
    "Failed to unlock Two Factor": "Не удалось разблокировать двухфакторную аутентификацию",
    "Removed Two Factor.": "Убрана второй фактор.",
    "Failed to remove Two Factor": "Не удалось убрать второй фактор",
    "Real Name": "Повне ім'я",
    "Email": "Адреса електронної пошти",
    "Description": "Опис",
    "Level": "Рівень",
    "Normal": "Звичайний",
    "Global Read": "Глобальне читання",
    "Admin": "Адміністратор",
    "Demo": "Демо",
    "Password": "Пароль",
    "Current Password": "Поточний пароль",
    "New Password": "Новий пароль",
    "Confirm Password": "Підтвердження пароля",
    "Can Modify Password": "Може змінити пароль",
    "Create User": "Створити користувача",
    "Username": "Ім'я користувача",
    "ID": "ID",
    "Access": "Доступ",
    "Auth": "Автентифікація",
    "Actions": "Дії",
    "Edit": "Редагувати",
    "Delete": "Видалити",
    "Manage Access": "Управління доступом",
    "Add User": "Додати користувача",
    "Are you sure you want to delete ": "Ви впевнені, що бажаєте видалити ",
    "The user could not be deleted": "Користувач не може бути видалений",
    "Whoops, the web server could not write required files to the filesystem.": "Упс, веб сервер не зміг записати необхідні файли до файлової системи.",
    "Running the following commands will fix the issue most of the time:": "Виконання наступних команд у більшості випадків призведе до вирішення проблеми:",
    "Whoops, looks like something went wrong. Check your librenms.log.": "Упс, щось пішло не так. Перевірте свій librenms.log.",
    "Public Devices": "Публічні пристрої",
    "System Status": "Статус системи",
    "Logon": "Вхід",
    "Device": "Пристрій",
    "Platform": "Платформа",
    "Uptime": "Час роботи",
    "Location": "Місцезнаходження",
    "Status": "Статус",
    "Remember Me": "Запам'ятати мене",
    "Please enter auth token": "Будь ласка, введіть токен авторизації",
    "Submit": "Надіслати",
    "Logout": "Вийти",
    "Locations": "Місцезнаходження",
    "Coordinates": "Координати",
    "Servers": "Сервери",
    "Firewalls": "Фаєрволи",
    "Save changes": "Зберегти зміни",
    "N/A": "N/A",
    "Location must have devices to show graphs": "Для відображення графіків місцезнаходження повинне містити пристрої",
    "Traffic": "Трафік",
    "Cannot delete locations used by devices": "Неможливо видалити місцезнаходження що використовується пристроєм",
    "Location deleted": "Місцезнаходження видалено",
    "Failed to delete location": "Не вдалося видалиити місцезнаходження",
    "Timestamp": "Часова мітка",
    "Source": "Джерело",
    "Message": "Повідомлення",
    "Facility": "Об'єкт",
    "Total hosts": "Усього хостів",
    "ignored": "проігноровано",
    "disabled": "відключено",
    "up": "норма",
    "warn": "попередження",
    "down": "недоступно",
    "Total services": "Усього сервісів",
    "Widget title": "Назва віджета",
    "Default Title": "Назва за замовчуванням",
    "Columns": "Колонки",
    "Markers": "Маркери",
    "Resolution": "Отримання адреси",
    "Countries": "Країни",
    "Provinces": "Провінції",
    "Metros": "Мегаполіси",
    "Region": "Регіон",
    "Help": "Допомога",
    "Stream": "Потік",
    "All Messages": "Усі повідомлення",
    "Page Size": "Розмір сторінки",
    "Time Range": "Інтервал часу",
    "Search all time": "Шукати за весь час",
    "Search last 5 minutes": "Шукати за останні 5 хвилин",
    "Search last 15 minutes": "Шукати за останні 15 хвилин",
    "Search last 30 minutes": "Шукати за останні 30 хвилин",
    "Search last 1 hour": "Шукати за останню 1 годину",
    "Search last 2 hours": "Шукати за останні 2 години",
    "Search last 8 hours": "Шукати за останні 8 годин",
    "Search last 1 day": "Шукати за останній 1 день",
    "Search last 2 days": "Шукати за останні 2 дні",
    "Search last 5 days": "Шукати за останні 5 днів",
    "Search last 7 days": "Шукати за останні 7 днів",
    "Search last 14 days": "Шукати за останні 14 днів",
    "Search last 30 days": "Шукати за останні 30 днів",
    "Custom title": "Користувацька назва",
    "Initial Latitude": "Початкова широта",
    "ie. 51.4800 for Greenwich": "наприклад, 51.4800 для Гринвіча",
    "Initial Longitude": "Початкова довгота",
    "ie. 0 for Greenwich": "наприклад, 0 для Гринвіча",
    "Initial Zoom": "Початкове збільшення",
    "Grouping radius": "Радіус групування",
    "default 80": "за замовчуванням 80",
    "Show devices": "Показати пристрої",
    "Up + Down": "Норма + Недоступно",
    "Up": "Норма",
    "Show Services": "Показати сервіси",
    "no": "ні",
    "yes": "так",
    "Show Port Errors": "Показати помилки на портах",
    "Notes": "Нотатки",
    "Custom title for widget": "Користувацька назва віджета",
    "Display type": "Тип відображення",
    "boxes": "прямокутниками",
    "compact": "компактний",
    "Uniform Tiles": "Рівномірні плитки",
    "Tile size": "Розмір плиток",
    "Disabled/ignored": "Відключено/проігноровано",
    "Show": "Показати",
    "Hide": "Приховати",
    "Mode select": "Вибір режиму",
    "only devices": "лише пристрої",
    "only services": "лише сервіси",
    "devices and services": "пристрої та сервіси",
    "Order By": "Впорядкувати за",
    "Hostname": "Ім'я хосту",
    "Device group": "Група пристроїв",
    "Automatic Title": "Автоматично назва",
    "Graph type": "Тип графіка",
    "Select a graph": "Оберіть графік",
    "Show legend": "Показати легенду",
    "Date range": "Часовий проміжок",
    "One Hour": "Одна година",
    "Four Hours": "Чотири години",
    "Six Hours": "Шість годин",
    "Twelve Hours": "Дванадцять годин",
    "One Day": "Один день",
    "One Week": "Один тиждень",
    "Two Weeks": "Два тижні",
    "One Month": "Один місяць",
    "Two Months": "Два місяця",
    "Three Months": "Три місяця",
    "One Year": "Один рік",
    "Two Years": "Два роки",
    "Select a device": "Оберіть пристрій",
    "Port": "Порт",
    "Select a port": "Оберіть порт",
    "Application": "Застосунок",
    "Select an application": "Оберіть застосунок",
    "Munin plugin": "Плагін Munin",
    "Select a Munin plugin": "Оберіть плагін Munin",
    "Bill": "Рахунок",
    "Select a bill": "Оберіть рахунок",
    "Custom Aggregator(s)": "Користувацький агрегатор(и)",
    "Select or add one or more": "Оберіть або додайте один або більше",
    "Select one or more": "Оберіть один або більше",
    "Top query": "Найпопулярнійший запит",
    "Response time": "Час відповіді",
    "Poller duration": "Тривалість опитування",
    "Processor load": "Завантаження процесору",
    "Memory usage": "Використання пам'яті",
    "Disk usage": "Використання дискового простору",
    "Sort order": "Порядок сортування",
    "Ascending": "За зростанням",
    "Descending": "За спаданням",
    "Number of Devices": "Кількість пристроїв",
    "Last Polled (minutes)": "Останній раз опитувався (хвилин)",
    "Image URL": "URL зображення",
    "Target URL": "Цільовий URL",
    "Show acknowledged": "Показати підтверджені",
    "not filtered": "не відфільтровано",
    "show only acknowledged": "показати лише підтверджені",
    "hide acknowledged": "приховати підтверджені",
    "Show only fired": "Показати лише спрацювання",
    "show only fired alerts": "показати лише спрацювавші сповіщення",
    "Displayed severity": "Відображувана важливість",
    "any severity": "будь-якої важливості",
    "or higher": "або вищої",
    "State": "Стан",
    "any state": "будь-який стан",
    "All alerts": "Усі сповіщення",
    "Show Procedure field": "Показати процедуру",
    "show": "показати",
    "hide": "приховати",
    "Sort alerts by": "Сортувати сповіщення за",
    "timestamp, descending": "часом, за спаданням",
    "severity, descending": "важливістю, за спаданням",
    "Event type": "Тип події",
    "All types": "Усі типи",
    "Number of interfaces": "Кількість інтерфейсів",
    "Last polled (minutes)": "Останнє опитування (хвилин)",
    "Interface type": "Тип інтерфейса",
    "Total": "Усього",
    "Ignored": "Ігноровано",
    "Errored": "Мають помилки",
    "No devices found within interval.": "У інтервалі не було знайдено жодного пристрою.",
    "Summary": "Загалом",
    "Interface": "Інтерфейс",
    "Total traffic": "Спільний трафік",
    "Groups": "Групи",
    "Performance": "Продуктивність",
    "History": "Історія",
    "passwords": {
      "password": "Паролі мають бути щонайменш шість символів у довжину та співпадати з повтореним.",
      "reset": "Ваш пароль було скинуто!",
      "sent": "Ми надіслали посилання на скид паролю на вашу адресу електронної пошти!",
      "token": "Цей токен скиду паролю не валідний.",
      "user": "Ми не можемо знайти користувача з такою адресою електронної пошти."
    },
    "sensors": {
      "airflow": {
        "short": "Повітряний потік",
        "long": "Повітряний потік",
        "unit": "cfm",
        "unit_long": "Кубічний фут в хвилину"
      },
      "ber": {
        "short": "BER",
        "long": "Частота бітових помилок",
        "unit": "",
        "unit_long": ""
      },
      "charge": {
        "short": "Заряд",
        "long": "Процент заряду",
        "unit": "%",
        "unit_long": "процентів"
      },
      "chromatic_dispersion": {
        "short": "Хроматична дисперсія",
        "long": "Хроматична дисперсія",
        "unit": "ps/nm/km",
        "unit_long": "Пікосекунди на нанометри на кілометри"
      },
      "cooling": {
        "short": "Охолодження",
        "long": "",
        "unit": "W",
        "unit_long": "Ват"
      },
      "count": {
        "short": "Лічильник",
        "long": "Лічильник",
        "unit": "",
        "unit_long": ""
      },
      "current": {
        "short": "Сила струму",
        "long": "Сила струму",
        "unit": "A",
        "unit_long": "Ампер"
      },
      "dbm": {
        "short": "dBm",
        "long": "dBm",
        "unit": "dBm",
        "unit_long": "Децибел-мілівати"
      },
      "delay": {
        "short": "Затримка",
        "long": "Затримка",
        "unit": "s",
        "unit_long": "Секунди"
      },
      "eer": {
        "short": "EER",
        "long": "Коефіцієнт енергоефективності",
        "unit": "",
        "unit_long": ""
      },
      "fanspeed": {
        "short": "Оберти вентилятора",
        "long": "Швидкість обертів вентилятора",
        "unit": "RPM",
        "unit_long": "Обертів за хвилину"
      },
      "frequency": {
        "short": "Частота",
        "long": "Частота",
        "unit": "Hz",
        "unit_long": "Герц"
      },
      "humidity": {
        "short": "Вологість",
        "long": "Процент вологості повітря",
        "unit": "%",
        "unit_long": "Проценти"
      },
      "load": {
        "short": "Навантаження",
        "long": "Процент навантаження",
        "unit": "%",
        "unit_long": "Проценти"
      },
      "power": {
        "short": "Потужність",
        "long": "Потужність",
        "unit": "W",
        "unit_long": "Ват"
      },
      "power_consumed": {
        "short": "Спожита потужність",
        "long": "Спожита потужність",
        "unit": "kWh",
        "unit_long": "Кіловат-години"
      },
      "power_factor": {
        "short": "Коефіцієнт потужності",
        "long": "Коефіцієнт потужності",
        "unit": "",
        "unit_long": ""
      },
      "pressure": {
        "short": "Тиск",
        "long": "Тиск",
        "unit": "kPa",
        "unit_long": "Кілопаскалі"
      },
      "quality_factor": {
        "short": "Добротність",
        "long": "Добротність",
        "unit": "",
        "unit_long": ""
      },
      "runtime": {
        "short": "Час роботи",
        "long": "Час роботи",
        "unit": "хвилини",
        "unit_long": "Хвилини"
      },
      "signal": {
        "short": "Сигнал",
        "long": "Сигнал",
        "unit": "dBm",
        "unit_long": "Децибел-мілівати"
      },
      "snr": {
        "short": "SNR",
        "long": "Відношення сигналу до шуму",
        "unit": "dB",
        "unit_long": "Децибели"
      },
      "state": {
        "short": "Стан",
        "long": "Стан",
        "unit": ""
      },
      "temperature": {
        "short": "Температура",
        "long": "Температура",
        "unit": "°C",
        "unit_long": "° Цельсія"
      },
      "voltage": {
        "short": "Напруга",
        "long": "Напруга",
        "unit": "V",
        "unit_long": "Вольт"
      },
      "waterflow": {
        "short": "Потік води",
        "long": "Потік води",
        "unit": "l/m",
        "unit_long": "Літри за хвилину"
      }
    },
    "wireless": {
      "ap-count": {
        "short": "AP",
        "long": "Кількість точок доступу",
        "unit": ""
      },
      "clients": {
        "short": "Споживачі",
        "long": "Кількість споживачів",
        "unit": ""
      },
      "capacity": {
        "short": "Ємність",
        "long": "Ємність",
        "unit": "%"
      },
      "ccq": {
        "short": "CCQ",
        "long": "Якість з'єднання споживача",
        "unit": "%"
      },
      "errors": {
        "short": "Помилки",
        "long": "Кількість помилок",
        "unit": ""
      },
      "error-ratio": {
        "short": "Відсоток помилок",
        "long": "Відсоток помилкових бітів/пакетів",
        "unit": "%"
      },
      "error-rate": {
        "short": "BER",
        "long": "Частота бітових помилок",
        "unit": "bps"
      },
      "frequency": {
        "short": "Частота",
        "long": "Частота",
        "unit": "MHz"
      },
      "distance": {
        "short": "Відстань",
        "long": "Відстань",
        "unit": "km"
      },
      "mse": {
        "short": "MSE",
        "long": "Середньоквадратична помилка",
        "unit": "dB"
      },
      "noise-floor": {
        "short": "Рівень шуму",
        "long": "Рівень шуму",
        "unit": "dBm/Hz"
      },
      "power": {
        "short": "Сила сигналу",
        "long": "TX/RX Сила сигналу",
        "unit": "dBm"
      },
      "quality": {
        "short": "Якість",
        "long": "Якість",
        "unit": "%"
      },
      "rate": {
        "short": "Швидкість передачі даних",
        "long": "Швидкість передачі даних TX/RX",
        "unit": "bps"
      },
      "rssi": {
        "short": "RSSI",
        "long": "Індикатор потужності отриманого сигналу",
        "unit": "dBm"
      },
      "snr": {
        "short": "SNR",
        "long": "Співвідношення сигнал/шум",
        "unit": "dB"
      },
      "ssr": {
        "short": "SSR",
        "long": "Коефіцієнт потужності сигналу",
        "unit": "dB"
      },
      "utilization": {
        "short": "Використання",
        "long": "Використання",
        "unit": "%"
      },
      "xpi": {
        "short": "XPI",
        "long": "Крос-поляризаційні завади",
        "unit": "dB"
      }
    },
    "auth": {
      "failed": "Ці облікові дані не відповідають нашим записам.",
      "throttle": "Перевищено кількість спроб входу. Будь ласка, спробуйте ще раз через {seconds} секунд."
    },
    "pagination": {
      "previous": "&laquo; Попередня",
      "next": "Наступна &raquo;"
    },
    "validation": {
      "accepted": "{attribute} має бути прийнято.",
      "active_url": "{attribute} не є дійсним URL.",
      "after": "{attribute} має бути датою пізнішою за {date}.",
      "after_or_equal": "{attribute} має бути датою не ранішою за {date}.",
      "alpha": "{attribute} має містити лише літери.",
      "alpha_dash": "{attribute} може містити лише літери, цифри, тире та підкреслення.",
      "alpha_num": "{attribute} може містити лише літери та цифри.",
      "array": "{attribute} має бути масивом.",
      "before": "{attribute} має бути датою ранішою за {date}.",
      "before_or_equal": "{attribute} має бути датою не пізнішою за {date}.",
      "between": {
        "numeric": "{attribute} має бути у проміжку між {min} та {max}.",
        "file": "{attribute} має бути у проміжку між {min} та {max} кілобайт.",
        "string": "{attribute} має містити від {min} до {max} символів.",
        "array": "{attribute} має містити від {min} до {max} елементів."
      },
      "boolean": "{attribute} має бути true чи false.",
      "confirmed": "Підтвердження {attribute} не співпадає.",
      "date": "{attribute} не є коректною датою.",
      "date_equals": "{attribute} має бути рівним {date}.",
      "date_format": "{attribute} не співпадає з форматом {format}.",
      "different": "{attribute} та {other} повинні мати різні значення.",
      "digits": "{attribute} має містити {digits} цифр.",
      "digits_between": "{attribute} має містити від {min} до {max} цифр.",
      "dimensions": "{attribute} містить некоректні виміри зображення.",
      "distinct": "Поле {attribute} містить однакове значення.",
      "email": "{attribute} має бути дійсною адресою електронної пошти.",
      "exists": "Обраний {attribute} не є валідним.",
      "file": "{attribute} має бути файлом.",
      "filled": "Поле {attribute} повинне мати значення.",
      "gt": {
        "numeric": "{attribute} має бути більшим за {value}.",
        "file": "{attribute} має бути більшим за {value} кілобайт.",
        "string": "{attribute} має бути довшим за {value} символів.",
        "array": "{attribute} повинен містити більше ніж {value} елементів."
      },
      "gte": {
        "numeric": "{attribute} має бути не меншим за {value}.",
        "file": "{attribute} має бути не меншим за {value} кілобайт.",
        "string": "{attribute} має бути не коротшим за {value} символів.",
        "array": "{attribute} повинен містити не менше ніж {value} елементів."
      },
      "image": "{attribute} має бути зображенням.",
      "in": "Обраний {attribute} не є валідним.",
      "in_array": "Поле {attribute} не існує у {other}.",
      "integer": "{attribute} має бути типу integer.",
      "ip": "{attribute} має бути валідною IP адресою.",
      "ipv4": "{attribute} має бути валідною IPv4.",
      "ipv6": "{attribute} має бути валідною IPv6 адресою.",
      "json": "{attribute} має бути валідним JSON.",
      "lt": {
        "numeric": "{attribute} має бути меншим за {value}.",
        "file": "{attribute} має бути меншим за {value} кілобайт.",
        "string": "{attribute} має бути коротшим за {value} символів.",
        "array": "{attribute} повинен містити менше ніж {value} елементів."
      },
      "lte": {
        "numeric": "{attribute} має бути не більшим за {value}.",
        "file": "{attribute} має бути не більшим за {value} кілобайт.",
        "string": "{attribute} має бути не довшим за {value} символів.",
        "array": "{attribute} повинен містити не більше ніж {value} елементів."
      },
      "max": {
        "numeric": "{attribute} не може бути більшим за {max}.",
        "file": "{attribute} не може бути більшим за {max} кілобайт.",
        "string": "{attribute} не може бути довшим за {max} символів.",
        "array": "{attribute} не може мати більше ніж {max} елементів."
      },
      "mimes": "{attribute} має бути файлом типу: {values}.",
      "mimetypes": "{attribute} має бути файлом типу: {values}.",
      "min": {
        "numeric": "{attribute} має бути щонайменше {min}.",
        "file": "{attribute} має бути щонайменше {min} кілобайт.",
        "string": "{attribute} має бути щонайменше {min} символів.",
        "array": "{attribute} має містити щонайменше {min} елементів."
      },
      "not_in": "Обраний {attribute} не валідний.",
      "not_regex": "Формат {attribute} не валідний.",
      "numeric": "{attribute} має бути числом.",
      "present": "Поле {attribute} має бути наявним.",
      "regex": "Формат {attribute} не валідний.",
      "required": "Необхідне поле {attribute}.",
      "required_if": "Поле {attribute} необхідне коли {other} має значення {value}.",
      "required_unless": "Поле {attribute} необхідне, окрім випадків коли {other} має значення {values}.",
      "required_with": "Поле {attribute} необхідне при наявності одного з {values}.",
      "required_with_all": "Поле {attribute} необхідне при наявності усіх перерахованих {values}.",
      "required_without": "Поле {attribute} необхідне за відсутності одного з {values}.",
      "required_without_all": "Поле {attribute} необхідне за відсутності усіх перерахованих {values}.",
      "same": "{attribute} та {other} повинні співпадати.",
      "size": {
        "numeric": "{attribute} має бути {size}.",
        "file": "{attribute} має бути {size} кілобайт.",
        "string": "{attribute} повинен складати {size} символів.",
        "array": "{attribute} повинен містити {size} елементів."
      },
      "starts_with": "{attribute} повинен починатися з одного з наступних: {values}",
      "string": "{attribute} має бути типу string.",
      "timezone": "{attribute} має бути валідною часовою зоною.",
      "unique": "{attribute} вже призначений.",
      "uploaded": "{attribute} не було завантажено успішно.",
      "url": "Формат {attribute} є не валідним.",
      "uuid": "{attribute} має бути валідним UUID.",
      "custom": {
        "attribute-name": {
          "rule-name": "custom-message"
        }
      },
      "attributes": []
    },
    "preferences": {
      "lang": "Українська"
    },
    "commands": {
      "user{add}": {
        "description": "Додати локального користувача, вхід доступний лише при використанні mysql автентифікації",
        "arguments": {
          "username": "Ім'я користувача для входу"
        },
        "options": {
          "descr": "Опис користувача",
          "email": "Електронна пошта користувача",
          "password": "Пароль користувача, якщо не наданий, то буде запропоновано",
          "full-name": "Повне ім'я користувача",
          "role": "Користувачеві буде назначено роль {roles}"
        },
        "password-request": "Будь ласка, введіть пароль користувача",
        "success": "Успішно додано користувача: {username}",
        "wrong-auth": "Увага! Ви не зможете авторизуватися, оскільки не використовуєте MySQL авторизацію"
      }
    }
  },
  "zh-TW": {
    "Priority": "重要性",
    "Program": "程式",
    "Authlog": "驗證歷程",
    "User": "使用者",
    "IP Address": "IP 位址",
    "Result": "結果",
    "Translation not fully supported": "多國語系功能尚未完備",
    "Warning {service_count}": "警告 {service_count}",
    "Critical {service_count}": "嚴重 {service_count}",
    "Disabled {port_count}": "已停用 {port_count}",
    "Errored {port_count}": "發生錯誤 {port_count}",
    "Ignored {port_count}": "已忽略 {port_count}",
    "Down {port_count}": "已關閉 {port_count}",
    "Deleted {port_count}": "已刪除 {port_count}",
    "About {project_name}": "關於 {project_name}",
    "See the <a href=\"{url}\">list of contributors</a> on GitHub.": "至 GitHub 觀看<a href=\"{url}\">貢獻者名單</a>。",
    "Docs": "文件",
    "Close": "關閉",
    "LibreNMS is an autodiscovering PHP/MySQL-based network monitoring system": "LibreNMS 是個以 PHP/MySQL 為基底的自動探索網路監控系統",
    "Packages": "軟體包",
    "Disabled": "已停用",
    "Version": "版本",
    "Database Schema": "資料庫綱要",
    "Web Server": "Web 伺服器",
    "LibreNMS is a community-based project": "LibreNMS 是建基於社群開發的專案",
    "Please feel free to join us and contribute code, documentation, and bug reports:": "您隨時都可以加入社群貢獻您的程式碼、文件以及問題回報：",
    "Web site": "官方網站",
    "Bug tracker": "問題追蹤",
    "Community Forum": "社群論壇",
    "Changelog": "變更記錄",
    "Local git log": "本機 Git 記錄",
    "Contributors": "貢獻者",
    "Acknowledgements": "特別感謝",
    "Opt in to send anonymous usage statistics to LibreNMS?": "您要選擇以匿名方式提供使用統計資料給 LibreNMS 嗎？",
    "Online stats:": "線上統計資料",
    "Clear remote stats": "清除遠端統計資料",
    "IPv4 Addresses": "IPv4 位址",
    "IPv4 Networks": "IPv4 網路",
    "IPv6 Addresses": "IPv6 位址",
    "IPv6 Networks": "IPv6 網路",
    "Processors": "處理器",
    "Applications": "應用",
    "Disk I/O": "磁碟 I/O",
    "Syslog Entries": "Syslog 項目",
    "Eventlog Entries": "事件記錄項目",
    "Sensors": "感測器",
    "Wireless Sensors": "無線感測器",
    "Toner": "碳粉",
    "License": "授權",
    "Shutdown": "關機",
    "Select Devices": "選擇裝置",
    "Dynamic": "動態",
    "Static": "靜態",
    "Define Rules": "預設規則",
    "Create Device Group": "建立裝置群組",
    "Edit Device Group": "編輯裝置群組",
    "New Device Group": "新增裝置群組",
    "Pattern": "模式",
    "Type": "類型",
    "Name": "名稱",
    "User Preferences": "使用者喜好設定",
    "Global Administrative Access": "全域管理存取",
    "Device Permissions": "裝置權限",
    "Preferences": "喜好設定",
    "Language": "語言",
    "Change Password": "變更密碼",
    "Verify New Password": "確認新密碼",
    "Peering + Transit": "互連 + 轉送",
    "FDB Tables": "FDB 對照表",
    "ARP Tables": "ARP 對照表",
    "MAC Address": "MAC 位址",
    "IPv6 Address": "IPv6 位址",
    "IPv4 Address": "IPv4 位址",
    "Package": "軟體包",
    "Virtual Machines": "虛擬機器",
    "Device Groups": "裝置群組",
    "Register": "註冊",
    "Overview": "概觀",
    "Maps": "地圖",
    "Availability": "可用性",
    "Device Groups Maps": "裝置群組地圖",
    "Geographical": "地理",
    "Plugins": "外掛",
    "Plugin Admin": "外掛管理",
    "Tools": "工具",
    "Eventlog": "事件記錄",
    "Inventory": "設備",
    "MIB definitions": "MIB 定義",
    "No devices": "沒有裝置",
    "MIB associations": "MIB 關聯",
    "Manage Groups": "群組管理",
    "Device Dependencies": "裝置相依性",
    "Add Device": "新增裝置",
    "Delete Device": "刪除裝置",
    "All Services": "所有服務",
    "Add Service": "新增服務",
    "Traffic Bills": "流量帳單",
    "Pseudowires": "虛擬線路",
    "Customers": "客戶",
    "Transit": "轉送",
    "Core": "核心",
    "Alerts": "警報",
    "Deleted": "已刪除",
    "Health": "健康情況",
    "Memory": "記憶體",
    "Processor": "處理器",
    "Storage": "儲存",
    "Wireless": "無線",
    "Apps": "應用程式",
    "Routing": "路由",
    "Alerted": "已警報",
    "Notifications": "通知",
    "Alert History": "警報歷程",
    "Statistics": "統計資料",
    "Alert Rules": "警報規則",
    "Scheduled Maintenance": "定期維護",
    "Alert Templates": "警報範本",
    "Alert Transports": "警報傳送",
    "My Settings": "我的設定",
    "Settings": "設定",
    "Global Settings": "全域設定",
    "Validate Config": "組態驗證",
    "Auth History": "驗證歷程",
    "Peering": "互連",
    "API Settings": "API 設定",
    "API Docs": "API 文件",
    "The {attribute} must a valid IP address/network or hostname.": "這個 {attribute} 必需為有效的 IP 位址/網路或主機名稱。",
    "Never polled": "從未輪詢",
    "This indicates the most likely endpoint switchport": "這將表示為最有可能的交換器連接埠端點",
    "Two-Factor unlocked.": "雙因素驗證已解鎖。",
    "Failed to unlock Two-Factor.": "雙因素驗證解鎖失敗。",
    "Two-Factor removed.": "雙因素驗證已移除。",
    "Failed to remove Two-Factor.": "雙因素驗證移除失敗。",
    "TwoFactor auth removed.": "雙因素驗證已移除。",
    "Too many two-factor failures, please contact administrator.": "雙因素驗證失敗次數太多，請年繫您的系統管理員，",
    "Too many two-factor failures, please wait {time} seconds": "雙因素驗證失敗次數太多，請等候 {time} 秒再試",
    "No Two-Factor Token entered.": "沒有輸入雙因素驗證權仗。",
    "No Two-Factor settings, how did you get here?": "沒有設定雙因素驗證，您要如何使用呢？",
    "Wrong Two-Factor Token.": "錯誤的雙因素驗證權仗。",
    "TwoFactor auth added.": "雙因素驗證已加入。",
    "User {username} created": "使用者 {username} 已建立",
    "Failed to create user": "建立使用者失敗",
    "Updated dashboard for {username}": "已更新 {username} 的資訊看版",
    "User {username} updated": "使用者 {username} 已更新",
    "Failed to update user {username}": "更新使用者 {username} 失敗",
    "User {username} deleted.": "使用者 {username} 已刪除。",
    "Device does not exist": "裝置不存在",
    "Port does not exist": "連接埠不存在",
    "App does not exist": "應用不存在",
    "Bill does not exist": "帳單不存在",
    "Munin plugin does not exist": "Munin 外掛程式不存在",
    "Ok": "確認",
    "Warning": "警告",
    "Critical": "嚴重",
    "Existing password did not match": "與既有的密碼不符",
    "The {attribute} field is required.": "{attribute} 是必要欄位。",
    "Edit User": "編輯使用者",
    "Unlock": "解除鎖定",
    "User exceeded failures": "使用者達到失敗上限",
    "Disable TwoFactor": "取消雙因素驗證",
    "No TwoFactor key generated for this user, Nothing to do.": "沒有為這個使用者產生雙因素驗證金鑰，暫不動作。",
    "Save": "儲存",
    "Cancel": "取消",
    "Unlocked Two Factor.": "解儲雙因素驗證鎖定。",
    "Failed to unlock Two Factor": "雙因素驗證解除鎖定失敗",
    "Removed Two Factor.": "雙因素驗證已經移除。",
    "Failed to remove Two Factor": "移除雙因素驗證失敗",
    "Real Name": "真實姓名",
    "Email": "郵件",
    "Description": "描述",
    "Level": "等級",
    "Normal": "正常",
    "Global Read": "全域讀取",
    "Admin": "Admin",
    "Demo": "Demo",
    "Dashboard": "資訊看版",
    "Password": "密碼",
    "Current Password": "目前密碼",
    "New Password": "新密碼",
    "Confirm Password": "確認新密碼",
    "Can Modify Password": "允許修改密碼",
    "Create User": "建立使用者",
    "Username": "使用者名稱",
    "Manage Users": "管理使用者",
    "ID": "ID",
    "Access": "存取權限",
    "Auth": "驗證",
    "Actions": "動作",
    "Edit": "編輯",
    "Delete": "刪除",
    "Manage Access": "管理存取權限",
    "Add User": "新增使用者",
    "Are you sure you want to delete ": "您確定要刪除 ",
    "The user could not be deleted": "這個使用者無法刪除",
    "Whoops, the web server could not write required files to the filesystem.": "噢，Web Server 無法寫入檔案到檔案系統。",
    "Running the following commands will fix the issue most of the time:": "Running the following commands will fix the issue most of the time:",
    "Whoops, looks like something went wrong. Check your librenms.log.": "噢，看起來發生了一些錯誤。請您查閱 librenms.log。",
    "Public Devices": "公開裝置",
    "System Status": "系統裝態",
    "Logon": "登入",
    "Device": "裝置",
    "Platform": "平台",
    "Uptime": "運作時間",
    "Location": "位置",
    "Status": "狀態",
    "Remember Me": "記住我",
    "Login": "登入",
    "Please enter auth token": "請輸入驗證權仗",
    "Submit": "提交",
    "Logout": "登出",
    "Locations": "位置",
    "Coordinates": "座標",
    "Devices": "裝置",
    "Network": "網路",
    "Servers": "伺服器",
    "Firewalls": "防火牆",
    "Down": "離線",
    "Save changes": "儲存變更",
    "N/A": "無",
    "Location must have devices to show graphs": "此位置必需有裝置才能顯示圖表",
    "Traffic": "流量",
    "Cannot delete locations used by devices": "無法刪除已有裝置使用的位置",
    "Location deleted": "位置已刪除",
    "Failed to delete location": "刪除位置失敗",
    "Timestamp": "時間戳記",
    "Source": "來源",
    "Message": "訊息",
    "Facility": "設備",
    "Total hosts": "所有主機",
    "ignored": "已忽略",
    "disabled": "已取消",
    "up": "上線",
    "warn": "警告",
    "down": "離線",
    "Total services": "所有服務",
    "Widget title": "小工具標題",
    "Default Title": "預設標題",
    "Columns": "欄位",
    "Markers": "標記",
    "Ports": "連接埠",
    "Resolution": "解析度",
    "Countries": "國家",
    "Provinces": "省份",
    "Metros": "Metros",
    "Region": "地區",
    "Help": "說明",
    "Stream": "串流",
    "All Messages": "所有訊息",
    "All Devices": "所有裝置",
    "Page Size": "頁面大小",
    "Time Range": "時間範圍",
    "Search all time": "搜尋所有時間",
    "Search last 5 minutes": "搜尋最近 5 分鐘內",
    "Search last 15 minutes": "搜尋最近 15 分鐘內",
    "Search last 30 minutes": "搜尋最近 30 分鐘內",
    "Search last 1 hour": "搜尋最近 1 小時內",
    "Search last 2 hours": "搜尋最近 2 小時內",
    "Search last 8 hours": "搜尋最近 8 小時內",
    "Search last 1 day": "搜尋最近 1 天內",
    "Search last 2 days": "搜尋最近 2 天內",
    "Search last 5 days": "搜尋最近 5 天內",
    "Search last 7 days": "搜尋最近 7 天內",
    "Search last 14 days": "搜尋最近 14 天內",
    "Search last 30 days": "搜尋最近 30 天內",
    "Custom title": "自訂標題",
    "Initial Latitude": "初始緯度",
    "ie. 51.4800 for Greenwich": "例如 51.4800 為格林威治",
    "Initial Longitude": "初始經度",
    "ie. 0 for Greenwich": "例如 0 為格林威治",
    "Initial Zoom": "初始 Zoom 縮放等級",
    "Grouping radius": "Grouping radius",
    "default 80": "預設 80",
    "Show devices": "顯示裝置",
    "Up + Down": "上線 + 離線",
    "Up": "上線",
    "Show Services": "顯示服務",
    "no": "否",
    "yes": "是",
    "Show Port Errors": "顯示連接埠錯誤",
    "Notes": "附註",
    "Custom title for widget": "自訂小工具標題",
    "Display type": "顯示類型",
    "boxes": "區塊",
    "compact": "精簡",
    "Uniform Tiles": "Uniform Tiles",
    "Tile size": "Tile size",
    "Disabled/ignored": "Disabled/ignored",
    "Show": "顯示",
    "Hide": "隱藏",
    "Mode select": "模式選擇",
    "only devices": "僅裝置",
    "only services": "僅服務",
    "devices and services": "裝置與服務",
    "Order By": "排序",
    "Hostname": "主機名稱",
    "Device group": "裝置群組",
    "Automatic Title": "自動產生標題",
    "Graph type": "圖表類型",
    "Select a graph": "選擇圖表",
    "Show legend": "顯示圖例",
    "Date range": "日期範圍",
    "One Hour": "1 小時",
    "Four Hours": "4 小時",
    "Six Hours": "6 小時",
    "Twelve Hours": "12 小時",
    "One Day": "1 天",
    "One Week": "1 週",
    "Two Weeks": "2 週",
    "One Month": "1 個月",
    "Two Months": "2 個月",
    "Three Months": "3 個月",
    "One Year": "1 年",
    "Two Years": "2 年",
    "Select a device": "選擇裝置",
    "Port": "連接埠",
    "Select a port": "選擇連接埠",
    "Application": "應用",
    "Select an application": "選擇應用",
    "Munin plugin": "Munin 外掛程式",
    "Select a Munin plugin": "選擇 Munin 外掛程式",
    "Bill": "帳單",
    "Select a bill": "選擇帳單",
    "Custom Aggregator(s)": "Custom Aggregator(s)",
    "Select or add one or more": "選擇或加入一或多個",
    "Select one or more": "選擇一或多個",
    "Top query": "排行榜查詢",
    "Response time": "回應時間",
    "Poller duration": "輪詢器花費時間",
    "Processor load": "處理器負載",
    "Memory usage": "記憶體使用量",
    "Disk usage": "磁碟使用量",
    "Sort order": "排序",
    "Ascending": "升冪",
    "Descending": "降冪",
    "Number of Devices": "裝置數量",
    "Last Polled (minutes)": "最後一次輪詢 (分鐘)",
    "Image URL": "圖片 URL",
    "Target URL": "連結目標 URL",
    "Show acknowledged": "顯示已通知",
    "not filtered": "不要篩選",
    "show only acknowledged": "僅顯示已通知",
    "hide acknowledged": "隱藏已通知",
    "Show only fired": "僅顯示已觸發",
    "show only fired alerts": "僅顯示已觸發警報",
    "Displayed severity": "顯示嚴重程度",
    "any severity": "任何嚴重程度",
    "or higher": "或更高者",
    "State": "狀態",
    "any state": "任何狀態",
    "All alerts": "所有警報",
    "Show Procedure field": "顯示處理欄位",
    "show": "顯示",
    "hide": "隱藏",
    "Sort alerts by": "排序警報依據",
    "timestamp, descending": "時間戳記、降冪",
    "severity, descending": "嚴重程度、降冪",
    "All devices": "所有裝置",
    "Event type": "事件類型",
    "All types": "所有類型",
    "Number of interfaces": "介面數量",
    "Last polled (minutes)": "最後一次輪詢 (分鐘)",
    "Interface type": "介面類型",
    "All Ports": "所有連接埠",
    "Total": "總計",
    "Ignored": "已忽略",
    "Errored": "已錯誤",
    "Services": "服務",
    "No devices found within interval.": "在最近一次輪詢間隔內尚未找到裝置。",
    "Summary": "摘要",
    "Interface": "介面",
    "Total traffic": "流量總計",
    "Check your log for more details.": "查閱您的記錄檔以取得更詳細的資訊。",
    "If you need additional help, you can find how to get help at": "若您需要更多的說明，您可以在這裡找到更多相關資訊。",
    "Geo Locations": "地理位置",
    "All Locations": "所有位置",
    "Pollers": "輪詢器",
    "Groups": "群組",
    "Performance": "效能",
    "History": "歷程",
    "syslog": {
      "severity": ["緊急", "警報", "重大", "錯誤", "警告", "通知", "資訊", "除錯"],
      "facility": ["kernel messages", "user-level messages", "mail-system", "system daemons", "security/authorization messages", "messages generated internally by syslogd", "line printer subsystem", "network news subsystem", "UUCP subsystem", "clock daemon", "security/authorization messages", "FTP daemon", "NTP subsystem", "log audit", "log alert", "clock daemon (note 2)", "local use 0  (local0)", "local use 1  (local1)", "local use 2  (local2)", "local use 3  (local3)", "local use 4  (local4)", "local use 5  (local5)", "local use 6  (local6)", "local use 7  (local7)"]
    },
    "passwords": {
      "password": "密碼至少需要六個字元，並且要確認兩者相符。",
      "reset": "您的密碼已重置。",
      "sent": "已經寄送密碼重置連結至您的電子郵件信箱。",
      "token": "此密碼重置權仗無效。",
      "user": "找不到使用者的電子郵件位址。"
    },
    "sensors": {
      "airflow": {
        "short": "氣流",
        "long": "氣流",
        "unit": "cfm",
        "unit_long": "每分鐘標準立方呎"
      },
      "ber": {
        "short": "BER",
        "long": "位元錯誤率",
        "unit": "",
        "unit_long": ""
      },
      "charge": {
        "short": "電量",
        "long": "電量百分比",
        "unit": "%",
        "unit_long": "百分比"
      },
      "chromatic_dispersion": {
        "short": "色散",
        "long": "色散",
        "unit": "ps/nm/km",
        "unit_long": "Picoseconds per Nanometer per Kilometer"
      },
      "cooling": {
        "short": "Cooling",
        "long": "",
        "unit": "W",
        "unit_long": "瓦特"
      },
      "count": {
        "short": "Count",
        "long": "Count",
        "unit": "",
        "unit_long": ""
      },
      "current": {
        "short": "電流",
        "long": "電流",
        "unit": "A",
        "unit_long": "安培"
      },
      "dbm": {
        "short": "dBm",
        "long": "dBm",
        "unit": "dBm",
        "unit_long": "毫瓦分貝"
      },
      "delay": {
        "short": "延遲",
        "long": "延遲",
        "unit": "s",
        "unit_long": "秒"
      },
      "eer": {
        "short": "EER",
        "long": "能效比",
        "unit": "",
        "unit_long": ""
      },
      "fanspeed": {
        "short": "風扇轉速",
        "long": "風扇轉速",
        "unit": "RPM",
        "unit_long": "每分鐘旋轉次數"
      },
      "frequency": {
        "short": "頻率",
        "long": "頻率",
        "unit": "Hz",
        "unit_long": "赫茲"
      },
      "humidity": {
        "short": "濕度",
        "long": "濕度百分比",
        "unit": "%",
        "unit_long": "百分比"
      },
      "load": {
        "short": "負載",
        "long": "負載百分比",
        "unit": "%",
        "unit_long": "百分比"
      },
      "power": {
        "short": "電力",
        "long": "電力",
        "unit": "W",
        "unit_long": "瓦特"
      },
      "power_consumed": {
        "short": "消耗功率",
        "long": "消耗功率",
        "unit": "kWh",
        "unit_long": "千瓦小時"
      },
      "power_factor": {
        "short": "功率因數",
        "long": "功率因數",
        "unit": "",
        "unit_long": ""
      },
      "pressure": {
        "short": "壓力",
        "long": "壓力",
        "unit": "kPa",
        "unit_long": "千帕"
      },
      "quality_factor": {
        "short": "品質因子",
        "long": "品質因子",
        "unit": "",
        "unit_long": ""
      },
      "runtime": {
        "short": "Runtime",
        "long": "Runtime",
        "unit": "分",
        "unit_long": "分鐘"
      },
      "signal": {
        "short": "訊號",
        "long": "訊號",
        "unit": "dBm",
        "unit_long": "毫瓦分貝"
      },
      "snr": {
        "short": "SNR",
        "long": "訊號雜訊比",
        "unit": "dB",
        "unit_long": "分貝"
      },
      "state": {
        "short": "狀態",
        "long": "狀態",
        "unit": ""
      },
      "temperature": {
        "short": "溫度",
        "long": "溫度",
        "unit": "°C",
        "unit_long": "° 攝氏"
      },
      "voltage": {
        "short": "電壓",
        "long": "電壓",
        "unit": "V",
        "unit_long": "伏特"
      },
      "waterflow": {
        "short": "水流",
        "long": "水流",
        "unit": "l/m",
        "unit_long": "升每分鐘"
      }
    },
    "wireless": {
      "ap-count": {
        "short": "AP 數量",
        "long": "AP 數量",
        "unit": ""
      },
      "clients": {
        "short": "用戶端",
        "long": "用戶端數量",
        "unit": ""
      },
      "capacity": {
        "short": "容量",
        "long": "容量",
        "unit": "%"
      },
      "ccq": {
        "short": "CCQ",
        "long": "客戶端連線品質",
        "unit": "%"
      },
      "errors": {
        "short": "錯誤",
        "long": "錯誤數量",
        "unit": ""
      },
      "error-ratio": {
        "short": "錯誤率",
        "long": "位元/封包錯誤率",
        "unit": "%"
      },
      "error-rate": {
        "short": "BER",
        "long": "位元錯誤率",
        "unit": "bps"
      },
      "frequency": {
        "short": "頻率",
        "long": "頻率",
        "unit": "MHz"
      },
      "distance": {
        "short": "距離",
        "long": "距離",
        "unit": "km"
      },
      "mse": {
        "short": "MSE",
        "long": "平均誤差",
        "unit": "dB"
      },
      "noise-floor": {
        "short": "背景雜訊",
        "long": "背景雜訊",
        "unit": "dBm/Hz"
      },
      "power": {
        "short": "電力/訊號",
        "long": "TX/RX 電力或訊號",
        "unit": "dBm"
      },
      "quality": {
        "short": "品質",
        "long": "品質",
        "unit": "%"
      },
      "rate": {
        "short": "傳送率",
        "long": "TX/RX 傳送率",
        "unit": "bps"
      },
      "rssi": {
        "short": "RSSI",
        "long": "接收訊號強度指標",
        "unit": "dBm"
      },
      "snr": {
        "short": "SNR",
        "long": "訊號噪訊比",
        "unit": "dB"
      },
      "ssr": {
        "short": "SSR",
        "long": "訊號強度比",
        "unit": "dB"
      },
      "utilization": {
        "short": "使用率",
        "long": "使用率",
        "unit": "%"
      },
      "xpi": {
        "short": "XPI",
        "long": "交互極化干擾",
        "unit": "dB"
      }
    },
    "auth": {
      "failed": "這些憑證與我們的記錄不符。",
      "throttle": "嘗試登入次數過多。請稍候 {seconds} 秒再試。"
    },
    "pagination": {
      "previous": "&laquo; 往前",
      "next": "往後 &raquo;"
    },
    "validation": {
      "accepted": "{attribute} 須同意。",
      "active_url": "{attribute} 不是有效的 URL。",
      "after": "{attribute} 須為 {date} 之後的日期。",
      "after_or_equal": "{attribute} 須等於 {date} 或之後的日期。",
      "alpha": "The {attribute} may only contain letters.",
      "alpha_dash": "The {attribute} may only contain letters, numbers, dashes and underscores.",
      "alpha_num": "The {attribute} may only contain letters and numbers.",
      "array": "{attribute} 需為陣列。",
      "before": "{attribute} 須為 {date} 之前的日期。",
      "before_or_equal": "{attribute} 須等於 {date} 或之前的日期。",
      "between": {
        "numeric": "The {attribute} must be between {min} and {max}.",
        "file": "The {attribute} must be between {min} and {max} kilobytes.",
        "string": "The {attribute} must be between {min} and {max} characters.",
        "array": "The {attribute} must have between {min} and {max} items."
      },
      "boolean": "The {attribute} field must be true or false.",
      "confirmed": "The {attribute} confirmation does not match.",
      "date": "The {attribute} is not a valid date.",
      "date_equals": "The {attribute} must be a date equal to {date}.",
      "date_format": "The {attribute} does not match the format {format}.",
      "different": "The {attribute} and {other} must be different.",
      "digits": "The {attribute} must be {digits} digits.",
      "digits_between": "The {attribute} must be between {min} and {max} digits.",
      "dimensions": "The {attribute} has invalid image dimensions.",
      "distinct": "The {attribute} field has a duplicate value.",
      "email": "The {attribute} must be a valid email address.",
      "exists": "The selected {attribute} is invalid.",
      "file": "The {attribute} must be a file.",
      "filled": "The {attribute} field must have a value.",
      "gt": {
        "numeric": "The {attribute} must be greater than {value}.",
        "file": "The {attribute} must be greater than {value} kilobytes.",
        "string": "The {attribute} must be greater than {value} characters.",
        "array": "The {attribute} must have more than {value} items."
      },
      "gte": {
        "numeric": "The {attribute} must be greater than or equal {value}.",
        "file": "The {attribute} must be greater than or equal {value} kilobytes.",
        "string": "The {attribute} must be greater than or equal {value} characters.",
        "array": "The {attribute} must have {value} items or more."
      },
      "image": "The {attribute} must be an image.",
      "in": "The selected {attribute} is invalid.",
      "in_array": "The {attribute} field does not exist in {other}.",
      "integer": "The {attribute} must be an integer.",
      "ip": "The {attribute} must be a valid IP address.",
      "ipv4": "The {attribute} must be a valid IPv4 address.",
      "ipv6": "The {attribute} must be a valid IPv6 address.",
      "json": "The {attribute} must be a valid JSON string.",
      "lt": {
        "numeric": "The {attribute} must be less than {value}.",
        "file": "The {attribute} must be less than {value} kilobytes.",
        "string": "The {attribute} must be less than {value} characters.",
        "array": "The {attribute} must have less than {value} items."
      },
      "lte": {
        "numeric": "The {attribute} must be less than or equal {value}.",
        "file": "The {attribute} must be less than or equal {value} kilobytes.",
        "string": "The {attribute} must be less than or equal {value} characters.",
        "array": "The {attribute} must not have more than {value} items."
      },
      "max": {
        "numeric": "The {attribute} may not be greater than {max}.",
        "file": "The {attribute} may not be greater than {max} kilobytes.",
        "string": "The {attribute} may not be greater than {max} characters.",
        "array": "The {attribute} may not have more than {max} items."
      },
      "mimes": "The {attribute} must be a file of type: {values}.",
      "mimetypes": "The {attribute} must be a file of type: {values}.",
      "min": {
        "numeric": "The {attribute} must be at least {min}.",
        "file": "The {attribute} must be at least {min} kilobytes.",
        "string": "The {attribute} must be at least {min} characters.",
        "array": "The {attribute} must have at least {min} items."
      },
      "not_in": "The selected {attribute} is invalid.",
      "not_regex": "The {attribute} format is invalid.",
      "numeric": "The {attribute} must be a number.",
      "present": "The {attribute} field must be present.",
      "regex": "The {attribute} format is invalid.",
      "required": "The {attribute} field is required.",
      "required_if": "The {attribute} field is required when {other} is {value}.",
      "required_unless": "The {attribute} field is required unless {other} is in {values}.",
      "required_with": "The {attribute} field is required when {values} is present.",
      "required_with_all": "The {attribute} field is required when {values} are present.",
      "required_without": "The {attribute} field is required when {values} is not present.",
      "required_without_all": "The {attribute} field is required when none of {values} are present.",
      "same": "The {attribute} and {other} must match.",
      "size": {
        "numeric": "The {attribute} must be {size}.",
        "file": "The {attribute} must be {size} kilobytes.",
        "string": "The {attribute} must be {size} characters.",
        "array": "The {attribute} must contain {size} items."
      },
      "starts_with": "The {attribute} must start with one of the following: {values}",
      "string": "{attribute} 須是字串。",
      "timezone": "The {attribute} must be a valid zone.",
      "unique": "The {attribute} has already been taken.",
      "uploaded": "{attribute} 上傳失敗。",
      "url": "{attribute} 格式無效。",
      "uuid": "{attribute} 須是有效的 UUID。",
      "custom": {
        "attribute-name": {
          "rule-name": "custom-message"
        }
      },
      "attributes": []
    },
    "preferences": {
      "lang": "繁體中文"
    },
    "commands": {
      "user{add}": {
        "description": "新增一個本機使用者，只有在設定驗證使用 mysql 時才可以使用此使用者帳號登入",
        "arguments": {
          "username": "使用者用來登入的名稱"
        },
        "options": {
          "descr": "使用者描述",
          "email": "使用者的郵件",
          "password": "使用者的密碼，如果沒有提供，您將會收到提示",
          "full-name": "使用者的全名",
          "role": "將使用者指派至角色 {roles}"
        },
        "password-request": "請輸入使用者的密碼",
        "success": "已成功新增使用者: {username}",
        "wrong-auth": "警告，您將無法以這個使用者登入，因為您沒有使用 MySQL 驗證"
      }
    }
  }
});

/***/ }),

/***/ "./resources/sass/app.scss":
/*!*********************************!*\
  !*** ./resources/sass/app.scss ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 0:
/*!*************************************************************!*\
  !*** multi ./resources/js/app.js ./resources/sass/app.scss ***!
  \*************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! /home/murrant/projects/librenms/resources/js/app.js */"./resources/js/app.js");
module.exports = __webpack_require__(/*! /home/murrant/projects/librenms/resources/sass/app.scss */"./resources/sass/app.scss");


/***/ })

},[[0,"/js/manifest","/js/vendor"]]]);
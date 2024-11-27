var __defProp = Object.defineProperty;
var __defProps = Object.defineProperties;
var __getOwnPropDescs = Object.getOwnPropertyDescriptors;
var __getOwnPropSymbols = Object.getOwnPropertySymbols;
var __hasOwnProp = Object.prototype.hasOwnProperty;
var __propIsEnum = Object.prototype.propertyIsEnumerable;
var __defNormalProp = (obj, key, value) => key in obj ? __defProp(obj, key, { enumerable: true, configurable: true, writable: true, value }) : obj[key] = value;
var __spreadValues = (a, b) => {
  for (var prop in b || (b = {}))
    if (__hasOwnProp.call(b, prop))
      __defNormalProp(a, prop, b[prop]);
  if (__getOwnPropSymbols)
    for (var prop of __getOwnPropSymbols(b)) {
      if (__propIsEnum.call(b, prop))
        __defNormalProp(a, prop, b[prop]);
    }
  return a;
};
var __spreadProps = (a, b) => __defProps(a, __getOwnPropDescs(b));
(function(vue, i18n, store, hooks) {
  "use strict";
  function _interopNamespaceDefault(e) {
    const n = Object.create(null, { [Symbol.toStringTag]: { value: "Module" } });
    if (e) {
      for (const k in e) {
        if (k !== "default") {
          const d = Object.getOwnPropertyDescriptor(e, k);
          Object.defineProperty(n, k, d.get ? d : {
            enumerable: true,
            get: () => e[k]
          });
        }
      }
    }
    n.default = e;
    return Object.freeze(n);
  }
  const i18n__namespace = /* @__PURE__ */ _interopNamespaceDefault(i18n);
  const _sfc_main$a = /* @__PURE__ */ vue.defineComponent({
    __name: "CustomFontModalContent",
    props: {
      fontConfig: { default: () => ({ font_family: "", weight: "400", woff: "", woff2: "", ttf: "", svg: "", eot: "" }) }
    },
    emits: ["save-font", "set-title"],
    setup(__props, { emit }) {
      const props = __props;
      const localFontConfig = vue.ref(props.fontConfig);
      const fontWeightOptions = [
        {
          name: "100",
          id: "100"
        },
        {
          name: "200",
          id: "200"
        },
        {
          name: "300",
          id: "300"
        },
        {
          name: "400",
          id: "400"
        },
        {
          name: "500",
          id: "500"
        },
        {
          name: "600",
          id: "600"
        },
        {
          name: "700",
          id: "700"
        },
        {
          name: "800",
          id: "800"
        },
        {
          name: "900",
          id: "900"
        }
      ];
      function saveFont() {
        emit("save-font", localFontConfig.value);
      }
      function updateValue(type, url) {
        localFontConfig.value = __spreadProps(__spreadValues({}, localFontConfig.value), {
          [type]: url
        });
      }
      return (_ctx, _cache) => {
        const _component_BaseInput = vue.resolveComponent("BaseInput");
        const _component_ModalTwoColTemplate = vue.resolveComponent("ModalTwoColTemplate");
        const _component_InputSelect = vue.resolveComponent("InputSelect");
        const _component_InputFile = vue.resolveComponent("InputFile");
        const _component_ModalTemplateSaveButton = vue.resolveComponent("ModalTemplateSaveButton");
        return vue.openBlock(), vue.createBlock(_component_ModalTemplateSaveButton, { onSaveModal: saveFont }, {
          default: vue.withCtx(() => [
            vue.createVNode(_component_ModalTwoColTemplate, {
              title: i18n__namespace.__("Font name", "zionbuilder-pro")
            }, {
              default: vue.withCtx(() => [
                vue.createVNode(_component_BaseInput, {
                  "model-value": localFontConfig.value.font_family,
                  "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => (localFontConfig.value.font_family = $event, emit("set-title", $event)))
                }, null, 8, ["model-value"])
              ]),
              _: 1
            }, 8, ["title"]),
            vue.createVNode(_component_ModalTwoColTemplate, {
              title: i18n__namespace.__("Font weight", "zionbuilder-pro")
            }, {
              default: vue.withCtx(() => [
                vue.createVNode(_component_InputSelect, {
                  "model-value": localFontConfig.value.weight || "",
                  options: fontWeightOptions,
                  "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => updateValue("weight", $event))
                }, null, 8, ["model-value"])
              ]),
              _: 1
            }, 8, ["title"]),
            vue.createVNode(_component_ModalTwoColTemplate, {
              title: i18n__namespace.__("Woff file", "zionbuilder-pro")
            }, {
              default: vue.withCtx(() => [
                vue.createVNode(_component_InputFile, {
                  "model-value": localFontConfig.value.woff,
                  type: ".woff",
                  "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => updateValue("woff", $event))
                }, null, 8, ["model-value"])
              ]),
              _: 1
            }, 8, ["title"]),
            vue.createVNode(_component_ModalTwoColTemplate, {
              title: i18n__namespace.__("Woff2 file", "zionbuilder-pro")
            }, {
              default: vue.withCtx(() => [
                vue.createVNode(_component_InputFile, {
                  "model-value": localFontConfig.value.woff2,
                  type: ".woff2",
                  "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => updateValue("woff2", $event))
                }, null, 8, ["model-value"])
              ]),
              _: 1
            }, 8, ["title"]),
            vue.createVNode(_component_ModalTwoColTemplate, {
              title: i18n__namespace.__("TTF file", "zionbuilder-pro")
            }, {
              default: vue.withCtx(() => [
                vue.createVNode(_component_InputFile, {
                  "model-value": localFontConfig.value.ttf,
                  type: ".ttf",
                  "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => localFontConfig.value.ttf = $event)
                }, null, 8, ["model-value"])
              ]),
              _: 1
            }, 8, ["title"]),
            vue.createVNode(_component_ModalTwoColTemplate, {
              title: i18n__namespace.__("SVG file", "zionbuilder-pro")
            }, {
              default: vue.withCtx(() => [
                vue.createVNode(_component_InputFile, {
                  "model-value": localFontConfig.value.svg,
                  type: ".svg",
                  "onUpdate:modelValue": _cache[5] || (_cache[5] = ($event) => localFontConfig.value.svg = $event)
                }, null, 8, ["model-value"])
              ]),
              _: 1
            }, 8, ["title"]),
            vue.createVNode(_component_ModalTwoColTemplate, {
              title: i18n__namespace.__("EOT file", "zionbuilder-pro")
            }, {
              default: vue.withCtx(() => [
                vue.createVNode(_component_InputFile, {
                  "model-value": localFontConfig.value.eot,
                  type: ".eot",
                  "onUpdate:modelValue": _cache[6] || (_cache[6] = ($event) => localFontConfig.value.eot = $event)
                }, null, 8, ["model-value"])
              ]),
              _: 1
            }, 8, ["title"])
          ]),
          _: 1
        });
      };
    }
  });
  const _hoisted_1$8 = { class: "znpb-admin__google-font-tab" };
  const _hoisted_2$7 = { class: "znpb-admin__google-font-tab-title" };
  const _hoisted_3$6 = { class: "znpb-admin__google-font-tab-variants" };
  const _hoisted_4$4 = { class: "znpb-admin__google-font-tab-actions" };
  const _sfc_main$9 = /* @__PURE__ */ vue.defineComponent({
    __name: "CustomFont",
    props: {
      font: {}
    },
    emits: ["delete", "font-updated"],
    setup(__props, { emit }) {
      const showModal = vue.ref(false);
      return (_ctx, _cache) => {
        const _component_Icon = vue.resolveComponent("Icon");
        const _component_Tooltip = vue.resolveComponent("Tooltip");
        const _component_modal = vue.resolveComponent("modal");
        return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$8, [
          vue.createElementVNode("div", _hoisted_2$7, vue.toDisplayString(_ctx.font.font_family), 1),
          vue.createElementVNode("div", _hoisted_3$6, vue.toDisplayString(_ctx.font.weight), 1),
          vue.createElementVNode("div", _hoisted_4$4, [
            vue.createVNode(_component_Tooltip, {
              class: "znpb-actions-popup-icons",
              content: i18n__namespace.__("Click to edit this font", "zionbuilder-pro"),
              "append-to": "body",
              modifiers: [{ name: "offset", options: { offset: [0, 15] } }],
              "position-fixed": true
            }, {
              default: vue.withCtx(() => [
                vue.createVNode(_component_Icon, {
                  icon: "edit",
                  onClick: _cache[0] || (_cache[0] = ($event) => showModal.value = true)
                })
              ]),
              _: 1
            }, 8, ["content"]),
            vue.createVNode(_component_Tooltip, {
              class: "znpb-actions-popup-icons",
              content: i18n__namespace.__("Click to delete this font", "zionbuilder-pro"),
              "append-to": "body",
              modifiers: [{ name: "offset", options: { offset: [0, 15] } }],
              "position-fixed": true
            }, {
              default: vue.withCtx(() => [
                vue.createVNode(_component_Icon, {
                  icon: "delete",
                  onClick: _cache[1] || (_cache[1] = ($event) => emit("delete", _ctx.font))
                })
              ]),
              _: 1
            }, 8, ["content"])
          ]),
          vue.createVNode(_component_modal, {
            show: showModal.value,
            "onUpdate:show": _cache[3] || (_cache[3] = ($event) => showModal.value = $event),
            width: 570,
            title: _ctx.font.font_family,
            "append-to": "#znpb-admin",
            "show-maximize": false
          }, {
            default: vue.withCtx(() => [
              vue.createVNode(_sfc_main$a, {
                "font-config": _ctx.font,
                onSaveFont: _cache[2] || (_cache[2] = ($event) => (emit("font-updated", $event), showModal.value = false))
              }, null, 8, ["font-config"])
            ]),
            _: 1
          }, 8, ["show", "title"])
        ]);
      };
    }
  });
  const CustomFont_vue_vue_type_style_index_0_lang = "";
  const _hoisted_1$7 = {
    key: 0,
    class: "znpb-admin__google-font-tab znpb-admin__google-font-tab--titles"
  };
  const _hoisted_2$6 = { class: "znpb-admin__google-font-tab-title" };
  const _hoisted_3$5 = { class: "znpb-admin__google-font-tab-variants" };
  const _hoisted_4$3 = { class: "znpb-admin__google-font-tab-actions" };
  const _hoisted_5$3 = { key: 2 };
  const _hoisted_6$3 = { class: "znpb-admin-google-fonts-actions" };
  const _hoisted_7$2 = { class: "znpb-admin-info-p" };
  const _sfc_main$8 = /* @__PURE__ */ vue.defineComponent({
    __name: "CustomFonts",
    setup(__props) {
      const builderOptionsStore = store.useBuilderOptionsStore();
      const showModal = vue.ref(false);
      let customFonts = vue.computed(() => {
        return builderOptionsStore.getOptionValue("custom_fonts");
      });
      function onFontDelete(font) {
        builderOptionsStore.deleteCustomFont(font.font_family);
      }
      function onFontUpdated({ font, value: newValue }) {
        builderOptionsStore.updateCustomFont(font.font_family, newValue);
      }
      function onCustomFontAdded(font) {
        builderOptionsStore.addCustomFont(__spreadValues({
          font_family: font.family,
          font_variants: ["regular"],
          font_subset: ["latin"]
        }, font));
        showModal.value = false;
      }
      return (_ctx, _cache) => {
        const _component_EmptyList = vue.resolveComponent("EmptyList");
        const _component_Tooltip = vue.resolveComponent("Tooltip");
        const _component_ListAnimation = vue.resolveComponent("ListAnimation");
        const _component_Icon = vue.resolveComponent("Icon");
        const _component_Button = vue.resolveComponent("Button");
        const _component_modal = vue.resolveComponent("modal");
        const _component_PageTemplate = vue.resolveComponent("PageTemplate");
        return vue.openBlock(), vue.createBlock(_component_PageTemplate, null, {
          right: vue.withCtx(() => [
            vue.createElementVNode("p", _hoisted_7$2, vue.toDisplayString(i18n__namespace.__("Upload custom fonts", "zionbuilder-pro")), 1)
          ]),
          default: vue.withCtx(() => [
            vue.createElementVNode("h3", null, vue.toDisplayString(i18n__namespace.__("Custom fonts", "zionbuilder-pro")), 1),
            vue.unref(customFonts).length > 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$7, [
              vue.createElementVNode("div", _hoisted_2$6, vue.toDisplayString(i18n__namespace.__("Font name", "zionbuilder-pro")), 1),
              vue.createElementVNode("div", _hoisted_3$5, vue.toDisplayString(i18n__namespace.__("Font variants", "zionbuilder-pro")), 1),
              vue.createElementVNode("div", _hoisted_4$3, vue.toDisplayString(i18n__namespace.__("Actions", "zionbuilder-pro")), 1)
            ])) : vue.createCommentVNode("", true),
            vue.unref(customFonts).length === 0 ? (vue.openBlock(), vue.createBlock(_component_Tooltip, {
              key: 1,
              content: i18n__namespace.__("Click to add font", "zionbuilder-pro")
            }, {
              default: vue.withCtx(() => [
                vue.createVNode(_component_EmptyList, {
                  onClick: _cache[0] || (_cache[0] = ($event) => showModal.value = true)
                }, {
                  default: vue.withCtx(() => [
                    vue.createTextVNode(vue.toDisplayString(i18n__namespace.__("No custom fonts", "zionbuilder-pro")), 1)
                  ]),
                  _: 1
                })
              ]),
              _: 1
            }, 8, ["content"])) : vue.createCommentVNode("", true),
            vue.unref(customFonts).length > 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_5$3, [
              vue.createVNode(_component_ListAnimation, { tag: "div" }, {
                default: vue.withCtx(() => [
                  (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(vue.unref(customFonts), (font, i) => {
                    return vue.openBlock(), vue.createBlock(_sfc_main$9, {
                      key: i,
                      class: "znpb-admin-tab",
                      font,
                      onDelete: onFontDelete,
                      onFontUpdated: ($event) => onFontUpdated({
                        font,
                        value: $event
                      })
                    }, null, 8, ["font", "onFontUpdated"]);
                  }), 128))
                ]),
                _: 1
              })
            ])) : vue.createCommentVNode("", true),
            vue.createElementVNode("div", _hoisted_6$3, [
              vue.createVNode(_component_Button, {
                type: "secondary",
                onClick: _cache[1] || (_cache[1] = ($event) => showModal.value = true)
              }, {
                default: vue.withCtx(() => [
                  vue.createVNode(_component_Icon, { icon: "plus" }),
                  vue.createTextVNode(" " + vue.toDisplayString(i18n__namespace.__("Add font", "zionbuilder-pro")), 1)
                ]),
                _: 1
              })
            ]),
            vue.createVNode(_component_modal, {
              show: showModal.value,
              "onUpdate:show": _cache[3] || (_cache[3] = ($event) => showModal.value = $event),
              width: 570,
              title: i18n__namespace.__("Custom fonts", "zionbuilder-pro"),
              "append-to": "#znpb-admin",
              "show-maximize": false
            }, {
              default: vue.withCtx(() => [
                vue.createVNode(_sfc_main$a, {
                  onSaveFont: _cache[2] || (_cache[2] = ($event) => onCustomFontAdded($event))
                })
              ]),
              _: 1
            }, 8, ["show", "title"])
          ]),
          _: 1
        });
      };
    }
  });
  const _hoisted_1$6 = { class: "znpb-admin__typekit-font-tab-title" };
  const _sfc_main$7 = /* @__PURE__ */ vue.defineComponent({
    __name: "AdobeFontsTab",
    props: {
      font: {}
    },
    setup(__props) {
      const props = __props;
      const builderOptionsStore = store.useBuilderOptionsStore();
      const isActive = vue.computed({
        get: () => {
          return builderOptionsStore.getOptionValue("typekit_fonts").includes(props.font.id);
        },
        set: (val) => {
          if (val) {
            builderOptionsStore.addFontProject(props.font.id);
          } else {
            builderOptionsStore.removeFontProject(props.font.id);
          }
        }
      });
      return (_ctx, _cache) => {
        const _component_InputCheckbox = vue.resolveComponent("InputCheckbox");
        const _component_Tooltip = vue.resolveComponent("Tooltip");
        return vue.openBlock(), vue.createElementBlock("div", {
          class: vue.normalizeClass(["znpb-admin__typekit-font-tab", { "znpb-admin__typekit-font-tab--active": isActive.value }])
        }, [
          vue.createElementVNode("span", _hoisted_1$6, vue.toDisplayString(_ctx.font.name), 1),
          vue.createVNode(_component_Tooltip, {
            content: i18n__namespace.__("This is your active Typekit font. Uncheck to deactivate it", "zionbuilder-pro")
          }, {
            default: vue.withCtx(() => [
              vue.createVNode(_component_InputCheckbox, {
                modelValue: isActive.value,
                "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => isActive.value = $event),
                rounded: true
              }, null, 8, ["modelValue"])
            ]),
            _: 1
          }, 8, ["content"])
        ], 2);
      };
    }
  });
  const AdobeFontsTab_vue_vue_type_style_index_0_lang = "";
  function bind(fn, thisArg) {
    return function wrap() {
      return fn.apply(thisArg, arguments);
    };
  }
  const { toString } = Object.prototype;
  const { getPrototypeOf } = Object;
  const kindOf = ((cache) => (thing) => {
    const str = toString.call(thing);
    return cache[str] || (cache[str] = str.slice(8, -1).toLowerCase());
  })(/* @__PURE__ */ Object.create(null));
  const kindOfTest = (type) => {
    type = type.toLowerCase();
    return (thing) => kindOf(thing) === type;
  };
  const typeOfTest = (type) => (thing) => typeof thing === type;
  const { isArray } = Array;
  const isUndefined = typeOfTest("undefined");
  function isBuffer(val) {
    return val !== null && !isUndefined(val) && val.constructor !== null && !isUndefined(val.constructor) && isFunction(val.constructor.isBuffer) && val.constructor.isBuffer(val);
  }
  const isArrayBuffer = kindOfTest("ArrayBuffer");
  function isArrayBufferView(val) {
    let result;
    if (typeof ArrayBuffer !== "undefined" && ArrayBuffer.isView) {
      result = ArrayBuffer.isView(val);
    } else {
      result = val && val.buffer && isArrayBuffer(val.buffer);
    }
    return result;
  }
  const isString = typeOfTest("string");
  const isFunction = typeOfTest("function");
  const isNumber = typeOfTest("number");
  const isObject$1 = (thing) => thing !== null && typeof thing === "object";
  const isBoolean = (thing) => thing === true || thing === false;
  const isPlainObject = (val) => {
    if (kindOf(val) !== "object") {
      return false;
    }
    const prototype2 = getPrototypeOf(val);
    return (prototype2 === null || prototype2 === Object.prototype || Object.getPrototypeOf(prototype2) === null) && !(Symbol.toStringTag in val) && !(Symbol.iterator in val);
  };
  const isDate = kindOfTest("Date");
  const isFile = kindOfTest("File");
  const isBlob = kindOfTest("Blob");
  const isFileList = kindOfTest("FileList");
  const isStream = (val) => isObject$1(val) && isFunction(val.pipe);
  const isFormData = (thing) => {
    let kind;
    return thing && (typeof FormData === "function" && thing instanceof FormData || isFunction(thing.append) && ((kind = kindOf(thing)) === "formdata" || // detect form-data instance
    kind === "object" && isFunction(thing.toString) && thing.toString() === "[object FormData]"));
  };
  const isURLSearchParams = kindOfTest("URLSearchParams");
  const trim = (str) => str.trim ? str.trim() : str.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, "");
  function forEach(obj, fn, { allOwnKeys = false } = {}) {
    if (obj === null || typeof obj === "undefined") {
      return;
    }
    let i;
    let l;
    if (typeof obj !== "object") {
      obj = [obj];
    }
    if (isArray(obj)) {
      for (i = 0, l = obj.length; i < l; i++) {
        fn.call(null, obj[i], i, obj);
      }
    } else {
      const keys = allOwnKeys ? Object.getOwnPropertyNames(obj) : Object.keys(obj);
      const len = keys.length;
      let key;
      for (i = 0; i < len; i++) {
        key = keys[i];
        fn.call(null, obj[key], key, obj);
      }
    }
  }
  function findKey(obj, key) {
    key = key.toLowerCase();
    const keys = Object.keys(obj);
    let i = keys.length;
    let _key;
    while (i-- > 0) {
      _key = keys[i];
      if (key === _key.toLowerCase()) {
        return _key;
      }
    }
    return null;
  }
  const _global = (() => {
    if (typeof globalThis !== "undefined")
      return globalThis;
    return typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : global;
  })();
  const isContextDefined = (context) => !isUndefined(context) && context !== _global;
  function merge() {
    const { caseless } = isContextDefined(this) && this || {};
    const result = {};
    const assignValue = (val, key) => {
      const targetKey = caseless && findKey(result, key) || key;
      if (isPlainObject(result[targetKey]) && isPlainObject(val)) {
        result[targetKey] = merge(result[targetKey], val);
      } else if (isPlainObject(val)) {
        result[targetKey] = merge({}, val);
      } else if (isArray(val)) {
        result[targetKey] = val.slice();
      } else {
        result[targetKey] = val;
      }
    };
    for (let i = 0, l = arguments.length; i < l; i++) {
      arguments[i] && forEach(arguments[i], assignValue);
    }
    return result;
  }
  const extend = (a, b, thisArg, { allOwnKeys } = {}) => {
    forEach(b, (val, key) => {
      if (thisArg && isFunction(val)) {
        a[key] = bind(val, thisArg);
      } else {
        a[key] = val;
      }
    }, { allOwnKeys });
    return a;
  };
  const stripBOM = (content) => {
    if (content.charCodeAt(0) === 65279) {
      content = content.slice(1);
    }
    return content;
  };
  const inherits = (constructor, superConstructor, props, descriptors2) => {
    constructor.prototype = Object.create(superConstructor.prototype, descriptors2);
    constructor.prototype.constructor = constructor;
    Object.defineProperty(constructor, "super", {
      value: superConstructor.prototype
    });
    props && Object.assign(constructor.prototype, props);
  };
  const toFlatObject = (sourceObj, destObj, filter, propFilter) => {
    let props;
    let i;
    let prop;
    const merged = {};
    destObj = destObj || {};
    if (sourceObj == null)
      return destObj;
    do {
      props = Object.getOwnPropertyNames(sourceObj);
      i = props.length;
      while (i-- > 0) {
        prop = props[i];
        if ((!propFilter || propFilter(prop, sourceObj, destObj)) && !merged[prop]) {
          destObj[prop] = sourceObj[prop];
          merged[prop] = true;
        }
      }
      sourceObj = filter !== false && getPrototypeOf(sourceObj);
    } while (sourceObj && (!filter || filter(sourceObj, destObj)) && sourceObj !== Object.prototype);
    return destObj;
  };
  const endsWith = (str, searchString, position) => {
    str = String(str);
    if (position === void 0 || position > str.length) {
      position = str.length;
    }
    position -= searchString.length;
    const lastIndex = str.indexOf(searchString, position);
    return lastIndex !== -1 && lastIndex === position;
  };
  const toArray = (thing) => {
    if (!thing)
      return null;
    if (isArray(thing))
      return thing;
    let i = thing.length;
    if (!isNumber(i))
      return null;
    const arr = new Array(i);
    while (i-- > 0) {
      arr[i] = thing[i];
    }
    return arr;
  };
  const isTypedArray = ((TypedArray) => {
    return (thing) => {
      return TypedArray && thing instanceof TypedArray;
    };
  })(typeof Uint8Array !== "undefined" && getPrototypeOf(Uint8Array));
  const forEachEntry = (obj, fn) => {
    const generator = obj && obj[Symbol.iterator];
    const iterator = generator.call(obj);
    let result;
    while ((result = iterator.next()) && !result.done) {
      const pair = result.value;
      fn.call(obj, pair[0], pair[1]);
    }
  };
  const matchAll = (regExp, str) => {
    let matches;
    const arr = [];
    while ((matches = regExp.exec(str)) !== null) {
      arr.push(matches);
    }
    return arr;
  };
  const isHTMLForm = kindOfTest("HTMLFormElement");
  const toCamelCase = (str) => {
    return str.toLowerCase().replace(
      /[-_\s]([a-z\d])(\w*)/g,
      function replacer(m, p1, p2) {
        return p1.toUpperCase() + p2;
      }
    );
  };
  const hasOwnProperty$1 = (({ hasOwnProperty: hasOwnProperty2 }) => (obj, prop) => hasOwnProperty2.call(obj, prop))(Object.prototype);
  const isRegExp = kindOfTest("RegExp");
  const reduceDescriptors = (obj, reducer) => {
    const descriptors2 = Object.getOwnPropertyDescriptors(obj);
    const reducedDescriptors = {};
    forEach(descriptors2, (descriptor, name) => {
      let ret;
      if ((ret = reducer(descriptor, name, obj)) !== false) {
        reducedDescriptors[name] = ret || descriptor;
      }
    });
    Object.defineProperties(obj, reducedDescriptors);
  };
  const freezeMethods = (obj) => {
    reduceDescriptors(obj, (descriptor, name) => {
      if (isFunction(obj) && ["arguments", "caller", "callee"].indexOf(name) !== -1) {
        return false;
      }
      const value = obj[name];
      if (!isFunction(value))
        return;
      descriptor.enumerable = false;
      if ("writable" in descriptor) {
        descriptor.writable = false;
        return;
      }
      if (!descriptor.set) {
        descriptor.set = () => {
          throw Error("Can not rewrite read-only method '" + name + "'");
        };
      }
    });
  };
  const toObjectSet = (arrayOrString, delimiter) => {
    const obj = {};
    const define = (arr) => {
      arr.forEach((value) => {
        obj[value] = true;
      });
    };
    isArray(arrayOrString) ? define(arrayOrString) : define(String(arrayOrString).split(delimiter));
    return obj;
  };
  const noop = () => {
  };
  const toFiniteNumber = (value, defaultValue) => {
    value = +value;
    return Number.isFinite(value) ? value : defaultValue;
  };
  const ALPHA = "abcdefghijklmnopqrstuvwxyz";
  const DIGIT = "0123456789";
  const ALPHABET = {
    DIGIT,
    ALPHA,
    ALPHA_DIGIT: ALPHA + ALPHA.toUpperCase() + DIGIT
  };
  const generateString = (size = 16, alphabet = ALPHABET.ALPHA_DIGIT) => {
    let str = "";
    const { length } = alphabet;
    while (size--) {
      str += alphabet[Math.random() * length | 0];
    }
    return str;
  };
  function isSpecCompliantForm(thing) {
    return !!(thing && isFunction(thing.append) && thing[Symbol.toStringTag] === "FormData" && thing[Symbol.iterator]);
  }
  const toJSONObject = (obj) => {
    const stack = new Array(10);
    const visit = (source, i) => {
      if (isObject$1(source)) {
        if (stack.indexOf(source) >= 0) {
          return;
        }
        if (!("toJSON" in source)) {
          stack[i] = source;
          const target = isArray(source) ? [] : {};
          forEach(source, (value, key) => {
            const reducedValue = visit(value, i + 1);
            !isUndefined(reducedValue) && (target[key] = reducedValue);
          });
          stack[i] = void 0;
          return target;
        }
      }
      return source;
    };
    return visit(obj, 0);
  };
  const isAsyncFn = kindOfTest("AsyncFunction");
  const isThenable = (thing) => thing && (isObject$1(thing) || isFunction(thing)) && isFunction(thing.then) && isFunction(thing.catch);
  const utils = {
    isArray,
    isArrayBuffer,
    isBuffer,
    isFormData,
    isArrayBufferView,
    isString,
    isNumber,
    isBoolean,
    isObject: isObject$1,
    isPlainObject,
    isUndefined,
    isDate,
    isFile,
    isBlob,
    isRegExp,
    isFunction,
    isStream,
    isURLSearchParams,
    isTypedArray,
    isFileList,
    forEach,
    merge,
    extend,
    trim,
    stripBOM,
    inherits,
    toFlatObject,
    kindOf,
    kindOfTest,
    endsWith,
    toArray,
    forEachEntry,
    matchAll,
    isHTMLForm,
    hasOwnProperty: hasOwnProperty$1,
    hasOwnProp: hasOwnProperty$1,
    // an alias to avoid ESLint no-prototype-builtins detection
    reduceDescriptors,
    freezeMethods,
    toObjectSet,
    toCamelCase,
    noop,
    toFiniteNumber,
    findKey,
    global: _global,
    isContextDefined,
    ALPHABET,
    generateString,
    isSpecCompliantForm,
    toJSONObject,
    isAsyncFn,
    isThenable
  };
  function AxiosError(message, code, config, request, response) {
    Error.call(this);
    if (Error.captureStackTrace) {
      Error.captureStackTrace(this, this.constructor);
    } else {
      this.stack = new Error().stack;
    }
    this.message = message;
    this.name = "AxiosError";
    code && (this.code = code);
    config && (this.config = config);
    request && (this.request = request);
    response && (this.response = response);
  }
  utils.inherits(AxiosError, Error, {
    toJSON: function toJSON() {
      return {
        // Standard
        message: this.message,
        name: this.name,
        // Microsoft
        description: this.description,
        number: this.number,
        // Mozilla
        fileName: this.fileName,
        lineNumber: this.lineNumber,
        columnNumber: this.columnNumber,
        stack: this.stack,
        // Axios
        config: utils.toJSONObject(this.config),
        code: this.code,
        status: this.response && this.response.status ? this.response.status : null
      };
    }
  });
  const prototype$1 = AxiosError.prototype;
  const descriptors = {};
  [
    "ERR_BAD_OPTION_VALUE",
    "ERR_BAD_OPTION",
    "ECONNABORTED",
    "ETIMEDOUT",
    "ERR_NETWORK",
    "ERR_FR_TOO_MANY_REDIRECTS",
    "ERR_DEPRECATED",
    "ERR_BAD_RESPONSE",
    "ERR_BAD_REQUEST",
    "ERR_CANCELED",
    "ERR_NOT_SUPPORT",
    "ERR_INVALID_URL"
    // eslint-disable-next-line func-names
  ].forEach((code) => {
    descriptors[code] = { value: code };
  });
  Object.defineProperties(AxiosError, descriptors);
  Object.defineProperty(prototype$1, "isAxiosError", { value: true });
  AxiosError.from = (error, code, config, request, response, customProps) => {
    const axiosError = Object.create(prototype$1);
    utils.toFlatObject(error, axiosError, function filter(obj) {
      return obj !== Error.prototype;
    }, (prop) => {
      return prop !== "isAxiosError";
    });
    AxiosError.call(axiosError, error.message, code, config, request, response);
    axiosError.cause = error;
    axiosError.name = error.name;
    customProps && Object.assign(axiosError, customProps);
    return axiosError;
  };
  const httpAdapter = null;
  function isVisitable(thing) {
    return utils.isPlainObject(thing) || utils.isArray(thing);
  }
  function removeBrackets(key) {
    return utils.endsWith(key, "[]") ? key.slice(0, -2) : key;
  }
  function renderKey(path, key, dots) {
    if (!path)
      return key;
    return path.concat(key).map(function each(token, i) {
      token = removeBrackets(token);
      return !dots && i ? "[" + token + "]" : token;
    }).join(dots ? "." : "");
  }
  function isFlatArray(arr) {
    return utils.isArray(arr) && !arr.some(isVisitable);
  }
  const predicates = utils.toFlatObject(utils, {}, null, function filter(prop) {
    return /^is[A-Z]/.test(prop);
  });
  function toFormData(obj, formData, options) {
    if (!utils.isObject(obj)) {
      throw new TypeError("target must be an object");
    }
    formData = formData || new FormData();
    options = utils.toFlatObject(options, {
      metaTokens: true,
      dots: false,
      indexes: false
    }, false, function defined(option, source) {
      return !utils.isUndefined(source[option]);
    });
    const metaTokens = options.metaTokens;
    const visitor = options.visitor || defaultVisitor;
    const dots = options.dots;
    const indexes = options.indexes;
    const _Blob = options.Blob || typeof Blob !== "undefined" && Blob;
    const useBlob = _Blob && utils.isSpecCompliantForm(formData);
    if (!utils.isFunction(visitor)) {
      throw new TypeError("visitor must be a function");
    }
    function convertValue(value) {
      if (value === null)
        return "";
      if (utils.isDate(value)) {
        return value.toISOString();
      }
      if (!useBlob && utils.isBlob(value)) {
        throw new AxiosError("Blob is not supported. Use a Buffer instead.");
      }
      if (utils.isArrayBuffer(value) || utils.isTypedArray(value)) {
        return useBlob && typeof Blob === "function" ? new Blob([value]) : Buffer.from(value);
      }
      return value;
    }
    function defaultVisitor(value, key, path) {
      let arr = value;
      if (value && !path && typeof value === "object") {
        if (utils.endsWith(key, "{}")) {
          key = metaTokens ? key : key.slice(0, -2);
          value = JSON.stringify(value);
        } else if (utils.isArray(value) && isFlatArray(value) || (utils.isFileList(value) || utils.endsWith(key, "[]")) && (arr = utils.toArray(value))) {
          key = removeBrackets(key);
          arr.forEach(function each(el, index) {
            !(utils.isUndefined(el) || el === null) && formData.append(
              // eslint-disable-next-line no-nested-ternary
              indexes === true ? renderKey([key], index, dots) : indexes === null ? key : key + "[]",
              convertValue(el)
            );
          });
          return false;
        }
      }
      if (isVisitable(value)) {
        return true;
      }
      formData.append(renderKey(path, key, dots), convertValue(value));
      return false;
    }
    const stack = [];
    const exposedHelpers = Object.assign(predicates, {
      defaultVisitor,
      convertValue,
      isVisitable
    });
    function build(value, path) {
      if (utils.isUndefined(value))
        return;
      if (stack.indexOf(value) !== -1) {
        throw Error("Circular reference detected in " + path.join("."));
      }
      stack.push(value);
      utils.forEach(value, function each(el, key) {
        const result = !(utils.isUndefined(el) || el === null) && visitor.call(
          formData,
          el,
          utils.isString(key) ? key.trim() : key,
          path,
          exposedHelpers
        );
        if (result === true) {
          build(el, path ? path.concat(key) : [key]);
        }
      });
      stack.pop();
    }
    if (!utils.isObject(obj)) {
      throw new TypeError("data must be an object");
    }
    build(obj);
    return formData;
  }
  function encode$1(str) {
    const charMap = {
      "!": "%21",
      "'": "%27",
      "(": "%28",
      ")": "%29",
      "~": "%7E",
      "%20": "+",
      "%00": "\0"
    };
    return encodeURIComponent(str).replace(/[!'()~]|%20|%00/g, function replacer(match) {
      return charMap[match];
    });
  }
  function AxiosURLSearchParams(params, options) {
    this._pairs = [];
    params && toFormData(params, this, options);
  }
  const prototype = AxiosURLSearchParams.prototype;
  prototype.append = function append(name, value) {
    this._pairs.push([name, value]);
  };
  prototype.toString = function toString2(encoder) {
    const _encode = encoder ? function(value) {
      return encoder.call(this, value, encode$1);
    } : encode$1;
    return this._pairs.map(function each(pair) {
      return _encode(pair[0]) + "=" + _encode(pair[1]);
    }, "").join("&");
  };
  function encode(val) {
    return encodeURIComponent(val).replace(/%3A/gi, ":").replace(/%24/g, "$").replace(/%2C/gi, ",").replace(/%20/g, "+").replace(/%5B/gi, "[").replace(/%5D/gi, "]");
  }
  function buildURL(url, params, options) {
    if (!params) {
      return url;
    }
    const _encode = options && options.encode || encode;
    const serializeFn = options && options.serialize;
    let serializedParams;
    if (serializeFn) {
      serializedParams = serializeFn(params, options);
    } else {
      serializedParams = utils.isURLSearchParams(params) ? params.toString() : new AxiosURLSearchParams(params, options).toString(_encode);
    }
    if (serializedParams) {
      const hashmarkIndex = url.indexOf("#");
      if (hashmarkIndex !== -1) {
        url = url.slice(0, hashmarkIndex);
      }
      url += (url.indexOf("?") === -1 ? "?" : "&") + serializedParams;
    }
    return url;
  }
  class InterceptorManager {
    constructor() {
      this.handlers = [];
    }
    /**
     * Add a new interceptor to the stack
     *
     * @param {Function} fulfilled The function to handle `then` for a `Promise`
     * @param {Function} rejected The function to handle `reject` for a `Promise`
     *
     * @return {Number} An ID used to remove interceptor later
     */
    use(fulfilled, rejected, options) {
      this.handlers.push({
        fulfilled,
        rejected,
        synchronous: options ? options.synchronous : false,
        runWhen: options ? options.runWhen : null
      });
      return this.handlers.length - 1;
    }
    /**
     * Remove an interceptor from the stack
     *
     * @param {Number} id The ID that was returned by `use`
     *
     * @returns {Boolean} `true` if the interceptor was removed, `false` otherwise
     */
    eject(id) {
      if (this.handlers[id]) {
        this.handlers[id] = null;
      }
    }
    /**
     * Clear all interceptors from the stack
     *
     * @returns {void}
     */
    clear() {
      if (this.handlers) {
        this.handlers = [];
      }
    }
    /**
     * Iterate over all the registered interceptors
     *
     * This method is particularly useful for skipping over any
     * interceptors that may have become `null` calling `eject`.
     *
     * @param {Function} fn The function to call for each interceptor
     *
     * @returns {void}
     */
    forEach(fn) {
      utils.forEach(this.handlers, function forEachHandler(h) {
        if (h !== null) {
          fn(h);
        }
      });
    }
  }
  const InterceptorManager$1 = InterceptorManager;
  const transitionalDefaults = {
    silentJSONParsing: true,
    forcedJSONParsing: true,
    clarifyTimeoutError: false
  };
  const URLSearchParams$1 = typeof URLSearchParams !== "undefined" ? URLSearchParams : AxiosURLSearchParams;
  const FormData$1 = typeof FormData !== "undefined" ? FormData : null;
  const Blob$1 = typeof Blob !== "undefined" ? Blob : null;
  const isStandardBrowserEnv = (() => {
    let product;
    if (typeof navigator !== "undefined" && ((product = navigator.product) === "ReactNative" || product === "NativeScript" || product === "NS")) {
      return false;
    }
    return typeof window !== "undefined" && typeof document !== "undefined";
  })();
  const isStandardBrowserWebWorkerEnv = (() => {
    return typeof WorkerGlobalScope !== "undefined" && // eslint-disable-next-line no-undef
    self instanceof WorkerGlobalScope && typeof self.importScripts === "function";
  })();
  const platform = {
    isBrowser: true,
    classes: {
      URLSearchParams: URLSearchParams$1,
      FormData: FormData$1,
      Blob: Blob$1
    },
    isStandardBrowserEnv,
    isStandardBrowserWebWorkerEnv,
    protocols: ["http", "https", "file", "blob", "url", "data"]
  };
  function toURLEncodedForm(data, options) {
    return toFormData(data, new platform.classes.URLSearchParams(), Object.assign({
      visitor: function(value, key, path, helpers) {
        if (platform.isNode && utils.isBuffer(value)) {
          this.append(key, value.toString("base64"));
          return false;
        }
        return helpers.defaultVisitor.apply(this, arguments);
      }
    }, options));
  }
  function parsePropPath(name) {
    return utils.matchAll(/\w+|\[(\w*)]/g, name).map((match) => {
      return match[0] === "[]" ? "" : match[1] || match[0];
    });
  }
  function arrayToObject(arr) {
    const obj = {};
    const keys = Object.keys(arr);
    let i;
    const len = keys.length;
    let key;
    for (i = 0; i < len; i++) {
      key = keys[i];
      obj[key] = arr[key];
    }
    return obj;
  }
  function formDataToJSON(formData) {
    function buildPath(path, value, target, index) {
      let name = path[index++];
      const isNumericKey = Number.isFinite(+name);
      const isLast = index >= path.length;
      name = !name && utils.isArray(target) ? target.length : name;
      if (isLast) {
        if (utils.hasOwnProp(target, name)) {
          target[name] = [target[name], value];
        } else {
          target[name] = value;
        }
        return !isNumericKey;
      }
      if (!target[name] || !utils.isObject(target[name])) {
        target[name] = [];
      }
      const result = buildPath(path, value, target[name], index);
      if (result && utils.isArray(target[name])) {
        target[name] = arrayToObject(target[name]);
      }
      return !isNumericKey;
    }
    if (utils.isFormData(formData) && utils.isFunction(formData.entries)) {
      const obj = {};
      utils.forEachEntry(formData, (name, value) => {
        buildPath(parsePropPath(name), value, obj, 0);
      });
      return obj;
    }
    return null;
  }
  function stringifySafely(rawValue, parser, encoder) {
    if (utils.isString(rawValue)) {
      try {
        (parser || JSON.parse)(rawValue);
        return utils.trim(rawValue);
      } catch (e) {
        if (e.name !== "SyntaxError") {
          throw e;
        }
      }
    }
    return (encoder || JSON.stringify)(rawValue);
  }
  const defaults = {
    transitional: transitionalDefaults,
    adapter: platform.isNode ? "http" : "xhr",
    transformRequest: [function transformRequest(data, headers) {
      const contentType = headers.getContentType() || "";
      const hasJSONContentType = contentType.indexOf("application/json") > -1;
      const isObjectPayload = utils.isObject(data);
      if (isObjectPayload && utils.isHTMLForm(data)) {
        data = new FormData(data);
      }
      const isFormData2 = utils.isFormData(data);
      if (isFormData2) {
        if (!hasJSONContentType) {
          return data;
        }
        return hasJSONContentType ? JSON.stringify(formDataToJSON(data)) : data;
      }
      if (utils.isArrayBuffer(data) || utils.isBuffer(data) || utils.isStream(data) || utils.isFile(data) || utils.isBlob(data)) {
        return data;
      }
      if (utils.isArrayBufferView(data)) {
        return data.buffer;
      }
      if (utils.isURLSearchParams(data)) {
        headers.setContentType("application/x-www-form-urlencoded;charset=utf-8", false);
        return data.toString();
      }
      let isFileList2;
      if (isObjectPayload) {
        if (contentType.indexOf("application/x-www-form-urlencoded") > -1) {
          return toURLEncodedForm(data, this.formSerializer).toString();
        }
        if ((isFileList2 = utils.isFileList(data)) || contentType.indexOf("multipart/form-data") > -1) {
          const _FormData = this.env && this.env.FormData;
          return toFormData(
            isFileList2 ? { "files[]": data } : data,
            _FormData && new _FormData(),
            this.formSerializer
          );
        }
      }
      if (isObjectPayload || hasJSONContentType) {
        headers.setContentType("application/json", false);
        return stringifySafely(data);
      }
      return data;
    }],
    transformResponse: [function transformResponse(data) {
      const transitional = this.transitional || defaults.transitional;
      const forcedJSONParsing = transitional && transitional.forcedJSONParsing;
      const JSONRequested = this.responseType === "json";
      if (data && utils.isString(data) && (forcedJSONParsing && !this.responseType || JSONRequested)) {
        const silentJSONParsing = transitional && transitional.silentJSONParsing;
        const strictJSONParsing = !silentJSONParsing && JSONRequested;
        try {
          return JSON.parse(data);
        } catch (e) {
          if (strictJSONParsing) {
            if (e.name === "SyntaxError") {
              throw AxiosError.from(e, AxiosError.ERR_BAD_RESPONSE, this, null, this.response);
            }
            throw e;
          }
        }
      }
      return data;
    }],
    /**
     * A timeout in milliseconds to abort a request. If set to 0 (default) a
     * timeout is not created.
     */
    timeout: 0,
    xsrfCookieName: "XSRF-TOKEN",
    xsrfHeaderName: "X-XSRF-TOKEN",
    maxContentLength: -1,
    maxBodyLength: -1,
    env: {
      FormData: platform.classes.FormData,
      Blob: platform.classes.Blob
    },
    validateStatus: function validateStatus(status) {
      return status >= 200 && status < 300;
    },
    headers: {
      common: {
        "Accept": "application/json, text/plain, */*",
        "Content-Type": void 0
      }
    }
  };
  utils.forEach(["delete", "get", "head", "post", "put", "patch"], (method) => {
    defaults.headers[method] = {};
  });
  const defaults$1 = defaults;
  const ignoreDuplicateOf = utils.toObjectSet([
    "age",
    "authorization",
    "content-length",
    "content-type",
    "etag",
    "expires",
    "from",
    "host",
    "if-modified-since",
    "if-unmodified-since",
    "last-modified",
    "location",
    "max-forwards",
    "proxy-authorization",
    "referer",
    "retry-after",
    "user-agent"
  ]);
  const parseHeaders = (rawHeaders) => {
    const parsed = {};
    let key;
    let val;
    let i;
    rawHeaders && rawHeaders.split("\n").forEach(function parser(line) {
      i = line.indexOf(":");
      key = line.substring(0, i).trim().toLowerCase();
      val = line.substring(i + 1).trim();
      if (!key || parsed[key] && ignoreDuplicateOf[key]) {
        return;
      }
      if (key === "set-cookie") {
        if (parsed[key]) {
          parsed[key].push(val);
        } else {
          parsed[key] = [val];
        }
      } else {
        parsed[key] = parsed[key] ? parsed[key] + ", " + val : val;
      }
    });
    return parsed;
  };
  const $internals = Symbol("internals");
  function normalizeHeader(header) {
    return header && String(header).trim().toLowerCase();
  }
  function normalizeValue(value) {
    if (value === false || value == null) {
      return value;
    }
    return utils.isArray(value) ? value.map(normalizeValue) : String(value);
  }
  function parseTokens(str) {
    const tokens = /* @__PURE__ */ Object.create(null);
    const tokensRE = /([^\s,;=]+)\s*(?:=\s*([^,;]+))?/g;
    let match;
    while (match = tokensRE.exec(str)) {
      tokens[match[1]] = match[2];
    }
    return tokens;
  }
  const isValidHeaderName = (str) => /^[-_a-zA-Z0-9^`|~,!#$%&'*+.]+$/.test(str.trim());
  function matchHeaderValue(context, value, header, filter, isHeaderNameFilter) {
    if (utils.isFunction(filter)) {
      return filter.call(this, value, header);
    }
    if (isHeaderNameFilter) {
      value = header;
    }
    if (!utils.isString(value))
      return;
    if (utils.isString(filter)) {
      return value.indexOf(filter) !== -1;
    }
    if (utils.isRegExp(filter)) {
      return filter.test(value);
    }
  }
  function formatHeader(header) {
    return header.trim().toLowerCase().replace(/([a-z\d])(\w*)/g, (w, char, str) => {
      return char.toUpperCase() + str;
    });
  }
  function buildAccessors(obj, header) {
    const accessorName = utils.toCamelCase(" " + header);
    ["get", "set", "has"].forEach((methodName) => {
      Object.defineProperty(obj, methodName + accessorName, {
        value: function(arg1, arg2, arg3) {
          return this[methodName].call(this, header, arg1, arg2, arg3);
        },
        configurable: true
      });
    });
  }
  class AxiosHeaders {
    constructor(headers) {
      headers && this.set(headers);
    }
    set(header, valueOrRewrite, rewrite) {
      const self2 = this;
      function setHeader(_value, _header, _rewrite) {
        const lHeader = normalizeHeader(_header);
        if (!lHeader) {
          throw new Error("header name must be a non-empty string");
        }
        const key = utils.findKey(self2, lHeader);
        if (!key || self2[key] === void 0 || _rewrite === true || _rewrite === void 0 && self2[key] !== false) {
          self2[key || _header] = normalizeValue(_value);
        }
      }
      const setHeaders = (headers, _rewrite) => utils.forEach(headers, (_value, _header) => setHeader(_value, _header, _rewrite));
      if (utils.isPlainObject(header) || header instanceof this.constructor) {
        setHeaders(header, valueOrRewrite);
      } else if (utils.isString(header) && (header = header.trim()) && !isValidHeaderName(header)) {
        setHeaders(parseHeaders(header), valueOrRewrite);
      } else {
        header != null && setHeader(valueOrRewrite, header, rewrite);
      }
      return this;
    }
    get(header, parser) {
      header = normalizeHeader(header);
      if (header) {
        const key = utils.findKey(this, header);
        if (key) {
          const value = this[key];
          if (!parser) {
            return value;
          }
          if (parser === true) {
            return parseTokens(value);
          }
          if (utils.isFunction(parser)) {
            return parser.call(this, value, key);
          }
          if (utils.isRegExp(parser)) {
            return parser.exec(value);
          }
          throw new TypeError("parser must be boolean|regexp|function");
        }
      }
    }
    has(header, matcher) {
      header = normalizeHeader(header);
      if (header) {
        const key = utils.findKey(this, header);
        return !!(key && this[key] !== void 0 && (!matcher || matchHeaderValue(this, this[key], key, matcher)));
      }
      return false;
    }
    delete(header, matcher) {
      const self2 = this;
      let deleted = false;
      function deleteHeader(_header) {
        _header = normalizeHeader(_header);
        if (_header) {
          const key = utils.findKey(self2, _header);
          if (key && (!matcher || matchHeaderValue(self2, self2[key], key, matcher))) {
            delete self2[key];
            deleted = true;
          }
        }
      }
      if (utils.isArray(header)) {
        header.forEach(deleteHeader);
      } else {
        deleteHeader(header);
      }
      return deleted;
    }
    clear(matcher) {
      const keys = Object.keys(this);
      let i = keys.length;
      let deleted = false;
      while (i--) {
        const key = keys[i];
        if (!matcher || matchHeaderValue(this, this[key], key, matcher, true)) {
          delete this[key];
          deleted = true;
        }
      }
      return deleted;
    }
    normalize(format) {
      const self2 = this;
      const headers = {};
      utils.forEach(this, (value, header) => {
        const key = utils.findKey(headers, header);
        if (key) {
          self2[key] = normalizeValue(value);
          delete self2[header];
          return;
        }
        const normalized = format ? formatHeader(header) : String(header).trim();
        if (normalized !== header) {
          delete self2[header];
        }
        self2[normalized] = normalizeValue(value);
        headers[normalized] = true;
      });
      return this;
    }
    concat(...targets) {
      return this.constructor.concat(this, ...targets);
    }
    toJSON(asStrings) {
      const obj = /* @__PURE__ */ Object.create(null);
      utils.forEach(this, (value, header) => {
        value != null && value !== false && (obj[header] = asStrings && utils.isArray(value) ? value.join(", ") : value);
      });
      return obj;
    }
    [Symbol.iterator]() {
      return Object.entries(this.toJSON())[Symbol.iterator]();
    }
    toString() {
      return Object.entries(this.toJSON()).map(([header, value]) => header + ": " + value).join("\n");
    }
    get [Symbol.toStringTag]() {
      return "AxiosHeaders";
    }
    static from(thing) {
      return thing instanceof this ? thing : new this(thing);
    }
    static concat(first, ...targets) {
      const computed = new this(first);
      targets.forEach((target) => computed.set(target));
      return computed;
    }
    static accessor(header) {
      const internals = this[$internals] = this[$internals] = {
        accessors: {}
      };
      const accessors = internals.accessors;
      const prototype2 = this.prototype;
      function defineAccessor(_header) {
        const lHeader = normalizeHeader(_header);
        if (!accessors[lHeader]) {
          buildAccessors(prototype2, _header);
          accessors[lHeader] = true;
        }
      }
      utils.isArray(header) ? header.forEach(defineAccessor) : defineAccessor(header);
      return this;
    }
  }
  AxiosHeaders.accessor(["Content-Type", "Content-Length", "Accept", "Accept-Encoding", "User-Agent", "Authorization"]);
  utils.reduceDescriptors(AxiosHeaders.prototype, ({ value }, key) => {
    let mapped = key[0].toUpperCase() + key.slice(1);
    return {
      get: () => value,
      set(headerValue) {
        this[mapped] = headerValue;
      }
    };
  });
  utils.freezeMethods(AxiosHeaders);
  const AxiosHeaders$1 = AxiosHeaders;
  function transformData(fns, response) {
    const config = this || defaults$1;
    const context = response || config;
    const headers = AxiosHeaders$1.from(context.headers);
    let data = context.data;
    utils.forEach(fns, function transform(fn) {
      data = fn.call(config, data, headers.normalize(), response ? response.status : void 0);
    });
    headers.normalize();
    return data;
  }
  function isCancel(value) {
    return !!(value && value.__CANCEL__);
  }
  function CanceledError(message, config, request) {
    AxiosError.call(this, message == null ? "canceled" : message, AxiosError.ERR_CANCELED, config, request);
    this.name = "CanceledError";
  }
  utils.inherits(CanceledError, AxiosError, {
    __CANCEL__: true
  });
  function settle(resolve, reject, response) {
    const validateStatus = response.config.validateStatus;
    if (!response.status || !validateStatus || validateStatus(response.status)) {
      resolve(response);
    } else {
      reject(new AxiosError(
        "Request failed with status code " + response.status,
        [AxiosError.ERR_BAD_REQUEST, AxiosError.ERR_BAD_RESPONSE][Math.floor(response.status / 100) - 4],
        response.config,
        response.request,
        response
      ));
    }
  }
  const cookies = platform.isStandardBrowserEnv ? (
    // Standard browser envs support document.cookie
    function standardBrowserEnv() {
      return {
        write: function write(name, value, expires, path, domain, secure) {
          const cookie = [];
          cookie.push(name + "=" + encodeURIComponent(value));
          if (utils.isNumber(expires)) {
            cookie.push("expires=" + new Date(expires).toGMTString());
          }
          if (utils.isString(path)) {
            cookie.push("path=" + path);
          }
          if (utils.isString(domain)) {
            cookie.push("domain=" + domain);
          }
          if (secure === true) {
            cookie.push("secure");
          }
          document.cookie = cookie.join("; ");
        },
        read: function read(name) {
          const match = document.cookie.match(new RegExp("(^|;\\s*)(" + name + ")=([^;]*)"));
          return match ? decodeURIComponent(match[3]) : null;
        },
        remove: function remove(name) {
          this.write(name, "", Date.now() - 864e5);
        }
      };
    }()
  ) : (
    // Non standard browser env (web workers, react-native) lack needed support.
    function nonStandardBrowserEnv() {
      return {
        write: function write() {
        },
        read: function read() {
          return null;
        },
        remove: function remove() {
        }
      };
    }()
  );
  function isAbsoluteURL(url) {
    return /^([a-z][a-z\d+\-.]*:)?\/\//i.test(url);
  }
  function combineURLs(baseURL, relativeURL) {
    return relativeURL ? baseURL.replace(/\/+$/, "") + "/" + relativeURL.replace(/^\/+/, "") : baseURL;
  }
  function buildFullPath(baseURL, requestedURL) {
    if (baseURL && !isAbsoluteURL(requestedURL)) {
      return combineURLs(baseURL, requestedURL);
    }
    return requestedURL;
  }
  const isURLSameOrigin = platform.isStandardBrowserEnv ? (
    // Standard browser envs have full support of the APIs needed to test
    // whether the request URL is of the same origin as current location.
    function standardBrowserEnv() {
      const msie = /(msie|trident)/i.test(navigator.userAgent);
      const urlParsingNode = document.createElement("a");
      let originURL;
      function resolveURL(url) {
        let href = url;
        if (msie) {
          urlParsingNode.setAttribute("href", href);
          href = urlParsingNode.href;
        }
        urlParsingNode.setAttribute("href", href);
        return {
          href: urlParsingNode.href,
          protocol: urlParsingNode.protocol ? urlParsingNode.protocol.replace(/:$/, "") : "",
          host: urlParsingNode.host,
          search: urlParsingNode.search ? urlParsingNode.search.replace(/^\?/, "") : "",
          hash: urlParsingNode.hash ? urlParsingNode.hash.replace(/^#/, "") : "",
          hostname: urlParsingNode.hostname,
          port: urlParsingNode.port,
          pathname: urlParsingNode.pathname.charAt(0) === "/" ? urlParsingNode.pathname : "/" + urlParsingNode.pathname
        };
      }
      originURL = resolveURL(window.location.href);
      return function isURLSameOrigin2(requestURL) {
        const parsed = utils.isString(requestURL) ? resolveURL(requestURL) : requestURL;
        return parsed.protocol === originURL.protocol && parsed.host === originURL.host;
      };
    }()
  ) : (
    // Non standard browser envs (web workers, react-native) lack needed support.
    function nonStandardBrowserEnv() {
      return function isURLSameOrigin2() {
        return true;
      };
    }()
  );
  function parseProtocol(url) {
    const match = /^([-+\w]{1,25})(:?\/\/|:)/.exec(url);
    return match && match[1] || "";
  }
  function speedometer(samplesCount, min) {
    samplesCount = samplesCount || 10;
    const bytes = new Array(samplesCount);
    const timestamps = new Array(samplesCount);
    let head = 0;
    let tail = 0;
    let firstSampleTS;
    min = min !== void 0 ? min : 1e3;
    return function push(chunkLength) {
      const now2 = Date.now();
      const startedAt = timestamps[tail];
      if (!firstSampleTS) {
        firstSampleTS = now2;
      }
      bytes[head] = chunkLength;
      timestamps[head] = now2;
      let i = tail;
      let bytesCount = 0;
      while (i !== head) {
        bytesCount += bytes[i++];
        i = i % samplesCount;
      }
      head = (head + 1) % samplesCount;
      if (head === tail) {
        tail = (tail + 1) % samplesCount;
      }
      if (now2 - firstSampleTS < min) {
        return;
      }
      const passed = startedAt && now2 - startedAt;
      return passed ? Math.round(bytesCount * 1e3 / passed) : void 0;
    };
  }
  function progressEventReducer(listener, isDownloadStream) {
    let bytesNotified = 0;
    const _speedometer = speedometer(50, 250);
    return (e) => {
      const loaded2 = e.loaded;
      const total = e.lengthComputable ? e.total : void 0;
      const progressBytes = loaded2 - bytesNotified;
      const rate = _speedometer(progressBytes);
      const inRange = loaded2 <= total;
      bytesNotified = loaded2;
      const data = {
        loaded: loaded2,
        total,
        progress: total ? loaded2 / total : void 0,
        bytes: progressBytes,
        rate: rate ? rate : void 0,
        estimated: rate && total && inRange ? (total - loaded2) / rate : void 0,
        event: e
      };
      data[isDownloadStream ? "download" : "upload"] = true;
      listener(data);
    };
  }
  const isXHRAdapterSupported = typeof XMLHttpRequest !== "undefined";
  const xhrAdapter = isXHRAdapterSupported && function(config) {
    return new Promise(function dispatchXhrRequest(resolve, reject) {
      let requestData = config.data;
      const requestHeaders = AxiosHeaders$1.from(config.headers).normalize();
      const responseType = config.responseType;
      let onCanceled;
      function done() {
        if (config.cancelToken) {
          config.cancelToken.unsubscribe(onCanceled);
        }
        if (config.signal) {
          config.signal.removeEventListener("abort", onCanceled);
        }
      }
      if (utils.isFormData(requestData)) {
        if (platform.isStandardBrowserEnv || platform.isStandardBrowserWebWorkerEnv) {
          requestHeaders.setContentType(false);
        } else {
          requestHeaders.setContentType("multipart/form-data;", false);
        }
      }
      let request = new XMLHttpRequest();
      if (config.auth) {
        const username = config.auth.username || "";
        const password = config.auth.password ? unescape(encodeURIComponent(config.auth.password)) : "";
        requestHeaders.set("Authorization", "Basic " + btoa(username + ":" + password));
      }
      const fullPath = buildFullPath(config.baseURL, config.url);
      request.open(config.method.toUpperCase(), buildURL(fullPath, config.params, config.paramsSerializer), true);
      request.timeout = config.timeout;
      function onloadend() {
        if (!request) {
          return;
        }
        const responseHeaders = AxiosHeaders$1.from(
          "getAllResponseHeaders" in request && request.getAllResponseHeaders()
        );
        const responseData = !responseType || responseType === "text" || responseType === "json" ? request.responseText : request.response;
        const response = {
          data: responseData,
          status: request.status,
          statusText: request.statusText,
          headers: responseHeaders,
          config,
          request
        };
        settle(function _resolve(value) {
          resolve(value);
          done();
        }, function _reject(err) {
          reject(err);
          done();
        }, response);
        request = null;
      }
      if ("onloadend" in request) {
        request.onloadend = onloadend;
      } else {
        request.onreadystatechange = function handleLoad() {
          if (!request || request.readyState !== 4) {
            return;
          }
          if (request.status === 0 && !(request.responseURL && request.responseURL.indexOf("file:") === 0)) {
            return;
          }
          setTimeout(onloadend);
        };
      }
      request.onabort = function handleAbort() {
        if (!request) {
          return;
        }
        reject(new AxiosError("Request aborted", AxiosError.ECONNABORTED, config, request));
        request = null;
      };
      request.onerror = function handleError() {
        reject(new AxiosError("Network Error", AxiosError.ERR_NETWORK, config, request));
        request = null;
      };
      request.ontimeout = function handleTimeout() {
        let timeoutErrorMessage = config.timeout ? "timeout of " + config.timeout + "ms exceeded" : "timeout exceeded";
        const transitional = config.transitional || transitionalDefaults;
        if (config.timeoutErrorMessage) {
          timeoutErrorMessage = config.timeoutErrorMessage;
        }
        reject(new AxiosError(
          timeoutErrorMessage,
          transitional.clarifyTimeoutError ? AxiosError.ETIMEDOUT : AxiosError.ECONNABORTED,
          config,
          request
        ));
        request = null;
      };
      if (platform.isStandardBrowserEnv) {
        const xsrfValue = (config.withCredentials || isURLSameOrigin(fullPath)) && config.xsrfCookieName && cookies.read(config.xsrfCookieName);
        if (xsrfValue) {
          requestHeaders.set(config.xsrfHeaderName, xsrfValue);
        }
      }
      requestData === void 0 && requestHeaders.setContentType(null);
      if ("setRequestHeader" in request) {
        utils.forEach(requestHeaders.toJSON(), function setRequestHeader(val, key) {
          request.setRequestHeader(key, val);
        });
      }
      if (!utils.isUndefined(config.withCredentials)) {
        request.withCredentials = !!config.withCredentials;
      }
      if (responseType && responseType !== "json") {
        request.responseType = config.responseType;
      }
      if (typeof config.onDownloadProgress === "function") {
        request.addEventListener("progress", progressEventReducer(config.onDownloadProgress, true));
      }
      if (typeof config.onUploadProgress === "function" && request.upload) {
        request.upload.addEventListener("progress", progressEventReducer(config.onUploadProgress));
      }
      if (config.cancelToken || config.signal) {
        onCanceled = (cancel) => {
          if (!request) {
            return;
          }
          reject(!cancel || cancel.type ? new CanceledError(null, config, request) : cancel);
          request.abort();
          request = null;
        };
        config.cancelToken && config.cancelToken.subscribe(onCanceled);
        if (config.signal) {
          config.signal.aborted ? onCanceled() : config.signal.addEventListener("abort", onCanceled);
        }
      }
      const protocol = parseProtocol(fullPath);
      if (protocol && platform.protocols.indexOf(protocol) === -1) {
        reject(new AxiosError("Unsupported protocol " + protocol + ":", AxiosError.ERR_BAD_REQUEST, config));
        return;
      }
      request.send(requestData || null);
    });
  };
  const knownAdapters = {
    http: httpAdapter,
    xhr: xhrAdapter
  };
  utils.forEach(knownAdapters, (fn, value) => {
    if (fn) {
      try {
        Object.defineProperty(fn, "name", { value });
      } catch (e) {
      }
      Object.defineProperty(fn, "adapterName", { value });
    }
  });
  const adapters = {
    getAdapter: (adapters2) => {
      adapters2 = utils.isArray(adapters2) ? adapters2 : [adapters2];
      const { length } = adapters2;
      let nameOrAdapter;
      let adapter;
      for (let i = 0; i < length; i++) {
        nameOrAdapter = adapters2[i];
        if (adapter = utils.isString(nameOrAdapter) ? knownAdapters[nameOrAdapter.toLowerCase()] : nameOrAdapter) {
          break;
        }
      }
      if (!adapter) {
        if (adapter === false) {
          throw new AxiosError(
            `Adapter ${nameOrAdapter} is not supported by the environment`,
            "ERR_NOT_SUPPORT"
          );
        }
        throw new Error(
          utils.hasOwnProp(knownAdapters, nameOrAdapter) ? `Adapter '${nameOrAdapter}' is not available in the build` : `Unknown adapter '${nameOrAdapter}'`
        );
      }
      if (!utils.isFunction(adapter)) {
        throw new TypeError("adapter is not a function");
      }
      return adapter;
    },
    adapters: knownAdapters
  };
  function throwIfCancellationRequested(config) {
    if (config.cancelToken) {
      config.cancelToken.throwIfRequested();
    }
    if (config.signal && config.signal.aborted) {
      throw new CanceledError(null, config);
    }
  }
  function dispatchRequest(config) {
    throwIfCancellationRequested(config);
    config.headers = AxiosHeaders$1.from(config.headers);
    config.data = transformData.call(
      config,
      config.transformRequest
    );
    if (["post", "put", "patch"].indexOf(config.method) !== -1) {
      config.headers.setContentType("application/x-www-form-urlencoded", false);
    }
    const adapter = adapters.getAdapter(config.adapter || defaults$1.adapter);
    return adapter(config).then(function onAdapterResolution(response) {
      throwIfCancellationRequested(config);
      response.data = transformData.call(
        config,
        config.transformResponse,
        response
      );
      response.headers = AxiosHeaders$1.from(response.headers);
      return response;
    }, function onAdapterRejection(reason) {
      if (!isCancel(reason)) {
        throwIfCancellationRequested(config);
        if (reason && reason.response) {
          reason.response.data = transformData.call(
            config,
            config.transformResponse,
            reason.response
          );
          reason.response.headers = AxiosHeaders$1.from(reason.response.headers);
        }
      }
      return Promise.reject(reason);
    });
  }
  const headersToObject = (thing) => thing instanceof AxiosHeaders$1 ? thing.toJSON() : thing;
  function mergeConfig(config1, config2) {
    config2 = config2 || {};
    const config = {};
    function getMergedValue(target, source, caseless) {
      if (utils.isPlainObject(target) && utils.isPlainObject(source)) {
        return utils.merge.call({ caseless }, target, source);
      } else if (utils.isPlainObject(source)) {
        return utils.merge({}, source);
      } else if (utils.isArray(source)) {
        return source.slice();
      }
      return source;
    }
    function mergeDeepProperties(a, b, caseless) {
      if (!utils.isUndefined(b)) {
        return getMergedValue(a, b, caseless);
      } else if (!utils.isUndefined(a)) {
        return getMergedValue(void 0, a, caseless);
      }
    }
    function valueFromConfig2(a, b) {
      if (!utils.isUndefined(b)) {
        return getMergedValue(void 0, b);
      }
    }
    function defaultToConfig2(a, b) {
      if (!utils.isUndefined(b)) {
        return getMergedValue(void 0, b);
      } else if (!utils.isUndefined(a)) {
        return getMergedValue(void 0, a);
      }
    }
    function mergeDirectKeys(a, b, prop) {
      if (prop in config2) {
        return getMergedValue(a, b);
      } else if (prop in config1) {
        return getMergedValue(void 0, a);
      }
    }
    const mergeMap = {
      url: valueFromConfig2,
      method: valueFromConfig2,
      data: valueFromConfig2,
      baseURL: defaultToConfig2,
      transformRequest: defaultToConfig2,
      transformResponse: defaultToConfig2,
      paramsSerializer: defaultToConfig2,
      timeout: defaultToConfig2,
      timeoutMessage: defaultToConfig2,
      withCredentials: defaultToConfig2,
      adapter: defaultToConfig2,
      responseType: defaultToConfig2,
      xsrfCookieName: defaultToConfig2,
      xsrfHeaderName: defaultToConfig2,
      onUploadProgress: defaultToConfig2,
      onDownloadProgress: defaultToConfig2,
      decompress: defaultToConfig2,
      maxContentLength: defaultToConfig2,
      maxBodyLength: defaultToConfig2,
      beforeRedirect: defaultToConfig2,
      transport: defaultToConfig2,
      httpAgent: defaultToConfig2,
      httpsAgent: defaultToConfig2,
      cancelToken: defaultToConfig2,
      socketPath: defaultToConfig2,
      responseEncoding: defaultToConfig2,
      validateStatus: mergeDirectKeys,
      headers: (a, b) => mergeDeepProperties(headersToObject(a), headersToObject(b), true)
    };
    utils.forEach(Object.keys(Object.assign({}, config1, config2)), function computeConfigValue(prop) {
      const merge2 = mergeMap[prop] || mergeDeepProperties;
      const configValue = merge2(config1[prop], config2[prop], prop);
      utils.isUndefined(configValue) && merge2 !== mergeDirectKeys || (config[prop] = configValue);
    });
    return config;
  }
  const VERSION = "1.5.0";
  const validators$1 = {};
  ["object", "boolean", "number", "function", "string", "symbol"].forEach((type, i) => {
    validators$1[type] = function validator2(thing) {
      return typeof thing === type || "a" + (i < 1 ? "n " : " ") + type;
    };
  });
  const deprecatedWarnings = {};
  validators$1.transitional = function transitional(validator2, version, message) {
    function formatMessage(opt, desc) {
      return "[Axios v" + VERSION + "] Transitional option '" + opt + "'" + desc + (message ? ". " + message : "");
    }
    return (value, opt, opts) => {
      if (validator2 === false) {
        throw new AxiosError(
          formatMessage(opt, " has been removed" + (version ? " in " + version : "")),
          AxiosError.ERR_DEPRECATED
        );
      }
      if (version && !deprecatedWarnings[opt]) {
        deprecatedWarnings[opt] = true;
        console.warn(
          formatMessage(
            opt,
            " has been deprecated since v" + version + " and will be removed in the near future"
          )
        );
      }
      return validator2 ? validator2(value, opt, opts) : true;
    };
  };
  function assertOptions(options, schema, allowUnknown) {
    if (typeof options !== "object") {
      throw new AxiosError("options must be an object", AxiosError.ERR_BAD_OPTION_VALUE);
    }
    const keys = Object.keys(options);
    let i = keys.length;
    while (i-- > 0) {
      const opt = keys[i];
      const validator2 = schema[opt];
      if (validator2) {
        const value = options[opt];
        const result = value === void 0 || validator2(value, opt, options);
        if (result !== true) {
          throw new AxiosError("option " + opt + " must be " + result, AxiosError.ERR_BAD_OPTION_VALUE);
        }
        continue;
      }
      if (allowUnknown !== true) {
        throw new AxiosError("Unknown option " + opt, AxiosError.ERR_BAD_OPTION);
      }
    }
  }
  const validator = {
    assertOptions,
    validators: validators$1
  };
  const validators = validator.validators;
  class Axios {
    constructor(instanceConfig) {
      this.defaults = instanceConfig;
      this.interceptors = {
        request: new InterceptorManager$1(),
        response: new InterceptorManager$1()
      };
    }
    /**
     * Dispatch a request
     *
     * @param {String|Object} configOrUrl The config specific for this request (merged with this.defaults)
     * @param {?Object} config
     *
     * @returns {Promise} The Promise to be fulfilled
     */
    request(configOrUrl, config) {
      if (typeof configOrUrl === "string") {
        config = config || {};
        config.url = configOrUrl;
      } else {
        config = configOrUrl || {};
      }
      config = mergeConfig(this.defaults, config);
      const { transitional, paramsSerializer, headers } = config;
      if (transitional !== void 0) {
        validator.assertOptions(transitional, {
          silentJSONParsing: validators.transitional(validators.boolean),
          forcedJSONParsing: validators.transitional(validators.boolean),
          clarifyTimeoutError: validators.transitional(validators.boolean)
        }, false);
      }
      if (paramsSerializer != null) {
        if (utils.isFunction(paramsSerializer)) {
          config.paramsSerializer = {
            serialize: paramsSerializer
          };
        } else {
          validator.assertOptions(paramsSerializer, {
            encode: validators.function,
            serialize: validators.function
          }, true);
        }
      }
      config.method = (config.method || this.defaults.method || "get").toLowerCase();
      let contextHeaders = headers && utils.merge(
        headers.common,
        headers[config.method]
      );
      headers && utils.forEach(
        ["delete", "get", "head", "post", "put", "patch", "common"],
        (method) => {
          delete headers[method];
        }
      );
      config.headers = AxiosHeaders$1.concat(contextHeaders, headers);
      const requestInterceptorChain = [];
      let synchronousRequestInterceptors = true;
      this.interceptors.request.forEach(function unshiftRequestInterceptors(interceptor) {
        if (typeof interceptor.runWhen === "function" && interceptor.runWhen(config) === false) {
          return;
        }
        synchronousRequestInterceptors = synchronousRequestInterceptors && interceptor.synchronous;
        requestInterceptorChain.unshift(interceptor.fulfilled, interceptor.rejected);
      });
      const responseInterceptorChain = [];
      this.interceptors.response.forEach(function pushResponseInterceptors(interceptor) {
        responseInterceptorChain.push(interceptor.fulfilled, interceptor.rejected);
      });
      let promise;
      let i = 0;
      let len;
      if (!synchronousRequestInterceptors) {
        const chain = [dispatchRequest.bind(this), void 0];
        chain.unshift.apply(chain, requestInterceptorChain);
        chain.push.apply(chain, responseInterceptorChain);
        len = chain.length;
        promise = Promise.resolve(config);
        while (i < len) {
          promise = promise.then(chain[i++], chain[i++]);
        }
        return promise;
      }
      len = requestInterceptorChain.length;
      let newConfig = config;
      i = 0;
      while (i < len) {
        const onFulfilled = requestInterceptorChain[i++];
        const onRejected = requestInterceptorChain[i++];
        try {
          newConfig = onFulfilled(newConfig);
        } catch (error) {
          onRejected.call(this, error);
          break;
        }
      }
      try {
        promise = dispatchRequest.call(this, newConfig);
      } catch (error) {
        return Promise.reject(error);
      }
      i = 0;
      len = responseInterceptorChain.length;
      while (i < len) {
        promise = promise.then(responseInterceptorChain[i++], responseInterceptorChain[i++]);
      }
      return promise;
    }
    getUri(config) {
      config = mergeConfig(this.defaults, config);
      const fullPath = buildFullPath(config.baseURL, config.url);
      return buildURL(fullPath, config.params, config.paramsSerializer);
    }
  }
  utils.forEach(["delete", "get", "head", "options"], function forEachMethodNoData(method) {
    Axios.prototype[method] = function(url, config) {
      return this.request(mergeConfig(config || {}, {
        method,
        url,
        data: (config || {}).data
      }));
    };
  });
  utils.forEach(["post", "put", "patch"], function forEachMethodWithData(method) {
    function generateHTTPMethod(isForm) {
      return function httpMethod(url, data, config) {
        return this.request(mergeConfig(config || {}, {
          method,
          headers: isForm ? {
            "Content-Type": "multipart/form-data"
          } : {},
          url,
          data
        }));
      };
    }
    Axios.prototype[method] = generateHTTPMethod();
    Axios.prototype[method + "Form"] = generateHTTPMethod(true);
  });
  const Axios$1 = Axios;
  class CancelToken {
    constructor(executor) {
      if (typeof executor !== "function") {
        throw new TypeError("executor must be a function.");
      }
      let resolvePromise;
      this.promise = new Promise(function promiseExecutor(resolve) {
        resolvePromise = resolve;
      });
      const token = this;
      this.promise.then((cancel) => {
        if (!token._listeners)
          return;
        let i = token._listeners.length;
        while (i-- > 0) {
          token._listeners[i](cancel);
        }
        token._listeners = null;
      });
      this.promise.then = (onfulfilled) => {
        let _resolve;
        const promise = new Promise((resolve) => {
          token.subscribe(resolve);
          _resolve = resolve;
        }).then(onfulfilled);
        promise.cancel = function reject() {
          token.unsubscribe(_resolve);
        };
        return promise;
      };
      executor(function cancel(message, config, request) {
        if (token.reason) {
          return;
        }
        token.reason = new CanceledError(message, config, request);
        resolvePromise(token.reason);
      });
    }
    /**
     * Throws a `CanceledError` if cancellation has been requested.
     */
    throwIfRequested() {
      if (this.reason) {
        throw this.reason;
      }
    }
    /**
     * Subscribe to the cancel signal
     */
    subscribe(listener) {
      if (this.reason) {
        listener(this.reason);
        return;
      }
      if (this._listeners) {
        this._listeners.push(listener);
      } else {
        this._listeners = [listener];
      }
    }
    /**
     * Unsubscribe from the cancel signal
     */
    unsubscribe(listener) {
      if (!this._listeners) {
        return;
      }
      const index = this._listeners.indexOf(listener);
      if (index !== -1) {
        this._listeners.splice(index, 1);
      }
    }
    /**
     * Returns an object that contains a new `CancelToken` and a function that, when called,
     * cancels the `CancelToken`.
     */
    static source() {
      let cancel;
      const token = new CancelToken(function executor(c) {
        cancel = c;
      });
      return {
        token,
        cancel
      };
    }
  }
  const CancelToken$1 = CancelToken;
  function spread(callback) {
    return function wrap(arr) {
      return callback.apply(null, arr);
    };
  }
  function isAxiosError(payload) {
    return utils.isObject(payload) && payload.isAxiosError === true;
  }
  const HttpStatusCode = {
    Continue: 100,
    SwitchingProtocols: 101,
    Processing: 102,
    EarlyHints: 103,
    Ok: 200,
    Created: 201,
    Accepted: 202,
    NonAuthoritativeInformation: 203,
    NoContent: 204,
    ResetContent: 205,
    PartialContent: 206,
    MultiStatus: 207,
    AlreadyReported: 208,
    ImUsed: 226,
    MultipleChoices: 300,
    MovedPermanently: 301,
    Found: 302,
    SeeOther: 303,
    NotModified: 304,
    UseProxy: 305,
    Unused: 306,
    TemporaryRedirect: 307,
    PermanentRedirect: 308,
    BadRequest: 400,
    Unauthorized: 401,
    PaymentRequired: 402,
    Forbidden: 403,
    NotFound: 404,
    MethodNotAllowed: 405,
    NotAcceptable: 406,
    ProxyAuthenticationRequired: 407,
    RequestTimeout: 408,
    Conflict: 409,
    Gone: 410,
    LengthRequired: 411,
    PreconditionFailed: 412,
    PayloadTooLarge: 413,
    UriTooLong: 414,
    UnsupportedMediaType: 415,
    RangeNotSatisfiable: 416,
    ExpectationFailed: 417,
    ImATeapot: 418,
    MisdirectedRequest: 421,
    UnprocessableEntity: 422,
    Locked: 423,
    FailedDependency: 424,
    TooEarly: 425,
    UpgradeRequired: 426,
    PreconditionRequired: 428,
    TooManyRequests: 429,
    RequestHeaderFieldsTooLarge: 431,
    UnavailableForLegalReasons: 451,
    InternalServerError: 500,
    NotImplemented: 501,
    BadGateway: 502,
    ServiceUnavailable: 503,
    GatewayTimeout: 504,
    HttpVersionNotSupported: 505,
    VariantAlsoNegotiates: 506,
    InsufficientStorage: 507,
    LoopDetected: 508,
    NotExtended: 510,
    NetworkAuthenticationRequired: 511
  };
  Object.entries(HttpStatusCode).forEach(([key, value]) => {
    HttpStatusCode[value] = key;
  });
  const HttpStatusCode$1 = HttpStatusCode;
  function createInstance(defaultConfig) {
    const context = new Axios$1(defaultConfig);
    const instance = bind(Axios$1.prototype.request, context);
    utils.extend(instance, Axios$1.prototype, context, { allOwnKeys: true });
    utils.extend(instance, context, null, { allOwnKeys: true });
    instance.create = function create(instanceConfig) {
      return createInstance(mergeConfig(defaultConfig, instanceConfig));
    };
    return instance;
  }
  const axios = createInstance(defaults$1);
  axios.Axios = Axios$1;
  axios.CanceledError = CanceledError;
  axios.CancelToken = CancelToken$1;
  axios.isCancel = isCancel;
  axios.VERSION = VERSION;
  axios.toFormData = toFormData;
  axios.AxiosError = AxiosError;
  axios.Cancel = axios.CanceledError;
  axios.all = function all(promises) {
    return Promise.all(promises);
  };
  axios.spread = spread;
  axios.isAxiosError = isAxiosError;
  axios.mergeConfig = mergeConfig;
  axios.AxiosHeaders = AxiosHeaders$1;
  axios.formToJSON = (thing) => formDataToJSON(utils.isHTMLForm(thing) ? new FormData(thing) : thing);
  axios.getAdapter = adapters.getAdapter;
  axios.HttpStatusCode = HttpStatusCode$1;
  axios.default = axios;
  const axios$1 = axios;
  let restConfig = window.ZionProRestConfig;
  const ZionService = axios$1.create({
    baseURL: `${restConfig.rest_root}zionbuilder-pro/`,
    headers: {
      "X-WP-Nonce": restConfig.nonce,
      Accept: "application/json",
      "Content-Type": "application/json"
    }
  });
  const getAdobeFonts = function() {
    return ZionService.get("v1/adobe-fonts");
  };
  const refreshAdobeFontsLists = function() {
    return ZionService.get("v1/adobe-fonts/refresh-kits");
  };
  const uploadIconsPackage = function(iconPack) {
    return ZionService.post("v1/icons", iconPack, {
      headers: {
        "Content-Type": "multipart/form-data"
      }
    });
  };
  const exportIconsPackage = function(name) {
    return ZionService.post(
      `v1/icons/export`,
      {
        icon_package: name
      },
      {
        responseType: "arraybuffer"
      }
    );
  };
  const deleteIconsPackage = function(name) {
    return ZionService.delete("v1/icons", {
      data: {
        icon_package: name
      }
    });
  };
  const connectApiKey = function(license) {
    return ZionService.post("v1/license/connect", {
      api_key: license
    });
  };
  const deleteLicense = function() {
    return ZionService.post("v1/license/disconnect");
  };
  const projects = vue.ref([]);
  const loaded = vue.ref(false);
  const useAdobeFonts = () => {
    const fetchTypekitFonts = (useCache = true) => {
      return new Promise((resolve, reject) => {
        if (loaded.value && useCache) {
          resolve();
        } else {
          const method = useCache ? getAdobeFonts : refreshAdobeFontsLists;
          method().then((response) => {
            projects.value = response.data;
            resolve(response.data);
          }).catch(function(error) {
            reject(error);
          });
        }
      });
    };
    const getAdobeProjects = vue.computed(() => {
      return projects.value;
    });
    const resetTypekitFonts = () => {
      projects.value = [];
    };
    return {
      fetchTypekitFonts,
      resetTypekitFonts,
      getAdobeProjects
    };
  };
  const initialData = window.ZionBuilderProInitialData;
  const license_key = vue.ref(initialData.license_key);
  const license_details = vue.ref(initialData.license_details);
  const useLicense = () => {
    const getKey = () => {
      return license_key.value;
    };
    const getKeyDetails = () => {
      return license_details.value;
    };
    const updateApiKey = (newValue) => {
      license_key.value = newValue;
    };
    const updateApiDetails = (newValue) => {
      license_details.value = newValue;
    };
    const deleteApiKey = () => {
      license_key.value = "";
      license_details.value = null;
    };
    return {
      getKey,
      getKeyDetails,
      updateApiKey,
      updateApiDetails,
      deleteApiKey
    };
  };
  const _hoisted_1$5 = { class: "znpb-admin-typekit-fonts__header" };
  const _hoisted_2$5 = { class: "" };
  const _hoisted_3$4 = { class: "" };
  const _hoisted_4$2 = { class: "znpb-admin-typekit-fonts__content" };
  const _hoisted_5$2 = { key: 2 };
  const _hoisted_6$2 = { class: "znpb-admin-info-p" };
  const _sfc_main$6 = /* @__PURE__ */ vue.defineComponent({
    __name: "AdobeFonts",
    setup(__props) {
      const builderOptionsStore = store.useBuilderOptionsStore();
      const loading = vue.ref(false);
      const { getAdobeProjects, resetTypekitFonts, fetchTypekitFonts } = useAdobeFonts();
      let token = vue.computed({
        get: () => {
          return builderOptionsStore.getOptionValue("typekit_token");
        },
        set: (val) => {
          if (val.length > 0) {
            loading.value = true;
            loadKits();
          } else {
            resetTypekitFonts();
          }
          builderOptionsStore.addTypeKitToken(val);
          builderOptionsStore.saveOptionsToDB().then(() => {
            if (val.length > 0) {
              return loadKits(false);
            }
          }).finally(() => {
            loading.value = false;
          });
        }
      });
      loadKits();
      function loadKits(useCache = true) {
        loading.value = true;
        return fetchTypekitFonts(useCache).finally(() => {
          loading.value = false;
        });
      }
      return (_ctx, _cache) => {
        const _component_Button = vue.resolveComponent("Button");
        const _component_BaseInput = vue.resolveComponent("BaseInput");
        const _component_Tooltip = vue.resolveComponent("Tooltip");
        const _component_EmptyList = vue.resolveComponent("EmptyList");
        const _component_Loader = vue.resolveComponent("Loader");
        const _component_Icon = vue.resolveComponent("Icon");
        const _component_PageTemplate = vue.resolveComponent("PageTemplate");
        return vue.openBlock(), vue.createBlock(_component_PageTemplate, null, {
          right: vue.withCtx(() => [
            vue.createElementVNode("p", _hoisted_6$2, vue.toDisplayString(i18n__namespace.__("Here you can setup the Typekit fonts that you want to use in your site.", "zionbuilder-pro")), 1)
          ]),
          default: vue.withCtx(() => [
            vue.createElementVNode("h3", null, vue.toDisplayString(i18n__namespace.__("Typekit fonts", "zionbuilder-pro")), 1),
            vue.createElementVNode("div", _hoisted_1$5, [
              vue.createElementVNode("div", _hoisted_2$5, [
                vue.createElementVNode("h4", null, vue.toDisplayString(i18n__namespace.__("API token", "zionbuilder-pro")), 1)
              ]),
              vue.createElementVNode("div", _hoisted_3$4, [
                vue.createVNode(_component_Tooltip, {
                  content: i18n__namespace.__("Paste the Typekit Token in this field", "zionbuilder-pro")
                }, {
                  default: vue.withCtx(() => [
                    vue.createVNode(_component_BaseInput, {
                      modelValue: vue.unref(token),
                      "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => vue.isRef(token) ? token.value = $event : token = $event)
                    }, vue.createSlots({ _: 2 }, [
                      !vue.unref(token).length ? {
                        name: "suffix",
                        fn: vue.withCtx(() => [
                          vue.createVNode(_component_Button, { type: "line" }, {
                            default: vue.withCtx(() => [
                              vue.createTextVNode(vue.toDisplayString(i18n__namespace.__("Submit", "zionbuilder-pro")), 1)
                            ]),
                            _: 1
                          })
                        ]),
                        key: "0"
                      } : void 0
                    ]), 1032, ["modelValue"])
                  ]),
                  _: 1
                }, 8, ["content"])
              ])
            ]),
            vue.createElementVNode("div", _hoisted_4$2, [
              vue.unref(token) && !loading.value && vue.unref(getAdobeProjects).length === 0 ? (vue.openBlock(), vue.createBlock(_component_EmptyList, { key: 0 }, {
                default: vue.withCtx(() => [
                  vue.createTextVNode(vue.toDisplayString(i18n__namespace.__("No web projects added", "zionbuilder-pro")), 1)
                ]),
                _: 1
              })) : vue.createCommentVNode("", true),
              loading.value ? (vue.openBlock(), vue.createBlock(_component_Loader, { key: 1 })) : vue.createCommentVNode("", true),
              !loading.value && vue.unref(getAdobeProjects).length > 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_5$2, [
                (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(vue.unref(getAdobeProjects), (font, id) => {
                  return vue.openBlock(), vue.createElementBlock("div", { key: id }, [
                    (vue.openBlock(), vue.createBlock(_sfc_main$7, {
                      key: id,
                      font
                    }, null, 8, ["font"]))
                  ]);
                }), 128))
              ])) : vue.createCommentVNode("", true),
              vue.unref(token) && !loading.value ? (vue.openBlock(), vue.createBlock(_component_Button, {
                key: 3,
                type: "secondary",
                onClick: _cache[1] || (_cache[1] = ($event) => loadKits(false))
              }, {
                default: vue.withCtx(() => [
                  vue.createVNode(_component_Icon, { icon: "refresh" }),
                  vue.createTextVNode(" " + vue.toDisplayString(i18n__namespace.__("Refresh lists", "zionbuilder-pro")), 1)
                ]),
                _: 1
              })) : vue.createCommentVNode("", true)
            ])
          ]),
          _: 1
        });
      };
    }
  });
  const AdobeFonts_vue_vue_type_style_index_0_lang = "";
  var commonjsGlobal = typeof globalThis !== "undefined" ? globalThis : typeof window !== "undefined" ? window : typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : {};
  var FileSaver_min = { exports: {} };
  (function(module, exports) {
    (function(a, b) {
      b();
    })(commonjsGlobal, function() {
      function b(a2, b2) {
        return "undefined" == typeof b2 ? b2 = { autoBom: false } : "object" != typeof b2 && (console.warn("Deprecated: Expected third argument to be a object"), b2 = { autoBom: !b2 }), b2.autoBom && /^\s*(?:text\/\S*|application\/xml|\S*\/\S*\+xml)\s*;.*charset\s*=\s*utf-8/i.test(a2.type) ? new Blob(["\uFEFF", a2], { type: a2.type }) : a2;
      }
      function c(a2, b2, c2) {
        var d2 = new XMLHttpRequest();
        d2.open("GET", a2), d2.responseType = "blob", d2.onload = function() {
          g(d2.response, b2, c2);
        }, d2.onerror = function() {
          console.error("could not download file");
        }, d2.send();
      }
      function d(a2) {
        var b2 = new XMLHttpRequest();
        b2.open("HEAD", a2, false);
        try {
          b2.send();
        } catch (a3) {
        }
        return 200 <= b2.status && 299 >= b2.status;
      }
      function e(a2) {
        try {
          a2.dispatchEvent(new MouseEvent("click"));
        } catch (c2) {
          var b2 = document.createEvent("MouseEvents");
          b2.initMouseEvent("click", true, true, window, 0, 0, 0, 80, 20, false, false, false, false, 0, null), a2.dispatchEvent(b2);
        }
      }
      var f = "object" == typeof window && window.window === window ? window : "object" == typeof self && self.self === self ? self : "object" == typeof commonjsGlobal && commonjsGlobal.global === commonjsGlobal ? commonjsGlobal : void 0, a = f.navigator && /Macintosh/.test(navigator.userAgent) && /AppleWebKit/.test(navigator.userAgent) && !/Safari/.test(navigator.userAgent), g = f.saveAs || ("object" != typeof window || window !== f ? function() {
      } : "download" in HTMLAnchorElement.prototype && !a ? function(b2, g2, h) {
        var i = f.URL || f.webkitURL, j = document.createElement("a");
        g2 = g2 || b2.name || "download", j.download = g2, j.rel = "noopener", "string" == typeof b2 ? (j.href = b2, j.origin === location.origin ? e(j) : d(j.href) ? c(b2, g2, h) : e(j, j.target = "_blank")) : (j.href = i.createObjectURL(b2), setTimeout(function() {
          i.revokeObjectURL(j.href);
        }, 4e4), setTimeout(function() {
          e(j);
        }, 0));
      } : "msSaveOrOpenBlob" in navigator ? function(f2, g2, h) {
        if (g2 = g2 || f2.name || "download", "string" != typeof f2)
          navigator.msSaveOrOpenBlob(b(f2, h), g2);
        else if (d(f2))
          c(f2, g2, h);
        else {
          var i = document.createElement("a");
          i.href = f2, i.target = "_blank", setTimeout(function() {
            e(i);
          });
        }
      } : function(b2, d2, e2, g2) {
        if (g2 = g2 || open("", "_blank"), g2 && (g2.document.title = g2.document.body.innerText = "downloading..."), "string" == typeof b2)
          return c(b2, d2, e2);
        var h = "application/octet-stream" === b2.type, i = /constructor/i.test(f.HTMLElement) || f.safari, j = /CriOS\/[\d]+/.test(navigator.userAgent);
        if ((j || h && i || a) && "undefined" != typeof FileReader) {
          var k = new FileReader();
          k.onloadend = function() {
            var a2 = k.result;
            a2 = j ? a2 : a2.replace(/^data:[^;]*;/, "data:attachment/file;"), g2 ? g2.location.href = a2 : location = a2, g2 = null;
          }, k.readAsDataURL(b2);
        } else {
          var l = f.URL || f.webkitURL, m = l.createObjectURL(b2);
          g2 ? g2.location = m : location.href = m, g2 = null, setTimeout(function() {
            l.revokeObjectURL(m);
          }, 4e4);
        }
      });
      f.saveAs = g.saveAs = g, module.exports = g;
    });
  })(FileSaver_min);
  var FileSaver_minExports = FileSaver_min.exports;
  const _hoisted_1$4 = { class: "znpb-icon-pack-modal" };
  const _hoisted_2$4 = { class: "znpb-icon-pack-modal__search" };
  const _hoisted_3$3 = { class: "znpb-icon-pack-modal-scroll znpb-fancy-scrollbar" };
  const _sfc_main$5 = /* @__PURE__ */ vue.defineComponent({
    __name: "IconsPackModalContent",
    props: {
      iconList: { default: () => [] },
      family: { default: "" }
    },
    setup(__props) {
      const props = __props;
      const keyword = vue.ref("");
      const searchModel = vue.computed({
        get() {
          return keyword.value;
        },
        set(newVal) {
          keyword.value = newVal;
        }
      });
      const filteredList = vue.computed(() => {
        if (keyword.value.length > 0) {
          let filtered = [];
          for (const icon of props.iconList) {
            if (icon.name.includes(keyword.value)) {
              filtered.push(icon);
            }
          }
          return filtered;
        } else
          return props.iconList;
      });
      const getPlaceholder = vue.computed(() => {
        let a = `${i18n__namespace.__("Search through", "zionbuilder-pro")} ${getIconNumber.value} ${i18n__namespace.__("icons", "zionbuilder-pro")}`;
        return a;
      });
      const getIconNumber = vue.computed(() => {
        return filteredList.value.length;
      });
      return (_ctx, _cache) => {
        const _component_BaseInput = vue.resolveComponent("BaseInput");
        const _component_IconPackGrid = vue.resolveComponent("IconPackGrid");
        return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$4, [
          vue.createElementVNode("div", _hoisted_2$4, [
            vue.createVNode(_component_BaseInput, {
              modelValue: searchModel.value,
              "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => searchModel.value = $event),
              placeholder: getPlaceholder.value,
              clearable: true,
              icon: "search"
            }, null, 8, ["modelValue", "placeholder"])
          ]),
          vue.createElementVNode("div", _hoisted_3$3, [
            vue.createVNode(_component_IconPackGrid, {
              "icon-list": filteredList.value,
              family: _ctx.family,
              "has-scroll": false
            }, null, 8, ["icon-list", "family"])
          ])
        ]);
      };
    }
  });
  const IconsPackModalContent_vue_vue_type_style_index_0_lang = "";
  const _hoisted_1$3 = { class: "znpb-admin__google-font-tab" };
  const _hoisted_2$3 = { class: "znpb-admin__google-font-tab-title" };
  const _hoisted_3$2 = { class: "znpb-admin__google-font-tab-actions" };
  const _sfc_main$4 = /* @__PURE__ */ vue.defineComponent({
    __name: "IconTab",
    props: {
      iconsSet: {}
    },
    setup(__props) {
      const props = __props;
      const dataSetsStore = store.useDataSetsStore();
      const showModalConfirm = vue.ref(false);
      const showModal = vue.ref(false);
      function downloadPack() {
        exportIconsPackage(props.iconsSet.id).then((response) => {
          const blob = new Blob([response.data], {
            type: "application/zip"
          });
          FileSaver_minExports.saveAs(blob, `${props.iconsSet.name}.zip`);
        });
      }
      function deletePack() {
        showModalConfirm.value = false;
        deleteIconsPackage(props.iconsSet.id).then(() => {
          dataSetsStore.deleteIconSet(props.iconsSet.id);
        });
      }
      return (_ctx, _cache) => {
        const _component_Icon = vue.resolveComponent("Icon");
        const _component_ModalConfirm = vue.resolveComponent("ModalConfirm");
        const _component_modal = vue.resolveComponent("modal");
        const _directive_znpb_tooltip = vue.resolveDirective("znpb-tooltip");
        return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$3, [
          vue.createElementVNode("div", _hoisted_2$3, vue.toDisplayString(_ctx.iconsSet.name), 1),
          vue.createElementVNode("div", _hoisted_3$2, [
            vue.withDirectives(vue.createVNode(_component_Icon, {
              class: "znpb-actions-popup-icons",
              icon: "eye",
              onClick: _cache[0] || (_cache[0] = ($event) => showModal.value = true)
            }, null, 512), [
              [_directive_znpb_tooltip, i18n__namespace.__("Preview icon package", "zionbuilder-pro")]
            ]),
            vue.withDirectives(vue.createVNode(_component_Icon, {
              class: "znpb-actions-popup-icons",
              icon: "import",
              rotate: 180,
              onClick: downloadPack
            }, null, 512), [
              [_directive_znpb_tooltip, i18n__namespace.__("Download icon package", "zionbuilder-pro")]
            ]),
            !_ctx.iconsSet.built_in ? vue.withDirectives((vue.openBlock(), vue.createBlock(_component_Icon, {
              key: 0,
              class: "znpb-actions-popup-icons",
              icon: "delete",
              onClick: _cache[1] || (_cache[1] = ($event) => showModalConfirm.value = true)
            }, null, 512)), [
              [_directive_znpb_tooltip, i18n__namespace.__("Delete icon package", "zionbuilder-pro")]
            ]) : vue.createCommentVNode("", true)
          ]),
          showModalConfirm.value ? (vue.openBlock(), vue.createBlock(_component_ModalConfirm, {
            key: 0,
            width: 530,
            "confirm-text": i18n__namespace.__("Delete Pack", "zionbuilder-pro"),
            "cancel-text": i18n__namespace.__("Cancel", "zionbuilder-pro"),
            onConfirm: deletePack,
            onCancel: _cache[2] || (_cache[2] = ($event) => showModalConfirm.value = false)
          }, {
            default: vue.withCtx(() => [
              vue.createTextVNode(vue.toDisplayString(i18n__namespace.__("Are you sure you want to delete this icon set?", "zionbuilder-pro")), 1)
            ]),
            _: 1
          }, 8, ["confirm-text", "cancel-text"])) : vue.createCommentVNode("", true),
          vue.createVNode(_component_modal, {
            show: showModal.value,
            "onUpdate:show": _cache[3] || (_cache[3] = ($event) => showModal.value = $event),
            width: 590,
            title: _ctx.iconsSet.name,
            fullscreen: false,
            "show-backdrop": false,
            "append-to": "#znpb-admin",
            "show-maximize": false
          }, {
            default: vue.withCtx(() => [
              vue.createVNode(_sfc_main$5, {
                "icon-list": _ctx.iconsSet.icons,
                family: _ctx.iconsSet.name
              }, null, 8, ["icon-list", "family"])
            ]),
            _: 1
          }, 8, ["show", "title"])
        ]);
      };
    }
  });
  const IconTab_vue_vue_type_style_index_0_lang = "";
  const _hoisted_1$2 = {
    key: 0,
    class: "znpb-admin__google-font-tab znpb-admin__google-font-tab--titles"
  };
  const _hoisted_2$2 = { class: "znpb-admin__google-font-tab-title" };
  const _hoisted_3$1 = { class: "znpb-admin__google-font-tab-actions" };
  const _hoisted_4$1 = { class: "znpb-admin-icons-wrapper" };
  const _hoisted_5$1 = {
    key: 1,
    class: "znpb-admin-google-fonts-wrapper"
  };
  const _hoisted_6$1 = { class: "znpb-admin-google-fonts-actions znpb-admin-upload-icons" };
  const _hoisted_7$1 = ["value", "disabled"];
  const _hoisted_8$1 = { class: "znpb-admin-info-p" };
  const _sfc_main$3 = /* @__PURE__ */ vue.defineComponent({
    __name: "IconsManager",
    setup(__props) {
      const dataSetsStore = store.useDataSetsStore();
      const isSaving = vue.ref(false);
      const value = vue.ref();
      let inputValue = vue.computed({
        get: () => {
          return value.value;
        },
        set: (file) => {
          if (!file) {
            return;
          }
          const formData = new FormData();
          formData.append("zip", file, file.name);
          uploadIconsPackage(formData).then((response) => {
            if (response.data.css) {
              const style = document.createElement("style");
              style.appendChild(document.createTextNode(response.data.css));
              document.head.appendChild(style);
            }
            dataSetsStore.addIconsSet(response.data);
          }).finally(() => {
            inputValue.value = null;
          });
        }
      });
      return (_ctx, _cache) => {
        const _component_EmptyList = vue.resolveComponent("EmptyList");
        const _component_Tooltip = vue.resolveComponent("Tooltip");
        const _component_ListAnimation = vue.resolveComponent("ListAnimation");
        const _component_Icon = vue.resolveComponent("Icon");
        const _component_Button = vue.resolveComponent("Button");
        const _component_PageTemplate = vue.resolveComponent("PageTemplate");
        return vue.openBlock(), vue.createBlock(_component_PageTemplate, null, {
          right: vue.withCtx(() => [
            vue.createElementVNode("p", _hoisted_8$1, vue.toDisplayString(i18n__namespace.__("In this page you can add additional icon packages", "zionbuilder-pro")), 1)
          ]),
          default: vue.withCtx(() => [
            vue.createElementVNode("h3", null, vue.toDisplayString(i18n__namespace.__("Custom icons", "zionbuilder-pro")), 1),
            vue.unref(dataSetsStore).dataSets.icons.length > 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$2, [
              vue.createElementVNode("div", _hoisted_2$2, vue.toDisplayString(i18n__namespace.__("Icon pack", "zionbuilder-pro")), 1),
              vue.createElementVNode("div", _hoisted_3$1, vue.toDisplayString(i18n__namespace.__("Actions", "zionbuilder-pro")), 1)
            ])) : vue.createCommentVNode("", true),
            vue.createElementVNode("div", _hoisted_4$1, [
              vue.unref(dataSetsStore).dataSets.icons.length === 0 ? (vue.openBlock(), vue.createBlock(_component_Tooltip, {
                key: 0,
                content: i18n__namespace.__("Click to add icons", "zionbuilder-pro")
              }, {
                default: vue.withCtx(() => [
                  vue.createVNode(_component_EmptyList, null, {
                    default: vue.withCtx(() => [
                      vue.createTextVNode(vue.toDisplayString(i18n__namespace.__("No icons", "zionbuilder-pro")), 1)
                    ]),
                    _: 1
                  })
                ]),
                _: 1
              }, 8, ["content"])) : vue.createCommentVNode("", true),
              vue.unref(dataSetsStore).dataSets.icons.length > 0 ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_5$1, [
                vue.createVNode(_component_ListAnimation, null, {
                  default: vue.withCtx(() => [
                    (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(vue.unref(dataSetsStore).dataSets.icons, (set, i) => {
                      return vue.openBlock(), vue.createBlock(_sfc_main$4, {
                        key: i,
                        "icons-set": set,
                        class: "znpb-admin-tab"
                      }, null, 8, ["icons-set"]);
                    }), 128))
                  ]),
                  _: 1
                })
              ])) : vue.createCommentVNode("", true),
              vue.createElementVNode("div", _hoisted_6$1, [
                vue.createElementVNode("input", {
                  value: vue.unref(inputValue),
                  type: "file",
                  accept: "zip, application/octet-stream, application/zip, application/x-zip, application/x-zip-compressed",
                  multiple: "",
                  disabled: isSaving.value,
                  class: "znpb-library-input-file",
                  onChange: _cache[0] || (_cache[0] = ($event) => vue.isRef(inputValue) ? inputValue.value = $event.target.files[0] : inputValue = $event.target.files[0])
                }, null, 40, _hoisted_7$1),
                vue.createVNode(_component_Button, { type: "secondary" }, {
                  default: vue.withCtx(() => [
                    vue.createVNode(_component_Icon, { icon: "plus" }),
                    vue.createTextVNode(" " + vue.toDisplayString(i18n__namespace.__("Add icons", "zionbuilder-pro")), 1)
                  ]),
                  _: 1
                })
              ])
            ])
          ]),
          _: 1
        });
      };
    }
  });
  const IconsManager_vue_vue_type_style_index_0_lang = "";
  const _hoisted_1$1 = { class: "znpb-admin-content-wrapper" };
  const _hoisted_2$1 = /* @__PURE__ */ vue.createElementVNode("div", { class: "znpb-admin-content znpb-admin-content--left znpb-admin-content--hiddenXs" }, null, -1);
  const _hoisted_3 = {
    key: 0,
    class: "znpb-admin-license-inputWrapper"
  };
  const _hoisted_4 = { key: 1 };
  const _hoisted_5 = { class: "znpb-admin-templates-titles" };
  const _hoisted_6 = { class: "znpb-admin-templates-titles__heading znpb-admin-templates-titles__heading--title" };
  const _hoisted_7 = { class: "znpb-admin-templates-titles__heading" };
  const _hoisted_8 = { class: "znpb-admin-templates-titles__heading znpb-admin-templates-titles__heading--actions" };
  const _hoisted_9 = { class: "znpb-admin-single-template" };
  const _hoisted_10 = ["innerHTML"];
  const _hoisted_11 = ["innerHTML"];
  const _hoisted_12 = { class: "znpb-admin-single-template__actions" };
  const _hoisted_13 = ["innerHTML"];
  const _hoisted_14 = { class: "znpb-admin-info-p" };
  const _hoisted_15 = { class: "znpb-admin-info-p" };
  const _sfc_main$2 = /* @__PURE__ */ vue.defineComponent({
    __name: "ProLicense",
    setup(__props) {
      const { getKey, getKeyDetails, updateApiKey, updateApiDetails, deleteApiKey } = useLicense();
      const license_key2 = vue.ref(getKey());
      const showLicenseInput = vue.ref(license_key2.value.length > 0 ? false : true);
      const message = vue.ref("");
      const loading = vue.ref(false);
      const apiKeyModel = vue.computed({
        get() {
          return license_key2.value;
        },
        set(newValue) {
          license_key2.value = newValue;
        }
      });
      const hiddenLicensekey = vue.computed(() => {
        let lastFourPosition = license_key2.value.length - 4;
        return `XXXXXXXXXXXXXXXXXXXXXXXXXXXX${license_key2.value.substr(lastFourPosition)}`;
      });
      function getValidDate() {
        let details = getKeyDetails();
        if (details && details.expires === "lifetime") {
          return "lifetime";
        } else {
          const validUntil = details ? details.expires : null;
          const date = new Date(Date.parse(validUntil));
          return date.toLocaleDateString("en-US");
        }
      }
      function deleteKey() {
        loading.value = true;
        deleteLicense().then(() => {
          showLicenseInput.value = true;
          license_key2.value = "";
          deleteApiKey();
          loading.value = false;
        }).catch((error) => {
          message.value = error.message;
          loading.value = false;
        });
      }
      function callCheckLicense() {
        loading.value = true;
        connectApiKey(license_key2.value).then((response) => {
          updateApiKey(license_key2.value);
          updateApiDetails(response.data);
          showLicenseInput.value = false;
          loading.value = false;
        }).catch((error) => {
          message.value = error.message;
          loading.value = false;
        }).finally(() => {
          loading.value = false;
          license_key2.value = getKey();
        });
      }
      return (_ctx, _cache) => {
        const _component_BaseInput = vue.resolveComponent("BaseInput");
        const _component_Loader = vue.resolveComponent("Loader");
        const _component_Button = vue.resolveComponent("Button");
        const _component_Icon = vue.resolveComponent("Icon");
        const _component_Tooltip = vue.resolveComponent("Tooltip");
        const _component_PageTemplate = vue.resolveComponent("PageTemplate");
        return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$1, [
          _hoisted_2$1,
          vue.createVNode(_component_PageTemplate, null, {
            right: vue.withCtx(() => [
              vue.createElementVNode("div", null, [
                vue.createElementVNode("p", _hoisted_14, vue.toDisplayString(i18n__namespace.__("Add PRO license", "zionbuilder-pro")), 1),
                vue.createElementVNode("p", _hoisted_15, vue.toDisplayString(i18n__namespace.__("Add the license key you received after purchased", "zionbuilder-pro")), 1)
              ])
            ]),
            default: vue.withCtx(() => [
              vue.createElementVNode("h3", null, vue.toDisplayString(i18n__namespace.__("PRO license key", "zionbuilder-pro")), 1),
              showLicenseInput.value ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3, [
                vue.createVNode(_component_BaseInput, {
                  modelValue: apiKeyModel.value,
                  "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => apiKeyModel.value = $event),
                  placeholder: i18n__namespace.__("11x1x111111x1x111111x11x11xx1xx1", "zionbuilder-pro"),
                  size: "narrow"
                }, null, 8, ["modelValue", "placeholder"]),
                vue.createVNode(_component_Button, {
                  type: "line",
                  onClick: _cache[1] || (_cache[1] = ($event) => callCheckLicense(apiKeyModel.value))
                }, {
                  default: vue.withCtx(() => [
                    vue.createVNode(vue.Transition, {
                      name: "fade",
                      mode: "out-in"
                    }, {
                      default: vue.withCtx(() => [
                        loading.value ? (vue.openBlock(), vue.createBlock(_component_Loader, {
                          key: 0,
                          size: 13
                        })) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_4, vue.toDisplayString(i18n__namespace.__("Add license key", "zionbuilder-pro")), 1))
                      ]),
                      _: 1
                    })
                  ]),
                  _: 1
                })
              ])) : (vue.openBlock(), vue.createElementBlock(vue.Fragment, { key: 1 }, [
                vue.createElementVNode("div", _hoisted_5, [
                  vue.createElementVNode("h5", _hoisted_6, vue.toDisplayString(i18n__namespace.__("Key", "zionbuilder-pro")), 1),
                  vue.createElementVNode("h5", _hoisted_7, vue.toDisplayString(i18n__namespace.__("Valid until", "zionbuilder-pro")), 1),
                  vue.createElementVNode("h5", _hoisted_8, vue.toDisplayString(i18n__namespace.__("Actions", "zionbuilder-pro")), 1)
                ]),
                vue.createElementVNode("div", _hoisted_9, [
                  vue.createElementVNode("span", {
                    class: "znpb-admin-single-template__title",
                    innerHTML: hiddenLicensekey.value
                  }, null, 8, _hoisted_10),
                  vue.createElementVNode("span", {
                    class: "znpb-admin-single-template__author",
                    innerHTML: getValidDate()
                  }, null, 8, _hoisted_11),
                  vue.createElementVNode("div", _hoisted_12, [
                    !loading.value ? (vue.openBlock(), vue.createBlock(_component_Tooltip, {
                      key: 0,
                      content: i18n__namespace.__("Delete key", "zionbuilder-pro"),
                      "append-to": "element",
                      class: "znpb-admin-single-template__action znpb-delete-icon-pop",
                      modifiers: [
                        {
                          name: "offset",
                          options: {
                            offset: [0, 15]
                          }
                        }
                      ],
                      "position-fixed": true
                    }, {
                      default: vue.withCtx(() => [
                        vue.createVNode(_component_Icon, {
                          icon: "delete",
                          onClick: deleteKey
                        })
                      ]),
                      _: 1
                    }, 8, ["content"])) : (vue.openBlock(), vue.createBlock(_component_Loader, {
                      key: 1,
                      size: 13
                    }))
                  ])
                ])
              ], 64)),
              message.value.length ? (vue.openBlock(), vue.createElementBlock("p", {
                key: 2,
                class: "znpb-admin-license__error-message",
                innerHTML: message.value
              }, null, 8, _hoisted_13)) : vue.createCommentVNode("", true)
            ]),
            _: 1
          })
        ]);
      };
    }
  });
  const ProLicense_vue_vue_type_style_index_0_lang = "";
  const _sfc_main$1 = /* @__PURE__ */ vue.defineComponent({
    __name: "LibraryPage",
    setup(__props) {
      const builderOptionsStore = store.useBuilderOptionsStore();
      const computedModel = vue.computed({
        get() {
          return builderOptionsStore.getOptionValue("library_share", {});
        },
        set(newValue) {
          if (newValue === null) {
            builderOptionsStore.updateOptionValue("library_share", {}, false);
          } else {
            const valuesWithIds = generateSourceIDs(newValue);
            builderOptionsStore.updateOptionValue("library_share", valuesWithIds, false);
          }
          builderOptionsStore.debouncedSaveOptions();
        }
      });
      const schema = window.ZionBuilderProInitialData.schemas.library_share;
      function generateSourceIDs(values) {
        if (typeof values.library_sources && Array.isArray(values.library_sources)) {
          values.library_sources.forEach((sourceConfig) => {
            if (typeof sourceConfig.id === "undefined") {
              sourceConfig.id = window.zb.utils.generateUID();
            }
          });
        }
        return values;
      }
      return (_ctx, _cache) => {
        const _component_OptionsForm = vue.resolveComponent("OptionsForm");
        const _component_PageTemplate = vue.resolveComponent("PageTemplate");
        return vue.openBlock(), vue.createBlock(_component_PageTemplate, { class: "znpb-librarySourcesPage" }, {
          default: vue.withCtx(() => [
            vue.createElementVNode("h3", null, vue.toDisplayString(i18n__namespace.__("Library share", "zionbuilder-pro")), 1),
            vue.createVNode(_component_OptionsForm, {
              modelValue: computedModel.value,
              "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => computedModel.value = $event),
              schema: vue.unref(schema),
              class: "znpb-connectorForm"
            }, null, 8, ["modelValue", "schema"])
          ]),
          _: 1
        });
      };
    }
  });
  const LibraryPage_vue_vue_type_style_index_0_lang = "";
  class Admin {
    constructor(ZionInterface) {
      this.changeRoutes(ZionInterface.routes);
    }
    changeRoutes(routes) {
      const customFontsRoute = routes.getRouteConfig("settings.font-options.custom-fonts");
      if (customFontsRoute) {
        delete customFontsRoute.routeConfig.meta.label;
        customFontsRoute.set("component", _sfc_main$8);
      }
      const adobeFontsRoute = routes.getRouteConfig("settings.font-options.adobe-fonts");
      if (adobeFontsRoute) {
        delete adobeFontsRoute.routeConfig.meta.label;
        adobeFontsRoute.set("component", _sfc_main$6);
      }
      const customIconsRoute = routes.getRouteConfig("settings.custom-icons");
      if (customIconsRoute) {
        delete customIconsRoute.routeConfig.meta.label;
        customIconsRoute.set("component", _sfc_main$3);
      }
      const connectorRoute = routes.getRouteConfig("settings.library");
      if (connectorRoute) {
        delete connectorRoute.routeConfig.meta.label;
        connectorRoute.set("component", _sfc_main$1);
      }
      routes.addRoute("pro-license", {
        path: "/pro-license",
        component: _sfc_main$2,
        meta: {
          title: i18n__namespace.__("License key", "zionbuilder")
        }
      });
    }
  }
  var freeGlobal = typeof global == "object" && global && global.Object === Object && global;
  const freeGlobal$1 = freeGlobal;
  var freeSelf = typeof self == "object" && self && self.Object === Object && self;
  var root = freeGlobal$1 || freeSelf || Function("return this")();
  const root$1 = root;
  var Symbol$1 = root$1.Symbol;
  const Symbol$2 = Symbol$1;
  var objectProto$1 = Object.prototype;
  var hasOwnProperty = objectProto$1.hasOwnProperty;
  var nativeObjectToString$1 = objectProto$1.toString;
  var symToStringTag$1 = Symbol$2 ? Symbol$2.toStringTag : void 0;
  function getRawTag(value) {
    var isOwn = hasOwnProperty.call(value, symToStringTag$1), tag = value[symToStringTag$1];
    try {
      value[symToStringTag$1] = void 0;
      var unmasked = true;
    } catch (e) {
    }
    var result = nativeObjectToString$1.call(value);
    if (unmasked) {
      if (isOwn) {
        value[symToStringTag$1] = tag;
      } else {
        delete value[symToStringTag$1];
      }
    }
    return result;
  }
  var objectProto = Object.prototype;
  var nativeObjectToString = objectProto.toString;
  function objectToString(value) {
    return nativeObjectToString.call(value);
  }
  var nullTag = "[object Null]", undefinedTag = "[object Undefined]";
  var symToStringTag = Symbol$2 ? Symbol$2.toStringTag : void 0;
  function baseGetTag(value) {
    if (value == null) {
      return value === void 0 ? undefinedTag : nullTag;
    }
    return symToStringTag && symToStringTag in Object(value) ? getRawTag(value) : objectToString(value);
  }
  function isObjectLike(value) {
    return value != null && typeof value == "object";
  }
  var symbolTag = "[object Symbol]";
  function isSymbol(value) {
    return typeof value == "symbol" || isObjectLike(value) && baseGetTag(value) == symbolTag;
  }
  var reWhitespace = /\s/;
  function trimmedEndIndex(string) {
    var index = string.length;
    while (index-- && reWhitespace.test(string.charAt(index))) {
    }
    return index;
  }
  var reTrimStart = /^\s+/;
  function baseTrim(string) {
    return string ? string.slice(0, trimmedEndIndex(string) + 1).replace(reTrimStart, "") : string;
  }
  function isObject(value) {
    var type = typeof value;
    return value != null && (type == "object" || type == "function");
  }
  var NAN = 0 / 0;
  var reIsBadHex = /^[-+]0x[0-9a-f]+$/i;
  var reIsBinary = /^0b[01]+$/i;
  var reIsOctal = /^0o[0-7]+$/i;
  var freeParseInt = parseInt;
  function toNumber(value) {
    if (typeof value == "number") {
      return value;
    }
    if (isSymbol(value)) {
      return NAN;
    }
    if (isObject(value)) {
      var other = typeof value.valueOf == "function" ? value.valueOf() : value;
      value = isObject(other) ? other + "" : other;
    }
    if (typeof value != "string") {
      return value === 0 ? value : +value;
    }
    value = baseTrim(value);
    var isBinary = reIsBinary.test(value);
    return isBinary || reIsOctal.test(value) ? freeParseInt(value.slice(2), isBinary ? 2 : 8) : reIsBadHex.test(value) ? NAN : +value;
  }
  var now = function() {
    return root$1.Date.now();
  };
  const now$1 = now;
  var FUNC_ERROR_TEXT = "Expected a function";
  var nativeMax = Math.max, nativeMin = Math.min;
  function debounce(func, wait, options) {
    var lastArgs, lastThis, maxWait, result, timerId, lastCallTime, lastInvokeTime = 0, leading = false, maxing = false, trailing = true;
    if (typeof func != "function") {
      throw new TypeError(FUNC_ERROR_TEXT);
    }
    wait = toNumber(wait) || 0;
    if (isObject(options)) {
      leading = !!options.leading;
      maxing = "maxWait" in options;
      maxWait = maxing ? nativeMax(toNumber(options.maxWait) || 0, wait) : maxWait;
      trailing = "trailing" in options ? !!options.trailing : trailing;
    }
    function invokeFunc(time) {
      var args = lastArgs, thisArg = lastThis;
      lastArgs = lastThis = void 0;
      lastInvokeTime = time;
      result = func.apply(thisArg, args);
      return result;
    }
    function leadingEdge(time) {
      lastInvokeTime = time;
      timerId = setTimeout(timerExpired, wait);
      return leading ? invokeFunc(time) : result;
    }
    function remainingWait(time) {
      var timeSinceLastCall = time - lastCallTime, timeSinceLastInvoke = time - lastInvokeTime, timeWaiting = wait - timeSinceLastCall;
      return maxing ? nativeMin(timeWaiting, maxWait - timeSinceLastInvoke) : timeWaiting;
    }
    function shouldInvoke(time) {
      var timeSinceLastCall = time - lastCallTime, timeSinceLastInvoke = time - lastInvokeTime;
      return lastCallTime === void 0 || timeSinceLastCall >= wait || timeSinceLastCall < 0 || maxing && timeSinceLastInvoke >= maxWait;
    }
    function timerExpired() {
      var time = now$1();
      if (shouldInvoke(time)) {
        return trailingEdge(time);
      }
      timerId = setTimeout(timerExpired, remainingWait(time));
    }
    function trailingEdge(time) {
      timerId = void 0;
      if (trailing && lastArgs) {
        return invokeFunc(time);
      }
      lastArgs = lastThis = void 0;
      return result;
    }
    function cancel() {
      if (timerId !== void 0) {
        clearTimeout(timerId);
      }
      lastInvokeTime = 0;
      lastArgs = lastCallTime = lastThis = timerId = void 0;
    }
    function flush() {
      return timerId === void 0 ? result : trailingEdge(now$1());
    }
    function debounced() {
      var time = now$1(), isInvoking = shouldInvoke(time);
      lastArgs = arguments;
      lastThis = this;
      lastCallTime = time;
      if (isInvoking) {
        if (timerId === void 0) {
          return leadingEdge(lastCallTime);
        }
        if (maxing) {
          clearTimeout(timerId);
          timerId = setTimeout(timerExpired, wait);
          return invokeFunc(lastCallTime);
        }
      }
      if (timerId === void 0) {
        timerId = setTimeout(timerExpired, wait);
      }
      return result;
    }
    debounced.cancel = cancel;
    debounced.flush = flush;
    return debounced;
  }
  const _hoisted_1 = { class: "znpb-white-label" };
  const _hoisted_2 = { class: "znpb-admin-info-p" };
  const _sfc_main = /* @__PURE__ */ vue.defineComponent({
    __name: "WhiteLabel",
    props: {
      modelValue: { default: () => ({}) }
    },
    setup(__props) {
      const builderOptionsStore = store.useBuilderOptionsStore();
      const valueModel = vue.computed({
        get: () => builderOptionsStore.getOptionValue("white_label") || {},
        set: (newValue) => updateOptionValueThrottled("white_label", newValue)
      });
      const schema = window.ZionBuilderProInitialData.white_label_schema;
      const updateOptionValueThrottled = debounce(builderOptionsStore.updateOptionValue, 300);
      return (_ctx, _cache) => {
        const _component_OptionsForm = vue.resolveComponent("OptionsForm");
        const _component_PageTemplate = vue.resolveComponent("PageTemplate");
        return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
          vue.createVNode(_component_PageTemplate, null, {
            right: vue.withCtx(() => [
              vue.createElementVNode("p", _hoisted_2, vue.toDisplayString(i18n__namespace.__(
                "Welcome to white label hidden options! Input added will replace the content everywhere it is used in the pagebuilder",
                "zionbuilder-pro"
              )), 1)
            ]),
            default: vue.withCtx(() => [
              vue.createElementVNode("h3", null, vue.toDisplayString(i18n__namespace.__("White label", "zionbuilder-pro")), 1),
              vue.createVNode(_component_OptionsForm, {
                modelValue: valueModel.value,
                "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => valueModel.value = $event),
                schema: vue.unref(schema),
                class: "znpb-white-label-form"
              }, null, 8, ["modelValue", "schema"])
            ]),
            _: 1
          })
        ]);
      };
    }
  });
  function getRouter(router) {
    router.beforeEach((to) => {
      if (to.path === "/settings/whitelabel" && !router.hasRoute("whitelabel")) {
        router.addRoute("settings", {
          path: "/settings/whitelabel",
          name: "whitelabel",
          component: _sfc_main,
          title: i18n__namespace.__("White label", "zionbuilder-pro")
        });
        return to.fullPath;
      } else
        return true;
    });
    router.afterEach((to, from) => {
      if (from.path === "/settings/whitelabel") {
        router.removeRoute("whitelabel");
      }
    });
    return router;
  }
  window.addEventListener("zionbuilder/admin/init", function({ detail: Api }) {
    hooks.addFilter("zionbuilder/router", getRouter);
    new Admin(Api);
  });
})(zb.vue, wp.i18n, zb.store, zb.hooks);

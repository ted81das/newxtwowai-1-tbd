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
var __objRest = (source, exclude) => {
  var target = {};
  for (var prop in source)
    if (__hasOwnProp.call(source, prop) && exclude.indexOf(prop) < 0)
      target[prop] = source[prop];
  if (source != null && __getOwnPropSymbols)
    for (var prop of __getOwnPropSymbols(source)) {
      if (exclude.indexOf(prop) < 0 && __propIsEnum.call(source, prop))
        target[prop] = source[prop];
    }
  return target;
};
var __publicField = (obj, key, value) => {
  __defNormalProp(obj, typeof key !== "symbol" ? key + "" : key, value);
  return value;
};
(function(vue, i18n, hooks, store, composables) {
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
  const useElementCustomCSS = () => {
    window.zb.hooks.addFilter("zionbuilder/element/custom_css", addElementCustomCSS);
    function addElementCustomCSS(customCSS, optionsInstance, element) {
      const elementCustomCSS = optionsInstance.getValue("_advanced_options._custom_css");
      if (elementCustomCSS) {
        customCSS += elementCustomCSS.replaceAll("[ELEMENT]", `#${element.elementCssId}`);
      }
      return customCSS;
    }
  };
  var freeGlobal = typeof global == "object" && global && global.Object === Object && global;
  const freeGlobal$1 = freeGlobal;
  var freeSelf = typeof self == "object" && self && self.Object === Object && self;
  var root = freeGlobal$1 || freeSelf || Function("return this")();
  const root$1 = root;
  var Symbol$1 = root$1.Symbol;
  const Symbol$2 = Symbol$1;
  var objectProto$h = Object.prototype;
  var hasOwnProperty$f = objectProto$h.hasOwnProperty;
  var nativeObjectToString$1 = objectProto$h.toString;
  var symToStringTag$1 = Symbol$2 ? Symbol$2.toStringTag : void 0;
  function getRawTag(value) {
    var isOwn = hasOwnProperty$f.call(value, symToStringTag$1), tag = value[symToStringTag$1];
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
  var objectProto$g = Object.prototype;
  var nativeObjectToString = objectProto$g.toString;
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
  var symbolTag$3 = "[object Symbol]";
  function isSymbol(value) {
    return typeof value == "symbol" || isObjectLike(value) && baseGetTag(value) == symbolTag$3;
  }
  function arrayMap(array, iteratee) {
    var index = -1, length = array == null ? 0 : array.length, result = Array(length);
    while (++index < length) {
      result[index] = iteratee(array[index], index, array);
    }
    return result;
  }
  var isArray$1 = Array.isArray;
  const isArray$2 = isArray$1;
  var INFINITY$2 = 1 / 0;
  var symbolProto$2 = Symbol$2 ? Symbol$2.prototype : void 0, symbolToString = symbolProto$2 ? symbolProto$2.toString : void 0;
  function baseToString(value) {
    if (typeof value == "string") {
      return value;
    }
    if (isArray$2(value)) {
      return arrayMap(value, baseToString) + "";
    }
    if (isSymbol(value)) {
      return symbolToString ? symbolToString.call(value) : "";
    }
    var result = value + "";
    return result == "0" && 1 / value == -INFINITY$2 ? "-0" : result;
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
  function isObject$1(value) {
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
    if (isObject$1(value)) {
      var other = typeof value.valueOf == "function" ? value.valueOf() : value;
      value = isObject$1(other) ? other + "" : other;
    }
    if (typeof value != "string") {
      return value === 0 ? value : +value;
    }
    value = baseTrim(value);
    var isBinary = reIsBinary.test(value);
    return isBinary || reIsOctal.test(value) ? freeParseInt(value.slice(2), isBinary ? 2 : 8) : reIsBadHex.test(value) ? NAN : +value;
  }
  function identity(value) {
    return value;
  }
  var asyncTag = "[object AsyncFunction]", funcTag$2 = "[object Function]", genTag$1 = "[object GeneratorFunction]", proxyTag = "[object Proxy]";
  function isFunction$1(value) {
    if (!isObject$1(value)) {
      return false;
    }
    var tag = baseGetTag(value);
    return tag == funcTag$2 || tag == genTag$1 || tag == asyncTag || tag == proxyTag;
  }
  var coreJsData = root$1["__core-js_shared__"];
  const coreJsData$1 = coreJsData;
  var maskSrcKey = function() {
    var uid = /[^.]+$/.exec(coreJsData$1 && coreJsData$1.keys && coreJsData$1.keys.IE_PROTO || "");
    return uid ? "Symbol(src)_1." + uid : "";
  }();
  function isMasked(func) {
    return !!maskSrcKey && maskSrcKey in func;
  }
  var funcProto$2 = Function.prototype;
  var funcToString$2 = funcProto$2.toString;
  function toSource(func) {
    if (func != null) {
      try {
        return funcToString$2.call(func);
      } catch (e) {
      }
      try {
        return func + "";
      } catch (e) {
      }
    }
    return "";
  }
  var reRegExpChar = /[\\^$.*+?()[\]{}|]/g;
  var reIsHostCtor = /^\[object .+?Constructor\]$/;
  var funcProto$1 = Function.prototype, objectProto$f = Object.prototype;
  var funcToString$1 = funcProto$1.toString;
  var hasOwnProperty$e = objectProto$f.hasOwnProperty;
  var reIsNative = RegExp(
    "^" + funcToString$1.call(hasOwnProperty$e).replace(reRegExpChar, "\\$&").replace(/hasOwnProperty|(function).*?(?=\\\()| for .+?(?=\\\])/g, "$1.*?") + "$"
  );
  function baseIsNative(value) {
    if (!isObject$1(value) || isMasked(value)) {
      return false;
    }
    var pattern = isFunction$1(value) ? reIsNative : reIsHostCtor;
    return pattern.test(toSource(value));
  }
  function getValue(object, key) {
    return object == null ? void 0 : object[key];
  }
  function getNative(object, key) {
    var value = getValue(object, key);
    return baseIsNative(value) ? value : void 0;
  }
  var WeakMap = getNative(root$1, "WeakMap");
  const WeakMap$1 = WeakMap;
  var objectCreate = Object.create;
  var baseCreate = function() {
    function object() {
    }
    return function(proto) {
      if (!isObject$1(proto)) {
        return {};
      }
      if (objectCreate) {
        return objectCreate(proto);
      }
      object.prototype = proto;
      var result = new object();
      object.prototype = void 0;
      return result;
    };
  }();
  const baseCreate$1 = baseCreate;
  function apply(func, thisArg, args) {
    switch (args.length) {
      case 0:
        return func.call(thisArg);
      case 1:
        return func.call(thisArg, args[0]);
      case 2:
        return func.call(thisArg, args[0], args[1]);
      case 3:
        return func.call(thisArg, args[0], args[1], args[2]);
    }
    return func.apply(thisArg, args);
  }
  function noop$1() {
  }
  function copyArray(source, array) {
    var index = -1, length = source.length;
    array || (array = Array(length));
    while (++index < length) {
      array[index] = source[index];
    }
    return array;
  }
  var HOT_COUNT = 800, HOT_SPAN = 16;
  var nativeNow = Date.now;
  function shortOut(func) {
    var count = 0, lastCalled = 0;
    return function() {
      var stamp = nativeNow(), remaining = HOT_SPAN - (stamp - lastCalled);
      lastCalled = stamp;
      if (remaining > 0) {
        if (++count >= HOT_COUNT) {
          return arguments[0];
        }
      } else {
        count = 0;
      }
      return func.apply(void 0, arguments);
    };
  }
  function constant(value) {
    return function() {
      return value;
    };
  }
  var defineProperty = function() {
    try {
      var func = getNative(Object, "defineProperty");
      func({}, "", {});
      return func;
    } catch (e) {
    }
  }();
  const defineProperty$1 = defineProperty;
  var baseSetToString = !defineProperty$1 ? identity : function(func, string) {
    return defineProperty$1(func, "toString", {
      "configurable": true,
      "enumerable": false,
      "value": constant(string),
      "writable": true
    });
  };
  const baseSetToString$1 = baseSetToString;
  var setToString = shortOut(baseSetToString$1);
  const setToString$1 = setToString;
  function arrayEach(array, iteratee) {
    var index = -1, length = array == null ? 0 : array.length;
    while (++index < length) {
      if (iteratee(array[index], index, array) === false) {
        break;
      }
    }
    return array;
  }
  function baseFindIndex(array, predicate, fromIndex, fromRight) {
    var length = array.length, index = fromIndex + (fromRight ? 1 : -1);
    while (fromRight ? index-- : ++index < length) {
      if (predicate(array[index], index, array)) {
        return index;
      }
    }
    return -1;
  }
  function baseIsNaN(value) {
    return value !== value;
  }
  function strictIndexOf(array, value, fromIndex) {
    var index = fromIndex - 1, length = array.length;
    while (++index < length) {
      if (array[index] === value) {
        return index;
      }
    }
    return -1;
  }
  function baseIndexOf(array, value, fromIndex) {
    return value === value ? strictIndexOf(array, value, fromIndex) : baseFindIndex(array, baseIsNaN, fromIndex);
  }
  function arrayIncludes(array, value) {
    var length = array == null ? 0 : array.length;
    return !!length && baseIndexOf(array, value, 0) > -1;
  }
  var MAX_SAFE_INTEGER$1 = 9007199254740991;
  var reIsUint = /^(?:0|[1-9]\d*)$/;
  function isIndex(value, length) {
    var type = typeof value;
    length = length == null ? MAX_SAFE_INTEGER$1 : length;
    return !!length && (type == "number" || type != "symbol" && reIsUint.test(value)) && (value > -1 && value % 1 == 0 && value < length);
  }
  function baseAssignValue(object, key, value) {
    if (key == "__proto__" && defineProperty$1) {
      defineProperty$1(object, key, {
        "configurable": true,
        "enumerable": true,
        "value": value,
        "writable": true
      });
    } else {
      object[key] = value;
    }
  }
  function eq(value, other) {
    return value === other || value !== value && other !== other;
  }
  var objectProto$e = Object.prototype;
  var hasOwnProperty$d = objectProto$e.hasOwnProperty;
  function assignValue(object, key, value) {
    var objValue = object[key];
    if (!(hasOwnProperty$d.call(object, key) && eq(objValue, value)) || value === void 0 && !(key in object)) {
      baseAssignValue(object, key, value);
    }
  }
  function copyObject(source, props, object, customizer) {
    var isNew = !object;
    object || (object = {});
    var index = -1, length = props.length;
    while (++index < length) {
      var key = props[index];
      var newValue = customizer ? customizer(object[key], source[key], key, object, source) : void 0;
      if (newValue === void 0) {
        newValue = source[key];
      }
      if (isNew) {
        baseAssignValue(object, key, newValue);
      } else {
        assignValue(object, key, newValue);
      }
    }
    return object;
  }
  var nativeMax$1 = Math.max;
  function overRest(func, start, transform) {
    start = nativeMax$1(start === void 0 ? func.length - 1 : start, 0);
    return function() {
      var args = arguments, index = -1, length = nativeMax$1(args.length - start, 0), array = Array(length);
      while (++index < length) {
        array[index] = args[start + index];
      }
      index = -1;
      var otherArgs = Array(start + 1);
      while (++index < start) {
        otherArgs[index] = args[index];
      }
      otherArgs[start] = transform(array);
      return apply(func, this, otherArgs);
    };
  }
  function baseRest(func, start) {
    return setToString$1(overRest(func, start, identity), func + "");
  }
  var MAX_SAFE_INTEGER = 9007199254740991;
  function isLength(value) {
    return typeof value == "number" && value > -1 && value % 1 == 0 && value <= MAX_SAFE_INTEGER;
  }
  function isArrayLike(value) {
    return value != null && isLength(value.length) && !isFunction$1(value);
  }
  function isIterateeCall(value, index, object) {
    if (!isObject$1(object)) {
      return false;
    }
    var type = typeof index;
    if (type == "number" ? isArrayLike(object) && isIndex(index, object.length) : type == "string" && index in object) {
      return eq(object[index], value);
    }
    return false;
  }
  function createAssigner(assigner) {
    return baseRest(function(object, sources) {
      var index = -1, length = sources.length, customizer = length > 1 ? sources[length - 1] : void 0, guard = length > 2 ? sources[2] : void 0;
      customizer = assigner.length > 3 && typeof customizer == "function" ? (length--, customizer) : void 0;
      if (guard && isIterateeCall(sources[0], sources[1], guard)) {
        customizer = length < 3 ? void 0 : customizer;
        length = 1;
      }
      object = Object(object);
      while (++index < length) {
        var source = sources[index];
        if (source) {
          assigner(object, source, index, customizer);
        }
      }
      return object;
    });
  }
  var objectProto$d = Object.prototype;
  function isPrototype(value) {
    var Ctor = value && value.constructor, proto = typeof Ctor == "function" && Ctor.prototype || objectProto$d;
    return value === proto;
  }
  function baseTimes(n, iteratee) {
    var index = -1, result = Array(n);
    while (++index < n) {
      result[index] = iteratee(index);
    }
    return result;
  }
  var argsTag$3 = "[object Arguments]";
  function baseIsArguments(value) {
    return isObjectLike(value) && baseGetTag(value) == argsTag$3;
  }
  var objectProto$c = Object.prototype;
  var hasOwnProperty$c = objectProto$c.hasOwnProperty;
  var propertyIsEnumerable$1 = objectProto$c.propertyIsEnumerable;
  var isArguments = baseIsArguments(function() {
    return arguments;
  }()) ? baseIsArguments : function(value) {
    return isObjectLike(value) && hasOwnProperty$c.call(value, "callee") && !propertyIsEnumerable$1.call(value, "callee");
  };
  const isArguments$1 = isArguments;
  function stubFalse() {
    return false;
  }
  var freeExports$2 = typeof exports == "object" && exports && !exports.nodeType && exports;
  var freeModule$2 = freeExports$2 && typeof module == "object" && module && !module.nodeType && module;
  var moduleExports$2 = freeModule$2 && freeModule$2.exports === freeExports$2;
  var Buffer$2 = moduleExports$2 ? root$1.Buffer : void 0;
  var nativeIsBuffer = Buffer$2 ? Buffer$2.isBuffer : void 0;
  var isBuffer$1 = nativeIsBuffer || stubFalse;
  const isBuffer$2 = isBuffer$1;
  var argsTag$2 = "[object Arguments]", arrayTag$2 = "[object Array]", boolTag$3 = "[object Boolean]", dateTag$3 = "[object Date]", errorTag$3 = "[object Error]", funcTag$1 = "[object Function]", mapTag$5 = "[object Map]", numberTag$3 = "[object Number]", objectTag$4 = "[object Object]", regexpTag$3 = "[object RegExp]", setTag$5 = "[object Set]", stringTag$3 = "[object String]", weakMapTag$2 = "[object WeakMap]";
  var arrayBufferTag$3 = "[object ArrayBuffer]", dataViewTag$4 = "[object DataView]", float32Tag$2 = "[object Float32Array]", float64Tag$2 = "[object Float64Array]", int8Tag$2 = "[object Int8Array]", int16Tag$2 = "[object Int16Array]", int32Tag$2 = "[object Int32Array]", uint8Tag$2 = "[object Uint8Array]", uint8ClampedTag$2 = "[object Uint8ClampedArray]", uint16Tag$2 = "[object Uint16Array]", uint32Tag$2 = "[object Uint32Array]";
  var typedArrayTags = {};
  typedArrayTags[float32Tag$2] = typedArrayTags[float64Tag$2] = typedArrayTags[int8Tag$2] = typedArrayTags[int16Tag$2] = typedArrayTags[int32Tag$2] = typedArrayTags[uint8Tag$2] = typedArrayTags[uint8ClampedTag$2] = typedArrayTags[uint16Tag$2] = typedArrayTags[uint32Tag$2] = true;
  typedArrayTags[argsTag$2] = typedArrayTags[arrayTag$2] = typedArrayTags[arrayBufferTag$3] = typedArrayTags[boolTag$3] = typedArrayTags[dataViewTag$4] = typedArrayTags[dateTag$3] = typedArrayTags[errorTag$3] = typedArrayTags[funcTag$1] = typedArrayTags[mapTag$5] = typedArrayTags[numberTag$3] = typedArrayTags[objectTag$4] = typedArrayTags[regexpTag$3] = typedArrayTags[setTag$5] = typedArrayTags[stringTag$3] = typedArrayTags[weakMapTag$2] = false;
  function baseIsTypedArray(value) {
    return isObjectLike(value) && isLength(value.length) && !!typedArrayTags[baseGetTag(value)];
  }
  function baseUnary(func) {
    return function(value) {
      return func(value);
    };
  }
  var freeExports$1 = typeof exports == "object" && exports && !exports.nodeType && exports;
  var freeModule$1 = freeExports$1 && typeof module == "object" && module && !module.nodeType && module;
  var moduleExports$1 = freeModule$1 && freeModule$1.exports === freeExports$1;
  var freeProcess = moduleExports$1 && freeGlobal$1.process;
  var nodeUtil = function() {
    try {
      var types = freeModule$1 && freeModule$1.require && freeModule$1.require("util").types;
      if (types) {
        return types;
      }
      return freeProcess && freeProcess.binding && freeProcess.binding("util");
    } catch (e) {
    }
  }();
  const nodeUtil$1 = nodeUtil;
  var nodeIsTypedArray = nodeUtil$1 && nodeUtil$1.isTypedArray;
  var isTypedArray$1 = nodeIsTypedArray ? baseUnary(nodeIsTypedArray) : baseIsTypedArray;
  const isTypedArray$2 = isTypedArray$1;
  var objectProto$b = Object.prototype;
  var hasOwnProperty$b = objectProto$b.hasOwnProperty;
  function arrayLikeKeys(value, inherited) {
    var isArr = isArray$2(value), isArg = !isArr && isArguments$1(value), isBuff = !isArr && !isArg && isBuffer$2(value), isType = !isArr && !isArg && !isBuff && isTypedArray$2(value), skipIndexes = isArr || isArg || isBuff || isType, result = skipIndexes ? baseTimes(value.length, String) : [], length = result.length;
    for (var key in value) {
      if ((inherited || hasOwnProperty$b.call(value, key)) && !(skipIndexes && // Safari 9 has enumerable `arguments.length` in strict mode.
      (key == "length" || // Node.js 0.10 has enumerable non-index properties on buffers.
      isBuff && (key == "offset" || key == "parent") || // PhantomJS 2 has enumerable non-index properties on typed arrays.
      isType && (key == "buffer" || key == "byteLength" || key == "byteOffset") || // Skip index properties.
      isIndex(key, length)))) {
        result.push(key);
      }
    }
    return result;
  }
  function overArg(func, transform) {
    return function(arg) {
      return func(transform(arg));
    };
  }
  var nativeKeys = overArg(Object.keys, Object);
  const nativeKeys$1 = nativeKeys;
  var objectProto$a = Object.prototype;
  var hasOwnProperty$a = objectProto$a.hasOwnProperty;
  function baseKeys(object) {
    if (!isPrototype(object)) {
      return nativeKeys$1(object);
    }
    var result = [];
    for (var key in Object(object)) {
      if (hasOwnProperty$a.call(object, key) && key != "constructor") {
        result.push(key);
      }
    }
    return result;
  }
  function keys(object) {
    return isArrayLike(object) ? arrayLikeKeys(object) : baseKeys(object);
  }
  function nativeKeysIn(object) {
    var result = [];
    if (object != null) {
      for (var key in Object(object)) {
        result.push(key);
      }
    }
    return result;
  }
  var objectProto$9 = Object.prototype;
  var hasOwnProperty$9 = objectProto$9.hasOwnProperty;
  function baseKeysIn(object) {
    if (!isObject$1(object)) {
      return nativeKeysIn(object);
    }
    var isProto = isPrototype(object), result = [];
    for (var key in object) {
      if (!(key == "constructor" && (isProto || !hasOwnProperty$9.call(object, key)))) {
        result.push(key);
      }
    }
    return result;
  }
  function keysIn(object) {
    return isArrayLike(object) ? arrayLikeKeys(object, true) : baseKeysIn(object);
  }
  var assignInWith = createAssigner(function(object, source, srcIndex, customizer) {
    copyObject(source, keysIn(source), object, customizer);
  });
  const extendWith = assignInWith;
  var reIsDeepProp = /\.|\[(?:[^[\]]*|(["'])(?:(?!\1)[^\\]|\\.)*?\1)\]/, reIsPlainProp = /^\w*$/;
  function isKey(value, object) {
    if (isArray$2(value)) {
      return false;
    }
    var type = typeof value;
    if (type == "number" || type == "symbol" || type == "boolean" || value == null || isSymbol(value)) {
      return true;
    }
    return reIsPlainProp.test(value) || !reIsDeepProp.test(value) || object != null && value in Object(object);
  }
  var nativeCreate = getNative(Object, "create");
  const nativeCreate$1 = nativeCreate;
  function hashClear() {
    this.__data__ = nativeCreate$1 ? nativeCreate$1(null) : {};
    this.size = 0;
  }
  function hashDelete(key) {
    var result = this.has(key) && delete this.__data__[key];
    this.size -= result ? 1 : 0;
    return result;
  }
  var HASH_UNDEFINED$2 = "__lodash_hash_undefined__";
  var objectProto$8 = Object.prototype;
  var hasOwnProperty$8 = objectProto$8.hasOwnProperty;
  function hashGet(key) {
    var data = this.__data__;
    if (nativeCreate$1) {
      var result = data[key];
      return result === HASH_UNDEFINED$2 ? void 0 : result;
    }
    return hasOwnProperty$8.call(data, key) ? data[key] : void 0;
  }
  var objectProto$7 = Object.prototype;
  var hasOwnProperty$7 = objectProto$7.hasOwnProperty;
  function hashHas(key) {
    var data = this.__data__;
    return nativeCreate$1 ? data[key] !== void 0 : hasOwnProperty$7.call(data, key);
  }
  var HASH_UNDEFINED$1 = "__lodash_hash_undefined__";
  function hashSet(key, value) {
    var data = this.__data__;
    this.size += this.has(key) ? 0 : 1;
    data[key] = nativeCreate$1 && value === void 0 ? HASH_UNDEFINED$1 : value;
    return this;
  }
  function Hash(entries) {
    var index = -1, length = entries == null ? 0 : entries.length;
    this.clear();
    while (++index < length) {
      var entry = entries[index];
      this.set(entry[0], entry[1]);
    }
  }
  Hash.prototype.clear = hashClear;
  Hash.prototype["delete"] = hashDelete;
  Hash.prototype.get = hashGet;
  Hash.prototype.has = hashHas;
  Hash.prototype.set = hashSet;
  function listCacheClear() {
    this.__data__ = [];
    this.size = 0;
  }
  function assocIndexOf(array, key) {
    var length = array.length;
    while (length--) {
      if (eq(array[length][0], key)) {
        return length;
      }
    }
    return -1;
  }
  var arrayProto = Array.prototype;
  var splice = arrayProto.splice;
  function listCacheDelete(key) {
    var data = this.__data__, index = assocIndexOf(data, key);
    if (index < 0) {
      return false;
    }
    var lastIndex = data.length - 1;
    if (index == lastIndex) {
      data.pop();
    } else {
      splice.call(data, index, 1);
    }
    --this.size;
    return true;
  }
  function listCacheGet(key) {
    var data = this.__data__, index = assocIndexOf(data, key);
    return index < 0 ? void 0 : data[index][1];
  }
  function listCacheHas(key) {
    return assocIndexOf(this.__data__, key) > -1;
  }
  function listCacheSet(key, value) {
    var data = this.__data__, index = assocIndexOf(data, key);
    if (index < 0) {
      ++this.size;
      data.push([key, value]);
    } else {
      data[index][1] = value;
    }
    return this;
  }
  function ListCache(entries) {
    var index = -1, length = entries == null ? 0 : entries.length;
    this.clear();
    while (++index < length) {
      var entry = entries[index];
      this.set(entry[0], entry[1]);
    }
  }
  ListCache.prototype.clear = listCacheClear;
  ListCache.prototype["delete"] = listCacheDelete;
  ListCache.prototype.get = listCacheGet;
  ListCache.prototype.has = listCacheHas;
  ListCache.prototype.set = listCacheSet;
  var Map = getNative(root$1, "Map");
  const Map$1 = Map;
  function mapCacheClear() {
    this.size = 0;
    this.__data__ = {
      "hash": new Hash(),
      "map": new (Map$1 || ListCache)(),
      "string": new Hash()
    };
  }
  function isKeyable(value) {
    var type = typeof value;
    return type == "string" || type == "number" || type == "symbol" || type == "boolean" ? value !== "__proto__" : value === null;
  }
  function getMapData(map, key) {
    var data = map.__data__;
    return isKeyable(key) ? data[typeof key == "string" ? "string" : "hash"] : data.map;
  }
  function mapCacheDelete(key) {
    var result = getMapData(this, key)["delete"](key);
    this.size -= result ? 1 : 0;
    return result;
  }
  function mapCacheGet(key) {
    return getMapData(this, key).get(key);
  }
  function mapCacheHas(key) {
    return getMapData(this, key).has(key);
  }
  function mapCacheSet(key, value) {
    var data = getMapData(this, key), size = data.size;
    data.set(key, value);
    this.size += data.size == size ? 0 : 1;
    return this;
  }
  function MapCache(entries) {
    var index = -1, length = entries == null ? 0 : entries.length;
    this.clear();
    while (++index < length) {
      var entry = entries[index];
      this.set(entry[0], entry[1]);
    }
  }
  MapCache.prototype.clear = mapCacheClear;
  MapCache.prototype["delete"] = mapCacheDelete;
  MapCache.prototype.get = mapCacheGet;
  MapCache.prototype.has = mapCacheHas;
  MapCache.prototype.set = mapCacheSet;
  var FUNC_ERROR_TEXT$1 = "Expected a function";
  function memoize(func, resolver) {
    if (typeof func != "function" || resolver != null && typeof resolver != "function") {
      throw new TypeError(FUNC_ERROR_TEXT$1);
    }
    var memoized = function() {
      var args = arguments, key = resolver ? resolver.apply(this, args) : args[0], cache2 = memoized.cache;
      if (cache2.has(key)) {
        return cache2.get(key);
      }
      var result = func.apply(this, args);
      memoized.cache = cache2.set(key, result) || cache2;
      return result;
    };
    memoized.cache = new (memoize.Cache || MapCache)();
    return memoized;
  }
  memoize.Cache = MapCache;
  var MAX_MEMOIZE_SIZE = 500;
  function memoizeCapped(func) {
    var result = memoize(func, function(key) {
      if (cache2.size === MAX_MEMOIZE_SIZE) {
        cache2.clear();
      }
      return key;
    });
    var cache2 = result.cache;
    return result;
  }
  var rePropName = /[^.[\]]+|\[(?:(-?\d+(?:\.\d+)?)|(["'])((?:(?!\2)[^\\]|\\.)*?)\2)\]|(?=(?:\.|\[\])(?:\.|\[\]|$))/g;
  var reEscapeChar = /\\(\\)?/g;
  var stringToPath = memoizeCapped(function(string) {
    var result = [];
    if (string.charCodeAt(0) === 46) {
      result.push("");
    }
    string.replace(rePropName, function(match, number, quote, subString) {
      result.push(quote ? subString.replace(reEscapeChar, "$1") : number || match);
    });
    return result;
  });
  const stringToPath$1 = stringToPath;
  function toString$1(value) {
    return value == null ? "" : baseToString(value);
  }
  function castPath(value, object) {
    if (isArray$2(value)) {
      return value;
    }
    return isKey(value, object) ? [value] : stringToPath$1(toString$1(value));
  }
  var INFINITY$1 = 1 / 0;
  function toKey(value) {
    if (typeof value == "string" || isSymbol(value)) {
      return value;
    }
    var result = value + "";
    return result == "0" && 1 / value == -INFINITY$1 ? "-0" : result;
  }
  function baseGet(object, path) {
    path = castPath(path, object);
    var index = 0, length = path.length;
    while (object != null && index < length) {
      object = object[toKey(path[index++])];
    }
    return index && index == length ? object : void 0;
  }
  function get(object, path, defaultValue) {
    var result = object == null ? void 0 : baseGet(object, path);
    return result === void 0 ? defaultValue : result;
  }
  function arrayPush(array, values) {
    var index = -1, length = values.length, offset = array.length;
    while (++index < length) {
      array[offset + index] = values[index];
    }
    return array;
  }
  var spreadableSymbol = Symbol$2 ? Symbol$2.isConcatSpreadable : void 0;
  function isFlattenable(value) {
    return isArray$2(value) || isArguments$1(value) || !!(spreadableSymbol && value && value[spreadableSymbol]);
  }
  function baseFlatten(array, depth, predicate, isStrict, result) {
    var index = -1, length = array.length;
    predicate || (predicate = isFlattenable);
    result || (result = []);
    while (++index < length) {
      var value = array[index];
      if (depth > 0 && predicate(value)) {
        if (depth > 1) {
          baseFlatten(value, depth - 1, predicate, isStrict, result);
        } else {
          arrayPush(result, value);
        }
      } else if (!isStrict) {
        result[result.length] = value;
      }
    }
    return result;
  }
  var getPrototype = overArg(Object.getPrototypeOf, Object);
  const getPrototype$1 = getPrototype;
  var objectTag$3 = "[object Object]";
  var funcProto = Function.prototype, objectProto$6 = Object.prototype;
  var funcToString = funcProto.toString;
  var hasOwnProperty$6 = objectProto$6.hasOwnProperty;
  var objectCtorString = funcToString.call(Object);
  function isPlainObject$1(value) {
    if (!isObjectLike(value) || baseGetTag(value) != objectTag$3) {
      return false;
    }
    var proto = getPrototype$1(value);
    if (proto === null) {
      return true;
    }
    var Ctor = hasOwnProperty$6.call(proto, "constructor") && proto.constructor;
    return typeof Ctor == "function" && Ctor instanceof Ctor && funcToString.call(Ctor) == objectCtorString;
  }
  var domExcTag = "[object DOMException]", errorTag$2 = "[object Error]";
  function isError(value) {
    if (!isObjectLike(value)) {
      return false;
    }
    var tag = baseGetTag(value);
    return tag == errorTag$2 || tag == domExcTag || typeof value.message == "string" && typeof value.name == "string" && !isPlainObject$1(value);
  }
  var attempt = baseRest(function(func, args) {
    try {
      return apply(func, void 0, args);
    } catch (e) {
      return isError(e) ? e : new Error(e);
    }
  });
  const attempt$1 = attempt;
  function basePropertyOf(object) {
    return function(key) {
      return object == null ? void 0 : object[key];
    };
  }
  function stackClear() {
    this.__data__ = new ListCache();
    this.size = 0;
  }
  function stackDelete(key) {
    var data = this.__data__, result = data["delete"](key);
    this.size = data.size;
    return result;
  }
  function stackGet(key) {
    return this.__data__.get(key);
  }
  function stackHas(key) {
    return this.__data__.has(key);
  }
  var LARGE_ARRAY_SIZE$1 = 200;
  function stackSet(key, value) {
    var data = this.__data__;
    if (data instanceof ListCache) {
      var pairs = data.__data__;
      if (!Map$1 || pairs.length < LARGE_ARRAY_SIZE$1 - 1) {
        pairs.push([key, value]);
        this.size = ++data.size;
        return this;
      }
      data = this.__data__ = new MapCache(pairs);
    }
    data.set(key, value);
    this.size = data.size;
    return this;
  }
  function Stack(entries) {
    var data = this.__data__ = new ListCache(entries);
    this.size = data.size;
  }
  Stack.prototype.clear = stackClear;
  Stack.prototype["delete"] = stackDelete;
  Stack.prototype.get = stackGet;
  Stack.prototype.has = stackHas;
  Stack.prototype.set = stackSet;
  function baseAssign(object, source) {
    return object && copyObject(source, keys(source), object);
  }
  function baseAssignIn(object, source) {
    return object && copyObject(source, keysIn(source), object);
  }
  var freeExports = typeof exports == "object" && exports && !exports.nodeType && exports;
  var freeModule = freeExports && typeof module == "object" && module && !module.nodeType && module;
  var moduleExports = freeModule && freeModule.exports === freeExports;
  var Buffer$1 = moduleExports ? root$1.Buffer : void 0, allocUnsafe = Buffer$1 ? Buffer$1.allocUnsafe : void 0;
  function cloneBuffer(buffer, isDeep) {
    if (isDeep) {
      return buffer.slice();
    }
    var length = buffer.length, result = allocUnsafe ? allocUnsafe(length) : new buffer.constructor(length);
    buffer.copy(result);
    return result;
  }
  function arrayFilter(array, predicate) {
    var index = -1, length = array == null ? 0 : array.length, resIndex = 0, result = [];
    while (++index < length) {
      var value = array[index];
      if (predicate(value, index, array)) {
        result[resIndex++] = value;
      }
    }
    return result;
  }
  function stubArray() {
    return [];
  }
  var objectProto$5 = Object.prototype;
  var propertyIsEnumerable = objectProto$5.propertyIsEnumerable;
  var nativeGetSymbols$1 = Object.getOwnPropertySymbols;
  var getSymbols = !nativeGetSymbols$1 ? stubArray : function(object) {
    if (object == null) {
      return [];
    }
    object = Object(object);
    return arrayFilter(nativeGetSymbols$1(object), function(symbol) {
      return propertyIsEnumerable.call(object, symbol);
    });
  };
  const getSymbols$1 = getSymbols;
  function copySymbols(source, object) {
    return copyObject(source, getSymbols$1(source), object);
  }
  var nativeGetSymbols = Object.getOwnPropertySymbols;
  var getSymbolsIn = !nativeGetSymbols ? stubArray : function(object) {
    var result = [];
    while (object) {
      arrayPush(result, getSymbols$1(object));
      object = getPrototype$1(object);
    }
    return result;
  };
  const getSymbolsIn$1 = getSymbolsIn;
  function copySymbolsIn(source, object) {
    return copyObject(source, getSymbolsIn$1(source), object);
  }
  function baseGetAllKeys(object, keysFunc, symbolsFunc) {
    var result = keysFunc(object);
    return isArray$2(object) ? result : arrayPush(result, symbolsFunc(object));
  }
  function getAllKeys(object) {
    return baseGetAllKeys(object, keys, getSymbols$1);
  }
  function getAllKeysIn(object) {
    return baseGetAllKeys(object, keysIn, getSymbolsIn$1);
  }
  var DataView = getNative(root$1, "DataView");
  const DataView$1 = DataView;
  var Promise$1 = getNative(root$1, "Promise");
  const Promise$2 = Promise$1;
  var Set = getNative(root$1, "Set");
  const Set$1 = Set;
  var mapTag$4 = "[object Map]", objectTag$2 = "[object Object]", promiseTag = "[object Promise]", setTag$4 = "[object Set]", weakMapTag$1 = "[object WeakMap]";
  var dataViewTag$3 = "[object DataView]";
  var dataViewCtorString = toSource(DataView$1), mapCtorString = toSource(Map$1), promiseCtorString = toSource(Promise$2), setCtorString = toSource(Set$1), weakMapCtorString = toSource(WeakMap$1);
  var getTag = baseGetTag;
  if (DataView$1 && getTag(new DataView$1(new ArrayBuffer(1))) != dataViewTag$3 || Map$1 && getTag(new Map$1()) != mapTag$4 || Promise$2 && getTag(Promise$2.resolve()) != promiseTag || Set$1 && getTag(new Set$1()) != setTag$4 || WeakMap$1 && getTag(new WeakMap$1()) != weakMapTag$1) {
    getTag = function(value) {
      var result = baseGetTag(value), Ctor = result == objectTag$2 ? value.constructor : void 0, ctorString = Ctor ? toSource(Ctor) : "";
      if (ctorString) {
        switch (ctorString) {
          case dataViewCtorString:
            return dataViewTag$3;
          case mapCtorString:
            return mapTag$4;
          case promiseCtorString:
            return promiseTag;
          case setCtorString:
            return setTag$4;
          case weakMapCtorString:
            return weakMapTag$1;
        }
      }
      return result;
    };
  }
  const getTag$1 = getTag;
  var objectProto$4 = Object.prototype;
  var hasOwnProperty$5 = objectProto$4.hasOwnProperty;
  function initCloneArray(array) {
    var length = array.length, result = new array.constructor(length);
    if (length && typeof array[0] == "string" && hasOwnProperty$5.call(array, "index")) {
      result.index = array.index;
      result.input = array.input;
    }
    return result;
  }
  var Uint8Array$1 = root$1.Uint8Array;
  const Uint8Array$2 = Uint8Array$1;
  function cloneArrayBuffer(arrayBuffer) {
    var result = new arrayBuffer.constructor(arrayBuffer.byteLength);
    new Uint8Array$2(result).set(new Uint8Array$2(arrayBuffer));
    return result;
  }
  function cloneDataView(dataView, isDeep) {
    var buffer = isDeep ? cloneArrayBuffer(dataView.buffer) : dataView.buffer;
    return new dataView.constructor(buffer, dataView.byteOffset, dataView.byteLength);
  }
  var reFlags = /\w*$/;
  function cloneRegExp(regexp) {
    var result = new regexp.constructor(regexp.source, reFlags.exec(regexp));
    result.lastIndex = regexp.lastIndex;
    return result;
  }
  var symbolProto$1 = Symbol$2 ? Symbol$2.prototype : void 0, symbolValueOf$1 = symbolProto$1 ? symbolProto$1.valueOf : void 0;
  function cloneSymbol(symbol) {
    return symbolValueOf$1 ? Object(symbolValueOf$1.call(symbol)) : {};
  }
  function cloneTypedArray(typedArray, isDeep) {
    var buffer = isDeep ? cloneArrayBuffer(typedArray.buffer) : typedArray.buffer;
    return new typedArray.constructor(buffer, typedArray.byteOffset, typedArray.length);
  }
  var boolTag$2 = "[object Boolean]", dateTag$2 = "[object Date]", mapTag$3 = "[object Map]", numberTag$2 = "[object Number]", regexpTag$2 = "[object RegExp]", setTag$3 = "[object Set]", stringTag$2 = "[object String]", symbolTag$2 = "[object Symbol]";
  var arrayBufferTag$2 = "[object ArrayBuffer]", dataViewTag$2 = "[object DataView]", float32Tag$1 = "[object Float32Array]", float64Tag$1 = "[object Float64Array]", int8Tag$1 = "[object Int8Array]", int16Tag$1 = "[object Int16Array]", int32Tag$1 = "[object Int32Array]", uint8Tag$1 = "[object Uint8Array]", uint8ClampedTag$1 = "[object Uint8ClampedArray]", uint16Tag$1 = "[object Uint16Array]", uint32Tag$1 = "[object Uint32Array]";
  function initCloneByTag(object, tag, isDeep) {
    var Ctor = object.constructor;
    switch (tag) {
      case arrayBufferTag$2:
        return cloneArrayBuffer(object);
      case boolTag$2:
      case dateTag$2:
        return new Ctor(+object);
      case dataViewTag$2:
        return cloneDataView(object, isDeep);
      case float32Tag$1:
      case float64Tag$1:
      case int8Tag$1:
      case int16Tag$1:
      case int32Tag$1:
      case uint8Tag$1:
      case uint8ClampedTag$1:
      case uint16Tag$1:
      case uint32Tag$1:
        return cloneTypedArray(object, isDeep);
      case mapTag$3:
        return new Ctor();
      case numberTag$2:
      case stringTag$2:
        return new Ctor(object);
      case regexpTag$2:
        return cloneRegExp(object);
      case setTag$3:
        return new Ctor();
      case symbolTag$2:
        return cloneSymbol(object);
    }
  }
  function initCloneObject(object) {
    return typeof object.constructor == "function" && !isPrototype(object) ? baseCreate$1(getPrototype$1(object)) : {};
  }
  var mapTag$2 = "[object Map]";
  function baseIsMap(value) {
    return isObjectLike(value) && getTag$1(value) == mapTag$2;
  }
  var nodeIsMap = nodeUtil$1 && nodeUtil$1.isMap;
  var isMap = nodeIsMap ? baseUnary(nodeIsMap) : baseIsMap;
  const isMap$1 = isMap;
  var setTag$2 = "[object Set]";
  function baseIsSet(value) {
    return isObjectLike(value) && getTag$1(value) == setTag$2;
  }
  var nodeIsSet = nodeUtil$1 && nodeUtil$1.isSet;
  var isSet = nodeIsSet ? baseUnary(nodeIsSet) : baseIsSet;
  const isSet$1 = isSet;
  var CLONE_DEEP_FLAG$1 = 1, CLONE_FLAT_FLAG = 2, CLONE_SYMBOLS_FLAG$1 = 4;
  var argsTag$1 = "[object Arguments]", arrayTag$1 = "[object Array]", boolTag$1 = "[object Boolean]", dateTag$1 = "[object Date]", errorTag$1 = "[object Error]", funcTag = "[object Function]", genTag = "[object GeneratorFunction]", mapTag$1 = "[object Map]", numberTag$1 = "[object Number]", objectTag$1 = "[object Object]", regexpTag$1 = "[object RegExp]", setTag$1 = "[object Set]", stringTag$1 = "[object String]", symbolTag$1 = "[object Symbol]", weakMapTag = "[object WeakMap]";
  var arrayBufferTag$1 = "[object ArrayBuffer]", dataViewTag$1 = "[object DataView]", float32Tag = "[object Float32Array]", float64Tag = "[object Float64Array]", int8Tag = "[object Int8Array]", int16Tag = "[object Int16Array]", int32Tag = "[object Int32Array]", uint8Tag = "[object Uint8Array]", uint8ClampedTag = "[object Uint8ClampedArray]", uint16Tag = "[object Uint16Array]", uint32Tag = "[object Uint32Array]";
  var cloneableTags = {};
  cloneableTags[argsTag$1] = cloneableTags[arrayTag$1] = cloneableTags[arrayBufferTag$1] = cloneableTags[dataViewTag$1] = cloneableTags[boolTag$1] = cloneableTags[dateTag$1] = cloneableTags[float32Tag] = cloneableTags[float64Tag] = cloneableTags[int8Tag] = cloneableTags[int16Tag] = cloneableTags[int32Tag] = cloneableTags[mapTag$1] = cloneableTags[numberTag$1] = cloneableTags[objectTag$1] = cloneableTags[regexpTag$1] = cloneableTags[setTag$1] = cloneableTags[stringTag$1] = cloneableTags[symbolTag$1] = cloneableTags[uint8Tag] = cloneableTags[uint8ClampedTag] = cloneableTags[uint16Tag] = cloneableTags[uint32Tag] = true;
  cloneableTags[errorTag$1] = cloneableTags[funcTag] = cloneableTags[weakMapTag] = false;
  function baseClone(value, bitmask, customizer, key, object, stack) {
    var result, isDeep = bitmask & CLONE_DEEP_FLAG$1, isFlat = bitmask & CLONE_FLAT_FLAG, isFull = bitmask & CLONE_SYMBOLS_FLAG$1;
    if (customizer) {
      result = object ? customizer(value, key, object, stack) : customizer(value);
    }
    if (result !== void 0) {
      return result;
    }
    if (!isObject$1(value)) {
      return value;
    }
    var isArr = isArray$2(value);
    if (isArr) {
      result = initCloneArray(value);
      if (!isDeep) {
        return copyArray(value, result);
      }
    } else {
      var tag = getTag$1(value), isFunc = tag == funcTag || tag == genTag;
      if (isBuffer$2(value)) {
        return cloneBuffer(value, isDeep);
      }
      if (tag == objectTag$1 || tag == argsTag$1 || isFunc && !object) {
        result = isFlat || isFunc ? {} : initCloneObject(value);
        if (!isDeep) {
          return isFlat ? copySymbolsIn(value, baseAssignIn(result, value)) : copySymbols(value, baseAssign(result, value));
        }
      } else {
        if (!cloneableTags[tag]) {
          return object ? value : {};
        }
        result = initCloneByTag(value, tag, isDeep);
      }
    }
    stack || (stack = new Stack());
    var stacked = stack.get(value);
    if (stacked) {
      return stacked;
    }
    stack.set(value, result);
    if (isSet$1(value)) {
      value.forEach(function(subValue) {
        result.add(baseClone(subValue, bitmask, customizer, subValue, value, stack));
      });
    } else if (isMap$1(value)) {
      value.forEach(function(subValue, key2) {
        result.set(key2, baseClone(subValue, bitmask, customizer, key2, value, stack));
      });
    }
    var keysFunc = isFull ? isFlat ? getAllKeysIn : getAllKeys : isFlat ? keysIn : keys;
    var props = isArr ? void 0 : keysFunc(value);
    arrayEach(props || value, function(subValue, key2) {
      if (props) {
        key2 = subValue;
        subValue = value[key2];
      }
      assignValue(result, key2, baseClone(subValue, bitmask, customizer, key2, value, stack));
    });
    return result;
  }
  var CLONE_DEEP_FLAG = 1, CLONE_SYMBOLS_FLAG = 4;
  function cloneDeep(value) {
    return baseClone(value, CLONE_DEEP_FLAG | CLONE_SYMBOLS_FLAG);
  }
  var HASH_UNDEFINED = "__lodash_hash_undefined__";
  function setCacheAdd(value) {
    this.__data__.set(value, HASH_UNDEFINED);
    return this;
  }
  function setCacheHas(value) {
    return this.__data__.has(value);
  }
  function SetCache(values) {
    var index = -1, length = values == null ? 0 : values.length;
    this.__data__ = new MapCache();
    while (++index < length) {
      this.add(values[index]);
    }
  }
  SetCache.prototype.add = SetCache.prototype.push = setCacheAdd;
  SetCache.prototype.has = setCacheHas;
  function arraySome(array, predicate) {
    var index = -1, length = array == null ? 0 : array.length;
    while (++index < length) {
      if (predicate(array[index], index, array)) {
        return true;
      }
    }
    return false;
  }
  function cacheHas(cache2, key) {
    return cache2.has(key);
  }
  var COMPARE_PARTIAL_FLAG$5 = 1, COMPARE_UNORDERED_FLAG$3 = 2;
  function equalArrays(array, other, bitmask, customizer, equalFunc, stack) {
    var isPartial = bitmask & COMPARE_PARTIAL_FLAG$5, arrLength = array.length, othLength = other.length;
    if (arrLength != othLength && !(isPartial && othLength > arrLength)) {
      return false;
    }
    var arrStacked = stack.get(array);
    var othStacked = stack.get(other);
    if (arrStacked && othStacked) {
      return arrStacked == other && othStacked == array;
    }
    var index = -1, result = true, seen = bitmask & COMPARE_UNORDERED_FLAG$3 ? new SetCache() : void 0;
    stack.set(array, other);
    stack.set(other, array);
    while (++index < arrLength) {
      var arrValue = array[index], othValue = other[index];
      if (customizer) {
        var compared = isPartial ? customizer(othValue, arrValue, index, other, array, stack) : customizer(arrValue, othValue, index, array, other, stack);
      }
      if (compared !== void 0) {
        if (compared) {
          continue;
        }
        result = false;
        break;
      }
      if (seen) {
        if (!arraySome(other, function(othValue2, othIndex) {
          if (!cacheHas(seen, othIndex) && (arrValue === othValue2 || equalFunc(arrValue, othValue2, bitmask, customizer, stack))) {
            return seen.push(othIndex);
          }
        })) {
          result = false;
          break;
        }
      } else if (!(arrValue === othValue || equalFunc(arrValue, othValue, bitmask, customizer, stack))) {
        result = false;
        break;
      }
    }
    stack["delete"](array);
    stack["delete"](other);
    return result;
  }
  function mapToArray(map) {
    var index = -1, result = Array(map.size);
    map.forEach(function(value, key) {
      result[++index] = [key, value];
    });
    return result;
  }
  function setToArray(set2) {
    var index = -1, result = Array(set2.size);
    set2.forEach(function(value) {
      result[++index] = value;
    });
    return result;
  }
  var COMPARE_PARTIAL_FLAG$4 = 1, COMPARE_UNORDERED_FLAG$2 = 2;
  var boolTag = "[object Boolean]", dateTag = "[object Date]", errorTag = "[object Error]", mapTag = "[object Map]", numberTag = "[object Number]", regexpTag = "[object RegExp]", setTag = "[object Set]", stringTag = "[object String]", symbolTag = "[object Symbol]";
  var arrayBufferTag = "[object ArrayBuffer]", dataViewTag = "[object DataView]";
  var symbolProto = Symbol$2 ? Symbol$2.prototype : void 0, symbolValueOf = symbolProto ? symbolProto.valueOf : void 0;
  function equalByTag(object, other, tag, bitmask, customizer, equalFunc, stack) {
    switch (tag) {
      case dataViewTag:
        if (object.byteLength != other.byteLength || object.byteOffset != other.byteOffset) {
          return false;
        }
        object = object.buffer;
        other = other.buffer;
      case arrayBufferTag:
        if (object.byteLength != other.byteLength || !equalFunc(new Uint8Array$2(object), new Uint8Array$2(other))) {
          return false;
        }
        return true;
      case boolTag:
      case dateTag:
      case numberTag:
        return eq(+object, +other);
      case errorTag:
        return object.name == other.name && object.message == other.message;
      case regexpTag:
      case stringTag:
        return object == other + "";
      case mapTag:
        var convert = mapToArray;
      case setTag:
        var isPartial = bitmask & COMPARE_PARTIAL_FLAG$4;
        convert || (convert = setToArray);
        if (object.size != other.size && !isPartial) {
          return false;
        }
        var stacked = stack.get(object);
        if (stacked) {
          return stacked == other;
        }
        bitmask |= COMPARE_UNORDERED_FLAG$2;
        stack.set(object, other);
        var result = equalArrays(convert(object), convert(other), bitmask, customizer, equalFunc, stack);
        stack["delete"](object);
        return result;
      case symbolTag:
        if (symbolValueOf) {
          return symbolValueOf.call(object) == symbolValueOf.call(other);
        }
    }
    return false;
  }
  var COMPARE_PARTIAL_FLAG$3 = 1;
  var objectProto$3 = Object.prototype;
  var hasOwnProperty$4 = objectProto$3.hasOwnProperty;
  function equalObjects(object, other, bitmask, customizer, equalFunc, stack) {
    var isPartial = bitmask & COMPARE_PARTIAL_FLAG$3, objProps = getAllKeys(object), objLength = objProps.length, othProps = getAllKeys(other), othLength = othProps.length;
    if (objLength != othLength && !isPartial) {
      return false;
    }
    var index = objLength;
    while (index--) {
      var key = objProps[index];
      if (!(isPartial ? key in other : hasOwnProperty$4.call(other, key))) {
        return false;
      }
    }
    var objStacked = stack.get(object);
    var othStacked = stack.get(other);
    if (objStacked && othStacked) {
      return objStacked == other && othStacked == object;
    }
    var result = true;
    stack.set(object, other);
    stack.set(other, object);
    var skipCtor = isPartial;
    while (++index < objLength) {
      key = objProps[index];
      var objValue = object[key], othValue = other[key];
      if (customizer) {
        var compared = isPartial ? customizer(othValue, objValue, key, other, object, stack) : customizer(objValue, othValue, key, object, other, stack);
      }
      if (!(compared === void 0 ? objValue === othValue || equalFunc(objValue, othValue, bitmask, customizer, stack) : compared)) {
        result = false;
        break;
      }
      skipCtor || (skipCtor = key == "constructor");
    }
    if (result && !skipCtor) {
      var objCtor = object.constructor, othCtor = other.constructor;
      if (objCtor != othCtor && ("constructor" in object && "constructor" in other) && !(typeof objCtor == "function" && objCtor instanceof objCtor && typeof othCtor == "function" && othCtor instanceof othCtor)) {
        result = false;
      }
    }
    stack["delete"](object);
    stack["delete"](other);
    return result;
  }
  var COMPARE_PARTIAL_FLAG$2 = 1;
  var argsTag = "[object Arguments]", arrayTag = "[object Array]", objectTag = "[object Object]";
  var objectProto$2 = Object.prototype;
  var hasOwnProperty$3 = objectProto$2.hasOwnProperty;
  function baseIsEqualDeep(object, other, bitmask, customizer, equalFunc, stack) {
    var objIsArr = isArray$2(object), othIsArr = isArray$2(other), objTag = objIsArr ? arrayTag : getTag$1(object), othTag = othIsArr ? arrayTag : getTag$1(other);
    objTag = objTag == argsTag ? objectTag : objTag;
    othTag = othTag == argsTag ? objectTag : othTag;
    var objIsObj = objTag == objectTag, othIsObj = othTag == objectTag, isSameTag = objTag == othTag;
    if (isSameTag && isBuffer$2(object)) {
      if (!isBuffer$2(other)) {
        return false;
      }
      objIsArr = true;
      objIsObj = false;
    }
    if (isSameTag && !objIsObj) {
      stack || (stack = new Stack());
      return objIsArr || isTypedArray$2(object) ? equalArrays(object, other, bitmask, customizer, equalFunc, stack) : equalByTag(object, other, objTag, bitmask, customizer, equalFunc, stack);
    }
    if (!(bitmask & COMPARE_PARTIAL_FLAG$2)) {
      var objIsWrapped = objIsObj && hasOwnProperty$3.call(object, "__wrapped__"), othIsWrapped = othIsObj && hasOwnProperty$3.call(other, "__wrapped__");
      if (objIsWrapped || othIsWrapped) {
        var objUnwrapped = objIsWrapped ? object.value() : object, othUnwrapped = othIsWrapped ? other.value() : other;
        stack || (stack = new Stack());
        return equalFunc(objUnwrapped, othUnwrapped, bitmask, customizer, stack);
      }
    }
    if (!isSameTag) {
      return false;
    }
    stack || (stack = new Stack());
    return equalObjects(object, other, bitmask, customizer, equalFunc, stack);
  }
  function baseIsEqual(value, other, bitmask, customizer, stack) {
    if (value === other) {
      return true;
    }
    if (value == null || other == null || !isObjectLike(value) && !isObjectLike(other)) {
      return value !== value && other !== other;
    }
    return baseIsEqualDeep(value, other, bitmask, customizer, baseIsEqual, stack);
  }
  var COMPARE_PARTIAL_FLAG$1 = 1, COMPARE_UNORDERED_FLAG$1 = 2;
  function baseIsMatch(object, source, matchData, customizer) {
    var index = matchData.length, length = index, noCustomizer = !customizer;
    if (object == null) {
      return !length;
    }
    object = Object(object);
    while (index--) {
      var data = matchData[index];
      if (noCustomizer && data[2] ? data[1] !== object[data[0]] : !(data[0] in object)) {
        return false;
      }
    }
    while (++index < length) {
      data = matchData[index];
      var key = data[0], objValue = object[key], srcValue = data[1];
      if (noCustomizer && data[2]) {
        if (objValue === void 0 && !(key in object)) {
          return false;
        }
      } else {
        var stack = new Stack();
        if (customizer) {
          var result = customizer(objValue, srcValue, key, object, source, stack);
        }
        if (!(result === void 0 ? baseIsEqual(srcValue, objValue, COMPARE_PARTIAL_FLAG$1 | COMPARE_UNORDERED_FLAG$1, customizer, stack) : result)) {
          return false;
        }
      }
    }
    return true;
  }
  function isStrictComparable(value) {
    return value === value && !isObject$1(value);
  }
  function getMatchData(object) {
    var result = keys(object), length = result.length;
    while (length--) {
      var key = result[length], value = object[key];
      result[length] = [key, value, isStrictComparable(value)];
    }
    return result;
  }
  function matchesStrictComparable(key, srcValue) {
    return function(object) {
      if (object == null) {
        return false;
      }
      return object[key] === srcValue && (srcValue !== void 0 || key in Object(object));
    };
  }
  function baseMatches(source) {
    var matchData = getMatchData(source);
    if (matchData.length == 1 && matchData[0][2]) {
      return matchesStrictComparable(matchData[0][0], matchData[0][1]);
    }
    return function(object) {
      return object === source || baseIsMatch(object, source, matchData);
    };
  }
  function baseHasIn(object, key) {
    return object != null && key in Object(object);
  }
  function hasPath(object, path, hasFunc) {
    path = castPath(path, object);
    var index = -1, length = path.length, result = false;
    while (++index < length) {
      var key = toKey(path[index]);
      if (!(result = object != null && hasFunc(object, key))) {
        break;
      }
      object = object[key];
    }
    if (result || ++index != length) {
      return result;
    }
    length = object == null ? 0 : object.length;
    return !!length && isLength(length) && isIndex(key, length) && (isArray$2(object) || isArguments$1(object));
  }
  function hasIn(object, path) {
    return object != null && hasPath(object, path, baseHasIn);
  }
  var COMPARE_PARTIAL_FLAG = 1, COMPARE_UNORDERED_FLAG = 2;
  function baseMatchesProperty(path, srcValue) {
    if (isKey(path) && isStrictComparable(srcValue)) {
      return matchesStrictComparable(toKey(path), srcValue);
    }
    return function(object) {
      var objValue = get(object, path);
      return objValue === void 0 && objValue === srcValue ? hasIn(object, path) : baseIsEqual(srcValue, objValue, COMPARE_PARTIAL_FLAG | COMPARE_UNORDERED_FLAG);
    };
  }
  function baseProperty(key) {
    return function(object) {
      return object == null ? void 0 : object[key];
    };
  }
  function basePropertyDeep(path) {
    return function(object) {
      return baseGet(object, path);
    };
  }
  function property(path) {
    return isKey(path) ? baseProperty(toKey(path)) : basePropertyDeep(path);
  }
  function baseIteratee(value) {
    if (typeof value == "function") {
      return value;
    }
    if (value == null) {
      return identity;
    }
    if (typeof value == "object") {
      return isArray$2(value) ? baseMatchesProperty(value[0], value[1]) : baseMatches(value);
    }
    return property(value);
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
    if (isObject$1(options)) {
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
  function isArrayLikeObject(value) {
    return isObjectLike(value) && isArrayLike(value);
  }
  function arrayIncludesWith(array, value, comparator) {
    var index = -1, length = array == null ? 0 : array.length;
    while (++index < length) {
      if (comparator(value, array[index])) {
        return true;
      }
    }
    return false;
  }
  function last(array) {
    var length = array == null ? 0 : array.length;
    return length ? array[length - 1] : void 0;
  }
  var htmlEscapes = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;"
  };
  var escapeHtmlChar = basePropertyOf(htmlEscapes);
  const escapeHtmlChar$1 = escapeHtmlChar;
  var reUnescapedHtml = /[&<>"']/g, reHasUnescapedHtml = RegExp(reUnescapedHtml.source);
  function escape(string) {
    string = toString$1(string);
    return string && reHasUnescapedHtml.test(string) ? string.replace(reUnescapedHtml, escapeHtmlChar$1) : string;
  }
  function baseValues(object, props) {
    return arrayMap(props, function(key) {
      return object[key];
    });
  }
  function baseSet(object, path, value, customizer) {
    if (!isObject$1(object)) {
      return object;
    }
    path = castPath(path, object);
    var index = -1, length = path.length, lastIndex = length - 1, nested = object;
    while (nested != null && ++index < length) {
      var key = toKey(path[index]), newValue = value;
      if (key === "__proto__" || key === "constructor" || key === "prototype") {
        return object;
      }
      if (index != lastIndex) {
        var objValue = nested[key];
        newValue = customizer ? customizer(objValue, key, nested) : void 0;
        if (newValue === void 0) {
          newValue = isObject$1(objValue) ? objValue : isIndex(path[index + 1]) ? [] : {};
        }
      }
      assignValue(nested, key, newValue);
      nested = nested[key];
    }
    return object;
  }
  function set(object, path, value) {
    return object == null ? object : baseSet(object, path, value);
  }
  var objectProto$1 = Object.prototype;
  var hasOwnProperty$2 = objectProto$1.hasOwnProperty;
  function customDefaultsAssignIn(objValue, srcValue, key, object) {
    if (objValue === void 0 || eq(objValue, objectProto$1[key]) && !hasOwnProperty$2.call(object, key)) {
      return srcValue;
    }
    return objValue;
  }
  var stringEscapes = {
    "\\": "\\",
    "'": "'",
    "\n": "n",
    "\r": "r",
    "\u2028": "u2028",
    "\u2029": "u2029"
  };
  function escapeStringChar(chr) {
    return "\\" + stringEscapes[chr];
  }
  var reInterpolate = /<%=([\s\S]+?)%>/g;
  const reInterpolate$1 = reInterpolate;
  var reEscape = /<%-([\s\S]+?)%>/g;
  const reEscape$1 = reEscape;
  var reEvaluate = /<%([\s\S]+?)%>/g;
  const reEvaluate$1 = reEvaluate;
  var templateSettings = {
    /**
     * Used to detect `data` property values to be HTML-escaped.
     *
     * @memberOf _.templateSettings
     * @type {RegExp}
     */
    "escape": reEscape$1,
    /**
     * Used to detect code to be evaluated.
     *
     * @memberOf _.templateSettings
     * @type {RegExp}
     */
    "evaluate": reEvaluate$1,
    /**
     * Used to detect `data` property values to inject.
     *
     * @memberOf _.templateSettings
     * @type {RegExp}
     */
    "interpolate": reInterpolate$1,
    /**
     * Used to reference the data object in the template text.
     *
     * @memberOf _.templateSettings
     * @type {string}
     */
    "variable": "",
    /**
     * Used to import variables into the compiled template.
     *
     * @memberOf _.templateSettings
     * @type {Object}
     */
    "imports": {
      /**
       * A reference to the `lodash` function.
       *
       * @memberOf _.templateSettings.imports
       * @type {Function}
       */
      "_": { "escape": escape }
    }
  };
  const templateSettings$1 = templateSettings;
  var INVALID_TEMPL_VAR_ERROR_TEXT = "Invalid `variable` option passed into `_.template`";
  var reEmptyStringLeading = /\b__p \+= '';/g, reEmptyStringMiddle = /\b(__p \+=) '' \+/g, reEmptyStringTrailing = /(__e\(.*?\)|\b__t\)) \+\n'';/g;
  var reForbiddenIdentifierChars = /[()=,{}\[\]\/\s]/;
  var reEsTemplate = /\$\{([^\\}]*(?:\\.[^\\}]*)*)\}/g;
  var reNoMatch = /($^)/;
  var reUnescapedString = /['\n\r\u2028\u2029\\]/g;
  var objectProto = Object.prototype;
  var hasOwnProperty$1 = objectProto.hasOwnProperty;
  function template(string, options, guard) {
    var settings = templateSettings$1.imports._.templateSettings || templateSettings$1;
    if (guard && isIterateeCall(string, options, guard)) {
      options = void 0;
    }
    string = toString$1(string);
    options = extendWith({}, options, settings, customDefaultsAssignIn);
    var imports = extendWith({}, options.imports, settings.imports, customDefaultsAssignIn), importsKeys = keys(imports), importsValues = baseValues(imports, importsKeys);
    var isEscaping, isEvaluating, index = 0, interpolate = options.interpolate || reNoMatch, source = "__p += '";
    var reDelimiters = RegExp(
      (options.escape || reNoMatch).source + "|" + interpolate.source + "|" + (interpolate === reInterpolate$1 ? reEsTemplate : reNoMatch).source + "|" + (options.evaluate || reNoMatch).source + "|$",
      "g"
    );
    var sourceURL = hasOwnProperty$1.call(options, "sourceURL") ? "//# sourceURL=" + (options.sourceURL + "").replace(/\s/g, " ") + "\n" : "";
    string.replace(reDelimiters, function(match, escapeValue, interpolateValue, esTemplateValue, evaluateValue, offset) {
      interpolateValue || (interpolateValue = esTemplateValue);
      source += string.slice(index, offset).replace(reUnescapedString, escapeStringChar);
      if (escapeValue) {
        isEscaping = true;
        source += "' +\n__e(" + escapeValue + ") +\n'";
      }
      if (evaluateValue) {
        isEvaluating = true;
        source += "';\n" + evaluateValue + ";\n__p += '";
      }
      if (interpolateValue) {
        source += "' +\n((__t = (" + interpolateValue + ")) == null ? '' : __t) +\n'";
      }
      index = offset + match.length;
      return match;
    });
    source += "';\n";
    var variable = hasOwnProperty$1.call(options, "variable") && options.variable;
    if (!variable) {
      source = "with (obj) {\n" + source + "\n}\n";
    } else if (reForbiddenIdentifierChars.test(variable)) {
      throw new Error(INVALID_TEMPL_VAR_ERROR_TEXT);
    }
    source = (isEvaluating ? source.replace(reEmptyStringLeading, "") : source).replace(reEmptyStringMiddle, "$1").replace(reEmptyStringTrailing, "$1;");
    source = "function(" + (variable || "obj") + ") {\n" + (variable ? "" : "obj || (obj = {});\n") + "var __t, __p = ''" + (isEscaping ? ", __e = _.escape" : "") + (isEvaluating ? ", __j = Array.prototype.join;\nfunction print() { __p += __j.call(arguments, '') }\n" : ";\n") + source + "return __p\n}";
    var result = attempt$1(function() {
      return Function(importsKeys, sourceURL + "return " + source).apply(void 0, importsValues);
    });
    result.source = source;
    if (isError(result)) {
      throw result;
    }
    return result;
  }
  var INFINITY = 1 / 0;
  var createSet = !(Set$1 && 1 / setToArray(new Set$1([, -0]))[1] == INFINITY) ? noop$1 : function(values) {
    return new Set$1(values);
  };
  const createSet$1 = createSet;
  var LARGE_ARRAY_SIZE = 200;
  function baseUniq(array, iteratee, comparator) {
    var index = -1, includes = arrayIncludes, length = array.length, isCommon = true, result = [], seen = result;
    if (comparator) {
      isCommon = false;
      includes = arrayIncludesWith;
    } else if (length >= LARGE_ARRAY_SIZE) {
      var set2 = iteratee ? null : createSet$1(array);
      if (set2) {
        return setToArray(set2);
      }
      isCommon = false;
      includes = cacheHas;
      seen = new SetCache();
    } else {
      seen = iteratee ? [] : result;
    }
    outer:
      while (++index < length) {
        var value = array[index], computed = iteratee ? iteratee(value) : value;
        value = comparator || value !== 0 ? value : 0;
        if (isCommon && computed === computed) {
          var seenIndex = seen.length;
          while (seenIndex--) {
            if (seen[seenIndex] === computed) {
              continue outer;
            }
          }
          if (iteratee) {
            seen.push(computed);
          }
          result.push(value);
        } else if (!includes(seen, computed, comparator)) {
          if (seen !== result) {
            seen.push(computed);
          }
          result.push(value);
        }
      }
    return result;
  }
  var unionBy = baseRest(function(arrays) {
    var iteratee = last(arrays);
    if (isArrayLikeObject(iteratee)) {
      iteratee = void 0;
    }
    return baseUniq(baseFlatten(arrays, 1, isArrayLikeObject, true), baseIteratee(iteratee));
  });
  const unionBy$1 = unionBy;
  const initialData = vue.ref(window.ZionBuilderProInitialData);
  const useDynamicContent = () => {
    const getDynamicContentFieldsByCategory = (category) => {
      const registeredFields = initialData.value.dynamic_fields_info.fields;
      return registeredFields.filter((field) => {
        if (Array.isArray(field.category)) {
          return field.category.includes(category);
        } else {
          return field.category === category;
        }
      });
    };
    const setInitialDynamicContentOptions = (payload) => {
      initialData.value.dynamic_fields_info = payload;
    };
    function getFieldValue(type, fieldID) {
      return get(initialData.value, `dynamic_fields_data.${fieldID}`, null);
    }
    return {
      getFieldValue,
      initialData,
      getDynamicContentFieldsByCategory,
      setInitialDynamicContentOptions
    };
  };
  function useRepeater() {
    function isRepeaterConsumer(element) {
      return element.getOptionValue("_advanced_options.is_repeater_consumer", false);
    }
    function isRepeaterProvider(element) {
      return element.getOptionValue("_advanced_options.is_repeater_provider", false);
    }
    function queryProviderData(config, element) {
      return new Promise((resolve, reject) => {
        element.serverRequester.request(
          {
            type: "perform_repeater_query",
            config
          },
          (response) => {
            resolve(response.items);
          },
          function(message) {
            reject(message);
          }
        );
      });
    }
    function getRepeaterConsumerConfig(element) {
      return element.getOptionValue("_advanced_options.repeater_consumer_config", {
        start: null,
        end: null
      });
    }
    function getRepeaterProviderConfig(element) {
      return element.getOptionValue("_advanced_options.repeater_provider_config", {
        type: "active_page_query"
      });
    }
    function getRepeaterItems(items2, element) {
      const config = getRepeaterConsumerConfig(element);
      const repeaterStartIndexValue = config.start || 0;
      const dataLength = items2.length;
      const repeaterEndIndexValue = config.end || dataLength;
      const repeaterEndValueOrMax = repeaterEndIndexValue > dataLength ? dataLength : repeaterEndIndexValue;
      return items2.slice(repeaterStartIndexValue, repeaterEndValueOrMax);
    }
    function getRepeaterParentConfig(element) {
      let repeaterConfig = false;
      while (element) {
        repeaterConfig = {
          repeaterProvider: isRepeaterProvider(element) ? getRepeaterProviderConfig(element) : false,
          repeaterConsumer: isRepeaterConsumer(element) ? getRepeaterConsumerConfig(element) : false,
          repeaterItemData: element.repeaterItemData,
          repeaterItemIndex: element.repeaterItemIndex,
          children: repeaterConfig
        };
        element = element.parent;
      }
      return repeaterConfig;
    }
    return {
      isRepeaterConsumer,
      isRepeaterProvider,
      queryProviderData,
      getRepeaterProviderConfig,
      getRepeaterConsumerConfig,
      getRepeaterItems,
      getRepeaterParentConfig
    };
  }
  const _hoisted_1$a = { class: "znpb-dynamic-content-list-slot znpb-fancy-scrollbar" };
  const _hoisted_2$7 = { class: "znpb-dynamic-content-list__title" };
  const _hoisted_3$5 = ["onClick"];
  const _sfc_main$p = /* @__PURE__ */ vue.defineComponent({
    __name: "DynamicContent",
    props: {
      config: {
        type: Object,
        required: true
      }
    },
    emits: ["enable"],
    setup(__props, { emit }) {
      const props = __props;
      const { initialData: initialData2, getDynamicContentFieldsByCategory } = useDynamicContent();
      const expanded = vue.ref(false);
      const getFieldGroups = initialData2.value.dynamic_fields_info.field_groups;
      function onDynamicContentSelected(field) {
        emit("enable", {
          type: field.id
        });
        expanded.value = !expanded.value;
      }
      const fieldsByGroup = vue.computed(() => {
        const fields = getDynamicContentFieldsByCategory(props.config.type);
        const byGroup = {};
        fields.forEach((field) => {
          if (!byGroup[field.group]) {
            byGroup[field.group] = [];
          }
          byGroup[field.group].push(field);
        });
        return byGroup;
      });
      return (_ctx, _cache) => {
        const _component_Icon = vue.resolveComponent("Icon");
        const _component_Tooltip = vue.resolveComponent("Tooltip");
        const _directive_znpb_tooltip = vue.resolveDirective("znpb-tooltip");
        return vue.openBlock(), vue.createBlock(_component_Tooltip, {
          show: expanded.value,
          "onUpdate:show": _cache[1] || (_cache[1] = ($event) => expanded.value = $event),
          trigger: "null",
          "close-on-outside-click": true,
          "show-arrows": true,
          "tooltip-class": "hg-popper--no-padding znpb-mh-200 znpb-flex",
          placement: "bottom",
          "append-to": "element",
          strategy: "fixed",
          class: "znpb-dynamic-content-list-trigger znpb-flex znpb-flex--vcenter"
        }, {
          content: vue.withCtx(() => [
            vue.createElementVNode("div", _hoisted_1$a, [
              (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(fieldsByGroup.value, (group, groupId) => {
                return vue.openBlock(), vue.createElementBlock("div", {
                  key: groupId,
                  class: "znpb-dynamic-content-list"
                }, [
                  vue.createElementVNode("h5", _hoisted_2$7, vue.toDisplayString(vue.unref(getFieldGroups)[groupId].name), 1),
                  (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(group, (field) => {
                    return vue.openBlock(), vue.createElementBlock("div", {
                      key: field.id,
                      class: "znpb-dynamic-content-list__item hg-popper-list__item",
                      onClick: vue.withModifiers(($event) => onDynamicContentSelected(field), ["stop"])
                    }, vue.toDisplayString(field.name), 9, _hoisted_3$5);
                  }), 128))
                ]);
              }), 128))
            ])
          ]),
          default: vue.withCtx(() => [
            vue.withDirectives(vue.createVNode(_component_Icon, {
              icon: "dynamic",
              onClick: _cache[0] || (_cache[0] = vue.withModifiers(($event) => expanded.value = !expanded.value, ["prevent", "stop"]))
            }, null, 512), [
              [_directive_znpb_tooltip, i18n__namespace.__("Use dynamic data", "zionbuilder-pro")]
            ])
          ]),
          _: 1
        }, 8, ["show"]);
      };
    }
  });
  const DynamicContent_vue_vue_type_style_index_0_lang = "";
  const _sfc_main$o = /* @__PURE__ */ vue.defineComponent({
    __name: "DynamicContentWrapper",
    setup(__props) {
      const inputWrapper = vue.inject("inputWrapper", null);
      const optionsForm = vue.inject("OptionsForm", null);
      const getDynamicContentConfig = vue.computed(() => {
        if (inputWrapper === null) {
          return false;
        }
        const { dynamic } = inputWrapper.schema;
        let returnConfig = false;
        if (optionsForm.dynamicDataEnabled.value || dynamic && dynamic.enabled === true) {
          if (inputWrapper.optionTypeConfig.value.dynamic) {
            if (inputWrapper.optionTypeConfig.value.dynamic.type !== "TYPE_HIDDEN") {
              returnConfig = inputWrapper.optionTypeConfig.value.dynamic;
            }
          }
        }
        return returnConfig;
      });
      function onDynamicContentSelected(saveArguments) {
        const { optionId: id } = inputWrapper;
        optionsForm.updateValueByPath(`__dynamic_content__.${id}`, saveArguments);
      }
      return (_ctx, _cache) => {
        return !!getDynamicContentConfig.value ? (vue.openBlock(), vue.createBlock(_sfc_main$p, {
          key: 0,
          config: getDynamicContentConfig.value,
          onEnable: onDynamicContentSelected
        }, null, 8, ["config"])) : vue.createCommentVNode("", true);
      };
    }
  });
  const _sfc_main$n = /* @__PURE__ */ vue.defineComponent({
    __name: "DynamicLinkWrapper",
    setup(__props) {
      const inputWrapper = vue.inject("inputWrapper");
      const optionsForm = vue.inject("OptionsForm");
      const expanded = vue.ref(false);
      const getDynamicContentConfig = vue.computed(() => ({
        type: "LINK"
      }));
      function onDynamicContentSelected(saveArguments) {
        const { optionId: id } = inputWrapper;
        optionsForm.updateValueByPath(`${id}.__dynamic_content__.link`, saveArguments);
        expanded.value = !expanded.value;
      }
      return (_ctx, _cache) => {
        return !!getDynamicContentConfig.value ? (vue.openBlock(), vue.createBlock(_sfc_main$p, {
          key: 0,
          config: getDynamicContentConfig.value,
          onEnable: onDynamicContentSelected
        }, null, 8, ["config"])) : vue.createCommentVNode("", true);
      };
    }
  });
  const _sfc_main$m = /* @__PURE__ */ vue.defineComponent({
    __name: "DynamicImageWrapper",
    setup(__props) {
      const inputWrapper = vue.inject("inputWrapper");
      const optionsForm = vue.inject("OptionsForm");
      const expanded = vue.ref(false);
      const getDynamicContentConfig = vue.computed(() => ({
        type: "IMAGE"
      }));
      function onDynamicContentSelected(saveArguments) {
        const { optionId: id, schema = {} } = inputWrapper;
        if (schema.show_size) {
          optionsForm.updateValueByPath(`${id}.__dynamic_content__.image`, saveArguments);
        } else {
          if (schema.type === "background") {
            optionsForm.updateValueByPath(`__dynamic_content__.background-image`, saveArguments);
          } else {
            optionsForm.updateValueByPath(`__dynamic_content__.${id}`, saveArguments);
          }
        }
        expanded.value = !expanded.value;
      }
      return (_ctx, _cache) => {
        return !!getDynamicContentConfig.value ? (vue.openBlock(), vue.createBlock(_sfc_main$p, {
          key: 0,
          config: getDynamicContentConfig.value,
          onEnable: onDynamicContentSelected
        }, null, 8, ["config"])) : vue.createCommentVNode("", true);
      };
    }
  });
  const DynamicImageWrapper_vue_vue_type_style_index_0_lang = "";
  const _hoisted_1$9 = {
    key: 0,
    class: "znpb-dynamic-field-wrapper"
  };
  const _hoisted_2$6 = { class: "znpb-dynamic-field-title" };
  const _hoisted_3$4 = { class: "znpb-dynamic-field-actions" };
  const _hoisted_4$2 = { class: "znpb-dynamic-field-content znpb-fancy-scrollbar" };
  const _hoisted_5$1 = { class: "znpb-dynamic-field-header" };
  const __default__ = {
    inheritAttrs: false
  };
  const _sfc_main$l = /* @__PURE__ */ vue.defineComponent(__spreadProps(__spreadValues({}, __default__), {
    __name: "DynamicContentOption",
    props: {
      dynamicData: {}
    },
    emits: ["delete", "update"],
    setup(__props, { emit }) {
      const props = __props;
      const expanded = vue.ref(false);
      const { initialData: initialData2 } = useDynamicContent();
      const getDynamicContentFieldsInfo = initialData2.value.dynamic_fields_info.fields;
      const defaultOptionsSchema = {
        _before: {
          type: "text",
          title: i18n__namespace.__("Before", "zionbuilder-pro"),
          dynamic: false
        },
        _after: {
          type: "text",
          title: i18n__namespace.__("After", "zionbuilder-pro"),
          dynamic: false
        },
        _fallback: {
          type: "text",
          title: i18n__namespace.__("Fallback", "zionbuilder-pro"),
          dynamic: false
        }
      };
      function onDelete() {
        emit("delete");
      }
      const fieldTypeModel = vue.computed(() => {
        const { type } = props.dynamicData;
        return type !== void 0 ? getDynamicContentFieldsInfo.find((fieldConfig) => fieldConfig.id === type) : "";
      });
      const optionsSchema = vue.computed(() => {
        return __spreadValues(__spreadValues({}, fieldTypeModel.value.options), defaultOptionsSchema);
      });
      const fieldOptions = vue.computed({
        get() {
          return props.dynamicData.options || {};
        },
        set(newValue) {
          emit("update", newValue);
        }
      });
      return (_ctx, _cache) => {
        const _component_Icon = vue.resolveComponent("Icon");
        const _component_OptionsForm = vue.resolveComponent("OptionsForm");
        const _component_Tooltip = vue.resolveComponent("Tooltip");
        const _directive_znpb_tooltip = vue.resolveDirective("znpb-tooltip");
        return _ctx.dynamicData ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_1$9, [
          vue.createElementVNode("h5", _hoisted_2$6, vue.toDisplayString(fieldTypeModel.value ? fieldTypeModel.value.name : i18n__namespace.__("Field not available", "zionbuilder-pro")), 1),
          vue.createElementVNode("div", _hoisted_3$4, [
            vue.createVNode(_component_Tooltip, {
              show: expanded.value,
              "onUpdate:show": _cache[3] || (_cache[3] = ($event) => expanded.value = $event),
              trigger: "null",
              "show-arrows": true,
              "tooltip-class": "hg-popper--no-padding",
              "close-on-outside-click": true,
              placement: "bottom",
              modifiers: [
                {
                  name: "preventOverflow",
                  options: {
                    rootBoundary: "viewport"
                  }
                }
              ]
            }, {
              content: vue.withCtx(() => [
                vue.createElementVNode("div", _hoisted_4$2, [
                  vue.createElementVNode("div", _hoisted_5$1, [
                    vue.createElementVNode("h3", null, vue.toDisplayString(i18n__namespace.__("Field options", "zionbuilder-pro")), 1),
                    vue.createVNode(_component_Icon, {
                      icon: "close",
                      class: "znpb-modal__header-button",
                      onClick: _cache[0] || (_cache[0] = ($event) => expanded.value = false)
                    })
                  ]),
                  vue.createVNode(_component_OptionsForm, {
                    modelValue: fieldOptions.value,
                    "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => fieldOptions.value = $event),
                    schema: optionsSchema.value,
                    "enable-dynamic-data": false
                  }, null, 8, ["modelValue", "schema"])
                ])
              ]),
              default: vue.withCtx(() => [
                fieldTypeModel.value ? vue.withDirectives((vue.openBlock(), vue.createBlock(_component_Icon, {
                  key: 0,
                  icon: "edit",
                  onClick: _cache[2] || (_cache[2] = ($event) => expanded.value = !expanded.value)
                }, null, 512)), [
                  [_directive_znpb_tooltip, i18n__namespace.__("Edit field options", "zionbuilder-pro")]
                ]) : vue.createCommentVNode("", true)
              ]),
              _: 1
            }, 8, ["show"]),
            vue.withDirectives(vue.createVNode(_component_Icon, {
              icon: "close",
              onClick: onDelete
            }, null, 512), [
              [_directive_znpb_tooltip, i18n__namespace.__("Delete dynamic field", "zionbuilder-pro")]
            ]),
            vue.renderSlot(_ctx.$slots, "append")
          ])
        ])) : vue.createCommentVNode("", true);
      };
    }
  }));
  const DynamicContentOption_vue_vue_type_style_index_0_lang = "";
  const _sfc_main$k = /* @__PURE__ */ vue.defineComponent({
    __name: "DynamicContentOptionWrapper",
    setup(__props) {
      const deleteValueByPath = vue.inject("deleteValueByPath");
      const inputWrapper = vue.inject("inputWrapper");
      const optionsForm = vue.inject("OptionsForm");
      const dynamicContentConfig = vue.computed(() => {
        const { id } = inputWrapper.schema;
        return optionsForm.modelValue.value.__dynamic_content__[id];
      });
      function onDelete() {
        const { id } = inputWrapper.schema;
        deleteValueByPath(`__dynamic_content__.${id}`);
      }
      function onUpdate(newValue) {
        const { id } = inputWrapper.schema;
        optionsForm.updateValueByPath(`__dynamic_content__.${id}.options`, newValue);
      }
      return (_ctx, _cache) => {
        return vue.openBlock(), vue.createBlock(_sfc_main$l, {
          "dynamic-data": dynamicContentConfig.value,
          onUpdate,
          onDelete
        }, null, 8, ["dynamic-data"]);
      };
    }
  });
  const _sfc_main$j = /* @__PURE__ */ vue.defineComponent({
    __name: "DynamicContentOptionImageWrapper",
    setup(__props) {
      const deleteValueByPath = vue.inject("deleteValueByPath");
      const inputWrapper = vue.inject("inputWrapper");
      const optionsForm = vue.inject("OptionsForm");
      const idPath = vue.computed(() => {
        const { id, show_size = false, type } = inputWrapper.schema;
        if (show_size) {
          return `${id}.__dynamic_content__.image`;
        } else {
          if (type === "background") {
            return "__dynamic_content__.background-image";
          }
          return `__dynamic_content__.${id}`;
        }
      });
      const dynamicContentConfig = vue.computed(() => {
        return get(optionsForm.modelValue.value, idPath.value);
      });
      function onDelete() {
        deleteValueByPath(idPath.value);
      }
      function onUpdate(newValue) {
        optionsForm.updateValueByPath(`${idPath.value}.options`, newValue);
      }
      return (_ctx, _cache) => {
        return vue.openBlock(), vue.createBlock(_sfc_main$l, {
          "dynamic-data": dynamicContentConfig.value,
          onUpdate,
          onDelete
        }, null, 8, ["dynamic-data"]);
      };
    }
  });
  const DynamicContentOptionImageWrapper_vue_vue_type_style_index_0_lang = "";
  const _sfc_main$i = /* @__PURE__ */ vue.defineComponent({
    __name: "DynamicContentOptionLinkWrapper",
    setup(__props) {
      const deleteValueByPath = vue.inject("deleteValueByPath");
      const inputWrapper = vue.inject("inputWrapper");
      const optionsForm = vue.inject("OptionsForm");
      const dynamicContentConfig = vue.computed(() => {
        const { id } = inputWrapper.schema;
        return get(optionsForm.modelValue.value, `${id}.__dynamic_content__.link`);
      });
      function onDelete() {
        const { id } = inputWrapper.schema;
        deleteValueByPath(`${id}.__dynamic_content__.link`);
      }
      function onUpdate(newValue) {
        const { id } = inputWrapper.schema;
        optionsForm.updateValueByPath(`${id}.__dynamic_content__.link.options`, newValue);
      }
      return (_ctx, _cache) => {
        return vue.openBlock(), vue.createBlock(_sfc_main$l, {
          "dynamic-data": dynamicContentConfig.value,
          onUpdate,
          onDelete
        }, {
          append: vue.withCtx(() => [
            vue.renderSlot(_ctx.$slots, "append")
          ]),
          _: 3
        }, 8, ["dynamic-data"]);
      };
    }
  });
  const _hoisted_1$8 = { class: "znpb-global-color-select-tooltipInner znpb-fancy-scrollbar" };
  const _hoisted_2$5 = { class: "znpb-global-color-select-tooltip__input" };
  const _hoisted_3$3 = { class: "znpb-global-color-select-tooltip__list" };
  const _hoisted_4$1 = ["onClick"];
  const _sfc_main$h = /* @__PURE__ */ vue.defineComponent({
    __name: "GlobalColors",
    props: {
      colors: {}
    },
    emits: ["color-change"],
    setup(__props, { emit }) {
      const props = __props;
      const keyword = vue.ref("");
      const filteredColors = vue.computed(() => {
        if (keyword.value.length > 2) {
          return props.colors.filter((item) => {
            return item.name.toLowerCase().indexOf(keyword.value.toLowerCase()) > -1;
          });
        }
        return props.colors;
      });
      return (_ctx, _cache) => {
        const _component_BaseInput = vue.resolveComponent("BaseInput");
        return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$8, [
          vue.createElementVNode("div", _hoisted_2$5, [
            vue.createVNode(_component_BaseInput, {
              modelValue: keyword.value,
              "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => keyword.value = $event),
              placeholder: i18n__namespace.__("Type a preset", "zionbuilder-pro"),
              clearable: true,
              size: "narrow"
            }, null, 8, ["modelValue", "placeholder"])
          ]),
          vue.createElementVNode("ul", _hoisted_3$3, [
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(filteredColors.value, (color, i) => {
              return vue.openBlock(), vue.createElementBlock("li", {
                key: i,
                onClick: vue.withModifiers(($event) => emit("color-change", color), ["stop"])
              }, [
                vue.createElementVNode("span", {
                  class: "znpb-colorpicker-circle znpb-colorpicker-circle-color",
                  style: vue.normalizeStyle({ background: color.color })
                }, null, 4),
                vue.createElementVNode("span", null, vue.toDisplayString(color.id), 1)
              ], 8, _hoisted_4$1);
            }), 128))
          ])
        ]);
      };
    }
  });
  const GlobalColors_vue_vue_type_style_index_0_lang = "";
  const _hoisted_1$7 = { class: "znpb-global-color-select" };
  const _hoisted_2$4 = {
    key: 0,
    class: "znpb-global-color-select__id"
  };
  const _sfc_main$g = /* @__PURE__ */ vue.defineComponent({
    __name: "DynamicContentColorpicker",
    setup(__props) {
      const schema = vue.inject("schema");
      const expanded = vue.ref(false);
      const { getValueByPath, updateValueByPath, deleteValueByPath } = vue.inject("OptionsForm");
      const useBuilderOptions = vue.inject("builderOptions");
      const { getOptionValue } = useBuilderOptions();
      let allGlobalColors = vue.computed(() => {
        return getOptionValue("global_colors");
      });
      let selectedGlobalColorData = vue.computed(() => {
        const { id } = schema;
        const { options = {} } = getValueByPath(`__dynamic_content__.${id}`, {});
        return options;
      });
      let selectedColorValue = vue.computed(() => {
        return allGlobalColors.value.find((colorConfig) => colorConfig.id === selectedGlobalColorData.value.color_id);
      });
      function onDelete() {
        const { id } = schema;
        deleteValueByPath(`__dynamic_content__.${id}`);
      }
      function onChange(colorConfig) {
        const { id } = schema;
        updateValueByPath(`__dynamic_content__.${id}`, {
          type: "global-color",
          options: {
            color_id: colorConfig.id
          }
        });
        expanded.value = false;
      }
      return (_ctx, _cache) => {
        const _component_Icon = vue.resolveComponent("Icon");
        const _component_Tooltip = vue.resolveComponent("Tooltip");
        return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$7, [
          vue.createVNode(_component_Tooltip, {
            trigger: null,
            "append-to": "element",
            show: expanded.value,
            "show-arrows": false,
            placement: "bottom-end",
            class: "znpb-global-color-select-tooltip",
            "tooltip-class": "hg-popper--no-padding",
            modifiers: [{ name: "offset", options: { offset: [0, 15] } }]
          }, {
            content: vue.withCtx(() => [
              vue.createVNode(_sfc_main$h, {
                colors: vue.unref(allGlobalColors),
                onColorChange: _cache[0] || (_cache[0] = ($event) => onChange($event))
              }, null, 8, ["colors"])
            ]),
            default: vue.withCtx(() => [
              vue.createElementVNode("div", {
                class: "znpb-global-color-select-innerWrapper",
                onClick: _cache[1] || (_cache[1] = ($event) => expanded.value = !expanded.value)
              }, [
                vue.createElementVNode("span", {
                  class: "znpb-colorpicker-circle znpb-colorpicker-circle-color",
                  style: vue.normalizeStyle({ background: vue.unref(selectedColorValue) ? vue.unref(selectedColorValue).color : null })
                }, null, 4),
                vue.createVNode(_component_Icon, { icon: "globe" }),
                vue.unref(selectedColorValue) ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_2$4, vue.toDisplayString(vue.unref(selectedColorValue).id), 1)) : vue.createCommentVNode("", true),
                vue.createVNode(_component_Icon, {
                  icon: "edit",
                  size: 12
                })
              ])
            ]),
            _: 1
          }, 8, ["show"]),
          vue.createVNode(_component_Icon, {
            icon: "close",
            size: 12,
            onClick: vue.withModifiers(onDelete, ["stop"])
          }, null, 8, ["onClick"])
        ]);
      };
    }
  });
  const DynamicContentColorpicker_vue_vue_type_style_index_0_lang = "";
  const _sfc_main$f = /* @__PURE__ */ vue.defineComponent({
    __name: "DynamicContentGradient",
    setup(__props) {
      const schema = vue.inject("schema");
      const getValueByPath = vue.inject("getValueByPath");
      const deleteValue = vue.inject("deleteValueByPath");
      const expanded = vue.ref(false);
      const useBuilderOptions = vue.inject("builderOptions");
      const { getOptionValue } = useBuilderOptions();
      let allGlobalGradients = vue.computed(() => {
        return getOptionValue("global_gradients");
      });
      let selectedGlobalGradientData = vue.computed(() => {
        const { id } = schema;
        const { options = {} } = getValueByPath(`__dynamic_content__.${id}`, {});
        return options;
      });
      let selectedGradientValue = vue.computed(() => {
        return allGlobalGradients.value.find(
          (gradientConfig) => gradientConfig.id === selectedGlobalGradientData.value.gradient_id
        ) || {};
      });
      function deleteGradientValue() {
        const { id } = schema;
        deleteValue(`__dynamic_content__.${id}`);
      }
      return (_ctx, _cache) => {
        const _component_GradientPreview = vue.resolveComponent("GradientPreview");
        const _component_Icon = vue.resolveComponent("Icon");
        const _component_Tooltip = vue.resolveComponent("Tooltip");
        const _component_ActionsOverlay = vue.resolveComponent("ActionsOverlay");
        return vue.openBlock(), vue.createElementBlock("div", null, [
          vue.createVNode(_component_ActionsOverlay, { class: "znpbpro-gradientActions" }, {
            actions: vue.withCtx(() => [
              vue.createVNode(_component_Tooltip, {
                trigger: null,
                "append-to": "element",
                show: expanded.value,
                "show-arrows": false,
                placement: "bottom-end",
                class: "znpb-global-color-select-tooltip",
                "tooltip-class": "hg-popper--no-padding",
                modifiers: [{ name: "offset", options: { offset: [0, 15] } }]
              }, {
                default: vue.withCtx(() => [
                  vue.createElementVNode("div", {
                    class: "znpb-global-color-select-innerWrapper",
                    onClick: _cache[0] || (_cache[0] = ($event) => expanded.value = !expanded.value)
                  }, [
                    vue.createVNode(_component_Icon, { icon: "globe" }),
                    vue.createElementVNode("span", null, vue.toDisplayString(vue.unref(selectedGradientValue).name), 1)
                  ])
                ]),
                _: 1
              }, 8, ["show"]),
              vue.createVNode(_component_Icon, {
                class: "znpbpro-gradientActions__deleteGradient",
                icon: "delete",
                "bg-size": 30,
                onClick: vue.withModifiers(deleteGradientValue, ["stop"])
              }, null, 8, ["onClick"])
            ]),
            default: vue.withCtx(() => [
              vue.createVNode(_component_GradientPreview, {
                config: vue.unref(selectedGradientValue).config || []
              }, null, 8, ["config"])
            ]),
            _: 1
          })
        ]);
      };
    }
  });
  const DynamicContentGradient_vue_vue_type_style_index_0_lang = "";
  const _hoisted_1$6 = { key: 0 };
  const _sfc_main$e = /* @__PURE__ */ vue.defineComponent({
    __name: "DynamicContentBackgroundColor",
    setup(__props) {
      const schema = vue.inject("schema");
      const getValueByPath = vue.inject("getValueByPath");
      const deleteValue = vue.inject("deleteValueByPath");
      const expanded = vue.ref(false);
      const useBuilderOptions = vue.inject("builderOptions");
      const { getOptionValue } = useBuilderOptions();
      const allGlobalColors = vue.computed(() => {
        return getOptionValue("global_colors", []);
      });
      const selectedGlobalColorData = vue.computed(() => {
        const { id } = schema;
        const { options = {} } = getValueByPath(`__dynamic_content__.${id}`, {});
        return options;
      });
      const selectedColorValue = vue.computed(() => {
        return allGlobalColors.value.find((colorConfig) => colorConfig.id === selectedGlobalColorData.value.color_id);
      });
      function deleteGradientValue() {
        const { id } = schema;
        deleteValue(`__dynamic_content__.${id}`);
      }
      return (_ctx, _cache) => {
        const _component_Icon = vue.resolveComponent("Icon");
        const _component_Tooltip = vue.resolveComponent("Tooltip");
        const _component_ActionsOverlay = vue.resolveComponent("ActionsOverlay");
        return vue.openBlock(), vue.createBlock(_component_ActionsOverlay, { class: "znpbpro-gradientActions" }, {
          actions: vue.withCtx(() => [
            vue.createVNode(_component_Tooltip, {
              trigger: null,
              "append-to": "element",
              show: expanded.value,
              "show-arrows": false,
              placement: "bottom-end",
              class: "znpb-global-color-select-tooltip",
              "tooltip-class": "hg-popper--no-padding",
              modifiers: [{ name: "offset", options: { offset: [0, 15] } }]
            }, {
              default: vue.withCtx(() => [
                vue.createElementVNode("div", {
                  class: "znpb-global-color-select-innerWrapper",
                  onClick: _cache[0] || (_cache[0] = ($event) => expanded.value = !expanded.value)
                }, [
                  vue.createVNode(_component_Icon, { icon: "globe" }),
                  selectedColorValue.value ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_1$6, vue.toDisplayString(selectedColorValue.value.id), 1)) : vue.createCommentVNode("", true)
                ])
              ]),
              _: 1
            }, 8, ["show"]),
            vue.createVNode(_component_Icon, {
              class: "znpbpro-gradientActions__deleteGradient",
              icon: "delete",
              "bg-size": 30,
              onClick: vue.withModifiers(deleteGradientValue, ["stop"])
            }, null, 8, ["onClick"])
          ]),
          default: vue.withCtx(() => [
            vue.createElementVNode("div", {
              class: "znpbpro-backgroundColorPreview",
              style: vue.normalizeStyle({ "background-color": selectedColorValue.value ? selectedColorValue.value.color : null })
            }, null, 4)
          ]),
          _: 1
        });
      };
    }
  });
  const DynamicContentBackgroundColor_vue_vue_type_style_index_0_lang = "";
  function getDefaultExportFromCjs(x) {
    return x && x.__esModule && Object.prototype.hasOwnProperty.call(x, "default") ? x["default"] : x;
  }
  function commonjsRequire(path) {
    throw new Error('Could not dynamically require "' + path + '". Please configure the dynamicRequireTargets or/and ignoreDynamicRequires option of @rollup/plugin-commonjs appropriately for this require call to work.');
  }
  var object_hash = { exports: {} };
  (function(module2, exports2) {
    !function(e) {
      module2.exports = e();
    }(function() {
      return function r(o, i, u) {
        function s(n, e2) {
          if (!i[n]) {
            if (!o[n]) {
              var t = "function" == typeof commonjsRequire && commonjsRequire;
              if (!e2 && t)
                return t(n, true);
              if (a)
                return a(n, true);
              throw new Error("Cannot find module '" + n + "'");
            }
            e2 = i[n] = { exports: {} };
            o[n][0].call(e2.exports, function(e3) {
              var t2 = o[n][1][e3];
              return s(t2 || e3);
            }, e2, e2.exports, r, o, i, u);
          }
          return i[n].exports;
        }
        for (var a = "function" == typeof commonjsRequire && commonjsRequire, e = 0; e < u.length; e++)
          s(u[e]);
        return s;
      }({ 1: [function(w, b, m) {
        !function(e, n, s, c, d, h, p, g, y) {
          var r = w("crypto");
          function t(e2, t2) {
            t2 = u(e2, t2);
            var n2;
            return void 0 === (n2 = "passthrough" !== t2.algorithm ? r.createHash(t2.algorithm) : new l()).write && (n2.write = n2.update, n2.end = n2.update), f(t2, n2).dispatch(e2), n2.update || n2.end(""), n2.digest ? n2.digest("buffer" === t2.encoding ? void 0 : t2.encoding) : (e2 = n2.read(), "buffer" !== t2.encoding ? e2.toString(t2.encoding) : e2);
          }
          (m = b.exports = t).sha1 = function(e2) {
            return t(e2);
          }, m.keys = function(e2) {
            return t(e2, { excludeValues: true, algorithm: "sha1", encoding: "hex" });
          }, m.MD5 = function(e2) {
            return t(e2, { algorithm: "md5", encoding: "hex" });
          }, m.keysMD5 = function(e2) {
            return t(e2, { algorithm: "md5", encoding: "hex", excludeValues: true });
          };
          var o = r.getHashes ? r.getHashes().slice() : ["sha1", "md5"], i = (o.push("passthrough"), ["buffer", "hex", "binary", "base64"]);
          function u(e2, t2) {
            var n2 = {};
            if (n2.algorithm = (t2 = t2 || {}).algorithm || "sha1", n2.encoding = t2.encoding || "hex", n2.excludeValues = !!t2.excludeValues, n2.algorithm = n2.algorithm.toLowerCase(), n2.encoding = n2.encoding.toLowerCase(), n2.ignoreUnknown = true === t2.ignoreUnknown, n2.respectType = false !== t2.respectType, n2.respectFunctionNames = false !== t2.respectFunctionNames, n2.respectFunctionProperties = false !== t2.respectFunctionProperties, n2.unorderedArrays = true === t2.unorderedArrays, n2.unorderedSets = false !== t2.unorderedSets, n2.unorderedObjects = false !== t2.unorderedObjects, n2.replacer = t2.replacer || void 0, n2.excludeKeys = t2.excludeKeys || void 0, void 0 === e2)
              throw new Error("Object argument required.");
            for (var r2 = 0; r2 < o.length; ++r2)
              o[r2].toLowerCase() === n2.algorithm.toLowerCase() && (n2.algorithm = o[r2]);
            if (-1 === o.indexOf(n2.algorithm))
              throw new Error('Algorithm "' + n2.algorithm + '"  not supported. supported values: ' + o.join(", "));
            if (-1 === i.indexOf(n2.encoding) && "passthrough" !== n2.algorithm)
              throw new Error('Encoding "' + n2.encoding + '"  not supported. supported values: ' + i.join(", "));
            return n2;
          }
          function a(e2) {
            if ("function" == typeof e2)
              return null != /^function\s+\w*\s*\(\s*\)\s*{\s+\[native code\]\s+}$/i.exec(Function.prototype.toString.call(e2));
          }
          function f(o2, t2, i2) {
            i2 = i2 || [];
            function u2(e2) {
              return t2.update ? t2.update(e2, "utf8") : t2.write(e2, "utf8");
            }
            return { dispatch: function(e2) {
              return this["_" + (null === (e2 = o2.replacer ? o2.replacer(e2) : e2) ? "null" : typeof e2)](e2);
            }, _object: function(t3) {
              var n2, e2 = Object.prototype.toString.call(t3), r2 = /\[object (.*)\]/i.exec(e2);
              r2 = (r2 = r2 ? r2[1] : "unknown:[" + e2 + "]").toLowerCase();
              if (0 <= (e2 = i2.indexOf(t3)))
                return this.dispatch("[CIRCULAR:" + e2 + "]");
              if (i2.push(t3), void 0 !== s && s.isBuffer && s.isBuffer(t3))
                return u2("buffer:"), u2(t3);
              if ("object" === r2 || "function" === r2 || "asyncfunction" === r2)
                return e2 = Object.keys(t3), o2.unorderedObjects && (e2 = e2.sort()), false === o2.respectType || a(t3) || e2.splice(0, 0, "prototype", "__proto__", "constructor"), o2.excludeKeys && (e2 = e2.filter(function(e3) {
                  return !o2.excludeKeys(e3);
                })), u2("object:" + e2.length + ":"), n2 = this, e2.forEach(function(e3) {
                  n2.dispatch(e3), u2(":"), o2.excludeValues || n2.dispatch(t3[e3]), u2(",");
                });
              if (!this["_" + r2]) {
                if (o2.ignoreUnknown)
                  return u2("[" + r2 + "]");
                throw new Error('Unknown object type "' + r2 + '"');
              }
              this["_" + r2](t3);
            }, _array: function(e2, t3) {
              t3 = void 0 !== t3 ? t3 : false !== o2.unorderedArrays;
              var n2 = this;
              if (u2("array:" + e2.length + ":"), !t3 || e2.length <= 1)
                return e2.forEach(function(e3) {
                  return n2.dispatch(e3);
                });
              var r2 = [], t3 = e2.map(function(e3) {
                var t4 = new l(), n3 = i2.slice();
                return f(o2, t4, n3).dispatch(e3), r2 = r2.concat(n3.slice(i2.length)), t4.read().toString();
              });
              return i2 = i2.concat(r2), t3.sort(), this._array(t3, false);
            }, _date: function(e2) {
              return u2("date:" + e2.toJSON());
            }, _symbol: function(e2) {
              return u2("symbol:" + e2.toString());
            }, _error: function(e2) {
              return u2("error:" + e2.toString());
            }, _boolean: function(e2) {
              return u2("bool:" + e2.toString());
            }, _string: function(e2) {
              u2("string:" + e2.length + ":"), u2(e2.toString());
            }, _function: function(e2) {
              u2("fn:"), a(e2) ? this.dispatch("[native]") : this.dispatch(e2.toString()), false !== o2.respectFunctionNames && this.dispatch("function-name:" + String(e2.name)), o2.respectFunctionProperties && this._object(e2);
            }, _number: function(e2) {
              return u2("number:" + e2.toString());
            }, _xml: function(e2) {
              return u2("xml:" + e2.toString());
            }, _null: function() {
              return u2("Null");
            }, _undefined: function() {
              return u2("Undefined");
            }, _regexp: function(e2) {
              return u2("regex:" + e2.toString());
            }, _uint8array: function(e2) {
              return u2("uint8array:"), this.dispatch(Array.prototype.slice.call(e2));
            }, _uint8clampedarray: function(e2) {
              return u2("uint8clampedarray:"), this.dispatch(Array.prototype.slice.call(e2));
            }, _int8array: function(e2) {
              return u2("int8array:"), this.dispatch(Array.prototype.slice.call(e2));
            }, _uint16array: function(e2) {
              return u2("uint16array:"), this.dispatch(Array.prototype.slice.call(e2));
            }, _int16array: function(e2) {
              return u2("int16array:"), this.dispatch(Array.prototype.slice.call(e2));
            }, _uint32array: function(e2) {
              return u2("uint32array:"), this.dispatch(Array.prototype.slice.call(e2));
            }, _int32array: function(e2) {
              return u2("int32array:"), this.dispatch(Array.prototype.slice.call(e2));
            }, _float32array: function(e2) {
              return u2("float32array:"), this.dispatch(Array.prototype.slice.call(e2));
            }, _float64array: function(e2) {
              return u2("float64array:"), this.dispatch(Array.prototype.slice.call(e2));
            }, _arraybuffer: function(e2) {
              return u2("arraybuffer:"), this.dispatch(new Uint8Array(e2));
            }, _url: function(e2) {
              return u2("url:" + e2.toString());
            }, _map: function(e2) {
              u2("map:");
              e2 = Array.from(e2);
              return this._array(e2, false !== o2.unorderedSets);
            }, _set: function(e2) {
              u2("set:");
              e2 = Array.from(e2);
              return this._array(e2, false !== o2.unorderedSets);
            }, _file: function(e2) {
              return u2("file:"), this.dispatch([e2.name, e2.size, e2.type, e2.lastModfied]);
            }, _blob: function() {
              if (o2.ignoreUnknown)
                return u2("[blob]");
              throw Error('Hashing Blob objects is currently not supported\n(see https://github.com/puleos/object-hash/issues/26)\nUse "options.replacer" or "options.ignoreUnknown"\n');
            }, _domwindow: function() {
              return u2("domwindow");
            }, _bigint: function(e2) {
              return u2("bigint:" + e2.toString());
            }, _process: function() {
              return u2("process");
            }, _timer: function() {
              return u2("timer");
            }, _pipe: function() {
              return u2("pipe");
            }, _tcp: function() {
              return u2("tcp");
            }, _udp: function() {
              return u2("udp");
            }, _tty: function() {
              return u2("tty");
            }, _statwatcher: function() {
              return u2("statwatcher");
            }, _securecontext: function() {
              return u2("securecontext");
            }, _connection: function() {
              return u2("connection");
            }, _zlib: function() {
              return u2("zlib");
            }, _context: function() {
              return u2("context");
            }, _nodescript: function() {
              return u2("nodescript");
            }, _httpparser: function() {
              return u2("httpparser");
            }, _dataview: function() {
              return u2("dataview");
            }, _signal: function() {
              return u2("signal");
            }, _fsevent: function() {
              return u2("fsevent");
            }, _tlswrap: function() {
              return u2("tlswrap");
            } };
          }
          function l() {
            return { buf: "", write: function(e2) {
              this.buf += e2;
            }, end: function(e2) {
              this.buf += e2;
            }, read: function() {
              return this.buf;
            } };
          }
          m.writeToStream = function(e2, t2, n2) {
            return void 0 === n2 && (n2 = t2, t2 = {}), f(t2 = u(e2, t2), n2).dispatch(e2);
          };
        }.call(this, w("lYpoI2"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, w("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/fake_9a5aa49d.js", "/");
      }, { buffer: 3, crypto: 5, lYpoI2: 11 }], 2: [function(e, t, f) {
        !function(e2, t2, n, r, o, i, u, s, a) {
          !function(e3) {
            var a2 = "undefined" != typeof Uint8Array ? Uint8Array : Array, t3 = "+".charCodeAt(0), n2 = "/".charCodeAt(0), r2 = "0".charCodeAt(0), o2 = "a".charCodeAt(0), i2 = "A".charCodeAt(0), u2 = "-".charCodeAt(0), s2 = "_".charCodeAt(0);
            function f2(e4) {
              e4 = e4.charCodeAt(0);
              return e4 === t3 || e4 === u2 ? 62 : e4 === n2 || e4 === s2 ? 63 : e4 < r2 ? -1 : e4 < r2 + 10 ? e4 - r2 + 26 + 26 : e4 < i2 + 26 ? e4 - i2 : e4 < o2 + 26 ? e4 - o2 + 26 : void 0;
            }
            e3.toByteArray = function(e4) {
              var t4, n3;
              if (0 < e4.length % 4)
                throw new Error("Invalid string. Length must be a multiple of 4");
              var r3 = e4.length, r3 = "=" === e4.charAt(r3 - 2) ? 2 : "=" === e4.charAt(r3 - 1) ? 1 : 0, o3 = new a2(3 * e4.length / 4 - r3), i3 = 0 < r3 ? e4.length - 4 : e4.length, u3 = 0;
              function s3(e5) {
                o3[u3++] = e5;
              }
              for (t4 = 0; t4 < i3; t4 += 4, 0)
                s3((16711680 & (n3 = f2(e4.charAt(t4)) << 18 | f2(e4.charAt(t4 + 1)) << 12 | f2(e4.charAt(t4 + 2)) << 6 | f2(e4.charAt(t4 + 3)))) >> 16), s3((65280 & n3) >> 8), s3(255 & n3);
              return 2 == r3 ? s3(255 & (n3 = f2(e4.charAt(t4)) << 2 | f2(e4.charAt(t4 + 1)) >> 4)) : 1 == r3 && (s3((n3 = f2(e4.charAt(t4)) << 10 | f2(e4.charAt(t4 + 1)) << 4 | f2(e4.charAt(t4 + 2)) >> 2) >> 8 & 255), s3(255 & n3)), o3;
            }, e3.fromByteArray = function(e4) {
              var t4, n3, r3, o3, i3 = e4.length % 3, u3 = "";
              function s3(e5) {
                return "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/".charAt(e5);
              }
              for (t4 = 0, r3 = e4.length - i3; t4 < r3; t4 += 3)
                n3 = (e4[t4] << 16) + (e4[t4 + 1] << 8) + e4[t4 + 2], u3 += s3((o3 = n3) >> 18 & 63) + s3(o3 >> 12 & 63) + s3(o3 >> 6 & 63) + s3(63 & o3);
              switch (i3) {
                case 1:
                  u3 = (u3 += s3((n3 = e4[e4.length - 1]) >> 2)) + s3(n3 << 4 & 63) + "==";
                  break;
                case 2:
                  u3 = (u3 = (u3 += s3((n3 = (e4[e4.length - 2] << 8) + e4[e4.length - 1]) >> 10)) + s3(n3 >> 4 & 63)) + s3(n3 << 2 & 63) + "=";
              }
              return u3;
            };
          }(void 0 === f ? this.base64js = {} : f);
        }.call(this, e("lYpoI2"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/node_modules/gulp-browserify/node_modules/base64-js/lib/b64.js", "/node_modules/gulp-browserify/node_modules/base64-js/lib");
      }, { buffer: 3, lYpoI2: 11 }], 3: [function(O, e, H) {
        !function(e2, n, f, r, h, p, g, y, w) {
          var a = O("base64-js"), i = O("ieee754");
          function f(e3, t2, n2) {
            if (!(this instanceof f))
              return new f(e3, t2, n2);
            var r2, o2, i2, u2, s2 = typeof e3;
            if ("base64" === t2 && "string" == s2)
              for (e3 = (u2 = e3).trim ? u2.trim() : u2.replace(/^\s+|\s+$/g, ""); e3.length % 4 != 0; )
                e3 += "=";
            if ("number" == s2)
              r2 = j(e3);
            else if ("string" == s2)
              r2 = f.byteLength(e3, t2);
            else {
              if ("object" != s2)
                throw new Error("First argument needs to be a number, array or string.");
              r2 = j(e3.length);
            }
            if (f._useTypedArrays ? o2 = f._augment(new Uint8Array(r2)) : ((o2 = this).length = r2, o2._isBuffer = true), f._useTypedArrays && "number" == typeof e3.byteLength)
              o2._set(e3);
            else if (C(u2 = e3) || f.isBuffer(u2) || u2 && "object" == typeof u2 && "number" == typeof u2.length)
              for (i2 = 0; i2 < r2; i2++)
                f.isBuffer(e3) ? o2[i2] = e3.readUInt8(i2) : o2[i2] = e3[i2];
            else if ("string" == s2)
              o2.write(e3, 0, t2);
            else if ("number" == s2 && !f._useTypedArrays && !n2)
              for (i2 = 0; i2 < r2; i2++)
                o2[i2] = 0;
            return o2;
          }
          function b(e3, t2, n2, r2) {
            return f._charsWritten = c(function(e4) {
              for (var t3 = [], n3 = 0; n3 < e4.length; n3++)
                t3.push(255 & e4.charCodeAt(n3));
              return t3;
            }(t2), e3, n2, r2);
          }
          function m(e3, t2, n2, r2) {
            return f._charsWritten = c(function(e4) {
              for (var t3, n3, r3 = [], o2 = 0; o2 < e4.length; o2++)
                n3 = e4.charCodeAt(o2), t3 = n3 >> 8, n3 = n3 % 256, r3.push(n3), r3.push(t3);
              return r3;
            }(t2), e3, n2, r2);
          }
          function v(e3, t2, n2) {
            var r2 = "";
            n2 = Math.min(e3.length, n2);
            for (var o2 = t2; o2 < n2; o2++)
              r2 += String.fromCharCode(e3[o2]);
            return r2;
          }
          function o(e3, t2, n2, r2) {
            r2 || (d("boolean" == typeof n2, "missing or invalid endian"), d(null != t2, "missing offset"), d(t2 + 1 < e3.length, "Trying to read beyond buffer length"));
            var o2, r2 = e3.length;
            if (!(r2 <= t2))
              return n2 ? (o2 = e3[t2], t2 + 1 < r2 && (o2 |= e3[t2 + 1] << 8)) : (o2 = e3[t2] << 8, t2 + 1 < r2 && (o2 |= e3[t2 + 1])), o2;
          }
          function u(e3, t2, n2, r2) {
            r2 || (d("boolean" == typeof n2, "missing or invalid endian"), d(null != t2, "missing offset"), d(t2 + 3 < e3.length, "Trying to read beyond buffer length"));
            var o2, r2 = e3.length;
            if (!(r2 <= t2))
              return n2 ? (t2 + 2 < r2 && (o2 = e3[t2 + 2] << 16), t2 + 1 < r2 && (o2 |= e3[t2 + 1] << 8), o2 |= e3[t2], t2 + 3 < r2 && (o2 += e3[t2 + 3] << 24 >>> 0)) : (t2 + 1 < r2 && (o2 = e3[t2 + 1] << 16), t2 + 2 < r2 && (o2 |= e3[t2 + 2] << 8), t2 + 3 < r2 && (o2 |= e3[t2 + 3]), o2 += e3[t2] << 24 >>> 0), o2;
          }
          function _(e3, t2, n2, r2) {
            if (r2 || (d("boolean" == typeof n2, "missing or invalid endian"), d(null != t2, "missing offset"), d(t2 + 1 < e3.length, "Trying to read beyond buffer length")), !(e3.length <= t2))
              return r2 = o(e3, t2, n2, true), 32768 & r2 ? -1 * (65535 - r2 + 1) : r2;
          }
          function E(e3, t2, n2, r2) {
            if (r2 || (d("boolean" == typeof n2, "missing or invalid endian"), d(null != t2, "missing offset"), d(t2 + 3 < e3.length, "Trying to read beyond buffer length")), !(e3.length <= t2))
              return r2 = u(e3, t2, n2, true), 2147483648 & r2 ? -1 * (4294967295 - r2 + 1) : r2;
          }
          function I(e3, t2, n2, r2) {
            return r2 || (d("boolean" == typeof n2, "missing or invalid endian"), d(t2 + 3 < e3.length, "Trying to read beyond buffer length")), i.read(e3, t2, n2, 23, 4);
          }
          function A(e3, t2, n2, r2) {
            return r2 || (d("boolean" == typeof n2, "missing or invalid endian"), d(t2 + 7 < e3.length, "Trying to read beyond buffer length")), i.read(e3, t2, n2, 52, 8);
          }
          function s(e3, t2, n2, r2, o2) {
            o2 || (d(null != t2, "missing value"), d("boolean" == typeof r2, "missing or invalid endian"), d(null != n2, "missing offset"), d(n2 + 1 < e3.length, "trying to write beyond buffer length"), Y(t2, 65535));
            o2 = e3.length;
            if (!(o2 <= n2))
              for (var i2 = 0, u2 = Math.min(o2 - n2, 2); i2 < u2; i2++)
                e3[n2 + i2] = (t2 & 255 << 8 * (r2 ? i2 : 1 - i2)) >>> 8 * (r2 ? i2 : 1 - i2);
          }
          function l(e3, t2, n2, r2, o2) {
            o2 || (d(null != t2, "missing value"), d("boolean" == typeof r2, "missing or invalid endian"), d(null != n2, "missing offset"), d(n2 + 3 < e3.length, "trying to write beyond buffer length"), Y(t2, 4294967295));
            o2 = e3.length;
            if (!(o2 <= n2))
              for (var i2 = 0, u2 = Math.min(o2 - n2, 4); i2 < u2; i2++)
                e3[n2 + i2] = t2 >>> 8 * (r2 ? i2 : 3 - i2) & 255;
          }
          function B(e3, t2, n2, r2, o2) {
            o2 || (d(null != t2, "missing value"), d("boolean" == typeof r2, "missing or invalid endian"), d(null != n2, "missing offset"), d(n2 + 1 < e3.length, "Trying to write beyond buffer length"), F(t2, 32767, -32768)), e3.length <= n2 || s(e3, 0 <= t2 ? t2 : 65535 + t2 + 1, n2, r2, o2);
          }
          function L(e3, t2, n2, r2, o2) {
            o2 || (d(null != t2, "missing value"), d("boolean" == typeof r2, "missing or invalid endian"), d(null != n2, "missing offset"), d(n2 + 3 < e3.length, "Trying to write beyond buffer length"), F(t2, 2147483647, -2147483648)), e3.length <= n2 || l(e3, 0 <= t2 ? t2 : 4294967295 + t2 + 1, n2, r2, o2);
          }
          function U(e3, t2, n2, r2, o2) {
            o2 || (d(null != t2, "missing value"), d("boolean" == typeof r2, "missing or invalid endian"), d(null != n2, "missing offset"), d(n2 + 3 < e3.length, "Trying to write beyond buffer length"), D(t2, 34028234663852886e22, -34028234663852886e22)), e3.length <= n2 || i.write(e3, t2, n2, r2, 23, 4);
          }
          function x(e3, t2, n2, r2, o2) {
            o2 || (d(null != t2, "missing value"), d("boolean" == typeof r2, "missing or invalid endian"), d(null != n2, "missing offset"), d(n2 + 7 < e3.length, "Trying to write beyond buffer length"), D(t2, 17976931348623157e292, -17976931348623157e292)), e3.length <= n2 || i.write(e3, t2, n2, r2, 52, 8);
          }
          H.Buffer = f, H.SlowBuffer = f, H.INSPECT_MAX_BYTES = 50, f.poolSize = 8192, f._useTypedArrays = function() {
            try {
              var e3 = new ArrayBuffer(0), t2 = new Uint8Array(e3);
              return t2.foo = function() {
                return 42;
              }, 42 === t2.foo() && "function" == typeof t2.subarray;
            } catch (e4) {
              return false;
            }
          }(), f.isEncoding = function(e3) {
            switch (String(e3).toLowerCase()) {
              case "hex":
              case "utf8":
              case "utf-8":
              case "ascii":
              case "binary":
              case "base64":
              case "raw":
              case "ucs2":
              case "ucs-2":
              case "utf16le":
              case "utf-16le":
                return true;
              default:
                return false;
            }
          }, f.isBuffer = function(e3) {
            return !(null == e3 || !e3._isBuffer);
          }, f.byteLength = function(e3, t2) {
            var n2;
            switch (e3 += "", t2 || "utf8") {
              case "hex":
                n2 = e3.length / 2;
                break;
              case "utf8":
              case "utf-8":
                n2 = T(e3).length;
                break;
              case "ascii":
              case "binary":
              case "raw":
                n2 = e3.length;
                break;
              case "base64":
                n2 = M(e3).length;
                break;
              case "ucs2":
              case "ucs-2":
              case "utf16le":
              case "utf-16le":
                n2 = 2 * e3.length;
                break;
              default:
                throw new Error("Unknown encoding");
            }
            return n2;
          }, f.concat = function(e3, t2) {
            if (d(C(e3), "Usage: Buffer.concat(list, [totalLength])\nlist should be an Array."), 0 === e3.length)
              return new f(0);
            if (1 === e3.length)
              return e3[0];
            if ("number" != typeof t2)
              for (o2 = t2 = 0; o2 < e3.length; o2++)
                t2 += e3[o2].length;
            for (var n2 = new f(t2), r2 = 0, o2 = 0; o2 < e3.length; o2++) {
              var i2 = e3[o2];
              i2.copy(n2, r2), r2 += i2.length;
            }
            return n2;
          }, f.prototype.write = function(e3, t2, n2, r2) {
            isFinite(t2) ? isFinite(n2) || (r2 = n2, n2 = void 0) : (a2 = r2, r2 = t2, t2 = n2, n2 = a2), t2 = Number(t2) || 0;
            var o2, i2, u2, s2, a2 = this.length - t2;
            switch ((!n2 || a2 < (n2 = Number(n2))) && (n2 = a2), r2 = String(r2 || "utf8").toLowerCase()) {
              case "hex":
                o2 = function(e4, t3, n3, r3) {
                  n3 = Number(n3) || 0;
                  var o3 = e4.length - n3;
                  (!r3 || o3 < (r3 = Number(r3))) && (r3 = o3), d((o3 = t3.length) % 2 == 0, "Invalid hex string"), o3 / 2 < r3 && (r3 = o3 / 2);
                  for (var i3 = 0; i3 < r3; i3++) {
                    var u3 = parseInt(t3.substr(2 * i3, 2), 16);
                    d(!isNaN(u3), "Invalid hex string"), e4[n3 + i3] = u3;
                  }
                  return f._charsWritten = 2 * i3, i3;
                }(this, e3, t2, n2);
                break;
              case "utf8":
              case "utf-8":
                i2 = this, u2 = t2, s2 = n2, o2 = f._charsWritten = c(T(e3), i2, u2, s2);
                break;
              case "ascii":
              case "binary":
                o2 = b(this, e3, t2, n2);
                break;
              case "base64":
                i2 = this, u2 = t2, s2 = n2, o2 = f._charsWritten = c(M(e3), i2, u2, s2);
                break;
              case "ucs2":
              case "ucs-2":
              case "utf16le":
              case "utf-16le":
                o2 = m(this, e3, t2, n2);
                break;
              default:
                throw new Error("Unknown encoding");
            }
            return o2;
          }, f.prototype.toString = function(e3, t2, n2) {
            var r2, o2, i2, u2, s2 = this;
            if (e3 = String(e3 || "utf8").toLowerCase(), t2 = Number(t2) || 0, (n2 = void 0 !== n2 ? Number(n2) : s2.length) === t2)
              return "";
            switch (e3) {
              case "hex":
                r2 = function(e4, t3, n3) {
                  var r3 = e4.length;
                  (!t3 || t3 < 0) && (t3 = 0);
                  (!n3 || n3 < 0 || r3 < n3) && (n3 = r3);
                  for (var o3 = "", i3 = t3; i3 < n3; i3++)
                    o3 += k(e4[i3]);
                  return o3;
                }(s2, t2, n2);
                break;
              case "utf8":
              case "utf-8":
                r2 = function(e4, t3, n3) {
                  var r3 = "", o3 = "";
                  n3 = Math.min(e4.length, n3);
                  for (var i3 = t3; i3 < n3; i3++)
                    e4[i3] <= 127 ? (r3 += N(o3) + String.fromCharCode(e4[i3]), o3 = "") : o3 += "%" + e4[i3].toString(16);
                  return r3 + N(o3);
                }(s2, t2, n2);
                break;
              case "ascii":
              case "binary":
                r2 = v(s2, t2, n2);
                break;
              case "base64":
                o2 = s2, u2 = n2, r2 = 0 === (i2 = t2) && u2 === o2.length ? a.fromByteArray(o2) : a.fromByteArray(o2.slice(i2, u2));
                break;
              case "ucs2":
              case "ucs-2":
              case "utf16le":
              case "utf-16le":
                r2 = function(e4, t3, n3) {
                  for (var r3 = e4.slice(t3, n3), o3 = "", i3 = 0; i3 < r3.length; i3 += 2)
                    o3 += String.fromCharCode(r3[i3] + 256 * r3[i3 + 1]);
                  return o3;
                }(s2, t2, n2);
                break;
              default:
                throw new Error("Unknown encoding");
            }
            return r2;
          }, f.prototype.toJSON = function() {
            return { type: "Buffer", data: Array.prototype.slice.call(this._arr || this, 0) };
          }, f.prototype.copy = function(e3, t2, n2, r2) {
            if (t2 = t2 || 0, (r2 = r2 || 0 === r2 ? r2 : this.length) !== (n2 = n2 || 0) && 0 !== e3.length && 0 !== this.length) {
              d(n2 <= r2, "sourceEnd < sourceStart"), d(0 <= t2 && t2 < e3.length, "targetStart out of bounds"), d(0 <= n2 && n2 < this.length, "sourceStart out of bounds"), d(0 <= r2 && r2 <= this.length, "sourceEnd out of bounds"), r2 > this.length && (r2 = this.length);
              var o2 = (r2 = e3.length - t2 < r2 - n2 ? e3.length - t2 + n2 : r2) - n2;
              if (o2 < 100 || !f._useTypedArrays)
                for (var i2 = 0; i2 < o2; i2++)
                  e3[i2 + t2] = this[i2 + n2];
              else
                e3._set(this.subarray(n2, n2 + o2), t2);
            }
          }, f.prototype.slice = function(e3, t2) {
            var n2 = this.length;
            if (e3 = S(e3, n2, 0), t2 = S(t2, n2, n2), f._useTypedArrays)
              return f._augment(this.subarray(e3, t2));
            for (var r2 = t2 - e3, o2 = new f(r2, void 0, true), i2 = 0; i2 < r2; i2++)
              o2[i2] = this[i2 + e3];
            return o2;
          }, f.prototype.get = function(e3) {
            return console.log(".get() is deprecated. Access using array indexes instead."), this.readUInt8(e3);
          }, f.prototype.set = function(e3, t2) {
            return console.log(".set() is deprecated. Access using array indexes instead."), this.writeUInt8(e3, t2);
          }, f.prototype.readUInt8 = function(e3, t2) {
            if (t2 || (d(null != e3, "missing offset"), d(e3 < this.length, "Trying to read beyond buffer length")), !(e3 >= this.length))
              return this[e3];
          }, f.prototype.readUInt16LE = function(e3, t2) {
            return o(this, e3, true, t2);
          }, f.prototype.readUInt16BE = function(e3, t2) {
            return o(this, e3, false, t2);
          }, f.prototype.readUInt32LE = function(e3, t2) {
            return u(this, e3, true, t2);
          }, f.prototype.readUInt32BE = function(e3, t2) {
            return u(this, e3, false, t2);
          }, f.prototype.readInt8 = function(e3, t2) {
            if (t2 || (d(null != e3, "missing offset"), d(e3 < this.length, "Trying to read beyond buffer length")), !(e3 >= this.length))
              return 128 & this[e3] ? -1 * (255 - this[e3] + 1) : this[e3];
          }, f.prototype.readInt16LE = function(e3, t2) {
            return _(this, e3, true, t2);
          }, f.prototype.readInt16BE = function(e3, t2) {
            return _(this, e3, false, t2);
          }, f.prototype.readInt32LE = function(e3, t2) {
            return E(this, e3, true, t2);
          }, f.prototype.readInt32BE = function(e3, t2) {
            return E(this, e3, false, t2);
          }, f.prototype.readFloatLE = function(e3, t2) {
            return I(this, e3, true, t2);
          }, f.prototype.readFloatBE = function(e3, t2) {
            return I(this, e3, false, t2);
          }, f.prototype.readDoubleLE = function(e3, t2) {
            return A(this, e3, true, t2);
          }, f.prototype.readDoubleBE = function(e3, t2) {
            return A(this, e3, false, t2);
          }, f.prototype.writeUInt8 = function(e3, t2, n2) {
            n2 || (d(null != e3, "missing value"), d(null != t2, "missing offset"), d(t2 < this.length, "trying to write beyond buffer length"), Y(e3, 255)), t2 >= this.length || (this[t2] = e3);
          }, f.prototype.writeUInt16LE = function(e3, t2, n2) {
            s(this, e3, t2, true, n2);
          }, f.prototype.writeUInt16BE = function(e3, t2, n2) {
            s(this, e3, t2, false, n2);
          }, f.prototype.writeUInt32LE = function(e3, t2, n2) {
            l(this, e3, t2, true, n2);
          }, f.prototype.writeUInt32BE = function(e3, t2, n2) {
            l(this, e3, t2, false, n2);
          }, f.prototype.writeInt8 = function(e3, t2, n2) {
            n2 || (d(null != e3, "missing value"), d(null != t2, "missing offset"), d(t2 < this.length, "Trying to write beyond buffer length"), F(e3, 127, -128)), t2 >= this.length || (0 <= e3 ? this.writeUInt8(e3, t2, n2) : this.writeUInt8(255 + e3 + 1, t2, n2));
          }, f.prototype.writeInt16LE = function(e3, t2, n2) {
            B(this, e3, t2, true, n2);
          }, f.prototype.writeInt16BE = function(e3, t2, n2) {
            B(this, e3, t2, false, n2);
          }, f.prototype.writeInt32LE = function(e3, t2, n2) {
            L(this, e3, t2, true, n2);
          }, f.prototype.writeInt32BE = function(e3, t2, n2) {
            L(this, e3, t2, false, n2);
          }, f.prototype.writeFloatLE = function(e3, t2, n2) {
            U(this, e3, t2, true, n2);
          }, f.prototype.writeFloatBE = function(e3, t2, n2) {
            U(this, e3, t2, false, n2);
          }, f.prototype.writeDoubleLE = function(e3, t2, n2) {
            x(this, e3, t2, true, n2);
          }, f.prototype.writeDoubleBE = function(e3, t2, n2) {
            x(this, e3, t2, false, n2);
          }, f.prototype.fill = function(e3, t2, n2) {
            if (t2 = t2 || 0, n2 = n2 || this.length, d("number" == typeof (e3 = "string" == typeof (e3 = e3 || 0) ? e3.charCodeAt(0) : e3) && !isNaN(e3), "value is not a number"), d(t2 <= n2, "end < start"), n2 !== t2 && 0 !== this.length) {
              d(0 <= t2 && t2 < this.length, "start out of bounds"), d(0 <= n2 && n2 <= this.length, "end out of bounds");
              for (var r2 = t2; r2 < n2; r2++)
                this[r2] = e3;
            }
          }, f.prototype.inspect = function() {
            for (var e3 = [], t2 = this.length, n2 = 0; n2 < t2; n2++)
              if (e3[n2] = k(this[n2]), n2 === H.INSPECT_MAX_BYTES) {
                e3[n2 + 1] = "...";
                break;
              }
            return "<Buffer " + e3.join(" ") + ">";
          }, f.prototype.toArrayBuffer = function() {
            if ("undefined" == typeof Uint8Array)
              throw new Error("Buffer.toArrayBuffer not supported in this browser");
            if (f._useTypedArrays)
              return new f(this).buffer;
            for (var e3 = new Uint8Array(this.length), t2 = 0, n2 = e3.length; t2 < n2; t2 += 1)
              e3[t2] = this[t2];
            return e3.buffer;
          };
          var t = f.prototype;
          function S(e3, t2, n2) {
            return "number" != typeof e3 ? n2 : t2 <= (e3 = ~~e3) ? t2 : 0 <= e3 || 0 <= (e3 += t2) ? e3 : 0;
          }
          function j(e3) {
            return (e3 = ~~Math.ceil(+e3)) < 0 ? 0 : e3;
          }
          function C(e3) {
            return (Array.isArray || function(e4) {
              return "[object Array]" === Object.prototype.toString.call(e4);
            })(e3);
          }
          function k(e3) {
            return e3 < 16 ? "0" + e3.toString(16) : e3.toString(16);
          }
          function T(e3) {
            for (var t2 = [], n2 = 0; n2 < e3.length; n2++) {
              var r2 = e3.charCodeAt(n2);
              if (r2 <= 127)
                t2.push(e3.charCodeAt(n2));
              else
                for (var o2 = n2, i2 = (55296 <= r2 && r2 <= 57343 && n2++, encodeURIComponent(e3.slice(o2, n2 + 1)).substr(1).split("%")), u2 = 0; u2 < i2.length; u2++)
                  t2.push(parseInt(i2[u2], 16));
            }
            return t2;
          }
          function M(e3) {
            return a.toByteArray(e3);
          }
          function c(e3, t2, n2, r2) {
            for (var o2 = 0; o2 < r2 && !(o2 + n2 >= t2.length || o2 >= e3.length); o2++)
              t2[o2 + n2] = e3[o2];
            return o2;
          }
          function N(e3) {
            try {
              return decodeURIComponent(e3);
            } catch (e4) {
              return String.fromCharCode(65533);
            }
          }
          function Y(e3, t2) {
            d("number" == typeof e3, "cannot write a non-number as a number"), d(0 <= e3, "specified a negative value for writing an unsigned value"), d(e3 <= t2, "value is larger than maximum value for type"), d(Math.floor(e3) === e3, "value has a fractional component");
          }
          function F(e3, t2, n2) {
            d("number" == typeof e3, "cannot write a non-number as a number"), d(e3 <= t2, "value larger than maximum allowed value"), d(n2 <= e3, "value smaller than minimum allowed value"), d(Math.floor(e3) === e3, "value has a fractional component");
          }
          function D(e3, t2, n2) {
            d("number" == typeof e3, "cannot write a non-number as a number"), d(e3 <= t2, "value larger than maximum allowed value"), d(n2 <= e3, "value smaller than minimum allowed value");
          }
          function d(e3, t2) {
            if (!e3)
              throw new Error(t2 || "Failed assertion");
          }
          f._augment = function(e3) {
            return e3._isBuffer = true, e3._get = e3.get, e3._set = e3.set, e3.get = t.get, e3.set = t.set, e3.write = t.write, e3.toString = t.toString, e3.toLocaleString = t.toString, e3.toJSON = t.toJSON, e3.copy = t.copy, e3.slice = t.slice, e3.readUInt8 = t.readUInt8, e3.readUInt16LE = t.readUInt16LE, e3.readUInt16BE = t.readUInt16BE, e3.readUInt32LE = t.readUInt32LE, e3.readUInt32BE = t.readUInt32BE, e3.readInt8 = t.readInt8, e3.readInt16LE = t.readInt16LE, e3.readInt16BE = t.readInt16BE, e3.readInt32LE = t.readInt32LE, e3.readInt32BE = t.readInt32BE, e3.readFloatLE = t.readFloatLE, e3.readFloatBE = t.readFloatBE, e3.readDoubleLE = t.readDoubleLE, e3.readDoubleBE = t.readDoubleBE, e3.writeUInt8 = t.writeUInt8, e3.writeUInt16LE = t.writeUInt16LE, e3.writeUInt16BE = t.writeUInt16BE, e3.writeUInt32LE = t.writeUInt32LE, e3.writeUInt32BE = t.writeUInt32BE, e3.writeInt8 = t.writeInt8, e3.writeInt16LE = t.writeInt16LE, e3.writeInt16BE = t.writeInt16BE, e3.writeInt32LE = t.writeInt32LE, e3.writeInt32BE = t.writeInt32BE, e3.writeFloatLE = t.writeFloatLE, e3.writeFloatBE = t.writeFloatBE, e3.writeDoubleLE = t.writeDoubleLE, e3.writeDoubleBE = t.writeDoubleBE, e3.fill = t.fill, e3.inspect = t.inspect, e3.toArrayBuffer = t.toArrayBuffer, e3;
          };
        }.call(this, O("lYpoI2"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, O("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/node_modules/gulp-browserify/node_modules/buffer/index.js", "/node_modules/gulp-browserify/node_modules/buffer");
      }, { "base64-js": 2, buffer: 3, ieee754: 10, lYpoI2: 11 }], 4: [function(c, d, e) {
        !function(e2, t, a, n, r, o, i, u, s) {
          var a = c("buffer").Buffer, f = 4, l = new a(f);
          l.fill(0);
          d.exports = { hash: function(e3, t2, n2, r2) {
            for (var o2 = t2(function(e4, t3) {
              e4.length % f != 0 && (n3 = e4.length + (f - e4.length % f), e4 = a.concat([e4, l], n3));
              for (var n3, r3 = [], o3 = t3 ? e4.readInt32BE : e4.readInt32LE, i3 = 0; i3 < e4.length; i3 += f)
                r3.push(o3.call(e4, i3));
              return r3;
            }(e3 = a.isBuffer(e3) ? e3 : new a(e3), r2), 8 * e3.length), t2 = r2, i2 = new a(n2), u2 = t2 ? i2.writeInt32BE : i2.writeInt32LE, s2 = 0; s2 < o2.length; s2++)
              u2.call(i2, o2[s2], 4 * s2, true);
            return i2;
          } };
        }.call(this, c("lYpoI2"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, c("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/node_modules/gulp-browserify/node_modules/crypto-browserify/helpers.js", "/node_modules/gulp-browserify/node_modules/crypto-browserify");
      }, { buffer: 3, lYpoI2: 11 }], 5: [function(v, e, _) {
        !function(l, c, u, d, h, p, g, y, w) {
          var u = v("buffer").Buffer, e2 = v("./sha"), t = v("./sha256"), n = v("./rng"), b = { sha1: e2, sha256: t, md5: v("./md5") }, s = 64, a = new u(s);
          function r(e3, n2) {
            var r2 = b[e3 = e3 || "sha1"], o2 = [];
            return r2 || i("algorithm:", e3, "is not yet supported"), { update: function(e4) {
              return u.isBuffer(e4) || (e4 = new u(e4)), o2.push(e4), e4.length, this;
            }, digest: function(e4) {
              var t2 = u.concat(o2), t2 = n2 ? function(e5, t3, n3) {
                u.isBuffer(t3) || (t3 = new u(t3)), u.isBuffer(n3) || (n3 = new u(n3)), t3.length > s ? t3 = e5(t3) : t3.length < s && (t3 = u.concat([t3, a], s));
                for (var r3 = new u(s), o3 = new u(s), i2 = 0; i2 < s; i2++)
                  r3[i2] = 54 ^ t3[i2], o3[i2] = 92 ^ t3[i2];
                return n3 = e5(u.concat([r3, n3])), e5(u.concat([o3, n3]));
              }(r2, n2, t2) : r2(t2);
              return o2 = null, e4 ? t2.toString(e4) : t2;
            } };
          }
          function i() {
            var e3 = [].slice.call(arguments).join(" ");
            throw new Error([e3, "we accept pull requests", "http://github.com/dominictarr/crypto-browserify"].join("\n"));
          }
          a.fill(0), _.createHash = function(e3) {
            return r(e3);
          }, _.createHmac = r, _.randomBytes = function(e3, t2) {
            if (!t2 || !t2.call)
              return new u(n(e3));
            try {
              t2.call(this, void 0, new u(n(e3)));
            } catch (e4) {
              t2(e4);
            }
          };
          var o, f = ["createCredentials", "createCipher", "createCipheriv", "createDecipher", "createDecipheriv", "createSign", "createVerify", "createDiffieHellman", "pbkdf2"], m = function(e3) {
            _[e3] = function() {
              i("sorry,", e3, "is not implemented yet");
            };
          };
          for (o in f)
            m(f[o]);
        }.call(this, v("lYpoI2"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, v("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/node_modules/gulp-browserify/node_modules/crypto-browserify/index.js", "/node_modules/gulp-browserify/node_modules/crypto-browserify");
      }, { "./md5": 6, "./rng": 7, "./sha": 8, "./sha256": 9, buffer: 3, lYpoI2: 11 }], 6: [function(w, b, e) {
        !function(e2, r, o, i, u, a, f, l, y) {
          var t = w("./helpers");
          function n(e3, t2) {
            e3[t2 >> 5] |= 128 << t2 % 32, e3[14 + (t2 + 64 >>> 9 << 4)] = t2;
            for (var n2 = 1732584193, r2 = -271733879, o2 = -1732584194, i2 = 271733878, u2 = 0; u2 < e3.length; u2 += 16) {
              var s2 = n2, a2 = r2, f2 = o2, l2 = i2, n2 = c(n2, r2, o2, i2, e3[u2 + 0], 7, -680876936), i2 = c(i2, n2, r2, o2, e3[u2 + 1], 12, -389564586), o2 = c(o2, i2, n2, r2, e3[u2 + 2], 17, 606105819), r2 = c(r2, o2, i2, n2, e3[u2 + 3], 22, -1044525330);
              n2 = c(n2, r2, o2, i2, e3[u2 + 4], 7, -176418897), i2 = c(i2, n2, r2, o2, e3[u2 + 5], 12, 1200080426), o2 = c(o2, i2, n2, r2, e3[u2 + 6], 17, -1473231341), r2 = c(r2, o2, i2, n2, e3[u2 + 7], 22, -45705983), n2 = c(n2, r2, o2, i2, e3[u2 + 8], 7, 1770035416), i2 = c(i2, n2, r2, o2, e3[u2 + 9], 12, -1958414417), o2 = c(o2, i2, n2, r2, e3[u2 + 10], 17, -42063), r2 = c(r2, o2, i2, n2, e3[u2 + 11], 22, -1990404162), n2 = c(n2, r2, o2, i2, e3[u2 + 12], 7, 1804603682), i2 = c(i2, n2, r2, o2, e3[u2 + 13], 12, -40341101), o2 = c(o2, i2, n2, r2, e3[u2 + 14], 17, -1502002290), n2 = d(n2, r2 = c(r2, o2, i2, n2, e3[u2 + 15], 22, 1236535329), o2, i2, e3[u2 + 1], 5, -165796510), i2 = d(i2, n2, r2, o2, e3[u2 + 6], 9, -1069501632), o2 = d(o2, i2, n2, r2, e3[u2 + 11], 14, 643717713), r2 = d(r2, o2, i2, n2, e3[u2 + 0], 20, -373897302), n2 = d(n2, r2, o2, i2, e3[u2 + 5], 5, -701558691), i2 = d(i2, n2, r2, o2, e3[u2 + 10], 9, 38016083), o2 = d(o2, i2, n2, r2, e3[u2 + 15], 14, -660478335), r2 = d(r2, o2, i2, n2, e3[u2 + 4], 20, -405537848), n2 = d(n2, r2, o2, i2, e3[u2 + 9], 5, 568446438), i2 = d(i2, n2, r2, o2, e3[u2 + 14], 9, -1019803690), o2 = d(o2, i2, n2, r2, e3[u2 + 3], 14, -187363961), r2 = d(r2, o2, i2, n2, e3[u2 + 8], 20, 1163531501), n2 = d(n2, r2, o2, i2, e3[u2 + 13], 5, -1444681467), i2 = d(i2, n2, r2, o2, e3[u2 + 2], 9, -51403784), o2 = d(o2, i2, n2, r2, e3[u2 + 7], 14, 1735328473), n2 = h(n2, r2 = d(r2, o2, i2, n2, e3[u2 + 12], 20, -1926607734), o2, i2, e3[u2 + 5], 4, -378558), i2 = h(i2, n2, r2, o2, e3[u2 + 8], 11, -2022574463), o2 = h(o2, i2, n2, r2, e3[u2 + 11], 16, 1839030562), r2 = h(r2, o2, i2, n2, e3[u2 + 14], 23, -35309556), n2 = h(n2, r2, o2, i2, e3[u2 + 1], 4, -1530992060), i2 = h(i2, n2, r2, o2, e3[u2 + 4], 11, 1272893353), o2 = h(o2, i2, n2, r2, e3[u2 + 7], 16, -155497632), r2 = h(r2, o2, i2, n2, e3[u2 + 10], 23, -1094730640), n2 = h(n2, r2, o2, i2, e3[u2 + 13], 4, 681279174), i2 = h(i2, n2, r2, o2, e3[u2 + 0], 11, -358537222), o2 = h(o2, i2, n2, r2, e3[u2 + 3], 16, -722521979), r2 = h(r2, o2, i2, n2, e3[u2 + 6], 23, 76029189), n2 = h(n2, r2, o2, i2, e3[u2 + 9], 4, -640364487), i2 = h(i2, n2, r2, o2, e3[u2 + 12], 11, -421815835), o2 = h(o2, i2, n2, r2, e3[u2 + 15], 16, 530742520), n2 = p(n2, r2 = h(r2, o2, i2, n2, e3[u2 + 2], 23, -995338651), o2, i2, e3[u2 + 0], 6, -198630844), i2 = p(i2, n2, r2, o2, e3[u2 + 7], 10, 1126891415), o2 = p(o2, i2, n2, r2, e3[u2 + 14], 15, -1416354905), r2 = p(r2, o2, i2, n2, e3[u2 + 5], 21, -57434055), n2 = p(n2, r2, o2, i2, e3[u2 + 12], 6, 1700485571), i2 = p(i2, n2, r2, o2, e3[u2 + 3], 10, -1894986606), o2 = p(o2, i2, n2, r2, e3[u2 + 10], 15, -1051523), r2 = p(r2, o2, i2, n2, e3[u2 + 1], 21, -2054922799), n2 = p(n2, r2, o2, i2, e3[u2 + 8], 6, 1873313359), i2 = p(i2, n2, r2, o2, e3[u2 + 15], 10, -30611744), o2 = p(o2, i2, n2, r2, e3[u2 + 6], 15, -1560198380), r2 = p(r2, o2, i2, n2, e3[u2 + 13], 21, 1309151649), n2 = p(n2, r2, o2, i2, e3[u2 + 4], 6, -145523070), i2 = p(i2, n2, r2, o2, e3[u2 + 11], 10, -1120210379), o2 = p(o2, i2, n2, r2, e3[u2 + 2], 15, 718787259), r2 = p(r2, o2, i2, n2, e3[u2 + 9], 21, -343485551), n2 = g(n2, s2), r2 = g(r2, a2), o2 = g(o2, f2), i2 = g(i2, l2);
            }
            return Array(n2, r2, o2, i2);
          }
          function s(e3, t2, n2, r2, o2, i2) {
            return g((t2 = g(g(t2, e3), g(r2, i2))) << o2 | t2 >>> 32 - o2, n2);
          }
          function c(e3, t2, n2, r2, o2, i2, u2) {
            return s(t2 & n2 | ~t2 & r2, e3, t2, o2, i2, u2);
          }
          function d(e3, t2, n2, r2, o2, i2, u2) {
            return s(t2 & r2 | n2 & ~r2, e3, t2, o2, i2, u2);
          }
          function h(e3, t2, n2, r2, o2, i2, u2) {
            return s(t2 ^ n2 ^ r2, e3, t2, o2, i2, u2);
          }
          function p(e3, t2, n2, r2, o2, i2, u2) {
            return s(n2 ^ (t2 | ~r2), e3, t2, o2, i2, u2);
          }
          function g(e3, t2) {
            var n2 = (65535 & e3) + (65535 & t2);
            return (e3 >> 16) + (t2 >> 16) + (n2 >> 16) << 16 | 65535 & n2;
          }
          b.exports = function(e3) {
            return t.hash(e3, n, 16);
          };
        }.call(this, w("lYpoI2"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, w("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/node_modules/gulp-browserify/node_modules/crypto-browserify/md5.js", "/node_modules/gulp-browserify/node_modules/crypto-browserify");
      }, { "./helpers": 4, buffer: 3, lYpoI2: 11 }], 7: [function(e, l, t) {
        !function(e2, t2, n, r, o, i, u, s, f) {
          l.exports = function(e3) {
            for (var t3, n2 = new Array(e3), r2 = 0; r2 < e3; r2++)
              0 == (3 & r2) && (t3 = 4294967296 * Math.random()), n2[r2] = t3 >>> ((3 & r2) << 3) & 255;
            return n2;
          };
        }.call(this, e("lYpoI2"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/node_modules/gulp-browserify/node_modules/crypto-browserify/rng.js", "/node_modules/gulp-browserify/node_modules/crypto-browserify");
      }, { buffer: 3, lYpoI2: 11 }], 8: [function(c, d, e) {
        !function(e2, t, n, r, o, s, a, f, l) {
          var i = c("./helpers");
          function u(l2, c2) {
            l2[c2 >> 5] |= 128 << 24 - c2 % 32, l2[15 + (c2 + 64 >> 9 << 4)] = c2;
            for (var e3, t2, n2, r2 = Array(80), o2 = 1732584193, i2 = -271733879, u2 = -1732584194, s2 = 271733878, d2 = -1009589776, h = 0; h < l2.length; h += 16) {
              for (var p = o2, g = i2, y = u2, w = s2, b = d2, a2 = 0; a2 < 80; a2++) {
                r2[a2] = a2 < 16 ? l2[h + a2] : v(r2[a2 - 3] ^ r2[a2 - 8] ^ r2[a2 - 14] ^ r2[a2 - 16], 1);
                var f2 = m(m(v(o2, 5), (f2 = i2, t2 = u2, n2 = s2, (e3 = a2) < 20 ? f2 & t2 | ~f2 & n2 : !(e3 < 40) && e3 < 60 ? f2 & t2 | f2 & n2 | t2 & n2 : f2 ^ t2 ^ n2)), m(m(d2, r2[a2]), (e3 = a2) < 20 ? 1518500249 : e3 < 40 ? 1859775393 : e3 < 60 ? -1894007588 : -899497514)), d2 = s2, s2 = u2, u2 = v(i2, 30), i2 = o2, o2 = f2;
              }
              o2 = m(o2, p), i2 = m(i2, g), u2 = m(u2, y), s2 = m(s2, w), d2 = m(d2, b);
            }
            return Array(o2, i2, u2, s2, d2);
          }
          function m(e3, t2) {
            var n2 = (65535 & e3) + (65535 & t2);
            return (e3 >> 16) + (t2 >> 16) + (n2 >> 16) << 16 | 65535 & n2;
          }
          function v(e3, t2) {
            return e3 << t2 | e3 >>> 32 - t2;
          }
          d.exports = function(e3) {
            return i.hash(e3, u, 20, true);
          };
        }.call(this, c("lYpoI2"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, c("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/node_modules/gulp-browserify/node_modules/crypto-browserify/sha.js", "/node_modules/gulp-browserify/node_modules/crypto-browserify");
      }, { "./helpers": 4, buffer: 3, lYpoI2: 11 }], 9: [function(c, d, e) {
        !function(e2, t, n, r, u, s, a, f, l) {
          function b(e3, t2) {
            var n2 = (65535 & e3) + (65535 & t2);
            return (e3 >> 16) + (t2 >> 16) + (n2 >> 16) << 16 | 65535 & n2;
          }
          function o(e3, l2) {
            var c2, d2 = new Array(1116352408, 1899447441, 3049323471, 3921009573, 961987163, 1508970993, 2453635748, 2870763221, 3624381080, 310598401, 607225278, 1426881987, 1925078388, 2162078206, 2614888103, 3248222580, 3835390401, 4022224774, 264347078, 604807628, 770255983, 1249150122, 1555081692, 1996064986, 2554220882, 2821834349, 2952996808, 3210313671, 3336571891, 3584528711, 113926993, 338241895, 666307205, 773529912, 1294757372, 1396182291, 1695183700, 1986661051, 2177026350, 2456956037, 2730485921, 2820302411, 3259730800, 3345764771, 3516065817, 3600352804, 4094571909, 275423344, 430227734, 506948616, 659060556, 883997877, 958139571, 1322822218, 1537002063, 1747873779, 1955562222, 2024104815, 2227730452, 2361852424, 2428436474, 2756734187, 3204031479, 3329325298), t2 = new Array(1779033703, 3144134277, 1013904242, 2773480762, 1359893119, 2600822924, 528734635, 1541459225), n2 = new Array(64);
            e3[l2 >> 5] |= 128 << 24 - l2 % 32, e3[15 + (l2 + 64 >> 9 << 4)] = l2;
            for (var r2, o2, h = 0; h < e3.length; h += 16) {
              for (var i2 = t2[0], u2 = t2[1], s2 = t2[2], p = t2[3], a2 = t2[4], g = t2[5], y = t2[6], w = t2[7], f2 = 0; f2 < 64; f2++)
                n2[f2] = f2 < 16 ? e3[f2 + h] : b(b(b((o2 = n2[f2 - 2], m(o2, 17) ^ m(o2, 19) ^ v(o2, 10)), n2[f2 - 7]), (o2 = n2[f2 - 15], m(o2, 7) ^ m(o2, 18) ^ v(o2, 3))), n2[f2 - 16]), c2 = b(b(b(b(w, m(o2 = a2, 6) ^ m(o2, 11) ^ m(o2, 25)), a2 & g ^ ~a2 & y), d2[f2]), n2[f2]), r2 = b(m(r2 = i2, 2) ^ m(r2, 13) ^ m(r2, 22), i2 & u2 ^ i2 & s2 ^ u2 & s2), w = y, y = g, g = a2, a2 = b(p, c2), p = s2, s2 = u2, u2 = i2, i2 = b(c2, r2);
              t2[0] = b(i2, t2[0]), t2[1] = b(u2, t2[1]), t2[2] = b(s2, t2[2]), t2[3] = b(p, t2[3]), t2[4] = b(a2, t2[4]), t2[5] = b(g, t2[5]), t2[6] = b(y, t2[6]), t2[7] = b(w, t2[7]);
            }
            return t2;
          }
          var i = c("./helpers"), m = function(e3, t2) {
            return e3 >>> t2 | e3 << 32 - t2;
          }, v = function(e3, t2) {
            return e3 >>> t2;
          };
          d.exports = function(e3) {
            return i.hash(e3, o, 32, true);
          };
        }.call(this, c("lYpoI2"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, c("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/node_modules/gulp-browserify/node_modules/crypto-browserify/sha256.js", "/node_modules/gulp-browserify/node_modules/crypto-browserify");
      }, { "./helpers": 4, buffer: 3, lYpoI2: 11 }], 10: [function(e, t, f) {
        !function(e2, t2, n, r, o, i, u, s, a) {
          f.read = function(e3, t3, n2, r2, o2) {
            var i2, u2, l = 8 * o2 - r2 - 1, c = (1 << l) - 1, d = c >> 1, s2 = -7, a2 = n2 ? o2 - 1 : 0, f2 = n2 ? -1 : 1, o2 = e3[t3 + a2];
            for (a2 += f2, i2 = o2 & (1 << -s2) - 1, o2 >>= -s2, s2 += l; 0 < s2; i2 = 256 * i2 + e3[t3 + a2], a2 += f2, s2 -= 8)
              ;
            for (u2 = i2 & (1 << -s2) - 1, i2 >>= -s2, s2 += r2; 0 < s2; u2 = 256 * u2 + e3[t3 + a2], a2 += f2, s2 -= 8)
              ;
            if (0 === i2)
              i2 = 1 - d;
            else {
              if (i2 === c)
                return u2 ? NaN : 1 / 0 * (o2 ? -1 : 1);
              u2 += Math.pow(2, r2), i2 -= d;
            }
            return (o2 ? -1 : 1) * u2 * Math.pow(2, i2 - r2);
          }, f.write = function(e3, t3, l, n2, r2, c) {
            var o2, i2, u2 = 8 * c - r2 - 1, s2 = (1 << u2) - 1, a2 = s2 >> 1, d = 23 === r2 ? Math.pow(2, -24) - Math.pow(2, -77) : 0, f2 = n2 ? 0 : c - 1, h = n2 ? 1 : -1, c = t3 < 0 || 0 === t3 && 1 / t3 < 0 ? 1 : 0;
            for (t3 = Math.abs(t3), isNaN(t3) || t3 === 1 / 0 ? (i2 = isNaN(t3) ? 1 : 0, o2 = s2) : (o2 = Math.floor(Math.log(t3) / Math.LN2), t3 * (n2 = Math.pow(2, -o2)) < 1 && (o2--, n2 *= 2), 2 <= (t3 += 1 <= o2 + a2 ? d / n2 : d * Math.pow(2, 1 - a2)) * n2 && (o2++, n2 /= 2), s2 <= o2 + a2 ? (i2 = 0, o2 = s2) : 1 <= o2 + a2 ? (i2 = (t3 * n2 - 1) * Math.pow(2, r2), o2 += a2) : (i2 = t3 * Math.pow(2, a2 - 1) * Math.pow(2, r2), o2 = 0)); 8 <= r2; e3[l + f2] = 255 & i2, f2 += h, i2 /= 256, r2 -= 8)
              ;
            for (o2 = o2 << r2 | i2, u2 += r2; 0 < u2; e3[l + f2] = 255 & o2, f2 += h, o2 /= 256, u2 -= 8)
              ;
            e3[l + f2 - h] |= 128 * c;
          };
        }.call(this, e("lYpoI2"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/node_modules/gulp-browserify/node_modules/ieee754/index.js", "/node_modules/gulp-browserify/node_modules/ieee754");
      }, { buffer: 3, lYpoI2: 11 }], 11: [function(e, h, t) {
        !function(e2, t2, n, r, o, f, l, c, d) {
          var i, u, s;
          function a() {
          }
          (e2 = h.exports = {}).nextTick = (u = "undefined" != typeof window && window.setImmediate, s = "undefined" != typeof window && window.postMessage && window.addEventListener, u ? function(e3) {
            return window.setImmediate(e3);
          } : s ? (i = [], window.addEventListener("message", function(e3) {
            var t3 = e3.source;
            t3 !== window && null !== t3 || "process-tick" !== e3.data || (e3.stopPropagation(), 0 < i.length && i.shift()());
          }, true), function(e3) {
            i.push(e3), window.postMessage("process-tick", "*");
          }) : function(e3) {
            setTimeout(e3, 0);
          }), e2.title = "browser", e2.browser = true, e2.env = {}, e2.argv = [], e2.on = a, e2.addListener = a, e2.once = a, e2.off = a, e2.removeListener = a, e2.removeAllListeners = a, e2.emit = a, e2.binding = function(e3) {
            throw new Error("process.binding is not supported");
          }, e2.cwd = function() {
            return "/";
          }, e2.chdir = function(e3) {
            throw new Error("process.chdir is not supported");
          };
        }.call(this, e("lYpoI2"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/node_modules/gulp-browserify/node_modules/process/browser.js", "/node_modules/gulp-browserify/node_modules/process");
      }, { buffer: 3, lYpoI2: 11 }] }, {}, [1])(1);
    });
  })(object_hash);
  var object_hashExports = object_hash.exports;
  const hash = /* @__PURE__ */ getDefaultExportFromCjs(object_hashExports);
  function bind(fn, thisArg) {
    return function wrap() {
      return fn.apply(thisArg, arguments);
    };
  }
  const { toString } = Object.prototype;
  const { getPrototypeOf } = Object;
  const kindOf = ((cache2) => (thing) => {
    const str = toString.call(thing);
    return cache2[str] || (cache2[str] = str.slice(8, -1).toLowerCase());
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
  const isObject = (thing) => thing !== null && typeof thing === "object";
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
  const isStream = (val) => isObject(val) && isFunction(val.pipe);
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
      const keys2 = allOwnKeys ? Object.getOwnPropertyNames(obj) : Object.keys(obj);
      const len = keys2.length;
      let key;
      for (i = 0; i < len; i++) {
        key = keys2[i];
        fn.call(null, obj[key], key, obj);
      }
    }
  }
  function findKey(obj, key) {
    key = key.toLowerCase();
    const keys2 = Object.keys(obj);
    let i = keys2.length;
    let _key;
    while (i-- > 0) {
      _key = keys2[i];
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
    const assignValue2 = (val, key) => {
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
      arguments[i] && forEach(arguments[i], assignValue2);
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
  const hasOwnProperty = (({ hasOwnProperty: hasOwnProperty2 }) => (obj, prop) => hasOwnProperty2.call(obj, prop))(Object.prototype);
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
      if (isObject(source)) {
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
  const isThenable = (thing) => thing && (isObject(thing) || isFunction(thing)) && isFunction(thing.then) && isFunction(thing.catch);
  const utils = {
    isArray,
    isArrayBuffer,
    isBuffer,
    isFormData,
    isArrayBufferView,
    isString,
    isNumber,
    isBoolean,
    isObject,
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
    hasOwnProperty,
    hasOwnProp: hasOwnProperty,
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
    const keys2 = Object.keys(arr);
    let i;
    const len = keys2.length;
    let key;
    for (i = 0; i < len; i++) {
      key = keys2[i];
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
      const keys2 = Object.keys(this);
      let i = keys2.length;
      let deleted = false;
      while (i--) {
        const key = keys2[i];
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
      const loaded = e.loaded;
      const total = e.lengthComputable ? e.total : void 0;
      const progressBytes = loaded - bytesNotified;
      const rate = _speedometer(progressBytes);
      const inRange = loaded <= total;
      bytesNotified = loaded;
      const data = {
        loaded,
        total,
        progress: total ? loaded / total : void 0,
        bytes: progressBytes,
        rate: rate ? rate : void 0,
        estimated: rate && total && inRange ? (total - loaded) / rate : void 0,
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
    const keys2 = Object.keys(options);
    let i = keys2.length;
    while (i-- > 0) {
      const opt = keys2[i];
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
  const getWPPageSelectorData = function(type, subtype, page, search, include) {
    return ZionService.get("v1/page-selector-data", {
      params: {
        type,
        subtype,
        page,
        search,
        include
      }
    });
  };
  const getTerms = function(params) {
    return ZionService.get("v1/terms", {
      params
    });
  };
  const cache = vue.ref({});
  const items = vue.ref({});
  function usePageSelectorData() {
    function getItem(config, id) {
      const { type, subtype } = config;
      const cachedItems = get(items.value, `${type}.${subtype}`, []);
      return cachedItems.find((item) => item.id === id);
    }
    function getItems(config, defaultValue = []) {
      const { type, subtype } = config;
      return get(items.value, `${type}.${subtype}`, defaultValue);
    }
    function fetch(config, page = 1, search = "", include = null) {
      const cacheKey = generateCacheKey(__spreadProps(__spreadValues({}, config), {
        page,
        search
      }));
      const { type, subtype } = config;
      if (cache[cacheKey]) {
        return Promise.resolve(cache[cacheKey]);
      } else {
        return getWPPageSelectorData(type, subtype, page, search, include).then((response) => {
          saveItems(type, subtype, response.data);
          addToCache(cacheKey, response.data);
          return response.data;
        });
      }
    }
    function saveItems(type, subtype, newItems) {
      const existingItems = get(items.value, `${type}.${subtype}`, []);
      set(items.value, `${type}.${subtype}`, unionBy$1(existingItems, newItems, "id"));
    }
    function generateCacheKey(data) {
      return hash(data);
    }
    function addToCache(cacheKey, cacheData) {
      cache[cacheKey] = cacheData;
    }
    return {
      fetch,
      getItems,
      getItem
    };
  }
  const _hoisted_1$5 = { class: "znpb-form__input-title" };
  const _hoisted_2$3 = { class: "znpbpro-dynamicSourceTypeSelector znpbpro-dynamicSourceDropdown" };
  const _hoisted_3$2 = { key: 1 };
  const _hoisted_4 = { class: "znpbpro-dynamicSourceTypeSelectorListWrapper" };
  const _hoisted_5 = ["onClick"];
  const _hoisted_6 = {
    key: 1,
    class: "znpb-optionWPPageSelectorItemNoMore"
  };
  const _sfc_main$d = /* @__PURE__ */ vue.defineComponent({
    __name: "SourceType",
    props: {
      options: { default: () => [] },
      modelValue: {},
      optionTitle: {},
      searchable: { type: Boolean, default: false },
      searchData: { default: () => ({}) }
    },
    emits: ["update:modelValue"],
    setup(__props, { emit }) {
      const props = __props;
      const searchKeyword = vue.ref("");
      const showDropdown = vue.ref(false);
      const loading = vue.ref(false);
      const loadingTitle = vue.ref(props.searchable);
      const stopSearch = vue.ref(false);
      let page = 1;
      const { fetch, getItems, getItem } = usePageSelectorData();
      const items2 = vue.computed(() => {
        let items22;
        if (props.searchable) {
          items22 = getItems(props.searchData);
          if (searchKeyword.value.length > 0) {
            items22 = items22.filter((item) => item.name.indexOf(searchKeyword.value) !== -1);
          }
        } else {
          items22 = props.options;
        }
        return items22;
      });
      vue.watch(
        () => props.searchData,
        () => {
          stopSearch.value = false;
          searchKeyword.value = "";
          debouncedGetItems();
        }
      );
      vue.watch(searchKeyword, (newValue) => {
        if (newValue.length > 0) {
          stopSearch.value = false;
          debouncedGetItems();
        }
      });
      const debouncedGetItems = debounce(() => {
        loadNext();
      }, 300);
      function loadNext() {
        loading.value = true;
        loadingTitle.value = true;
        const include = props.modelValue;
        fetch(props.searchData, page, searchKeyword.value, include).then((response) => {
          if (response.length < 25) {
            stopSearch.value = true;
          }
          loading.value = false;
          loadingTitle.value = false;
        });
      }
      function onScrollEnd() {
        if (!props.searchable) {
          return;
        }
        console.log(stopSearch.value);
        if (!stopSearch.value) {
          page++;
          loadNext();
        }
      }
      if (props.searchable) {
        loadNext();
      }
      const dropdownPlaceholder = vue.computed(() => {
        if (typeof props.modelValue === "undefined") {
          return props.optionTitle;
        } else {
          if (props.searchable) {
            const selectedItem = getItem(props.searchData, props.modelValue);
            return selectedItem ? selectedItem.name : props.optionTitle;
          } else {
            const activeTitle = props.options.find((option) => option.id === props.modelValue);
            return activeTitle ? activeTitle.name : props.optionTitle;
          }
        }
      });
      function onOptionSelect(option) {
        emit("update:modelValue", option);
        showDropdown.value = false;
      }
      return (_ctx, _cache) => {
        const _component_Loader = vue.resolveComponent("Loader");
        const _component_Icon = vue.resolveComponent("Icon");
        const _component_BaseInput = vue.resolveComponent("BaseInput");
        const _component_ListScroll = vue.resolveComponent("ListScroll");
        const _component_Tooltip = vue.resolveComponent("Tooltip");
        return vue.openBlock(), vue.createBlock(_component_Tooltip, {
          show: showDropdown.value,
          "onUpdate:show": _cache[1] || (_cache[1] = ($event) => showDropdown.value = $event),
          placement: "bottom",
          trigger: "click",
          "close-on-outside-click": true,
          "tooltip-class": "znpbpro-dynamicSourceDropdownTooltip znpb-fancy-scrollbar",
          class: "znpbpro-dynamicSourceDropdownWrapper"
        }, {
          content: vue.withCtx(() => [
            vue.createElementVNode("div", _hoisted_4, [
              _ctx.searchable ? (vue.openBlock(), vue.createBlock(_component_BaseInput, {
                key: 0,
                ref: "searchInput",
                modelValue: searchKeyword.value,
                "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => searchKeyword.value = $event),
                class: "znpb-dynamicSourceTypeSearch",
                placeholder: i18n__namespace.__("Search", "zionbuilder"),
                clearable: true,
                icon: "search",
                autocomplete: "off"
              }, null, 8, ["modelValue", "placeholder"])) : vue.createCommentVNode("", true),
              vue.createVNode(_component_ListScroll, {
                loading: loading.value,
                class: "znpbpro-dynamicSourceListScroll",
                onScrollEnd
              }, {
                default: vue.withCtx(() => [
                  (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(items2.value, (option) => {
                    return vue.openBlock(), vue.createElementBlock("div", {
                      key: option.id,
                      class: vue.normalizeClass(["znpbpro-dynamicSourceTypeSelectorListItem", { "znpbpro-dynamicSourceTypeSelectorListItem--active": _ctx.modelValue === option.id }]),
                      onClick: ($event) => onOptionSelect(option.id)
                    }, vue.toDisplayString(option.name), 11, _hoisted_5);
                  }), 128))
                ]),
                _: 1
              }, 8, ["loading"]),
              stopSearch.value ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_6, vue.toDisplayString(i18n__namespace.__("No more items", "zionbuilder-pro")), 1)) : vue.createCommentVNode("", true)
            ])
          ]),
          default: vue.withCtx(() => [
            vue.createElementVNode("div", _hoisted_1$5, vue.toDisplayString(_ctx.optionTitle), 1),
            vue.createElementVNode("div", _hoisted_2$3, [
              loadingTitle.value ? (vue.openBlock(), vue.createBlock(_component_Loader, {
                key: 0,
                size: 14
              })) : (vue.openBlock(), vue.createElementBlock("span", _hoisted_3$2, vue.toDisplayString(dropdownPlaceholder.value), 1)),
              vue.createVNode(_component_Icon, {
                icon: "select",
                class: "znpb-optionWPPageSelectorIcon"
              })
            ])
          ]),
          _: 1
        }, 8, ["show"]);
      };
    }
  });
  const SourceType_vue_vue_type_style_index_0_lang = "";
  const _sfc_main$c = /* @__PURE__ */ vue.defineComponent({
    __name: "WPPageSelector",
    props: {
      modelValue: { default: () => ({}) }
    },
    emits: ["update:modelValue"],
    setup(__props, { emit }) {
      const props = __props;
      vue.ref(false);
      const dataSetsStore = store.useDataSetsStore();
      const sourceTypes = [
        {
          name: "Current page query",
          id: "current_page_query"
        },
        {
          name: "Single",
          id: "single",
          hasSubtype: true,
          subTypeTitle: i18n__namespace.__("Select post type", "zionbuilder-pro"),
          hasIdSelector: true,
          idSelectorTitle: i18n__namespace.__("Select post", "zionbuilder-pro")
        },
        {
          name: "Post type archive",
          id: "archive",
          hasSubtype: true,
          subTypeTitle: i18n__namespace.__("Select post type", "zionbuilder-pro")
        },
        {
          name: "Taxonomy archive",
          id: "taxonomy_archive",
          hasSubtype: true,
          subTypeTitle: i18n__namespace.__("Select taxonomy", "zionbuilder-pro"),
          hasIdSelector: true,
          idSelectorTitle: i18n__namespace.__("Select taxonomy", "zionbuilder-pro")
        }
      ];
      const sourceTypeModel = vue.computed({
        get() {
          return props.modelValue.type || "current_page_query";
        },
        set(newValue) {
          if (newValue === sourceTypeModel.value) {
            return;
          }
          if (newValue === "current_page_query") {
            emit("update:modelValue", {
              type: "current_page_query"
            });
          } else {
            emit("update:modelValue", {
              type: newValue
            });
          }
          hooks.doAction("zionbuilder/server_component/refresh");
        }
      });
      const activeSourceConfig = vue.computed(() => {
        return sourceTypes.find((source) => source.id === sourceTypeModel.value);
      });
      const canShowSubtype = vue.computed(() => {
        return activeSourceConfig.value.hasSubtype;
      });
      const subTypeModel = vue.computed({
        get() {
          return props.modelValue.subtype || "";
        },
        set(newValue) {
          emit("update:modelValue", {
            type: sourceTypeModel.value,
            subtype: newValue
          });
        }
      });
      const computedSubtypeOptions = vue.computed(() => {
        if (["archive", "single"].includes(sourceTypeModel.value)) {
          return dataSetsStore.dataSets.post_types;
        } else if (sourceTypeModel.value === "taxonomy_archive") {
          return dataSetsStore.dataSets.taxonomies;
        }
        return [];
      });
      const canShowIdDropdown = vue.computed(() => {
        return activeSourceConfig.value.hasIdSelector;
      });
      const searchData = vue.computed(() => {
        return {
          type: sourceTypeModel.value,
          subtype: subTypeModel.value
        };
      });
      const idModel = vue.computed({
        get() {
          return props.modelValue.id || "";
        },
        set(newValue) {
          emit("update:modelValue", {
            type: sourceTypeModel.value,
            subtype: subTypeModel.value,
            id: newValue
          });
        }
      });
      return (_ctx, _cache) => {
        return vue.openBlock(), vue.createElementBlock("div", null, [
          vue.createVNode(_sfc_main$d, {
            modelValue: sourceTypeModel.value,
            "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => sourceTypeModel.value = $event),
            options: sourceTypes,
            "option-title": i18n__namespace.__("Source type", "zionbuilder-pro"),
            searchable: false
          }, null, 8, ["modelValue", "option-title"]),
          canShowSubtype.value ? (vue.openBlock(), vue.createBlock(_sfc_main$d, {
            key: 0,
            modelValue: subTypeModel.value,
            "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => subTypeModel.value = $event),
            "option-title": activeSourceConfig.value.subTypeTitle,
            options: computedSubtypeOptions.value
          }, null, 8, ["modelValue", "option-title", "options"])) : vue.createCommentVNode("", true),
          canShowIdDropdown.value && subTypeModel.value && subTypeModel.value.length > 0 ? (vue.openBlock(), vue.createBlock(_sfc_main$d, {
            key: 1,
            modelValue: idModel.value,
            "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => idModel.value = $event),
            "option-title": activeSourceConfig.value.idSelectorTitle,
            searchable: true,
            "search-data": searchData.value
          }, null, 8, ["modelValue", "option-title", "search-data"])) : vue.createCommentVNode("", true)
        ]);
      };
    }
  });
  const WPPageSelector_vue_vue_type_style_index_0_lang = "";
  const WPPageSelector = {
    id: "wp_page_selector",
    component: _sfc_main$c
  };
  const _sfc_main$b = /* @__PURE__ */ vue.defineComponent({
    __name: "QueryBuilder",
    props: {
      modelValue: { default: () => ({}) }
    },
    emits: ["update:modelValue"],
    setup(__props, { emit }) {
      const props = __props;
      function update(key, value) {
        const clone = __spreadValues({}, props.modelValue);
        clone[key] = value;
        emit("update:modelValue", clone);
      }
      const queryTypes = hooks.applyFilters(
        "zionbuilderpro/options/query_builder/types",
        window.ZionBuilderProInitialData.repeater_data.query_builder_types
      );
      const typeModel = vue.computed({
        get: () => props.modelValue,
        set: (newValue) => update("type", newValue.type)
      });
      const configSchema = vue.computed({
        get: () => props.modelValue.config || {},
        set: (newValue) => update("config", newValue)
      });
      const typeSchema = {
        type: {
          id: "type",
          type: "select",
          title: i18n__namespace.__("Query type", "zionbuilder-pro"),
          default: queryTypes[0].id,
          options: queryTypes
        }
      };
      const queryConfigSchema = vue.computed({
        get() {
          const queryType = props.modelValue.type || queryTypes[0].id;
          const queryTypeConfig = queryTypes.find((type) => type.id === queryType);
          if (!queryTypeConfig)
            return [];
          return queryTypeConfig.schema;
        },
        set(newValue) {
          update("config", newValue);
        }
      });
      return (_ctx, _cache) => {
        const _component_OptionsForm = vue.resolveComponent("OptionsForm");
        return vue.openBlock(), vue.createElementBlock("div", null, [
          vue.createVNode(_component_OptionsForm, {
            modelValue: typeModel.value,
            "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => typeModel.value = $event),
            schema: typeSchema,
            class: "znpb-pro-query-builder-form"
          }, null, 8, ["modelValue"]),
          vue.createVNode(_component_OptionsForm, {
            modelValue: configSchema.value,
            "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => configSchema.value = $event),
            schema: queryConfigSchema.value,
            class: "znpb-pro-query-builder-form"
          }, null, 8, ["modelValue", "schema"])
        ]);
      };
    }
  });
  const QueryBuilder_vue_vue_type_style_index_0_lang = "";
  const _sfc_main$a = /* @__PURE__ */ vue.defineComponent({
    __name: "QueryBuilderTaxonomy",
    props: {
      modelValue: { default: () => ({}) }
    },
    emits: ["update:modelValue"],
    setup(__props, { emit }) {
      const props = __props;
      const terms = vue.ref([]);
      const value = vue.computed({
        get: () => props.modelValue[0] || {},
        set: (newValue) => emit("update:modelValue", [newValue])
      });
      vue.watch(
        () => value.value.taxonomy,
        () => {
          value.value.terms = null;
        }
      );
      const taxonomySchema = vue.computed(() => {
        return {
          taxonomy: {
            type: "select",
            data_source: "taxonomies",
            placeholder: "Select taxonomy"
          },
          terms: {
            title: "Terms select",
            type: "select",
            searchable: true,
            multiple: true,
            placeholder: "Select Terms",
            filterable: true,
            options: terms.value,
            dependency: [
              {
                option: "taxonomy",
                type: "value_set"
              }
            ]
          }
        };
      });
      vue.watchEffect(() => {
        get_taxonomy_terms({
          taxonomy: value.value.taxonomy,
          includes: value.value.terms || []
        });
      });
      function get_taxonomy_terms(params) {
        getTerms(params).then((response) => {
          const items2 = response.data.map((item) => {
            return {
              id: item.term_id,
              name: item.name
            };
          });
          terms.value = items2;
        }).catch((error) => {
          console.error(error);
        });
      }
      return (_ctx, _cache) => {
        const _component_OptionsForm = vue.resolveComponent("OptionsForm");
        return vue.openBlock(), vue.createBlock(_component_OptionsForm, {
          modelValue: value.value,
          "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => value.value = $event),
          schema: taxonomySchema.value,
          class: "znpb-pro-query-builder-form"
        }, null, 8, ["modelValue", "schema"]);
      };
    }
  });
  const QueryBuilderOption = {
    id: "query_builder",
    component: _sfc_main$b
  };
  const QueryBuilderTaxonomyOption = {
    id: "query_builder_taxonomy",
    component: _sfc_main$a
  };
  function registerCustomOptions() {
    const { registerOption } = composables.useOptions();
    registerOption(WPPageSelector);
    registerOption(QueryBuilderOption);
    registerOption(QueryBuilderTaxonomyOption);
  }
  function RepeaterProvider(provideElement = null, config = {}, data = []) {
    return {
      config,
      data,
      provideElement
    };
  }
  const _sfc_main$9 = /* @__PURE__ */ vue.defineComponent({
    __name: "RepeaterItem",
    props: {
      element: {},
      data: {},
      config: {},
      main: {}
    },
    setup(__props) {
      const props = __props;
      const isDisabled = vue.computed(() => {
        return props.element.isClone;
      });
      const itemData = vue.computed(() => props.data);
      const main = vue.computed(() => props.main);
      vue.provide("isRepeaterItemMain", main);
      vue.provide("repeaterItemData", itemData);
      return (_ctx, _cache) => {
        const _component_ElementWrapper = vue.resolveComponent("ElementWrapper");
        return vue.openBlock(), vue.createBlock(_component_ElementWrapper, {
          key: _ctx.element.uid,
          element: _ctx.element,
          class: vue.normalizeClass({
            "znpb-element__wrapper--is-disabled": isDisabled.value
          })
        }, null, 8, ["element", "class"]);
      };
    }
  });
  const RepeaterItem_vue_vue_type_style_index_0_lang = "";
  const _sfc_main$8 = /* @__PURE__ */ vue.defineComponent({
    __name: "RepeaterContainer",
    props: {
      element: {}
    },
    setup(__props) {
      const props = __props;
      const repeaterProvider = vue.inject("repeaterProvider");
      const contentStore = window.zb.editor.useContentStore();
      const consumerElements2 = vue.ref([
        {
          element: props.element,
          data: null,
          config: null
        }
      ]);
      const initialConsumerElements = vue.computed(() => {
        const element = props.element;
        if (!repeaterProvider.value.data || repeaterProvider.value.data.length === 0) {
          return [
            {
              element,
              data: null,
              config: null
            }
          ];
        }
        const { getRepeaterItems } = useRepeater();
        return getRepeaterItems(repeaterProvider.value.data, element);
      });
      vue.watch(
        initialConsumerElements,
        (newValue, oldValue) => {
          const element = props.element;
          consumerElements2.value = newValue.map((repeaterItemData, index) => {
            let repeaterItem;
            if (index === 0) {
              repeaterItem = attachRepeaterData(element, index, repeaterItemData);
            } else {
              repeaterItem = createRepeaterClone(element, index, repeaterItemData);
            }
            return {
              main: index === 0,
              element: repeaterItem,
              data: repeaterItemData,
              config: repeaterProvider.value.config
            };
          });
        },
        { immediate: true }
      );
      const consumerElements = vue.computed(() => {
        const element = props.element;
        if (!repeaterProvider.value.data || repeaterProvider.value.data.length === 0) {
          return [
            {
              element,
              data: null,
              config: null
            }
          ];
        }
        const { getRepeaterItems } = useRepeater();
        const repeaterItems = getRepeaterItems(repeaterProvider.value.data, element);
        return repeaterItems.map((repeaterItemData, index) => {
          let repeaterItem;
          if (index === 0) {
            repeaterItem = attachRepeaterData(element, index, repeaterItemData);
          } else {
            repeaterItem = createRepeaterClone(element, index, repeaterItemData);
          }
          return {
            main: index === 0,
            element: repeaterItem,
            data: repeaterItemData,
            config: repeaterProvider.value.config
          };
        });
      });
      function createRepeaterClone(element, index, repeaterItemData, isChild = false) {
        const contentStore2 = window.zb.editor.useContentStore();
        const elementCssId = element.getOptionValue("_advanced_options._element_id", element.uid);
        const cssClass = elementCssId + "_" + index;
        const elementConfigAsJSON = JSON.parse(JSON.stringify(element.toJSON()));
        elementConfigAsJSON.uid = cssClass;
        const cloneChildren = elementConfigAsJSON.content;
        elementConfigAsJSON.content = [];
        const clonedElement = contentStore2.registerElement(elementConfigAsJSON, element.parent.uid);
        clonedElement.repeaterItemData = repeaterItemData;
        clonedElement.repeaterItemIndex = index;
        clonedElement.repeaterCSSClasses = [elementCssId, cssClass];
        clonedElement.repeaterCSSID = cssClass;
        clonedElement.isClone = index !== 0;
        if (Array.isArray(cloneChildren)) {
          const children = [];
          cloneChildren.forEach((childElementJSON) => {
            const childInstance = contentStore2.getElement(childElementJSON.uid);
            const clonedChildInstance = createRepeaterClone(childInstance, index, repeaterItemData, true);
            children.push(clonedChildInstance.uid);
          });
          clonedElement.content = children;
        }
        return vue.reactive(clonedElement);
      }
      function attachRepeaterData(element, index, repeaterItemData, isChild = false) {
        const { isRepeaterConsumer } = useRepeater();
        const elementCssId = element.getOptionValue("_advanced_options._element_id", element.uid);
        const cssClass = elementCssId + "_" + index;
        if (!(isChild && isRepeaterConsumer(element))) {
          element.repeaterItemData = repeaterItemData;
          element.repeaterItemIndex = index;
          element.repeaterCSSClasses = [elementCssId, cssClass];
          element.repeaterCSSID = cssClass;
          if (element.content && Array.isArray(element.content)) {
            element.content.forEach((childElementUID) => {
              const childElement = contentStore.getElement(childElementUID);
              attachRepeaterData(childElement, index, repeaterItemData, true);
            });
          }
        }
        return element;
      }
      return (_ctx, _cache) => {
        return vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(consumerElements.value, (repeaterItem) => {
          return vue.openBlock(), vue.createBlock(_sfc_main$9, vue.mergeProps({
            key: repeaterItem.element.uid,
            element: repeaterItem.element,
            data: repeaterItem.data,
            config: repeaterItem.config,
            main: repeaterItem.main
          }, _ctx.$attrs), null, 16, ["element", "data", "config", "main"]);
        }), 128);
      };
    }
  });
  function Repeater() {
    const {
      isRepeaterConsumer,
      isRepeaterProvider,
      queryProviderData,
      getRepeaterProviderConfig,
      getRepeaterParentConfig
    } = useRepeater();
    function init() {
      window.zb.hooks.addAction("zionbuilder/preview/app/setup", onPreviewAppSetup);
      window.zb.hooks.addAction("zionbuilder/preview/element/setup", onElementSetup);
      window.zb.hooks.addFilter("zionbuilder/preview/element/wrapper_component", checkForRepeaterConsumer);
      window.zb.hooks.addFilter("zionbuilder/element/css_selector", changeElementCSSSelector);
      window.zb.hooks.addFilter("zionbuilder/element/css_classes", changeElementCSSClasses);
      window.zb.hooks.addFilter("zionbuilder/server_request/element_requester_data", parseData);
      window.zb.hooks.addFilter("zionbuilder/element/css_id", changeElementID);
      window.zb_get_repeater_fields_as_options = function() {
        const UIStore = window.zb.editor.useUIStore();
        UIStore.editedElement;
        if (UIStore.editedElement && UIStore.editedElement.repeaterItemData) {
          return Object.keys(UIStore.editedElement.repeaterItemData).map((fieldID) => {
            return {
              id: fieldID,
              name: fieldID
            };
          });
        }
        return [];
      };
    }
    function parseData(data, element) {
      if (element) {
        data.repeaterConfigs = getRepeaterParentConfig(element);
      }
      return data;
    }
    function changeElementCSSClasses(classes, optionsInstance, element) {
      if (element.repeaterCSSClasses) {
        element.repeaterCSSClasses.forEach((cssClass) => {
          classes[cssClass] = true;
        });
      }
      return classes;
    }
    function changeElementID(id, element) {
      if (element.repeaterCSSID) {
        return element.repeaterCSSID;
      }
      return id;
    }
    function changeElementCSSSelector(selector, optionsInstance, element) {
      const isRepeaterItemMain = vue.inject("isRepeaterItemMain", false);
      if (isRepeaterItemMain) {
        selector = `.zb .${element.elementCssId}`;
      }
      return selector;
    }
    function onElementSetup(element) {
      const repeaterProvider = vue.inject("repeaterProvider");
      const localProvide = vue.ref({
        element,
        config: getRepeaterProviderConfig(element),
        data: []
      });
      const computedRepeaterData = vue.computed(() => {
        if (isRepeaterProvider(element)) {
          return localProvide.value;
        } else {
          return repeaterProvider.value;
        }
      });
      vue.watch(
        () => getRepeaterProviderConfig(element),
        (newValue) => {
          localProvide.value.config = newValue;
        }
      );
      const isProvider = vue.computed(() => computedRepeaterData.value === localProvide.value);
      const localConfig = vue.computed(() => localProvide.value.config);
      vue.watch(isProvider, (newValue, oldValue) => {
        if (newValue && newValue !== oldValue) {
          performQuery();
        }
      });
      vue.watch(localConfig, (newValue, oldValue) => {
        if (JSON.stringify(newValue) !== JSON.stringify(oldValue)) {
          performQuery();
        }
      });
      const pageSettingsStore = window.zb.editor.usePageSettingsStore();
      const dynamicDataSourceRef = vue.computed(() => pageSettingsStore.settings.dynamic_data_source || {});
      vue.watch(dynamicDataSourceRef, (newValue, oldValue) => {
        if (isProvider.value) {
          performQuery();
        }
      });
      if (isProvider.value) {
        performQuery();
      }
      function performQuery() {
        element.loading = true;
        queryProviderData(localConfig.value, element).then((data) => {
          localProvide.value.data = data;
        }).catch((error) => {
          console.error(error);
        }).finally(() => {
          element.loading = false;
        });
      }
      vue.provide("repeaterProvider", computedRepeaterData);
    }
    function checkForRepeaterConsumer(component, element) {
      if (isRepeaterConsumer(element)) {
        if (component !== _sfc_main$8) {
          component = _sfc_main$8;
        }
      }
      return component;
    }
    function onPreviewAppSetup() {
      vue.provide("repeaterProvider", vue.ref(RepeaterProvider()));
    }
    return {
      init
    };
  }
  const requests = vue.ref([]);
  const _sfc_main$7 = /* @__PURE__ */ vue.defineComponent({
    __name: "Select",
    props: {
      modelValue: {
        type: [String, Number, Boolean],
        required: false,
        default: void 0
      },
      schema: {
        type: Object,
        required: true
      }
    },
    emits: ["update:model-value"],
    setup(__props, { emit }) {
      const props = __props;
      vue.watchEffect(() => {
        if (props.schema.rest) {
          if (typeof requests.value[props.schema.rest] === "undefined") {
            requests.value[props.schema.rest] = [];
            ZionService.get(props.schema.rest).then((response) => {
              requests.value[props.schema.rest] = response.data;
            });
          }
        }
      });
      vue.onUpdated(() => {
        if (typeof computedModel.value === "undefined") {
          if (props.schema.options && typeof props.schema.options[0] !== "undefined") {
            computedModel.value = props.schema.options[0].id;
          }
        }
      });
      const computedModel = vue.computed({
        get() {
          return props.modelValue;
        },
        set(newValue) {
          emit("update:model-value", newValue);
        }
      });
      const computedSchema = vue.computed(() => {
        const schema = __spreadValues({}, props.schema);
        if (!schema.placeholder) {
          schema.placeholder = i18n__namespace.__("Select", "zionbuilder-pro");
        }
        if (schema.rest) {
          schema.options = requests.value[schema.rest];
        }
        return schema;
      });
      return (_ctx, _cache) => {
        const _component_InputSelect = vue.resolveComponent("InputSelect");
        const _directive_znpb_tooltip = vue.resolveDirective("znpb-tooltip");
        return vue.withDirectives((vue.openBlock(), vue.createBlock(_component_InputSelect, vue.mergeProps({
          modelValue: computedModel.value,
          "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => computedModel.value = $event)
        }, computedSchema.value), null, 16, ["modelValue"])), [
          [_directive_znpb_tooltip, __props.schema.title]
        ]);
      };
    }
  });
  const Text_vue_vue_type_style_index_0_lang = "";
  const _sfc_main$6 = {
    __name: "Text",
    props: {
      modelValue: {
        type: [String, Number],
        required: false,
        default: void 0
      },
      schema: {
        type: Object,
        required: true
      }
    },
    emits: ["update:model-value"],
    setup(__props, { emit }) {
      const props = __props;
      const computedModel = vue.computed({
        get() {
          return props.modelValue;
        },
        set(newValue) {
          emit("update:model-value", newValue);
        }
      });
      return (_ctx, _cache) => {
        const _component_BaseInput = vue.resolveComponent("BaseInput");
        const _directive_znpb_tooltip = vue.resolveDirective("znpb-tooltip");
        return vue.withDirectives((vue.openBlock(), vue.createBlock(_component_BaseInput, vue.mergeProps({
          modelValue: computedModel.value,
          "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => computedModel.value = $event)
        }, __props.schema, { class: "znpb-elementConditionsFieldText" }), null, 16, ["modelValue"])), [
          [_directive_znpb_tooltip, __props.schema.title]
        ]);
      };
    }
  };
  const Placeholder_vue_vue_type_style_index_0_lang = "";
  const _hoisted_1$4 = { class: "znpbPro-elConditions-inputPlaceholder" };
  const _sfc_main$5 = {
    __name: "Placeholder",
    props: {
      schema: {
        type: Object,
        required: true
      }
    },
    setup(__props) {
      return (_ctx, _cache) => {
        return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$4, vue.toDisplayString(__props.schema.content), 1);
      };
    }
  };
  const _sfc_main$4 = /* @__PURE__ */ vue.defineComponent({
    __name: "ConditionInput",
    props: {
      modelValue: {
        type: [Object, String, Array, Number, Boolean],
        required: false,
        default: void 0
      },
      schema: {
        type: Object,
        required: true
      }
    },
    emits: ["update:model-value"],
    setup(__props, { emit }) {
      const props = __props;
      const computedModelValue = vue.computed({
        get() {
          return props.modelValue;
        },
        set(newValue) {
          emit("update:model-value", newValue);
        }
      });
      const optionsMap = {
        select: _sfc_main$7,
        text: _sfc_main$6,
        placeholder: _sfc_main$5
      };
      const component = vue.computed(() => optionsMap[props.schema.type]);
      return (_ctx, _cache) => {
        return vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(component.value), {
          modelValue: computedModelValue.value,
          "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => computedModelValue.value = $event),
          schema: __props.schema,
          class: "znpb-elementConditionsRuleInput"
        }, null, 8, ["modelValue", "schema"]);
      };
    }
  });
  const ConditionInput_vue_vue_type_style_index_0_lang = "";
  const ConditionRule_vue_vue_type_style_index_0_lang = "";
  const _hoisted_1$3 = { class: "znpb-elementConditionsRule" };
  const _hoisted_2$2 = { class: "znpb-elementConditionsRuleFields" };
  const _sfc_main$3 = {
    __name: "ConditionRule",
    props: {
      condition: {
        type: Object,
        required: true
      }
    },
    emits: ["delete-condition", "update-condition"],
    setup(__props, { emit }) {
      const props = __props;
      const formModel = vue.computed({
        get() {
          return props.condition;
        },
        set(newValue) {
          emit("update-condition", newValue !== null ? newValue : {});
        }
      });
      vue.watch(
        () => formModel.value.type,
        (newValue, oldValue) => {
          if (newValue !== oldValue) {
            formModel.value = {
              type: newValue
            };
          }
        }
      );
      const inputs = vue.computed(() => {
        let inputs2 = [];
        inputs2.push({
          type: "select",
          options: ZionBuilderProInitialData.element_conditions.condition_options,
          placeholder: i18n__namespace.__("Select", "zionbuilder-pro"),
          filterable: true,
          id: "type"
        });
        if (formModel.value.type) {
          const conditionConfig = ZionBuilderProInitialData.element_conditions.conditions[formModel.value.type];
          if (conditionConfig && conditionConfig.form) {
            Object.keys(conditionConfig.form).forEach((optionID) => {
              const optionConfig = __spreadValues({}, conditionConfig.form[optionID]);
              optionConfig.id = optionID;
              let canDisplay = true;
              if (optionConfig.requires) {
                if (typeof optionConfig.requires === "string") {
                  canDisplay = typeof formModel.value[optionConfig.requires] !== "undefined";
                } else if (Array.isArray(optionConfig.requires)) {
                  for (let displayCondition of optionConfig.requires) {
                    const { option_id, operator = "in", value } = displayCondition;
                    const savedValue = formModel.value[option_id];
                    if (operator === "in" && !value.includes(savedValue)) {
                      canDisplay = false;
                      break;
                    } else if (operator === "not_in" && value.includes(savedValue)) {
                      canDisplay = false;
                      break;
                    }
                  }
                }
              }
              if (canDisplay) {
                if (optionConfig.rest) {
                  const compile = template(optionConfig.rest);
                  try {
                    const result = compile(formModel.value);
                    if (result) {
                      optionConfig.rest = result;
                    }
                  } catch (err) {
                  }
                }
                inputs2.push(optionConfig);
              }
            });
          }
        }
        return inputs2;
      });
      function onOptionUpdate(type, newValue) {
        formModel.value = __spreadProps(__spreadValues({}, formModel.value), {
          [type]: newValue
        });
      }
      return (_ctx, _cache) => {
        const _component_Icon = vue.resolveComponent("Icon");
        const _component_Button = vue.resolveComponent("Button");
        return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$3, [
          vue.createElementVNode("div", _hoisted_2$2, [
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(inputs.value, (input) => {
              return vue.openBlock(), vue.createBlock(_sfc_main$4, {
                key: input.id,
                schema: input,
                "model-value": formModel.value[input.id],
                "onUpdate:modelValue": ($event) => onOptionUpdate(input.id, $event)
              }, null, 8, ["schema", "model-value", "onUpdate:modelValue"]);
            }), 128))
          ]),
          vue.createVNode(_component_Button, {
            type: "line",
            onClick: _cache[0] || (_cache[0] = ($event) => emit("delete-condition"))
          }, {
            default: vue.withCtx(() => [
              vue.createVNode(_component_Icon, { icon: "delete" })
            ]),
            _: 1
          })
        ]);
      };
    }
  };
  const ConditionGroup_vue_vue_type_style_index_0_lang = "";
  const _hoisted_1$2 = { class: "znpb-elementConditionsGroup" };
  const _hoisted_2$1 = { class: "znpb-elementConditionsGroupActions" };
  const _hoisted_3$1 = {
    key: 0,
    class: "znpb-elementConditionsGroupSeparator"
  };
  const _sfc_main$2 = {
    __name: "ConditionGroup",
    props: {
      group: {
        type: Array,
        required: true
      },
      addCondition: {
        type: Function,
        required: true
      },
      updateCondition: {
        type: Function,
        required: true
      },
      deleteCondition: {
        type: Function,
        required: true
      },
      deleteGroup: {
        type: Function,
        required: true
      },
      showSeparator: {
        type: Boolean,
        required: true,
        default: false
      }
    },
    setup(__props) {
      return (_ctx, _cache) => {
        const _component_Icon = vue.resolveComponent("Icon");
        return vue.openBlock(), vue.createElementBlock("div", _hoisted_1$2, [
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(__props.group, (condition, index) => {
            return vue.openBlock(), vue.createBlock(_sfc_main$3, {
              key: index,
              condition,
              onDeleteCondition: ($event) => __props.deleteCondition(__props.group, condition),
              onUpdateCondition: ($event) => __props.updateCondition(__props.group, condition, $event)
            }, null, 8, ["condition", "onDeleteCondition", "onUpdateCondition"]);
          }), 128)),
          vue.createElementVNode("div", _hoisted_2$1, [
            vue.createElementVNode("button", {
              onClick: _cache[0] || (_cache[0] = ($event) => __props.deleteGroup(__props.group))
            }, [
              vue.createVNode(_component_Icon, { icon: "delete" }),
              vue.createTextVNode(" " + vue.toDisplayString(i18n__namespace.__("Delete group", "zionbuilder-pro")), 1)
            ]),
            vue.createElementVNode("button", {
              onClick: _cache[1] || (_cache[1] = ($event) => __props.addCondition(__props.group))
            }, [
              vue.createVNode(_component_Icon, { icon: "plus" }),
              vue.createTextVNode(" " + vue.toDisplayString(i18n__namespace.__("Add condition", "zionbuilder-pro")), 1)
            ])
          ]),
          __props.showSeparator ? (vue.openBlock(), vue.createElementBlock("div", _hoisted_3$1, [
            vue.createElementVNode("span", null, vue.toDisplayString(i18n__namespace.__("or", "zionbuilder-pro")), 1)
          ])) : vue.createCommentVNode("", true)
        ]);
      };
    }
  };
  const _hoisted_1$1 = { class: "znpb-elementConditionsContent znpb-fancy-scrollbar" };
  const _hoisted_2 = { class: "znpb-elementAddConditionsGroup" };
  const _hoisted_3 = { class: "znpb-elementConditionsModalFooter" };
  const _sfc_main$1 = /* @__PURE__ */ vue.defineComponent({
    __name: "ConditionsModal",
    props: {
      modelValue: { default: () => [] }
    },
    emits: ["update:modelValue", "update:show-modal"],
    setup(__props, { emit }) {
      const props = __props;
      const conditionGroups = vue.ref(JSON.parse(JSON.stringify(props.modelValue)));
      function onSaveConditions() {
        emit("update:modelValue", conditionGroups.value);
        emit("update:show-modal", false);
      }
      function addConditionsGroup() {
        const newGroup = [{}];
        conditionGroups.value = [...conditionGroups.value, newGroup];
      }
      function addCondition(group) {
        group.push([
          {
            type: ""
          }
        ]);
      }
      function deleteCondition(group, condition) {
        group.splice(group.indexOf(condition), 1);
        if (group.length === 0) {
          conditionGroups.value.splice(conditionGroups.value.indexOf(group), 1);
        }
      }
      function updateCondition(group, condition, newValue) {
        group.splice(group.indexOf(condition), 1, newValue);
      }
      function deleteGroup(group) {
        conditionGroups.value.splice(conditionGroups.value.indexOf(group), 1);
      }
      return (_ctx, _cache) => {
        const _component_Button = vue.resolveComponent("Button");
        const _component_Modal = vue.resolveComponent("Modal");
        return vue.openBlock(), vue.createBlock(_component_Modal, {
          show: true,
          width: 860,
          "show-maximize": false,
          class: "znpb-elementConditionsModal",
          "append-to": "body",
          title: i18n__namespace.__("Advanced display conditions", "zionbuilder-pro"),
          "close-on-click": false,
          onCloseModal: _cache[0] || (_cache[0] = ($event) => emit("update:show-modal", false))
        }, {
          default: vue.withCtx(() => [
            vue.createElementVNode("div", _hoisted_1$1, [
              (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(conditionGroups.value, (group, index) => {
                return vue.openBlock(), vue.createBlock(_sfc_main$2, vue.mergeProps({ key: index }, {
                  addCondition,
                  updateCondition,
                  group,
                  deleteCondition,
                  deleteGroup
                }, {
                  "show-separator": index < conditionGroups.value.length - 1
                }), null, 16, ["show-separator"]);
              }), 128)),
              vue.createElementVNode("div", _hoisted_2, [
                vue.createVNode(_component_Button, {
                  type: "secondary",
                  onClick: addConditionsGroup
                }, {
                  default: vue.withCtx(() => [
                    vue.createTextVNode(vue.toDisplayString(i18n__namespace.__("Add conditions group", "zionbuilder-pro")), 1)
                  ]),
                  _: 1
                })
              ])
            ]),
            vue.createElementVNode("div", _hoisted_3, [
              vue.createVNode(_component_Button, {
                type: "secondary",
                onClick: onSaveConditions
              }, {
                default: vue.withCtx(() => [
                  vue.createTextVNode(vue.toDisplayString(i18n__namespace.__("Save conditions", "zionbuilder-pro")), 1)
                ]),
                _: 1
              })
            ])
          ]),
          _: 1
        }, 8, ["title"]);
      };
    }
  });
  const ConditionsModal_vue_vue_type_style_index_0_lang = "";
  const _hoisted_1 = { class: "znpb-elementConditionsWrapper" };
  const _sfc_main = /* @__PURE__ */ vue.defineComponent({
    __name: "ElementConditions",
    props: {
      modelValue: { default: () => [] }
    },
    emits: ["update:modelValue"],
    setup(__props, { emit }) {
      const props = __props;
      const showModal = vue.ref(false);
      const conditions = vue.computed({
        get() {
          return props.modelValue || [];
        },
        set(newValue) {
          emit("update:modelValue", newValue);
        }
      });
      return (_ctx, _cache) => {
        const _component_Button = vue.resolveComponent("Button");
        return vue.openBlock(), vue.createElementBlock("div", _hoisted_1, [
          vue.createVNode(_component_Button, {
            type: "secondary",
            onClick: _cache[0] || (_cache[0] = ($event) => showModal.value = true)
          }, {
            default: vue.withCtx(() => [
              vue.createTextVNode(vue.toDisplayString(i18n__namespace.__("Set advanced conditions", "zionbuilder-pro")), 1)
            ]),
            _: 1
          }),
          showModal.value ? (vue.openBlock(), vue.createBlock(_sfc_main$1, {
            key: 0,
            showModal: showModal.value,
            "onUpdate:showModal": _cache[1] || (_cache[1] = ($event) => showModal.value = $event),
            modelValue: conditions.value,
            "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => conditions.value = $event)
          }, null, 8, ["showModal", "modelValue"])) : vue.createCommentVNode("", true)
        ]);
      };
    }
  });
  const ElementConditions_vue_vue_type_style_index_0_lang = "";
  function elementConditions() {
    function init() {
      registerOptions();
    }
    function registerOptions() {
      const { registerOption } = composables.useOptions();
      registerOption({
        id: "element_conditions",
        component: _sfc_main
      });
    }
    return {
      init
    };
  }
  const GlobalColor = {
    id: "global-color",
    getContent(config) {
      let useBuilderOptions;
      if (window.zb.admin) {
        useBuilderOptions = store.useBuilderOptionsStore;
      } else if (window.zb.editor) {
        useBuilderOptions = store.useBuilderOptionsStore;
      }
      const { getOptionValue } = useBuilderOptions();
      const globalColors = getOptionValue("global_colors");
      const colorData = globalColors.find((colorConfig) => {
        return colorConfig.id === config.options.color_id;
      });
      if (colorData) {
        return colorData.color;
      }
      return config.fallback ? config.fallback : false;
    }
  };
  const GlobalGradient = {
    id: "global-gradient",
    getContent(config) {
      let useBuilderOptions;
      if (window.zb.admin) {
        useBuilderOptions = store.useBuilderOptionsStore;
      } else if (window.zb.editor) {
        useBuilderOptions = store.useBuilderOptionsStore;
      }
      const { getOptionValue } = useBuilderOptions();
      const globalGradients = getOptionValue("global_gradients");
      const gradientData = globalGradients.find((gradientConfig) => {
        return gradientConfig.id === config.options.gradient_id;
      });
      if (gradientData) {
        return gradientData.config;
      }
      return config.fallback ? config.fallback : [];
    }
  };
  const RepeaterField = {
    id: "repeater-field-text",
    getContent(config, optionsInstance) {
      const { options = {} } = config;
      if (!options) {
        return;
      }
      const { repeater_field } = options;
      const { element } = optionsInstance;
      if (repeater_field && element) {
        const fieldValue = get(element, `repeaterItemData.${repeater_field}`);
        if (typeof fieldValue === "string" || typeof fieldValue === "number") {
          return fieldValue;
        }
      }
      return;
    }
  };
  class DynamicContent {
    constructor() {
      __publicField(this, "cache", {});
      this.registeredContentTypes = {};
      this.registerDefaultContentTypes();
    }
    registerDefaultContentTypes() {
      this.registerContentType(GlobalColor);
      this.registerContentType(GlobalGradient);
      this.registerContentType(RepeaterField);
    }
    getContentType(id) {
      return this.registeredContentTypes[id];
    }
    registerContentType(contentType) {
      const { id } = contentType;
      if (!id) {
        console.warn("Content types must have an id");
        return;
      }
      this.registeredContentTypes[id] = contentType;
    }
    checkAndUpdateDynamicFields(model, optionsInstance) {
      if (model && typeof model === "object") {
        Object.keys(model).forEach((optionKey) => {
          if (optionKey === "__dynamic_content__") {
            if (typeof model.__dynamic_content__ === "object" && Object.keys(model.__dynamic_content__).length > 0) {
              Object.keys(model.__dynamic_content__).forEach((contentKey) => {
                const tagConfig = model.__dynamic_content__[contentKey];
                const tagConfigForServer = this.getTagDataForServer(tagConfig);
                const dynamicContentItem = this.getContentType(tagConfig.type);
                if (dynamicContentItem && typeof dynamicContentItem.getContent === "function") {
                  const rawFieldValue = dynamicContentItem.getContent(tagConfig, optionsInstance);
                  if (typeof rawFieldValue === "object") {
                    const valueClone = JSON.parse(JSON.stringify(rawFieldValue));
                    this.checkAndUpdateDynamicFields(valueClone, optionsInstance);
                  }
                  const fieldValue = this.getFinalDynamicData(tagConfig, rawFieldValue);
                  model[contentKey] = fieldValue;
                } else {
                  optionsInstance.startLoading();
                  model[contentKey] = vue.ref(model[contentKey] || null);
                  optionsInstance.serverRequester.request(
                    {
                      type: "get_dynamic_data",
                      config: Object.assign({}, tagConfigForServer, {
                        __current_model: JSON.parse(JSON.stringify(model))
                      }),
                      useCache: true
                    },
                    (response) => {
                      model[contentKey].value = "";
                      model[contentKey] = this.getFinalDynamicData(tagConfig, response);
                      optionsInstance.endLoading();
                    },
                    function() {
                      optionsInstance.endLoading();
                    }
                  );
                }
              });
            }
          } else {
            this.checkAndUpdateDynamicFields(model[optionKey], optionsInstance);
          }
        });
      }
    }
    getTagDataForServer(tagConfig) {
      const cloneConfig = cloneDeep(tagConfig);
      const _a = cloneConfig.options || {}, { _before, _after, _fallback } = _a, remainingProperties = __objRest(_a, ["_before", "_after", "_fallback"]);
      cloneConfig.options = remainingProperties;
      return cloneConfig;
    }
    getFinalDynamicData(tagConfig, tagValue) {
      if (typeof tagValue !== "string") {
        return tagValue;
      }
      const defaultOptions = {
        _before: "",
        _after: "",
        _fallback: ""
      };
      const { type, options } = tagConfig;
      const { _before, _after, _fallback } = __spreadValues(__spreadValues({}, defaultOptions), options);
      let returnValue = _fallback;
      if (tagValue.length > 0) {
        returnValue = tagValue;
      }
      return `${_before}${returnValue}${_after}`;
    }
  }
  const dynamicContent = new DynamicContent();
  hooks.addFilter("zionbuilderpro/repeater/acf/fields", showAvailableRepeaterFields);
  hooks.addFilter("zionbuilderpro/dynamic_data/acf/options", addACFRepeatedFields);
  function getAcfParentRepeaters(element) {
    const parentRepeaters = [];
    if (element && element.parent) {
      element = element.parent;
      while (element) {
        const isRepeaterProvider = element.getOptionValue("_advanced_options.is_repeater_provider", false);
        const repeaterProviderConfig = element.getOptionValue("_advanced_options.repeater_provider_config", {
          type: "active_page_query"
        });
        if (isRepeaterProvider && repeaterProviderConfig.type === "acf_repeater") {
          const acfRepeaterField = element.getOptionValue(
            "_advanced_options.repeater_provider_config.config.repeater_field",
            null
          );
          if (acfRepeaterField) {
            parentRepeaters.push(acfRepeaterField);
          }
        }
        element = element.parent;
      }
    }
    return parentRepeaters;
  }
  function showAvailableRepeaterFields(options, element) {
    const acfParentRepeaters = getAcfParentRepeaters(element);
    const hasAcfParents = acfParentRepeaters.length > 0;
    const optionsToReturn = options.filter((option) => {
      if (hasAcfParents) {
        const lastParent = acfParentRepeaters[0];
        const itemBreak = lastParent.split(":");
        return itemBreak[0] === option.acf_parent;
      } else {
        return !option.acf_parent;
      }
    });
    return optionsToReturn.map((option) => {
      return __spreadProps(__spreadValues({}, option), {
        id: option.acf_parent ? `${option.id}:repeater_child` : option.id
      });
    });
  }
  function addACFRepeatedFields(options, element) {
    const repeatedFields = [];
    const repeaterItemData = element.repeaterItemData;
    const oldOptions = options;
    options = [];
    if (repeaterItemData && Object.keys(repeaterItemData).length > 0) {
      Object.keys(repeaterItemData).forEach((repeatedItemKey) => {
        repeatedFields.push({
          id: `${repeatedItemKey}:repeater_child`,
          name: repeatedItemKey,
          is_group_item: true
        });
      });
      if (repeatedFields.length > 0) {
        options.push({
          id: "",
          name: i18n__namespace.__("ACF repeater Field", "zionbuilder-pro"),
          is_label: true
        });
      }
      options = [...options, ...repeatedFields, ...oldOptions];
    } else {
      return oldOptions;
    }
    return options;
  }
  registerCustomOptions();
  const addDynamicContentOption = (componentConfig, schema, value, formModel) => {
    const { type, id } = schema;
    if (type === "colorpicker" && hasDynamicValue(formModel, id)) {
      return {
        component: _sfc_main$g
      };
    } else if (type === "background_gradient" && hasDynamicValue(formModel, id)) {
      return {
        component: _sfc_main$f
      };
    } else if (type === "background_color" && hasDynamicValue(formModel, id)) {
      return {
        component: _sfc_main$e
      };
    } else {
      if (hasDynamicValue(formModel, id)) {
        return __spreadProps(__spreadValues({}, componentConfig), {
          component: _sfc_main$k
        });
      }
    }
    return componentConfig;
  };
  function hasDynamicValue(formModel, id) {
    return !!get(formModel, `__dynamic_content__.${id}`);
  }
  function updateOptionWithDynamicContent(options, optionsInstance) {
    dynamicContent.checkAndUpdateDynamicFields(options, optionsInstance);
    return options;
  }
  function getAttributesComponent() {
    const attributesComponent = {
      type: "accordion_menu",
      title: i18n__namespace.__("custom attributes", "zionbuilder-pro"),
      icon: "tags-attributes",
      is_layout: true,
      child_options: {
        attributes: {
          type: "repeater",
          title: i18n__namespace.__("Attributes", "zionbuilder-pro"),
          add_button_text: i18n__namespace.__("Add new attribute", "zionbuilder-pro"),
          item_title: "attribute_name",
          default_item_title: "attr %s",
          id: "attributes",
          child_options: {
            attribute_name: {
              id: "attribute_name",
              type: "text",
              title: i18n__namespace.__("Attribute name", "zionbuilder-pro"),
              dynamic: {
                enabled: true
              }
            },
            attribute_value: {
              type: "text",
              id: "attribute_value",
              title: i18n__namespace.__("Attribute value", "zionbuilder-pro"),
              dynamic: {
                enabled: true
              }
            }
          }
        }
      }
    };
    return attributesComponent;
  }
  window.addEventListener("zionbuilder/editor/ready", function() {
    useElementCustomCSS();
    hooks.addFilter("zionbuilder/options/attributes", getAttributesComponent);
    const { registerComponent } = window.zb.composables.useInjections();
    registerComponent("input_wrapper/end/editor", _sfc_main$o);
    registerComponent("input_wrapper/end/textarea", _sfc_main$o);
    registerComponent("base_input/append", _sfc_main$o);
    registerComponent("options/link/append", _sfc_main$n);
    hooks.addFilter("zionbuilder/options/link/url_component", (component, modelValue) => {
      if (hasDynamicValue(modelValue, "link")) {
        return _sfc_main$i;
      }
      return component;
    });
    registerComponent("options/media/append", _sfc_main$o);
    hooks.addFilter("zionbuilder/options/media/input_component", (component, modelValue) => {
      const inputWrapper = vue.inject("inputWrapper");
      vue.inject("OptionsForm");
      const { schema, optionId } = inputWrapper;
      if (hasDynamicValue(modelValue, optionId)) {
        return _sfc_main$i;
      }
      return component;
    });
    registerComponent("options/image/actions", _sfc_main$m);
    hooks.addFilter("zionbuilder/options/image/display_component", (component, modelValue, inputWrapper, optionsForm) => {
      const { schema, optionId: id } = inputWrapper;
      let hasDynamicValue2 = false;
      if (schema.show_size) {
        hasDynamicValue2 = get(modelValue, `__dynamic_content__.image`);
      } else {
        const modelValue2 = optionsForm.modelValue.value;
        if (schema.type === "background") {
          hasDynamicValue2 = get(modelValue2, `__dynamic_content__.background-image`);
        } else {
          hasDynamicValue2 = get(modelValue2, `__dynamic_content__.${id}`);
        }
      }
      if (hasDynamicValue2) {
        return _sfc_main$j;
      }
      return component;
    });
    function changeBulkActionsData(data) {
      const pageSettingsStore = window.zb.editor.usePageSettingsStore();
      const dynamicDataSource = pageSettingsStore.settings.dynamic_data_source || {};
      data.dynamic_data_source = dynamicDataSource;
      return data;
    }
    hooks.addFilter("zionbuilder/getOptionConfig", addDynamicContentOption);
    hooks.addFilter("zionbuilder/options/model", updateOptionWithDynamicContent);
    hooks.addFilter("zionbuilder/server_request/data", changeBulkActionsData);
    hooks.addAction("zionbuilder/input/image/src_url", updateDynamicImageSrc);
    function updateDynamicImageSrc(imageSrc, modelValue, element) {
      if (!element) {
        imageSrc.value = null;
        return;
      }
      const tagConfig = get(modelValue, `__dynamic_content__.image`, null);
      if (!tagConfig) {
        imageSrc.value = null;
      }
      element.serverRequester.request(
        {
          type: "get_dynamic_data",
          config: tagConfig,
          useCache: true
        },
        (response) => {
          imageSrc.value = response;
        },
        function() {
        }
      );
    }
    const { init: initRepeater } = Repeater();
    initRepeater();
  });
  elementConditions().init();
})(zb.vue, wp.i18n, zb.hooks, zb.store, zb.composables);

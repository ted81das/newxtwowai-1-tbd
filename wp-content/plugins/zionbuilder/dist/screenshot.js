var __defProp = Object.defineProperty;
var __getOwnPropSymbols = Object.getOwnPropertySymbols;
var __hasOwnProp = Object.prototype.hasOwnProperty;
var __propIsEnum = Object.prototype.propertyIsEnumerable;
var __pow = Math.pow;
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
var __async = (__this, __arguments, generator) => {
  return new Promise((resolve, reject) => {
    var fulfilled = (value) => {
      try {
        step(generator.next(value));
      } catch (e) {
        reject(e);
      }
    };
    var rejected = (value) => {
      try {
        step(generator.throw(value));
      } catch (e) {
        reject(e);
      }
    };
    var step = (x) => x.done ? resolve(x.value) : Promise.resolve(x.value).then(fulfilled, rejected);
    step((generator = generator.apply(__this, __arguments)).next());
  });
};
(function() {
  "use strict";
  const index = "";
  function resolveUrl(url, baseUrl) {
    if (url.match(/^[a-z]+:\/\//i)) {
      return url;
    }
    if (url.match(/^\/\//)) {
      return window.location.protocol + url;
    }
    if (url.match(/^[a-z]+:/i)) {
      return url;
    }
    const doc = document.implementation.createHTMLDocument();
    const base = doc.createElement("base");
    const a = doc.createElement("a");
    doc.head.appendChild(base);
    doc.body.appendChild(a);
    if (baseUrl) {
      base.href = baseUrl;
    }
    a.href = url;
    return a.href;
  }
  const uuid = (() => {
    let counter = 0;
    const random = () => (
      // eslint-disable-next-line no-bitwise
      `0000${(Math.random() * __pow(36, 4) << 0).toString(36)}`.slice(-4)
    );
    return () => {
      counter += 1;
      return `u${random()}${counter}`;
    };
  })();
  function toArray(arrayLike) {
    const arr = [];
    for (let i = 0, l = arrayLike.length; i < l; i++) {
      arr.push(arrayLike[i]);
    }
    return arr;
  }
  function px(node, styleProperty) {
    const win = node.ownerDocument.defaultView || window;
    const val = win.getComputedStyle(node).getPropertyValue(styleProperty);
    return val ? parseFloat(val.replace("px", "")) : 0;
  }
  function getNodeWidth(node) {
    const leftBorder = px(node, "border-left-width");
    const rightBorder = px(node, "border-right-width");
    return node.clientWidth + leftBorder + rightBorder;
  }
  function getNodeHeight(node) {
    const topBorder = px(node, "border-top-width");
    const bottomBorder = px(node, "border-bottom-width");
    return node.clientHeight + topBorder + bottomBorder;
  }
  function getImageSize(targetNode, options = {}) {
    const width = options.width || getNodeWidth(targetNode);
    const height = options.height || getNodeHeight(targetNode);
    return { width, height };
  }
  function getPixelRatio() {
    let ratio;
    let FINAL_PROCESS;
    try {
      FINAL_PROCESS = process;
    } catch (e) {
    }
    const val = FINAL_PROCESS && FINAL_PROCESS.env ? FINAL_PROCESS.env.devicePixelRatio : null;
    if (val) {
      ratio = parseInt(val, 10);
      if (Number.isNaN(ratio)) {
        ratio = 1;
      }
    }
    return ratio || window.devicePixelRatio || 1;
  }
  const canvasDimensionLimit = 16384;
  function checkCanvasDimensions(canvas) {
    if (canvas.width > canvasDimensionLimit || canvas.height > canvasDimensionLimit) {
      if (canvas.width > canvasDimensionLimit && canvas.height > canvasDimensionLimit) {
        if (canvas.width > canvas.height) {
          canvas.height *= canvasDimensionLimit / canvas.width;
          canvas.width = canvasDimensionLimit;
        } else {
          canvas.width *= canvasDimensionLimit / canvas.height;
          canvas.height = canvasDimensionLimit;
        }
      } else if (canvas.width > canvasDimensionLimit) {
        canvas.height *= canvasDimensionLimit / canvas.width;
        canvas.width = canvasDimensionLimit;
      } else {
        canvas.width *= canvasDimensionLimit / canvas.height;
        canvas.height = canvasDimensionLimit;
      }
    }
  }
  function createImage(url) {
    return new Promise((resolve, reject) => {
      const img = new Image();
      img.decode = () => resolve(img);
      img.onload = () => resolve(img);
      img.onerror = reject;
      img.crossOrigin = "anonymous";
      img.decoding = "async";
      img.src = url;
    });
  }
  function svgToDataURL(svg) {
    return __async(this, null, function* () {
      return Promise.resolve().then(() => new XMLSerializer().serializeToString(svg)).then(encodeURIComponent).then((html) => `data:image/svg+xml;charset=utf-8,${html}`);
    });
  }
  function nodeToDataURL(node, width, height) {
    return __async(this, null, function* () {
      const xmlns = "http://www.w3.org/2000/svg";
      const svg = document.createElementNS(xmlns, "svg");
      const foreignObject = document.createElementNS(xmlns, "foreignObject");
      svg.setAttribute("width", `${width}`);
      svg.setAttribute("height", `${height}`);
      svg.setAttribute("viewBox", `0 0 ${width} ${height}`);
      foreignObject.setAttribute("width", "100%");
      foreignObject.setAttribute("height", "100%");
      foreignObject.setAttribute("x", "0");
      foreignObject.setAttribute("y", "0");
      foreignObject.setAttribute("externalResourcesRequired", "true");
      svg.appendChild(foreignObject);
      foreignObject.appendChild(node);
      return svgToDataURL(svg);
    });
  }
  const isInstanceOfElement = (node, instance) => {
    if (node instanceof instance)
      return true;
    const nodePrototype = Object.getPrototypeOf(node);
    if (nodePrototype === null)
      return false;
    return nodePrototype.constructor.name === instance.name || isInstanceOfElement(nodePrototype, instance);
  };
  function formatCSSText(style) {
    const content = style.getPropertyValue("content");
    return `${style.cssText} content: '${content.replace(/'|"/g, "")}';`;
  }
  function formatCSSProperties(style) {
    return toArray(style).map((name) => {
      const value = style.getPropertyValue(name);
      const priority = style.getPropertyPriority(name);
      return `${name}: ${value}${priority ? " !important" : ""};`;
    }).join(" ");
  }
  function getPseudoElementStyle(className, pseudo, style) {
    const selector = `.${className}:${pseudo}`;
    const cssText = style.cssText ? formatCSSText(style) : formatCSSProperties(style);
    return document.createTextNode(`${selector}{${cssText}}`);
  }
  function clonePseudoElement(nativeNode, clonedNode, pseudo) {
    const style = window.getComputedStyle(nativeNode, pseudo);
    const content = style.getPropertyValue("content");
    if (content === "" || content === "none") {
      return;
    }
    const className = uuid();
    try {
      clonedNode.className = `${clonedNode.className} ${className}`;
    } catch (err) {
      return;
    }
    const styleElement = document.createElement("style");
    styleElement.appendChild(getPseudoElementStyle(className, pseudo, style));
    clonedNode.appendChild(styleElement);
  }
  function clonePseudoElements(nativeNode, clonedNode) {
    clonePseudoElement(nativeNode, clonedNode, ":before");
    clonePseudoElement(nativeNode, clonedNode, ":after");
  }
  const WOFF = "application/font-woff";
  const JPEG = "image/jpeg";
  const mimes = {
    woff: WOFF,
    woff2: WOFF,
    ttf: "application/font-truetype",
    eot: "application/vnd.ms-fontobject",
    png: "image/png",
    jpg: JPEG,
    jpeg: JPEG,
    gif: "image/gif",
    tiff: "image/tiff",
    svg: "image/svg+xml",
    webp: "image/webp"
  };
  function getExtension(url) {
    const match = /\.([^./]*?)$/g.exec(url);
    return match ? match[1] : "";
  }
  function getMimeType(url) {
    const extension = getExtension(url).toLowerCase();
    return mimes[extension] || "";
  }
  function getContentFromDataUrl(dataURL) {
    return dataURL.split(/,/)[1];
  }
  function isDataUrl(url) {
    return url.search(/^(data:)/) !== -1;
  }
  function makeDataUrl(content, mimeType) {
    return `data:${mimeType};base64,${content}`;
  }
  function fetchAsDataURL(url, init, process2) {
    return __async(this, null, function* () {
      const res = yield fetch(url, init);
      if (res.status === 404) {
        throw new Error(`Resource "${res.url}" not found`);
      }
      const blob = yield res.blob();
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onerror = reject;
        reader.onloadend = () => {
          try {
            resolve(process2({ res, result: reader.result }));
          } catch (error) {
            reject(error);
          }
        };
        reader.readAsDataURL(blob);
      });
    });
  }
  const cache = {};
  function getCacheKey(url, contentType, includeQueryParams) {
    let key = url.replace(/\?.*/, "");
    if (includeQueryParams) {
      key = url;
    }
    if (/ttf|otf|eot|woff2?/i.test(key)) {
      key = key.replace(/.*\//, "");
    }
    return contentType ? `[${contentType}]${key}` : key;
  }
  function resourceToDataURL(resourceUrl, contentType, options) {
    return __async(this, null, function* () {
      const cacheKey = getCacheKey(resourceUrl, contentType, options.includeQueryParams);
      if (cache[cacheKey] != null) {
        return cache[cacheKey];
      }
      if (options.cacheBust) {
        resourceUrl += (/\?/.test(resourceUrl) ? "&" : "?") + (/* @__PURE__ */ new Date()).getTime();
      }
      let dataURL;
      try {
        const content = yield fetchAsDataURL(resourceUrl, options.fetchRequestInit, ({ res, result }) => {
          if (!contentType) {
            contentType = res.headers.get("Content-Type") || "";
          }
          return getContentFromDataUrl(result);
        });
        dataURL = makeDataUrl(content, contentType);
      } catch (error) {
        dataURL = options.imagePlaceholder || "";
        let msg = `Failed to fetch resource: ${resourceUrl}`;
        if (error) {
          msg = typeof error === "string" ? error : error.message;
        }
        if (msg) {
          console.warn(msg);
        }
      }
      cache[cacheKey] = dataURL;
      return dataURL;
    });
  }
  function cloneCanvasElement(canvas) {
    return __async(this, null, function* () {
      const dataURL = canvas.toDataURL();
      if (dataURL === "data:,") {
        return canvas.cloneNode(false);
      }
      return createImage(dataURL);
    });
  }
  function cloneVideoElement(video, options) {
    return __async(this, null, function* () {
      if (video.currentSrc) {
        const canvas = document.createElement("canvas");
        const ctx = canvas.getContext("2d");
        canvas.width = video.clientWidth;
        canvas.height = video.clientHeight;
        ctx === null || ctx === void 0 ? void 0 : ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const dataURL2 = canvas.toDataURL();
        return createImage(dataURL2);
      }
      const poster = video.poster;
      const contentType = getMimeType(poster);
      const dataURL = yield resourceToDataURL(poster, contentType, options);
      return createImage(dataURL);
    });
  }
  function cloneIFrameElement(iframe) {
    return __async(this, null, function* () {
      var _a;
      try {
        if ((_a = iframe === null || iframe === void 0 ? void 0 : iframe.contentDocument) === null || _a === void 0 ? void 0 : _a.body) {
          return yield cloneNode(iframe.contentDocument.body, {}, true);
        }
      } catch (_b) {
      }
      return iframe.cloneNode(false);
    });
  }
  function cloneSingleNode(node, options) {
    return __async(this, null, function* () {
      if (isInstanceOfElement(node, HTMLCanvasElement)) {
        return cloneCanvasElement(node);
      }
      if (isInstanceOfElement(node, HTMLVideoElement)) {
        return cloneVideoElement(node, options);
      }
      if (isInstanceOfElement(node, HTMLIFrameElement)) {
        return cloneIFrameElement(node);
      }
      return node.cloneNode(false);
    });
  }
  const isSlotElement = (node) => node.tagName != null && node.tagName.toUpperCase() === "SLOT";
  function cloneChildren(nativeNode, clonedNode, options) {
    return __async(this, null, function* () {
      var _a, _b;
      let children = [];
      if (isSlotElement(nativeNode) && nativeNode.assignedNodes) {
        children = toArray(nativeNode.assignedNodes());
      } else if (isInstanceOfElement(nativeNode, HTMLIFrameElement) && ((_a = nativeNode.contentDocument) === null || _a === void 0 ? void 0 : _a.body)) {
        children = toArray(nativeNode.contentDocument.body.childNodes);
      } else {
        children = toArray(((_b = nativeNode.shadowRoot) !== null && _b !== void 0 ? _b : nativeNode).childNodes);
      }
      if (children.length === 0 || isInstanceOfElement(nativeNode, HTMLVideoElement)) {
        return clonedNode;
      }
      yield children.reduce((deferred, child) => deferred.then(() => cloneNode(child, options)).then((clonedChild) => {
        if (clonedChild) {
          clonedNode.appendChild(clonedChild);
        }
      }), Promise.resolve());
      return clonedNode;
    });
  }
  function cloneCSSStyle(nativeNode, clonedNode) {
    const targetStyle = clonedNode.style;
    if (!targetStyle) {
      return;
    }
    const sourceStyle = window.getComputedStyle(nativeNode);
    if (sourceStyle.cssText) {
      targetStyle.cssText = sourceStyle.cssText;
      targetStyle.transformOrigin = sourceStyle.transformOrigin;
    } else {
      toArray(sourceStyle).forEach((name) => {
        let value = sourceStyle.getPropertyValue(name);
        if (name === "font-size" && value.endsWith("px")) {
          const reducedFont = Math.floor(parseFloat(value.substring(0, value.length - 2))) - 0.1;
          value = `${reducedFont}px`;
        }
        if (isInstanceOfElement(nativeNode, HTMLIFrameElement) && name === "display" && value === "inline") {
          value = "block";
        }
        if (name === "d" && clonedNode.getAttribute("d")) {
          value = `path(${clonedNode.getAttribute("d")})`;
        }
        targetStyle.setProperty(name, value, sourceStyle.getPropertyPriority(name));
      });
    }
  }
  function cloneInputValue(nativeNode, clonedNode) {
    if (isInstanceOfElement(nativeNode, HTMLTextAreaElement)) {
      clonedNode.innerHTML = nativeNode.value;
    }
    if (isInstanceOfElement(nativeNode, HTMLInputElement)) {
      clonedNode.setAttribute("value", nativeNode.value);
    }
  }
  function cloneSelectValue(nativeNode, clonedNode) {
    if (isInstanceOfElement(nativeNode, HTMLSelectElement)) {
      const clonedSelect = clonedNode;
      const selectedOption = Array.from(clonedSelect.children).find((child) => nativeNode.value === child.getAttribute("value"));
      if (selectedOption) {
        selectedOption.setAttribute("selected", "");
      }
    }
  }
  function decorate(nativeNode, clonedNode) {
    if (isInstanceOfElement(clonedNode, Element)) {
      cloneCSSStyle(nativeNode, clonedNode);
      clonePseudoElements(nativeNode, clonedNode);
      cloneInputValue(nativeNode, clonedNode);
      cloneSelectValue(nativeNode, clonedNode);
    }
    return clonedNode;
  }
  function ensureSVGSymbols(clone, options) {
    return __async(this, null, function* () {
      const uses = clone.querySelectorAll ? clone.querySelectorAll("use") : [];
      if (uses.length === 0) {
        return clone;
      }
      const processedDefs = {};
      for (let i = 0; i < uses.length; i++) {
        const use = uses[i];
        const id = use.getAttribute("xlink:href");
        if (id) {
          const exist = clone.querySelector(id);
          const definition = document.querySelector(id);
          if (!exist && definition && !processedDefs[id]) {
            processedDefs[id] = yield cloneNode(definition, options, true);
          }
        }
      }
      const nodes = Object.values(processedDefs);
      if (nodes.length) {
        const ns = "http://www.w3.org/1999/xhtml";
        const svg = document.createElementNS(ns, "svg");
        svg.setAttribute("xmlns", ns);
        svg.style.position = "absolute";
        svg.style.width = "0";
        svg.style.height = "0";
        svg.style.overflow = "hidden";
        svg.style.display = "none";
        const defs = document.createElementNS(ns, "defs");
        svg.appendChild(defs);
        for (let i = 0; i < nodes.length; i++) {
          defs.appendChild(nodes[i]);
        }
        clone.appendChild(svg);
      }
      return clone;
    });
  }
  function cloneNode(node, options, isRoot) {
    return __async(this, null, function* () {
      if (!isRoot && options.filter && !options.filter(node)) {
        return null;
      }
      return Promise.resolve(node).then((clonedNode) => cloneSingleNode(clonedNode, options)).then((clonedNode) => cloneChildren(node, clonedNode, options)).then((clonedNode) => decorate(node, clonedNode)).then((clonedNode) => ensureSVGSymbols(clonedNode, options));
    });
  }
  const URL_REGEX = /url\((['"]?)([^'"]+?)\1\)/g;
  const URL_WITH_FORMAT_REGEX = /url\([^)]+\)\s*format\((["']?)([^"']+)\1\)/g;
  const FONT_SRC_REGEX = /src:\s*(?:url\([^)]+\)\s*format\([^)]+\)[,;]\s*)+/g;
  function toRegex(url) {
    const escaped = url.replace(/([.*+?^${}()|\[\]\/\\])/g, "\\$1");
    return new RegExp(`(url\\(['"]?)(${escaped})(['"]?\\))`, "g");
  }
  function parseURLs(cssText) {
    const urls = [];
    cssText.replace(URL_REGEX, (raw, quotation, url) => {
      urls.push(url);
      return raw;
    });
    return urls.filter((url) => !isDataUrl(url));
  }
  function embed(cssText, resourceURL, baseURL, options, getContentFromUrl) {
    return __async(this, null, function* () {
      try {
        const resolvedURL = baseURL ? resolveUrl(resourceURL, baseURL) : resourceURL;
        const contentType = getMimeType(resourceURL);
        let dataURL;
        if (getContentFromUrl) {
          const content = yield getContentFromUrl(resolvedURL);
          dataURL = makeDataUrl(content, contentType);
        } else {
          dataURL = yield resourceToDataURL(resolvedURL, contentType, options);
        }
        return cssText.replace(toRegex(resourceURL), `$1${dataURL}$3`);
      } catch (error) {
      }
      return cssText;
    });
  }
  function filterPreferredFontFormat(str, { preferredFontFormat }) {
    return !preferredFontFormat ? str : str.replace(FONT_SRC_REGEX, (match) => {
      while (true) {
        const [src, , format] = URL_WITH_FORMAT_REGEX.exec(match) || [];
        if (!format) {
          return "";
        }
        if (format === preferredFontFormat) {
          return `src: ${src};`;
        }
      }
    });
  }
  function shouldEmbed(url) {
    return url.search(URL_REGEX) !== -1;
  }
  function embedResources(cssText, baseUrl, options) {
    return __async(this, null, function* () {
      if (!shouldEmbed(cssText)) {
        return cssText;
      }
      const filteredCSSText = filterPreferredFontFormat(cssText, options);
      const urls = parseURLs(filteredCSSText);
      return urls.reduce((deferred, url) => deferred.then((css) => embed(css, url, baseUrl, options)), Promise.resolve(filteredCSSText));
    });
  }
  function embedProp(propName, node, options) {
    return __async(this, null, function* () {
      var _a;
      const propValue = (_a = node.style) === null || _a === void 0 ? void 0 : _a.getPropertyValue(propName);
      if (propValue) {
        const cssString = yield embedResources(propValue, null, options);
        node.style.setProperty(propName, cssString, node.style.getPropertyPriority(propName));
        return true;
      }
      return false;
    });
  }
  function embedBackground(clonedNode, options) {
    return __async(this, null, function* () {
      if (!(yield embedProp("background", clonedNode, options))) {
        yield embedProp("background-image", clonedNode, options);
      }
      if (!(yield embedProp("mask", clonedNode, options))) {
        yield embedProp("mask-image", clonedNode, options);
      }
    });
  }
  function embedImageNode(clonedNode, options) {
    return __async(this, null, function* () {
      const isImageElement = isInstanceOfElement(clonedNode, HTMLImageElement);
      if (!(isImageElement && !isDataUrl(clonedNode.src)) && !(isInstanceOfElement(clonedNode, SVGImageElement) && !isDataUrl(clonedNode.href.baseVal))) {
        return;
      }
      const url = isImageElement ? clonedNode.src : clonedNode.href.baseVal;
      const dataURL = yield resourceToDataURL(url, getMimeType(url), options);
      yield new Promise((resolve, reject) => {
        clonedNode.onload = resolve;
        clonedNode.onerror = reject;
        const image = clonedNode;
        if (image.decode) {
          image.decode = resolve;
        }
        if (image.loading === "lazy") {
          image.loading = "eager";
        }
        if (isImageElement) {
          clonedNode.srcset = "";
          clonedNode.src = dataURL;
        } else {
          clonedNode.href.baseVal = dataURL;
        }
      });
    });
  }
  function embedChildren(clonedNode, options) {
    return __async(this, null, function* () {
      const children = toArray(clonedNode.childNodes);
      const deferreds = children.map((child) => embedImages(child, options));
      yield Promise.all(deferreds).then(() => clonedNode);
    });
  }
  function embedImages(clonedNode, options) {
    return __async(this, null, function* () {
      if (isInstanceOfElement(clonedNode, Element)) {
        yield embedBackground(clonedNode, options);
        yield embedImageNode(clonedNode, options);
        yield embedChildren(clonedNode, options);
      }
    });
  }
  function applyStyle(node, options) {
    const { style } = node;
    if (options.backgroundColor) {
      style.backgroundColor = options.backgroundColor;
    }
    if (options.width) {
      style.width = `${options.width}px`;
    }
    if (options.height) {
      style.height = `${options.height}px`;
    }
    const manual = options.style;
    if (manual != null) {
      Object.keys(manual).forEach((key) => {
        style[key] = manual[key];
      });
    }
    return node;
  }
  const cssFetchCache = {};
  function fetchCSS(url) {
    return __async(this, null, function* () {
      let cache2 = cssFetchCache[url];
      if (cache2 != null) {
        return cache2;
      }
      const res = yield fetch(url);
      const cssText = yield res.text();
      cache2 = { url, cssText };
      cssFetchCache[url] = cache2;
      return cache2;
    });
  }
  function embedFonts(data, options) {
    return __async(this, null, function* () {
      let cssText = data.cssText;
      const regexUrl = /url\(["']?([^"')]+)["']?\)/g;
      const fontLocs = cssText.match(/url\([^)]+\)/g) || [];
      const loadFonts = fontLocs.map((loc) => __async(this, null, function* () {
        let url = loc.replace(regexUrl, "$1");
        if (!url.startsWith("https://")) {
          url = new URL(url, data.url).href;
        }
        return fetchAsDataURL(url, options.fetchRequestInit, ({ result }) => {
          cssText = cssText.replace(loc, `url(${result})`);
          return [loc, result];
        });
      }));
      return Promise.all(loadFonts).then(() => cssText);
    });
  }
  function parseCSS(source) {
    if (source == null) {
      return [];
    }
    const result = [];
    const commentsRegex = /(\/\*[\s\S]*?\*\/)/gi;
    let cssText = source.replace(commentsRegex, "");
    const keyframesRegex = new RegExp("((@.*?keyframes [\\s\\S]*?){([\\s\\S]*?}\\s*?)})", "gi");
    while (true) {
      const matches = keyframesRegex.exec(cssText);
      if (matches === null) {
        break;
      }
      result.push(matches[0]);
    }
    cssText = cssText.replace(keyframesRegex, "");
    const importRegex = /@import[\s\S]*?url\([^)]*\)[\s\S]*?;/gi;
    const combinedCSSRegex = "((\\s*?(?:\\/\\*[\\s\\S]*?\\*\\/)?\\s*?@media[\\s\\S]*?){([\\s\\S]*?)}\\s*?})|(([\\s\\S]*?){([\\s\\S]*?)})";
    const unifiedRegex = new RegExp(combinedCSSRegex, "gi");
    while (true) {
      let matches = importRegex.exec(cssText);
      if (matches === null) {
        matches = unifiedRegex.exec(cssText);
        if (matches === null) {
          break;
        } else {
          importRegex.lastIndex = unifiedRegex.lastIndex;
        }
      } else {
        unifiedRegex.lastIndex = importRegex.lastIndex;
      }
      result.push(matches[0]);
    }
    return result;
  }
  function getCSSRules(styleSheets, options) {
    return __async(this, null, function* () {
      const ret = [];
      const deferreds = [];
      styleSheets.forEach((sheet) => {
        if ("cssRules" in sheet) {
          try {
            toArray(sheet.cssRules || []).forEach((item, index2) => {
              if (item.type === CSSRule.IMPORT_RULE) {
                let importIndex = index2 + 1;
                const url = item.href;
                const deferred = fetchCSS(url).then((metadata) => embedFonts(metadata, options)).then((cssText) => parseCSS(cssText).forEach((rule) => {
                  try {
                    sheet.insertRule(rule, rule.startsWith("@import") ? importIndex += 1 : sheet.cssRules.length);
                  } catch (error) {
                    console.error("Error inserting rule from remote css", {
                      rule,
                      error
                    });
                  }
                })).catch((e) => {
                  console.error("Error loading remote css", e.toString());
                });
                deferreds.push(deferred);
              }
            });
          } catch (e) {
            const inline = styleSheets.find((a) => a.href == null) || document.styleSheets[0];
            if (sheet.href != null) {
              deferreds.push(fetchCSS(sheet.href).then((metadata) => embedFonts(metadata, options)).then((cssText) => parseCSS(cssText).forEach((rule) => {
                inline.insertRule(rule, sheet.cssRules.length);
              })).catch((err) => {
                console.error("Error loading remote stylesheet", err);
              }));
            }
            console.error("Error inlining remote css file", e);
          }
        }
      });
      return Promise.all(deferreds).then(() => {
        styleSheets.forEach((sheet) => {
          if ("cssRules" in sheet) {
            try {
              toArray(sheet.cssRules || []).forEach((item) => {
                ret.push(item);
              });
            } catch (e) {
              console.error(`Error while reading CSS rules from ${sheet.href}`, e);
            }
          }
        });
        return ret;
      });
    });
  }
  function getWebFontRules(cssRules) {
    return cssRules.filter((rule) => rule.type === CSSRule.FONT_FACE_RULE).filter((rule) => shouldEmbed(rule.style.getPropertyValue("src")));
  }
  function parseWebFontRules(node, options) {
    return __async(this, null, function* () {
      if (node.ownerDocument == null) {
        throw new Error("Provided element is not within a Document");
      }
      const styleSheets = toArray(node.ownerDocument.styleSheets);
      const cssRules = yield getCSSRules(styleSheets, options);
      return getWebFontRules(cssRules);
    });
  }
  function getWebFontCSS(node, options) {
    return __async(this, null, function* () {
      const rules = yield parseWebFontRules(node, options);
      const cssTexts = yield Promise.all(rules.map((rule) => {
        const baseUrl = rule.parentStyleSheet ? rule.parentStyleSheet.href : null;
        return embedResources(rule.cssText, baseUrl, options);
      }));
      return cssTexts.join("\n");
    });
  }
  function embedWebFonts(clonedNode, options) {
    return __async(this, null, function* () {
      const cssText = options.fontEmbedCSS != null ? options.fontEmbedCSS : options.skipFonts ? null : yield getWebFontCSS(clonedNode, options);
      if (cssText) {
        const styleNode = document.createElement("style");
        const sytleContent = document.createTextNode(cssText);
        styleNode.appendChild(sytleContent);
        if (clonedNode.firstChild) {
          clonedNode.insertBefore(styleNode, clonedNode.firstChild);
        } else {
          clonedNode.appendChild(styleNode);
        }
      }
    });
  }
  function toSvg(_0) {
    return __async(this, arguments, function* (node, options = {}) {
      const { width, height } = getImageSize(node, options);
      const clonedNode = yield cloneNode(node, options, true);
      yield embedWebFonts(clonedNode, options);
      yield embedImages(clonedNode, options);
      applyStyle(clonedNode, options);
      const datauri = yield nodeToDataURL(clonedNode, width, height);
      return datauri;
    });
  }
  function toCanvas(_0) {
    return __async(this, arguments, function* (node, options = {}) {
      const { width, height } = getImageSize(node, options);
      const svg = yield toSvg(node, options);
      const img = yield createImage(svg);
      const canvas = document.createElement("canvas");
      const context = canvas.getContext("2d");
      const ratio = options.pixelRatio || getPixelRatio();
      const canvasWidth = options.canvasWidth || width;
      const canvasHeight = options.canvasHeight || height;
      canvas.width = canvasWidth * ratio;
      canvas.height = canvasHeight * ratio;
      if (!options.skipAutoScale) {
        checkCanvasDimensions(canvas);
      }
      canvas.style.width = `${canvasWidth}`;
      canvas.style.height = `${canvasHeight}`;
      if (options.backgroundColor) {
        context.fillStyle = options.backgroundColor;
        context.fillRect(0, 0, canvas.width, canvas.height);
      }
      context.drawImage(img, 0, 0, canvas.width, canvas.height);
      return canvas;
    });
  }
  function toPng(_0) {
    return __async(this, arguments, function* (node, options = {}) {
      const canvas = yield toCanvas(node, options);
      return canvas.toDataURL();
    });
  }
  function screenshot() {
    const options = window.ZnPbScreenshotData;
    function init() {
      handleIframe();
      handleCSS();
      handleImages();
      window.addEventListener("load", createImage2);
    }
    function createImage2() {
      toPng(document.body).then((dataUrl) => {
        sendMessage({
          success: true,
          thumbnail: dataUrl
        });
      }).catch((error) => {
        sendMessage({
          success: false,
          errorMessage: error
        });
      });
    }
    function handleCSS() {
      const stylesheets = document.getElementsByTagName("link");
      const externalStylesheets = Array.from(stylesheets).filter((stylesheet) => {
        return stylesheet.rel === "stylesheet" && stylesheet.href.indexOf(options.home_url) === -1;
      });
      for (const stylesheet of externalStylesheets) {
        stylesheet.href = getProxyURL(stylesheet.href);
      }
    }
    function getProxyURL(url) {
      return `${options.home_url}?${options.constants.PROXY_URL_ARGUMENT}&${options.constants.PROXY_URL_NONCE_KEY}=${options.nonce_key}&${options.constants.PROXY_ASSET_PARAM}=${url}`;
    }
    function handleImages() {
      const images = document.getElementsByTagName("img");
      const externalImages = Array.from(images).filter((image) => {
        return image.src.indexOf(options.home_url) === -1;
      });
      for (const image of externalImages) {
        image.src = getProxyURL(image.src);
      }
    }
    function handleIframe() {
      const iframe = document.getElementsByTagName("iframe");
      Array.from(iframe).forEach((iframe2) => {
        var _a;
        const { width, height } = iframe2.getBoundingClientRect();
        const iframePlaceholder = document.createElement("div");
        iframePlaceholder.style.width = `${width}px`;
        iframePlaceholder.style.height = `${height}px`;
        iframePlaceholder.classList.add("znpb-iframePlaceholderBG");
        (_a = iframe2.parentElement) == null ? void 0 : _a.replaceChild(iframePlaceholder, iframe2);
      });
    }
    function sendMessage(message) {
      window.postMessage(__spreadValues({
        type: "zionbuilder-screenshot"
      }, message));
    }
    try {
      init();
    } catch (error) {
      console.error(error);
      sendMessage({
        success: false,
        error
      });
    }
  }
  screenshot();
})();

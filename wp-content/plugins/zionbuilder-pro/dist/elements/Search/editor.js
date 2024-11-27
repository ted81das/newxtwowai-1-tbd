(function(vue) {
  "use strict";
  const _hoisted_1 = { class: "zb-el-search__form" };
  const _hoisted_2 = ["placeholder"];
  const _hoisted_3 = {
    key: 1,
    type: "hidden",
    name: "post_type",
    value: "product"
  };
  const _sfc_main = /* @__PURE__ */ vue.defineComponent({
    __name: "Search",
    props: {
      options: {},
      api: {},
      element: {}
    },
    setup(__props) {
      const props = __props;
      const getPlaceholder = vue.computed(() => {
        return props.options.placeholder_text || "Search for articles";
      });
      const getButtonText = vue.computed(() => {
        return props.options.search_text || "Search";
      });
      const showButton = vue.computed(() => {
        return props.options.show_button ? props.options.show_button : false;
      });
      return (_ctx, _cache) => {
        return vue.openBlock(), vue.createElementBlock("div", null, [
          vue.renderSlot(_ctx.$slots, "start"),
          vue.createElementVNode("form", _hoisted_1, [
            vue.createElementVNode("input", {
              type: "text",
              maxlength: "30",
              name: "s",
              class: vue.normalizeClass(["zb-el-search__input", _ctx.api.getStyleClasses("input_styles")]),
              placeholder: getPlaceholder.value
            }, null, 10, _hoisted_2),
            showButton.value ? (vue.openBlock(), vue.createElementBlock("button", {
              key: 0,
              type: "submit",
              alt: "Search",
              class: vue.normalizeClass(["zb-el-search__submit", _ctx.api.getStyleClasses("button_styles")]),
              value: "Search"
            }, vue.toDisplayString(getButtonText.value), 3)) : vue.createCommentVNode("", true),
            _ctx.options.woocommerce ? (vue.openBlock(), vue.createElementBlock("input", _hoisted_3)) : vue.createCommentVNode("", true)
          ]),
          vue.renderSlot(_ctx.$slots, "end")
        ]);
      };
    }
  });
  window.zb.editor.registerElementComponent({
    elementType: "search",
    component: _sfc_main
  });
})(zb.vue);

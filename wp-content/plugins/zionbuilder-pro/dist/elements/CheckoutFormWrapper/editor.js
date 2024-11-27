(function(vue) {
  "use strict";
  const _sfc_main = /* @__PURE__ */ vue.defineComponent({
    __name: "CheckoutFormWrapper",
    props: {
      options: {},
      api: {},
      element: {}
    },
    setup(__props) {
      return (_ctx, _cache) => {
        const _component_SortableContent = vue.resolveComponent("SortableContent");
        return vue.openBlock(), vue.createBlock(_component_SortableContent, {
          class: "checkout woocommerce-checkout",
          element: _ctx.element,
          tag: "form",
          style: { "width": "100%" }
        }, {
          start: vue.withCtx(() => [
            vue.renderSlot(_ctx.$slots, "start")
          ]),
          end: vue.withCtx(() => [
            vue.renderSlot(_ctx.$slots, "end")
          ]),
          _: 3
        }, 8, ["element"]);
      };
    }
  });
  window.zb.editor.registerElementComponent({
    elementType: "woo-checkout-form-wrapper",
    component: _sfc_main
  });
})(zb.vue);

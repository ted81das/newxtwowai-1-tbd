(function(vue) {
  "use strict";
  const _sfc_main = /* @__PURE__ */ vue.defineComponent({
    __name: "Modal",
    props: {
      options: {},
      api: {},
      element: {}
    },
    setup(__props) {
      const props = __props;
      const root = vue.ref(null);
      const inlineClass = vue.computed(() => {
        if (props.options.modal_state) {
          switch (props.options.modal_state) {
            case "open":
              return "zb-modal--open";
            case "inline":
              return "zb-modal--inline";
            default:
              return "zb-modal--inline";
          }
        }
        return "zb-modal--inline";
      });
      return (_ctx, _cache) => {
        const _component_SortableContent = vue.resolveComponent("SortableContent");
        return vue.openBlock(), vue.createElementBlock("div", {
          ref_key: "root",
          ref: root,
          class: vue.normalizeClass(["zb-modal", inlineClass.value])
        }, [
          vue.renderSlot(_ctx.$slots, "start"),
          vue.createVNode(_component_SortableContent, vue.mergeProps({
            element: _ctx.element,
            class: "zb-modalContent"
          }, _ctx.api.getAttributesForTag("modal_content")), {
            end: vue.withCtx(() => [
              vue.createElementVNode("div", vue.mergeProps({ class: "zb-modalClose" }, _ctx.api.getAttributesForTag("close_button")), null, 16)
            ]),
            _: 1
          }, 16, ["element"]),
          vue.renderSlot(_ctx.$slots, "end")
        ], 2);
      };
    }
  });
  const Modal_vue_vue_type_style_index_0_lang = "";
  window.zb.editor.registerElementComponent({
    elementType: "modal",
    component: _sfc_main
  });
})(zb.vue);

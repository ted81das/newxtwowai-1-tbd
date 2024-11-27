(function(vue) {
  "use strict";
  const _sfc_main = /* @__PURE__ */ vue.defineComponent({
    __name: "HeaderBuilder",
    props: {
      options: {},
      element: {},
      api: {}
    },
    setup(__props) {
      const props = __props;
      const htmlTag = vue.computed(() => {
        return props.options.tag && /^[a-z0-9]+$/i.test(props.options.tag) ? props.options.tag : "header";
      });
      return (_ctx, _cache) => {
        const _component_SortableContent = vue.resolveComponent("SortableContent");
        return vue.openBlock(), vue.createBlock(_component_SortableContent, {
          element: _ctx.element,
          tag: htmlTag.value
        }, {
          start: vue.withCtx(() => [
            vue.renderSlot(_ctx.$slots, "start")
          ]),
          end: vue.withCtx(() => [
            vue.renderSlot(_ctx.$slots, "end")
          ]),
          _: 3
        }, 8, ["element", "tag"]);
      };
    }
  });
  window.zb.editor.registerElementComponent({
    elementType: "zion_header_builder",
    component: _sfc_main
  });
})(zb.vue);

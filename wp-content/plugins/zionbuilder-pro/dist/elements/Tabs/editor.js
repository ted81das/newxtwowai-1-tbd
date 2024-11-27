(function(vue, editor) {
  "use strict";
  const _sfc_main = /* @__PURE__ */ vue.defineComponent({
    __name: "TabsItem",
    props: {
      options: {},
      api: {},
      element: {}
    },
    setup(__props) {
      const props = __props;
      if (props.element.content && props.element.content.length === 0) {
        props.element.addChild({
          element_type: "zion_text"
        });
      }
      const UIStore = editor.useUIStore();
      const TabsElement = vue.inject("TabsElement", null);
      vue.watch(
        () => UIStore.editedElement,
        () => {
          if (TabsElement && UIStore.editedElement === props.element) {
            TabsElement.changeTab(props.element.uid);
          }
        }
      );
      return (_ctx, _cache) => {
        const _component_SortableContent = vue.resolveComponent("SortableContent");
        return vue.openBlock(), vue.createBlock(_component_SortableContent, { element: _ctx.element }, {
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
  const TabsItem_vue_vue_type_style_index_0_lang = "";
  window.zb.editor.registerElementComponent({
    elementType: "tabs_item",
    component: _sfc_main
  });
})(zb.vue, zb.editor);

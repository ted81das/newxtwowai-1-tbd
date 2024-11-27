(function(vue) {
  "use strict";
  const _hoisted_1 = /* @__PURE__ */ vue.createElementVNode("span", { class: "zb-el-accordions-accordionIcon" }, null, -1);
  const _sfc_main = /* @__PURE__ */ vue.defineComponent({
    __name: "accordionItem",
    props: {
      options: {},
      api: {},
      element: {}
    },
    setup(__props) {
      const props = __props;
      const accordionApi = vue.inject("accordionsApi", null);
      const titleTag = vue.computed(() => {
        const parentAccordionTitle = accordionApi ? accordionApi.options.value.title_tag : "div";
        return props.options.title_tag || parentAccordionTitle || "div";
      });
      if (props.element.content.length === 0) {
        props.element.addChild({
          element_type: "zion_text"
        });
      }
      return (_ctx, _cache) => {
        const _component_SortableContent = vue.resolveComponent("SortableContent");
        return vue.unref(accordionApi) ? (vue.openBlock(), vue.createElementBlock("div", {
          key: 0,
          class: vue.normalizeClass(["zb-el-accordions-accordionWrapper", { "zb-el-accordions--active": _ctx.options.active_by_default }])
        }, [
          vue.renderSlot(_ctx.$slots, "start"),
          (vue.openBlock(), vue.createBlock(vue.resolveDynamicComponent(titleTag.value), vue.mergeProps({
            class: ["zb-el-accordions-accordionTitle", vue.unref(accordionApi).getStyleClasses("inner_content_styles_title")]
          }, vue.unref(accordionApi).getAttributesForTag("inner_content_styles_title")), {
            default: vue.withCtx(() => [
              vue.createTextVNode(vue.toDisplayString(_ctx.options.title) + " ", 1),
              _hoisted_1
            ]),
            _: 1
          }, 16, ["class"])),
          vue.createElementVNode("div", vue.mergeProps({
            class: ["zb-el-accordions-accordionContent", vue.unref(accordionApi).getStyleClasses("inner_content_styles_content")]
          }, vue.unref(accordionApi).getAttributesForTag("inner_content_styles_content")), [
            vue.createVNode(_component_SortableContent, {
              element: _ctx.element,
              class: "zb-el-accordions-accordionContent__inner"
            }, {
              start: vue.withCtx(() => [
                vue.renderSlot(_ctx.$slots, "start")
              ]),
              end: vue.withCtx(() => [
                vue.renderSlot(_ctx.$slots, "end")
              ]),
              _: 3
            }, 8, ["element"])
          ], 16),
          vue.renderSlot(_ctx.$slots, "end")
        ], 2)) : vue.createCommentVNode("", true);
      };
    }
  });
  const accordionItem_vue_vue_type_style_index_0_lang = "";
  window.zb.editor.registerElementComponent({
    elementType: "accordion_item",
    component: _sfc_main
  });
})(zb.vue);

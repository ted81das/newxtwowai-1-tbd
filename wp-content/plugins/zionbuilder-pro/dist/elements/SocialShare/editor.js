(function(vue) {
  "use strict";
  const _hoisted_1 = {
    key: 0,
    class: "zb-el-socialShare__label"
  };
  const _sfc_main = /* @__PURE__ */ vue.defineComponent({
    __name: "SocialShare",
    props: {
      options: {},
      api: {},
      element: {}
    },
    setup(__props) {
      const props = __props;
      const iconConfig = vue.computed(() => {
        return props.options.share_icon_group ? props.options.share_icon_group : [];
      });
      function getIcon(config) {
        if (config !== void 0) {
          return config.name;
        } else
          return "";
      }
      return (_ctx, _cache) => {
        const _component_ElementIcon = vue.resolveComponent("ElementIcon");
        return vue.openBlock(), vue.createElementBlock("div", null, [
          vue.renderSlot(_ctx.$slots, "start"),
          (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(iconConfig.value, (item, index) => {
            return vue.openBlock(), vue.createElementBlock("a", {
              key: index,
              href: "#",
              class: vue.normalizeClass(["zb-el-socialShare__item", [
                _ctx.api.getStyleClasses("social_block"),
                { [`zb-el-socialShare__item--is-${getIcon(item.icon)}`]: getIcon(item.icon) }
              ]])
            }, [
              vue.createVNode(_component_ElementIcon, vue.mergeProps({ class: "zb-el-socialShare__icon" }, _ctx.api.getAttributesForTag("icon_styles"), {
                "icon-config": item.icon
              }), null, 16, ["icon-config"]),
              item.icon_label ? (vue.openBlock(), vue.createElementBlock("span", _hoisted_1, vue.toDisplayString(item.icon_label), 1)) : vue.createCommentVNode("", true)
            ], 2);
          }), 128)),
          vue.renderSlot(_ctx.$slots, "end")
        ]);
      };
    }
  });
  window.zb.editor.registerElementComponent({
    elementType: "social_share",
    component: _sfc_main
  });
})(zb.vue);

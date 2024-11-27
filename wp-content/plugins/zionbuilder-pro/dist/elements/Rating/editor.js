(function(vue) {
  "use strict";
  const _sfc_main = /* @__PURE__ */ vue.defineComponent({
    __name: "Rating",
    props: {
      options: {},
      api: {},
      element: {}
    },
    setup(__props) {
      const props = __props;
      const numberOfIcons = vue.computed(() => {
        return props.options.number_of_icons || 5;
      });
      const iconConfig = vue.computed(() => {
        return props.options.icon;
      });
      const computedStyles = vue.computed(() => {
        const filledColor = props.options.fill_color;
        const unFilledColor = props.options.unfilled_color;
        const ratingValue = props.options.rating_value;
        const styles = {
          "background-image": `linear-gradient(90deg, ${filledColor} ${ratingValue}%, ${unFilledColor} ${ratingValue}%)`
        };
        return styles;
      });
      return (_ctx, _cache) => {
        const _component_ElementIcon = vue.resolveComponent("ElementIcon");
        return vue.openBlock(), vue.createElementBlock("div", null, [
          vue.renderSlot(_ctx.$slots, "start"),
          vue.createElementVNode("div", {
            class: "zb-el-ratingWrapper",
            style: vue.normalizeStyle(computedStyles.value)
          }, [
            (vue.openBlock(true), vue.createElementBlock(vue.Fragment, null, vue.renderList(numberOfIcons.value, (index) => {
              return vue.openBlock(), vue.createBlock(_component_ElementIcon, vue.mergeProps({
                key: index,
                "icon-config": iconConfig.value
              }, _ctx.api.getAttributesForTag("icon"), { class: "zb-el-ratingIcon" }), null, 16, ["icon-config"]);
            }), 128))
          ], 4),
          vue.renderSlot(_ctx.$slots, "end")
        ]);
      };
    }
  });
  window.zb.editor.registerElementComponent({
    elementType: "rating",
    component: _sfc_main
  });
})(zb.vue);

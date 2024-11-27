(function(vue) {
  "use strict";
  const _hoisted_1 = ["innerHTML"];
  const _hoisted_2 = ["innerHTML"];
  const _sfc_main = /* @__PURE__ */ vue.defineComponent({
    __name: "customCode",
    props: {
      options: {},
      api: {},
      element: {}
    },
    setup(__props) {
      const props = __props;
      const phpMarkup = vue.ref("");
      const phpError = vue.ref("");
      const content = vue.computed(() => {
        return props.options.content + phpMarkup.value;
      });
      function onApplyPHPCode() {
        if (props.options.php && props.options.php.length > 0) {
          window.zb.editor.serverRequest.request(
            {
              type: "parse_php",
              config: props.options.php
            },
            (response) => {
              if (response && response.error) {
                phpError.value = response.message;
                phpMarkup.value = "";
              } else {
                phpMarkup.value = response;
                phpError.value = "";
              }
            },
            function(message) {
              console.log("server Request fail", message);
            }
          );
        }
      }
      props.element.on("apply_php_code", onApplyPHPCode);
      vue.onMounted(onApplyPHPCode);
      vue.onBeforeUnmount(() => props.element.off("apply_php_code", onApplyPHPCode));
      return (_ctx, _cache) => {
        return vue.openBlock(), vue.createElementBlock("div", null, [
          vue.renderSlot(_ctx.$slots, "start"),
          vue.createElementVNode("div", { innerHTML: content.value }, null, 8, _hoisted_1),
          phpError.value.length > 0 ? (vue.openBlock(), vue.createElementBlock("div", {
            key: 0,
            class: "znpb-notice znpb-notice--error",
            innerHTML: phpError.value
          }, null, 8, _hoisted_2)) : vue.createCommentVNode("", true),
          vue.renderSlot(_ctx.$slots, "end")
        ]);
      };
    }
  });
  window.zb.editor.registerElementComponent({
    elementType: "custom_html",
    component: _sfc_main
  });
})(zb.vue);

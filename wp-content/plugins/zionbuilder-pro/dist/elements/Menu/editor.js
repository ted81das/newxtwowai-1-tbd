(function() {
  "use strict";
  const editor = "";
  window.zb.hooks.addAction("zionbuilder/server_component/before_rendered", function(html, element) {
    if (element.element_type === "menu") {
      const menuElement = html.querySelector(".zb-menu");
      if (menuElement && menuElement.zbMenu) {
        menuElement.zbMenu.destroy();
      }
    }
  });
})();

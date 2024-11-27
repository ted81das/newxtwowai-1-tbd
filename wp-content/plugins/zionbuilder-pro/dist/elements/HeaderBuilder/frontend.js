(function() {
  "use strict";
  const main = "";
  function headerBuilder(header) {
    const configString = header.dataset.zbHeaderConfig || {};
    const config = JSON.parse(configString);
    const { sticky = false, stickyThreshold = 0, sticky_appear_animation = false } = config;
    if (sticky && stickyThreshold > 0) {
      document.addEventListener("scroll", stickyHeader);
    }
    const appearCSSClasses = ["zb-headerSticky"];
    if (sticky_appear_animation) {
      appearCSSClasses.push(sticky_appear_animation);
      appearCSSClasses.push("animated");
    }
    function stickyHeader() {
      const scrollPosition = window.scrollY;
      if (scrollPosition > stickyThreshold) {
        header.classList.add(...appearCSSClasses);
      } else {
        header.classList.remove(...appearCSSClasses);
      }
    }
    function destroy() {
      document.removeEventListener("scroll", stickyHeader);
    }
    return {
      destroy
    };
  }
  const elements = Array.from(document.querySelectorAll(".zb-el-zionHeaderBuilder"));
  console.log(elements);
  elements.forEach((element) => {
    headerBuilder(element);
  });
  window.zbScripts = window.zbScripts || {};
  window.zbScripts.headerBuilder = headerBuilder;
})();

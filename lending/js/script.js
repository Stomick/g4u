M.AutoInit();

document.addEventListener("DOMContentLoaded", function() {
  var elems = document.querySelectorAll(".sidenav");
  var tabs = document.querySelector(".modal__tabs");
  var instances = M.Sidenav.init(elems);
  var instance = M.Tabs.init(tabs, { swipeable: true });
});

(function() {
  const panels = document.querySelectorAll(".leagues__panel");

  function toggleOpen() {
    this.classList.toggle("open");
    this.classList.contains("open") && window.innerWidth > 600
      ? panels.forEach(panel => {
          panel.querySelector(".leagues__heading-text").style.display = "none";
        })
      : panels.forEach(panel => {
          panel.querySelector(".leagues__heading-text").style.display =
            "inline";
        });
  }

  function toggleActive(e) {
    if (e.propertyName.includes("flex")) {
      this.classList.toggle("open--active");
    }
  }

  panels.forEach(panel => {
    panel.addEventListener("click", toggleOpen);
  });
  panels.forEach(panel =>
    panel.addEventListener("transitionend", toggleActive)
  );

  document.addEventListener("scroll", function() {
    var nav = document.querySelector(".navigation__nav");
    window.scrollY > 10
      ? (nav.style.backgroundColor = "rgba(0,0,0,.7)")
      : (nav.style.backgroundColor = "");
  });
})();

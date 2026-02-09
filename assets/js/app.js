(function(){
  // Apply theme from cookie
  const match = document.cookie.match(/schola_theme=(dark|light)/);
  const theme = match ? match[1] : "light";
  document.body.setAttribute("data-theme", theme);

  // Toggle button
  const btn = document.getElementById("themeToggle");
  if (btn) {
    btn.addEventListener("click", function(){
      const current = document.body.getAttribute("data-theme") || "light";
      const next = current === "dark" ? "light" : "dark";
      document.body.setAttribute("data-theme", next);

      // save cookie for 1 year
      document.cookie = "schola_theme=" + next + "; path=/; max-age=" + (60*60*24*365);

      // swap icon quickly
      const icon = btn.querySelector("i");
      if (icon) {
        icon.className = "bi " + (next === "dark" ? "bi-sun" : "bi-moon");
      }
    });
  }
})();

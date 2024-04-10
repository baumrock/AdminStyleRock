(() => {
  // handle clicks on darkmode toggle
  document.addEventListener("click", function (e) {
    let el = e.target.closest("[data-darkmode]");
    if (!el) return;
    var darkModeValue = el.getAttribute("data-darkmode");
    localStorage.setItem("asr-darkmode", darkModeValue);
    toggleDarkMode();
  });

  function toggleDarkMode() {
    const isDarkMode = localStorage.getItem("asr-darkmode") == "1";
    if (isDarkMode) {
      document.documentElement.classList.add("asr-dark");
    } else {
      document.documentElement.classList.remove("asr-dark");
    }
  }
  toggleDarkMode();
})();

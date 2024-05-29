// add darkmode toggle to navbar
(() => {
  if (!ProcessWire.config.asrDarkmodeToggle) return;
  document.addEventListener("DOMContentLoaded", function () {
    const navbarRight = document.querySelector(
      "#pw-masthead nav.uk-navbar .uk-navbar-right"
    );
    if (!navbarRight) return;
    navbarRight.insertAdjacentHTML(
      "afterbegin",
      '<div class="darkmode-toggle">' +
        '<a class="uk-link-reset asr-show-light" data-darkmode=1 title="Dark mode" uk-tooltip><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3h.393a7.5 7.5 0 0 0 7.92 12.446A9 9 0 1 1 12 2.992z"/></svg></a>' +
        '<a class="uk-link-reset asr-show-dark" data-darkmode=0 title="Light mode" uk-tooltip><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 1 0-5.656-5.656a4 4 0 0 0 5.656 5.656zm-8.485 2.829l-1.414 1.414M6.343 6.343L4.929 4.929m12.728 1.414l1.414-1.414m-1.414 12.728l1.414 1.414M4 12H2m10-8V2m8 10h2m-10 8v2"/></svg></a>' +
        "</div>"
    );
  });

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

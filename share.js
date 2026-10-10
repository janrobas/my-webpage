(function () {
  "use strict";

  var box = document.querySelector("[data-share]");
  if (!box) return;

  var title = box.getAttribute("data-share-title") || document.title;
  var url = window.location.href;

  var status = document.createElement("span");
  status.className = "note-share-status";
  status.setAttribute("aria-live", "polite");

  function copyText(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      return navigator.clipboard.writeText(text);
    }
    return new Promise(function (resolve, reject) {
      var ta = document.createElement("textarea");
      ta.value = text;
      ta.setAttribute("readonly", "");
      ta.style.position = "absolute";
      ta.style.left = "-9999px";
      document.body.appendChild(ta);
      ta.select();
      try {
        document.execCommand("copy") ? resolve() : reject();
      } catch (e) {
        reject(e);
      }
      document.body.removeChild(ta);
    });
  }

  var btn = document.createElement("button");
  btn.type = "button";
  btn.className = "note-share-btn";

  if (navigator.share) {
    btn.textContent = "Deli zapisek";
    btn.addEventListener("click", function () {
      navigator.share({ title: title, url: url }).catch(function () {});
    });
    box.appendChild(btn);
    return;
  }

  btn.textContent = "Kopiraj povezavo";
  btn.addEventListener("click", function () {
    copyText(url).then(function () {
      btn.textContent = "Kopirano!";
      status.textContent = "Povezava je kopirana.";
      window.setTimeout(function () {
        btn.textContent = "Kopiraj povezavo";
        status.textContent = "";
      }, 2000);
    }).catch(function () {
      status.textContent = "Kopiranje ni uspelo.";
    });
  });
  box.appendChild(btn);
  box.appendChild(status);
})();

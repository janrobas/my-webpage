<?php
  if (isset($_GET["subpage"]) && $_GET["subpage"] != "index") {
    $subpage = preg_replace("/[^a-zA-Z0-9_]+/", "", $_GET["subpage"]);
  } else {
    $subpage = "home";
  }
  $allowed = array("home", "about", "cv", "projekti", "contact", "contact_error", "contact_success");
  if (!in_array($subpage, $allowed)) {
    $subpage = "home";
  }
  require $subpage . ".php";

  $activeNav = in_array($subpage, array("contact", "contact_error", "contact_success")) ? "contact" : $subpage;
?>
<!DOCTYPE html>
<html lang="sl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jan Robas | <?=htmlspecialchars($title)?></title>
  <meta name="description" content="Osebna spletna stran Jan Robasa – predavatelj, razvijalec programske opreme in inštruktor programiranja.">
  <meta name="theme-color" media="(prefers-color-scheme: light)" content="#f6f3ec">
  <meta name="theme-color" media="(prefers-color-scheme: dark)" content="#000000">
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <script>
    (function () {
      var t = null;
      try { t = localStorage.getItem("janrobas-theme"); } catch (e) {}
      if (t !== "light" && t !== "dark") {
        t = window.matchMedia && window.matchMedia("(prefers-color-scheme: light)").matches ? "light" : "dark";
      }
      document.documentElement.setAttribute("data-theme", t);
    })();
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <?php if ($subpage === "contact") { ?>
  <script src="https://www.hCaptcha.com/1/api.js?hl=sl" async defer></script>
  <?php } ?>
</head>
<body>
  <header class="topbar">
    <div class="topbar-inner">
      <a class="logo" href="index.html">Jan&nbsp;Robas</a>
      <nav class="topnav" id="topnav" aria-label="Glavna navigacija">
        <a href="index.html"<?= $activeNav === "home" ? ' class="active"' : "" ?>>Domov</a>
        <a href="about.html"<?= $activeNav === "about" ? ' class="active"' : "" ?>>Vizitka</a>
        <a href="cv.html"<?= $activeNav === "cv" ? ' class="active"' : "" ?>>CV</a>
        <a href="projekti.html"<?= $activeNav === "projekti" ? ' class="active"' : "" ?>>Projekti</a>
        <a href="contact.html"<?= $activeNav === "contact" ? ' class="active"' : "" ?>>Kontakt</a>
      </nav>
      <div class="topbar-actions">
        <button type="button" id="theme-toggle" class="theme-toggle" aria-label="Preklopi svetlo/temno temo" title="Preklopi temo">
          <svg class="icon-sun" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 17a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2a7 7 0 1 1 0-14 7 7 0 0 1 0 14Zm8-9h2v2h-2v-2Zm0-6h2v2h-2V4ZM4 5h2v2H4V5Zm0 12h2v2H4v-2Zm7-17h2v2h-2V0Zm0 22h2v2h-2v-2Z"/></svg>
          <svg class="icon-moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/></svg>
        </button>
        <button type="button" id="menu-open" class="hamburger" aria-label="Odpri meni" aria-expanded="false" aria-controls="topnav">
          <span class="bar"></span><span class="bar"></span><span class="bar"></span>
        </button>
      </div>
    </div>
  </header>

  <section class="banner" id="banner" role="button" tabindex="0" aria-label="Razburkaj val">
    <canvas id="banner-canvas" aria-hidden="true"></canvas>
  </section>

  <main class="content">
    <h1 class="page-title"><?=htmlspecialchars($title)?></h1>
    <div class="content-inner">
      <?=$content?>
    </div>
  </main>

  <footer class="site-footer">
    <div class="footer-inner">&copy; <?=date("Y")?> Jan Robas<span class="footer-cursor">&#9608;</span></div>
  </footer>

  <div class="menu-backdrop" id="menu-backdrop"></div>

  <script src="banner.js"></script>
  <script>
    (function () {
      var menuOpen = document.getElementById("menu-open");
      var topnav = document.getElementById("topnav");
      var backdrop = document.getElementById("menu-backdrop");
      var themeToggle = document.getElementById("theme-toggle");

      function openMenu() {
        topnav.classList.add("open");
        backdrop.classList.add("show");
        menuOpen.classList.add("is-active");
        menuOpen.setAttribute("aria-expanded", "true");
        menuOpen.setAttribute("aria-label", "Zapri meni");
      }

      function closeMenu() {
        topnav.classList.remove("open");
        backdrop.classList.remove("show");
        menuOpen.classList.remove("is-active");
        menuOpen.setAttribute("aria-expanded", "false");
        menuOpen.setAttribute("aria-label", "Odpri meni");
      }

      menuOpen.addEventListener("click", function (ev) {
        ev.stopPropagation();
        if (topnav.classList.contains("open")) {
          closeMenu();
        } else {
          openMenu();
        }
      });

      backdrop.addEventListener("click", closeMenu);

      topnav.querySelectorAll("a").forEach(function (a) {
        a.addEventListener("click", closeMenu);
      });

      document.addEventListener("keydown", function (ev) {
        if (ev.key === "Escape") closeMenu();
      });

      window.addEventListener("resize", function () {
        if (window.innerWidth >= 720) closeMenu();
      });

      themeToggle.addEventListener("click", function () {
        var root = document.documentElement;
        var next = root.getAttribute("data-theme") === "light" ? "dark" : "light";
        root.setAttribute("data-theme", next);
        try { localStorage.setItem("janrobas-theme", next); } catch (e) {}
        document.dispatchEvent(new CustomEvent("themechange"));
      });
    })();
  </script>
</body>
</html>
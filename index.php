<?php
  if (isset($_GET["subpage"])) {
    $subpage = preg_replace("/[^a-zA-Z0-9_]+/", "", $_GET["subpage"]);
  } else {
    $subpage = "home";
  }
  if ($subpage === "" || $subpage === "index") {
    $subpage = "home";
  }
  $allowed = array("home", "about", "cv", "projekti", "zapiski", "contact", "contact_error", "contact_success");
  $notFound = !in_array($subpage, $allowed, true);
  if ($notFound) {
    http_response_code(404);
    $subpage = "notfound";
  }
  require $subpage . ".php";

  $activeNav = in_array($subpage, array("contact", "contact_error", "contact_success"), true) ? "contact" : $subpage;
  $noindex = $notFound || !empty($pageNoindex) || in_array($subpage, array("contact_error", "contact_success"), true);
  $heading = $heading ?? $title;
  $browserTitle = $browserTitle ?? ("Jan Robas | " . $title);

  $scheme = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
  $hostRaw = $_SERVER["HTTP_HOST"] ?? "janrobas.com";
  $hostOnly = strtolower(preg_replace("/:\\d+$/", "", $hostRaw));
  $liveHosts = array("janrobas.com", "www.janrobas.com", "lalala.si", "www.lalala.si");
  // Both domains stay live; SEO consolidates onto the canonical origin.
  $origin = in_array($hostOnly, $liveHosts, true) ? "https://janrobas.com" : $scheme . "://" . $hostRaw;
  $basePath = rtrim(dirname($_SERVER["SCRIPT_NAME"] ?? "/"), "/");
  $siteUrl = $origin . $basePath . "/";
  $canonical = $siteUrl;
  if (!empty($canonicalOverride)) {
    $canonical .= $canonicalOverride;
  } elseif (!$notFound && !in_array($subpage, array("home", "index"), true)) {
    $pageFile = in_array($subpage, array("contact_error", "contact_success"), true) ? "contact" : $subpage;
    $canonical .= $pageFile . ".html";
  }
  $ogImage = $siteUrl . "og-image.png";
  $siteDesc = "Osebna spletna stran Jan Robasa: predavatelj, razvijalec programske opreme in inštruktor programiranja.";
  $pageDesc = (!empty($metaDescription)) ? $metaDescription : $siteDesc;
  $personSchema = array(
    "@context" => "https://schema.org",
    "@type" => "Person",
    "name" => "Jan Robas",
    "url" => $siteUrl,
    "image" => $ogImage,
    "description" => $siteDesc,
    "jobTitle" => "Predavatelj, razvijalec programske opreme in inštruktor programiranja",
    "sameAs" => array(
      "https://github.com/janrobas",
      "https://www.linkedin.com/in/jan-robas-bab83580/",
      "https://www.facebook.com/janrobas",
      "https://janrobas.itch.io/",
    ),
  );
?>
<!DOCTYPE html>
<html lang="sl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?=htmlspecialchars($browserTitle)?></title>
  <meta name="description" content="<?=htmlspecialchars($pageDesc)?>">
  <meta name="author" content="Jan Robas">
  <meta name="robots" content="<?= $noindex ? "noindex, follow" : "index, follow" ?>">
  <meta name="theme-color" id="meta-theme-color" content="#000000">
  <link rel="icon" type="image/svg+xml" href="icon.svg">
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <link rel="apple-touch-icon" href="apple-touch-icon.png">
  <?php if (!$noindex) { ?>
  <link rel="canonical" href="<?=htmlspecialchars($canonical)?>">
  <?php } ?>
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Jan Robas">
  <meta property="og:locale" content="sl_SI">
  <meta property="og:title" content="<?=htmlspecialchars($browserTitle)?>">
  <meta property="og:description" content="<?=htmlspecialchars($pageDesc)?>">
  <?php if (!$noindex) { ?>
  <meta property="og:url" content="<?=htmlspecialchars($canonical)?>">
  <?php } ?>
  <meta property="og:image" content="<?=htmlspecialchars($ogImage)?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="Jan Robas">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?=htmlspecialchars($browserTitle)?>">
  <meta name="twitter:description" content="<?=htmlspecialchars($pageDesc)?>">
  <meta name="twitter:image" content="<?=htmlspecialchars($ogImage)?>">
  <script type="application/ld+json"><?=json_encode($personSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)?></script>
  <script>
    (function () {
      var t = null;
      try { t = localStorage.getItem("janrobas-theme"); } catch (e) {}
      if (t !== "light" && t !== "dark") {
        t = window.matchMedia && window.matchMedia("(prefers-color-scheme: light)").matches ? "light" : "dark";
      }
      document.documentElement.setAttribute("data-theme", t);
      var meta = document.getElementById("meta-theme-color");
      if (meta) meta.setAttribute("content", t === "light" ? "#f6f3ec" : "#000000");
    })();
  </script>
  <link rel="stylesheet" href="style.css?v=<?=filemtime(__DIR__ . "/style.css")?>">
  <?php if ($subpage === "contact") { ?>
  <script src="https://js.hcaptcha.com/1/api.js?hl=sl" async defer></script>
  <?php } ?>
</head>
<body>
  <a class="skip-link" href="#main">Preskoči na vsebino</a>
  <header class="topbar">
    <div class="topbar-inner">
      <a class="logo" href="index.html">Jan&nbsp;Robas</a>
      <nav class="topnav" id="topnav" aria-label="Glavna navigacija">
        <a href="index.html"<?= $activeNav === "home" ? ' class="active" aria-current="page"' : "" ?>>Domov</a>
        <a href="about.html"<?= $activeNav === "about" ? ' class="active" aria-current="page"' : "" ?>>Vizitka</a>
        <a href="cv.html"<?= $activeNav === "cv" ? ' class="active" aria-current="page"' : "" ?>>CV</a>
        <a href="projekti.html"<?= $activeNav === "projekti" ? ' class="active" aria-current="page"' : "" ?>>Projekti</a>
        <a href="zapiski.html"<?= $activeNav === "zapiski" ? ' class="active" aria-current="page"' : "" ?>>Zapiski</a>
        <a href="contact.html"<?= $activeNav === "contact" ? ' class="active" aria-current="page"' : "" ?>>Kontakt</a>
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

  <section class="banner<?= $subpage === "home" ? "" : " banner--compact" ?>" id="banner" data-auto="<?= $subpage === "home" ? "1" : "0" ?>" aria-hidden="true">
    <canvas id="banner-canvas"></canvas>
  </section>

  <main class="content" id="main" tabindex="-1">
    <h1 class="page-title<?= $subpage === "home" ? " page-title--home" : "" ?>"><?=htmlspecialchars($heading)?></h1>
    <div class="content-inner">
      <?=$content?>
    </div>
  </main>

  <footer class="site-footer">
    <div class="footer-inner">&copy; <?=date("Y")?> Jan Robas<span class="footer-cursor" aria-hidden="true"></span></div>
  </footer>

  <div class="menu-backdrop" id="menu-backdrop"></div>

  <script src="banner.js?v=<?=filemtime(__DIR__ . "/banner.js")?>"></script>
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
        document.documentElement.classList.add("menu-locked");
        menuOpen.setAttribute("aria-expanded", "true");
        menuOpen.setAttribute("aria-label", "Zapri meni");
      }

      function closeMenu() {
        topnav.classList.remove("open");
        backdrop.classList.remove("show");
        menuOpen.classList.remove("is-active");
        document.documentElement.classList.remove("menu-locked");
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
        var meta = document.getElementById("meta-theme-color");
        if (meta) meta.setAttribute("content", next === "light" ? "#f6f3ec" : "#000000");
        try { localStorage.setItem("janrobas-theme", next); } catch (e) {}
        document.dispatchEvent(new CustomEvent("themechange"));
      });
    })();
  </script>
</body>
</html>
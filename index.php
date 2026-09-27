<?php
  if (isset($_GET["subpage"]) && $_GET["subpage"] != "index") {
    $subpage = preg_replace("/[^a-zA-Z0-9_]+/", "", $_GET["subpage"]);
  } else {
    $subpage = "about";
  }
  require $subpage.".php";
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>Jan Robas | <?=$title?></title>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="style.css">
  <script src='https://www.hCaptcha.com/1/api.js?hl=sl' async defer></script>
</head>
<body>
    <header>
      <a id="menu-open">
        <i class="fa fa-bars"></i>
      </a>
      <h1>Jan Robas</h1>
    </header>
    <nav class="menu-sidebar">
      <div class="ornament">
      </div>
      <ul>
        <li><a href="about.html">Vizitka</a></li>
        <li><a href="cv.html">CV</a></li>
        <li><a href="contact.html">Kontakt</a></li>
      </ul>
    </nav>
    <main class="content">
      <h2><?=$title?></h2>
      <div class="content-inner">
        <?=$content?>
      </div>
    </main>
<script>
    (() => {
      const $menuOpen = document.getElementById("menu-open");
      const $menuSidebar = document.querySelector(".menu-sidebar");
      const $nav = document.querySelector("nav");

      $menuOpen.addEventListener("click", (ev) => {
        $menuSidebar.classList.add("menu-open");
        ev.stopPropagation();
      });
      document.addEventListener("click", () => {
        $menuSidebar.classList.remove("menu-open");
      });
      $nav.addEventListener("click", (ev) => {
        ev.stopPropagation();
      });
  })();
</script>
</body>
</html>
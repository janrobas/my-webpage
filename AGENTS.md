# AGENTS.md

Osebna spletna stran Jan Robasa. PHP + vanilijski CSS/JS, **brez build orodij in odvisnosti** (razen Google Fonts in itch.io embedov). Teče lokalno na XAMPP.

## Ogled in testiranje
- Stran je dostopna na `http://localhost/my-webpage/`.
- Lint posamezne PHP datoteke: `C:\xampp\php\php.exe -l <file.php>` (PHP ni na PATH — uporabi XAMPP path).
- Po spremembi CSS/JS: hard refresh (`Ctrl+Shift+R`), saj brskalnik predpomni datoteke.
- Po spremembi preveri, da se stran dejansko odpre (preglej `index.html`, `projekti.html`, `cv.html`, ... prek HTTP).

## Struktura in usmerjanje
- `.htaccess` pretvori `*.html` → `index.php?subpage=X` (če fizične datoteke ni).
- `index.php`: allowlist dovoljenih podstrani, nato `require $subpage.".php"`.
- Vsaka podstran (`home.php`, `about.php`, `cv.php`, `projekti.php`, `contact.php`, `contact_error.php`, `contact_success.php`) nastavi `$title` in `$content` (heredoc HTML).
- Navigacija: Domov · Vizitka · CV · Projekti · Kontakt. Aktivni link določi `$activeNav` v `index.php`.
- Nova podstran = nova PHP datoteka + vnos v allowlist + povezava `.html` (rewrite deluje samodejno).

## Kontaktna pot
- `contact.php` = obrazec + hCaptcha. `do_contact.php` preveri hCaptcha (skrivnost v `config.php`, gitignored) in sporočilo pošlje na Telegram (config: `telegram_bot_token`, `telegram_chat_id`).

## Dizajn — pomembno
- **NE maramo "AI generiranega" izgleda**: izogibaj se enotnih mrež kartic (card grid) na landing strani in odvečnih okvirjev. Raje preprosto, človeško, minimalno (navadni seznami, odprt tekst). Preveri pri uporabniku, če nisi prepričan.
- Dvojna tema: temna (privzeto) / svetla prek `data-theme` na `<html>`. Barve so CSS spremenljivke v `style.css` (`--bg`, `--surface`, `--text`, `--accent` = `#e53b44`, ...). Toggle shrani v localStorage (`janrobas-theme`); vrednost se nastavi v inline skriptu v `<head>`, da ni flasha.
- Pisava: Lato (Google Fonts). Zaobljeni robovi 14–19px.
- `banner.js`: animiran pixel-val (crescendo L→R), občasno "povalovi"; klik/tap = easter egg (sploosh). Spoštuje `prefers-reduced-motion`.
- Vsebina je slovenska; komentarji v kodi minimalni.

## Konvencije
- Enostavno > zapleteno. Brez novih knjižnic/odvisnosti brez izrecnega dovoljenja.
- Ne dodajaj nepotrebnih komentarjev, dokumentacijskih datotek ali generičnih placeholder vsebin.
- Ohranjaj `.html` povezave in obstoječi flow (htaccess + subpage).
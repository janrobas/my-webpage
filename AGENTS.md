# AGENTS.md

Osebna spletna stran Jan Robasa. PHP + vanilijski CSS/JS, **brez build orodij in odvisnosti** (pisava Inter je samohostana, zunanji so le itch.io embedi). Teče lokalno na XAMPP.

## Ogled in testiranje
- Stran je dostopna na `http://localhost/my-webpage/`.
- Lint posamezne PHP datoteke: `C:\xampp\php\php.exe -l <file.php>` (PHP ni na PATH — uporabi XAMPP path).
- Po spremembi CSS/JS: `style.css` in `banner.js` sta v `index.php` povezana z `?v=filemtime(...)`, zato običajen refresh pobere novo različico (HTML se ne predpomni).
- Po spremembi preveri, da se stran dejansko odpre (preglej `index.html`, `projekti.html`, `cv.html`, ... prek HTTP).

## Struktura in usmerjanje
- `.htaccess` pretvori `*.html` → `index.php?subpage=X` (če fizične datoteke ni), neznane poti pa na `index.php?subpage=notfound` (stilizirana 404). Blokira `inc/` in dotfile (`.git`, ...), onemogoči directory listing, vsili HTTPS (HSTS), doda `Permissions-Policy`, CSP v načinu `-Report-Only` (pred vsiljenjem bi inline skripte potrebovale nonce) in predpomni statiko (`Cache-Control`), HTML pa označi z `no-cache`. `lalala.si` (tudi `www`, http/https) se 301 preusmeri na `https://janrobas.com` (pot in query se ohranita); `localhost` ni prizadet, zato je lokalni razvoj normalen. `canonical`/`og:url`/JSON-LD v `index.php` vedno kažejo na `https://janrobas.com` (SEO konsolidacija).
- `robots.txt` in `sitemap.xml` sta statična; ob novi podstrani ju posodobi.
- `index.php`: allowlist dovoljenih podstrani, nato `require $subpage.".php"`; neznana/stran z napako dobi status 404 in `notfound.php` ter `noindex`. Podstrani `contact_error`, `contact_success` in `notfound` so `noindex` in brez `canonical`.
- Vsaka podstran (`home.php`, `about.php`, `cv.php`, `projekti.php`, `contact.php`, `contact_error.php`, `contact_success.php`, `notfound.php`) nastavi `$title`, `$heading` (H1), `$browserTitle` (`<title>`/og:title) in `$content` (heredoc HTML).
- Navigacija: Domov · Vizitka · CV · Projekti · Kontakt. Aktivni link določi `$activeNav` v `index.php`.
- Nova podstran = nova PHP datoteka + vnos v allowlist + povezava `.html` (rewrite deluje samodejno).

## Kontaktna pot
- `contact.php` = obrazec + hCaptcha (javni sitekey v `contact.php`, skrivnost v `config.php`, gitignored). `do_contact.php` preveri hCaptcha in pošlje sporočilo na Telegram (config: `telegram_bot_token`, `telegram_chat_id`). Honeypot polje `website` + omejitev enega sporočila na 20 s. Sprejema samo POST, validira vnos in preveri `ok` iz Telegrama preden preusmeri na `contact_success.html`.

## Dizajn — pomembno
- **NE maramo "AI generiranega" izgleda**: izogibaj se enotnih mrež kartic (card grid) na landing strani in odvečnih okvirjev. Raje preprosto, človeško, minimalno (navadni seznami, odprt tekst). Preveri pri uporabniku, če nisi prepričan.
- Dvojna tema: temna (privzeto) / svetla prek `data-theme` na `<html>`. Barve so CSS spremenljivke v `style.css` (`--bg`, `--surface`, `--text`, `--accent` = `#d65d66`, ...). Toggle shrani v localStorage (`janrobas-theme`); vrednost se nastavi v inline skriptu v `<head>`, da ni flasha.
- Pisava: Inter, samohostan v `fonts/` (variabilna: 2 × woff2 — `latin` in `latin-ext`, `@font-face` s `unicode-range` na vrhu `style.css`; licenca `fonts/OFL-Inter.txt`). `latin-ext` je nujen za č/š/ž — brez njega ti znaki padejo na sistemsko pisavo. Zaobljeni robovi 14–19px.
- `banner.js`: animiran pixel-val (crescendo L→R), občasno "povalovi"; klik/tap = easter egg (sploosh), ki animira vedno — tudi ob `prefers-reduced-motion`, ker je to dejanje uporabnika (samodejni valovi ob reduced-motion mirujejo). Za bralnike zaslona je banner dekorativen (`aria-hidden`), zato ga tipkovnica ne doseže.
- Vsebina je slovenska; komentarji v kodi minimalni.

## Konvencije
- Enostavno > zapleteno. Brez novih knjižnic/odvisnosti brez izrecnega dovoljenja.
- Ne dodajaj nepotrebnih komentarjev, dokumentacijskih datotek ali generičnih placeholder vsebin.
- Ohranjaj `.html` povezave in obstoječi flow (htaccess + subpage).
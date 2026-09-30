<?php
require "inc/utils.php";

// Public site key (the secret stays in config.php)
$hcaptchaSitekey = "0948ce9d-3d00-450c-b726-71ad42e8e8f3";

$title = "Kontakt";
$heading = "Kontakt";
$browserTitle = "Kontakt - Jan Robas";
$metaDescription = "Kontakt: pošljite sporočilo Jan Robasu.";

$captchaField = '<div class="h-captcha" data-sitekey="' . htmlspecialchars($hcaptchaSitekey, ENT_QUOTES) . '"></div>';

$content = <<<HTML
<p>Lahko me kontaktirate za dogovor o predavanjih, inštrukcijah, svetovanju pri uporabi umetne inteligence ali razvojnih projektih in povezovanju rešitev.</p>

<form action="do_contact.php" method="post" id="contact-form">
<div class="field">
<label for="name">Ime:</label>
<input type="text" id="name" name="name" value="" autocomplete="name" maxlength="100" required>
</div>

<div class="field">
<label for="email">E-pošta:</label>
<input type="email" id="email" name="email" value="" autocomplete="email" maxlength="200">
</div>

<div class="field">
<label for="phone">Telefon:</label>
<input type="tel" id="phone" name="phone" value="" autocomplete="tel" maxlength="50">
</div>

<div class="field">
<label for="content">Vaše sporočilo:</label>
<textarea name="content" id="content" maxlength="5000" required></textarea>
</div>

<div class="hp-field" aria-hidden="true">
<label for="website">Spletna stran</label>
<input type="text" id="website" name="website" value="" tabindex="-1" autocomplete="off">
</div>

{$captchaField}

<div class="form-error" id="form-error" role="alert" hidden></div>

<input type="submit" value="Pošlji sporočilo">
</form>
HTML
. other_channels().
<<<HTML
<script>
    (function () {
        var form = document.getElementById("contact-form");
        var errorBox = document.getElementById("form-error");

        function showError(message, field) {
            errorBox.textContent = message;
            errorBox.hidden = false;
            if (field) field.focus();
        }

        form.addEventListener("submit", function (e) {
            errorBox.hidden = true;
            errorBox.textContent = "";

            if (form.elements.email.value.trim() === "" && form.elements.phone.value.trim() === "") {
                e.preventDefault();
                showError("Vnesite e-pošto ali telefon, da vam lahko odgovorim.", form.elements.email);
                return;
            }

            var captcha = form.querySelector('[name="h-captcha-response"]');
            if (captcha && captcha.value === "") {
                e.preventDefault();
                showError("Prosimo, obkljukajte »Jaz sem človek« preden pošljete sporočilo.");
            }
        });
    })();
</script>
HTML;

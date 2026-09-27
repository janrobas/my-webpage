<?php
require "inc/utils.php";

$title = "Kontakt";
$metaDescription = "Kontakt: pošljite sporočilo Jan Robasu.";
$content = <<<HTML

<p>Lahko me kontaktirate za dogovor o predavanjih, inštrukcijah, svetovanju pri uporabi umetne inteligence ali razvojnih projektih in povezovanju rešitev.</p>

<form action="do_contact.php" method="post" id="contact-form">
<div class="field">
<label for="name">Ime:</label>
<input type="text" id="name" name="name" value="">
</div>

<div class="field">
<label for="email">E-pošta:</label>
<input type="text" id="email" name="email" value="">
</div>

<div class="field">
<label for="phone">Telefon:</label>
<input type="text" id="phone" name="phone" value="">
</div>

<div class="field">
<label for="content">Vaše sporočilo:</label>
<textarea name="content" id="content"></textarea>
</div>

<div class="h-captcha" data-sitekey="0948ce9d-3d00-450c-b726-71ad42e8e8f3"></div>

<input type="submit" value="Pošlji sporočilo">
</form>
HTML
.other_channels().
<<<HTML
<script>
    let captchaOk = false;
        
    function recaptcha_callback() {
        captchaOk = true;
    }

    document.getElementById("contact-form").addEventListener("submit", function(e) {
        
        const hcaptchaVal = document.querySelector('[name=h-captcha-response]').value;
        if (hcaptchaVal === "") {
            alert("Prosimo, obkljukajte \"Jaz sem človek\" preden pošljete sporočilo.");
            e.preventDefault();
        }
    });
</script>
HTML;
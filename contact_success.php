<?php
require "inc/utils.php";

$title = "Kontakt";
$heading = "Sporočilo je poslano";
$browserTitle = "Sporočilo je poslano – Jan Robas";
$metaDescription = "Vaše sporočilo je bilo poslano.";
$content = <<<HTMLCONTENT
<p class="lede">Hvala, vaše sporočilo je bilo poslano.</p>
<p>Če ne odgovorim v doglednem času, me lahko kontaktirate tudi preko drugih kanalov.</p>
HTMLCONTENT.other_channels();

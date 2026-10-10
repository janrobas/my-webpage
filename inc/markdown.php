<?php

require_once __DIR__ . "/vendor/Parsedown.php";
require_once __DIR__ . "/vendor/ParsedownExtra.php";

function render_markdown(string $markdown): string {
    static $parser = null;
    if ($parser === null) {
        $parser = new ParsedownExtra();
        // Escape raw HTML instead of passing it through, and keep single
        // newlines as soft breaks (standard Markdown behaviour).
        $parser->setSafeMode(true);
        $parser->setBreaksEnabled(false);
    }
    return $parser->text($markdown);
}

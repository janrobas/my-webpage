<?php
require "inc/memorydown.php";
require "inc/markdown.php";

function zapiski_date(string $iso): string {
    if ($iso === "") {
        return "";
    }
    try {
        $dt = new DateTimeImmutable($iso);
        return $dt->setTimezone(new DateTimeZone("Europe/Ljubljana"))->format("j. n. Y");
    } catch (Exception $e) {
        return "";
    }
}

$id = isset($_GET["id"]) ? (string) $_GET["id"] : "";

$title = "Zapiski";
$heading = "Zapiski";
$browserTitle = "Zapiski | Jan Robas";
$metaDescription = "Kratki zapisi, misli in beležke Jan Robasa.";

if (!memorydown_enabled()) {
    $content = '<p>Zapiski trenutno niso na voljo.</p>';
} elseif ($id !== "") {
    $note = memorydown_get($id);
    if ($note === null) {
        http_response_code(404);
        $pageNoindex = true;
        $content = '<p>Zapiska ni mogoče najti. <a href="zapiski.html">Nazaj na zapiske</a>.</p>';
    } else {
        $noteTitle = trim((string) ($note["title"] ?? "")) ?: "Zapisek";
        $heading = $noteTitle;
        $browserTitle = $noteTitle . " | Jan Robas";
        $canonicalOverride = "zapiski.html?id=" . rawurlencode($id);

        $rendered = render_markdown((string) ($note["markdown"] ?? ""));
        $excerpt = trim(preg_replace('/\s+/', ' ', strip_tags($rendered)));
        $metaDescription = $excerpt !== "" ? mb_substr($excerpt, 0, 160, "UTF-8") : ($noteTitle . " - zapisek Jan Robasa.");

        $date = zapiski_date((string) ($note["created"] ?? ""));
        $metaParts = array();
        if ($date !== "") {
            $metaParts[] = '<span class="note-date">' . htmlspecialchars($date) . '</span>';
        }
        foreach (is_array($note["tags"] ?? null) ? $note["tags"] : array() as $t) {
            $t = trim((string) $t);
            if ($t !== "") {
                $metaParts[] = '<span class="writing-tag">' . htmlspecialchars($t) . '</span>';
            }
        }
        $badge = !empty($note["archived"]) ? '<span class="writing-badge">arhivirano</span>' : "";
        $metaLine = implode('<span class="note-sep">·</span>', $metaParts);

        $content = '<article class="note">'
            . (($metaLine !== "" || $badge !== "") ? '<p class="note-meta">' . $metaLine . $badge . '</p>' : '')
            . '<div class="note-body">' . $rendered . '</div>'
            . '</article>'
            . '<div class="note-share" data-share data-share-title="' . htmlspecialchars($noteTitle, ENT_QUOTES) . '"></div>'
            . '<p class="writing-back"><a href="zapiski.html">&larr; Nazaj na zapiske</a></p>';
    }
} else {
    $data = memorydown_list();
    if ($data === null) {
        $content = '<p>Zapiski trenutno niso na voljo.</p>';
    } else {
        $items = array();
        foreach ($data["writings"] as $w) {
            if (!empty($w["archived"])) {
                continue; // archived-but-public stays readable via its link, just out of the list
            }
            $wid = (string) ($w["id"] ?? "");
            if ($wid === "") {
                continue;
            }
            $wt = trim((string) ($w["title"] ?? "")) ?: $wid;
            $metaParts = array();
            $date = zapiski_date((string) ($w["created"] ?? ""));
            if ($date !== "") {
                $metaParts[] = '<span class="writing-date">' . htmlspecialchars($date) . '</span>';
            }
            foreach (is_array($w["tags"] ?? null) ? $w["tags"] : array() as $t) {
                $t = trim((string) $t);
                if ($t !== "") {
                    $metaParts[] = '<span class="writing-tag">' . htmlspecialchars($t) . '</span>';
                }
            }
            $meta = implode('<span class="note-sep">·</span>', $metaParts);
            $items[] = '<li class="writing-item"><a class="writing-link" href="zapiski.html?id=' . rawurlencode($wid) . '">'
                . '<span class="writing-title">' . htmlspecialchars($wt) . '</span>'
                . ($meta !== "" ? '<span class="writing-meta">' . $meta . '</span>' : '')
                . '</a></li>';
        }

        $intro = '<p class="writing-intro">Kratki zapisi, misli in beležke.</p>';
        $content = $items
            ? $intro . '<ul class="writing-list">' . implode("", $items) . '</ul>'
            : $intro . '<p class="writing-empty">Še ni objavljenih zapiskov.</p>';
    }
}

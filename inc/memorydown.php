<?php

// Read-only client for the MemoryDown public writings API
// (https://memory.janrobas.com/public/writings). The bearer token stays
// server-side; the browser never sees it.

const MEMORYDOWN_CACHE_TTL = 60;

function memorydown_config(): array {
    static $cfg = null;
    if ($cfg === null) {
        $file = is_file(__DIR__ . "/../config.php")
            ? __DIR__ . "/../config.php"
            : __DIR__ . "/../config.example.php";
        $raw = require $file;
        $cfg = array(
            "base" => rtrim((string) ($raw["memorydown_base_url"] ?? ""), "/"),
            "token" => (string) ($raw["memorydown_token"] ?? ""),
        );
    }
    return $cfg;
}

function memorydown_enabled(): bool {
    $cfg = memorydown_config();
    return $cfg["base"] !== "" && $cfg["token"] !== "";
}

function memorydown_list(): ?array {
    $data = memorydown_request("/public/writings");
    return isset($data["writings"]) && is_array($data["writings"]) ? $data : null;
}

function memorydown_get(string $id): ?array {
    // Same slug whitelist MemoryDown itself enforces before touching disk.
    if (!preg_match('/^[a-z0-9][a-z0-9._-]{0,127}$/', $id)) {
        return null;
    }
    $data = memorydown_request("/public/writings/" . rawurlencode($id));
    return isset($data["writing"]) && is_array($data["writing"]) ? $data["writing"] : null;
}

function memorydown_request(string $path): ?array {
    $cfg = memorydown_config();
    $url = $cfg["base"] . $path;
    $cacheFile = memorydown_cache_file($url);
    $cached = memorydown_cache_read($cacheFile);

    if ($cached !== null && (time() - $cached["ts"]) < MEMORYDOWN_CACHE_TTL) {
        return $cached["body"];
    }

    $headers = array(
        "Authorization: Bearer " . $cfg["token"],
        "Accept: application/json",
    );
    if ($cached !== null && !empty($cached["etag"])) {
        $headers[] = "If-None-Match: " . $cached["etag"];
    }

    $result = memorydown_curl($url, $headers);
    if ($result["error"]) {
        // Network/SSL failure: serve a stale copy if we have one.
        return $cached !== null ? $cached["body"] : null;
    }

    if ($result["status"] === 304 && $cached !== null) {
        $cached["ts"] = time();
        memorydown_cache_write($cacheFile, $cached);
        return $cached["body"];
    }

    if ($result["status"] === 200) {
        $decoded = json_decode($result["body"], true);
        if (is_array($decoded)) {
            memorydown_cache_write($cacheFile, array(
                "body" => $decoded,
                "etag" => $result["etag"],
                "ts" => time(),
            ));
            return $decoded;
        }
    }

    return $cached !== null ? $cached["body"] : null;
}

function memorydown_curl(string $url, array $headers): array {
    $ch = curl_init($url);
    $responseHeaders = "";
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 6,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADERFUNCTION => function ($ch, $line) use (&$responseHeaders) {
            $responseHeaders .= $line;
            return strlen($line);
        },
    ));
    $body = curl_exec($ch);
    $errno = curl_errno($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno !== 0 || $body === false) {
        return array("status" => 0, "body" => "", "etag" => null, "error" => true);
    }

    $etag = null;
    if (preg_match('/^ETag:\s*(.+)$/mi', $responseHeaders, $m)) {
        $etag = trim($m[1]);
    }

    return array("status" => $status, "body" => (string) $body, "etag" => $etag, "error" => false);
}

function memorydown_cache_file(string $url): string {
    $dir = __DIR__ . "/cache";
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    return $dir . "/" . sha1($url) . ".json";
}

function memorydown_cache_read(string $file): ?array {
    if (!is_file($file)) {
        return null;
    }
    $raw = @file_get_contents($file);
    if ($raw === false) {
        return null;
    }
    $data = json_decode($raw, true);
    if (!is_array($data) || !isset($data["body"]) || !is_array($data["body"])) {
        return null;
    }
    return array(
        "body" => $data["body"],
        "etag" => $data["etag"] ?? null,
        "ts" => (int) ($data["ts"] ?? 0),
    );
}

function memorydown_cache_write(string $file, array $data): void {
    @file_put_contents($file, json_encode($data), LOCK_EX);
}

<?php

ini_set('display_errors', '0');

$MIN_INTERVAL = 20; // seconds between two submitted messages

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: contact.html', true, 303);
    exit;
}

// Honeypot: real browsers never fill this in. Pretend it worked.
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    header('Location: contact_success.html');
    exit;
}

$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    error_log('Contact form: config.php is missing.');
    header('Location: contact_error.html');
    exit;
}
$config = require $configFile;

$name    = trim((string) ($_POST['name'] ?? ''));
$email   = trim((string) ($_POST['email'] ?? ''));
$phone   = trim((string) ($_POST['phone'] ?? ''));
$content = trim((string) ($_POST['content'] ?? ''));
$captcha = (string) ($_POST['h-captcha-response'] ?? '');

$valid = $captcha !== ''
    && $name !== ''
    && $content !== ''
    && ($email !== '' || $phone !== '')
    && ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
    && mb_strlen($name) <= 100
    && mb_strlen($email) <= 200
    && mb_strlen($phone) <= 50
    && mb_strlen($content) <= 5000;

if (!$valid) {
    header('Location: contact_error.html');
    exit;
}

session_start();
$lastSent = (int) ($_SESSION['contact_last'] ?? 0);
if ($lastSent > 0 && time() - $lastSent < $MIN_INTERVAL) {
    header('Location: contact_error.html');
    exit;
}

$verify = curl_init('https://api.hcaptcha.com/siteverify');
curl_setopt_array($verify, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'secret'   => $config['hcaptcha_secret'],
        'response' => $captcha,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
]);
$captchaResponse = curl_exec($verify);
curl_close($verify);

$captchaData = is_string($captchaResponse) ? json_decode($captchaResponse) : null;
if (!$captchaData || empty($captchaData->success)) {
    header('Location: contact_error.html');
    exit;
}

// Telegram caps a message at 4096 characters.
$truncated = mb_strlen($content) > 3500;
if ($truncated) {
    $content = mb_substr($content, 0, 3500);
}

$text = $name;
if ($email !== '') {
    $text .= ', mail: ' . $email;
}
if ($phone !== '') {
    $text .= ', tel: ' . $phone;
}
$text .= "\n\n" . $content;
if ($truncated) {
    $text .= "\n\n[sporočilo skrajšano]";
}

$_SESSION['contact_last'] = time();

$send = curl_init('https://api.telegram.org/bot' . $config['telegram_bot_token'] . '/sendMessage');
curl_setopt_array($send, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'chat_id' => $config['telegram_chat_id'],
        'text'    => $text,
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
]);
$telegramResponse = curl_exec($send);
curl_close($send);

$telegramData = is_string($telegramResponse) ? json_decode($telegramResponse) : null;
if ($telegramData && !empty($telegramData->ok)) {
    header('Location: contact_success.html');
    exit;
}

error_log('Contact form: Telegram send failed: ' . (is_string($telegramResponse) ? $telegramResponse : 'no response'));
header('Location: contact_error.html');
exit;

<?php

ini_set('display_errors', '0');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: contact.html', true, 303);
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
    && ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
    && mb_strlen($name) <= 100
    && mb_strlen($email) <= 200
    && mb_strlen($phone) <= 50
    && mb_strlen($content) <= 5000;

if (!$valid) {
    header('Location: contact_error.html');
    exit;
}

$verify = curl_init('https://hcaptcha.com/siteverify');
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

$text = $name;
if ($email !== '') {
    $text .= ', mail: ' . $email;
}
if ($phone !== '') {
    $text .= ', tel: ' . $phone;
}
$text .= "\n\n" . $content;

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

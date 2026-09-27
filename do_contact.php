<?php

$config = require __DIR__ . '/config.php';

$data = array(
    'secret' => $config['hcaptcha_secret'],
    'response' => $_POST['h-captcha-response']
);

$verify = curl_init();
curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
curl_setopt($verify, CURLOPT_POST, true);
curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($verify);
$responseData = json_decode($response);

if (!$responseData->success) {
    header("location: contact_error.html");
    exit;
}

$text = $_POST["name"];

if (!empty($_POST["email"])) {
    $text .= ", mail: " . $_POST["email"];
}
if (!empty($_POST["phone"])) {
    $text .= ", tel: " . $_POST["phone"];
}

$text .= "\n\n".$_POST["content"];

if (!empty($text)) {
    $data = [
        'chat_id' => $config['telegram_chat_id'],
        'text' => $text
    ];
    $apiToken = $config['telegram_bot_token'];
    $response = file_get_contents("https://api.telegram.org/bot$apiToken/sendMessage?" . http_build_query($data) );
    header("location: contact_success.html");
}

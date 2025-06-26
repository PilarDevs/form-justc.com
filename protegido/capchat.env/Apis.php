<?php /////////
$code = require 'codeSecret.php';
$response = $_POST['g-recaptcha-response'];
$secret = $code['RECAPTCHA'];
$verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$response");
$captcha_success = json_decode($verify);
if (!$captcha_success->success) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'mensaje' => 'Validación CAPTCHA fallida']);
    exit;
}

//////// ?>
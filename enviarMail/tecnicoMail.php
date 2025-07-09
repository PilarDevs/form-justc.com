<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require '../protegido/config.php'; // Conexión BD
$config = require '../protegido/configMail.php'; // SMTP


if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'admin') {
    die("Acceso denegado.");
}

// Entradas
$id_solicitud = $_POST['id_solicitud'] ?? null;
$tecnicos = $_POST['tecnicos'] ?? [];

if (!$id_solicitud || empty($tecnicos)) {
    die("Faltan datos.");
}

// Guardar técnicos en la tabla relacional
$stmtInsert = $pdo->prepare("INSERT INTO solicitud_tecnicos (id_solicitud, nombre_tecnico) VALUES (?, ?)");
foreach ($tecnicos as $tecnico) {
    $tecnico = trim($tecnico);
    if (!empty($tecnico)) {
        $stmtInsert->execute([$id_solicitud, $tecnico]);
    }
}

// Obtener todos los correos de usuarios
$stmtCorreos = $pdo->query("SELECT correo FROM usuarios");
$correos = $stmtCorreos->fetchAll(PDO::FETCH_COLUMN);

// Crear cuerpo HTML del correo
$lista_tecnicos = "<ul>";
foreach ($tecnicos as $t) {
    $lista_tecnicos .= "<li>" . htmlspecialchars($t) . "</li>";
}
$lista_tecnicos .= "</ul>";

$mensajeHTML = "
    <p>Se han asignado los siguientes técnicos a la solicitud <strong>#{$id_solicitud}</strong>:</p>
    {$lista_tecnicos}
    <p>Por favor, dar seguimiento correspondiente.</p>
";

// Configurar y enviar correo
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp_user'];
    $mail->Password   = $config['smtp_pass'];
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom($config['smtp_user'], 'Justech');
    $mail->Subject = "Asignacion de tecnico(s) a solicitud #$id_solicitud";
    $mail->isHTML(true);
    $mail->Body = $mensajeHTML;

    foreach ($correos as $correo) {
        $mail->addAddress($correo);
    }

    $mail->send();
    echo "<script>alert('Técnico(s) asignado(s) y correo enviado a todos los usuarios.'); window.location.href = 'ver-solicitudes.php';</script>";
} catch (Exception $e) {
    echo "Error al enviar el correo: {$mail->ErrorInfo}";
}

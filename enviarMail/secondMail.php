<?php
require 'vendor/autoload.php';
require 'protegido/config.php'; // Conexión a la base de datos
$config = require 'protegido/configMail.php'; // Configuración SMTP

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    die("No autorizado. Debes iniciar sesión.");
}

// Obtener correo del usuario
try {
    $stmt = $pdo->prepare("SELECT correo FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die("Usuario no encontrado.");
    }

    $correo_usuario = $usuario['correo'];
} catch (PDOException $e) {
    die("Error al obtener correo del usuario: " . $e->getMessage());
}

// Obtener la URL del PDF de la última solicitud del usuario
try {
    $stmt = $pdo->prepare("
        SELECT url_pdf 
        FROM solicitudes 
        WHERE id_usuario = :id_usuario 
        ORDER BY fecha_envio DESC 
        LIMIT 1
    ");
    $stmt->execute([':id_usuario' => $id_usuario]);
    $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$solicitud || empty($solicitud['url_pdf'])) {
        die("No se encontró un PDF asociado a la última solicitud.");
    }

    $rutaPDF = $solicitud['url_pdf']; // Ruta del archivo PDF (ej: "pdfs/solicitud_123.pdf")
} catch (PDOException $e) {
    die("Error al obtener la solicitud: " . $e->getMessage());
}

// Enviar correo con el PDF adjunto
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtp_user'];
    $mail->Password = $config['smtp_pass'];
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom($config['smtp_user'], 'Sistema de Solicitudes');

    $mail->addAddress($correo_usuario, 'Solicitante');             // Usuario logueado

    $mail->isHTML(true);
    $mail->Subject = 'Nueva solicitud de instalación/reparación de red';
    $mail->Body = "Se a recibido tu solicitud, espera mientras la evaluamos.";
    $mail->addAttachment($rutaPDF); // Adjunta el PDF desde la base de datos

    $mail->send();

    echo "<script>alert('Solicitud enviada correctamente'); window.location='panel-usuario.php';</script>";
} catch (Exception $e) {
    echo "Error al enviar el correo: {$mail->ErrorInfo}";
}

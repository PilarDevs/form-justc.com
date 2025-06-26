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
        SELECT s.url_pdf, ds.archivo_opcional1, ds.archivo_opcional2
        FROM solicitudes s
        LEFT JOIN detalles_solicitudes ds ON ds.id_solicitud = s.id
        WHERE s.id_usuario = :id_usuario
        ORDER BY s.fecha_envio DESC 
        LIMIT 1
    ");
    $stmt->execute([':id_usuario' => $id_usuario]);
    $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$solicitud || empty($solicitud['url_pdf'])) {
        die("No se encontró un PDF asociado a la última solicitud.");
    }

    $rutaPDF = __DIR__ . '/../pdfs/' . $solicitud['url_pdf'];
    if (!file_exists($rutaPDF)) {
        die("El archivo PDF no existe en la ruta especificada.");
    }

    $urlArchivo1 = $solicitud['archivo_opcional1'] ?? '';
    $urlArchivo2 = $solicitud['archivo_opcional2'] ?? '';

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
    $mail->addAddress($config['smtp_to'], 'Solicitante');
    $mail->addAddress($correo_usuario);

    $mail->isHTML(true);
    $mail->Subject = 'Nueva solicitud de instalación/reparación de red';
    $mail->Body = "Se ha recibido una nueva solicitud técnica.<br>Se adjunta el PDF con los detalles.";
    $mail->addAttachment($rutaPDF);

    if (!empty($urlArchivo1)) {
        $rutaArchivo1 = __DIR__ . '/../' . $urlArchivo1;
        if (file_exists($rutaArchivo1)) {
            $mail->addAttachment($rutaArchivo1);
        }
    }

    if (!empty($urlArchivo2)) {
        $rutaArchivo2 = __DIR__ . '/../' . $urlArchivo2;
        if (file_exists($rutaArchivo2)) {
            $mail->addAttachment($rutaArchivo2);
        }
    }

    $mail->send();

    echo "<script>alert('Solicitud enviada correctamente'); window.location='panel-usuario.php';</script>";
} catch (Exception $e) {
    echo "Error al enviar el correo: {$mail->ErrorInfo}";
}

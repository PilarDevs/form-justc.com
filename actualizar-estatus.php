<?php
require_once 'protegido/config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $estatus = $_POST['estatus'] ?? null;

    if ($id && in_array($estatus, ['pendiente', 'cerrada', 'completada'])) {
        // Actualizar estatus
        $stmt = $pdo->prepare("UPDATE solicitudes SET estatus = ? WHERE id = ?");
        if ($stmt->execute([$estatus, $id])) {

            // Obtener todos los usuarios para enviar correo
            $stmtUsuarios = $pdo->query("SELECT nombre, correo FROM usuarios");
            $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

            if ($usuarios) {
                // Configuración SMTP
                $config = require 'protegido/configMail.php';

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = $config['smtp_user'];
                    $mail->Password = $config['smtp_pass'];
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom($config['smtp_user'], 'Justech - Sistema de Solicitudes');
                    $mail->isHTML(true);
                    $mail->Subject = "Actualización de estado de la solicitud #$id";
                    $mail->Body = "
                        Estimado/a usuario,<br><br>
                        El estado de la solicitud con ID <strong>$id</strong> ha sido actualizado a <strong>$estatus</strong>.<br><br>
                        Puede ingresar al sistema para más detalles.<br><br>
                        Atentamente,<br>Equipo Justech.
                    ";

                    // Agregar todos los destinatarios
                    foreach ($usuarios as $usuario) {
                        if (filter_var($usuario['correo'], FILTER_VALIDATE_EMAIL)) {
                            $mail->addAddress($usuario['correo'], $usuario['nombre']);
                        }
                    }

                    $mail->send();
                    echo "Estado actualizado y correo enviado a todos los usuarios.";
                } catch (Exception $e) {
                    error_log("Error al enviar correo: " . $mail->ErrorInfo);
                    echo "Estado actualizado, pero hubo un error al enviar los correos.";
                }
            } else {
                echo "Estado actualizado, pero no se encontraron usuarios para notificar.";
            }
        } else {
            echo "Error al actualizar.";
        }
    } else {
        echo "Datos inválidos.";
    }
}

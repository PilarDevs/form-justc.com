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

            // Obtener datos del usuario que hizo la solicitud
            $stmtUsuario = $pdo->prepare("
                SELECT u.nombre, u.correo 
                FROM solicitudes s
                INNER JOIN usuarios u ON s.id_usuario = u.id
                WHERE s.id = ?
            ");
            $stmtUsuario->execute([$id]);
            $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                // Enviar correo
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
                    $mail->addAddress($usuario['correo'], $usuario['nombre']);

                    $mail->isHTML(true);
                    $mail->Subject = "Actualización de estado de su solicitud #$id";
                    $mail->Body = "
                        Estimado/a <strong>{$usuario['nombre']}</strong>,<br><br>
                        El estado de su solicitud con ID <strong>$id</strong> ha sido actualizado a <strong>$estatus</strong>.<br><br>
                        Puede ingresar al sistema para más detalles.<br><br>
                        Atentamente,<br>Equipo Justech.
                    ";

                    $mail->send();
                    echo "Estado actualizado y correo enviado.";
                } catch (Exception $e) {
                    error_log("Error al enviar correo: " . $mail->ErrorInfo);
                    echo "Estado actualizado, pero hubo un error al enviar el correo.";
                }
            } else {
                echo "Estado actualizado, pero no se encontró el usuario.";
            }
        } else {
            echo "Error al actualizar.";
        }
    } else {
        echo "Datos inválidos.";
    }
}

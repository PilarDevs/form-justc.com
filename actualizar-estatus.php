<?php
require_once 'protegido/config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $estatus = $_POST['estatus'] ?? null;

    if ($id && in_array($estatus, ['pendiente', 'cerrada', 'completada', 'cerrado'])) {
        // Verificar si la solicitud ya está cerrada dentro del SLA y bloquear cambio
        $stmtCheck = $pdo->prepare("SELECT s.estatus, s.fecha_envio, s.fecha_cierre_original FROM solicitudes s WHERE s.id = ?");
        $stmtCheck->execute([$id]);
        $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $currentStatus = $row['estatus'];
            $fechaEnvio = new DateTime($row['fecha_envio']);
            $fechaCierre = !empty($row['fecha_cierre_original']) ? new DateTime($row['fecha_cierre_original']) : null;

            // función local: sumar N días laborables (ignorar sábados(6) y domingos(7)) a una fecha
            $add_working_days = function (DateTime $dt, $days) {
                $d = clone $dt;
                $count = 0;
                while ($count < $days) {
                    // mover al siguiente día
                    $d->modify('+1 day');
                    $dow = $d->format('N');
                    if ($dow != 6 && $dow != 7) {
                        $count++;
                    }
                }
                return $d;
            };

            // calcular inicio a partir de fecha_envio +1 dia (si cae fin de semana, mover al siguiente lunes)
            $start = clone $fechaEnvio;
            $start->modify('+1 day');
            $dow = $start->format('N');
            if ($dow == 6) $start->modify('next monday');
            if ($dow == 7) $start->modify('next monday');

            $limite = $add_working_days($start, 4); // deadline = start + 4 dias laborables

            if (($currentStatus === 'cerrado' || $currentStatus === 'completada') && $fechaCierre instanceof DateTime) {
                if ($fechaCierre <= $limite) {
                    echo "No se puede actualizar: la solicitud fue cerrada dentro del SLA (fecha_cierre_original).";
                    exit;
                }
            }
        }

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

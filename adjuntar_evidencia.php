<?php
session_start();
require_once 'protegido/config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id_solicitud = $_GET['id'] ?? null;
if (!$id_solicitud || !is_numeric($id_solicitud)) {
    echo "ID inválido.";
    exit;
}

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comentario = trim($_POST['comentario'] ?? '');
    $archivos = $_FILES['archivos'] ?? null;

    if (!$archivos || !isset($archivos['name']) || count($archivos['name']) === 0) {
        $mensaje = "Debe subir al menos un archivo.";
    } else {
        $dir = __DIR__ . '/evidencias/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $permitidos = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        $archivosGuardados = [];
        $errores = [];

        for ($i = 0; $i < count($archivos['name']); $i++) {
            $nombreArchivo = $archivos['name'][$i];
            $tmpName = $archivos['tmp_name'][$i];
            $size = $archivos['size'][$i];
            $error = $archivos['error'][$i];

            if ($error !== UPLOAD_ERR_OK) {
                $errores[] = "Error al subir archivo: $nombreArchivo";
                continue;
            }
            if ($size > 10 * 1024 * 1024) {
                $errores[] = "Archivo demasiado grande (max 10MB): $nombreArchivo";
                continue;
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmpName);
            finfo_close($finfo);

            if (!in_array($mime, $permitidos)) {
                $errores[] = "Formato no permitido: $nombreArchivo";
                continue;
            }

            $nuevoNombre = time() . '_' . bin2hex(random_bytes(5)) . '_' . basename($nombreArchivo);
            $rutaArchivo = $dir . $nuevoNombre;

            if (move_uploaded_file($tmpName, $rutaArchivo)) {
                $archivosGuardados[] = [
                    'ruta' => $rutaArchivo,
                    'nombre' => $nombreArchivo
                ];
            } else {
                $errores[] = "No se pudo mover el archivo: $nombreArchivo";
            }
        }

        if (count($archivosGuardados) === 0) {
            $mensaje = "No se subió ningún archivo correctamente.<br>" . implode('<br>', $errores);
        } else {
            $stmt = $pdo->query("SELECT correo, nombre FROM usuarios");
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$usuarios) {
                $mensaje = "No hay usuarios para enviar el correo.";
            } else {
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

                    $mail->setFrom($config['smtp_user'], 'Justech - Sistema de Soporte');
                    $mail->isHTML(true);
                    $mail->Subject = "Soporte final adjunto para solicitud #$id_solicitud (TICKET CERRADO)";

                    $listaArchivos = "<ul>";
                    foreach ($archivosGuardados as $a) {
                        $listaArchivos .= "<li>" . htmlspecialchars($a['nombre']) . "</li>";
                        $mail->addAttachment($a['ruta'], $a['nombre']);
                    }
                    $listaArchivos .= "</ul>";

                    $body = "
                        <p>Se ha adjuntado soporte final para la solicitud <strong>#$id_solicitud</strong>.</p>
                        <p><strong>Comentario:</strong><br>" . nl2br(htmlspecialchars($comentario)) . "</p>
                        <p><strong>Archivos adjuntos:</strong>$listaArchivos</p>
                        <p><strong>El ticket ha sido marcado como <span style='color:red;'>CERRADO</span>.</strong></p>
                    ";
                    $mail->Body = $body;

                    foreach ($usuarios as $usuario) {
                        if (filter_var($usuario['correo'], FILTER_VALIDATE_EMAIL)) {
                            $mail->addAddress($usuario['correo'], $usuario['nombre']);
                        }
                    }

                    $mail->send();

                    // ACTUALIZAR A CERRADO
                    $update = $pdo->prepare("UPDATE solicitudes SET estatus = 'cerrado' WHERE id = ?");
                    $update->execute([$id_solicitud]);

                    $mensaje = "Soporte enviado y ticket #$id_solicitud cerrado correctamente.";
                    if ($errores) {
                        $mensaje .= "<br>Sin embargo, hubo algunos errores:<br>" . implode('<br>', $errores);
                    }
                } catch (Exception $e) {
                    $mensaje = "Error al enviar correos: " . $mail->ErrorInfo;
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Adjuntar Evidencias a Solicitud #<?= htmlspecialchars($id_solicitud) ?></title>
    <style>
        body {
            font-family: Arial;
            margin: 40px;
        }

        label {
            display: block;
            margin-top: 15px;
        }

        textarea {
            width: 100%;
            height: 100px;
            margin-top: 5px;
            resize: vertical;
        }

        .input-archivo {
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .input-archivo input[type="file"] {
            flex: 1;
        }

        .input-archivo button {
            padding: 6px 10px;
            background-color: #888;
            color: white;
            border: none;
            cursor: pointer;
        }

        .input-archivo button:hover {
            background-color: #555;
        }

        button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #005792;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #00466b;
        }

        .mensaje {
            margin-top: 20px;
            font-weight: bold;
            color: green;
        }

        .error {
            color: red;
        }

        #loader {
            margin-top: 15px;
            display: none;
            font-weight: bold;
            color: #005792;
        }
    </style>
</head>

<body>

    <h2>Adjuntar Evidencias a Solicitud #<?= htmlspecialchars($id_solicitud) ?></h2>

    <?php if ($mensaje): ?>
        <div class="mensaje <?= strpos(strtolower($mensaje), 'error') !== false ? 'error' : '' ?>">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="form-evidencia">
        <label>Archivos (PDF, Word, Imágenes; máx 10MB c/u):</label>
        <div id="grupo-archivos">
            <div class="input-archivo">
                <input type="file" name="archivos[]" accept=".pdf,.doc,.docx,image/*" required>
                <button type="button" onclick="agregarArchivo()">+</button>
            </div>
        </div>

        <label for="comentario">Comentario:</label>
        <textarea name="comentario" id="comentario" placeholder="Agrega un comentario..."></textarea>

        <button type="submit" id="btn-enviar">Subir Evidencias y Enviar Correo</button>
        <div id="loader">Enviando evidencias... por favor espera.</div>
    </form>

    <p><a href="ver-solicitudes.php">Volver a lista de solicitudes</a></p>

    <script>
        function agregarArchivo() {
            const div = document.createElement('div');
            div.className = 'input-archivo';
            div.innerHTML = `
        <input type="file" name="archivos[]" accept=".pdf,.doc,.docx,image/*" required>
        <button type="button" onclick="this.parentNode.remove()">-</button>
    `;
            document.getElementById('grupo-archivos').appendChild(div);
        }

        document.getElementById("form-evidencia").addEventListener("submit", function() {
            const btn = document.getElementById("btn-enviar");
            const loader = document.getElementById("loader");
            btn.disabled = true;
            btn.style.opacity = 0.6;
            loader.style.display = "block";
        });
    </script>

</body>

</html>
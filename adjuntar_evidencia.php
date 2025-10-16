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
    $stmtSucursal = $pdo->prepare("SELECT sucursal FROM detalles_solicitudes WHERE id_solicitud = ?");
    $stmtSucursal->execute([$id_solicitud]);
    $sucursal = $stmtSucursal->fetchColumn();
    if (!$id_solicitud || !is_numeric($id_solicitud)) {
    echo "ID inválido.";
    exit;
}

$mensaje = '';
$archivosGuardados = [];
$exito = false;

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
            // Generar PDF con las imágenes subidas
            $imagenes = array_filter($archivosGuardados, function($a) {
                $ext = strtolower(pathinfo($a['ruta'], PATHINFO_EXTENSION));
                return in_array($ext, ['jpg','jpeg','png','gif']);
            });
            if (count($imagenes) > 0) {
                $html = '<div style="font-family:Arial,sans-serif;max-width:700px;margin:auto;">';
                $html .= '<h2 style="color:#005792;text-align:center;margin-bottom:10px;">Evidencias de Solicitud #'.htmlspecialchars($id_solicitud).'</h2>';
                $html .= '<div style="background:#f6f7f9;border-radius:8px;padding:12px 18px;margin-bottom:18px;">';
                $html .= '<strong>Sucursal:</strong> '.htmlspecialchars($sucursal).'<br>';
                $html .= '<strong>Comentario:</strong><br><div style="margin:8px 0 0 0;padding:8px 12px;background:#fff;border-radius:6px;border:1px solid #e0e0e0;">'.nl2br(htmlspecialchars($comentario)).'</div>';
                $html .= '</div>';
                $html .= '<h3 style="color:#333;text-align:center;margin:30px 0 18px 0;border-bottom:1px solid #e0e0e0;padding-bottom:8px;">Imágenes adjuntas</h3>';
                foreach ($imagenes as $img) {
                    $ruta = $img['ruta'];
                    $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
                    $mime = ($ext === 'jpg' || $ext === 'jpeg') ? 'image/jpeg' : (($ext === 'png') ? 'image/png' : (($ext === 'gif') ? 'image/gif' : ''));
                    $imgData = base64_encode(file_get_contents($ruta));
                    $src = 'data:' . $mime . ';base64,' . $imgData;
                    $html .= '<div style="page-break-inside:avoid;margin-bottom:28px;padding:18px 0 10px 0;border-bottom:1px solid #e0e0e0;">';
                    $html .= '<img src="'. $src .'" style="max-width:500px;max-height:600px;display:block;margin:auto 0 10px auto;border-radius:8px;box-shadow:0 2px 8px #bbb;">';
                    $html .= '<div style="text-align:center;color:#555;font-size:0.98em;margin-top:4px;">'.htmlspecialchars($img['nombre']).'</div>';
                    $html .= '</div>';
                }
                $html .= '</div>';
                $pdfFile = $dir.'evidencias_solicitud_'.$id_solicitud.'.pdf';
                $dompdf = new Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                file_put_contents($pdfFile, $dompdf->output());
            }

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
                        // Solo adjuntar archivos que no sean imagen (las imágenes van en el PDF)
                        $ext = strtolower(pathinfo($a['ruta'], PATHINFO_EXTENSION));
                        if (!in_array($ext, ['jpg','jpeg','png','gif'])) {
                            $mail->addAttachment($a['ruta'], $a['nombre']);
                        }
                    }
                    $listaArchivos .= "</ul>";
                    // Adjuntar solo el PDF generado con las imágenes
                    if (isset($pdfFile) && file_exists($pdfFile)) {
                        $mail->addAttachment($pdfFile, basename($pdfFile));
                    }

                    $body = "
                            <p>Se ha adjuntado soporte final para la solicitud <strong>#$id_solicitud</strong>.</p>
                            <p><strong>Sucursal:</strong> " . htmlspecialchars($sucursal) . "</p>
                            <p><strong>Comentario:</strong><br>" . nl2br(htmlspecialchars($comentario)) . "</p>
                            <p><strong>El ticket ha sido marcado como <span style='color:red;'>CERRADO</span>.</strong></p>
                        ";

                    $mail->Body = $body;

                    foreach ($usuarios as $usuario) {
                        if (filter_var($usuario['correo'], FILTER_VALIDATE_EMAIL)) {
                            $mail->addAddress($usuario['correo'], $usuario['nombre']);
                        }
                    }
                    $mail->addAddress('recepcion@justech.do', 'Copia Fija'); 

                    $mail->send();

                    // Actualizar el estatus a cerrado
                    $update = $pdo->prepare("UPDATE solicitudes SET estatus = 'cerrado' WHERE id = ?");
                    $update->execute([$id_solicitud]);

                    $mensaje = "Soporte enviado y ticket #$id_solicitud cerrado correctamente.";
                    if ($errores) {
                        $mensaje .= "<br>Sin embargo, hubo algunos errores:<br>" . implode('<br>', $errores);
                    }

                    $exito = true;
                } catch (Exception $e) {
                    $mensaje = "Error al enviar correos: " . $mail->ErrorInfo;
                }
            }
        }
    }
}

if ($exito) {
    header("Location: ver-solicitudes.php?mensaje=soporte_enviado&id=$id_solicitud");
    exit;
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
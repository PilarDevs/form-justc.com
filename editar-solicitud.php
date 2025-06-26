<?php
session_start();
    require 'protegido/config.php';
    require 'vendor/autoload.php';
    $config = require 'protegido/configMail.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

// Verificar que el usuario es admin
if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'admin') {
    die("Acceso no autorizado");
}

// Obtener ID solicitud
$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID de solicitud no proporcionado");
}

// Traer datos actuales de solicitud y detalles
$stmt = $pdo->prepare("
    SELECT s.*, d.* 
    FROM solicitudes s 
    LEFT JOIN detalles_solicitudes d ON s.id = d.id_solicitud 
    WHERE s.id = ?");
$stmt->execute([$id]);
$solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$solicitud) {
    die("Solicitud no encontrada");
}

// Procesar formulario al enviar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ejemplo de campos a actualizar, agrega todos los que necesites
    $sucursal = $_POST['sucursal'] ?? '';
    $piso = $_POST['piso'] ?? '';
    $contacto = $_POST['contacto'] ?? '';
    $tipo_otro = $_POST['tipo_otro'] ?? '';
    $cantidad_servicio = $_POST['cantidad_servicio'] ?? '';
    $puntos_instalar = $_POST['puntos_instalar'] ?? '';
    $puntos_reparar = $_POST['puntos_reparar'] ?? '';
    $materiales = $_POST['materiales'] ?? '';
    $detalle_cable_utp = $_POST['detalle_cable_utp'] ?? '';
    $detalle_patch_cord = $_POST['detalle_patch_cord'] ?? '';
    $detalle_conector_rj45 = $_POST['detalle_conector_rj45'] ?? '';
    $detalle_jack = $_POST['detalle_jack'] ?? '';
    $detalle_patch_panel = $_POST['detalle_patch_panel'] ?? '';
    $faceplate_bocas = $_POST['faceplate_bocas'] ?? '';
    $faceplate_cantidad = $_POST['faceplate_cantidad'] ?? '';
    $canalizacion = $_POST['canalizacion'] ?? '';
    $tamano_canaleta = $_POST['tamano_canaleta'] ?? '';
    $diametro_emt = $_POST['diametro_emt'] ?? '';
    $cantidad_bandeja = $_POST['cantidad_bandeja'] ?? '';
    $gabinete_tipo = $_POST['gabinete_tipo'] ?? '';
    $gabinete_tamano = $_POST['gabinete_tamano'] ?? '';
    $equipos = $_POST['equipos'] ?? '';
    $cantidad_pdu = $_POST['cantidad_pdu'] ?? '';
    $equipo_otro = $_POST['equipo_otro'] ?? '';
    $comentario = $_POST['comentario'] ?? '';
    $prioridad = $_POST['prioridad'] ?? '';
    $seguridad = $_POST['seguridad'] ?? '';

    // Actualizar tabla detalles_solicitudes
    $sql = "UPDATE detalles_solicitudes SET
        sucursal = :sucursal,
        piso = :piso,
        contacto = :contacto,
        tipo_otro = :tipo_otro,
        cantidad_servicio = :cantidad_servicio,
        puntos_instalar = :puntos_instalar,
        puntos_reparar = :puntos_reparar,
        materiales = :materiales,
        detalle_cable_utp = :detalle_cable_utp,
        detalle_patch_cord = :detalle_patch_cord,
        detalle_conector_rj45 = :detalle_conector_rj45,
        detalle_jack = :detalle_jack,
        detalle_patch_panel = :detalle_patch_panel,
        faceplate_bocas = :faceplate_bocas,
        faceplate_cantidad = :faceplate_cantidad,
        canalizacion = :canalizacion,
        tamano_canaleta = :tamano_canaleta,
        diametro_emt = :diametro_emt,
        cantidad_bandeja = :cantidad_bandeja,
        gabinete_tipo = :gabinete_tipo,
        gabinete_tamano = :gabinete_tamano,
        equipos = :equipos,
        cantidad_pdu = :cantidad_pdu,
        equipo_otro = :equipo_otro,
        comentario = :comentario,
        prioridad = :prioridad,
        seguridad = :seguridad
        WHERE id_solicitud = :id_solicitud";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':sucursal' => $sucursal,
        ':piso' => $piso,
        ':contacto' => $contacto,
        ':tipo_otro' => $tipo_otro,
        ':cantidad_servicio' => $cantidad_servicio,
        ':puntos_instalar' => $puntos_instalar,
        ':puntos_reparar' => $puntos_reparar,
        ':materiales' => $materiales,
        ':detalle_cable_utp' => $detalle_cable_utp,
        ':detalle_patch_cord' => $detalle_patch_cord,
        ':detalle_conector_rj45' => $detalle_conector_rj45,
        ':detalle_jack' => $detalle_jack,
        ':detalle_patch_panel' => $detalle_patch_panel,
        ':faceplate_bocas' => $faceplate_bocas,
        ':faceplate_cantidad' => $faceplate_cantidad,
        ':canalizacion' => $canalizacion,
        ':tamano_canaleta' => $tamano_canaleta,
        ':diametro_emt' => $diametro_emt,
        ':cantidad_bandeja' => $cantidad_bandeja,
        ':gabinete_tipo' => $gabinete_tipo,
        ':gabinete_tamano' => $gabinete_tamano,
        ':equipos' => $equipos,
        ':cantidad_pdu' => $cantidad_pdu,
        ':equipo_otro' => $equipo_otro,
        ':comentario' => $comentario,
        ':prioridad' => $prioridad,
        ':seguridad' => $seguridad,
        ':id_solicitud' => $id
    ]);


    // 1. Obtener correo del solicitante de esta solicitud
$stmtUsuario = $pdo->prepare("
    SELECT u.correo, u.nombre
    FROM solicitudes s
    INNER JOIN usuarios u ON s.id_usuario = u.id
    WHERE s.id = :id
");
$stmtUsuario->execute([':id' => $id]);
$usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

if ($usuario) {

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
        $mail->Subject = 'Actualización de su solicitud #' . $id;
        $mail->Body = "
            Estimado/a <strong>{$usuario['nombre']}</strong>,<br><br>
            Su solicitud técnica con ID <strong>$id</strong> ha sido actualizada por el equipo de soporte.<br><br>
            Si desea revisar los cambios, inicie sesión en el sistema.<br><br>
            Atentamente,<br>Equipo Justech.
        ";

        $mail->send();
        // No mostramos mensaje aquí porque ya haces un alert antes
    } catch (Exception $e) {
        error_log("Error al enviar correo de actualización: " . $mail->ErrorInfo);
    }
}


    echo "<script>alert('Detalles de solicitud actualizados'); window.location = 'ver-solicitudes.php';</script>";
    
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Editar detalles solicitud #<?= htmlspecialchars($id) ?></title>
<style>
  label { display: block; margin-top: 10px; font-weight: bold; }
  input, textarea, select { width: 100%; padding: 8px; margin-top: 4px; }
  button { margin-top: 15px; padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 6px; cursor: pointer; }
  button:hover { background-color: #0056b3; }
  .container { max-width: 700px; margin: auto; padding: 20px; background: #f9f9f9; border-radius: 8px; }
</style>
</head>
<body>

<div class="container">
  <h1>Editar detalles solicitud #<?= htmlspecialchars($id) ?></h1>
  <form method="POST" action="">
    <label for="sucursal">Sucursal</label>
    <input type="text" id="sucursal" name="sucursal" value="<?= htmlspecialchars($solicitud['sucursal'] ?? '') ?>" required>

    <label for="piso">Piso / Área</label>
    <input type="text" id="piso" name="piso" value="<?= htmlspecialchars($solicitud['piso'] ?? '') ?>">

    <label for="contacto">Persona contacto</label>
    <input type="text" id="contacto" name="contacto" value="<?= htmlspecialchars($solicitud['contacto'] ?? '') ?>">

    <label for="tipo_otro">Otro tipo de servicio</label>
    <input type="text" id="tipo_otro" name="tipo_otro" value="<?= htmlspecialchars($solicitud['tipo_otro'] ?? '') ?>">

    <label for="cantidad_servicio">Cantidad de puntos requeridos</label>
    <input type="number" id="cantidad_servicio" name="cantidad_servicio" value="<?= htmlspecialchars($solicitud['cantidad_servicio'] ?? '') ?>">

    <label for="puntos_instalar">Cantidad puntos a instalar</label>
    <input type="number" id="puntos_instalar" name="puntos_instalar" value="<?= htmlspecialchars($solicitud['puntos_instalar'] ?? '') ?>">

    <label for="puntos_reparar">Cantidad puntos a reparar</label>
    <input type="number" id="puntos_reparar" name="puntos_reparar" value="<?= htmlspecialchars($solicitud['puntos_reparar'] ?? '') ?>">

    <label for="materiales">Materiales (coma separados)</label>
    <input type="text" id="materiales" name="materiales" value="<?= htmlspecialchars($solicitud['materiales'] ?? '') ?>">

    <label for="detalle_cable_utp">Detalle Cable UTP (m)</label>
    <input type="text" id="detalle_cable_utp" name="detalle_cable_utp" value="<?= htmlspecialchars($solicitud['detalle_cable_utp'] ?? '') ?>">

    <label for="detalle_patch_cord">Detalle Patch cord</label>
    <input type="text" id="detalle_patch_cord" name="detalle_patch_cord" value="<?= htmlspecialchars($solicitud['detalle_patch_cord'] ?? '') ?>">

    <label for="detalle_conector_rj45">Detalle Conector RJ45</label>
    <input type="text" id="detalle_conector_rj45" name="detalle_conector_rj45" value="<?= htmlspecialchars($solicitud['detalle_conector_rj45'] ?? '') ?>">

    <label for="detalle_jack">Detalle Jack RJ45</label>
    <input type="text" id="detalle_jack" name="detalle_jack" value="<?= htmlspecialchars($solicitud['detalle_jack'] ?? '') ?>">

    <label for="detalle_patch_panel">Detalle Patch panel</label>
    <input type="text" id="detalle_patch_panel" name="detalle_patch_panel" value="<?= htmlspecialchars($solicitud['detalle_patch_panel'] ?? '') ?>">

    <label for="faceplate_bocas">Faceplate (bocas)</label>
    <input type="text" id="faceplate_bocas" name="faceplate_bocas" value="<?= htmlspecialchars($solicitud['faceplate_bocas'] ?? '') ?>">

    <label for="faceplate_cantidad">Cantidad de faceplates</label>
    <input type="number" id="faceplate_cantidad" name="faceplate_cantidad" value="<?= htmlspecialchars($solicitud['faceplate_cantidad'] ?? '') ?>">

    <label for="canalizacion">Canalización (coma separados)</label>
    <input type="text" id="canalizacion" name="canalizacion" value="<?= htmlspecialchars($solicitud['canalizacion'] ?? '') ?>">

    <label for="tamano_canaleta">Tamaño canaleta</label>
    <input type="text" id="tamano_canaleta" name="tamano_canaleta" value="<?= htmlspecialchars($solicitud['tamano_canaleta'] ?? '') ?>">

    <label for="diametro_emt">Diámetro EMT</label>
    <input type="text" id="diametro_emt" name="diametro_emt" value="<?= htmlspecialchars($solicitud['diametro_emt'] ?? '') ?>">

    <label for="cantidad_bandeja">Cantidad de bandejas</label>
    <input type="number" id="cantidad_bandeja" name="cantidad_bandeja" value="<?= htmlspecialchars($solicitud['cantidad_bandeja'] ?? '') ?>">

    <label for="gabinete_tipo">Gabinete tipo</label>
    <input type="text" id="gabinete_tipo" name="gabinete_tipo" value="<?= htmlspecialchars($solicitud['gabinete_tipo'] ?? '') ?>">

    <label for="gabinete_tamano">Gabinete tamaño</label>
    <input type="text" id="gabinete_tamano" name="gabinete_tamano" value="<?= htmlspecialchars($solicitud['gabinete_tamano'] ?? '') ?>">

    <label for="equipos">Equipos (coma separados)</label>
    <input type="text" id="equipos" name="equipos" value="<?= htmlspecialchars($solicitud['equipos'] ?? '') ?>">

    <label for="cantidad_pdu">Cantidad PDU</label>
    <input type="number" id="cantidad_pdu" name="cantidad_pdu" value="<?= htmlspecialchars($solicitud['cantidad_pdu'] ?? '') ?>">

    <label for="equipo_otro">Equipo otro</label>
    <input type="text" id="equipo_otro" name="equipo_otro" value="<?= htmlspecialchars($solicitud['equipo_otro'] ?? '') ?>">

    <label for="comentario">Comentario</label>
    <textarea id="comentario" name="comentario"><?= htmlspecialchars($solicitud['comentario'] ?? '') ?></textarea>

    <label for="prioridad">Prioridad</label>
    <input type="text" id="prioridad" name="prioridad" value="<?= htmlspecialchars($solicitud['prioridad'] ?? '') ?>">

    <label for="seguridad">Seguridad (coma separados)</label>
    <input type="text" id="seguridad" name="seguridad" value="<?= htmlspecialchars($solicitud['seguridad'] ?? '') ?>">

    <button type="submit">Guardar cambios</button>
  </form>
</div>

</body>
</html>

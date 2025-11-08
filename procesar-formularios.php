<?php
require 'vendor/autoload.php';
require 'protegido/config.php';  // Config DB
$config = require 'protegido/configMail.php'; // Config mail

use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    die("No autorizado. Debes iniciar sesión.");
}

// Datos del formulario
$fecha_envio = date('Y-m-d H:i:s');
$estatus = 'pendiente';

$sucursal = $_POST['sucursal'] ?? '';
$piso = $_POST['piso'] ?? '';
$contacto = $_POST['contacto'] ?? '';
$comentario = $_POST['comentario'] ?? '';
$prioridad = $_POST['prioridad'] ?? '';

$otroTipo = $_POST['otro_tipo'] ?? '';
$cantidadServicio = $_POST['cantidad_servicio'] ?? '';

$materiales = $_POST['materiales'] ?? [];
$detalle_Cable_UTP = $_POST['detalle_Cable_UTP'] ?? '';
$detalle_Patch_cord = $_POST['detalle_Patch_cord'] ?? '';
$detalle_Conector_RJ45 = $_POST['detalle_Conector_RJ45'] ?? '';
$detalle_Jack = $_POST['detalle_Jack'] ?? '';
$detalle_Patch_panel = $_POST['detalle_Patch_panel'] ?? '';
$faceplate = $_POST['faceplate_bocas'] ?? '';
$faceplateCantidad = $_POST['faceplate_cantidad'] ?? '';

$canalizacion = $_POST['canalizacion'] ?? [];
$diametroEMT = $_POST['diametro_emt'] ?? '';
$tamanoCanaleta = $_POST['tamano_canaleta'] ?? '';
$cantidadBandeja = $_POST['cantidad_bandeja'] ?? '';
$gabineteTipo = $_POST['gabinete_tipo'] ?? '';
$gabineteTamano = $_POST['gabinete_tamano'] ?? '';

$equipos = $_POST['equipos'] ?? [];
$cantidadPDU = $_POST['cantidad_pdu'] ?? '';
$equipoOtro = $_POST['equipo_otro'] ?? '';

$seguridad = $_POST['seguridad'] ?? [];
$cantidad_puntos_instalar = $_POST['cantidad_puntos_instalar'] ?? '';
$cantidad_puntos_reparar = $_POST['cantidad_puntos_reparar'] ?? '';
$file1 = $_FILES['file_opcional1'] ?? '';
$file2 = $_FILES['file_opcional2'] ?? '';

// Crear carpeta PDF si no existe
$pdfsDir = __DIR__ . '/pdfs/';
if (!file_exists($pdfsDir)) {
    mkdir($pdfsDir, 0755, true);
}

// 1) Insertar solicitud inicialmente con url_pdf vacío para obtener ID
try {
    $sql = "INSERT INTO solicitudes (id_usuario, url_pdf, fecha_envio, estatus) 
            VALUES (:id_usuario, '', :fecha_envio, :estatus)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_usuario' => $id_usuario,
        ':fecha_envio' => $fecha_envio,
        ':estatus' => $estatus
    ]);
    $id_solicitud = $pdo->lastInsertId();
} catch (PDOException $e) {
    die("Error al guardar la solicitud: " . $e->getMessage());
}

// Logo base64 para PDF
$logoBase64 = base64_encode(file_get_contents('img/Justech-text-logo.png"'));

// 2) Construir HTML para PDF con ID ya disponible
$html = '
<style>
  body { font-family: Arial, sans-serif; font-size: 13px; color: #333; }
  .header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
  }
  .header img {
    width: 80px;
    height: auto;
    max-height: 60px;
    position: relative;
    left: 620px; 
    top: 10px;
  }
  .header .info {
    text-align: center;
    flex-grow: 1;
    margin-left: 20px;
  }
  h1 { background: #005baa; color: #fff; font-size: 18px; padding: 8px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  td, th { padding: 8px; border: 1px solid #ccc; vertical-align: top; }
  .label { background-color: #f0f0f0; font-weight: bold; width: 30%; }
</style>

<div class="header">
  <img src="data:image/png;base64,' . $logoBase64 . '" alt="Logo" />
  <div class="info">
    <h2>Solicitud Técnica de Instalación o Reparación</h2>
    <p><strong>Fecha:</strong> ' . $fecha_envio . '<br>
    <strong>No. ID:</strong> ' . $id_solicitud . '</p>
  </div>
</div>

<h1>Datos del solicitante</h1>
<table>
  <tr><td class="label">Sucursal</td><td>' . htmlspecialchars($sucursal) . '</td></tr>
  <tr><td class="label">Piso / Área</td><td>' . htmlspecialchars($piso) . '</td></tr>
  <tr><td class="label">Persona contacto</td><td>' . htmlspecialchars($contacto) . '</td></tr>
</table>

<h1>Detalles del servicio</h1>
<table>';
if (!empty($otroTipo)) {
  $html .= '<tr><td class="label">Otro (descripción)</td><td>' . htmlspecialchars($otroTipo) . '</td></tr>';
}
if (!empty($cantidadServicio)) {
  $html .= '<tr><td class="label">Cantidad de puntos requeridos</td><td>' . htmlspecialchars($cantidadServicio) . '</td></tr>';
}
if (!empty($cantidad_puntos_instalar)) {
  $html .= '<tr><td class="label">Cantidad de puntos a instalar</td><td>' . htmlspecialchars($cantidad_puntos_instalar) . '</td></tr>';
}
if (!empty($cantidad_puntos_reparar)) {
  $html .= '<tr><td class="label">Cantidad de puntos a reparar</td><td>' . htmlspecialchars($cantidad_puntos_reparar) . '</td></tr>';
}
$html .= '</table>

<h1>Materiales requeridos</h1>
<table>
  <tr><td class="label">Materiales</td><td>' . htmlspecialchars(implode(', ', $materiales)) . '</td></tr>';
if (!empty($detalle_Cable_UTP)) $html .= '<tr><td class="label">Cable UTP (m)</td><td>' . htmlspecialchars($detalle_Cable_UTP) . '</td></tr>';
if (!empty($detalle_Patch_cord)) $html .= '<tr><td class="label">Patch cord</td><td>' . htmlspecialchars($detalle_Patch_cord) . '</td></tr>';
if (!empty($detalle_Conector_RJ45)) $html .= '<tr><td class="label">Conectores RJ45</td><td>' . htmlspecialchars($detalle_Conector_RJ45) . '</td></tr>';
if (!empty($detalle_Jack)) $html .= '<tr><td class="label">Jack RJ45</td><td>' . htmlspecialchars($detalle_Jack) . '</td></tr>';
if (!empty($detalle_Patch_panel)) $html .= '<tr><td class="label">Patch panel</td><td>' . htmlspecialchars($detalle_Patch_panel) . '</td></tr>';
if (!empty($faceplate)) $html .= '<tr><td class="label">Faceplate (bocas)</td><td>' . htmlspecialchars($faceplate) . '</td></tr>';
if (!empty($faceplateCantidad)) $html .= '<tr><td class="label">Cantidad de faceplates</td><td>' . htmlspecialchars($faceplateCantidad) . '</td></tr>';
$html .= '</table>

<h1>Canalización y montaje</h1>
<table>
  <tr><td class="label">Elementos</td><td>' . htmlspecialchars(implode(', ', $canalizacion)) . '</td></tr>';
if (!empty($tamanoCanaleta)) $html .= '<tr><td class="label">Tamaño canaleta</td><td>' . htmlspecialchars($tamanoCanaleta) . '</td></tr>';
if (!empty($diametroEMT)) $html .= '<tr><td class="label">Diámetro EMT</td><td>' . htmlspecialchars($diametroEMT) . '</td></tr>';
if (!empty($cantidadBandeja)) $html .= '<tr><td class="label">Cantidad de bandejas</td><td>' . htmlspecialchars($cantidadBandeja) . '</td></tr>';
if (!empty($gabineteTipo)) $html .= '<tr><td class="label">Gabinete Tipo</td><td>' . htmlspecialchars($gabineteTipo) . '</td></tr>';
if (!empty($gabineteTamano)) $html .= '<tr><td class="label">Gabinete Tamaño</td><td>' . htmlspecialchars($gabineteTamano) . '</td></tr>';
$html .= '</table>

<h1>Equipos activos</h1>
<table>
  <tr><td class="label">Equipos</td><td>' . htmlspecialchars(implode(', ', $equipos)) . '</td></tr>';
if (!empty($cantidadPDU)) $html .= '<tr><td class="label">Cantidad de PDU</td><td>' . htmlspecialchars($cantidadPDU) . '</td></tr>';
if (!empty($equipoOtro)) $html .= '<tr><td class="label">Otros equipos</td><td>' . htmlspecialchars($equipoOtro) . '</td></tr>';
$html .= '</table>

<h1>Observaciones</h1>
<table>
  <tr><td class="label">Comentario</td><td>' . htmlspecialchars($comentario) . '</td></tr>
  <tr><td class="label">Prioridad</td><td>' . htmlspecialchars($prioridad) . '</td></tr>
</table>

<h1>Requisitos de seguridad</h1>
<table>
  <tr><td class="label">Autorizaciones especiales</td><td>' . (!empty($seguridad) ? htmlspecialchars(implode(', ', $seguridad)) : 'No especificadas') . '</td></tr>
</table>
 <label for="">Solicitado por: ' . htmlspecialchars($_SESSION['nombre']) . '</label>';

// 3) Generar PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$nombrePDF = 'solicitud_' . time() . '.pdf';
$rutaPDF = $pdfsDir . $nombrePDF;
file_put_contents($rutaPDF, $dompdf->output());

// 4) Actualizar la solicitud con el nombre del PDF
try {
    $sql_update = "UPDATE solicitudes SET url_pdf = :url_pdf WHERE id = :id";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([
        ':url_pdf' => $nombrePDF,
        ':id' => $id_solicitud
    ]);
} catch (PDOException $e) {
    die("Error al actualizar la URL del PDF: " . $e->getMessage());
}

//////////

// Subida de archivos opcionales
$archivosDir = __DIR__ . '/archivos/';
if (!file_exists($archivosDir)) {
    mkdir($archivosDir, 0755, true);
}

$urlArchivo1 = '';
$urlArchivo2 = '';

if (!empty($_FILES['file-opcional']['name']) && $_FILES['file-opcional']['error'] === UPLOAD_ERR_OK) {
    $nombre1 = 'archivo1_' . time() . '_' . basename($_FILES['file-opcional']['name']);
    move_uploaded_file($_FILES['file-opcional']['tmp_name'], $archivosDir . $nombre1);
    $urlArchivo1 = 'archivos/' . $nombre1;
}

if (!empty($_FILES['file-opcional2']['name']) && $_FILES['file-opcional2']['error'] === UPLOAD_ERR_OK) {
    $nombre2 = 'archivo2_' . time() . '_' . basename($_FILES['file-opcional2']['name']);
    move_uploaded_file($_FILES['file-opcional2']['tmp_name'], $archivosDir . $nombre2);
    $urlArchivo2 = 'archivos/' . $nombre2;
}

/////////

// 5) Insertar detalles de la solicitud
try {
    $sql_detalle = "INSERT INTO detalles_solicitudes (
        id_solicitud, sucursal, piso, contacto,
        tipo_otro, cantidad_servicio,
        puntos_instalar, puntos_reparar,
        materiales, detalle_cable_utp, detalle_patch_cord, detalle_conector_rj45, detalle_jack, detalle_patch_panel,
        faceplate_bocas, faceplate_cantidad,
        canalizacion, tamano_canaleta, diametro_emt, cantidad_bandeja,
        gabinete_tipo, gabinete_tamano,
        equipos, cantidad_pdu, equipo_otro,
        comentario, prioridad, seguridad,
        archivo_opcional1, archivo_opcional2
    ) VALUES (
        :id_solicitud, :sucursal, :piso, :contacto,
        :tipo_otro, :cantidad_servicio,
        :puntos_instalar, :puntos_reparar,
        :materiales, :detalle_cable_utp, :detalle_patch_cord, :detalle_conector_rj45, :detalle_jack, :detalle_patch_panel,
        :faceplate_bocas, :faceplate_cantidad,
        :canalizacion, :tamano_canaleta, :diametro_emt, :cantidad_bandeja,
        :gabinete_tipo, :gabinete_tamano,
        :equipos, :cantidad_pdu, :equipo_otro,
        :comentario, :prioridad, :seguridad,
        :archivo_opcional1, :archivo_opcional2
    )";

    $stmt_detalle = $pdo->prepare($sql_detalle);
    $stmt_detalle->execute([
        ':id_solicitud' => $id_solicitud,
        ':sucursal' => $sucursal,
        ':piso' => $piso,
        ':contacto' => $contacto,
        ':tipo_otro' => $otroTipo,
        ':cantidad_servicio' => $cantidadServicio,
        ':puntos_instalar' => $cantidad_puntos_instalar,
        ':puntos_reparar' => $cantidad_puntos_reparar,
        ':materiales' => implode(', ', $materiales),
        ':detalle_cable_utp' => $detalle_Cable_UTP,
        ':detalle_patch_cord' => $detalle_Patch_cord,
        ':detalle_conector_rj45' => $detalle_Conector_RJ45,
        ':detalle_jack' => $detalle_Jack,
        ':detalle_patch_panel' => $detalle_Patch_panel,
        ':faceplate_bocas' => $faceplate,
        ':faceplate_cantidad' => $faceplateCantidad,
        ':canalizacion' => implode(', ', $canalizacion),
        ':tamano_canaleta' => $tamanoCanaleta,
        ':diametro_emt' => $diametroEMT,
        ':cantidad_bandeja' => $cantidadBandeja,
        ':gabinete_tipo' => $gabineteTipo,
        ':gabinete_tamano' => $gabineteTamano,
        ':equipos' => implode(', ', $equipos),
        ':cantidad_pdu' => $cantidadPDU,
        ':equipo_otro' => $equipoOtro,
        ':comentario' => $comentario,
        ':prioridad' => $prioridad,
        ':seguridad' => implode(', ', $seguridad),
        ':archivo_opcional1' => $urlArchivo1,
        ':archivo_opcional2' => $urlArchivo2,
    ]);
} catch (PDOException $e) {
    die("Error al guardar detalles: " . $e->getMessage());
}

include 'enviarMail/sendPrimaryMail.php';
include 'enviarMail/secondMail.php';

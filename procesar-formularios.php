<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$config = require 'protegido/configMail.php';

// === Crear carpeta para PDF si no existe ===
$pdfsDir = __DIR__ . '/pdfs/';
if (!file_exists($pdfsDir)) {
    mkdir($pdfsDir, 0755, true);
}

// === Capturar datos del formulario ===
$fecha = date('Y-m-d H:i:s');

$sucursal = $_POST['sucursal'] ?? '';
$piso = $_POST['piso'] ?? '';
$contacto = $_POST['contacto'] ?? '';
$comentario = $_POST['comentario'] ?? '';
$lineasInstalar = $_POST['lineas_instalar'] ?? '';
$lineasReparar = $_POST['lineas_reparar'] ?? '';
$prioridad = $_POST['prioridad'] ?? '';

$tipoServicio = $_POST['tipo_servicio'] ?? [];
$otroTipo = $_POST['otro_tipo'] ?? '';
$cantidadServicio = $_POST['cantidad_servicio'] ?? '';

$materiales = $_POST['materiales'] ?? [];
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

// === Logo en base64 ===
$logoBase64 = base64_encode(file_get_contents('img/just-logo.png'));

// === Armar HTML del PDF ===
$html = '
<style>
  body { font-family: Arial, sans-serif; font-size: 13px; color: #333; }
  .header { text-align: center; }
  .header img { width: 120px; margin-bottom: 5px; }
  h1 { background: #005baa; color: #fff; font-size: 18px; padding: 8px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  td, th { padding: 8px; border: 1px solid #ccc; vertical-align: top; }
  .label { background-color: #f0f0f0; font-weight: bold; width: 30%; }
</style>

<div class="header">
  <!--<img src="data:image/png;base64,' . $logoBase64 . '" alt="Logo">-->
  <h2>Solicitud Técnica de Instalación o Reparación</h2>
  <p><strong>Fecha:</strong> ' . $fecha . '</p>
</div>

<h1>Datos del solicitante</h1>
<table>
  <tr><td class="label">Sucursal</td><td>' . $sucursal . '</td></tr>
  <tr><td class="label">Piso / Área</td><td>' . $piso . '</td></tr>
  <tr><td class="label">Persona contacto</td><td>' . $contacto . '</td></tr>
</table>

<h1>Tipo de servicio</h1>
<table>
  <tr><td class="label">Servicios seleccionados</td><td>' . implode(', ', $tipoServicio) . '</td></tr>';

if (!empty($otroTipo)) {
  $html .= '<tr><td class="label">Otro (descripción)</td><td>' . $otroTipo . '</td></tr>';
}

if (!empty($cantidadServicio)) {
  $html .= '<tr><td class="label">Cantidad de puntos requeridos</td><td>' . $cantidadServicio . '</td></tr>';
}

$html .= '
  <tr><td class="label">Líneas a instalar</td><td>' . $lineasInstalar . '</td></tr>
  <tr><td class="label">Líneas a reparar</td><td>' . $lineasReparar . '</td></tr>
</table>

<h1>Materiales requeridos</h1>
<table>
  <tr><td class="label">Materiales</td><td>' . implode(', ', $materiales) . '</td></tr>';

if (!empty($faceplate)) {
  $html .= '<tr><td class="label">Faceplate (bocas)</td><td>' . $faceplate . '</td></tr>';
}
if (!empty($faceplateCantidad)) {
  $html .= '<tr><td class="label">Cantidad de faceplates</td><td>' . $faceplateCantidad . '</td></tr>';
}

$html .= '</table>

<h1>Canalización y montaje</h1>
<table>
  <tr><td class="label">Elementos seleccionados</td><td>' . implode(', ', $canalizacion) . '</td></tr>';

if (!empty($tamanoCanaleta)) {
  $html .= '<tr><td class="label">Tamaño canaleta</td><td>' . $tamanoCanaleta . ' mm</td></tr>';
}
if (!empty($diametroEMT)) {
  $html .= '<tr><td class="label">Diámetro EMT</td><td>' . $diametroEMT . ' mm</td></tr>';
}
if (!empty($cantidadBandeja)) {
  $html .= '<tr><td class="label">Cantidad de bandejas</td><td>' . $cantidadBandeja . '</td></tr>';
}
if (!empty($gabineteTipo)) {
  $html .= '<tr><td class="label">Gabinete Tipo</td><td>' . $gabineteTipo . '</td></tr>';
}
if (!empty($gabineteTamano)) {
  $html .= '<tr><td class="label">Gabinete Tamaño</td><td>' . $gabineteTamano . '</td></tr>';
}

$html .= '</table>

<h1>Equipos activos</h1>
<table>
  <tr><td class="label">Equipos</td><td>' . implode(', ', $equipos) . '</td></tr>';

if (!empty($cantidadPDU)) {
  $html .= '<tr><td class="label">Cantidad de PDU</td><td>' . $cantidadPDU . '</td></tr>';
}
if (!empty($equipoOtro)) {
  $html .= '<tr><td class="label">Otros equipos</td><td>' . $equipoOtro . '</td></tr>';
}

$html .= '</table>

<h1>Observaciones</h1>
<table>
  <tr><td class="label">Comentario</td><td>' . $comentario . '</td></tr>
  <tr><td class="label">Prioridad</td><td>' . $prioridad . '</td></tr>
</table>

<h1>Requisitos de seguridad</h1>
<table>
  <tr><td class="label">Autorizaciones especiales</td><td>' . (!empty($seguridad) ? implode(', ', $seguridad) : 'No especificadas') . '</td></tr>
</table>
';

// === Generar PDF ===
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$nombrePDF = 'solicitud_' . time() . '.pdf';
$rutaPDF = $pdfsDir . $nombrePDF;
file_put_contents($rutaPDF, $dompdf->output());

// === Enviar correo ===
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
    $mail->addAddress($config['smtp_to'], 'Departamento Técnico');
    $mail->isHTML(true);
    $mail->Subject = 'Nueva solicitud de instalación/reparación de red';

    $mail->Body = "Se ha recibido una nueva solicitud técnica.<br>Se adjunta el PDF con los detalles.";
    $mail->addAttachment($rutaPDF);
    $mail->send();

    echo "<script>alert('Solicitud enviada correctamente'); window.location='index.html';</script>";
} catch (Exception $e) {
    echo "Error al enviar el correo: {$mail->ErrorInfo}";
}

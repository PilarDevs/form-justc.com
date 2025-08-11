<?php
require_once 'protegido/config.php';
header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    http_response_code(400);
    exit('Solicitud inválida');
}

$id = intval($_POST['id']);

try {
    $sql = "UPDATE solicitudes SET estatus = 'pendiente', fecha_cierre_original = NULL WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    echo 'Solicitud reabierta correctamente';
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Error al reabrir la solicitud: ' . $e->getMessage();
}

<?php
require_once 'protegido/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $estatus = $_POST['estatus'] ?? null;

    if ($id && in_array($estatus, ['pendiente', 'cerrada', 'completada'])) {
        $stmt = $pdo->prepare("UPDATE solicitudes SET estatus = ? WHERE id = ?");
        if ($stmt->execute([$estatus, $id])) {
            echo "Estado actualizado correctamente.";
        } else {
            echo "Error al actualizar.";
        }
    } else {
        echo "Datos inválidos.";
    }
}

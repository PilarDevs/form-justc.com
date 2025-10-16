<?php
require '../protegido/config.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    die('ID de usuario inválido.');
}

$stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
if ($stmt->execute([$id])) {
    header('Location: ../ver-usuarios.php?msg=eliminado');
    exit;
} else {
    echo 'Error al eliminar usuario.';
}

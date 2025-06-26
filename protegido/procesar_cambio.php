<?php
session_start();
require 'config.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$nuevo_usuario = trim($_POST['nuevo_usuario'] ?? '');
$nueva_pass = trim($_POST['nueva_pass'] ?? '');

if (!$nuevo_usuario || !$nueva_pass) {
    die("Todos los campos son obligatorios.");
}

if (strlen($nueva_pass) < 8) {
    die("La contraseña debe tener al menos 8 caracteres.");
}

// Hashear la nueva contraseña
$pass_hash = password_hash($nueva_pass, PASSWORD_DEFAULT);

// Actualizar en base de datos
$stmt = $pdo->prepare("UPDATE usuarios SET usuario = :usuario, contrasena = :contrasena, credenciales_actualizadas = 1 WHERE id = :id");
$stmt->execute([
    ':usuario' => $nuevo_usuario,
    ':contrasena' => $pass_hash,
    ':id' => $id_usuario
]);

// Actualizar en sesión
$_SESSION['usuario'] = $nuevo_usuario;
$_SESSION['credenciales_actualizadas'] = 1;

// Redirigir al dashboard
//header("Location: cambios_credenciales.php");
exit;

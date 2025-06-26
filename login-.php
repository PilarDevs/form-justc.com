<?php
session_start();
require 'protegido/config.php';

//require 'protegido/capchat.env/Apis.php';

$usuario = $_POST['usuario'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';

if (empty($usuario) || empty($contrasena)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'mensaje' => 'Debe completar todos los campos']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, nombre, usuario, contrasena, tipo, credenciales_actualizadas FROM usuarios WHERE usuario = ?");
$stmt->execute([$usuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($contrasena, $user['contrasena'])) {
    // Guardar sesión
    $_SESSION['id_usuario'] = $user['id'];
    $_SESSION['usuario'] = $user['usuario'];
    $_SESSION['nombre'] = $user['nombre'];
    $_SESSION['tipo'] = $user['tipo'];
    $_SESSION['credenciales_actualizadas'] = $user['credenciales_actualizadas'];

    header('Content-Type: application/json');

    // Validar si debe cambiar credenciales
    if ((int)$user['credenciales_actualizadas'] === 0) {
        echo json_encode([
            'status' => 'redirect',
            'mensaje' => 'Debe cambiar sus credenciales',
            'redirect' => 'protegido/cambios_credenciales.php'
        ]);
    } else {
        echo json_encode([  
            'status' => 'ok',
            'id' => $user['id'],
            'usuario' => $user['usuario'],
            'nombre' => $user['nombre'],
            'tipo' => $user['tipo']
        ]);
    }
} else {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'mensaje' => 'Usuario o contraseña incorrectos']);
    exit;
}

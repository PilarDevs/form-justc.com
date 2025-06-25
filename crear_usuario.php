<?php
require 'protegido/config.php';

$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$usuario = $_POST['usuario'];
$contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, usuario, contrasena) VALUES (?, ?, ?, ?)");
$stmt->execute([$nombre, $correo, $usuario, $contrasena]);

echo "<p>Usuario creado correctamente. <a href='agregar-usuario.php'>Agregar otro</a></p>";

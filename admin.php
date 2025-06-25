<?php

exit('⚠️ Acceso deshabilitado.');


require 'protegido/config.php';

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $usuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    if ($nombre && $correo && $usuario && $contrasena) {
        // Hashear contraseña
        $hash = password_hash($contrasena, PASSWORD_BCRYPT);

        // Insertar en base de datos con tipo "admin"
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, usuario, contrasena, tipo) VALUES (?, ?, ?, ?, 'admin')");
        $exito = $stmt->execute([$nombre, $correo, $usuario, $hash]);

        $mensaje = $exito ? 'Administrador creado correctamente ✅' : 'Error al crear administrador ❌';
    } else {
        $mensaje = 'Todos los campos son obligatorios ⚠️';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear Administrador</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      padding: 40px;
    }
    .form-container {
      max-width: 400px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-top: 10px;
    }
    input[type="submit"] {
      margin-top: 20px;
      width: 100%;
      padding: 10px;
      background-color: #28a745;
      color: #fff;
      border: none;
    }
    .msg {
      margin-top: 15px;
      text-align: center;
      color: #333;
    }
  </style>
</head>
<body>

<div class="form-container">
  <h2>Crear Usuario Admin</h2>
  <form method="POST">
    <input type="text" name="nombre" placeholder="Nombre completo" required>
    <input type="email" name="correo" placeholder="Correo electrónico" required>
    <input type="text" name="usuario" placeholder="Usuario" required>
    <input type="password" name="contrasena" placeholder="Contraseña" required>
    <input type="submit" value="Crear Administrador">
  </form>

  <?php if ($mensaje): ?>
    <div class="msg"><?= htmlspecialchars($mensaje) ?></div>
  <?php endif; ?>
</div>

</body>
</html>

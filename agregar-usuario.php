<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Usuario</title>
</head>
<body>
  <h2>Agregar nuevo usuario</h2>
  <form action="crear_usuario.php" method="POST">
    <label>Nombre:</label><br>
    <input type="text" name="nombre" required><br><br>
    <label>Correo:</label><br>
    <input type="email" name="correo" required><br><br>
    <label>Usuario:</label><br>
    <input type="text" name="usuario" required><br><br>
    <label>Contraseña:</label><br>
    <input type="password" name="contrasena" required><br><br>
    <input type="submit" value="Crear usuario">
  </form>
</body>
</html>

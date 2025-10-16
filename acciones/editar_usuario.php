<?php
require '../protegido/config.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    die('ID de usuario inválido.');
}

// Obtener datos actuales
$stmt = $pdo->prepare('SELECT nombre, usuario, correo FROM usuarios WHERE id = ?');
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$usuario) {
    die('Usuario no encontrado.');
}

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $user = trim($_POST['usuario'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($nombre && $user && $correo) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $update = $pdo->prepare('UPDATE usuarios SET nombre=?, usuario=?, correo=?, contrasena=? WHERE id=?');
            $ok = $update->execute([$nombre, $user, $correo, $hash, $id]);
        } else {
            $update = $pdo->prepare('UPDATE usuarios SET nombre=?, usuario=?, correo=? WHERE id=?');
            $ok = $update->execute([$nombre, $user, $correo, $id]);
        }
        if ($ok) {
            $mensaje = 'Usuario actualizado correctamente.';
            $usuario = ['nombre' => $nombre, 'usuario' => $user, 'correo' => $correo];
        } else {
            $mensaje = 'Error al actualizar usuario.';
        }
    } else {
        $mensaje = 'Todos los campos son obligatorios.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <style>
        body {
            font-family: Arial;
            background: #f4f7fa;
            margin: 0;
            padding: 40px;
        }

        .form-edit {
            background: #fff;
            max-width: 400px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 2px 12px #bbb;
            padding: 32px;
        }

        h2 {
            color: #005792;
            text-align: center;
        }

        label {
            display: block;
            margin-top: 18px;
            color: #333;
        }

        input[type=text],
        input[type=email] {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-top: 6px;
        }

        button {
            margin-top: 24px;
            padding: 10px 24px;
            background: #005792;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1.1em;
            cursor: pointer;
        }

        button:hover {
            background: #003b73;
        }

        .mensaje {
            margin-top: 18px;
            color: green;
            text-align: center;
        }

        .error {
            color: red;
        }

        a {
            display: block;
            margin-top: 24px;
            text-align: center;
            color: #005792;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <form class="form-edit" method="POST">
        <h2>Editar Usuario</h2>
        <?php if ($mensaje): ?>
            <div class="mensaje <?= strpos(strtolower($mensaje), 'error') !== false ? 'error' : '' ?>"> <?= $mensaje ?> </div>
        <?php endif; ?>
        <label>Nombre:
            <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
        </label>
        <label>Usuario:
            <input type="text" name="usuario" value="<?= htmlspecialchars($usuario['usuario']) ?>" required>
        </label>
        <label>Correo:
            <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
        </label>
        <label>Nueva contraseña:
            <input type="password" name="password" placeholder="Dejar en blanco para no cambiar" style="width:100%;padding:10px;border-radius:6px;border:1px solid #ccc;margin-top:6px;">
        </label>
        <button type="submit">Guardar Cambios</button>
        <a href="../ver-usuarios.php">Volver a usuarios</a>
    </form>
</body>

</html>
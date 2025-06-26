<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['credenciales_actualizadas'] == 1) {
    header("Location: ../form-tecnico.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cambiar credenciales</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f5f5f5;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .form-container {
      background: #fff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      max-width: 400px;
      width: 100%;
    }

    h2 {
      text-align: center;
      margin-bottom: 1.5rem;
    }

    label {
      font-weight: bold;
      display: block;
      margin: 10px 0 5px;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 0.7rem;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .checkbox-group {
      margin-top: 1rem;
    }

    input[type="submit"] {
      width: 100%;
      padding: 0.9rem;
      background: #007bff;
      color: white;
      border: none;
      margin-top: 1rem;
      border-radius: 5px;
      font-size: 1rem;
      cursor: pointer;
    }

    input[type="submit"]:disabled {
      background: #aaa;
      cursor: not-allowed;
    }

    .warning {
      margin-top: 10px;
      color: #c0392b;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>

<div class="form-container">
  <h2>Actualiza tus credenciales</h2>
  <form action="procesar_cambio.php" method="post">
    <label for="nuevo_usuario">Nuevo usuario</label>
    <input type="text" id="nuevo_usuario" name="nuevo_usuario" required>

    <label for="nueva_pass">Nueva contraseña</label>
    <input type="password" id="nueva_pass" name="nueva_pass" required minlength="8">

    <div class="warning">
      ⚠️ Este cambio es único. Asegúrate de recordar tu nueva contraseña.
    </div>

    <div class="checkbox-group">
      <input type="checkbox" id="confirm_check">
      <label for="confirm_check">Confirmo que he elegido una contraseña segura</label>
    </div>

    <input type="submit" value="Guardar cambios" id="submit_btn" disabled>
  </form>
</div>

<script>
  const checkbox = document.getElementById('confirm_check');
  const submitBtn = document.getElementById('submit_btn');

  checkbox.addEventListener('change', () => {
    submitBtn.disabled = !checkbox.checked;
  });
</script>

</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar Sesión</title>
  <style>
    body { font-family: Arial; background: #f2f2f2; }
    .login-container {
      width: 300px;
      margin: 100px auto;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0px 0px 10px #aaa;
    }
    input[type="text"], input[type="password"] {
      width: 100%; padding: 10px; margin-top: 10px;
    }
    input[type="submit"] {
      width: 100%; padding: 10px; margin-top: 20px;
      background-color: #007bff; color: #fff; border: none;
    }
    .error { color: red; margin-top: 10px; }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Iniciar Sesión</h2>
    <form id="loginForm">
  <input type="text" id="usuario" name="usuario" required placeholder="Usuario">
  <input type="password" id="contrasena" name="contrasena" required placeholder="Contraseña">
  <input type="submit" value="Iniciar sesión">
</form>

<script>
document.getElementById('loginForm').addEventListener('submit', async function (e) {
  e.preventDefault();

  const formData = new FormData(this);
  const data = new URLSearchParams(formData);

  const res = await fetch('login-.php', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: data
  });

  const json = await res.json();
  if (json.status === 'ok') {
    // Guardar sesión local
    localStorage.setItem('usuario', json.usuario);
    localStorage.setItem('nombre', json.nombre);
    localStorage.setItem('id_usuario', json.id);
    localStorage.setItem('tipo', json.tipo);

    // Redirigir según tipo
    if (json.tipo === 'admin') {
      window.location.href = 'panel.php';
    } else {
      window.location.href = 'form-tecnico.php';
    }
  } else {
    alert(json.mensaje);
  }
});
</script>


  </div>
</body>
</html>

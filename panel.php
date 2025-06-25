<?php
session_start();

// Verificar sesión iniciada
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

// Redirigir si no es admin
if ($_SESSION['tipo'] !== 'admin') {
    header("Location: form-tecnico.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panel de control</title>
  <link rel="icon" href="img/just-logo.png" type="image/png" />
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #eef2f7;
      margin: 0;
      padding: 0;
    }

    .panel-container {
      max-width: 1000px;
      margin: auto;
      background-color: white;
      padding: 30px 20px;
      margin-top: 40px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.08);
      text-align: center;
    }

    .panel-container img {
      max-width: 150px;
      margin-bottom: 20px;
    }

    h1 {
      color: #003b73;
      margin-bottom: 10px;
    }

    h2 {
      color: #333;
      margin-top: 10px;
      font-size: 1.2rem;
    }

    .button-group {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 15px;
      margin-top: 25px;
    }

    .button-group button {
      padding: 12px 20px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .button-group button:hover {
      background-color: #0056b3;
    }

    #contenedor-iframe {
      margin-top: 30px;
    }

    iframe {
      width: 100%;
      height: 500px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    @media (max-width: 768px) {
      .button-group {
        flex-direction: column;
        gap: 10px;
      }

      iframe {
        height: 400px;
      }
    }
  </style>
</head>
<body>

<div class="panel-container">
  <img src="img/just-logo.png" alt="Logo Justech">
  <h1>Panel de Control</h1>
  <h2>Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?>.</h2>

  <div class="button-group">
    <button onclick="cargarContenido('ver-solicitudes.php')">Ver solicitudes</button>
    <button onclick="cargarContenido('ver-usuarios.php')">Ver usuarios</button>
    <button onclick="cargarContenido('agregar-usuario.php')">Agregar usuario</button>
    <button onclick="window.location.href='form-tecnico.php'">Ir a página</button>
    <button onclick="window.location.href='logout.php'">Cerrar sesión</button>
  </div>

  <div class="oculto" id="contenedor-iframe">
    <iframe id="panel-frame" src=""></iframe>
  </div>
</div>

<script>
  const usuario = localStorage.getItem('usuario');
  const nombre = localStorage.getItem('nombre');

  if (!usuario || !nombre) {
    window.location.href = "logout.php";
  }

  function cargarContenido(ruta) {
    document.getElementById('panel-frame').src = ruta;
    document.getElementById('contenedor-iframe').classList.remove('oculto');
  }
</script>

</body>
</html>

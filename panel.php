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

<script>
  const usuario = localStorage.getItem('usuario');
  const nombre = localStorage.getItem('nombre');

  if (!usuario || !nombre) {
    // limpiar sesión y redirigir si localStorage no está
    window.location.href = "logout.php";
  }
</script>

<h2>Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?>.</h2>



<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panel de control</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f5f5f5;
      padding: 20px;
      text-align: center;
    }
    .btn button {
      margin: 10px;
      padding: 10px 20px;
      background-color: #007bff;
      color: white;
      border: none;
      cursor: pointer;
    }
    .btn button:hover {
      background-color: #0056b3;
    }
    iframe {
      width: 100%;
      height: 500px;
      border: 1px solid #ccc;
      margin-top: 20px;
    }
    .oculto {
      display: none;
    }
    .img {
      max-height: 60px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

<img src="img/just-logo.png" alt="Logo Justech" class="img">
<h1>Panel de control</h1>

<div class="btn">
  <button onclick="cargarContenido('ver-solicitudes.php')">Ver solicitudes</button>
  <button onclick="cargarContenido('ver-usuarios.php')">Ver usuarios</button>
  <button onclick="cargarContenido('agregar-usuario.php')">Agregar usuario</button>
  <button onclick="window.location.href='form-tecnico.php'">Ir a página</button>
  <button onclick="window.location.href='logout.php'">Cerrar sesion</button>

  <div class="oculto" id="contenedor-iframe">
    <iframe id="panel-frame" src=""></iframe>
  </div>
</div>

<script>
function cargarContenido(ruta) {
  document.getElementById('panel-frame').src = ruta;
  document.getElementById('contenedor-iframe').classList.remove('oculto');
}
</script>

</body>
</html>

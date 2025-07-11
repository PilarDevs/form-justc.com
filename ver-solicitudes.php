<?php
session_start();
require_once 'protegido/config.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'admin') {
  header("Location: login.php");
  exit;
}

$sql = "SELECT s.*, u.nombre 
        FROM solicitudes s
        JOIN usuarios u ON s.id_usuario = u.id
        ORDER BY s.id DESC";

$stmt = $pdo->query($sql);
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Ver Solicitudes</title>
  <style>
    body {
      font-family: Arial;
      padding: 30px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 30px;
    }

    th,
    td {
      padding: 12px;
      border: 1px solid #ccc;
      text-align: center;
    }

    th {
      background-color: #005792;
      color: white;
    }

    select,
    button {
      padding: 6px 8px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    .btn-guardar {
      background-color: #28a745;
      color: white;
      border: none;
      cursor: pointer;
    }

    .btn-pdf {
      background-color: #dc3545;
      color: white;
      border: none;
      cursor: pointer;
    }

    .btn-editar {
      background-color: #007bff;
      color: white;
      padding: 6px 10px;
      border-radius: 5px;
      text-decoration: none;
      display: inline-block;
    }

    .btn-editar:hover {
      background-color: #0056b3;
    }

    .btn-guardar:hover {
      background-color: #218838;
    }

    .btn-pdf:hover {
      background-color: #c82333;
    }

    @media (max-width: 768px) {
      table {
        font-size: 14px;
      }
    }
  </style>
</head>

<body>

  <h2>Lista de Solicitudes</h2>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Usuario</th>
        <th>Fecha</th>
        <th>Estado</th>
        <th>Actualizar estado</th>
        <th>Editar</th>
        <th>Asignar técnico</th>
        <th>Cerrar ticket</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($solicitudes as $s): ?>
        <tr>
          <td><?= $s['id'] ?></td>
          <td><?= htmlspecialchars($s['nombre']) ?></td>
          <td><?= date('d/m/Y', strtotime($s['fecha_envio'])) ?></td>
          <td>
            <select id="estatus-<?= $s['id'] ?>">
              <option value="pendiente" <?= $s['estatus'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
              <option value="completada" <?= $s['estatus'] === 'completada' || $s['estatus'] === 'cerrado' ? 'selected' : '' ?>>Completada</option>
            </select>
          </td>
          <td>
            <button class="btn-guardar" onclick="actualizarEstatus(<?= $s['id'] ?>)">Guardar</button>
          </td>
          <td>
            <a class="btn-editar" href="editar-solicitud.php?id=<?= urlencode($s['id']) ?>">Editar</a>
          </td>
          <td>
            <a class="btn-editar" href="asignar_tecnico.php?id=<?= $s['id'] ?>">Asignar</a>
          </td>
          <td>
            <a class="btn-editar" href="adjuntar_evidencia.php?id=<?= $s['id'] ?>">Cerrar</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <script>
    function actualizarEstatus(id) {
      const nuevoEstatus = document.getElementById(`estatus-${id}`).value;

      fetch('actualizar-estatus.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `id=${id}&estatus=${nuevoEstatus}`
        })
        .then(res => res.text())
        .then(data => {
          alert(data.trim());
        })
        .catch(err => {
          alert("Error al actualizar estado");
          console.error(err);
        });
    }
  </script>

</body>

</html>
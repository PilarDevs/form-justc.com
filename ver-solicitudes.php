<?php
session_start();
require_once 'protegido/config.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'admin') {
  header("Location: login.php");
  exit;
}

// Obtener todas las solicitudes
$sql = "SELECT s.*, u.nombre 
        FROM solicitudes s
        JOIN usuarios u ON s.id_usuario = u.id
        ORDER BY s.id DESC";
$stmt = $pdo->query($sql);
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener técnicos asignados para cada solicitud (optimizado en un solo query)
$tecnicos_por_solicitud = [];
try {
    $ids = array_column($solicitudes, 'id');
    if (count($ids) > 0) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmtTec = $pdo->prepare("SELECT id_solicitud FROM solicitud_tecnicos WHERE id_solicitud IN ($in)");
        $stmtTec->execute($ids);
        foreach ($stmtTec->fetchAll(PDO::FETCH_COLUMN) as $id_sol) {
            $tecnicos_por_solicitud[$id_sol] = true;
        }
    }
} catch (Exception $e) {
    $tecnicos_por_solicitud = [];
}
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

    .btn-pdf:disabled {
      background-color: #aaa;
      cursor: not-allowed;
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

    .btn-pdf:hover:not(:disabled) {
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
        <th>Ver PDF</th>
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
            <?php if (!empty($tecnicos_por_solicitud[$s['id']])): ?>
              <a class="btn-editar" style="background:#888;" href="asignar_tecnico.php?id=<?= $s['id'] ?>">Asignado</a>
            <?php else: ?>
              <a class="btn-editar" href="asignar_tecnico.php?id=<?= $s['id'] ?>">Asignar</a>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($s['estatus'] === 'cerrado'): ?>
              <a class="btn-editar" style="background:#888;cursor:default;pointer-events:none;">Cerrado</a>
            <?php else: ?>
              <a class="btn-editar" href="adjuntar_evidencia.php?id=<?= $s['id'] ?>">Cerrar</a>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!empty($s['url_pdf']) && file_exists(__DIR__ . '/pdfs/' . $s['url_pdf'])): ?>
              <button class="btn-pdf" onclick="window.open('pdfs/<?= urlencode($s['url_pdf']) ?>', '_blank')">Abrir PDF</button>
            <?php else: ?>
              <button class="btn-pdf" disabled>No disponible</button>
            <?php endif; ?>
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
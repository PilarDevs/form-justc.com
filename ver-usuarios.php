<?php
require 'protegido/config.php';
$stmt = $pdo->query("SELECT id, nombre, usuario, correo FROM usuarios ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Usuarios Registrados</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f4f7fa;
      margin: 0;
      padding: 20px;
    }

    .acciones-usuario a {
      transition: color 0.2s, background 0.2s;
      padding: 5px 12px;
      border-radius: 5px;
      margin-right: 6px;
      font-weight: bold;
      text-decoration: none;
      display: inline-block;
    }

    .acciones-usuario a:hover {
      background: #eaf6ff;
      color: #003b73 !important;
      box-shadow: 0 2px 8px #b3d7f7;
    }

    h2 {
      text-align: center;
      color: #003b73;
      margin-bottom: 30px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background-color: #fff;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
      overflow: hidden;
    }

    th,
    td {
      padding: 14px 20px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #005792;
      color: white;
    }

    tr:hover {
      background-color: #f1f1f1;
    }

    @media (max-width: 768px) {

      table,
      thead,
      tbody,
      th,
      td,
      tr {
        display: block;
      }

      th {
        display: none;
      }

      td {
        position: relative;
        padding-left: 50%;
        border-bottom: 1px solid #eee;
      }

      td::before {
        content: attr(data-label);
        position: absolute;
        left: 16px;
        top: 14px;
        font-weight: bold;
        color: #555;
      }

      tr {
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 6px;
        overflow: hidden;
      }
    }
  </style>
</head>

<body>

  <h2>Usuarios Registrados</h2>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Usuario</th>
        <th>Correo</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
        <tr>
          <td data-label="ID"><?= htmlspecialchars($row['id']) ?></td>
          <td data-label="Nombre"><?= htmlspecialchars($row['nombre']) ?></td>
          <td data-label="Usuario"><?= htmlspecialchars($row['usuario']) ?></td>
          <td data-label="Correo"><?= htmlspecialchars($row['correo']) ?></td>
          <td data-label="Acciones" class="acciones-usuario">
            <a href="acciones/editar_usuario.php?id=<?= $row['id'] ?>" style="color:#005792;">Editar</a>
            <a href="acciones/eliminar_usuario.php?id=<?= $row['id'] ?>" style="color:#c00;" onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">Eliminar</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

</body>

</html>
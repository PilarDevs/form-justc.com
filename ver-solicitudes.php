<?php
session_start();
require_once 'protegido/config.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'admin') {
  header("Location: login.php");
  exit;
}

// Obtener todas las solicitudes
$sql = "SELECT s.*, u.nombre, d.sucursal 
  FROM solicitudes s
  JOIN usuarios u ON s.id_usuario = u.id
  LEFT JOIN detalles_solicitudes d ON s.id = d.id_solicitud
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
      margin-left: 0;
      margin-right: 0;
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
    <!-- Bscador general -->
  <input type="search" id="buscador" placeholder="Buscar..." style="width:350px;padding:10px 16px;margin-bottom:16px;border-radius:8px;border:1px solid #005792;font-size:16px;">

  <table id="tabla-solicitudes">
    <thead>
      <tr>
        <th>ID</th>
        <th>Usuario</th>
        <th>Fecha</th>
        <th>Sucursal</th>
        <th>Estado</th>
        <th>Actualizar estado</th>
        <th>Editar</th>
        <th>Asignar técnico</th>
        <th>Cerrar ticket</th>
        <th>Ver PDF</th>
        <th>SLA</th>
        <th>Reabrir</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($solicitudes as $s): ?>
        <tr>
          <td><?= $s['id'] ?></td>
          <td><?= htmlspecialchars($s['nombre']) ?></td>
          <td><?= date('d/m/Y', strtotime($s['fecha_envio'])) ?></td>
          <td><?= htmlspecialchars($s['sucursal'] ?? '-') ?></td>
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
          <?php
            $hoy = new DateTime();
            $emision = new DateTime($s['fecha_envio']);
            $emision->modify('+1 day'); // El conteo inicia al día siguiente
            $limite = clone $emision;
            $limite->modify('+4 days'); // Sumamos 4 días para total de 4 días laborables
            // Si la emisión es sábado (6) o domingo (7), empezar a contar desde el lunes siguiente
            $dia_emision = $emision->format('N');
            if ($dia_emision == 6) {
              $emision->modify('next monday');
              $limite = clone $emision;
              $limite->modify('+4 days');
            } else if ($dia_emision == 7) {
              $emision->modify('next monday');
              $limite = clone $emision;
              $limite->modify('+3 days');
            }
            // Calcular días laborables (lunes a viernes, ignorando jueves y domingo)
            $dias_laborales = 0;
            $temp = clone $hoy;
            while ($temp < $limite) {
              $dia_semana = $temp->format('N'); // 1=lunes ... 7=domingo
              if ($dia_semana != 4 && $dia_semana != 7) {
                $dias_laborales++;
              }
              $temp->modify('+1 day');
            }
            $dias_restantes = $dias_laborales;
            if (($s['estatus'] === 'cerrado' || $s['estatus'] === 'completada') && $hoy <= $limite) {
              $sla = 'Cerrada con tiempo';
            } else if ($dias_laborales <= 0 && ($s['estatus'] !== 'cerrado' && $s['estatus'] !== 'completada')) {
              $sla = 'Fuera de tiempo';
            } else if (($s['estatus'] !== 'cerrado' && $s['estatus'] !== 'completada') && $dias_laborales > 0) {
              $sla = 'En tiempo - ' . $dias_laborales . ' días laborables';
            } else if (($s['estatus'] === 'cerrado' || $s['estatus'] === 'completada') && $dias_laborales <= 0) {
              $sla = 'Cerrada fuera de tiempo';
            } else {
              $sla = '';
            }
          ?>
          <?php
            $slaColor = '';
            if (strpos($sla, 'En tiempo') !== false) {
              $slaColor = 'background:#d4edda;color:#155724;font-weight:bold;'; // verde
            } else if (strpos($sla, 'Fuera de tiempo') !== false) {
              $slaColor = 'background:#f8d7da;color:#721c24;font-weight:bold;'; // rojo
            } else if (strpos($sla, 'Cerrada con tiempo') !== false) {
              $slaColor = 'background:#cce5ff;color:#004085;font-weight:bold;'; // azul
            } else if (strpos($sla, 'Cerrada fuera de tiempo') !== false) {
              $slaColor = 'background:#e2e3e5;color:#6c757d;font-weight:bold;'; // gris
            }
          ?>
          <td style="<?= $slaColor ?>"><?= $sla ?></td>
          <td>
            <?php if ($s['estatus'] === 'cerrado'): ?>
              <button class="btn-reabrir" onclick="reabrirSolicitud(<?= $s['id'] ?>)">Reabrir</button>
            <?php else: ?>
              <span style="color:gray;">-</span>
            <?php endif; ?>
          </td>
        </tr>
  <script>
  function reabrirSolicitud(id) {
    if(confirm('¿Seguro que deseas reabrir esta solicitud?')) {
      fetch('reabrir_solicitud.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id='+id
      })
      .then(r => r.text())
      .then(resp => {
        alert(resp);
        location.reload();
      });
    }
  }
  </script>
      <?php endforeach; ?>
    </tbody>
  </table>

  <script>
    // Buscador en tiempo real por ID, usuario, fecha, estado, cerrado
    document.getElementById('buscador').addEventListener('input', function() {
      const filtro = this.value.toLowerCase();
      const filas = document.querySelectorAll('#tabla-solicitudes tbody tr');
      // Meses en español para búsqueda
      const meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
      let mesBuscado = null;
      let numMes = null;
      meses.forEach((mes, i) => {
        if (filtro === mes) {
          mesBuscado = mes;
          numMes = (i+1).toString().padStart(2,'0');
        }
      });
      // Si el filtro es un número de mes (01-12)
      if (!mesBuscado && /^\d{2}$/.test(filtro) && parseInt(filtro) >= 1 && parseInt(filtro) <= 12) {
        numMes = filtro;
      }
      filas.forEach(fila => {
        let texto = fila.textContent.toLowerCase();
        let mostrar = false;
        if (numMes) {
          // Buscar en la columna de fecha
          let fechaTd = fila.querySelector('td:nth-child(3)');
          if (fechaTd) {
            let fecha = fechaTd.textContent.trim();
            let partes = fecha.split('/'); // formato dd/mm/yyyy
            if (partes.length === 3 && partes[1] === numMes) {
              mostrar = true;
            }
          }
        }
        // Si no es búsqueda por mes, buscar por texto normal
        if (!numMes && texto.includes(filtro)) {
          mostrar = true;
        }
        fila.style.display = mostrar ? '' : 'none';
      });
    });

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
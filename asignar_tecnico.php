<?php
session_start();
require_once 'protegido/config.php';

// Lógica para remover técnicos asignados si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remover_tecnicos'], $_POST['id_solicitud'])) {
    $id_solicitud_post = $_POST['id_solicitud'];
    $stmt = $pdo->prepare("DELETE FROM solicitud_tecnicos WHERE id_solicitud = ?");
    $stmt->execute([$id_solicitud_post]);
    // Redirigir para recargar la página y mostrar el formulario de asignación
    header("Location: asignar_tecnico.php?id=" . urlencode($id_solicitud_post));
    exit;
}

if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id_solicitud = $_GET['id'] ?? null;
if (!$id_solicitud) {
    echo "ID inválido.";
    exit;
}

// Obtener técnicos guardados
$tecnicos_guardados = [];
try {
    $stmt = $pdo->query("SELECT nombre FROM tecnicos ORDER BY nombre ASC");
    $tecnicos_guardados = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $tecnicos_guardados = [];
}

// Obtener técnicos ya asignados a la solicitud
$tecnicos_asignados = [];
try {
    $stmt = $pdo->prepare("SELECT nombre_tecnico FROM solicitud_tecnicos WHERE id_solicitud = ?");
    $stmt->execute([$id_solicitud]);
    $tecnicos_asignados = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $tecnicos_asignados = [];
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Asignar Técnicos</title>
    <style>
        body {
            font-family: Arial;
            margin: 40px;
        }

        .campo-tecnico {
            display: flex;
            margin-bottom: 10px;
        }

        .campo-tecnico input {
            flex: 1;
            padding: 8px;
            margin-right: 10px;
        }

        .campo-tecnico button {
            padding: 8px 12px;
            cursor: pointer;
        }

        button.enviar {
            background-color: #005792;
            color: white;
            padding: 10px 20px;
            border: none;
            margin-top: 20px;
            cursor: pointer;
        }

        /* Pantalla de carga */
        #pantalla-carga {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            color: white;
            font-size: 24px;
            text-align: center;
            padding-top: 20vh;
            user-select: none;
        }
    </style>
</head>

<body>


    <h2>Asignar técnico(s) a la solicitud #<?= htmlspecialchars($id_solicitud) ?></h2>

    <?php if (count($tecnicos_asignados) > 0): ?>
        <h3>Técnicos asignados</h3>
        <table border="1" cellpadding="8" style="margin-bottom:20px;">
            <tr><th>Nombre del técnico</th></tr>
            <?php foreach ($tecnicos_asignados as $tec): ?>
                <tr><td><?= htmlspecialchars($tec) ?></td></tr>
            <?php endforeach; ?>
        </table>
        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas remover todos los técnicos asignados?');">
            <input type="hidden" name="remover_tecnicos" value="1">
            <input type="hidden" name="id_solicitud" value="<?= $id_solicitud ?>">
            <button type="submit" class="enviar" style="background:#d9534f;">Remover técnicos</button>
        </form>
        <button type="button" class="enviar" disabled style="background:#888;cursor:default;">Técnicos</button>
    <?php else: ?>
        <form method="POST" action="enviarMail/tecnicoMail.php" id="form-tecnicos">
            <div id="tecnicos">
                <div class="campo-tecnico">
                    <select name="tecnicos[]">
                        <option value="">-- Selecciona un técnico --</option>
                        <?php foreach ($tecnicos_guardados as $tec): ?>
                            <option value="<?= htmlspecialchars($tec) ?>"><?= htmlspecialchars($tec) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="nuevo_tecnico[]" placeholder="Nuevo técnico (opcional)" autocomplete="off">
                    <button type="button" onclick="agregarTecnico()">+</button>
                </div>
            </div>
            <div style="margin-top:18px;margin-bottom:10px;">
                <label for="comentario" style="font-weight:bold;">Comentario (opcional):</label><br>
                <textarea name="comentario" id="comentario" rows="3" style="width:100%;max-width:500px;padding:8px;border-radius:6px;border:1px solid #ccc;resize:vertical;" placeholder="Agrega un comentario para el técnico..."></textarea>
            </div>
            <input type="hidden" name="id_solicitud" value="<?= $id_solicitud ?>">
            <button type="submit" class="enviar">Asignar</button>
        </form>
        <div id="pantalla-carga">Enviando, por favor espera...</div>
        <script>
            function agregarTecnico() {
                const contenedor = document.getElementById('tecnicos');
                const campo = document.createElement('div');
                campo.className = 'campo-tecnico';
                campo.innerHTML = `
                    <select name="tecnicos[]">
                        <option value="">-- Selecciona un técnico --</option>
                        <?php foreach ($tecnicos_guardados as $tec): ?>
                            <option value="<?= htmlspecialchars($tec) ?>"><?= htmlspecialchars($tec) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="nuevo_tecnico[]" placeholder="Nuevo técnico (opcional)" autocomplete="off">
                    <button type="button" onclick="this.parentNode.remove()">-</button>
                `;
                contenedor.appendChild(campo);
            }
            document.getElementById('form-tecnicos').addEventListener('submit', function(e) {
                const botonEnviar = this.querySelector('button.enviar');
                botonEnviar.disabled = true;
                document.getElementById('pantalla-carga').style.display = 'block';
            });
        </script>
    <?php endif; ?>

</body>

</html>
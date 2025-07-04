<?php
session_start();
require_once 'protegido/config.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id_solicitud = $_GET['id'] ?? null;
if (!$id_solicitud) {
    echo "ID inválido.";
    exit;
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

    <form method="POST" action="enviarMail/tecnicoMail.php" id="form-tecnicos">
        <div id="tecnicos">
            <div class="campo-tecnico">
                <input type="text" name="tecnicos[]" placeholder="Nombre del técnico" required autocomplete="off">
                <button type="button" onclick="agregarTecnico()">+</button>
            </div>
        </div>

        <input type="hidden" name="id_solicitud" value="<?= $id_solicitud ?>">
        <button type="submit" class="enviar">Asignar y Notificar</button>
    </form>

    <div id="pantalla-carga">Enviando, por favor espera...</div>

    <script>
        function agregarTecnico() {
            const contenedor = document.getElementById('tecnicos');
            const campo = document.createElement('div');
            campo.className = 'campo-tecnico';
            campo.innerHTML = `
                <input type="text" name="tecnicos[]" placeholder="Nombre del técnico" required autocomplete="off">
                <button type="button" onclick="this.parentNode.remove()">-</button>
            `;
            contenedor.appendChild(campo);
        }

        // Mostrar pantalla de carga y deshabilitar botón para evitar doble envío
        document.getElementById('form-tecnicos').addEventListener('submit', function(e) {
            const botonEnviar = this.querySelector('button.enviar');
            botonEnviar.disabled = true;
            document.getElementById('pantalla-carga').style.display = 'block';
        });
    </script>

</body>

</html>
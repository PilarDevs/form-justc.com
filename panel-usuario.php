<?php
session_start();
require 'protegido/config.php'; // Archivo donde tienes tu conexión PDO

// Verificar sesión de usuario
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// Obtener solicitudes del usuario
try {
    $sql = "SELECT id, url_pdf, fecha_envio, estatus 
            FROM solicitudes 
            WHERE id_usuario = :id_usuario
            ORDER BY fecha_envio DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_usuario' => $id_usuario]);
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener solicitudes: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Panel de Usuario - Solicitudes</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; margin: 20px; }
        h1 { color: #005baa; }
        table { border-collapse: collapse; width: 100%; background: white; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #005baa; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        a.pdf-link { color: #005baa; text-decoration: none; }
        a.pdf-link:hover { text-decoration: underline; }
        .estatus-pendiente { color: orange; font-weight: bold; }
        .estatus-aprobado { color: green; font-weight: bold; }
        .estatus-rechazado { color: red; font-weight: bold; }
        .logout-btn {
            background-color: #005baa;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 4px;
            float: right;
            margin: 5px;
        }
        .logout-btn:hover { background-color: #004080; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <a href="logout.php" class="logout-btn">Cerrar sesión</a>
        <a href="form-tecnico.php" class="logout-btn">Solicitud</a>
        <h1>Mis Solicitudes</h1>
        <?php if (empty($solicitudes)): ?>
            <p>No tienes solicitudes registradas.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Solicitud</th>
                        <th>Fecha de envío</th>
                        <th>Estatus</th>
                        <th>PDF</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudes as $sol): ?>
                        <tr>
                            <td><?= htmlspecialchars($sol['id']) ?></td>
                            <td><?= htmlspecialchars($sol['fecha_envio']) ?></td>
                            <td>
                                <?php 
                                    $estatus = strtolower($sol['estatus']);
                                    $clase = '';
                                    if ($estatus === 'pendiente') $clase = 'estatus-pendiente';
                                    elseif ($estatus === 'aprobado') $clase = 'estatus-aprobado';
                                    elseif ($estatus === 'rechazado') $clase = 'estatus-rechazado';
                                    echo "<span class=\"$clase\">" . ucfirst($sol['estatus']) . "</span>";
                                ?>
                            </td>
                            <td>
                                <?php if (!empty($sol['url_pdf']) && file_exists('pdfs/' . $sol['url_pdf'])): ?>
                                    <a href="pdfs/<?= urlencode($sol['url_pdf']) ?>" target="_blank" class="pdf-link">Descargar PDF</a>
                                <?php else: ?>
                                    No disponible
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
require 'protegido/config.php';
$stmt = $pdo->query("SELECT s.id, u.usuario, s.url_pdf, s.fecha_envio 
                     FROM solicitudes s
                     JOIN usuarios u ON s.id_usuario = u.id
                     ORDER BY s.fecha_envio DESC");

echo "<h2>Solicitudes</h2><table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Usuario</th><th>PDF</th><th>Fecha</th></tr>";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['usuario']}</td>
            <td><a href='{$row['url_pdf']}' target='_blank'>Ver PDF</a></td>
            <td>{$row['fecha_envio']}</td>
          </tr>";
}
echo "</table>";

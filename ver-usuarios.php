<?php
require 'protegido/config.php';
$stmt = $pdo->query("SELECT id, nombre, usuario, correo FROM usuarios ORDER BY id DESC");

echo "<h2>Usuarios Registrados</h2><table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Correo</th></tr>";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['nombre']}</td>
            <td>{$row['usuario']}</td>
            <td>{$row['correo']}</td>
          </tr>";
}
echo "</table>";

<?php

require_once __DIR__ . '/auth.php';
requiere_ajax();

require_once __DIR__ . '/database_connection.php';


$id = param_int('id');

$conn = conectar();

$stmt = $conn->prepare('UPDATE alarmas SET activa=0, fecha_fin=NOW() WHERE activa=1 AND idregistros = ?');
if ($stmt) {
    $stmt->bind_param('i', $id);
    $result = $stmt->execute();
    echo $result ? '1' : '0';
    $stmt->close();
} else {
    echo '0';
}

$conn->close();

<?php

require_once __DIR__ . '/auth.php';

require_once __DIR__ . '/database_connection.php';


$planta = param_texto('planta');

$conn = conectar();
$arr1 = array();
$stmt = $conn->prepare('SELECT DISTINCT linea FROM datalogger.inverters WHERE planta = ? ORDER BY linea');
if ($stmt) {
    $stmt->bind_param('s', $planta);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($linea = $result->fetch_assoc()) {
            $arr1[] = array("linea" => $linea["linea"]);
        }
    }
    $stmt->close();
}
$conn->close();
echo json_encode($arr1);
<?php

require_once __DIR__ . '/auth.php';

require_once __DIR__ . '/database_connection.php';


$planta = param_texto('planta');
$ct = param_int('CT');


$conn = conectar();
$arr1 = array();

$stmt = $conn->prepare('SELECT inverter,nombre,idinverters FROM datalogger.inverters WHERE planta = ? AND ct = ? ORDER BY inverter');
if ($stmt) {
    $stmt->bind_param('si', $planta, $ct);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($linea = $result->fetch_assoc()) {
            $arr1[] = array(
                "inverter" => $linea["inverter"],
                "nombre" => $linea["nombre"],
                "id" => $linea["idinverters"]
            );
        }
    }
    $stmt->close();
}

$conn->close();
echo json_encode($arr1);
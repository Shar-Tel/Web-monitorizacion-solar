<?php

require_once __DIR__ . '/auth.php';

require_once __DIR__ . '/database_connection.php';


$planta = param_texto('planta');

$conn = conectar();
//DEVUELVE LOS DATOS PARA LA FNUCIÓN QUE ACTIVA LA PLANTA QUE SE QUIERE VER
$stmt = $conn->prepare('SELECT nombre_datos, linea, nombre FROM datalogger.plantas WHERE id = ? ORDER BY nombre');
$arr1 = array();
if ($stmt) {
    $stmt->bind_param('s', $planta);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($linea = $result->fetch_assoc()) {
            $arr1[] = array(
                "nombre_datos" => $linea["nombre_datos"],
                "linea" => $linea["linea"],
                "nombre" => $linea["nombre"],
            );
        }
    }
    $stmt->close();
} else {
    error_log('consulta_plantas: prepare failed');
}

$conn->close();
echo json_encode($arr1);

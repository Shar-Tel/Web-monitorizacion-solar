<?php

require_once __DIR__ . '/auth.php';

require_once __DIR__ . '/database_connection.php';


$planta = param_texto('planta');
$ct = param_int('CT');
$inverter = param_int('inverter');


$conn = conectar();
$arr1 = array();


if ($inverter == 0) {
    $stmt = $conn->prepare('SELECT temporal.inverter, round(PAC/1000,2) as PAC, KDY , KMT, POTENCIA_PICO, DATE_FORMAT(fecha,\'%H:%i:%s\') as hora, round(PAC/(potencia_instalada*10),2) as rendimiento, nombre FROM datalogger.temporal, inverters WHERE temporal.planta = ? AND ct = ? AND temporal.planta = inverters.planta AND temporal.linea = inverters.linea AND temporal.inverter = inverters.inverter AND fecha > DATE_SUB(NOW(),INTERVAL 60 MINUTE) ORDER BY inverters.nombre');
    if ($stmt) {
        $stmt->bind_param('si', $planta, $ct);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            while ($linea = $result->fetch_assoc()) {
                $arr1[] = array(
                    'inverter' => "Inverter " . $linea["inverter"],
                    'PAC' => $linea["PAC"],
                    'KDY' => $linea["KDY"],
                    'KMT' => $linea["KMT"],
                    'rendimiento' => $linea["rendimiento"],
                    'ratio' => round($linea["KDY"] / $linea["POTENCIA_PICO"], 2),
                    'nombre' => $linea["nombre"],
                    'fecha' => $linea["hora"],
                );
            }
        }
        $stmt->close();
    }
} else {
    $stmt = $conn->prepare('SELECT temporal.inverter, round(PAC/1000,2) as PAC, KDY , KMT, POTENCIA_PICO, nombre, DATE_FORMAT(fecha,\'%H:%i:%s\') as hora, round(PAC/(potencia_instalada*10),2) as rendimiento FROM datalogger.temporal, inverters WHERE temporal.planta = ? AND ct = ? AND temporal.planta = inverters.planta AND temporal.linea = inverters.linea AND temporal.inverter = inverters.inverter AND temporal.inverter = ? AND fecha > DATE_SUB(NOW(),INTERVAL 60 MINUTE) ORDER BY inverters.nombre');
    if ($stmt) {
        $stmt->bind_param('sii', $planta, $ct, $inverter);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            while ($linea = $result->fetch_assoc()) {
                $arr1[] = array(
                    'inverter' => "Inverter " . $linea["inverter"],
                    'PAC' => $linea["PAC"],
                    'KDY' => $linea["KDY"],
                    'KMT' => $linea["KMT"],
                    'rendimiento' => $linea["rendimiento"],
                    'ratio' => round($linea["KDY"] / $linea["POTENCIA_PICO"], 2),
                    'nombre' => $linea["nombre"],
                    'fecha' => $linea["hora"],
                );
            }
        }
        $stmt->close();
    }
}

$conn->close();
echo json_encode($arr1);
<?php

require_once __DIR__ . '/auth.php';

require_once __DIR__ . '/database_connection.php';


$planta = param_texto('planta');
$mes = param_int('mes');
$anyo = param_int('anyo');
$dia = param_int('dia');

$fecha = sprintf('%04d-%02d-%02d', $anyo, $mes, $dia);

$conn = conectar();
$arr1 = array();

if ($planta === '') {
    $sql = "(SELECT idregistros,inverters.planta,CT,inverters.inverter,nombre, SAL, error,fecha_inicio,fecha_fin FROM datalogger.alarmas inner join inverters on DATE_FORMAT(fecha_inicio, '%Y-%m-%d') < ? and fecha_fin is null and inverters.planta=alarmas.planta and inverters.linea=alarmas.linea and inverters.inverter=alarmas.inverter LEFT JOIN codigos_alarmas on codigo=SAL and tipo_inverter=tipo)"
         . " UNION "
         . "(SELECT idregistros,inverters.planta,CT,inverters.inverter,nombre, SAL, error,fecha_inicio,fecha_fin FROM datalogger.alarmas inner join inverters on year(fecha_inicio)=? and month(fecha_inicio)=? and dayofmonth(fecha_inicio)=? and inverters.planta=alarmas.planta and inverters.linea=alarmas.linea and inverters.inverter=alarmas.inverter LEFT JOIN codigos_alarmas on codigo=SAL and tipo_inverter=tipo)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('siii', $fecha, $anyo, $mes, $dia);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $arr1[] = array(
                    'id' => $row["idregistros"],
                    'planta' => $row["planta"],
                    'linea' => $row["CT"],
                    'inverter' => $row["inverter"],
                    'nombre' => $row["nombre"],
                    'alarma' => $row["SAL"] . " - " . $row["error"],
                    'fecha_inicio' => $row["fecha_inicio"],
                    'fecha_fin' => $row["fecha_fin"],
                );
            }
        }
        $stmt->close();
    }
} else {
    $sql = "(SELECT idregistros,inverters.planta,CT,inverters.inverter,nombre, SAL, error,fecha_inicio,fecha_fin FROM datalogger.alarmas inner join inverters on DATE_FORMAT(fecha_inicio, '%Y-%m-%d') < ? and alarmas.planta = ? and fecha_fin is null and inverters.planta=alarmas.planta and inverters.linea=alarmas.linea and inverters.inverter=alarmas.inverter LEFT JOIN codigos_alarmas on codigo=SAL and tipo_inverter=tipo)"
         . " UNION "
         . "(SELECT idregistros,inverters.planta,CT,inverters.inverter,nombre, SAL, error,fecha_inicio,fecha_fin FROM datalogger.alarmas inner join inverters on year(fecha_inicio)=? and month(fecha_inicio)=? and dayofmonth(fecha_inicio)=? and alarmas.planta = ? and inverters.planta=alarmas.planta and inverters.linea=alarmas.linea and inverters.inverter=alarmas.inverter LEFT JOIN codigos_alarmas on codigo=SAL and tipo_inverter=tipo)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('ssiiis', $fecha, $planta, $anyo, $mes, $dia, $planta);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $arr1[] = array(
                    'id' => $row["idregistros"],
                    'planta' => $row["planta"],
                    'linea' => $row["CT"],
                    'inverter' => $row["inverter"],
                    'nombre' => $row["nombre"],
                    'alarma' => $row["SAL"] . " - " . $row["error"],
                    'fecha_inicio' => $row["fecha_inicio"],
                    'fecha_fin' => $row["fecha_fin"],
                );
            }
        }
        $stmt->close();
    }
}

$conn->close();
echo json_encode($arr1);

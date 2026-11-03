<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');


$planta = param_texto('planta');
$ct = param_int('CT');
$inverter = param_int('inverter');



$conn = conectar();


if ($inverter==0)
{
    $sql="SELECT temporal.inverter, round(PAC/1000,2) as PAC, KDY , KMT, POTENCIA_PICO, DATE_FORMAT(fecha,'%H:%i:%s') as hora, round(PAC/(potencia_instalada*10),2) as rendimiento, nombre FROM datalogger.temporal, inverters where temporal.planta='".$planta."' and ct=".$ct." and temporal.planta=inverters.planta and temporal.linea=inverters.linea and temporal.inverter=inverters.inverter and fecha > DATE_SUB(NOW(),INTERVAL 60 MINUTE) order by inverters.nombre;";
}
else
{
   $sql="SELECT temporal.inverter, round(PAC/1000,2) as PAC, KDY , KMT,POTENCIA_PICO, nombre, DATE_FORMAT(fecha,'%H:%i:%s') as hora, round(PAC/(potencia_instalada*10),2) as rendimiento FROM datalogger.temporal, inverters where temporal.planta='".$planta."' and ct=".$ct." and temporal.planta=inverters.planta and temporal.linea=inverters.linea and temporal.inverter=inverters.inverter and temporal.inverter=".$inverter." and fecha > DATE_SUB(NOW(),INTERVAL 60 MINUTE) order by inverters.nombre;";
    
}


$result = $conn->query($sql);

//				  'hora'=>date("H:i",$linea["hora"]),
if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {			
	$arr1[]=array('inverter'=>"Inverter ".$linea["inverter"],
					'PAC'=>$linea["PAC"],
					'KDY'=>$linea["KDY"],
					'KMT'=>$linea["KMT"],
					'rendimiento'=>$linea["rendimiento"],
					'ratio'=>round($linea["KDY"]/$linea["POTENCIA_PICO"],2),
					'nombre'=>$linea["nombre"],
					'fecha'=>$linea["hora"],
				   );
				   }
} else {
    echo "0 results";
}
$conn->close(); 
echo '' .json_encode($arr1).'';
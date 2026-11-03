<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');


$planta = param_texto('planta');
$ct = param_int('CT');



$conn = conectar();
$sql="SELECT nombre FROM datalogger.temporal, inverters where temporal.planta='".$planta."' and ct=".$ct." and temporal.planta=inverters.planta and temporal.linea=inverters.linea and temporal.inverter=inverters.inverter and fecha < DATE_SUB(NOW(),INTERVAL 15 MINUTE) order by inverters.nombre;";
$result = $conn->query($sql);


if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {			
	$arr1[]=array('nombre'=>$linea["nombre"]
				   );
				   }
				   
} else {
$arr1[]=array('nombre'=>'Ninguno'
				   );
}
$conn->close(); 
echo '' .json_encode($arr1).'';

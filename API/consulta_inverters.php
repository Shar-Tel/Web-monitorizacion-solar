<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');


$planta = param_texto('planta');
$ct = param_int('CT');


$conn = conectar();

$sql="SELECT  inverter,nombre,idinverters FROM datalogger.inverters where planta='".$planta."' and ct=".$ct." order by inverter;";



$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {			
	$arr1[]=array("inverter"=>$linea["inverter"],"nombre"=>$linea["nombre"],"id"=>$linea["idinverters"]
				   );
				   }
} else {
    echo "0 results";
}
$conn->close(); 
echo '' .json_encode($arr1).'';
<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');


$planta = param_texto('planta');



$conn = conectar();

$sql="SELECT nombre,numero,direccion,punto_medida,clave,IP_TEL FROM datalogger.CONTADORES where planta='".$planta."'  order by nombre;";



$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {
	$cadena=$linea["nombre"].'&nbsp;&nbsp;-&nbsp;&nbsp;NUM:'.$linea["numero"].'&nbsp;&nbsp;&nbsp;DIR:'.$linea["direccion"].'&nbsp;&nbsp;&nbsp;PM:'.$linea["punto_medida"].'&nbsp;&nbsp;&nbsp;CLAVE:'.$linea["clave"].'&nbsp;&nbsp;&nbsp;IP/TEL:'.$linea["IP_TEL"];		
	$arr1[]=array("nombre"=>$cadena,
				"numero"=>$linea["numero"]
				   );
				   }
} else {
    echo "0 results";
}
$conn->close(); 
echo '' .json_encode($arr1).'';

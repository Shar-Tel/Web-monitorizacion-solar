<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');


$planta = param_texto('planta');



$conn = conectar();

$sql="SELECT nombre,numero FROM datalogger.CONTADORES where planta='".$planta."'  order by nombre;";



$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {			
	$arr1[]=array("nombre"=>$linea["nombre"],
				"numero"=>$linea["numero"]
				   );
				   }
} else {
    echo "0 results";
}
$conn->close(); 
echo '' .json_encode($arr1).'';

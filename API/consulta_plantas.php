<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');


$planta = param_texto('planta');

$conn = conectar();
//DEVUELVE LOS DATOS PARA LA FNUCIÓN QUE ACTIVA LA PLANTA QUE SE QUIERE VER
$sql="SELECT  *  FROM datalogger.plantas where id='".$planta."' order by nombre;";


$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {		
	$arr1[]=array("nombre_datos"=>$linea["nombre_datos"],
	"linea"=>$linea["linea"],
	"nombre"=>$linea["nombre"],
				   );
				   }
} else {
    echo "0 results";
}
$conn->close(); 
echo '' .json_encode($arr1).'';

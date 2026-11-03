<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');


//**************************************************************************************
//ESTA CONSULTA SACA LA SUMA DE LAS MEDIAS DIARIAS DE RADIACIÓN
//**************************************************************************************

$conn = conectar();

//ESTE SACA LA RADIACIÓN MEDIA DE LOS DÍAS DE UN MES
$sql="select respuesta from lectura_instant";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {			
	$arr1[]=array(
				  'respuesta'=>$linea["respuesta"],
				   );
				   }
} else {
    echo "0 results";
}
$conn->close(); 
echo '' .json_encode($arr1).'';
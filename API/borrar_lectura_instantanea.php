<?php

require_once __DIR__ . '/auth.php';
requiere_ajax();

include('database_connection.php');


$planta = param_texto('planta');
$contador = param_int('contador');




//Pongo en la BD el contador a leer
$conn = conectar();
$sql="UPDATE lectura_instant SET PLANTA='', NUMERO=0, RESPUESTA=''";

$result = $conn->query($sql);
 
if (!$result) {
	echo 'KO';
} else {
	echo 'OK';
}

$conn->close(); 

//echo $textoCOM."<br>".$textoPAC;
//echo '' .json_encode($arr1).'';

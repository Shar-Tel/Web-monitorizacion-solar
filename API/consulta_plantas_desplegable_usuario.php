<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');
include('config.php');

// El usuario se toma de la sesión del servidor, no del parámetro de la URL.
$usuario = (int) usuario_sesion();

$conn = conectar();
//DEVUELVE LA LISTA DE PLANTAS QUE PUEDE VER EL USUARIO
if (esUsuarioAdmin($usuario)){
	$sql="SELECT  *  FROM datalogger.plantas order by nombre;";
}
else {
	$sql="SELECT  datalogger.plantas.id,datalogger.plantas.nombre  FROM datalogger.plantas, datalogger.usuarios_plantas where usuario=".$usuario." and datalogger.usuarios_plantas.planta=datalogger.plantas.id order by nombre;";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {		
	$arr1[]=array("planta"=>$linea["id"],
	"nombre"=>$linea["nombre"],
				   );
				   }
} else {
    echo "0 results";
}
$conn->close(); 
echo '' .json_encode($arr1).'';

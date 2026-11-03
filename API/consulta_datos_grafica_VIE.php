<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');


$planta = param_texto('planta');
$fechaI = param_fecha('fechaI');
$fechaF = param_fecha('fechaF');






//Sacar los meses entre la inicial y la final
function obtenerMesesEntreFechas($fechaInicio, $fechaFin) {
    $meses = array();

    $fechaInicio = new DateTime($fechaInicio);
    $fechaFin = new DateTime($fechaFin);

    // Añadir el primer mes
    $meses[] = $fechaInicio->format('Y-m');

    while ($fechaInicio < $fechaFin) {
        // Añadir un mes a la fecha de inicio
        $fechaInicio->modify('+1 month');

        // Añadir el mes al array
        $meses[] = $fechaInicio->format('Y-m');
    }

    return $meses;
}

$fechaIcorta=substr($fechaI,0,7);
$fechaFcorta=substr($fechaF,0,7);
$resultado = obtenerMesesEntreFechas($fechaIcorta, $fechaFcorta);




//Monto el SQL, que pueden ser varios SELECT con UNION
$inicioSQL="SELECT planta, valor, nombre_general, hora from (";
$finSQL=") as d order by hora";
$sql="";
//Hacer un for con todos los meses que vaya añadiendo consultas con UNION
foreach ($resultado as $fecha)
{

	//Tengo que sacar el mes y año de la fecha
	$anyo= substr($fecha,0,4);
	$mes= substr($fecha,5,2);
	$BD="datalogger".$anyo.$mes;

	//Si $sql no está vacía es que ya hay una sentencia, tengo que añadir UNION
	if($sql!=""){
		$sql.=" UNION ";
	}
	

		$sql.="select planta, valor, nombre_general, fecha as hora from ".$BD.".VIE, plantas where valor>0 and planta='".$planta."' and plantas.nombre_datos='".$planta."' and fecha between '".$fechaI."' and '".$fechaF." 23:59'";

}

$sqlCompleto=$inicioSQL.$sql.$finSQL;


$conn = conectar();

$result = $conn->query($sqlCompleto);

if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {			
	$arr1[]=array(
				  'nombre'=>'Velocidad del viento',
				  'valor'=>$linea["valor"],
				  'hora'=>$linea["hora"],
				   );
				   }
} else {
    echo "0 results";
}

$conn->close(); 
echo '' .json_encode($arr1).'';
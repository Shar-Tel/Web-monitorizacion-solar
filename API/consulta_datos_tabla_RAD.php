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



//Saco el nombre de la planta
$sql="SELECT nombre_general FROM plantas where id='".$planta."'";
$conn = conectar();

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {
	$nombrePlanta=$linea["nombre_general"];				
	}
}


//**************************************************************************************
//ESTA CONSULTA SE GASTA EN LA TABLA DE LA PÁGINA DE DATOS AL ELEGIR LA RADIACIÓN DIARIA
//**************************************************************************************

//Tengo que cambiar la planta para algunos casos porque la RAD es compartida
switch ($planta) {
    case "Leyva":
        $plantaRAD="Mazarron";
        break;
    case "Granja-Villarreal":
        $plantaRAD="Villarreal";
        break;
    default:
		$plantaRAD=$planta;
}

//Monto el SQL, que pueden ser varios SELECT con UNION
$inicioSQL="select planta, radiacion,temp, nombre_general, hora from (";
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
		$sql.="select RAD.planta, RAD.valor as radiacion,TMP_PL.valor AS temp, nombre_general, date_format(RAD.fecha,'%Y-%m-%d %H:%i') as hora
		from  plantas, ".$BD.".RAD
		left join ".$BD.".TMP_PL on  RAD.planta=TMP_PL.planta and date_format(RAD.fecha,'%d-%m-%Y %H:%i')=date_format(TMP_PL.fecha,'%d-%m-%Y %H:%i')
		where RAD.planta='".$plantaRAD."' and plantas.nombre_datos=RAD.planta  and RAD.fecha between '".$fechaI."' and '".$fechaF." 23:59'";

}




$sqlCompleto=$inicioSQL.$sql.$finSQL;


$result = $conn->query($sqlCompleto);

if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {			
	$arr1[]=array(
				  'inverter'=>$nombrePlanta,
				  'radiacion'=>$linea["radiacion"],
				  'temperatura'=>$linea["temp"],
				  'fecha'=>$linea["hora"],
				   );
				   }
} else {
    echo "0 results";
}
$conn->close(); 
echo '' .json_encode($arr1).'';
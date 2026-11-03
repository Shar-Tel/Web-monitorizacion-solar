<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');


$planta = param_texto('planta');
$mes = param_int('mes');
$anyo = param_int('anyo');
$BD="datalogger".$anyo.$mes;
//**************************************************************************************
//ESTA CONSULTA SACA LA SUMA DE LAS MEDIAS DIARIAS DE RADIACIÓN
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

$conn = conectar();

//ESTE SACA LA RADIACIÓN MEDIA DE LOS DÍAS DE UN MES
$sql="select round(sum(sumadia),3) as suma from (select sum(dato) as sumadia, dia from (select planta, avg(valor) as dato, nombre_general, concat(lpad(hour(fecha),2,0),' ',lpad(day(fecha),2,0),'-',lpad(month(fecha),2,0),'-',year(fecha)) as hora, concat(lpad(day(fecha),2,0),'-',lpad(month(fecha),2,0),'-',year(fecha)) as dia from ".$BD.".RAD, plantas where valor>0 and planta='".$plantaRAD."' and plantas.nombre_datos=planta and plantas.linea in (0,1) and year(fecha)=".$anyo." and month(fecha)=".$mes." group by planta, nombre_general, hora, dia) as h group by dia) as d";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {			
	$arr1[]=array(
				  'valor'=>$linea["suma"],
				   );
				   }
} else {
    echo "0 results";
}
$conn->close(); 
echo '' .json_encode($arr1).'';
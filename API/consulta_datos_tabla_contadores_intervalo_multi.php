<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');


$planta = param_texto('planta');
$fechaI = param_fecha('fechaI');
$fechaF = param_fecha('fechaF');
$numeros = param_lista_enteros('numeros');




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



//ESTA PARTE ES PARA CALCULAR LA RADIACIÓN
$inicioSQL="select round(sum(sumadia),3) as suma from (";
$finSQL=") as d";
$sql="";

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

//Hacer un for con todos los meses que vaya añadiendo consultas con UNION
foreach ($resultado as $fecha)
{

	//Tengo que sacar el mes y año de la fecha
	$anyo= substr($fecha,0,4);
	$mes= substr($fecha,5,2);
	$BD="datalogger".$anyo.$mes.".";

	//Si $sql no está vacía es que ya hay una sentencia, tengo que añadir UNION
	if($sql!=""){
		$sql.=" UNION ";
	}
	
	$sql.="select sum(dato) as sumadia, dia from (select planta, avg(valor) as dato, nombre_general, concat(lpad(hour(fecha),2,0),' ',lpad(day(fecha),2,0),'-',lpad(month(fecha),2,0),'-',year(fecha)) as hora, concat(lpad(day(fecha),2,0),'-',lpad(month(fecha),2,0),'-',year(fecha)) as dia from ".$BD."RAD, plantas where valor>0 and planta='".$plantaRAD."' and plantas.nombre_datos=planta and plantas.linea in (0,1) and fecha between '".$fechaI."' and '".$fechaF." 23:59' group by planta, nombre_general, hora, dia) as h group by dia";	

}

$sqlCompleto=$inicioSQL.$sql.$finSQL;
//Saco la radiación del periodo
$conn = conectar();
$result = $conn->query($sqlCompleto);

//Pongo radiación a 0 por si no devuelve nada
$rad=0;

if ($result->num_rows > 0) {
$linea = $result->fetch_assoc();	
	$rad=$linea["suma"];
}




//CONSULTO LOS CONTADORES

$inicioSQL="Select planta, Nombre, sum(sumaAI) as sumaAI, sum(sumaAE) as sumaAE, sum(sumaRQ1) as sumaRQ1, sum(sumaRQ2) as sumaRQ2, sum(sumaRQ3) as sumaRQ3, sum(sumaRQ4) as sumaRQ4,pmax FROM (";
$finSQL=") as sal group by planta, nombre,pmax order by Nombre";
$sql="";
//Hacer un for con todos los meses que vaya añadiendo consultas con UNION
foreach ($resultado as $fecha)
{

	//Tengo que sacar el mes y año de la fecha
	$anyo= substr($fecha,0,4);
	$mes= substr($fecha,5,2);
	$BD="datalogger".$anyo.$mes.".";

	//Si $sql no está vacía es que ya hay una sentencia, tengo que añadir UNION
	if($sql!=""){
		$sql.=" UNION ";
	}
	//Si el número es 0 los devuelvo todos
	if ($numeros==0){
	$sql.="select CONT.Planta,Nombre, sum(Activa_I) as sumaAI, sum(Activa_E) as sumaAE, sum(Reactiva_Q1) as sumaRQ1, sum(Reactiva_Q2) as sumaRQ2,sum(Reactiva_Q3) as sumaRQ3,sum(Reactiva_Q4) as sumaRQ4, pmax from ".$BD."CONT,CONTADORES WHERE CONT.Planta=CONTADORES.Planta and CONT.Numero=CONTADORES.Numero and fecha between '".$fechaI."' and '".$fechaF." 23:59'  and CONT.Planta='".$planta."' group by Nombre, pmax";	
	}	
	//Si no, los elegidos
	else
	{	
	$sql.="select CONT.Planta,Nombre, sum(Activa_I) as sumaAI, sum(Activa_E) as sumaAE, sum(Reactiva_Q1) as sumaRQ1, sum(Reactiva_Q2) as sumaRQ2,sum(Reactiva_Q3) as sumaRQ3,sum(Reactiva_Q4) as sumaRQ4, pmax from ".$BD."CONT,CONTADORES WHERE CONT.Planta=CONTADORES.Planta and CONT.Numero=CONTADORES.Numero and CONT.Numero in (".$numeros.") and fecha between '".$fechaI."' and '".$fechaF." 23:59'  and CONT.Planta='".$planta."' group by Nombre, pmax";	
	}
	;

}
$conn = conectar();

$sqlCompleto=$inicioSQL.$sql.$finSQL;
$result = $conn->query($sqlCompleto);






if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {			
	$arr1[]=array("Nombre"=>$linea["Nombre"],
				 // "Hora"=>$linea["Hora"],
				  "Activa_I"=>$linea["sumaAI"],
				  "Activa_E"=>$linea["sumaAE"],
					"Reactiva_Q1"=>$linea["sumaRQ1"],
					"Reactiva_Q2"=>$linea["sumaRQ2"],
					"Reactiva_Q3"=>$linea["sumaRQ3"],
					"Reactiva_Q4"=>$linea["sumaRQ4"],
					"PMax"=>$linea["pmax"],
					"Heq"=>round($linea["sumaAE"]/$linea["pmax"],2),
					"PR"=>($rad!=0)?round($linea["sumaAE"]/$rad/$linea["pmax"]*100000,2):"-", //Si radiación es 0 devuelvo -, si no, calculo
				   );
				   }
	//Calculo la media del Heq
	$media=0;			   
	foreach ($arr1 as $elem){
		$media+=$elem["Heq"];
	}
	$media = $media/count($arr1);
	//Calculo la desviación del Heq respecto a la media
	foreach ($arr1 as $elem){
		$desviacion=round(($elem["Heq"]/$media-1)*100,2);
		$elem["desviacion"]=$desviacion;
		$res[]=$elem;
	}

} else {
    echo "0 results";
}
$conn->close(); 
echo '' .json_encode($res).'';



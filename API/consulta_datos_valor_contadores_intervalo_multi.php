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

//Hacer un for con todos los meses que vaya añadiendo consultas con UNION
foreach ($resultado as $fecha)
{
	//Tengo que sacar el mes y año de la fecha
	$anyo= substr($fecha,0,4);
	$mes= substr($fecha,5,2);
	$BD="datalogger".$anyo.$mes.".";

	//Si el número es 0 los devuelvo todos
	if ($numeros==0){
	$sql="select CONT.Planta,Nombre, concat(fecha,' ',  hora) as fechaHora, Activa_E from ".$BD."CONT,CONTADORES WHERE CONT.Planta=CONTADORES.Planta and CONT.Numero=CONTADORES.Numero and fecha  between '".$fechaI."' and '".$fechaF."' and CONT.Planta='".$planta."' order by Nombre,fecha, hora";	
	}	
	//Si no, los elegidos
	else
	{	
	$sql="select CONT.Planta,Nombre, concat(fecha,' ',  hora) as fechaHora, Activa_E from ".$BD."CONT,CONTADORES WHERE CONT.Planta=CONTADORES.Planta and CONT.Numero=CONTADORES.Numero and CONT.Numero in (".$numeros.")  and fecha  between '".$fechaI."' and '".$fechaF."' and CONT.Planta='".$planta."' order by Nombre,fecha, hora";	
	}
	;
	$conn = conectar();
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		// output data of each row
		while($linea = $result->fetch_assoc()) {

		$arr1[]=array("planta"=>$linea["Planta"],
					  'valor'=>$linea["Activa_E"],
					  'hora'=>$linea["fechaHora"],	
					  'nombre'=>$linea["Nombre"],
					   );
		}
	}
}


$conn->close(); 
echo '' .json_encode($arr1).'';

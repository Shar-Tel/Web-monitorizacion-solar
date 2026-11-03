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


//Hacer un for con todos los meses que vaya añadiendo los resultados al vector
foreach ($resultado as $fecha)
{
	//Tengo que sacar el mes y año de la fecha
	$anyo= substr($fecha,0,4);
	$mes= substr($fecha,5,2);
	$BD="datalogger".$anyo.$mes.".";
	//Si la planta está vacía devuelve de todas
	if ($planta==''){
			
		//Si el número es 0 los devuelvo todos
		if ($numeros==0){
		$sql="select plantas.NOMBRE as Planta,CONTADORES.NOMBRE as Nombre, Fecha, Hora, Activa_I, Activa_E, Reactiva_Q1, Reactiva_Q2, Reactiva_Q3, Reactiva_Q4 from ".$BD."CONT,CONTADORES,plantas WHERE CONT.Planta=CONTADORES.Planta and CONT.Numero=CONTADORES.Numero and fecha between '".$fechaI."' and '".$fechaF."'  and CONTADORES.planta=plantas.id order by plantas.NOMBRE,fecha, hora,CONTADORES.NOMBRE";
		}	
		//Si no, los elegidos
		else
		{	
		$sql="select plantas.NOMBRE as Planta,CONTADORES.NOMBRE as Nombre, Fecha, Hora, Activa_I, Activa_E, Reactiva_Q1, Reactiva_Q2, Reactiva_Q3, Reactiva_Q4 from ".$BD."CONT,CONTADORES,plantas WHERE CONT.Planta=CONTADORES.Planta and CONT.Numero=CONTADORES.Numero and CONT.Numero in (".$numeros.")  and fecha between '".$fechaI."' and '".$fechaF."'  and CONTADORES.planta=plantas.id order by plantas.NOMBRE,fecha, hora,CONTADORES.NOMBRE";	
		}
	}
	else{
		//Si el número es 0 los devuelvo todos
		if ($numeros==0){
		$sql="select plantas.NOMBRE as Planta,CONTADORES.NOMBRE as Nombre, Fecha, Hora, Activa_I, Activa_E, Reactiva_Q1, Reactiva_Q2, Reactiva_Q3, Reactiva_Q4 from ".$BD."CONT,CONTADORES,plantas WHERE CONT.Planta=CONTADORES.Planta and CONT.Numero=CONTADORES.Numero and fecha between '".$fechaI."' and '".$fechaF."' and CONT.Planta='".$planta."' and CONTADORES.planta=plantas.id order by plantas.NOMBRE,fecha, hora,CONTADORES.NOMBRE";	
		}	
		//Si no, los elegidos
		else
		{	
		$sql="select plantas.NOMBRE as Planta,CONTADORES.NOMBRE as Nombre, Fecha, Hora, Activa_I, Activa_E, Reactiva_Q1, Reactiva_Q2, Reactiva_Q3, Reactiva_Q4 from ".$BD."CONT,CONTADORES,plantas  WHERE CONT.Planta=CONTADORES.Planta and CONT.Numero=CONTADORES.Numero and CONT.Numero in (".$numeros.")  and fecha between '".$fechaI."' and '".$fechaF."' and CONT.Planta='".$planta."' and CONTADORES.planta=plantas.id order by plantas.NOMBRE,fecha, hora,CONTADORES.NOMBRE";	
		}		
	}
	;

	$conn = conectar();
	$result = $conn->query($sql);


	if ($result->num_rows > 0) {
		// output data of each row
		while($linea = $result->fetch_assoc()) {			
		$arr1[]=array("Planta"=>$linea["Planta"],
						"Nombre"=>$linea["Nombre"],
					  "Fecha"=>$linea["Fecha"],
					  "Hora"=>$linea["Hora"],
					  "Activa_I"=>$linea["Activa_I"],
					  "Activa_E"=>$linea["Activa_E"],
						"Reactiva_Q1"=>$linea["Reactiva_Q1"],
						"Reactiva_Q2"=>$linea["Reactiva_Q2"],
						"Reactiva_Q3"=>$linea["Reactiva_Q3"],
						"Reactiva_Q4"=>$linea["Reactiva_Q4"],
					   );
					   }
	} 
}
$conn->close(); 
echo '' .json_encode($arr1).'';



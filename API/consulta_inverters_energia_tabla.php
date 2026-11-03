<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');


$planta = param_texto('planta');
$ct = param_int('CT');
$id = param_lista_enteros('inverter');
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
$inicioSQL="select inverter, planta, valor, dia,linea, nombre from (";
$finSQL=") as d order by dia,nombre;";
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
	
	if ($id==0){
		//EL COMENTADO MIRA EL MÁXIMO DIARIO, EL OTRO EL ÚLTIMO DIARIO
		$sql="SELECT KDY.inverter, KDY.planta,KDY.linea, KDY.valor, DATE(KDY.fecha) as dia, inverters.nombre FROM ".$BD.".KDY JOIN datalogger.inverters ON KDY.idinverter = inverters.idinverters JOIN (SELECT idinverter, DATE(fecha) AS dia, MAX(fecha) AS ultima_fecha FROM ".$BD.".KDY WHERE planta = '".$planta."'	and fecha between '".$fechaI."' and '".$fechaF." 23:59' GROUP BY idinverter, linea, dia ) Ultimos ON KDY.idinverter = Ultimos.idinverter AND DATE(KDY.fecha) = Ultimos.dia AND KDY.fecha = Ultimos.ultima_fecha and KDY.planta='".$planta."'";

	}	
	else{
		//EL COMENTADO MIRA EL MÁXIMO DIARIO, EL OTRO EL ÚLTIMO DIARIO
		$sql="SELECT KDY.inverter, KDY.planta, KDY.linea, KDY.valor, DATE(KDY.fecha) as dia, inverters.nombre FROM ".$BD.".KDY JOIN datalogger.inverters ON KDY.idinverter = inverters.idinverters JOIN (SELECT idinverter, DATE(fecha) AS dia, MAX(fecha) AS ultima_fecha FROM ".$BD.".KDY WHERE planta = '".$planta."' and idinverter in (".$id.")	and fecha between '".$fechaI."' and '".$fechaF." 23:59' GROUP BY idinverter, linea, dia ) Ultimos ON KDY.idinverter = Ultimos.idinverter AND DATE(KDY.fecha) = Ultimos.dia AND KDY.fecha = Ultimos.ultima_fecha and KDY.planta='".$planta."'";

	}
}

$sqlCompleto=$inicioSQL.$sql.$finSQL;
$conn = conectar();

$result = $conn->query($sqlCompleto);

if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {			
	$arr1[]=array("inverter"=>$linea["inverter"],
				"planta"=>$linea["planta"],
				"linea"=>$linea["linea"],
				'valor'=>$linea["valor"],
				  "valor"=>$linea["valor"],
				  "hora"=>$linea["dia"],
				  "nombre"=>$linea["nombre"]
				   );
				   }
} else {
    echo "0 results";
}
$conn->close(); 
echo '' .json_encode($arr1).'';
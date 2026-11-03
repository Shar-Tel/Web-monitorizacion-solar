<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');


$planta = param_texto('planta');
$ct = param_int('CT');
$id = param_lista_enteros('inverter');
$fechaI = param_fecha('fechaI');
$fechaF = param_fecha('fechaF');

// Solo se permiten columnas conocidas: nunca se usa el parámetro como SQL.
$COLUMNAS_VALOR = array('PAC', 'KDY', 'IL1', 'IL2', 'IL3', 'IDC', 'TKK', 'UL1', 'UL2', 'UL3', 'UDC', 'UGD', 'RAD', 'VIE', 'KMT', 'TEMP');
$columnaValor = param_identificador('valor', $COLUMNAS_VALOR, 'PAC');





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
$inicioSQL="SELECT inverter,planta,ct,valor, nombre, hora, potencia_instalada from (";
$finSQL=") as d order by hora, inverter";
$sql="";
//Hacer un for con todos los meses que vaya añadiendo consultas con UNION
foreach ($resultado as $fecha)
{

	//Tengo que sacar el mes y año de la fecha
	$anyo= substr($fecha,0,4);
	$mes= substr($fecha,5,2);
	$valor="datalogger".$anyo.$mes.".".$columnaValor;

	//Si $sql no está vacía es que ya hay una sentencia, tengo que añadir UNION
	if($sql!=""){
		$sql.=" UNION ";
	}
	
	if ($id==0){
		$sql.="select ".$valor.".inverter,".$valor.".planta,ct,valor, nombre, fecha as hora, potencia_instalada from ".$valor.", inverters where ".$valor.".planta='".$planta."' and ct=".$ct." and ".$valor.".idinverter=inverters.idinverters and fecha between '".$fechaI."' and '".$fechaF." 23:59'";
	}
	else{
		$sql.="select ".$valor.".inverter,".$valor.".planta,ct,valor,nombre, fecha as hora, potencia_instalada from ".$valor.", inverters where ".$valor.".idinverter in (".$id.") and ".$valor.".idinverter=inverters.idinverters and fecha between '".$fechaI."' and '".$fechaF." 23:59'";	
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
				  "linea"=>$linea["ct"],
				  'valor'=>$linea["valor"],
				  'hora'=>$linea["hora"],	
				  'nombre'=>$linea["nombre"],
				   );
	}
} else {
    echo "0 results";
}
$conn->close(); 
echo '' .json_encode($arr1).'';

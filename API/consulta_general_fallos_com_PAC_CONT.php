<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');


$planta = param_texto('planta');
$ct = param_int('CT');


$textoCOM="Inverters sin comunicación: ";
$textoPAC="Inverters con potencia 0: ";
$textoCONT="Contadores que no han comunicado: ";
$conn = conectar();
$sql="SELECT nombre FROM datalogger.temporal, inverters where temporal.planta='".$planta."' and ct=".$ct." and temporal.planta=inverters.planta and temporal.linea=inverters.linea and temporal.inverter=inverters.inverter and fecha < DATE_SUB(NOW(),INTERVAL 15 MINUTE) order by inverters.nombre;";

$result = $conn->query($sql);
$coma=" ";

if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {
	$textoCOM=$textoCOM.$coma.$linea["nombre"];
	$coma=", ";
	
				   }
				   
} else {
	$textoCOM="Inverters sin comunicación: Ninguno";
}

//Aquí añado los fallos de PAC=0
$sql="SELECT nombre FROM datalogger.temporal, inverters where temporal.planta='".$planta."' and ct=".$ct." and temporal.planta=inverters.planta and temporal.linea=inverters.linea and temporal.inverter=inverters.inverter and PAC=0 order by inverters.nombre;";
$result = $conn->query($sql);
$coma=" ";

if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {
		$textoPAC=$textoPAC.$coma.$linea["nombre"];
	$coma=", ";
	
				   }
				   
} else {
	$textoPAC="Inverters con potencia 0: Ninguno";
}

//Aquí añado los fallos de CONTADORES
$sql="SELECT CONTADORES_ERROR.planta, CONTADORES_ERROR.numero, nombre FROM CONTADORES_ERROR, CONTADORES where fecha=DATE_FORMAT(CURDATE(),'%d-%m-%Y') and CONTADORES_ERROR.PLANTA=CONTADORES.PLANTA and CONTADORES_ERROR.NUMERO=CONTADORES.NUMERO and CONTADORES_ERROR.planta='".$planta."'";
$result = $conn->query($sql);
$coma=" ";

if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {
		$textoCONT=$textoCONT.$coma.$linea["nombre"];
	$coma=", ";
	
				   }
				   
} else {
	$textoCONT="Contadores que no han comunicado: Ninguno";
}
$conn->close(); 

$arr1[]=array('fallos'=>$textoCOM."<br>".$textoPAC."<br>".$textoCONT);
//echo $textoCOM."<br>".$textoPAC;
echo '' .json_encode($arr1).'';

<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');


$planta = param_texto('planta');
$ct = param_int('CT');
$dia = param_int('dia');
$mes = param_int('mes');
$anyo = param_int('anyo');
$BD="datalogger".$anyo.$mes;


$conn = conectar();
if ($ct==0){
	$sql="select PAC.inverter,PAC.planta,ct,(valor/potencia_instalada) as ratio, nombre, concat(lpad(HOUR(fecha),2,0),':',lpad(MINUTE(fecha),2,0)) as hora, potencia_instalada from ".$BD.".PAC, inverters where PAC.planta='".$planta."' and PAC.planta=inverters.planta and PAC.inverter=inverters.inverter and PAC.linea=inverters.linea and year(fecha)=".$anyo." and month(fecha)=".$mes." and day(fecha)=".$dia." and hour(fecha)>6 order by ct, inverter, fecha";	
}
else{
	$sql="select PAC.inverter,PAC.planta,ct,(valor/potencia_instalada) as ratio, nombre, concat(lpad(HOUR(fecha),2,0),':',lpad(MINUTE(fecha),2,0)) as hora, potencia_instalada from ".$BD.".PAC, inverters where PAC.planta='".$planta."' and inverters.ct=".$ct." and PAC.planta=inverters.planta and PAC.inverter=inverters.inverter and PAC.linea=inverters.linea and year(fecha)=".$anyo." and month(fecha)=".$mes." and day(fecha)=".$dia." and hour(fecha)>6 order by inverter, fecha";

}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($lineaRes = $result->fetch_assoc()) {	
	$hora=$lineaRes["hora"];

	$arr1[]=array("inverter"=>$lineaRes["inverter"],
				  "planta"=>$lineaRes["planta"],
				  "linea"=>$lineaRes["ct"],
				  "nombre"=>$lineaRes["nombre"],
				  'PAC'=>$lineaRes["ratio"],
				  'hora'=>$hora,	
				   );
	}
} else {
    echo "0 results";
}

$sql="select planta, valor, nombre_general, concat(lpad(HOUR(fecha),2,0),':',lpad(MINUTE(fecha),2,0)) as hora from ".$BD.".RAD, plantas where valor>0 and planta='".$planta."' and plantas.nombre_datos='".$planta."' and year(fecha)=".$anyo." and month(fecha)=".$mes." and day(fecha)=".$dia." and hour(fecha)>6  order by fecha";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {			
	$arr1[]=array(
				  "inverter"=>"RAD",
				  "linea"=>"0",
				  'nombre'=>'Radiacion',
				  'PAC'=>$linea["valor"],
				  'hora'=>$linea["hora"],
				   );
				   }
} 




$conn->close(); 
echo '' .json_encode($arr1).'';
<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');


$planta = param_texto('planta');
$ct = param_int('CT');
$mes = param_int('mes');
$anyo = param_int('anyo');
$id = param_lista_enteros('inverter');
$sumado = param_int('diaomes');


//Saco el nombre de la BD
$BD="datalogger".$anyo.$mes;
$conn = conectar();

//todo el mes sumado
if($sumado=="1")
{
	if ($id=="0"){
	$sql="select inverter, nombre, ct,concat(lpad(mes,2,0),'-',anyo) as fecha_formateada, round(sum(maxKDY),2) as maxKDY, round( (sum(maxKDY)/potencia_pico),2) as rendimiento from (select inverters.planta, inverters.inverter, ct, nombre, idinverter,potencia_pico, YEAR(fecha) as anyo,Month(fecha) as mes,dayofmonth(fecha) as dia, max(valor) as maxKDY from ".$BD.".KDY,inverters where KDY.idinverter=inverters.idinverters and inverters.planta='".$planta."' and ct=".$ct." and year(fecha)=".$anyo." and month(fecha)=".$mes."  group by inverter, ct, YEAR(fecha),Month(fecha),dayofmonth(fecha),idinverter order by ct,inverter) as resultado group by anyo,mes, ct, inverter,nombre,resultado.potencia_pico order by nombre;";

	}
	else{
	$sql="select inverter, nombre, ct,concat(lpad(mes,2,0),'-',anyo) as fecha_formateada, round(sum(maxKDY),2) as maxKDY, round( (sum(maxKDY)/potencia_pico),2) as rendimiento from (select inverters.planta, inverters.inverter, ct, nombre, idinverter,potencia_pico, YEAR(fecha) as anyo,Month(fecha) as mes,dayofmonth(fecha) as dia, max(valor) as maxKDY from ".$BD.".KDY,inverters where KDY.idinverter=inverters.idinverters and inverters.planta='".$planta."' and  idinverter in(".$id.") and ct=".$ct." and year(fecha)=".$anyo." and month(fecha)=".$mes."  group by inverter, ct, YEAR(fecha),Month(fecha),dayofmonth(fecha),idinverter order by ct,inverter) as resultado group by anyo,mes, ct, inverter,nombre,resultado.potencia_pico order by nombre;";

	
	}
}
//día a día
else{
	if ($id=="0"){
	$sql="select  inverters.inverter, inverters.linea, nombre, concat(lpad(dayofmonth(fecha),2,0),'-',lpad(Month(fecha),2,0),'-',YEAR(fecha)) as fecha_formateada, max(valor) as maxKDY, round(max(valor)/potencia_pico,2) as rendimiento from ".$BD.".KDY, inverters where inverters.planta=KDY.planta and inverters.linea=KDY.linea and inverters.inverter=KDY.inverter and inverters.planta='".$planta."' and inverters.ct=".$ct." and year(fecha)=".$anyo." and month(fecha)=".$mes." group by concat(lpad(dayofmonth(fecha),2,0),'-',lpad(Month(fecha),2,0),'-',YEAR(fecha)),inverters.linea,inverters.inverter,nombre, potencia_pico order by fecha_formateada,inverters.linea,inverters.inverter;";			
	}
	else{
	$sql="select  inverters.inverter, inverters.linea, nombre, concat(lpad(dayofmonth(fecha),2,0),'-',lpad(Month(fecha),2,0),'-',YEAR(fecha)) as fecha_formateada, max(valor) as maxKDY, round(max(valor)/potencia_pico,2) as rendimiento from ".$BD.".KDY, inverters where inverters.planta=KDY.planta and inverters.linea=KDY.linea and inverters.inverter=KDY.inverter and inverters.planta='".$planta."' and  idinverter in(".$id.") and year(fecha)=".$anyo." and month(fecha)=".$mes." group by concat(lpad(dayofmonth(fecha),2,0),'-',lpad(Month(fecha),2,0),'-',YEAR(fecha)),inverters.linea,inverters.inverter,nombre, potencia_pico order by fecha_formateada,inverters.linea,inverters.inverter;";	
		
	}	
}	

//echo $sql;

$nombre=$planta;

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {	
//	if ($linea["linea"] != "0"){
//	};	
	$arr1[]=array("planta"=>$linea["nombre"],
				  "KDY"=>$linea["maxKDY"],
				  "fecha"=>$linea["fecha_formateada"],
				  "rendimiento"=>$linea["rendimiento"],
				   );
				   }
} else {
    echo "0 results";
}
$conn->close(); 
echo '' .json_encode($arr1).'';
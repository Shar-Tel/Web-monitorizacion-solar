<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');


$planta = param_texto('planta');
$linea = param_int('linea');
$mes = param_int('mes');
$anyo = param_int('anyo');
$sumado = param_int('diaomes');

$conn = conectar();



//Saco el nombre de la BD
$BD="datalogger".$anyo.$mes;
//devuelve el mes sumado
if($sumado=="1")
{
	if ($linea=="0"){
	$sql="select planta, linea,concat(lpad(mes,2,0),'-',anyo) as fecha_formateada, round(sum(maxKDY),2) as totalKDY from (select planta, inverter, linea, YEAR(fecha) as anyo,Month(fecha) as mes,dayofmonth(fecha) as dia, max(valor) as maxKDY from ".$BD.".KDY where planta='".$planta."'  and year(fecha)=".$anyo." and month(fecha)=".$mes."   group by inverter, linea, YEAR(fecha),Month(fecha),dayofmonth(fecha) order by linea,inverter) as resultado group by anyo,mes, linea;";
		
	}
	else{
	$sql="select planta, linea,concat(lpad(mes,2,0),'-',anyo) as fecha_formateada, round(sum(maxKDY),2) as totalKDY from (select planta, inverter, linea, YEAR(fecha) as anyo,Month(fecha) as mes,dayofmonth(fecha) as dia, max(valor) as maxKDY from ".$BD.".KDY where planta='".$planta."' and linea=".$linea." and year(fecha)=".$anyo." and month(fecha)=".$mes."   group by inverter, linea, YEAR(fecha),Month(fecha),dayofmonth(fecha) order by linea,inverter) as resultado group by anyo,mes, linea;";
			
	}
	
}

//devuelve dia a dia
else{
	if ($linea=="0"){
	$sql="select planta, linea,concat(lpad(dia,2,0),'-',lpad(mes,2,0),'-',anyo) as fecha_formateada, round(sum(maxKDY),2) as totalKDY from (select planta, inverter, linea, YEAR(fecha) as anyo,Month(fecha) as mes,dayofmonth(fecha) as dia, max(valor) as maxKDY from ".$BD.".KDY where planta='".$planta."'  and year(fecha)=".$anyo." and month(fecha)=".$mes."  group by inverter, linea, YEAR(fecha),Month(fecha),dayofmonth(fecha) order by linea,inverter) as resultado group by anyo,mes,dia, linea order by anyo,mes,dia, linea;";
		
	}
	else{
	$sql="select planta, linea,concat(lpad(dia,2,0),'-',lpad(mes,2,0),'-',anyo) as fecha_formateada, round(sum(maxKDY),2) as totalKDY from (select planta, inverter, linea, YEAR(fecha) as anyo,Month(fecha) as mes,dayofmonth(fecha) as dia, max(valor) as maxKDY from ".$BD.".KDY where planta='".$planta."' and linea=".$linea." and year(fecha)=".$anyo." and month(fecha)=".$mes."  group by inverter, linea, YEAR(fecha),Month(fecha),dayofmonth(fecha) order by linea,inverter) as resultado group by anyo,mes,dia, linea order by anyo,mes,dia, linea;";	
	}
}

$nombre=$planta;

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {
	if ($linea["linea"] != "0"){
		$nombre=$linea["planta"]." ".$linea["linea"];
	};			
	$arr1[]=array("planta"=>$nombre,
				  "KDY"=>$linea["totalKDY"],
				  "fecha"=>$linea["fecha_formateada"],
				   );
				   }
} else {
    echo "0 results";
}
$conn->close(); 
echo '' .json_encode($arr1).'';
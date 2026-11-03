<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');


$planta = param_texto('planta');
$mes = param_int('mes');
$anyo = param_int('anyo');
$dia = param_int('dia');

$fecha=$anyo."-".$mes."-".$dia;

$conn = conectar();
//Saca todas las plantas
if ($planta=="")
{
	$sql="(SELECT idregistros,inverters.planta,CT,inverters.inverter,nombre, SAL, error,fecha_inicio,fecha_fin FROM datalogger.alarmas inner join inverters on DATE_FORMAT(fecha_inicio, '%Y-%m-%d')<'".$fecha."' and fecha_fin is null and inverters.planta=alarmas.planta and inverters.linea=alarmas.linea and inverters.inverter=alarmas.inverter LEFT JOIN codigos_alarmas on codigo=SAL and tipo_inverter=tipo)";
$sql.=" UNION ";
	$sql.="(SELECT idregistros,inverters.planta,CT,inverters.inverter,nombre, SAL, error,fecha_inicio,fecha_fin FROM datalogger.alarmas inner join inverters on  year(fecha_inicio)=".$anyo." and month(fecha_inicio)=".$mes." and dayofmonth(fecha_inicio)=".$dia." and inverters.planta=alarmas.planta and inverters.linea=alarmas.linea and inverters.inverter=alarmas.inverter LEFT JOIN codigos_alarmas on codigo=SAL and tipo_inverter=tipo)";

	}
else{
		$sql="(SELECT idregistros,inverters.planta,CT,inverters.inverter,nombre, SAL, error,fecha_inicio,fecha_fin FROM datalogger.alarmas inner join inverters on DATE_FORMAT(fecha_inicio, '%Y-%m-%d')<'".$fecha."' and alarmas.planta='".$planta."' and fecha_fin is null and inverters.planta=alarmas.planta and inverters.linea=alarmas.linea and inverters.inverter=alarmas.inverter LEFT JOIN codigos_alarmas on codigo=SAL and tipo_inverter=tipo)";
$sql.=" UNION ";
	$sql.="(SELECT idregistros,inverters.planta,CT,inverters.inverter,nombre, SAL, error,fecha_inicio,fecha_fin FROM datalogger.alarmas inner join inverters on  year(fecha_inicio)=".$anyo." and month(fecha_inicio)=".$mes." and dayofmonth(fecha_inicio)=".$dia." and alarmas.planta='".$planta."' and inverters.planta=alarmas.planta and inverters.linea=alarmas.linea and inverters.inverter=alarmas.inverter LEFT JOIN codigos_alarmas on codigo=SAL and tipo_inverter=tipo)";
}	
//Saca las alarmas con el CT
//Saca las alarmas con CT y los errores que no tienen descripción también salen

//echo $sql;
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // output data of each row
    while($row= $result->fetch_assoc()) {
//	if ($linea["linea"]!=0){$nombrePlanta=$planta." ".$linea["linea"];};	ESTO SERIA CON TODAS LAS LINEAS
	$arr1[]=array('id'=>$row["idregistros"],
	'planta'=>$row["planta"],
		'linea'=>$row["CT"],
	'inverter'=>$row["inverter"],
	'nombre'=>$row["nombre"],
	
						'alarma'=>$row["SAL"]. " - ". $row["error"],
					'fecha_inicio'=>$row["fecha_inicio"],
					'fecha_fin'=>$row["fecha_fin"],
				   );
				   }
} else {
    echo "0 results";
}
$conn->close(); 
echo '' .json_encode($arr1).'';

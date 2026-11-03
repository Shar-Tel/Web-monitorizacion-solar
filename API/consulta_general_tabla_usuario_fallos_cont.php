<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');
include('config.php');



// El usuario se toma de la sesión del servidor, no del parámetro de la URL.
$usuario = (int) usuario_sesion();

$conn = conectar();

//S es el usuario general las saco todoas, si no las que toquen
if (esUsuarioAdmin($usuario)){
$sql="select c1.planta, c1.PAC, c1.KDY, c2.fallos, c3.numero, c1.nombre_general, c4.PAC0, c5.numero_cont, c6.errores, c7.errores_cero from
(SELECT  temporal.planta, nombre_general,  round(sum(PAC/1000),2) as PAC, round(sum(KDY),2) as KDY FROM datalogger.temporal, plantas where nombre_datos=temporal.planta group by temporal.planta, nombre_general order by nombre_general) c1

left join(
SELECT planta, count(*) as fallos FROM datalogger.temporal where fecha < DATE_SUB(NOW(),INTERVAL 15 MINUTE) and inverter<>0 group by planta order by planta ) c2
on c1.planta=c2.planta
left join(
SELECT planta, count(*) as numero FROM inverters  group by planta) c3
on c1.planta=c3.planta 
left join(
SELECT planta, count(*) as PAC0 FROM datalogger.temporal where PAC=0 and inverter<>0 group by planta order by planta ) c4
on c1.planta=c4.planta
left join(
SELECT planta, count(*) as numero_cont FROM CONTADORES  group by planta ) c5
on c1.planta=c5.planta
left join(
SELECT planta, count(*) as errores FROM datalogger.CONTADORES_ERROR where fecha=DATE_FORMAT(CURDATE(),'%d-%m-%Y') and tipo=0 group by planta ) c6
on c1.planta=c6.planta
left join(
SELECT planta, count(*) as errores_cero FROM datalogger.CONTADORES_ERROR where fecha=DATE_FORMAT(CURDATE(),'%d-%m-%Y') and tipo=1 group by planta ) c7
on c1.planta=c7.planta	";	
}
else{
$sql="select c1.planta, c1.PAC, c1.KDY, c2.fallos, c3.numero, c1.nombre_general, c4.PAC0, c5.numero_cont, c6.errores, c7.errores_cero from
(SELECT  temporal.planta, nombre_general,  round(sum(PAC/1000),2) as PAC, round(sum(KDY),2) as KDY FROM datalogger.temporal, plantas, usuarios_plantas where nombre_datos=temporal.planta and usuario=".$usuario." and datalogger.usuarios_plantas.planta=datalogger.plantas.id group by temporal.planta, nombre_general order by nombre_general) c1

left join(
SELECT planta, count(*) as fallos FROM datalogger.temporal where fecha < DATE_SUB(NOW(),INTERVAL 15 MINUTE) and inverter<>0 group by planta order by planta ) c2
on c1.planta=c2.planta
left join(
SELECT planta, count(*) as numero FROM inverters  group by planta) c3
on c1.planta=c3.planta 
left join(
SELECT planta, count(*) as PAC0 FROM datalogger.temporal where PAC=0 and inverter<>0 group by planta order by planta ) c4
on c1.planta=c4.planta
left join(
SELECT planta, count(*) as numero_cont FROM CONTADORES  group by planta ) c5
on c1.planta=c5.planta
left join(
SELECT planta, count(*) as errores FROM datalogger.CONTADORES_ERROR where fecha=DATE_FORMAT(CURDATE(),'%d-%m-%Y') and tipo=0 group by planta ) c6
on c1.planta=c6.planta
left join(
SELECT planta, count(*) as errores_cero FROM datalogger.CONTADORES_ERROR where fecha=DATE_FORMAT(CURDATE(),'%d-%m-%Y') and tipo=1 group by planta ) c7
on c1.planta=c7.planta		";
}



$result = $conn->query($sql);




//				  'hora'=>date("H:i",$linea["hora"]),
if ($result->num_rows > 0) {
    // output data of each row
    while($linea = $result->fetch_assoc()) {	
	$arr1[]=array('planta'=>$linea["planta"],
					'nombre'=>$linea["nombre_general"],
					'PAC'=>$linea["PAC"],
					'KDY'=>$linea["KDY"],
					'num_inverters'=>$linea["numero"],
				//	'fallos'=>$errorRatio." (".strval((!is_null($linea["fallos"]))?$linea["fallos"]:0).")", CAMBIAR ESTA LINEA POR LA SIGUIENTE PARA SACAR LO DEL RATIO
					'fallos'=>strval((!is_null($linea["PAC0"]))?$linea["PAC0"]:0)." (".strval((!is_null($linea["fallos"]))?$linea["fallos"]:0).")",
					'num_contadores'=>strval((!is_null($linea["numero_cont"]))?$linea["numero_cont"]:0),
					'fallos_contadores'=>strval((!is_null($linea["errores_cero"]))?$linea["errores_cero"]:0)." (".strval((!is_null($linea["errores"]))?$linea["errores"]:0).")",
				//	'fallos_contadores'=>strval((!is_null($linea["errores"]))?$linea["errores"]:0),
				   );
				   }
} else {
    echo "0 results";
}
$conn->close(); 
echo '' .json_encode($arr1).'';





//ESTO SACA LOS ERRORES POR DESVIACIÓN DE RATIOS
function sacaErroresRatio()
{
	$anyo="2022";
	$mes="06";
	$dia="01";
	$conn = conectar();
	//Esto saca la media del ratio de los inverters
	$sql="select PAC.inverter,PAC.planta,PAC.linea, avg((valor/potencia_instalada)) as ratio from PAC, inverters where  PAC.planta=inverters.planta and PAC.inverter=inverters.inverter and PAC.linea=inverters.linea and year(fecha)=".$anyo." and month(fecha)=".$mes." and day(fecha)=".$dia." and fallo_com=0 group by PAC.inverter,PAC.planta, PAC.linea";
	//echo $sql;	

	$result = $conn->query($sql);

	$plantasRes=[];
	//Guardamos los resultados en un vector
	if ($result->num_rows > 0) {
		while($lineaRes = $result->fetch_assoc()) {	
		//Voy rellenando un vector con todas las plantas y errores a 0

		//Saco los resultados y los guardo en un vector
		$ratios[]=array("inverter"=>$lineaRes["inverter"],
						"linea"=>$lineaRes["linea"],
					  "planta"=>$lineaRes["planta"],
					  "ratio"=>$lineaRes["ratio"],	
					   );
		//Guardo los valores por plantas			   
		$plantas[$lineaRes["planta"]][]=$lineaRes["ratio"];		   
		}
	} else {
		echo "0 results";
	}
	$conn->close(); 
	//Calculamos las medias de cada planta y las guardamos en otro vector
	//Recorro el vector de plantas
	foreach ($plantas as $clave => $valor)
	{
		//Voy sumando todos los ratios y saco la media
		$media = array_sum($valor)/count($valor);
		$medias[$clave]=$media;
		
	}


	//Recorro el vector de ratios y los comparo con el ratio medio de su planta. Si el resultado difiere un %, lo añado como error
	foreach ($ratios as $inverter)
	{
		$porcentaje=20;
		//Saco la media de la planta de ese inverter
		$mediaPlanta=$medias[$inverter["planta"]];
		//Saco el valor de la media de ese inverter
		$mediaInverter=$inverter["ratio"];
		//Miro si los valores se desvían lo suficiente como para ser error
		$diferencia=($mediaPlanta/$mediaInverter)*100-100;
		if ($diferencia>=$porcentaje)
		{
			$plantasRes[$inverter["planta"]]++;
		//	echo $inverter["planta"]."-".$inverter["inverter"]."-".$inverter["linea"]."-diferencia=".$diferencia."<br />";
		}
	}



	return $plantasRes;
};

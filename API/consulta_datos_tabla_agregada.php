<?php

require_once __DIR__ . '/auth.php';

include('database_connection.php');


$planta = param_texto('planta');
$ct = param_int('CT');
$id = param_lista_enteros('inverter');
$dia = param_int('dia');
$mes = param_int('mes');
$anyo = param_int('anyo');
$BD="datalogger".$anyo.$mes.".";


$datos=array("KDY", "IL1", "IL2","IL3","UL1","UL2","UL3","UDC","UGD");


if ($id==0){
	//Saco los inverters de esa planta y ct
	$sqlINV="select idinverters from inverters where planta='".$planta."' and ct=".$ct;
	//echo $sqlINV;
	$connINV = conectar();
	$resultINV = $connINV->query($sqlINV);
	if ($resultINV->num_rows > 0) {
		// creo una lista con los inverters
		$listaInverters='';
		while($linea = $resultINV->fetch_assoc()) {	
		if ($listaInverters=='')
		{
		$listaInverters=$linea["idinverters"];	
		}
		else{
		$listaInverters=$listaInverters.",".$linea["idinverters"];
		}
					   }		   
	} else {
		echo "0 results";
	}
	$connINV->close();
$id= $listaInverters;
}

//TENGO QUE HACER LAS CONSULTAS POR SEPARADO PORQUE CON LEFT JOIN VA MUY LENTO


$sqlPAC="select PAC.idinverter,valor,nombre, concat(lpad(HOUR(fecha),2,0),':',lpad(MINUTE(fecha),2,0)) as hora from ".$BD."PAC, inverters where PAC.idinverter in (".$id.") and PAC.idinverter=inverters.idinverters  and year(fecha)=".$anyo." and month(fecha)=".$mes." and day(fecha)=".$dia." order by hora,nombre";	



//Creo el objeto inverter y sus funciones asociadas

class Inverter {
  public function __construct($idInverter, $nombre, $hora, $PAC) {
	$this->idInverter = $idInverter;
	$this->nombre = $nombre;
	$this->hora = $hora;
	$this->PAC = $PAC;
	$this->KDY = "-";
	$this->IL1 = "-";
	$this->IL2 = "-";
	$this->IL3 = "-";
	$this->UL1 = "-";
	$this->UL2 = "-";
	$this->UL3 = "-";
	$this->UDC = "-";
	$this->UGD = "-";			
  }
  
 //Función que añade valor a un dato 
 public function guardaDato($dato, $valor){
	 switch ($dato) {
    case "KDY":
		$this->KDY = $valor;
        break;
	case "IL1":
		$this->IL1 = $valor;
        break;
	case "IL2":
		$this->IL2 = $valor;
        break;
	case "IL3":
		$this->IL3 = $valor;
        break;
	case "UL1":
		$this->UL1 = $valor;
        break;
	case "UL2":
		$this->UL2 = $valor;
        break;
	case "UL3":
		$this->UL3 = $valor;
        break;		
	case "UDC":
		$this->UDC = $valor;
        break;		
	case "UGD":
		$this->UGD = $valor;
        break;				
}
 }	 

//Devuelve un vector con los valores en el formato correcto
 public function sacaValores() {
	 return array(
					'PAC'=>$this->PAC,
					'KDY'=>$this->KDY,
					'IL1'=>$this->IL1,
					'IL2'=>$this->IL2,
					'IL3'=>$this->IL3,
					'UL1'=>$this->UL1,
					'UL2'=>$this->UL2,
					'UL3'=>$this->UL3,
					'UDC'=>$this->UDC,
					'UGD'=>$this->UGD,																												  
				  'fecha'=>$this->hora,	
				  'inverter'=>$this->nombre,
				   );
  }
}

//Saco el PAC y creo el array de inverters
$connPAC = conectar();
$resultPAC = $connPAC->query($sqlPAC);
if ($resultPAC->num_rows > 0) {
    // output data of each row
	while($linea = $resultPAC->fetch_assoc()) {			
	$array[]=new Inverter($linea["idinverter"],$linea["nombre"],$linea["hora"],$linea["valor"]);
				   }		   
} else {
    echo "0 results";
}
$connPAC->close(); 

//Saco el resto de datos que hay en el array y lo guardo en su objeto correspondiente
foreach ($datos as $dato) {
	//Creo la consulta que toca
	$sqlDato="select ".$dato.".idinverter,".$dato.".planta,valor, concat(lpad(HOUR(fecha),2,0),':',lpad(MINUTE(fecha),2,0)) as hora from ".$BD.$dato." where ".$dato.".idinverter in (".$id.") and year(fecha)=".$anyo." and month(fecha)=".$mes." and day(fecha)=".$dia;	
//	echo $sqlDato;
	$connDato = conectar();
	$resultDato= $connDato->query($sqlDato);

	if ($resultDato->num_rows > 0) {
		// Recorro las líneas devueltas
		while($linea = $resultDato->fetch_assoc()) {	
			//Recorro el array buscando el inverter al que añadir el valor del dato sacado
			foreach ($array as &$inverter) {
				if ($inverter->idInverter==$linea["idinverter"] and $inverter->hora==$linea["hora"])
				{
					$inverter->guardaDato($dato,$linea["valor"]);
				}
					   }		   
		}
	}
$connDato->close(); 
} 
foreach ($array as $valor) {
    $arr1[]=$valor->sacaValores();
}
echo '' .json_encode($arr1).'';  
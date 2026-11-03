/////////////////////////////////////////
//ESTA ES LA PARTE DEL TAB DE INVERTERS//
/////////////////////////////////////////

function dibujaGraficasInverters(){
  //rellenaTablaInvertersInstantanea();
	  switch (sessionStorage.getItem("btnActivoInverters")){
		//ESTE DE MOMENTO NO SE USA
		// case "Planta":
		// 	$('#tablaInvertersInstantaneos').hide();
		// 	$('#tablaInvertersRAD').hide();
		// 	if (sessionStorage.getItem("btnGraficaInvertersMostrar")=="PAC"){
		// 		dibujaGraficaInvertersPAC();	
		// 	}
		// 	else{
		// 		dibujaGraficaInvertersEnergiaPlanta();			
		// 	}
		// 	rellenaTablaInvertersPlanta();
		// 	break;
		case "Inverters":
			$('#tablaInvertersInstantaneos').hide();
			$('#tablaInvertersPlanta').hide();
			//La energía diaria es un gráfico de barras
			if (sessionStorage.getItem("btnGraficaInvertersMostrar")=="KDY"){
				dibujaGraficaInvertersEnergia();				
			}
			//El resto son gráficos de líneas
			else{		
				dibujaGraficaInvertersInverters();
			}
			if (sessionStorage.getItem("btnGraficaInvertersMostrar")=="RAD"){	
				$('#tablaInvertersAgregada').hide();
				rellenaTablaInvertersRAD();				
			}
			rellenaRadiacionMensual();
			$('#tablaInvertersRAD').hide();
			break;
		}
}



//////////////////////////// AQUI LO DEL INTERVALO /////////////////////

function sacaIntervaloInverters(){
	var fechas = $('#datepickerInverters').val().split(","); 
	fechas=convierteListaFechas(fechas);
	let [menor,mayor] = obtenerMayorMenor(fechas);
	return [menor,mayor];
}

//Añade los inverters a la lista de la tab de inverters
function anyadeInverters(){
	var planta=	sessionStorage.getItem("planta");
	var linea=	sessionStorage.getItem("linea");
//	var $dropdown = $(".selectpicker.datos");
//	var $dropdown = $("#invertersInverters");
    $('#invertersInverters').empty();
	$("#invertersInverters").append('<option value=0 selected="selected">inverters </option>');
	$.getJSON("API/consulta_inverters.php?planta="+planta+"&CT="+linea, function(data) {  
		$.each(data, function(key, value){
			inverter=value.nombre;
		$('#invertersInverters').append('<option value='+value.id+'>'+value.nombre+'</option>'); 
		});	
		$('#invertersInverters').selectpicker('refresh');
		}
		);
}


$("#invertersInverters").change(function(){
//	sessionStorage.setItem("inverter",$(this).find("option:selected").val());
	var anterior=sessionStorage.getItem("inverter");
	//Si los valores de ahora son distintos a los de antes, que salte
	if (anterior!=$("#invertersInverters").val()){
		var valores=$("#invertersInverters").val();
		//Hago esto porque en realidad lo guarda como string, con lo que si tiene un valor 10 da true al buscar 0, por ejemplo
		anterior=anterior.split(",");
		//Si no es solo 0, ver qué hacer
		if(!(valores.includes("0") && valores.length==1)){
			//Si antes no había 0 y ahora sí, quitar los demás
			if(valores.includes("0") && (anterior.indexOf("0") <= -1)) {
				valores=["0"];
			}		
			//Si no, quitar el 0
			else{
				valores=removeItemOnce(valores,"0");
			}
		}
	sessionStorage.setItem("inverter",valores);
	$("#invertersInverters").selectpicker('val', valores);
	$("#invertersInverters").selectpicker('refresh');	
	dibujaGraficasInverters();
	rellenaTablaInvertersAgregada();
	rellenaTablaInvertersDato();
	}
});

function removeItemOnce(arr, value) {
  var index = arr.indexOf(value);
  if (index > -1) {
    arr.splice(index, 1);
  }
  return arr;
}

function contains(arr, element) {
    for (var i = 0; i < arr.length; i++) {
		alert(arr[i]);
        if (arr[i] === element) {
			alert (arr[i]);
			alert (element);
            return true;
        }
    }
    return false;
}

$("#desplegableInverterInformes").change(function(){
	//Si el valor anterior era RAD hay que rellenar la tabla agreagada
	if (sessionStorage.getItem("btnGraficaInvertersMostrar")=="RAD"){
	rellenaTablaInvertersAgregada();
	}
	sessionStorage.setItem("btnGraficaInvertersMostrar",$(this).find("option:selected").val());
	dibujaGraficasInverters();
	rellenaTablaInvertersDato();
});



$( "#btnAnteriorFechaInverters" ).click(function() {
		var fecha = new Date(sacaIntervaloInverters()[1]);
	//	fecha=$('#datepickerInverters').val();
		fecha.setDate(fecha.getDate()-1);
		$("#btnPosteriorFechaInverters").prop('disabled', false);
		$('#datepickerInverters').datepicker("update", fecha);
		dibujaGraficasInverters();
		if (sessionStorage.getItem("btnGraficaInvertersMostrar")!="RAD"){
			rellenaTablaInvertersAgregada();
			rellenaTablaInvertersDato();
		}
//		desactivaBotones();
		
});

$( "#btnPosteriorFechaInverters" ).click(function() {
		var fecha = new Date(sacaIntervaloInverters()[1]);
		var hoy= new Date();
		if (hoy.getDate()==fecha.getDate()){
			$("#btnPosteriorFechaInverters").prop('disabled', true);
		}
		else{
			fecha.setDate(fecha.getDate()+1);
			$('#datepickerInverters').datepicker("update", fecha);
			dibujaGraficasInverters();		
			if (sessionStorage.getItem("btnGraficaInvertersMostrar")!="RAD"){
				rellenaTablaInvertersAgregada();
				rellenaTablaInvertersDato();
			}
		}
//	desactivaBotones();
});



  
function desactivaBotones(){
	$('#btnAnteriorFechaInverters').prop('disabled', true);
	$('#btnPosteriorFechaInverters').prop('disabled', true);
	$('#btnAnteriorInverterInverters').prop('disabled', true);
	$('#btnPosteriorInverterInverters').prop('disabled', true);
	$('#btnAnteriorFechaInformes').prop('disabled', true);
	$('#btnPosteriorFechaInformes').prop('disabled', true);
	$('#btnAnteriorPlantaInformes').prop('disabled', true);
	$('#btnPosteriorPlantaInformes').prop('disabled', true);
	$('#invertersInverters').prop('disabled', true);
$('#invertersInverters').selectpicker('refresh');
	
}

function activaBotones(){
	$('#btnAnteriorFechaInverters').prop('disabled', false);
	$('#btnPosteriorFechaInverters').prop('disabled', false);
	$('#btnAnteriorInverterInverters').prop('disabled', false);
	$('#btnPosteriorInverterInverters').prop('disabled', false);
	$('#btnAnteriorFechaInformes').prop('disabled', false);
	$('#btnPosteriorFechaInformes').prop('disabled', false);
	$('#btnAnteriorPlantaInformes').prop('disabled', false);
	$('#btnPosteriorPlantaInformes').prop('disabled', false);
	$('#invertersInverters').prop('disabled', false);
$('#invertersInverters').selectpicker('refresh');
}




function toDate(selector) {
  var from = $(selector).val().split("/");
  return new Date(from[2], from[1] - 1, from[0]);
}

function toDateM(selector) {
  var from = $(selector).val().split("/");
  return new Date(from[1], from[0] - 1);
}


$('#btnPlanta').on('click', function(){ 
  $(this).addClass('active'); //eventually removeClass of some previous class
  $('#btnInverter').removeClass('active');
  $('#desplegableInvertersInverters').hide();
  sessionStorage.setItem("btnActivoInverters", "Planta");
  dibujaGraficasInverters();
  rellenaTablaInvertersAgregada();
  rellenaTablaInvertersDato();
  //other stuff
});

$('#btnInverter').on('click', function(){ 
  $(this).addClass('active'); //eventually removeClass of some previous class
  $('#btnPlanta').removeClass('active');
  	  $('#desplegableInvertersInverters').show();
  sessionStorage.setItem("btnActivoInverters", "Inverters");
  sessionStorage.setItem("inverter",0);
  dibujaGraficasInverters();
  rellenaTablaInvertersAgregada();
  rellenaTablaInvertersDato();
  //other stuff
});

//ESTO RELLENA LA TABLA INSTANTÁNEA DE LA PANTALLA DE DATOS


// function rellenaTablaInvertersPlanta(){
// 	if (sessionStorage.getItem("tabActiva")=="Inverters"){
// 		var inverter=0;
// 		var planta=	sessionStorage.getItem("planta");
// 		var linea=	sessionStorage.getItem("linea");
// 		var fecha = $('#datepickerInverters').val().split("/");

// 		var mes = fecha[1];
// 		var dia =  fecha[0];
// 		var anyo =  fecha[2];
// 		var sql;
// 		$('#tablaInvertersPlanta').show();
	
//     	$.getJSON(sql, function(datos) {  
// 		$('#tablaInvertersPlanta').bootstrapTable('load', datos);
// 			}
// );	
// 	}
	
// }

//Exporta datos a Excel al apretar el botón
$("#btnInvertersExportar").click(function(){
	var inverter = sessionStorage.getItem("inverter");
	var planta=	sessionStorage.getItem("planta");
	var linea=	sessionStorage.getItem("linea");
	var dato= sessionStorage.getItem("btnGraficaInvertersMostrar");
	var fechas=sacaIntervaloInverters(); //Guardo el menor y mayor del intervalo
	var fechaI=fechas[0];
	var fechaF=fechas[1];
	
	//Según si la fechas son iguales o tengo que crear diferente texto 
	
	var fechaTitulo=fechaI.split("-");
	// Saco el mes
	var mes = fechaTitulo[1];
	// Saco el día
	var dia =  fechaTitulo[2];
	// Saco el año
	var anyo =  fechaTitulo[0];
	var texto= '';
	texto=dia+"/"+mes+"/"+anyo;

	if (fechaI!=fechaF){
		fechaTitulo=fechaF.split("-");
		// Saco el mes
		mes = fechaTitulo[1];
		// Saco el día
		dia =  fechaTitulo[2];
		// Saco el año
		anyo =  fechaTitulo[0];
		texto=texto+ "-" + dia+"/"+mes+"/"+anyo;
	}
	
	var nombre= planta
	var tabla
	if (linea !=0){
		nombre=nombre+"_"+linea};
	if (inverter !=0){
		nombre=nombre+"_inverter_"+inverter};	
	nombre=nombre+"_"+texto;
		switch (dato){
		case "RAD":
				tabla="#tablaInvertersRAD";
				nombre="Radiacion "+nombre;
				break;
		default:
				tabla="#tablaInvertersDato";
				nombre=dato+" inverters "+nombre;
				break;	
	// switch (sessionStorage.getItem("btnActivoInverters")){
		// case "Planta":
				// tabla="#tablaInvertersPlanta";
				// nombre="Planta "+nombre;
				// break;
		// case "Inverters":
				// tabla="#tablaInvertersAgregada";
				// nombre="Inverters "+nombre;
				// break;	
}				
  $(tabla).table2excel({
    // exclude CSS class
    exclude: ".noExl",
    name: "Hoja 1",
    filename: nombre, //do not include extension
    fileext: ".xls" // file extension
  }); 
});

// function rellenaTablaInvertersInverters(){
	// if (sessionStorage.getItem("tabActiva")=="Inverters"){
		// var inverter=sessionStorage.getItem("inverter");
		// var planta=	sessionStorage.getItem("planta");
		// var linea=	sessionStorage.getItem("linea");
		// var dato=sessionStorage.getItem("btnGraficaInvertersMostrar");
		// var fecha = $('#datepickerInverters').val().split("/");

		// var mes = fecha[1];
		// var dia =  fecha[0];
		// var anyo =  fecha[2];
		// var sql;
		
		// $('#tablaInvertersAgregada').hide();
		// if (dato=="KDY"){
			
		// $('#tablaInvertersInverters tr:eq(0) th:eq(2)').text("Acumulado diario (KWh)");			
		// }
		// //Estamos preguntando por la radiación. Saca radiación y temperatura de placa
		// else if (dato=="RAD"){
			// $('#tablaInvertersInverters tr:eq(0) th:eq(2)').text("W/M2");	
			// $('#tablaInvertersInverters tr:eq(0) th:eq(3)').text("grados");	
		// }
		// else{
				// $('#tablaInvertersInverters tr:eq(0) th:eq(2)').text("Valor");	
		// }
    	// $.getJSON(sql, function(datos) {  
		// $('#tablaInvertersInverters').hide();
		// $('#tablaInvertersInverters').bootstrapTable('load', datos);
		// $('#tablaInvertersInverters').show();
			// }
// );	
	// }
	
// }


//RELLENA LA TABLA CON LA RADIACIÓN Y TEMPERATURA DE LA PANTALLA DE INVERTERS
function rellenaTablaInvertersRAD(){
	if (sessionStorage.getItem("tabActiva")=="Inverters"){
		var inverter=sessionStorage.getItem("inverter");
		var planta=	sessionStorage.getItem("planta");
		var linea=	sessionStorage.getItem("linea");
		var dato=sessionStorage.getItem("btnGraficaInvertersMostrar");
		var fechas=sacaIntervaloInverters(); //Guardo el menor y mayor del intervalo de fechas
		var fechaI=fechas[0];
		var fechaF=fechas[1];	
		

		var sql;
		
		$('#tablaInvertersAgregada').hide();
		$('#tablaInvertersRAD tr:eq(0) th:eq(2)').text("W/M2");	
		$('#tablaInvertersRAD tr:eq(0) th:eq(3)').text("grados");	
		sql="API/consulta_datos_tabla_RAD.php?planta="+planta+"&fechaI="+fechaI+"&fechaF="+fechaF;
		
    	$.getJSON(sql, function(datos) {  
		$('#tablaInvertersRAD').hide();
		$('#tablaInvertersRAD').bootstrapTable('load', datos);
		$('#tablaInvertersRAD').show();
			}
);	
	}
	
}

function rellenaTablaInvertersAgregada(){
	if (sessionStorage.getItem("tabActiva")=="Inverters"){
		var inverter=sessionStorage.getItem("inverter");
		var planta=	sessionStorage.getItem("planta");
		var linea=	sessionStorage.getItem("linea");
		var fechas=sacaIntervaloInverters(); //Guardo el menor y mayor del intervalo de fechas
		//Miro si las fechas son iguales. Si no lo son, no hago nada más
		var fechaI=fechas[0];
		var fechaF=fechas[1];
		if (fechaI==fechaF){
			var fecha=fechas[0];	

			fechaSeparada=fecha.split("-");
			// Saco el mes
			mes = fechaSeparada[1];
			// Saco el día
			dia =  fechaSeparada[2];
			// Saco el año
			anyo =  fechaSeparada[0];
			var sql;
			$('#tablaInvertersAgregada').hide();
			sql="API/consulta_datos_tabla_agregada.php?anyo="+anyo+"&dia="+dia+"&mes="+mes+"&planta="+planta+"&CT="+linea+"&inverter="+inverter;
			//$('#tablaInvertersInverters tr:eq(0) th:eq(2)').text("Valor");	
			$.getJSON(sql, function(datos) {  
			$('#tablaInvertersAgregada').bootstrapTable('load', datos);
					$('#tablaInvertersAgregada').show();
				}
	);	
		}
		else{
			$('#tablaInvertersAgregada').hide();
		}

	}
	
}

//Rellena la tabla que muestra un sólo dato de los inverters
function rellenaTablaInvertersDato(){
	if (sessionStorage.getItem("tabActiva")=="Inverters"){
		var inverter=sessionStorage.getItem("inverter");
		var planta=	sessionStorage.getItem("planta");
		var linea=	sessionStorage.getItem("linea");
		var valor= sessionStorage.getItem("btnGraficaInvertersMostrar");
		var fechas=sacaIntervaloInverters(); //Guardo el menor y mayor del intervalo de fechas
		var fechaI=fechas[0];
		var fechaF=fechas[1];
		var sql;
		$('#tablaInvertersDato').hide();
		//La energia diaria saca una consulta distinta al resto de datos
		if (valor=="KDY"){
			sql="API/consulta_inverters_energia_tabla.php?fechaI="+fechaI+"&fechaF="+fechaF+"&planta="+planta+"&CT="+linea+"&inverter="+inverter;		//$('#tablaInvertersInverters tr:eq(0) th:eq(2)').text("Valor");	
 
		}
		else{
			sql="API/consulta_datos_valor_inverters_tabla.php?fechaI="+fechaI+"&fechaF="+fechaF+"&planta="+planta+"&CT="+linea+"&inverter="+inverter+"&valor="+valor;		//$('#tablaInvertersInverters tr:eq(0) th:eq(2)').text("Valor");	
 
		}
	   	$.getJSON(sql, function(datos) {  
		$('#tablaInvertersDato').bootstrapTable('load', datos);
		$('#tablaInvertersDato').show();
			}
);	
	}
	
}

// //CREA LA GRÁFICA DE PAC DE LA PANTALLA DE DATOS
// function dibujaGraficaInvertersPAC(){
// 	var datos=[]
// 	var datasets=[];
// 	var series=[];
// 	var ctx = document.getElementById("chartPotenciaInverters").getContext("2d");
// 	if (typeof window.grafInverters!== 'undefined')
// 	{
// 		window.grafInverters.destroy();
// 	};

	
// 	var planta=	sessionStorage.getItem("planta");
//     var linea=	sessionStorage.getItem("linea");
	
// 	var nombrePlanta=(linea==0 ? planta : planta+" "+linea);
	
// 	var fecha = $('#datepickerInverters').val().split("/");
// 	// Saco el mes
// 	var mes = fecha[1];
// 	// Saco el día
// 	var dia =  fecha[0];
// 	// Saco el año
// 	var anyo =  fecha[2];

// 	series.push(nombrePlanta);
// 	datos[0]=[];
// 	desactivaBotones();
// 	$.getJSON("API/consulta_datos_PAC.php?anyo="+anyo+"&dia="+dia+"&mes="+mes+"&planta="+planta+"&linea="+linea, function(data) {  
// 		$.each(data, function(key, value){
// 				 datos[0].push({"x":value.hora,"y":(value.PAC/1000).toFixed(2)});
// 		});	
		
// 	 for (i in datos)
// 		{
// 			temp={label: series[i],
// 			data:datos[i],
// 			fill: false,
// 			borderColor: colores[i]
// 			};
// 			datasets.push(temp);
// 		}; 

// 	  var timeFormat = 'HH:mm';

// 		var config = {
// 			type:    'line',
// 			data:    {
// 				datasets: datasets
// 			},
// 		   options: { 
// 					   legend: {
// 					  labels: {
// 						  boxWidth: 20
// 					  }
// 				  },
// 				responsive: true,
// 				title:      {
// 					display: true,
// 					text:    "PAC (kW) " + dia+"/"+mes+"/"+anyo
// 				},
				
// 				scales:     {
			
// 					xAxes: [
// 					{
// 						type:       "time",
// 						time:       {
// 							unit:'hour',
// 						  displayFormats: {
// 							hour: 'H'
// 						},
// 						   format: timeFormat,
// 						},
// 						ticks:{
// 					maxTicksLimit: 20,
// 					},
// 						scaleLabel: {
// 							display:     true,
// 							labelString: 'Hora'
// 						}
// 					}],
// 					yAxes: [{
// 						scaleLabel: {
// 							display:     true,
// 							labelString: 'kW'
// 						}
// 					}]
// 				}
// 			}
// 		};
		
// 			if (typeof window.grafInverters!== 'undefined')
// 	{
// 		window.grafInverters.destroy();
// 	};
// 			window.grafInverters = new Chart(ctx, config);
// 			activaBotones();
// 	}
// 	);
// };


//CREA LA GRÁFICA DE INVERTERS DE LA PANTALLA DE DATOS
function dibujaGraficaInvertersInverters(){
	var datos=[]
	var datasets=[];
	var series=[];
	var ctx = document.getElementById("chartPotenciaInverters").getContext("2d");
	if (typeof window.grafInverters!== 'undefined')
	{
		window.grafInverters.destroy();
	};

	
	var planta=	sessionStorage.getItem("planta");
    var linea=	sessionStorage.getItem("linea");
	var inverter= sessionStorage.getItem("inverter");
	var valor= sessionStorage.getItem("btnGraficaInvertersMostrar");
		


	var fechas=sacaIntervaloInverters(); //Guardo el menor y mayor del intervalo de fechas
	var fechaI=fechas[0];
	var fechaF=fechas[1];	
	
	//Según si la fechas son iguales o tengo que crear diferente texto 
	
	var fechaTitulo=fechaI.split("-");
	// Saco el mes
	var mes = fechaTitulo[1];
	// Saco el día
	var dia =  fechaTitulo[2];
	// Saco el año
	var anyo =  fechaTitulo[0];
	
	
	var textoFecha= '';
	

	

	textoFecha=dia+"/"+mes+"/"+anyo;

	if (fechaI!==fechaF){
		fechaTitulo=fechaF.split("-");
		// Saco el mes
		mes = fechaTitulo[1];
		// Saco el día
		dia =  fechaTitulo[2];
		// Saco el año
		anyo =  fechaTitulo[0];
		textoFecha=textoFecha+ "-" + dia+"/"+mes+"/"+anyo;
	}
	
	
	
	var texto= '';
	var texto2='';
	
	switch (valor)
	{
		case 'PAC':
			texto="Potencia AC (kW) " + textoFecha;
			texto2="KW";
				break;
		case 'IL1':
			texto="Corriente Ica L1 " + textoFecha;
			texto2="A";
				break;
		case 'IL2':
			texto="Corriente Ica L2 " + textoFecha;
			texto2="A";
				break;
		case 'IL3':
			texto="Corriente Ica L3 " + textoFecha;
			texto2="A";
				break;
		case 'IDC':
			texto="Corriente ICC " + textoFecha;
			texto2="A";
				break;
		case 'TKK':
			texto="Temperatura unidad de potencia " + textoFecha;
			texto2="ºC";
				break;
		case 'UL1':
			texto="Tensión Uca L1 " + textoFecha;
			texto2="V";
				break;
		case 'UL2':
			texto="Tensión Uca L2 " + textoFecha;
			texto2="V";
				break;
		case 'UL3':
			texto="Tensión Uca L3 " + textoFecha;
			texto2="V";
				break;	
		case 'UDC':
			texto="Tensión Ucc " + textoFecha;
			texto2="V";
				break;	
		case 'UGD':
			texto="Tensión Ugnd " + textoFecha;
			texto2="V";
				break;		
		case 'RAD':
			texto="Radiación solar " + textoFecha;
			texto2="W/M2";
				break;		
		case 'VIE':
			texto="Velocidad del viento " + textoFecha;
			texto2="KM/H";
				break;	
				
	}	
	desactivaBotones();
	if (valor=="RAD")
	{
		peticion="API/consulta_datos_grafica_RAD.php?fechaI="+fechaI+"&fechaF="+fechaF+"&planta="+planta;
	}
	else if (valor=="VIE")
	{
		peticion="API/consulta_datos_grafica_VIE.php?fechaI="+fechaI+"&fechaF="+fechaF+"&planta="+planta;
	}
	else
	{
		peticion="API/consulta_datos_valor_inverters_id.php?fechaI="+fechaI+"&fechaF="+fechaF+"&planta="+planta+"&CT="+linea+"&inverter="+inverter+"&valor="+valor;
	}
	$.getJSON(peticion, function(data) {  
		$.each(data, function(key, value){
			posicion=series.indexOf(value.nombre);
			//si no está el inverter el la serie, lo meto
			if (posicion==-1){
				series.push(value.nombre);
				posicion=series.indexOf(value.nombre);
				datos[posicion]=[];
			}
			//meto el dato en el vector que toca dentro de datos según el inverter.
			if (valor=='PAC'){
			 datos[posicion].push({"x":value.hora,"y":(value.valor/1000).toFixed(2)});
			}
			else
			{
			 datos[posicion].push({"x":value.hora,"y":value.valor});	
			}
		});	
		
	 for (i in datos)
		{
			temp={label: series[i],
			data:datos[i],
			fill: false,
			borderColor: colores[i]
			};
			datasets.push(temp);
		}; 

	  var timeFormat = 'YYYY-MM-DD HH:mm';

		var config = {
			type:    'line',
			data:    {
				datasets: datasets
			},
		   options: {
			   			   legend: {
          labels: {
              boxWidth: 20
          }
      },
				responsive: true,
				title:      {
					display: true,
					text:    texto
				},
				
				scales:     {
			
					xAxes: [
					{
						type:       "time",
						time:       {
							unit:'hour',
						  displayFormats: {
								hour: 'DD-MM-YYYY HH:m'
						  },
						   format: timeFormat,
						},
						ticks:{
								 autoSkip: true,
						maxTicksLimit: 24,
						maxRotation: 90,
						minRotation: 90
						},
						scaleLabel: {
							display:     true,
							labelString: 'Hora'
						}
					}],
					yAxes: [{
						scaleLabel: {
							display:     true,
							labelString: texto2
						}
					}]
				}
			}
		};
			if (typeof window.grafInverters!== 'undefined')
		{
			window.grafInverters.destroy();
		};
			window.grafInverters = new Chart(ctx, config);
			activaBotones();
	});
};



//CREA LA GRÁFICA DE BARRAS DE ENERGÍA DE LA PANTALLA DE DATOS
function dibujaGraficaInvertersEnergia(){
	var datos=[]
	var datasets=[];
	var series=[];

		var ctx = document.getElementById("chartPotenciaInverters").getContext("2d");
	if (typeof window.grafInverters!== 'undefined')
	{
		window.grafInverters.destroy();
	};

	
	var planta=	sessionStorage.getItem("planta");
	var linea= sessionStorage.getItem("linea");
	var inverter= sessionStorage.getItem("inverter");
	
	var fechas=sacaIntervaloInverters(); //Guardo el menor y mayor del intervalo de fechas
	var fechaI=fechas[0];
	var fechaF=fechas[1];		
	//Según si la fechas son iguales o tengo que crear diferente texto 	
	var fechaTitulo=fechaI.split("-");
	// Saco el mes
	var mes = fechaTitulo[1];
	// Saco el día
	var dia =  fechaTitulo[2];
	// Saco el año
	var anyo =  fechaTitulo[0];
	var textoFecha= '';
	textoFecha=dia+"/"+mes+"/"+anyo;
	if (fechaI!==fechaF){
		fechaTitulo=fechaF.split("-");
		// Saco el mes
		mes = fechaTitulo[1];
		// Saco el día
		dia =  fechaTitulo[2];
		// Saco el año
		anyo =  fechaTitulo[0];
		textoFecha=textoFecha+ "-" + dia+"/"+mes+"/"+anyo;
	}

	desactivaBotones();

	$.getJSON("API/consulta_datos_energia_inverters_id.php?planta="+planta+"&CT="+linea+"&inverter="+inverter+"&fechaI="+fechaI+"&fechaF="+fechaF, function(data) {  
    datos=data;
    // Organizar datos para Chart.js
	var dias = [...new Set(datos.map(item => item.fecha))];
	var datasets = [];
		datos.forEach(item => {
            var nombre = item.nombre;
            var kdy = parseFloat(item.KDY);
            var fecha = item.fecha;

            var datasetIndex = datasets.findIndex(ds => ds.label === nombre);
            if (datasetIndex === -1) {
                datasets.push({
                    label: nombre,
					//Genera colores aleatorios, el entero del final es la opacidad	
                  	backgroundColor: 'rgba(' + Math.floor(Math.random() * 256) + ',' + Math.floor(Math.random() * 256) + ',' + Math.floor(Math.random() * 256) + ',1)',
                 //   borderColor: 'rgba(' + Math.floor(Math.random() * 256) + ',' + Math.floor(Math.random() * 256) + ',' + Math.floor(Math.random() * 256) + ',1)',
                    borderWidth: 1,
                    data: []
                });
                datasetIndex = datasets.length - 1;
            }

            var dayIndex = dias.indexOf(fecha);
            if (datasets[datasetIndex].data[dayIndex] === undefined) {
                datasets[datasetIndex].data[dayIndex] = kdy;
            }
        });

		
        // Formatear las fechas con moment.js
        var formattedDates = dias.map(date => moment(date).format('DD-MM-YYYY'));
		// Configuración del gráfico
		var config = {
			type: 'bar',
			data: {
				labels: formattedDates,
				datasets: datasets
			},
			options: {
				title:      {
					display: true,
					text:    "Energía (kWh) " + textoFecha
				},
				scales:     {
			
					yAxes: [{
						scaleLabel: {
							display:     true,
							labelString: "KWh"
						},
    ticks: {
        beginAtZero: true // Asegura que el eje Y comience en 0
    }
					}]
				}
			}
		};
	//Si ya existe el gráfico lo destruyo	
	if (typeof window.grafInverters!== 'undefined')
	{
		window.grafInverters.destroy();
	};
		window.grafInverters = new Chart(ctx, config);
		activaBotones();
	});
};


//Escribe la radiación mensual en la pantalla de datos
function rellenaRadiacionMensual(){
	if (sessionStorage.getItem("tabActiva")=="Inverters"){
		var valor= sessionStorage.getItem("btnGraficaInvertersMostrar");
		if (valor == "RAD"){
			var planta=	sessionStorage.getItem("planta");
			var linea= sessionStorage.getItem("linea");
			//var fecha = $('#datepickerInverters').val().split("/");
			var fechas=sacaIntervaloInverters(); //Guardo el menor y mayor del intervalo de fechas
			var fecha=fechas[1];//Cojo el último mes del intervalo para mostar su radiación
			var fechaTitulo=fecha.split("-");
			// Saco el mes
			var mes = fechaTitulo[1];
			// Saco el año
			var anyo =  fechaTitulo[0];
			var sql;
			var texto="Radiación mensual: ";
			sql="API/consulta_datos_RAD_mensual.php?planta="+planta+"&anyo="+anyo+"&mes="+mes;
			$.getJSON(sql, function(datos) {  
			$.each(datos, function(key, value){
			texto=texto+value.valor;
			});	
			$('#RadiacionMensual').text(texto);
			}
		);	
		$('#RadiacionMensual').show();
		}
		else{$('#RadiacionMensual').hide();}
	}
	
}
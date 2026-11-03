//////////////////////////////////////////
//ESTA ES LA PARTE DEL TAB DE CONTADORES//
//////////////////////////////////////////







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




$( "#btnAnteriorFechaContadores" ).click(function() {
	//	var fecha=new Date(toDate('#datepickerContadores'));
		var fecha = new Date(sacaIntervalo()[1]);
		var ayer= new Date();
		ayer.setDate(ayer.getDate()-1);
	//	fecha=$('#datepickerContadores').val();
		//Compruebo si la fecha es mayor que ayer por culpa del intervalo y entonces la pongo a anteayer
		if (ayer.getDate()<=fecha.getDate())
		{
			fecha.setDate(ayer.getDate()-1);
		}
		else
		{
			fecha.setDate(fecha.getDate()-1);			
		}
		$("#btnPosteriorFechaContadores").prop('disabled', false);
		$('#datepickerContadores').datepicker("update", fecha);
		dibujaGraficasContadores();
		rellenaTablaContadoresContadores();
		rellenaTablaContadoresContadoresHoras();
	//	desactivaBotones();
		
});

$( "#btnPosteriorFechaContadores" ).click(function() {
		//var fecha=new Date(toDate('#datepickerContadores'));
		var fecha = new Date(sacaIntervalo()[1]);
		var ayer= new Date();
		ayer.setDate(ayer.getDate()-1);
		//Compruebo si la fecha es mayor que ayer por culpa del intervalo y entonces la pongo a ayer
		if (ayer.getDate()<fecha.getDate()){
			$("#btnPosteriorFechaContadores").prop('disabled', true);
			fecha.setDate(ayer.getDate());
			$('#datepickerContadores').datepicker("update", fecha);
			dibujaGraficasContadores();		
			rellenaTablaContadoresContadores();
			rellenaTablaContadoresContadoresHoras();
		}
		//Si es igual que ayer desactivo el botón
		else if (ayer.getDate()<=fecha.getDate()){
				$("#btnPosteriorFechaContadores").prop('disabled', true);		
		}
		else{
			fecha.setDate(fecha.getDate()+1);
			$('#datepickerContadores').datepicker("update", fecha);
			dibujaGraficasContadores();		
			rellenaTablaContadoresContadores();
			rellenaTablaContadoresContadoresHoras();

		}

});




  
function desactivaBotones(){
	$('#btnAnteriorFechaContadores').prop('disabled', true);
	$('#btnPosteriorFechaContadores').prop('disabled', true);
	$('#btnAnteriorFechaDatos').prop('disabled', true);
	$('#btnPosteriorFechaDatos').prop('disabled', true);	
	$('#btnAnteriorInverterDatos').prop('disabled', true);
	$('#btnPosteriorInverterDatos').prop('disabled', true);
	$('#btnAnteriorFechaInformes').prop('disabled', true);
	$('#btnPosteriorFechaInformes').prop('disabled', true);
	$('#btnAnteriorPlantaInformes').prop('disabled', true);
	$('#btnPosteriorPlantaInformes').prop('disabled', true);
	$('#invertersDatos').prop('disabled', true);
$('#invertersDatos').selectpicker('refresh');
	
}

function activaBotones(){
	$('#btnAnteriorFechaContadores').prop('disabled', false);
	$('#btnPosteriorFechaContadores').prop('disabled', false);
	$('#btnAnteriorFechaDatos').prop('disabled', false);
	$('#btnPosteriorFechaDatos').prop('disabled', false);
	$('#btnAnteriorInverterDatos').prop('disabled', false);
	$('#btnPosteriorInverterDatos').prop('disabled', false);
	$('#btnAnteriorFechaInformes').prop('disabled', false);
	$('#btnPosteriorFechaInformes').prop('disabled', false);
	$('#btnAnteriorPlantaInformes').prop('disabled', false);
	$('#btnPosteriorPlantaInformes').prop('disabled', false);
	$('#invertersDatos').prop('disabled', false);
$('#invertersDatos').selectpicker('refresh');
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
  $('#desplegableInvertersDatos').hide();
  sessionStorage.setItem("btnActivoContadores", "Planta");
  dibujaGraficasContadores();
  rellenaTablaContadoresContadores();
  rellenaTablaContadoresContadoresHoras();
  //other stuff
});



//Exporta datos a Excel al apretar el botón
$("#btnContadoresExportar").click(function(){
	var inverter = sessionStorage.getItem("inverter");
	var planta=	sessionStorage.getItem("planta");
	var linea=	sessionStorage.getItem("linea");
	//var fecha = diaMenosNormal($('#datepickerContadores').val().split("/"))
	
	var fechas=sacaIntervalo(); //Guardo el menor y mayor del intervalo
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

	if (fechaI!==fechaF){
		fechaTitulo=fechaF.split("-");
		// Saco el mes
		mes = fechaTitulo[1];
		// Saco el día
		dia =  fechaTitulo[2];
		// Saco el año
		anyo =  fechaTitulo[0];
		texto=texto+ "-" + dia+"/"+mes+"/"+anyo;
	}
	
	
	var nombre= "Contadores"+planta
	var tabla
	tabla="#tablaContadoresContadores";
	nombre=nombre+"_"+texto;
			
  $(tabla).table2excel({
    // exclude CSS class
    exclude: ".noExl",
    name: "Hoja 1",
    filename: nombre, //do not include extension
    fileext: ".xls" // file extension
  }); 
});


//Exporta datos por horas a Excel al apretar el botón
$("#btnContadoresHorasExportar").click(function(){
	var inverter = sessionStorage.getItem("inverter");
	var planta=	sessionStorage.getItem("planta");
	var linea=	sessionStorage.getItem("linea");
	//var fecha = diaMenosNormal($('#datepickerContadores').val().split("/"))
	
	var fechas=sacaIntervalo(); //Guardo el menor y mayor del intervalo
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

	if (fechaI!==fechaF){
		fechaTitulo=fechaF.split("-");
		// Saco el mes
		mes = fechaTitulo[1];
		// Saco el día
		dia =  fechaTitulo[2];
		// Saco el año
		anyo =  fechaTitulo[0];
		texto=texto+ "-" + dia+"/"+mes+"/"+anyo;
	}
	
	
	var nombre= "Contadores por horas "+planta
	var tabla
	tabla="#tablaContadoresContadoresHoras";
	nombre=nombre+"_"+texto;
			
  $(tabla).table2excel({
    // exclude CSS class
    exclude: ".noExl",
    name: "Hoja 1",
    filename: nombre, //do not include extension
    fileext: ".xls" // file extension
  }); 
});


//Resta un día a la fecha pasada y la devuelve con el año antes
function diaMenos(fecha)
{
	var mes = fecha[1];
	var dia =  fecha[0];
	var anyo =  fecha[2];
	fecha=fecha[2]+"-"+fecha[1]+"-"+fecha[0];
	var fechaDate= new Date(fecha);
	fechaDate.setDate(fechaDate.getDate()-1);
	var MyDateString
	MyDateString = fechaDate.getFullYear() + '-'
		 + ('0' + (fechaDate.getMonth()+1)).slice(-2) + '-'
		 + ('0' + (fechaDate.getDate())).slice(-2);
	return MyDateString;	 
	
}
//Resta un día a la fecha pasada y la devuelve con el día antes
function diaMenosNormal(fecha)
{
	var mes = fecha[1];
	var dia =  fecha[0];
	var anyo =  fecha[2];
	fecha=fecha[2]+"-"+fecha[1]+"-"+fecha[0];
	var fechaDate= new Date(fecha);
	fechaDate.setDate(fechaDate.getDate()-1);
	var MyDateString
	MyDateString =('0' + (fechaDate.getDate())).slice(-2) + '-'+ ('0' + (fechaDate.getMonth()+1)).slice(-2) + '-'+ fechaDate.getFullYear();
	return MyDateString;	 
	
}

function rellenaTablaContadoresContadores(){
	if (sessionStorage.getItem("tabActiva")=="Contadores"){
		var planta=	sessionStorage.getItem("planta");
		var contador= sessionStorage.getItem("contador");
		var fechas=sacaIntervalo(); //Guardo el menor y mayor del intervalo
		var fechaI=fechas[0];
		var fechaF=fechas[1];
		var sql;
		$('#tablaContadoresContadores').hide();
		sql="API/consulta_datos_tabla_contadores_intervalo_multi.php?fechaI="+fechaI+"&fechaF="+fechaF+"&planta="+planta+"&numeros="+contador;
    	$.getJSON(sql, function(datos) {  
		$('#tablaContadoresContadores').bootstrapTable('load', datos);
				$('#tablaContadoresContadores').show();
			}
);	
	}
	
}


function rellenaTablaContadoresContadoresHoras(){
	if (sessionStorage.getItem("tabActiva")=="Contadores"){
		var planta=	sessionStorage.getItem("planta");
		var contador= sessionStorage.getItem("contador");
		var fechas=sacaIntervalo(); //Guardo el menor y mayor del intervalo
		var fechaI=fechas[0];
		var fechaF=fechas[1];
		var sql;
		$('#tablaContadoresContadoresHoras').hide();
		sql="API/consulta_datos_tabla_contadores_horas_multi.php?fechaI="+fechaI+"&fechaF="+fechaF+"&planta="+planta+"&numeros="+contador;
		//$('#tablaDatosInverters tr:eq(0) th:eq(2)').text("Valor");	
    	$.getJSON(sql, function(datos) {  
		$('#tablaContadoresContadoresHoras').bootstrapTable('load', datos);
				$('#tablaContadoresContadoresHoras').show();
			}
);	
	}
	
}

//CREA LA GRÁFICA DE CONTADORES
function dibujaGraficasContadores(){
	if (sessionStorage.getItem("tabActiva")=="Contadores"){
		var datos=[]
		var datasets=[];
		var series=[];
		var ctx = document.getElementById("chartContadorContadores").getContext("2d");
		if (typeof window.grafContadoresContador!== 'undefined')
		{
			window.grafContadoresContador.destroy();
		};

		
		var planta=	sessionStorage.getItem("planta");
		var linea=	sessionStorage.getItem("linea");
		var contador= sessionStorage.getItem("contador");
		var valor= sessionStorage.getItem("btnGraficaContadoresMostrar");
			
		
		
		var fechas=sacaIntervalo(); //Guardo el menor y mayor del intervalo
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
		var texto2='';
		

		

		texto="Activo E (Unidad) " + dia+"/"+mes+"/"+anyo;
		texto2="Unidad";

		if (fechaI!==fechaF){
			fechaTitulo=fechaF.split("-");
			// Saco el mes
			mes = fechaTitulo[1];
			// Saco el día
			dia =  fechaTitulo[2];
			// Saco el año
			anyo =  fechaTitulo[0];
			texto=texto+ "-" + dia+"/"+mes+"/"+anyo;
		}
		
	//	desactivaBotones();


		peticion="API/consulta_datos_valor_contadores_intervalo_multi.php?fechaI="+fechaI+"&fechaF="+fechaF+"&planta="+planta+"&numeros="+contador;

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
				 datos[posicion].push({"x":value.hora,"y":value.valor});	
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
			
				if (typeof window.grafContadoresContador!== 'undefined')
		{
			window.grafContadoresContador.destroy();
		};
				window.grafContadoresContador = new Chart(ctx, config);
				activaBotones();
		});
	}
};


//////////////////////////// AQUI LO DEL INTERVALO /////////////////////

function sacaIntervalo(){
		var fechas = $('#datepickerContadores').val().split(","); 
		fechas=convierteListaFechas(fechas);
		let [menor,mayor] = obtenerMayorMenor(fechas);
		return [menor,mayor];
}


function convierteListaFechas(lista) {
	let resultado=[];
  for (let i = 0; i < lista.length; i++) {
	var fecha=lista[i].split("/");
	resultado.push(fecha);
  }
  return resultado;
}


//Resta un día a la fecha pasada
function diaGuiones(fecha)
{
	var mes = fecha[1];
	var dia =  fecha[0];
	var anyo =  fecha[2];
	fecha=fecha[2]+"-"+fecha[1]+"-"+fecha[0];
	var fechaDate= new Date(fecha);
	fechaDate.setDate(fechaDate.getDate());
	var MyDateString
	MyDateString = fechaDate.getFullYear() + '-'
		 + ('0' + (fechaDate.getMonth()+1)).slice(-2) + '-'
		 + ('0' + (fechaDate.getDate())).slice(-2);
	return MyDateString;	 
	
}


//Compara dos fechas, devuelve true si la primera es mayor que la segunda
function compararFechas(fecha1, fecha2) {
  // Convertir las fechas a objetos Date
  const fechaObj1 = new Date(fecha1[2]+"-"+fecha1[1]+"-"+fecha1[0]);
  const fechaObj2 = new Date(fecha2[2]+"-"+fecha2[1]+"-"+fecha2[0]);

  // Comparar las fechas
  if (fechaObj1 > fechaObj2) {
    return true;
  } else if (fechaObj1 <= fechaObj2) {
    return false;
  } 
}


//Devuelve el valor mayor y menor 
function obtenerMayorMenor(lista) {
  let mayor = lista[0];
  let menor = lista[0];
  for (let i = 1; i < lista.length; i++) {
    if (compararFechas(lista[i],mayor)) {
      mayor = lista[i];
    }
    if (compararFechas(menor,lista[i])) {
      menor = lista[i];
    }
  }
  
  //SI EL MAYOR Y EL MENOR SON IGUALES DEBERÍA DEVOLVER EL MISMO
  if (mayor===menor){
	  return [diaGuiones(mayor),diaGuiones(mayor)]
  }
  //¿Debería no restar un día en este caso?
  return [diaGuiones(menor),diaGuiones(mayor)];
}


//ESTO ES PARA RELLENAR EL DESPLEGABLE DE CONTADORES
//Añade los contadores al desplegable de la tab de contadores
function anyadeContadores(){
	var planta=	sessionStorage.getItem("planta");
    $('#contadoresContadores').empty();
	$("#contadoresContadores").append('<option value=0 selected="selected">contadores </option>');
	$.getJSON("API/consulta_contadores.php?planta="+planta, function(data) {  
		$.each(data, function(key, value){
			contador=value.nombre;
		$('#contadoresContadores').append('<option value='+value.numero+'>'+value.nombre+'</option>'); 
		});	
		$('#contadoresContadores').selectpicker('refresh');
		}
		);
}


$("#contadoresContadores").change(function(){
	var anterior=sessionStorage.getItem("contador");
	//Si los valores de ahora son distintos a los de antes, que salte
	if (anterior!=$("#contadoresContadores").val()){
		var valores=$("#contadoresContadores").val();
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
	sessionStorage.setItem("contador",valores);
	$("#contadoresContadores").selectpicker('val', valores);
	$("#contadoresContadores").selectpicker('refresh');	
	dibujaGraficasContadores();
	rellenaTablaContadoresContadores();
	rellenaTablaContadoresContadoresHoras();
	}
});


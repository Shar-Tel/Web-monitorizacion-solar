/////////////////////////////////////////
//ESTA ES LA PARTE DE LA PANTALLA GENERAL
/////////////////////////////////////////

function rellenaTablaPrincipalResumen(){
	if((sessionStorage.getItem("tabActiva")=="General") && (sessionStorage.getItem("planta")=="")){
		var usuario=sessionStorage.getItem("usuario");
		$('#tablaPrincipalResumen').show();
		sql="API/consulta_general_tabla_usuario_fallos_cont.php?usuario="+usuario;	
		
		$('#tablaPrincipalResumen').hide();
    	$.getJSON(sql, function(datos) {  
		$('#tablaPrincipalResumen').bootstrapTable('load', datos);
		$('#tablaPrincipalResumen').show();
			}
);	
	}
	
}

//CREA LA GRÁFICA DE PAC DE LA PANTALLA GENERAL
function dibujaGraficaGeneralPAC(){
	var datos=[]
	var datasets=[];
	var series=[];
		var ctx = document.getElementById("chartPotenciaInverterHoy").getContext("2d");
	if (typeof window.grafGeneralPAC!== 'undefined')
	{
		window.grafGeneralPAC.destroy();
	};

	
	var planta=	sessionStorage.getItem("planta");
    var linea=	sessionStorage.getItem("linea");
		
	
	var date = new Date();
	// Saco el mes
	var mes = ("0" + (date.getMonth() + 1)).slice(-2);
	// Saco el día
	var dia =  ("0" + date.getDate()).slice(-2);
	// Saco el año
	var anyo =  date.getFullYear();

	$.getJSON("/API/consulta_general_Ratio.php?anyo="+anyo+"&dia="+dia+"&mes="+mes+"&planta="+planta+"&CT="+linea, function(data) {  
		$.each(data, function(key, value){
				//si es por linea o por inverter
			posicion=series.indexOf(value.nombre);
			//si no está el inverter el la serie, lo meto
			if (posicion==-1){
				series.push(value.nombre);
				posicion=series.indexOf(value.nombre);
				datos[posicion]=[];
			}

			//meto el dato en el vector que toca dentro de datos según el inverter.
			 datos[posicion].push({"x":value.hora,"y":(value.PAC/1).toFixed(2)});
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

	  var timeFormat = 'HH:mm';

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
					text:    "Ratio (kW/KWp) " + dia+"/"+mes+"/"+anyo
				},
				
				scales:     {
			
					xAxes: [
					{
						type:       "time",
						time:       {
							unit:'hour',
						  displayFormats: {
							hour: 'H'
						},
						   format: timeFormat,
						},
						ticks:{
					maxTicksLimit: 20,
					},
						scaleLabel: {
							display:     true,
							labelString: 'Hora'
						}
					}],
					yAxes: [{
						scaleLabel: {
							display:     true,
							labelString: 'Ratio'
						}
					}]
				}
			}
		};
			if (typeof window.grafGeneralPAC!== 'undefined')
	{
		window.grafGeneralPAC.destroy();
	};
			window.grafGeneralPAC = new Chart(ctx, config);
	});
};


function rellenaTablaGeneralInstantanea(){
	if (sessionStorage.getItem("tabActiva")=="General"){
		var inverter=0;
		var planta=	sessionStorage.getItem("planta");
		var linea=	sessionStorage.getItem("linea");
		var fecha = $('#datepickerInverters').val().split("/");
		// Saco el mes
//		var mes = fecha[1];
		// Saco el año
	//	var anyo =  fecha[2];
		var sql;
	//	switch (sessionStorage.getItem("btnActivoDatos")){
		//	case "Planta":
//		break;
	//		case "Inverter":
				sql="API/consulta_datos_tabla_instantanea.php?planta="+planta+"&CT="+linea+"&inverter="+inverter;
		//		break;				
		//	}
					
		$('#tablaGeneralInstantaneos').hide();	
		$.getJSON(sql, function(datos) { 
		$('#tablaGeneralInstantaneos').bootstrapTable('load', datos);
		$('#tablaGeneralInstantaneos').show();
			}
);	
	}
	
}

//Escribe los inverters que están en fallo COM
function rellenaGeneralFallosCom(){
	if (sessionStorage.getItem("tabActiva")=="General"){
		var inverter=0;
		var planta=	sessionStorage.getItem("planta");
		var linea=	sessionStorage.getItem("linea");
		var sql;
		var coma="";
		var texto="Inverters sin comunicación: ";
		sql="API/consulta_general_fallos_com.php?planta="+planta+"&CT="+linea;
		$('#CuadroFallosCOM').hide();
		$.getJSON(sql, function(datos) {  
		$.each(datos, function(key, value){
		texto=texto+coma+value.nombre;
		coma=", "
		});	
		$('#CuadroFallosCOM').text(texto);
		if (planta != "")
		{
			$('#CuadroFallosCOM').show();
		}		
			}
);	
	}
	
}


//Escribe los inverters que están en fallo COM y PAC
function rellenaGeneralFallosComyPAC(){
	if (sessionStorage.getItem("tabActiva")=="General"){
		var inverter=0;
		var planta=	sessionStorage.getItem("planta");
		var linea=	sessionStorage.getItem("linea");
		var sql;
		var texto="Inverters sin comunicación: ";
		sql="API/consulta_general_fallos_com_PAC_CONT.php?planta="+planta+"&CT="+linea;
		$('#CuadroFallosCOM').hide();
		$.getJSON(sql, function(datos) {  
		$.each(datos, function(key, value){
		texto=value.fallos;
		});	
		$('#CuadroFallosCOM').html(texto);
		if (planta != "")
		{
			$('#CuadroFallosCOM').show();
		}		
			}
);	
	}
	
}

//Dibuja las gráficas y rellena las tablas de la tab general
function dibujaGraficasGeneral(){
	  dibujaGraficaGeneralPAC();
	//  dibujaGraficaGeneralEnergia();
	  rellenaTablaGeneralInstantanea();
	  rellenaGeneralFallosComyPAC();
}


//Hago click en la tabla y pone la planta seleccionada (FALTA  VER SI VA POR PLANTA COMPLETA O POR PLANTA Y CT)
 $('#tablaPrincipalResumen').bootstrapTable ({
	 onClickRow: function(row){
		 
	 valor=row.planta;
	 
	 $('#desplegablePlantas').val(valor);
	$('.selectpicker').selectpicker('refresh') 
	cambiaPlanta(valor);}

    } );



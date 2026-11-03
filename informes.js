///////////////////////////////////////
//ESTA ES LA PARTE DEL TAB DE INFORMES
//////////////////////////////////////


function dibujaGraficasInformes(){
			if ($('#diasMesInformes').find("option:selected").val()=="0")
		{
			dibujaGraficaInformesEnergiaDiario();				
		}
		else
		{
			dibujaGraficaInformesEnergiaMes();			
		}
	
		rellenaTablaInformesEnergia();
}

//Añade los inverters a la lista de la tab de informes
function anyadeInvertersInformes(){
	var planta=	sessionStorage.getItem("planta");
	var linea=	sessionStorage.getItem("linea");
//	var $dropdown = $(".selectpicker.datos");
//	var $dropdown = $("#invertersDatos");
    $('#invertersInformes').empty();
	$("#invertersInformes").append('<option value=0 selected="selected">inverters </option>');
	$.getJSON("API/consulta_inverters.php?planta="+planta+"&CT="+linea, function(data) {  
		$.each(data, function(key, value){
			inverter=value.nombre;
		$('#invertersInformes').append('<option value='+value.id+'>'+value.nombre+'</option>'); 
		});	
		$('#invertersInformes').selectpicker('refresh');
		}
		);
}

$("#invertersInformes").change(function(){
//	sessionStorage.setItem("inverter",$(this).find("option:selected").val());
	var anterior=sessionStorage.getItem("inverter");
	//Si los valores de ahora son distintos a los de antes, que salte
	if (anterior!=$("#invertersInformes").val()){
		var valores=$("#invertersInformes").val();
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
	$("#invertersInformes").selectpicker('val', valores);
	$("#invertersInformes").selectpicker('refresh');	
	dibujaGraficasInformes();	
	}
});

//Añade las plantas a la lista de la tab de informes HAY QUE CAMBIARLO
function anyadeLineas(){
	var planta=	sessionStorage.getItem("planta");
	var $dropdown = $("#lineasInformes");
    $dropdown.empty();
	$dropdown.append('<option value=0 selected="selected">planta </option>');
	$.getJSON("API/consulta_lineas.php?planta="+planta, function(data) {  
		$.each(data, function(key, value){
			if (value.linea != 0){
			linea="Linea "+value.linea;
		$dropdown.append('<option value='+value.linea+'>'+linea+'</option>'); 
			}
		});	
		$('#lineasInformes').prop('selectedIndex',sessionStorage.getItem("lineaInformes")); 
		}
		);
}

$("#desplegableLineasInformes").change(function(){
	sessionStorage.setItem("lineaInformes",$(this).find("option:selected").val());
	dibujaGraficasInformes();
});

$("#diasMesInformes").change(function(){
	dibujaGraficasInformes();
});

$( "#btnAnteriorLineaInformes" ).click(function() {
	if($($('#lineasInformes option:selected').prev()).length){
		$('#lineasInformes').val($('#lineasInformes option:selected').prev().val());
		sessionStorage.setItem("lineaInformes",$('#lineasInformes').val());
		dibujaGraficasInformes();
	}
});

$( "#btnPosteriorLineaInformes" ).click(function() {
if($($('#lineasInformes option:selected').next()).length){
		$('#lineasInformes').val($('#lineasInformes option:selected').next().val());
		sessionStorage.setItem("lineaInformes",$('#lineasInformes').val());
		dibujaGraficasInformes();
	}
});

$( "#btnAnteriorFechaInformes" ).click(function() {
		var fecha=new Date(toDateM('#datepickerInformes'));
	//	fecha=$('#datepickerDatos').val();
		fecha.setMonth(fecha.getMonth()-1);
		$('#datepickerInformes').datepicker("update", fecha);
		dibujaGraficasInformes();
	//	desactivaBotones();
		
});

$( "#btnPosteriorFechaInformes" ).click(function() {
		var fecha=new Date(toDateM('#datepickerInformes'));
		var hoy= new Date();
		if (hoy.getMonth()==fecha.getMonth()){
			$("#btnPosteriorFechaInformes").prop('disabled', true);
		}
		else{
			fecha.setMonth(fecha.getMonth()+1);
			$('#datepickerInformes').datepicker("update", fecha);
			dibujaGraficasInformes();			
		}

});


$( "#btnEnergiaInformes" ).click(function() {
		$(this).addClass('active'); //eventually removeClass of some previous class
		$('#btnPerformanceInformes').removeClass('active');
		sessionStorage.setItem("btnGraficaInformesMostrar","energia");
		dibujaGraficasInformes();
});

$( "#btnPerformanceInformes" ).click(function() {
		$(this).addClass('active'); //eventually removeClass of some previous class
		$('#btnEnergiaInformes').removeClass('active');
		sessionStorage.setItem("btnGraficaInformesMostrar","performance");
		dibujaGraficasInformes();
});



//CREA LA GRÁFICA DE  DE LA PANTALLA DE INFORMES
function dibujaGraficaInformesEnergiaDiario(){
	var datos=[]
	var datasets=[];
	var series=[];
	var ctx = document.getElementById("chartInformes").getContext("2d");
	
	if (typeof window.grafInformes!== 'undefined')
	{
		window.grafInformes.destroy();
	};

	
	var planta=	sessionStorage.getItem("planta");
    var linea=	sessionStorage.getItem("lineaInformes");
		
	
	var fecha = $('#datepickerInformes').val().split("/");
	// Saco el mes
	var mes = fecha[0];
	// Saco el día
	//var dia =  fecha[0];
	// Saco el año
	var anyo =  fecha[1];
	desactivaBotones();
	$.getJSON("API/consulta_informes_energia.php?anyo="+anyo+"&mes="+mes+"&planta="+planta+"&linea="+linea+"&diaomes=0", function(data) {  
		$.each(data, function(key, value){
			posicion=series.indexOf(value.planta);
			//si no está el inverter el la serie, lo meto
			if (posicion==-1){
				series.push(value.planta);
				posicion=series.indexOf(value.planta);
				datos[posicion]=[];
			}
			//meto el dato en el vector que toca dentro de datos según el inverter.
			 datos[posicion].push({"x":value.fecha,"y":value.KDY});
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
				responsive: true,
				title:      {
					display: true,
					text:    "Energía (kWh) " + mes+"/"+anyo
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
							labelString: 'Día'
						}
					}],
					yAxes: [{
						scaleLabel: {
							display:     true,
							labelString: 'kW'
						}
					}]
				}
			}
		};
		
		
			window.grafInformes = new Chart(ctx, config);

	});
				activaBotones();
};


//CREA LA GRÁFICA DE BARRAS DE ENERGÍA DE LA PANTALLA DE INFORMES
function dibujaGraficaInformesEnergiaMes(){
	var datos=[]
	var datasets=[];
	var series=[];

	var ctx = document.getElementById("chartInformes").getContext("2d");
	
	if (typeof window.grafInformes!== 'undefined')
	{
		window.grafInformes.destroy();
	};

	
	var planta=	sessionStorage.getItem("planta");
    var linea=	sessionStorage.getItem("lineaInformes");
		
	
	var fecha = $('#datepickerInformes').val().split("/");
	// Saco el mes
	var mes = fecha[0];
	// Saco el día
	//var dia =  fecha[0];
	// Saco el año
	var anyo =  fecha[1];
	desactivaBotones();

$.getJSON("API/consulta_informes_energia.php?anyo="+anyo+"&mes="+mes+"&planta="+planta+"&linea="+linea+"&diaomes=1", function(data) {  
			$.each(data, function(key, value){
		 posicion=series.indexOf(value.planta);
        //si no está el inverter el la serie, lo meto
        if (posicion==-1){
				series.push(value.planta);
				posicion=series.indexOf(value.planta);
            datos[posicion]=[];
        }
        //meto el dato en el vector que toca dentro de datos según el inverter.
         datos[posicion].push(value.KDY);
		});	
		
	 for (i in datos)
		{
			temp={label: series[i],
			data:datos[i],
			backgroundColor: colores[i],
			fill: false,

			};
			datasets.push(temp);
		}; 

	//  var timeFormat = 'HH:mm';

		var config = {
			type:    'bar',
			data:    {
			//	labels: series,
				datasets: datasets
			},
		   options: {
				responsive: true,
				title:      {
					display: true,
					text:    "Energía (kW) " + mes+"/"+anyo
				},
				
				scales:     {
			
				}
			}
		};
			window.grafInformes = new Chart(ctx, config);
			activaBotones();
	});
};

function rellenaTablaInformesEnergia(){
	if (sessionStorage.getItem("tabActiva")=="Informes"){
		var planta=	sessionStorage.getItem("planta");
		var linea=	sessionStorage.getItem("lineaInformes");
		var inverter= sessionStorage.getItem("inverter");
		var fecha = $('#datepickerInformes').val().split("/");
		var diaomes =$('#diasMesInformes').find("option:selected").val();

		var mes = fecha[0];
		var anyo =  fecha[1];
		var sql;
		$('#tablaInformesEnergia').show();
		sql="API/consulta_informes_energia_tabla.php?anyo="+anyo+"&mes="+mes+"&planta="+planta+"&CT="+linea+"&inverter="+inverter+"&diaomes="+diaomes;			
    	$.getJSON(sql, function(datos) {  
		$('#tablaInformesEnergia').bootstrapTable('load', datos);
			}
);	
	}
	
}

//Exporta datos a Excel al apretar el botón
$("#btnInformesExportar").click(function(){
	var planta=	sessionStorage.getItem("planta");
	var linea=	sessionStorage.getItem("lineaInformes");
	var fecha = $('#datepickerInformes').val()
	var nombre= planta
	var tabla
	if (linea !=0){
		nombre=nombre+"_"+linea};	
	nombre=nombre+"_"+fecha;
	switch (sessionStorage.getItem("btnGraficaInformesMostrar")){
		case "energia":
				tabla="#tablaInformesEnergia";
				nombre="Planta "+nombre;
				break;
}				
  $(tabla).table2excel({
    // exclude CSS class
    exclude: ".noExl",
    name: "Hoja 1",
    filename: nombre, //do not include extension
    fileext: ".xls" // file extension
  }); 
});
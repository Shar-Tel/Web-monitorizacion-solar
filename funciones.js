
feather.replace();

//Se ejectua cuando cambia la tab activa
$('.nav-tabs2 a').on('shown.bs.tab', function(event){
  var x = $(event.target).text();         
  sessionStorage.setItem("tabActiva", x); //Guardo la tab activa
  switch (x){
	case "Inverters":
	  $('#tablaPrincipalResumen').hide();
	  $('#btnPlanta').removeClass('active');
	  $('#btnInverter').addClass('active');
	  sessionStorage.setItem("btnActivoInverters", "Inverters");
	  sessionStorage.setItem("btnGraficaInvertersMostrar","PAC");
	  dibujaGraficasInverters();
	  rellenaTablaInvertersAgregada();
	  rellenaTablaInvertersDato();
	  break;
    case "Contadores":
        $('#tablaPrincipalResumen').hide();
        $('#btnPlanta').removeClass('active');
        $('#btnInverter').addClass('active');
        sessionStorage.setItem("btnActivoDatos", "Inverters");
        sessionStorage.setItem("btnGraficaDatosMostrar","PAC");
		dibujaGraficasContadores();
        rellenaTablaContadoresContadores();
		rellenaTablaContadoresContadoresHoras();
        break;
	case "General":
	  rellenaTablaPrincipalResumen();
	  dibujaGraficasGeneral();	
	  break;
	case "Lecturas instantáneas":
		$('#tablaPrincipalResumen').hide();
		//Debería borrar el dato que hay actualmente
		borrarLecturaInstant();
		rellenaLecturaInstant();
		break;							   
	case "Informes":
		sessionStorage.setItem("btnGraficaInformesMostrar","energia");
		$('#tablaPrincipalResumen').hide();
		$('#btnEnergiaInformes').addClass('active');
		$('#btnPerformanceInformes').removeClass('active');
		$('#lineasInformes').prop('selectedIndex',sessionStorage.getItem("lineaInformes")); 
		$('#diasMesInformes').prop('selectedIndex',0); 
		dibujaGraficasInformes();
		break;
	case "Alarmas":
				$('#tablaPrincipalResumen').hide();
	  rellenaTablaAlarmas();	
	  break;	
		
  }
  
 // var y = $(event.relatedTarget).text();  // previous tab
   //     console.log(y);
});


/////////////////////////////////
//ESTO DIBUJA TODAS LAS GRÁFICAS
////////////////////////////////
function dibujaGraficas(){
  switch (sessionStorage.getItem("tabActiva")){
	case "General":
		dibujaGraficasGeneral();
	  break;
	case "Inverters":
		dibujaGraficasInverters();
    case "Contadores":
        dibujaGraficasContadores();
	  break;
	case "Informes":
		dibujaGraficasInformes();
		break;	
	case "Alarmas":
	    rellenaTablaAlarmas();
		break;	
  }
}


setInterval("rellenaTablaPrincipalResumen();",30000);


//Cosas que hacer cuando carga la página
window.onload = function () {
colores=creaColores();
//rellenaDatosMenuLateral();
anyadePlantasDesplegable();
$('#CuadroFallosCOM').hide();
$('#tablaGeneralInstantaneos').hide();
$('#datepickerInverters').datepicker("update", new Date());
var fecha=new Date();
fecha.setDate(fecha.getDate()-1);
$('#datepickerContadores').datepicker("update", fecha);
$('#datepickerInformes').datepicker("update", new Date());
$('#datepickerAlarmas').datepicker("update", new Date());
sessionStorage.setItem("tabActiva", "General");
  sessionStorage.setItem("planta", "");
  sessionStorage.setItem("linea", 0);
  rellenaTablaPrincipalResumen();
if (!sessionStorage.getItem("usuario")){
    $("*").empty(); 
  };
  
//Elimina la pestaña de petición de datos a los usuarios sin permiso
if (!puedePedirLecturaInstantanea()){
	$('#instantContadores').hide();
};																					 


}

function dynamicColors() {
    var r = Math.floor(Math.random() * 255);
    var g = Math.floor(Math.random() * 255);
    var b = Math.floor(Math.random() * 255);
    return "rgb(" + r + "," + g + "," + b + ")";
	}
	
function creaColores()
{
 var tmp=[]
  for (i=0;i<100;i++){
  tmp[i]=dynamicColors();
  };
  return tmp;
  
}	
	
var colores=[];

$("#desplegablePlantas").change(function(){
	cambiaPlanta($(this).find("option:selected").val());
});

//Hace que esté activa la plata elegida en el desplegable de plantas
function cambiaPlanta(plantaElegida) {
	//Si no hay ninguno lo reseteo todo
	if (plantaElegida== "Ninguna"){
		sessionStorage.setItem("planta", "");
		sessionStorage.setItem("linea", 0);
		sessionStorage.setItem("lineaInformes", 0);
		reseteaValores();
		if (sessionStorage.getItem("tabActiva")=="General"){
		$("#tablaPrincipalResumen").show();
		$('#tablaGeneralInstantaneos').hide();
		}
		$("#tituloGeneral").text("");
		$("#tituloInverters").text("");
        $("#tituloContadores").text("");
		$("#tituloInformes").text("");	
		anyadeInverters();
		anyadeContadores();
		anyadeContadoresInstant();
		anyadeInvertersInformes();
		anyadeLineas();
		dibujaGraficas(); 
		$("#tablaInvertersAgregada").hide();	
		$('#CuadroFallosCOM').hide();
        if (sessionStorage.getItem("tabActiva")=="Contadores"){
			$('#chartContadoresContadores').hide();
			$('#tablaContadoresContadores').hide();
			rellenaTablaContadoresContadoresHoras();
            }
		}
		
	//Busco los datos de esa planta y los pongo	
	else{
		var nombre_datos;
		var nombre;
		var linea;
		//Leemos de la BD lo que necesitamos
		$.getJSON("API/consulta_plantas.php?planta="+plantaElegida, function(data) {  
		$.each(data, function(key, value){
			nombre=value.nombre;
			nombre_datos=value.nombre_datos;
			linea=value.linea;
			sessionStorage.setItem("planta", nombre_datos);
			sessionStorage.setItem("linea", linea);	
			sessionStorage.setItem("lineaInformes", linea);
			anyadeLineas();
			reseteaValores();
			$("#tablaPrincipalResumen").hide();
			$("#tituloGeneral").text("General "+nombre);
			$("#tituloInverters").text("Inverters "+nombre);
            $("#tituloContadores").text("Contadores "+nombre);
			$("#tituloInformes").text("Informes "+nombre);
			$("#tituloAlarmas").text("Alarmas "+nombre);
			anyadeInverters();
			anyadeContadores();
			anyadeContadoresInstant();
			anyadeInvertersInformes();
			anyadeLineas();
			dibujaGraficas(); 
			if (sessionStorage.getItem("tabActiva")=="Inverters"){
			rellenaTablaInvertersAgregada();
			rellenaTablaInvertersDato();
		}
        if (sessionStorage.getItem("tabActiva")=="Contadores"){
            rellenaTablaContadoresContadores();
			rellenaTablaContadoresContadoresHoras();
            }
		});
		})
	}
};

//Añade los inverters a la lista de la tab de informes
function anyadePlantasDesplegable(){
	var usuario=sessionStorage.getItem("usuario");
    $('#desplegablePlantas').empty();
	$("#desplegablePlantas").append('<option value="Ninguna" selected="selected">Ninguna </option>');
	$.getJSON("API/consulta_plantas_desplegable_usuario.php?usuario="+usuario, function(data) {  
		$.each(data, function(key, value){
		$('#desplegablePlantas').append('<option value='+value.planta+'>'+value.nombre+'</option>'); 
		});	
		$('#desplegablePlantas').selectpicker('refresh');
		}
		);
}


function reseteaValores(){
  sessionStorage.setItem("inverter",0);
  sessionStorage.setItem("contador",0);
  sessionStorage.setItem("btnActivoInverters", "Inverters");
  sessionStorage.setItem("btnActivoContadores", "Contadores");
  }

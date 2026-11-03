/////////////////////////////////////
//ESTA ES LA PARTE DEL TAB DE ALARMAS
/////////////////////////////////////



$( "#btnAnteriorFechaAlarmas" ).click(function() {
		var fecha=new Date(toDate('#datepickerAlarmas'));
	//	fecha=$('#datepickerDatos').val();
		fecha.setDate(fecha.getDate()-1);
		$('#datepickerAlarmas').datepicker("update", fecha);
		dibujaGraficas();
		desactivaBotones();
		
});

$( "#btnPosteriorFechaAlarmas" ).click(function() {
		var fecha=new Date(toDate('#datepickerAlarmas'));
		var hoy= new Date();
		if (hoy.getDate()==fecha.getDate()){
			$("#btnPosteriorFechaAlarmas").prop('disabled', true);
		}
		else{
			fecha.setDate(fecha.getDate()+1);
			$('#datepickerAlarmas').datepicker("update", fecha);
			dibujaGraficas();			
		}

});



function rellenaTablaAlarmas(){
	if (sessionStorage.getItem("tabActiva")=="Alarmas"){
		var planta=	sessionStorage.getItem("planta");
	//	var linea=	sessionStorage.getItem("lineaInformes");
		var fecha = $('#datepickerAlarmas').val().split("/");
	//	var diaomes =$('#diasMesInformes').find("option:selected").val();
		var dia= fecha[0];
		var mes = fecha[1];
		var anyo =  fecha[2];
		var sql;
		 $('#tablaAlarmas').bootstrapTable('destroy'); //Destroy bootstrap table
		$('#tablaAlarmas').bootstrapTable()
		$('#tablaAlarmas').show();
		if (puedeVerAlarmas()){
			sql="API/consulta_alarmas.php?anyo="+anyo+"&mes="+mes+"&dia="+dia+"&planta="+planta;			
			$.getJSON(sql, function(datos) {  
			$('#tablaAlarmas').bootstrapTable('load', datos);
				});	
		}
	}
	
}

//Exporta datos a Excel al apretar el botón
$("#btnAlarmasExportar").click(function(){
	var planta=	sessionStorage.getItem("planta");
//	var linea=	sessionStorage.getItem("lineaInformes");
	var fecha = $('#datepickerAlarmas').val()
	var nombre= planta
	var tabla
	tabla="#tablaAlarmas";	
	nombre="Alarmas "+nombre+"_"+fecha;	
  $(tabla).table2excel({
    // exclude CSS class
    exclude: ".noExl",
    name: "Hoja 1",
    filename: nombre, //do not include extension
    fileext: ".xls" // file extension
  }); 
});


 $("#tablaAlarmas").on("click-row.bs.table", function (row, $element) {
	 	sql="API/actualiza_alarma.php?id="+$element.id;			
    	$.getJSON(sql, function(datos) {  
		rellenaTablaAlarmas();
			}
);
});
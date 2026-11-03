///////////////////////////////////////////////////
//ESTA ES LA PARTE DEL TAB DE LECTURAS INSTANTÁNEAS
///////////////////////////////////////////////////

//Borra el resultado anterior en la BD
function borrarLecturaInstant(){
	if (sessionStorage.getItem("tabActiva")=="Lecturas instantáneas"){
		//var linea= sessionStorage.getItem("linea");
		var sql;
		//var texto="";
		//Borro el texto en el cuadro y en la BD
		$('#lecturaInstant').html("");
		sql="API/borrar_lectura_instantanea.php";
		$.getJSON(sql, function(datos) { 
		}
		);	
	}
}

//Escribe la petición de lectura en la BD
function peticionLecturaInstant(){
	if (sessionStorage.getItem("tabActiva")=="Lecturas instantáneas"){
		var planta=	sessionStorage.getItem("planta");
		var contador=sessionStorage.getItem("contador");
		var sql;
		//texto="Ejecutando petición de datos";
		if (planta !='' && contador !=0){
		sql="API/consulta_instantanea_peticion.php?planta="+planta+"&contador="+contador;
		$.getJSON(sql, function(datos) {  
			}
);	
	}
	}
}


$( "#btnPedirLecturaInstant" ).click(function() {
	if (puedePedirLecturaInstantanea()){
		peticionLecturaInstant();
	}
})
;

//Lee la lectura instantánea para la pantalla de lecturas instantáneas
function rellenaLecturaInstant(){
	if (sessionStorage.getItem("tabActiva")=="Lecturas instantáneas"){
		var planta=	sessionStorage.getItem("planta");
		//var linea= sessionStorage.getItem("linea");
		var sql;
		var texto="";
		sql="API/consulta_instantanea_lectura.php";
		$.getJSON(sql, function(datos) {  
		$.each(datos, function(key, value){
		texto=texto+value.respuesta;
		});	
		$('#lecturaInstant').html(texto);
		}
		);	
	}
}

//Añade los contadores a la lista de la tab de lecturas instantáneas
function anyadeContadoresInstant(){
	var planta=	sessionStorage.getItem("planta");
//	var linea=	sessionStorage.getItem("linea");
//	var $dropdown = $(".selectpicker.datos");
//	var $dropdown = $("#invertersInverters");
    $('#lecturaContadores').empty();
	$("#lecturaContadores").append('<option value=0 selected="selected">contadores </option>');
	$.getJSON("API/consulta_contadores_con_datos.php?planta="+planta, function(data) {  
		$.each(data, function(key, value){
			contador=value.nombre;
		$('#lecturaContadores').append('<option value='+value.numero+'>'+value.nombre+'</option>'); 
		});	
		$('#lecturaContadores').selectpicker('refresh');
		}
		);
}


$("#lecturaContadores").change(function(){
//	sessionStorage.setItem("inverter",$(this).find("option:selected").val());
	var anterior=sessionStorage.getItem("contador");
	//Si los valores de ahora son distintos a los de antes, que salte
	if (anterior!=$("#lecturaContadores").val()){
		var valor=$("#lecturaContadores").val();
	sessionStorage.setItem("contador",valor);
	$("#lecturaContadores").selectpicker('val', valor);
	$("#lecturaContadores").selectpicker('refresh');
	}
});


setInterval("rellenaLecturaInstant();",5000);	
	
	
	
	
	

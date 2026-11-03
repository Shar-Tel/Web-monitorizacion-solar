<?php

require_once __DIR__ . '/auth.php';
requiere_ajax();

include('database_connection.php');


$id = param_int('id');

$conn = conectar();

$sql="update alarmas set activa=0, fecha_fin=NOW()  where activa=1 and idregistros=".$id;

$result = $conn->query($sql);
echo $result;

$conn->close(); 

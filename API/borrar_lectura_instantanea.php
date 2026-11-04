<?php

require_once __DIR__ . '/auth.php';
requiere_ajax();

require_once __DIR__ . '/database_connection.php';


//Pongo en la BD el contador a leer
$conn = conectar();
$stmt = $conn->prepare("UPDATE lectura_instant SET PLANTA='', NUMERO=0, RESPUESTA=''");
if ($stmt) {
    $result = $stmt->execute();
    if (!$result) {
        echo 'KO';
    } else {
        echo 'OK';
    }
    $stmt->close();
} else {
    echo 'KO';
}

$conn->close();

//echo $textoCOM."<br>".$textoPAC;
//echo '' .json_encode($arr1).'';

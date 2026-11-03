<?php

function conectar() {
    $config = require __DIR__ . '/db_config.php';

    if (function_exists('mysqli_report')) {
        mysqli_report(MYSQLI_REPORT_OFF);
    }

    $conn = @new mysqli(
        $config['host'],
        $config['usuario'],
        $config['password'],
        $config['base_datos']
    );

    if ($conn->connect_errno) {
        // No se expone el detalle al cliente; queda en el log del servidor.
        error_log('DB connection failed: ' . $conn->connect_error);
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('error' => 'Error de conexión con la base de datos'));
        exit;
    }

    if (function_exists('mysqli_set_charset')) {
        $conn->set_charset('utf8mb4');
    }

    return $conn;
}

?>

<?php
/**
 * Endurecimiento común y validación de entrada para todos los endpoints.
 * Este fichero no debe imprimir nada por sí mismo (salvo responder_error()).
 */

// Si es true, todos los endpoints exigen una sesión iniciada desde login.php.
// Ponlo a false solo si tu hosting no permite sesiones PHP.
if (!defined('REQUERIR_SESION')) {
    define('REQUERIR_SESION', true);
}

// No mostrar errores al cliente; registrarlos en el log del servidor.
@ini_set('display_errors', '0');
@ini_set('log_errors', '1');
error_reporting(E_ALL);

// Que mysqli devuelva false en vez de lanzar excepciones (comportamiento que
// espera el código antiguo de esta aplicación).
if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_OFF);
}

// Sesión segura (cookie HttpOnly + SameSite, Secure solo si hay HTTPS).
if (session_status() === PHP_SESSION_NONE) {
    $https = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

    @ini_set('session.use_strict_mode', '1');
    session_name('PLANTASSOLARES');
    session_set_cookie_params(array(
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ));
    @session_start();
}

// Cabeceras defensivas básicas.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
// CSP básico (ajustar si se usan orígenes externos)
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");
// HSTS solo si hay HTTPS
$httpsSeg = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
if ($httpsSeg) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

/**
 * Devuelve una respuesta de error JSON y termina la ejecución.
 */
function responder_error($codigo, $mensaje) {
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('error' => $mensaje));
    exit;
}

/**
 * Devuelve el valor crudo de un parámetro, o null si no existe / no es escalar.
 */
function valor_parametro($nombre) {
    if (!isset($_REQUEST[$nombre]) || !is_scalar($_REQUEST[$nombre])) {
        return null;
    }
    return (string) $_REQUEST[$nombre];
}

/**
 * Entero estricto. Cualquier valor no numérico devuelve el valor por defecto.
 */
function param_int($nombre, $defecto = 0) {
    $v = valor_parametro($nombre);
    if ($v === null || !preg_match('/^-?\d{1,12}$/', trim($v))) {
        return (int) $defecto;
    }
    return (int) $v;
}

/**
 * Lista de enteros separados por comas ("1,2,3" o "0").
 * Devuelve siempre una cadena segura para usar dentro de IN (...).
 */
function param_lista_enteros($nombre, $defecto = '0') {
    $v = valor_parametro($nombre);
    if ($v === null) {
        return $defecto;
    }
    $partes = preg_split('/\s*,\s*/', trim($v), -1, PREG_SPLIT_NO_EMPTY);
    if (!$partes) {
        return $defecto;
    }
    $limpios = array();
    foreach ($partes as $parte) {
        if (!preg_match('/^-?\d{1,12}$/', $parte)) {
            return $defecto;
        }
        $limpios[] = (int) $parte;
    }
    $limpios = array_slice(array_unique($limpios), 0, 500);
    return implode(',', $limpios);
}

/**
 * Texto corto sin comillas ni caracteres de control.
 * Válido para nombres/códigos de planta y similares.
 */
function param_texto($nombre, $defecto = '', $max = 64) {
    $v = valor_parametro($nombre);
    if ($v === null) {
        return $defecto;
    }
    $v = trim($v);
    if ($v === '') {
        return '';
    }
    if (strlen($v) > $max) {
        return $defecto;
    }
    // Letras (con acentos), números, espacios y separadores habituales.
    if (!preg_match('/^[\p{L}\p{N} _.\-()\/+#]+$/u', $v)) {
        return $defecto;
    }
    return $v;
}

/**
 * Fecha estricta en formato YYYY-MM-DD (o YYYY-MM-DD HH:MM[:SS]).
 * Devuelve el valor por defecto si no es una fecha real.
 */
function param_fecha($nombre, $defecto = '') {
    $v = valor_parametro($nombre);
    if ($v === null) {
        return $defecto;
    }
    $v = trim($v);

    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $v, $m)) {
        return checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $v : $defecto;
    }
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?$/', $v, $m)) {
        if (!checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return $defecto;
        }
        if ((int) $m[4] > 23 || (int) $m[5] > 59) {
            return $defecto;
        }
        return $v;
    }
    return $defecto;
}

/**
 * Valor de una lista blanca (por ejemplo el nombre de una columna/tabla).
 */
function param_identificador($nombre, $permitidos, $defecto = '') {
    $v = valor_parametro($nombre);
    if ($v === null) {
        return $defecto;
    }
    $v = strtoupper(trim($v));
    return in_array($v, $permitidos, true) ? $v : $defecto;
}

/**
 * ID del usuario autenticado en el servidor (no el que envía el navegador).
 */
function usuario_sesion() {
    return isset($_SESSION['usuario']) ? (string) $_SESSION['usuario'] : null;
}

/**
 * Corta la petición con 401 si no hay sesión iniciada.
 */
function requiere_sesion() {
    if (usuario_sesion() === null) {
        responder_error(401, 'No autenticado');
    }
}

/**
 * Corta la petición si no es una petición AJAX del propio sitio.
 * Sirve como defensa CSRF sencilla para endpoints que modifican datos.
 */
function requiere_ajax() {
    $cabecera = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : '';
    if (strtolower($cabecera) !== 'xmlhttprequest') {
        responder_error(403, 'Petición no permitida');
    }
}

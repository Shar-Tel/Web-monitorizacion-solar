<?php
/**
 * Login de la aplicación.
 * - Solo acepta POST.
 * - Consulta preparada (sin inyección SQL).
 * - Guarda el usuario en la sesión del servidor.
 * - Compatible con contraseñas MD5 antiguas y con password_hash() moderno.
 */

require_once __DIR__ . '/API/seguridad.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SERVER['REQUEST_METHOD']) || strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
    http_response_code(405);
    echo '0';
    exit;
}

$login = isset($_POST['usuario']) ? trim((string) $_POST['usuario']) : '';
$password = isset($_POST['password']) ? trim((string) $_POST['password']) : '';

if ($login === '' || $password === '' || strlen($login) > 100 || strlen($password) > 255) {
    echo '0';
    exit;
}

// Límite sencillo de intentos por sesión: 5 fallos => 60 segundos de espera.
$ahora = time();
$intentos = isset($_SESSION['login_intentos']) ? (int) $_SESSION['login_intentos'] : 0;
$bloqueo = isset($_SESSION['login_bloqueo']) ? (int) $_SESSION['login_bloqueo'] : 0;

if ($bloqueo > $ahora) {
    http_response_code(429);
    echo '0';
    exit;
}
if ($bloqueo > 0) {
    $intentos = 0;
    unset($_SESSION['login_bloqueo']);
}

require_once __DIR__ . '/API/database_connection.php';

$conn = conectar();
$stmt = $conn->prepare('SELECT idusuarios, password FROM usuarios WHERE login = ? LIMIT 1');

if (!$stmt) {
    $conn->close();
    error_log('login: no se pudo preparar la consulta');
    echo '0';
    exit;
}

$stmt->bind_param('s', $login);
$stmt->execute();

$fila = null;
if (method_exists($stmt, 'get_result')) {
    $resultado = $stmt->get_result();
    if ($resultado) {
        $fila = $resultado->fetch_assoc();
    }
} else {
    $idUsuario = null;
    $hashGuardado = null;
    $stmt->bind_result($idUsuario, $hashGuardado);
    if ($stmt->fetch()) {
        $fila = array('idusuarios' => $idUsuario, 'password' => $hashGuardado);
    }
}

$stmt->close();
$conn->close();

$correcto = false;
if ($fila) {
    $guardado = (string) $fila['password'];
    if (preg_match('/^\$2[aby]\$/', $guardado)) {
        $correcto = password_verify($password, $guardado);
    } else {
        // Compatibilidad con las contraseñas MD5 ya existentes en la BD.
        $correcto = hash_equals(strtolower($guardado), strtolower($password));
    }
}

if ($correcto) {
    unset($_SESSION['login_intentos'], $_SESSION['login_bloqueo']);
    session_regenerate_id(true);
    $_SESSION['usuario'] = (string) $fila['idusuarios'];
    echo (string) $fila['idusuarios'];
    exit;
}

$intentos++;
$_SESSION['login_intentos'] = $intentos;
if ($intentos >= 5) {
    $_SESSION['login_bloqueo'] = $ahora + 60;
    $_SESSION['login_intentos'] = 0;
}

usleep(300000);
echo '0';

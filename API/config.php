<?php
// Configuración genérica del servidor.
// Usuarios que pueden ver todas las plantas (administradores).
$USUARIOS_ADMIN = array(1);

function esUsuarioAdmin($usuario) {
    global $USUARIOS_ADMIN;
    if ($usuario === null) {
        return false;
    }
    return in_array((string)$usuario, array_map('strval', $USUARIOS_ADMIN), true);
}
?>

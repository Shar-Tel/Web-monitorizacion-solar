<?php
/**
 * Comprobación de sesión para los endpoints de la API.
 * Incluir este fichero al principio de cada endpoint.
 */

require_once __DIR__ . '/seguridad.php';

if (REQUERIR_SESION) {
    requiere_sesion();
}

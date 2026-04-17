<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// ================================================================
// CREDENCIALES — único lugar donde se definen en todo el proyecto
// ================================================================
define('DB_HOST',    '//host.docker.internal:1521/orcl');
define('DB_CHARSET', 'AL32UTF8');

// Solo lectura — navegación pública
define('DB_READ_USER', 'Consulta');
define('DB_READ_PASS', 'Consulta2026#');

// Lectura + escritura — operaciones de usuario logueado
define('DB_OPER_USER', 'Operador');
define('DB_OPER_PASS', 'Operador2026#');

// ================================================================
// CONEXIONES
// ================================================================

// Consulta: para vistas públicas (productos, categorías, búsqueda)
function getReadConnection() {
    return @oci_connect(DB_READ_USER, DB_READ_PASS, DB_HOST, DB_CHARSET);
}

// Operador: para writes y datos de usuario (carrito, ventas, registro)
function getOperadorConnection() {
    return @oci_connect(DB_OPER_USER, DB_OPER_PASS, DB_HOST, DB_CHARSET);
}

// ================================================================
// $conn GLOBAL — usado por vistas vía require_once
// Siempre Consulta: alcanza para navegar, ver productos y filtros
// ================================================================
$conn = getReadConnection();

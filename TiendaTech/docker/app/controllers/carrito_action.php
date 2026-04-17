<?php
require_once __DIR__ . '/../config/db.php';
ob_start();
session_start();

if (empty($_SESSION['usuario'])) {
    header('Location: /index.php?login=1&redirect=/index.php?page=carrito');
    exit;
}

require_once __DIR__ . '/../models/CarritoModel.php';

$accion     = $_POST['accion']     ?? 'agregar';
$idProducto = (int)($_POST['id_producto'] ?? 0);
$cantidad   = (int)($_POST['cantidad']    ?? 1);
$idUsuario  = (int)($_SESSION['usuario']['ID_USUARIO'] ?? 0);

if ($idUsuario <= 0) {
    header('Location: /index.php?page=carrito');
    exit;
}

$conn = getOperadorConnection();
if (!$conn) {
    header('Location: /index.php?page=carrito&error=conexion');
    exit;
}

switch ($accion) {
    case 'agregar':
        if ($idProducto > 0) {
            agregarAlCarrito($conn, $idUsuario, $idProducto, max(1, $cantidad));
        }
        $redirect = $_POST['redirect'] ?? 'index.php?page=carrito';
        header('Location: ' . (strpos($redirect, 'http') === 0 ? $redirect : '/'. ltrim($redirect, '/')) . '&agregado=1');
        break;

    case 'actualizar':
        if ($idProducto > 0) {
            actualizarCantidadCarrito($conn, $idUsuario, $idProducto, $cantidad);
        }
        header('Location: /index.php?page=carrito');
        break;

    case 'eliminar':
        if ($idProducto > 0) {
            eliminarDelCarrito($conn, $idUsuario, $idProducto);
        }
        header('Location: /index.php?page=carrito');
        break;

    case 'vaciar':
        vaciarCarrito($conn, $idUsuario);
        header('Location: /index.php?page=carrito');
        break;

    default:
        header('Location: /index.php?page=carrito');
}
exit;

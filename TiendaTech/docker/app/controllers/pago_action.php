<?php
require_once __DIR__ . '/../config/db.php';
ob_start();
session_start();

if (empty($_SESSION['usuario'])) {
    header('Location: /index.php?login=1');
    exit;
}

require_once __DIR__ . '/../models/CarritoModel.php';
require_once __DIR__ . '/../models/VentaModel.php';

$idUsuario = (int)$_SESSION['usuario']['ID_USUARIO'];
$idCliente = (int)($_SESSION['usuario']['ID_CLIENTE'] ?? 0);
$idMetodo  = (int)($_POST['id_metodo'] ?? 0);

if ($idMetodo  <= 0) { header('Location: /index.php?page=carrito&error=metodo');  exit; }
if ($idCliente <= 0) { header('Location: /index.php?page=carrito&error=cliente'); exit; }

$conn = getOperadorConnection();
if (!$conn) { header('Location: /index.php?page=carrito&error=conexion'); exit; }

$items = getCarritoByUsuario($conn, $idUsuario);
if (empty($items)) { header('Location: /index.php?page=carrito&error=vacio'); exit; }

registrarVenta($conn, $idCliente, $idMetodo, $items);
vaciarCarrito($conn, $idUsuario);

header('Location: /index.php?compra=ok');
exit;

<?php
session_start();

$pagina = $_GET['page'] ?? 'home';

// Solo perfil y carrito requieren login
$protegidas = ['perfil', 'carrito'];

if (in_array($pagina, $protegidas) && empty($_SESSION['usuario'])) {
    header('Location: index.php?login=1&redirect=' . urlencode('index.php?page=' . $pagina));
    exit;
}

switch ($pagina) {

    case 'categorias':
        $view = 'views/categorias.php';
        $pageTitle = "Categorías";
    break;

    case 'buscar':
        $view = 'views/busqueda.php';
        $pageTitle = "Buscar";
    break;

    case 'producto':
        $view = 'views/producto.php';
        $pageTitle = "Producto";
    break;

    case 'carrito':
        $view = 'views/carrito.php';
        $pageTitle = "Carrito";
    break;

    case 'perfil':
        $view = 'views/perfil.php';
        $pageTitle = "Mi Perfil";
    break;

    default:
        $view = 'views/home.php';
        $pageTitle = "Inicio";
}

include 'views/layout.php';

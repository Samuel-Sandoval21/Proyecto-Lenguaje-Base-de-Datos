<?php

$pagina = $_GET['page'] ?? 'home';

switch($pagina){

    case 'categorias':
        $view = 'views/categorias.php';
        $pageTitle = "Categorías";
    break;

    case 'buscar':
        $view = 'views/busqueda.php';
        $pageTitle = "Buscar";
    break;

    default:
        $view = 'views/home.php';
        $pageTitle = "Inicio";
}

include 'views/layout.php';
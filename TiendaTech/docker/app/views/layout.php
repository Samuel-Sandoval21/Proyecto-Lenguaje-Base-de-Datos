<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= $pageTitle ?? "TiendaTech" ?></title>

<link rel="stylesheet" href="/css/style.css">

</head>
<body>

<!-- Barra principal -->
<div class="topbar">

    <div class="logo">
        TiendaTech
    </div>

    <div class="search">
        <form method="GET" action="index.php">
            <input type="hidden" name="page" value="buscar">
            <input type="text" name="buscar" placeholder="Buscar producto...">
            <button type="submit">🔍</button>
        </form>
    </div>

</div>

<!-- Submenu -->
<div class="submenu">
    <a href="index.php?page=categorias">Ver categorías</a>
</div>

<!-- CONTENIDO DE LAS VISTAS -->
<div class="container">

<?php
if(isset($view)){
    include $view;
}
?>

</div>

</body>
</html>
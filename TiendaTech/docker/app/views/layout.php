<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?? "TiendaTech" ?></title>
    <link rel="stylesheet" href="views/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dropdown-submenu {
            position: relative;
        }

        .dropdown-submenu .dropdown-menu {
            top: 0;
            left: 100%;
            margin-left: .1rem;
        }
    </style>
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

        <?php
        require_once __DIR__ . '/../config/db.php';

        $sql = "SELECT DISTINCT TIPO_GENERAL
        FROM AdminProyecto.CATEGORIAS
        ORDER BY TIPO_GENERAL";

        $stid = oci_parse($conn, $sql);
        oci_execute($stid);
        ?>

        <div class="dropdown">

            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                Ver categorías
            </button>

            <ul class="dropdown-menu">

                <?php
                $sqlTipos = "SELECT DISTINCT TIPO_GENERAL
             FROM AdminProyecto.CATEGORIAS
             ORDER BY TIPO_GENERAL";

                $stidTipos = oci_parse($conn, $sqlTipos);
                oci_execute($stidTipos);

                while ($tipo = oci_fetch_assoc($stidTipos)) {

                    $tipoNombre = $tipo['TIPO_GENERAL'];
                ?>

                    <li class="dropdown-submenu">

                        <a class="dropdown-item dropdown-toggle" href="#">
                            <?php echo $tipoNombre; ?>
                        </a>

                        <ul class="dropdown-menu">

                            <?php
                            $sqlCategorias = "SELECT ID_CATEGORIA, NOMBRE_CATEGORIA
                  FROM AdminProyecto.CATEGORIAS
                  WHERE TIPO_GENERAL = :tipo
                  ORDER BY NOMBRE_CATEGORIA";

                            $stidCat = oci_parse($conn, $sqlCategorias);
                            oci_bind_by_name($stidCat, ":tipo", $tipoNombre);
                            oci_execute($stidCat);

                            while ($cat = oci_fetch_assoc($stidCat)) {
                            ?>

                                <li>
                                    <a class="dropdown-item"
                                        href="index.php?page=buscar&categoria=<?php echo $cat['ID_CATEGORIA']; ?>">
                                        <?php echo $cat['NOMBRE_CATEGORIA']; ?>
                                    </a>
                                </li>

                            <?php } ?>

                        </ul>

                    </li>

                <?php } ?>

            </ul>

        </div>

    </div>

    <!-- CONTENIDO DE LAS VISTAS -->
    <div class="container">

        <?php
        if (isset($view)) {
            include $view;
        }
        ?>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.dropdown-submenu > a').forEach(function(element) {

            element.addEventListener("click", function(e) {

                e.preventDefault();
                e.stopPropagation();

                // cerrar todos los submenus
                document.querySelectorAll('.dropdown-submenu > a').forEach(function(element) {
                    element.addEventListener("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        // cerrar todos los submenus
                        document.querySelectorAll('.dropdown-submenu .dropdown-menu').forEach(function(menu) {
                            menu.classList.remove("show");
                        });

                        // abrir solo el seleccionado
                        let submenu = this.nextElementSibling;
                        submenu.classList.add("show");
                    });
                });

                // Cerrar submenus cuando se cierra el dropdown principal
                document.querySelectorAll('.dropdown').forEach(function(dropdown) {
                    dropdown.addEventListener('hidden.bs.dropdown', function() {
                        document.querySelectorAll('.dropdown-submenu .dropdown-menu').forEach(function(menu) {
                            menu.classList.remove("show");
                        });
                    });
                });
            });
        });
    </script>
</body>

</html>
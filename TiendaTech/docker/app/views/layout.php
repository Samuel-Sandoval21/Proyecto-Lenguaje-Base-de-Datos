<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?? "TiendaTech" ?></title>
    <link rel="stylesheet" href="views/components/style.css?v=<?= time() ?>">

    <style>
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-btn {
            background: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            background: white;
            min-width: 220px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            list-style: none;
            padding: 0;
            margin: 0;
            z-index: 1000;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-menu li {
            position: relative;
        }

        .dropdown-menu a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: black;
        }

        .dropdown-menu a:hover {
            background: #f1f1f1;
        }

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

    <div class="topbar">
        <div class="logo">
            <a href="index.php" style="text-decoration:none; color:inherit;">
                TiendaTech
            </a>
        </div>

        <div class="topbar-right">
            <div class="search">
                <form method="GET" action="index.php">
                    <input type="hidden" name="page" value="buscar">
                    <input type="text" name="buscar" placeholder="Buscar producto...">
                    <button type="submit">🔍</button>
                </form>
            </div>

            <!-- Círculo de perfil de usuario -->
            <a href="index.php?page=perfil" class="perfil-avatar" title="Mi perfil">
                <?php
                    $iniciales = '?';
                    if (!empty($_SESSION['usuario'])) {
                        $nombre = $_SESSION['usuario']['NOMBRE'] ?? $_SESSION['usuario']['USERNAME'] ?? '';
                        $partes = explode(' ', trim($nombre));
                        $iniciales = mb_strtoupper(mb_substr($partes[0], 0, 1));
                        if (isset($partes[1])) {
                            $iniciales .= mb_strtoupper(mb_substr($partes[1], 0, 1));
                        }
                    }
                    echo htmlspecialchars($iniciales);
                ?>
            </a>
        </div>
    </div>

    <div class="submenu">
        <?php require_once __DIR__ . '/../config/db.php'; ?>

        <div class="dropdown">
            <button class="dropdown-btn" id="menuBtn">Ver categorías</button>

            <ul class="dropdown-menu" id="mainMenu">
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
                        <a href="#" class="submenu-toggle"><?= $tipoNombre ?></a>

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
                                    <a href="index.php?page=categorias&categoria=<?= $cat['ID_CATEGORIA'] ?>">
                                        <?= $cat['NOMBRE_CATEGORIA'] ?>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>

    <?php
    if (isset($view)) {
        include $view;
    }
    ?>

    <script>
        const menuBtn = document.getElementById("menuBtn");
        const mainMenu = document.getElementById("mainMenu");

        menuBtn.addEventListener("click", function () {
            mainMenu.classList.toggle("show");
        });

        document.querySelectorAll(".submenu-toggle").forEach(item => {
            item.addEventListener("click", function(e) {
                e.preventDefault();
                let submenu = this.nextElementSibling;

                document.querySelectorAll(".dropdown-submenu .dropdown-menu").forEach(menu => {
                    if (menu !== submenu) {
                        menu.classList.remove("show");
                    }
                });

                submenu.classList.toggle("show");
            });
        });

        document.addEventListener("click", function(e) {
            if (!e.target.closest(".dropdown")) {
                mainMenu.classList.remove("show");
                document.querySelectorAll(".dropdown-submenu .dropdown-menu").forEach(menu => {
                    menu.classList.remove("show");
                });
            }
        });
    </script>
        <script>
    const carousel = document.getElementById("carousel");
    const btnLeft = document.getElementById("btnLeft");
    const btnRight = document.getElementById("btnRight");

    function updateButtons() {
        const scrollLeft = carousel.scrollLeft;
        const maxScroll = carousel.scrollWidth - carousel.clientWidth;

        btnLeft.classList.toggle("hidden", scrollLeft <= 0);
        btnRight.classList.toggle("hidden", scrollLeft >= maxScroll - 5);
    }

    btnLeft.addEventListener("click", () => {
        carousel.scrollBy({ left: -300, behavior: "smooth" });
    });

    btnRight.addEventListener("click", () => {
        carousel.scrollBy({ left: 300, behavior: "smooth" });
    });

    carousel.addEventListener("scroll", updateButtons);

    // Inicial
    updateButtons();
    </script>
</body>
</html>

<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?? "TiendaTech" ?></title>
    <link rel="stylesheet" href="views/components/style.css?v=<?= time() ?>">

    
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

            <!-- Ícono de carrito -->
            <?php
            $carritoCount = 0;
            if (!empty($_SESSION['carrito'])) {
                foreach ($_SESSION['carrito'] as $item) {
                    $carritoCount += $item['CANTIDAD'] ?? 1;
                }
            }
            ?>
            <?php if (!empty($_SESSION['usuario'])): ?>
                <a href="index.php?page=carrito" class="carrito-btn" title="Mi carrito">
                    🛒
                    <?php if ($carritoCount > 0): ?>
                        <span class="carrito-badge"><?= $carritoCount ?></span>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                <button type="button" class="carrito-btn" onclick="abrirLoginConRedirect('index.php?page=carrito')" title="Mi carrito">
                    🛒
                </button>
            <?php endif; ?>

            <!-- Círculo de perfil de usuario -->
            <?php
                $iniciales = '?';
                $logueado  = !empty($_SESSION['usuario']);
                if ($logueado) {
                    $nombre = $_SESSION['usuario']['NOMBRE'] ?? $_SESSION['usuario']['USERNAME'] ?? '';
                    $partes = explode(' ', trim($nombre));
                    $iniciales = mb_strtoupper(mb_substr($partes[0], 0, 1));
                    if (isset($partes[1])) {
                        $iniciales .= mb_strtoupper(mb_substr($partes[1], 0, 1));
                    }
                }
            ?>
            <?php if ($logueado): ?>
                <a href="index.php?page=perfil" class="perfil-avatar perfil-avatar--activo" title="Mi perfil">
                    <?= htmlspecialchars($iniciales) ?>
                </a>
            <?php else: ?>
                <button type="button" class="perfil-avatar" id="btnAbrirLogin" title="Iniciar sesión">
                    <?= htmlspecialchars($iniciales) ?>
                </button>
            <?php endif; ?>
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

<!-- ===================== MODAL COMPRA EXITOSA ===================== -->
<div id="modalCompraOk" class="modal-login-overlay">
    <div class="modal-login-box" style="text-align:center; max-width:380px;">
        <div style="
            width:72px; height:72px; border-radius:50%;
            background:#d1fae5; color:#059669;
            font-size:2.2rem; font-weight:800;
            display:flex; align-items:center; justify-content:center;
            margin: 0 auto 18px;
        ">✓</div>
        <h2 style="font-size:1.35rem; font-weight:800; color:#111; margin:0 0 10px;">
            ¡Compra realizada!
        </h2>
        <p style="color:#6b7280; font-size:.95rem; margin:0 0 28px; line-height:1.5;">
            Tu pedido fue procesado correctamente.<br>
            <strong style="color:#111;">Tu producto se entregará pronto.</strong>
        </p>
        <button onclick="document.getElementById('modalCompraOk').classList.remove('modal-login-open')"
            style="
                background:#2563eb; color:#fff;
                border:none; border-radius:10px;
                padding:11px 32px; font-size:.96rem;
                font-weight:700; cursor:pointer;
            ">Continuar comprando</button>
    </div>
</div>

<!-- ===================== MODAL DE LOGIN ===================== -->
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$loginError  = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
$abrirModal  = isset($_GET['login']) || $loginError;
// El destino post-login: si viene ?redirect=X lo usamos, si no la pagina actual sin el param login
$redirectParam = $_GET['redirect'] ?? null;
if ($redirectParam) {
    $postLoginDest = $redirectParam;
} else {
    $tmpGet = $_GET;
    unset($tmpGet['login'], $tmpGet['redirect']);
    $postLoginDest = 'index.php' . (!empty($tmpGet) ? '?' . http_build_query($tmpGet) : '');
}
?>

<div id="modalLoginOverlay" class="modal-login-overlay <?php echo $abrirModal ? 'modal-login-open' : ''; ?>">
    <div class="modal-login-box">
        <button class="modal-login-close" id="btnCerrarLogin" title="Cerrar">&times;</button>

        <div class="modal-login-logo">TiendaTech</div>
        <h2 class="modal-login-title">Iniciar sesión</h2>

        <?php if ($loginError): ?>
            <div class="modal-login-error"><?php echo htmlspecialchars($loginError); ?></div>
        <?php endif; ?>

        <form action="controllers/login_action.php" method="post" class="modal-login-form">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($postLoginDest); ?>">

            <label for="ml_username">Usuario</label>
            <input type="text" id="ml_username" name="username" placeholder="Ej: admin" autocomplete="username" required>

            <label for="ml_password">Contraseña</label>
            <div class="modal-login-pw-wrap">
                <input type="password" id="ml_password" name="password" placeholder="••••••••" autocomplete="current-password" required>
                <button type="button" class="toggle-pw" tabindex="-1" title="Mostrar/ocultar">👁</button>
            </div>

            <button type="submit" class="modal-login-submit">Entrar</button>
        </form>

        <p class="modal-login-register">
            ¿No tenés cuenta? <a href="registro.php" class="modal-login-register-link">Crear Cuenta</a>
        </p>
    </div>
</div>


<script>
(function () {
    var overlay   = document.getElementById('modalLoginOverlay');
    var btnAbrir  = document.getElementById('btnAbrirLogin');
    var btnCerrar = document.getElementById('btnCerrarLogin');
    var pwInput   = document.getElementById('ml_password');
    var togglePw  = document.querySelector('.toggle-pw');

    function abrir()  { overlay.classList.add('modal-login-open'); }
    function cerrar() { overlay.classList.remove('modal-login-open'); }

    if (btnAbrir)  btnAbrir.addEventListener('click', abrir);
    if (btnCerrar) btnCerrar.addEventListener('click', cerrar);

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) cerrar();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') cerrar();
    });

    if (togglePw && pwInput) {
        togglePw.addEventListener('click', function () {
            pwInput.type = pwInput.type === 'password' ? 'text' : 'password';
        });
    }
})();

// Modal de confirmación de compra
(function() {
    var params = new URLSearchParams(window.location.search);
    if (params.get('compra') === 'ok') {
        document.getElementById('modalCompraOk').classList.add('modal-login-open');
        // Limpiar el param de la URL sin recargar
        history.replaceState(null, '', 'index.php');
    }
})();

// Función global: abre el modal con un redirect específico (usado por producto.php)
function abrirLoginConRedirect(redirectUrl) {
    var overlay  = document.getElementById('modalLoginOverlay');
    var hiddenRd = overlay.querySelector('input[name="redirect"]');
    if (hiddenRd) hiddenRd.value = redirectUrl;
    overlay.classList.add('modal-login-open');
}
</script>
</body>
</html>

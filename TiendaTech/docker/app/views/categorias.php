<?php /* categorias.php */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/CategoriaModel.php';
require_once __DIR__ . '/../models/MarcaModel.php';

$categoria = $_GET['categoria'] ?? null;

if (!$categoria) {
    echo "<div class='main-container'><p>No se seleccionó categoría</p></div>";
    return;
}

/* ── NOMBRE CATEGORÍA ─────────────────────────── */
$stNombre = oci_parse($conn, getNombreCategoriaSQL());
oci_bind_by_name($stNombre, ":cat", $categoria);
oci_execute($stNombre);
$nombreCat = oci_fetch_assoc($stNombre)['NOMBRE_CATEGORIA'] ?? 'Categoría';

/* ── PRECIO MÁXIMO ────────────────────────────── */
$stMax = oci_parse($conn,
    "SELECT MAX(PRECIO) AS MAX_PRECIO
     FROM AdminProyecto.PRODUCTOS
     WHERE ID_CATEGORIA = :cat");
oci_bind_by_name($stMax, ":cat", $categoria);
oci_execute($stMax);
$maxPrecio = (int) ceil(oci_fetch_assoc($stMax)['MAX_PRECIO'] ?? 1000);

/* ── MARCAS ───────────────────────────────────── */
$stMarcas = oci_parse($conn, getMarcasPorCategoriaSQL());
oci_bind_by_name($stMarcas, ":cat", $categoria);
oci_execute($stMarcas);
$marcas = [];
while ($r = oci_fetch_assoc($stMarcas)) $marcas[] = $r;

/* ── ATRIBUTOS DISTINTOS (para los filtros) ───── */
$stAtribs = oci_parse($conn, getAtributosPorCategoriaSQL());
oci_bind_by_name($stAtribs, ":cat", $categoria);
oci_execute($stAtribs);
$atributosFiltro = []; // [ 'VRAM' => ['8GB GDDR6', '12GB GDDR6X', ...], ... ]
while ($r = oci_fetch_assoc($stAtribs)) {
    $atributosFiltro[$r['NOMBRE_ATRIBUTO']][] = $r['VALOR'];
}

/* ── ATRIBUTOS POR PRODUCTO (para data-atributos) */
$stAtribProd = oci_parse($conn, getAtributosProductosSQL());
oci_bind_by_name($stAtribProd, ":cat", $categoria);
oci_execute($stAtribProd);
$atribPorProducto = []; // [ id_producto => [ nombre => valor, ... ] ]
while ($r = oci_fetch_assoc($stAtribProd)) {
    $atribPorProducto[$r['ID_PRODUCTO']][$r['NOMBRE_ATRIBUTO']] = $r['VALOR'];
}

/* ── TODOS LOS PRODUCTOS ──────────────────────── */
$stid = oci_parse($conn, getProductosPorCategoriaSQL());
oci_bind_by_name($stid, ":cat", $categoria);
oci_execute($stid);
$productos = [];
while ($row = oci_fetch_assoc($stid)) $productos[] = $row;

/* ── TOP 6 PARA CAROUSEL (del mismo array) ────── */
$topProductos = array_slice($productos, 0, 6);
?>

<!-- ═══════════════════════════════
     CAROUSEL TOP DESCUENTOS
═══════════════════════════════ -->
<div class="sub-container">
    <h1>🔥 Ofertas en <?= htmlspecialchars($nombreCat) ?></h1>
    <div class="carousel-container">
        <button class="carousel-btn left" id="btnLeft">‹</button>
        <div class="carousel" id="carousel">
            <?php foreach ($topProductos as $row): ?>
                <div class="carousel-item">
                    <?php include __DIR__ . '/components/info_producto.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-btn right" id="btnRight">›</button>
    </div>
</div>

<!-- ═══════════════════════════════
     LAYOUT: FILTROS + PRODUCTOS
═══════════════════════════════ -->
<div class="layout-categoria">

    <!-- ── PANEL DE FILTROS ───────────────── -->
    <div class="filtros-container">
        <h2>Filtros</h2>

        <!-- PRECIO SLIDER -->
        <p class="filtro-seccion-titulo">Precio</p>
        <div class="filtro-precio-wrapper">
            <div class="filtro-precio-valores">
                <span>$0</span>
                <span id="precio-valor">$<?= $maxPrecio ?></span>
            </div>
            <input type="range" id="filtro-precio-slider" class="filtro-precio-slider"
                   min="0" max="<?= $maxPrecio ?>" value="<?= $maxPrecio ?>" step="1">
            <div class="filtro-precio-label">
                Hasta: <strong id="precio-label-valor">$<?= $maxPrecio ?></strong>
            </div>
        </div>

        <!-- MARCAS -->
        <p class="filtro-marca-titulo">
            Marca &mdash; <?= htmlspecialchars($nombreCat) ?>
        </p>
        <ul class="filtro-marcas-lista">
            <?php foreach ($marcas as $marca): ?>
                <li>
                    <label class="filtro-marca-label">
                        <input type="checkbox" class="filtro-marca-check"
                               value="<?= htmlspecialchars($marca['NOMBRE_MARCA']) ?>">
                        <?= htmlspecialchars($marca['NOMBRE_MARCA']) ?>
                    </label>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- ATRIBUTOS TÉCNICOS (dinámicos desde PRODUCTO_ATRIBUTOS) -->
        <?php foreach ($atributosFiltro as $nombreAtrib => $valores): ?>
            <div class="filtro-atributo-grupo"
                 data-atributo="<?= htmlspecialchars($nombreAtrib) ?>">

                <p class="filtro-seccion-titulo filtro-atrib-titulo">
                    <?= htmlspecialchars($nombreAtrib) ?>
                </p>

                <ul class="filtro-marcas-lista">
                    <?php foreach ($valores as $valor): ?>
                        <li class="filtro-atrib-item">
                            <label class="filtro-marca-label">
                                <input type="checkbox"
                                       class="filtro-atrib-check"
                                       data-atributo="<?= htmlspecialchars($nombreAtrib) ?>"
                                       value="<?= htmlspecialchars($valor) ?>">
                                <?= htmlspecialchars($valor) ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

    </div><!-- /filtros-container -->

    <!-- ── GRID DE PRODUCTOS ──────────────── -->
    <div class="sub-main-container">
        <h1 style="margin-bottom:30px;"><?= htmlspecialchars($nombreCat) ?></h1>

        <div class="productos-grid" id="productos-grid">
            <?php foreach ($productos as $row):
                $idProd    = $row['ID_PRODUCTO'];
                $attrsMap  = $atribPorProducto[$idProd] ?? [];
                $attrsJson = htmlspecialchars(json_encode($attrsMap), ENT_QUOTES);
            ?>
                <div class="producto-item"
                     data-marca="<?= htmlspecialchars($row['NOMBRE_MARCA']) ?>"
                     data-precio="<?= (float) $row['PRECIO'] ?>"
                     data-atributos="<?= $attrsJson ?>">
                    <?php include __DIR__ . '/components/info_producto.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <p id="sin-resultados" style="display:none;">
            No hay productos para los filtros seleccionados.
        </p>
    </div>

</div><!-- /layout-categoria -->

<!-- ═══════════════════════════════
     JAVASCRIPT DE FILTROS
═══════════════════════════════ -->
<script>
(function () {
    const marcaChecks = document.querySelectorAll('.filtro-marca-check');
    const atribChecks = document.querySelectorAll('.filtro-atrib-check');
    const items       = document.querySelectorAll('.producto-item');
    const aviso       = document.getElementById('sin-resultados');
    const slider      = document.getElementById('filtro-precio-slider');
    const labelV      = document.getElementById('precio-label-valor');
    const topV        = document.getElementById('precio-valor');

    /* ── Aplica todos los filtros activos ─────── */
    function aplicarFiltro() {
        const marcasSel = new Set(
            [...marcaChecks].filter(c => c.checked).map(c => c.value)
        );
        const hayMarca  = marcasSel.size > 0;
        const maxPrecio = parseFloat(slider.value);

        // Atributos seleccionados: { nombreAtrib: Set(valores) }
        const atribsSel = {};
        atribChecks.forEach(c => {
            if (!c.checked) return;
            const nom = c.dataset.atributo;
            if (!atribsSel[nom]) atribsSel[nom] = new Set();
            atribsSel[nom].add(c.value);
        });
        const hayAtrib = Object.keys(atribsSel).length > 0;

        let visibles = 0;
        items.forEach(item => {
            const marcaOk  = !hayMarca || marcasSel.has(item.dataset.marca);
            const precioOk = parseFloat(item.dataset.precio) <= maxPrecio;

            let atribOk = true;
            if (hayAtrib) {
                const prod = JSON.parse(item.dataset.atributos || '{}');
                for (const [nom, vals] of Object.entries(atribsSel)) {
                    if (!prod[nom] || !vals.has(prod[nom])) {
                        atribOk = false;
                        break;
                    }
                }
            }

            const mostrar = marcaOk && precioOk && atribOk;
            item.style.display = mostrar ? '' : 'none';
            if (mostrar) visibles++;
        });

        aviso.style.display = visibles === 0 ? 'block' : 'none';

        // Actualizar qué checks de atributo tienen sentido
        refrescarAtributos();
    }

    /* ── Oculta checks cuyo valor no existe en
         los productos que pasan marca + precio ── */
    function refrescarAtributos() {
        const marcasSel = new Set(
            [...marcaChecks].filter(c => c.checked).map(c => c.value)
        );
        const hayMarca  = marcasSel.size > 0;
        const maxPrecio = parseFloat(slider.value);

        // Recorrer productos que pasan el filtro de marca y precio
        const disponibles = {};
        items.forEach(item => {
            if (parseFloat(item.dataset.precio) > maxPrecio) return;
            if (hayMarca && !marcasSel.has(item.dataset.marca)) return;
            const attrs = JSON.parse(item.dataset.atributos || '{}');
            for (const [nom, val] of Object.entries(attrs)) {
                if (!disponibles[nom]) disponibles[nom] = new Set();
                disponibles[nom].add(val);
            }
        });

        // Mostrar / ocultar cada item de atributo
        document.querySelectorAll('.filtro-atributo-grupo').forEach(grupo => {
            const nom      = grupo.dataset.atributo;
            const disp     = disponibles[nom] || new Set();
            let hayAlguno  = false;

            grupo.querySelectorAll('.filtro-atrib-item').forEach(li => {
                const chk    = li.querySelector('.filtro-atrib-check');
                const existe = disp.has(chk.value);
                li.style.display = existe ? '' : 'none';
                if (!existe && chk.checked) chk.checked = false;
                if (existe) hayAlguno = true;
            });

            grupo.style.display = hayAlguno ? '' : 'none';
        });
    }

    /* ── Eventos ──────────────────────────────── */
    slider.addEventListener('input', function () {
        const v = '$' + parseInt(this.value).toLocaleString();
        labelV.textContent = v;
        topV.textContent   = v;
        aplicarFiltro();
    });

    marcaChecks.forEach(c => c.addEventListener('change', aplicarFiltro));
    atribChecks.forEach(c => c.addEventListener('change', aplicarFiltro));

    // Estado inicial
    refrescarAtributos();
})();
</script>

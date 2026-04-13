<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/BusquedaModel.php';

$categoria = $_GET['categoria'] ?? null;
$buscar    = trim($_GET['buscar'] ?? '');

/* =========================
   FILTRO POR CATEGORÍA
========================= */
if ($categoria) {
    $sqlNombre = getNombreCategoriaSQL();
    $stNombre = oci_parse($conn, $sqlNombre);
    oci_bind_by_name($stNombre, ":cat", $categoria);
    oci_execute($stNombre);

    $nombreCat = oci_fetch_assoc($stNombre)['NOMBRE_CATEGORIA'] ?? 'Categoría';

    echo "<div class='main-container'>";
    echo "<h1>" . htmlspecialchars($nombreCat) . "</h1>";

    $sql = getProductosPorCategoriaSQL();
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ":cat", $categoria);
    oci_execute($stid);

/* =========================
   BÚSQUEDA
========================= */
} elseif ($buscar !== '') {
    echo "<div class='main-container'>";
    echo "<h1>Resultados para: <em>" . htmlspecialchars($buscar) . "</em></h1>";

    $palabras = array_filter(
        explode(' ', strtoupper($buscar)),
        fn($p) => strlen($p) > 1
    );

    if (empty($palabras)) {
        echo "<p>Escribí al menos dos letras para buscar.</p></div>";
        return;
    }

    $resultadoBusqueda = construirBusquedaSQL($palabras);

    $stid = oci_parse($conn, $resultadoBusqueda['sql']);

    foreach ($resultadoBusqueda['binds'] as $key => $valor) {
        oci_bind_by_name($stid, $key, $resultadoBusqueda['binds'][$key]);
    }

    oci_execute($stid);

/* =========================
   SIN PARÁMETROS
========================= */
} else {
    echo "<div class='main-container'>";
    echo "<p>Usá la lupa para buscar un producto o elegí una categoría del menú.</p></div>";
    return;
}

/* =========================
   RENDER
========================= */
$hayResultados = false;

echo '<div class="productos-grid">';

while ($row = oci_fetch_assoc($stid)) {
    $hayResultados = true;
    include __DIR__ . '/components/info_producto.php';
}

echo '</div>';

if (!$hayResultados) {
    echo "<p>No se encontraron productos.</p>";
}

echo '</div>';
?>
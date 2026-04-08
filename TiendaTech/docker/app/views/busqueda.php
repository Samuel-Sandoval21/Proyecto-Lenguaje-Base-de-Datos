<?php
require_once __DIR__ . '/../config/db.php';

$categoria = $_GET['categoria'] ?? null;
$buscar    = trim($_GET['buscar'] ?? '');

// ── Función para quitar tildes y normalizar texto ────────────────────────────
function normalizar(string $texto): string {
    $con = ['á','é','í','ó','ú','Á','É','Í','Ó','Ú','ä','ë','ï','ö','ü','ñ','Ñ'];
    $sin = ['a','e','i','o','u','a','e','i','o','u','a','e','i','o','u','n','n'];
    return strtoupper(str_replace($con, $sin, $texto));
}

// ── Caso 1: FILTRO POR CATEGORÍA ─────────────────────────────────────────────
if ($categoria) {

    $sqlNombre = "SELECT NOMBRE_CATEGORIA 
                  FROM AdminProyecto.CATEGORIAS 
                  WHERE ID_CATEGORIA = :cat";

    $stNombre = oci_parse($conn, $sqlNombre);
    oci_bind_by_name($stNombre, ":cat", $categoria);
    oci_execute($stNombre);

    $nombreCat = oci_fetch_assoc($stNombre)['NOMBRE_CATEGORIA'] ?? 'Categoría';

    echo "<h1>" . htmlspecialchars($nombreCat) . "</h1>";

    $sql = "SELECT 
                p.ID_PRODUCTO,
                p.NOMBRE, 
                p.DESCRIPCION, 
                p.PRECIO, 
                p.STOCK, 
                p.IMAGEN, 
                m.NOMBRE_MARCA
            FROM AdminProyecto.PRODUCTOS p
            JOIN AdminProyecto.MARCAS m ON p.ID_MARCA = m.ID_MARCA
            WHERE p.ID_CATEGORIA = :cat
            ORDER BY p.NOMBRE";

    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ":cat", $categoria);
    oci_execute($stid);

// ── Caso 2: BÚSQUEDA ─────────────────────────────────────────────────────────
} elseif ($buscar !== '') {

    echo "<h1>Resultados para: <em>" . htmlspecialchars($buscar) . "</em></h1>";

    $palabras = array_filter(
        explode(' ', normalizar($buscar)),
        fn($p) => strlen($p) > 1
    );

    if (empty($palabras)) {
        echo "<p>Escribí al menos dos letras para buscar.</p>";
        return;
    }

    $normalOracle = "TRANSLATE(UPPER(#), 'ÁÉÍÓÚÄËÏÖÜÑ', 'AEIOUAEIOUN')";

    $condiciones = [];
    $binds       = [];

    foreach (array_values($palabras) as $i => $palabra) {

        $key = ":t$i";

        $n = str_replace('#', 'p.NOMBRE', $normalOracle);
        $d = str_replace('#', 'p.DESCRIPCION', $normalOracle);
        $m = str_replace('#', 'm.NOMBRE_MARCA', $normalOracle);

        $condiciones[] = "($n LIKE $key OR $d LIKE $key OR $m LIKE $key)";
        $binds[$key]   = "%$palabra%";
    }

    $where = implode(' OR ', $condiciones);

    $sql = "SELECT 
                p.ID_PRODUCTO,
                p.NOMBRE, 
                p.DESCRIPCION, 
                p.PRECIO, 
                p.STOCK, 
                p.IMAGEN, 
                m.NOMBRE_MARCA
            FROM AdminProyecto.PRODUCTOS p
            JOIN AdminProyecto.MARCAS m ON p.ID_MARCA = m.ID_MARCA
            WHERE $where
            ORDER BY p.NOMBRE";

    $stid = oci_parse($conn, $sql);

    foreach ($binds as $key => $_) {
        oci_bind_by_name($stid, $key, $binds[$key]);
    }

    oci_execute($stid);

// ── Caso 3: SIN PARÁMETROS ───────────────────────────────────────────────────
} else {
    echo "<p>Usá la lupa para buscar un producto o elegí una categoría del menú.</p>";
    return;
}

// ── RENDERIZADO ──────────────────────────────────────────────────────────────
$hayResultados = false;

echo '<div style="display:grid; grid-template-columns:repeat(5,1fr); gap:20px;">';

while ($row = oci_fetch_assoc($stid)) {
    $hayResultados = true;
    include __DIR__ . '/components/info_producto.php';
}
?>


<?php

echo '</div>';

if (!$hayResultados) {
    echo "<p>No se encontraron productos.</p>";
}
?>


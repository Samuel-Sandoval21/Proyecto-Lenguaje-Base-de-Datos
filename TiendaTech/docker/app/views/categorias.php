<?php
require_once __DIR__ . '/../config/db.php';

$categoria = $_GET['categoria'] ?? null;

if (!$categoria) {
    echo "<p>No se seleccionó categoría</p>";
    return;
}

// Obtener nombre de la categoría
$sqlNombre = "SELECT NOMBRE_CATEGORIA 
              FROM AdminProyecto.CATEGORIAS 
              WHERE ID_CATEGORIA = :cat";

$stNombre = oci_parse($conn, $sqlNombre);
oci_bind_by_name($stNombre, ":cat", $categoria);
oci_execute($stNombre);

$nombreCat = oci_fetch_assoc($stNombre)['NOMBRE_CATEGORIA'] ?? 'Categoría';

echo "<h1>$nombreCat</h1>";

// Productos de la categoría
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
?>

<div style="display:grid; grid-template-columns:repeat(5,1fr); gap:20px;">

<?php
while ($row = oci_fetch_assoc($stid)) {
    include __DIR__ . '/components/info_producto.php';
}
?>

</div>
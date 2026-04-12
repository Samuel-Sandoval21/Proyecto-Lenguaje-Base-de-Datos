<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/ProductoModel.php';
require_once __DIR__ . '/../models/DetalleVentaModel.php';

/* =========================
   TOP 6 MÁS VENDIDOS
========================= */
$sqlMasVendidos = getBaseDetalleVentaSQL();
$stidMasVendidos = oci_parse($conn, $sqlMasVendidos);
oci_execute($stidMasVendidos);

/* =========================
   TODOS LOS PRODUCTOS
========================= */
$sql = getBaseProductosSQL() . " ORDER BY p.NOMBRE";
$stid = oci_parse($conn, $sql);
oci_execute($stid);
?>

<h1>🔥 Top 6 más vendidos</h1>

<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-bottom:40px;">
<?php while ($row = oci_fetch_assoc($stidMasVendidos)) { ?>
    <?php include __DIR__ . '/components/info_producto.php'; ?>
<?php } ?>
</div>

<h1>Productos disponibles</h1>

<div style="display:grid; grid-template-columns:repeat(5,1fr); gap:20px;">
<?php while ($row = oci_fetch_assoc($stid)) { ?>
    <?php include __DIR__ . '/components/info_producto.php'; ?>
<?php } ?>
</div>
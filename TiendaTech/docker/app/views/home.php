<?php /* home.php */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/ProductoModel.php';
require_once __DIR__ . '/../models/DetalleVentaModel.php';
 
/* ── TOP 6 MÁS VENDIDOS ──────────── */
$sqlMasVendidos  = getBaseDetalleVentaSQL();
$stidMasVendidos = oci_parse($conn, $sqlMasVendidos);
oci_execute($stidMasVendidos);
$topVendidos = [];
while ($r = oci_fetch_assoc($stidMasVendidos)) $topVendidos[] = $r;
 
/* ── TODOS LOS PRODUCTOS ─────────── */
$productos = getTodosLosProductos($conn);
?>
 
<!-- =========================
     TOP 6 MÁS VENDIDOS — CAROUSEL
========================= -->
<div class="sub-container">
    <h1>🔥 Top 6 más vendidos</h1>
 
    <div class="carousel-container">
        <button class="carousel-btn left" id="btnLeft">‹</button>
 
        <div class="carousel" id="carousel">
            <?php foreach ($topVendidos as $row): ?>
                <div class="carousel-item">
                    <?php include __DIR__ . '/components/info_producto.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
 
        <button class="carousel-btn right" id="btnRight">›</button>
    </div>
</div>
 
<!-- =========================
     PRODUCTOS DISPONIBLES
========================= -->
<div class="main-container">
    <h1 class="titulo-seccion">Productos disponibles</h1>
 
    <div class="productos-grid">
        <?php foreach ($productos as $row): ?>
            <?php include __DIR__ . '/components/info_producto.php'; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php
require_once __DIR__ . '/../config/db.php';

$sql = "SELECT 
            p.ID_PRODUCTO,
            p.NOMBRE,
            p.DESCRIPCION,
            p.PRECIO,
            p.STOCK,
            m.NOMBRE_MARCA,
            c.NOMBRE_CATEGORIA
        FROM AdminProyecto.PRODUCTOS p
        JOIN AdminProyecto.MARCAS m ON p.ID_MARCA = m.ID_MARCA
        JOIN AdminProyecto.CATEGORIAS c ON p.ID_CATEGORIA = c.ID_CATEGORIA
        ORDER BY p.NOMBRE";

$stid = oci_parse($conn, $sql);
oci_execute($stid);
?>

<h1>Productos disponibles</h1>

<div style="display:grid; grid-template-columns:repeat(5,1fr); gap:20px;">

<?php while ($row = oci_fetch_assoc($stid)) { ?>

    <div style="
        background:white;
        border:1px solid #ddd;
        border-radius:8px;
        padding:15px;
        box-shadow:0 2px 6px rgba(0,0,0,0.1);
    ">

        <h3><?php echo $row['NOMBRE']; ?></h3>

        <p><?php echo $row['DESCRIPCION']; ?></p>

        <p><strong>Marca:</strong> <?php echo $row['NOMBRE_MARCA']; ?></p>

        <p><strong>Categoría:</strong> <?php echo $row['NOMBRE_CATEGORIA']; ?></p>

        <p style="font-weight:bold; color:#1a73e8;">
            $<?php echo $row['PRECIO']; ?>
        </p>

        <p style="color:#555;">
            Stock: <?php echo $row['STOCK']; ?>
        </p>

    </div>

<?php } ?>

</div>
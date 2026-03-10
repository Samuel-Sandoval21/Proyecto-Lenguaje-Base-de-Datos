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
        FROM PRODUCTOS p
        JOIN MARCAS m ON p.ID_MARCA = m.ID_MARCA
        JOIN CATEGORIAS c ON p.ID_CATEGORIA = c.ID_CATEGORIA
        ORDER BY p.NOMBRE";

$stid = oci_parse($conn, $sql);
oci_execute($stid);
?>

<h1>Productos disponibles</h1>

<div class="productos">

<?php while ($row = oci_fetch_assoc($stid)) { ?>

    <div class="producto-card">

        <h3><?php echo $row['NOMBRE']; ?></h3>

        <p><?php echo $row['DESCRIPCION']; ?></p>

        <p><strong>Marca:</strong> <?php echo $row['NOMBRE_MARCA']; ?></p>

        <p><strong>Categoría:</strong> <?php echo $row['NOMBRE_CATEGORIA']; ?></p>

        <p class="precio">$<?php echo $row['PRECIO']; ?></p>

        <p class="stock">Stock: <?php echo $row['STOCK']; ?></p>

    </div>

<?php } ?>

</div>
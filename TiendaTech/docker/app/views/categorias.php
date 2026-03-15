<?php
require_once __DIR__ . '/../config/db.php';

$sql = "SELECT ID_CATEGORIA, NOMBRE_CATEGORIA
        FROM AdminProyecto.CATEGORIAS
        ORDER BY NOMBRE_CATEGORIA";

$stid = oci_parse($conn, $sql);
oci_execute($stid);
?>

<h1>Categorías</h1>

<div class="categorias-menu">

<?php while ($row = oci_fetch_assoc($stid)) { ?>

    <a class="categoria-btn"
       href="index.php?page=buscar&categoria=<?php echo $row['ID_CATEGORIA']; ?>">

        <?php echo $row['NOMBRE_CATEGORIA']; ?>

    </a>

<?php } ?>

</div>
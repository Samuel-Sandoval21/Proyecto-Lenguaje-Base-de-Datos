<?php
require_once __DIR__ . '/../config/db.php';

$sql = "SELECT ID_CATEGORIA, NOMBRE_CATEGORIA
        FROM AdminProyecto.CATEGORIAS
        ORDER BY NOMBRE_CATEGORIA";

$stid = oci_parse($conn, $sql);
oci_execute($stid);
?>

<h1>Categorías</h1>

<div style="display:grid; grid-template-columns:repeat(5,1fr); gap:20px;">

<?php while ($row = oci_fetch_assoc($stid)) { ?>

<a href="index.php?page=buscar&categoria=<?php echo $row['ID_CATEGORIA']; ?>"
   style="
   text-decoration:none;
   color:#333;
   ">

    <div style="
        background:white;
        border:1px solid #ddd;
        border-radius:8px;
        padding:20px;
        text-align:center;
        font-weight:bold;
        box-shadow:0 2px 6px rgba(0,0,0,0.1);
        transition:0.2s;
    ">

        <?php echo $row['NOMBRE_CATEGORIA']; ?>

    </div>

</a>

<?php } ?>

</div>
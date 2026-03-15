<?php
require_once __DIR__ . '/../config/db.php';

$categoria = $_GET['categoria'] ?? null;

if($categoria){

    $sql = "SELECT NOMBRE, DESCRIPCION, PRECIO
            FROM AdminProyecto.PRODUCTOS
            WHERE ID_CATEGORIA = :categoria";

    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ":categoria", $categoria);
    oci_execute($stid);

    echo "<h3>Productos de la categoría</h3>";

    while ($row = oci_fetch_assoc($stid)) {

        echo "<div class='card mb-3 p-3'>";
        echo "<h5>".$row['NOMBRE']."</h5>";
        echo "<p>".$row['DESCRIPCION']."</p>";
        echo "<strong>₡".$row['PRECIO']."</strong>";
        echo "</div>";
    }

}else{
    echo "Seleccione una categoría";
}
?>
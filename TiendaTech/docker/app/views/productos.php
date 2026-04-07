<?php
require_once __DIR__ . '/../config/db.php';

$id = $_GET['id'] ?? null;

if ($id) {

    $sql = "SELECT 
                p.ID_PRODUCTO,
                p.NOMBRE,
                p.DESCRIPCION,
                p.PRECIO,
                p.STOCK,
                p.IMAGEN,
                p.ID_CATEGORIA,
                m.NOMBRE_MARCA,
                c.NOMBRE_CATEGORIA
            FROM AdminProyecto.PRODUCTOS p
            JOIN AdminProyecto.MARCAS m ON p.ID_MARCA = m.ID_MARCA
            JOIN AdminProyecto.CATEGORIAS c ON p.ID_CATEGORIA = c.ID_CATEGORIA
            WHERE p.ID_PRODUCTO = :id";

    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ":id", $id);
    oci_execute($stid);

    $producto = oci_fetch_assoc($stid);

    if ($producto) {
?>

<div style="max-width:1200px; margin:50px auto;">

    <!-- CONTENEDOR PRINCIPAL -->
    <div style="
        display:flex;
        gap:50px;
        align-items:flex-start;
    ">

        <!-- IMAGEN -->
        <div style="flex:1.5;">
            <img 
                src="<?php echo $producto['IMAGEN']; ?>" 
                style="
                    width:100%;
                    max-width:550px;
                    object-fit:contain;
                    border-radius:10px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.15);
                "
            >
        </div>

        <!-- INFO EN UNA SOLA CAJA -->
        <div style="
            flex:2;
            background:#f9f9f9;
            padding:25px;
            border-radius:10px;
            display:flex;
            flex-direction:column;
            gap:20px;
        ">

            <h1 style="font-size:32px; margin:0;">
                <?php echo $producto['NOMBRE']; ?>
            </h1>

            <p style="font-size:20px; color:#555; margin:0;">
                <strong>Categoría:</strong> <?php echo $producto['NOMBRE_CATEGORIA']; ?>
            </p>

            <p style="font-size:18px; margin:0;">
                <?php echo $producto['DESCRIPCION']; ?>
            </p>

            <p style="font-size:26px; font-weight:bold; color:#1a73e8; margin:0;">
                $<?php echo $producto['PRECIO']; ?>
            </p>

            <p style="font-size:18px; color:#777; margin:0;">
                Stock disponible: <?php echo $producto['STOCK']; ?>
            </p>

            <!-- BOTON -->
            <form action="carrito.php" method="POST">
                <input type="hidden" name="id_producto" value="<?php echo $producto['ID_PRODUCTO']; ?>">

                <button type="submit" style="
                    background:#1a73e8;
                    color:white;
                    border:none;
                    padding:18px;
                    border-radius:10px;
                    font-size:18px;
                    cursor:pointer;
                    width:100%;
                    transition:0.3s;
                "
                onmouseover="this.style.background='#1558b0'"
                onmouseout="this.style.background='#1a73e8'">

                    🛒 Agregar al carrito

                </button>
            </form>

        </div>

    </div>

    <!-- PRODUCTOS RELACIONADOS -->
    <?php
    $sql_rel = "SELECT 
                    p.ID_PRODUCTO,
                    p.NOMBRE,
                    p.PRECIO,
                    p.IMAGEN
                FROM AdminProyecto.PRODUCTOS p
                WHERE p.ID_CATEGORIA = :categoria
                AND p.ID_PRODUCTO != :id
                ORDER BY p.NOMBRE";

    $stid_rel = oci_parse($conn, $sql_rel);

    oci_bind_by_name($stid_rel, ":categoria", $producto['ID_CATEGORIA']);
    oci_bind_by_name($stid_rel, ":id", $producto['ID_PRODUCTO']);

    oci_execute($stid_rel);
    ?>

    <h2 style="margin-top:60px; text-align:center;">
        Productos relacionados
    </h2>

    <div style="
        display:grid;
        grid-template-columns:repeat(4,1fr);
        gap:20px;
        margin-top:20px;
    ">

    <?php while ($rel = oci_fetch_assoc($stid_rel)) { ?>

        <a href="?page=productos&id=<?php echo $rel['ID_PRODUCTO']; ?>" style="text-decoration:none; color:black;">

            <div style="
                background:white;
                padding:15px;
                border-radius:10px;
                box-shadow:0 2px 6px rgba(0,0,0,0.1);
                transition:0.3s;
            "
            onmouseover="this.style.transform='scale(1.05)'"
            onmouseout="this.style.transform='scale(1)'">

                <img 
                    src="<?php echo $rel['IMAGEN']; ?>" 
                    style="width:100%; height:120px; object-fit:contain;"
                >

                <h4><?php echo $rel['NOMBRE']; ?></h4>

                <p style="color:#1a73e8; font-weight:bold;">
                    $<?php echo $rel['PRECIO']; ?>
                </p>

            </div>

        </a>

    <?php } ?>

    </div>

</div>

<?php
    } else {
        echo "<p style='text-align:center;'>Producto no encontrado</p>";
    }

} else {
    echo "<p style='text-align:center;'>No se seleccionó ningún producto</p>";
}
?>
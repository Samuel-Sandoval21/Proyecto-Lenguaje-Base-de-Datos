<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/ProductoModel.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $producto = getProductoById($conn, (int)$id);

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
            <?php
            if (session_status() === PHP_SESSION_NONE) session_start();
            $currentUrl = 'index.php?page=producto&id=' . $producto['ID_PRODUCTO'];
            $logueado = !empty($_SESSION['usuario']);
            ?>
            <?php if ($logueado): ?>
                <form action="controllers/carrito_action.php" method="POST">
                    <input type="hidden" name="accion"      value="agregar">
                    <input type="hidden" name="id_producto" value="<?php echo $producto['ID_PRODUCTO']; ?>">
                    <input type="hidden" name="cantidad"    value="1">
                    <input type="hidden" name="redirect"    value="<?php echo htmlspecialchars($currentUrl); ?>">
                    <button type="submit" style="background:#1a73e8;color:white;border:none;padding:18px;border-radius:10px;font-size:18px;cursor:pointer;width:100%;transition:0.3s;"
                        onmouseover="this.style.background='#1558b0'"
                        onmouseout="this.style.background='#1a73e8'">
                        🛒 Agregar al carrito
                    </button>
                </form>
            <?php else: ?>
                <button
                    onclick="abrirLoginConRedirect('<?php echo htmlspecialchars($currentUrl); ?>')"
                    style="background:#1a73e8;color:white;border:none;padding:18px;border-radius:10px;font-size:18px;cursor:pointer;width:100%;transition:0.3s;"
                    onmouseover="this.style.background='#1558b0'"
                    onmouseout="this.style.background='#1a73e8'">
                    🛒 Agregar al carrito
                </button>
            <?php endif; ?>

        </div>

    </div>

    <!-- PRODUCTOS RELACIONADOS -->
    <?php
    $relacionados = getProductosRelacionados($conn, (int)$producto['ID_CATEGORIA'], (int)$producto['ID_PRODUCTO']);
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

    <?php
    foreach ($relacionados as $row) {
        include __DIR__ . '/components/info_producto.php';
    }
    ?>

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
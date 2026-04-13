<?php
session_start();

$carrito = $_SESSION['carrito'] ?? [];

if (empty($carrito)) {
    echo "<h1>🛒 Carrito</h1>";
    echo "<p>Tu carrito está vacío.</p>";
    return;
}

$total = 0;
?>

<h1 style="margin-bottom:30px;">🛒 Mi carrito</h1>

<div class="cart-grid">
    <?php foreach ($carrito as $producto) { 
        $subtotal = $producto['PRECIO'] * $producto['CANTIDAD'];
        $total += $subtotal;
    ?>
        <div class="cart-item">
            <img src="<?= $producto['IMAGEN'] ?>" alt="<?= $producto['NOMBRE'] ?>">
            
            <div class="cart-info">
                <h3><?= $producto['NOMBRE'] ?></h3>
                <p>Marca: <?= $producto['NOMBRE_MARCA'] ?></p>
                <p>Precio: ₡<?= number_format($producto['PRECIO'], 2) ?></p>
                <p>Cantidad: <?= $producto['CANTIDAD'] ?></p>
                <p><strong>Subtotal: ₡<?= number_format($subtotal, 2) ?></strong></p>
            </div>
        </div>
    <?php } ?>
</div>

<div class="cart-total">
    <h2>Total: ₡<?= number_format($total, 2) ?></h2>
</div>
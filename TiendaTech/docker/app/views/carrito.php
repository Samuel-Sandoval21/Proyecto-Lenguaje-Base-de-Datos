<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['usuario'])) {
    header('Location: index.php?login=1&redirect=index.php?page=carrito');
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/CarritoModel.php';
require_once __DIR__ . '/../models/MetodoPagoModel.php';

$conn = getOperadorConnection();

$idUsuario   = (int)$_SESSION['usuario']['ID_USUARIO'];
$items       = $conn ? getCarritoByUsuario($conn, $idUsuario)   : [];
$metodos     = $conn ? getMetodosPago($conn)                    : [];
$total       = array_sum(array_column($items, 'SUBTOTAL'));
$errorMetodo = isset($_GET['error']) && $_GET['error'] === 'metodo';
$agregado    = isset($_GET['agregado']);
?>
<div class="carrito-page">
    <div class="carrito-titulo">🛒 Mi carrito</div>

    <?php if ($agregado): ?>
        <div class="carrito-toast">✅ Producto agregado correctamente.</div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <div class="carrito-vacio">
            <div class="icon">🛒</div>
            <p>Tu carrito está vacío.</p>
            <a href="index.php" class="btn-seguir">Seguir comprando</a>
        </div>

    <?php else: ?>

        <div class="carrito-lista">
            <?php foreach ($items as $item): ?>
                <div class="carrito-item">
                    <img src="<?= htmlspecialchars($item['IMAGEN'] ?? '') ?>" alt="<?= htmlspecialchars($item['NOMBRE']) ?>">

                    <div class="carrito-item-info">
                        <div class="carrito-item-nombre"><?= htmlspecialchars($item['NOMBRE']) ?></div>
                        <div class="carrito-item-marca"><?= htmlspecialchars($item['NOMBRE_MARCA']) ?></div>
                        <div class="carrito-item-precio">₡<?= number_format((float)$item['PRECIO'], 2) ?> c/u</div>
                    </div>

                    <div class="cant-form">
                        <form action="controllers/carrito_action.php" method="POST" class="cant-mini-form">
                            <input type="hidden" name="accion"      value="<?= $item['CANTIDAD'] - 1 <= 0 ? 'eliminar' : 'actualizar' ?>">
                            <input type="hidden" name="id_producto" value="<?= $item['ID_PRODUCTO'] ?>">
                            <input type="hidden" name="cantidad"    value="<?= $item['CANTIDAD'] - 1 ?>">
                            <button type="submit" class="cant-btn">−</button>
                        </form>
                        <form action="controllers/carrito_action.php" method="POST" class="cant-mini-form">
                            <input type="hidden" name="accion"      value="actualizar">
                            <input type="hidden" name="id_producto" value="<?= $item['ID_PRODUCTO'] ?>">
                            <input type="number" name="cantidad" value="<?= $item['CANTIDAD'] ?>" min="1" max="<?= $item['STOCK'] ?>" class="cant-num" onchange="this.form.submit()">
                        </form>
                        <form action="controllers/carrito_action.php" method="POST" class="cant-mini-form">
                            <input type="hidden" name="accion"      value="actualizar">
                            <input type="hidden" name="id_producto" value="<?= $item['ID_PRODUCTO'] ?>">
                            <input type="hidden" name="cantidad"    value="<?= $item['CANTIDAD'] + 1 ?>">
                            <button type="submit" class="cant-btn" <?= $item['CANTIDAD'] >= $item['STOCK'] ? 'disabled' : '' ?>>+</button>
                        </form>
                    </div>

                    <div class="carrito-item-subtotal">₡<?= number_format((float)$item['SUBTOTAL'], 2) ?></div>

                    <form action="controllers/carrito_action.php" method="POST">
                        <input type="hidden" name="accion"      value="eliminar">
                        <input type="hidden" name="id_producto" value="<?= $item['ID_PRODUCTO'] ?>">
                        <button type="submit" class="btn-eliminar" title="Quitar">🗑</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Form de vaciar FUERA del formPago para evitar anidamiento -->
        <form action="controllers/carrito_action.php" method="POST" id="formVaciar">
            <input type="hidden" name="accion"      value="vaciar">
            <input type="hidden" name="id_producto" value="0">
        </form>

        <form action="controllers/pago_action.php" method="POST" id="formPago">
        <div class="carrito-bottom-layout">

            <div class="carrito-resumen carrito-resumen--grow">
                <div>
                    <div class="carrito-total-label">Total (<?= count($items) ?> producto<?= count($items) > 1 ? 's' : '' ?>)</div>
                    <div class="carrito-total-monto">₡<?= number_format($total, 2) ?></div>
                </div>
                <div class="carrito-acciones">
                    <button type="submit" form="formVaciar" class="btn-vaciar">🗑 Vaciar</button>
                    <button type="submit" form="formPago" class="btn-comprar" id="btnPagar" disabled>
                        Proceder al pago →
                    </button>
                </div>
            </div>

            <div class="carrito-metodos-card">
                <div class="metodos-titulo">💳 Método de pago</div>
                <?php if ($errorMetodo): ?>
                    <div class="error-metodo">⚠ Seleccioná un método de pago.</div>
                <?php endif; ?>
                <ul class="metodo-lista">
                    <?php foreach ($metodos as $met): ?>
                        <li>
                            <input type="radio" name="id_metodo" id="met_<?= $met['ID_METODO'] ?>"
                                   value="<?= $met['ID_METODO'] ?>" class="metodo-radio" form="formPago"
                                   onchange="document.getElementById('btnPagar').disabled=false;">
                            <label for="met_<?= $met['ID_METODO'] ?>" class="metodo-label">
                                <span class="metodo-check-icon">✓</span>
                                <?= htmlspecialchars($met['NOMBRE']) ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        </div>
        </form>

    <?php endif; ?>
</div>

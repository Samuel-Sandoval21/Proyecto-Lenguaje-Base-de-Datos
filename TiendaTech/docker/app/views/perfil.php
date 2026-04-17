<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['usuario'])) {
    header('Location: index.php?login=1');
    exit;
}

$u         = $_SESSION['usuario'];
$nombre    = htmlspecialchars($u['NOMBRE'] ?? $u['USERNAME'] ?? 'Usuario');
$user      = htmlspecialchars($u['USERNAME'] ?? '');
$rol       = htmlspecialchars($u['ROL'] ?? '');
$idCliente = $u['ID_CLIENTE'] ?? null;

$partes    = explode(' ', trim($u['NOMBRE'] ?? $u['USERNAME'] ?? 'U'));
$iniciales = mb_strtoupper(mb_substr($partes[0], 0, 1));
if (isset($partes[1])) $iniciales .= mb_strtoupper(mb_substr($partes[1], 0, 1));

// ── Compras anteriores (usando ClienteModel) ──────────────────────────
require_once __DIR__ . '/../models/ClienteModel.php';

$compras   = [];
$idCliente = isset($u['ID_CLIENTE']) ? (int)$u['ID_CLIENTE'] : 0;

if ($idCliente > 0) {
    $connP = getOperadorConnection();
    if ($connP) {
        $compras = getVentasByCliente($connP, $idCliente);
    }
}

?>
<div class="perfil-page">

    <!-- ── Tarjeta de datos de usuario ── -->
    <?php if (isset($_GET['nuevo'])): ?>
        <div class="perfil-bienvenida">
            ✅ ¡Cuenta creada exitosamente! Bienvenido/a, <?= $nombre ?>.
        </div>
    <?php endif; ?>

    <div class="perfil-card">
        <div class="perfil-top">
            <div class="perfil-avatar-grande"><?= $iniciales ?></div>
            <div>
                <div class="perfil-nombre"><?= $nombre ?></div>
                <div class="perfil-username">@<?= $user ?></div>
                <?php
                $rolClass = strtolower($rol);
                if (!in_array($rolClass, ['admin','vendedor','cliente'])) $rolClass = 'default';
                ?>
                <span class="perfil-rol-badge <?= $rolClass ?>"><?= $rol ?></span>
            </div>
        </div>

        <hr class="perfil-divider">

        <ul class="perfil-info-list">
            <li><span>Nombre completo</span><span><?= $nombre ?></span></li>
            <li><span>Usuario</span><span><?= $user ?></span></li>
            <li><span>Rol</span><span><?= $rol ?></span></li>
        </ul>

        <a href="controllers/logout.php" class="btn-logout">&#x2192; Cerrar sesión</a>
    </div>

    <!-- ── Compras anteriores ── -->
    <div class="perfil-card">
        <div class="compras-titulo">🧾 Compras anteriores</div>

        <?php if (empty($compras)): ?>
            <div class="compras-empty">No tenés compras registradas aún.</div>
        <?php else: ?>
            <?php foreach ($compras as $idDet => $pedido): ?>
                <div class="pedido-card">
                    <div class="pedido-header">
                        <div class="pedido-header-left">
                            <strong>Pedido #<?= $idDet ?></strong>
                            &nbsp;·&nbsp;
                            <?= htmlspecialchars($pedido['fecha'] ?? '') ?>
                        </div>
                        <div class="pedido-header-right">
                            <span class="pedido-metodo"><?= htmlspecialchars($pedido['metodo']) ?></span>
                            <span class="pedido-total">₡<?= number_format($pedido['total'], 2) ?></span>
                        </div>
                    </div>
                    <div class="pedido-items">
                        <?php foreach ($pedido['items'] as $item): ?>
                            <div class="pedido-item">
                                <img src="<?= htmlspecialchars($item['IMAGEN'] ?? '') ?>" alt="<?= htmlspecialchars($item['PRODUCTO']) ?>">
                                <div class="pedido-item-info">
                                    <div class="pedido-item-name"><?= htmlspecialchars($item['PRODUCTO']) ?></div>
                                    <div class="pedido-item-meta">
                                        Cant: <?= $item['CANTIDAD'] ?> &nbsp;·&nbsp;
                                        ₡<?= number_format($item['PRECIO_UNITARIO'], 2) ?> c/u
                                    </div>
                                </div>
                                <div class="pedido-item-sub">₡<?= number_format($item['SUBTOTAL'], 2) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

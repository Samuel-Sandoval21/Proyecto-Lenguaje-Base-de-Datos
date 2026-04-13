<?php

$perfil_error = $_SESSION['perfil_error'] ?? null;
$perfil_success = $_SESSION['perfil_success'] ?? null;
unset($_SESSION['perfil_error'], $_SESSION['perfil_success']);

?>

<div class="container py-5">
    <div class="row g-4">

        <!-- Columna izquierda: datos de perfil -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">
                        <i class="bi bi-person-circle me-2"></i>Mi perfil
                    </h2>

                    <?php if ($perfil_error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($perfil_error) ?></div>
                    <?php endif; ?>

                    <?php if ($perfil_success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($perfil_success) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($usuario)): ?>
                        <form action="index.php?route=perfil" method="post" novalidate>
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre completo</label>
                                <input type="text" class="form-control" id="nombre" name="nombre"
                                    value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="email"
                                    value="<?= htmlspecialchars($usuario['email']) ?>" disabled>
                                <div class="form-text">El correo no se puede modificar.</div>
                            </div>

                            <div class="mb-3">
                                <label for="telefono" class="form-label">Telefono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono"
                                    value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="cv_url" class="form-label">Link al CV (PDF)</label>
                                <input type="url" class="form-control" id="cv_url" name="cv_url"
                                    placeholder="https://.../mi_cv.pdf"
                                    value="<?= htmlspecialchars($usuario['cv_url'] ?? '') ?>">
                                <div class="form-text">Opcional: enlace a un CV almacenado en la nube.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tipo de usuario</label>
                                <input type="text" class="form-control"
                                    value="<?= htmlspecialchars($usuario['tipo_usuario']) ?>" disabled>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Guardar cambios
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <p class="text-muted">No se pudo cargar la información del usuario.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna derecha: postulaciones -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">
                        <i class="bi bi-clipboard-check me-2"></i>Mis postulaciones
                    </h2>

                    <?php if (empty($postulaciones)): ?>
                        <p class="text-muted mb-0">Aún no has realizado ninguna postulación.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Oferta</th>
                                        <th>Empresa</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($postulaciones as $post): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($post['titulo_oferta']) ?></td>
                                            <td><?= htmlspecialchars($post['empresa_nombre']) ?></td>
                                            <td>
                                                <?php
                                                $estado = $post['estado'];
                                                $badgeClass = 'secondary';
                                                switch ($estado) {
                                                    case 'POSTULADA':
                                                        $badgeClass = 'primary';
                                                        break;
                                                    case 'PRESELECCION':
                                                        $badgeClass = 'info';
                                                        break;
                                                    case 'ENTREVISTA':
                                                        $badgeClass = 'warning';
                                                        break;
                                                    case 'OFERTA':
                                                        $badgeClass = 'success';
                                                        break;
                                                    case 'CONTRATADA':
                                                        $badgeClass = 'success';
                                                        break;
                                                    case 'RECHAZADA':
                                                        $badgeClass = 'danger';
                                                        break;
                                                    case 'RETIRADA':
                                                        $badgeClass = 'secondary';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge text-bg-<?= $badgeClass ?>">
                                                    <?= htmlspecialchars($estado) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($post['fecha_postulacion']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
</div>
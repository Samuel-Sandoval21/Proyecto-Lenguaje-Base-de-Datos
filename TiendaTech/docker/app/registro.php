<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/db.php';

// Si ya está logueado, redirigir al perfil
if (!empty($_SESSION['usuario'])) {
    header('Location: /index.php?page=perfil');
    exit;
}

$error   = '';
$success = false;
$campos  = [
    'username'  => '',
    'nombre'    => '',
    'apellido'  => '',
    'correo'    => '',
    'direccion' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username']   ?? '');
    $nombre     = trim($_POST['nombre']     ?? '');
    $apellido   = trim($_POST['apellido']   ?? '');
    $password   = trim($_POST['password']   ?? '');
    $password2  = trim($_POST['password2']  ?? '');
    $correo     = trim($_POST['correo']     ?? '');
    $direccion  = trim($_POST['direccion']  ?? '');

    // Guardar para repoblar el form si hay error
    $campos = compact('username', 'nombre', 'apellido', 'correo', 'direccion');

    // Validaciones
    if (empty($username) || empty($nombre) || empty($apellido) ||
        empty($password) || empty($correo) || empty($direccion)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (strlen($username) < 3) {
        $error = 'El nombre de usuario debe tener al menos 3 caracteres.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'El usuario solo puede tener letras, números y guión bajo.';
    } elseif (strlen($password) < 4) {
        $error = 'La contraseña debe tener al menos 4 caracteres.';
    } elseif ($password !== $password2) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';
    } else {
        $conn = getOperadorConnection();
        if (!$conn) {
            $error = 'Error de conexión a la base de datos.';
        } else {
            require_once __DIR__ . '/models/RegistroModel.php';

            if (usernameExiste($conn, $username)) {
                $error = 'Ese nombre de usuario ya está en uso.';
            } elseif (correoExiste($conn, $correo)) {
                $error = 'Ese correo ya está registrado.';
            } else {
                try {
                    $idUsuario = registrarUsuario($conn, $username, $password,
                                                  $nombre, $apellido, $correo, $direccion);

                    // Iniciar sesión automáticamente
                    $_SESSION['usuario'] = [
                        'ID_USUARIO' => $idUsuario,
                        'USERNAME'   => $username,
                        'ROL'        => 'CLIENTE',
                        'NOMBRE'     => $nombre . ' ' . $apellido,
                        'ID_CLIENTE' => null, // se actualizará en perfil si hace falta
                    ];

                    // Obtener ID_CLIENTE para la sesión
                    $sqlGetCli = "SELECT ID_CLIENTE FROM USUARIOS WHERE ID_USUARIO = TO_NUMBER(:idus)";
                    $stGetCli  = oci_parse($conn, $sqlGetCli);
                    $idUsStr   = (string)$idUsuario;
                    oci_bind_by_name($stGetCli, ':idus', $idUsStr);
                    oci_execute($stGetCli);
                    $rowCli = oci_fetch_assoc($stGetCli);
                    $_SESSION['usuario']['ID_CLIENTE'] = (int)($rowCli['ID_CLIENTE'] ?? 0);

                    header('Location: /index.php?page=perfil&nuevo=1');
                    exit;
                } catch (Exception $e) {
                    $error = 'Error al registrar: ' . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Cuenta — TiendaTech</title>
    <link rel="stylesheet" href="views/components/style.css?v=<?= time() ?>">
    
</head>
<body>

    <div class="reg-topbar">
        <a href="index.php" class="reg-topbar-logo">TiendaTech</a>
        <span class="reg-topbar-sep">›</span>
        <span class="reg-topbar-title">Crear cuenta</span>
    </div>

    <div class="reg-wrap">
        <div class="reg-card">
            <h1 class="reg-titulo">Crear cuenta</h1>
            <p class="reg-subtitulo">Completá los datos para registrarte.</p>

            <?php if ($error): ?>
                <div class="reg-error">⚠ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="registro.php" autocomplete="off">
                <div class="reg-grid">

                    <div class="reg-field full">
                        <label class="reg-label" for="username">Usuario <span>*</span></label>
                        <input type="text" id="username" name="username" class="reg-input"
                               placeholder="Ej: juanp" maxlength="50"
                               value="<?= htmlspecialchars($campos['username']) ?>" required>
                    </div>

                    <div class="reg-field">
                        <label class="reg-label" for="nombre">Nombre <span>*</span></label>
                        <input type="text" id="nombre" name="nombre" class="reg-input"
                               placeholder="Juan" maxlength="100"
                               value="<?= htmlspecialchars($campos['nombre']) ?>" required>
                    </div>

                    <div class="reg-field">
                        <label class="reg-label" for="apellido">Apellido <span>*</span></label>
                        <input type="text" id="apellido" name="apellido" class="reg-input"
                               placeholder="Pérez" maxlength="100"
                               value="<?= htmlspecialchars($campos['apellido']) ?>" required>
                    </div>

                    <div class="reg-field">
                        <label class="reg-label" for="password">Contraseña <span>*</span></label>
                        <div class="reg-pw-wrap">
                            <input type="password" id="password" name="password" class="reg-input"
                                   placeholder="••••••••" required>
                            <button type="button" class="reg-pw-toggle" onclick="togglePw('password')">👁</button>
                        </div>
                    </div>

                    <div class="reg-field">
                        <label class="reg-label" for="password2">Confirmar contraseña <span>*</span></label>
                        <div class="reg-pw-wrap">
                            <input type="password" id="password2" name="password2" class="reg-input"
                                   placeholder="••••••••" required>
                            <button type="button" class="reg-pw-toggle" onclick="togglePw('password2')">👁</button>
                        </div>
                    </div>

                    <div class="reg-field full">
                        <label class="reg-label" for="correo">Correo electrónico <span>*</span></label>
                        <input type="email" id="correo" name="correo" class="reg-input"
                               placeholder="juan@gmail.com" maxlength="150"
                               value="<?= htmlspecialchars($campos['correo']) ?>" required>
                    </div>

                    <div class="reg-field full">
                        <label class="reg-label" for="direccion">Dirección <span>*</span></label>
                        <input type="text" id="direccion" name="direccion" class="reg-input"
                               placeholder="San José, Costa Rica" maxlength="250"
                               value="<?= htmlspecialchars($campos['direccion']) ?>" required>
                    </div>

                </div>

                <button type="submit" class="reg-submit">Crear cuenta →</button>
            </form>

            <p class="reg-login">
                ¿Ya tenés cuenta? <a href="index.php?login=1">Iniciar sesión</a>
            </p>
        </div>
    </div>

    <script>
    function togglePw(id) {
        var input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
    }
    </script>
</body>
</html>

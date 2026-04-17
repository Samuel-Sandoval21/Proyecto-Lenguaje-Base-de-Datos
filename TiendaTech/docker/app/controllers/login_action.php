<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';

$conn = getOperadorConnection();
if (!$conn) {
    $_SESSION['login_error'] = 'Error de conexión a la base de datos.';
    $redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? 'index.php';
    header('Location: /index.php?login=1&redirect=' . urlencode($redirect));
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? 'index.php';

if ($username === '' || $password === '') {
    $_SESSION['login_error'] = 'Por favor ingresá usuario y contraseña.';
    header('Location: /index.php?login=1&redirect=' . urlencode($redirect));
    exit;
}

$sql = "SELECT U.ID_USUARIO, U.USERNAME, U.PASSWORD, U.ROL, U.ID_CLIENTE,
               C.NOMBRE, C.APELLIDO
        FROM AdminProyecto.USUARIOS U
        LEFT JOIN AdminProyecto.CLIENTES C ON C.ID_CLIENTE = U.ID_CLIENTE
        WHERE UPPER(U.USERNAME) = UPPER(:username)";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ':username', $username);
oci_execute($stid);
$row = oci_fetch_assoc($stid);

if (!$row || $row['PASSWORD'] !== $password) {
    $_SESSION['login_error'] = 'Usuario o contraseña incorrectos.';
    header('Location: /index.php?login=1&redirect=' . urlencode($redirect));
    exit;
}

$_SESSION['usuario'] = [
    'ID_USUARIO' => $row['ID_USUARIO'],
    'USERNAME'   => $row['USERNAME'],
    'ROL'        => $row['ROL'],
    'NOMBRE'     => !empty($row['NOMBRE']) ? $row['NOMBRE'] . ' ' . $row['APELLIDO'] : $row['USERNAME'],
    'ID_CLIENTE' => $row['ID_CLIENTE'] ?? null,
];

unset($_SESSION['login_error']);

header('Location: ' . (strpos($redirect, 'http') === 0 ? $redirect : '/' . ltrim($redirect, '/')));
exit;

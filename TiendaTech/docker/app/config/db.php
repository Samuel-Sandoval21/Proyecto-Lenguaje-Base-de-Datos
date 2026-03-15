<?php
$usuario = "Consulta";
$pass = "Consulta2026#";
$base_datos = "//host.docker.internal:1521/orcl";

$conn = oci_connect($usuario, $pass, $base_datos, 'AL32UTF8');

if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
} else {
    echo "Conexión exitosa a Oracle 19c";
}

?>
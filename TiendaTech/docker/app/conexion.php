<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = oci_connect(
    "AdminProyecto",
    "AdminProyecto2026#",
    "host.docker.internal:1521/ORCL"
);

if (!$conn) {
    $e = oci_error();
    echo "Error: " . $e['message'];
    exit;
}

echo "Conectado a Oracle correctamente";

?>
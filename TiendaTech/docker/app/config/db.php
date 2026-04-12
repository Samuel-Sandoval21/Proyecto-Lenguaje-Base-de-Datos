<?php
$usuario = "Consulta";
$pass = "Consulta2026#";
$base_datos = "//host.docker.internal:1521/orcl";

$conn = oci_connect($usuario, $pass, $base_datos, 'AL32UTF8');

?>
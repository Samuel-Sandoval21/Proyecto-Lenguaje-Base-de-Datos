
<?php

$conn = oci_connect(
    "Consulta",
    "Consulta2026#",
    "(DESCRIPTION=
        (ADDRESS=(PROTOCOL=TCP)(HOST=host.docker.internal)(PORT=1521))
        (CONNECT_DATA=(SERVICE_NAME=ORCL))
    )"
);

if (!$conn) {
    $e = oci_error();
    echo $e['message'];
}
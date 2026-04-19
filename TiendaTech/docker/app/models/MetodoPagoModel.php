<?php

function getMetodosPago($conn): array {
    $sql  = "SELECT ID_METODO, NOMBRE FROM AdminProyecto.METODOS_PAGO ORDER BY ID_METODO";
    $stid = oci_parse($conn, $sql);
    oci_execute($stid);
    $metodos = [];
    while ($row = oci_fetch_assoc($stid)) {
        $metodos[] = $row;
    }
    return $metodos;
}

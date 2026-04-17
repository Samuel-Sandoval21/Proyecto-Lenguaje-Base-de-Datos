<?php

function registrarVenta($conn, int $idCliente, int $idMetodo, array $items): bool {
    $cliStr = (string)$idCliente;
    $metStr = (string)$idMetodo;

    // 1. Insertar cabecera en DETALLE_VENTAS
    $sqlDet = "INSERT INTO AdminProyecto.DETALLE_VENTAS (ID_CLIENTE, ID_METODO)
               VALUES (TO_NUMBER(:idcliente), TO_NUMBER(:idmetodo))";
    $stDet  = oci_parse($conn, $sqlDet);
    oci_bind_by_name($stDet, ':idcliente', $cliStr);
    oci_bind_by_name($stDet, ':idmetodo',  $metStr);
    oci_execute($stDet);

    // 2. Obtener ID_DETALLE recién creado
    $sqlLast = "SELECT MAX(ID_DETALLE) AS ID_DETALLE FROM AdminProyecto.DETALLE_VENTAS WHERE ID_CLIENTE = TO_NUMBER(:idcliente)";
    $stLast  = oci_parse($conn, $sqlLast);
    oci_bind_by_name($stLast, ':idcliente', $cliStr);
    oci_execute($stLast);
    $rowLast   = oci_fetch_assoc($stLast);
    $idDetalle = (string)$rowLast['ID_DETALLE'];

    // 3. Insertar cada línea en VENTAS
    $sqlVenta = "INSERT INTO AdminProyecto.VENTAS (ID_DETALLE, ID_PRODUCTO, CANTIDAD, PRECIO_UNITARIO)
                 VALUES (TO_NUMBER(:iddetalle), TO_NUMBER(:idproducto), TO_NUMBER(:cantidad), TO_NUMBER(:precio))";
    $stVenta  = oci_parse($conn, $sqlVenta);

    foreach ($items as $item) {
        $pidStr    = (string)$item['ID_PRODUCTO'];
        $cantStr   = (string)$item['CANTIDAD'];
        $precioStr = (string)$item['PRECIO'];
        oci_bind_by_name($stVenta, ':iddetalle',  $idDetalle);
        oci_bind_by_name($stVenta, ':idproducto', $pidStr);
        oci_bind_by_name($stVenta, ':cantidad',   $cantStr);
        oci_bind_by_name($stVenta, ':precio',     $precioStr);
        oci_execute($stVenta);
    }

    oci_commit($conn);
    return true;
}

<?php

function getCarritoByUsuario($conn, int $idUsuario): array {
    $sql = "SELECT
                C.ID_CARRITO,
                C.ID_PRODUCTO,
                C.CANTIDAD,
                P.NOMBRE,
                P.IMAGEN,
                P.PRECIO,
                P.STOCK,
                M.NOMBRE_MARCA,
                (C.CANTIDAD * P.PRECIO) AS SUBTOTAL
            FROM AdminProyecto.CARRITO C
            JOIN AdminProyecto.PRODUCTOS P ON P.ID_PRODUCTO = C.ID_PRODUCTO
            JOIN AdminProyecto.MARCAS    M ON M.ID_MARCA    = P.ID_MARCA
            WHERE C.ID_USUARIO = TO_NUMBER(:idusuario)
            ORDER BY C.FECHA_AGREGADO ASC";

    $stid   = oci_parse($conn, $sql);
    $uidStr = (string)$idUsuario;
    oci_bind_by_name($stid, ':idusuario', $uidStr);
    oci_execute($stid);

    $items = [];
    while ($row = oci_fetch_assoc($stid)) {
        $items[] = $row;
    }
    return $items;
}

function agregarAlCarrito($conn, int $idUsuario, int $idProducto, int $cantidad = 1): void {
    $uidStr = (string)$idUsuario;
    $pidStr = (string)$idProducto;

    $sqlCheck = "SELECT ID_CARRITO, CANTIDAD FROM AdminProyecto.CARRITO
                 WHERE ID_USUARIO = TO_NUMBER(:idusuario) AND ID_PRODUCTO = TO_NUMBER(:idproducto)";
    $stCheck  = oci_parse($conn, $sqlCheck);
    oci_bind_by_name($stCheck, ':idusuario',  $uidStr);
    oci_bind_by_name($stCheck, ':idproducto', $pidStr);
    oci_execute($stCheck);
    $existe = oci_fetch_assoc($stCheck);

    if ($existe) {
        $nuevaCantidad = (string)($existe['CANTIDAD'] + $cantidad);
        $sqlUpdate = "UPDATE AdminProyecto.CARRITO SET CANTIDAD = :cant
                      WHERE ID_USUARIO = TO_NUMBER(:idusuario) AND ID_PRODUCTO = TO_NUMBER(:idproducto)";
        $stUpdate  = oci_parse($conn, $sqlUpdate);
        oci_bind_by_name($stUpdate, ':cant',       $nuevaCantidad);
        oci_bind_by_name($stUpdate, ':idusuario',  $uidStr);
        oci_bind_by_name($stUpdate, ':idproducto', $pidStr);
        oci_execute($stUpdate);
    } else {
        $cantStr   = (string)$cantidad;
        $sqlInsert = "INSERT INTO AdminProyecto.CARRITO (ID_USUARIO, ID_PRODUCTO, CANTIDAD)
                      VALUES (TO_NUMBER(:idusuario), TO_NUMBER(:idproducto), TO_NUMBER(:cantidad))";
        $stInsert  = oci_parse($conn, $sqlInsert);
        oci_bind_by_name($stInsert, ':idusuario',  $uidStr);
        oci_bind_by_name($stInsert, ':idproducto', $pidStr);
        oci_bind_by_name($stInsert, ':cantidad',   $cantStr);
        oci_execute($stInsert);
    }
    oci_commit($conn);
}

function actualizarCantidadCarrito($conn, int $idUsuario, int $idProducto, int $cantidad): void {
    if ($cantidad <= 0) {
        eliminarDelCarrito($conn, $idUsuario, $idProducto);
        return;
    }
    $cantStr = (string)$cantidad;
    $uidStr  = (string)$idUsuario;
    $pidStr  = (string)$idProducto;
    $sqlUpd  = "UPDATE AdminProyecto.CARRITO SET CANTIDAD = :cant
                WHERE ID_USUARIO = TO_NUMBER(:idusuario) AND ID_PRODUCTO = TO_NUMBER(:idproducto)";
    $stUpd   = oci_parse($conn, $sqlUpd);
    oci_bind_by_name($stUpd, ':cant',       $cantStr);
    oci_bind_by_name($stUpd, ':idusuario',  $uidStr);
    oci_bind_by_name($stUpd, ':idproducto', $pidStr);
    oci_execute($stUpd);
    oci_commit($conn);
}

function eliminarDelCarrito($conn, int $idUsuario, int $idProducto): void {
    $uidStr = (string)$idUsuario;
    $pidStr = (string)$idProducto;
    $sqlDel = "DELETE FROM AdminProyecto.CARRITO
               WHERE ID_USUARIO = TO_NUMBER(:idusuario) AND ID_PRODUCTO = TO_NUMBER(:idproducto)";
    $stDel  = oci_parse($conn, $sqlDel);
    oci_bind_by_name($stDel, ':idusuario',  $uidStr);
    oci_bind_by_name($stDel, ':idproducto', $pidStr);
    oci_execute($stDel);
    oci_commit($conn);
}

function vaciarCarrito($conn, int $idUsuario): void {
    $uidStr = (string)$idUsuario;
    $sqlVac = "DELETE FROM AdminProyecto.CARRITO WHERE ID_USUARIO = TO_NUMBER(:idusuario)";
    $stVac  = oci_parse($conn, $sqlVac);
    oci_bind_by_name($stVac, ':idusuario', $uidStr);
    oci_execute($stVac);
    oci_commit($conn);
}

function contarItemsCarrito($conn, int $idUsuario): int {
    $uidStr = (string)$idUsuario;
    $sql    = "SELECT SUM(CANTIDAD) AS TOTAL FROM AdminProyecto.CARRITO WHERE ID_USUARIO = TO_NUMBER(:idusuario)";
    $stid   = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ':idusuario', $uidStr);
    oci_execute($stid);
    $row = oci_fetch_assoc($stid);
    return (int)($row['TOTAL'] ?? 0);
}

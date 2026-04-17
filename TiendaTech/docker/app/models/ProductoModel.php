<?php

function getBaseProductosSQL(): string {
    return "SELECT
                p.ID_PRODUCTO,
                p.NOMBRE,
                p.DESCRIPCION,
                p.PRECIO,
                p.STOCK,
                p.IMAGEN,
                p.ID_CATEGORIA,
                m.NOMBRE_MARCA,
                c.NOMBRE_CATEGORIA
            FROM AdminProyecto.PRODUCTOS p
            JOIN AdminProyecto.MARCAS m
                ON p.ID_MARCA = m.ID_MARCA
            JOIN AdminProyecto.CATEGORIAS c
                ON p.ID_CATEGORIA = c.ID_CATEGORIA";
}

function getProductoById($conn, int $id): ?array {
    $sql  = getBaseProductosSQL() . " WHERE p.ID_PRODUCTO = :id";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ':id', $id);
    oci_execute($stid);
    $row = oci_fetch_assoc($stid);
    return $row ?: null;
}

function getProductosRelacionados($conn, int $idCategoria, int $excluirId): array {
    $sql  = getBaseProductosSQL() . "
            WHERE p.ID_CATEGORIA = :categoria AND p.ID_PRODUCTO != :id
            ORDER BY p.NOMBRE";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ':categoria', $idCategoria);
    oci_bind_by_name($stid, ':id',        $excluirId);
    oci_execute($stid);
    $rows = [];
    while ($row = oci_fetch_assoc($stid)) {
        $rows[] = $row;
    }
    return $rows;
}

function getTodosLosProductos($conn): array {
    $sql  = getBaseProductosSQL() . " ORDER BY p.NOMBRE";
    $stid = oci_parse($conn, $sql);
    oci_execute($stid);
    $rows = [];
    while ($row = oci_fetch_assoc($stid)) {
        $rows[] = $row;
    }
    return $rows;
}

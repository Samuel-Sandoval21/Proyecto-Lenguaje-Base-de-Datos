<?php

function getNombreCategoriaSQL(): string {
    return "SELECT NOMBRE_CATEGORIA
            FROM AdminProyecto.CATEGORIAS
            WHERE ID_CATEGORIA = :cat";
}

function getProductosPorCategoriaSQL(): string {
    return "SELECT 
                p.ID_PRODUCTO,
                p.NOMBRE,
                p.DESCRIPCION,
                p.PRECIO,
                p.STOCK,
                p.IMAGEN,
                m.NOMBRE_MARCA
            FROM AdminProyecto.PRODUCTOS p
            JOIN AdminProyecto.MARCAS m 
                ON p.ID_MARCA = m.ID_MARCA
            WHERE p.ID_CATEGORIA = :cat
            ORDER BY p.NOMBRE";
}

function construirBusquedaSQL(array $palabras): array {
    $condiciones = [];
    $binds = [];

    foreach (array_values($palabras) as $i => $palabra) {
        $key = ":t$i";

        $condiciones[] = "(
            AdminProyecto.NORMALIZAR_TEXTO(p.NOMBRE) LIKE AdminProyecto.NORMALIZAR_TEXTO($key)
            OR AdminProyecto.NORMALIZAR_TEXTO(p.DESCRIPCION) LIKE AdminProyecto.NORMALIZAR_TEXTO($key)
            OR AdminProyecto.NORMALIZAR_TEXTO(m.NOMBRE_MARCA) LIKE AdminProyecto.NORMALIZAR_TEXTO($key)
        )";

        $binds[$key] = "%$palabra%";
    }

    $where = implode(' OR ', $condiciones);

    $sql = "SELECT 
                p.ID_PRODUCTO,
                p.NOMBRE,
                p.DESCRIPCION,
                p.PRECIO,
                p.STOCK,
                p.IMAGEN,
                m.NOMBRE_MARCA
            FROM AdminProyecto.PRODUCTOS p
            JOIN AdminProyecto.MARCAS m 
                ON p.ID_MARCA = m.ID_MARCA
            WHERE $where
            ORDER BY p.NOMBRE";

    return [
        'sql' => $sql,
        'binds' => $binds
    ];
}
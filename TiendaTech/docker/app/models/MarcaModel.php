<?php

function getMarcasPorCategoriaSQL() {
    return "SELECT DISTINCT m.ID_MARCA, m.NOMBRE_MARCA
            FROM AdminProyecto.PRODUCTOS p
            JOIN AdminProyecto.MARCAS m 
                ON p.ID_MARCA = m.ID_MARCA
            WHERE p.ID_CATEGORIA = :cat
            ORDER BY m.NOMBRE_MARCA";
}

function getAtributosPorCategoriaSQL() {
    return "SELECT DISTINCT pa.NOMBRE_ATRIBUTO, pa.VALOR
            FROM AdminProyecto.PRODUCTO_ATRIBUTOS pa
            JOIN AdminProyecto.PRODUCTOS p
                ON pa.ID_PRODUCTO = p.ID_PRODUCTO
            WHERE p.ID_CATEGORIA = :cat
            ORDER BY pa.NOMBRE_ATRIBUTO, pa.VALOR";
}

function getAtributosProductosSQL() {
    return "SELECT p.ID_PRODUCTO, pa.NOMBRE_ATRIBUTO, pa.VALOR
            FROM AdminProyecto.PRODUCTO_ATRIBUTOS pa
            JOIN AdminProyecto.PRODUCTOS p
                ON pa.ID_PRODUCTO = p.ID_PRODUCTO
            WHERE p.ID_CATEGORIA = :cat
            ORDER BY p.ID_PRODUCTO, pa.NOMBRE_ATRIBUTO";
}

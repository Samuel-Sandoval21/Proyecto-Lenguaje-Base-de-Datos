<?php

function getNombreCategoriaSQL() {
    return "SELECT NOMBRE_CATEGORIA
            FROM AdminProyecto.CATEGORIAS
            WHERE ID_CATEGORIA = :cat";
}

function getProductosPorCategoriaSQL() {
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
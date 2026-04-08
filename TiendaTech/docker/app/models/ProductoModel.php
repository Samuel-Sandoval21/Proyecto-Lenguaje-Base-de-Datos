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
?>
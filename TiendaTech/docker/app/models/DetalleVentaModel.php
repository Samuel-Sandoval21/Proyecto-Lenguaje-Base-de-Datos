<?php
require_once __DIR__ . '/ProductoModel.php';

function getBaseDetalleVentaSQL(): string {
    return "SELECT 
                base.*,
                SUM(dv.CANTIDAD) AS TOTAL_VENDIDO
            FROM AdminProyecto.DETALLE_VENTA dv
            JOIN (" . getBaseProductosSQL() . ") base
                ON dv.ID_PRODUCTO = base.ID_PRODUCTO
            GROUP BY
                base.ID_PRODUCTO,
                base.NOMBRE,
                base.DESCRIPCION,
                base.PRECIO,
                base.STOCK,
                base.IMAGEN,
                base.ID_CATEGORIA,
                base.NOMBRE_MARCA,
                base.NOMBRE_CATEGORIA
            ORDER BY TOTAL_VENDIDO DESC
            FETCH FIRST 6 ROWS ONLY";
}
?>
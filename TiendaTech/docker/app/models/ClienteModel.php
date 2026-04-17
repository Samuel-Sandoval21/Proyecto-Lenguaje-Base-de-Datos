<?php

function getVentasByCliente($conn, int $idCliente): array {
    $sql = "SELECT
                DV.ID_DETALLE,
                TO_CHAR(DV.FECHA_VENTA, 'DD/MM/YYYY') AS FECHA_VENTA,
                MP.NOMBRE        AS METODO,
                P.NOMBRE         AS PRODUCTO,
                P.IMAGEN,
                V.CANTIDAD,
                V.PRECIO_UNITARIO,
                (V.CANTIDAD * V.PRECIO_UNITARIO) AS SUBTOTAL
            FROM AdminProyecto.DETALLE_VENTAS DV
            JOIN AdminProyecto.VENTAS V         ON V.ID_DETALLE  = DV.ID_DETALLE
            JOIN AdminProyecto.PRODUCTOS P      ON P.ID_PRODUCTO = V.ID_PRODUCTO
            JOIN AdminProyecto.METODOS_PAGO MP  ON MP.ID_METODO  = DV.ID_METODO
            WHERE DV.ID_CLIENTE = TO_NUMBER(:idcliente)
            ORDER BY DV.ID_DETALLE DESC";

    $stid   = oci_parse($conn, $sql);
    $cliStr = (string)$idCliente;
    oci_bind_by_name($stid, ':idcliente', $cliStr);
    oci_execute($stid);

    $compras      = [];
    $pedidoActual = null;
    while ($row = oci_fetch_assoc($stid)) {
        $idDet = $row['ID_DETALLE'];
        if ($pedidoActual !== $idDet) {
            $pedidoActual   = $idDet;
            $compras[$idDet] = [
                'fecha'  => $row['FECHA_VENTA'],
                'metodo' => $row['METODO'],
                'items'  => [],
                'total'  => 0,
            ];
        }
        $compras[$idDet]['items'][] = $row;
        $compras[$idDet]['total']  += (float)$row['SUBTOTAL'];
    }
    return $compras;
}

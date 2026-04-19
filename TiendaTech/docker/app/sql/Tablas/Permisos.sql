-- =========================
-- PERMISOS
-- =========================
GRANT SELECT ON AdminProyecto.PRODUCTOS TO Consulta;

GRANT SELECT ON AdminProyecto.MARCAS TO Consulta;

GRANT SELECT ON AdminProyecto.CATEGORIAS TO Consulta;

GRANT SELECT ON AdminProyecto.DETALLE_VENTA TO Consulta;

GRANT SELECT ON AdminProyecto.PRODUCTO_ATRIBUTOS TO Consulta;

GRANT SELECT ON AdminProyecto.USUARIOS TO Consulta;

GRANT SELECT ON AdminProyecto.USUARIOS TO Operador;

GRANT SELECT ON AdminProyecto.CLIENTES TO Operador;

GRANT SELECT, INSERT, UPDATE, DELETE ON AdminProyecto.CARRITO TO Operador;

GRANT SELECT ON AdminProyecto.PRODUCTOS TO Operador;

GRANT SELECT ON AdminProyecto.MARCAS TO Operador;

GRANT SELECT, INSERT ON AdminProyecto.DETALLE_VENTAS TO Operador;

GRANT SELECT, INSERT ON AdminProyecto.VENTAS TO Operador;

GRANT SELECT ON AdminProyecto.METODOS_PAGO TO Operador;

GRANT SELECT, INSERT ON AdminProyecto.CLIENTES TO Operador;

GRANT SELECT, INSERT ON AdminProyecto.USUARIOS TO Operador;

GRANT SELECT, INSERT ON AdminProyecto.CORREOS TO Operador;

GRANT INSERT ON AdminProyecto.DIRECCIONES TO Operador;

GRANT SELECT ON AdminProyecto.CATEGORIAS TO Operador;

GRANT SELECT ON AdminProyecto.PRODUCTO_ATRIBUTOS TO Operador;

GRANT SELECT ON AdminProyecto.VENTAS TO Consulta;

GRANT SELECT ON AdminProyecto.REGISTRO TO Consulta;
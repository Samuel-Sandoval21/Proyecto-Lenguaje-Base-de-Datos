-- =============================================
-- TABLA CARRITO
-- Guarda los productos en carrito por usuario
-- Ejecutar como AdminProyecto
-- =============================================

CREATE TABLE CARRITO (
    ID_CARRITO    NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    ID_USUARIO    NUMBER NOT NULL,
    ID_PRODUCTO   NUMBER NOT NULL,
    CANTIDAD      NUMBER DEFAULT 1 NOT NULL CHECK (CANTIDAD > 0),
    FECHA_AGREGADO DATE DEFAULT SYSDATE,
    CONSTRAINT FK_CARRITO_USUARIO
        FOREIGN KEY (ID_USUARIO)
        REFERENCES USUARIOS(ID_USUARIO)
        ON DELETE CASCADE,
    CONSTRAINT FK_CARRITO_PRODUCTO
        FOREIGN KEY (ID_PRODUCTO)
        REFERENCES PRODUCTOS(ID_PRODUCTO)
        ON DELETE CASCADE,
    -- Un usuario no puede tener el mismo producto duplicado
    CONSTRAINT UK_CARRITO_USUARIO_PRODUCTO
        UNIQUE (ID_USUARIO, ID_PRODUCTO)
);

-- Permiso de lectura/escritura para el rol operador
GRANT SELECT, INSERT, UPDATE, DELETE ON AdminProyecto.CARRITO TO Operador;

-- ======================================================
-- AUTOR: Samuel Sandoval
-- PARTE: Procedimientos Almacenados (Stored Procedures)
-- PROYECTO: TiendaTech - Lenguajes de Base de Datos SC-504
-- FECHA: Abril 2026
-- DESCRIPCION: Implementacion de procedimientos almacenados
--              para la gestion de ventas, inventario y reportes
-- ======================================================

SET SERVEROUTPUT ON;

-- ======================================================
-- PROCEDIMIENTO 1: Registrar una nueva venta completa
-- ======================================================
-- DESCRIPCION: Este procedimiento recibe los datos de una venta
--              (cliente, metodo de pago, y productos con cantidades)
--              y realiza toda la transaccion de forma atomica.
--              Formato de P_PRODUCTOS: 'ID_PRODUCTO:CANTIDAD,ID_PRODUCTO:CANTIDAD'
--              Ejemplo: '1:2,17:1,21:3'
-- ======================================================

CREATE OR REPLACE PROCEDURE SP_REGISTRAR_VENTA (
    P_ID_CLIENTE        IN  NUMBER,
    P_ID_METODO         IN  NUMBER,
    P_PRODUCTOS         IN  VARCHAR2,
    P_TOTAL_VENTA       OUT NUMBER,
    P_ID_VENTA_GENERADA OUT NUMBER,
    P_MENSAJE           OUT VARCHAR2
)
IS
    v_pos               NUMBER;
    v_comma_pos         NUMBER;
    v_colon_pos         NUMBER;
    v_item              VARCHAR2(100);
    v_id_prod           NUMBER;
    v_cantidad          NUMBER;
    v_nuevo_id_detalle  NUMBER;  -- ID_DETALLE de la cabecera
    v_precio_unitario   PRODUCTOS.PRECIO%TYPE;
    v_contador          NUMBER := 0;
    v_total_calc        NUMBER := 0;
    v_stock_actual      NUMBER;
BEGIN
    -- Iniciar punto de restauracion para la transaccion
    SAVEPOINT INICIO_VENTA;
    
    -- 1. Validar que el cliente exista
    BEGIN
        SELECT 1 INTO v_contador FROM CLIENTES WHERE ID_CLIENTE = P_ID_CLIENTE;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            P_MENSAJE := 'ERROR: El cliente con ID ' || P_ID_CLIENTE || ' no existe.';
            P_TOTAL_VENTA := -1;
            P_ID_VENTA_GENERADA := -1;
            RETURN;
    END;
    
    -- 2. Insertar la cabecera de la venta en DETALLE_VENTAS
    INSERT INTO DETALLE_VENTAS (ID_CLIENTE, ID_METODO)
    VALUES (P_ID_CLIENTE, P_ID_METODO)
    RETURNING ID_DETALLE INTO v_nuevo_id_detalle;
    
    -- 3. Procesar el string de productos (Ejemplo: "1:2,17:1,21:3")
    v_pos := 1;
    v_contador := 0;
    v_total_calc := 0;
    
    LOOP
        -- Extraer cada item separado por coma
        v_comma_pos := INSTR(P_PRODUCTOS || ',', ',', v_pos);
        EXIT WHEN v_comma_pos = 0;
        
        v_item := SUBSTR(P_PRODUCTOS, v_pos, v_comma_pos - v_pos);
        
        -- Separar ID_PRODUCTO y CANTIDAD usando ':' como delimitador
        v_colon_pos := INSTR(v_item, ':');
        IF v_colon_pos > 0 THEN
            v_id_prod := TO_NUMBER(SUBSTR(v_item, 1, v_colon_pos - 1));
            v_cantidad := TO_NUMBER(SUBSTR(v_item, v_colon_pos + 1));
            
            -- Validar cantidad positiva
            IF v_cantidad <= 0 THEN
                RAISE_APPLICATION_ERROR(-20011, 'La cantidad debe ser mayor a 0 para el producto ' || v_id_prod);
            END IF;
            
            -- Obtener el precio unitario actual del producto y validar stock
            BEGIN
                SELECT PRECIO, STOCK INTO v_precio_unitario, v_stock_actual
                FROM PRODUCTOS
                WHERE ID_PRODUCTO = v_id_prod;
                
                -- Validar stock disponible
                IF v_stock_actual < v_cantidad THEN
                    RAISE_APPLICATION_ERROR(-20013, 'Stock insuficiente para producto ' || v_id_prod || 
                                              '. Disponible: ' || v_stock_actual || ', Solicitado: ' || v_cantidad);
                END IF;
                
            EXCEPTION
                WHEN NO_DATA_FOUND THEN
                    RAISE_APPLICATION_ERROR(-20010, 'El producto con ID ' || v_id_prod || ' no existe.');
            END;
            
            -- Insertar el detalle de venta en VENTAS
            INSERT INTO VENTAS (ID_DETALLE, ID_PRODUCTO, CANTIDAD, PRECIO_UNITARIO)
            VALUES (v_nuevo_id_detalle, v_id_prod, v_cantidad, v_precio_unitario);
            
            -- Actualizar el stock del producto
            UPDATE PRODUCTOS
            SET STOCK = STOCK - v_cantidad
            WHERE ID_PRODUCTO = v_id_prod;
            
            v_total_calc := v_total_calc + (v_cantidad * v_precio_unitario);
            v_contador := v_contador + 1;
        END IF;
        
        v_pos := v_comma_pos + 1;
    END LOOP;
    
    -- Validar que se haya agregado al menos un producto
    IF v_contador = 0 THEN
        RAISE_APPLICATION_ERROR(-20012, 'No se especificaron productos para la venta.');
    END IF;
    
    P_TOTAL_VENTA := v_total_calc;
    P_ID_VENTA_GENERADA := v_nuevo_id_detalle;
    P_MENSAJE := 'EXITO: Venta registrada correctamente. ID Venta: ' || v_nuevo_id_detalle || 
                 ' - Total: $' || TO_CHAR(P_TOTAL_VENTA, '999,999.99');
    
    COMMIT;
    
EXCEPTION
    WHEN OTHERS THEN
        ROLLBACK TO INICIO_VENTA;
        P_TOTAL_VENTA := -1;
        P_ID_VENTA_GENERADA := -1;
        P_MENSAJE := 'ERROR: ' || SQLERRM;
        DBMS_OUTPUT.PUT_LINE('Error en SP_REGISTRAR_VENTA: ' || SQLERRM);
END SP_REGISTRAR_VENTA;
/

-- ======================================================
-- PROCEDIMIENTO 2: Ajustar stock de un producto con auditoria (CORREGIDO)
-- ======================================================

CREATE OR REPLACE PROCEDURE SP_AJUSTAR_STOCK (
    P_ID_PRODUCTO   IN  NUMBER,
    P_NUEVO_STOCK   IN  NUMBER,
    P_MOTIVO        IN  VARCHAR2 DEFAULT 'Ajuste manual',
    P_RESULTADO     OUT VARCHAR2
)
IS
    v_stock_actual  PRODUCTOS.STOCK%TYPE;
    v_nombre_prod   PRODUCTOS.NOMBRE%TYPE;
BEGIN
    -- Validar que el producto exista y obtener datos actuales
    BEGIN
        SELECT NOMBRE, STOCK INTO v_nombre_prod, v_stock_actual
        FROM PRODUCTOS
        WHERE ID_PRODUCTO = P_ID_PRODUCTO;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            P_RESULTADO := 'ERROR: El producto con ID ' || P_ID_PRODUCTO || ' no existe.';
            RETURN;
    END;
    
    -- Validar que el nuevo stock no sea negativo
    IF P_NUEVO_STOCK < 0 THEN
        P_RESULTADO := 'ERROR: El stock no puede ser negativo. Valor actual: ' || v_stock_actual;
        RETURN;
    END IF;
    
    -- Validar que el nuevo stock sea diferente al actual
    IF P_NUEVO_STOCK = v_stock_actual THEN
        P_RESULTADO := 'INFO: El stock ya es ' || v_stock_actual || '. No se realizaron cambios.';
        RETURN;
    END IF;
    
    -- Actualizar el stock
    UPDATE PRODUCTOS
    SET STOCK = P_NUEVO_STOCK
    WHERE ID_PRODUCTO = P_ID_PRODUCTO;
    
    -- Insertar registro en auditoria
    INSERT INTO AUDITORIA_SISTEMA (
        TABLA_AFECTADA, OPERACION, ID_REGISTRO, CAMPO_MODIFICADO,
        VALOR_ANTERIOR, VALOR_NUEVO, USUARIO_DB
    ) VALUES (
        'PRODUCTOS', 'UPDATE_STOCK', P_ID_PRODUCTO, 'STOCK',
        TO_CHAR(v_stock_actual), TO_CHAR(P_NUEVO_STOCK), USER
    );
    
    COMMIT;
    
    -- Generar mensaje de resultado
    P_RESULTADO := 'EXITO: Stock de "' || v_nombre_prod || '" actualizado de ' || 
                   v_stock_actual || ' a ' || P_NUEVO_STOCK || '. Motivo: ' || P_MOTIVO;
    
EXCEPTION
    WHEN OTHERS THEN
        ROLLBACK;
        P_RESULTADO := 'ERROR INESPERADO: ' || SQLERRM;
END SP_AJUSTAR_STOCK;
/

-- ======================================================
-- PROCEDIMIENTO 3: Reporte de ventas por cliente (CORREGIDO)
-- ======================================================

CREATE OR REPLACE PROCEDURE SP_REPORTE_VENTAS_CLIENTE (
    P_ID_CLIENTE IN NUMBER,
    P_CURSOR_REPORTE OUT SYS_REFCURSOR,
    P_NOMBRE_CLIENTE OUT VARCHAR2,
    P_TOTAL_GASTADO OUT NUMBER
)
IS
    v_nombre CLIENTES.NOMBRE%TYPE;
    v_apellido CLIENTES.APELLIDO%TYPE;
BEGIN
    -- Validar y obtener datos del cliente
    SELECT NOMBRE, APELLIDO INTO v_nombre, v_apellido
    FROM CLIENTES
    WHERE ID_CLIENTE = P_ID_CLIENTE;
    
    P_NOMBRE_CLIENTE := v_nombre || ' ' || v_apellido;
    
    -- Calcular total gastado por el cliente sumando los detalles de VENTAS
    SELECT NVL(SUM(V.CANTIDAD * V.PRECIO_UNITARIO), 0) INTO P_TOTAL_GASTADO
    FROM DETALLE_VENTAS DV
    INNER JOIN VENTAS V ON V.ID_DETALLE = DV.ID_DETALLE
    WHERE DV.ID_CLIENTE = P_ID_CLIENTE;
    
    -- Abrir cursor REF con el detalle de ventas
    OPEN P_CURSOR_REPORTE FOR
        SELECT 
            DV.ID_DETALLE AS ID_VENTA,
            DV.FECHA_VENTA,
            MP.NOMBRE AS METODO_PAGO,
            (SELECT SUM(V2.CANTIDAD * V2.PRECIO_UNITARIO) 
             FROM VENTAS V2 
             WHERE V2.ID_DETALLE = DV.ID_DETALLE) AS TOTAL,
            (SELECT COUNT(*) FROM VENTAS V3 WHERE V3.ID_DETALLE = DV.ID_DETALLE) AS CANTIDAD_PRODUCTOS
        FROM DETALLE_VENTAS DV
        INNER JOIN METODOS_PAGO MP ON MP.ID_METODO = DV.ID_METODO
        WHERE DV.ID_CLIENTE = P_ID_CLIENTE
        ORDER BY DV.FECHA_VENTA DESC;
    
EXCEPTION
    WHEN NO_DATA_FOUND THEN
        P_NOMBRE_CLIENTE := 'Cliente no encontrado';
        P_TOTAL_GASTADO := 0;
        OPEN P_CURSOR_REPORTE FOR SELECT NULL AS ID_VENTA FROM DUAL WHERE 1=0;
    WHEN OTHERS THEN
        P_NOMBRE_CLIENTE := 'ERROR';
        P_TOTAL_GASTADO := -1;
        OPEN P_CURSOR_REPORTE FOR SELECT NULL AS ID_VENTA FROM DUAL WHERE 1=0;
        DBMS_OUTPUT.PUT_LINE('Error en SP_REPORTE_VENTAS_CLIENTE: ' || SQLERRM);
END SP_REPORTE_VENTAS_CLIENTE;
/

-- ======================================================
-- PROCEDIMIENTO 4: Obtener detalles de un producto especifico
-- ======================================================

CREATE OR REPLACE PROCEDURE SP_OBTENER_PRODUCTO (
    P_ID_PRODUCTO   IN  NUMBER,
    P_NOMBRE        OUT VARCHAR2,
    P_CATEGORIA     OUT VARCHAR2,
    P_MARCA         OUT VARCHAR2,
    P_PRECIO        OUT NUMBER,
    P_STOCK         OUT NUMBER,
    P_EXISTE        OUT BOOLEAN
)
IS
BEGIN
    SELECT 
        P.NOMBRE,
        C.NOMBRE_CATEGORIA,
        M.NOMBRE_MARCA,
        P.PRECIO,
        P.STOCK
    INTO 
        P_NOMBRE,
        P_CATEGORIA,
        P_MARCA,
        P_PRECIO,
        P_STOCK
    FROM PRODUCTOS P
    INNER JOIN CATEGORIAS C ON C.ID_CATEGORIA = P.ID_CATEGORIA
    INNER JOIN MARCAS M ON M.ID_MARCA = P.ID_MARCA
    WHERE P.ID_PRODUCTO = P_ID_PRODUCTO;
    
    P_EXISTE := TRUE;
    
EXCEPTION
    WHEN NO_DATA_FOUND THEN
        P_EXISTE := FALSE;
        P_NOMBRE := NULL;
        P_CATEGORIA := NULL;
        P_MARCA := NULL;
        P_PRECIO := NULL;
        P_STOCK := NULL;
END SP_OBTENER_PRODUCTO;
/

-- ======================================================
-- PRUEBAS DE LOS PROCEDIMIENTOS
-- ======================================================

-- PRUEBA 1: Ajustar stock de un producto
DECLARE
    v_resultado VARCHAR2(500);
BEGIN
    DBMS_OUTPUT.PUT_LINE('=== PRUEBA 1: AJUSTAR STOCK ===');
    SP_AJUSTAR_STOCK(1, 50, 'Reabastecimiento por proveedor', v_resultado);
    DBMS_OUTPUT.PUT_LINE(v_resultado);
END;
/

-- PRUEBA 2: Registrar una nueva venta
DECLARE
    v_total NUMBER;
    v_id_venta NUMBER;
    v_mensaje VARCHAR2(500);
BEGIN
    DBMS_OUTPUT.PUT_LINE('=== PRUEBA 2: REGISTRAR VENTA ===');
    -- Formato: 'ID_PRODUCTO:CANTIDAD,ID_PRODUCTO:CANTIDAD'
    SP_REGISTRAR_VENTA(1, 2, '17:1,18:2', v_total, v_id_venta, v_mensaje);
    DBMS_OUTPUT.PUT_LINE(v_mensaje);
END;
/

-- PRUEBA 3: Reporte de ventas por cliente
DECLARE
    v_cursor SYS_REFCURSOR;
    v_nombre_cliente VARCHAR2(200);
    v_total_gastado NUMBER;
    v_id_venta NUMBER;
    v_fecha DATE;
    v_metodo VARCHAR2(50);
    v_total NUMBER;
    v_cantidad_prod NUMBER;
BEGIN
    DBMS_OUTPUT.PUT_LINE('=== PRUEBA 3: REPORTE DE VENTAS ===');
    SP_REPORTE_VENTAS_CLIENTE(1, v_cursor, v_nombre_cliente, v_total_gastado);
    
    DBMS_OUTPUT.PUT_LINE('Cliente: ' || v_nombre_cliente);
    DBMS_OUTPUT.PUT_LINE('Total gastado: $' || TO_CHAR(v_total_gastado, '999,999.99'));
    DBMS_OUTPUT.PUT_LINE('----------------------------------------');
    
    LOOP
        FETCH v_cursor INTO v_id_venta, v_fecha, v_metodo, v_total, v_cantidad_prod;
        EXIT WHEN v_cursor%NOTFOUND;
        
        DBMS_OUTPUT.PUT_LINE('Venta ID: ' || v_id_venta || 
                             ' | Fecha: ' || TO_CHAR(v_fecha, 'DD/MM/YYYY') || 
                             ' | Metodo: ' || v_metodo ||
                             ' | Total: $' || TO_CHAR(v_total, '999,999.99') ||
                             ' | Productos: ' || v_cantidad_prod);
    END LOOP;
    
    CLOSE v_cursor;
END;
/





CREATE OR REPLACE PROCEDURE PRODUCTOS_COMPRADOS
IS
    V_NOMBRE   VARCHAR2(100);
    V_MARCA    VARCHAR2(100);
    V_CANTIDAD NUMBER;

    CURSOR C_PRODUCTOS IS
        SELECT
            PRODUCTOS.NOMBRE,
            MARCAS.NOMBRE_MARCA,
            SUM(VENTAS.CANTIDAD) AS TOTAL_COMPRADO
        FROM VENTAS
        JOIN PRODUCTOS ON PRODUCTOS.ID_PRODUCTO = VENTAS.ID_PRODUCTO
        JOIN MARCAS    ON MARCAS.ID_MARCA        = PRODUCTOS.ID_MARCA
        GROUP BY PRODUCTOS.NOMBRE, MARCAS.NOMBRE_MARCA
        ORDER BY TOTAL_COMPRADO DESC;

BEGIN
    DBMS_OUTPUT.PUT_LINE('====================================');
    DBMS_OUTPUT.PUT_LINE('PRODUCTOS MÁS COMPRADOS');
    DBMS_OUTPUT.PUT_LINE('====================================');

    OPEN C_PRODUCTOS;
    LOOP
        FETCH C_PRODUCTOS INTO V_NOMBRE, V_MARCA, V_CANTIDAD;
        EXIT WHEN C_PRODUCTOS%NOTFOUND;

        DBMS_OUTPUT.PUT_LINE(
            V_NOMBRE || ' (' || V_MARCA || ')' ||
            ' — Comprados: ' || V_CANTIDAD
        );
    END LOOP;
    CLOSE C_PRODUCTOS;

EXCEPTION
    WHEN NO_DATA_FOUND THEN
        DBMS_OUTPUT.PUT_LINE('No hay ventas registradas.');
    WHEN OTHERS THEN
        RAISE_APPLICATION_ERROR(-20010, 'Error en PRODUCTOS_COMPRADOS: ' || SQLERRM);
END;
/

EXEC PRODUCTOS_COMPRADOS;
-- Verificar cambios en las tablas
SELECT ID_PRODUCTO, NOMBRE, STOCK FROM PRODUCTOS WHERE ID_PRODUCTO = 1;
SELECT * FROM DETALLE_VENTAS ORDER BY ID_DETALLE DESC;
SELECT * FROM VENTAS ORDER BY ID_VENTA DESC;
SELECT * FROM AUDITORIA_SISTEMA ORDER BY ID_AUDITORIA DESC;
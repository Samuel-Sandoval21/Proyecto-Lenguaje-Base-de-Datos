-- ======================================================
-- AUTOR: Samuel Sandoval
-- PARTE: Paquetes (Packages)
-- PROYECTO: TiendaTech - Lenguajes de Base de Datos SC-504
-- FECHA: Abril 2026
-- DESCRIPCION: Implementacion de paquetes para agrupar
--              procedimientos y funciones relacionadas
-- ======================================================

SET SERVEROUTPUT ON;

-- ======================================================
-- ESPECIFICACION DEL PAQUETE (PARTE PUBLICA)
-- ======================================================

CREATE OR REPLACE PACKAGE PKG_TIENDATECH AS
    
    -- ==================================================
    -- CONSTANTES PUBLICAS
    -- ==================================================
    C_VERSION           CONSTANT VARCHAR2(20) := '1.0.0';
    C_ERROR_GENERAL     CONSTANT NUMBER := -1;
    C_EXITO             CONSTANT NUMBER := 0;
    
    -- ==================================================
    -- PROCEDIMIENTOS PUBLICOS
    -- ==================================================
    
    -- Registra una nueva venta completa
    PROCEDURE REGISTRAR_VENTA (
        P_ID_CLIENTE        IN  NUMBER,
        P_ID_METODO         IN  NUMBER,
        P_PRODUCTOS         IN  VARCHAR2,
        P_TOTAL_VENTA       OUT NUMBER,
        P_ID_VENTA_GENERADA OUT NUMBER,
        P_MENSAJE           OUT VARCHAR2
    );
    
    -- Ajusta el stock de un producto con auditoria
    PROCEDURE AJUSTAR_STOCK (
        P_ID_PRODUCTO   IN  NUMBER,
        P_NUEVO_STOCK   IN  NUMBER,
        P_MOTIVO        IN  VARCHAR2 DEFAULT 'Ajuste manual',
        P_RESULTADO     OUT VARCHAR2
    );
    
    -- Genera reporte de ventas por cliente
    PROCEDURE REPORTE_VENTAS_CLIENTE (
        P_ID_CLIENTE        IN  NUMBER,
        P_CURSOR_REPORTE    OUT SYS_REFCURSOR,
        P_NOMBRE_CLIENTE    OUT VARCHAR2,
        P_TOTAL_GASTADO     OUT NUMBER
    );
    
    -- ==================================================
    -- FUNCIONES PUBLICAS
    -- ==================================================
    
    -- Obtiene el nombre de un producto por su ID
    FUNCTION OBTENER_NOMBRE_PRODUCTO(P_ID_PRODUCTO NUMBER) RETURN VARCHAR2;
    
    -- Calcula el total de ventas de un cliente
    FUNCTION TOTAL_CLIENTE(P_ID_CLIENTE NUMBER) RETURN NUMBER;
    
    -- Verifica si un producto tiene suficiente stock
    FUNCTION VERIFICAR_STOCK(P_ID_PRODUCTO NUMBER, P_CANTIDAD NUMBER) RETURN BOOLEAN;
    
END PKG_TIENDATECH;
/

-- ======================================================
-- CUERPO DEL PAQUETE (PARTE PRIVADA - IMPLEMENTACION)
-- ======================================================

CREATE OR REPLACE PACKAGE BODY PKG_TIENDATECH AS

    -- ==================================================
    -- IMPLEMENTACION DE PROCEDIMIENTOS
    -- ==================================================
    
    -- --------------------------------------------------
    -- REGISTRAR_VENTA (CORREGIDO)
    -- --------------------------------------------------
    PROCEDURE REGISTRAR_VENTA (
        P_ID_CLIENTE        IN  NUMBER,
        P_ID_METODO         IN  NUMBER,
        P_PRODUCTOS         IN  VARCHAR2,
        P_TOTAL_VENTA       OUT NUMBER,
        P_ID_VENTA_GENERADA OUT NUMBER,
        P_MENSAJE           OUT VARCHAR2
    ) IS
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
        v_stock_disponible  NUMBER;
    BEGIN
        SAVEPOINT INICIO_VENTA;
        
        -- Validar cliente
        BEGIN
            SELECT 1 INTO v_contador FROM CLIENTES WHERE ID_CLIENTE = P_ID_CLIENTE;
        EXCEPTION
            WHEN NO_DATA_FOUND THEN
                P_MENSAJE := 'ERROR: Cliente no existe. ID: ' || P_ID_CLIENTE;
                P_TOTAL_VENTA := C_ERROR_GENERAL;
                P_ID_VENTA_GENERADA := C_ERROR_GENERAL;
                RETURN;
        END;
        
        -- Crear cabecera de venta en DETALLE_VENTAS
        INSERT INTO DETALLE_VENTAS (ID_CLIENTE, ID_METODO)
        VALUES (P_ID_CLIENTE, P_ID_METODO)
        RETURNING ID_DETALLE INTO v_nuevo_id_detalle;
        
        -- Procesar productos
        v_pos := 1;
        v_contador := 0;
        v_total_calc := 0;
        
        LOOP
            v_comma_pos := INSTR(P_PRODUCTOS || ',', ',', v_pos);
            EXIT WHEN v_comma_pos = 0;
            
            v_item := SUBSTR(P_PRODUCTOS, v_pos, v_comma_pos - v_pos);
            v_colon_pos := INSTR(v_item, ':');
            
            IF v_colon_pos > 0 THEN
                v_id_prod := TO_NUMBER(SUBSTR(v_item, 1, v_colon_pos - 1));
                v_cantidad := TO_NUMBER(SUBSTR(v_item, v_colon_pos + 1));
                
                -- Validar cantidad
                IF v_cantidad <= 0 THEN
                    RAISE_APPLICATION_ERROR(-20011, 'Cantidad invalida para producto ' || v_id_prod);
                END IF;
                
                -- Obtener precio y validar stock
                SELECT PRECIO, STOCK INTO v_precio_unitario, v_stock_disponible
                FROM PRODUCTOS
                WHERE ID_PRODUCTO = v_id_prod;
                
                -- Validar stock disponible
                IF v_stock_disponible < v_cantidad THEN
                    RAISE_APPLICATION_ERROR(-20013, 'Stock insuficiente para producto ' || v_id_prod || 
                                              '. Disponible: ' || v_stock_disponible);
                END IF;
                
                -- Insertar detalle en VENTAS
                INSERT INTO VENTAS (ID_DETALLE, ID_PRODUCTO, CANTIDAD, PRECIO_UNITARIO)
                VALUES (v_nuevo_id_detalle, v_id_prod, v_cantidad, v_precio_unitario);
                
                -- Actualizar stock
                UPDATE PRODUCTOS 
                SET STOCK = STOCK - v_cantidad
                WHERE ID_PRODUCTO = v_id_prod;
                
                v_total_calc := v_total_calc + (v_cantidad * v_precio_unitario);
                v_contador := v_contador + 1;
            END IF;
            
            v_pos := v_comma_pos + 1;
        END LOOP;
        
        IF v_contador = 0 THEN
            RAISE_APPLICATION_ERROR(-20012, 'No se especificaron productos.');
        END IF;
        
        P_TOTAL_VENTA := v_total_calc;
        P_ID_VENTA_GENERADA := v_nuevo_id_detalle;
        P_MENSAJE := 'EXITO: Venta ' || v_nuevo_id_detalle || ' registrada. Total: $' || 
                     TO_CHAR(P_TOTAL_VENTA, '999,999.99');
        
        COMMIT;
        
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK TO INICIO_VENTA;
            P_TOTAL_VENTA := C_ERROR_GENERAL;
            P_ID_VENTA_GENERADA := C_ERROR_GENERAL;
            P_MENSAJE := 'ERROR: ' || SQLERRM;
    END REGISTRAR_VENTA;
    
    -- --------------------------------------------------
    -- AJUSTAR_STOCK
    -- --------------------------------------------------
    PROCEDURE AJUSTAR_STOCK (
        P_ID_PRODUCTO   IN  NUMBER,
        P_NUEVO_STOCK   IN  NUMBER,
        P_MOTIVO        IN  VARCHAR2 DEFAULT 'Ajuste manual',
        P_RESULTADO     OUT VARCHAR2
    ) IS
        v_stock_actual  PRODUCTOS.STOCK%TYPE;
        v_nombre_prod   PRODUCTOS.NOMBRE%TYPE;
    BEGIN
        SELECT NOMBRE, STOCK INTO v_nombre_prod, v_stock_actual
        FROM PRODUCTOS
        WHERE ID_PRODUCTO = P_ID_PRODUCTO;
        
        IF P_NUEVO_STOCK < 0 THEN
            P_RESULTADO := 'ERROR: Stock no puede ser negativo.';
            RETURN;
        END IF;
        
        IF P_NUEVO_STOCK = v_stock_actual THEN
            P_RESULTADO := 'INFO: Stock ya es ' || v_stock_actual;
            RETURN;
        END IF;
        
        UPDATE PRODUCTOS SET STOCK = P_NUEVO_STOCK WHERE ID_PRODUCTO = P_ID_PRODUCTO;
        
        INSERT INTO AUDITORIA_SISTEMA (
            TABLA_AFECTADA, OPERACION, ID_REGISTRO, CAMPO_MODIFICADO,
            VALOR_ANTERIOR, VALOR_NUEVO, USUARIO_DB
        ) VALUES (
            'PRODUCTOS', 'UPDATE_STOCK', P_ID_PRODUCTO, 'STOCK',
            TO_CHAR(v_stock_actual), TO_CHAR(P_NUEVO_STOCK), USER
        );
        
        COMMIT;
        P_RESULTADO := 'EXITO: Stock de "' || v_nombre_prod || '" actualizado de ' || 
                       v_stock_actual || ' a ' || P_NUEVO_STOCK;
        
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            P_RESULTADO := 'ERROR: Producto ' || P_ID_PRODUCTO || ' no existe.';
        WHEN OTHERS THEN
            ROLLBACK;
            P_RESULTADO := 'ERROR: ' || SQLERRM;
    END AJUSTAR_STOCK;
    
    -- --------------------------------------------------
    -- REPORTE_VENTAS_CLIENTE (CORREGIDO - usando VENTAS para precios)
    -- --------------------------------------------------
    PROCEDURE REPORTE_VENTAS_CLIENTE (
        P_ID_CLIENTE        IN  NUMBER,
        P_CURSOR_REPORTE    OUT SYS_REFCURSOR,
        P_NOMBRE_CLIENTE    OUT VARCHAR2,
        P_TOTAL_GASTADO     OUT NUMBER
    ) IS
        v_nombre CLIENTES.NOMBRE%TYPE;
        v_apellido CLIENTES.APELLIDO%TYPE;
    BEGIN
        SELECT NOMBRE, APELLIDO INTO v_nombre, v_apellido
        FROM CLIENTES WHERE ID_CLIENTE = P_ID_CLIENTE;
        
        P_NOMBRE_CLIENTE := v_nombre || ' ' || v_apellido;
        
        -- Calcular total gastado sumando desde VENTAS (donde está PRECIO_UNITARIO)
        SELECT NVL(SUM(V.CANTIDAD * V.PRECIO_UNITARIO), 0) INTO P_TOTAL_GASTADO
        FROM DETALLE_VENTAS DV
        INNER JOIN VENTAS V ON V.ID_DETALLE = DV.ID_DETALLE
        WHERE DV.ID_CLIENTE = P_ID_CLIENTE;
        
        -- Abrir cursor con detalle de ventas
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
    END REPORTE_VENTAS_CLIENTE;
    
    -- ==================================================
    -- IMPLEMENTACION DE FUNCIONES
    -- ==================================================
    
    FUNCTION OBTENER_NOMBRE_PRODUCTO(P_ID_PRODUCTO NUMBER) RETURN VARCHAR2 IS
        v_nombre PRODUCTOS.NOMBRE%TYPE;
    BEGIN
        SELECT NOMBRE INTO v_nombre 
        FROM PRODUCTOS 
        WHERE ID_PRODUCTO = P_ID_PRODUCTO;
        RETURN v_nombre;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            RETURN 'Producto no encontrado';
    END OBTENER_NOMBRE_PRODUCTO;
    
    -- TOTAL_CLIENTE corregido: usa VENTAS para el cálculo
    FUNCTION TOTAL_CLIENTE(P_ID_CLIENTE NUMBER) RETURN NUMBER IS
        v_total NUMBER;
    BEGIN
        SELECT NVL(SUM(V.CANTIDAD * V.PRECIO_UNITARIO), 0) INTO v_total
        FROM DETALLE_VENTAS DV
        INNER JOIN VENTAS V ON V.ID_DETALLE = DV.ID_DETALLE
        WHERE DV.ID_CLIENTE = P_ID_CLIENTE;
        RETURN v_total;
    EXCEPTION
        WHEN OTHERS THEN
            RETURN 0;
    END TOTAL_CLIENTE;
    
    FUNCTION VERIFICAR_STOCK(P_ID_PRODUCTO NUMBER, P_CANTIDAD NUMBER) RETURN BOOLEAN IS
        v_stock PRODUCTOS.STOCK%TYPE;
    BEGIN
        SELECT STOCK INTO v_stock
        FROM PRODUCTOS
        WHERE ID_PRODUCTO = P_ID_PRODUCTO;
        
        RETURN v_stock >= P_CANTIDAD;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            RETURN FALSE;
    END VERIFICAR_STOCK;
    
END PKG_TIENDATECH;
/

-- ======================================================
-- PRUEBAS DEL PAQUETE
-- ======================================================

-- PRUEBA 1: Usar funciones del paquete
DECLARE
    v_nombre_producto VARCHAR2(100);
    v_total_cliente NUMBER;
    v_tiene_stock BOOLEAN;
BEGIN
    DBMS_OUTPUT.PUT_LINE('=== PRUEBA PAQUETE 1: FUNCIONES ===');
    
    v_nombre_producto := PKG_TIENDATECH.OBTENER_NOMBRE_PRODUCTO(17);
    DBMS_OUTPUT.PUT_LINE('Producto ID 17: ' || v_nombre_producto);
    
    v_total_cliente := PKG_TIENDATECH.TOTAL_CLIENTE(1);
    DBMS_OUTPUT.PUT_LINE('Total gastado por cliente 1: $' || TO_CHAR(v_total_cliente, '999,999.99'));
    
    v_tiene_stock := PKG_TIENDATECH.VERIFICAR_STOCK(17, 5);
    DBMS_OUTPUT.PUT_LINE('Stock suficiente para 5 unidades del producto 17: ' || 
                         CASE WHEN v_tiene_stock THEN 'SI' ELSE 'NO' END);
    
    DBMS_OUTPUT.PUT_LINE('Version del paquete: ' || PKG_TIENDATECH.C_VERSION);
END;
/

-- PRUEBA 2: Usar procedimientos del paquete
DECLARE
    v_total NUMBER;
    v_id_venta NUMBER;
    v_mensaje VARCHAR2(500);
    v_resultado VARCHAR2(500);
    v_cursor SYS_REFCURSOR;
    v_nombre_cliente VARCHAR2(200);
    v_total_gastado NUMBER;
    v_id_venta_cursor NUMBER;
    v_fecha DATE;
    v_metodo VARCHAR2(50);
    v_total_venta NUMBER;
    v_cantidad_prod NUMBER;
BEGIN
    DBMS_OUTPUT.PUT_LINE('=== PRUEBA PAQUETE 2: PROCEDIMIENTOS ===');
    
    -- Ajustar stock
    PKG_TIENDATECH.AJUSTAR_STOCK(2, 30, 'Correccion inventario', v_resultado);
    DBMS_OUTPUT.PUT_LINE(v_resultado);
    
    -- Registrar venta
    PKG_TIENDATECH.REGISTRAR_VENTA(2, 1, '17:1,18:2', v_total, v_id_venta, v_mensaje);
    DBMS_OUTPUT.PUT_LINE(v_mensaje);
    
    -- Reporte ventas
    PKG_TIENDATECH.REPORTE_VENTAS_CLIENTE(1, v_cursor, v_nombre_cliente, v_total_gastado);
    DBMS_OUTPUT.PUT_LINE('Reporte para: ' || v_nombre_cliente);
    DBMS_OUTPUT.PUT_LINE('Total gastado: $' || TO_CHAR(v_total_gastado, '999,999.99'));
    
    LOOP
        FETCH v_cursor INTO v_id_venta_cursor, v_fecha, v_metodo, v_total_venta, v_cantidad_prod;
        EXIT WHEN v_cursor%NOTFOUND;
        DBMS_OUTPUT.PUT_LINE('  Venta ' || v_id_venta_cursor || ': $' || 
                             TO_CHAR(v_total_venta, '999,999.99'));
    END LOOP;
    CLOSE v_cursor;
END;
/



-- Verificacion final
SELECT ID_PRODUCTO, NOMBRE, STOCK FROM PRODUCTOS WHERE ID_PRODUCTO IN (1,2);
SELECT * FROM DETALLE_VENTAS ORDER BY ID_DETALLE DESC;
SELECT * FROM VENTAS ORDER BY ID_VENTA DESC;
SELECT * FROM AUDITORIA_SISTEMA ORDER BY ID_AUDITORIA DESC;
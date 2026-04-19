-- =========================
-- EXPRECIONES REGULARES
-- =========================

SET SERVEROUTPUT ON;

SELECT c.ID_CLIENTE, c.CORREO
FROM   AdminProyecto.CORREOS c
WHERE  REGEXP_LIKE(c.CORREO,
           '^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$');

-- =======================================================
-- 1.2  Correos INV�LIDOS (para auditoria / limpieza)
-- =======================================================
SELECT c.ID_CLIENTE, c.CORREO
FROM   AdminProyecto.CORREOS c
WHERE  NOT REGEXP_LIKE(c.CORREO,
           '^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$');

-- =======================================================
-- 1.3  Validar tel�fonos: exactamente 8 d�gitos (Costa Rica)
--      �til en TELEFONOS de clientes
-- =======================================================
SELECT t.ID_CLIENTE, t.TELEFONO
FROM   AdminProyecto.TELEFONOS t
WHERE  REGEXP_LIKE(t.TELEFONO, '^[0-9]{8}$');

-- =======================================================
-- 1.4  Tel�fonos que NO cumplen el formato (para revisar)
-- =======================================================
SELECT t.ID_CLIENTE, t.TELEFONO
FROM   AdminProyecto.TELEFONOS t
WHERE  NOT REGEXP_LIKE(t.TELEFONO, '^[0-9]{8}$');

-- =======================================================
-- 1.5  Buscar productos cuyo nombre contenga un n�mero
--      (ej: RTX 4060, 980 Pro, 16GB �)
--      �til para filtrar componentes con specs en el nombre
-- =======================================================
SELECT ID_PRODUCTO, NOMBRE, PRECIO
FROM   AdminProyecto.PRODUCTOS
WHERE  REGEXP_LIKE(NOMBRE, '[0-9]')
ORDER  BY NOMBRE;

-- =======================================================
-- 1.6  Productos cuya descripci�n mencione "DDR4" o "DDR5"
--      (case-insensitive) � Memoria RAM
-- =======================================================
SELECT ID_PRODUCTO, NOMBRE, DESCRIPCION
FROM   AdminProyecto.PRODUCTOS
WHERE  REGEXP_LIKE(DESCRIPCION, 'DDR[45]', 'i');

-- =======================================================
-- 1.7  Extraer la capacidad (n�mero + GB/TB) del nombre
--      del producto � �til para ordenar almacenamiento
-- =======================================================
SELECT NOMBRE,
       REGEXP_SUBSTR(NOMBRE, '[0-9]+\s*(GB|TB)', 1, 1, 'i') AS CAPACIDAD
FROM   AdminProyecto.PRODUCTOS
WHERE  REGEXP_LIKE(NOMBRE, '[0-9]+\s*(GB|TB)', 'i')
ORDER  BY NOMBRE;

-- =======================================================
-- 1.8  Limpiar espacios dobles en DESCRIPCION de productos
--      (REGEXP_REPLACE) � normalizaci�n de texto
-- =======================================================
SELECT ID_PRODUCTO,
       NOMBRE,
       REGEXP_REPLACE(DESCRIPCION, ' {2,}', ' ') AS DESCRIPCION_LIMPIA
FROM   AdminProyecto.PRODUCTOS;

-- =======================================================
-- 1.9  Validar USERNAME: solo letras, n�meros y gui�n bajo,
--      m�nimo 4 caracteres
-- =======================================================
SELECT ID_USUARIO, USERNAME, ROL
FROM   AdminProyecto.USUARIOS
WHERE  REGEXP_LIKE(USERNAME, '^[A-Za-z0-9_]{4,}$');

-- =======================================================
-- 1.10 Proveedores cuyo correo sea de dominio .com o .cr
-- =======================================================
SELECT ID_PROVEEDOR, NOMBRE, CORREO
FROM   AdminProyecto.PROVEEDORES
WHERE  REGEXP_LIKE(CORREO, '\.(com|cr)$', 'i');

-- =======================================================
-- 1.11 Posici�n donde aparece el primer n�mero en el nombre
--      del producto (REGEXP_INSTR)
-- =======================================================
SELECT NOMBRE,
       REGEXP_INSTR(NOMBRE, '[0-9]') AS POS_PRIMER_NUMERO
FROM   AdminProyecto.PRODUCTOS
WHERE  REGEXP_INSTR(NOMBRE, '[0-9]') > 0
ORDER  BY NOMBRE;

-- =======================================================
-- 1.12 Buscar atributos de producto cuyo valor sea un
--      n�mero con unidad (MHz, GB, TB, W, mm, nm �)
-- =======================================================
SELECT pa.ID_PRODUCTO, p.NOMBRE AS PRODUCTO,
       pa.NOMBRE_ATRIBUTO, pa.VALOR
FROM   AdminProyecto.PRODUCTO_ATRIBUTOS pa
JOIN   AdminProyecto.PRODUCTOS p ON p.ID_PRODUCTO = pa.ID_PRODUCTO
WHERE  REGEXP_LIKE(pa.VALOR, '^[0-9]+(\.[0-9]+)?\s*(MHz|GB|TB|W|mm|nm|Hz|rpm)', 'i')
ORDER  BY p.NOMBRE, pa.NOMBRE_ATRIBUTO;

-- =========================
-- SQL DINAMICO
-- =========================


CREATE OR REPLACE PROCEDURE BuscarProductos(
    p_categoria  IN NUMBER   DEFAULT NULL,
    p_marca      IN NUMBER   DEFAULT NULL,
    p_precio_max IN NUMBER   DEFAULT NULL,
    p_texto      IN VARCHAR2 DEFAULT NULL
) AS
    v_sql    VARCHAR2(2000);
    v_cursor SYS_REFCURSOR;
    v_id     NUMBER;
    v_nombre VARCHAR2(100);
    v_precio NUMBER;
    v_marca  VARCHAR2(100);
BEGIN
    v_sql := 'SELECT p.ID_PRODUCTO, p.NOMBRE, p.PRECIO, m.NOMBRE_MARCA
              FROM AdminProyecto.PRODUCTOS p
              JOIN AdminProyecto.MARCAS m ON m.ID_MARCA = p.ID_MARCA
              WHERE 1=1';

    IF p_categoria IS NOT NULL THEN
        v_sql := v_sql || ' AND p.ID_CATEGORIA = ' || p_categoria;
    END IF;

    IF p_marca IS NOT NULL THEN
        v_sql := v_sql || ' AND p.ID_MARCA = ' || p_marca;
    END IF;

    IF p_precio_max IS NOT NULL THEN
        v_sql := v_sql || ' AND p.PRECIO <= ' || p_precio_max;
    END IF;

    IF p_texto IS NOT NULL THEN
        v_sql := v_sql || ' AND (UPPER(p.NOMBRE) LIKE UPPER(''%' || p_texto || '%'')'
                        || ' OR UPPER(p.DESCRIPCION) LIKE UPPER(''%' || p_texto || '%''))';
    END IF;

    v_sql := v_sql || ' ORDER BY p.NOMBRE';

    OPEN v_cursor FOR v_sql;
    LOOP
        FETCH v_cursor INTO v_id, v_nombre, v_precio, v_marca;
        EXIT WHEN v_cursor%NOTFOUND;
        DBMS_OUTPUT.PUT_LINE(v_id || ' | ' || v_nombre || ' | $' || v_precio || ' | ' || v_marca);
    END LOOP;
    CLOSE v_cursor;
END BuscarProductos;
/

EXEC BuscarProductos(p_categoria => 9, p_precio_max => 500);
-- Ejemplo de uso:
-- EXEC AdminProyecto.BuscarProductos(p_categoria => 9, p_precio_max => 500);
-- EXEC AdminProyecto.BuscarProductos(p_texto => 'RTX');

-- =======================================================
-- 2.2  Contar productos por cualquier columna din�mica
--      �til para reportes de administraci�n
-- =======================================================
CREATE OR REPLACE PROCEDURE AdminProyecto.ContarPorColumna(
    p_columna IN VARCHAR2   -- 'ID_CATEGORIA' | 'ID_MARCA' | 'STOCK'
) AS
    v_sql    VARCHAR2(500);
    v_cursor SYS_REFCURSOR;
    v_grupo  VARCHAR2(100);
    v_total  NUMBER;
BEGIN
    -- Solo se permite un conjunto seguro de columnas
    IF p_columna NOT IN ('ID_CATEGORIA','ID_MARCA','STOCK') THEN
        RAISE_APPLICATION_ERROR(-20001, 'Columna no permitida: ' || p_columna);
    END IF;

    v_sql := 'SELECT TO_CHAR(' || p_columna || '), COUNT(*)
              FROM AdminProyecto.PRODUCTOS
              GROUP BY ' || p_columna || '
              ORDER BY COUNT(*) DESC';

    OPEN v_cursor FOR v_sql;
    LOOP
        FETCH v_cursor INTO v_grupo, v_total;
        EXIT WHEN v_cursor%NOTFOUND;
        DBMS_OUTPUT.PUT_LINE(p_columna || ': ' || v_grupo || ' => ' || v_total || ' productos');
    END LOOP;
    CLOSE v_cursor;
END ContarPorColumna;
/

-- Ejemplo:
-- EXEC AdminProyecto.ContarPorColumna('ID_CATEGORIA');
-- EXEC AdminProyecto.ContarPorColumna('ID_MARCA');

-- =======================================================
-- 2.3  Actualizar precio de productos de una categor�a
--      con un porcentaje din�mico (descuento o aumento)
--      �til para campa�as de ofertas desde el admin
-- =======================================================
CREATE OR REPLACE PROCEDURE AdminProyecto.AjustarPrecioCategoria(
    p_id_categoria IN NUMBER,
    p_porcentaje   IN NUMBER   -- negativo = descuento, positivo = aumento
) AS
    v_sql VARCHAR2(300);
BEGIN
    v_sql := 'UPDATE AdminProyecto.PRODUCTOS
              SET PRECIO = ROUND(PRECIO * (1 + (' || p_porcentaje || ' / 100)), 2)
              WHERE ID_CATEGORIA = ' || p_id_categoria;

    EXECUTE IMMEDIATE v_sql;

    DBMS_OUTPUT.PUT_LINE('Filas actualizadas: ' || SQL%ROWCOUNT);
    COMMIT;
END AjustarPrecioCategoria;
/

-- Ejemplo: bajar 10% todas las Gr�ficas (ID_CATEGORIA = 9)
-- EXEC AdminProyecto.AjustarPrecioCategoria(9, -10);

-- =======================================================
-- 2.4  Buscar atributos de producto por nombre din�mico
--      Ej: todas las GPUs con atributo "VRAM"
-- =======================================================
CREATE OR REPLACE PROCEDURE AdminProyecto.BuscarPorAtributo(
    p_nombre_atributo IN VARCHAR2,
    p_valor_minimo    IN VARCHAR2 DEFAULT NULL
) AS
    v_sql    VARCHAR2(1000);
    v_cursor SYS_REFCURSOR;
    v_prod   VARCHAR2(100);
    v_attr   VARCHAR2(100);
    v_valor  VARCHAR2(200);
BEGIN
    v_sql := 'SELECT p.NOMBRE, pa.NOMBRE_ATRIBUTO, pa.VALOR
              FROM AdminProyecto.PRODUCTO_ATRIBUTOS pa
              JOIN AdminProyecto.PRODUCTOS p ON p.ID_PRODUCTO = pa.ID_PRODUCTO
              WHERE UPPER(pa.NOMBRE_ATRIBUTO) = UPPER(''' || p_nombre_atributo || ''')';

    IF p_valor_minimo IS NOT NULL THEN
        v_sql := v_sql || ' AND pa.VALOR >= ''' || p_valor_minimo || '''';
    END IF;

    v_sql := v_sql || ' ORDER BY p.NOMBRE';

    OPEN v_cursor FOR v_sql;
    LOOP
        FETCH v_cursor INTO v_prod, v_attr, v_valor;
        EXIT WHEN v_cursor%NOTFOUND;
        DBMS_OUTPUT.PUT_LINE(v_prod || ' | ' || v_attr || ': ' || v_valor);
    END LOOP;
    CLOSE v_cursor;
END BuscarPorAtributo;
/

-- Ejemplo:
-- EXEC AdminProyecto.BuscarPorAtributo('VRAM');
-- EXEC AdminProyecto.BuscarPorAtributo('Frecuencia', '3200MHz');

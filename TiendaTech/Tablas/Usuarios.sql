-- =========================
-- USUARIO PRINCIPAL
-- =========================

CREATE USER AdminProyecto IDENTIFIED BY AdminProyecto2026#
    DEFAULT TABLESPACE Proyecto_Base_de_DatosLN
    TEMPORARY TABLESPACE TEMP
    PROFILE perfil_admin
    ACCOUNT UNLOCK;

GRANT rol_admin TO AdminProyecto;
GRANT CONNECT TO AdminProyecto;
ALTER USER AdminProyecto QUOTA UNLIMITED ON Proyecto_Base_de_DatosLN;

-- =========================
-- USUARIO OPERADOR
-- =========================

CREATE USER Operador IDENTIFIED BY Operador2026#
    DEFAULT TABLESPACE Proyecto_Base_de_DatosLN
    TEMPORARY TABLESPACE TEMP
    PROFILE perfil_operador
    ACCOUNT UNLOCK;

GRANT rol_operador TO Operador;
GRANT CONNECT TO Operador;
ALTER USER Operador QUOTA UNLIMITED ON Proyecto_Base_de_DatosLN;

-- =========================
-- USUARIO CONSULTA
-- =========================

CREATE USER Consulta IDENTIFIED BY Consulta2026#
    DEFAULT TABLESPACE Proyecto_Base_de_DatosLN
    TEMPORARY TABLESPACE TEMP
    PROFILE perfil_consulta
    ACCOUNT UNLOCK;

GRANT rol_consulta TO Consulta;
GRANT CONNECT TO Consulta;

-- =========================
-- Verificacion 
-- =========================
SELECT USERNAME, PROFILE
FROM DBA_USERS
WHERE USERNAME IN ('ADMINPROYECTO','OPERADOR','CONSULTA');


ALTER USER AdminProyecto
DEFAULT TABLESPACE Proyecto_Base_de_DatosLN;

ALTER USER Operador
DEFAULT TABLESPACE Proyecto_Base_de_DatosLN;

ALTER USER Consulta
DEFAULT TABLESPACE Proyecto_Base_de_DatosLN;


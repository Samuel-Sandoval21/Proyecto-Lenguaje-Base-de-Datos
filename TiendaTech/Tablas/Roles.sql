-- =========================
--Roles
-- =========================

CREATE ROLE rol_admin;
GRANT CREATE SESSION TO rol_admin;
GRANT CREATE USER TO rol_admin;
GRANT ALTER USER TO rol_admin;
GRANT DROP USER TO rol_admin;
GRANT CREATE TABLE TO rol_admin;
GRANT CREATE VIEW TO rol_admin;
GRANT CREATE SEQUENCE TO rol_admin;
GRANT CREATE TRIGGER TO rol_admin;
GRANT CREATE PROCEDURE TO rol_admin;

CREATE ROLE rol_operador;
GRANT CREATE SESSION TO rol_operador;
GRANT CREATE TABLE TO rol_operador;
GRANT CREATE VIEW TO rol_operador;
GRANT CREATE SEQUENCE TO rol_operador;
GRANT CREATE TRIGGER TO rol_operador;
GRANT SELECT, INSERT, UPDATE, DELETE ON AdminProyecto.NOMBRE_TABLA TO rol_operador;

CREATE ROLE rol_consulta;
    GRANT CREATE SESSION TO rol_consulta;
    GRANT SELECT ON AdminProyecto.PRODUCTOS TO rol_consulta;
    
SELECT ROLE, PASSWORD_REQUIRED
FROM DBA_ROLES;

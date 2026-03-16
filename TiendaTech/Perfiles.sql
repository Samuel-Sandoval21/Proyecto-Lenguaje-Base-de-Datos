-- =========================
-- Creacion de perfiles 
-- =========================
CREATE PROFILE perfil_admin LIMIT
    SESSIONS_PER_USER          2
    IDLE_TIME                  30
    CONNECT_TIME               600
    LOGICAL_READS_PER_SESSION  2000000
    FAILED_LOGIN_ATTEMPTS      3
    PASSWORD_LIFE_TIME         30
    PASSWORD_LOCK_TIME         1/24
    PASSWORD_GRACE_TIME        4;

CREATE PROFILE perfil_operador LIMIT
    SESSIONS_PER_USER         1
    IDLE_TIME                 15        
    CONNECT_TIME              480
    LOGICAL_READS_PER_SESSION 1500000
    FAILED_LOGIN_ATTEMPTS     5
    PASSWORD_LIFE_TIME        30
    PASSWORD_LOCK_TIME        1/24
    PASSWORD_GRACE_TIME       4;

CREATE PROFILE perfil_consulta LIMIT
    SESSIONS_PER_USER         1
    IDLE_TIME                 10       
    CONNECT_TIME              240
    LOGICAL_READS_PER_SESSION 60000
    FAILED_LOGIN_ATTEMPTS     5
    PASSWORD_LIFE_TIME        30
    PASSWORD_LOCK_TIME        1/24
    PASSWORD_GRACE_TIME       4;

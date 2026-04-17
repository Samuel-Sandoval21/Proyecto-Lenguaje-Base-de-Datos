<?php

/**
 * Verifica si un USERNAME ya existe en USUARIOS
 */
function usernameExiste($conn, string $username): bool {
    $sql  = "SELECT COUNT(*) AS CNT FROM AdminProyecto.USUARIOS WHERE UPPER(USERNAME) = UPPER(:username)";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ':username', $username);
    oci_execute($stid);
    $row = oci_fetch_assoc($stid);
    return (int)$row['CNT'] > 0;
}

/**
 * Verifica si un correo ya existe en CORREOS
 */
function correoExiste($conn, string $correo): bool {
    $sql  = "SELECT COUNT(*) AS CNT FROM AdminProyecto.CORREOS WHERE LOWER(CORREO) = LOWER(:correo)";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ':correo', $correo);
    oci_execute($stid);
    $row = oci_fetch_assoc($stid);
    return (int)$row['CNT'] > 0;
}

/**
 * Registra un nuevo usuario:
 * 1. INSERT en CLIENTES  → obtiene ID_CLIENTE
 * 2. INSERT en USUARIOS  (ROL = 'CLIENTE')
 * 3. INSERT en CORREOS
 * 4. INSERT en DIRECCIONES
 *
 * Retorna el ID_USUARIO creado, o lanza Exception si falla.
 */
function registrarUsuario($conn, string $username, string $password, string $nombre,
                           string $apellido, string $correo, string $direccion): int {
    // 1. Insertar CLIENTE
    $sqlCli = "INSERT INTO AdminProyecto.CLIENTES (NOMBRE, APELLIDO) VALUES (:nombre, :apellido)";
    $stCli  = oci_parse($conn, $sqlCli);
    oci_bind_by_name($stCli, ':nombre',   $nombre);
    oci_bind_by_name($stCli, ':apellido', $apellido);
    if (!oci_execute($stCli, OCI_NO_AUTO_COMMIT)) {
        $e = oci_error($stCli);
        throw new Exception("Error al crear cliente: " . $e['message']);
    }

    // 2. Obtener ID_CLIENTE recién creado
    $sqlIdCli = "SELECT MAX(ID_CLIENTE) AS ID_CLIENTE FROM AdminProyecto.CLIENTES
                 WHERE NOMBRE = :nombre AND APELLIDO = :apellido";
    $stIdCli  = oci_parse($conn, $sqlIdCli);
    oci_bind_by_name($stIdCli, ':nombre',   $nombre);
    oci_bind_by_name($stIdCli, ':apellido', $apellido);
    oci_execute($stIdCli, OCI_NO_AUTO_COMMIT);
    $rowCli    = oci_fetch_assoc($stIdCli);
    $idCliente = (string)$rowCli['ID_CLIENTE'];

    // 3. Insertar USUARIO
    $sqlUsr = "INSERT INTO AdminProyecto.USUARIOS (USERNAME, PASSWORD, ROL, ID_CLIENTE)
               VALUES (:username, :password, 'CLIENTE', TO_NUMBER(:idcliente))";
    $stUsr  = oci_parse($conn, $sqlUsr);
    oci_bind_by_name($stUsr, ':username',  $username);
    oci_bind_by_name($stUsr, ':password',  $password);
    oci_bind_by_name($stUsr, ':idcliente', $idCliente);
    if (!oci_execute($stUsr, OCI_NO_AUTO_COMMIT)) {
        $e = oci_error($stUsr);
        throw new Exception("Error al crear usuario: " . $e['message']);
    }

    // 4. Obtener ID_USUARIO
    $sqlIdUsr = "SELECT MAX(ID_USUARIO) AS ID_USUARIO FROM AdminProyecto.USUARIOS WHERE UPPER(USERNAME) = UPPER(:username)";
    $stIdUsr  = oci_parse($conn, $sqlIdUsr);
    oci_bind_by_name($stIdUsr, ':username', $username);
    oci_execute($stIdUsr, OCI_NO_AUTO_COMMIT);
    $rowUsr    = oci_fetch_assoc($stIdUsr);
    $idUsuario = (int)$rowUsr['ID_USUARIO'];

    // 5. Insertar CORREO
    $sqlCorr = "INSERT INTO AdminProyecto.CORREOS (ID_CLIENTE, CORREO) VALUES (TO_NUMBER(:idcliente), :correo)";
    $stCorr  = oci_parse($conn, $sqlCorr);
    oci_bind_by_name($stCorr, ':idcliente', $idCliente);
    oci_bind_by_name($stCorr, ':correo',    $correo);
    if (!oci_execute($stCorr, OCI_NO_AUTO_COMMIT)) {
        $e = oci_error($stCorr);
        throw new Exception("Error al guardar correo: " . $e['message']);
    }

    // 6. Insertar DIRECCIÓN
    $sqlDir = "INSERT INTO AdminProyecto.DIRECCIONES (ID_CLIENTE, DIRECCION) VALUES (TO_NUMBER(:idcliente), :direccion)";
    $stDir  = oci_parse($conn, $sqlDir);
    oci_bind_by_name($stDir, ':idcliente', $idCliente);
    oci_bind_by_name($stDir, ':direccion', $direccion);
    if (!oci_execute($stDir, OCI_NO_AUTO_COMMIT)) {
        $e = oci_error($stDir);
        throw new Exception("Error al guardar dirección: " . $e['message']);
    }

    oci_commit($conn);
    return $idUsuario;
}

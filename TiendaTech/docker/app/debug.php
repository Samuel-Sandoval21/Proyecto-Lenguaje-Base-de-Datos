<?php
$host    = '//host.docker.internal:1521/orcl';
$charset = 'AL32UTF8';
 
echo "<pre>";
 
// Test Operador sin @
$conn = oci_connect('Operador', 'Operador2026#', $host, $charset);
if ($conn) {
    echo "✅ Operador conectado\n";
 
    $st = oci_parse($conn, "SELECT U.USERNAME, U.ROL FROM AdminProyecto.USUARIOS U WHERE ROWNUM <= 3");
    if (oci_execute($st)) {
        echo "✅ SELECT USUARIOS OK:\n";
        while ($r = oci_fetch_assoc($st)) {
            echo "   - {$r['USERNAME']} / {$r['ROL']}\n";
        }
    } else {
        $e = oci_error($st);
        echo "❌ SELECT USUARIOS falló: {$e['message']}\n";
    }
} else {
    $e = oci_error();
    echo "❌ Operador NO conectó: {$e['message']}\n";
}
 
echo "</pre>";
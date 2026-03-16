<?php
require_once __DIR__ . '/../config/db.php';
 
$categoria = $_GET['categoria'] ?? null;
$buscar    = trim($_GET['buscar'] ?? '');
 
// ── Función para quitar tildes y normalizar texto ────────────────────────────
function normalizar(string $texto): string {
    $con    = ['á','é','í','ó','ú','Á','É','Í','Ó','Ú','ä','ë','ï','ö','ü','ñ','Ñ'];
    $sin    = ['a','e','i','o','u','a','e','i','o','u','a','e','i','o','u','n','n'];
    return strtoupper(str_replace($con, $sin, $texto));
}
 
// ── Caso 1: viene del menú dropdown (filtra por categoría) ──────────────────
if ($categoria) {
 
    $sqlNombre = "SELECT NOMBRE_CATEGORIA FROM AdminProyecto.CATEGORIAS
                  WHERE ID_CATEGORIA = :cat";
    $stNombre  = oci_parse($conn, $sqlNombre);
    oci_bind_by_name($stNombre, ":cat", $categoria);
    oci_execute($stNombre);
    $nombreCat = oci_fetch_assoc($stNombre)['NOMBRE_CATEGORIA'] ?? 'Categoría';
 
    echo "<h1>" . htmlspecialchars($nombreCat) . "</h1>";
 
    $sql  = "SELECT p.NOMBRE, p.DESCRIPCION, p.PRECIO, p.STOCK, p.IMAGEN,
                    m.NOMBRE_MARCA
             FROM AdminProyecto.PRODUCTOS p
             JOIN AdminProyecto.MARCAS m ON p.ID_MARCA = m.ID_MARCA
             WHERE p.ID_CATEGORIA = :cat
             ORDER BY p.NOMBRE";
 
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ":cat", $categoria);
    oci_execute($stid);
 
// ── Caso 2: viene de la lupa — sin importar mayúsculas, minúsculas ni tildes ─
} elseif ($buscar !== '') {
 
    echo "<h1>Resultados para: <em>" . htmlspecialchars($buscar) . "</em></h1>";
 
    // Normalizar cada palabra del texto ingresado
    $palabras = array_filter(
        explode(' ', normalizar($buscar)),
        fn($p) => strlen($p) > 1
    );
 
    if (empty($palabras)) {
        echo "<p>Escribí al menos dos letras para buscar.</p>";
        return;
    }
 
    // Por cada palabra: buscar en nombre, descripción y marca (también normalizados en Oracle)
    // TRANSLATE quita tildes del lado de la base de datos
    $normalOracle = "TRANSLATE(UPPER(#), 'ÁÉÍÓÚÄËÏÖÜÑ', 'AEIOUAEIOUN')";
 
    $condiciones = [];
    $binds       = [];
 
    foreach (array_values($palabras) as $i => $palabra) {
        $key = ":t$i";
        $n   = str_replace('#', 'p.NOMBRE',      $normalOracle);
        $d   = str_replace('#', 'p.DESCRIPCION', $normalOracle);
        $m   = str_replace('#', 'm.NOMBRE_MARCA', $normalOracle);
        $condiciones[] = "$n LIKE $key OR $d LIKE $key OR $m LIKE $key";
        $binds[$key]   = "%$palabra%";
    }
 
    $where = implode(' OR ', $condiciones);
 
    $sql = "SELECT p.NOMBRE, p.DESCRIPCION, p.PRECIO, p.STOCK, p.IMAGEN,
                   m.NOMBRE_MARCA
            FROM AdminProyecto.PRODUCTOS p
            JOIN AdminProyecto.MARCAS m ON p.ID_MARCA = m.ID_MARCA
            WHERE $where
            ORDER BY p.NOMBRE";
 
    $stid = oci_parse($conn, $sql);
 
    foreach ($binds as $key => $_) {
        oci_bind_by_name($stid, $key, $binds[$key]);
    }
 
    oci_execute($stid);
 
// ── Caso 3: llegó sin parámetros ─────────────────────────────────────────────
} else {
    echo "<p>Usá la lupa para buscar un producto o elegí una categoría del menú.</p>";
    return;
}
 
// ── Renderizado de resultados (compartido por caso 1 y 2) ────────────────────
$hayResultados = false;
 
echo '<div style="display:grid; grid-template-columns:repeat(5,1fr); gap:20px;">';
 
while ($row = oci_fetch_assoc($stid)) {
    $hayResultados = true;
    ?>
 
    <div style="
        background:white;
        border:1px solid #ddd;
        border-radius:8px;
        padding:15px;
        box-shadow:0 2px 6px rgba(0,0,0,0.1);
    ">
        <img
            src="<?php echo $row['IMAGEN']; ?>"
            style="width:100%; height:150px; object-fit:contain; margin-bottom:10px;"
        >
        <h3><?php echo htmlspecialchars($row['NOMBRE']); ?></h3>
        <p><?php echo htmlspecialchars($row['DESCRIPCION']); ?></p>
        <p><strong>Marca:</strong> <?php echo htmlspecialchars($row['NOMBRE_MARCA']); ?></p>
        <p style="font-weight:bold; color:#1a73e8;">
            $<?php echo $row['PRECIO']; ?>
        </p>
        <p style="color:#555;">Stock: <?php echo $row['STOCK']; ?></p>
    </div>
 
    <?php
}
 
echo '</div>';
 
if (!$hayResultados) {
    echo "<p>No se encontraron productos.</p>";
}
?>
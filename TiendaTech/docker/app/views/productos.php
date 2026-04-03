<?php
echo "SI ENTRO A PRODUCTOS";
?>
<?php
require_once __DIR__ . '/../config/db.php';

$sql = "SELECT * FROM productos";
$stmt = $conn->prepare($sql);
$stmt->execute();

$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Productos</h1>

<div class="contenedor-productos">

<?php foreach ($productos as $p) { ?>

    <div class="card-producto">

        <img src="<?php echo $p['imagen']; ?>" alt="">

        <h3><?php echo $p['nombre']; ?></h3>

        <p>₡<?php echo $p['precio']; ?></p>

        <a href="index.php?page=producto&id=<?php echo $p['id']; ?>">
            Ver producto
        </a>

    </div>

<?php } ?>

</div>
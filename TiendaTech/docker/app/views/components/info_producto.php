<a href="?page=producto&id=<?php echo $row['ID_PRODUCTO']; ?>" 
   style="text-decoration:none; color:inherit;">

    <div style="
        background:white;
        border:1px solid #ddd;
        border-radius:8px;
        padding:15px;
        box-shadow:0 2px 6px rgba(0,0,0,0.1);
        cursor:pointer;
        height:100%;
        transition:all 0.25s ease;
    "
    onmouseover="this.style.transform='scale(1.03) translateY(-5px)'; this.style.boxShadow='0 6px 18px rgba(0,0,0,0.2)'"
    onmouseout="this.style.transform='scale(1) translateY(0)'; this.style.boxShadow='0 2px 6px rgba(0,0,0,0.1)'"
    >

        <img 
            src="<?php echo $row['IMAGEN']; ?>" 
            style="width:100%; height:150px; object-fit:contain; margin-bottom:10px;"
        >

        <h3><?php echo htmlspecialchars($row['NOMBRE']); ?></h3>

        <p><?php echo htmlspecialchars($row['DESCRIPCION']); ?></p>

        <p><strong>Marca:</strong>
            <?php echo htmlspecialchars($row['NOMBRE_MARCA']); ?>
        </p>

        <p style="font-weight:bold; color:#1a73e8;">
            $<?php echo $row['PRECIO']; ?>
        </p>

        <p style="color:#555;">
            Stock: <?php echo $row['STOCK']; ?>
        </p>

    </div>
</a>
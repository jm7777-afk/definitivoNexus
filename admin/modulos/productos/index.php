<?php
require_once '../../../includes/funciones.php';
verificar_sesion();

// Eliminar producto
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    
    // Obtener la imagen antes de eliminar
    $stmt = $pdo->prepare("SELECT imagen FROM PRODUCTOS WHERE id_producto = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();
    
    // Eliminar la imagen del servidor
    if ($producto && !empty($producto['imagen'])) {
        $ruta_imagen = '../../uploads/productos/' . $producto['imagen'];
        if (file_exists($ruta_imagen)) {
            unlink($ruta_imagen);
        }
    }
    
    $stmt = $pdo->prepare("DELETE FROM PRODUCTOS WHERE id_producto = ?");
    $stmt->execute([$id]);
    header('Location: index.php?mensaje=eliminado');
    exit;
}

$productos = $pdo->query("SELECT * FROM PRODUCTOS ORDER BY id_producto DESC")->fetchAll();

// Función para obtener la ruta de la imagen
function getImagenProducto($imagen) {
    if (!empty($imagen)) {
        $ruta = '../../uploads/productos/' . $imagen;
        if (file_exists($ruta)) {
            return $ruta;
        }
    }
    return null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Nexus Digital Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f6fa;
            padding: 30px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .header h1 { font-size: 28px; color: #333; }
        .header p { color: #666; margin-top: 5px; }
        .btn-nuevo {
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.2s;
        }
        .btn-nuevo:hover {
            transform: translateY(-2px);
        }
        .btn-volver {
            background: #6c757d;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.2s;
        }
        .btn-volver:hover {
            transform: translateY(-2px);
        }
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        th {
            text-align: left;
            padding: 15px;
            background: #f8f9fa;
            color: #666;
            font-weight: 600;
            font-size: 14px;
        }
        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #333;
            font-size: 14px;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .producto-imagen {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            background: #f8f9fa;
        }
        .imagen-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #adb5bd;
            font-size: 24px;
        }
        .stock-bajo { 
            color: #dc3545; 
            font-weight: 600; 
        }
        .stock-normal { 
            color: #28a745; 
        }
        .btn-accion {
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }
        .btn-editar {
            background: #ffc107;
            color: #000;
        }
        .btn-editar:hover {
            background: #e0a800;
        }
        .btn-eliminar {
            background: #dc3545;
            color: white;
        }
        .btn-eliminar:hover {
            background: #c82333;
        }
        .btn-ver {
            background: #17a2b8;
            color: white;
        }
        .btn-ver:hover {
            background: #138496;
        }
        .mensaje {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .acciones {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            .acciones {
                flex-direction: column;
            }
            .btn-accion {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1><i class="fas fa-box"></i> Productos</h1>
                <p style="color: #666;">Administra tu catálogo de productos</p>
            </div>

<div style="display: flex; gap: 15px;">
    <a href="nuevo.php" class="btn-nuevo" style="background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);">
        <i class="fas fa-plus"></i> Nuevo Producto
    </a>
    <a href="compra.php" class="btn-nuevo" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
        <i class="fas fa-shopping-cart"></i> Registrar Compra
    </a>
    <a href="../../index.php" class="btn-volver">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

          
        <?php if (isset($_GET['mensaje'])): ?>
        <div class="mensaje">
            <i class="fas fa-check-circle"></i>
            <?php 
                if ($_GET['mensaje'] == 'creado') echo "Producto creado exitosamente";
                if ($_GET['mensaje'] == 'actualizado') echo "Producto actualizado exitosamente";
                if ($_GET['mensaje'] == 'eliminado') echo "Producto eliminado exitosamente";
                if ($_GET['mensaje'] == 'compra_exitosa') echo "Compra registrada exitosamente";
            ?>
        </div>
        <?php endif; ?>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Stock</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($productos as $p): ?>
                    <tr>
                        <td>
                            <?php 
                            $ruta_imagen = getImagenProducto($p['imagen']);
                            if ($ruta_imagen): 
                            ?>
                                <img src="<?php echo $ruta_imagen; ?>" 
                                     alt="<?php echo htmlspecialchars($p['nombre']); ?>"
                                     class="producto-imagen"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="imagen-placeholder" style="display: none;">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php else: ?>
                                <div class="imagen-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>#<?php echo $p['id_producto']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($p['nombre']); ?></strong><br>
                            <small style="color: #666;"><?php echo htmlspecialchars(substr($p['descripcion'] ?? 'Sin descripción', 0, 50)); ?></small>
                        </td>
                        <td class="<?php echo $p['stock'] <= 5 ? 'stock-bajo' : 'stock-normal'; ?>">
                            <i class="fas fa-cubes"></i> <?php echo $p['stock']; ?> unidades
                            <?php if($p['stock'] <= 5 && $p['stock'] > 0): ?>
                                <br><small style="color: #dc3545;">⚠️ Stock bajo</small>
                            <?php elseif($p['stock'] == 0): ?>
                                <br><small style="color: #dc3545;">❌ Agotado</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo formato_moneda($p['precio']); ?></strong>
                        </td>
                        <td class="acciones">
                            <a href="ver.php?id=<?php echo $p['id_producto']; ?>" class="btn-accion btn-ver" title="Ver detalles">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <a href="editar.php?id=<?php echo $p['id_producto']; ?>" class="btn-accion btn-editar" title="Editar producto">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="?eliminar=<?php echo $p['id_producto']; ?>" class="btn-accion btn-eliminar" title="Eliminar producto" onclick="return confirm('¿Estás seguro de eliminar este producto? Esta acción no se puede deshacer.')">
                                <i class="fas fa-trash"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
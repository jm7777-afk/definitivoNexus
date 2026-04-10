<?php
require_once '../../../includes/funciones.php';
verificar_sesion();

$id = $_GET['id'] ?? 0;

// Obtener datos del producto
$stmt = $pdo->prepare("SELECT * FROM PRODUCTOS WHERE id_producto = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    header('Location: index.php?error=no_encontrado');
    exit;
}

// Obtener ventas de este producto
$stmt = $pdo->prepare("
    SELECT do.*, o.fecha, o.id_orden 
    FROM DETALLE_ORDEN do 
    JOIN ORDENES o ON do.id_orden = o.id_orden 
    WHERE do.id_producto = ? 
    ORDER BY o.fecha DESC LIMIT 10
");
$stmt->execute([$id]);
$ventas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Producto - Nexus Digital</title>
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
            max-width: 900px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 28px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
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
        }
        .btn-editar {
            background: #ffc107;
            color: #000;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .product-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-item {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }
        .info-item i {
            font-size: 32px;
            color: #5B4B9E;
            margin-bottom: 10px;
        }
        .info-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 24px;
            font-weight: 700;
            color: #333;
        }
        .info-value.stock-bajo { color: #dc3545; }
        .description {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        .description h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .table-container h3 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            text-align: left;
            padding: 12px;
            background: #f8f9fa;
            color: #666;
            font-weight: 600;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            color: #333;
        }
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-box" style="color: #5B4B9E;"></i> Detalle del Producto</h1>
            <div style="display: flex; gap: 15px;">
                <a href="editar.php?id=<?php echo $producto['id_producto']; ?>" class="btn-editar">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="index.php" class="btn-volver">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        
        <div class="card">
            <div class="product-title">
                <?php echo htmlspecialchars($producto['nombre']); ?>
                <span style="font-size: 14px; font-weight: normal; color: #666; margin-left: 15px;">
                    ID: #<?php echo $producto['id_producto']; ?>
                </span>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <i class="fas fa-cubes"></i>
                    <div class="info-label">Stock Actual</div>
                    <div class="info-value <?php echo $producto['stock'] <= 5 ? 'stock-bajo' : ''; ?>">
                        <?php echo $producto['stock']; ?> unidades
                    </div>
                    <?php if($producto['stock'] <= 5): ?>
                        <small style="color: #dc3545; display: block; margin-top: 5px;">
                            <i class="fas fa-exclamation-triangle"></i> Stock bajo
                        </small>
                    <?php endif; ?>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-dollar-sign"></i>
                    <div class="info-label">Precio de Venta</div>
                    <div class="info-value"><?php echo formato_moneda($producto['precio']); ?></div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-calendar"></i>
                    <div class="info-label">Fecha de Alta</div>
                    <div class="info-value"><?php echo date('d/m/Y', strtotime($producto['fecha_creacion'])); ?></div>
                </div>
            </div>
            
            <?php if(!empty($producto['descripcion'])): ?>
            <div class="description">
                <h3><i class="fas fa-align-left"></i> Descripción</h3>
                <p style="color: #666; line-height: 1.8;"><?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="table-container">
            <h3><i class="fas fa-history"></i> Últimas Ventas de este Producto</h3>
            
            <?php if(count($ventas) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>N° Venta</th>
                        <th>Fecha</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($ventas as $venta): ?>
                    <tr>
                        <td>#<?php echo $venta['id_orden']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></td>
                        <td><?php echo $venta['cantidad']; ?> uds</td>
                        <td><?php echo formato_moneda($venta['precio_unitario']); ?></td>
                        <td><?php echo formato_moneda($venta['precio_unitario'] * $venta['cantidad']); ?></td>
                        <td>
                            <span class="badge badge-success">Completada</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="text-align: center; color: #666; padding: 40px;">
                <i class="fas fa-info-circle"></i> Este producto aún no tiene ventas registradas.
            </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
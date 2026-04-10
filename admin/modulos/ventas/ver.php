<?php
require_once '../../../includes/funciones.php';
verificar_sesion();

$id = $_GET['id'] ?? 0;

// Obtener datos de la venta
$stmt = $pdo->prepare("
    SELECT o.*, c.nombre as cliente_nombre, c.email, c.telefono, c.direccion 
    FROM ORDENES o 
    JOIN CLIENTES c ON o.id_cliente = c.id_cliente 
    WHERE o.id_orden = ?
");
$stmt->execute([$id]);
$venta = $stmt->fetch();

if (!$venta) {
    header('Location: index.php?error=no_encontrado');
    exit;
}

// Obtener detalles de la venta
$stmt = $pdo->prepare("
    SELECT do.*, p.nombre as producto_nombre 
    FROM DETALLE_ORDEN do 
    JOIN PRODUCTOS p ON do.id_producto = p.id_producto 
    WHERE do.id_orden = ?
");
$stmt->execute([$id]);
$detalles = $stmt->fetchAll();

// Actualizar estado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['estado'])) {
    $estado = $_POST['estado'];
    $stmt = $pdo->prepare("UPDATE ORDENES SET estado = ? WHERE id_orden = ?");
    $stmt->execute([$estado, $id]);
    header('Location: ver.php?id=' . $id . '&mensaje=actualizado');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Venta - Nexus Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="/nexus-digital/favicon.ico">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f6fa;
            padding: 30px;
        }
        .container {
            max-width: 1000px;
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
        .btn-imprimir {
            background: #17a2b8;
            color: white;
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
        .venta-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .venta-numero {
            font-size: 24px;
            font-weight: 700;
            color: #5B4B9E;
        }
        .venta-fecha {
            color: #666;
            font-size: 14px;
        }
        .estado-actual {
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .info-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
        }
        .info-section h3 {
            font-size: 16px;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        .info-label {
            width: 120px;
            color: #666;
            font-size: 14px;
        }
        .info-value {
            flex: 1;
            color: #333;
            font-weight: 500;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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
        .total-venta {
            text-align: right;
            font-size: 20px;
            font-weight: 700;
            color: #5B4B9E;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        .acciones-venta {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .btn-estado {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-pendiente { background: #ffc107; color: #000; }
        .btn-procesando { background: #17a2b8; color: white; }
        .btn-completada { background: #28a745; color: white; }
        .btn-cancelada { background: #dc3545; color: white; }
        .mensaje {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if(isset($_GET['mensaje'])): ?>
        <div class="mensaje">
            <i class="fas fa-check-circle"></i> Estado actualizado correctamente
        </div>
        <?php endif; ?>
        
        <div class="header">
            <h1><i class="fas fa-file-invoice"></i> Detalle de Venta</h1>
            <div style="display: flex; gap: 15px;">
                <a href="javascript:window.print()" class="btn-imprimir">
                    <i class="fas fa-print"></i> Imprimir
                </a>
                <a href="../ventas/" class="btn-volver">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        
        <div class="card">
            <div class="venta-header">
                <div>
                    <div class="venta-numero">Venta #<?php echo $venta['id_orden']; ?></div>
                    <div class="venta-fecha"><?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></div>
                </div>
                <div>
                    <span class="estado-actual badge-<?php echo $venta['estado']; ?>">
                        <?php echo traducir_estado_venta($venta['estado']); ?>
                    </span>
                </div>
            </div>
            
            <div class="grid-2">
                <div class="info-section">
                    <h3><i class="fas fa-user"></i> Información del Cliente</h3>
                    <div class="info-row">
                        <span class="info-label">Nombre:</span>
                        <span class="info-value"><?php echo htmlspecialchars($venta['cliente_nombre']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($venta['email']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Teléfono:</span>
                        <span class="info-value"><?php echo htmlspecialchars($venta['telefono'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Dirección:</span>
                        <span class="info-value"><?php echo htmlspecialchars($venta['direccion'] ?? 'N/A'); ?></span>
                    </div>
                </div>
                
                <div class="info-section">
                    <h3><i class="fas fa-credit-card"></i> Información del Pago</h3>
                    <div class="info-row">
                        <span class="info-label">Método de Pago:</span>
                        <span class="info-value"><?php echo ucfirst(str_replace('_', ' ', $venta['metodo_pago'] ?? 'N/A')); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Estado:</span>
                        <span class="info-value"><?php echo $venta['estado_pago'] ?? 'Pagado'; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Subtotal:</span>
                        <span class="info-value"><?php echo formato_moneda($venta['total']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Total:</span>
                        <span class="info-value" style="font-weight: 700; color: #5B4B9E;"><?php echo formato_moneda($venta['total']); ?></span>
                    </div>
                </div>
            </div>
            
            <h3 style="margin-bottom: 20px;"><i class="fas fa-box"></i> Productos Vendidos</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($detalles as $detalle): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($detalle['producto_nombre']); ?></strong></td>
                        <td><?php echo $detalle['cantidad']; ?> unidades</td>
                        <td><?php echo formato_moneda($detalle['precio_unitario']); ?></td>
                        <td><?php echo formato_moneda($detalle['precio_unitario'] * $detalle['cantidad']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="total-venta">
                Total: <?php echo formato_moneda($venta['total']); ?>
            </div>
            
            <?php if($_SESSION['usuario_rol'] == 'admin'): ?>
            <div class="acciones-venta">
                <form method="POST" style="display: flex; gap: 10px;">
                    <input type="hidden" name="estado" value="pendiente">
                    <button type="submit" class="btn-estado btn-pendiente" <?php echo $venta['estado'] == 'pendiente' ? 'disabled' : ''; ?>>
                        <i class="fas fa-clock"></i> Pendiente
                    </button>
                </form>
                <form method="POST" style="display: flex; gap: 10px;">
                    <input type="hidden" name="estado" value="procesando">
                    <button type="submit" class="btn-estado btn-procesando" <?php echo $venta['estado'] == 'procesando' ? 'disabled' : ''; ?>>
                        <i class="fas fa-cog"></i> Procesando
                    </button>
                </form>
                <form method="POST" style="display: flex; gap: 10px;">
                    <input type="hidden" name="estado" value="completada">
                    <button type="submit" class="btn-estado btn-completada" <?php echo $venta['estado'] == 'completada' ? 'disabled' : ''; ?>>
                        <i class="fas fa-check"></i> Completada
                    </button>
                </form>
                <form method="POST" style="display: flex; gap: 10px;">
                    <input type="hidden" name="estado" value="cancelada">
                    <button type="submit" class="btn-estado btn-cancelada" <?php echo $venta['estado'] == 'cancelada' ? 'disabled' : ''; ?>>
                        <i class="fas fa-times"></i> Cancelada
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div style="display: flex; gap: 15px; margin-top: 20px;">
    <a href="factura.php?id=<?php echo $venta['id_orden']; ?>" class="btn btn-factura" style="background: #5B4B9E; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
        <i class="fas fa-file-invoice"></i> Ver Factura
    </a>
    <a href="ticket.php?id=<?php echo $venta['id_orden']; ?>" class="btn btn-ticket" style="background: #17a2b8; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
        <i class="fas fa-receipt"></i> Ver Ticket
    </a>
</div>
    
    <style media="print">
        body { background: white; }
        .btn-volver, .btn-imprimir, .acciones-venta { display: none; }
        .card { box-shadow: none; border: 1px solid #ddd; }
    </style>
</body>
</html>
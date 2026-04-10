<?php
require_once '../../../includes/funciones.php';
verificar_sesion();

$id = $_GET['id'] ?? 0;

// ✅ CORREGIDO - SIN columnas que no existen
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

// Obtener detalles
$stmt = $pdo->prepare("
    SELECT do.*, p.nombre as producto_nombre 
    FROM DETALLE_ORDEN do 
    JOIN PRODUCTOS p ON do.id_producto = p.id_producto 
    WHERE do.id_orden = ?
");
$stmt->execute([$id]);
$detalles = $stmt->fetchAll();

// Datos de la empresa
$empresa = [
    'nombre' => 'Nexus Digital',
    'cuit' => 'J-30-71234567-9',
    'direccion' => 'Av. Tecnología #123, CABA',
    'telefono' => '011-4127-2122',
    'email' => 'facturacion@nexusdigital.com',
    'inicio_actividades' => '01/01/2020',
    'ingresos_brutos' => '901-123456-7'
];

$numero_factura = 'F' . date('Y') . '-' . str_pad($venta['id_orden'], 8, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #<?php echo $venta['id_orden']; ?> - Nexus Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f6fa;
            padding: 30px;
        }
        .factura-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .factura-header {
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            color: white;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo h1 { font-size: 28px; font-weight: 700; }
        .logo span { color: #00D4E8; }
        .factura-titulo h2 { font-size: 24px; margin-bottom: 5px; }
        .factura-body { padding: 30px; }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px dashed #eee;
        }
        .info-empresa h3, .info-cliente h3 {
            font-size: 16px;
            color: #5B4B9E;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .info-label {
            width: 100px;
            color: #666;
        }
        .info-value {
            flex: 1;
            color: #333;
            font-weight: 500;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #666;
            border-bottom: 2px solid #e1e1e1;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        .text-right { text-align: right; }
        .resumen-factura {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
        }
        .resumen-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 15px;
        }
        .resumen-row.total {
            border-top: 2px solid #ddd;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 18px;
            font-weight: 700;
            color: #5B4B9E;
        }
        .pago-info {
            margin-top: 20px;
            padding: 15px;
            background: #e2f0f9;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 15px;
            color: #0c5460;
        }
        .factura-footer {
            padding: 30px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            text-align: center;
        }
        .acciones-factura {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            max-width: 800px;
            margin: 0 auto 20px;
        }
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-imprimir { background: #17a2b8; color: white; }
        .btn-volver { background: #6c757d; color: white; }
        .btn-imprimir:hover, .btn-volver:hover { transform: translateY(-2px); }
        @media print {
            body { background: white; padding: 0; }
            .acciones-factura { display: none; }
            .factura-container { box-shadow: none; border: 1px solid #ddd; }
            .factura-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="acciones-factura">
        <a href="ver.php?id=<?php echo $venta['id_orden']; ?>" class="btn btn-volver">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <button onclick="window.print()" class="btn btn-imprimir">
            <i class="fas fa-print"></i> Imprimir Factura
        </button>
    </div>
    
    <div class="factura-container" id="factura">
        <div class="factura-header">
            <div class="logo">
                <h1>Nexus<span>Digital</span></h1>
                <p style="font-size: 12px; opacity: 0.9;">Tecnología & Soluciones</p>
            </div>
            <div class="factura-titulo">
                <h2>FACTURA B</h2>
                <p>N° <?php echo $numero_factura; ?></p>
            </div>
        </div>
        
        <div class="factura-body">
            <div class="info-grid">
                <div class="info-empresa">
                    <h3><i class="fas fa-building"></i> NEXUS DIGITAL</h3>
                    <div class="info-row"><span class="info-label">CUIT:</span><span class="info-value"><?php echo $empresa['cuit']; ?></span></div>
                    <div class="info-row"><span class="info-label">II.BB.:</span><span class="info-value"><?php echo $empresa['ingresos_brutos']; ?></span></div>
                    <div class="info-row"><span class="info-label">Dirección:</span><span class="info-value"><?php echo $empresa['direccion']; ?></span></div>
                    <div class="info-row"><span class="info-label">Teléfono:</span><span class="info-value"><?php echo $empresa['telefono']; ?></span></div>
                    <div class="info-row"><span class="info-label">Email:</span><span class="info-value"><?php echo $empresa['email']; ?></span></div>
                </div>
                
                <div class="info-cliente">
                    <h3><i class="fas fa-user"></i> DATOS DEL CLIENTE</h3>
                    <div class="info-row"><span class="info-label">Nombre:</span><span class="info-value"><?php echo htmlspecialchars($venta['cliente_nombre']); ?></span></div>
                    <div class="info-row"><span class="info-label">Email:</span><span class="info-value"><?php echo htmlspecialchars($venta['email']); ?></span></div>
                    <div class="info-row"><span class="info-label">Teléfono:</span><span class="info-value"><?php echo htmlspecialchars($venta['telefono'] ?? 'N/A'); ?></span></div>
                    <div class="info-row"><span class="info-label">Dirección:</span><span class="info-value"><?php echo htmlspecialchars($venta['direccion'] ?? 'N/A'); ?></span></div>
                    <div class="info-row"><span class="info-label">Fecha:</span><span class="info-value"><?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></span></div>
                </div>
            </div>
            
            <h3 style="margin-bottom: 15px;"><i class="fas fa-boxes"></i> DETALLE DE PRODUCTOS</h3>
            <table>
                <thead>
                    <tr>
                        <th>CANT.</th>
                        <th>DESCRIPCIÓN</th>
                        <th class="text-right">P. UNITARIO</th>
                        <th class="text-right">SUBTOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($detalles as $detalle): 
                        $subtotal = $detalle['precio_unitario'] * $detalle['cantidad'];
                    ?>
                    <tr>
                        <td><?php echo $detalle['cantidad']; ?></td>
                        <td><?php echo htmlspecialchars($detalle['producto_nombre']); ?></td>
                        <td class="text-right"><?php echo formato_moneda($detalle['precio_unitario']); ?></td>
                        <td class="text-right"><?php echo formato_moneda($subtotal); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="resumen-factura">
                <div class="resumen-row"><span>SUBTOTAL:</span><span><?php echo formato_moneda($venta['total']); ?></span></div>
                <div class="resumen-row"><span>IVA 21%:</span><span><?php echo formato_moneda($venta['total'] * 0.21); ?></span></div>
                <div class="resumen-row total"><span>TOTAL:</span><span><?php echo formato_moneda($venta['total']); ?></span></div>
            </div>
            
            <div class="pago-info">
                <i class="fas fa-credit-card" style="font-size: 24px;"></i>
                <div>
                    <strong>Método de Pago:</strong> <?php echo ucfirst(str_replace('_', ' ', $venta['metodo_pago'] ?? 'Efectivo')); ?><br>
                    <strong>Estado:</strong> <?php echo $venta['estado_pago'] ?? 'Pagado'; ?>
                </div>
            </div>
        </div>
        
        <div class="factura-footer">
            <div style="margin-bottom: 10px;">¡Gracias por su compra!</div>
            <div style="font-size: 12px; color: #666;">
                <i class="fas fa-clock"></i> Horario: Lun-Vie 8:00-17:30 | Sáb 9:00-13:00<br>
                <i class="fas fa-headset"></i> Soporte: soporte@nexusdigital.com
            </div>
            <div style="margin-top: 15px; font-size: 11px; color: #999;">
                Este comprobante no es válido como factura fiscal.
            </div>
        </div>
    </div>
</body>
</html>
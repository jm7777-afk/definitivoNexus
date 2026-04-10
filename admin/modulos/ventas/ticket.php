<?php
require_once '../../../includes/funciones.php';
verificar_sesion();

$id = $_GET['id'] ?? 0;

// Obtener datos de la venta
$stmt = $pdo->prepare("
    SELECT o.*, c.nombre as cliente_nombre 
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?php echo $venta['id_orden']; ?></title>
    <link rel="icon" type="image/x-icon" href="/nexus-digital/favicon.ico">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            background: #ccc;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        
        .ticket {
            width: 80mm; /* Ancho estándar para impresora térmica */
            background: white;
            padding: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            font-size: 12px;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            padding-bottom: 10px;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
        }
        
        .header h1 {
            font-size: 18px;
            font-weight: bold;
        }
        
        .header span {
            color: #5B4B9E;
        }
        
        .empresa {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .info {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }
        
        .productos {
            margin-bottom: 10px;
        }
        
        .producto {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .detalle-producto {
            flex: 1;
        }
        
        .precios {
            text-align: right;
        }
        
        .total {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-weight: bold;
            font-size: 14px;
        }
        
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            text-align: center;
            font-size: 11px;
        }
        
        .gracias {
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .linea {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .ticket {
                box-shadow: none;
            }
            .no-print {
                display: none;
            }
        }
        
        .btn-imprimir {
            display: block;
            width: 80mm;
            margin: 20px auto;
            padding: 10px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            cursor: pointer;
        }
        
        .btn-imprimir:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div style="display: flex; flex-direction: column; align-items: center;">
        <button class="btn-imprimir no-print" onclick="window.print()">
            <i class="fas fa-print"></i> IMPRIMIR TICKET
        </button>
        
        <div class="ticket" id="ticket">
            <!-- HEADER -->
            <div class="header">
                <h1>Nexus<span>Digital</span></h1>
                <div class="empresa">
                    <div>RIF: 30-71234567-9</div>
                    <div>Av. Tecnología #123, CABA</div>
                    <div>Tel: 011-4127-2122</div>
                </div>
            </div>
            
            <!-- NÚMERO DE TICKET -->
            <div class="text-center" style="margin-bottom: 10px;">
                <strong>TICKET N° <?php echo str_pad($venta['id_orden'], 8, '0', STR_PAD_LEFT); ?></strong>
            </div>
            
            <!-- FECHA Y HORA -->
            <div class="info">
                <div style="display: flex; justify-content: space-between;">
                    <span>Fecha:</span>
                    <span><?php echo date('d/m/Y', strtotime($venta['fecha'])); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Hora:</span>
                    <span><?php echo date('H:i', strtotime($venta['fecha'])); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Cliente:</span>
                    <span><?php echo htmlspecialchars($venta['cliente_nombre']); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Vendedor:</span>
                    <span><?php echo $_SESSION['usuario_nombre'] ?? 'N/A'; ?></span>
                </div>
            </div>
            
            <!-- PRODUCTOS -->
            <div class="productos">
                <div style="display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 5px;">
                    <span>PRODUCTO</span>
                    <span>CANT</span>
                    <span>PRECIO</span>
                    <span>TOTAL</span>
                </div>
                
                <?php foreach($detalles as $detalle): ?>
                <div class="producto">
                    <div class="detalle-producto">
                        <?php echo htmlspecialchars($detalle['producto_nombre']); ?>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <span style="width: 30px; text-align: right;"><?php echo $detalle['cantidad']; ?></span>
                        <span style="width: 60px; text-align: right;">$<?php echo number_format($detalle['precio_unitario'], 2, ',', '.'); ?></span>
                        <span style="width: 70px; text-align: right;">$<?php echo number_format($detalle['precio_unitario'] * $detalle['cantidad'], 2, ',', '.'); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- LÍNEA SEPARADORA -->
            <div class="linea"></div>
            
            <!-- TOTALES -->
            <div style="margin-top: 10px;">
                <div style="display: flex; justify-content: space-between;">
                    <span>SUBTOTAL:</span>
                    <span>$<?php echo number_format($venta['total'], 2, ',', '.'); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>IVA 21%:</span>
                    <span>$<?php echo number_format($venta['total'] * 0.21, 2, ',', '.'); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 14px; font-weight: bold; margin-top: 5px;">
                    <span>TOTAL:</span>
                    <span>$<?php echo number_format($venta['total'], 2, ',', '.'); ?></span>
                </div>
            </div>
            
            <!-- MÉTODO DE PAGO -->
            <div style="margin-top: 15px; padding-top: 10px; border-top: 1px dashed #000;">
                <div style="display: flex; justify-content: space-between;">
                    <span>Método de Pago:</span>
                    <span><?php echo ucfirst(str_replace('_', ' ', $venta['metodo_pago'] ?? 'Efectivo')); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Estado:</span>
                    <span><?php echo $venta['estado'] == 'completada' ? 'PAGADO' : 'PENDIENTE'; ?></span>
                </div>
            </div>
            
            <!-- GRACIAS -->
            <div class="footer">
                <div class="gracias">¡GRACIAS POR SU COMPRA!</div>
                <div>Horario: Lun-Vie 8:00-17:30</div>
                <div>www.nexusdigital.com</div>
                <div style="margin-top: 10px; font-size: 10px;">
                    Ticket No Válido como Factura<br>
                    Conserve este comprobante para garantía
                </div>
                <div style="margin-top: 10px; font-size: 10px;">
                    <?php echo date('d/m/Y H:i:s'); ?> - #<?php echo str_pad($venta['id_orden'], 8, '0', STR_PAD_LEFT); ?>
                </div>
            </div>
        </div>
        
        <button class="btn-imprimir no-print" onclick="window.print()" style="margin-top: 20px; background: #17a2b8;">
            <i class="fas fa-print"></i> IMPRIMIR TICKET
        </button>
    </div>
</body>
</html>
<?php
require_once '../../../includes/funciones.php';
verificar_sesion();

// Obtener todas las garantías
$garantias = $pdo->query("
    SELECT g.*, c.nombre as cliente_nombre, p.nombre as producto_nombre 
    FROM GARANTIAS g
    JOIN ORDENES o ON g.id_orden = o.id_orden
    JOIN CLIENTES c ON o.id_cliente = c.id_cliente
    JOIN PRODUCTOS p ON g.id_producto = p.id_producto
    ORDER BY g.fecha_reclamo DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Garantías - Nexus Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f6fa;
            padding: 30px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
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
        .btn-nueva {
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
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
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            text-align: left;
            padding: 15px;
            background: #f8f9fa;
            color: #666;
            font-weight: 600;
        }
        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #333;
        }
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-abierta { background: #fff3cd; color: #856404; }
        .badge-en_proceso { background: #cce5ff; color: #004085; }
        .badge-resuelta { background: #d4edda; color: #155724; }
        .badge-rechazada { background: #f8d7da; color: #721c24; }
        .btn-ver {
            background: #17a2b8;
            color: white;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
        }
        .btn-editar {
            background: #ffc107;
            color: #000;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-shield-alt" style="color: #5B4B9E;"></i> Gestión de Garantías</h1>
            <div style="display: flex; gap: 15px;">
                <a href="nueva.php" class="btn-nueva">
                    <i class="fas fa-plus"></i> Nueva Garantía
                </a>
                <a href="../../index.php" class="btn-volver">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Producto</th>
                        <th>N° Serie</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($garantias as $garantia): ?>
                    <tr>
                        <td>#<?php echo $garantia['id_garantia']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($garantia['fecha_reclamo'])); ?></td>
                        <td><?php echo htmlspecialchars($garantia['cliente_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($garantia['producto_nombre']); ?></td>
                        <td><?php echo $garantia['numero_serie'] ?? 'N/A'; ?></td>
                        <td>
                            <span class="badge badge-<?php echo $garantia['estado']; ?>">
                                <?php echo traducir_estado_garantia($garantia['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="ver.php?id=<?php echo $garantia['id_garantia']; ?>" class="btn-ver">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <a href="editar.php?id=<?php echo $garantia['id_garantia']; ?>" class="btn-editar">
                                <i class="fas fa-edit"></i> Editar
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
<?php
require_once '../includes/funciones.php';
verificar_sesion();

// Si es admin, redirigir al dashboard de admin
if ($_SESSION['usuario_rol'] == 'admin') {
    header('Location: dashboard.php');
    exit;
}

$usuario_nombre = $_SESSION['usuario_nombre'];
$usuario_id = $_SESSION['usuario_id'];

// Obtener estadísticas del vendedor
$hoy = date('Y-m-d');

try {
    // Ventas del día (solo las de este vendedor)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as monto 
        FROM ordenes 
        WHERE usuario_id = ? AND DATE(fecha) = ?
    ");
    $stmt->execute([$usuario_id, $hoy]);
    $ventas_hoy = $stmt->fetch();
    
    // Ventas del mes
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as monto 
        FROM ordenes 
        WHERE usuario_id = ? AND MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())
    ");
    $stmt->execute([$usuario_id]);
    $ventas_mes = $stmt->fetch();
    
    // Total de ventas del vendedor
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as monto 
        FROM ordenes 
        WHERE usuario_id = ?
    ");
    $stmt->execute([$usuario_id]);
    $ventas_total = $stmt->fetch();
    
    // Ventas recientes
    $stmt = $pdo->prepare("
        SELECT o.*, c.nombre as cliente_nombre 
        FROM ordenes o
        JOIN clientes c ON o.id_cliente = c.id_cliente
        WHERE o.usuario_id = ?
        ORDER BY o.fecha DESC 
        LIMIT 10
    ");
    $stmt->execute([$usuario_id]);
    $ventas_recientes = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $ventas_hoy = ['total' => 0, 'monto' => 0];
    $ventas_mes = ['total' => 0, 'monto' => 0];
    $ventas_total = ['total' => 0, 'monto' => 0];
    $ventas_recientes = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Vendedor - Nexus Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="/nexus-digital/favicon.ico">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f6fa;
            overflow-x: hidden;
        }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            color: white;
            transition: all 0.3s;
            z-index: 100;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h2 {
            font-size: 24px;
            font-weight: 700;
        }
        
        .sidebar-header h2 span {
            color: #00D4E8;
        }
        
        .sidebar-header p {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 5px;
        }
        
        .user-info-sidebar {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .user-avatar {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 30px;
            font-weight: bold;
        }
        
        .user-name {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .user-role {
            font-size: 12px;
            opacity: 0.7;
        }
        
        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            margin: 5px 10px;
            border-radius: 10px;
        }
        
        .sidebar nav a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar nav a.active {
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            color: white;
        }
        
        .sidebar nav a i {
            width: 20px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        /* Header */
        .top-header {
            background: white;
            border-radius: 15px;
            padding: 20px 25px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .page-title h1 {
            font-size: 24px;
            color: #333;
        }
        
        .page-title p {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .btn-nueva-venta {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.2s;
        }
        
        .btn-nueva-venta:hover {
            transform: translateY(-2px);
        }
        
        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 10px;
            background: #f8f9fa;
        }
        
        .user-dropdown img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .stat-header h3 {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .stat-icon.primary {
            background: rgba(91,75,158,0.1);
            color: #5B4B9E;
        }
        
        .stat-icon.success {
            background: rgba(40,167,69,0.1);
            color: #28a745;
        }
        
        .stat-icon.warning {
            background: rgba(255,193,7,0.1);
            color: #ffc107;
        }
        
        .stat-icon.info {
            background: rgba(23,162,184,0.1);
            color: #17a2b8;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 13px;
        }
        
        /* Tabla */
        .section-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .section-header h2 {
            font-size: 18px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .table-container {
            overflow-x: auto;
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
            font-size: 13px;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            color: #555;
            font-size: 14px;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .badge-pendiente {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-procesando {
            background: #cce5ff;
            color: #004085;
        }
        
        .badge-completada {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-cancelada {
            background: #f8d7da;
            color: #721c24;
        }
        
        .btn-ver {
            background: #17a2b8;
            color: white;
            padding: 5px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            
            .sidebar-header h2 span,
            .sidebar-header p,
            .user-info-sidebar,
            .sidebar nav a span {
                display: none;
            }
            
            .sidebar nav a {
                justify-content: center;
                padding: 12px;
            }
            
            .sidebar nav a i {
                font-size: 20px;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Animaciones */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .stat-card, .section-card {
            animation: fadeIn 0.5s ease forwards;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Nexus<span>Digital</span></h2>
            <p>Sistema de Ventas</p>
        </div>
        
        <div class="user-info-sidebar">
            <div class="user-avatar">
                <?php echo strtoupper(substr($usuario_nombre, 0, 1)); ?>
            </div>
            <div class="user-name"><?php echo htmlspecialchars($usuario_nombre); ?></div>
            <div class="user-role">Vendedor</div>
        </div>
        
        <nav>
            <a href="dashboard_vendedor.php" class="active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="modulos/ventas/nueva.php">
                <i class="fas fa-shopping-cart"></i>
                <span>Nueva Venta</span>
            </a>
            <a href="modulos/ventas/">
                <i class="fas fa-list"></i>
                <span>Mis Ventas</span>
            </a>
            <a href="modulos/clientes/">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
            </a>
            <a href="modulos/productos/">
                <i class="fas fa-box"></i>
                <span>Productos</span>
            </a>
            <a href="../logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </a>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="top-header">
            <div class="page-title">
                <h1>¡Bienvenido, <?php echo htmlspecialchars($usuario_nombre); ?>!</h1>
                <p>Aquí está el resumen de tus ventas y actividades</p>
            </div>
            <div class="header-actions">
                <a href="modulos/ventas/nueva.php" class="btn-nueva-venta">
                    <i class="fas fa-plus"></i> Nueva Venta
                </a>
                <div class="user-dropdown">
                    <i class="fas fa-user-circle" style="font-size: 40px; color: #5B4B9E;"></i>
                </div>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <h3>Ventas Hoy</h3>
                    <div class="stat-icon primary">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $ventas_hoy['total']; ?></div>
                <div class="stat-label">Total: <?php echo formato_moneda($ventas_hoy['monto']); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <h3>Ventas del Mes</h3>
                    <div class="stat-icon success">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $ventas_mes['total']; ?></div>
                <div class="stat-label">Total: <?php echo formato_moneda($ventas_mes['monto']); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <h3>Total de Ventas</h3>
                    <div class="stat-icon warning">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo $ventas_total['total']; ?></div>
                <div class="stat-label">Total: <?php echo formato_moneda($ventas_total['monto']); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <h3>Comisión Estimada</h3>
                    <div class="stat-icon info">
                        <i class="fas fa-percent"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo formato_moneda($ventas_total['monto'] * 0.05); ?></div>
                <div class="stat-label">5% de comisión sobre ventas</div>
            </div>
        </div>
        
        <!-- Ventas Recientes -->
        <div class="section-card">
            <div class="section-header">
                <h2>
                    <i class="fas fa-history"></i>
                    Mis Ventas Recientes
                </h2>
                <a href="modulos/ventas/" style="color: #5B4B9E; text-decoration: none;">
                    Ver todas <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="table-container">
                <?php if (count($ventas_recientes) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>N° Venta</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($ventas_recientes as $venta): ?>
                        <tr>
                            <td>#<?php echo $venta['id_orden']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></td>
                            <td><?php echo htmlspecialchars($venta['cliente_nombre']); ?></td>
                            <td><?php echo formato_moneda($venta['total']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $venta['estado']; ?>">
                                    <?php echo traducir_estado_venta($venta['estado']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="modulos/ventas/ver.php?id=<?php echo $venta['id_orden']; ?>" class="btn-ver">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-shopping-cart" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                    <p>No tienes ventas registradas aún</p>
                    <a href="modulos/ventas/nueva.php" style="display: inline-block; margin-top: 15px; color: #5B4B9E;">
                        <i class="fas fa-plus"></i> Realizar primera venta
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Tips Rápidos -->
        <div class="section-card">
            <div class="section-header">
                <h2>
                    <i class="fas fa-lightbulb"></i>
                    Tips Rápidos
                </h2>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                <div style="padding: 15px; background: #f8f9fa; border-radius: 10px;">
                    <i class="fas fa-search" style="color: #5B4B9E; margin-bottom: 10px; display: block;"></i>
                    <strong>Buscar productos</strong>
                    <p style="font-size: 13px; color: #666; margin-top: 5px;">Usa el buscador para encontrar productos rápidamente</p>
                </div>
                <div style="padding: 15px; background: #f8f9fa; border-radius: 10px;">
                    <i class="fas fa-user-check" style="color: #28a745; margin-bottom: 10px; display: block;"></i>
                    <strong>Clientes frecuentes</strong>
                    <p style="font-size: 13px; color: #666; margin-top: 5px;">Guarda la información de tus clientes para ventas más rápidas</p>
                </div>
                <div style="padding: 15px; background: #f8f9fa; border-radius: 10px;">
                    <i class="fas fa-receipt" style="color: #ffc107; margin-bottom: 10px; display: block;"></i>
                    <strong>Facturación</strong>
                    <p style="font-size: 13px; color: #666; margin-top: 5px;">Genera facturas automáticas al finalizar cada venta</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Actualizar estadísticas cada 30 segundos (opcional)
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
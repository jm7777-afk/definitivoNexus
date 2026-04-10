<?php
require_once '../includes/funciones.php';
verificar_sesion();

$resumen = obtener_resumen_dashboard($pdo);
$ventas_recientes = obtener_ventas_recientes($pdo);
$stock_bajo = obtener_productos_stock_bajo($pdo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Nexus Digital Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f6fa;
            display: flex;
        }
        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1a1e2c 0%, #0a0e1a 100%);
            color: white;
            height: 100vh;
            position: fixed;
            overflow-y: auto;
        }
        .sidebar-header {
            padding: 25px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-header h2 {
            font-size: 24px;
            font-weight: 700;
        }
        .sidebar-header span { color: #00D4E8; }
        .user-info {
            padding: 20px;
            background: rgba(255,255,255,0.05);
            margin: 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-info i { font-size: 40px; color: #00D4E8; }
        .nav-menu {
            list-style: none;
            padding: 20px;
        }
        .nav-menu li { margin-bottom: 5px; }
        .nav-menu a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .nav-menu a:hover, .nav-menu a.active {
            background: rgba(91,75,158,0.3);
            color: white;
        }
        .nav-menu i {
            width: 25px;
            margin-right: 10px;
            color: #00D4E8;
        }
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .page-title h1 { font-size: 24px; color: #333; }
        .page-title p { color: #666; font-size: 14px; }
        .logout-btn {
            background: #ff4757;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }
        .logout-btn:hover { background: #ff6b81; }
        /* Cards */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .card-info h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            font-weight: 500;
        }
        .card-info .number {
            font-size: 32px;
            font-weight: 700;
            color: #333;
        }
        .card-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }
        /* Tables */
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .table-header h2 { font-size: 18px; color: #333; }
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
            color: #333;
            font-size: 14px;
        }
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .stock-low { color: #dc3545; font-weight: 600; }
        a { text-decoration: none; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Nexus<span>Digital</span></h2>
            <p style="color: rgba(255,255,255,0.5); font-size: 12px; margin-top: 5px;">Sistema Interno</p>
        </div>
        
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <div>
                <strong style="display: block;"><?php echo $_SESSION['usuario_nombre']; ?></strong>
                <small style="color: rgba(255,255,255,0.7);">
                    <?php echo $_SESSION['usuario_rol'] == 'admin' ? 'Administrador' : 'Vendedor'; ?>
                </small>
            </div>
        </div>
        
        <ul class="nav-menu">
            <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="modulos/ventas/"><i class="fas fa-shopping-cart"></i> Ventas</a></li>
            <li><a href="modulos/productos/"><i class="fas fa-box"></i> Productos</a></li>
            <li><a href="modulos/productos/nuevo.php"><i class="fas fa-box"></i> compra</a></li>
            <li><a href="modulos/clientes/"><i class="fas fa-users"></i> Clientes</a></li>
            <li><a href="modulos/garantias/"><i class="fas fa-shield-alt"></i> Garantías</a></li>
            <?php if ($_SESSION['usuario_rol'] == 'admin'): ?>
            <li style="margin-top: 20px;"><a href="modulos/user/user.php"><i class="fas fa-user-cog"></i> Usuarios</a></li>
            <?php endif; ?>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Dashboard</h1>
                <p>Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?></p>
            </div>
            <a href="salir.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
        
        <!-- Cards -->
        <div class="cards-grid">
            <div class="card">
                <div class="card-info">
                    <h3>Clientes</h3>
                    <div class="number"><?php echo $resumen['total_clientes']; ?></div>
                    <small style="color: #666;">Registrados</small>
                </div>
                <div class="card-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="card">
                <div class="card-info">
                    <h3>Productos</h3>
                    <div class="number"><?php echo $resumen['total_productos']; ?></div>
                    <small style="color: #666;">En catálogo</small>
                </div>
                <div class="card-icon"><i class="fas fa-box"></i></div>
            </div>
            <div class="card">
                <div class="card-info">
                    <h3>Ventas Hoy</h3>
                    <div class="number"><?php echo $resumen['ventas_hoy']; ?></div>
                    <small style="color: #666;"><?php echo formato_moneda($resumen['ventas_hoy_monto']); ?></small>
                </div>
                <div class="card-icon"><i class="fas fa-shopping-cart"></i></div>
            </div>
            <div class="card">
                <div class="card-info">
                    <h3>Pendientes</h3>
                    <div class="number"><?php echo $resumen['ventas_pendientes']; ?></div>
                    <small style="color: #666;">Por procesar</small>
                </div>
                <div class="card-icon"><i class="fas fa-clock"></i></div>
            </div>
            <div class="card">
                <div class="card-info">
                    <h3>Stock Bajo</h3>
                    <div class="number <?php echo $resumen['stock_bajo'] > 0 ? 'stock-low' : ''; ?>">
                        <?php echo $resumen['stock_bajo']; ?>
                    </div>
                    <small style="color: #666;">Críticos</small>
                </div>
                <div class="card-icon" style="background: linear-gradient(135deg, #ff4757 0%, #ff6b81 100%);">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
            <div class="card">
                <div class="card-info">
                    <h3>Garantías</h3>
                    <div class="number"><?php echo $resumen['garantias_activas']; ?></div>
                    <small style="color: #666;">Activas</small>
                </div>
                <div class="card-icon"><i class="fas fa-shield-alt"></i></div>
            </div>
        </div>
        
        <!-- Ventas Recientes -->
        <div class="table-container">
            <div class="table-header">
                <h2><i class="fas fa-history"></i> Ventas Recientes</h2>
                <a href="modulos/ventas/" style="color: #5B4B9E;">Ver todas</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>N° Venta</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($ventas_recientes as $venta): ?>
                    <tr>
                        <td>#<?php echo $venta['id_orden']; ?></td>
                        <td><?php echo $venta['cliente_nombre']; ?></td>
                        <td><?php echo formato_fecha($venta['fecha']); ?></td>
                        <td><?php echo formato_moneda($venta['total']); ?></td>
                        <td>
                            <span class="badge badge-<?php 
                                echo $venta['estado'] == 'completada' ? 'success' : 
                                    ($venta['estado'] == 'pendiente' ? 'warning' : 'danger'); 
                            ?>">
                                <?php echo traducir_estado_venta($venta['estado']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Stock Bajo -->
        <div class="table-container">
            <div class="table-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Productos con Stock Bajo</h2>
                <a href="modulos/productos/" style="color: #5B4B9E;">Ver inventario</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Stock</th>
                        <th>Precio</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($stock_bajo as $producto): ?>
                    <tr>
                        <td><strong><?php echo $producto['nombre']; ?></strong></td>
                        <td class="stock-low"><?php echo $producto['stock']; ?> uds</td>
                        <td><?php echo formato_moneda($producto['precio']); ?></td>
                        <td>
                            <a href="modulos/productos/editar.php?id=<?php echo $producto['id_producto']; ?>" 
                               style="color: #5B4B9E;">
                                <i class="fas fa-plus-circle"></i> Agregar stock
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
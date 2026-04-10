
<?php
session_start();
require_once '../config/conexion.php';

// Verificar si el cliente está logueado
if (!isset($_SESSION['cliente_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_SESSION['cliente_id'];

// Obtener datos del cliente
$stmt = $pdo->prepare("SELECT * FROM CLIENTES WHERE id_cliente = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch();

// Obtener compras del cliente
$stmt = $pdo->prepare("
    SELECT o.*, COUNT(do.id_detalle) as total_productos 
    FROM ORDENES o 
    LEFT JOIN DETALLE_ORDEN do ON o.id_orden = do.id_orden 
    WHERE o.id_cliente = ? 
    GROUP BY o.id_orden 
    ORDER BY o.fecha DESC 
    LIMIT 10
");
$stmt->execute([$id]);
$compras = $stmt->fetchAll();

// Actualizar datos
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar'])) {
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    
    $stmt = $pdo->prepare("UPDATE CLIENTES SET nombre = ?, telefono = ?, direccion = ? WHERE id_cliente = ?");
    if ($stmt->execute([$nombre, $telefono, $direccion, $id])) {
        $_SESSION['cliente_nombre'] = $nombre;
        $mensaje = "Datos actualizados correctamente";
        // Recargar datos
        $stmt = $pdo->prepare("SELECT * FROM CLIENTES WHERE id_cliente = ?");
        $stmt->execute([$id]);
        $cliente = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Nexus Digital</title>
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
        }
        
        .header {
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            color: white;
            padding: 20px 5%;
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .profile-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .profile-title i {
            font-size: 40px;
            color: #5B4B9E;
        }
        
        .profile-title h1 {
            font-size: 28px;
            color: #333;
        }
        
        .btn-logout {
            background: #dc3545;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-logout:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .profile-card h2 {
            font-size: 20px;
            color: #333;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 15px;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #666;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #5B4B9E;
            outline: none;
        }
        
        .btn-update {
            background: #28a745;
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }
        
        .btn-update:hover {
            background: #218838;
            transform: translateY(-2px);
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
        }
        
        .purchases-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .purchases-section h2 {
            font-size: 20px;
            color: #333;
            margin-bottom: 25px;
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
        
        .badge-completada {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-pendiente {
            background: #fff3cd;
            color: #856404;
        }
        
        .no-purchases {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .no-purchases i {
            font-size: 50px;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-header {
                flex-direction: column;
                gap: 20px;
                align-items: flex-start;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <h1 style="color: white;">Nexus<span style="color: #00D4E8;">Digital</span></h1>
            </div>
            <div>
                <a href="index.php" style="color: white; text-decoration: none;">
                    <i class="fas fa-store"></i> Tienda
                </a>
            </div>
        </div>
    </header>
    
    <div class="main-container">
        <div class="profile-header">
            <div class="profile-title">
                <i class="fas fa-user-circle"></i>
                <h1>Mi Perfil</h1>
            </div>
            <a href="logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="mensaje">
                <i class="fas fa-check-circle"></i> <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-grid">
            <!-- Información Personal -->
            <div class="profile-card">
                <h2><i class="fas fa-id-card"></i> Mis Datos</h2>
                
                <div class="info-row">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['nombre']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['email']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Teléfono:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['telefono'] ?? 'No registrado'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dirección:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['direccion'] ?? 'No registrada'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Cliente desde:</span>
                    <span class="info-value"><?php echo date('d/m/Y', strtotime($cliente['fecha_registro'])); ?></span>
                </div>
            </div>
            
            <!-- Editar Datos -->
            <div class="profile-card">
                <h2><i class="fas fa-edit"></i> Actualizar Datos</h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Nombre completo</label>
                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="tel" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Dirección</label>
                        <textarea name="direccion" rows="3"><?php echo htmlspecialchars($cliente['direccion'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" name="actualizar" class="btn-update">
                        <i class="fas fa-save"></i> Actualizar Datos
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Historial de Compras -->
        <div class="purchases-section">
            <h2><i class="fas fa-history"></i> Mis Últimas Compras</h2>
            
            <?php if (count($compras) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>N° Orden</th>
                            <th>Fecha</th>
                            <th>Productos</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($compras as $compra): ?>
                        <tr>
                            <td>#<?php echo $compra['id_orden']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($compra['fecha'])); ?></td>
                            <td><?php echo $compra['total_productos']; ?> producto(s)</td>
                            <td>$<?php echo number_format($compra['total'], 2, ',', '.'); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $compra['estado']; ?>">
                                    <?php echo ucfirst($compra['estado']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="../modulos/ventas/factura.php?id=<?php echo $compra['id_orden']; ?>" 
                                   style="color: #5B4B9E; text-decoration: none;">
                                    <i class="fas fa-file-invoice"></i> Ver
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-purchases">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>No has realizado compras aún</h3>
                    <p style="margin-top: 10px;">
                        <a href="index.php" style="color: #5B4B9E; font-weight: 600;">
                            Visita nuestra tienda
                        </a> y descubre nuestros productos.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
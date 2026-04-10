<?php
session_start();
require_once '../config/conexion.php';

$id = $_GET['id'] ?? 0;

// Obtener producto
$stmt = $pdo->prepare("SELECT * FROM PRODUCTOS WHERE id_producto = ?");
// O si quieres solo productos con stock:
$stmt = $pdo->prepare("SELECT * FROM PRODUCTOS WHERE id_producto = ? AND stock > 0");
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    header('Location: index.php');
    exit;
}

$cliente_logueado = isset($_SESSION['ci']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($producto['nombre']); ?> - Nexus Digital</title>
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
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo h1 {
            font-size: 28px;
            font-weight: 700;
        }
        
        .logo span {
            color: #00D4E8;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .back-link {
            margin-bottom: 30px;
        }
        
        .back-link a {
            color: #666;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s;
        }
        
        .back-link a:hover {
            color: #5B4B9E;
        }
        
        .product-detail {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }
        
        .product-image {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #eee;
        }
        
        .product-image i {
            font-size: 200px;
            color: #5B4B9E;
            opacity: 0.5;
        }
        
        .product-info h2 {
            font-size: 32px;
            color: #333;
            margin-bottom: 20px;
        }
        
        .product-code {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        
        .product-description {
            margin-bottom: 30px;
        }
        
        .product-description h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .product-description p {
            color: #666;
            line-height: 1.8;
        }
        
        .product-stock {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }
        
        .stock-status {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .stock-alto {
            background: #d4edda;
            color: #155724;
        }
        
        .stock-medio {
            background: #fff3cd;
            color: #856404;
        }
        
        .stock-bajo {
            background: #f8d7da;
            color: #721c24;
        }
        
        .product-price {
            margin-bottom: 30px;
        }
        
        .price-label {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .current-price {
            font-size: 48px;
            font-weight: 700;
            color: #5B4B9E;
        }
        
        .product-actions {
            display: flex;
            gap: 20px;
            margin-top: 30px;
        }
        
        .btn-contact {
            flex: 1;
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            color: white;
            padding: 16px 30px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
        }
        
        .btn-contact:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(91,75,158,0.4);
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 16px 30px;
            border-radius: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: #5a6268;
        }
        
        .login-message {
            margin-top: 20px;
            padding: 15px;
            background: #fff3cd;
            color: #856404;
            border-radius: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .login-message a {
            color: #5B4B9E;
            font-weight: 600;
            text-decoration: none;
        }
        
        .login-message a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .product-detail {
                grid-template-columns: 1fr;
                gap: 30px;
                padding: 20px;
            }
            
            .product-image i {
                font-size: 120px;
            }
            
            .product-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <h1>Nexus<span>Digital</span></h1>
            </div>
            <div>
                <?php if($cliente_logueado): ?>
                    <span style="color: white;">
                        <i class="fas fa-user"></i> <?php echo $_SESSION['cliente_nombre']; ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </header>
    
    <div class="main-container">
        <div class="back-link">
            <a href="index.php">
                <i class="fas fa-arrow-left"></i> Volver al catálogo
            </a>
        </div>
        
        <div class="product-detail">
            <div class="product-image">
                <i class="fas fa-laptop"></i>
            </div>
            
            <div class="product-info">
                <h2><?php echo htmlspecialchars($producto['nombre']); ?></h2>
                
                <div class="product-code">
                    <i class="fas fa-barcode"></i> Código: #<?php echo $producto['id_producto']; ?>
                </div>
                
                <div class="product-description">
                    <h3><i class="fas fa-info-circle"></i> Descripción</h3>
                    <p><?php echo nl2br(htmlspecialchars($producto['descripcion'] ?? 'Sin descripción disponible.')); ?></p>
                </div>
                
                <div class="product-stock">
                    <?php 
                    $stock = $producto['stock'];
                    if ($stock > 10) {
                        $stock_class = 'stock-alto';
                        $stock_text = 'Stock disponible';
                    } elseif ($stock > 5) {
                        $stock_class = 'stock-medio';
                        $stock_text = 'Últimas unidades';
                    } elseif ($stock > 0) {
                        $stock_class = 'stock-bajo';
                        $stock_text = 'Stock bajo';
                    } else {
                        $stock_class = 'stock-bajo';
                        $stock_text = 'Producto agotado';
                    }
                    ?>
                    <span class="stock-status <?php echo $stock_class; ?>">
                        <i class="fas fa-cubes"></i> <?php echo $stock_text; ?>
                    </span>
                    <?php if($stock > 0): ?>
                        <p style="color: #666; margin-top: 10px;">
                            <strong><?php echo $stock; ?></strong> unidades disponibles
                        </p>
                    <?php else: ?>
                        <p style="color: #dc3545; margin-top: 10px;">
                            <i class="fas fa-exclamation-triangle"></i> Producto sin stock
                        </p>
                    <?php endif; ?>
                </div>
                
                <div class="product-price">
                    <div class="price-label">Precio de venta al público</div>
                    <span class="current-price">$<?php echo number_format($producto['precio'], 2, ',', '.'); ?></span>
                </div>
                
                <div class="product-actions">
                    <?php if($stock > 0): ?>
                        <?php if($cliente_logueado): ?>
                            <a href="comprar.php?id=<?php echo $producto['id_producto']; ?>" class="btn-contact">
                                <i class="fas fa-shopping-cart"></i> Comprar ahora
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="btn-contact">
                                <i class="fas fa-sign-in-alt"></i> Inicia sesión para comprar
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn-contact" style="background: #6c757d;" disabled>
                            <i class="fas fa-times-circle"></i> Sin stock
                        </button>
                    <?php endif; ?>
                    
                    <a href="index.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Seguir viendo
                    </a>
                </div>
                
                <?php if(!$cliente_logueado && $stock > 0): ?>
                    <div class="login-message">
                        <i class="fas fa-info-circle"></i>
                        ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a> para comprar. 
                        ¿Eres nuevo? <a href="registro.php">Regístrate aquí</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
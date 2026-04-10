<?php
session_start();
require_once '../config/conexion.php';

// Verificar si el cliente está logueado
$cliente_logueado = isset($_SESSION['cliente_id']);

// SOLO BÚSQUEDA - SIN CATEGORÍAS
$busqueda = $_GET['busqueda'] ?? '';
$orden = $_GET['orden'] ?? 'nombre';
$precio_min = $_GET['precio_min'] ?? '';
$precio_max = $_GET['precio_max'] ?? '';

// Construir consulta
$sql = "SELECT * FROM PRODUCTOS WHERE stock > 0";
$params = [];

if ($busqueda) {
    $sql .= " AND (nombre LIKE ? OR descripcion LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

if ($precio_min !== '') {
    $sql .= " AND precio >= ?";
    $params[] = $precio_min;
}

if ($precio_max !== '') {
    $sql .= " AND precio <= ?";
    $params[] = $precio_max;
}

// Ordenamiento
switch ($orden) {
    case 'precio_asc':
        $sql .= " ORDER BY precio ASC";
        break;
    case 'precio_desc':
        $sql .= " ORDER BY precio DESC";
        break;
    case 'nombre':
    default:
        $sql .= " ORDER BY nombre ASC";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

// Función para verificar si la imagen existe
function getImagenProducto($imagen) {
    if (!empty($imagen)) {
        // Verificar si la imagen existe en la carpeta
        $ruta_imagen = '../uploads/productos/' . $imagen;
        if (file_exists($ruta_imagen)) {
            return 'uploads/productos/' . $imagen;
        }
        
        // Buscar en diferentes formatos
        $extensiones = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        foreach ($extensiones as $ext) {
            $ruta = '../uploads/productos/' . pathinfo($imagen, PATHINFO_FILENAME) . '.' . $ext;
            if (file_exists($ruta)) {
                return 'uploads/productos/' . pathinfo($imagen, PATHINFO_FILENAME) . '.' . $ext;
            }
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
    <title>Catálogo de Productos - Nexus Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="/nexus-digital/favicon.ico">
    <style>
        /* [Mantén todos los estilos anteriores] */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
        }
        
        /* Header Mejorado */
        .header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 15px 5%;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .logo h1 {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, #fff 0%, #00D4E8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .logo p {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 5px;
        }
        
        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #00D4E8;
            transition: width 0.3s;
        }
        
        .nav-links a:hover::after {
            width: 100%;
        }
        
        .nav-links a:hover {
            color: #00D4E8;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-name {
            background: rgba(255,255,255,0.1);
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 14px;
            backdrop-filter: blur(10px);
        }
        
        .btn-logout {
            background: rgba(220,53,69,0.8);
            color: white;
            padding: 8px 16px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .btn-logout:hover {
            background: #dc3545;
            transform: translateY(-2px);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #00D4E8 0%, #0099ff 100%);
            color: white;
            padding: 10px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,212,232,0.3);
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 5%;
            text-align: center;
            color: white;
        }
        
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            animation: fadeInUp 0.6s ease;
        }
        
        .hero p {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 30px;
            animation: fadeInUp 0.8s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Búsqueda Avanzada */
        .search-section {
            max-width: 1400px;
            margin: -30px auto 30px;
            padding: 0 20px;
            position: relative;
            z-index: 10;
        }
        
        .search-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .search-box {
            margin-bottom: 20px;
        }
        
        .search-box label {
            display: block;
            margin-bottom: 10px;
            color: #666;
            font-weight: 600;
            font-size: 14px;
        }
        
        .search-input {
            display: flex;
            gap: 15px;
        }
        
        .search-input input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e1e1e1;
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .search-input input:focus {
            border-color: #5B4B9E;
            outline: none;
            box-shadow: 0 0 0 3px rgba(91,75,158,0.1);
        }
        
        .search-input button {
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0 30px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .search-input button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(91,75,158,0.3);
        }
        
        /* Filtros */
        .filters {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .filter-group {
            flex: 1;
            min-width: 150px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-size: 12px;
        }
        
        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }
        
        .btn-clear {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-clear:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        /* Productos */
        .products-section {
            max-width: 1400px;
            margin: 0 auto 50px;
            padding: 0 20px;
        }
        
        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .products-header h2 {
            font-size: 28px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .products-count {
            background: #e9ecef;
            padding: 8px 16px;
            border-radius: 25px;
            color: #666;
            font-size: 14px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .product-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .product-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: 600;
            z-index: 1;
        }
        
        .product-image {
            height: 220px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.05);
        }
        
        .product-image i {
            font-size: 80px;
            color: #5B4B9E;
            transition: transform 0.3s;
        }
        
        .product-card:hover .product-image i {
            transform: scale(1.1);
        }
        
        .product-info {
            padding: 25px;
        }
        
        .product-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .product-description {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-stock {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            font-size: 13px;
        }
        
        .stock-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 11px;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }
        
        .current-price {
            font-size: 28px;
            font-weight: 700;
            color: #5B4B9E;
        }
        
        .old-price {
            font-size: 14px;
            color: #999;
            text-decoration: line-through;
            margin-left: 10px;
        }
        
        .btn-view {
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-view:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(91,75,158,0.3);
        }
        
        /* No results */
        .no-products {
            grid-column: 1 / -1;
            text-align: center;
            padding: 80px;
            background: white;
            border-radius: 20px;
        }
        
        .no-products i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        /* Footer Mejorado */
        .footer {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 60px 5% 30px;
            margin-top: 50px;
        }
        
        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }
        
        .footer-section h3 {
            margin-bottom: 20px;
            color: #00D4E8;
            font-size: 18px;
        }
        
        .footer-section p {
            color: #aaa;
            line-height: 1.8;
            font-size: 14px;
        }
        
        .footer-section i {
            margin-right: 10px;
            color: #00D4E8;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-links a {
            color: white;
            background: rgba(255,255,255,0.1);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            background: #00D4E8;
            transform: translateY(-3px);
        }
        
        .footer-bottom {
            max-width: 1400px;
            margin: 0 auto;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            color: #aaa;
            font-size: 13px;
        }
        
        /* Scroll to top */
        .scroll-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #5B4B9E;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .scroll-top.show {
            opacity: 1;
            visibility: visible;
        }
        
        .scroll-top:hover {
            background: #2B7CB3;
            transform: translateY(-5px);
        }
        
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 32px;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .search-input {
                flex-direction: column;
            }
            
            .filters {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <h1>Nexus<span>Digital</span></h1>
                <p>Tecnología que conecta</p>
            </div>
            
            <div class="nav-links">
                <a href="index.php"><i class="fas fa-home"></i> Inicio</a>
                <a href="#productos"><i class="fas fa-box"></i> Productos</a>
                <a href="#contacto"><i class="fas fa-envelope"></i> Contacto</a>
                
                <div class="user-menu">
                    <?php if ($cliente_logueado): ?>
                        <span class="user-name">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['cliente_nombre']); ?>
                        </span>
                        <a href="perfil.php" style="color: white;">
                            <i class="fas fa-cog"></i>
                        </a>
                        <a href="logout.php" class="btn-logout">
                            <i class="fas fa-sign-out-alt"></i> Salir
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn-login">
                            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                        </a>
                        <a href="registro.php" style="color: white;">
                            <i class="fas fa-user-plus"></i> Registrarse
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Hero Section -->
    <section class="hero">
        <h1>Bienvenido a Nexus Digital</h1>
        <p>Descubre la mejor tecnología al alcance de tu mano</p>
    </section>
    
    <!-- Búsqueda Avanzada -->
    <div class="search-section">
        <div class="search-container">
            <form method="GET" id="searchForm">
                <div class="search-box">
                    <label><i class="fas fa-search"></i> Buscar productos</label>
                    <div class="search-input">
                        <input type="text" name="busqueda" id="busqueda" 
                               placeholder="¿Qué estás buscando? Ej: Laptop, Mouse, Teclado..." 
                               value="<?php echo htmlspecialchars($busqueda); ?>">
                        <button type="submit">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </div>
                
                <div class="filters">
                    <div class="filter-group">
                        <label><i class="fas fa-sort"></i> Ordenar por</label>
                        <select name="orden" id="orden">
                            <option value="nombre" <?php echo $orden == 'nombre' ? 'selected' : ''; ?>>Nombre</option>
                            <option value="precio_asc" <?php echo $orden == 'precio_asc' ? 'selected' : ''; ?>>Precio: menor a mayor</option>
                            <option value="precio_desc" <?php echo $orden == 'precio_desc' ? 'selected' : ''; ?>>Precio: mayor a menor</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fas fa-dollar-sign"></i> Precio mínimo</label>
                        <input type="number" name="precio_min" placeholder="Desde" 
                               value="<?php echo htmlspecialchars($precio_min); ?>" step="1000">
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fas fa-dollar-sign"></i> Precio máximo</label>
                        <input type="number" name="precio_max" placeholder="Hasta" 
                               value="<?php echo htmlspecialchars($precio_max); ?>" step="1000">
                    </div>
                    
                    <?php if($busqueda || $precio_min || $precio_max || $orden != 'nombre'): ?>
                    <div class="filter-group">
                        <label>&nbsp;</label>
                        <a href="index.php" class="btn-clear">
                            <i class="fas fa-times"></i> Limpiar filtros
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Productos -->
    <div class="products-section" id="productos">
        <div class="products-header">
            <h2>
                <i class="fas fa-box" style="color: #5B4B9E;"></i> Nuestros Productos
            </h2>
            <div class="products-count">
                <i class="fas fa-tag"></i> <?php echo count($productos); ?> producto(s) encontrado(s)
            </div>
        </div>
        
        <div class="products-grid" id="productsGrid">
            <?php if (count($productos) > 0): ?>
                <?php foreach($productos as $producto): ?>
                    <div class="product-card">
                        <?php if($producto['stock'] <= 5 && $producto['stock'] > 0): ?>
                            <div class="product-badge">
                                <i class="fas fa-exclamation-triangle"></i> Últimas unidades
                            </div>
                        <?php elseif($producto['stock'] == 0): ?>
                            <div class="product-badge" style="background: #dc3545;">
                                <i class="fas fa-times-circle"></i> Agotado
                            </div>
                        <?php endif; ?>
                        
                        <div class="product-image">
                            <?php 
                            $ruta_imagen = getImagenProducto($producto['imagen']);
                            if ($ruta_imagen && file_exists('../' . $ruta_imagen)): 
                            ?>
                                <img src="<?php echo $ruta_imagen; ?>" 
                                     alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                     onerror="this.src='https://placehold.co/400x400/e9ecef/5B4B9E?text=Sin+Imagen'">
                            <?php else: ?>
                                <i class="fas fa-microchip"></i>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                            <p class="product-description">
                                <?php echo htmlspecialchars(substr($producto['descripcion'] ?? 'Sin descripción', 0, 100)); ?>
                            </p>
                            
                            <div class="product-stock">
                                <?php 
                                $stock = $producto['stock'];
                                if ($stock > 10) {
                                    $stock_class = 'stock-alto';
                                    $stock_text = '✓ Stock disponible';
                                } elseif ($stock > 5) {
                                    $stock_class = 'stock-medio';
                                    $stock_text = '⚠️ Stock limitado';
                                } elseif ($stock > 0) {
                                    $stock_class = 'stock-bajo';
                                    $stock_text = '⚠️ ¡Últimas unidades!';
                                } else {
                                    $stock_class = 'stock-bajo';
                                    $stock_text = '✗ Sin stock';
                                }
                                ?>
                                <span class="stock-status <?php echo $stock_class; ?>">
                                    <?php echo $stock_text; ?>
                                </span>
                                <?php if($stock > 0): ?>
                                    <span style="color: #666; font-size: 12px;">
                                        <?php echo $stock; ?> disponibles
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-price">
                                <div>
                                    <span class="current-price">$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></span>
                                    <?php if($producto['precio'] > 100000): ?>
                                        <span class="old-price">$<?php echo number_format($producto['precio'] * 1.2, 0, ',', '.'); ?></span>
                                    <?php endif; ?>
                                </div>
                                <a href="producto.php?id=<?php echo $producto['id_producto']; ?>" class="btn-view">
                                    <i class="fas fa-eye"></i> Ver detalles
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-products">
                    <i class="fas fa-box-open"></i>
                    <h3 style="margin-bottom: 10px; color: #333;">No hay productos disponibles</h3>
                    <p style="color: #666;">No se encontraron productos que coincidan con tu búsqueda.</p>
                    <?php if($busqueda || $precio_min || $precio_max): ?>
                        <a href="index.php" style="display: inline-block; margin-top: 20px; color: #5B4B9E; text-decoration: none;">
                            <i class="fas fa-arrow-left"></i> Ver todos los productos
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer" id="contacto">
        <div class="footer-content">
            <div class="footer-section">
                <h3><i class="fas fa-rocket"></i> Nexus Digital</h3>
                <p>Somos tu nexo confiable con la tecnología de vanguardia. Ofrecemos productos de alta calidad y soporte especializado para garantizar tu satisfacción.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3><i class="fas fa-info-circle"></i> Información</h3>
                <p><i class="fas fa-map-marker-alt"></i> Av. Tecnología #123, CABA</p>
                <p><i class="fas fa-phone"></i> +54 11 4127-2122</p>
                <p><i class="fas fa-envelope"></i> info@nexusdigital.com</p>
                <p><i class="fas fa-clock"></i> Lun-Vie: 8:00 - 17:30</p>
            </div>
            
            <div class="footer-section">
                <h3><i class="fas fa-shield-alt"></i> Garantías</h3>
                <p>✓ 1 año de garantía en todos los productos</p>
                <p>✓ Soporte técnico especializado</p>
                <p>✓ Envíos a todo el país</p>
                <p>✓ Medios de pago seguros</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2026 Nexus Digital - Todos los derechos reservados</p>
            <p style="margin-top: 10px;">
                <a href="#" style="color: #aaa; text-decoration: none;">Términos y condiciones</a> | 
                <a href="#" style="color: #aaa; text-decoration: none;">Política de privacidad</a>
            </p>
        </div>
    </footer>
    
    <!-- Scroll to top -->
    <div class="scroll-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
        <i class="fas fa-arrow-up"></i>
    </div>
    
    <script>
        // Filtros automáticos al cambiar select/inputs
        document.getElementById('orden').addEventListener('change', function() {
            document.getElementById('searchForm').submit();
        });
        
        // Debounce para precio min/max
        let timeout;
        const priceInputs = document.querySelectorAll('input[name="precio_min"], input[name="precio_max"]');
        priceInputs.forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    document.getElementById('searchForm').submit();
                }, 500);
            });
        });
        
        // Scroll to top button
        window.addEventListener('scroll', function() {
            const scrollTop = document.querySelector('.scroll-top');
            if (window.scrollY > 300) {
                scrollTop.classList.add('show');
            } else {
                scrollTop.classList.remove('show');
            }
        });
        
        // Animación de carga para productos
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.product-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        card.style.transition = 'all 0.5s ease';
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 50);
                }, index * 100);
            });
        });
    </script>
</body>
</html>
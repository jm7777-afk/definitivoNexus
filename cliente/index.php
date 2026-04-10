<?php
// Activar errores para depuración (eliminar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Intentar incluir conexión con manejo de errores
try {
    require_once '../config/conexion.php';
} catch (Exception $e) {
    die("Error de configuración: " . $e->getMessage());
}

// Verificar si el cliente está logueado
$cliente_logueado = isset($_SESSION['cliente_id']);

// SOLO BÚSQUEDA - SIN CATEGORÍAS
$busqueda = $_GET['busqueda'] ?? '';
$orden = $_GET['orden'] ?? 'nombre';
$precio_min = $_GET['precio_min'] ?? '';
$precio_max = $_GET['precio_max'] ?? '';

// Construir consulta
$sql = "SELECT * FROM productos WHERE stock > 0"; // Cambié PRODUCTOS a productos (minúsculas)
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

$productos = [];
try {
    if (isset($pdo) && $pdo) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $productos = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    // No mostrar error, solo productos vacíos
    $productos = [];
}

// Función para verificar si la imagen existe (CORREGIDA)
function getImagenProducto($imagen) {
    if (empty($imagen)) {
        return null;
    }
    
    // Verificar si la imagen existe en la carpeta (ruta CORREGIDA)
    $ruta_imagen = '../uploads/productos/' . $imagen;
    if (file_exists($ruta_imagen)) {
        return '/nexus-digital/uploads/productos/' . $imagen;
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
        }
        
        .header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 15px 5%;
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
            transition: color 0.3s;
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
        }
        
        .btn-logout {
            background: rgba(220,53,69,0.8);
            color: white;
            padding: 8px 16px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 14px;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #00D4E8 0%, #0099ff 100%);
            color: white;
            padding: 10px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
        }
        
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 5%;
            text-align: center;
            color: white;
        }
        
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 18px;
            margin-bottom: 30px;
        }
        
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
        }
        
        .search-input input:focus {
            border-color: #5B4B9E;
            outline: none;
        }
        
        .search-input button {
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0 30px;
            cursor: pointer;
            font-weight: 600;
        }
        
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
        }
        
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
            transition: transform 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .product-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #ff6b6b;
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
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-image i {
            font-size: 80px;
            color: #5B4B9E;
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
        }
        
        .stock-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
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
        }
        
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
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['cliente_nombre'] ?? 'Cliente'); ?>
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
    
    <section class="hero">
        <h1>Bienvenido a Nexus Digital</h1>
        <p>Descubre la mejor tecnología al alcance de tu mano</p>
    </section>
    
    <div class="search-section">
        <div class="search-container">
            <form method="GET" id="searchForm">
                <div class="search-box">
                    <label><i class="fas fa-search"></i> Buscar productos</label>
                    <div class="search-input">
                        <input type="text" name="busqueda" id="busqueda" 
                               placeholder="¿Qué estás buscando?..." 
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
                        <input type="number" name="precio_min" placeholder="Desde" value="<?php echo htmlspecialchars($precio_min); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fas fa-dollar-sign"></i> Precio máximo</label>
                        <input type="number" name="precio_max" placeholder="Hasta" value="<?php echo htmlspecialchars($precio_max); ?>">
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
    
    <div class="products-section" id="productos">
        <div class="products-header">
            <h2>
                <i class="fas fa-box" style="color: #5B4B9E;"></i> Nuestros Productos
            </h2>
            <div class="products-count">
                <i class="fas fa-tag"></i> <?php echo count($productos); ?> producto(s)
            </div>
        </div>
        
        <div class="products-grid">
            <?php if (count($productos) > 0): ?>
                <?php foreach($productos as $producto): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($producto['imagen'] && file_exists('../uploads/productos/' . $producto['imagen'])): ?>
                                <img src="/nexus-digital/uploads/productos/<?php echo $producto['imagen']; ?>" 
                                     alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                            <?php else: ?>
                                <i class="fas fa-microchip"></i>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                            <p class="product-description">
                                <?php echo htmlspecialchars(substr($p
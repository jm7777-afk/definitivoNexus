<?php
// ==================== CONFIGURACIÓN INICIAL ====================
// Incluir la conexión usando ruta correcta
require_once dirname(__DIR__) . '/config/conexion.php';

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==================== FUNCIONES DE SESIÓN ====================

function verificar_sesion() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /nexus-digital/admin/login.php');
        exit;
    }
}

function verificar_rol($rol_requerido) {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /nexus-digital/admin/login.php');
        exit;
    }
    
    // Verificar que exista el rol en la sesión
    if (!isset($_SESSION['usuario_rol'])) {
        header('Location: /nexus-digital/admin/login.php?error=sin_rol');
        exit;
    }
    
    // Permitir acceso si es admin (admin tiene todos los permisos)
    if ($_SESSION['usuario_rol'] === 'admin') {
        return true;
    }
    
    // Si no es admin, verificar el rol específico
    if ($_SESSION['usuario_rol'] !== $rol_requerido) {
        $_SESSION['error_permiso'] = "No tienes permiso para acceder a esta página";
        header('Location: /nexus-digital/admin/dashboard.php');
        exit;
    }
    
    return true;
}

function cerrar_sesion() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    header('Location: /nexus-digital/admin/login.php');
    exit;
}

// ==================== FUNCIONES DE FORMATO ====================

function formato_moneda($cantidad) {
    return '$' . number_format(floatval($cantidad), 0, ',', '.');
}

function formato_fecha($fecha, $formato = 'd/m/Y H:i') {
    if ($fecha && $fecha !== '0000-00-00 00:00:00') {
        return date($formato, strtotime($fecha));
    }
    return 'N/A';
}

function traducir_estado_venta($estado) {
    $estados = [
        'pendiente' => 'Pendiente',
        'procesando' => 'Procesando',
        'completada' => 'Completada',
        'cancelada' => 'Cancelada'
    ];
    return $estados[$estado] ?? $estado;
}

function traducir_estado_garantia($estado) {
    $estados = [
        'abierta' => 'Abierta',
        'en_proceso' => 'En Proceso',
        'resuelta' => 'Resuelta',
        'rechazada' => 'Rechazada'
    ];
    return $estados[$estado] ?? $estado;
}

// ==================== FUNCIONES DEL DASHBOARD ====================

function obtener_resumen_dashboard($pdo) {
    $hoy = date('Y-m-d');
    $resumen = [];
    
    try {
        // Total clientes
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM CLIENTES");
        $resumen['total_clientes'] = $stmt->fetch()['total'] ?? 0;
        
        // Total productos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM PRODUCTOS");
        $resumen['total_productos'] = $stmt->fetch()['total'] ?? 0;
        
        // Ventas hoy
        $stmt = $pdo->prepare("SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as monto FROM ORDENES WHERE DATE(fecha) = ?");
        $stmt->execute([$hoy]);
        $ventas_hoy = $stmt->fetch();
        $resumen['ventas_hoy'] = $ventas_hoy['total'] ?? 0;
        $resumen['ventas_hoy_monto'] = $ventas_hoy['monto'] ?? 0;
        
        // Ventas pendientes
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM ORDENES WHERE estado = 'pendiente'");
        $resumen['ventas_pendientes'] = $stmt->fetch()['total'] ?? 0;
        
        // Stock bajo (menos de 5 unidades)
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM PRODUCTOS WHERE stock <= 5");
        $resumen['stock_bajo'] = $stmt->fetch()['total'] ?? 0;
        
        // Garantías activas
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM GARANTIAS WHERE estado IN ('abierta', 'en_proceso')");
        $resumen['garantias_activas'] = $stmt->fetch()['total'] ?? 0;
        
    } catch (PDOException $e) {
        error_log("Error en obtener_resumen_dashboard: " . $e->getMessage());
        $resumen = [
            'total_clientes' => 0,
            'total_productos' => 0,
            'ventas_hoy' => 0,
            'ventas_hoy_monto' => 0,
            'ventas_pendientes' => 0,
            'stock_bajo' => 0,
            'garantias_activas' => 0
        ];
    }
    
    return $resumen;
}

function obtener_ventas_recientes($pdo, $limite = 5) {
    try {
        $sql = "SELECT o.*, c.nombre as cliente_nombre 
                FROM ORDENES o 
                JOIN CLIENTES c ON o.id_cliente = c.id_cliente 
                ORDER BY o.fecha DESC LIMIT " . intval($limite);
        return $pdo->query($sql)->fetchAll();
    } catch (PDOException $e) {
        error_log("Error en obtener_ventas_recientes: " . $e->getMessage());
        return [];
    }
}

function obtener_productos_stock_bajo($pdo, $limite = 5) {
    try {
        $sql = "SELECT * FROM PRODUCTOS WHERE stock <= 5 ORDER BY stock ASC LIMIT " . intval($limite);
        return $pdo->query($sql)->fetchAll();
    } catch (PDOException $e) {
        error_log("Error en obtener_productos_stock_bajo: " . $e->getMessage());
        return [];
    }
}

// ==================== FUNCIONES PARA BÚSQUEDAS ====================

function buscarClientes($pdo, $termino) {
    if (empty($termino)) {
        return [];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id_cliente, ci, nombre, email, telefono, direccion 
                              FROM CLIENTES 
                              WHERE (ci LIKE ? OR nombre LIKE ?) AND activo = 1 
                              ORDER BY nombre LIMIT 10");
        $busqueda = "%$termino%";
        $stmt->execute([$busqueda, $busqueda]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en buscarClientes: " . $e->getMessage());
        return [];
    }
}

function buscarProductos($pdo, $termino) {
    if (empty($termino)) {
        return [];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id_producto, nombre, descripcion, precio, stock 
                              FROM PRODUCTOS 
                              WHERE (nombre LIKE ? OR descripcion LIKE ?) AND stock > 0 
                              ORDER BY nombre LIMIT 20");
        $busqueda = "%$termino%";
        $stmt->execute([$busqueda, $busqueda]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en buscarProductos: " . $e->getMessage());
        return [];
    }
}

function obtenerTodosProductos($pdo) {
    try {
        $stmt = $pdo->query("SELECT id_producto, nombre, descripcion, precio, stock 
                            FROM PRODUCTOS 
                            WHERE stock > 0 
                            ORDER BY nombre");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en obtenerTodosProductos: " . $e->getMessage());
        return [];
    }
}

function obtenerClientePorId($pdo, $id_cliente) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM CLIENTES WHERE id_cliente = ? AND activo = 1");
        $stmt->execute([$id_cliente]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en obtenerClientePorId: " . $e->getMessage());
        return null;
    }
}

function obtenerProductoPorId($pdo, $id_producto) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM PRODUCTOS WHERE id_producto = ?");
        $stmt->execute([$id_producto]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en obtenerProductoPorId: " . $e->getMessage());
        return null;
    }
}

// ==================== FUNCIONES AJAX ====================

function jsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function manejarAjax() {
    global $pdo;
    
    // Verificar que sea una petición AJAX
    if (!isset($_GET['action'])) {
        jsonResponse(['error' => 'Acción no especificada'], 400);
        return;
    }
    
    $action = $_GET['action'];
    
    switch ($action) {
        case 'buscar_clientes':
            $termino = isset($_GET['q']) ? $_GET['q'] : '';
            $resultados = buscarClientes($pdo, $termino);
            jsonResponse($resultados);
            break;
            
        case 'buscar_productos':
            $termino = isset($_GET['q']) ? $_GET['q'] : '';
            $resultados = buscarProductos($pdo, $termino);
            jsonResponse($resultados);
            break;
            
        case 'todos_productos':
            $resultados = obtenerTodosProductos($pdo);
            jsonResponse($resultados);
            break;
            
        case 'cliente':
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $resultado = obtenerClientePorId($pdo, $id);
            jsonResponse($resultado);
            break;
            
        case 'producto':
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $resultado = obtenerProductoPorId($pdo, $id);
            jsonResponse($resultado);
            break;
            
        default:
            jsonResponse(['error' => 'Acción no válida'], 400);
            break;
    }
}
?>
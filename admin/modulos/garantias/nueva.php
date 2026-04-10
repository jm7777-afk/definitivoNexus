<?php
require_once '../../../includes/funciones.php';
verificar_sesion();

// Obtener órdenes para el select
$ordenes = $pdo->query("
    SELECT o.id_orden, c.nombre as cliente_nombre, o.fecha 
    FROM ORDENES o
    JOIN CLIENTES c ON o.id_cliente = c.id_cliente
    WHERE o.estado = 'completada'
    ORDER BY o.fecha DESC
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_orden = $_POST['id_orden'];
    $id_producto = $_POST['id_producto'];
    $numero_serie = $_POST['numero_serie'];
    $estado = 'abierta';
    
    try {
        $sql = "INSERT INTO GARANTIAS (id_orden, id_producto, numero_serie, estado) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_orden, $id_producto, $numero_serie, $estado]);
        
        header('Location: index.php?mensaje=creado');
        exit;
    } catch (PDOException $e) {
        $error = "Error al registrar garantía: " . $e->getMessage();
    }
}

// Obtener productos de la orden seleccionada (via AJAX)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Garantía - Nexus Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f6fa;
            padding: 30px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 24px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-volver {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #666;
            font-weight: 500;
            font-size: 14px;
        }
        .form-group label i { color: #5B4B9E; margin-right: 5px; }
        .form-group select, .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }
        .form-group select:focus, .form-group input:focus {
            border-color: #5B4B9E;
            outline: none;
        }
        .btn-guardar {
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-shield-alt"></i> Nueva Garantía</h1>
            <a href="index.php" class="btn-volver">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="formGarantia">
            <div class="form-group">
                <label><i class="fas fa-shopping-cart"></i> Número de Venta</label>
                <select name="id_orden" id="id_orden" required>
                    <option value="">-- Seleccione una venta --</option>
                    <?php foreach($ordenes as $orden): ?>
                        <option value="<?php echo $orden['id_orden']; ?>">
                            #<?php echo $orden['id_orden']; ?> - <?php echo htmlspecialchars($orden['cliente_nombre']); ?> - <?php echo date('d/m/Y', strtotime($orden['fecha'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-box"></i> Producto</label>
                <select name="id_producto" id="id_producto" required>
                    <option value="">-- Primero seleccione una venta --</option>
                </select>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-barcode"></i> Número de Serie</label>
                <input type="text" name="numero_serie" placeholder="Ingrese el número de serie del producto">
            </div>
            
            <button type="submit" class="btn-guardar">
                <i class="fas fa-save"></i> Registrar Garantía
            </button>
        </form>
    </div>
    
    <script>
        document.getElementById('id_orden').addEventListener('change', function() {
            const id_orden = this.value;
            const selectProducto = document.getElementById('id_producto');
            
            if (id_orden) {
                // Aquí deberías hacer una petición AJAX para obtener los productos de la orden
                // Por ahora, simulamos algunos productos
                selectProducto.innerHTML = '<option value="">Cargando productos...</option>';
                
                // Simulación - en producción usa fetch a un archivo PHP
                setTimeout(() => {
                    selectProducto.innerHTML = '<option value="1">Producto de ejemplo 1</option><option value="2">Producto de ejemplo 2</option>';
                }, 500);
            } else {
                selectProducto.innerHTML = '<option value="">-- Primero seleccione una venta --</option>';
            }
        });
    </script>
</body>
</html>
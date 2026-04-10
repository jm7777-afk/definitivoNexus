<?php
require_once '../../../includes/funciones.php';
verificar_sesion();

$error = '';
$success = '';

// Obtener productos existentes
$productos = $pdo->query("SELECT id_producto, nombre, stock, precio FROM PRODUCTOS ORDER BY nombre")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productos_compra = $_POST['productos'] ?? [];
    $cantidades = $_POST['cantidades'] ?? [];
    $precios_compra = $_POST['precios_compra'] ?? [];
    $proveedor = trim($_POST['proveedor'] ?? '');
    $factura_numero = trim($_POST['factura_numero'] ?? '');
    $fecha_compra = $_POST['fecha_compra'] ?? date('Y-m-d');
    
    if (empty($productos_compra) || empty($cantidades) || empty($precios_compra)) {
        $error = "Debe agregar al menos un producto";
    } elseif (empty($proveedor)) {
        $error = "El nombre del proveedor es obligatorio";
    } else {
        try {
            $pdo->beginTransaction();
            
            // Calcular total
            $total = 0;
            foreach ($productos_compra as $index => $id_producto) {
                $cantidad = intval($cantidades[$index]);
                $precio_compra = floatval($precios_compra[$index]);
                $total += $precio_compra * $cantidad;
            }
            
            // Verificar si existe la tabla compras, si no, solo actualizar stock
            try {
                // Intentar insertar en compras
                $stmt = $pdo->prepare("INSERT INTO compras (proveedor, factura_numero, fecha_compra, usuario_id, total) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$proveedor, $factura_numero, $fecha_compra, $_SESSION['usuario_id'], $total]);
                $id_compra = $pdo->lastInsertId();
                
                // Insertar detalles
                foreach ($productos_compra as $index => $id_producto) {
                    $cantidad = intval($cantidades[$index]);
                    $precio_compra = floatval($precios_compra[$index]);
                    
                    $stmt = $pdo->prepare("INSERT INTO detalle_compra (id_compra, id_producto, cantidad, precio_compra) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$id_compra, $id_producto, $cantidad, $precio_compra]);
                    
                    // Actualizar stock
                    $stmt = $pdo->prepare("UPDATE PRODUCTOS SET stock = stock + ? WHERE id_producto = ?");
                    $stmt->execute([$cantidad, $id_producto]);
                }
            } catch (PDOException $e) {
                // Si no existe la tabla compras, solo actualizar stock
                foreach ($productos_compra as $index => $id_producto) {
                    $cantidad = intval($cantidades[$index]);
                    $stmt = $pdo->prepare("UPDATE PRODUCTOS SET stock = stock + ? WHERE id_producto = ?");
                    $stmt->execute([$cantidad, $id_producto]);
                }
            }
            
            $pdo->commit();
            header('Location: index.php?mensaje=compra_exitosa');
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error al procesar la compra: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Compra - Nexus Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 25px 30px;
        }
        
        .card-header h1 {
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #666;
            font-weight: 500;
            font-size: 14px;
        }
        
        input, select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }
        
        input:focus, select:focus {
            border-color: #28a745;
            outline: none;
        }
        
        .producto-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 50px;
            gap: 15px;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .buscador-container {
            position: relative;
        }
        
        .buscador-producto {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .resultados-busqueda {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            z-index: 1000;
        }
        
        .resultados-busqueda.active {
            display: block;
        }
        
        .resultado-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        
        .resultado-item:hover {
            background: #f0f0f0;
        }
        
        .btn-eliminar {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .btn-agregar {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .btn-guardar {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }
        
        .btn-cancelar {
            background: #6c757d;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            width: 100%;
            margin-top: 10px;
        }
        
        .total {
            text-align: right;
            font-size: 20px;
            font-weight: 700;
            color: #28a745;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        @media (max-width: 768px) {
            .producto-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>
                    <i class="fas fa-shopping-cart"></i>
                    Registrar Compra
                </h1>
                <p>Registre la entrada de productos al inventario</p>
            </div>
            
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Proveedor</label>
                            <input type="text" name="proveedor" required placeholder="Nombre del proveedor">
                        </div>
                        <div class="form-group">
                            <label>Número de Factura</label>
                            <input type="text" name="factura_numero" placeholder="Factura #">
                        </div>
                        <div class="form-group">
                            <label>Fecha de Compra</label>
                            <input type="date" name="fecha_compra" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    
                    <h3 style="margin-bottom: 15px;">Productos</h3>
                    <div id="productos-container">
                        <div class="producto-row">
                            <div class="buscador-container">
                                <input type="text" class="buscador-producto" placeholder="Buscar producto...">
                                <input type="hidden" name="productos[]" class="producto-id">
                                <div class="resultados-busqueda"></div>
                            </div>
                            <input type="number" name="cantidades[]" placeholder="Cantidad" min="1" required>
                            <input type="number" name="precios_compra[]" placeholder="Precio compra" step="0.01" min="0" required>
                            <button type="button" class="btn-eliminar" onclick="eliminarProducto(this)">🗑️</button>
                        </div>
                    </div>
                    
                    <button type="button" class="btn-agregar" onclick="agregarProducto()">
                        <i class="fas fa-plus"></i> Agregar Producto
                    </button>
                    
                    <div class="total" id="totalDisplay">
                        Total Compra: $0.00
                    </div>
                    
                    <button type="submit" class="btn-guardar">
                        <i class="fas fa-check-circle"></i> Registrar Compra
                    </button>
                    
                    <a href="index.php" class="btn-cancelar">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    const productosData = <?php echo json_encode($productos); ?>;
    
    function buscarProducto(input) {
        const container = input.closest('.buscador-container');
        const resultadosDiv = container.querySelector('.resultados-busqueda');
        const searchTerm = input.value.toLowerCase().trim();
        
        if (searchTerm === '') {
            resultadosDiv.classList.remove('active');
            return;
        }
        
        const filtrados = productosData.filter(p => p.nombre.toLowerCase().includes(searchTerm));
        
        if (filtrados.length > 0) {
            resultadosDiv.innerHTML = filtrados.map(p => `
                <div class="resultado-item" onclick="seleccionarProducto(this, '${p.id_producto}', '${p.nombre}', ${p.stock})">
                    <strong>${p.nombre}</strong><br>
                    <small>Stock actual: ${p.stock} | Precio venta: $${p.precio}</small>
                </div>
            `).join('');
            resultadosDiv.classList.add('active');
        } else {
            resultadosDiv.innerHTML = '<div class="resultado-item">No encontrado</div>';
            resultadosDiv.classList.add('active');
        }
    }
    
    function seleccionarProducto(element, id, nombre, stock) {
        const container = element.closest('.buscador-container');
        container.querySelector('.buscador-producto').value = nombre;
        container.querySelector('.producto-id').value = id;
        container.querySelector('.resultados-busqueda').classList.remove('active');
    }
    
    function agregarProducto() {
        const container = document.getElementById('productos-container');
        const nuevaFila = document.createElement('div');
        nuevaFila.className = 'producto-row';
        nuevaFila.innerHTML = `
            <div class="buscador-container">
                <input type="text" class="buscador-producto" placeholder="Buscar producto...">
                <input type="hidden" name="productos[]" class="producto-id">
                <div class="resultados-busqueda"></div>
            </div>
            <input type="number" name="cantidades[]" placeholder="Cantidad" min="1" required>
            <input type="number" name="precios_compra[]" placeholder="Precio compra" step="0.01" min="0" required>
            <button type="button" class="btn-eliminar" onclick="eliminarProducto(this)">🗑️</button>
        `;
        container.appendChild(nuevaFila);
        
        const nuevoInput = nuevaFila.querySelector('.buscador-producto');
        nuevoInput.addEventListener('input', function() { buscarProducto(this); });
        
        const cantidad = nuevaFila.querySelector('input[name="cantidades[]"]');
        const precio = nuevaFila.querySelector('input[name="precios_compra[]"]');
        cantidad.addEventListener('input', calcularTotal);
        precio.addEventListener('input', calcularTotal);
    }
    
    function eliminarProducto(btn) {
        const container = document.getElementById('productos-container');
        if (container.children.length > 1) {
            btn.closest('.producto-row').remove();
            calcularTotal();
        }
    }
    
    function calcularTotal() {
        let total = 0;
        document.querySelectorAll('.producto-row').forEach(row => {
            const cantidad = row.querySelector('input[name="cantidades[]"]').value;
            const precio = row.querySelector('input[name="precios_compra[]"]').value;
            if (cantidad && precio) total += parseFloat(precio) * parseInt(cantidad);
        });
        document.getElementById('totalDisplay').innerHTML = `Total Compra: $${total.toFixed(2)}`;
    }
    
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.buscador-container')) {
            document.querySelectorAll('.resultados-busqueda').forEach(d => d.classList.remove('active'));
        }
    });
    
    document.querySelectorAll('.buscador-producto').forEach(input => {
        input.addEventListener('input', function() { buscarProducto(this); });
    });
    </script>
</body>
</html>
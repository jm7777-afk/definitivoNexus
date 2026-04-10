<?php
require_once '../../../includes/funciones.php';
verificar_sesion();

// Obtener clientes con TODOS los datos necesarios
$clientes = $pdo->query("SELECT id_cliente, nombre, ci, telefono, email, direccion FROM CLIENTES ORDER BY nombre")->fetchAll();

// Obtener productos para la venta
$productos = $pdo->query("SELECT * FROM PRODUCTOS WHERE stock > 0 ORDER BY nombre")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_cliente = $_POST['id_cliente']; // Cambiado de cliente_id a id_cliente
    $productos_venta = $_POST['productos'];
    $cantidades = $_POST['cantidades'];
    $metodo_pago = $_POST['metodo_pago'];
    
    try {
        $pdo->beginTransaction();
        
        // Calcular total
        $total = 0;
        foreach ($productos_venta as $index => $id_producto) {
            $cantidad = $cantidades[$index];
            $stmt = $pdo->prepare("SELECT precio FROM PRODUCTOS WHERE id_producto = ?");
            $stmt->execute([$id_producto]);
            $producto = $stmt->fetch();
            $total += $producto['precio'] * $cantidad;
        }
        
        // Crear orden
        $stmt = $pdo->prepare("INSERT INTO ORDENES (id_cliente, total, metodo_pago, usuario_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_cliente, $total, $metodo_pago, $_SESSION['usuario_id']]);
        $id_orden = $pdo->lastInsertId();
        
        // Crear detalles y actualizar stock
        foreach ($productos_venta as $index => $id_producto) {
            $cantidad = $cantidades[$index];
            $stmt = $pdo->prepare("SELECT precio FROM PRODUCTOS WHERE id_producto = ?");
            $stmt->execute([$id_producto]);
            $producto = $stmt->fetch();
            
            // Insertar detalle
            $stmt = $pdo->prepare("INSERT INTO DETALLE_ORDEN (id_orden, id_producto, cantidad, precio_unitario) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->execute([$id_orden, $id_producto, $cantidad, $producto['precio']]);
            
            // Actualizar stock
            $stmt = $pdo->prepare("UPDATE PRODUCTOS SET stock = stock - ? WHERE id_producto = ?");
            $stmt->execute([$cantidad, $id_producto]);
        }
        
        $pdo->commit();
        header('Location: ../ventas/ver.php/?mensaje=venta_exitosa&id=' . $id_orden);
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error al procesar la venta: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Venta - Nexus Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="/nexus-digital/favicon.ico">
    <style>
        /* Tus estilos aquí (mantener los que ya tienes) */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f6fa;
            padding: 30px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 28px;
            color: #333;
        }
        
        .btn-volver {
            background: #6c757d;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .form-section h2 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-procesar {
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }
        
        .total {
            text-align: right;
            font-size: 20px;
            font-weight: 700;
            color: #5B4B9E;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        /* Estilos para buscadores */
        .buscador-container {
            position: relative;
            flex: 2;
            min-width: 200px;
        }

        .buscador-producto, .buscador-cliente {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .buscador-producto:focus, .buscador-cliente:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.2);
        }

        .resultados-busqueda {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .resultados-busqueda.active {
            display: block;
        }

        .resultado-item, .resultado-cliente {
            padding: 8px 12px;
            cursor: pointer;
            transition: background 0.2s;
            border-bottom: 1px solid #f0f0f0;
        }

        .resultado-item:hover, .resultado-cliente:hover {
            background: #f5f5f5;
        }

        .resultado-item strong, .resultado-cliente strong {
            color: #333;
            display: block;
        }

        .producto-row {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .producto-row input[type="number"] {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-width: 100px;
        }

        .btn-eliminar {
            background: #f44336;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-agregar {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        .info-cliente {
            margin-top: 15px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .cliente-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
        }

        .cliente-card h3 {
            margin: 0 0 15px 0;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cliente-card p {
            margin: 8px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @media (max-width: 768px) {
            .producto-row {
                flex-direction: column;
            }
            
            .buscador-container {
                width: 100%;
            }
            
            .producto-row input[type="number"] {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-shopping-cart"></i> Nueva Venta</h1>
            <a href="../ventas/" class="btn-volver">
                <i class="fas fa-arrow-left"></i> Cancelar
            </a>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="formVenta">
            <div class="form-section">
                <h2><i class="fas fa-user"></i> Datos del Cliente</h2>
                
                <div class="buscador-cliente-container" style="position: relative;">
                    <label for="buscador-cliente">Buscar Cliente:</label>
                    <input type="text" 
                           id="buscador-cliente" 
                           class="buscador-cliente" 
                           placeholder="Escriba CI o nombre del cliente..." 
                           autocomplete="off">
                    <input type="hidden" 
                           name="id_cliente" 
                           id="cliente-id" 
                           required>
                    <div id="resultados-clientes" class="resultados-busqueda"></div>
                </div>
                
                <div id="info-cliente" class="info-cliente" style="display: none;">
                    <div class="cliente-card">
                        <h3><i class="fas fa-user-circle"></i> <span id="cliente-nombre"></span></h3>
                        <p><i class="fas fa-id-card"></i> CI: <span id="cliente-ci"></span></p>
                        <p><i class="fas fa-phone"></i> Teléfono: <span id="cliente-telefono">No registrado</span></p>
                        <p><i class="fas fa-envelope"></i> Email: <span id="cliente-email">No registrado</span></p>
                        <p><i class="fas fa-map-marker-alt"></i> Dirección: <span id="cliente-direccion">No registrada</span></p>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2><i class="fas fa-box"></i> Productos</h2>
                <div id="productos-container">
                    <div class="producto-row">
                        <div class="buscador-container">
                            <input type="text" 
                                   class="buscador-producto" 
                                   placeholder="Buscar producto..." 
                                   autocomplete="off">
                            <input type="hidden" 
                                   name="productos[]" 
                                   class="producto-id" 
                                   required>
                            <div class="resultados-busqueda"></div>
                        </div>
                        <input type="number" 
                               name="cantidades[]" 
                               placeholder="Cantidad" 
                               min="1" 
                               required>
                        <button type="button" class="btn-eliminar" onclick="eliminarProducto(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                
                <button type="button" class="btn-agregar" onclick="agregarProducto()">
                    <i class="fas fa-plus"></i> Agregar Producto
                </button>
            </div>

            <div class="form-section">
                <h2><i class="fas fa-credit-card"></i> Método de Pago</h2>
                <div class="form-group">
                    <select name="metodo_pago" required>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta_credito">Tarjeta de Crédito</option>
                        <option value="tarjeta_debito">Tarjeta de Débito</option>
                        <option value="transferencia">Transferencia Bancaria</option>
                        <option value="mercadopago">Mercado Pago</option>
                    </select>
                </div>
                
                <div class="total" id="totalDisplay">
                    Total: $0.00
                </div>
            </div>
            
            <button type="submit" class="btn-procesar">
                <i class="fas fa-check-circle"></i> Procesar Venta
            </button>
        </form>
    </div>
    
    <script>
    // Datos desde PHP
    const clientesData = <?php echo json_encode($clientes); ?>;
    const productosData = <?php echo json_encode($productos); ?>;

    // ========== FUNCIONES PARA CLIENTES ==========
    function buscarCliente(input) {
        const resultadosDiv = document.getElementById('resultados-clientes');
        const searchTerm = input.value.toLowerCase().trim();
        
        if (searchTerm === '') {
            resultadosDiv.classList.remove('active');
            resultadosDiv.innerHTML = '';
            return;
        }
        
        const clientesFiltrados = clientesData.filter(cliente => 
            (cliente.ci && cliente.ci.toString().toLowerCase().includes(searchTerm)) || 
            cliente.nombre.toLowerCase().includes(searchTerm)
        );
        
        if (clientesFiltrados.length > 0) {
            resultadosDiv.innerHTML = clientesFiltrados.map(cliente => `
                <div class="resultado-cliente" onclick="seleccionarCliente(${JSON.stringify(cliente).replace(/"/g, '&quot;')})">
                    <strong>${escapeHtml(cliente.nombre)}</strong>
                    <div>
                        <span class="cliente-ci">CI: ${cliente.ci || 'No registrado'}</span>
                        ${cliente.telefono ? `<span class="cliente-telefono">📱 ${cliente.telefono}</span>` : ''}
                    </div>
                    ${cliente.email ? `<small>✉️ ${escapeHtml(cliente.email)}</small>` : ''}
                </div>
            `).join('');
            resultadosDiv.classList.add('active');
        } else {
            resultadosDiv.innerHTML = '<div class="resultado-cliente">No se encontraron clientes</div>';
            resultadosDiv.classList.add('active');
        }
    }

    function seleccionarCliente(cliente) {
        const input = document.getElementById('buscador-cliente');
        const hiddenInput = document.getElementById('cliente-id');
        const infoCliente = document.getElementById('info-cliente');
        
        input.value = `${cliente.nombre} (CI: ${cliente.ci || 'N/A'})`;
        hiddenInput.value = cliente.id_cliente;
        
        document.getElementById('cliente-nombre').innerHTML = escapeHtml(cliente.nombre);
        document.getElementById('cliente-ci').textContent = cliente.ci || 'No registrado';
        document.getElementById('cliente-telefono').textContent = cliente.telefono || 'No registrado';
        document.getElementById('cliente-email').textContent = cliente.email || 'No registrado';
        document.getElementById('cliente-direccion').textContent = cliente.direccion || 'No registrada';
        
        infoCliente.style.display = 'block';
        
        const resultadosDiv = document.getElementById('resultados-clientes');
        resultadosDiv.classList.remove('active');
    }

    // ========== FUNCIONES PARA PRODUCTOS ==========
    function buscarProducto(input) {
        const container = input.closest('.buscador-container');
        const resultadosDiv = container.querySelector('.resultados-busqueda');
        const searchTerm = input.value.toLowerCase().trim();
        
        if (searchTerm === '') {
            resultadosDiv.classList.remove('active');
            resultadosDiv.innerHTML = '';
            return;
        }
        
        const productosFiltrados = productosData.filter(producto => 
            producto.nombre.toLowerCase().includes(searchTerm)
        );
        
        if (productosFiltrados.length > 0) {
            resultadosDiv.innerHTML = productosFiltrados.map(producto => `
                <div class="resultado-item" onclick="seleccionarProducto(this, '${producto.id_producto}', '${escapeHtml(producto.nombre)}', ${producto.stock}, ${producto.precio})">
                    <strong>${escapeHtml(producto.nombre)}</strong>
                    <small>
                        <span class="stock">Stock: ${producto.stock}</span> | 
                        <span class="precio">$${parseFloat(producto.precio).toFixed(2)}</span>
                    </small>
                </div>
            `).join('');
            resultadosDiv.classList.add('active');
        } else {
            resultadosDiv.innerHTML = '<div class="resultado-item">No se encontraron productos</div>';
            resultadosDiv.classList.add('active');
        }
    }

    function seleccionarProducto(element, id, nombre, stock, precio) {
        const container = element.closest('.buscador-container');
        const input = container.querySelector('.buscador-producto');
        const hiddenInput = container.querySelector('.producto-id');
        
        input.value = `${nombre} (Stock: ${stock} - $${parseFloat(precio).toFixed(2)})`;
        hiddenInput.value = id;
        
        const resultadosDiv = container.querySelector('.resultados-busqueda');
        resultadosDiv.classList.remove('active');
        
        const cantidadInput = container.closest('.producto-row').querySelector('input[type="number"]');
        if (cantidadInput.value && parseInt(cantidadInput.value) > stock) {
            alert(`Stock insuficiente. Stock disponible: ${stock}`);
            cantidadInput.value = '';
        }
        
        calcularTotal();
    }

    function agregarProducto() {
        const container = document.getElementById('productos-container');
        const nuevaFila = document.createElement('div');
        nuevaFila.className = 'producto-row';
        nuevaFila.innerHTML = `
            <div class="buscador-container">
                <input type="text" 
                       class="buscador-producto" 
                       placeholder="Buscar producto..." 
                       autocomplete="off">
                <input type="hidden" 
                       name="productos[]" 
                       class="producto-id">
                <div class="resultados-busqueda"></div>
            </div>
            <input type="number" 
                   name="cantidades[]" 
                   placeholder="Cantidad" 
                   min="1" 
                   required>
            <button type="button" class="btn-eliminar" onclick="eliminarProducto(this)">
                <i class="fas fa-trash"></i>
            </button>
        `;
        container.appendChild(nuevaFila);
        
        // Agregar event listeners al nuevo producto
        const nuevoInput = nuevaFila.querySelector('.buscador-producto');
        nuevoInput.addEventListener('input', function() {
            buscarProducto(this);
        });
        
        const nuevaCantidad = nuevaFila.querySelector('input[type="number"]');
        nuevaCantidad.addEventListener('change', function() {
            validarStock(this);
            calcularTotal();
        });
    }

    function eliminarProducto(button) {
        const row = button.closest('.producto-row');
        const container = document.getElementById('productos-container');
        
        if (container.children.length > 1) {
            row.remove();
            calcularTotal();
        } else {
            alert('Debe haber al menos un producto');
        }
    }

    function validarStock(input) {
        const row = input.closest('.producto-row');
        const hiddenInput = row.querySelector('.producto-id');
        const productoId = hiddenInput.value;
        
        if (productoId) {
            const producto = productosData.find(p => p.id_producto == productoId);
            if (producto && parseInt(input.value) > producto.stock) {
                alert(`Stock insuficiente. Stock disponible: ${producto.stock}`);
                input.value = producto.stock;
            }
        }
    }

    function calcularTotal() {
        let total = 0;
        document.querySelectorAll('.producto-row').forEach(row => {
            const hiddenInput = row.querySelector('.producto-id');
            const cantidad = row.querySelector('input[type="number"]').value;
            
            if (hiddenInput.value && cantidad) {
                const producto = productosData.find(p => p.id_producto == hiddenInput.value);
                if (producto) {
                    total += parseFloat(producto.precio) * parseInt(cantidad);
                }
            }
        });
        document.getElementById('totalDisplay').innerHTML = `Total: $${total.toFixed(2)}`;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ========== EVENT LISTENERS ==========
    document.addEventListener('DOMContentLoaded', function() {
        // Buscador de clientes
        const buscadorCliente = document.getElementById('buscador-cliente');
        if (buscadorCliente) {
            buscadorCliente.addEventListener('input', function() {
                buscarCliente(this);
            });
        }
        
        // Buscadores de productos iniciales
        document.querySelectorAll('.buscador-producto').forEach(input => {
            input.addEventListener('input', function() {
                buscarProducto(this);
            });
        });
        
        // Cantidades iniciales
        document.querySelectorAll('.producto-row input[type="number"]').forEach(input => {
            input.addEventListener('change', function() {
                validarStock(this);
                calcularTotal();
            });
        });
    });

    // Cerrar resultados al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.buscador-cliente-container')) {
            const resultadosDiv = document.getElementById('resultados-clientes');
            if (resultadosDiv) resultadosDiv.classList.remove('active');
        }
        
        if (!e.target.closest('.buscador-container')) {
            document.querySelectorAll('.resultados-busqueda').forEach(div => {
                div.classList.remove('active');
            });
        }
    });

    // Validar formulario antes de enviar
    document.getElementById('formVenta').addEventListener('submit', function(e) {
        const clienteId = document.getElementById('cliente-id').value;
        if (!clienteId) {
            alert('Por favor seleccione un cliente');
            e.preventDefault();
            return;
        }
        
        let valido = true;
        document.querySelectorAll('.producto-row').forEach(row => {
            const productoId = row.querySelector('.producto-id').value;
            const cantidad = row.querySelector('input[type="number"]').value;
            
            if (!productoId) {
                alert('Por favor seleccione un producto en todas las filas');
                valido = false;
                e.preventDefault();
                return;
            }
            
            if (!cantidad || cantidad < 1) {
                alert('Por favor ingrese una cantidad válida');
                valido = false;
                e.preventDefault();
                return;
            }
        });
        
        if (!valido) {
            e.preventDefault();
        }
    });
    </script>
</body>
</html>
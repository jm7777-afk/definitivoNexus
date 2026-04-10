<?php
require_once '../../../includes/funciones.php';
verificar_sesion();

// Obtener todas las ventas con más datos del cliente
$ventas = $pdo->query("
    SELECT o.*, c.nombre AS cliente_nombre, c.ci AS cliente_ci
    FROM ordenes o
    JOIN clientes c ON o.id_cliente = c.id_cliente
    ORDER BY o.fecha DESC
")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas - Nexus Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="/nexus-digital/favicon.ico">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f6fa;
            padding: 30px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .header h1 {
            font-size: 28px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-nueva {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.2s;
        }
        .btn-nueva:hover {
            transform: translateY(-2px);
        }
        .btn-volver {
            background: #6c757d;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.2s;
        }
        .btn-volver:hover {
            transform: translateY(-2px);
        }
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            overflow-x: auto;
        }
        .filtros {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
            flex-wrap: wrap;
        }
        .filtro-select, .filtro-input {
            padding: 10px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            flex: 1;
            min-width: 150px;
        }
        .filtro-select:focus, .filtro-input:focus {
            border-color: #5B4B9E;
            outline: none;
        }
        .btn-buscar {
            background: #5B4B9E;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
        }
        .btn-buscar:hover {
            background: #4a3d82;
        }
        .btn-limpiar {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
        }
        .btn-limpiar:hover {
            background: #5a6268;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        th {
            text-align: left;
            padding: 15px;
            background: #f8f9fa;
            color: #666;
            font-weight: 600;
        }
        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #333;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        .badge-pendiente { background: #fff3cd; color: #856404; }
        .badge-procesando { background: #cce5ff; color: #004085; }
        .badge-completada { background: #d4edda; color: #155724; }
        .badge-cancelada { background: #f8d7da; color: #721c24; }
        .btn-ver {
            background: #17a2b8;
            color: white;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background 0.3s;
        }
        .btn-ver:hover {
            background: #138496;
        }
        .total-ventas {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            text-align: right;
            font-size: 18px;
            font-weight: 700;
            color: #5B4B9E;
        }
        .sin-resultados {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        .cliente-info {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .cliente-nombre {
            font-weight: 600;
            color: #333;
        }
        .cliente-ci {
            font-size: 11px;
            color: #666;
        }
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            .filtros {
                flex-direction: column;
            }
            .filtro-select, .filtro-input, .btn-buscar, .btn-limpiar {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div class="header">
            <h1><i class="fas fa-shopping-cart" style="color: #28a745;"></i> Gestión de Ventas</h1>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="nueva.php" class="btn-nueva">
                    <i class="fas fa-plus"></i> Nueva Venta
                </a>
                <a href="../../index.php" class="btn-volver">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        
        <div class="table-container">
            <div class="filtros">
                <select class="filtro-select" id="filtroEstado">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="procesando">Procesando</option>
                    <option value="completada">Completada</option>
                    <option value="cancelada">Cancelada</option>
                </select>
                
                <input type="text" class="filtro-input" id="filtroCliente" placeholder="Buscar por nombre del cliente...">
                
                <input type="text" class="filtro-input" id="filtroCI" placeholder="Buscar por CI del cliente...">
                
                <input type="date" class="filtro-input" id="filtroFecha">
                
                <input type="text" class="filtro-input" id="filtroTotal" placeholder="Buscar por total (ej: 100.00)...">
                
                <button class="btn-buscar" onclick="aplicarFiltros()">
                    <i class="fas fa-search"></i> Buscar
                </button>
                
                <button class="btn-limpiar" onclick="limpiarFiltros()">
                    <i class="fas fa-eraser"></i> Limpiar
                </button>
            </div>
            
            <div style="margin-bottom: 15px; padding: 10px; background: #e7f3ff; border-radius: 8px;">
                <small><i class="fas fa-info-circle"></i> Puedes buscar por: Nombre, CI, Total o usar múltiples filtros combinados</small>
            </div>
            
            <table id="tablaVentas">
                <thead>
                    <tr>
                        <th>N° Venta</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>CI</th>
                        <th>Total</th>
                        <th>Método Pago</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                    <?php foreach($ventas as $venta): ?>
                    <tr data-estado="<?php echo strtolower($venta['estado']); ?>"
                        data-cliente="<?php echo strtolower(htmlspecialchars($venta['cliente_nombre'])); ?>"
                        data-ci="<?php echo $venta['cliente_ci']; ?>"
                        data-fecha="<?php echo date('Y-m-d', strtotime($venta['fecha'])); ?>"
                        data-total="<?php echo $venta['total']; ?>">
                        <td>#<?php echo $venta['id_orden']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></td>
                        <td>
                            <div class="cliente-info">
                                <span class="cliente-nombre"><?php echo htmlspecialchars($venta['cliente_nombre']); ?></span>
                            </div>
                        </td>
                        <td><?php echo $venta['cliente_ci'] ?: 'N/A'; ?></td>
                        <td><?php echo formato_moneda($venta['total']); ?></td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $venta['metodo_pago'] ?? 'N/A')); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $venta['estado']; ?>">
                                <?php echo traducir_estado_venta($venta['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="ver.php?id=<?php echo $venta['id_orden']; ?>" class="btn-ver">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php 
            $total_ventas = array_sum(array_column($ventas, 'total'));
            $total_filtrado = 0;
            ?>
            <div class="total-ventas" id="totalVentas">
                Total en ventas: <?php echo formato_moneda($total_ventas); ?>
            </div>
        </div>
    </div>
    
    <script>
        function aplicarFiltros() {
            const filtroEstado = document.getElementById('filtroEstado').value.toLowerCase();
            const filtroCliente = document.getElementById('filtroCliente').value.toLowerCase().trim();
            const filtroCI = document.getElementById('filtroCI').value.toLowerCase().trim();
            const filtroFecha = document.getElementById('filtroFecha').value;
            const filtroTotal = document.getElementById('filtroTotal').value.toLowerCase().trim();
            
            const filas = document.querySelectorAll('#tablaBody tr');
            let totalFiltrado = 0;
            let filasVisibles = 0;
            
            filas.forEach(fila => {
                let mostrar = true;
                
                // Filtrar por estado
                if (filtroEstado) {
                    const estado = fila.getAttribute('data-estado');
                    if (!estado || estado !== filtroEstado) mostrar = false;
                }
                
                // Filtrar por nombre del cliente
                if (mostrar && filtroCliente) {
                    const nombreCliente = fila.getAttribute('data-cliente');
                    if (!nombreCliente || !nombreCliente.includes(filtroCliente)) mostrar = false;
                }
                
                // Filtrar por CI del cliente
                if (mostrar && filtroCI) {
                    const ciCliente = fila.getAttribute('data-ci');
                    if (!ciCliente || !ciCliente.toString().toLowerCase().includes(filtroCI)) mostrar = false;
                }
                
                // Filtrar por fecha
                if (mostrar && filtroFecha) {
                    const fechaFila = fila.getAttribute('data-fecha');
                    if (!fechaFila || fechaFila !== filtroFecha) mostrar = false;
                }
                
                // Filtrar por total
                if (mostrar && filtroTotal) {
                    const total = fila.getAttribute('data-total');
                    if (!total || !total.toString().includes(filtroTotal)) mostrar = false;
                }
                
                if (mostrar) {
                    fila.style.display = '';
                    const totalCelda = fila.cells[4].textContent;
                    const totalNumerico = parseFloat(totalCelda.replace(/[^0-9.-]/g, ''));
                    if (!isNaN(totalNumerico)) {
                        totalFiltrado += totalNumerico;
                    }
                    filasVisibles++;
                } else {
                    fila.style.display = 'none';
                }
            });
            
            // Actualizar el total mostrado
            const totalDiv = document.getElementById('totalVentas');
            if (filasVisibles > 0) {
                totalDiv.innerHTML = `Total en ventas (filtrado): ${formatoMoneda(totalFiltrado)} | Total general: ${formatoMoneda(<?php echo $total_ventas; ?>)}`;
            } else {
                totalDiv.innerHTML = `No hay resultados | Total general: ${formatoMoneda(<?php echo $total_ventas; ?>)}`;
                mostrarMensajeSinResultados();
            }
        }
        
        function limpiarFiltros() {
            document.getElementById('filtroEstado').value = '';
            document.getElementById('filtroCliente').value = '';
            document.getElementById('filtroCI').value = '';
            document.getElementById('filtroFecha').value = '';
            document.getElementById('filtroTotal').value = '';
            
            const filas = document.querySelectorAll('#tablaBody tr');
            filas.forEach(fila => {
                fila.style.display = '';
            });
            
            const totalDiv = document.getElementById('totalVentas');
            totalDiv.innerHTML = `Total en ventas: ${formatoMoneda(<?php echo $total_ventas; ?>)}`;
        }
        
        function formatoMoneda(valor) {
            return new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(valor);
        }
        
        function mostrarMensajeSinResultados() {
            const tablaBody = document.getElementById('tablaBody');
            const filasVisibles = Array.from(tablaBody.querySelectorAll('tr')).filter(fila => fila.style.display !== 'none').length;
            
            if (filasVisibles === 0 && !document.getElementById('mensaje-no-resultados')) {
                const mensaje = document.createElement('tr');
                mensaje.id = 'mensaje-no-resultados';
                mensaje.innerHTML = `
                    <td colspan="8" style="text-align: center; padding: 40px;">
                        <i class="fas fa-search" style="font-size: 48px; color: #ccc; margin-bottom: 10px; display: block;"></i>
                        No se encontraron ventas con los criterios de búsqueda seleccionados
                    </td>
                `;
                tablaBody.appendChild(mensaje);
            } else if (filasVisibles > 0 && document.getElementById('mensaje-no-resultados')) {
                document.getElementById('mensaje-no-resultados').remove();
            }
        }
        
        // Filtros en tiempo real con debounce
        let timeoutId;
        function filtrarEnTiempoReal() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                aplicarFiltros();
            }, 300);
        }
        
        // Agregar eventos para filtrado en tiempo real
        document.getElementById('filtroCliente').addEventListener('input', filtrarEnTiempoReal);
        document.getElementById('filtroCI').addEventListener('input', filtrarEnTiempoReal);
        document.getElementById('filtroTotal').addEventListener('input', filtrarEnTiempoReal);
        document.getElementById('filtroEstado').addEventListener('change', aplicarFiltros);
        document.getElementById('filtroFecha').addEventListener('change', aplicarFiltros);
        
        // Buscar por CI al presionar Enter
        document.getElementById('filtroCI').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                aplicarFiltros();
            }
        });
        
        // Resaltar columna de CI
        const style = document.createElement('style');
        style.textContent = `
            td:nth-child(4) {
                font-family: monospace;
                font-weight: 500;
                color: #5B4B9E;
            }
        `;
        document.head.appendChild(style);
        
        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar si hay mensaje de éxito en la URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('mensaje') === 'venta_exitosa') {
                const id = urlParams.get('id');
                mostrarNotificacion(`Venta #${id} creada exitosamente`, 'success');
                // Limpiar URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
        
        function mostrarNotificacion(mensaje, tipo) {
            const notificacion = document.createElement('div');
            notificacion.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${tipo === 'success' ? '#28a745' : '#dc3545'};
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                z-index: 1000;
                animation: slideIn 0.3s ease;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            `;
            notificacion.innerHTML = `<i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${mensaje}`;
            document.body.appendChild(notificacion);
            
            setTimeout(() => {
                notificacion.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notificacion.remove(), 300);
            }, 3000);
        }
        
        // Agregar animaciones
        const animaciones = document.createElement('style');
        animaciones.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(animaciones);
    </script>
</body>
</html>
<?php

// Para /admin/modulos/user/user.php
require_once '../../../includes/funciones.php';


verificar_sesion();
verificar_rol('admin');

// Procesar acciones
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = $_GET['id'] ?? 0;
    
    if ($action == 'eliminar' && $id) {
        try {
            if ($id == $_SESSION['usuario_id']) {
                $error = "No puedes eliminar tu propio usuario";
            } else {
                $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
                $stmt->execute([$id]);
                $success = "Usuario eliminado correctamente";
            }
        } catch (Exception $e) {
            $error = "Error al eliminar usuario: " . $e->getMessage();
        }
    }
    
    if ($action == 'cambiar_estado' && $id) {
        try {
            $stmt = $pdo->prepare("UPDATE usuarios SET activo = NOT activo WHERE id_usuario = ?");
            $stmt->execute([$id]);
            $success = "Estado del usuario actualizado";
        } catch (Exception $e) {
            $error = "Error al cambiar estado: " . $e->getMessage();
        }
    }
}

// Obtener todos los usuarios - CORREGIDO
try {
    $sql = "
        SELECT 
            u.id_usuario,
            u.usuario,
            u.nombre,
            u.email,
            u.rol,
            u.activo,
            u.ultimo_acceso,
            u.fecha_registro,
            COUNT(DISTINCT o.id_orden) as total_ventas,
            COALESCE(SUM(o.total), 0) as total_vendido
        FROM usuarios u
        LEFT JOIN ordenes o ON u.id_usuario = o.usuario_id
        GROUP BY u.id_usuario, u.usuario, u.nombre, u.email, u.rol, u.activo, u.ultimo_acceso, u.fecha_registro
        ORDER BY u.fecha_registro DESC
    ";
    $usuarios = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    // Si hay error con el JOIN, mostrar solo usuarios
    $usuarios = $pdo->query("
        SELECT 
            id_usuario,
            usuario,
            nombre,
            email,
            rol,
            activo,
            ultimo_acceso,
            fecha_registro,
            0 as total_ventas,
            0 as total_vendido
        FROM usuarios
        ORDER BY fecha_registro DESC
    ")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Nexus Digital</title>
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
            background: #f5f6fa;
            padding: 30px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
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
        
        .btn-nuevo {
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
        
        .btn-nuevo:hover {
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
        
        .filtro-input {
            padding: 10px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            flex: 1;
            min-width: 200px;
        }
        
        .filtro-select {
            padding: 10px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            min-width: 150px;
        }
        
        .filtro-input:focus, .filtro-select:focus {
            border-color: #5B4B9E;
            outline: none;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }
        
        th {
            text-align: left;
            padding: 15px;
            background: #f8f9fa;
            color: #666;
            font-weight: 600;
            font-size: 14px;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #333;
            font-size: 14px;
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
        
        .badge-admin {
            background: #dc3545;
            color: white;
        }
        
        .badge-vendedor {
            background: #28a745;
            color: white;
        }
        
        .badge-activo {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-inactivo {
            background: #f8d7da;
            color: #721c24;
        }
        
        .btn-accion {
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin: 0 3px;
            transition: all 0.2s;
        }
        
        .btn-editar {
            background: #ffc107;
            color: #333;
        }
        
        .btn-editar:hover {
            background: #e0a800;
        }
        
        .btn-cambiar-estado {
            background: #17a2b8;
            color: white;
        }
        
        .btn-cambiar-estado:hover {
            background: #138496;
        }
        
        .btn-eliminar {
            background: #dc3545;
            color: white;
        }
        
        .btn-eliminar:hover {
            background: #c82333;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }
        
        .usuario-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stats {
            font-size: 12px;
            color: #666;
        }
        
        .fecha-registro {
            font-size: 12px;
            color: #666;
            white-space: nowrap;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .filtros {
                flex-direction: column;
            }
            
            .btn-accion {
                padding: 4px 8px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
    <h1><i class="fas fa-users"></i> Gestión de Usuarios</h1>
    <div style="display: flex; gap: 15px;">
        <a href="nuevo.php" class="btn-nuevo">
            <i class="fas fa-user-plus"></i> Nuevo Usuario
        </a>
        <a href="../../index.php" class="btn-volver">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="table-container">
            <div class="filtros">
                <input type="text" class="filtro-input" id="filtroNombre" placeholder="Buscar por nombre o email...">
                <select class="filtro-select" id="filtroRol">
                    <option value="">Todos los roles</option>
                    <option value="admin">Administrador</option>
                    <option value="vendedor">Vendedor</option>
                </select>
                <select class="filtro-select" id="filtroEstado">
                    <option value="">Todos los estados</option>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            
            <table id="tablaUsuarios">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Ventas</th>
                        <th>Total Vendido</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($usuarios as $usuario): ?>
                    <tr data-nombre="<?php echo strtolower($usuario['nombre'] . ' ' . ($usuario['email'] ?? '')); ?>"
                        data-rol="<?php echo $usuario['rol']; ?>"
                        data-estado="<?php echo $usuario['activo']; ?>">
                        <td>
                            <div class="usuario-info">
                                <div class="avatar">
                                    <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($usuario['nombre']); ?></strong>
                                    <div class="stats">
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($usuario['usuario']); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($usuario['email'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $usuario['rol']; ?>">
                                <?php echo $usuario['rol'] == 'admin' ? 'Administrador' : 'Vendedor'; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $usuario['activo'] ? 'activo' : 'inactivo'; ?>">
                                <?php echo $usuario['activo'] ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </td>
                        <td><?php echo $usuario['total_ventas'] ?? 0; ?></td>
                        <td><?php echo formato_moneda($usuario['total_vendido'] ?? 0); ?></td>
                        <td class="fecha-registro">
                            <?php echo date('d/m/Y H:i', strtotime($usuario['fecha_registro'])); ?>
                        </td>
                        <td>
                            <a href="editar.php?id=<?php echo $usuario['id_usuario']; ?>" class="btn-accion btn-editar" title="Editar">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="?action=cambiar_estado&id=<?php echo $usuario['id_usuario']; ?>" 
                               class="btn-accion btn-cambiar-estado" 
                               title="<?php echo $usuario['activo'] ? 'Desactivar' : 'Activar'; ?>"
                               onclick="return confirm('¿Estás seguro de <?php echo $usuario['activo'] ? 'desactivar' : 'activar'; ?> este usuario?')">
                                <i class="fas <?php echo $usuario['activo'] ? 'fa-ban' : 'fa-check-circle'; ?>"></i>
                                <?php echo $usuario['activo'] ? 'Desactivar' : 'Activar'; ?>
                            </a>
                            <?php if($usuario['id_usuario'] != $_SESSION['usuario_id']): ?>
                            <a href="?action=eliminar&id=<?php echo $usuario['id_usuario']; ?>" 
                               class="btn-accion btn-eliminar" 
                               title="Eliminar"
                               onclick="return confirm('¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.')">
                                <i class="fas fa-trash"></i> Eliminar
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        // Filtros dinámicos
        function filtrar() {
            const filtroNombre = document.getElementById('filtroNombre').value.toLowerCase();
            const filtroRol = document.getElementById('filtroRol').value;
            const filtroEstado = document.getElementById('filtroEstado').value;
            
            const filas = document.querySelectorAll('#tablaUsuarios tbody tr');
            let visibles = 0;
            
            filas.forEach(fila => {
                let mostrar = true;
                
                if (filtroNombre) {
                    const nombre = fila.getAttribute('data-nombre');
                    if (!nombre || !nombre.includes(filtroNombre)) mostrar = false;
                }
                
                if (mostrar && filtroRol) {
                    const rol = fila.getAttribute('data-rol');
                    if (rol !== filtroRol) mostrar = false;
                }
                
                if (mostrar && filtroEstado !== '') {
                    const estado = fila.getAttribute('data-estado');
                    if (estado !== filtroEstado) mostrar = false;
                }
                
                fila.style.display = mostrar ? '' : 'none';
                if (mostrar) visibles++;
            });
            
            // Mostrar mensaje si no hay resultados
            const tbody = document.querySelector('#tablaUsuarios tbody');
            let mensajeNoResultados = document.getElementById('mensaje-no-resultados');
            
            if (visibles === 0) {
                if (!mensajeNoResultados) {
                    mensajeNoResultados = document.createElement('tr');
                    mensajeNoResultados.id = 'mensaje-no-resultados';
                    mensajeNoResultados.innerHTML = `
                        <td colspan="8" style="text-align: center; padding: 40px;">
                            <i class="fas fa-search" style="font-size: 48px; color: #ccc; margin-bottom: 10px; display: block;"></i>
                            No se encontraron usuarios con los criterios de búsqueda
                        </td>
                    `;
                    tbody.appendChild(mensajeNoResultados);
                }
            } else if (mensajeNoResultados) {
                mensajeNoResultados.remove();
            }
        }
        
        document.getElementById('filtroNombre').addEventListener('input', filtrar);
        document.getElementById('filtroRol').addEventListener('change', filtrar);
        document.getElementById('filtroEstado').addEventListener('change', filtrar);
    </script>
</body>
</html>
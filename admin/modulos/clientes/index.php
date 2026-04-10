<?php
require_once '../../../includes/funciones.php';
verificar_sesion();

// Procesar eliminación
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM CLIENTES WHERE id_cliente = ?");
    $stmt->execute([$id]);
    header('Location: index.php?mensaje=eliminado');
    exit;
}

// Obtener todos los clientes
$clientes = $pdo->query("SELECT * FROM CLIENTES ORDER BY id_cliente DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Nexus Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../../assets/favicon.png">
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
        
        .btn-nuevo {
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
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
        }
        
        .btn-volver {
            background: #6c757d;
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
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
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
        
        .acciones {
            display: flex;
            gap: 10px;
        }
        
        .btn-editar {
            background: #ffc107;
            color: #000;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
        }
        
        .btn-eliminar {
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1><i class="fas fa-users"></i> Gestión de Clientes</h1>
            <p style="color: #666;">Administra tu cartera de clientes</p>
        </div>
        <div style="display: flex; gap: 15px;">
            <a href="nuevo.php" class="btn-nuevo">
                <i class="fas fa-plus"></i> Nuevo Cliente
            </a>
            <a href="../../index.php" class="btn-volver">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>CI</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Fecha Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($clientes as $cliente): ?>
                <tr>
                    <td><?php echo $cliente['ci']; ?></td>
                    <td><strong><?php echo $cliente['nombre']; ?></strong></td>
                    <td><?php echo $cliente['email']; ?></td>
                    <td><?php echo $cliente['telefono'] ?: 'N/A'; ?></td>
                    <td><?php echo $cliente['direccion'] ?: 'N/A'; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($cliente['fecha_registro'])); ?></td>
                    <td class="acciones">
                        <a href="../ventas/editar.php?id=<?php echo $cliente['ci']; ?>" class="btn-editar">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="?eliminar=<?php echo $cliente['ci']; ?>" 
                           class="btn-eliminar" 
                           onclick="return confirm('¿Eliminar este cliente?')">
                            <i class="fas fa-trash"></i> Eliminar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
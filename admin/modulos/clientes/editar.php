<?php
require_once '../../../includes/funciones.php';
verificar_sesion();

$identificacion = $_GET['ci'] ?? 0;

// Obtener datos del cliente
$stmt = $pdo->prepare("SELECT * FROM CLIENTES WHERE ci = ?");
$stmt->execute([$identificacion]);
$cliente = $stmt->fetch();

if (!$cliente) {
    header('Location: index.php?error=no_encontrado');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ci = $_POST['ci'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    
    try {
        $sql = "UPDATE CLIENTES SET ci = ?, nombre = ?, email = ?, telefono = ?, direccion = ? WHERE id_cliente = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ci, $nombre, $email, $telefono, $direccion, $cliente['id_cliente']]);
        
        header('Location: index.php?mensaje=actualizado');
        exit;
    } catch (PDOException $e) {
        $error = "Error al actualizar el cliente: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente - Nexus Digital</title>
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
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-volver:hover {
            background: #5a6268;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #666;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group label i {
            color: #5B4B9E;
            margin-right: 5px;
        }
        
        .form-group input, 
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input:focus, 
        .form-group textarea:focus {
            border-color: #5B4B9E;
            outline: none;
            box-shadow: 0 0 0 3px rgba(91,75,158,0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn-actualizar {
            background: linear-gradient(135deg, #2B7CB3 0%, #5B4B9E 100%);
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
            transition: all 0.3s;
        }
        
        .btn-actualizar:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(43,124,179,0.3);
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info {
            background: #e2f0f9;
            color: #0c5460;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
        }
        
        .required {
            color: #dc3545;
            margin-left: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-edit" style="color: #2B7CB3;"></i> 
                Editar Cliente
            </h1>
            <a href="index.php" class="btn-volver">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <i class="fas fa-id-card"></i>
            Identificación: <strong>#<?php echo $cliente['identificacion']; ?></strong>
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>
                    <i class="fas fa-user"></i> 
                    CI/RIF <span class="required">*</span>
                </label>
                <input 
                    type="number" 
                    name="ci" 
                    value="<?php echo htmlspecialchars($cliente['ci']); ?>" 
                    required
                >
            </div>
            <div class="form-group">
                <label>
                    <i class="fas fa-user"></i> 
                    Nombre completo <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    name="nombre" 
                    value="<?php echo htmlspecialchars($cliente['nombre']); ?>" 
                    required
                >
            </div>
            
            <div class="form-group">
                <label>
                    <i class="fas fa-envelope"></i> 
                    Correo electrónico <span class="required">*</span>
                </label>
                <input 
                    type="email" 
                    name="email" 
                    value="<?php echo htmlspecialchars($cliente['email']); ?>" 
                    required
                >
            </div>
            
            <div class="form-group">
                <label>
                    <i class="fas fa-phone"></i> 
                    Teléfono
                </label>
                <input 
                    type="tel" 
                    name="telefono" 
                    value="<?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>"
                >
            </div>
            
            <div class="form-group">
                <label>
                    <i class="fas fa-map-marker-alt"></i> 
                    Dirección
                </label>
                <textarea name="direccion"><?php echo htmlspecialchars($cliente['direccion'] ?? ''); ?></textarea>
            </div>
            
            <button type="submit" class="btn-actualizar">
                <i class="fas fa-save"></i> 
                Actualizar Cliente
            </button>
        </form>
    </div>
</body>
</html>
<?php
require_once '../../../includes/funciones.php';
verificar_sesion();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $stock = intval($_POST['stock']);
    $precio = floatval($_POST['precio']);
    $imagen = '';
    
    if (empty($nombre)) {
        $error = "El nombre del producto es obligatorio";
    } elseif ($stock < 0) {
        $error = "El stock no puede ser negativo";
    } elseif ($precio <= 0) {
        $error = "El precio debe ser mayor a 0";
    } else {
        try {
            // Procesar imagen
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $filename = $_FILES['imagen']['name'];
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (in_array($extension, $allowed)) {
                    $newname = uniqid() . '.' . $extension;
                    $upload_path = '../../../uploads/productos/' . $newname;
                    
                    if (!is_dir('../../../uploads/productos/')) {
                        mkdir('../../../uploads/productos/', 0777, true);
                    }
                    
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $upload_path)) {
                        $imagen = $newname;
                    }
                }
            }
            
            // ✅ CORREGIDO: productos en minúsculas
            $sql = "INSERT INTO productos (nombre, descripcion, stock, precio, imagen, fecha_creacion) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $descripcion, $stock, $precio, $imagen]);
            
            header('Location: index.php?mensaje=creado');
            exit;
            
        } catch (PDOException $e) {
            $error = "Error al crear producto: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Producto - Nexus Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 24px;
            margin-bottom: 30px;
            color: #333;
        }
        .form-group { margin-bottom: 20px; }
        label {
            display: block;
            margin-bottom: 8px;
            color: #666;
            font-weight: 500;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
        }
        input:focus, textarea:focus {
            border-color: #5B4B9E;
            outline: none;
        }
        textarea { resize: vertical; min-height: 100px; }
        .btn-guardar {
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }
        .btn-cancelar {
            background: #6c757d;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 10px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .imagen-preview {
            margin-top: 10px;
            max-width: 200px;
            display: none;
        }
        .imagen-preview img { width: 100%; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-plus-circle"></i> Nuevo Producto</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nombre del producto</label>
                <input type="text" name="nombre" required>
            </div>
            
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" required></textarea>
            </div>
            
            <div class="form-group">
                <label>Imagen</label>
                <input type="file" name="imagen" accept="image/*" onchange="previewImage(this)">
                <div class="imagen-preview" id="imagenPreview">
                    <img id="preview" src="#">
                </div>
            </div>
            
            <div class="form-group">
                <label>Stock</label>
                <input type="number" name="stock" min="0" required>
            </div>
            
            <div class="form-group">
                <label>Precio</label>
                <input type="number" name="precio" step="0.01" min="0" required>
            </div>
            
            <button type="submit" class="btn-guardar">Crear Producto</button>
            <a href="index.php" class="btn-cancelar">Cancelar</a>
        </form>
    </div>
    
    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagenPreview');
            const previewImg = document.getElementById('preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>

<?php
require_once '../../../includes/funciones.php';
verificar_sesion();
verificar_rol('admin');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $stock = intval($_POST['stock']);
    $precio = floatval($_POST['precio']);
    $imagen = '';
    
    // Validaciones
    if (empty($nombre)) {
        $error = "El nombre del producto es obligatorio";
    } elseif ($stock < 0) {
        $error = "El stock no puede ser negativo";
    } elseif ($precio <= 0) {
        $error = "El precio debe ser mayor a 0";
    } else {
        try {
            // Procesar la imagen
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $filename = $_FILES['imagen']['name'];
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (in_array($extension, $allowed)) {
                    $newname = uniqid() . '.' . $extension;
                    $upload_path = '../../../uploads/productos/' . $newname;
                    
                    // Crear carpeta si no existe
                    if (!is_dir('../../../uploads/productos/')) {
                        mkdir('../../../uploads/productos/', 0777, true);
                    }
                    
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $upload_path)) {
                        $imagen = $newname;
                    } else {
                        $error = "Error al subir la imagen";
                    }
                } else {
                    $error = "Formato de imagen no permitido. Use: JPG, PNG, GIF, WEBP";
                }
            }
            
            if (empty($error)) {
                $sql = "INSERT INTO PRODUCTOS (nombre, descripcion, stock, precio, imagen, fecha_creacion) VALUES (?, ?, ?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $descripcion, $stock, $precio, $imagen]);
                
                header('Location: index.php?mensaje=creado');
                exit;
            }
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
            max-width: 700px;
            margin: 0 auto;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            color: white;
            padding: 25px 30px;
        }
        
        .card-header h1 {
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-header p {
            margin-top: 5px;
            opacity: 0.9;
            font-size: 14px;
        }
        
        .card-body {
            padding: 30px;
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
        
        .required {
            color: #dc3545;
        }
        
        input, textarea, select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        input:focus, textarea:focus, select:focus {
            border-color: #5B4B9E;
            outline: none;
            box-shadow: 0 0 0 3px rgba(91,75,158,0.1);
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .imagen-area {
            border: 2px dashed #e1e1e1;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .imagen-area:hover {
            border-color: #5B4B9E;
            background: #f8f9fa;
        }
        
        .imagen-area i {
            font-size: 48px;
            color: #adb5bd;
            margin-bottom: 10px;
        }
        
        .imagen-preview {
            margin-top: 15px;
            max-width: 200px;
            margin-left: auto;
            margin-right: auto;
            display: none;
        }
        
        .imagen-preview img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
            transition: transform 0.2s;
        }
        
        .btn-guardar:hover {
            transform: translateY(-2px);
        }
        
        .btn-cancelar {
            background: #6c757d;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            width: 100%;
            margin-top: 10px;
        }
        
        .btn-cancelar:hover {
            background: #5a6268;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .card-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>
                    <i class="fas fa-plus-circle"></i>
                    Nuevo Producto
                </h1>
                <p>Complete el formulario para agregar un nuevo producto al catálogo</p>
            </div>
            
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Nombre del producto <span class="required">*</span></label>
                        <input type="text" name="nombre" required placeholder="Ej: Laptop Gaming Pro" value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Descripción <span class="required">*</span></label>
                        <textarea name="descripcion" required placeholder="Descripción detallada del producto..."><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Imagen del producto</label>
                        <div class="imagen-area" onclick="document.getElementById('imagenInput').click()">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Haga clic para seleccionar una imagen</p>
                            <small style="color: #666;">Formatos: JPG, PNG, GIF, WEBP</small>
                        </div>
                        <input type="file" name="imagen" id="imagenInput" accept="image/*" style="display: none;" onchange="previewImage(this)">
                        <div class="imagen-preview" id="imagenPreview">
                            <img id="preview" src="#" alt="Vista previa">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Stock inicial <span class="required">*</span></label>
                        <input type="number" name="stock" min="0" required placeholder="0" value="<?php echo $_POST['stock'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Precio de venta <span class="required">*</span></label>
                        <input type="number" name="precio" step="0.01" min="0" required placeholder="0.00" value="<?php echo $_POST['precio'] ?? ''; ?>">
                    </div>
                    
                    <button type="submit" class="btn-guardar">
                        <i class="fas fa-save"></i> Crear Producto
                    </button>
                    
                    <a href="index.php" class="btn-cancelar">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </form>
            </div>
        </div>
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
<?php
require_once '../../../includes/funciones.php';
verificar_sesion();
verificar_rol('admin');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $usuario = trim($_POST['usuario']);
    $email = trim($_POST['email']);
    $contrasena = $_POST['contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];
    $rol = $_POST['rol'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones
    if (empty($nombre) || empty($usuario) || empty($email) || empty($contrasena)) {
        $error = "Todos los campos son obligatorios";
    } elseif ($contrasena !== $confirmar_contrasena) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($contrasena) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El email no es válido";
    } else {
        try {
            // Verificar si el usuario ya existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = ? OR email = ?");
            $stmt->execute([$usuario, $email]);
            if ($stmt->fetchColumn() > 0) {
                $error = "El nombre de usuario o email ya existe";
            } else {
                // Crear usuario
                $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO usuarios (nombre, usuario, email, contrasena, rol, activo, fecha_registro) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$nombre, $usuario, $email, $contrasena_hash, $rol, $activo]);
                
                $success = "Usuario creado exitosamente";
                // Limpiar formulario
              
// Después de crear el usuario exitosamente

if ($success) {
    // Redirigir a la página de éxito con los datos del usuario
    header('Location: success.php?success=creado&nombre=' . urlencode($nombre) . '&usuario=' . urlencode($usuario) . '&email=' . urlencode($email) . '&rol=' . $rol);
    exit;
}

            }
        } catch (PDOException $e) {
            $error = "Error al crear usuario: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Usuario - Nexus Digital</title>
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
            max-width: 600px;
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
        
        label .required {
            color: #dc3545;
        }
        
        input, select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        input:focus, select:focus {
            border-color: #5B4B9E;
            outline: none;
            box-shadow: 0 0 0 3px rgba(91,75,158,0.1);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input {
            width: auto;
        }
        
        .checkbox-group label {
            margin: 0;
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
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .password-requirements {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }
        
        .input-icon input {
            padding-left: 45px;
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
                    <i class="fas fa-user-plus"></i>
                    Nuevo Usuario
                </h1>
                <p>Complete el formulario para crear un nuevo usuario</p>
            </div>
            
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Nombre completo <span class="required">*</span></label>
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" name="nombre" required 
                                   value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>"
                                   placeholder="Ej: Juan Pérez">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Nombre de usuario <span class="required">*</span></label>
                        <div class="input-icon">
                            <i class="fas fa-id-card"></i>
                            <input type="text" name="usuario" required 
                                   value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>"
                                   placeholder="Ej: juanperez">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" required 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   placeholder="ejemplo@correo.com">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Contraseña <span class="required">*</span></label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="contrasena" id="contrasena" required>
                        </div>
                        <div class="password-requirements">
                            <i class="fas fa-info-circle"></i> Mínimo 6 caracteres
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirmar Contraseña <span class="required">*</span></label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Rol <span class="required">*</span></label>
                        <select name="rol" required>
                            <option value="vendedor" <?php echo (isset($_POST['rol']) && $_POST['rol'] == 'vendedor') ? 'selected' : ''; ?>>
                                Vendedor
                            </option>
                            <option value="admin" <?php echo (isset($_POST['rol']) && $_POST['rol'] == 'admin') ? 'selected' : ''; ?>>
                                Administrador
                            </option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" name="activo" id="activo" 
                                   <?php echo (!isset($_POST['activo']) || $_POST['activo'] == 'on') ? 'checked' : ''; ?>>
                            <label for="activo">Usuario activo</label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-guardar">
                        <i class="fas fa-save"></i> Crear Usuario
                    </button>
                    
                    <a href="index.php" class="btn-cancelar">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Validar contraseñas en tiempo real
        const contrasena = document.getElementById('contrasena');
        const confirmar = document.getElementById('confirmar_contrasena');
        
        function validarContrasenas() {
            if (contrasena.value !== confirmar.value) {
                confirmar.setCustomValidity('Las contraseñas no coinciden');
            } else {
                confirmar.setCustomValidity('');
            }
        }
        
        contrasena.addEventListener('change', validarContrasenas);
        confirmar.addEventListener('keyup', validarContrasenas);
    </script>
</body>
</html>
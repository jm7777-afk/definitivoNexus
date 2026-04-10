<?php
session_start();
require_once '../config/conexion.php';

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ci = $_POST['ci'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $contrasena = $_POST['contrasena'];
    $confirmar = $_POST['confirmar_contrasena'];
    
    // Validaciones
    if ($contrasena !== $confirmar) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($contrasena) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } else {
        // Verificar si email ya existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM CLIENTES WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Este email ya está registrado";
        } else {
            // Registrar cliente
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $sql = "INSERT INTO CLIENTES (nombre, email, telefono, direccion, contrasena, fecha_registro) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$nombre, $email, $telefono, $direccion, $hash])) {
                $exito = "Registro exitoso. Ya puedes iniciar sesión.";
            } else {
                $error = "Error al registrar. Intenta nuevamente.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Nexus Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="/nexus-digital/favicon.ico">
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
        }
        
        .register-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            font-size: 32px;
            font-weight: 700;
            color: #5B4B9E;
        }
        
        .logo span {
            color: #00D4E8;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #666;
            font-weight: 500;
            font-size: 14px;
        }
        
        label i {
            color: #5B4B9E;
            margin-right: 5px;
        }
        
        input, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        input:focus, textarea:focus {
            border-color: #5B4B9E;
            outline: none;
            box-shadow: 0 0 0 3px rgba(91,75,158,0.1);
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .btn-register {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(91,75,158,0.4);
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .exito {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
        }
        
        .login-link a {
            color: #5B4B9E;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .required {
            color: #dc3545;
            margin-left: 3px;
        }
        
        .password-strength {
            margin-top: 5px;
            height: 5px;
            background: #e1e1e1;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s;
        }
        
        .strength-text {
            font-size: 12px;
            margin-top: 5px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <h1>Nexus<span>Digital</span></h1>
            <p style="color: #666; margin-top: 10px;">Crear cuenta de cliente</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($exito): ?>
            <div class="exito">
                <i class="fas fa-check-circle"></i> <?php echo $exito; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> CI/RIF <span class="required">*</span></label>
                    <input type="number" name="ci" placeholder="00000/J-000" required value="<?php echo $_POST['ci'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nombre <span class="required">*</span></label>
                    <input type="text" name="nombre" placeholder="Tu nombre completo" required value="<?php echo $_POST['nombre'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Teléfono</label>
                    <input type="tel" name="telefono" placeholder="Ej: 1123456789" value="<?php echo $_POST['telefono'] ?? ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Correo electrónico <span class="required">*</span></label>
                <input type="email" name="email" placeholder="tucorreo@ejemplo.com" required value="<?php echo $_POST['email'] ?? ''; ?>">
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Dirección</label>
                <textarea name="direccion" placeholder="Tu dirección completa"><?php echo $_POST['direccion'] ?? ''; ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Contraseña <span class="required">*</span></label>
                    <input type="password" name="contrasena" id="password" placeholder="Mínimo 6 caracteres" required>
                    <div class="password-strength">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="strength-text" id="strengthText"></div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Confirmar <span class="required">*</span></label>
                    <input type="password" name="confirmar_contrasena" placeholder="Repite tu contraseña" required>
                </div>
            </div>
            
            <button type="submit" class="btn-register">
                <i class="fas fa-user-plus"></i> Crear Cuenta
            </button>
        </form>
        
        <div class="login-link">
            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
        </div>
    </div>
    
    <script>
        // Medidor de fortaleza de contraseña
        const password = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        
        password.addEventListener('input', function() {
            const value = this.value;
            let strength = 0;
            
            if (value.length >= 6) strength += 1;
            if (value.length >= 8) strength += 1;
            if (/[A-Z]/.test(value)) strength += 1;
            if (/[0-9]/.test(value)) strength += 1;
            if (/[^A-Za-z0-9]/.test(value)) strength += 1;
            
            const porcentaje = (strength / 5) * 100;
            strengthBar.style.width = porcentaje + '%';
            
            if (porcentaje <= 20) {
                strengthBar.style.background = '#dc3545';
                strengthText.textContent = 'Contraseña débil';
                strengthText.style.color = '#dc3545';
            } else if (porcentaje <= 60) {
                strengthBar.style.background = '#ffc107';
                strengthText.textContent = 'Contraseña media';
                strengthText.style.color = '#856404';
            } else {
                strengthBar.style.background = '#28a745';
                strengthText.textContent = 'Contraseña fuerte';
                strengthText.style.color = '#155724';
            }
        });
    </script>
</body>
</html>
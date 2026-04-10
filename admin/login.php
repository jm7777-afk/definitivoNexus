<?php
require_once '../includes/funciones.php';
require_once '../config/conexion.php';

// Si ya está logueado, redirigir según su rol
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['usuario_rol'] == 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../admin/ventas/nueva.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    
    if ($usuario && $contrasena) {
        try {
            // Verificar si la columna se llama 'password' o 'contrasena'
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? AND activo = 1");
            $stmt->execute([$usuario]);
            $user = $stmt->fetch();
            
            // Intentar con ambos nombres de columna posibles
            $password_field = isset($user['password']) ? 'password' : (isset($user['contrasena']) ? 'contrasena' : '');
            
            if ($user && $password_field && password_verify($contrasena, $user[$password_field])) {
                // Guardar datos en sesión
                $_SESSION['usuario_id'] = $user['id_usuario'];
                $_SESSION['usuario_nombre'] = $user['nombre'];
                $_SESSION['usuario_rol'] = $user['rol'];
                $_SESSION['usuario_usuario'] = $user['usuario'];
                $_SESSION['login_time'] = time();
                
                // Actualizar último acceso
                $stmt = $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = ?");
                $stmt->execute([$user['id_usuario']]);
                
                // Redirigir según el rol
                if ($user['rol'] == 'admin') {
                    header('Location: ../admin/index.php');
                } else {
                    header('Location: ../admin/vendedor.php');
                }
                exit;
            } else {
                $error = "Usuario o contraseña incorrectos";
            }
        } catch (PDOException $e) {
            $error = "Error en el sistema: " . $e->getMessage();
        }
    } else {
        $error = "Complete todos los campos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Nexus Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Fondo animado */
        body::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1%, transparent 1%);
            background-size: 50px 50px;
            animation: moveBackground 20s linear infinite;
            opacity: 0.3;
        }
        
        @keyframes moveBackground {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.6s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo { text-align: center; margin-bottom: 30px; }
        .logo h1 { 
            font-size: 36px; 
            font-weight: 700; 
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .logo span { color: #00D4E8; }
        .logo p { color: #666; margin-top: 5px; font-size: 14px; }
        
        .form-group { margin-bottom: 20px; }
        label { 
            display: block; 
            margin-bottom: 8px; 
            color: #666; 
            font-weight: 500;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }
        input:focus { 
            border-color: #5B4B9E; 
            outline: none;
            box-shadow: 0 0 0 3px rgba(91,75,158,0.1);
        }
        
        .btn-login {
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
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(91,75,158,0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .info {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 13px;
        }
        
        .info strong {
            color: #5B4B9E;
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
        
        @media (max-width: 480px) {
            .login-container {
                margin: 20px;
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Nexus<span>Digital</span></h1>
            <p>Sistema Interno - Administración</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="loginForm">
            <div class="form-group">
                <label>Usuario</label>
                <div class="input-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" name="usuario" placeholder="Ingrese su usuario" required autofocus>
                </div>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="contrasena" id="password" placeholder="Ingrese su contraseña" required>
                </div>
            </div>
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" id="showPassword" style="width: auto;">
                    <span style="font-weight: normal;">Mostrar contraseña</span>
                </label>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Ingresar al Sistema
            </button>
        </form>
        <div class="info">
            <p><i class="fas fa-info-circle"></i> Credenciales de prueba:</p>
            <p><strong>Admin:</strong> admin | admin123</p>
            <p><strong>Vendedor:</strong> vendedor | vendedor123</p>
        </div>
    </div>
    
    <script>
        // Mostrar/ocultar contraseña
        document.getElementById('showPassword').addEventListener('change', function() {
            const passwordInput = document.getElementById('password');
            if (this.checked) {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        });
        
        // Validación en tiempo real
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const usuario = document.querySelector('input[name="usuario"]').value.trim();
            const contrasena = document.querySelector('input[name="contrasena"]').value;
            
            if (!usuario || !contrasena) {
                e.preventDefault();
                mostrarError('Por favor complete todos los campos');
            }
        });
        
        function mostrarError(mensaje) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error';
            errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + mensaje;
            const container = document.querySelector('.login-container');
            const form = document.querySelector('form');
            container.insertBefore(errorDiv, form);
            
            setTimeout(() => {
                errorDiv.remove();
            }, 3000);
        }
    </script>
</body>
</html>
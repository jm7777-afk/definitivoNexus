<?php
session_start();
require_once '../config/conexion.php';

// Si ya está logueado, redirigir al catálogo
if (isset($_SESSION['cliente_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $contrasena = $_POST['contrasena'];
    
    $stmt = $pdo->prepare("SELECT * FROM CLIENTES WHERE email = ? AND activo = 1");
    $stmt->execute([$email]);
    $cliente = $stmt->fetch();
    
    if ($cliente && password_verify($contrasena, $cliente['contrasena'])) {
        $_SESSION['cliente_id'] = $cliente['id_cliente'];
        $_SESSION['cliente_nombre'] = $cliente['nombre'];
        $_SESSION['cliente_email'] = $cliente['email'];
        
        // 🚨 ELIMINADO: Actualizar último acceso (columna no existe)
        // $stmt = $pdo->prepare("UPDATE CLIENTES SET ultimo_acceso = NOW() WHERE id_cliente = ?");
        // $stmt->execute([$cliente['id_cliente']]);
        
        header('Location: index.php');
        exit;
    } else {
        $error = "Email o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Clientes - Nexus Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link rel="icon" type="image/x-icon" href="/nexus-digital/favicon.ico">   
    <style>
        /* MISMOS ESTILOS QUE ANTES */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
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
        
        .logo p {
            color: #666;
            margin-top: 10px;
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
        
        label i {
            color: #5B4B9E;
            margin-right: 5px;
        }
        
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .btn-login:hover {
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
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
        }
        
        .register-link a {
            color: #5B4B9E;
            text-decoration: none;
            font-weight: 600;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
        
        .back-link a {
            color: #666;
            text-decoration: none;
            font-size: 13px;
        }
        
        .back-link a:hover {
            color: #5B4B9E;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Nexus<span>Digital</span></h1>
            <p>Accede a tu cuenta</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" placeholder="tucorreo@ejemplo.com" required autofocus>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Contraseña</label>
                <input type="password" name="contrasena" placeholder="Ingresa tu contraseña" required>
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </form>
        
        <div class="register-link">
            ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
        </div>
        
        <div class="back-link">
            <a href="index.php">
                <i class="fas fa-arrow-left"></i> Volver a la tienda
            </a>
        </div>
    </div>
</body>
</html>
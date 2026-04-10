<?php
// Verificar que viene de una creación exitosa
if (!isset($_GET['success']) || $_GET['success'] != 'creado') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuario Creado - Nexus Digital</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        /* Animación de fondo */
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
        
        /* Tarjeta principal */
        .success-card {
            background: white;
            border-radius: 30px;
            padding: 50px;
            max-width: 550px;
            width: 100%;
            text-align: center;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.6s ease;
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
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
        
        /* Icono de éxito */
        .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: pulse 0.8s ease;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .success-icon i {
            font-size: 60px;
            color: white;
        }
        
        /* Títulos */
        h1 {
            font-size: 32px;
            color: #28a745;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .subtitle {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
        }
        
        /* Información del usuario */
        .user-info {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 20px;
            padding: 25px;
            margin: 25px 0;
            text-align: left;
        }
        
        .user-info h3 {
            color: #333;
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
        }
        
        .user-info h3 i {
            color: #5B4B9E;
        }
        
        .info-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            width: 100px;
            font-weight: 600;
            color: #666;
        }
        
        .info-value {
            flex: 1;
            color: #333;
            font-weight: 500;
        }
        
        /* Botones */
        .buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(91,75,158,0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(40,167,69,0.3);
        }
        
        /* Contador de redirección */
        .redirect-timer {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #999;
            font-size: 13px;
        }
        
        .redirect-timer strong {
            color: #5B4B9E;
            font-size: 16px;
        }
        
        /* Animación de confeti */
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background: #f00;
            position: absolute;
            animation: confetti 5s ease-in-out infinite;
        }
        
        @keyframes confetti {
            0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(360deg); opacity: 0; }
        }
        
        @media (max-width: 480px) {
            .success-card {
                padding: 30px;
            }
            
            .success-icon {
                width: 80px;
                height: 80px;
            }
            
            .success-icon i {
                font-size: 40px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .buttons {
                flex-direction: column;
            }
            
            .btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h1>¡Usuario Creado!</h1>
        <p class="subtitle">El usuario ha sido registrado exitosamente en el sistema</p>
        
        <div class="user-info">
            <h3>
                <i class="fas fa-user-circle"></i>
                Información del Usuario
            </h3>
            
            <div class="info-row">
                <div class="info-label">
                    <i class="fas fa-user"></i> Nombre:
                </div>
                <div class="info-value" id="userNombre">
                    <?php echo htmlspecialchars($_GET['nombre'] ?? 'Usuario'); ?>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">
                    <i class="fas fa-id-card"></i> Usuario:
                </div>
                <div class="info-value" id="userUsuario">
                    <?php echo htmlspecialchars($_GET['usuario'] ?? 'usuario'); ?>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">
                    <i class="fas fa-envelope"></i> Email:
                </div>
                <div class="info-value" id="userEmail">
                    <?php echo htmlspecialchars($_GET['email'] ?? 'usuario@ejemplo.com'); ?>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">
                    <i class="fas fa-user-tag"></i> Rol:
                </div>
                <div class="info-value" id="userRol">
                    <?php 
                    $rol = $_GET['rol'] ?? 'vendedor';
                    echo $rol == 'admin' ? 'Administrador' : 'Vendedor';
                    ?>
                </div>
            </div>
        </div>
        
        <div class="buttons">
            <a href="user.php" class="btn btn-primary">
                <i class="fas fa-list"></i> Ver Todos los Usuarios
            </a>
            <a href="nuevo.php" class="btn btn-success">
                <i class="fas fa-user-plus"></i> Crear Otro Usuario
            </a>
            <a href="../../index.php" class="btn btn-secondary">
                <i class="fas fa-tachometer-alt"></i> Ir al Dashboard
            </a>
        </div>
        
        <div class="redirect-timer">
            <i class="fas fa-clock"></i> Redirigiendo a la lista de usuarios en 
            <strong id="timer">5</strong> segundos
        </div>
    </div>
    
    <script>
        // Contador de redirección
        let seconds = 5;
        const timerElement = document.getElementById('timer');
        
        const countdown = setInterval(() => {
            seconds--;
            timerElement.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(countdown);
                window.location.href = 'index.php';
            }
        }, 1000);
        
        // Crear confeti animado
        function createConfetti() {
            const colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff', '#ffa500', '#ff69b4'];
            const confettiCount = 100;
            
            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.width = Math.random() * 10 + 5 + 'px';
                confetti.style.height = Math.random() * 10 + 5 + 'px';
                confetti.style.animationDelay = Math.random() * 3 + 's';
                confetti.style.animationDuration = Math.random() * 3 + 2 + 's';
                document.body.appendChild(confetti);
                
                // Eliminar después de la animación
                setTimeout(() => {
                    confetti.remove();
                }, 5000);
            }
        }
        
        // Iniciar confeti
        createConfetti();
        
        // Repetir confeti cada 2 segundos por 6 segundos
        let confettiInterval = setInterval(() => {
            createConfetti();
        }, 2000);
        
        setTimeout(() => {
            clearInterval(confettiInterval);
        }, 6000);
    </script>
</body>
</html>
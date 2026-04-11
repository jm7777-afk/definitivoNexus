<?php
require_once '/funciones.php';
verificar_sesion();

$destino = $_GET['destino'] ?? 'index.php';
$mensaje = $_GET['mensaje'] ?? '';
$tipo = $_GET['tipo'] ?? 'success'; // success, error, warning, info
$segundos = isset($_GET['segundos']) ? intval($_GET['segundos']) : 3;

// Si es admin, puede ver el dashboard de admin, si no, el de vendedor
$dashboard = ($_SESSION['usuario_rol'] == 'admin') ? '../dashboard.php' : '../dashboard_vendedor.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirigiendo - Nexus Digital</title>
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
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        /* Partículas flotantes */
        .particle {
            position: absolute;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            pointer-events: none;
            animation: floatParticle linear infinite;
        }
        
        @keyframes floatParticle {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
                opacity: 0;
            }
        }
        
        /* Tarjeta principal */
        .redirect-card {
            background: white;
            border-radius: 30px;
            padding: 50px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            position: relative;
            z-index: 1;
            animation: fadeInScale 0.6s ease;
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
        }
        
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        /* Icono animado */
        .icon-container {
            width: 120px;
            height: 120px;
            margin: 0 auto 25px;
            animation: pulseIcon 0.8s ease;
        }
        
        @keyframes pulseIcon {
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
        
        .icon-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 50%;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(40,167,69,0.3);
        }
        
        .icon-error {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border-radius: 50%;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(220,53,69,0.3);
        }
        
        .icon-warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            border-radius: 50%;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(255,193,7,0.3);
        }
        
        .icon-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border-radius: 50%;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(23,162,184,0.3);
        }
        
        .icon-container i {
            font-size: 55px;
            color: white;
        }
        
        /* Títulos */
        h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .message {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        /* Barra de progreso */
        .progress-container {
            background: #e9ecef;
            border-radius: 10px;
            height: 8px;
            margin: 25px 0;
            overflow: hidden;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, #5B4B9E 0%, #2B7CB3 100%);
            height: 100%;
            width: 100%;
            border-radius: 10px;
            animation: progress <?php echo $segundos; ?>s linear forwards;
        }
        
        @keyframes progress {
            from {
                width: 100%;
            }
            to {
                width: 0%;
            }
        }
        
        /* Contador */
        .counter {
            font-size: 14px;
            color: #999;
            margin-bottom: 20px;
        }
        
        .counter strong {
            font-size: 20px;
            color: #5B4B9E;
        }
        
        /* Botones */
        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
        
        /* Confeti animado */
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background: #f00;
            animation: confettiFall 4s ease-out forwards;
            pointer-events: none;
            z-index: 1000;
        }
        
        @keyframes confettiFall {
            0% {
                transform: translateY(-100vh) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(720deg);
                opacity: 0;
            }
        }
        
        @media (max-width: 480px) {
            .redirect-card {
                padding: 30px;
            }
            
            .icon-container {
                width: 80px;
                height: 80px;
            }
            
            .icon-container i {
                font-size: 35px;
            }
            
            h1 {
                font-size: 22px;
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
    <div class="redirect-card">
        <div class="icon-container">
            <div class="icon-<?php echo $tipo; ?>">
                <?php if ($tipo == 'success'): ?>
                    <i class="fas fa-check-circle"></i>
                <?php elseif ($tipo == 'error'): ?>
                    <i class="fas fa-times-circle"></i>
                <?php elseif ($tipo == 'warning'): ?>
                    <i class="fas fa-exclamation-triangle"></i>
                <?php else: ?>
                    <i class="fas fa-info-circle"></i>
                <?php endif; ?>
            </div>
        </div>
        
        <h1>
            <?php 
            if ($tipo == 'success') echo '¡Operación Exitosa!';
            elseif ($tipo == 'error') echo '¡Error!';
            elseif ($tipo == 'warning') echo '¡Atención!';
            else echo 'Información';
            ?>
        </h1>
        
        <div class="message">
            <?php 
            if ($mensaje) {
                echo htmlspecialchars($mensaje);
            } else {
                echo 'Redirigiendo a la página solicitada...';
            }
            ?>
        </div>
        
        <div class="progress-container">
            <div class="progress-bar"></div>
        </div>
        
        <div class="counter">
            <i class="fas fa-clock"></i> Redirigiendo en <strong id="countdown"><?php echo $segundos; ?></strong> segundos
        </div>
        
        <div class="buttons">
            <a href="<?php echo $destino; ?>" class="btn btn-primary">
                <i class="fas fa-arrow-right"></i> Ir ahora
            </a>
            <a href="<?php echo $dashboard; ?>" class="btn btn-secondary">
                <i class="fas fa-home"></i> Ir al Dashboard
            </a>
        </div>
    </div>
    
    <script>
        // Contador regresivo
        let seconds = <?php echo $segundos; ?>;
        const countdownElement = document.getElementById('countdown');
        
        const countdownInterval = setInterval(() => {
            seconds--;
            countdownElement.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(countdownInterval);
                window.location.href = '<?php echo $destino; ?>';
            }
        }, 1000);
        
        // Crear confeti solo si es éxito
        <?php if ($tipo == 'success'): ?>
        function createConfetti() {
            const colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff', '#ffa500', '#ff69b4'];
            const confettiCount = 150;
            
            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.width = Math.random() * 10 + 5 + 'px';
                confetti.style.height = Math.random() * 10 + 5 + 'px';
                confetti.style.animationDelay = Math.random() * 2 + 's';
                confetti.style.animationDuration = Math.random() * 2 + 2 + 's';
                document.body.appendChild(confetti);
                
                setTimeout(() => {
                    confetti.remove();
                }, 4000);
            }
        }
        
        // Crear partículas flotantes
        function createParticles() {
            for (let i = 0; i < 50; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.width = Math.random() * 5 + 2 + 'px';
                particle.style.height = particle.style.width;
                particle.style.animationDuration = Math.random() * 5 + 3 + 's';
                particle.style.animationDelay = Math.random() * 5 + 's';
                document.body.appendChild(particle);
            }
        }
        
        createConfetti();
        createParticles();
        
        // Confeti adicional cada segundo por 3 segundos
        let confettiCount = 0;
        const confettiInterval = setInterval(() => {
            if (confettiCount < 3) {
                createConfetti();
                confettiCount++;
            } else {
                clearInterval(confettiInterval);
            }
        }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>
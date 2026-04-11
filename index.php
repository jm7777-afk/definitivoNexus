<?php
session_start();
// Destruir cualquier sesión existente para empezar limpio
session_destroy();
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus Digital - Acceso</title>
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
            background: rgba(255,255,255,0.2);
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
        .card {
            background: white;
            border-radius: 30px;
            padding: 50px;
            max-width: 900px;
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

        /* Logo */
        .logo {
            margin-bottom: 30px;
        }

        .logo h1 {
            font-size: 42px;
            font-weight: 700;
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo p {
            color: #666;
            margin-top: 10px;
            font-size: 16px;
        }

        .logo span {
            color: #00D4E8;
        }

        /* Título */
        .title {
            margin-bottom: 40px;
        }

        .title h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }

        .title p {
            color: #666;
            font-size: 14px;
        }

        /* Opciones */
        .options {
            display: flex;
            gap: 30px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }

        .option {
            flex: 1;
            min-width: 250px;
            background: #f8f9fa;
            border-radius: 20px;
            padding: 40px 30px;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
            text-decoration: none;
            display: block;
        }

        .option:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .option-admin:hover {
            border-color: #5B4B9E;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .option-client:hover {
            border-color: #28a745;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .option-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }

        .option-admin .option-icon {
            background: linear-gradient(135deg, #5B4B9E 0%, #2B7CB3 100%);
            color: white;
        }

        .option-client .option-icon {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .option h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }

        .option p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #999;
            font-size: 12px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card {
                padding: 30px;
            }

            .logo h1 {
                font-size: 32px;
            }

            .title h2 {
                font-size: 22px;
            }

            .options {
                flex-direction: column;
                gap: 20px;
            }

            .option {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <h1>Nexus<span>Digital</span></h1>
            <p>Tu nexo confiable con la tecnología</p>
        </div>

        <div class="title">
            <h2>Bienvenido</h2>
            <p>Selecciona tu tipo de acceso para continuar</p>
        </div>

        <div class="options">
            <!-- Opción Administrador -->
            <a href="admin/login.php" class="option option-admin">
                <div class="option-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3>Administrador</h3>
                <p>Accede al panel de control, gestiona productos, ventas y usuarios del sistema</p>
            </a>

            <!-- Opción Cliente -->
            <a href="cliente/index.php" class="option option-client">
                <div class="option-icon">
                    <i class="fas fa-user"></i>
                </div>
                <h3>Cliente</h3>
                <p>Explora nuestro catálogo, realiza compras y sigue tus pedidos</p>
            </a>
        </div>

        <div class="footer">
            <p>&copy; 2026 Nexus Digital - Todos los derechos reservados</p>
        </div>
    </div>

    <script>
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
        
        createParticles();
    </script>
</body>
</html>
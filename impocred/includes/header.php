<?php
// Start secure session
require_once 'functions.php';
startSecureSession();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Impocred - Sistema de Gestión de Créditos">
    <meta name="author" content="Impocred">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Impocred' : 'Impocred - Sistema de Gestión de Créditos'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/impocred/assets/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="/impocred/assets/css/style.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Additional CSS for specific pages -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo htmlspecialchars($css); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <style>
        /* Override font family with Google Fonts */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        /* Mobile menu styles */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }
        
        @media (max-width: 767px) {
            .menu-toggle {
                display: block;
            }
            
            .nav-menu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: #2c3e50;
                flex-direction: column;
                padding: 1rem;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            .nav-menu.active {
                display: flex;
            }
            
            .nav-menu a {
                padding: 0.75rem 1rem;
                border-bottom: 1px solid rgba(255,255,255,0.1);
            }
            
            .nav-menu a:last-child {
                border-bottom: none;
            }
        }
        
        /* Touch device improvements */
        .touch-device .btn:active,
        .touch-device .btn.touch-active {
            transform: scale(0.98);
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="/impocred/" class="logo">
                    Impocred
                </a>
                
                <button class="menu-toggle" type="button" aria-label="Toggle navigation">
                    ☰
                </button>
                
                <nav class="nav-menu">
                    <a href="/impocred/">Inicio</a>
                    
                    <?php if (isset($_SESSION['collector_id'])): ?>
                        <!-- Collector menu -->
                        <a href="/impocred/collector/dashboard.php">Dashboard</a>
                        <a href="/impocred/collector/record_payment.php">Registrar Pago</a>
                        <a href="/impocred/collector/logout.php">Cerrar Sesión</a>
                    <?php else: ?>
                        <!-- Public menu -->
                        <a href="/impocred/collector/login.php">Acceso Cobradores</a>
                        <a href="/impocred/admin/">Administración</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <?php
            // Display flash messages if they exist
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                unset($_SESSION['success_message']);
            }
            
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
                unset($_SESSION['error_message']);
            }
            
            if (isset($_SESSION['warning_message'])) {
                echo '<div class="alert alert-warning">' . htmlspecialchars($_SESSION['warning_message']) . '</div>';
                unset($_SESSION['warning_message']);
            }
            
            if (isset($_SESSION['info_message'])) {
                echo '<div class="alert alert-info">' . htmlspecialchars($_SESSION['info_message']) . '</div>';
                unset($_SESSION['info_message']);
            }
            ?>

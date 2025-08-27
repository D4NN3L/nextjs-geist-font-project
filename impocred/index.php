<?php
$pageTitle = "Inicio";
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 4rem 0; margin: -1rem -1rem 3rem -1rem;">
    <div class="container">
        <div class="row">
            <div class="col text-center">
                <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                    Bienvenido a Impocred
                </h1>
                <p style="font-size: 1.25rem; margin-bottom: 2rem; opacity: 0.9; max-width: 600px; margin-left: auto; margin-right: auto;">
                    Sistema integral de gestión de créditos diseñado para optimizar el control de pagos, clientes y cobradores de manera eficiente y segura.
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="collector/login.php" class="btn btn-primary" style="background: white; color: #667eea; font-weight: 600;">
                        Acceso Cobradores
                    </a>
                    <a href="admin/" class="btn btn-secondary" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white;">
                        Administración
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section" style="margin-bottom: 3rem;">
    <div class="container">
        <div class="row">
            <div class="col text-center mb-4">
                <h2 style="font-size: 2rem; font-weight: 600; color: #2c3e50; margin-bottom: 1rem;">
                    Características Principales
                </h2>
                <p style="color: #7f8c8d; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
                    Herramientas completas para la gestión eficiente de créditos y pagos
                </p>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="feature-card" style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center; border-top: 4px solid #3498db;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">👥</div>
                <h3 style="font-size: 1.25rem; font-weight: 600; color: #2c3e50; margin-bottom: 0.5rem;">
                    Gestión de Clientes
                </h3>
                <p style="color: #7f8c8d; line-height: 1.6;">
                    Registro completo de información de clientes con validaciones automáticas y búsqueda avanzada.
                </p>
            </div>
            
            <div class="feature-card" style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center; border-top: 4px solid #27ae60;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">💳</div>
                <h3 style="font-size: 1.25rem; font-weight: 600; color: #2c3e50; margin-bottom: 0.5rem;">
                    Control de Créditos
                </h3>
                <p style="color: #7f8c8d; line-height: 1.6;">
                    Sistema de créditos a 3 meses con cálculo automático de fechas de vencimiento y seguimiento de pagos.
                </p>
            </div>
            
            <div class="feature-card" style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center; border-top: 4px solid #e74c3c;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">⚡</div>
                <h3 style="font-size: 1.25rem; font-weight: 600; color: #2c3e50; margin-bottom: 0.5rem;">
                    Penalizaciones Automáticas
                </h3>
                <p style="color: #7f8c8d; line-height: 1.6;">
                    Cálculo automático de penalizaciones: $2 a los 3 días y $3 a los 5 días de retraso.
                </p>
            </div>
            
            <div class="feature-card" style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center; border-top: 4px solid #f39c12;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
                <h3 style="font-size: 1.25rem; font-weight: 600; color: #2c3e50; margin-bottom: 0.5rem;">
                    Intereses Mensuales
                </h3>
                <p style="color: #7f8c8d; line-height: 1.6;">
                    Aplicación automática del 7.5% de interés mensual después del vencimiento del crédito.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- How it Works Section -->
<section class="how-it-works" style="background: #f8f9fa; padding: 3rem 0; margin: 0 -1rem;">
    <div class="container">
        <div class="row">
            <div class="col text-center mb-4">
                <h2 style="font-size: 2rem; font-weight: 600; color: #2c3e50; margin-bottom: 1rem;">
                    ¿Cómo Funciona?
                </h2>
                <p style="color: #7f8c8d; font-size: 1.1rem;">
                    Proceso simple y eficiente en 4 pasos
                </p>
            </div>
        </div>
        
        <div class="row" style="margin-top: 2rem;">
            <div class="col col-md-6 col-lg-3 mb-3">
                <div style="text-align: center; padding: 1.5rem;">
                    <div style="width: 60px; height: 60px; background: #3498db; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; margin: 0 auto 1rem;">1</div>
                    <h4 style="font-size: 1.1rem; font-weight: 600; color: #2c3e50; margin-bottom: 0.5rem;">Registro de Cliente</h4>
                    <p style="color: #7f8c8d; font-size: 0.9rem;">Se registra la información completa del cliente y el producto adquirido.</p>
                </div>
            </div>
            
            <div class="col col-md-6 col-lg-3 mb-3">
                <div style="text-align: center; padding: 1.5rem;">
                    <div style="width: 60px; height: 60px; background: #27ae60; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; margin: 0 auto 1rem;">2</div>
                    <h4 style="font-size: 1.1rem; font-weight: 600; color: #2c3e50; margin-bottom: 0.5rem;">Creación de Crédito</h4>
                    <p style="color: #7f8c8d; font-size: 0.9rem;">Se genera automáticamente el crédito a 3 meses con fecha de vencimiento.</p>
                </div>
            </div>
            
            <div class="col col-md-6 col-lg-3 mb-3">
                <div style="text-align: center; padding: 1.5rem;">
                    <div style="width: 60px; height: 60px; background: #e74c3c; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; margin: 0 auto 1rem;">3</div>
                    <h4 style="font-size: 1.1rem; font-weight: 600; color: #2c3e50; margin-bottom: 0.5rem;">Asignación a Cobrador</h4>
                    <p style="color: #7f8c8d; font-size: 0.9rem;">El crédito se asigna a un cobrador para el seguimiento de pagos.</p>
                </div>
            </div>
            
            <div class="col col-md-6 col-lg-3 mb-3">
                <div style="text-align: center; padding: 1.5rem;">
                    <div style="width: 60px; height: 60px; background: #f39c12; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; margin: 0 auto 1rem;">4</div>
                    <h4 style="font-size: 1.1rem; font-weight: 600; color: #2c3e50; margin-bottom: 0.5rem;">Registro de Pagos</h4>
                    <p style="color: #7f8c8d; font-size: 0.9rem;">Los cobradores registran los pagos con cálculo automático de penalizaciones.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Access Section -->
<section class="access-section" style="margin: 3rem 0;">
    <div class="container">
        <div class="row">
            <div class="col col-md-6 mb-3">
                <div class="card" style="height: 100%; border-left: 4px solid #3498db;">
                    <div class="card-header">
                        <h3 class="card-title" style="color: #3498db;">
                            👨‍💼 Acceso para Cobradores
                        </h3>
                    </div>
                    <div style="padding: 0 1.5rem 1.5rem;">
                        <p style="color: #7f8c8d; margin-bottom: 1.5rem;">
                            Los cobradores pueden acceder con su email y número de cédula para:
                        </p>
                        <ul style="color: #7f8c8d; margin-bottom: 1.5rem; padding-left: 1.5rem;">
                            <li>Ver dashboard con créditos asignados</li>
                            <li>Registrar pagos de clientes</li>
                            <li>Consultar historial de pagos</li>
                            <li>Ver cálculos automáticos de penalizaciones</li>
                        </ul>
                        <a href="collector/login.php" class="btn btn-primary btn-full">
                            Iniciar Sesión como Cobrador
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col col-md-6 mb-3">
                <div class="card" style="height: 100%; border-left: 4px solid #27ae60;">
                    <div class="card-header">
                        <h3 class="card-title" style="color: #27ae60;">
                            ⚙️ Panel de Administración
                        </h3>
                    </div>
                    <div style="padding: 0 1.5rem 1.5rem;">
                        <p style="color: #7f8c8d; margin-bottom: 1.5rem;">
                            Área administrativa para gestionar:
                        </p>
                        <ul style="color: #7f8c8d; margin-bottom: 1.5rem; padding-left: 1.5rem;">
                            <li>Registro de nuevos clientes</li>
                            <li>Gestión de productos y precios</li>
                            <li>Administración de cobradores</li>
                            <li>Control de gastos personales y empresariales</li>
                        </ul>
                        <a href="admin/" class="btn btn-success btn-full">
                            Acceder a Administración
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="benefits-section" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 3rem 0; margin: 0 -1rem;">
    <div class="container">
        <div class="row">
            <div class="col text-center mb-4">
                <h2 style="font-size: 2rem; font-weight: 600; margin-bottom: 1rem;">
                    Beneficios del Sistema
                </h2>
            </div>
        </div>
        
        <div class="row">
            <div class="col col-md-4 mb-3">
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem;">🚀</div>
                    <h4 style="font-size: 1.2rem; font-weight: 600; margin-bottom: 0.5rem;">Eficiencia</h4>
                    <p style="opacity: 0.9;">Automatización de cálculos y procesos para mayor productividad.</p>
                </div>
            </div>
            
            <div class="col col-md-4 mb-3">
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem;">🔒</div>
                    <h4 style="font-size: 1.2rem; font-weight: 600; margin-bottom: 0.5rem;">Seguridad</h4>
                    <p style="opacity: 0.9;">Sistema seguro con autenticación y protección de datos.</p>
                </div>
            </div>
            
            <div class="col col-md-4 mb-3">
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem;">📱</div>
                    <h4 style="font-size: 1.2rem; font-weight: 600; margin-bottom: 0.5rem;">Responsivo</h4>
                    <p style="opacity: 0.9;">Funciona perfectamente en computadoras, tablets y celulares.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Additional responsive styles for the homepage */
@media (max-width: 767px) {
    .hero-section h1 {
        font-size: 2rem !important;
    }
    
    .hero-section p {
        font-size: 1.1rem !important;
    }
    
    .stats-grid {
        grid-template-columns: 1fr !important;
    }
    
    .feature-card {
        margin-bottom: 1.5rem;
    }
}

@media (min-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 992px) {
    .stats-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Smooth animations */
.feature-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15) !important;
}

/* Button hover effects */
.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
</style>

<?php require_once 'includes/footer.php'; ?>

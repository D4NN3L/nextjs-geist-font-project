<?php
$pageTitle = "Panel de Administración";
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Get system statistics
try {
    // Total clients
    $stmt = executeQuery("SELECT COUNT(*) as total FROM clients");
    $total_clients = $stmt ? $stmt->fetch()['total'] : 0;
    
    // Total products
    $stmt = executeQuery("SELECT COUNT(*) as total FROM products");
    $total_products = $stmt ? $stmt->fetch()['total'] : 0;
    
    // Total collectors
    $stmt = executeQuery("SELECT COUNT(*) as total FROM collectors");
    $total_collectors = $stmt ? $stmt->fetch()['total'] : 0;
    
    // Total credits
    $stmt = executeQuery("SELECT COUNT(*) as total FROM credits");
    $total_credits = $stmt ? $stmt->fetch()['total'] : 0;
    
    // Active credits
    $stmt = executeQuery("SELECT COUNT(*) as total FROM credits WHERE status = 'active'");
    $active_credits = $stmt ? $stmt->fetch()['total'] : 0;
    
    // Overdue credits
    $stmt = executeQuery("SELECT COUNT(*) as total FROM credits WHERE due_date < CURDATE() AND status = 'active'");
    $overdue_credits = $stmt ? $stmt->fetch()['total'] : 0;
    
    // Total collected this month
    $stmt = executeQuery(
        "SELECT COALESCE(SUM(total_paid), 0) as total 
         FROM payments 
         WHERE MONTH(payment_date) = MONTH(CURRENT_DATE()) AND YEAR(payment_date) = YEAR(CURRENT_DATE())"
    );
    $monthly_collected = $stmt ? $stmt->fetch()['total'] : 0;
    
    // Total expenses this month
    $stmt = executeQuery(
        "SELECT COALESCE(SUM(amount), 0) as total 
         FROM expenses 
         WHERE MONTH(expense_date) = MONTH(CURRENT_DATE()) AND YEAR(expense_date) = YEAR(CURRENT_DATE())"
    );
    $monthly_expenses = $stmt ? $stmt->fetch()['total'] : 0;
    
    // Recent activities
    $stmt = executeQuery(
        "SELECT 'payment' as type, p.payment_date as date, p.total_paid as amount, 
                CONCAT('Pago de ', cl.name, ' - ', pr.product_name) as description
         FROM payments p
         JOIN credits c ON p.credit_id = c.id
         JOIN clients cl ON c.client_id = cl.id
         JOIN products pr ON c.product_id = pr.id
         ORDER BY p.created_at DESC
         LIMIT 5"
    );
    $recent_activities = $stmt ? $stmt->fetchAll() : [];
    
} catch (Exception $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $error = "Error al cargar las estadísticas del sistema.";
}

require_once '../includes/header.php';
?>

<!-- Welcome Section -->
<div class="row mb-4">
    <div class="col">
        <div class="card" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; border: none;">
            <div style="padding: 2rem;">
                <h1 style="font-size: 1.75rem; font-weight: 600; margin-bottom: 0.5rem;">
                    ⚙️ Panel de Administración - Impocred
                </h1>
                <p style="opacity: 0.9; margin-bottom: 1rem;">
                    Gestión completa del sistema de créditos - <?php echo date('d/m/Y'); ?>
                </p>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="manage_clients.php" class="btn" style="background: white; color: #2c3e50; font-weight: 600;">
                        👥 Gestionar Clientes
                    </a>
                    <a href="manage_products.php" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white;">
                        📦 Gestionar Productos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Grid -->
<div class="stats-grid mb-4">
    <div class="stat-card" style="border-top: 4px solid #3498db;">
        <div class="stat-number" style="color: #3498db;">
            <?php echo $total_clients; ?>
        </div>
        <div class="stat-label">
            Total Clientes
        </div>
    </div>
    
    <div class="stat-card" style="border-top: 4px solid #27ae60;">
        <div class="stat-number" style="color: #27ae60;">
            <?php echo $total_products; ?>
        </div>
        <div class="stat-label">
            Total Productos
        </div>
    </div>
    
    <div class="stat-card" style="border-top: 4px solid #e74c3c;">
        <div class="stat-number" style="color: #e74c3c;">
            <?php echo $total_collectors; ?>
        </div>
        <div class="stat-label">
            Total Cobradores
        </div>
    </div>
    
    <div class="stat-card" style="border-top: 4px solid #f39c12;">
        <div class="stat-number" style="color: #f39c12;">
            <?php echo $total_credits; ?>
        </div>
        <div class="stat-label">
            Total Créditos
        </div>
    </div>
</div>

<!-- Credit Statistics -->
<div class="row mb-4">
    <div class="col col-md-4">
        <div class="card" style="border-left: 4px solid #27ae60;">
            <div style="padding: 1.5rem; text-align: center;">
                <h3 style="color: #27ae60; font-size: 2rem; margin-bottom: 0.5rem;">
                    <?php echo $active_credits; ?>
                </h3>
                <p style="color: #7f8c8d; margin: 0;">Créditos Activos</p>
            </div>
        </div>
    </div>
    
    <div class="col col-md-4">
        <div class="card" style="border-left: 4px solid #e74c3c;">
            <div style="padding: 1.5rem; text-align: center;">
                <h3 style="color: #e74c3c; font-size: 2rem; margin-bottom: 0.5rem;">
                    <?php echo $overdue_credits; ?>
                </h3>
                <p style="color: #7f8c8d; margin: 0;">Créditos Vencidos</p>
            </div>
        </div>
    </div>
    
    <div class="col col-md-4">
        <div class="card" style="border-left: 4px solid #f39c12;">
            <div style="padding: 1.5rem; text-align: center;">
                <h3 style="color: #f39c12; font-size: 2rem; margin-bottom: 0.5rem;">
                    <?php echo formatCurrency($monthly_collected); ?>
                </h3>
                <p style="color: #7f8c8d; margin: 0;">Cobrado Este Mes</p>
            </div>
        </div>
    </div>
</div>

<!-- Management Modules -->
<div class="row mb-4">
    <div class="col">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    🛠️ Módulos de Gestión
                </h3>
                <p style="color: #7f8c8d; margin: 0;">
                    Acceso rápido a las funciones principales del sistema
                </p>
            </div>
            
            <div class="row" style="padding: 1rem;">
                <div class="col col-md-6 col-lg-3 mb-3">
                    <div class="card" style="height: 100%; border: 1px solid #dee2e6; transition: all 0.3s ease;">
                        <div style="padding: 1.5rem; text-align: center;">
                            <div style="font-size: 2.5rem; margin-bottom: 1rem; color: #3498db;">👥</div>
                            <h4 style="color: #2c3e50; margin-bottom: 1rem;">Gestión de Clientes</h4>
                            <p style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 1.5rem;">
                                Registrar y administrar información de clientes
                            </p>
                            <a href="manage_clients.php" class="btn btn-primary btn-full">
                                Gestionar Clientes
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col col-md-6 col-lg-3 mb-3">
                    <div class="card" style="height: 100%; border: 1px solid #dee2e6; transition: all 0.3s ease;">
                        <div style="padding: 1.5rem; text-align: center;">
                            <div style="font-size: 2.5rem; margin-bottom: 1rem; color: #27ae60;">📦</div>
                            <h4 style="color: #2c3e50; margin-bottom: 1rem;">Gestión de Productos</h4>
                            <p style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 1.5rem;">
                                Administrar catálogo de productos y precios
                            </p>
                            <a href="manage_products.php" class="btn btn-success btn-full">
                                Gestionar Productos
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col col-md-6 col-lg-3 mb-3">
                    <div class="card" style="height: 100%; border: 1px solid #dee2e6; transition: all 0.3s ease;">
                        <div style="padding: 1.5rem; text-align: center;">
                            <div style="font-size: 2.5rem; margin-bottom: 1rem; color: #e74c3c;">👨‍💼</div>
                            <h4 style="color: #2c3e50; margin-bottom: 1rem;">Gestión de Cobradores</h4>
                            <p style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 1.5rem;">
                                Administrar cobradores y sus credenciales
                            </p>
                            <a href="manage_collectors.php" class="btn btn-danger btn-full">
                                Gestionar Cobradores
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col col-md-6 col-lg-3 mb-3">
                    <div class="card" style="height: 100%; border: 1px solid #dee2e6; transition: all 0.3s ease;">
                        <div style="padding: 1.5rem; text-align: center;">
                            <div style="font-size: 2.5rem; margin-bottom: 1rem; color: #f39c12;">💰</div>
                            <h4 style="color: #2c3e50; margin-bottom: 1rem;">Gestión de Gastos</h4>
                            <p style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 1.5rem;">
                                Control de gastos personales y empresariales
                            </p>
                            <a href="manage_expenses.php" class="btn" style="background: #f39c12; color: white;" class="btn-full">
                                Gestionar Gastos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Financial Summary -->
<div class="row mb-4">
    <div class="col col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    📊 Resumen Financiero del Mes
                </h3>
            </div>
            
            <div class="row" style="padding: 1rem;">
                <div class="col col-md-4 mb-3">
                    <div style="text-align: center; padding: 1rem; background: #d4edda; border-radius: 8px;">
                        <h4 style="color: #155724; margin-bottom: 0.5rem;">Ingresos</h4>
                        <p style="font-size: 1.5rem; font-weight: bold; color: #155724; margin: 0;">
                            <?php echo formatCurrency($monthly_collected); ?>
                        </p>
                        <small style="color: #155724;">Pagos recibidos</small>
                    </div>
                </div>
                
                <div class="col col-md-4 mb-3">
                    <div style="text-align: center; padding: 1rem; background: #f8d7da; border-radius: 8px;">
                        <h4 style="color: #721c24; margin-bottom: 0.5rem;">Gastos</h4>
                        <p style="font-size: 1.5rem; font-weight: bold; color: #721c24; margin: 0;">
                            <?php echo formatCurrency($monthly_expenses); ?>
                        </p>
                        <small style="color: #721c24;">Gastos registrados</small>
                    </div>
                </div>
                
                <div class="col col-md-4 mb-3">
                    <div style="text-align: center; padding: 1rem; background: #d1ecf1; border-radius: 8px;">
                        <h4 style="color: #0c5460; margin-bottom: 0.5rem;">Balance</h4>
                        <p style="font-size: 1.5rem; font-weight: bold; color: #0c5460; margin: 0;">
                            <?php echo formatCurrency($monthly_collected - $monthly_expenses); ?>
                        </p>
                        <small style="color: #0c5460;">Diferencia neta</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    🔗 Enlaces Rápidos
                </h3>
            </div>
            
            <div style="padding: 0 1.5rem 1.5rem;">
                <a href="../" class="btn btn-secondary btn-full" style="margin-bottom: 0.5rem;">
                    🏠 Volver al Inicio
                </a>
                <a href="../collector/login.php" class="btn btn-secondary btn-full" style="margin-bottom: 0.5rem;">
                    👨‍💼 Acceso Cobradores
                </a>
                <button onclick="window.print()" class="btn btn-secondary btn-full">
                    🖨️ Imprimir Reporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<?php if (!empty($recent_activities)): ?>
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    📈 Actividad Reciente
                </h3>
            </div>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Descripción</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_activities as $activity): ?>
                            <tr>
                                <td><?php echo formatDate($activity['date']); ?></td>
                                <td><?php echo htmlspecialchars($activity['description']); ?></td>
                                <td style="color: #27ae60; font-weight: 600;">
                                    <?php echo formatCurrency($activity['amount']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
/* Admin dashboard specific styles */
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.management-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.management-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

/* Mobile optimizations */
@media (max-width: 767px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    
    .row .col {
        margin-bottom: 1rem;
    }
}

/* Print styles */
@media print {
    .btn,
    .card-header {
        display: none !important;
    }
    
    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
        margin-bottom: 1rem !important;
        break-inside: avoid;
    }
    
    .stats-grid {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 1rem !important;
    }
    
    .stat-card {
        flex: 1 !important;
        min-width: 150px !important;
    }
}
</style>

<script>
// Admin dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to management cards
    const managementCards = document.querySelectorAll('.card');
    managementCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Auto-refresh statistics every 5 minutes
    setInterval(function() {
        if (!document.hidden) {
            location.reload();
        }
    }, 300000);
    
    // Add confirmation for sensitive actions
    const sensitiveLinks = document.querySelectorAll('a[href*="delete"], a[href*="remove"]');
    sensitiveLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('¿Está seguro de realizar esta acción?')) {
                e.preventDefault();
            }
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>

<?php
$pageTitle = "Dashboard - Cobrador";
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Require collector login
requireCollectorLogin();

$collector_id = $_SESSION['collector_id'];
$collector_name = $_SESSION['collector_name'];

// Get collector statistics
try {
    // Total credits assigned
    $stmt = executeQuery(
        "SELECT COUNT(*) as total_credits FROM credits WHERE collector_id = ?",
        [$collector_id]
    );
    $total_credits = $stmt ? $stmt->fetch()['total_credits'] : 0;
    
    // Active credits
    $stmt = executeQuery(
        "SELECT COUNT(*) as active_credits FROM credits WHERE collector_id = ? AND status = 'active'",
        [$collector_id]
    );
    $active_credits = $stmt ? $stmt->fetch()['active_credits'] : 0;
    
    // Overdue credits
    $stmt = executeQuery(
        "SELECT COUNT(*) as overdue_credits FROM credits WHERE collector_id = ? AND status = 'overdue'",
        [$collector_id]
    );
    $overdue_credits = $stmt ? $stmt->fetch()['overdue_credits'] : 0;
    
    // Total collected this month
    $stmt = executeQuery(
        "SELECT COALESCE(SUM(p.total_paid), 0) as total_collected 
         FROM payments p 
         JOIN credits c ON p.credit_id = c.id 
         WHERE c.collector_id = ? AND MONTH(p.payment_date) = MONTH(CURRENT_DATE()) AND YEAR(p.payment_date) = YEAR(CURRENT_DATE())",
        [$collector_id]
    );
    $total_collected = $stmt ? $stmt->fetch()['total_collected'] : 0;
    
    // Recent credits assigned to this collector
    $stmt = executeQuery(
        "SELECT c.id, c.purchase_date, c.due_date, c.total_amount, c.remaining_amount, c.status,
                cl.name as client_name, cl.phone as client_phone,
                p.product_name, p.price as product_price
         FROM credits c
         JOIN clients cl ON c.client_id = cl.id
         JOIN products p ON c.product_id = p.id
         WHERE c.collector_id = ?
         ORDER BY c.created_at DESC
         LIMIT 10",
        [$collector_id]
    );
    $recent_credits = $stmt ? $stmt->fetchAll() : [];
    
    // Recent payments
    $stmt = executeQuery(
        "SELECT p.payment_date, p.amount, p.penalty, p.interest, p.total_paid,
                cl.name as client_name,
                pr.product_name
         FROM payments p
         JOIN credits c ON p.credit_id = c.id
         JOIN clients cl ON c.client_id = cl.id
         JOIN products pr ON c.product_id = pr.id
         WHERE c.collector_id = ?
         ORDER BY p.created_at DESC
         LIMIT 5",
        [$collector_id]
    );
    $recent_payments = $stmt ? $stmt->fetchAll() : [];
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $error = "Error al cargar los datos del dashboard.";
}

require_once '../includes/header.php';
?>

<!-- Welcome Section -->
<div class="row mb-4">
    <div class="col">
        <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
            <div style="padding: 2rem;">
                <h1 style="font-size: 1.75rem; font-weight: 600; margin-bottom: 0.5rem;">
                    👋 Bienvenido, <?php echo htmlspecialchars($collector_name); ?>
                </h1>
                <p style="opacity: 0.9; margin-bottom: 1rem;">
                    Dashboard de cobrador - <?php echo date('d/m/Y'); ?>
                </p>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="record_payment.php" class="btn" style="background: white; color: #667eea; font-weight: 600;">
                        💰 Registrar Pago
                    </a>
                    <a href="logout.php" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white;">
                        🚪 Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid mb-4">
    <div class="stat-card" style="border-top: 4px solid #3498db;">
        <div class="stat-number" style="color: #3498db;">
            <?php echo $total_credits; ?>
        </div>
        <div class="stat-label">
            Total Créditos
        </div>
    </div>
    
    <div class="stat-card" style="border-top: 4px solid #27ae60;">
        <div class="stat-number" style="color: #27ae60;">
            <?php echo $active_credits; ?>
        </div>
        <div class="stat-label">
            Créditos Activos
        </div>
    </div>
    
    <div class="stat-card" style="border-top: 4px solid #e74c3c;">
        <div class="stat-number" style="color: #e74c3c;">
            <?php echo $overdue_credits; ?>
        </div>
        <div class="stat-label">
            Créditos Vencidos
        </div>
    </div>
    
    <div class="stat-card" style="border-top: 4px solid #f39c12;">
        <div class="stat-number" style="color: #f39c12;">
            <?php echo formatCurrency($total_collected); ?>
        </div>
        <div class="stat-label">
            Cobrado Este Mes
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Credits -->
    <div class="col col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    📋 Créditos Asignados
                </h3>
                <p style="color: #7f8c8d; margin: 0; font-size: 0.9rem;">
                    Últimos créditos asignados para cobro
                </p>
            </div>
            
            <?php if (empty($recent_credits)): ?>
                <div style="padding: 2rem; text-align: center; color: #7f8c8d;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📝</div>
                    <p>No tienes créditos asignados aún.</p>
                    <p style="font-size: 0.9rem;">Los créditos aparecerán aquí cuando sean asignados por el administrador.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Producto</th>
                                <th>Monto</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_credits as $credit): ?>
                                <?php
                                $status_display = getStatusDisplay($credit['status']);
                                $is_overdue = strtotime($credit['due_date']) < time() && $credit['status'] === 'active';
                                if ($is_overdue) {
                                    $status_display = ['text' => 'Vencido', 'class' => 'status-overdue'];
                                }
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($credit['client_name']); ?></strong>
                                        <br>
                                        <small style="color: #7f8c8d;">
                                            📞 <?php echo htmlspecialchars($credit['client_phone']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($credit['product_name']); ?>
                                        <br>
                                        <small style="color: #7f8c8d;">
                                            <?php echo formatCurrency($credit['product_price']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong><?php echo formatCurrency($credit['total_amount']); ?></strong>
                                        <br>
                                        <small style="color: #7f8c8d;">
                                            Pendiente: <?php echo formatCurrency($credit['remaining_amount']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo formatDate($credit['due_date']); ?>
                                        <br>
                                        <small style="color: <?php echo $is_overdue ? '#e74c3c' : '#7f8c8d'; ?>;">
                                            <?php
                                            $days_diff = floor((strtotime($credit['due_date']) - time()) / (60 * 60 * 24));
                                            if ($days_diff < 0) {
                                                echo abs($days_diff) . ' días vencido';
                                            } elseif ($days_diff == 0) {
                                                echo 'Vence hoy';
                                            } else {
                                                echo $days_diff . ' días restantes';
                                            }
                                            ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $status_display['class']; ?>">
                                            <?php echo $status_display['text']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($credit['status'] === 'active' || $is_overdue): ?>
                                            <a href="record_payment.php?credit_id=<?php echo $credit['id']; ?>" 
                                               class="btn btn-primary" style="font-size: 0.875rem; padding: 0.5rem 1rem;">
                                                💰 Pago
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #7f8c8d; font-size: 0.875rem;">
                                                <?php echo $credit['status'] === 'paid' ? '✅ Pagado' : '❌ Cancelado'; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Payments -->
    <div class="col col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    💳 Pagos Recientes
                </h3>
            </div>
            
            <?php if (empty($recent_payments)): ?>
                <div style="padding: 1.5rem; text-align: center; color: #7f8c8d;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">💳</div>
                    <p style="font-size: 0.9rem;">No hay pagos registrados aún.</p>
                </div>
            <?php else: ?>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php foreach ($recent_payments as $payment): ?>
                        <div style="padding: 1rem; border-bottom: 1px solid #eee;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                <strong style="font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($payment['client_name']); ?>
                                </strong>
                                <span style="font-weight: 600; color: #27ae60;">
                                    <?php echo formatCurrency($payment['total_paid']); ?>
                                </span>
                            </div>
                            <div style="font-size: 0.8rem; color: #7f8c8d; margin-bottom: 0.25rem;">
                                <?php echo htmlspecialchars($payment['product_name']); ?>
                            </div>
                            <div style="font-size: 0.8rem; color: #7f8c8d;">
                                📅 <?php echo formatDate($payment['payment_date']); ?>
                            </div>
                            <?php if ($payment['penalty'] > 0 || $payment['interest'] > 0): ?>
                                <div style="font-size: 0.75rem; color: #e74c3c; margin-top: 0.25rem;">
                                    <?php if ($payment['penalty'] > 0): ?>
                                        Penalización: <?php echo formatCurrency($payment['penalty']); ?>
                                    <?php endif; ?>
                                    <?php if ($payment['interest'] > 0): ?>
                                        <?php echo $payment['penalty'] > 0 ? ' | ' : ''; ?>
                                        Interés: <?php echo formatCurrency($payment['interest']); ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col">
        <div class="card" style="background: #f8f9fa; border: none;">
            <div class="card-header text-center">
                <h3 style="color: #2c3e50; margin-bottom: 1rem;">
                    ⚡ Acciones Rápidas
                </h3>
            </div>
            
            <div class="row">
                <div class="col col-md-4 mb-3">
                    <div style="text-align: center; padding: 1rem;">
                        <a href="record_payment.php" class="btn btn-primary btn-full" style="margin-bottom: 0.5rem;">
                            💰 Registrar Nuevo Pago
                        </a>
                        <p style="color: #7f8c8d; font-size: 0.875rem; margin: 0;">
                            Registre pagos de clientes con cálculo automático
                        </p>
                    </div>
                </div>
                
                <div class="col col-md-4 mb-3">
                    <div style="text-align: center; padding: 1rem;">
                        <button onclick="window.print()" class="btn btn-secondary btn-full" style="margin-bottom: 0.5rem;">
                            🖨️ Imprimir Reporte
                        </button>
                        <p style="color: #7f8c8d; font-size: 0.875rem; margin: 0;">
                            Imprima un resumen de sus créditos asignados
                        </p>
                    </div>
                </div>
                
                <div class="col col-md-4 mb-3">
                    <div style="text-align: center; padding: 1rem;">
                        <button onclick="location.reload()" class="btn btn-secondary btn-full" style="margin-bottom: 0.5rem;">
                            🔄 Actualizar Datos
                        </button>
                        <p style="color: #7f8c8d; font-size: 0.875rem; margin: 0;">
                            Actualice la información del dashboard
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Dashboard specific styles */
@media (max-width: 767px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 0.75rem;
    }
    
    .stat-card {
        padding: 1rem !important;
    }
    
    .stat-number {
        font-size: 1.5rem !important;
    }
    
    .table {
        font-size: 0.875rem;
    }
    
    .table th,
    .table td {
        padding: 0.5rem !important;
    }
}

/* Print styles */
@media print {
    .btn,
    .card-header,
    .nav-menu {
        display: none !important;
    }
    
    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
        margin-bottom: 1rem !important;
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

/* Status badge animations */
.status-badge {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive table improvements */
@media (max-width: 991px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .table th:nth-child(n+4),
    .table td:nth-child(n+4) {
        display: none;
    }
    
    .table th:nth-child(6),
    .table td:nth-child(6) {
        display: table-cell;
    }
}
</style>

<script>
// Dashboard specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh dashboard every 5 minutes
    setInterval(function() {
        // Only refresh if the page is visible
        if (!document.hidden) {
            location.reload();
        }
    }, 300000); // 5 minutes
    
    // Add click-to-call functionality for phone numbers
    const phoneNumbers = document.querySelectorAll('small:contains("📞")');
    phoneNumbers.forEach(phone => {
        const phoneText = phone.textContent.replace('📞 ', '');
        phone.innerHTML = `📞 <a href="tel:${phoneText}" style="color: inherit; text-decoration: underline;">${phoneText}</a>`;
    });
    
    // Add tooltips to status badges
    const statusBadges = document.querySelectorAll('.status-badge');
    statusBadges.forEach(badge => {
        const status = badge.textContent.trim();
        let tooltip = '';
        
        switch(status) {
            case 'Activo':
                tooltip = 'Crédito en período normal de pago';
                break;
            case 'Vencido':
                tooltip = 'Crédito pasado la fecha de vencimiento';
                break;
            case 'Pagado':
                tooltip = 'Crédito completamente pagado';
                break;
            case 'Cancelado':
                tooltip = 'Crédito cancelado por el administrador';
                break;
        }
        
        if (tooltip) {
            badge.setAttribute('data-tooltip', tooltip);
        }
    });
    
    // Highlight overdue credits
    const overdueRows = document.querySelectorAll('tr');
    overdueRows.forEach(row => {
        const statusBadge = row.querySelector('.status-overdue');
        if (statusBadge) {
            row.style.backgroundColor = '#fff5f5';
            row.style.borderLeft = '4px solid #e74c3c';
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>

<?php
$pageTitle = "Registrar Pago";
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Require collector login
requireCollectorLogin();

$collector_id = $_SESSION['collector_id'];
$success = '';
$error = '';
$credit_id = $_GET['credit_id'] ?? '';

// Get collector's credits for dropdown
try {
    $stmt = executeQuery(
        "SELECT c.id, c.purchase_date, c.due_date, c.total_amount, c.remaining_amount, c.status,
                cl.name as client_name, cl.phone as client_phone,
                p.product_name, p.price as product_price
         FROM credits c
         JOIN clients cl ON c.client_id = cl.id
         JOIN products p ON c.product_id = p.id
         WHERE c.collector_id = ? AND c.status IN ('active', 'overdue')
         ORDER BY c.due_date ASC",
        [$collector_id]
    );
    $available_credits = $stmt ? $stmt->fetchAll() : [];
} catch (Exception $e) {
    error_log("Error loading credits: " . $e->getMessage());
    $error = "Error al cargar los créditos disponibles.";
}

// Get selected credit details if credit_id is provided
$selected_credit = null;
if ($credit_id && !empty($available_credits)) {
    foreach ($available_credits as $credit) {
        if ($credit['id'] == $credit_id) {
            $selected_credit = $credit;
            break;
        }
    }
}

// Process payment form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $credit_id = sanitizeInput($_POST['credit_id'] ?? '');
    $payment_date = sanitizeInput($_POST['payment_date'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $notes = sanitizeInput($_POST['notes'] ?? '');
    
    // Validate inputs
    if (empty($credit_id) || empty($payment_date) || $amount <= 0) {
        $error = 'Por favor, complete todos los campos obligatorios.';
    } else {
        try {
            // Get credit details
            $stmt = executeQuery(
                "SELECT c.*, cl.name as client_name, p.product_name, p.price as product_price
                 FROM credits c
                 JOIN clients cl ON c.client_id = cl.id
                 JOIN products p ON c.product_id = p.id
                 WHERE c.id = ? AND c.collector_id = ?",
                [$credit_id, $collector_id]
            );
            
            if (!$stmt || !($credit = $stmt->fetch())) {
                $error = 'Crédito no encontrado o no autorizado.';
            } else {
                // Calculate penalty and interest
                $penalty = calculatePenalty($credit['due_date'], $payment_date);
                $interest = calculateInterest($credit['due_date'], $payment_date, $credit['total_amount']);
                $total_paid = $amount + $penalty + $interest;
                
                // Start transaction
                $pdo->beginTransaction();
                
                try {
                    // Insert payment record
                    $stmt = executeQuery(
                        "INSERT INTO payments (credit_id, payment_date, amount, penalty, interest, total_paid, notes) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)",
                        [$credit_id, $payment_date, $amount, $penalty, $interest, $total_paid, $notes]
                    );
                    
                    if (!$stmt) {
                        throw new Exception("Error al registrar el pago");
                    }
                    
                    // Update credit remaining amount
                    $new_remaining = max(0, $credit['remaining_amount'] - $total_paid);
                    $new_status = $new_remaining <= 0 ? 'paid' : $credit['status'];
                    
                    $stmt = executeQuery(
                        "UPDATE credits SET remaining_amount = ?, status = ?, updated_at = CURRENT_TIMESTAMP 
                         WHERE id = ?",
                        [$new_remaining, $new_status, $credit_id]
                    );
                    
                    if (!$stmt) {
                        throw new Exception("Error al actualizar el crédito");
                    }
                    
                    // Commit transaction
                    $pdo->commit();
                    
                    $success = "Pago registrado exitosamente. Total pagado: " . formatCurrency($total_paid);
                    if ($penalty > 0) {
                        $success .= " (incluye penalización de " . formatCurrency($penalty) . ")";
                    }
                    if ($interest > 0) {
                        $success .= " (incluye interés de " . formatCurrency($interest) . ")";
                    }
                    
                    // Clear form
                    $credit_id = '';
                    $selected_credit = null;
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    error_log("Payment transaction error: " . $e->getMessage());
                    $error = "Error al procesar el pago: " . $e->getMessage();
                }
            }
        } catch (Exception $e) {
            error_log("Payment processing error: " . $e->getMessage());
            $error = "Error del sistema. Por favor, intente nuevamente.";
        }
    }
}

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col col-lg-8">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title" style="color: #27ae60;">
                    💰 Registrar Pago de Cliente
                </h2>
                <p style="color: #7f8c8d; margin: 0;">
                    Complete el formulario para registrar un pago con cálculos automáticos
                </p>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($available_credits)): ?>
                <div style="padding: 2rem; text-align: center; color: #7f8c8d;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
                    <h3>No hay créditos disponibles</h3>
                    <p>No tienes créditos activos asignados para registrar pagos.</p>
                    <a href="dashboard.php" class="btn btn-primary">
                        ← Volver al Dashboard
                    </a>
                </div>
            <?php else: ?>
                <form method="POST" data-validate id="paymentForm">
                    <div class="form-group">
                        <label for="credit_id" class="form-label">
                            📋 Seleccionar Crédito *
                        </label>
                        <select id="credit_id" name="credit_id" class="form-select" required onchange="updateCreditDetails()">
                            <option value="">Seleccione un crédito...</option>
                            <?php foreach ($available_credits as $credit): ?>
                                <?php
                                $is_overdue = strtotime($credit['due_date']) < time();
                                $days_diff = floor((strtotime($credit['due_date']) - time()) / (60 * 60 * 24));
                                $status_text = $is_overdue ? " (VENCIDO - " . abs($days_diff) . " días)" : " (" . $days_diff . " días restantes)";
                                ?>
                                <option value="<?php echo $credit['id']; ?>" 
                                        data-client="<?php echo htmlspecialchars($credit['client_name']); ?>"
                                        data-product="<?php echo htmlspecialchars($credit['product_name']); ?>"
                                        data-price="<?php echo $credit['product_price']; ?>"
                                        data-total="<?php echo $credit['total_amount']; ?>"
                                        data-remaining="<?php echo $credit['remaining_amount']; ?>"
                                        data-due-date="<?php echo $credit['due_date']; ?>"
                                        data-phone="<?php echo htmlspecialchars($credit['client_phone']); ?>"
                                        <?php echo ($credit_id == $credit['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($credit['client_name']); ?> - 
                                    <?php echo htmlspecialchars($credit['product_name']); ?> - 
                                    <?php echo formatCurrency($credit['remaining_amount']); ?> pendiente
                                    <?php echo $status_text; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Credit Details (Hidden by default) -->
                    <div id="creditDetails" style="display: none;">
                        <div class="card" style="background: #f8f9fa; border: 1px solid #dee2e6; margin-bottom: 1rem;">
                            <div style="padding: 1rem;">
                                <h4 style="color: #2c3e50; margin-bottom: 1rem; font-size: 1.1rem;">
                                    📊 Detalles del Crédito Seleccionado
                                </h4>
                                <div class="row">
                                    <div class="col col-md-6">
                                        <p><strong>Cliente:</strong> <span id="clientName"></span></p>
                                        <p><strong>Teléfono:</strong> <span id="clientPhone"></span></p>
                                        <p><strong>Producto:</strong> <span id="productName"></span></p>
                                    </div>
                                    <div class="col col-md-6">
                                        <p><strong>Monto Total:</strong> <span id="totalAmount"></span></p>
                                        <p><strong>Monto Pendiente:</strong> <span id="remainingAmount"></span></p>
                                        <p><strong>Fecha Vencimiento:</strong> <span id="dueDate"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col col-md-6">
                            <div class="form-group">
                                <label for="payment_date" class="form-label">
                                    📅 Fecha de Pago *
                                </label>
                                <input 
                                    type="date" 
                                    id="payment_date" 
                                    name="payment_date" 
                                    class="form-input" 
                                    value="<?php echo date('Y-m-d'); ?>"
                                    max="<?php echo date('Y-m-d'); ?>"
                                    required
                                    onchange="calculateTotals()"
                                >
                            </div>
                        </div>
                        
                        <div class="col col-md-6">
                            <div class="form-group">
                                <label for="amount" class="form-label">
                                    💵 Monto del Pago *
                                </label>
                                <input 
                                    type="number" 
                                    id="amount" 
                                    name="amount" 
                                    class="form-input" 
                                    step="0.01" 
                                    min="0.01"
                                    placeholder="0.00"
                                    required
                                    onchange="calculateTotals()"
                                >
                            </div>
                        </div>
                    </div>
                    
                    <!-- Calculation Results -->
                    <div id="calculationResults" style="display: none;">
                        <div class="card" style="background: #fff3cd; border: 1px solid #ffeaa7; margin-bottom: 1rem;">
                            <div style="padding: 1rem;">
                                <h4 style="color: #856404; margin-bottom: 1rem; font-size: 1.1rem;">
                                    🧮 Cálculos Automáticos
                                </h4>
                                <div class="row">
                                    <div class="col col-md-3">
                                        <p><strong>Monto Base:</strong></p>
                                        <p style="font-size: 1.1rem; color: #2c3e50;" id="baseAmount">$0.00</p>
                                    </div>
                                    <div class="col col-md-3">
                                        <p><strong>Penalización:</strong></p>
                                        <p style="font-size: 1.1rem; color: #e74c3c;" id="penaltyAmount">$0.00</p>
                                    </div>
                                    <div class="col col-md-3">
                                        <p><strong>Interés:</strong></p>
                                        <p style="font-size: 1.1rem; color: #f39c12;" id="interestAmount">$0.00</p>
                                    </div>
                                    <div class="col col-md-3">
                                        <p><strong>Total a Pagar:</strong></p>
                                        <p style="font-size: 1.2rem; font-weight: bold; color: #27ae60;" id="totalToPay">$0.00</p>
                                    </div>
                                </div>
                                <div id="penaltyExplanation" style="margin-top: 1rem; padding: 0.75rem; background: #f8d7da; border-radius: 4px; display: none;">
                                    <small style="color: #721c24;">
                                        <strong>Penalización aplicada:</strong> <span id="penaltyText"></span>
                                    </small>
                                </div>
                                <div id="interestExplanation" style="margin-top: 0.5rem; padding: 0.75rem; background: #fff3cd; border-radius: 4px; display: none;">
                                    <small style="color: #856404;">
                                        <strong>Interés aplicado:</strong> <span id="interestText"></span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes" class="form-label">
                            📝 Notas Adicionales
                        </label>
                        <textarea 
                            id="notes" 
                            name="notes" 
                            class="form-textarea" 
                            rows="3"
                            placeholder="Observaciones sobre el pago (opcional)..."
                        ></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-success btn-full">
                            💰 Registrar Pago
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Sidebar with Information -->
    <div class="col col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    ℹ️ Información de Pagos
                </h3>
            </div>
            
            <div style="padding: 0 1.5rem 1.5rem;">
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="color: #e74c3c; font-size: 1rem; margin-bottom: 0.5rem;">
                        ⚠️ Sistema de Penalizaciones
                    </h4>
                    <ul style="color: #7f8c8d; font-size: 0.9rem; margin: 0; padding-left: 1.5rem;">
                        <li>$2.00 después de 3 días de retraso</li>
                        <li>$3.00 después de 5 días de retraso</li>
                    </ul>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="color: #f39c12; font-size: 1rem; margin-bottom: 0.5rem;">
                        📈 Sistema de Intereses
                    </h4>
                    <ul style="color: #7f8c8d; font-size: 0.9rem; margin: 0; padding-left: 1.5rem;">
                        <li>7.5% mensual después del vencimiento</li>
                        <li>Cálculo automático por mes completo</li>
                    </ul>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="color: #27ae60; font-size: 1rem; margin-bottom: 0.5rem;">
                        ✅ Proceso de Pago
                    </h4>
                    <ol style="color: #7f8c8d; font-size: 0.9rem; margin: 0; padding-left: 1.5rem;">
                        <li>Seleccione el crédito</li>
                        <li>Ingrese fecha y monto</li>
                        <li>Revise los cálculos automáticos</li>
                        <li>Confirme el registro</li>
                    </ol>
                </div>
                
                <div style="background: #d1ecf1; padding: 1rem; border-radius: 4px;">
                    <h4 style="color: #0c5460; font-size: 0.9rem; margin-bottom: 0.5rem;">
                        💡 Consejo
                    </h4>
                    <p style="color: #0c5460; font-size: 0.85rem; margin: 0;">
                        Los cálculos de penalizaciones e intereses se realizan automáticamente. 
                        Verifique siempre los totales antes de confirmar el pago.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    🔗 Enlaces Rápidos
                </h3>
            </div>
            
            <div style="padding: 0 1.5rem 1.5rem;">
                <a href="dashboard.php" class="btn btn-secondary btn-full" style="margin-bottom: 0.5rem;">
                    📊 Volver al Dashboard
                </a>
                <a href="logout.php" class="btn btn-secondary btn-full">
                    🚪 Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Payment form specific styles */
.form-select:focus,
.form-input:focus {
    border-color: #27ae60;
    box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.1);
}

#creditDetails {
    animation: slideDown 0.3s ease-out;
}

#calculationResults {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Mobile optimizations */
@media (max-width: 767px) {
    .row .col {
        margin-bottom: 1rem;
    }
    
    #calculationResults .row .col {
        margin-bottom: 0.5rem;
    }
    
    #calculationResults .col {
        text-align: center;
        padding: 0.5rem;
    }
}

/* Loading state for calculations */
.calculating {
    opacity: 0.7;
    pointer-events: none;
}

.calculating::after {
    content: " 🔄";
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<script>
// Payment form JavaScript
let selectedCreditData = null;

function updateCreditDetails() {
    const select = document.getElementById('credit_id');
    const creditDetails = document.getElementById('creditDetails');
    const calculationResults = document.getElementById('calculationResults');
    
    if (select.value) {
        const option = select.options[select.selectedIndex];
        selectedCreditData = {
            client: option.dataset.client,
            product: option.dataset.product,
            price: parseFloat(option.dataset.price),
            total: parseFloat(option.dataset.total),
            remaining: parseFloat(option.dataset.remaining),
            dueDate: option.dataset.dueDate,
            phone: option.dataset.phone
        };
        
        // Update credit details display
        document.getElementById('clientName').textContent = selectedCreditData.client;
        document.getElementById('clientPhone').textContent = selectedCreditData.phone;
        document.getElementById('productName').textContent = selectedCreditData.product;
        document.getElementById('totalAmount').textContent = formatCurrency(selectedCreditData.total);
        document.getElementById('remainingAmount').textContent = formatCurrency(selectedCreditData.remaining);
        document.getElementById('dueDate').textContent = formatDate(selectedCreditData.dueDate);
        
        creditDetails.style.display = 'block';
        
        // Set suggested amount to remaining amount
        document.getElementById('amount').value = selectedCreditData.remaining.toFixed(2);
        
        // Calculate totals
        calculateTotals();
    } else {
        creditDetails.style.display = 'none';
        calculationResults.style.display = 'none';
        selectedCreditData = null;
    }
}

function calculateTotals() {
    if (!selectedCreditData) return;
    
    const paymentDate = document.getElementById('payment_date').value;
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    
    if (!paymentDate || amount <= 0) {
        document.getElementById('calculationResults').style.display = 'none';
        return;
    }
    
    // Calculate penalty
    const penalty = calculatePenalty(selectedCreditData.dueDate, paymentDate);
    
    // Calculate interest
    const interest = calculateInterest(selectedCreditData.dueDate, paymentDate, selectedCreditData.total);
    
    // Calculate total
    const total = amount + penalty + interest;
    
    // Update display
    document.getElementById('baseAmount').textContent = formatCurrency(amount);
    document.getElementById('penaltyAmount').textContent = formatCurrency(penalty);
    document.getElementById('interestAmount').textContent = formatCurrency(interest);
    document.getElementById('totalToPay').textContent = formatCurrency(total);
    
    // Show/hide explanations
    const penaltyExplanation = document.getElementById('penaltyExplanation');
    const interestExplanation = document.getElementById('interestExplanation');
    
    if (penalty > 0) {
        const dueDate = new Date(selectedCreditData.dueDate);
        const payDate = new Date(paymentDate);
        const diffTime = Math.abs(payDate - dueDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        let penaltyText = '';
        if (penalty === 3.00) {
            penaltyText = `$3.00 por ${diffDays} días de retraso (5+ días)`;
        } else if (penalty === 2.00) {
            penaltyText = `$2.00 por ${diffDays} días de retraso (3-4 días)`;
        }
        
        document.getElementById('penaltyText').textContent = penaltyText;
        penaltyExplanation.style.display = 'block';
    } else {
        penaltyExplanation.style.display = 'none';
    }
    
    if (interest > 0) {
        const dueDate = new Date(selectedCreditData.dueDate);
        const payDate = new Date(paymentDate);
        const diffTime = Math.abs(payDate - dueDate);
        const diffMonths = Math.ceil(diffTime / (1000 * 60 * 60 * 24 * 30));
        
        const interestText = `7.5% mensual por ${diffMonths} mes(es) de retraso`;
        document.getElementById('interestText').textContent = interestText;
        interestExplanation.style.display = 'block';
    } else {
        interestExplanation.style.display = 'none';
    }
    
    document.getElementById('calculationResults').style.display = 'block';
}

function calculatePenalty(dueDate, paymentDate) {
    const due = new Date(dueDate);
    const payment = new Date(paymentDate);
    
    if (payment <= due) return 0;
    
    const diffTime = Math.abs(payment - due);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays >= 5) return 3.00;
    if (diffDays >= 3) return 2.00;
    return 0;
}

function calculateInterest(dueDate, currentDate, productPrice) {
    const due = new Date(dueDate);
    const current = new Date(currentDate);
    
    if (current <= due) return 0;
    
    const diffTime = Math.abs(current - due);
    const diffMonths = Math.ceil(diffTime / (1000 * 60 * 60 * 24 * 30));
    
    return productPrice * 0.075 * diffMonths;
}

function formatCurrency(amount) {
    return '$' + amount.toFixed(2);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES');
}

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    // If credit_id is pre-selected from URL, update details
    const creditSelect = document.getElementById('credit_id');
    if (creditSelect.value) {
        updateCreditDetails();
    }
    
    // Add form validation
    const form = document.getElementById('paymentForm');
    form.addEventListener('submit', function(e) {
        const creditId = document.getElementById('credit_id').value;
        const paymentDate = document.getElementById('payment_date').value;
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        
        if (!creditId || !paymentDate || amount <= 0) {
            e.preventDefault();
            alert('Por favor, complete todos los campos obligatorios.');
            return;
        }
        
        if (selectedCreditData && amount > selectedCreditData.remaining * 2) {
            if (!confirm('El monto ingresado es significativamente mayor al saldo pendiente. ¿Está seguro de continuar?')) {
                e.preventDefault();
                return;
            }
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>

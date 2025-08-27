<?php
$pageTitle = "Gestión de Cobradores";
require_once '../includes/db.php';
require_once '../includes/functions.php';

$success = '';
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $cedula = sanitizeInput($_POST['cedula'] ?? '');
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($cedula)) {
        $error = 'Por favor, complete todos los campos obligatorios.';
    } elseif (!validateEmail($email)) {
        $error = 'Por favor, ingrese un email válido.';
    } elseif (!validateCedula($cedula)) {
        $error = 'Por favor, ingrese una cédula válida.';
    } else {
        try {
            // Check if email or cedula already exists
            $stmt = executeQuery("SELECT id FROM collectors WHERE email = ? OR cedula = ?", [$email, $cedula]);
            if ($stmt && $stmt->fetch()) {
                $error = 'Ya existe un cobrador con este email o cédula.';
            } else {
                // Hash the cedula as password
                $password_hash = hashPassword($cedula);
                
                // Insert new collector
                $stmt = executeQuery(
                    "INSERT INTO collectors (name, email, cedula, password_hash) VALUES (?, ?, ?, ?)",
                    [$name, $email, $cedula, $password_hash]
                );
                
                if ($stmt) {
                    $success = 'Cobrador registrado exitosamente. Su contraseña es su número de cédula.';
                    // Clear form
                    $name = $email = $cedula = '';
                } else {
                    $error = 'Error al registrar el cobrador.';
                }
            }
        } catch (Exception $e) {
            error_log("Collector registration error: " . $e->getMessage());
            $error = 'Error del sistema. Por favor, intente nuevamente.';
        }
    }
}

// Get existing collectors with statistics
try {
    $stmt = executeQuery(
        "SELECT c.*, 
                COUNT(cr.id) as total_credits,
                COALESCE(SUM(CASE WHEN cr.status = 'active' THEN 1 ELSE 0 END), 0) as active_credits,
                COALESCE(SUM(CASE WHEN cr.due_date < CURDATE() AND cr.status = 'active' THEN 1 ELSE 0 END), 0) as overdue_credits,
                COALESCE(SUM(p.total_paid), 0) as total_collected
         FROM collectors c 
         LEFT JOIN credits cr ON c.id = cr.collector_id 
         LEFT JOIN payments p ON cr.id = p.credit_id
         GROUP BY c.id 
         ORDER BY c.created_at DESC"
    );
    $collectors = $stmt ? $stmt->fetchAll() : [];
} catch (Exception $e) {
    error_log("Error loading collectors: " . $e->getMessage());
    $collectors = [];
}

require_once '../includes/header.php';
?>

<div class="row">
    <!-- Collector Registration Form -->
    <div class="col col-lg-5">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title" style="color: #e74c3c;">
                    👨‍💼 Registrar Nuevo Cobrador
                </h2>
                <p style="color: #7f8c8d; margin: 0;">
                    Complete la información del cobrador
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
            
            <form method="POST" data-validate>
                <div class="form-group">
                    <label for="name" class="form-label">
                        👤 Nombre Completo *
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($name ?? ''); ?>"
                        placeholder="Nombre completo del cobrador"
                        required
                        autocomplete="name"
                    >
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">
                        📧 Correo Electrónico *
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($email ?? ''); ?>"
                        placeholder="cobrador@correo.com"
                        required
                        autocomplete="email"
                    >
                    <small style="color: #7f8c8d; font-size: 0.875rem;">
                        Este será su usuario para iniciar sesión
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="cedula" class="form-label">
                        🆔 Número de Cédula *
                    </label>
                    <input 
                        type="text" 
                        id="cedula" 
                        name="cedula" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($cedula ?? ''); ?>"
                        placeholder="12345678"
                        required
                        pattern="[0-9]{8,}"
                        title="La cédula debe tener al menos 8 dígitos"
                    >
                    <small style="color: #7f8c8d; font-size: 0.875rem;">
                        Esta será su contraseña para iniciar sesión
                    </small>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-danger btn-full">
                        ✅ Registrar Cobrador
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Access Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    🔐 Información de Acceso
                </h3>
            </div>
            
            <div style="padding: 0 1.5rem 1.5rem;">
                <div style="background: #d1ecf1; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                    <h4 style="color: #0c5460; font-size: 1rem; margin-bottom: 0.5rem;">
                        📋 Credenciales de Acceso
                    </h4>
                    <ul style="color: #0c5460; font-size: 0.9rem; margin: 0; padding-left: 1.5rem;">
                        <li><strong>Usuario:</strong> Su correo electrónico</li>
                        <li><strong>Contraseña:</strong> Su número de cédula</li>
                    </ul>
                </div>
                
                <div style="background: #fff3cd; padding: 1rem; border-radius: 4px;">
                    <h4 style="color: #856404; font-size: 1rem; margin-bottom: 0.5rem;">
                        ⚠️ Importante
                    </h4>
                    <p style="color: #856404; font-size: 0.9rem; margin: 0;">
                        Comparta estas credenciales de forma segura con el cobrador. 
                        Pueden acceder desde cualquier dispositivo móvil o computadora.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    🔗 Enlaces Rápidos
                </h3>
            </div>
            
            <div style="padding: 0 1.5rem 1.5rem;">
                <a href="index.php" class="btn btn-secondary btn-full" style="margin-bottom: 0.5rem;">
                    ← Volver al Panel
                </a>
                <a href="manage_clients.php" class="btn btn-secondary btn-full" style="margin-bottom: 0.5rem;">
                    👥 Gestionar Clientes
                </a>
                <a href="manage_products.php" class="btn btn-secondary btn-full" style="margin-bottom: 0.5rem;">
                    📦 Gestionar Productos
                </a>
                <a href="../collector/login.php" class="btn btn-primary btn-full">
                    🔐 Probar Acceso Cobrador
                </a>
            </div>
        </div>
    </div>
    
    <!-- Collectors List -->
    <div class="col col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    📋 Cobradores Registrados
                </h3>
                <p style="color: #7f8c8d; margin: 0;">
                    Total: <?php echo count($collectors); ?> cobradores
                </p>
            </div>
            
            <?php if (empty($collectors)): ?>
                <div style="padding: 2rem; text-align: center; color: #7f8c8d;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">👨‍💼</div>
                    <h4>No hay cobradores registrados</h4>
                    <p>Los cobradores aparecerán aquí una vez que sean registrados.</p>
                </div>
            <?php else: ?>
                <!-- Search and Filter -->
                <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #eee;">
                    <div class="row">
                        <div class="col col-md-8">
                            <input 
                                type="text" 
                                id="searchCollectors" 
                                class="form-input" 
                                placeholder="🔍 Buscar por nombre, email o cédula..."
                                onkeyup="filterCollectors()"
                            >
                        </div>
                        <div class="col col-md-4">
                            <button onclick="exportCollectors()" class="btn btn-secondary btn-full">
                                📊 Exportar Lista
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table" id="collectorsTable">
                        <thead style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">
                            <tr>
                                <th>Cobrador</th>
                                <th>Estadísticas</th>
                                <th>Rendimiento</th>
                                <th>Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($collectors as $collector): ?>
                                <tr class="collector-row">
                                    <td>
                                        <strong class="collector-name"><?php echo htmlspecialchars($collector['name']); ?></strong>
                                        <br>
                                        <small style="color: #7f8c8d;" class="collector-email">
                                            📧 <?php echo htmlspecialchars($collector['email']); ?>
                                        </small>
                                        <br>
                                        <small style="color: #7f8c8d;" class="collector-cedula">
                                            🆔 <?php echo htmlspecialchars($collector['cedula']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div style="margin-bottom: 0.25rem;">
                                            <span style="font-weight: 600; color: #2c3e50;">
                                                <?php echo $collector['total_credits']; ?>
                                            </span>
                                            <small style="color: #7f8c8d;"> total</small>
                                        </div>
                                        <div style="margin-bottom: 0.25rem;">
                                            <span style="font-weight: 600; color: #27ae60;">
                                                <?php echo $collector['active_credits']; ?>
                                            </span>
                                            <small style="color: #7f8c8d;"> activos</small>
                                        </div>
                                        <div>
                                            <span style="font-weight: 600; color: #e74c3c;">
                                                <?php echo $collector['overdue_credits']; ?>
                                            </span>
                                            <small style="color: #7f8c8d;"> vencidos</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="margin-bottom: 0.25rem;">
                                            <strong style="color: #27ae60; font-size: 1.1rem;">
                                                <?php echo formatCurrency($collector['total_collected']); ?>
                                            </strong>
                                        </div>
                                        <small style="color: #7f8c8d;">Total cobrado</small>
                                        <br>
                                        <?php
                                        $efficiency = $collector['total_credits'] > 0 ? 
                                                    (($collector['total_credits'] - $collector['active_credits']) / $collector['total_credits']) * 100 : 0;
                                        $efficiency_color = $efficiency >= 80 ? '#27ae60' : ($efficiency >= 60 ? '#f39c12' : '#e74c3c');
                                        ?>
                                        <small style="color: <?php echo $efficiency_color; ?>; font-weight: 600;">
                                            <?php echo number_format($efficiency, 1); ?>% efectividad
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo formatDate($collector['created_at']); ?>
                                        <br>
                                        <small style="color: #7f8c8d;">
                                            <?php
                                            $days_ago = floor((time() - strtotime($collector['created_at'])) / (60 * 60 * 24));
                                            echo $days_ago == 0 ? 'Hoy' : ($days_ago == 1 ? 'Ayer' : $days_ago . ' días');
                                            ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                                            <button 
                                                onclick="viewCollector(<?php echo $collector['id']; ?>)" 
                                                class="btn btn-primary" 
                                                style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                                                title="Ver detalles"
                                            >
                                                👁️
                                            </button>
                                            <button 
                                                onclick="editCollector(<?php echo $collector['id']; ?>)" 
                                                class="btn btn-secondary" 
                                                style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                                                title="Editar"
                                            >
                                                ✏️
                                            </button>
                                            <button 
                                                onclick="resetPassword(<?php echo $collector['id']; ?>)" 
                                                class="btn" 
                                                style="background: #f39c12; color: white; font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                                                title="Resetear contraseña"
                                            >
                                                🔑
                                            </button>
                                            <a 
                                                href="mailto:<?php echo $collector['email']; ?>" 
                                                class="btn btn-success" 
                                                style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                                                title="Enviar email"
                                            >
                                                📧
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Performance Summary -->
                <div style="padding: 1rem 1.5rem; background: #f8f9fa; border-top: 1px solid #eee;">
                    <div class="row">
                        <div class="col col-md-3 text-center">
                            <strong style="color: #2c3e50;">
                                <?php echo count($collectors); ?>
                            </strong>
                            <br>
                            <small style="color: #7f8c8d;">Total Cobradores</small>
                        </div>
                        <div class="col col-md-3 text-center">
                            <strong style="color: #3498db;">
                                <?php echo array_sum(array_column($collectors, 'total_credits')); ?>
                            </strong>
                            <br>
                            <small style="color: #7f8c8d;">Créditos Asignados</small>
                        </div>
                        <div class="col col-md-3 text-center">
                            <strong style="color: #27ae60;">
                                <?php echo formatCurrency(array_sum(array_column($collectors, 'total_collected'))); ?>
                            </strong>
                            <br>
                            <small style="color: #7f8c8d;">Total Cobrado</small>
                        </div>
                        <div class="col col-md-3 text-center">
                            <strong style="color: #e74c3c;">
                                <?php echo array_sum(array_column($collectors, 'overdue_credits')); ?>
                            </strong>
                            <br>
                            <small style="color: #7f8c8d;">Créditos Vencidos</small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Collector management specific styles */
.collector-row:hover {
    background-color: #f8f9fa;
}

.form-input:focus {
    border-color: #e74c3c;
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}

/* Performance indicators */
.performance-high {
    color: #27ae60;
    font-weight: 600;
}

.performance-medium {
    color: #f39c12;
    font-weight: 600;
}

.performance-low {
    color: #e74c3c;
    font-weight: 600;
}

/* Mobile optimizations */
@media (max-width: 767px) {
    .table th:nth-child(n+3),
    .table td:nth-child(n+3) {
        display: none;
    }
    
    .table th:nth-child(5),
    .table td:nth-child(5) {
        display: table-cell;
    }
    
    .collector-row td {
        padding: 0.75rem 0.5rem;
    }
    
    .btn {
        font-size: 0.875rem;
        padding: 0.5rem;
    }
}

/* Search highlighting */
.highlight {
    background-color: #fff3cd;
    padding: 0.1rem 0.2rem;
    border-radius: 2px;
}

/* Loading states */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.loading::after {
    content: " 🔄";
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<script>
// Collector management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Format cedula input (numbers only)
    const cedulaInput = document.getElementById('cedula');
    cedulaInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
    
    // Real-time validation
    const form = document.querySelector('form[data-validate]');
    const inputs = form.querySelectorAll('input[required]');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            clearFieldError(this);
        });
    });
});

function filterCollectors() {
    const searchTerm = document.getElementById('searchCollectors').value.toLowerCase();
    const rows = document.querySelectorAll('.collector-row');
    
    rows.forEach(row => {
        const name = row.querySelector('.collector-name').textContent.toLowerCase();
        const email = row.querySelector('.collector-email').textContent.toLowerCase();
        const cedula = row.querySelector('.collector-cedula').textContent.toLowerCase();
        
        const matches = name.includes(searchTerm) || 
                       email.includes(searchTerm) || 
                       cedula.includes(searchTerm);
        
        row.style.display = matches ? '' : 'none';
        
        // Highlight matching text
        if (searchTerm && matches) {
            highlightText(row, searchTerm);
        } else {
            removeHighlight(row);
        }
    });
}

function highlightText(element, searchTerm) {
    const textNodes = getTextNodes(element);
    textNodes.forEach(node => {
        const text = node.textContent;
        const regex = new RegExp(`(${searchTerm})`, 'gi');
        if (regex.test(text)) {
            const highlightedText = text.replace(regex, '<span class="highlight">$1</span>');
            const wrapper = document.createElement('span');
            wrapper.innerHTML = highlightedText;
            node.parentNode.replaceChild(wrapper, node);
        }
    });
}

function removeHighlight(element) {
    const highlights = element.querySelectorAll('.highlight');
    highlights.forEach(highlight => {
        const parent = highlight.parentNode;
        parent.replaceChild(document.createTextNode(highlight.textContent), highlight);
        parent.normalize();
    });
}

function getTextNodes(element) {
    const textNodes = [];
    const walker = document.createTreeWalker(
        element,
        NodeFilter.SHOW_TEXT,
        null,
        false
    );
    
    let node;
    while (node = walker.nextNode()) {
        textNodes.push(node);
    }
    
    return textNodes;
}

function viewCollector(collectorId) {
    alert('Función de ver detalles del cobrador (ID: ' + collectorId + ') - Por implementar');
}

function editCollector(collectorId) {
    alert('Función de editar cobrador (ID: ' + collectorId + ') - Por implementar');
}

function resetPassword(collectorId) {
    if (confirm('¿Está seguro de resetear la contraseña de este cobrador? La nueva contraseña será su número de cédula.')) {
        alert('Función de resetear contraseña (ID: ' + collectorId + ') - Por implementar');
    }
}

function exportCollectors() {
    const table = document.getElementById('collectorsTable');
    const rows = table.querySelectorAll('tr:not([style*="display: none"])');
    let csv = 'Nombre,Email,Cédula,Total Créditos,Créditos Activos,Créditos Vencidos,Total Cobrado,Efectividad,Fecha Registro\n';
    
    rows.forEach((row, index) => {
        if (index === 0) return; // Skip header
        
        const cells = row.querySelectorAll('td');
        if (cells.length === 0) return;
        
        const name = cells[0].querySelector('.collector-name').textContent;
        const email = cells[0].querySelector('.collector-email').textContent.replace('📧 ', '');
        const cedula = cells[0].querySelector('.collector-cedula').textContent.replace('🆔 ', '');
        const stats = cells[1].textContent.trim().split('\n');
        const totalCredits = stats[0].trim().split(' ')[0];
        const activeCredits = stats[1].trim().split(' ')[0];
        const overdueCredits = stats[2].trim().split(' ')[0];
        const performance = cells[2].textContent.trim().split('\n');
        const totalCollected = performance[0].trim();
        const effectiveness = performance[2].trim();
        const date = cells[3].textContent.trim().split('\n')[0];
        
        csv += `"${name}","${email}","${cedula}","${totalCredits}","${activeCredits}","${overdueCredits}","${totalCollected}","${effectiveness}","${date}"\n`;
    });
    
    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'cobradores_impocred_' + new Date().toISOString().split('T')[0] + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'Este campo es obligatorio.';
    } else if (value) {
        switch (field.type) {
            case 'email':
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    isValid = false;
                    errorMessage = 'Por favor, ingrese un email válido.';
                }
                break;
        }
        
        if (field.name === 'cedula' && value.replace(/\D/g, '').length < 8) {
            isValid = false;
            errorMessage = 'La cédula debe tener al menos 8 dígitos.';
        }
    }
    
    if (!isValid) {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

function showFieldError(field, message) {
    clearFieldError(field);
    field.classList.add('error');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.color = '#e74c3c';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('error');
    const errorMessage = field.parentNode.querySelector('.error-message');
    if (errorMessage) {
        errorMessage.remove();
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>

<?php
$pageTitle = "Gestión de Clientes";
require_once '../includes/db.php';
require_once '../includes/functions.php';

$success = '';
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $cedula = sanitizeInput($_POST['cedula'] ?? '');
    
    // Validate inputs
    if (empty($name) || empty($address) || empty($phone) || empty($cedula)) {
        $error = 'Por favor, complete todos los campos obligatorios.';
    } elseif (!validatePhone($phone)) {
        $error = 'Por favor, ingrese un teléfono válido.';
    } elseif (!empty($email) && !validateEmail($email)) {
        $error = 'Por favor, ingrese un email válido.';
    } elseif (!validateCedula($cedula)) {
        $error = 'Por favor, ingrese una cédula válida.';
    } else {
        try {
            // Check if cedula already exists
            $stmt = executeQuery("SELECT id FROM clients WHERE cedula = ?", [$cedula]);
            if ($stmt && $stmt->fetch()) {
                $error = 'Ya existe un cliente con esta cédula.';
            } else {
                // Insert new client
                $stmt = executeQuery(
                    "INSERT INTO clients (name, address, phone, email, cedula) VALUES (?, ?, ?, ?, ?)",
                    [$name, $address, $phone, $email ?: null, $cedula]
                );
                
                if ($stmt) {
                    $success = 'Cliente registrado exitosamente.';
                    // Clear form
                    $name = $address = $phone = $email = $cedula = '';
                } else {
                    $error = 'Error al registrar el cliente.';
                }
            }
        } catch (Exception $e) {
            error_log("Client registration error: " . $e->getMessage());
            $error = 'Error del sistema. Por favor, intente nuevamente.';
        }
    }
}

// Get existing clients
try {
    $stmt = executeQuery(
        "SELECT c.*, COUNT(cr.id) as total_credits 
         FROM clients c 
         LEFT JOIN credits cr ON c.id = cr.client_id 
         GROUP BY c.id 
         ORDER BY c.created_at DESC"
    );
    $clients = $stmt ? $stmt->fetchAll() : [];
} catch (Exception $e) {
    error_log("Error loading clients: " . $e->getMessage());
    $clients = [];
}

require_once '../includes/header.php';
?>

<div class="row">
    <!-- Client Registration Form -->
    <div class="col col-lg-5">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title" style="color: #3498db;">
                    👥 Registrar Nuevo Cliente
                </h2>
                <p style="color: #7f8c8d; margin: 0;">
                    Complete la información del cliente
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
                        placeholder="Nombre completo del cliente"
                        required
                        autocomplete="name"
                    >
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
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">
                        📞 Teléfono *
                    </label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                        placeholder="809-555-0123"
                        required
                        autocomplete="tel"
                    >
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">
                        📧 Correo Electrónico
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($email ?? ''); ?>"
                        placeholder="cliente@correo.com"
                        autocomplete="email"
                    >
                    <small style="color: #7f8c8d; font-size: 0.875rem;">
                        Campo opcional
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="address" class="form-label">
                        🏠 Dirección *
                    </label>
                    <textarea 
                        id="address" 
                        name="address" 
                        class="form-textarea" 
                        rows="3"
                        placeholder="Dirección completa del cliente"
                        required
                    ><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-full">
                        ✅ Registrar Cliente
                    </button>
                </div>
            </form>
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
                <a href="manage_products.php" class="btn btn-secondary btn-full" style="margin-bottom: 0.5rem;">
                    📦 Gestionar Productos
                </a>
                <a href="manage_collectors.php" class="btn btn-secondary btn-full">
                    👨‍💼 Gestionar Cobradores
                </a>
            </div>
        </div>
    </div>
    
    <!-- Clients List -->
    <div class="col col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    📋 Clientes Registrados
                </h3>
                <p style="color: #7f8c8d; margin: 0;">
                    Total: <?php echo count($clients); ?> clientes
                </p>
            </div>
            
            <?php if (empty($clients)): ?>
                <div style="padding: 2rem; text-align: center; color: #7f8c8d;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">👥</div>
                    <h4>No hay clientes registrados</h4>
                    <p>Los clientes aparecerán aquí una vez que sean registrados.</p>
                </div>
            <?php else: ?>
                <!-- Search and Filter -->
                <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #eee;">
                    <div class="row">
                        <div class="col col-md-8">
                            <input 
                                type="text" 
                                id="searchClients" 
                                class="form-input" 
                                placeholder="🔍 Buscar por nombre, cédula o teléfono..."
                                onkeyup="filterClients()"
                            >
                        </div>
                        <div class="col col-md-4">
                            <button onclick="exportClients()" class="btn btn-secondary btn-full">
                                📊 Exportar Lista
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table" id="clientsTable">
                        <thead style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">
                            <tr>
                                <th>Cliente</th>
                                <th>Contacto</th>
                                <th>Créditos</th>
                                <th>Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clients as $client): ?>
                                <tr class="client-row">
                                    <td>
                                        <strong class="client-name"><?php echo htmlspecialchars($client['name']); ?></strong>
                                        <br>
                                        <small style="color: #7f8c8d;" class="client-cedula">
                                            🆔 <?php echo htmlspecialchars($client['cedula']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="client-phone">📞 <?php echo htmlspecialchars($client['phone']); ?></div>
                                        <?php if ($client['email']): ?>
                                            <small style="color: #7f8c8d;">
                                                📧 <?php echo htmlspecialchars($client['email']); ?>
                                            </small>
                                        <?php endif; ?>
                                        <br>
                                        <small style="color: #7f8c8d;">
                                            🏠 <?php echo htmlspecialchars(substr($client['address'], 0, 30)) . (strlen($client['address']) > 30 ? '...' : ''); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span style="font-weight: 600; color: <?php echo $client['total_credits'] > 0 ? '#27ae60' : '#7f8c8d'; ?>;">
                                            <?php echo $client['total_credits']; ?>
                                        </span>
                                        <br>
                                        <small style="color: #7f8c8d;">
                                            <?php echo $client['total_credits'] == 1 ? 'crédito' : 'créditos'; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo formatDate($client['created_at']); ?>
                                        <br>
                                        <small style="color: #7f8c8d;">
                                            <?php
                                            $days_ago = floor((time() - strtotime($client['created_at'])) / (60 * 60 * 24));
                                            echo $days_ago == 0 ? 'Hoy' : ($days_ago == 1 ? 'Ayer' : $days_ago . ' días');
                                            ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                                            <button 
                                                onclick="viewClient(<?php echo $client['id']; ?>)" 
                                                class="btn btn-primary" 
                                                style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                                                title="Ver detalles"
                                            >
                                                👁️
                                            </button>
                                            <button 
                                                onclick="editClient(<?php echo $client['id']; ?>)" 
                                                class="btn btn-secondary" 
                                                style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                                                title="Editar"
                                            >
                                                ✏️
                                            </button>
                                            <a 
                                                href="tel:<?php echo $client['phone']; ?>" 
                                                class="btn btn-success" 
                                                style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                                                title="Llamar"
                                            >
                                                📞
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Client management specific styles */
.client-row:hover {
    background-color: #f8f9fa;
}

.form-input:focus,
.form-textarea:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
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
    
    .client-row td {
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
// Client management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Format phone and cedula inputs
    const phoneInput = document.getElementById('phone');
    const cedulaInput = document.getElementById('cedula');
    
    // Format cedula (numbers only)
    cedulaInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
    
    // Format phone number
    phoneInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length >= 10) {
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
        }
        this.value = value;
    });
    
    // Real-time validation
    const form = document.querySelector('form[data-validate]');
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            clearFieldError(this);
        });
    });
});

function filterClients() {
    const searchTerm = document.getElementById('searchClients').value.toLowerCase();
    const rows = document.querySelectorAll('.client-row');
    
    rows.forEach(row => {
        const name = row.querySelector('.client-name').textContent.toLowerCase();
        const cedula = row.querySelector('.client-cedula').textContent.toLowerCase();
        const phone = row.querySelector('.client-phone').textContent.toLowerCase();
        
        const matches = name.includes(searchTerm) || 
                       cedula.includes(searchTerm) || 
                       phone.includes(searchTerm);
        
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

function viewClient(clientId) {
    // Implementation for viewing client details
    alert('Función de ver detalles del cliente (ID: ' + clientId + ') - Por implementar');
}

function editClient(clientId) {
    // Implementation for editing client
    alert('Función de editar cliente (ID: ' + clientId + ') - Por implementar');
}

function exportClients() {
    // Simple CSV export
    const table = document.getElementById('clientsTable');
    const rows = table.querySelectorAll('tr:not([style*="display: none"])');
    let csv = 'Nombre,Cédula,Teléfono,Email,Dirección,Créditos,Fecha Registro\n';
    
    rows.forEach((row, index) => {
        if (index === 0) return; // Skip header
        
        const cells = row.querySelectorAll('td');
        if (cells.length === 0) return;
        
        const name = cells[0].querySelector('.client-name').textContent;
        const cedula = cells[0].querySelector('.client-cedula').textContent.replace('🆔 ', '');
        const phone = cells[1].querySelector('.client-phone').textContent.replace('📞 ', '');
        const email = cells[1].querySelector('small') ? 
                     cells[1].querySelector('small').textContent.replace('📧 ', '') : '';
        const address = cells[1].querySelectorAll('small')[1] ? 
                       cells[1].querySelectorAll('small')[1].textContent.replace('🏠 ', '') : '';
        const credits = cells[2].textContent.trim().split('\n')[0];
        const date = cells[3].textContent.trim().split('\n')[0];
        
        csv += `"${name}","${cedula}","${phone}","${email}","${address}","${credits}","${date}"\n`;
    });
    
    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'clientes_impocred_' + new Date().toISOString().split('T')[0] + '.csv';
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
            case 'tel':
                if (value.replace(/\D/g, '').length < 10) {
                    isValid = false;
                    errorMessage = 'El teléfono debe tener al menos 10 dígitos.';
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

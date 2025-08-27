<?php
$pageTitle = "Gestión de Productos";
require_once '../includes/db.php';
require_once '../includes/functions.php';

$success = '';
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = sanitizeInput($_POST['product_name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    
    // Validate inputs
    if (empty($product_name) || $price <= 0) {
        $error = 'Por favor, complete todos los campos obligatorios.';
    } else {
        try {
            // Check if product name already exists
            $stmt = executeQuery("SELECT id FROM products WHERE product_name = ?", [$product_name]);
            if ($stmt && $stmt->fetch()) {
                $error = 'Ya existe un producto con este nombre.';
            } else {
                // Insert new product
                $stmt = executeQuery(
                    "INSERT INTO products (product_name, description, price) VALUES (?, ?, ?)",
                    [$product_name, $description ?: null, $price]
                );
                
                if ($stmt) {
                    $success = 'Producto registrado exitosamente.';
                    // Clear form
                    $product_name = $description = '';
                    $price = 0;
                } else {
                    $error = 'Error al registrar el producto.';
                }
            }
        } catch (Exception $e) {
            error_log("Product registration error: " . $e->getMessage());
            $error = 'Error del sistema. Por favor, intente nuevamente.';
        }
    }
}

// Get existing products
try {
    $stmt = executeQuery(
        "SELECT p.*, COUNT(c.id) as total_credits,
                COALESCE(SUM(CASE WHEN c.status = 'active' THEN 1 ELSE 0 END), 0) as active_credits
         FROM products p 
         LEFT JOIN credits c ON p.id = c.product_id 
         GROUP BY p.id 
         ORDER BY p.created_at DESC"
    );
    $products = $stmt ? $stmt->fetchAll() : [];
} catch (Exception $e) {
    error_log("Error loading products: " . $e->getMessage());
    $products = [];
}

require_once '../includes/header.php';
?>

<div class="row">
    <!-- Product Registration Form -->
    <div class="col col-lg-5">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title" style="color: #27ae60;">
                    📦 Registrar Nuevo Producto
                </h2>
                <p style="color: #7f8c8d; margin: 0;">
                    Complete la información del producto
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
                    <label for="product_name" class="form-label">
                        📦 Nombre del Producto *
                    </label>
                    <input 
                        type="text" 
                        id="product_name" 
                        name="product_name" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($product_name ?? ''); ?>"
                        placeholder="Ej: Televisor 32 pulgadas"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="price" class="form-label">
                        💰 Precio *
                    </label>
                    <input 
                        type="number" 
                        id="price" 
                        name="price" 
                        class="form-input" 
                        value="<?php echo ($price ?? 0) > 0 ? $price : ''; ?>"
                        placeholder="0.00"
                        step="0.01"
                        min="0.01"
                        required
                    >
                    <small style="color: #7f8c8d; font-size: 0.875rem;">
                        Precio en dólares (USD)
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="description" class="form-label">
                        📝 Descripción
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        class="form-textarea" 
                        rows="4"
                        placeholder="Descripción detallada del producto (opcional)..."
                    ><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    <small style="color: #7f8c8d; font-size: 0.875rem;">
                        Campo opcional - Incluya características, modelo, etc.
                    </small>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-success btn-full">
                        ✅ Registrar Producto
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Product Categories Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    💡 Sugerencias de Productos
                </h3>
            </div>
            
            <div style="padding: 0 1.5rem 1.5rem;">
                <div style="margin-bottom: 1rem;">
                    <h4 style="color: #2c3e50; font-size: 1rem; margin-bottom: 0.5rem;">
                        🏠 Electrodomésticos
                    </h4>
                    <ul style="color: #7f8c8d; font-size: 0.9rem; margin: 0; padding-left: 1.5rem;">
                        <li>Refrigeradoras</li>
                        <li>Lavadoras</li>
                        <li>Estufas</li>
                        <li>Microondas</li>
                    </ul>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <h4 style="color: #2c3e50; font-size: 1rem; margin-bottom: 0.5rem;">
                        📺 Electrónicos
                    </h4>
                    <ul style="color: #7f8c8d; font-size: 0.9rem; margin: 0; padding-left: 1.5rem;">
                        <li>Televisores</li>
                        <li>Equipos de sonido</li>
                        <li>Computadoras</li>
                        <li>Teléfonos</li>
                    </ul>
                </div>
                
                <div>
                    <h4 style="color: #2c3e50; font-size: 1rem; margin-bottom: 0.5rem;">
                        🛋️ Muebles
                    </h4>
                    <ul style="color: #7f8c8d; font-size: 0.9rem; margin: 0; padding-left: 1.5rem;">
                        <li>Salas</li>
                        <li>Comedores</li>
                        <li>Camas</li>
                        <li>Roperos</li>
                    </ul>
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
                <a href="manage_collectors.php" class="btn btn-secondary btn-full">
                    👨‍💼 Gestionar Cobradores
                </a>
            </div>
        </div>
    </div>
    
    <!-- Products List -->
    <div class="col col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    📋 Productos Registrados
                </h3>
                <p style="color: #7f8c8d; margin: 0;">
                    Total: <?php echo count($products); ?> productos
                </p>
            </div>
            
            <?php if (empty($products)): ?>
                <div style="padding: 2rem; text-align: center; color: #7f8c8d;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📦</div>
                    <h4>No hay productos registrados</h4>
                    <p>Los productos aparecerán aquí una vez que sean registrados.</p>
                </div>
            <?php else: ?>
                <!-- Search and Filter -->
                <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #eee;">
                    <div class="row">
                        <div class="col col-md-6">
                            <input 
                                type="text" 
                                id="searchProducts" 
                                class="form-input" 
                                placeholder="🔍 Buscar productos..."
                                onkeyup="filterProducts()"
                            >
                        </div>
                        <div class="col col-md-3">
                            <select id="priceFilter" class="form-select" onchange="filterProducts()">
                                <option value="">Todos los precios</option>
                                <option value="0-100">$0 - $100</option>
                                <option value="100-300">$100 - $300</option>
                                <option value="300-500">$300 - $500</option>
                                <option value="500+">$500+</option>
                            </select>
                        </div>
                        <div class="col col-md-3">
                            <button onclick="exportProducts()" class="btn btn-secondary btn-full">
                                📊 Exportar
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table" id="productsTable">
                        <thead style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">
                            <tr>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Créditos</th>
                                <th>Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr class="product-row" data-price="<?php echo $product['price']; ?>">
                                    <td>
                                        <strong class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></strong>
                                        <?php if ($product['description']): ?>
                                            <br>
                                            <small style="color: #7f8c8d;" class="product-description">
                                                <?php echo htmlspecialchars(substr($product['description'], 0, 50)) . (strlen($product['description']) > 50 ? '...' : ''); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong style="color: #27ae60; font-size: 1.1rem;">
                                            <?php echo formatCurrency($product['price']); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <div>
                                            <span style="font-weight: 600; color: #2c3e50;">
                                                <?php echo $product['total_credits']; ?>
                                            </span>
                                            <small style="color: #7f8c8d;"> total</small>
                                        </div>
                                        <div>
                                            <span style="font-weight: 600; color: #27ae60;">
                                                <?php echo $product['active_credits']; ?>
                                            </span>
                                            <small style="color: #7f8c8d;"> activos</small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo formatDate($product['created_at']); ?>
                                        <br>
                                        <small style="color: #7f8c8d;">
                                            <?php
                                            $days_ago = floor((time() - strtotime($product['created_at'])) / (60 * 60 * 24));
                                            echo $days_ago == 0 ? 'Hoy' : ($days_ago == 1 ? 'Ayer' : $days_ago . ' días');
                                            ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                                            <button 
                                                onclick="viewProduct(<?php echo $product['id']; ?>)" 
                                                class="btn btn-primary" 
                                                style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                                                title="Ver detalles"
                                            >
                                                👁️
                                            </button>
                                            <button 
                                                onclick="editProduct(<?php echo $product['id']; ?>)" 
                                                class="btn btn-secondary" 
                                                style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                                                title="Editar"
                                            >
                                                ✏️
                                            </button>
                                            <button 
                                                onclick="createCredit(<?php echo $product['id']; ?>)" 
                                                class="btn btn-success" 
                                                style="font-size: 0.75rem; padding: 0.25rem 0.5rem;"
                                                title="Crear crédito"
                                            >
                                                💳
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Statistics Summary -->
                <div style="padding: 1rem 1.5rem; background: #f8f9fa; border-top: 1px solid #eee;">
                    <div class="row">
                        <div class="col col-md-3 text-center">
                            <strong style="color: #2c3e50;">
                                <?php echo count($products); ?>
                            </strong>
                            <br>
                            <small style="color: #7f8c8d;">Total Productos</small>
                        </div>
                        <div class="col col-md-3 text-center">
                            <strong style="color: #27ae60;">
                                <?php 
                                $avg_price = count($products) > 0 ? array_sum(array_column($products, 'price')) / count($products) : 0;
                                echo formatCurrency($avg_price); 
                                ?>
                            </strong>
                            <br>
                            <small style="color: #7f8c8d;">Precio Promedio</small>
                        </div>
                        <div class="col col-md-3 text-center">
                            <strong style="color: #3498db;">
                                <?php echo array_sum(array_column($products, 'total_credits')); ?>
                            </strong>
                            <br>
                            <small style="color: #7f8c8d;">Total Créditos</small>
                        </div>
                        <div class="col col-md-3 text-center">
                            <strong style="color: #e74c3c;">
                                <?php echo array_sum(array_column($products, 'active_credits')); ?>
                            </strong>
                            <br>
                            <small style="color: #7f8c8d;">Créditos Activos</small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Product management specific styles */
.product-row:hover {
    background-color: #f8f9fa;
}

.form-input:focus,
.form-textarea:focus,
.form-select:focus {
    border-color: #27ae60;
    box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.1);
}

/* Price highlighting */
.price-highlight {
    background: linear-gradient(45deg, #27ae60, #2ecc71);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: bold;
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
    
    .product-row td {
        padding: 0.75rem 0.5rem;
    }
    
    .btn {
        font-size: 0.875rem;
        padding: 0.5rem;
    }
    
    .row .col {
        margin-bottom: 0.5rem;
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

/* Statistics cards */
.stats-summary {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
    padding: 1rem;
}
</style>

<script>
// Product management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Format price input
    const priceInput = document.getElementById('price');
    
    priceInput.addEventListener('input', function() {
        let value = parseFloat(this.value);
        if (value < 0) {
            this.value = '';
        }
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
    
    // Auto-suggest product names
    const productNameInput = document.getElementById('product_name');
    const suggestions = [
        'Televisor LED 32"', 'Televisor LED 43"', 'Televisor LED 55"',
        'Refrigeradora 18 pies', 'Refrigeradora 21 pies',
        'Lavadora 15 kg', 'Lavadora 18 kg',
        'Estufa 4 hornillas', 'Estufa 6 hornillas',
        'Microondas 1.1 pies', 'Microondas 1.6 pies',
        'Sala 3 piezas', 'Sala 5 piezas',
        'Comedor 4 sillas', 'Comedor 6 sillas',
        'Cama Queen', 'Cama King',
        'Ropero 3 puertas', 'Ropero 4 puertas'
    ];
    
    productNameInput.addEventListener('input', function() {
        const value = this.value.toLowerCase();
        if (value.length > 2) {
            const matches = suggestions.filter(s => s.toLowerCase().includes(value));
            // Simple autocomplete implementation
            if (matches.length > 0 && !matches.includes(this.value)) {
                // Could implement dropdown here
            }
        }
    });
});

function filterProducts() {
    const searchTerm = document.getElementById('searchProducts').value.toLowerCase();
    const priceFilter = document.getElementById('priceFilter').value;
    const rows = document.querySelectorAll('.product-row');
    
    rows.forEach(row => {
        const name = row.querySelector('.product-name').textContent.toLowerCase();
        const description = row.querySelector('.product-description')?.textContent.toLowerCase() || '';
        const price = parseFloat(row.dataset.price);
        
        // Text search
        const textMatches = name.includes(searchTerm) || description.includes(searchTerm);
        
        // Price filter
        let priceMatches = true;
        if (priceFilter) {
            if (priceFilter === '0-100') {
                priceMatches = price >= 0 && price <= 100;
            } else if (priceFilter === '100-300') {
                priceMatches = price > 100 && price <= 300;
            } else if (priceFilter === '300-500') {
                priceMatches = price > 300 && price <= 500;
            } else if (priceFilter === '500+') {
                priceMatches = price > 500;
            }
        }
        
        const matches = textMatches && priceMatches;
        row.style.display = matches ? '' : 'none';
        
        // Highlight matching text
        if (searchTerm && matches) {
            highlightText(row, searchTerm);
        } else {
            removeHighlight(row);
        }
    });
    
    updateVisibleCount();
}

function updateVisibleCount() {
    const visibleRows = document.querySelectorAll('.product-row:not([style*="display: none"])');
    const totalRows = document.querySelectorAll('.product-row');
    
    // Update header count if exists
    const headerText = document.querySelector('.card-header p');
    if (headerText) {
        headerText.textContent = `Mostrando: ${visibleRows.length} de ${totalRows.length} productos`;
    }
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

function viewProduct(productId) {
    alert('Función de ver detalles del producto (ID: ' + productId + ') - Por implementar');
}

function editProduct(productId) {
    alert('Función de editar producto (ID: ' + productId + ') - Por implementar');
}

function createCredit(productId) {
    alert('Función de crear crédito para producto (ID: ' + productId + ') - Por implementar');
}

function exportProducts() {
    const table = document.getElementById('productsTable');
    const rows = table.querySelectorAll('tr:not([style*="display: none"])');
    let csv = 'Nombre,Descripción,Precio,Total Créditos,Créditos Activos,Fecha Registro\n';
    
    rows.forEach((row, index) => {
        if (index === 0) return; // Skip header
        
        const cells = row.querySelectorAll('td');
        if (cells.length === 0) return;
        
        const name = cells[0].querySelector('.product-name').textContent;
        const description = cells[0].querySelector('.product-description')?.textContent || '';
        const price = cells[1].textContent.replace('$', '').trim();
        const totalCredits = cells[2].textContent.trim().split(' ')[0];
        const activeCredits = cells[2].textContent.trim().split('\n')[1]?.trim().split(' ')[0] || '0';
        const date = cells[3].textContent.trim().split('\n')[0];
        
        csv += `"${name}","${description}","${price}","${totalCredits}","${activeCredits}","${date}"\n`;
    });
    
    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'productos_impocred_' + new Date().toISOString().split('T')[0] + '.csv';
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
        if (field.type === 'number' && field.name === 'price') {
            const price = parseFloat(value);
            if (isNaN(price) || price <= 0) {
                isValid = false;
                errorMessage = 'El precio debe ser mayor a 0.';
            }
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

<?php
$pageTitle = "Acceso Cobradores";
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Start session
startSecureSession();

// Redirect if already logged in
if (isCollectorLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$email = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $cedula = sanitizeInput($_POST['cedula'] ?? '');
    
    // Validate inputs
    if (empty($email) || empty($cedula)) {
        $error = 'Por favor, complete todos los campos.';
    } elseif (!validateEmail($email)) {
        $error = 'Por favor, ingrese un email válido.';
    } elseif (!validateCedula($cedula)) {
        $error = 'Por favor, ingrese una cédula válida.';
    } else {
        // Check credentials
        try {
            $stmt = executeQuery(
                "SELECT id, name, email, cedula, password_hash FROM collectors WHERE email = ? AND cedula = ?",
                [$email, $cedula]
            );
            
            if ($stmt && $collector = $stmt->fetch()) {
                // Verify password (cedula is used as password)
                if (verifyPassword($cedula, $collector['password_hash'])) {
                    // Login successful
                    session_regenerate_id(true);
                    $_SESSION['collector_id'] = $collector['id'];
                    $_SESSION['collector_name'] = $collector['name'];
                    $_SESSION['collector_email'] = $collector['email'];
                    $_SESSION['login_time'] = time();
                    
                    // Redirect to dashboard
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error = 'Credenciales incorrectas. Verifique su email y cédula.';
                }
            } else {
                $error = 'Credenciales incorrectas. Verifique su email y cédula.';
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'Error del sistema. Por favor, intente nuevamente.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col col-md-6" style="margin: 0 auto;">
        <div class="card">
            <div class="card-header text-center">
                <h2 class="card-title" style="color: #3498db; margin-bottom: 0.5rem;">
                    🔐 Acceso para Cobradores
                </h2>
                <p style="color: #7f8c8d; margin: 0;">
                    Ingrese sus credenciales para acceder al sistema
                </p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" data-validate novalidate>
                <div class="form-group">
                    <label for="email" class="form-label">
                        📧 Correo Electrónico
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($email); ?>"
                        placeholder="ejemplo@correo.com"
                        required
                        autocomplete="email"
                    >
                </div>
                
                <div class="form-group">
                    <label for="cedula" class="form-label">
                        🆔 Número de Cédula
                    </label>
                    <input 
                        type="text" 
                        id="cedula" 
                        name="cedula" 
                        class="form-input" 
                        placeholder="12345678"
                        required
                        autocomplete="off"
                        pattern="[0-9]{8,}"
                        title="La cédula debe tener al menos 8 dígitos"
                    >
                    <small style="color: #7f8c8d; font-size: 0.875rem; margin-top: 0.25rem; display: block;">
                        Su cédula es su contraseña de acceso
                    </small>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-full">
                        Iniciar Sesión
                    </button>
                </div>
            </form>
            
            <div style="text-align: center; padding-top: 1rem; border-top: 1px solid #eee; margin-top: 1rem;">
                <p style="color: #7f8c8d; font-size: 0.875rem; margin-bottom: 1rem;">
                    ¿Problemas para acceder?
                </p>
                <a href="../" class="btn btn-secondary" style="margin-right: 0.5rem;">
                    ← Volver al Inicio
                </a>
                <a href="../admin/" class="btn btn-secondary">
                    Contactar Administrador
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Information Section -->
<div class="row" style="margin-top: 2rem;">
    <div class="col">
        <div class="card" style="background: #f8f9fa; border: none;">
            <div class="card-header text-center">
                <h3 style="color: #2c3e50; margin-bottom: 1rem;">
                    ℹ️ Información para Cobradores
                </h3>
            </div>
            
            <div class="row">
                <div class="col col-md-4 mb-3">
                    <div style="text-align: center; padding: 1rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem; color: #3498db;">📊</div>
                        <h4 style="font-size: 1.1rem; font-weight: 600; color: #2c3e50; margin-bottom: 0.5rem;">
                            Dashboard Personalizado
                        </h4>
                        <p style="color: #7f8c8d; font-size: 0.9rem;">
                            Vea todos sus créditos asignados y el estado de los pagos en tiempo real.
                        </p>
                    </div>
                </div>
                
                <div class="col col-md-4 mb-3">
                    <div style="text-align: center; padding: 1rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem; color: #27ae60;">💰</div>
                        <h4 style="font-size: 1.1rem; font-weight: 600; color: #2c3e50; margin-bottom: 0.5rem;">
                            Registro de Pagos
                        </h4>
                        <p style="color: #7f8c8d; font-size: 0.9rem;">
                            Registre pagos de clientes con cálculo automático de penalizaciones e intereses.
                        </p>
                    </div>
                </div>
                
                <div class="col col-md-4 mb-3">
                    <div style="text-align: center; padding: 1rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem; color: #e74c3c;">📱</div>
                        <h4 style="font-size: 1.1rem; font-weight: 600; color: #2c3e50; margin-bottom: 0.5rem;">
                            Acceso Móvil
                        </h4>
                        <p style="color: #7f8c8d; font-size: 0.9rem;">
                            Sistema optimizado para funcionar perfectamente en su teléfono móvil.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Login page specific styles */
.form-input:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-input.error {
    border-color: #e74c3c;
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}

.error-message {
    color: #e74c3c;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
}

/* Mobile optimizations */
@media (max-width: 767px) {
    .card {
        margin: 0 0.5rem;
    }
    
    .btn {
        padding: 1rem;
        font-size: 1rem;
    }
    
    .form-input {
        padding: 1rem 0.75rem;
        font-size: 1rem;
    }
}

/* Loading animation */
.btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.spinner {
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    animation: spin 1s linear infinite;
    display: inline-block;
    margin-right: 0.5rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Accessibility improvements */
.form-input:focus {
    outline: 2px solid #3498db;
    outline-offset: 2px;
}

/* Touch improvements for mobile */
@media (pointer: coarse) {
    .form-input {
        min-height: 48px;
    }
    
    .btn {
        min-height: 48px;
    }
}
</style>

<script>
// Additional client-side validation for login form
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[data-validate]');
    const emailInput = document.getElementById('email');
    const cedulaInput = document.getElementById('cedula');
    
    // Format cedula input (numbers only)
    cedulaInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
    
    // Real-time email validation
    emailInput.addEventListener('blur', function() {
        const email = this.value.trim();
        if (email && !validateEmail(email)) {
            showFieldError(this, 'Por favor, ingrese un email válido.');
        }
    });
    
    // Real-time cedula validation
    cedulaInput.addEventListener('blur', function() {
        const cedula = this.value.trim();
        if (cedula && cedula.length < 8) {
            showFieldError(this, 'La cédula debe tener al menos 8 dígitos.');
        }
    });
    
    // Clear errors on input
    [emailInput, cedulaInput].forEach(input => {
        input.addEventListener('input', function() {
            clearFieldError(this);
        });
    });
    
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function showFieldError(field, message) {
        clearFieldError(field);
        field.classList.add('error');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
    
    function clearFieldError(field) {
        field.classList.remove('error');
        const errorMessage = field.parentNode.querySelector('.error-message');
        if (errorMessage) {
            errorMessage.remove();
        }
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>

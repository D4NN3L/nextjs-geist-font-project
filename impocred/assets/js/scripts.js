/**
 * Impocred Credit Management System - JavaScript
 * Client-side validations and interactive functionality
 * Mobile-friendly with touch support
 */

// DOM Content Loaded Event
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Initialize application
 */
function initializeApp() {
    // Initialize form validations
    initFormValidations();
    
    // Initialize mobile menu if exists
    initMobileMenu();
    
    // Initialize tooltips and interactive elements
    initInteractiveElements();
    
    // Initialize auto-calculations
    initCalculations();
    
    // Initialize responsive tables
    initResponsiveTables();
}

/**
 * Form validation initialization
 */
function initFormValidations() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        // Real-time validation on input
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
        
        // Form submission validation
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                showAlert('Por favor, corrija los errores en el formulario.', 'error');
            }
        });
    });
}

/**
 * Validate individual field
 */
function validateField(field) {
    const value = field.value.trim();
    const fieldName = field.name;
    const fieldType = field.type;
    let isValid = true;
    let errorMessage = '';
    
    // Clear previous errors
    clearFieldError(field);
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'Este campo es obligatorio.';
    }
    
    // Specific field validations
    if (value && isValid) {
        switch (fieldType) {
            case 'email':
                if (!validateEmail(value)) {
                    isValid = false;
                    errorMessage = 'Por favor, ingrese un email válido.';
                }
                break;
                
            case 'tel':
                if (!validatePhone(value)) {
                    isValid = false;
                    errorMessage = 'Por favor, ingrese un teléfono válido (mínimo 10 dígitos).';
                }
                break;
                
            case 'number':
                if (isNaN(value) || parseFloat(value) < 0) {
                    isValid = false;
                    errorMessage = 'Por favor, ingrese un número válido.';
                }
                break;
        }
        
        // Custom validations based on field name
        switch (fieldName) {
            case 'cedula':
                if (!validateCedula(value)) {
                    isValid = false;
                    errorMessage = 'La cédula debe tener al menos 8 dígitos.';
                }
                break;
                
            case 'price':
            case 'amount':
                if (parseFloat(value) <= 0) {
                    isValid = false;
                    errorMessage = 'El monto debe ser mayor a 0.';
                }
                break;
        }
    }
    
    // Show error if validation failed
    if (!isValid) {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

/**
 * Validate entire form
 */
function validateForm(form) {
    const fields = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isFormValid = true;
    
    fields.forEach(field => {
        if (!validateField(field)) {
            isFormValid = false;
        }
    });
    
    return isFormValid;
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    field.classList.add('error');
    
    // Remove existing error message
    const existingError = field.parentNode.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
    
    // Add new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.color = '#e74c3c';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    
    field.parentNode.appendChild(errorDiv);
}

/**
 * Clear field error
 */
function clearFieldError(field) {
    field.classList.remove('error');
    const errorMessage = field.parentNode.querySelector('.error-message');
    if (errorMessage) {
        errorMessage.remove();
    }
}

/**
 * Email validation
 */
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Phone validation
 */
function validatePhone(phone) {
    const phoneDigits = phone.replace(/\D/g, '');
    return phoneDigits.length >= 10;
}

/**
 * Cedula validation
 */
function validateCedula(cedula) {
    const cedulaDigits = cedula.replace(/\D/g, '');
    return cedulaDigits.length >= 8;
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert-dynamic');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dynamic`;
    alertDiv.textContent = message;
    
    // Add close button
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '×';
    closeBtn.style.cssText = 'float: right; background: none; border: none; font-size: 1.2rem; cursor: pointer; margin-left: 1rem;';
    closeBtn.onclick = () => alertDiv.remove();
    alertDiv.appendChild(closeBtn);
    
    // Insert at top of main content
    const mainContent = document.querySelector('.main-content') || document.body;
    mainContent.insertBefore(alertDiv, mainContent.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

/**
 * Initialize mobile menu
 */
function initMobileMenu() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            this.classList.toggle('active');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!menuToggle.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
                menuToggle.classList.remove('active');
            }
        });
    }
}

/**
 * Initialize interactive elements
 */
function initInteractiveElements() {
    // Add loading state to buttons
    const buttons = document.querySelectorAll('button[type="submit"], .btn-submit');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            if (this.form && validateForm(this.form)) {
                showButtonLoading(this);
            }
        });
    });
    
    // Initialize confirmation dialogs
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Show button loading state
 */
function showButtonLoading(button) {
    const originalText = button.textContent;
    button.disabled = true;
    button.innerHTML = '<span class="spinner"></span> Procesando...';
    
    // Reset after 10 seconds (fallback)
    setTimeout(() => {
        button.disabled = false;
        button.textContent = originalText;
    }, 10000);
}

/**
 * Initialize calculations
 */
function initCalculations() {
    // Auto-calculate due date when purchase date changes
    const purchaseDateInputs = document.querySelectorAll('input[name="purchase_date"]');
    purchaseDateInputs.forEach(input => {
        input.addEventListener('change', function() {
            calculateDueDate(this.value);
        });
    });
    
    // Auto-calculate penalties and interest
    const paymentDateInputs = document.querySelectorAll('input[name="payment_date"]');
    paymentDateInputs.forEach(input => {
        input.addEventListener('change', function() {
            calculatePaymentDetails();
        });
    });
}

/**
 * Calculate due date (3 months from purchase date)
 */
function calculateDueDate(purchaseDate) {
    if (!purchaseDate) return;
    
    const date = new Date(purchaseDate);
    date.setMonth(date.getMonth() + 3);
    
    const dueDateInput = document.querySelector('input[name="due_date"]');
    if (dueDateInput) {
        dueDateInput.value = date.toISOString().split('T')[0];
    }
    
    // Update display if exists
    const dueDateDisplay = document.querySelector('.due-date-display');
    if (dueDateDisplay) {
        dueDateDisplay.textContent = formatDate(date);
    }
}

/**
 * Calculate payment details (penalties and interest)
 */
function calculatePaymentDetails() {
    const paymentDate = document.querySelector('input[name="payment_date"]')?.value;
    const dueDate = document.querySelector('input[name="due_date"]')?.value;
    const productPrice = document.querySelector('input[name="product_price"]')?.value;
    
    if (!paymentDate || !dueDate || !productPrice) return;
    
    // Calculate penalty
    const penalty = calculatePenalty(dueDate, paymentDate);
    const penaltyInput = document.querySelector('input[name="penalty"]');
    if (penaltyInput) {
        penaltyInput.value = penalty.toFixed(2);
    }
    
    // Calculate interest
    const interest = calculateInterest(dueDate, paymentDate, parseFloat(productPrice));
    const interestInput = document.querySelector('input[name="interest"]');
    if (interestInput) {
        interestInput.value = interest.toFixed(2);
    }
    
    // Calculate total
    const amount = parseFloat(document.querySelector('input[name="amount"]')?.value || 0);
    const total = amount + penalty + interest;
    const totalInput = document.querySelector('input[name="total_paid"]');
    if (totalInput) {
        totalInput.value = total.toFixed(2);
    }
}

/**
 * Calculate penalty (client-side version)
 */
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

/**
 * Calculate interest (client-side version)
 */
function calculateInterest(dueDate, currentDate, productPrice) {
    const due = new Date(dueDate);
    const current = new Date(currentDate);
    
    if (current <= due) return 0;
    
    const diffTime = Math.abs(current - due);
    const diffMonths = Math.ceil(diffTime / (1000 * 60 * 60 * 24 * 30));
    
    return productPrice * 0.075 * diffMonths;
}

/**
 * Format date for display
 */
function formatDate(date) {
    return new Intl.DateTimeFormat('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }).format(date);
}

/**
 * Initialize responsive tables
 */
function initResponsiveTables() {
    const tables = document.querySelectorAll('.table');
    tables.forEach(table => {
        // Add responsive wrapper if not exists
        if (!table.parentNode.classList.contains('table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });
}

/**
 * Utility function to format currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

/**
 * Utility function to debounce function calls
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Touch-friendly interactions for mobile
 */
if ('ontouchstart' in window) {
    // Add touch class to body for CSS targeting
    document.body.classList.add('touch-device');
    
    // Improve button interactions on touch devices
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('touchstart', function() {
            this.classList.add('touch-active');
        });
        
        button.addEventListener('touchend', function() {
            setTimeout(() => {
                this.classList.remove('touch-active');
            }, 150);
        });
    });
}

// Export functions for global use
window.ImpocredApp = {
    showAlert,
    validateEmail,
    validatePhone,
    validateCedula,
    formatCurrency,
    calculateDueDate,
    calculatePaymentDetails
};

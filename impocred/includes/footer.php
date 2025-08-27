</div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col text-center">
                    <p>&copy; <?php echo date('Y'); ?> Impocred - Sistema de Gestión de Créditos. Todos los derechos reservados.</p>
                    <p style="font-size: 0.875rem; margin-top: 0.5rem; opacity: 0.8;">
                        Desarrollado con tecnología moderna y diseño responsivo
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="/impocred/assets/js/scripts.js"></script>
    
    <!-- Additional JavaScript for specific pages -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo htmlspecialchars($js); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline JavaScript for specific pages -->
    <?php if (isset($inlineJS)): ?>
        <script>
            <?php echo $inlineJS; ?>
        </script>
    <?php endif; ?>
    
    <script>
        // Initialize mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const navMenu = document.querySelector('.nav-menu');
            
            if (menuToggle && navMenu) {
                menuToggle.addEventListener('click', function() {
                    navMenu.classList.toggle('active');
                    
                    // Update aria-expanded for accessibility
                    const isExpanded = navMenu.classList.contains('active');
                    this.setAttribute('aria-expanded', isExpanded);
                    
                    // Change icon
                    this.innerHTML = isExpanded ? '✕' : '☰';
                });
                
                // Close menu when clicking outside
                document.addEventListener('click', function(e) {
                    if (!menuToggle.contains(e.target) && !navMenu.contains(e.target)) {
                        navMenu.classList.remove('active');
                        menuToggle.setAttribute('aria-expanded', 'false');
                        menuToggle.innerHTML = '☰';
                    }
                });
                
                // Close menu when pressing Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && navMenu.classList.contains('active')) {
                        navMenu.classList.remove('active');
                        menuToggle.setAttribute('aria-expanded', 'false');
                        menuToggle.innerHTML = '☰';
                        menuToggle.focus();
                    }
                });
            }
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (!alert.classList.contains('alert-permanent')) {
                    setTimeout(() => {
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-10px)';
                        setTimeout(() => {
                            if (alert.parentNode) {
                                alert.remove();
                            }
                        }, 300);
                    }, 5000);
                }
            });
            
            // Add smooth scrolling for anchor links
            const anchorLinks = document.querySelectorAll('a[href^="#"]');
            anchorLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const targetId = this.getAttribute('href').substring(1);
                    const targetElement = document.getElementById(targetId);
                    
                    if (targetElement) {
                        e.preventDefault();
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Add loading states to form submissions
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const submitButton = this.querySelector('button[type="submit"], input[type="submit"]');
                    if (submitButton && !submitButton.disabled) {
                        const originalText = submitButton.textContent || submitButton.value;
                        submitButton.disabled = true;
                        
                        if (submitButton.tagName === 'BUTTON') {
                            submitButton.innerHTML = '<span class="spinner"></span> Procesando...';
                        } else {
                            submitButton.value = 'Procesando...';
                        }
                        
                        // Reset after 30 seconds as fallback
                        setTimeout(() => {
                            submitButton.disabled = false;
                            if (submitButton.tagName === 'BUTTON') {
                                submitButton.textContent = originalText;
                            } else {
                                submitButton.value = originalText;
                            }
                        }, 30000);
                    }
                });
            });
            
            // Improve accessibility for keyboard navigation
            const focusableElements = document.querySelectorAll(
                'a, button, input, textarea, select, [tabindex]:not([tabindex="-1"])'
            );
            
            focusableElements.forEach(element => {
                element.addEventListener('focus', function() {
                    this.style.outline = '2px solid #3498db';
                    this.style.outlineOffset = '2px';
                });
                
                element.addEventListener('blur', function() {
                    this.style.outline = '';
                    this.style.outlineOffset = '';
                });
            });
            
            // Add touch feedback for mobile devices
            if ('ontouchstart' in window) {
                const touchElements = document.querySelectorAll('.btn, .card, .table tbody tr');
                touchElements.forEach(element => {
                    element.addEventListener('touchstart', function() {
                        this.style.transform = 'scale(0.98)';
                        this.style.transition = 'transform 0.1s ease';
                    });
                    
                    element.addEventListener('touchend', function() {
                        setTimeout(() => {
                            this.style.transform = '';
                        }, 100);
                    });
                });
            }
            
            // Initialize tooltips (simple implementation)
            const tooltipElements = document.querySelectorAll('[data-tooltip]');
            tooltipElements.forEach(element => {
                element.addEventListener('mouseenter', function() {
                    const tooltipText = this.getAttribute('data-tooltip');
                    const tooltip = document.createElement('div');
                    tooltip.className = 'tooltip';
                    tooltip.textContent = tooltipText;
                    tooltip.style.cssText = `
                        position: absolute;
                        background: #333;
                        color: white;
                        padding: 0.5rem;
                        border-radius: 4px;
                        font-size: 0.875rem;
                        z-index: 1000;
                        pointer-events: none;
                        white-space: nowrap;
                    `;
                    
                    document.body.appendChild(tooltip);
                    
                    const rect = this.getBoundingClientRect();
                    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                    tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
                    
                    this._tooltip = tooltip;
                });
                
                element.addEventListener('mouseleave', function() {
                    if (this._tooltip) {
                        this._tooltip.remove();
                        this._tooltip = null;
                    }
                });
            });
        });
        
        // Service Worker registration for offline functionality (optional)
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/impocred/sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful');
                    })
                    .catch(function(err) {
                        console.log('ServiceWorker registration failed');
                    });
            });
        }
        
        // Performance monitoring
        window.addEventListener('load', function() {
            if ('performance' in window) {
                const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
                if (loadTime > 3000) {
                    console.warn('Page load time is slow:', loadTime + 'ms');
                }
            }
        });
    </script>
</body>
</html>

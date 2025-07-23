/**
 * Main JavaScript file for ABC Hospital Management System
 * Contains core functionality and utilities
 */

class ABCHospital {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupCSRFToken();
        this.setupFormValidation();
        this.setupAjaxDefaults();
        this.setupScrollEffects();
        this.setupTooltips();
        this.setupModals();
        this.setupNotifications();
        this.setupDatePickers();
        this.setupSearchFunctionality();
    }

    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            // Mobile navigation toggle
            const mobileToggle = document.querySelector('.mobile-menu-toggle');
            if (mobileToggle) {
                mobileToggle.addEventListener('click', this.toggleMobileNav);
            }

            // Navbar scroll effect
            window.addEventListener('scroll', this.handleNavbarScroll);

            // Form submission handling
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', this.handleFormSubmit.bind(this));
            });

            // Auto-hide alerts
            this.setupAutoHideAlerts();

            // Password toggle functionality
            this.setupPasswordToggle();

            // Loading states
            this.setupLoadingStates();

            // Floating action button
            const floatingBtn = document.querySelector('.btn-float');
            if (floatingBtn && floatingBtn.onclick === null) {
                floatingBtn.addEventListener('click', this.scrollToTop);
            }
        });
    }

    setupCSRFToken() {
        // Get CSRF token from meta tag
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) {
            this.csrfToken = token;
        }
    }

    setupAjaxDefaults() {
        // Setup default AJAX settings
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                beforeSend: (xhr) => {
                    if (this.csrfToken) {
                        xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken);
                    }
                },
                error: (xhr, status, error) => {
                    this.showNotification('An error occurred. Please try again.', 'error');
                }
            });
        }
    }

    toggleMobileNav() {
        const mobileNav = document.getElementById('mobileNav');
        const toggle = document.querySelector('.mobile-menu-toggle i');
        
        if (mobileNav) {
            mobileNav.classList.toggle('show');
            
            // Animate hamburger icon
            if (toggle) {
                if (mobileNav.classList.contains('show')) {
                    toggle.className = 'fas fa-times';
                } else {
                    toggle.className = 'fas fa-bars';
                }
            }
        }
    }

    handleNavbarScroll() {
        const navbar = document.getElementById('navbar');
        if (navbar) {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }
    }

    scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    setupFormValidation() {
        const forms = document.querySelectorAll('form[data-validate]');
        
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            
            inputs.forEach(input => {
                input.addEventListener('blur', () => this.validateField(input));
                input.addEventListener('input', () => this.clearFieldError(input));
            });
        });
    }

    validateField(field) {
        const value = field.value.trim();
        const type = field.type;
        let isValid = true;
        let message = '';

        // Required field validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            message = 'This field is required.';
        }

        // Email validation
        if (type === 'email' && value && !this.isValidEmail(value)) {
            isValid = false;
            message = 'Please enter a valid email address.';
        }

        // Phone validation
        if (field.name === 'phone' && value && !this.isValidPhone(value)) {
            isValid = false;
            message = 'Please enter a valid phone number.';
        }

        // Password strength validation
        if (type === 'password' && field.name === 'password' && value && value.length < 6) {
            isValid = false;
            message = 'Password must be at least 6 characters long.';
        }

        // Confirm password validation
        if (field.name === 'confirm_password' && value) {
            const password = form.querySelector('input[name="password"]')?.value;
            if (password && value !== password) {
                isValid = false;
                message = 'Passwords do not match.';
            }
        }

        this.setFieldValidation(field, isValid, message);
        return isValid;
    }

    setFieldValidation(field, isValid, message) {
        const formGroup = field.closest('.form-group');
        const errorElement = formGroup?.querySelector('.form-error');

        // Remove existing classes
        field.classList.remove('error', 'success');
        
        if (isValid) {
            field.classList.add('success');
            if (errorElement) {
                errorElement.remove();
            }
        } else {
            field.classList.add('error');
            
            if (!errorElement && message) {
                const error = document.createElement('div');
                error.className = 'form-error';
                error.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
                formGroup?.appendChild(error);
            }
        }
    }

    clearFieldError(field) {
        const formGroup = field.closest('.form-group');
        const errorElement = formGroup?.querySelector('.form-error');
        
        field.classList.remove('error');
        if (errorElement) {
            errorElement.remove();
        }
    }

    handleFormSubmit(event) {
        const form = event.target;
        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
        
        // Add loading state
        if (submitBtn) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        }

        // Validate form if it has validation
        if (form.hasAttribute('data-validate')) {
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            let isFormValid = true;

            inputs.forEach(input => {
                if (!this.validateField(input)) {
                    isFormValid = false;
                }
            });

            if (!isFormValid) {
                event.preventDefault();
                if (submitBtn) {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                }
                this.showNotification('Please correct the errors in the form.', 'error');
                return;
            }
        }

        // Add CSRF token if not present
        if (this.csrfToken && !form.querySelector('input[name="csrf_token"]')) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = this.csrfToken;
            form.appendChild(csrfInput);
        }
    }

    setupPasswordToggle() {
        document.querySelectorAll('.password-toggle').forEach(container => {
            const input = container.querySelector('input[type="password"]');
            const toggle = container.querySelector('.toggle-icon');

            if (input && toggle) {
                toggle.addEventListener('click', () => {
                    const type = input.type === 'password' ? 'text' : 'password';
                    input.type = type;
                    
                    const icon = toggle.querySelector('i');
                    if (icon) {
                        icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
                    }
                });
            }
        });
    }

    setupAutoHideAlerts() {
        document.querySelectorAll('.alert').forEach(alert => {
            // Auto hide after 5 seconds
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);

            // Manual close button
            const closeBtn = alert.querySelector('.alert-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                });
            }
        });
    }

    setupLoadingStates() {
        document.querySelectorAll('[data-loading]').forEach(element => {
            element.addEventListener('click', () => {
                element.classList.add('loading');
                
                // Remove loading state after 3 seconds (fallback)
                setTimeout(() => {
                    element.classList.remove('loading');
                }, 3000);
            });
        });
    }

    setupScrollEffects() {
        // Parallax effect for hero sections
        const hero = document.querySelector('.hero');
        if (hero) {
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                const rate = scrolled * -0.5;
                hero.style.transform = `translateY(${rate}px)`;
            });
        }

        // Fade in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.6s ease forwards';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.card, .feature-card, .stat-card').forEach(el => {
            observer.observe(el);
        });
    }

    setupTooltips() {
        document.querySelectorAll('[data-tooltip]').forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = e.target.getAttribute('data-tooltip');
                document.body.appendChild(tooltip);

                const rect = e.target.getBoundingClientRect();
                tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
                
                setTimeout(() => tooltip.classList.add('show'), 10);
            });

            element.addEventListener('mouseleave', () => {
                const tooltip = document.querySelector('.tooltip');
                if (tooltip) {
                    tooltip.remove();
                }
            });
        });
    }

    setupModals() {
        // Modal open/close functionality
        document.querySelectorAll('[data-modal]').forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const modalId = trigger.getAttribute('data-modal');
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('show');
                    document.body.classList.add('modal-open');
                }
            });
        });

        document.querySelectorAll('.modal-close, .modal-backdrop').forEach(closer => {
            closer.addEventListener('click', () => {
                document.querySelectorAll('.modal.show').forEach(modal => {
                    modal.classList.remove('show');
                });
                document.body.classList.remove('modal-open');
            });
        });

        // Close modal on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal.show').forEach(modal => {
                    modal.classList.remove('show');
                });
                document.body.classList.remove('modal-open');
            }
        });
    }

    setupNotifications() {
        // Create notification container if it doesn't exist
        if (!document.querySelector('.notification-container')) {
            const container = document.createElement('div');
            container.className = 'notification-container';
            document.body.appendChild(container);
        }
    }

    showNotification(message, type = 'info', duration = 5000) {
        const container = document.querySelector('.notification-container');
        if (!container) return;

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };

        notification.innerHTML = `
            <i class="${icons[type]}"></i>
            <span>${message}</span>
            <button class="notification-close"><i class="fas fa-times"></i></button>
        `;

        container.appendChild(notification);

        // Show notification
        setTimeout(() => notification.classList.add('show'), 10);

        // Auto hide
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, duration);

        // Manual close
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        });
    }

    setupDatePickers() {
        // Simple date picker setup
        document.querySelectorAll('input[type="date"]').forEach(input => {
            // Set minimum date to today
            if (input.hasAttribute('data-min-today')) {
                input.min = new Date().toISOString().split('T')[0];
            }
        });
    }

    setupSearchFunctionality() {
        document.querySelectorAll('[data-search]').forEach(searchInput => {
            const targetSelector = searchInput.getAttribute('data-search');
            const targets = document.querySelectorAll(targetSelector);

            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();

                targets.forEach(target => {
                    const text = target.textContent.toLowerCase();
                    if (text.includes(query)) {
                        target.style.display = '';
                    } else {
                        target.style.display = 'none';
                    }
                });
            });
        });
    }

    // Utility functions
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    isValidPhone(phone) {
        return /^[\+]?[1-9][\d]{0,15}$/.test(phone.replace(/\s/g, ''));
    }

    formatDate(date, format = 'YYYY-MM-DD') {
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        
        return format
            .replace('YYYY', year)
            .replace('MM', month)
            .replace('DD', day);
    }

    debounce(func, wait) {
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

    // AJAX helper methods
    async get(url) {
        try {
            const response = await fetch(url, {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });
            return await response.json();
        } catch (error) {
            this.showNotification('Network error occurred.', 'error');
            throw error;
        }
    }

    async post(url, data) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            this.showNotification('Network error occurred.', 'error');
            throw error;
        }
    }
}

// Initialize the application
const app = new ABCHospital();

// Global functions for backward compatibility
function toggleMobileNav() {
    app.toggleMobileNav();
}

function scrollToTop() {
    app.scrollToTop();
}

function showNotification(message, type, duration) {
    app.showNotification(message, type, duration);
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ABCHospital;
}

// CSS for notifications and tooltips
const style = document.createElement('style');
style.textContent = `
    /* Notification Styles */
    .notification-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        max-width: 400px;
    }

    .notification {
        background: white;
        border-radius: 10px;
        padding: 15px 20px;
        margin-bottom: 10px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        transform: translateX(100%);
        opacity: 0;
        transition: all 0.3s ease;
        border-left: 4px solid #3498db;
    }

    .notification.show {
        transform: translateX(0);
        opacity: 1;
    }

    .notification-success {
        border-left-color: #2ecc71;
        color: #2ecc71;
    }

    .notification-error {
        border-left-color: #e74c3c;
        color: #e74c3c;
    }

    .notification-warning {
        border-left-color: #f39c12;
        color: #f39c12;
    }

    .notification-info {
        border-left-color: #3498db;
        color: #3498db;
    }

    .notification i {
        font-size: 1.2em;
    }

    .notification span {
        flex: 1;
        color: #2c3e50;
    }

    .notification-close {
        background: none;
        border: none;
        color: #bdc3c7;
        cursor: pointer;
        font-size: 1.1em;
        padding: 0;
        transition: color 0.3s ease;
    }

    .notification-close:hover {
        color: #7f8c8d;
    }

    /* Tooltip Styles */
    .tooltip {
        position: absolute;
        background: #2c3e50;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.85em;
        white-space: nowrap;
        z-index: 10001;
        opacity: 0;
        transform: translateY(5px);
        transition: all 0.3s ease;
        pointer-events: none;
    }

    .tooltip.show {
        opacity: 1;
        transform: translateY(0);
    }

    .tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: #2c3e50;
    }

    /* Modal Styles */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .modal.show {
        opacity: 1;
        visibility: visible;
    }

    .modal-content {
        background: white;
        border-radius: 15px;
        padding: 30px;
        max-width: 90%;
        max-height: 90%;
        overflow-y: auto;
        transform: scale(0.8);
        transition: transform 0.3s ease;
    }

    .modal.show .modal-content {
        transform: scale(1);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #ecf0f1;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5em;
        color: #bdc3c7;
        cursor: pointer;
        transition: color 0.3s ease;
    }

    .modal-close:hover {
        color: #7f8c8d;
    }

    body.modal-open {
        overflow: hidden;
    }

    /* Loading Animation */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Responsive Notifications */
    @media (max-width: 768px) {
        .notification-container {
            left: 20px;
            right: 20px;
            max-width: none;
        }

        .notification {
            padding: 12px 15px;
        }
    }
`;
document.head.appendChild(style);

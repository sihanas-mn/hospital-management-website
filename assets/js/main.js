// main.js - Core JavaScript functionality for ABC Hospital Management System

// Global variables
let loadingOverlay;
let floatingAction;

// Initialize application when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

// Main initialization function
function initializeApp() {
    // Initialize common elements
    loadingOverlay = document.getElementById('loadingOverlay');
    floatingAction = document.querySelector('.floating-action');
    
    // Initialize features
    initializeNavbar();
    initializeFloatingAction();
    initializeFormValidation();
    initializeAlerts();
    initializeAnimations();
    initializeSearch();
    
    // Show page content with animation
    showPageContent();
}

// Navbar functionality
function initializeNavbar() {
    const navbar = document.getElementById('navbar');
    if (!navbar) return;
    
    // Add scroll effect to navbar
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
        
        // Show/hide floating action button
        toggleFloatingAction();
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Floating action button
function initializeFloatingAction() {
    if (!floatingAction) return;
    
    // Default click handler for scroll to top
    if (!floatingAction.onclick) {
        floatingAction.addEventListener('click', scrollToTop);
    }
}

function toggleFloatingAction() {
    if (!floatingAction) return;
    
    if (window.scrollY > 300) {
        floatingAction.classList.add('show');
    } else {
        floatingAction.classList.remove('show');
    }
}

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Loading overlay functions
function showLoading() {
    if (loadingOverlay) {
        loadingOverlay.classList.add('show');
    }
}

function hideLoading() {
    if (loadingOverlay) {
        loadingOverlay.classList.remove('show');
    }
}

// Form validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        // Add real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
        
        // Form submission handling
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                return false;
            }
            
            // Show loading for forms with submit buttons
            const submitBtn = this.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn) {
                showLoading();
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute('required');
    
    // Clear previous errors
    clearFieldError(field);
    
    // Required field validation
    if (required && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    // Email validation
    if (type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Please enter a valid email address');
            return false;
        }
    }
    
    // Phone validation
    if (field.name === 'contact' && value) {
        const phoneRegex = /^[\d\s\-\+\(\)]+$/;
        if (!phoneRegex.test(value) || value.length < 10) {
            showFieldError(field, 'Please enter a valid phone number');
            return false;
        }
    }
    
    // Password validation
    if (type === 'password' && value) {
        if (value.length < 6) {
            showFieldError(field, 'Password must be at least 6 characters long');
            return false;
        }
    }
    
    // Date validation
    if (type === 'date' && value) {
        const selectedDate = new Date(value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            showFieldError(field, 'Please select a future date');
            return false;
        }
    }
    
    return true;
}

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
    errorDiv.style.fontSize = '0.8rem';
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

// Alert system
function initializeAlerts() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            hideAlert(alert);
        }, 5000);
        
        // Add close button
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '&times;';
        closeBtn.className = 'alert-close';
        closeBtn.style.cssText = `
            position: absolute;
            top: 0.5rem;
            right: 0.75rem;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s;
        `;
        closeBtn.addEventListener('click', () => hideAlert(alert));
        closeBtn.addEventListener('mouseenter', () => closeBtn.style.opacity = '1');
        closeBtn.addEventListener('mouseleave', () => closeBtn.style.opacity = '0.7');
        
        alert.style.position = 'relative';
        alert.appendChild(closeBtn);
    });
}

function showAlert(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    alert.style.position = 'fixed';
    alert.style.top = '100px';
    alert.style.right = '20px';
    alert.style.zIndex = '9999';
    alert.style.maxWidth = '400px';
    alert.style.animation = 'slideInRight 0.3s ease-out';
    
    document.body.appendChild(alert);
    
    setTimeout(() => hideAlert(alert), 5000);
    
    return alert;
}

function hideAlert(alert) {
    if (alert && alert.parentNode) {
        alert.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 300);
    }
}

// Animation system
function initializeAnimations() {
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease-out forwards';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    const animateElements = document.querySelectorAll('.card, .feature-card, .stat-card, .appointment-card');
    animateElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        observer.observe(el);
    });
}

function showPageContent() {
    // Fade in page content
    document.body.style.opacity = '1';
    document.body.style.transition = 'opacity 0.3s ease-in';
}

// Search functionality
function initializeSearch() {
    const searchInputs = document.querySelectorAll('[data-search]');
    
    searchInputs.forEach(input => {
        let searchTimeout;
        
        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(this);
            }, 300);
        });
    });
}

function performSearch(input) {
    const query = input.value.toLowerCase().trim();
    const targetSelector = input.getAttribute('data-search');
    const targets = document.querySelectorAll(targetSelector);
    
    targets.forEach(target => {
        const text = target.textContent.toLowerCase();
        const isMatch = text.includes(query);
        
        target.style.display = isMatch ? '' : 'none';
        
        // Add highlight effect
        if (isMatch && query) {
            target.style.animation = 'highlight 0.3s ease-out';
        }
    });
    
    // Update empty state
    const visibleTargets = Array.from(targets).filter(t => t.style.display !== 'none');
    updateEmptyState(visibleTargets.length === 0, targets[0]?.parentNode);
}

function updateEmptyState(show, container) {
    if (!container) return;
    
    let emptyState = container.querySelector('.empty-state');
    
    if (show && !emptyState) {
        emptyState = document.createElement('div');
        emptyState.className = 'empty-state';
        emptyState.innerHTML = `
            <i class="fas fa-search"></i>
            <h3>No results found</h3>
            <p>Try adjusting your search criteria</p>
        `;
        container.appendChild(emptyState);
    } else if (!show && emptyState) {
        emptyState.remove();
    }
}

// Filter functionality
function initializeFilters() {
    const filterButtons = document.querySelectorAll('[data-filter]');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all filter buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Perform filtering
            const filterValue = this.getAttribute('data-filter');
            const targetSelector = this.getAttribute('data-filter-target');
            performFilter(filterValue, targetSelector);
        });
    });
}

function performFilter(filterValue, targetSelector) {
    const targets = document.querySelectorAll(targetSelector);
    
    targets.forEach(target => {
        if (filterValue === 'all') {
            target.style.display = '';
        } else {
            const targetValue = target.getAttribute('data-filter-value');
            target.style.display = targetValue === filterValue ? '' : 'none';
        }
    });
}

// AJAX functionality
function makeAjaxRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    const config = { ...defaultOptions, ...options };
    
    return fetch(url, config)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error('AJAX request failed:', error);
            showAlert('Request failed. Please try again.', 'danger');
            throw error;
        });
}

// Utility functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatTime(timeString) {
    const time = new Date(`2000-01-01T${timeString}`);
    return time.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

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

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Neural Network Animation for Hero Section
class NeuralNetwork {
    constructor(canvas) {
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');
        this.nodes = [];
        this.connections = [];
        this.animationId = null;
        
        this.init();
    }
    
    init() {
        this.resizeCanvas();
        this.createNodes();
        this.createConnections();
        this.animate();
        
        window.addEventListener('resize', () => this.resizeCanvas());
    }
    
    resizeCanvas() {
        this.canvas.width = this.canvas.offsetWidth;
        this.canvas.height = this.canvas.offsetHeight;
    }
    
    createNodes() {
        const nodeCount = 50;
        this.nodes = [];
        
        for (let i = 0; i < nodeCount; i++) {
            this.nodes.push({
                x: Math.random() * this.canvas.width,
                y: Math.random() * this.canvas.height,
                vx: (Math.random() - 0.5) * 0.5,
                vy: (Math.random() - 0.5) * 0.5,
                radius: Math.random() * 3 + 1,
                opacity: Math.random() * 0.5 + 0.3
            });
        }
    }
    
    createConnections() {
        this.connections = [];
        const maxDistance = 150;
        
        for (let i = 0; i < this.nodes.length; i++) {
            for (let j = i + 1; j < this.nodes.length; j++) {
                const dx = this.nodes[i].x - this.nodes[j].x;
                const dy = this.nodes[i].y - this.nodes[j].y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                
                if (distance < maxDistance) {
                    this.connections.push({
                        nodeA: this.nodes[i],
                        nodeB: this.nodes[j],
                        opacity: (maxDistance - distance) / maxDistance * 0.3
                    });
                }
            }
        }
    }
    
    animate() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        // Update and draw nodes
        this.nodes.forEach(node => {
            node.x += node.vx;
            node.y += node.vy;
            
            // Boundary checking
            if (node.x < 0 || node.x > this.canvas.width) node.vx *= -1;
            if (node.y < 0 || node.y > this.canvas.height) node.vy *= -1;
            
            // Draw node
            this.ctx.beginPath();
            this.ctx.arc(node.x, node.y, node.radius, 0, Math.PI * 2);
            this.ctx.fillStyle = `rgba(255, 255, 255, ${node.opacity})`;
            this.ctx.fill();
        });
        
        // Draw connections
        this.connections.forEach(connection => {
            this.ctx.beginPath();
            this.ctx.moveTo(connection.nodeA.x, connection.nodeA.y);
            this.ctx.lineTo(connection.nodeB.x, connection.nodeB.y);
            this.ctx.strokeStyle = `rgba(255, 255, 255, ${connection.opacity})`;
            this.ctx.lineWidth = 1;
            this.ctx.stroke();
        });
        
        this.animationId = requestAnimationFrame(() => this.animate());
    }
    
    destroy() {
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }
    }
}

// Initialize Neural Network if canvas exists
document.addEventListener('DOMContentLoaded', function() {
    const neuralCanvas = document.getElementById('neural-canvas');
    if (neuralCanvas) {
        new NeuralNetwork(neuralCanvas);
    }
});

// Add CSS animations for notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    @keyframes highlight {
        0% { background-color: transparent; }
        50% { background-color: rgba(52, 152, 219, 0.1); }
        100% { background-color: transparent; }
    }
    
    .form-control.error {
        border-color: #e74c3c !important;
        box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1) !important;
    }
`;
document.head.appendChild(style);

// Global error handler
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
    hideLoading();
});

// Expose global functions
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.showAlert = showAlert;
window.scrollToTop = scrollToTop;
window.formatDate = formatDate;
window.formatTime = formatTime;
window.makeAjaxRequest = makeAjaxRequest;

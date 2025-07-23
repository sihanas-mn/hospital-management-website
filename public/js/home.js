/* Home Page JavaScript */

// Neural Network Animation
class NeuralNetwork {
    constructor(canvas) {
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');
        this.nodes = [];
        this.connections = [];
        this.animationId = null;
        
        this.init();
        this.setupEventListeners();
    }
    
    init() {
        this.resizeCanvas();
        this.createNodes();
        this.createConnections();
        this.animate();
    }
    
    resizeCanvas() {
        this.canvas.width = this.canvas.offsetWidth;
        this.canvas.height = this.canvas.offsetHeight;
    }
    
    createNodes() {
        const nodeCount = Math.floor((this.canvas.width * this.canvas.height) / 15000);
        this.nodes = [];
        
        for (let i = 0; i < nodeCount; i++) {
            this.nodes.push({
                x: Math.random() * this.canvas.width,
                y: Math.random() * this.canvas.height,
                vx: (Math.random() - 0.5) * 0.5,
                vy: (Math.random() - 0.5) * 0.5,
                radius: Math.random() * 2 + 1,
                opacity: Math.random() * 0.5 + 0.3
            });
        }
    }
    
    createConnections() {
        this.connections = [];
        for (let i = 0; i < this.nodes.length; i++) {
            for (let j = i + 1; j < this.nodes.length; j++) {
                const distance = this.getDistance(this.nodes[i], this.nodes[j]);
                if (distance < 150) {
                    this.connections.push({
                        nodeA: this.nodes[i],
                        nodeB: this.nodes[j],
                        distance: distance
                    });
                }
            }
        }
    }
    
    getDistance(nodeA, nodeB) {
        return Math.sqrt(Math.pow(nodeA.x - nodeB.x, 2) + Math.pow(nodeA.y - nodeB.y, 2));
    }
    
    updateNodes() {
        this.nodes.forEach(node => {
            node.x += node.vx;
            node.y += node.vy;
            
            if (node.x < 0 || node.x > this.canvas.width) node.vx *= -1;
            if (node.y < 0 || node.y > this.canvas.height) node.vy *= -1;
        });
    }
    
    drawNodes() {
        this.nodes.forEach(node => {
            this.ctx.beginPath();
            this.ctx.arc(node.x, node.y, node.radius, 0, Math.PI * 2);
            this.ctx.fillStyle = `rgba(52, 152, 219, ${node.opacity})`;
            this.ctx.fill();
        });
    }
    
    drawConnections() {
        this.connections.forEach(connection => {
            const opacity = Math.max(0, (150 - connection.distance) / 150) * 0.3;
            if (opacity > 0) {
                this.ctx.beginPath();
                this.ctx.moveTo(connection.nodeA.x, connection.nodeA.y);
                this.ctx.lineTo(connection.nodeB.x, connection.nodeB.y);
                this.ctx.strokeStyle = `rgba(46, 204, 113, ${opacity})`;
                this.ctx.stroke();
            }
        });
    }
    
    animate() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        this.updateNodes();
        this.createConnections();
        this.drawConnections();
        this.drawNodes();
        
        this.animationId = requestAnimationFrame(() => this.animate());
    }
    
    setupEventListeners() {
        window.addEventListener('resize', () => {
            this.resizeCanvas();
            this.createNodes();
        });
    }
    
    destroy() {
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }
    }
}

// Statistics Counter Animation
class StatisticsCounter {
    constructor() {
        this.counters = document.querySelectorAll('.stat-number');
        this.setupIntersectionObserver();
    }
    
    setupIntersectionObserver() {
        const options = {
            threshold: 0.5,
            rootMargin: '0px 0px -50px 0px'
        };
        
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateCounter(entry.target);
                    this.observer.unobserve(entry.target);
                }
            });
        }, options);
        
        this.counters.forEach(counter => {
            this.observer.observe(counter);
        });
    }
    
    animateCounter(element) {
        const target = parseInt(element.getAttribute('data-count') || element.textContent);
        const duration = 2000;
        const start = performance.now();
        
        const updateCounter = (currentTime) => {
            const elapsed = currentTime - start;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function for smooth animation
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const currentValue = Math.floor(target * easeOutQuart);
            
            element.textContent = currentValue.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            } else {
                element.textContent = target.toLocaleString();
            }
        };
        
        requestAnimationFrame(updateCounter);
    }
}

// Smooth Scroll Handler
class SmoothScroll {
    constructor() {
        this.init();
    }
    
    init() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = anchor.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }
}

// Fade-in Animation on Scroll
class ScrollAnimations {
    constructor() {
        this.elements = document.querySelectorAll('.feature-card, .service-card, .testimonial-card');
        this.setupIntersectionObserver();
    }
    
    setupIntersectionObserver() {
        const options = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    this.observer.unobserve(entry.target);
                }
            });
        }, options);
        
        this.elements.forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(50px)';
            element.style.transition = 'all 0.6s ease';
            this.observer.observe(element);
        });
    }
}

// Floating Shapes Animation
class FloatingShapes {
    constructor() {
        this.createShapes();
    }
    
    createShapes() {
        const shapesContainer = document.querySelector('.floating-shapes');
        if (!shapesContainer) return;
        
        // Clear existing shapes
        shapesContainer.innerHTML = '';
        
        const shapeCount = window.innerWidth > 768 ? 6 : 3;
        
        for (let i = 0; i < shapeCount; i++) {
            const shape = document.createElement('div');
            shape.className = 'shape';
            
            // Random positioning
            shape.style.left = Math.random() * 100 + '%';
            shape.style.top = Math.random() * 100 + '%';
            
            // Random size
            const size = Math.random() * 50 + 30;
            shape.style.width = size + 'px';
            shape.style.height = size + 'px';
            
            // Random animation delay
            shape.style.animationDelay = Math.random() * 6 + 's';
            
            // Random animation duration
            shape.style.animationDuration = (Math.random() * 4 + 4) + 's';
            
            shapesContainer.appendChild(shape);
        }
    }
}

// Loading Animation
class LoadingAnimation {
    constructor() {
        this.init();
    }
    
    init() {
        // Create loading overlay
        const loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'loading-overlay';
        loadingOverlay.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner">
                    <div class="spinner-circle"></div>
                    <div class="spinner-circle"></div>
                    <div class="spinner-circle"></div>
                </div>
                <p>Loading ABC Hospital...</p>
            </div>
        `;
        
        // Add loading styles
        const loadingStyles = `
            <style>
                #loading-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(135deg, #2c3e50, #3498db);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 9999;
                    transition: opacity 0.5s ease;
                }
                
                .loading-content {
                    text-align: center;
                    color: white;
                }
                
                .loading-spinner {
                    display: flex;
                    justify-content: center;
                    margin-bottom: 20px;
                }
                
                .spinner-circle {
                    width: 12px;
                    height: 12px;
                    background: #3498db;
                    border-radius: 50%;
                    margin: 0 5px;
                    animation: bounce 1.4s ease-in-out infinite both;
                }
                
                .spinner-circle:nth-child(1) { animation-delay: -0.32s; }
                .spinner-circle:nth-child(2) { animation-delay: -0.16s; }
                
                @keyframes bounce {
                    0%, 80%, 100% {
                        transform: scale(0);
                        background: #3498db;
                    }
                    40% {
                        transform: scale(1);
                        background: #2ecc71;
                    }
                }
                
                .loading-content p {
                    font-size: 1.2em;
                    font-weight: 300;
                    opacity: 0.9;
                }
            </style>
        `;
        
        document.head.insertAdjacentHTML('beforeend', loadingStyles);
        document.body.appendChild(loadingOverlay);
        
        // Remove loading screen after page load
        window.addEventListener('load', () => {
            setTimeout(() => {
                loadingOverlay.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(loadingOverlay);
                }, 500);
            }, 1000);
        });
    }
}

// Parallax Effect
class ParallaxEffect {
    constructor() {
        this.elements = document.querySelectorAll('.hero-background-effects');
        this.init();
    }
    
    init() {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            
            this.elements.forEach(element => {
                element.style.transform = `translateY(${rate}px)`;
            });
        });
    }
}

// Initialize all components when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize loading animation
    new LoadingAnimation();
    
    // Initialize neural network after a short delay
    setTimeout(() => {
        const canvas = document.getElementById('neural-canvas');
        if (canvas) {
            new NeuralNetwork(canvas);
        }
    }, 500);
    
    // Initialize other components
    new StatisticsCounter();
    new SmoothScroll();
    new ScrollAnimations();
    new FloatingShapes();
    new ParallaxEffect();
    
    // Handle window resize for floating shapes
    window.addEventListener('resize', () => {
        new FloatingShapes();
    });
    
    // Add some interactive feedback
    document.querySelectorAll('.feature-card, .service-card, .testimonial-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Add click effects to buttons
    document.querySelectorAll('.btn, .action-button').forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
    
    // Add ripple effect styles
    const rippleStyles = `
        <style>
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
            }
            
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            
            .btn, .action-button {
                position: relative;
                overflow: hidden;
            }
        </style>
    `;
    
    document.head.insertAdjacentHTML('beforeend', rippleStyles);
});

// Export for use in other files
window.HomePageComponents = {
    NeuralNetwork,
    StatisticsCounter,
    SmoothScroll,
    ScrollAnimations,
    FloatingShapes,
    LoadingAnimation,
    ParallaxEffect
};

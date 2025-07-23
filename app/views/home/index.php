<!-- Hero Section -->
<section class="hero">
    <canvas id="neural-canvas"></canvas>
    <div class="hero-background-effects">
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>
        <div class="medical-pulse">
            <i class="fas fa-plus pulse-cross"></i>
        </div>
    </div>
    
    <div class="hero-content">
        <h1 class="hero-title"><?php echo View::escape($hero['title']); ?></h1>
        <p class="hero-subtitle"><?php echo View::escape($hero['subtitle']); ?></p>
        <p class="hero-description"><?php echo View::escape($hero['description']); ?></p>
        
        <div class="hero-actions">
            <?php foreach ($hero['buttons'] as $button): ?>
                <a href="<?php echo View::url($button['url']); ?>" 
                   class="action-button <?php echo $button['class']; ?>">
                    <i class="fas fa-calendar-plus"></i>
                    <?php echo View::escape($button['text']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <h2 class="section-title">Why Choose ABC Hospital?</h2>
        <p class="section-subtitle">We provide comprehensive healthcare services with a patient-centered approach</p>
        
        <div class="features-grid">
            <?php foreach ($features as $feature): ?>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="<?php echo $feature['icon']; ?>"></i>
                    </div>
                    <h3><?php echo View::escape($feature['title']); ?></h3>
                    <p><?php echo View::escape($feature['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Services Section -->
<section id="our-services" class="services-section">
    <div class="container">
        <h2 class="section-title">Our Medical Services</h2>
        <p class="section-subtitle">Comprehensive healthcare solutions under one roof</p>
        
        <div class="services-grid">
            <?php foreach ($services as $service): ?>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="<?php echo $service['icon']; ?>"></i>
                    </div>
                    <h3><?php echo View::escape($service['title']); ?></h3>
                    <p><?php echo View::escape($service['description']); ?></p>
                    <a href="#" class="service-link">
                        Learn More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <?php foreach ($stats as $stat): ?>
                <div class="stat-card">
                    <div class="stat-number" data-target="<?php echo str_replace('+', '', $stat['number']); ?>">
                        0
                    </div>
                    <div class="stat-label"><?php echo View::escape($stat['label']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section id="reviews" class="testimonials-section">
    <div class="container">
        <h2 class="section-title">What Our Patients Say</h2>
        <p class="section-subtitle">Real experiences from our valued patients</p>
        
        <div class="testimonials-grid">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <div class="testimonial-stars">
                            <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="testimonial-text">"<?php echo View::escape($testimonial['text']); ?>"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <?php echo strtoupper(substr($testimonial['name'], 0, 2)); ?>
                        </div>
                        <div class="author-info">
                            <h4><?php echo View::escape($testimonial['name']); ?></h4>
                            <span>Verified Patient</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about-us" class="about-section">
    <div class="container">
        <div class="about-content">
            <div class="about-text">
                <h2 class="section-title">About ABC Hospital</h2>
                <p>
                    ABC Hospital has been serving the community for over 25 years, providing exceptional healthcare 
                    services with a commitment to excellence, compassion, and innovation. Our state-of-the-art 
                    facility is equipped with the latest medical technology and staffed by highly skilled healthcare 
                    professionals.
                </p>
                <p>
                    We believe in treating not just the condition, but the whole person. Our patient-centered approach 
                    ensures that every individual receives personalized care tailored to their unique needs and 
                    circumstances.
                </p>
                <div class="about-features">
                    <div class="about-feature">
                        <i class="fas fa-award"></i>
                        <span>Accredited Healthcare Facility</span>
                    </div>
                    <div class="about-feature">
                        <i class="fas fa-users"></i>
                        <span>Experienced Medical Team</span>
                    </div>
                    <div class="about-feature">
                        <i class="fas fa-clock"></i>
                        <span>24/7 Emergency Services</span>
                    </div>
                </div>
            </div>
            <div class="about-image">
                <img src="<?php echo View::image('hospital-building.jpg'); ?>" alt="ABC Hospital Building">
            </div>
        </div>
    </div>
    
    <!-- Background animation -->
    <div class="heartbeat-bg">
        <i class="fas fa-heartbeat heartbeat"></i>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Ready to Experience Quality Healthcare?</h2>
            <p>Book your appointment today and take the first step towards better health.</p>
            <div class="cta-actions">
                <a href="<?php echo View::url('pages/patient/book_appointment.php'); ?>" 
                   class="btn btn-primary btn-lg">
                    <i class="fas fa-calendar-plus"></i>
                    Book Appointment Now
                </a>
                <a href="<?php echo View::url('contact.php'); ?>" 
                   class="btn btn-outline btn-lg">
                    <i class="fas fa-phone"></i>
                    Contact Us
                </a>
            </div>
        </div>
    </div>
</section>

<script>
// Neural Network Animation for Hero Section
class NeuralNetwork {
    constructor(canvas) {
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');
        this.nodes = [];
        this.connections = [];
        this.resize();
        this.init();
        this.animate();
        
        window.addEventListener('resize', () => this.resize());
    }

    resize() {
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
    }

    init() {
        this.nodes = [];
        this.connections = [];
        
        const nodeCount = Math.floor((this.canvas.width * this.canvas.height) / 15000);
        
        for (let i = 0; i < nodeCount; i++) {
            this.nodes.push({
                x: Math.random() * this.canvas.width,
                y: Math.random() * this.canvas.height,
                vx: (Math.random() - 0.5) * 0.5,
                vy: (Math.random() - 0.5) * 0.5,
                radius: Math.random() * 3 + 1
            });
        }
    }

    animate() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        // Update nodes
        this.nodes.forEach(node => {
            node.x += node.vx;
            node.y += node.vy;
            
            if (node.x <= 0 || node.x >= this.canvas.width) node.vx *= -1;
            if (node.y <= 0 || node.y >= this.canvas.height) node.vy *= -1;
        });
        
        // Draw connections
        this.ctx.strokeStyle = 'rgba(52, 152, 219, 0.1)';
        this.ctx.lineWidth = 1;
        
        for (let i = 0; i < this.nodes.length; i++) {
            for (let j = i + 1; j < this.nodes.length; j++) {
                const dist = Math.hypot(
                    this.nodes[i].x - this.nodes[j].x,
                    this.nodes[i].y - this.nodes[j].y
                );
                
                if (dist < 150) {
                    this.ctx.beginPath();
                    this.ctx.moveTo(this.nodes[i].x, this.nodes[i].y);
                    this.ctx.lineTo(this.nodes[j].x, this.nodes[j].y);
                    this.ctx.stroke();
                }
            }
        }
        
        // Draw nodes
        this.ctx.fillStyle = 'rgba(52, 152, 219, 0.3)';
        this.nodes.forEach(node => {
            this.ctx.beginPath();
            this.ctx.arc(node.x, node.y, node.radius, 0, Math.PI * 2);
            this.ctx.fill();
        });
        
        requestAnimationFrame(() => this.animate());
    }
}

// Initialize neural network
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('neural-canvas');
    if (canvas) {
        new NeuralNetwork(canvas);
    }
});
</script>

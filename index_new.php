<?php
require_once 'config.php';

// Page configuration
$page_title = 'Welcome to ABC Hospital';
$show_floating_action = true;
$floating_action_function = 'scrollToTop()';
$additional_css = ['home'];
$additional_js = ['home'];

// Include header
include 'includes/header.php';
?>

<div class="hero">
    <canvas id="neural-canvas"></canvas>
    <div class="medical-pulse">
        <div class="pulse-cross"></div>
    </div>
    <div class="hero-content">
        <h1>Welcome to ABC Hospital</h1>
        <p class="subtitle">Providing Excellence in Healthcare</p>
        <p class="description">Your health is our priority. Experience world-class medical care with compassion and innovation at ABC Hospital.</p>
    </div>
</div>

<div class="container">
    <div class="action-buttons">
        <a href="<?php echo BASE_URL; ?>/pages/patient/book_appointment.php" class="action-button btn-primary">
            <i class="fas fa-calendar-plus"></i>
            Book Appointment
        </a>
        <a href="<?php echo BASE_URL; ?>/pages/patient/check_appointment.php" class="action-button btn-success">
            <i class="fas fa-search"></i>
            Check Status
        </a>
    </div>

    <section id="our-services" class="section">
        <h2 class="section-title">Our Services</h2>
        <div class="features">
            <div class="feature-card">
                <i class="fas fa-stethoscope feature-icon"></i>
                <h3>Expert Consultation</h3>
                <p>Get consultation from experienced doctors across various specializations with personalized care.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-ambulance feature-icon"></i>
                <h3>Emergency Care</h3>
                <p>24/7 emergency medical services with rapid response and critical care facilities.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-pills feature-icon"></i>
                <h3>Pharmacy Services</h3>
                <p>Complete pharmacy with all medications and medical supplies available on-site.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-x-ray feature-icon"></i>
                <h3>Diagnostic Services</h3>
                <p>Advanced diagnostic equipment for accurate testing and quick results.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-user-md feature-icon"></i>
                <h3>Specialized Care</h3>
                <p>Specialized departments for cardiology, neurology, orthopedics, and more.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-calendar-check feature-icon"></i>
                <h3>Easy Scheduling</h3>
                <p>Simple online appointment booking system with flexible scheduling options.</p>
            </div>
        </div>
    </section>

    <section id="reviews" class="reviews-section">
        <div class="dna-animation">
            <div class="dna-strand"></div>
            <div class="dna-strand"></div>
            <div class="dna-strand"></div>
            <div class="dna-strand"></div>
        </div>
        <div class="container">
            <h2 class="section-title">What Our Patients Say</h2>
            <div class="features">
                <div class="review-card">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>"Excellent service and caring staff. The doctors are very professional and the facilities are top-notch."</p>
                    <div class="reviewer-name">- Sarah Johnson</div>
                </div>
                <div class="review-card">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>"Fast and efficient service. The online appointment system made it so easy to book my visit."</p>
                    <div class="reviewer-name">- Michael Chen</div>
                </div>
                <div class="review-card">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>"The emergency care team saved my life. I'm forever grateful for their quick response and expertise."</p>
                    <div class="reviewer-name">- Emily Rodriguez</div>
                </div>
            </div>
        </div>
    </section>

    <section id="about-us" class="about-section">
        <div class="heartbeat-bg">
            <div class="heartbeat">❤️</div>
        </div>
        <div class="about-content">
            <h2 class="section-title">About ABC Hospital</h2>
            <p>For over 20 years, ABC Hospital has been at the forefront of medical excellence, providing comprehensive healthcare services to our community. Our state-of-the-art facilities, combined with our team of dedicated healthcare professionals, ensure that every patient receives the highest quality care.</p>
            <p>We believe in treating not just the condition, but the whole person. Our patient-centered approach focuses on comfort, dignity, and healing in a supportive environment.</p>
        </div>
    </section>
</div>

<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h4>Contact Information</h4>
                <p><i class="fas fa-map-marker-alt"></i> 123 Healthcare Avenue, Medical District</p>
                <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                <p><i class="fas fa-envelope"></i> info@abchospital.com</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <a href="<?php echo BASE_URL; ?>/pages/patient/book_appointment.php">Book Appointment</a>
                <a href="<?php echo BASE_URL; ?>/pages/patient/check_appointment.php">Check Status</a>
                <a href="<?php echo BASE_URL; ?>/login.php">Staff Login</a>
            </div>
            <div class="footer-section">
                <h4>Emergency</h4>
                <p>For medical emergencies, call:</p>
                <p style="color: #e74c3c; font-weight: bold; font-size: 1.2rem;">911</p>
                <p>Our emergency department is open 24/7</p>
            </div>
        </div>
        <div class="text-center" style="border-top: 1px solid #34495e; padding-top: 1rem; margin-top: 2rem;">
            <p>&copy; 2025 ABC Hospital. All rights reserved.</p>
        </div>
    </div>
</footer>

<?php
// Include footer
include 'includes/footer.php';
?>

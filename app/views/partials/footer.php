<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h4>
                    <i class="fas fa-hospital-symbol"></i>
                    ABC Hospital
                </h4>
                <p>Providing quality healthcare services with compassion and excellence since 1985. Your health is our priority.</p>
                <div class="footer-social">
                    <a href="#" class="social-link">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="#" class="social-link">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link">
                        <i class="fab fa-linkedin"></i>
                    </a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>
                    <i class="fas fa-stethoscope"></i>
                    Quick Links
                </h4>
                <a href="<?php echo View::url('index.php#our-services'); ?>">
                    <i class="fas fa-chevron-right"></i>
                    Our Services
                </a>
                <a href="<?php echo View::url('pages/patient/book_appointment.php'); ?>">
                    <i class="fas fa-chevron-right"></i>
                    Book Appointment
                </a>
                <a href="<?php echo View::url('pages/patient/check_appointment.php'); ?>">
                    <i class="fas fa-chevron-right"></i>
                    Check Appointment
                </a>
                <a href="<?php echo View::url('index.php#about-us'); ?>">
                    <i class="fas fa-chevron-right"></i>
                    About Us
                </a>
                <a href="<?php echo View::url('contact.php'); ?>">
                    <i class="fas fa-chevron-right"></i>
                    Contact Us
                </a>
            </div>
            
            <div class="footer-section">
                <h4>
                    <i class="fas fa-user-md"></i>
                    Departments
                </h4>
                <a href="#">
                    <i class="fas fa-heart"></i>
                    Cardiology
                </a>
                <a href="#">
                    <i class="fas fa-brain"></i>
                    Neurology
                </a>
                <a href="#">
                    <i class="fas fa-bone"></i>
                    Orthopedics
                </a>
                <a href="#">
                    <i class="fas fa-child"></i>
                    Pediatrics
                </a>
                <a href="#">
                    <i class="fas fa-procedures"></i>
                    Emergency
                </a>
            </div>
            
            <div class="footer-section">
                <h4>
                    <i class="fas fa-map-marker-alt"></i>
                    Contact Info
                </h4>
                <p>
                    <i class="fas fa-map-marker-alt"></i>
                    123 Healthcare Avenue<br>
                    Medical District, City 12345
                </p>
                <p>
                    <i class="fas fa-phone"></i>
                    Emergency: +1 (555) 911-HELP
                </p>
                <p>
                    <i class="fas fa-envelope"></i>
                    info@abchospital.com
                </p>
                <div class="footer-hours">
                    <h5>
                        <i class="fas fa-clock"></i>
                        Operating Hours
                    </h5>
                    <p>Emergency: 24/7</p>
                    <p>OPD: 8:00 AM - 8:00 PM</p>
                    <p>Pharmacy: 6:00 AM - 10:00 PM</p>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; <?php echo date('Y'); ?> ABC Hospital. All rights reserved.</p>
                <div class="footer-links">
                    <a href="<?php echo View::url('privacy.php'); ?>">Privacy Policy</a>
                    <a href="<?php echo View::url('terms.php'); ?>">Terms of Service</a>
                    <a href="<?php echo View::url('sitemap.php'); ?>">Sitemap</a>
                </div>
                <p class="footer-credit">
                    Designed with <i class="fas fa-heart" style="color: #e74c3c;"></i> for better healthcare
                </p>
            </div>
        </div>
    </div>
</footer>

<style>
/* Footer Styles */
.footer {
    background: linear-gradient(135deg, #2c3e50, #34495e);
    color: white;
    padding: 3rem 0 1rem;
    margin-top: 4rem;
    position: relative;
    overflow: hidden;
}

.footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #3498db, #2ecc71, #e74c3c, #f39c12);
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 3rem;
    margin-bottom: 2rem;
}

.footer-section h4 {
    color: #3498db;
    margin-bottom: 1.5rem;
    font-size: 1.3rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.footer-section p,
.footer-section a {
    color: #bdc3c7;
    text-decoration: none;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.footer-section a:hover {
    color: #3498db;
    transform: translateX(5px);
}

.footer-section i {
    color: #3498db;
    width: 16px;
}

.footer-social {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(52, 152, 219, 0.2);
    border-radius: 50%;
    color: #3498db;
    font-size: 1.2rem;
    transition: all 0.3s ease;
}

.social-link:hover {
    background: #3498db;
    color: white;
    transform: translateY(-3px);
}

.footer-hours h5 {
    color: #3498db;
    margin: 1rem 0 0.5rem 0;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.footer-bottom {
    border-top: 1px solid #34495e;
    padding-top: 1.5rem;
    margin-top: 2rem;
}

.footer-bottom-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.footer-links {
    display: flex;
    gap: 1.5rem;
}

.footer-links a {
    color: #bdc3c7;
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.3s ease;
}

.footer-links a:hover {
    color: #3498db;
}

.footer-credit {
    color: #95a5a6;
    font-size: 0.9rem;
    margin: 0;
}

/* Responsive Footer */
@media (max-width: 768px) {
    .footer {
        padding: 2rem 0 1rem;
    }
    
    .footer-content {
        grid-template-columns: 1fr;
        gap: 2rem;
        text-align: center;
    }
    
    .footer-bottom-content {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .footer-links {
        justify-content: center;
    }
    
    .footer-social {
        justify-content: center;
    }
}
</style>

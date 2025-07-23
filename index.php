<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to ABC Hospital</title>
    <?php echo getCommonCSS(); ?>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background-color: #f8f9fa;
        }

        .hero {
            height: 100vh;
            background: linear-gradient(135deg, rgba(1, 67, 110, 0.9), rgba(6, 100, 45, 0.8));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Neural Network Animation Canvas */
        #neural-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            padding: 0 20px;
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 4em;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            background: linear-gradient(45deg, #ffffffff, #ffffffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero .subtitle {
            font-size: 1.4em;
            margin-bottom: 30px;
            font-weight: 300;
            opacity: 0.95;
        }

        .hero .description {
            font-size: 1.1em;
            margin-bottom: 40px;
            opacity: 0.9;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            padding: 40px 0;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            padding: 35px 25px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(52, 152, 219, 0.1);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            transition: left 0.4s ease;
        }

        .feature-card:hover::before {
            left: 0;
        }

        .feature-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(52, 152, 219, 0.2);
        }

        .feature-icon {
            font-size: 3em;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #3498db, #2ecc71);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .feature-card h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 1.3em;
        }

        .feature-card p {
            color: #7f8c8d;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 25px;
            flex-wrap: wrap;
            margin: 60px 0;
        }

        .action-button {
            padding: 18px 35px;
            font-size: 1.1em;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .action-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .action-button:hover::before {
            left: 100%;
        }

        .action-button:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 35px rgba(0,0,0,0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
        }

        .btn-success {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
            background: rgba(44, 62, 80, 0.95);
            backdrop-filter: blur(10px);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(44, 62, 80, 0.98);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 1.8em;
            font-weight: 700;
            color: white;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
        }

        .logo i {
            margin-right: 12px;
            color: #3498db;
            font-size: 1.2em;
        }

        .logo img {
            max-height: 45px;
            margin-right: 12px;
            border-radius: 8px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .section-title {
            text-align: center;
            font-family: 'Poppins', sans-serif;
            font-size: 2.5em;
            font-weight: 600;
            margin-bottom: 50px;
            color: #2c3e50;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            border-radius: 2px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .section {
            padding: 80px 0;
        }

        .reviews-section {
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.05), rgba(46, 204, 113, 0.05));
            padding: 80px 0;
            position: relative;
        }

        /* DNA Helix Animation for Reviews Section */
        .dna-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .dna-strand {
            position: absolute;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, 
                rgba(52, 152, 219, 0.2) 0%,
                rgba(46, 204, 113, 0.2) 50%,
                rgba(52, 152, 219, 0.2) 100%);
            animation: dnaMove 10s linear infinite;
        }

        .dna-strand:nth-child(1) { left: 10%; animation-delay: 0s; }
        .dna-strand:nth-child(2) { left: 30%; animation-delay: -2s; }
        .dna-strand:nth-child(3) { right: 30%; animation-delay: -4s; }
        .dna-strand:nth-child(4) { right: 10%; animation-delay: -6s; }

        @keyframes dnaMove {
            0% { transform: translateY(-100%) rotate(0deg); }
            100% { transform: translateY(100vh) rotate(360deg); }
        }

        .review-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 4px solid #3498db;
            position: relative;
            z-index: 2;
        }

        .review-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .reviewer-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .stars {
            color: #f39c12;
            font-size: 1.2em;
            margin-top: 15px;
        }

        .about-section {
            background: white;
            padding: 80px 0;
            position: relative;
        }

        /* Heartbeat Animation for About Section */
        .heartbeat-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            opacity: 0.05;
            z-index: 1;
        }

        .heartbeat {
            color: #e74c3c;
            font-size: 200px;
            animation: heartbeat 1.5s ease-in-out infinite;
        }

        @keyframes heartbeat {
            0% { transform: scale(1); }
            14% { transform: scale(1.1); }
            28% { transform: scale(1); }
            42% { transform: scale(1.1); }
            70% { transform: scale(1); }
        }

        .about-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            font-size: 1.1em;
            line-height: 1.8;
            color: #555;
            position: relative;
            z-index: 2;
        }

        footer {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 40px 0;
            text-align: center;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .footer-section h4 {
            margin-bottom: 15px;
            font-family: 'Poppins', sans-serif;
        }

        .footer-section p, .footer-section a {
            color: #bdc3c7;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: #3498db;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5em;
            }
            
            .features {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .action-button {
                width: 80%;
            }

            .nav-links {
                display: none;
            }

            .navbar {
                padding: 1rem 3%;
            }
        }

        /* Pulse Animation for Medical Cross */
        .medical-pulse {
            position: absolute;
            top: 20%;
            right: 10%;
            width: 60px;
            height: 60px;
            z-index: 1;
        }

        .pulse-cross {
            color: rgba(255, 255, 255, 0.3);
            font-size: 60px;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { 
                transform: scale(1);
                opacity: 0.3;
            }
            50% { 
                transform: scale(1.2);
                opacity: 0.6;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar" id="navbar">
        <a href="index.php" class="logo">
            <i class="fas fa-hospital-symbol"></i>
            ABC Hospital
        </a>
        <div class="nav-links">
            <a href="#our-services"><i class="fas fa-stethoscope"></i> Our Services</a>
            <a href="#reviews"><i class="fas fa-star"></i> Reviews</a>
            <a href="#about-us"><i class="fas fa-info-circle"></i> About Us</a>
            <?php if (isLoggedIn()): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin_dashboard.php"><i class="fas fa-user-shield"></i> Admin Dashboard</a>
                <?php elseif ($_SESSION['role'] === 'doctor'): ?>
                    <a href="doctor_dashboard.php"><i class="fas fa-user-md"></i> Doctor Dashboard</a>
                <?php elseif ($_SESSION['role'] === 'receptionist'): ?>
                    <a href="receptionist_dashboard.php"><i class="fas fa-user-tie"></i> Receptionist Dashboard</a>
                <?php endif; ?>
                <a href="login.php?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="hero">
        <canvas id="neural-canvas"></canvas>
        <div class="medical-pulse">
            <i class="fas fa-plus pulse-cross"></i>
        </div>
        <div class="hero-content">
            <h1>Welcome to ABC Hospital</h1>
            <p class="subtitle">Your Health is Our Priority</p>
            <p class="description">Experience world-class healthcare with cutting-edge technology and compassionate care from our team of expert medical professionals.</p>
        </div>
    </div>

    <div class="container">
        <div class="action-buttons">
            <a href="book_appointment.php" class="action-button btn-success">
                <i class="fas fa-calendar-plus"></i> Book Appointment
            </a>
            <a href="check_appointment.php" class="action-button btn-primary">
                <i class="fas fa-search"></i> Check Appointment Status
            </a>
        </div>

        <section id="our-services" class="section">
            <h2 class="section-title">Available Services</h2>
            <div class="features">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h3>General Medicine</h3>
                    <p>Comprehensive healthcare services for all ages with experienced physicians providing personalized treatment plans.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-baby"></i>
                    </div>
                    <h3>Pediatrics</h3>
                    <p>Specialized care for children from newborn to adolescence with child-friendly environment and expert pediatricians.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <h3>Cardiology</h3>
                    <p>Advanced heart care with state-of-the-art equipment and renowned cardiologists for comprehensive cardiac treatment.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bone"></i>
                    </div>
                    <h3>Orthopedics</h3>
                    <p>Expert treatment for bone, joint, and muscle conditions with minimally invasive surgical techniques.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-ambulance"></i>
                    </div>
                    <h3>Emergency Care</h3>
                    <p>24/7 emergency services with rapid response team and fully equipped trauma center for urgent medical needs.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3>Neurology</h3>
                    <p>Advanced neurological care for brain and nervous system disorders with cutting-edge diagnostic technology.</p>
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
                <h2 class="section-title">Patient Reviews</h2>
                <div class="features">
                    <div class="review-card">
                        <div class="reviewer-name">
                            <i class="fas fa-user-circle"></i> Dr. Sarah Johnson
                        </div>
                        <p>"ABC Hospital provided exceptional care during my treatment. The staff was incredibly professional and compassionate throughout my recovery journey."</p>
                        <div class="stars">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                    </div>
                    <div class="review-card">
                        <div class="reviewer-name">
                            <i class="fas fa-user-circle"></i> Michael Chen
                        </div>
                        <p>"The medical team at ABC Hospital is outstanding. Their expertise and dedication to patient care is truly remarkable. Highly recommended!"</p>
                        <div class="stars">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                    </div>
                    <div class="review-card">
                        <div class="reviewer-name">
                            <i class="fas fa-user-circle"></i> Emily Rodriguez
                        </div>
                        <p>"From the moment I arrived, I felt truly cared for. The facilities are modern and the staff goes above and beyond for their patients."</p>
                        <div class="stars">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="about-us" class="about-section">
            <div class="heartbeat-bg">
                <i class="fas fa-heartbeat heartbeat"></i>
            </div>
            <div class="container">
                <h2 class="section-title">About ABC Hospital</h2>
                <div class="about-content">
                    <p>
                        <i class="fas fa-hospital"></i> ABC Hospital has been a beacon of hope and healing for over 50 years, providing world-class healthcare services to our community. Our mission is to deliver exceptional medical care with a focus on compassionate treatment and innovative solutions.
                    </p>
                    <br>
                    <p>
                        Our team of board-certified physicians, skilled nurses, and dedicated healthcare professionals work collaboratively to ensure the highest standards of care. We offer comprehensive medical services ranging from routine check-ups to complex surgical procedures, all delivered with cutting-edge technology and personalized attention.
                    </p>
                </div>
            </div>
        </section>
    </div>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4><i class="fas fa-hospital"></i> ABC Hospital</h4>
                    <p>Leading healthcare provider committed to excellence in medical care and patient satisfaction.</p>
                </div>
                <div class="footer-section">
                    <h4><i class="fas fa-phone"></i> Contact Info</h4>
                    <p>Emergency: +1234567890</p>
                    <p>General: +1234567891</p>
                    <p>Email: info@abchospital.com</p>
                </div>
                <div class="footer-section">
                    <h4><i class="fas fa-clock"></i> Operating Hours</h4>
                    <p>Emergency: 24/7</p>
                    <p>OPD: 8:00 AM - 8:00 PM</p>
                    <p>Pharmacy: 6:00 AM - 10:00 PM</p>
                </div>
            </div>
            <div style="border-top: 1px solid #34495e; padding-top: 20px; margin-top: 30px;">
                <p>© 2024 ABC Hospital. All rights reserved. | Designed with <i class="fas fa-heart" style="color: #e74c3c;"></i> for better healthcare</p>
            </div>
        </div>
    </footer>

    <!-- Neural Network Animation Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/particles.js/2.0.0/particles.min.js"></script>
    <script>
        // Neural Network Animation
        class NeuralNetwork {
            constructor(canvas) {
                this.canvas = canvas;
                this.ctx = canvas.getContext('2d');
                this.nodes = [];
                this.connections = [];
                this.resize();
                this.init();
                this.animate();
            }

            resize() {
                this.canvas.width = window.innerWidth;
                this.canvas.height = window.innerHeight;
            }

            init() {
                // Create nodes
                const nodeCount = Math.floor((this.canvas.width * this.canvas.height) / 15000);
                
                for (let i = 0; i < nodeCount; i++) {
                    this.nodes.push({
                        x: Math.random() * this.canvas.width,
                        y: Math.random() * this.canvas.height,
                        vx: (Math.random() - 0.5) * 0.5,
                        vy: (Math.random() - 0.5) * 0.5,
                        radius: Math.random() * 3 + 1,
                        alpha: Math.random() * 0.8 + 0.2
                    });
                }
            }

            updateNodes() {
                this.nodes.forEach(node => {
                    node.x += node.vx;
                    node.y += node.vy;

                    // Bounce off edges
                    if (node.x < 0 || node.x > this.canvas.width) node.vx *= -1;
                    if (node.y < 0 || node.y > this.canvas.height) node.vy *= -1;

                    // Keep nodes in bounds
                    node.x = Math.max(0, Math.min(this.canvas.width, node.x));
                    node.y = Math.max(0, Math.min(this.canvas.height, node.y));
                });
            }

            drawConnections() {
                const maxDistance = 120;
                
                for (let i = 0; i < this.nodes.length; i++) {
                    for (let j = i + 1; j < this.nodes.length; j++) {
                        const dx = this.nodes[i].x - this.nodes[j].x;
                        const dy = this.nodes[i].y - this.nodes[j].y;
                        const distance = Math.sqrt(dx * dx + dy * dy);

                        if (distance < maxDistance) {
                            const opacity = (1 - distance / maxDistance) * 0.5;
                            
                            // Create gradient for neural connection
                            const gradient = this.ctx.createLinearGradient(
                                this.nodes[i].x, this.nodes[i].y,
                                this.nodes[j].x, this.nodes[j].y
                            );
                            gradient.addColorStop(0, `rgba(52, 152, 219, ${opacity})`);
                            gradient.addColorStop(0.5, `rgba(46, 204, 113, ${opacity})`);
                            gradient.addColorStop(1, `rgba(52, 152, 219, ${opacity})`);

                            this.ctx.strokeStyle = gradient;
                            this.ctx.lineWidth = 1;
                            this.ctx.beginPath();
                            this.ctx.moveTo(this.nodes[i].x, this.nodes[i].y);
                            this.ctx.lineTo(this.nodes[j].x, this.nodes[j].y);
                            this.ctx.stroke();

                            // Add pulse effect
                            const pulsePhase = (Date.now() * 0.003 + distance * 0.1) % (Math.PI * 2);
                            const pulseIntensity = (Math.sin(pulsePhase) + 1) * 0.5;
                            
                            if (pulseIntensity > 0.8) {
                                this.ctx.strokeStyle = `rgba(255, 255, 255, ${opacity * 0.5})`;
                                this.ctx.lineWidth = 3;
                                this.ctx.stroke();
                            }
                        }
                    }
                }
            }

            drawNodes() {
                this.nodes.forEach(node => {
                    // Create radial gradient for nodes
                    const gradient = this.ctx.createRadialGradient(
                        node.x, node.y, 0,
                        node.x, node.y, node.radius
                    );
                    gradient.addColorStop(0, `rgba(255, 255, 255, ${node.alpha})`);
                    gradient.addColorStop(1, `rgba(52, 152, 219, ${node.alpha * 0.5})`);

                    this.ctx.fillStyle = gradient;
                    this.ctx.beginPath();
                    this.ctx.arc(node.x, node.y, node.radius, 0, Math.PI * 2);
                    this.ctx.fill();

                    // Add glow effect
                    this.ctx.shadowColor = 'rgba(52, 152, 219, 0.5)';
                    this.ctx.shadowBlur = 10;
                    this.ctx.fill();
                    this.ctx.shadowBlur = 0;
                });
            }

            animate() {
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                
                this.updateNodes();
                this.drawConnections();
                this.drawNodes();

                requestAnimationFrame(() => this.animate());
            }
        }

        // Initialize Neural Network
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('neural-canvas');
            const neuralNet = new NeuralNetwork(canvas);

            // Handle window resize
            window.addEventListener('resize', () => {
                neuralNet.resize();
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
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

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            const animatedElements = document.querySelectorAll('.feature-card, .review-card');
            animatedElements.forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });
    </script>
</body>
</html>

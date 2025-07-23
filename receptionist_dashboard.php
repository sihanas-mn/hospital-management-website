<?php
require_once 'config.php';

// Check if user is logged in and is a receptionist
checkRole(['receptionist']);

// Get receptionist details
$receptionist = getUserDetails($_SESSION['user_id'], 'receptionist');

// Handle appointment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_appointment'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $conn = getDBConnection();
    
    try {
        $conn->begin_transaction();
        
        // Update appointment_receptionist table
        $stmt = $conn->prepare("
            INSERT INTO appointment_receptionist (isConfirmed, receptionist_id, appointment_id)
            VALUES (1, ?, ?)
            ON DUPLICATE KEY UPDATE isConfirmed = 1
        ");
        $stmt->bind_param("ii", $receptionist['id'], $appointment_id);
        $stmt->execute();
        
        // Update appointment status
        $stmt = $conn->prepare("UPDATE appointment SET status = 1 WHERE id = ?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        
        $conn->commit();
        $success_message = "Appointment confirmed successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error confirming appointment: " . $e->getMessage();
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receptionist Dashboard - ABC Hospital</title>
    <link rel="stylesheet" href="styles.css">
    <?php echo getCommonCSS(); ?>
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
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: #2c3e50;
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            background: linear-gradient(135deg, #2c3e50, #34495e);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .logo {
            font-family: 'Poppins', sans-serif;
            font-size: 1.8em;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .logo::before {
            content: '\f0f8';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-right: 12px;
            color: #3498db;
        }

        .nav-links {
            display: flex;
            gap: 25px;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 120px 0 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin-top: 70px;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.1"><circle cx="30" cy="30" r="2"/></g></svg>');
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .dashboard-title {
            font-family: 'Poppins', sans-serif;
            font-size: 3em;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }

        .dashboard-subtitle {
            font-size: 1.2em;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .container {
            max-width: 1400px;
            margin: -30px auto 0;
            padding: 0 20px 60px;
            position: relative;
            z-index: 10;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border-left: 5px solid #3498db;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            transition: left 0.3s ease;
        }

        .stat-card:hover::before {
            left: 0;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .stat-icon {
            font-size: 2.5em;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #3498db, #2ecc71);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card h3 {
            color: #7f8c8d;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 2.5em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
            background: linear-gradient(135deg, #3498db, #2ecc71);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card {
            background: white;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            overflow: hidden;
            margin-top: 20px;
        }

        .card h2 {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 25px 30px;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            font-size: 1.5em;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .search-bar {
            padding: 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #ecf0f1;
        }

        .search-bar input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid #e8f4f8;
            border-radius: 12px;
            font-size: 16px;
            background: white;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
            position: relative;
        }

        .search-bar {
            position: relative;
        }

        .search-bar::before {
            content: '\f002';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 50px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            z-index: 2;
        }

        .search-bar input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
            outline: none;
            transform: translateY(-2px);
        }

        .filter-container {
            padding: 20px 30px;
            background: #f8f9fa;
            display: flex;
            gap: 15px;
            border-bottom: 1px solid #ecf0f1;
        }

        .filter-btn {
            padding: 12px 20px;
            border: 2px solid #e8f4f8;
            border-radius: 25px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            color: #7f8c8d;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border-color: #3498db;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .filter-btn:hover:not(.active) {
            border-color: #3498db;
            transform: translateY(-2px);
        }

        .appointment-card {
            background: white;
            padding: 25px 30px;
            margin: 0;
            border-bottom: 1px solid #ecf0f1;
            transition: all 0.3s ease;
            position: relative;
        }

        .appointment-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #3498db, #2ecc71);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .appointment-card:hover::before {
            transform: scaleY(1);
        }

        .appointment-card:hover {
            background: #f8f9fa;
            transform: translateX(10px);
        }

        .appointment-card:last-child {
            border-bottom: none;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header h3 {
            color: #2c3e50;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .appointment-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .appointment-details div {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid #3498db;
        }

        .appointment-details strong {
            color: #2c3e50;
            display: block;
            margin-bottom: 5px;
        }

        .btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
        }

        .btn.confirmed {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            cursor: default;
        }

        .btn.confirmed:hover {
            transform: none;
            box-shadow: none;
        }

        .alert {
            padding: 15px 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .hidden {
            display: none;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .filter-container {
                flex-wrap: wrap;
            }
            
            .appointment-details {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .navbar {
                padding: 1rem;
            }

            .nav-links {
                gap: 15px;
            }

            .dashboard-title {
                font-size: 2em;
            }

            .appointment-card:hover {
                transform: translateX(5px);
            }
        }

        .floating-action {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5em;
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.4);
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 100;
        }

        .floating-action:hover {
            transform: scale(1.1);
            box-shadow: 0 15px 35px rgba(52, 152, 219, 0.6);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">ABC Hospital</a>
        <div class="nav-links">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="login.php?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="dashboard-header">
        <div class="container">
            <h1 class="dashboard-title">Welcome, <?php echo htmlspecialchars($receptionist['name']); ?></h1>
            <p class="dashboard-subtitle">Receptionist Dashboard - Manage Hospital Appointments</p>
        </div>
    </div>

    <div class="container">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="stats-container">
            <?php
            $conn = getDBConnection();
            
            // Get pending appointments count
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointment WHERE status = 0");
            $stmt->execute();
            $pending_count = $stmt->get_result()->fetch_assoc()['count'];
            
            // Get today's appointments count
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointment_doctor WHERE date = CURDATE()");
            $stmt->execute();
            $today_count = $stmt->get_result()->fetch_assoc()['count'];
            
            // Get confirmed appointments count
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointment WHERE status = 1");
            $stmt->execute();
            $confirmed_count = $stmt->get_result()->fetch_assoc()['count'];
            
            $conn->close();
            ?>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Pending Appointments</h3>
                <div class="number"><?php echo $pending_count; ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <h3>Today's Appointments</h3>
                <div class="number"><?php echo $today_count; ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3>Confirmed Appointments</h3>
                <div class="number"><?php echo $confirmed_count; ?></div>
            </div>
        </div>

        <div class="card">
            <h2>
                <i class="fas fa-calendar-check"></i>
                Manage Appointments
            </h2>
            
            <div class="search-bar">
                <input type="text" id="searchAppointments" placeholder="Search appointments by patient name, doctor, or appointment ID..." onkeyup="searchAppointments()">
            </div>
            
            <div class="filter-container">
                <button class="filter-btn active" data-filter="all" onclick="filterAppointments('all')">
                    <i class="fas fa-list"></i> All
                </button>
                <button class="filter-btn" data-filter="pending" onclick="filterAppointments('pending')">
                    <i class="fas fa-clock"></i> Pending
                </button>
                <button class="filter-btn" data-filter="confirmed" onclick="filterAppointments('confirmed')">
                    <i class="fas fa-check-circle"></i> Confirmed
                </button>
            </div>

            <div id="appointmentsList">
                <?php
                $conn = getDBConnection();
                $query = "
                    SELECT 
                        a.id as appointment_id,
                        a.status,
                        a.reason,
                        p.name as patient_name,
                        p.contactNo as patient_contact,
                        d.name as doctor_name,
                        s.title as specialization,
                        ad.date,
                        ad.time,
                        ap.tokenNo
                    FROM appointment a
                    JOIN patient p ON a.patient_id = p.id
                    JOIN doctor d ON a.doctor_id = d.id
                    JOIN specialization s ON d.specialization_id = s.id
                    JOIN appointment_doctor ad ON a.id = ad.appointment_id
                    JOIN appointment_patient ap ON a.id = ap.appointment_id
                    ORDER BY ad.date ASC, ad.time ASC
                ";
                
                $result = $conn->query($query);
                if ($result->num_rows > 0):
                    while ($appointment = $result->fetch_assoc()):
                ?>
                    <div class="appointment-card" data-status="<?php echo $appointment['status'] ? 'confirmed' : 'pending'; ?>">
                        <div class="header">
                            <h3>
                                <i class="fas fa-clipboard-list"></i>
                                Appointment #<?php echo $appointment['appointment_id']; ?>
                            </h3>
                            <?php if (!$appointment['status']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                    <button type="submit" name="confirm_appointment" class="btn" onclick="return confirm('Confirm this appointment?')">
                                        <i class="fas fa-check"></i> Confirm Appointment
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="btn confirmed">
                                    <i class="fas fa-check-circle"></i> Confirmed
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="appointment-details">
                            <div>
                                <strong><i class="fas fa-user"></i> Patient:</strong>
                                <?php echo htmlspecialchars($appointment['patient_name']); ?><br>
                                <strong><i class="fas fa-phone"></i> Contact:</strong>
                                <?php echo htmlspecialchars($appointment['patient_contact']); ?>
                            </div>
                            <div>
                                <strong><i class="fas fa-user-md"></i> Doctor:</strong>
                                Dr. <?php echo htmlspecialchars($appointment['doctor_name']); ?><br>
                                <strong><i class="fas fa-stethoscope"></i> Specialization:</strong>
                                <?php echo htmlspecialchars($appointment['specialization']); ?>
                            </div>
                            <div>
                                <strong><i class="fas fa-calendar"></i> Date:</strong>
                                <?php echo formatDate($appointment['date']); ?><br>
                                <strong><i class="fas fa-clock"></i> Time:</strong>
                                <?php echo formatTime($appointment['time']); ?>
                            </div>
                            <div>
                                <strong><i class="fas fa-ticket-alt"></i> Token Number:</strong>
                                <?php echo $appointment['tokenNo']; ?><br>
                                <strong><i class="fas fa-notes-medical"></i> Reason:</strong>
                                <?php echo htmlspecialchars($appointment['reason']); ?>
                            </div>
                        </div>
                    </div>
                <?php 
                    endwhile;
                else:
                ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No appointments found</h3>
                        <p>There are currently no appointments in the system.</p>
                    </div>
                <?php endif; ?>
                <?php $conn->close(); ?>
            </div>
        </div>
    </div>

    <div class="floating-action" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </div>

    <script>
        function searchAppointments() {
            const searchInput = document.getElementById('searchAppointments').value.toLowerCase();
            const appointments = document.getElementsByClassName('appointment-card');
            let visibleCount = 0;
            
            for (let appointment of appointments) {
                const text = appointment.textContent.toLowerCase();
                if (text.includes(searchInput)) {
                    appointment.classList.remove('hidden');
                    visibleCount++;
                } else {
                    appointment.classList.add('hidden');
                }
            }
            
            // Show empty state if no results
            updateEmptyState(visibleCount === 0 && searchInput.length > 0);
        }

        function filterAppointments(status) {
            const appointments = document.getElementsByClassName('appointment-card');
            const filterBtns = document.getElementsByClassName('filter-btn');
            let visibleCount = 0;
            
            // Update active filter button
            for (let btn of filterBtns) {
                btn.classList.remove('active');
                if (btn.dataset.filter === status) {
                    btn.classList.add('active');
                }
            }
            
            // Filter appointments
            for (let appointment of appointments) {
                if (status === 'all' || appointment.dataset.status === status) {
                    appointment.classList.remove('hidden');
                    visibleCount++;
                } else {
                    appointment.classList.add('hidden');
                }
            }
            
            // Clear search when filtering
            document.getElementById('searchAppointments').value = '';
        }

        function updateEmptyState(show) {
            const existingEmptyState = document.querySelector('.search-empty-state');
            if (existingEmptyState) {
                existingEmptyState.remove();
            }
            
            if (show) {
                const emptyState = document.createElement('div');
                emptyState.className = 'empty-state search-empty-state';
                emptyState.innerHTML = `
                    <i class="fas fa-search"></i>
                    <h3>No results found</h3>
                    <p>Try adjusting your search criteria</p>
                `;
                document.getElementById('appointmentsList').appendChild(emptyState);
            }
        }

        // Smooth scroll to top
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Show floating action button on scroll
        window.addEventListener('scroll', function() {
            const floatingAction = document.querySelector('.floating-action');
            if (window.scrollY > 300) {
                floatingAction.style.display = 'flex';
            } else {
                floatingAction.style.display = 'none';
            }
        });

        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.getElementsByClassName('alert');
            for (let alert of alerts) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => alert.style.display = 'none', 300);
            }
        }, 5000);

        // Initialize page animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stats on load
            document.querySelectorAll('.stat-card .number').forEach(stat => {
                const target = parseInt(stat.textContent);
                if (!isNaN(target)) {
                    let current = 0;
                    const increment = target / 20;
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            stat.textContent = target;
                            clearInterval(timer);
                        } else {
                            stat.textContent = Math.floor(current);
                        }
                    }, 50);
                }
            });
            
            // Add stagger animation to appointment cards
            document.querySelectorAll('.appointment-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateX(-30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateX(0)';
                }, index * 100);
            });
        });

        // Enhanced search with debouncing
        let searchTimeout;
        document.getElementById('searchAppointments').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(searchAppointments, 300);
        });
    </script>
</body>
</html>

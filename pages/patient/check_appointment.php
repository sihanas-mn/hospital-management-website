<?php
require_once 'config.php';

$message = '';
$appointmentDetails = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reference_number'])) {
    $reference = sanitizeInput($_POST['reference_number']);
    $decoded = decodeReferenceNumber($reference);
    
    if ($decoded) {
        $conn = getDBConnection();
        
        // Comprehensive query to get all appointment details
        $query = "
            SELECT 
                a.id as appointment_id,
                a.status as appointment_status,
                a.reason,
                p.name as patient_name,
                p.contactNo as patient_contact,
                d.name as doctor_name,
                s.title as specialization,
                ad.date as appointment_date,
                ad.time as appointment_time,
                ap.tokenNo as token_number,
                ar.isConfirmed as is_confirmed,
                r.name as receptionist_name
            FROM appointment a
            JOIN patient p ON a.patient_id = p.id
            JOIN doctor d ON a.doctor_id = d.id
            JOIN specialization s ON d.specialization_id = s.id
            JOIN appointment_doctor ad ON a.id = ad.appointment_id
            JOIN appointment_patient ap ON a.id = ap.appointment_id
            LEFT JOIN appointment_receptionist ar ON a.id = ar.appointment_id
            LEFT JOIN receptionist r ON ar.receptionist_id = r.id
            WHERE p.id = ? AND d.id = ? AND ap.tokenNo = ? AND ad.id = ?
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiii", 
            $decoded['patient_id'],
            $decoded['doctor_id'],
            $decoded['token_no'],
            $decoded['appointment_doctor_id']
        );
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $appointmentDetails = $result->fetch_assoc();
        } else {
            $message = "No appointment found with the provided reference number.";
        }
        
        $stmt->close();
        $conn->close();
    } else {
        $message = "Invalid reference number format.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Appointment Status - ABC Hospital</title>
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

        .container {
            max-width: 1000px;
            margin: 80px auto 0;
            padding: 20px;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            text-align: center;
            border-radius: 25px 25px 0 0;
            position: relative;
            overflow: hidden;
            margin-bottom: 0;
        }

        .page-header::before {
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

        .page-header h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 2.8em;
            font-weight: 700;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
        }

        .page-header p {
            font-size: 1.2em;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .reference-form {
            background: white;
            padding: 40px;
            border-radius: 0 0 25px 25px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            position: relative;
        }

        .reference-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3498db, #2ecc71);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e8f4f8;
            border-radius: 12px;
            font-size: 16px;
            background: #f8f9fa;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            border-color: #3498db;
            background: white;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
            outline: none;
            transform: translateY(-2px);
        }

        .btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            display: inline-block;
            text-align: center;
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
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #7f8c8d, #95a5a6);
        }

        .btn-secondary:hover {
            box-shadow: 0 10px 25px rgba(127, 140, 141, 0.4);
        }

        .status-card {
            background: white;
            border-radius: 25px;
            padding: 40px;
            margin: 30px 0;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .status-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #3498db, #2ecc71, #e74c3c, #f39c12);
            background-size: 400% 400%;
            animation: gradientShift 3s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .status-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #ecf0f1;
        }

        .status-header h2 {
            color: #2c3e50;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .status-badge {
            padding: 12px 20px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 14px;
        }

        .status-confirmed {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
        }

        .status-pending {
            background: linear-gradient(135deg, #f39c12, #e67e22);
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .status-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #3498db;
            transition: all 0.3s ease;
        }

        .status-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .status-item strong {
            display: block;
            color: #2c3e50;
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-item span {
            color: #555;
            font-size: 16px;
            font-weight: 500;
        }

        .timeline {
            position: relative;
            margin: 30px 0;
            padding: 20px 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 30px;
            top: 0;
            width: 3px;
            height: 100%;
            background: linear-gradient(180deg, #3498db, #2ecc71);
        }

        .timeline-item {
            margin: 20px 0;
            padding: 20px 20px 20px 70px;
            background: #f8f9fa;
            border-radius: 12px;
            position: relative;
            transition: all 0.3s ease;
        }

        .timeline-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 25px;
            width: 20px;
            height: 20px;
            background: linear-gradient(135deg, #3498db, #2ecc71);
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .timeline-item strong {
            color: #2c3e50;
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .timeline-item p {
            color: #666;
            margin: 0;
            line-height: 1.6;
        }

        .alert {
            padding: 15px 25px;
            border-radius: 12px;
            margin: 30px 0;
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

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .text-center {
            text-align: center;
            margin-top: 30px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            .container {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 20px;
            }
            .status-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }

        @media (max-width: 768px) {
            .status-header {
                flex-direction: column;
                gap: 20px;
                align-items: flex-start;
            }
            
            .status-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                padding: 40px 20px;
            }
            
            .page-header h1 {
                font-size: 2.2em;
            }
            
            .reference-form {
                padding: 30px 20px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 80%;
            }

            .navbar {
                padding: 1rem;
            }

            .nav-links {
                gap: 15px;
            }

            .timeline-item {
                padding-left: 60px;
            }

            .timeline::before {
                left: 20px;
            }

            .timeline-item::before {
                left: 10px;
            }
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e8f4f8;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <nav class="navbar">
        <a href="index.php" class="logo">ABC Hospital</a>
        <div class="nav-links">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="book_appointment.php"><i class="fas fa-calendar-plus"></i> Book Appointment</a>
        </div>
    </nav>

    <div class="container">
        <div class="no-print">
            <div class="page-header">
                <h1><i class="fas fa-search"></i> Check Appointment Status</h1>
                <p>Enter your reference number to track your appointment</p>
            </div>
            <div class="reference-form">
                <form method="POST" action="" id="checkForm">
                    <div class="form-group">
                        <label for="reference_number">
                            <i class="fas fa-barcode"></i> Reference Number
                        </label>
                        <input type="text" 
                               id="reference_number" 
                               name="reference_number" 
                               class="form-control" 
                               placeholder="Enter your reference number (e.g., REF-0001-0001-001-0001)"
                               required
                               pattern="REF-\d{4}-\d{4}-\d{3}-\d{4}"
                               title="Please enter a valid reference number format"
                               value="<?php echo isset($_POST['reference_number']) ? htmlspecialchars($_POST['reference_number']) : ''; ?>">
                        <small style="color: #7f8c8d; margin-top: 5px; display: block;">
                            <i class="fas fa-info-circle"></i> 
                            Format: REF-0000-0000-000-0000
                        </small>
                    </div>
                    <button type="submit" class="btn" style="width: 100%;">
                        <i class="fas fa-search"></i> Check Status
                    </button>
                </form>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($appointmentDetails): ?>
            <div class="status-card">
                <div class="status-header">
                    <h2>
                        <i class="fas fa-clipboard-list"></i>
                        Appointment Details
                    </h2>
                    <span class="status-badge <?php echo $appointmentDetails['is_confirmed'] ? 'status-confirmed' : 'status-pending'; ?>">
                        <i class="fas fa-<?php echo $appointmentDetails['is_confirmed'] ? 'check-circle' : 'clock'; ?>"></i>
                        <?php echo $appointmentDetails['is_confirmed'] ? 'Confirmed' : 'Pending Confirmation'; ?>
                    </span>
                </div>

                <div class="status-grid">
                    <div class="status-item">
                        <strong><i class="fas fa-user"></i> Patient Name</strong>
                        <span><?php echo htmlspecialchars($appointmentDetails['patient_name']); ?></span>
                    </div>

                    <div class="status-item">
                        <strong><i class="fas fa-ticket-alt"></i> Token Number</strong>
                        <span><?php echo htmlspecialchars($appointmentDetails['token_number']); ?></span>
                    </div>

                    <div class="status-item">
                        <strong><i class="fas fa-user-md"></i> Doctor</strong>
                        <span>Dr. <?php echo htmlspecialchars($appointmentDetails['doctor_name']); ?></span>
                    </div>

                    <div class="status-item">
                        <strong><i class="fas fa-stethoscope"></i> Specialization</strong>
                        <span><?php echo htmlspecialchars($appointmentDetails['specialization']); ?></span>
                    </div>

                    <div class="status-item">
                        <strong><i class="fas fa-calendar"></i> Date</strong>
                        <span><?php echo formatDate($appointmentDetails['appointment_date']); ?></span>
                    </div>

                    <div class="status-item">
                        <strong><i class="fas fa-clock"></i> Time</strong>
                        <span><?php echo formatTime($appointmentDetails['appointment_time']); ?></span>
                    </div>

                    <div class="status-item">
                        <strong><i class="fas fa-phone"></i> Contact</strong>
                        <span><?php echo htmlspecialchars($appointmentDetails['patient_contact']); ?></span>
                    </div>

                    <div class="status-item">
                        <strong><i class="fas fa-notes-medical"></i> Reason</strong>
                        <span><?php echo htmlspecialchars($appointmentDetails['reason']); ?></span>
                    </div>
                </div>

                <div class="status-section">
                    <h3 style="color: #2c3e50; font-family: 'Poppins', sans-serif; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-timeline"></i> Appointment Timeline
                    </h3>
                    <div class="timeline">
                        <div class="timeline-item">
                            <strong><i class="fas fa-check"></i> Appointment Booked</strong>
                            <p>Your appointment has been successfully registered in our system and is awaiting confirmation.</p>
                        </div>

                        <?php if ($appointmentDetails['is_confirmed']): ?>
                            <div class="timeline-item">
                                <strong><i class="fas fa-user-check"></i> Appointment Confirmed</strong>
                                <p>
                                    Confirmed by: <?php echo htmlspecialchars($appointmentDetails['receptionist_name'] ?: 'Hospital Staff'); ?>
                                    <br>
                                    <small style="color: #7f8c8d;">Your appointment is now confirmed. Please arrive 15 minutes early.</small>
                                </p>
                            </div>

                            <?php if ($appointmentDetails['appointment_status']): ?>
                                <div class="timeline-item">
                                    <strong><i class="fas fa-check-double"></i> Appointment Completed</strong>
                                    <p>Your appointment has been completed. Thank you for visiting ABC Hospital.</p>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="timeline-item" style="opacity: 0.6;">
                                <strong><i class="fas fa-hourglass-half"></i> Awaiting Confirmation</strong>
                                <p>Your appointment is pending confirmation from our reception team. You will be notified once confirmed.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-center no-print">
                    <div class="action-buttons">
                        <button onclick="window.print()" class="btn">
                            <i class="fas fa-print"></i> Print Details
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-home"></i> Back to Home
                        </a>
                        <a href="book_appointment.php" class="btn btn-secondary">
                            <i class="fas fa-calendar-plus"></i> Book Another
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="floating-action no-print" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </div>

    <script>
        // Show loading overlay
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }

        // Enhanced form validation
        document.getElementById('checkForm').addEventListener('submit', function(e) {
            const refNumber = document.getElementById('reference_number').value.trim();
            const pattern = /^REF-\d{4}-\d{4}-\d{3}-\d{4}$/;
            
            if (!pattern.test(refNumber)) {
                e.preventDefault();
                showNotification('Please enter a valid reference number format: REF-0000-0000-000-0000', 'error');
                document.getElementById('reference_number').focus();
                return;
            }
            
            showLoading();
        });

        // Format reference number as user types
        document.getElementById('reference_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            
            if (value.length > 0) {
                value = 'REF-' + value;
                
                if (value.length > 8) {
                    value = value.slice(0, 8) + '-' + value.slice(8);
                }
                if (value.length > 13) {
                    value = value.slice(0, 13) + '-' + value.slice(13);
                }
                if (value.length > 17) {
                    value = value.slice(0, 17) + '-' + value.slice(17);
                }
                if (value.length > 22) {
                    value = value.slice(0, 22);
                }
            }
            
            e.target.value = value;
        });

        // Notification system
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                ${message}
            `;
            notification.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                background: ${type === 'success' ? 'linear-gradient(135deg, #d4edda, #c3e6cb)' : 'linear-gradient(135deg, #f8d7da, #f5c6cb)'};
                color: ${type === 'success' ? '#155724' : '#721c24'};
                padding: 15px 25px;
                border-radius: 12px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                z-index: 10000;
                animation: slideInRight 0.5s ease;
                border-left: 4px solid ${type === 'success' ? '#28a745' : '#dc3545'};
                max-width: 350px;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.5s ease';
                setTimeout(() => notification.remove(), 500);
            }, 4000);
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

        // Add CSS animations for notifications
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            hideLoading();
            
            // Auto-hide alerts
            setTimeout(function() {
                const alerts = document.getElementsByClassName('alert');
                for (let alert of alerts) {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => alert.style.display = 'none', 300);
                }
            }, 5000);

            // Add animation to status items
            document.querySelectorAll('.status-item').forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    item.style.transition = 'all 0.6s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Add animation to timeline items
            document.querySelectorAll('.timeline-item').forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateX(-30px)';
                setTimeout(() => {
                    item.style.transition = 'all 0.6s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, (index + 1) * 200);
            });
        });
    </script>
</body>
</html>
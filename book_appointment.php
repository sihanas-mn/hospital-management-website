<?php
require_once 'config.php';

// Handle AJAX request for doctors
if (isset($_GET['get_doctors'])) {
    $specialization_id = intval($_GET['get_doctors']);
    $doctors = getDoctorsBySpecializationAjax($specialization_id);
    header('Content-Type: application/json');
    echo json_encode($doctors);
    exit;
}

// Handle AJAX request for time slots
if (isset($_GET['get_time_slots'])) {
    $doctor_id = intval($_GET['doctor_id']);
    $date = $_GET['date'];
    $timeSlots = getAvailableTimeSlots($doctor_id, $date);
    header('Content-Type: application/json');
    echo json_encode($timeSlots);
    exit;
}

$successMessage = $errorMessage = '';
$selectedSpecialization = isset($_GET['specialization']) ? (int)$_GET['specialization'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = getDBConnection();
        
        // Sanitize inputs
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $contact = sanitizeInput($_POST['contact']);
        $address = sanitizeInput($_POST['address']);
        $gender = sanitizeInput($_POST['gender']);
        $dob = sanitizeInput($_POST['dob']);
        $doctor_id = (int)$_POST['doctor'];
        $appointment_date = sanitizeInput($_POST['appointment_date']);
        $appointment_time = sanitizeInput($_POST['appointment_time']);
        $reason = sanitizeInput($_POST['reason']);

        // Validate email
        if (!validateEmail($email)) {
            throw new Exception("Invalid email address");
        }

        // Start transaction
        $conn->begin_transaction();

        // Check if patient exists
        $stmt = $conn->prepare("SELECT id FROM patient WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $patient = $result->fetch_assoc();
            $patient_id = $patient['id'];
        } else {
            // Insert new patient
            $stmt = $conn->prepare("INSERT INTO patient (name, email, contactNo, address, DoB, gender) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $contact, $address, $dob, $gender);
            $stmt->execute();
            $patient_id = $conn->insert_id;
        }

        // Create appointment
        $stmt = $conn->prepare("INSERT INTO appointment (reason, doctor_id, patient_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $reason, $doctor_id, $patient_id);
        $stmt->execute();
        $appointment_id = $conn->insert_id;

        // Create doctor appointment
        $stmt = $conn->prepare("INSERT INTO appointment_doctor (date, time, doctor_id, appointment_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $appointment_date, $appointment_time, $doctor_id, $appointment_id);
        $stmt->execute();
        $appointment_doctor_id = $conn->insert_id;

        // Get next token number
        $stmt = $conn->prepare("SELECT COALESCE(MAX(tokenNo), 0) + 1 AS next_token FROM appointment_patient ap 
                               JOIN appointment a ON ap.appointment_id = a.id 
                               JOIN appointment_doctor ad ON a.id = ad.appointment_id 
                               WHERE ad.doctor_id = ? AND ad.date = ?");
        $stmt->bind_param("is", $doctor_id, $appointment_date);
        $stmt->execute();
        $token_result = $stmt->get_result();
        $token_row = $token_result->fetch_assoc();
        $token_no = $token_row['next_token'];

        // Create patient appointment
        $stmt = $conn->prepare("INSERT INTO appointment_patient (tokenNo, appointment_id, patient_id) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $token_no, $appointment_id, $patient_id);
        $stmt->execute();

        // Generate reference number
        $reference = generateReferenceNumber($patient_id, $doctor_id, $token_no, $appointment_doctor_id);

        $conn->commit();
        $successMessage = "Appointment booked successfully! Your reference number is: " . $reference;
        
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollback();
        }
        $errorMessage = "Error: " . $e->getMessage();
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - ABC Hospital</title>
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

        .booking-container {
            background: white;
            border-radius: 25px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            overflow: hidden;
            position: relative;
        }

        .booking-container::before {
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

        .booking-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .booking-header::before {
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

        .booking-header h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 2.8em;
            font-weight: 700;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
        }

        .booking-header p {
            font-size: 1.2em;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .form-container {
            padding: 50px 40px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .form-group {
            position: relative;
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

        .form-control:disabled {
            background: #ecf0f1;
            color: #7f8c8d;
            cursor: not-allowed;
        }

        .submit-btn {
            width: 100%;
            padding: 18px 30px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(52, 152, 219, 0.4);
        }

        .btn {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            margin: 5px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(46, 204, 113, 0.4);
            color: white;
            text-decoration: none;
        }

        .alert {
            padding: 15px 25px;
            border-radius: 12px;
            margin: 30px 40px;
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

        .reference-number {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left: 4px solid #28a745;
            padding: 25px;
            border-radius: 12px;
            margin: 30px 40px;
            text-align: center;
            font-weight: 600;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .text-center {
            text-align: center;
            padding: 20px 40px 40px;
        }

        .section-divider {
            margin: 30px 0;
            border-top: 2px solid #ecf0f1;
            position: relative;
        }

        .section-divider::after {
            content: 'Appointment Details';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 0 20px;
            color: #7f8c8d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 14px;
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

        .form-section {
            margin-bottom: 40px;
        }

        .form-section h3 {
            color: #2c3e50;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .booking-header {
                padding: 40px 20px;
            }
            
            .booking-header h1 {
                font-size: 2.2em;
            }
            
            .form-container {
                padding: 30px 20px;
            }
            
            .alert, .reference-number {
                margin: 20px;
            }
            
            .text-center {
                padding: 20px;
            }

            .navbar {
                padding: 1rem;
            }

            .nav-links {
                gap: 15px;
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
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <nav class="navbar">
        <a href="index.php" class="logo">ABC Hospital</a>
        <div class="nav-links">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="check_appointment.php"><i class="fas fa-search"></i> Check Appointment</a>
        </div>
    </nav>

    <div class="container">
        <div class="booking-container">
            <div class="booking-header">
                <h1><i class="fas fa-calendar-plus"></i> Book an Appointment</h1>
                <p>Schedule your visit with our expert medical professionals</p>
            </div>

            <?php if ($errorMessage): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>

            <?php if ($successMessage): ?>
                <div class="reference-number">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $successMessage; ?>
                </div>
                <div class="text-center">
                    <a href="check_appointment.php" class="btn">
                        <i class="fas fa-search"></i> Check Appointment Status
                    </a>
                    <a href="index.php" class="btn">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                </div>
            <?php else: ?>
                <div class="form-container">
                    <form method="POST" action="" id="appointmentForm">
                        <div class="form-section">
                            <h3><i class="fas fa-user"></i> Personal Information</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="name"><i class="fas fa-user"></i> Full Name *</label>
                                    <input type="text" id="name" name="name" class="form-control" required placeholder="Enter your full name">
                                </div>

                                <div class="form-group">
                                    <label for="email"><i class="fas fa-envelope"></i> Email Address *</label>
                                    <input type="email" id="email" name="email" class="form-control" required placeholder="your.email@example.com">
                                </div>

                                <div class="form-group">
                                    <label for="contact"><i class="fas fa-phone"></i> Contact Number *</label>
                                    <input type="tel" id="contact" name="contact" class="form-control" required placeholder="+1234567890">
                                </div>

                                <div class="form-group">
                                    <label for="dob"><i class="fas fa-calendar"></i> Date of Birth *</label>
                                    <input type="date" id="dob" name="dob" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="gender"><i class="fas fa-venus-mars"></i> Gender *</label>
                                    <select id="gender" name="gender" class="form-control" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="address"><i class="fas fa-map-marker-alt"></i> Address *</label>
                                    <input type="text" id="address" name="address" class="form-control" required placeholder="Enter your complete address">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3><i class="fas fa-stethoscope"></i> Appointment Details</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="specialization"><i class="fas fa-stethoscope"></i> Select Specialization *</label>
                                    <select name="specialization" id="specialization" class="form-control" required>
                                        <option value="">Select Specialization</option>
                                        <?php
                                        $specializations = getAllSpecializations();
                                        foreach($specializations as $spec) {
                                            echo "<option value='" . $spec['id'] . "'>" . htmlspecialchars($spec['title']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="doctor"><i class="fas fa-user-md"></i> Select Doctor *</label>
                                    <select name="doctor" id="doctor" class="form-control" required disabled>
                                        <option value="">First Select Specialization</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="appointment_date"><i class="fas fa-calendar"></i> Select Date *</label>
                                    <input type="date" name="appointment_date" id="appointment_date" 
                                           class="form-control" min="<?php echo date('Y-m-d'); ?>" 
                                           required disabled>
                                </div>

                                <div class="form-group">
                                    <label for="appointment_time"><i class="fas fa-clock"></i> Select Time *</label>
                                    <select name="appointment_time" id="appointment_time" 
                                            class="form-control" required disabled>
                                        <option value="">First Select Date</option>
                                    </select>
                                </div>

                                <div class="form-group" style="grid-column: 1 / -1;">
                                    <label for="reason"><i class="fas fa-notes-medical"></i> Reason for Visit *</label>
                                    <textarea id="reason" name="reason" class="form-control" rows="4" required placeholder="Please describe your symptoms or reason for the visit"></textarea>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn">
                            <i class="fas fa-calendar-plus"></i> Book Appointment
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="floating-action" onclick="scrollToTop()">
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

        // Unified functions for handling form interactions
        function getDoctors() {
            const specializationId = document.getElementById('specialization').value;
            const doctorSelect = document.getElementById('doctor');
            const dateInput = document.getElementById('appointment_date');
            const timeSelect = document.getElementById('appointment_time');
            
            // Reset dependent fields
            doctorSelect.disabled = true;
            dateInput.disabled = true;
            timeSelect.disabled = true;
            
            if (!specializationId) {
                doctorSelect.innerHTML = '<option value="">First Select Specialization</option>';
                return;
            }

            showLoading();
            fetch(`book_appointment.php?get_doctors=${specializationId}`)
                .then(response => response.json())
                .then(doctors => {
                    let options = '<option value="">Select Doctor</option>';
                    doctors.forEach(doctor => {
                        options += `<option value="${doctor.id}">Dr. ${doctor.name}</option>`;
                    });
                    doctorSelect.innerHTML = options;
                    doctorSelect.disabled = false;
                    hideLoading();
                })
                .catch(error => {
                    console.error('Error:', error);
                    hideLoading();
                    showNotification('Error loading doctors. Please try again.', 'error');
                });
        }

        function getTimeSlots() {
            const doctorId = document.getElementById('doctor').value;
            const date = document.getElementById('appointment_date').value;
            const timeSelect = document.getElementById('appointment_time');
            
            if (!doctorId || !date) {
                timeSelect.disabled = true;
                timeSelect.innerHTML = '<option value="">First Select Doctor and Date</option>';
                return;
            }

            showLoading();
            fetch(`book_appointment.php?get_time_slots=1&doctor_id=${doctorId}&date=${date}`)
                .then(response => response.json())
                .then(timeSlots => {
                    let options = '<option value="">Select Time</option>';
                    if (timeSlots.length === 0) {
                        options = '<option value="">No available time slots</option>';
                    } else {
                        timeSlots.forEach(slot => {
                            const time = new Date('2000-01-01T' + slot).toLocaleTimeString('en-US', {
                                hour: 'numeric',
                                minute: '2-digit',
                                hour12: true
                            });
                            options += `<option value="${slot}">${time}</option>`;
                        });
                    }
                    timeSelect.innerHTML = options;
                    timeSelect.disabled = timeSlots.length === 0;
                    hideLoading();
                })
                .catch(error => {
                    console.error('Error:', error);
                    hideLoading();
                    showNotification('Error loading time slots. Please try again.', 'error');
                });
        }

        // Event Listeners
        document.getElementById('specialization').addEventListener('change', getDoctors);
        
        document.getElementById('doctor').addEventListener('change', function() {
            const dateInput = document.getElementById('appointment_date');
            const timeSelect = document.getElementById('appointment_time');
            
            dateInput.disabled = !this.value;
            timeSelect.disabled = true;
            timeSelect.innerHTML = '<option value="">First Select Date</option>';
            
            if (this.value) {
                const today = new Date();
                const tomorrow = new Date(today);
                tomorrow.setDate(tomorrow.getDate() + 1);
                dateInput.min = tomorrow.toISOString().split('T')[0];
            }
        });

        document.getElementById('appointment_date').addEventListener('change', getTimeSlots);

        // Form Validation
        document.getElementById('appointmentForm').addEventListener('submit', function(e) {
            const today = new Date();
            const selectedDate = new Date(document.getElementById('appointment_date').value);
            const dob = new Date(document.getElementById('dob').value);
            const contact = document.getElementById('contact').value;

            if (dob >= today) {
                e.preventDefault();
                showNotification('Please enter a valid date of birth', 'error');
                document.getElementById('dob').focus();
                return;
            }

            if (selectedDate <= today) {
                e.preventDefault();
                showNotification('Please select a future date for the appointment', 'error');
                document.getElementById('appointment_date').focus();
                return;
            }

            // Contact validation
            if (!/^\+?[\d\s\-\(\)]{10,}$/.test(contact)) {
                e.preventDefault();
                showNotification('Please enter a valid contact number', 'error');
                document.getElementById('contact').focus();
                return;
            }

            // Show loading on form submission
            showLoading();
            this.querySelector('.submit-btn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Booking Appointment...';
            this.querySelector('.submit-btn').disabled = true;
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
            // Auto-hide alerts
            setTimeout(function() {
                const alerts = document.getElementsByClassName('alert');
                for (let alert of alerts) {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => alert.style.display = 'none', 300);
                }
            }, 5000);

            // Set DOB max to 100 years ago
            const dobInput = document.getElementById('dob');
            const today = new Date();
            const maxDate = new Date(today.getFullYear() - 100, today.getMonth(), today.getDate());
            const minDate = new Date(today.getFullYear() - 5, today.getMonth(), today.getDate());
            
            dobInput.max = minDate.toISOString().split('T')[0];
            dobInput.min = maxDate.toISOString().split('T')[0];
        });
    </script>
</body>
</html>
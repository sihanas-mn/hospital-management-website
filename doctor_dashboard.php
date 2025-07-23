<?php
require_once 'config.php';

// Check if user is logged in and is a doctor
checkRole(['doctor']);

$doctor_id = null;
$doctor_details = null;
$success_message = '';
$error_message = '';

// Get doctor details
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT d.*, s.title as specialization FROM doctor d 
                       JOIN specialization s ON d.specialization_id = s.id 
                       WHERE d.user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$doctor_details = $stmt->get_result()->fetch_assoc();
$doctor_id = $doctor_details['id'];

// Handle profile update
if (isset($_POST['update_profile'])) {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $contact = sanitizeInput($_POST['contact']);
    $address = sanitizeInput($_POST['address']);
    
    $stmt = $conn->prepare("UPDATE doctor SET name=?, email=?, contactNo=?, address=? WHERE id=?");
    $stmt->bind_param("ssssi", $name, $email, $contact, $address, $doctor_id);
    
    if ($stmt->execute()) {
        $success_message = "Profile updated successfully!";
        $doctor_details['name'] = $name;
        $doctor_details['email'] = $email;
        $doctor_details['contactNo'] = $contact;
        $doctor_details['address'] = $address;
    } else {
        $error_message = "Error updating profile!";
    }
}

// Handle schedule creation
if (isset($_POST['create_schedule'])) {
    $date = sanitizeInput($_POST['schedule_date']);
    $time = sanitizeInput($_POST['schedule_time']);
    
    if (isTimeSlotAvailable($doctor_id, $date, $time)) {
        $stmt = $conn->prepare("INSERT INTO appointment_doctor (date, time, doctor_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $date, $time, $doctor_id);
        
        if ($stmt->execute()) {
            $success_message = "Schedule created successfully!";
        } else {
            $error_message = "Error creating schedule!";
        }
    } else {
        $error_message = "This time slot is already booked!";
    }
}

// Handle schedule deletion
if (isset($_POST['delete_schedule'])) {
    $schedule_id = sanitizeInput($_POST['schedule_id']);
    
    // First check if this schedule has any appointments
    $check_stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM appointment a
        JOIN appointment_patient ap ON a.id = ap.appointment_id
        WHERE a.id = ?
    ");
    $check_stmt->bind_param("i", $schedule_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result()->fetch_assoc();
    
    if ($result['count'] == 0) {
        // No appointments, safe to delete
        $stmt = $conn->prepare("DELETE FROM appointment_doctor WHERE id = ? AND doctor_id = ?");
        $stmt->bind_param("ii", $schedule_id, $doctor_id);
        
        if ($stmt->execute()) {
            $success_message = "Schedule deleted successfully!";
        } else {
            $error_message = "Error deleting schedule!";
        }
    } else {
        $error_message = "Cannot delete schedule with existing appointments!";
    }
}

// Get all created schedules
$schedule_stmt = $conn->prepare("
    SELECT id, date, time 
    FROM appointment_doctor 
    WHERE doctor_id = ? AND date >= CURDATE()
    ORDER BY date ASC, time ASC
");
$schedule_stmt->bind_param("i", $doctor_id);
$schedule_stmt->execute();
$created_schedules = $schedule_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get upcoming appointments
// Get upcoming appointments
$stmt = $conn->prepare("
    SELECT 
        a.id as appointment_id,
        p.name as patient_name,
        p.contactNo as patient_contact,
        ad.date,
        ad.time,
        ap.tokenNo,
        a.reason,
        ar.isConfirmed
    FROM appointment a
    JOIN appointment_doctor ad ON a.id = ad.appointment_id
    JOIN appointment_patient ap ON a.id = ap.appointment_id
    JOIN patient p ON ap.patient_id = p.id
    JOIN appointment_receptionist ar ON a.id = ar.appointment_id
    WHERE a.doctor_id = ? AND ad.date >= CURDATE()
    ORDER BY ad.date ASC, ad.time ASC
");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$upcoming_appointments = [];
while($row = $result->fetch_assoc()) {
    $upcoming_appointments[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - ABC Hospital</title>
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

        .nav-links span {
            color: #ecf0f1;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 16px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
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
            max-width: 1400px;
            margin: 80px auto 0;
            padding: 20px;
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

        .stat-card h3 {
            font-size: 2.5em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #3498db, #2ecc71);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card p {
            color: #7f8c8d;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9em;
        }

        .stat-card.specialization h3 {
            font-size: 1.5em;
        }

        .tab-container {
            background: white;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            overflow: hidden;
            margin-top: 20px;
        }

        .tab-buttons {
            display: flex;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 0;
            margin: 0;
        }

        .tab-button {
            flex: 1;
            padding: 20px 30px;
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            color: #7f8c8d;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 16px;
        }

        .tab-button::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            transition: width 0.3s ease;
        }

        .tab-button.active {
            background: white;
            color: #2c3e50;
            box-shadow: 0 -5px 15px rgba(0,0,0,0.05);
        }

        .tab-button.active::after {
            width: 100%;
        }

        .tab-content {
            display: none;
            padding: 40px;
            animation: fadeIn 0.5s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        .card h2 {
            color: #2c3e50;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile-section {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
        }

        .btn-success {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
        }

        .btn-success:hover {
            box-shadow: 0 10px 25px rgba(46, 204, 113, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            padding: 8px 16px;
            font-size: 14px;
        }

        .btn-danger:hover {
            box-shadow: 0 10px 25px rgba(231, 76, 60, 0.4);
        }

        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .schedule-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            border-left: 4px solid #3498db;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .schedule-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(52, 152, 219, 0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.3s ease;
            opacity: 0;
        }

        .schedule-card:hover::before {
            opacity: 1;
            transform: rotate(45deg) translate(50%, 50%);
        }

        .schedule-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        .schedule-card p {
            margin: 8px 0;
            font-weight: 500;
        }

        .appointment-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 4px solid #3498db;
            transition: all 0.3s ease;
        }

        .appointment-card:hover {
            transform: translateX(5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .appointment-header h3 {
            color: #2c3e50;
            margin: 0;
        }

        .status-confirmed {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .status-pending {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .appointment-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .appointment-details p {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 0;
            border-left: 3px solid #3498db;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
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

        @media (max-width: 768px) {
            .profile-section {
                grid-template-columns: 1fr;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .tab-buttons {
                flex-direction: column;
            }
            
            .schedule-grid {
                grid-template-columns: 1fr;
            }
            
            .appointment-details {
                grid-template-columns: 1fr;
            }

            .navbar {
                padding: 1rem;
            }

            .nav-links {
                gap: 15px;
            }

            .nav-links span {
                display: none;
            }
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
            <span><i class="fas fa-user-md"></i> Dr. <?php echo htmlspecialchars($doctor_details['name']); ?></span>
            <a href="login.php?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <?php
        if ($success_message) {
            echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i> $success_message</div>";
        }
        if ($error_message) {
            echo "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle'></i> $error_message</div>";
        }
        ?>

        <div class="stats-container">
            <div class="stat-card">
                <h3><?php echo count($upcoming_appointments); ?></h3>
                <p><i class="fas fa-calendar-check"></i> Upcoming Appointments</p>
            </div>
            <div class="stat-card">
                <h3><?php 
                    $confirmed = array_filter($upcoming_appointments, function($apt) {
                        return $apt['isConfirmed'] == 1;
                    });
                    echo count($confirmed);
                ?></h3>
                <p><i class="fas fa-check-circle"></i> Confirmed Appointments</p>
            </div>
            <div class="stat-card specialization">
                <h3><?php echo $doctor_details['specialization']; ?></h3>
                <p><i class="fas fa-stethoscope"></i> Specialization</p>
            </div>
        </div>

        <div class="tab-container">
            <div class="tab-buttons">
                <button class="tab-button active" onclick="openTab('profile')">
                    <i class="fas fa-user"></i> Profile
                </button>
                <button class="tab-button" onclick="openTab('schedule')">
                    <i class="fas fa-calendar-plus"></i> Create Schedule
                </button>
                <button class="tab-button" onclick="openTab('appointments')">
                    <i class="fas fa-calendar-check"></i> Appointments
                </button>
            </div>

            <!-- Profile Tab -->
            <div id="profile" class="tab-content active">
                <div class="card">
                    <h2><i class="fas fa-user-md"></i> Doctor Profile</h2>
                    <form method="POST" class="profile-section">
                        <div class="info-section">
                            <div class="form-group">
                                <label><i class="fas fa-id-badge"></i> Doctor ID</label>
                                <input type="text" class="form-control" value="<?php echo $doctor_details['id']; ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-stethoscope"></i> Specialization</label>
                                <input type="text" class="form-control" value="<?php echo $doctor_details['specialization']; ?>" disabled>
                            </div>
                        </div>
                        <div class="edit-section">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($doctor_details['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($doctor_details['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> Contact Number</label>
                                <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($doctor_details['contactNo']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-map-marker-alt"></i> Address</label>
                                <textarea name="address" class="form-control" required rows="4"><?php echo htmlspecialchars($doctor_details['address']); ?></textarea>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-success">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Schedule Tab -->
            <div id="schedule" class="tab-content">
                <div class="card">
                    <h2><i class="fas fa-calendar-plus"></i> Create Appointment Schedule</h2>
                    <form method="POST" id="scheduleForm">
                        <div class="form-group">
                            <label><i class="fas fa-calendar"></i> Select Date</label>
                            <input type="date" name="schedule_date" class="form-control" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-clock"></i> Select Time</label>
                            <input type="time" name="schedule_time" class="form-control" required>
                        </div>
                        <button type="submit" name="create_schedule" class="btn btn-success">
                            <i class="fas fa-plus"></i> Create Schedule
                        </button>
                    </form>
                    
                    <div class="card" style="margin-top: 30px;">
                        <h3><i class="fas fa-list"></i> Created Time Slots</h3>
                        <?php if (empty($created_schedules)): ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <p>No time slots created yet</p>
                            </div>
                        <?php else: ?>
                            <div class="schedule-grid">
                                <?php foreach ($created_schedules as $schedule): ?>
                                    <div class="schedule-card">
                                        <p><i class="fas fa-calendar"></i> <strong><?php echo formatDate($schedule['date']); ?></strong></p>
                                        <p><i class="fas fa-clock"></i> <?php echo formatTime($schedule['time']); ?></p>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this schedule?');" style="margin-top: 15px;">
                                            <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">
                                            <button type="submit" name="delete_schedule" class="btn btn-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Appointments Tab -->
            <div id="appointments" class="tab-content">
                <div class="card">
                    <h2><i class="fas fa-calendar-check"></i> Upcoming Appointments</h2>
                    <?php if (empty($upcoming_appointments)): ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <p>No upcoming appointments scheduled</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcoming_appointments as $appointment): ?>
                            <div class="appointment-card">
                                <div class="appointment-header">
                                    <h3><i class="fas fa-user"></i> <?php echo htmlspecialchars($appointment['patient_name']); ?></h3>
                                    <span class="<?php echo $appointment['isConfirmed'] ? 'status-confirmed' : 'status-pending'; ?>">
                                        <i class="fas fa-<?php echo $appointment['isConfirmed'] ? 'check-circle' : 'clock'; ?>"></i>
                                        <?php echo $appointment['isConfirmed'] ? 'Confirmed' : 'Pending'; ?>
                                    </span>
                                </div>
                                <div class="appointment-details">
                                    <p><i class="fas fa-calendar"></i> <strong>Date:</strong> <?php echo formatDate($appointment['date']); ?></p>
                                    <p><i class="fas fa-clock"></i> <strong>Time:</strong> <?php echo formatTime($appointment['time']); ?></p>
                                    <p><i class="fas fa-ticket-alt"></i> <strong>Token:</strong> <?php echo $appointment['tokenNo']; ?></p>
                                    <p><i class="fas fa-phone"></i> <strong>Contact:</strong> <?php echo htmlspecialchars($appointment['patient_contact']); ?></p>
                                    <p><i class="fas fa-notes-medical"></i> <strong>Reason:</strong> <?php echo htmlspecialchars($appointment['reason']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="floating-action" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </div>

    <script>
        function openTab(tabName) {
            // Hide all tab contents
            const tabContents = document.getElementsByClassName('tab-content');
            for (let content of tabContents) {
                content.classList.remove('active');
            }
            
            // Deactivate all tab buttons
            const tabButtons = document.getElementsByClassName('tab-button');
            for (let button of tabButtons) {
                button.classList.remove('active');
            }
            
            // Show selected tab content and activate button
            document.getElementById(tabName).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        // Form validation for schedule creation
        document.getElementById('scheduleForm').addEventListener('submit', function(e) {
            const date = new Date(this.schedule_date.value);
            const time = this.schedule_time.value;
            
            // Prevent scheduling in the past
            if (date < new Date().setHours(0,0,0,0)) {
                e.preventDefault();
                alert('Cannot schedule appointments in the past!');
                return;
            }
            
            // Weekend validation (optional)
            if (date.getDay() === 0 || date.getDay() === 6) {
                if (!confirm('Are you sure you want to schedule on a weekend?')) {
                    e.preventDefault();
                    return;
                }
            }
        });

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

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.getElementsByClassName('alert');
            for (let alert of alerts) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => alert.style.display = 'none', 300);
            }
        }, 5000);

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Add stagger animation to cards
            document.querySelectorAll('.stat-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>
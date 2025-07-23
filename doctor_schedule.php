<?php
require_once 'config.php';

// Check if user is logged in as doctor
checkRole(['doctor']);

$doctor_id = null;
$success_message = $error_message = '';

// Get doctor details
$stmt = getDBConnection()->prepare("SELECT id FROM doctor WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if($row = $result->fetch_assoc()) {
    $doctor_id = $row['id'];
}
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    if (isset($_POST['add_schedule'])) {
        $date = sanitizeInput($_POST['date']);
        $times = $_POST['times'];
        
        // Validate date
        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            $error_message = "Cannot set schedule for past dates";
        } else {
            $stmt = $conn->prepare("INSERT INTO appointment_doctor (date, time, doctor_id) VALUES (?, ?, ?)");
            
            foreach ($times as $time) {
                if (isTimeSlotAvailable($doctor_id, $date, $time)) {
                    $stmt->bind_param("ssi", $date, $time, $doctor_id);
                    $stmt->execute();
                }
            }
            $success_message = "Schedule updated successfully";
        }
        $stmt->close();
    }
    
    if (isset($_POST['delete_slot'])) {
        $slot_id = (int)$_POST['slot_id'];
        $stmt = $conn->prepare("DELETE FROM appointment_doctor WHERE id = ? AND doctor_id = ? AND NOT EXISTS (SELECT 1 FROM appointment WHERE appointment_id = appointment_doctor.id)");
        $stmt->bind_param("ii", $slot_id, $doctor_id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $success_message = "Time slot deleted successfully";
        } else {
            $error_message = "Cannot delete booked time slot";
        }
        $stmt->close();
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schedule - ABC Hospital</title>
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
            max-width: 1400px;
            margin: 80px auto 0;
            padding: 20px;
        }

        h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 30px;
            text-align: center;
        }

        .schedule-form {
            background: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
        }

        .schedule-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3498db, #2ecc71);
        }

        .schedule-form h2 {
            color: #2c3e50;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
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

        .time-slots {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 15px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .time-slot {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            padding: 12px 15px;
            border-radius: 8px;
            border: 2px solid #ecf0f1;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .time-slot:hover {
            border-color: #3498db;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.2);
        }

        .time-slot input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .time-slot label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
            color: #2c3e50;
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

        .schedule-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .schedule-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 4px solid #3498db;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .schedule-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            transition: left 0.3s ease;
        }

        .schedule-card:hover::before {
            left: 0;
        }

        .schedule-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .schedule-card h3 {
            color: #2c3e50;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .time-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 12px 0;
            padding: 10px 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 3px solid #3498db;
        }

        .time-item span {
            font-weight: 500;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .delete-btn {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 12px;
            font-weight: 600;
        }

        .delete-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }

        .booked-status {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
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

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .empty-state i {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .time-slots {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .schedule-list {
                grid-template-columns: 1fr;
            }
            
            .navbar {
                padding: 1rem;
            }
            
            .nav-links {
                gap: 15px;
            }

            h1 {
                font-size: 2em;
            }

            .time-item {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
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
            <a href="doctor_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="doctor_appointments.php"><i class="fas fa-calendar-check"></i> View Appointments</a>
            <a href="login.php?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1><i class="fas fa-calendar-plus"></i> Manage Schedule</h1>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="schedule-form">
            <h2><i class="fas fa-plus-circle"></i> Add Available Time Slots</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="date"><i class="fas fa-calendar"></i> Select Date:</label>
                    <input type="date" id="date" name="date" class="form-control" 
                           min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-clock"></i> Select Time Slots:</label>
                    <div class="time-slots">
                        <?php
                        $start = strtotime('09:00');
                        $end = strtotime('17:00');
                        $interval = 30 * 60; // 30 minutes

                        for ($time = $start; $time <= $end; $time += $interval) {
                            $timeStr = date('H:i', $time);
                            echo "<div class='time-slot'>";
                            echo "<input type='checkbox' name='times[]' value='$timeStr' id='time_$timeStr'>";
                            echo "<label for='time_$timeStr'>" . date('h:i A', $time) . "</label>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
                
                <button type="submit" name="add_schedule" class="btn">
                    <i class="fas fa-plus"></i> Add Time Slots
                </button>
            </form>
        </div>

        <h2><i class="fas fa-list"></i> Current Schedule</h2>
        <div class="schedule-list">
            <?php
            $conn = getDBConnection();
            $stmt = $conn->prepare("
                SELECT ad.id, ad.date, ad.time, 
                       CASE WHEN a.id IS NOT NULL THEN 1 ELSE 0 END as is_booked
                FROM appointment_doctor ad
                LEFT JOIN appointment a ON a.id = ad.appointment_id
                WHERE ad.doctor_id = ? AND ad.date >= CURDATE()
                ORDER BY ad.date, ad.time
            ");
            $stmt->bind_param("i", $doctor_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $current_date = null;
            $hasSchedules = false;
            while ($row = $result->fetch_assoc()) {
                $hasSchedules = true;
                if ($current_date !== $row['date']) {
                    if ($current_date !== null) {
                        echo "</div>";
                    }
                    $current_date = $row['date'];
                    echo "<div class='schedule-card'>";
                    echo "<h3><i class='fas fa-calendar'></i> " . formatDate($row['date']) . "</h3>";
                }
                
                echo "<div class='time-item'>";
                echo "<span><i class='fas fa-clock'></i> " . formatTime($row['time']) . "</span>";
                if (!$row['is_booked']) {
                    echo "<form method='POST' style='display: inline;'>";
                    echo "<input type='hidden' name='slot_id' value='{$row['id']}'>";
                    echo "<button type='submit' name='delete_slot' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this time slot?\")'>";
                    echo "<i class='fas fa-trash'></i> Delete";
                    echo "</button>";
                    echo "</form>";
                } else {
                    echo "<span class='booked-status'><i class='fas fa-check'></i> Booked</span>";
                }
                echo "</div>";
            }
            if ($current_date !== null) {
                echo "</div>";
            }
            
            if (!$hasSchedules) {
                echo "<div class='empty-state'>";
                echo "<i class='fas fa-calendar-times'></i>";
                echo "<h3>No schedules created yet</h3>";
                echo "<p>Create your first schedule using the form above</p>";
                echo "</div>";
            }
            
            $stmt->close();
            $conn->close();
            ?>
        </div>
    </div>

    <div class="floating-action" onclick="window.location.href='doctor_dashboard.php'">
        <i class="fas fa-home"></i>
    </div>

    <script>
        // Add date validation
        document.getElementById('date').addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            today.setHours(0,0,0,0);
            
            if (selectedDate < today) {
                alert('Please select a future date');
                this.value = '';
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
            document.querySelectorAll('.schedule-card').forEach((card, index) => {
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
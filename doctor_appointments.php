<?php
require_once 'config.php';
checkRole(['doctor']);

$doctor_id = $_SESSION['user_id'];
$message = '';

// Get doctor's details
$doctor_details = getUserDetails($_SESSION['user_id'], 'doctor');

// Get today's date
$today = date('Y-m-d');

// Filter parameters
$date_filter = isset($_GET['date']) ? $_GET['date'] : $today;
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Get appointments
$conn = getDBConnection();
$query = "
    SELECT 
        a.id as appointment_id,
        p.name as patient_name,
        p.contactNo as patient_contact,
        ad.date,
        ad.time,
        a.status,
        a.reason,
        ap.tokenNo
    FROM appointment a
    JOIN appointment_doctor ad ON a.id = ad.appointment_id
    JOIN patient p ON a.patient_id = p.id
    JOIN appointment_receptionist ar ON a.id = ar.appointment_id
    WHERE a.doctor_id = ? 
    AND ad.date = ?
    AND ar.isConfirmed = 1
";

if ($status_filter !== 'all') {
    $query .= " AND a.status = " . ($status_filter === 'completed' ? '1' : '0');
}

$query .= " ORDER BY ad.time ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("is", $doctor_details['id'], $date_filter);
$stmt->execute();
$result = $stmt->get_result();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $appointment_id = $_POST['appointment_id'];
    $new_status = $_POST['new_status'];
    
    $update_stmt = $conn->prepare("UPDATE appointment SET status = ? WHERE id = ? AND doctor_id = ?");
    $update_stmt->bind_param("iii", $new_status, $appointment_id, $doctor_details['id']);
    
    if ($update_stmt->execute()) {
        $message = "Appointment status updated successfully!";
        // Refresh the page to show updated data
        header("Location: doctor_appointments.php?date=" . $date_filter . "&status=" . $status_filter);
        exit();
    } else {
        $message = "Error updating appointment status.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Appointments - ABC Hospital</title>
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

        .filters {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            display: flex;
            gap: 25px;
            margin-bottom: 30px;
            align-items: end;
        }

        .form-group {
            flex: 1;
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

        .appointment-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 4px solid #3498db;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .appointment-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            transition: left 0.3s ease;
        }

        .appointment-card:hover::before {
            left: 0;
        }

        .appointment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .appointment-time {
            font-weight: 700;
            color: #2c3e50;
            font-size: 1.2em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .token-number {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .appointment-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .detail-item {
            background: #f8f9fa;
            padding: 12px 15px;
            border-radius: 8px;
            border-left: 3px solid #3498db;
        }

        .detail-item strong {
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .appointment-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #ecf0f1;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-pending {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
        }

        .status-completed {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
        }

        .btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
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

        .empty-state {
            background: white;
            padding: 60px 20px;
            border-radius: 15px;
            text-align: center;
            color: #7f8c8d;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .empty-state i {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.5;
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

        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
                gap: 20px;
            }
            
            .appointment-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .appointment-footer {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
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

            h1 {
                font-size: 2em;
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
        <a href="doctor_dashboard.php" class="logo">ABC Hospital</a>
        <div class="nav-links">
            <a href="doctor_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="doctor_schedule.php"><i class="fas fa-calendar-plus"></i> Manage Schedule</a>
            <a href="doctor_appointments.php"><i class="fas fa-calendar-check"></i> View Appointments</a>
            <a href="login.php?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1><i class="fas fa-calendar-check"></i> My Appointments</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="filters">
            <div class="form-group">
                <label><i class="fas fa-calendar"></i> Date</label>
                <input type="date" 
                       class="form-control" 
                       value="<?php echo $date_filter; ?>" 
                       onchange="window.location.href='?date='+this.value+'&status=<?php echo $status_filter; ?>'"
                       min="<?php echo $today; ?>">
            </div>

            <div class="form-group">
                <label><i class="fas fa-filter"></i> Status</label>
                <select class="form-control" 
                        onchange="window.location.href='?date=<?php echo $date_filter; ?>&status='+this.value">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Appointments</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending Only</option>
                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed Only</option>
                </select>
            </div>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($appointment = $result->fetch_assoc()): ?>
                <div class="appointment-card">
                    <div class="appointment-header">
                        <span class="appointment-time">
                            <i class="fas fa-clock"></i>
                            <?php echo formatTime($appointment['time']); ?>
                        </span>
                        <span class="token-number">
                            <i class="fas fa-ticket-alt"></i>
                            Token: <?php echo $appointment['tokenNo']; ?>
                        </span>
                    </div>
                    
                    <div class="appointment-details">
                        <div class="detail-item">
                            <strong><i class="fas fa-user"></i> Patient:</strong>
                            <?php echo htmlspecialchars($appointment['patient_name']); ?>
                        </div>
                        <div class="detail-item">
                            <strong><i class="fas fa-phone"></i> Contact:</strong>
                            <?php echo htmlspecialchars($appointment['patient_contact']); ?>
                        </div>
                        <div class="detail-item">
                            <strong><i class="fas fa-notes-medical"></i> Reason:</strong>
                            <?php echo htmlspecialchars($appointment['reason']); ?>
                        </div>
                    </div>
                    
                    <div class="appointment-footer">
                        <span class="status-badge <?php echo $appointment['status'] ? 'status-completed' : 'status-pending'; ?>">
                            <i class="fas fa-<?php echo $appointment['status'] ? 'check-circle' : 'clock'; ?>"></i>
                            <?php echo $appointment['status'] ? 'Completed' : 'Pending'; ?>
                        </span>
                        
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                            <input type="hidden" name="new_status" value="<?php echo $appointment['status'] ? '0' : '1'; ?>">
                            <button type="submit" name="update_status" class="btn">
                                <i class="fas fa-<?php echo $appointment['status'] ? 'undo' : 'check'; ?>"></i>
                                Mark as <?php echo $appointment['status'] ? 'Pending' : 'Completed'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h3>No appointments found</h3>
                <p>No appointments scheduled for <?php echo formatDate($date_filter); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div class="floating-action" onclick="window.location.href='doctor_dashboard.php'">
        <i class="fas fa-home"></i>
    </div>

    <script>
        // Auto-refresh every 5 minutes
        setTimeout(function() {
            window.location.reload();
        }, 300000);

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
            document.querySelectorAll('.appointment-card').forEach((card, index) => {
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
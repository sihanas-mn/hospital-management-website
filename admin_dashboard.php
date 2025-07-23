<?php
require_once 'config.php';
checkRole(['admin']);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();
    
    // Add Doctor
    if (isset($_POST['add_doctor'])) {
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $gender = sanitizeInput($_POST['gender']);
        $contact = sanitizeInput($_POST['contact']);
        $address = sanitizeInput($_POST['address']);
        $specialization = (int)$_POST['specialization'];
        $username = sanitizeInput($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // First create user
        $stmt = $conn->prepare("INSERT INTO user (role, username, password) VALUES ('doctor', ?, ?)");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $user_id = $conn->insert_id;

        // Then create doctor
        $stmt = $conn->prepare("INSERT INTO doctor (name, gender, address, contactNo, email, specialization_id, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssii", $name, $gender, $address, $contact, $email, $specialization, $user_id);
        $stmt->execute();
        $_SESSION['message'] = "Doctor added successfully!";
    }

    // Add Receptionist
    if (isset($_POST['add_receptionist'])) {
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $gender = sanitizeInput($_POST['gender']);
        $contact = sanitizeInput($_POST['contact']);
        $address = sanitizeInput($_POST['address']);
        $username = sanitizeInput($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // First create user
        $stmt = $conn->prepare("INSERT INTO user (role, username, password) VALUES ('receptionist', ?, ?)");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $user_id = $conn->insert_id;

        // Then create receptionist
        $stmt = $conn->prepare("INSERT INTO receptionist (name, email, gender, contactNo, address, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $name, $email, $gender, $contact, $address, $user_id);
        $stmt->execute();
        $_SESSION['message'] = "Receptionist added successfully!";
    }

    // Delete Doctor
    if (isset($_POST['delete_doctor'])) {
        $doctor_id = (int)$_POST['doctor_id'];
        $stmt = $conn->prepare("DELETE FROM user WHERE id = (SELECT user_id FROM doctor WHERE id = ?)");
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        $_SESSION['message'] = "Doctor deleted successfully!";
    }

    // Delete Receptionist
    if (isset($_POST['delete_receptionist'])) {
        $receptionist_id = (int)$_POST['receptionist_id'];
        $stmt = $conn->prepare("DELETE FROM user WHERE id = (SELECT user_id FROM receptionist WHERE id = ?)");
        $stmt->bind_param("i", $receptionist_id);
        $stmt->execute();
        $_SESSION['message'] = "Receptionist deleted successfully!";
    }

    $conn->close();
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch existing doctors and receptionists
$conn = getDBConnection();
$doctors = $conn->query("
    SELECT d.*, s.title as specialization, u.username 
    FROM doctor d 
    JOIN specialization s ON d.specialization_id = s.id 
    JOIN user u ON d.user_id = u.id
    ORDER BY d.name
");

$receptionists = $conn->query("
    SELECT r.*, u.username 
    FROM receptionist r 
    JOIN user u ON r.user_id = u.id
    ORDER BY r.name
");

$specializations = $conn->query("SELECT * FROM specialization ORDER BY title");
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ABC Hospital</title>
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

        .container.dashboard-container {
            max-width: 1400px;
            margin: -30px auto 0;
            padding: 0 20px 60px;
            position: relative;
            z-index: 10;
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

        .stat-number {
            font-size: 2.5em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #7f8c8d;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9em;
        }

        .section-title {
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

        .card {
            background: white;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            margin-bottom: 40px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        .form-container {
            padding: 40px 30px;
        }

        .form-container h3 {
            color: #2c3e50;
            font-size: 1.3em;
            font-weight: 600;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-container h3::before {
            content: '\f055';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: #3498db;
        }

        .grid-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
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
        }

        .btn-danger:hover {
            box-shadow: 0 10px 25px rgba(231, 76, 60, 0.4);
        }

        .btn-small {
            padding: 8px 16px;
            font-size: 0.85em;
        }

        .list-container {
            padding: 30px;
            background: #f8f9fa;
        }

        .list-container h3 {
            color: #2c3e50;
            font-size: 1.3em;
            font-weight: 600;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .list-container h3::before {
            content: '\f03a';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: #3498db;
        }

        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        thead {
            background: linear-gradient(135deg, #34495e, #2c3e50);
            color: #ecf0f1;
        }

        th, td {
            padding: 18px 15px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        th {
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9em;
            color: black;
        }

        tbody tr {
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background: #f8f9fa;
            transform: scale(1.01);
        }

        .action-column {
            width: 120px;
            text-align: center;
        }

        .message {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            padding: 15px 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.5s ease;
        }

        .message::before {
            content: '\f058';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }

        .tooltip {
            position: relative;
            display: inline-block;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            width: 120px;
            background-color: #2c3e50;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -60px;
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 12px;
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
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
            .dashboard-title {
                font-size: 2em;
            }
            
            .grid-form {
                grid-template-columns: 1fr;
            }
            
            .dashboard-stats {
                grid-template-columns: 1fr;
            }
            
            .navbar {
                padding: 1rem;
            }
            
            .nav-links {
                gap: 15px;
            }
            
            .floating-action {
                bottom: 20px;
                right: 20px;
            }
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
        <h1 class="dashboard-title">Admin Dashboard</h1>
        <p class="dashboard-subtitle">Manage Hospital Staff & Operations</p>
    </div>

    <div class="container dashboard-container">
        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="message"><i class="fas fa-check-circle"></i> ' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']);
        }
        ?>

        <!-- Dashboard Stats -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="stat-number"><?php echo $doctors->num_rows; ?></div>
                <div class="stat-label">Total Doctors</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-number"><?php echo $receptionists->num_rows; ?></div>
                <div class="stat-label">Total Receptionists</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-stethoscope"></i>
                </div>
                <div class="stat-number"><?php echo $specializations->num_rows; ?></div>
                <div class="stat-label">Specializations</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-hospital"></i>
                </div>
                <div class="stat-number">24/7</div>
                <div class="stat-label">Emergency Care</div>
            </div>
        </div>

        <!-- Doctors Section -->
        <div class="card">
            <h2 class="section-title"><i class="fas fa-user-md"></i> Manage Doctors</h2>
            
            <!-- Add Doctor Form -->
            <div class="form-container">
                <h3><i class="fas fa-plus-circle"></i> Add New Doctor</h3>
                <form method="POST" class="grid-form">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="Enter doctor's full name">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" name="email" class="form-control" required placeholder="doctor@example.com">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-venus-mars"></i> Gender</label>
                        <select name="gender" class="form-control" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Contact Number</label>
                        <input type="text" name="contact" class="form-control" required placeholder="+1234567890">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Address</label>
                        <textarea name="address" class="form-control" required placeholder="Enter complete address" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-stethoscope"></i> Specialization</label>
                        <select name="specialization" class="form-control" required>
                            <option value="">Select Specialization</option>
                            <?php while ($spec = $specializations->fetch_assoc()): ?>
                                <option value="<?php echo $spec['id']; ?>">
                                    <?php echo htmlspecialchars($spec['title']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-user-shield"></i> Username</label>
                        <input type="text" name="username" class="form-control" required placeholder="Create username">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="Create secure password" minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="add_doctor" class="btn btn-success">
                            <i class="fas fa-plus"></i> Add Doctor
                        </button>
                    </div>
                </form>
            </div>

            <!-- Doctors List -->
            <div class="list-container">
                <h3><i class="fas fa-list"></i> Registered Doctors</h3>
                <?php if ($doctors->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th><i class="fas fa-user"></i> Name</th>
                                    <th><i class="fas fa-user-shield"></i> Username</th>
                                    <th><i class="fas fa-envelope"></i> Email</th>
                                    <th><i class="fas fa-phone"></i> Contact</th>
                                    <th><i class="fas fa-stethoscope"></i> Specialization</th>
                                    <th><i class="fas fa-cogs"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $doctors->data_seek(0); while ($doctor = $doctors->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($doctor['name']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['username']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['contactNo']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                                        <td class="action-column">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="doctor_id" value="<?php echo $doctor['id']; ?>">
                                                <button type="submit" name="delete_doctor" class="btn btn-danger btn-small tooltip" 
                                                        onclick="return confirm('Are you sure you want to delete Dr. <?php echo htmlspecialchars($doctor['name']); ?>?')">
                                                    <i class="fas fa-trash"></i>
                                                    <span class="tooltiptext">Delete Doctor</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-user-md"></i>
                        <h4>No Doctors Found</h4>
                        <p>No doctors have been registered yet. Add the first doctor using the form above.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Receptionists Section -->
        <div class="card">
            <h2 class="section-title"><i class="fas fa-user-tie"></i> Manage Receptionists</h2>
            
            <!-- Add Receptionist Form -->
            <div class="form-container">
                <h3><i class="fas fa-plus-circle"></i> Add New Receptionist</h3>
                <form method="POST" class="grid-form">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="Enter receptionist's full name">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" name="email" class="form-control" required placeholder="receptionist@example.com">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-venus-mars"></i> Gender</label>
                        <select name="gender" class="form-control" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Contact Number</label>
                        <input type="text" name="contact" class="form-control" required placeholder="+1234567890">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Address</label>
                        <textarea name="address" class="form-control" required placeholder="Enter complete address" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-user-shield"></i> Username</label>
                        <input type="text" name="username" class="form-control" required placeholder="Create username">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="Create secure password" minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="add_receptionist" class="btn btn-success">
                            <i class="fas fa-plus"></i> Add Receptionist
                        </button>
                    </div>
                </form>
            </div>

            <!-- Receptionists List -->
            <div class="list-container">
                <h3><i class="fas fa-list"></i> Registered Receptionists</h3>
                <?php if ($receptionists->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th><i class="fas fa-user"></i> Name</th>
                                    <th><i class="fas fa-user-shield"></i> Username</th>
                                    <th><i class="fas fa-envelope"></i> Email</th>
                                    <th><i class="fas fa-phone"></i> Contact</th>
                                    <th><i class="fas fa-cogs"></i> Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $receptionists->data_seek(0); while ($receptionist = $receptionists->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($receptionist['name']); ?></td>
                                        <td><?php echo htmlspecialchars($receptionist['username']); ?></td>
                                        <td><?php echo htmlspecialchars($receptionist['email']); ?></td>
                                        <td><?php echo htmlspecialchars($receptionist['contactNo']); ?></td>
                                        <td class="action-column">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="receptionist_id" value="<?php echo $receptionist['id']; ?>">
                                                <button type="submit" name="delete_receptionist" class="btn btn-danger btn-small tooltip"
                                                        onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($receptionist['name']); ?>?')">
                                                    <i class="fas fa-trash"></i>
                                                    <span class="tooltiptext">Delete Receptionist</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-user-tie"></i>
                        <h4>No Receptionists Found</h4>
                        <p>No receptionists have been registered yet. Add the first receptionist using the form above.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="floating-action" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </div>

    <script>
        // Enhanced form validation with better UX
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const password = this.querySelector('input[type="password"]');
                const contact = this.querySelector('input[name="contact"]');
                const email = this.querySelector('input[type="email"]');
                
                // Password validation
                if (password && password.value.length < 6) {
                    e.preventDefault();
                    showNotification('Password must be at least 6 characters long', 'error');
                    password.focus();
                    return;
                }
                
                // Contact validation
                if (contact && !/^\+?[\d\s\-\(\)]{10,}$/.test(contact.value)) {
                    e.preventDefault();
                    showNotification('Please enter a valid contact number', 'error');
                    contact.focus();
                    return;
                }
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
                
                // Re-enable if form submission fails
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
            });
        });

        // Enhanced table interactions
        document.querySelectorAll('tbody tr').forEach(row => {
            row.addEventListener('mouseover', function() {
                this.style.backgroundColor = '#f8f9fa';
                this.style.transform = 'scale(1.01)';
            });
            row.addEventListener('mouseout', function() {
                this.style.backgroundColor = '';
                this.style.transform = 'scale(1)';
            });
        });

        // Notification system
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                ${message}
            `;
            notification.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                background: ${type === 'success' ? 'linear-gradient(135deg, #2ecc71, #27ae60)' : 'linear-gradient(135deg, #e74c3c, #c0392b)'};
                color: white;
                padding: 15px 25px;
                border-radius: 12px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                z-index: 10000;
                animation: slideInRight 0.5s ease;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.5s ease';
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }

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

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stats on load
            document.querySelectorAll('.stat-number').forEach(stat => {
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
            
            // Add stagger animation to cards
            document.querySelectorAll('.card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
</body>
</html>
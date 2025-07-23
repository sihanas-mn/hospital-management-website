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

        .container.dashboard-container {
            max-width: 1400px;
            margin: 80px auto 0;
            padding: 20px;
            position: relative;
            z-index: 10;
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

        .section-title::before {
            content: '\f0f8';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: #3498db;
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

        @media (max-width: 768px) {
            .grid-form {
                grid-template-columns: 1fr;
            }
            
            .navbar {
                padding: 1rem;
            }
            
            .nav-links {
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">ABC Hospital</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="login.php?logout=1">Logout</a>
        </div>
    </nav>

    <div class="container dashboard-container">
        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="message">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']);
        }
        ?>

        <!-- Doctors Section -->
        <div class="card">
            <h2 class="section-title">Manage Doctors</h2>
            
            <!-- Add Doctor Form -->
            <div class="form-container">
                <h3>Add New Doctor</h3>
                <form method="POST" class="grid-form">
                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Gender:</label>
                        <select name="gender" class="form-control" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Contact Number:</label>
                        <input type="text" name="contact" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Address:</label>
                        <textarea name="address" class="form-control" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Specialization:</label>
                        <select name="specialization" class="form-control" required>
                            <?php while ($spec = $specializations->fetch_assoc()): ?>
                                <option value="<?php echo $spec['id']; ?>">
                                    <?php echo htmlspecialchars($spec['title']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Username:</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Password:</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="add_doctor" class="btn">Add Doctor</button>
                    </div>
                </form>
            </div>

            <!-- Doctors List -->
            <div class="list-container">
                <h3>Registered Doctors</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Specialization</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($doctor = $doctors->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($doctor['name']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['username']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['contactNo']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                                <td class="action-column">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="doctor_id" value="<?php echo $doctor['id']; ?>">
                                        <button type="submit" name="delete_doctor" class="btn btn-danger btn-small" 
                                                onclick="return confirm('Are you sure you want to delete this doctor?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Receptionists Section -->
        <div class="card">
            <h2 class="section-title">Manage Receptionists</h2>
            
            <!-- Add Receptionist Form -->
            <div class="form-container">
                <h3>Add New Receptionist</h3>
                <form method="POST" class="grid-form">
                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Gender:</label>
                        <select name="gender" class="form-control" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Contact Number:</label>
                        <input type="text" name="contact" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Address:</label>
                        <textarea name="address" class="form-control" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Username:</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Password:</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="add_receptionist" class="btn">Add Receptionist</button>
                    </div>
                </form>
            </div>

            <!-- Receptionists List -->
            <div class="list-container">
                <h3>Registered Receptionists</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($receptionist = $receptionists->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($receptionist['name']); ?></td>
                                <td><?php echo htmlspecialchars($receptionist['username']); ?></td>
                                <td><?php echo htmlspecialchars($receptionist['email']); ?></td>
                                <td><?php echo htmlspecialchars($receptionist['contactNo']); ?></td>
                                <td class="action-column">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="receptionist_id" value="<?php echo $receptionist['id']; ?>">
                                        <button type="submit" name="delete_receptionist" class="btn btn-danger btn-small"
                                                onclick="return confirm('Are you sure you want to delete this receptionist?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="main.js"></script>
</body>
</html>
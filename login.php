<?php
require_once 'config.php';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Process login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $error = "";

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        $conn = getDBConnection();
        
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, role, password FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role
                switch ($user['role']) {
                    case 'admin':
                        header("Location: admin_dashboard.php");
                        break;
                    case 'doctor':
                        header("Location: doctor_dashboard.php");
                        break;
                    case 'receptionist':
                        header("Location: receptionist_dashboard.php");
                        break;
                }
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "Username not found";
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ABC Hospital</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.05"><circle cx="30" cy="30" r="2"/></g></svg>');
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .login-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 50px 40px;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            animation: slideUp 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
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

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .hospital-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3498db, #2ecc71);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2.5em;
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .login-header h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }

        .login-header p {
            color: #7f8c8d;
            font-size: 16px;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 25px;
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

        .input-container {
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid #e8f4f8;
            border-radius: 15px;
            font-size: 16px;
            background: #f8f9fa;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            border-color: #3498db;
            background: white;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
            outline: none;
            transform: translateY(-2px);
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .form-control:focus + .input-icon {
            color: #3498db;
        }

        .submit-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #3498db, #2ecc71);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
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

        .submit-btn:active {
            transform: translateY(-1px);
        }

        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 30px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            padding: 12px 25px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            color: white;
        }

        .back-link i {
            margin-right: 8px;
        }

        .error-message {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 15px 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            animation: shake 0.6s ease-in-out;
        }

        .error-message i {
            margin-right: 10px;
            font-size: 16px;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .password-toggle {
            position: relative;
        }

        .toggle-icon {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #7f8c8d;
            font-size: 18px;
            transition: color 0.3s ease;
            z-index: 10;
        }

        .toggle-icon:hover {
            color: #3498db;
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation: float1 6s ease-in-out infinite;
        }

        .shape:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 70%;
            right: 10%;
            animation: float2 8s ease-in-out infinite;
        }

        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            bottom: 10%;
            left: 20%;
            animation: float3 7s ease-in-out infinite;
        }

        @keyframes float1 {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        @keyframes float2 {
            0%, 100% { transform: translateY(0px) rotate(360deg); }
            50% { transform: translateY(-30px) rotate(180deg); }
        }

        @keyframes float3 {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-25px) rotate(360deg); }
        }

        @media (max-width: 768px) {
            .login-container {
                padding: 15px;
            }
            
            .login-card {
                padding: 30px 25px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
            
            .hospital-icon {
                width: 60px;
                height: 60px;
                font-size: 2em;
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
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="hospital-icon">
                    <i class="fas fa-hospital-symbol"></i>
                </div>
                <h1>ABC Hospital</h1>
                <p>Sign in to access your dashboard</p>
            </div>

            <?php if (isset($error) && !empty($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return validateForm()" id="loginForm">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <div class="input-container">
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               required autocomplete="username">
                        <i class="input-icon fas fa-user"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="password-toggle">
                        <div class="input-container">
                            <input type="password" id="password" name="password" class="form-control" 
                                   required autocomplete="current-password">
                            <i class="input-icon fas fa-lock"></i>
                        </div>
                        <i class="toggle-icon fas fa-eye" onclick="togglePassword()"></i>
                    </div>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
        </div>
        
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Homepage
        </a>
    </div>

    <script>
        function validateForm() {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                showError('Please fill in all fields');
                return false;
            }
            
            if (username.length < 3) {
                showError('Username must be at least 3 characters long');
                return false;
            }
            
            if (password.length < 6) {
                showError('Password must be at least 6 characters long');
                return false;
            }
            
            return true;
        }

        function showError(message) {
            // Remove existing error if any
            const existingError = document.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            // Create new error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
            
            // Insert before form
            const form = document.querySelector('form');
            form.parentNode.insertBefore(errorDiv, form);
        }

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'toggle-icon fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'toggle-icon fas fa-eye';
            }
        }

        // Enhanced form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            if (validateForm()) {
                const submitBtn = document.getElementById('submitBtn');
                const loadingOverlay = document.getElementById('loadingOverlay');
                
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
                submitBtn.style.opacity = '0.8';
                submitBtn.disabled = true;
                
                loadingOverlay.style.display = 'flex';
                
                // If validation fails on server side, re-enable button
                setTimeout(() => {
                    if (window.location.href === window.location.href) {
                        submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
                        submitBtn.style.opacity = '1';
                        submitBtn.disabled = false;
                        loadingOverlay.style.display = 'none';
                    }
                }, 5000);
            }
        });

        // Add focus animations to form controls
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Add typing animation effect
        document.addEventListener('DOMContentLoaded', function() {
            const title = document.querySelector('.login-header h1');
            const text = title.textContent;
            title.textContent = '';
            
            let i = 0;
            const typeWriter = () => {
                if (i < text.length) {
                    title.textContent += text.charAt(i);
                    i++;
                    setTimeout(typeWriter, 100);
                }
            };
            
            setTimeout(typeWriter, 500);
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Alt + H to go home
            if (e.altKey && e.key === 'h') {
                e.preventDefault();
                window.location.href = 'index.php';
            }
        });
    </script>
</body>
</html>

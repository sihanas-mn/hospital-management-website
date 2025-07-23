<?php
if (!defined('INCLUDED_CONFIG')) {
    require_once __DIR__ . '/../config.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ABC Hospital' : 'ABC Hospital'; ?></title>
    
    <!-- External CSS Libraries -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/components.css">
    
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/<?php echo $css; ?>.css">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($inline_css)): ?>
        <style><?php echo $inline_css; ?></style>
    <?php endif; ?>
</head>
<body class="<?php echo isset($body_class) ? $body_class : ''; ?>">
    <?php if (isset($show_loading) && $show_loading): ?>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>
    <?php endif; ?>

    <?php if (!isset($hide_navbar) || !$hide_navbar): ?>
    <nav class="navbar<?php echo isset($navbar_class) ? ' ' . $navbar_class : ''; ?>" id="navbar">
        <a href="<?php echo BASE_URL; ?>/index.php" class="logo">
            <img src="<?php echo BASE_URL; ?>/assets/images/logoabc.jpg" alt="ABC Hospital" class="logo-img">
            ABC Hospital
        </a>
        <div class="nav-links">
            <?php if (isLoggedIn()): ?>
                <span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="<?php echo BASE_URL; ?>/pages/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <?php elseif ($_SESSION['role'] === 'doctor'): ?>
                    <a href="<?php echo BASE_URL; ?>/pages/doctor/dashboard.php"><i class="fas fa-stethoscope"></i> Dashboard</a>
                    <a href="<?php echo BASE_URL; ?>/pages/doctor/appointments.php"><i class="fas fa-calendar-check"></i> Appointments</a>
                    <a href="<?php echo BASE_URL; ?>/pages/doctor/schedule.php"><i class="fas fa-calendar-plus"></i> Schedule</a>
                <?php elseif ($_SESSION['role'] === 'receptionist'): ?>
                    <a href="<?php echo BASE_URL; ?>/pages/receptionist/dashboard.php"><i class="fas fa-desktop"></i> Dashboard</a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/login.php?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/index.php"><i class="fas fa-home"></i> Home</a>
                <a href="<?php echo BASE_URL; ?>/pages/patient/book_appointment.php"><i class="fas fa-calendar-plus"></i> Book Appointment</a>
                <a href="<?php echo BASE_URL; ?>/pages/patient/check_appointment.php"><i class="fas fa-search"></i> Check Status</a>
                <a href="<?php echo BASE_URL; ?>/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>

<nav class="navbar" id="navbar">
    <a href="<?php echo View::url('index.php'); ?>" class="logo">
        <img src="<?php echo View::image('logoabc.jpg'); ?>" alt="ABC Hospital" class="logo-img">
        ABC Hospital
    </a>
    
    <div class="nav-links">
        <!-- Public Navigation -->
        <?php if (!isLoggedIn()): ?>
            <a href="<?php echo View::url('index.php#our-services'); ?>">
                <i class="fas fa-stethoscope"></i>
                <span>Our Services</span>
            </a>
            <a href="<?php echo View::url('index.php#reviews'); ?>">
                <i class="fas fa-star"></i>
                <span>Reviews</span>
            </a>
            <a href="<?php echo View::url('index.php#about-us'); ?>">
                <i class="fas fa-info-circle"></i>
                <span>About Us</span>
            </a>
            <a href="<?php echo View::url('login.php'); ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-sign-in-alt"></i>
                <span>Login</span>
            </a>
        <?php else: ?>
            <!-- Authenticated Navigation -->
            <?php $user = auth(); ?>
            
            <!-- Admin Navigation -->
            <?php if ($user['role'] === Config::ROLE_ADMIN): ?>
                <a href="<?php echo View::url('admin_dashboard.php'); ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo View::url('admin_users.php'); ?>">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="<?php echo View::url('admin_reports.php'); ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            
            <!-- Doctor Navigation -->
            <?php elseif ($user['role'] === Config::ROLE_DOCTOR): ?>
                <a href="<?php echo View::url('doctor_dashboard.php'); ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo View::url('doctor_appointments.php'); ?>">
                    <i class="fas fa-calendar-check"></i>
                    <span>Appointments</span>
                </a>
                <a href="<?php echo View::url('doctor_schedule.php'); ?>">
                    <i class="fas fa-clock"></i>
                    <span>Schedule</span>
                </a>
                <a href="<?php echo View::url('doctor_patients.php'); ?>">
                    <i class="fas fa-user-injured"></i>
                    <span>Patients</span>
                </a>
            
            <!-- Patient Navigation -->
            <?php elseif ($user['role'] === Config::ROLE_PATIENT): ?>
                <a href="<?php echo View::url('patient_dashboard.php'); ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo View::url('pages/patient/book_appointment.php'); ?>">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Book Appointment</span>
                </a>
                <a href="<?php echo View::url('pages/patient/check_appointment.php'); ?>">
                    <i class="fas fa-calendar-check"></i>
                    <span>My Appointments</span>
                </a>
                <a href="<?php echo View::url('patient_medical_records.php'); ?>">
                    <i class="fas fa-file-medical"></i>
                    <span>Medical Records</span>
                </a>
            
            <!-- Receptionist Navigation -->
            <?php elseif ($user['role'] === Config::ROLE_RECEPTIONIST): ?>
                <a href="<?php echo View::url('receptionist_dashboard.php'); ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo View::url('receptionist_appointments.php'); ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Appointments</span>
                </a>
                <a href="<?php echo View::url('receptionist_patients.php'); ?>">
                    <i class="fas fa-users"></i>
                    <span>Patients</span>
                </a>
            <?php endif; ?>
            
            <!-- User Dropdown -->
            <div class="nav-dropdown">
                <div class="nav-dropdown-toggle">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                    </div>
                    <span class="welcome-text">
                        <?php echo View::escape($user['username']); ?>
                    </span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="nav-dropdown-menu">
                    <a href="<?php echo View::url('profile.php'); ?>">
                        <i class="fas fa-user"></i>
                        Profile
                    </a>
                    <a href="<?php echo View::url('settings.php'); ?>">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                    <a href="<?php echo View::url('help.php'); ?>">
                        <i class="fas fa-question-circle"></i>
                        Help
                    </a>
                    <hr style="margin: 5px 0; border: none; border-top: 1px solid #ecf0f1;">
                    <a href="<?php echo View::url('logout.php'); ?>" style="color: #e74c3c;">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleMobileNav()">
        <i class="fas fa-bars"></i>
    </button>
</nav>

<!-- Mobile Navigation -->
<div class="mobile-nav" id="mobileNav">
    <?php if (!isLoggedIn()): ?>
        <a href="<?php echo View::url('index.php#our-services'); ?>">
            <i class="fas fa-stethoscope"></i> Our Services
        </a>
        <a href="<?php echo View::url('index.php#reviews'); ?>">
            <i class="fas fa-star"></i> Reviews
        </a>
        <a href="<?php echo View::url('index.php#about-us'); ?>">
            <i class="fas fa-info-circle"></i> About Us
        </a>
        <a href="<?php echo View::url('login.php'); ?>">
            <i class="fas fa-sign-in-alt"></i> Login
        </a>
    <?php else: ?>
        <?php $user = auth(); ?>
        
        <?php if ($user['role'] === Config::ROLE_ADMIN): ?>
            <a href="<?php echo View::url('admin_dashboard.php'); ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="<?php echo View::url('admin_users.php'); ?>">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="<?php echo View::url('admin_reports.php'); ?>">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
        
        <?php elseif ($user['role'] === Config::ROLE_DOCTOR): ?>
            <a href="<?php echo View::url('doctor_dashboard.php'); ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="<?php echo View::url('doctor_appointments.php'); ?>">
                <i class="fas fa-calendar-check"></i> Appointments
            </a>
            <a href="<?php echo View::url('doctor_schedule.php'); ?>">
                <i class="fas fa-clock"></i> Schedule
            </a>
            <a href="<?php echo View::url('doctor_patients.php'); ?>">
                <i class="fas fa-user-injured"></i> Patients
            </a>
        
        <?php elseif ($user['role'] === Config::ROLE_PATIENT): ?>
            <a href="<?php echo View::url('patient_dashboard.php'); ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="<?php echo View::url('pages/patient/book_appointment.php'); ?>">
                <i class="fas fa-calendar-plus"></i> Book Appointment
            </a>
            <a href="<?php echo View::url('pages/patient/check_appointment.php'); ?>">
                <i class="fas fa-calendar-check"></i> My Appointments
            </a>
            <a href="<?php echo View::url('patient_medical_records.php'); ?>">
                <i class="fas fa-file-medical"></i> Medical Records
            </a>
        
        <?php elseif ($user['role'] === Config::ROLE_RECEPTIONIST): ?>
            <a href="<?php echo View::url('receptionist_dashboard.php'); ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="<?php echo View::url('receptionist_appointments.php'); ?>">
                <i class="fas fa-calendar-alt"></i> Appointments
            </a>
            <a href="<?php echo View::url('receptionist_patients.php'); ?>">
                <i class="fas fa-users"></i> Patients
            </a>
        <?php endif; ?>
        
        <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.2); margin: 10px 0;">
        <a href="<?php echo View::url('profile.php'); ?>">
            <i class="fas fa-user"></i> Profile
        </a>
        <a href="<?php echo View::url('settings.php'); ?>">
            <i class="fas fa-cog"></i> Settings
        </a>
        <a href="<?php echo View::url('logout.php'); ?>" style="color: #e74c3c;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    <?php endif; ?>
</div>

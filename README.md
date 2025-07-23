# ABC Hospital Management System

A comprehensive web-based Hospital Management System built with PHP, MySQL, HTML, CSS, and JavaScript. This system provides complete functionality for managing hospital operations including patient appointments, doctor schedules, administrative tasks, and receptionist operations.

![ABC Hospital](public/images/logoabc.jpg)

## 🏥 Features

- **Multi-Role System**: Admin, Doctor, Patient, and Receptionist access levels
- **Appointment Management**: Online booking, scheduling, and status tracking
- **Doctor Management**: Profile management, schedule creation, patient consultations
- **Admin Dashboard**: Complete system oversight, user management, reporting
- **Receptionist Portal**: Appointment confirmation, patient management
- **Modern UI/UX**: Responsive design with smooth animations and interactive elements
- **Security**: Password hashing, session management, SQL injection prevention
- **Real-time Features**: Live statistics, dynamic appointment updates

## 🚀 Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Server**: Apache (XAMPP recommended)
- **Architecture**: MVC-inspired structure with separated concerns

## 📋 Prerequisites

Before installing the ABC Hospital Management System, ensure you have:

- **XAMPP** (recommended) or similar Apache/MySQL/PHP stack
  - PHP 7.4 or higher
  - MySQL 5.7 or MariaDB 10.4+
  - Apache 2.4+
- **Web Browser** (Chrome, Firefox, Safari, Edge)
- **Text Editor** (VS Code, Sublime Text, etc.) for customization

## 💾 Installation Guide

### Step 1: Download and Setup XAMPP

1. **Download XAMPP** from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. **Install XAMPP** following the installation wizard
3. **Start XAMPP Control Panel** and start **Apache** and **MySQL** services

### Step 2: Clone/Download the Project

#### Option A: Using Git
```bash
cd C:\xampp\htdocs
git clone https://github.com/yourusername/abc-hospital-management.git abc_hospital_08
```

#### Option B: Manual Download
1. Download the project ZIP file
2. Extract to `C:\xampp\htdocs\abc_hospital_08`

### Step 3: Database Setup

1. **Open phpMyAdmin** by navigating to `http://localhost/phpmyadmin`

2. **Create Database**:
   - Click "New" in the left sidebar
   - Database name: `abc_hospital_00`
   - Collation: `utf8mb4_general_ci`
   - Click "Create"

3. **Import Database**:
   - Select the `abc_hospital_00` database
   - Click "Import" tab
   - Choose file: `database/abc_hospital_00_backup.sql`
   - Click "Go" to import

### Step 4: Configuration

1. **Configure Database Connection**:
   - Open `config.php` in a text editor
   - Verify database settings:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');        // Default XAMPP password is empty
   define('DB_NAME', 'abc_hospital_00');
   ```

2. **Set Base URL** (if needed):
   ```php
   define('BASE_URL', '/abc_hospital_08');
   ```

### Step 5: File Permissions (Linux/Mac)

If you're using Linux or Mac, set proper permissions:
```bash
chmod -R 755 /path/to/xampp/htdocs/abc_hospital_08
chmod -R 666 /path/to/xampp/htdocs/abc_hospital_08/assets/images
```

### Step 6: Access the Application

1. **Open your web browser**
2. **Navigate to**: `http://localhost/abc_hospital_08`
3. **You should see the ABC Hospital homepage**

## 🔐 Default Login Credentials

### Admin Access
- **URL**: `http://localhost/abc_hospital_08/admin_dashboard.php`
- **Username**: `admin`
- **Password**: `secret`

### Doctor Access
- **URL**: `http://localhost/abc_hospital_08/login.php`
- **Username**: `jathursan`
- **Password**: `password`

### Receptionist Access
- **URL**: `http://localhost/abc_hospital_08/login.php`
- **Username**: `ahnaf`
- **Password**: `password`

> **Note**: Change these default passwords immediately after installation!

## 📁 Project Structure

```
abc_hospital_08/
├── core/                           # Core framework classes
│   ├── Config.php                  # Application configuration
│   ├── Database.php                # Database singleton class
│   ├── Session.php                 # Session management
│   ├── View.php                    # Template engine
│   └── Router.php                  # URL routing
├── app/                            # Application layer
│   └── views/                      # Template files
│       ├── layouts/                # Layout templates
│       ├── partials/               # Reusable components
│       └── home/                   # Page-specific views
├── public/                         # Static assets
│   ├── css/                        # Stylesheets
│   │   ├── base.css               # Core styles
│   │   ├── navigation.css         # Navigation styles
│   │   ├── forms.css              # Form styles
│   │   ├── buttons.css            # Button styles
│   │   ├── components.css         # UI components
│   │   └── home.css               # Home page styles
│   ├── js/                        # JavaScript files
│   │   ├── main.js                # Core application JS
│   │   └── home.js                # Home page interactions
│   └── images/                     # Image assets
├── database/                       # Database files
│   └── abc_hospital_00_backup.sql # Database schema & data
├── includes/                       # Legacy include files
├── pages/                          # Legacy page files
│   ├── admin/                     # Admin pages
│   ├── doctor/                    # Doctor pages
│   ├── patient/                   # Patient pages
│   └── receptionist/              # Receptionist pages
├── assets/                         # Legacy assets
├── config.php                      # Main configuration
├── bootstrap.php                   # Application bootstrap
├── index.php                       # Legacy homepage
├── login.php                       # Login page
├── *_dashboard.php                 # Dashboard pages
└── README.md                       # This file
```

## 🎯 Key Features by Role

### 👨‍💼 Admin Features
- **User Management**: Add/edit/delete doctors and receptionists
- **System Overview**: Dashboard with key metrics and statistics
- **Reports**: Generate various system reports
- **Specialization Management**: Add/modify medical specializations

### 👨‍⚕️ Doctor Features
- **Appointment Management**: View and manage patient appointments
- **Schedule Creation**: Set available time slots for appointments
- **Patient History**: Access patient consultation history
- **Profile Management**: Update personal and professional information

### 👥 Receptionist Features
- **Appointment Confirmation**: Approve/reject appointment requests
- **Patient Registration**: Manage patient information
- **Daily Schedule**: View and organize daily appointments
- **Status Updates**: Update appointment statuses

### 👤 Patient Features
- **Online Booking**: Schedule appointments with preferred doctors
- **Appointment Status**: Check appointment status and details
- **Doctor Search**: Find doctors by specialization
- **Medical History**: View appointment history

## 🔧 Customization

### Database Configuration
Edit `config.php` to match your database setup:
```php
define('DB_HOST', 'your_host');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'your_database');
```

### Styling Customization
- **Primary Colors**: Edit `public/css/base.css` variables
- **Fonts**: Modify font imports in layout files
- **Layout**: Adjust responsive breakpoints in CSS files

### Adding New Features
1. Create new views in `app/views/`
2. Add corresponding CSS in `public/css/`
3. Implement JavaScript in `public/js/`
4. Update routing in `bootstrap.php` if needed

## 🔒 Security Features

- **Password Hashing**: Uses PHP's `password_hash()` and `password_verify()`
- **SQL Injection Prevention**: Prepared statements throughout
- **XSS Protection**: Input sanitization and output escaping
- **Session Security**: Secure session configuration
- **Role-Based Access**: Comprehensive permission system
- **CSRF Protection**: Token-based form protection

## 🐛 Troubleshooting

### Common Issues

**1. Database Connection Failed**
- Verify XAMPP MySQL service is running
- Check database credentials in `config.php`
- Ensure database `abc_hospital_00` exists

**2. Page Not Found (404)**
- Verify project is in `htdocs/abc_hospital_08/`
- Check Apache service is running
- Verify BASE_URL in `config.php`

**3. Login Issues**
- Use default credentials provided above
- Check if user table has correct data
- Clear browser cookies/cache

**4. CSS/JS Not Loading**
- Check file paths in browser developer tools
- Verify files exist in `public/` directory
- Clear browser cache

**5. Permission Denied**
- Set proper file permissions (Linux/Mac)
- Check XAMPP directory permissions
- Run text editor as administrator (Windows)

### Debugging Tips

1. **Enable Error Reporting**: Uncomment debug lines in `bootstrap.php`
2. **Check Browser Console**: For JavaScript errors
3. **Verify Database**: Use phpMyAdmin to check data
4. **Check Apache Logs**: XAMPP logs directory

## 📱 Browser Compatibility

- **Chrome** 70+
- **Firefox** 65+
- **Safari** 12+
- **Edge** 79+
- **Mobile** browsers supported

## 🔄 Updates and Maintenance

### Regular Maintenance
- **Database Backup**: Regular backups of `abc_hospital_00`
- **Security Updates**: Keep PHP and MySQL updated
- **Log Monitoring**: Check error logs regularly
- **Performance**: Monitor database performance

### Version Updates
1. Backup current installation
2. Replace files with new version
3. Run any database migrations
4. Clear browser cache
5. Test functionality

## 📄 License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📞 Support

- **Email**: support@abchospital.com
- **Issues**: [GitHub Issues](https://github.com/yourusername/abc-hospital-management/issues)
- **Documentation**: [Wiki](https://github.com/yourusername/abc-hospital-management/wiki)

## 🙏 Acknowledgments

- Font Awesome for icons
- Google Fonts for typography
- Bootstrap community for design inspiration
- PHP community for best practices

---

**Made with ❤️ for healthcare management**

---

## 📈 Changelog

### Version 2.0 (Latest)
- ✅ Complete MVC restructure
- ✅ Modern CSS Grid/Flexbox layouts
- ✅ Interactive JavaScript features
- ✅ Neural network animations
- ✅ Responsive design overhaul
- ✅ Security enhancements
- ✅ Performance optimizations

### Version 1.0
- ✅ Basic hospital management features
- ✅ Multi-role authentication
- ✅ Appointment booking system
- ✅ Database integration
- ✅ Basic responsive design

---

For detailed setup instructions and troubleshooting, please refer to the sections above or contact support.

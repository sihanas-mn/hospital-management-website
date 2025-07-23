# ABC Hospital Management System

A comprehensive web-based hospital management system built with PHP and MySQL that handles appointment booking, doctor scheduling, receptionist management, and administrative tasks.

## 🏥 Features

### Patient Features
- **Online Appointment Booking**: Book appointments with specific doctors and specializations
- **Appointment Status Check**: Track appointment status using reference numbers
- **Multiple Specializations**: Choose from various medical specializations
- **Responsive Design**: Mobile-friendly interface with modern UI

### Doctor Features
- **Personal Dashboard**: View and manage personal information
- **Schedule Management**: Create and manage available time slots
- **Appointment View**: View all scheduled appointments with filtering options
- **Patient Information**: Access patient details and appointment reasons
- **Status Updates**: Mark appointments as completed or pending

### Receptionist Features
- **Appointment Confirmation**: Confirm pending appointments
- **Patient Management**: View and manage patient information
- **Search & Filter**: Advanced search capabilities for appointments
- **Real-time Updates**: Live appointment status tracking

### Admin Features
- **Doctor Management**: Add, edit, and remove doctors
- **Receptionist Management**: Manage receptionist accounts
- **Specialization Management**: Handle medical specializations
- **System Overview**: Comprehensive dashboard with statistics

## 🚀 Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Styling**: Custom CSS with Font Awesome icons
- **Server**: Apache (XAMPP)

## 📋 Requirements

- **XAMPP** (Apache + MySQL + PHP)
- **PHP** 7.4 or higher
- **MySQL** 5.7 or higher
- **Web Browser** (Chrome, Firefox, Safari, Edge)

## 🔧 Installation Guide

### Step 1: Download and Install XAMPP

1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP in your preferred directory (usually `C:\xampp` on Windows)
3. Start the XAMPP Control Panel

### Step 2: Start Required Services

1. Open XAMPP Control Panel
2. Start **Apache** service
3. Start **MySQL** service
4. Ensure both services are running (green status)

### Step 3: Setup the Project

1. Clone or download this repository
2. Copy the project folder to XAMPP's htdocs directory:
   ```
   C:\xampp\htdocs\abc_hospital_08\
   ```

### Step 4: Database Setup

1. Open your web browser and go to: `http://localhost/phpmyadmin`
2. Create a new database named `abc_hospital_00`
3. Import the database structure:
   - Click on the database `abc_hospital_00`
   - Go to the **Import** tab
   - Select the `abc_hospital_00_backup.sql` file from the project directory
   - Click **Go** to import

### Step 5: Configuration

1. Open `config.php` in the project directory
2. Verify the database configuration:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'abc_hospital_00');
   ```
3. If your MySQL setup uses different credentials, update accordingly

### Step 6: Run the Application

1. Open your web browser
2. Navigate to: `http://localhost/abc_hospital_08/`
3. You should see the ABC Hospital homepage

## 🗃️ Database Structure

The system uses the following main tables:

- **user**: System users with roles (admin, doctor, receptionist)
- **doctor**: Doctor profiles and information
- **receptionist**: Receptionist profiles
- **patient**: Patient information
- **specialization**: Medical specializations
- **appointment**: Main appointment records
- **appointment_doctor**: Doctor scheduling and time slots
- **appointment_patient**: Patient appointment details
- **appointment_receptionist**: Appointment confirmations

## 👥 Default User Accounts

After importing the database, you can use these default accounts:

### Admin Access
- **Username**: `admin`
- **Password**: `password`

### Sample Doctor Account
- **Username**: `jasath`
- **Password**: `jasath123`

### Sample Receptionist Account
- **Username**: `ahnaf`
- **Password**: `ahnaf123`

*Note: Please change these default passwords after the first login for security.*

## 🔐 User Roles & Permissions

### Admin
- Full system access
- Manage doctors and receptionists
- View system statistics
- System configuration

### Doctor
- Manage personal schedule
- View appointments
- Update appointment status
- Profile management

### Receptionist
- Confirm appointments
- Manage patient information
- Search and filter appointments
- Basic system operations

### Patient (Public)
- Book appointments
- Check appointment status
- No login required for basic operations

## 📱 Key Functionalities

### Appointment Booking Process
1. Patient selects specialization
2. Choose available doctor
3. Select preferred date and time
4. Fill personal information
5. Submit appointment request
6. Receive reference number for tracking

### Reference Number System
- Format: `REF-[PatientID]-[DoctorID]-[TokenNo]-[AppointmentID]`
- Unique identifier for each appointment
- Used for status tracking and queries

### Schedule Management
- Doctors can create time slots
- Automatic conflict detection
- Time slot availability checking
- Easy schedule modification

## 🎨 UI Features

- **Modern Design**: Clean, professional healthcare interface
- **Responsive Layout**: Works on all device sizes
- **Interactive Elements**: Smooth animations and transitions
- **Neural Network Animation**: Animated background on homepage
- **Font Awesome Icons**: Professional iconography
- **Color Coding**: Status-based visual indicators

## 🔧 Troubleshooting

### Common Issues

**1. Database Connection Error**
- Check if MySQL service is running in XAMPP
- Verify database credentials in `config.php`
- Ensure database `abc_hospital_00` exists

**2. Page Not Loading**
- Confirm Apache service is running
- Check if project is in correct htdocs directory
- Verify URL: `http://localhost/abc_hospital_08/`

**3. Import Error**
- Ensure database is created first
- Check SQL file permissions
- Try importing sections of the SQL file separately

**4. Login Issues**
- Verify user accounts exist in database
- Check username/password combination
- Ensure user table has correct role assignments

### Port Conflicts
If default ports are occupied:
- Change Apache port in XAMPP (usually 80 to 8080)
- Access via: `http://localhost:8080/abc_hospital_08/`

## 📁 Project Structure

```
abc_hospital_08/
├── config.php              # Database configuration and common functions
├── index.php               # Homepage with services and features
├── login.php               # User authentication
├── book_appointment.php     # Appointment booking interface
├── check_appointment.php    # Appointment status checking
├── admin_dashboard.php      # Admin panel
├── doctor_dashboard.php     # Doctor management interface
├── doctor_appointments.php  # Doctor's appointment view
├── doctor_schedule.php      # Doctor schedule management
├── receptionist_dashboard.php # Receptionist panel
├── styles.css              # Main stylesheet
├── logoabc.jpg             # Hospital logo
├── abc_hospital_00_backup.sql # Database structure and sample data
└── README.md               # This file
```

## 🔮 Future Enhancements

- Email notifications for appointments
- SMS integration for reminders
- Online payment system
- Patient medical history tracking
- Prescription management
- Report generation system
- Mobile application
- Telemedicine integration

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 📞 Support

For support or questions:
- Create an issue on GitHub
- Email: support@abchospital.com
- Phone: +1234567890

## 🙏 Acknowledgments

- Font Awesome for icons
- Google Fonts for typography
- XAMPP team for the development environment
- PHP and MySQL communities

---

**ABC Hospital Management System** - Making healthcare management simple and efficient.

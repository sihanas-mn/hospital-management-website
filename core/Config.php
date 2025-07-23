<?php

class Config {
    // Database Configuration
    const DB_HOST = 'localhost';
    const DB_USERNAME = 'root';
    const DB_PASSWORD = '';
    const DB_NAME = 'abc_hospital_00';
    
    // Application Configuration
    const APP_NAME = 'ABC Hospital Management System';
    const APP_VERSION = '2.0';
    const APP_URL = 'http://localhost/abc_hospital_08';
    
    // Paths
    const BASE_PATH = __DIR__ . '/../';
    const APP_PATH = self::BASE_PATH . 'app/';
    const PUBLIC_PATH = self::BASE_PATH . 'public/';
    const VIEWS_PATH = self::APP_PATH . 'views/';
    
    // URLs
    const BASE_URL = self::APP_URL;
    const ASSETS_URL = self::BASE_URL . '/public';
    const CSS_URL = self::ASSETS_URL . '/css';
    const JS_URL = self::ASSETS_URL . '/js';
    const IMAGES_URL = self::ASSETS_URL . '/images';
    
    // Security
    const SESSION_TIMEOUT = 3600; // 1 hour in seconds
    const CSRF_TOKEN_NAME = 'csrf_token';
    
    // File Upload
    const MAX_FILE_SIZE = 5242880; // 5MB
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    
    // Pagination
    const RECORDS_PER_PAGE = 10;
    
    // User Roles
    const ROLE_ADMIN = 'admin';
    const ROLE_DOCTOR = 'doctor';
    const ROLE_PATIENT = 'patient';
    const ROLE_RECEPTIONIST = 'receptionist';
    
    // Appointment Status
    const APPOINTMENT_PENDING = 'pending';
    const APPOINTMENT_CONFIRMED = 'confirmed';
    const APPOINTMENT_COMPLETED = 'completed';
    const APPOINTMENT_CANCELLED = 'cancelled';
    
    public static function get($key, $default = null) {
        return defined("self::$key") ? constant("self::$key") : $default;
    }
}

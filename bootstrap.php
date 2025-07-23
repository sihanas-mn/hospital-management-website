<?php

// Define base paths
define('ROOT_PATH', __DIR__ . '/');
define('CORE_PATH', ROOT_PATH . 'core/');
define('APP_PATH', ROOT_PATH . 'app/');
define('PUBLIC_PATH', ROOT_PATH . 'public/');

// Autoloader
spl_autoload_register(function ($class) {
    $paths = [
        CORE_PATH . $class . '.php',
        APP_PATH . 'controllers/' . $class . '.php',
        APP_PATH . 'models/' . $class . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Load core classes
require_once CORE_PATH . 'Config.php';
require_once CORE_PATH . 'Database.php';
require_once CORE_PATH . 'Session.php';
require_once CORE_PATH . 'View.php';
require_once CORE_PATH . 'Router.php';

// Start session
Session::start();

// Check session timeout
Session::checkTimeout();

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Helper functions
function redirect($url, $code = 302) {
    Router::redirect($url, $code);
}

function back() {
    $referer = $_SERVER['HTTP_REFERER'] ?? Config::BASE_URL;
    redirect($referer);
}

function old($key, $default = '') {
    return Session::get('old_' . $key, $default);
}

function setOld($data) {
    foreach ($data as $key => $value) {
        Session::set('old_' . $key, $value);
    }
}

function clearOld() {
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'old_') === 0) {
            Session::remove($key);
        }
    }
}

function flash($key, $message = null) {
    if ($message === null) {
        $value = Session::get('flash_' . $key);
        Session::remove('flash_' . $key);
        return $value;
    }
    Session::set('flash_' . $key, $message);
}

function hasFlash($key) {
    return Session::has('flash_' . $key);
}

function csrf_token() {
    return Session::generateCSRFToken();
}

function csrf_field() {
    return '<input type="hidden" name="' . Config::CSRF_TOKEN_NAME . '" value="' . csrf_token() . '">';
}

function isLoggedIn() {
    return Session::isLoggedIn();
}

function auth() {
    return [
        'id' => Session::getUserId(),
        'username' => Session::getUsername(),
        'role' => Session::getRole()
    ];
}

function hasRole($role) {
    return Session::getRole() === $role;
}

function requireAuth() {
    if (!isLoggedIn()) {
        redirect(Config::BASE_URL . '/login.php');
        exit;
    }
}

function requireRole($role) {
    requireAuth();
    if (!hasRole($role)) {
        redirect(Config::BASE_URL . '/unauthorized.php');
        exit;
    }
}

function formatDate($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    
    return floor($time/31536000) . ' years ago';
}

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

// Database helper functions
function db() {
    return Database::getInstance();
}

function query($sql, $params = []) {
    $db = Database::getInstance();
    if (!empty($params)) {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    return $db->query($sql);
}

function fetchAll($sql, $params = []) {
    $result = query($sql, $params);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function fetchOne($sql, $params = []) {
    $result = query($sql, $params);
    return $result->fetch_assoc();
}

function insert($table, $data) {
    $db = Database::getInstance();
    $columns = implode(',', array_keys($data));
    $values = ':' . implode(', :', array_keys($data));
    
    $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
    $stmt = $db->prepare($sql);
    
    return $stmt->execute($data);
}

function update($table, $data, $where, $whereParams = []) {
    $db = Database::getInstance();
    $set = [];
    
    foreach (array_keys($data) as $key) {
        $set[] = "{$key} = :{$key}";
    }
    
    $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE {$where}";
    $stmt = $db->prepare($sql);
    
    return $stmt->execute(array_merge($data, $whereParams));
}

function delete($table, $where, $params = []) {
    $db = Database::getInstance();
    $sql = "DELETE FROM {$table} WHERE {$where}";
    $stmt = $db->prepare($sql);
    
    return $stmt->execute($params);
}

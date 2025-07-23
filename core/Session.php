<?php

class Session {
    public static function start() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null) {
        self::start();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }
    
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    public static function remove($key) {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    public static function destroy() {
        self::start();
        session_destroy();
    }
    
    public static function regenerate() {
        self::start();
        session_regenerate_id(true);
    }
    
    public static function isLoggedIn() {
        return self::has('user_id') && self::has('role');
    }
    
    public static function getUserId() {
        return self::get('user_id');
    }
    
    public static function getRole() {
        return self::get('role');
    }
    
    public static function getUsername() {
        return self::get('username');
    }
    
    public static function setUser($userId, $username, $role, $email = null) {
        self::set('user_id', $userId);
        self::set('username', $username);
        self::set('role', $role);
        if ($email) {
            self::set('email', $email);
        }
        self::set('login_time', time());
    }
    
    public static function logout() {
        self::start();
        session_unset();
        session_destroy();
    }
    
    public static function checkTimeout() {
        if (self::isLoggedIn()) {
            $loginTime = self::get('login_time', 0);
            if (time() - $loginTime > Config::SESSION_TIMEOUT) {
                self::logout();
                return false;
            }
        }
        return true;
    }
    
    public static function generateCSRFToken() {
        if (!self::has('csrf_token')) {
            self::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return self::get('csrf_token');
    }
    
    public static function validateCSRFToken($token) {
        return hash_equals(self::get('csrf_token', ''), $token);
    }
}

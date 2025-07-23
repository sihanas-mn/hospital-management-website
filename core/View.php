<?php

class View {
    private static $layoutPath = 'layouts/main.php';
    private static $data = [];
    
    public static function render($view, $data = [], $layout = null) {
        self::$data = $data;
        
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewFile = Config::VIEWS_PATH . $view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new Exception("View file not found: {$viewFile}");
        }
        
        // Get the view content
        $content = ob_get_clean();
        
        // If layout is specified, render with layout
        if ($layout !== false) {
            $layoutFile = Config::VIEWS_PATH . ($layout ?: self::$layoutPath);
            if (file_exists($layoutFile)) {
                include $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }
    
    public static function partial($partial, $data = []) {
        extract($data);
        $partialFile = Config::VIEWS_PATH . 'partials/' . $partial . '.php';
        if (file_exists($partialFile)) {
            include $partialFile;
        } else {
            throw new Exception("Partial file not found: {$partialFile}");
        }
    }
    
    public static function setLayout($layout) {
        self::$layoutPath = $layout;
    }
    
    public static function getData($key = null) {
        if ($key) {
            return isset(self::$data[$key]) ? self::$data[$key] : null;
        }
        return self::$data;
    }
    
    public static function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    public static function url($path = '') {
        return Config::BASE_URL . '/' . ltrim($path, '/');
    }
    
    public static function asset($path) {
        return Config::ASSETS_URL . '/' . ltrim($path, '/');
    }
    
    public static function css($file) {
        return Config::CSS_URL . '/' . ltrim($file, '/') . '.css';
    }
    
    public static function js($file) {
        return Config::JS_URL . '/' . ltrim($file, '/') . '.js';
    }
    
    public static function image($file) {
        return Config::IMAGES_URL . '/' . ltrim($file, '/');
    }
}

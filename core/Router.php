<?php

class Router {
    private static $routes = [];
    private static $middlewares = [];
    
    public static function get($path, $callback, $middleware = []) {
        self::addRoute('GET', $path, $callback, $middleware);
    }
    
    public static function post($path, $callback, $middleware = []) {
        self::addRoute('POST', $path, $callback, $middleware);
    }
    
    public static function put($path, $callback, $middleware = []) {
        self::addRoute('PUT', $path, $callback, $middleware);
    }
    
    public static function delete($path, $callback, $middleware = []) {
        self::addRoute('DELETE', $path, $callback, $middleware);
    }
    
    private static function addRoute($method, $path, $callback, $middleware) {
        self::$routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback,
            'middleware' => $middleware
        ];
    }
    
    public static function middleware($name, $callback) {
        self::$middlewares[$name] = $callback;
    }
    
    public static function run() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path if it exists
        $basePath = str_replace(Config::APP_URL, '', Config::BASE_URL);
        if ($basePath && strpos($requestPath, $basePath) === 0) {
            $requestPath = substr($requestPath, strlen($basePath));
        }
        
        foreach (self::$routes as $route) {
            if ($route['method'] === $requestMethod) {
                $pattern = self::pathToRegex($route['path']);
                if (preg_match($pattern, $requestPath, $matches)) {
                    // Remove the full match
                    array_shift($matches);
                    
                    // Run middleware
                    foreach ($route['middleware'] as $middlewareName) {
                        if (isset(self::$middlewares[$middlewareName])) {
                            $result = call_user_func(self::$middlewares[$middlewareName]);
                            if ($result === false) {
                                return;
                            }
                        }
                    }
                    
                    // Call the route callback
                    return call_user_func_array($route['callback'], $matches);
                }
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        View::render('errors/404', [], false);
    }
    
    private static function pathToRegex($path) {
        // Convert path parameters like {id} to regex groups
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    public static function redirect($url, $code = 302) {
        http_response_code($code);
        header('Location: ' . $url);
        exit;
    }
}

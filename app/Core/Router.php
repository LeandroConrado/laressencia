<?php

namespace App\Core;

class Router
{
    private static array $routes = [];
    private static array $groupStack = [];

    public static function group(array $attributes, callable $callback): void
    {
        self::$groupStack[] = $attributes;
        call_user_func($callback);
        array_pop(self::$groupStack);
    }

    private static function applyGroupContext(string $uri, array $options = []): array
    {
        $prefix = '';
        $middleware = $options['middleware'] ?? null;
        $role = $options['role'] ?? null;
        $module = $options['module'] ?? null;

        foreach (self::$groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= rtrim($group['prefix'], '/');
            }
            if (isset($group['middleware'])) {
                $middleware = $group['middleware'];
            }
            if (isset($group['role'])) {
                $role = $group['role'];
            }
            if (isset($group['module'])) {
                $module = $group['module'];
            }
        }

        $uri = $prefix . '/' . ltrim($uri, '/');
        return [$uri, ['middleware' => $middleware, 'role' => $role, 'module' => $module]];
    }

    public static function get(string $uri, $controller, array $options = []): void
    {
        [$uri, $options] = self::applyGroupContext($uri, $options);
        self::$routes['GET'][] = [
            'uri' => $uri,
            'controller' => $controller,
            'middleware' => $options['middleware'] ?? null,
            'role' => $options['role'] ?? null,
            'module' => $options['module'] ?? null
        ];
    }

    public static function post(string $uri, $controller, array $options = []): void
    {
        [$uri, $options] = self::applyGroupContext($uri, $options);
        self::$routes['POST'][] = [
            'uri' => $uri,
            'controller' => $controller,
            'middleware' => $options['middleware'] ?? null,
            'role' => $options['role'] ?? null,
            'module' => $options['module'] ?? null
        ];
    }

    public static function dispatch(): void
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $rawPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $path = is_string($rawPath) ? $rawPath : '/';
            
            // Log para depuração
            error_log("Router: Método {$method}, URI {$path}");
            
            // Removendo o base path do URL
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath !== '/' && strpos($path, $basePath) === 0) {
                $path = substr($path, strlen($basePath));
            }
            
            // Se o caminho estiver vazio, defina como "/"
            if (empty($path)) {
                $path = '/';
            }
            
            error_log("Router: URI normalizada para {$path}");

            $routes = self::$routes[$method] ?? [];
            
            foreach ($routes as $route) {
                $pattern = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([a-zA-Z0-9_-]+)', $route['uri']);
                $pattern = '#^' . $pattern . '$#';
                
                error_log("Router: Verificando rota {$route['uri']} com padrão {$pattern}");

                if (preg_match($pattern, $path, $matches)) {
                    error_log("Router: Rota encontrada para {$path}");
                    array_shift($matches);

                    if (!empty($route['middleware']) && $route['middleware'] === 'auth') {
                        \App\Core\Middleware\AuthMiddleware::handle();
                    }

                    if (!empty($route['role'])) {
                        \App\Core\Middleware\RoleMiddleware::handle($route['role']);
                    }

                    if (!empty($route['module'])) {
                        \App\Core\Middleware\ModuleMiddleware::handle($route['module']);
                    }

                    $controller = $route['controller'];

                    if (is_array($controller) && count($controller) === 2) {
                        [$class, $methodName] = $controller;
                    } elseif (is_string($controller) && strpos($controller, '@') !== false) {
                        [$class, $methodName] = explode('@', $controller);
                        $class = 'App\\Http\\Controllers\\' . str_replace('/', '\\', $class);
                    } else {
                        error_log("Router: Formato de controller inválido");
                        http_response_code(500);
                        echo "Formato de controller inválido.";
                        return;
                    }

                    if (!class_exists($class)) {
                        error_log("Router: Controller {$class} não encontrado");
                        http_response_code(500);
                        echo "Controller {$class} não encontrado.";
                        return;
                    }

                    try {
                        $instance = new $class();
                        call_user_func_array([$instance, $methodName], $matches);
                        return;
                    } catch (\Exception $e) {
                        error_log("Router: Erro ao executar controller: " . $e->getMessage());
                        error_log("Router: " . $e->getTraceAsString());
                        http_response_code(500);
                        echo "Erro interno do servidor. Por favor, tente novamente.";
                        return;
                    }
                }
            }

            error_log("Router: Página não encontrada: {$path}");
            http_response_code(404);
            
            // Redirecionando para página 404 personalizada
            if (isset(self::$routes['GET'])) {
                foreach (self::$routes['GET'] as $route) {
                    if ($route['uri'] === '/404') {
                        $controller = $route['controller'];
                        if (is_array($controller) && count($controller) === 2) {
                            [$class, $methodName] = $controller;
                            $instance = new $class();
                            call_user_func([$instance, $methodName]);
                            return;
                        }
                    }
                }
            }
            
            echo "Página não encontrada: {$path}";
        } catch (\Exception $e) {
            error_log("Router Exception: " . $e->getMessage());
            error_log("Router Exception Trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo "Erro interno do servidor. Por favor, tente novamente.";
        }
    }
}
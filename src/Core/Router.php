<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middleware = [];
    private ?string $currentRoute = null;
    private array $routeParams = [];

    public function addRoute(string $method, string $path, string $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
            'pattern' => $this->compilePattern($path),
        ];
    }

    public function get(string $path, string $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, string $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, string $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, string $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function addGroup(string $prefix, array $routes, array $middleware = []): void
    {
        foreach ($routes as $method => $handlers) {
            foreach ($handlers as $path => $handler) {
                $fullPath = rtrim($prefix, '/') . '/' . ltrim($path, '/');
                $this->addRoute($method, $fullPath, $handler, $middleware);
            }
        }
    }

    private function compilePattern(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                $this->currentRoute = $route['path'];
                array_shift($matches);
                $this->routeParams = $matches;

                if ($this->handleMiddleware($route['middleware'])) {
                    $this->executeHandler($route['handler'], $matches);
                }
                return;
            }
        }

        $this->handleNotFound();
    }

    private function handleMiddleware(array $middleware): bool
    {
        foreach ($middleware as $middlewareClass) {
            if (class_exists($middlewareClass)) {
                $instance = new $middlewareClass();
                if (method_exists($instance, 'handle')) {
                    $result = $instance->handle();
                    if ($result === false) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    private function executeHandler(string $handler, array $params): void
    {
        [$controller, $method] = explode('@', $handler);
        $controllerClass = 'App\\Controllers\\' . $controller;

        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller {$controllerClass} not found");
        }

        $instance = new $controllerClass();

        if (!method_exists($instance, $method)) {
            throw new \Exception("Method {$method} not found in {$controller}");
        }

        $instance->$method(...$params);
    }

    private function handleNotFound(): void
    {
        http_response_code(404);
        if (file_exists(__DIR__ . '/../Views/errors/404.php')) {
            require_once __DIR__ . '/../Views/errors/404.php';
        } else {
            echo '<h1>404 - Page Not Found</h1>';
        }
    }

    public function getCurrentRoute(): ?string
    {
        return $this->currentRoute;
    }

    public function getRouteParams(): array
    {
        return $this->routeParams;
    }
}

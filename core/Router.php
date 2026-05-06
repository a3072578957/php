<?php

namespace Core;

class Router
{
    private array $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function resolve(Request $request): ?array
    {
        $method = strtoupper($request->method());
        $path = $request->path();

        foreach ($this->routes as $route) {
            [$routeMethod, $routePath, $handler] = $route;
            if ($method !== strtoupper($routeMethod)) {
                continue;
            }

            $normalizedRoutePath = rtrim($routePath, '/') ?: '/';
            $pattern = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function ($matches) {
                return '(?P<' . $matches[1] . '>[^/]+)';
            }, $normalizedRoutePath);
            $pattern = str_replace('/', '\/', $pattern);
            $pattern = '#^' . $pattern . '$#';

            if (!preg_match($pattern, $path, $matches)) {
                continue;
            }

            $params = [];
            foreach ($matches as $key => $value) {
                if (!is_int($key)) {
                    $params[$key] = $value;
                }
            }

            return [$handler, $params];
        }

        return null;
    }
}

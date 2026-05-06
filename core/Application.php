<?php

namespace Core;

class Application
{
    private array $config;
    private Router $router;

    public function __construct(array $config, array $routes)
    {
        $this->config = $config;
        date_default_timezone_set($config['timezone'] ?? 'UTC');
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $this->router = new Router($routes);
    }

    public function run(): void
    {
        try {
            $request = new Request();
            $resolved = $this->router->resolve($request);

            if ($resolved === null) {
                http_response_code(404);
                echo '404 Not Found';
                return;
            }

            [$handler, $params] = $resolved;
            [$class, $method] = $handler;

            $controller = new $class($this->config, $request);
            echo $controller->{$method}($params);
        } catch (\Throwable $e) {
            http_response_code(500);
            if ($this->config['debug'] ?? false) {
                echo '<pre>' . htmlspecialchars($e->getMessage() . "\n\n" . $e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
                return;
            }
            echo '500 Internal Server Error';
        }
    }
}

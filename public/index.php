<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefixes = [
        'Core\\' => __DIR__ . '/../core/',
        'App\\'  => __DIR__ . '/../app/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (strpos($class, $prefix) !== 0) {
            continue;
        }

        $relative = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

$config = require __DIR__ . '/../config/app.php';
$routes = require __DIR__ . '/../routes/web.php';

$app = new Core\Application($config, $routes);
$app->run();

<?php

declare(strict_types=1);

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$target = __DIR__ . '/public' . $uri;

if ($uri !== '/' && file_exists($target) && !is_dir($target)) {
    return false;
}

require __DIR__ . '/public/index.php';

<?php

$path = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$publicDir = __DIR__ . '/public';
$target = $publicDir . $path;

if ($path !== '/' && is_file($target)) {
    return false;
}

return require $publicDir . '/index.php';

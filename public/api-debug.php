<?php
// This file goes in: larvel/public/api-debug.php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try to bootstrap Laravel to access routes
$autoload = __DIR__ . '/../vendor/autoload.php';
$app = __DIR__ . '/../bootstrap/app.php';

echo "<h1>API Debug Information</h1>";

// Check if files exist
echo "<h2>File Check:</h2>";
echo "autoload.php exists: " . (file_exists($autoload) ? 'Yes' : 'No') . "<br>";
echo "app.php exists: " . (file_exists($app) ? 'Yes' : 'No') . "<br>";

if (file_exists($autoload) && file_exists($app)) {
    require_once $autoload;
    $app = require_once $app;
    $app->make(Illuminate\Contracts\Http\Kernel::class);
    $request = Illuminate\Http\Request::capture();
    
    echo "<h2>API Routes:</h2>";
    $router = $app->make('router');
    $routes = $router->getRoutes();
    
    echo "<ul>";
    foreach ($routes as $route) {
        if (strpos($route->uri(), 'api') !== false) {
            echo "<li>" . $route->methods()[0] . " - " . $route->uri() . "</li>";
        }
    }
    echo "</ul>";
    
    echo "<h2>Environment Variables:</h2>";
    echo "APP_ENV: " . (env('APP_ENV') ?: 'not set') . "<br>";
    echo "APP_DEBUG: " . (env('APP_DEBUG') ?: 'not set') . "<br>";
    echo "APP_URL: " . (env('APP_URL') ?: 'not set') . "<br>";
} else {
    echo "Cannot bootstrap Laravel - files missing";
}

// Simple test without Laravel
echo "<h2>Simple POST Test:</h2>";
echo "If you see this, the file is accessible.<br>";
echo "Try posting to this file with: curl -X POST https://larvel-1.onrender.com/api-debug.php -d 'test=123'<br>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST Data Received:</h3>";
    echo "<pre>";
    print_r($_POST);
    print_r(json_decode(file_get_contents('php://input'), true));
    echo "</pre>";
}

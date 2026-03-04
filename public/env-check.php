<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

echo "<pre>";
echo "=== ENVIRONMENT VARIABLES IN LARAVEL ===\n";
echo "DB_HOST: " . env('DB_HOST') . "\n";
echo "DB_DATABASE: " . env('DB_DATABASE') . "\n";
echo "DB_USERNAME: " . env('DB_USERNAME') . "\n";
echo "DB_PASSWORD: " . (env('DB_PASSWORD') ? '[SET]' : '[NOT SET]') . "\n";
echo "MYSQL_ATTR_SSL_CA: " . env('MYSQL_ATTR_SSL_CA') . "\n";
echo "</pre>";
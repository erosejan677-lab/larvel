<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

echo "<pre>";
echo "Running migrations...\n";
Artisan::call('migrate', ['--force' => true]);
echo Artisan::output();
echo "Migrations complete!\n";
echo "</pre>";
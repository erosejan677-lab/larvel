<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "<pre>";
echo "=== Laravel Bootstrap Debug ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Laravel Version: " . app()->version() . "\n";
echo "Environment: " . app()->environment() . "\n";
echo "Storage Path: " . storage_path() . "\n";
echo "Storage Writable: " . (is_writable(storage_path()) ? 'Yes' : 'No') . "\n";

echo "\n=== Database Config ===\n";
echo "DB_HOST: " . config('database.connections.mysql.host') . "\n";
echo "DB_DATABASE: " . config('database.connections.mysql.database') . "\n";
echo "DB_USERNAME: " . config('database.connections.mysql.username') . "\n";

echo "\n=== Testing Database ===\n";
try {
    DB::connection()->getPdo();
    echo "Database: Connected\n";
    $tables = DB::select('SHOW TABLES');
    echo "Tables: " . count($tables) . "\n";
} catch (\Exception $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}

echo "\n=== APP Key ===\n";
echo "APP_KEY: " . config('app.key') . "\n";
echo "Key length: " . strlen(config('app.key')) . "\n";
echo "</pre>";
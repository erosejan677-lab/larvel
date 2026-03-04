<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

echo "<pre>";
echo "=== FINAL DEBUG ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Laravel Version: " . app()->version() . "\n";
echo "Environment: " . app()->environment() . "\n\n";

echo "=== DATABASE CONFIG ===\n";
echo "DB_HOST: " . config('database.connections.mysql.host') . "\n";
echo "DB_DATABASE: " . config('database.connections.mysql.database') . "\n";
echo "DB_USERNAME: " . config('database.connections.mysql.username') . "\n\n";

echo "=== DATABASE CONNECTION ===\n";
try {
    DB::connection()->getPdo();
    echo "✅ Connected successfully!\n";
    
    $tables = DB::select('SHOW TABLES');
    echo "Total tables: " . count($tables) . "\n";
    
    // Check specifically for products table
    $hasProducts = false;
    foreach($tables as $table) {
        $tableName = current($table);
        echo "  - $tableName\n";
        if($tableName == 'products') {
            $hasProducts = true;
            $productCount = DB::table('products')->count();
            echo "    → Products count: $productCount\n";
        }
    }
    
    if(!$hasProducts) {
        echo "❌ No 'products' table found!\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
}

echo "\n=== APP KEY ===\n";
echo "APP_KEY: " . config('app.key') . "\n";
echo "Key length: " . strlen(config('app.key')) . "\n";
echo "</pre>";
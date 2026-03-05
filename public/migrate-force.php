<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

echo "<pre>";
echo "=== FORCE MIGRATION ===\n\n";

try {
    // First, check if migrations table exists
    $hasMigrations = Schema::hasTable('migrations');
    echo "Migrations table exists: " . ($hasMigrations ? 'YES' : 'NO') . "\n";
    
    if($hasMigrations) {
        echo "Rolling back all migrations...\n";
        Artisan::call('migrate:reset', ['--force' => true]);
        echo Artisan::output();
    }
    
    echo "Running fresh migrations...\n";
    Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output();
    
    echo "\n✅ Migrations completed successfully!\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
echo "</pre>";
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// Boot app
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

echo "<pre>";
echo "=== DATABASE DIAGNOSTIC ===\n\n";

// Test connection with retry
$connected = false;
$attempts = 0;

while (!$connected && $attempts < 3) {
    try {
        $attempts++;
        echo "Attempt $attempts...\n";
        
        // Set longer timeout
        config(['database.connections.mysql.options' => [
            PDO::ATTR_TIMEOUT => 30,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]]);
        
        DB::connection()->reconnect();
        DB::connection()->getPdo();
        
        $connected = true;
        echo "✅ Connected on attempt $attempts!\n\n";
        
        $tables = DB::select('SHOW TABLES');
        echo "Tables (" . count($tables) . "):\n";
        foreach($tables as $table) {
            $name = current($table);
            $count = DB::table($name)->count();
            echo "  - $name ($count records)\n";
        }
        
    } catch (\Exception $e) {
        echo "❌ Attempt $attempts failed: " . $e->getMessage() . "\n";
        sleep(2); // Wait 2 seconds before retry
    }
}

if (!$connected) {
    echo "\n❌ Could not connect after $attempts attempts.\n";
    echo "\nChecking environment:\n";
    echo "DB_HOST: " . env('DB_HOST') . "\n";
    echo "DB_PORT: " . env('DB_PORT') . "\n";
    echo "DB_DATABASE: " . env('DB_DATABASE') . "\n";
    echo "DB_USERNAME: " . env('DB_USERNAME') . "\n";
    echo "DB_PASSWORD: " . (env('DB_PASSWORD') ? '[SET]' : '[NOT SET]') . "\n";
}
echo "</pre>";
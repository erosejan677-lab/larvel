<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

echo "<pre>";
echo "=== CURRENT DATABASE TABLES ===\n\n";

try {
    $tables = DB::select('SHOW TABLES');
    echo "Total tables: " . count($tables) . "\n";
    
    $hasMigrations = false;
    foreach($tables as $table) {
        $name = current($table);
        echo " - $name\n";
        if($name == 'migrations') {
            $hasMigrations = true;
            $count = DB::table('migrations')->count();
            echo "   → Migration records: $count\n";
        }
    }
    
    if(!$hasMigrations) {
        echo "\n❌ Migrations table is MISSING!\n";
        echo "This is why your API returns 404.\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
echo "</pre>";
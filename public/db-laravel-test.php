<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

try {
    $count = DB::table('products')->count();
    echo "Products count: " . $count;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
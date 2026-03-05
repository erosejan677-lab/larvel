<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$logFile = __DIR__ . '/../storage/logs/laravel.log';
echo "<pre>";
if (file_exists($logFile)) {
    echo file_get_contents($logFile);
} else {
    echo "No log file found at: " . $logFile;
}
echo "</pre>";
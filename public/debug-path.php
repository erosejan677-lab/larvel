<?php
// Save this as: public/debug-path.php
header('Content-Type: text/plain');
echo "Current script: " . __FILE__ . "\n";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Looking for index.php in: " . $_SERVER['DOCUMENT_ROOT'] . "/index.php\n";
echo "File exists: " . (file_exists($_SERVER['DOCUMENT_ROOT'] . "/index.php") ? "YES" : "NO") . "\n\n";

echo "Directory contents of " . $_SERVER['DOCUMENT_ROOT'] . ":\n";
$files = scandir($_SERVER['DOCUMENT_ROOT']);
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        echo "  - " . $file . "\n";
    }
}

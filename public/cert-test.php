<?php
echo "<pre>";
echo "=== CERTIFICATE TEST ===\n\n";

$certPath = '/var/www/html/storage/certs/ca.pem';

if (file_exists($certPath)) {
    echo "✅ Certificate exists at: $certPath\n";
    echo "File size: " . filesize($certPath) . " bytes\n";
    echo "Last modified: " . date('Y-m-d H:i:s', filemtime($certPath)) . "\n";
    echo "First 100 characters:\n";
    echo substr(file_get_contents($certPath), 0, 100) . "...\n";
} else {
    echo "❌ Certificate NOT found at: $certPath\n";
    
    // Check if directory exists
    if (is_dir('/var/www/html/storage/certs')) {
        echo "✅ Directory exists but file missing\n";
        $files = scandir('/var/www/html/storage/certs');
        echo "Files in directory:\n";
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "  - $file\n";
            }
        }
    } else {
        echo "❌ Directory /var/www/html/storage/certs does NOT exist\n";
        
        // Check if storage directory exists
        if (is_dir('/var/www/html/storage')) {
            echo "✅ Storage directory exists\n";
            $storageDirs = scandir('/var/www/html/storage');
            echo "Contents of storage:\n";
            foreach ($storageDirs as $dir) {
                if ($dir != '.' && $dir != '..') {
                    echo "  - $dir\n";
                }
            }
        } else {
            echo "❌ Storage directory does NOT exist\n";
        }
    }
}
echo "</pre>";
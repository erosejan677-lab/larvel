<?php
echo "<pre>";
echo "=== NGINX DEBUG ===\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "Path Info: " . ($_SERVER['PATH_INFO'] ?? 'none') . "\n";
echo "PHP Self: " . $_SERVER['PHP_SELF'] . "\n";
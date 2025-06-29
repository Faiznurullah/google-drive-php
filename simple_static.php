<?php
/**
 * Google Drive PHP - Simple Static Example
 * Penggunaan paling sederhana dengan static methods
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

use GoogleDrivePHP\GoogleDrive;

try {
    // Upload file (auto-initialize dari environment)
    $fileId = GoogleDrive::put('simple_static_test.txt', 'Hello from Static Google Drive!');
    echo "✅ File uploaded with ID: $fileId\n";
    
    // Download file
    $content = GoogleDrive::get('simple_static_test.txt');
    echo "📥 Downloaded content: $content\n";
    
    // List files
    $files = GoogleDrive::files();
    echo "📄 Found " . count($files) . " files\n";
    
    // Delete file
    GoogleDrive::delete('simple_static_test.txt');
    echo "🗑️ File deleted\n";
    
    echo "\n💡 Super simple! No need to create objects!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

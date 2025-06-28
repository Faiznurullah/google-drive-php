<?php
/**
 * Google Drive Upload Example
 * 
 * Contoh lengkap untuk upload file dengan berbagai cara:
 * - Upload dari string
 * - Upload dari file lokal
 * - Upload dengan metadata tambahan
 * - Upload ke folder tertentu
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/SimpleDrive.php';

// Load environment variables dari .env
if (file_exists(__DIR__ . '/../../.env')) {
    $env = parse_ini_file(__DIR__ . '/../../.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

use GoogleDrivePHP\SimpleDrive;
use Google\Service\Drive\DriveFile;

try {
    // Initialize Google Drive
    $drive = SimpleDrive::fromEnv();
    echo "✅ Google Drive initialized successfully\n\n";
    
    // 1. Upload dari string
    echo "1. Upload file dari string...\n";
    $content = "Hello World! This is a test file created from string.";
    $fileId = $drive->put('test_from_string.txt', $content);
    echo "   File uploaded with ID: $fileId\n\n";
    
    // 2. Upload dari file lokal
    echo "2. Upload file dari file lokal...\n";
    $localFilePath = __DIR__ . '/test_file.txt';
    file_put_contents($localFilePath, "This is a test file from local filesystem.");
    
    if (file_exists($localFilePath)) {
        $localContent = file_get_contents($localFilePath);
        $fileId = $drive->put('test_from_local.txt', $localContent);
        echo "   Local file uploaded with ID: $fileId\n";
        
        // Cleanup local file
        unlink($localFilePath);
        echo "   Local temp file cleaned up\n\n";
    }
    
    // 3. Upload JSON data
    echo "3. Upload JSON data...\n";
    $jsonData = json_encode([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'created_at' => date('Y-m-d H:i:s'),
        'data' => [
            'key1' => 'value1',
            'key2' => 'value2'
        ]
    ], JSON_PRETTY_PRINT);
    
    $fileId = $drive->put('test_data.json', $jsonData);
    echo "   JSON file uploaded with ID: $fileId\n\n";
    
    // 4. Upload dengan nama file yang lebih kompleks
    echo "4. Upload file dengan nama yang kompleks...\n";
    $timestamp = date('Y-m-d_H-i-s');
    $filename = "backup_data_{$timestamp}.txt";
    $content = "Backup data created at: " . date('Y-m-d H:i:s') . "\n";
    $content .= "System: PHP Google Drive Integration\n";
    $content .= "Status: Working correctly\n";
    
    $fileId = $drive->put($filename, $content);
    echo "   Complex filename uploaded: $filename (ID: $fileId)\n\n";
    
    // 5. Upload multiple files
    echo "5. Upload multiple files...\n";
    $files = [
        'file1.txt' => 'Content of file 1',
        'file2.txt' => 'Content of file 2',
        'file3.txt' => 'Content of file 3'
    ];
    
    $uploadedIds = [];
    foreach ($files as $filename => $content) {
        $fileId = $drive->put($filename, $content);
        $uploadedIds[$filename] = $fileId;
        echo "   - $filename uploaded (ID: $fileId)\n";
    }
    echo "\n";
    
    // 6. Verify uploads by listing files
    echo "6. Verifying uploads by listing files...\n";
    $allFiles = $drive->files();
    
    echo "   Total files in Drive: " . count($allFiles) . "\n";
    echo "   Recently uploaded files:\n";
    
    foreach ($allFiles as $file) {
        if (in_array($file['id'], array_values($uploadedIds)) || 
            in_array($file['id'], [$fileId])) {
            echo "   - {$file['name']} (Size: {$file['size']} bytes)\n";
        }
    }
    echo "\n";
    
    echo "✅ All upload examples completed successfully!\n\n";
    
    // Summary
    echo "📋 UPLOAD SUMMARY:\n";
    echo "- Upload dari string: ✅\n";
    echo "- Upload dari file lokal: ✅\n";
    echo "- Upload JSON data: ✅\n";
    echo "- Upload dengan nama kompleks: ✅\n";
    echo "- Upload multiple files: ✅\n";
    echo "- Verifikasi upload: ✅\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

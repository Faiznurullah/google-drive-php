<?php
/**
 * Test Case Delete - json_data.json
 * 
 * Script sederhana untuk menghapus file json_data.json dari Google Drive
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/SimpleDrive.php';

// Load environment variables dari .env
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

use GoogleDrivePHP\SimpleDrive;

try {
    echo "�️  DELETE TEST: json_data.json\n";
    echo str_repeat("=", 40) . "\n\n";
    
    // Initialize Google Drive
    $drive = SimpleDrive::fromEnv();
    echo "✅ Google Drive connected successfully\n\n";
    
    // 1. CEK APAKAH FILE ADA
    echo "1. 🔍 Checking if json_data.json exists...\n";
    
    $exists = $drive->exists('json_data.json');
    if ($exists) {
        echo "   ✅ File json_data.json found!\n\n";
        
        // 2. HAPUS FILE
        echo "2. �️  Deleting json_data.json...\n";
        
        $deleted = $drive->delete('json_data.json');
        
        if ($deleted) {
            echo "   ✅ File json_data.json successfully deleted!\n\n";
            
            // 3. VERIFIKASI PENGHAPUSAN
            echo "3. ✅ Verifying deletion...\n";
            
            $stillExists = $drive->exists('json_data.json');
            if (!$stillExists) {
                echo "   ✅ Confirmed: File no longer exists in Google Drive\n\n";
                
                echo "🎉 DELETE TEST COMPLETED SUCCESSFULLY!\n";
                echo "📋 RESULT: json_data.json has been permanently deleted from Google Drive\n\n";
            } else {
                echo "   ❌ Warning: File still exists after deletion attempt\n\n";
            }
            
        } else {
            echo "   ❌ Failed to delete json_data.json\n";
            echo "   💡 This might be due to permission issues\n\n";
        }
        
    } else {
        echo "   ❌ File json_data.json not found in Google Drive\n";
        echo "   💡 File might already be deleted or doesn't exist\n\n";
        
        echo "📋 RESULT: No file to delete\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\n🔧 TROUBLESHOOTING:\n";
    echo "   1. Pastikan file .env sudah benar\n";
    echo "   2. Pastikan access token masih valid\n";
    echo "   3. Pastikan koneksi internet stabil\n";
    echo "   4. Coba jalankan: php get_new_token.php\n\n";
}

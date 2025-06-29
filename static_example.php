<?php
/**
 * Google Drive PHP Library - New Static Design Pattern Example
 * 
 * Contoh penggunaan dengan design pattern static yang mengikuti referensi yang Anda sukai
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables dari .env
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

use GoogleDrivePHP\GoogleDrive;
use GoogleDrivePHP\Facades\GDrive;

try {
    echo "🚀 Google Drive PHP Library - Static Design Pattern\n";
    echo "====================================================\n\n";
    
    // ======================
    // 1. INITIALIZATION
    // ======================
    echo "1. INITIALIZATION\n";
    echo "=================\n";
    
    // Method 1: Auto initialize dari environment variables
    // GoogleDrive akan otomatis initialize saat method pertama dipanggil
    echo "✅ GoogleDrive will auto-initialize from environment variables\n";
    
    // Method 2: Manual initialize dengan config
    // GoogleDrive::init([
    //     'client_id' => 'your-client-id',
    //     'client_secret' => 'your-client-secret',
    //     'refresh_token' => 'your-refresh-token'
    // ]);
    
    // Method 3: Initialize dari credentials file
    // GoogleDrive::initFromCredentialsFile('credentials.json', $refreshToken);
    echo "\n";
    
    // ======================
    // 2. UPLOAD FILES
    // ======================
    echo "2. UPLOAD FILES\n";
    echo "===============\n";
    
    // Upload dari string menggunakan static method
    $fileId1 = GoogleDrive::put('static_hello.txt', 'Hello World from Static GoogleDrive!');
    echo "✅ Static upload: static_hello.txt with ID: $fileId1\n";
    
    // Upload menggunakan facade
    $fileId2 = GDrive::put('facade_hello.txt', 'Hello World from GDrive Facade!');
    echo "✅ Facade upload: facade_hello.txt with ID: $fileId2\n";
    
    // Upload file lokal
    $tempFile = __DIR__ . '/temp_static.txt';
    file_put_contents($tempFile, 'This is uploaded using static method');
    
    $fileId3 = GoogleDrive::putFile($tempFile, 'uploaded_static.txt');
    echo "✅ Upload local file: uploaded_static.txt with ID: $fileId3\n";
    
    unlink($tempFile); // cleanup
    echo "\n";
    
    // ======================
    // 3. FILE OPERATIONS
    // ======================
    echo "3. FILE OPERATIONS\n";
    echo "==================\n";
    
    // Download file
    $content = GoogleDrive::get('static_hello.txt');
    echo "📥 Downloaded content: $content\n";
    
    // Check if file exists
    $exists = GoogleDrive::exists('static_hello.txt');
    echo "🔍 File exists: " . ($exists ? 'Yes' : 'No') . "\n";
    
    // Get file info
    $fileInfo = GoogleDrive::getFileInfo('static_hello.txt');
    if ($fileInfo) {
        echo "ℹ️ File info: {$fileInfo['name']} ({$fileInfo['size']} bytes)\n";
    }
    
    // Copy file
    $copiedId = GoogleDrive::copy('static_hello.txt', 'static_hello_copy.txt');
    echo "📋 Copied file with ID: $copiedId\n";
    
    // Rename file
    GoogleDrive::rename('static_hello_copy.txt', 'static_hello_renamed.txt');
    echo "✏️ Renamed file successfully\n";
    echo "\n";
    
    // ======================
    // 4. FOLDER OPERATIONS
    // ======================
    echo "4. FOLDER OPERATIONS\n";
    echo "====================\n";
    
    // Create folder
    $folderId = GoogleDrive::makeDir('Static Test Folder');
    echo "📁 Created folder with ID: $folderId\n";
    
    // Upload file ke folder
    $fileInFolderId = GoogleDrive::put('file_in_static_folder.txt', 'This file is in static folder', $folderId);
    echo "📄 Uploaded file to folder with ID: $fileInFolderId\n";
    
    // Find folder ID
    $foundFolderId = GoogleDrive::findFolderId('Static Test Folder');
    echo "🔍 Found folder ID: $foundFolderId\n";
    echo "\n";
    
    // ======================
    // 5. LIST & SEARCH
    // ======================
    echo "5. LIST & SEARCH\n";
    echo "================\n";
    
    // List all files
    $files = GoogleDrive::files();
    echo "📄 Total files: " . count($files) . "\n";
    foreach (array_slice($files, 0, 3) as $file) {
        echo "   - {$file['name']} ({$file['size']} bytes)\n";
    }
    
    // Search files
    $searchResults = GoogleDrive::search('static');
    echo "🔍 Search 'static' found: " . count($searchResults) . " files\n";
    foreach ($searchResults as $file) {
        echo "   - {$file['name']}\n";
    }
    
    // List folders
    $folders = GoogleDrive::folders();
    echo "📁 Total folders: " . count($folders) . "\n";
    echo "\n";
    
    // ======================
    // 6. SHARING (OPTIONAL)
    // ======================
    echo "6. SHARING\n";
    echo "==========\n";
    
    // Make file public
    $publicLink = GoogleDrive::makePublic('static_hello.txt');
    echo "🌐 Public link: $publicLink\n";
    
    // Get shareable link for any file
    $shareLink = GoogleDrive::getShareableLink('facade_hello.txt');
    echo "🔗 Shareable link: $shareLink\n";
    echo "\n";
    
    // ======================
    // 7. BATCH OPERATIONS
    // ======================
    echo "7. BATCH OPERATIONS\n";
    echo "===================\n";
    
    // Upload multiple files sekaligus
    $batchFiles = [
        'batch_static_1.txt' => 'Static batch content 1',
        'batch_static_2.txt' => 'Static batch content 2',
        'batch_static_3.txt' => 'Static batch content 3'
    ];
    
    $batchResults = GoogleDrive::putMultiple($batchFiles);
    echo "📤 Batch upload results:\n";
    foreach ($batchResults as $filename => $result) {
        if ($result['success']) {
            echo "   ✅ $filename: {$result['fileId']}\n";
        } else {
            echo "   ❌ $filename: {$result['error']}\n";
        }
    }
    echo "\n";
    
    // ======================
    // 8. ALL CONTENTS
    // ======================
    echo "8. ALL CONTENTS\n";
    echo "===============\n";
    
    // List semua content (files + folders)
    $allContents = GoogleDrive::all();
    echo "📋 All contents: " . count($allContents) . " items\n";
    
    $fileCount = 0;
    $folderCount = 0;
    foreach ($allContents as $item) {
        if ($item['type'] === 'file') {
            $fileCount++;
        } else {
            $folderCount++;
        }
    }
    echo "   - Files: $fileCount\n";
    echo "   - Folders: $folderCount\n";
    echo "\n";
    
    // ======================
    // 9. CLEANUP
    // ======================
    echo "9. CLEANUP\n";
    echo "==========\n";
    
    // Delete files menggunakan batch
    $filesToDelete = [
        'static_hello.txt',
        'facade_hello.txt',
        'uploaded_static.txt',
        'static_hello_renamed.txt',
        'file_in_static_folder.txt',
        'batch_static_1.txt',
        'batch_static_2.txt',
        'batch_static_3.txt'
    ];
    
    $deleteResults = GoogleDrive::deleteMultiple($filesToDelete);
    foreach ($deleteResults as $filename => $result) {
        if ($result['success']) {
            echo "🗑️ Deleted: $filename\n";
        }
    }
    
    // Delete folder
    GoogleDrive::deleteDir('Static Test Folder');
    echo "🗑️ Deleted folder: Static Test Folder\n";
    
    echo "\n✅ All static operations completed successfully!\n";
    echo "\n💡 Key advantages of this design pattern:\n";
    echo "   - No need to instantiate objects\n";
    echo "   - Clean and simple syntax\n";
    echo "   - Auto-initialization from environment\n";
    echo "   - Facade pattern for alternative access\n";
    echo "   - Easy to use in any PHP framework\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

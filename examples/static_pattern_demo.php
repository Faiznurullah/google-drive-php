<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use GoogleDrivePHP\GoogleDrive;
use GoogleDrivePHP\Facades\GDrive;

/**
 * Google Drive PHP - Static Helper Pattern Example
 * 
 * Contoh lengkap penggunaan GoogleDrive dengan static pattern
 * yang terinspirasi dari yaza-putu/laravel-google-drive-storage
 */

// Load environment variables dari .env file
echo "Loading environment variables from .env file...\n";
try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    echo "✓ Environment variables loaded successfully\n\n";
    
    // Verify credentials are loaded
    $requiredVars = ['GOOGLE_DRIVE_CLIENT_ID', 'GOOGLE_DRIVE_CLIENT_SECRET', 'GOOGLE_DRIVE_REFRESH_TOKEN'];
    $missingVars = [];
    
    foreach ($requiredVars as $var) {
        if (empty($_ENV[$var])) {
            $missingVars[] = $var;
        }
    }
    
    if (!empty($missingVars)) {
        throw new Exception("Missing required environment variables: " . implode(', ', $missingVars));
    }
    
    echo "✓ All required credentials are present\n";
    echo "  CLIENT_ID: " . substr($_ENV['GOOGLE_DRIVE_CLIENT_ID'], 0, 15) . "...\n";
    echo "  CLIENT_SECRET: " . substr($_ENV['GOOGLE_DRIVE_CLIENT_SECRET'], 0, 12) . "...\n";
    echo "  REFRESH_TOKEN: " . substr($_ENV['GOOGLE_DRIVE_REFRESH_TOKEN'], 0, 15) . "...\n";
    if (!empty($_ENV['GOOGLE_DRIVE_ACCESS_TOKEN'])) {
        echo "  ACCESS_TOKEN: " . substr($_ENV['GOOGLE_DRIVE_ACCESS_TOKEN'], 0, 15) . "...\n";
    }
    echo "\n";
    
} catch (Exception $e) {
    echo "❌ Error loading environment: " . $e->getMessage() . "\n";
    echo "\nPlease make sure:\n";
    echo "1. You have .env file in project root\n";
    echo "2. The .env file contains all required variables:\n";
    echo "   - GOOGLE_DRIVE_CLIENT_ID\n";
    echo "   - GOOGLE_DRIVE_CLIENT_SECRET\n";
    echo "   - GOOGLE_DRIVE_REFRESH_TOKEN\n";
    echo "3. You have installed vlucas/phpdotenv: composer require vlucas/phpdotenv\n\n";
    exit(1);
}

// Initialize Google Drive
echo "Initializing Google Drive service...\n";
try {
    GoogleDrive::init();
    echo "✓ Google Drive service initialized successfully!\n\n";
} catch (Exception $e) {
    echo "❌ Failed to initialize Google Drive: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    echo "=== Google Drive PHP - Static Pattern Demo ===\n\n";

    // ==========================================
    // 1. BASIC FILE OPERATIONS
    // ==========================================
    echo "1. BASIC FILE OPERATIONS\n";
    echo "------------------------\n";

    // Upload file dari string content
    echo "• Upload file from string content...\n";
    $fileId = GoogleDrive::put('demo.txt', 'Hello World from GoogleDrive static method!');
    echo "  → File uploaded with ID: {$fileId}\n";

    // Upload file dari local path
    echo "• Upload file from local path...\n";
    file_put_contents('temp_upload.txt', 'Content from local file');
    $localFileId = GoogleDrive::putFile('temp_upload.txt', 'uploaded_from_local.txt');
    echo "  → Local file uploaded with ID: {$localFileId}\n";
    unlink('temp_upload.txt'); // cleanup

    // Download file
    echo "• Download file...\n";
    $content = GoogleDrive::get('demo.txt');
    echo "  → Downloaded content: {$content}\n";

    // Check if file exists
    echo "• Check if file exists...\n";
    $exists = GoogleDrive::exists('demo.txt');
    echo "  → File exists: " . ($exists ? 'Yes' : 'No') . "\n";

    // Get file info
    echo "• Get file information...\n";
    $fileInfo = GoogleDrive::getFileInfo('demo.txt');
    if ($fileInfo) {
        echo "  → Name: {$fileInfo['name']}\n";
        echo "  → Size: {$fileInfo['size']} bytes\n";
        echo "  → MIME Type: {$fileInfo['mimeType']}\n";
    }
    echo "\n";

    // ==========================================
    // 2. FOLDER OPERATIONS
    // ==========================================
    echo "2. FOLDER OPERATIONS\n";
    echo "--------------------\n";

    // Create folder
    echo "• Create folder...\n";
    $folderId = GoogleDrive::makeDir('demo-folder');
    echo "  → Folder created with ID: {$folderId}\n";

    // Upload file to folder
    echo "• Upload file to folder...\n";
    $fileInFolderId = GoogleDrive::put('file-in-folder.txt', 'This file is inside folder', $folderId);
    echo "  → File uploaded to folder with ID: {$fileInFolderId}\n";

    // List folders
    echo "• List folders...\n";
    $folders = GoogleDrive::folders();
    echo "  → Found " . count($folders) . " folders\n";
    foreach (array_slice($folders, 0, 3) as $folder) {
        echo "    - {$folder['name']} (ID: {$folder['id']})\n";
    }
    echo "\n";

    // ==========================================
    // 3. USING FACADE PATTERN
    // ==========================================
    echo "3. USING FACADE PATTERN\n";
    echo "-----------------------\n";

    // Facade memberikan cara alternatif untuk akses yang sama
    echo "• Using GDrive Facade...\n";
    $facadeFileId = GDrive::put('facade-demo.txt', 'Hello from GDrive Facade!');
    echo "  → File uploaded via facade with ID: {$facadeFileId}\n";

    $facadeContent = GDrive::get('facade-demo.txt');
    echo "  → Content via facade: {$facadeContent}\n";
    echo "\n";

    // ==========================================
    // 4. SHARING & PERMISSIONS
    // ==========================================
    echo "4. SHARING & PERMISSIONS\n";
    echo "------------------------\n";

    // Make file public
    echo "• Make file public...\n";
    $publicLink = GoogleDrive::makePublic('demo.txt');
    echo "  → Public link: {$publicLink}\n";

    // Get shareable link
    echo "• Get shareable link...\n";
    $shareableLink = GoogleDrive::getShareableLink('demo.txt');
    echo "  → Shareable link: {$shareableLink}\n";
    echo "\n";

    // ==========================================
    // 5. SEARCH & LISTING
    // ==========================================
    echo "5. SEARCH & LISTING\n";
    echo "-------------------\n";

    // List all files
    echo "• List files...\n";
    $files = GoogleDrive::files();
    echo "  → Found " . count($files) . " files\n";
    foreach (array_slice($files, 0, 5) as $file) {
        echo "    - {$file['name']} (Size: {$file['size']} bytes)\n";
    }

    // Search files
    echo "• Search files containing 'demo'...\n";
    $searchResults = GoogleDrive::search('demo');
    echo "  → Found " . count($searchResults) . " files matching 'demo'\n";
    foreach ($searchResults as $file) {
        echo "    - {$file['name']}\n";
    }

    // List all contents (files + folders)
    echo "• List all contents...\n";
    $allContents = GoogleDrive::all();
    $fileCount = count(array_filter($allContents, fn($item) => $item['type'] === 'file'));
    $folderCount = count(array_filter($allContents, fn($item) => $item['type'] === 'folder'));
    echo "  → Total: {$fileCount} files, {$folderCount} folders\n";
    echo "\n";

    // ==========================================
    // 6. BATCH OPERATIONS
    // ==========================================
    echo "6. BATCH OPERATIONS\n";
    echo "-------------------\n";

    // Upload multiple files
    echo "• Upload multiple files...\n";
    $multipleFiles = [
        'batch1.txt' => 'Content of batch file 1',
        'batch2.txt' => 'Content of batch file 2',
        'batch3.txt' => 'Content of batch file 3'
    ];
    $uploadResults = GoogleDrive::putMultiple($multipleFiles);
    foreach ($uploadResults as $filename => $result) {
        if ($result['success']) {
            echo "  → {$filename}: Uploaded (ID: {$result['fileId']})\n";
        } else {
            echo "  → {$filename}: Failed - {$result['error']}\n";
        }
    }
    echo "\n";

    // ==========================================
    // 7. FILE OPERATIONS (COPY, MOVE, RENAME)
    // ==========================================
    echo "7. FILE OPERATIONS\n";
    echo "------------------\n";

    // Copy file
    echo "• Copy file...\n";
    $copiedFileId = GoogleDrive::copy('demo.txt', 'demo-copy.txt');
    echo "  → File copied with ID: {$copiedFileId}\n";

    // Rename file
    echo "• Rename file...\n";
    $renamed = GoogleDrive::rename('demo-copy.txt', 'demo-renamed.txt');
    echo "  → File renamed: " . ($renamed ? 'Success' : 'Failed') . "\n";

    // Move file to folder
    echo "• Move file to folder...\n";
    $moved = GoogleDrive::move('demo-renamed.txt', $folderId);
    echo "  → File moved to folder: " . ($moved ? 'Success' : 'Failed') . "\n";
    echo "\n";

    // ==========================================
    // 8. DOWNLOAD TO LOCAL FILE
    // ==========================================
    echo "8. DOWNLOAD TO LOCAL FILE\n";
    echo "-------------------------\n";

    echo "• Download file to local path...\n";
    $downloaded = GoogleDrive::downloadToFile('demo.txt', './downloads/demo-downloaded.txt');
    if ($downloaded) {
        echo "  → File downloaded to: ./downloads/demo-downloaded.txt\n";
        echo "  → Content: " . file_get_contents('./downloads/demo-downloaded.txt') . "\n";
    } else {
        echo "  → Download failed\n";
    }
    echo "\n";

    // ==========================================
    // 9. CLEANUP
    // ==========================================
    echo "9. CLEANUP\n";
    echo "----------\n";

    echo "• Deleting test files...\n";
    $filesToDelete = ['demo.txt', 'uploaded_from_local.txt', 'facade-demo.txt', 'file-in-folder.txt'];
    $deleteResults = GoogleDrive::deleteMultiple($filesToDelete);
    foreach ($deleteResults as $filename => $result) {
        echo "  → {$filename}: " . ($result['success'] ? 'Deleted' : 'Failed') . "\n";
    }

    // Delete batch files
    $batchFiles = ['batch1.txt', 'batch2.txt', 'batch3.txt'];
    GoogleDrive::deleteMultiple($batchFiles);
    echo "  → Batch files deleted\n";

    // Delete folder (akan menghapus file di dalamnya juga)
    GoogleDrive::deleteDir('demo-folder');
    echo "  → Demo folder deleted\n";

    // Clean up local download
    if (file_exists('./downloads/demo-downloaded.txt')) {
        unlink('./downloads/demo-downloaded.txt');
        if (is_dir('./downloads')) rmdir('./downloads');
        echo "  → Local download cleaned up\n";
    }

    echo "\n";
    echo "🎉 === ALL OPERATIONS COMPLETED SUCCESSFULLY! ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
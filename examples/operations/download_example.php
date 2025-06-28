<?php

require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/../../.env')) {
    $lines = file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos(trim($line), '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value, ' "\''));
        }
    }
}

class DriveDownloader {
    private $service;
    
    public function __construct($service) {
        $this->service = $service;
    }
    
    public function downloadById($fileId) {
        try {
            $response = $this->service->files->get($fileId, ['alt' => 'media']);
            
            // FIX: Convert response object to string properly
            if (is_object($response)) {
                if (method_exists($response, 'getBody')) {
                    $body = $response->getBody();
                    if (method_exists($body, 'getContents')) {
                        return $body->getContents();
                    } else {
                        return (string) $body;
                    }
                } else {
                    return (string) $response;
                }
            }
            
            return $response;
            
        } catch (Exception $e) {
            throw new Exception("Download failed: " . $e->getMessage());
        }
    }
    
    public function downloadToFile($fileName, $localPath) {
        try {
            // Find file by name
            $files = $this->service->files->listFiles([
                'q' => "name='$fileName'",
                'fields' => 'files(id, name)'
            ])->getFiles();
            
            if (empty($files)) {
                throw new Exception("File '$fileName' not found");
            }
            
            $fileId = $files[0]->getId();
            $content = $this->downloadById($fileId);
            
            // Ensure directory exists
            $directory = dirname($localPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }
            
            // Write to file
            $bytesWritten = file_put_contents($localPath, $content);
            
            if ($bytesWritten === false) {
                throw new Exception("Failed to write file to: $localPath");
            }
            
            return [
                'success' => true,
                'localPath' => $localPath,
                'size' => $bytesWritten,
                'content' => substr($content, 0, 100) . (strlen($content) > 100 ? '...' : '')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function downloadMultiple($fileNames, $localDirectory = './downloads') {
        $results = [];
        
        // Ensure download directory exists
        if (!is_dir($localDirectory)) {
            mkdir($localDirectory, 0777, true);
            echo "📁 Created directory: $localDirectory\n";
        }
        
        foreach ($fileNames as $fileName) {
            echo "⬇️  Downloading: $fileName\n";
            
            $localPath = $localDirectory . DIRECTORY_SEPARATOR . $fileName;
            $result = $this->downloadToFile($fileName, $localPath);
            
            if ($result['success']) {
                echo "   ✅ Success! Size: " . $result['size'] . " bytes\n";
                echo "   📄 Preview: " . $result['content'] . "\n";
            } else {
                echo "   ❌ Failed: " . $result['error'] . "\n";
            }
            
            $results[$fileName] = $result;
        }
        
        return $results;
    }
    
    public function getFileInfo($fileName) {
        try {
            $files = $this->service->files->listFiles([
                'q' => "name='$fileName'",
                'fields' => 'files(id, name, size, mimeType, createdTime, modifiedTime)'
            ])->getFiles();
            
            if (empty($files)) {
                return null;
            }
            
            $file = $files[0];
            return [
                'id' => $file->getId(),
                'name' => $file->getName(),
                'size' => $file->getSize(),
                'mimeType' => $file->getMimeType(),
                'created' => $file->getCreatedTime(),
                'modified' => $file->getModifiedTime()
            ];
            
        } catch (Exception $e) {
            return null;
        }
    }
}

// Initialize Google Drive
try {
    $client = new Google\Client();
    $client->setClientId(getenv('GOOGLE_DRIVE_CLIENT_ID'));
    $client->setClientSecret(getenv('GOOGLE_DRIVE_CLIENT_SECRET'));
    $client->setScopes(['https://www.googleapis.com/auth/drive']);
    $client->setAccessType('offline');
    
    // Try access token first
    $accessToken = getenv('GOOGLE_DRIVE_ACCESS_TOKEN');
    if ($accessToken) {
        $client->setAccessToken($accessToken);
    }
    
    $service = new Google\Service\Drive($client);
    $downloader = new DriveDownloader($service);
    
    echo "✅ Google Drive initialized successfully\n\n";
    
} catch (Exception $e) {
    die("❌ Failed to initialize Google Drive: " . $e->getMessage() . "\n");
}

// Create downloads directory
$downloadsDir = __DIR__ . '/downloads';
if (!is_dir($downloadsDir)) {
    mkdir($downloadsDir, 0777, true);
    echo "📁 Created downloads directory: " . realpath($downloadsDir) . "\n\n";
}

echo "=== Google Drive Download Operations Test ===\n\n";

try {
    // 1. Create test files for demonstration
    echo "1. Creating test files for download demonstration...\n";
    $testFiles = [];
    
    // Create various test files
    $testData = [
        'download_test_1.txt' => "This is test file 1 for download.\nContent line 2.\nContent line 3.",
        'download_test_2.txt' => "This is test file 2 for download.\nWith different content.\nFor testing purposes.",
        'json_data.json' => json_encode(['name' => 'Test Data', 'version' => '1.0', 'items' => [1, 2, 3]], JSON_PRETTY_PRINT),
        'csv_data.csv' => "Name,Age,City\nJohn,25,Jakarta\nJane,30,Bandung\nBob,35,Surabaya"
    ];
    
    foreach ($testData as $fileName => $content) {
        $fileMetadata = new Google\Service\Drive\DriveFile(['name' => $fileName]);
        $uploadedFile = $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => 'text/plain',
            'uploadType' => 'multipart'
        ]);
        
        $testFiles[] = $fileName;
        echo "   - Created: $fileName (ID: " . $uploadedFile->getId() . ")\n";
    }
    
    // Create a test folder
    $folderMetadata = new Google\Service\Drive\DriveFile([
        'name' => 'DownloadTestFolder',
        'mimeType' => 'application/vnd.google-apps.folder'
    ]);
    $folder = $service->files->create($folderMetadata);
    echo "   - Created folder: DownloadTestFolder (ID: " . $folder->getId() . ")\n\n";
    
    // 2. Download individual files
    echo "2. Downloading individual files...\n";
    foreach (array_slice($testFiles, 0, 2) as $fileName) {
        try {
            $content = $downloader->downloadById($downloader->getFileInfo($fileName)['id']);
            $localPath = $downloadsDir . '/' . $fileName;
            file_put_contents($localPath, $content);
            
            echo "   ✅ Downloaded: $fileName\n";
            echo "   Content preview: " . substr($content, 0, 50) . "...\n";
            
        } catch (Exception $e) {
            echo "   ❌ Failed to download $fileName: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
    
    // 3. Download to specific paths
    echo "3. Downloading to specific local paths...\n";
    $specificDownloads = [
        'download_test_2.txt' => $downloadsDir . '/renamed_file.txt',
        'json_data.json' => $downloadsDir . '/backup/data.json'
    ];
    
    foreach ($specificDownloads as $fileName => $localPath) {
        $result = $downloader->downloadToFile($fileName, $localPath);
        
        if ($result['success']) {
            echo "   ✅ Downloaded '$fileName' to: " . $result['localPath'] . "\n";
            echo "   📏 Size: " . $result['size'] . " bytes\n";
        } else {
            echo "   ❌ Failed: " . $result['error'] . "\n";
        }
    }
    echo "\n";
    
    // 4. Batch download multiple files
    echo "4. Batch downloading multiple files...\n";
    $batchResults = $downloader->downloadMultiple(['csv_data.csv', 'json_data.json'], $downloadsDir . '/batch');
    
    $successCount = count(array_filter($batchResults, function($r) { return $r['success']; }));
    echo "   📊 Batch download completed: $successCount/" . count($batchResults) . " files successful\n\n";
    
    // 5. Get file information
    echo "5. Getting file information...\n";
    foreach ($testFiles as $fileName) {
        $info = $downloader->getFileInfo($fileName);
        if ($info) {
            echo "   📄 $fileName:\n";
            echo "      - Size: " . ($info['size'] ?? 'Unknown') . " bytes\n";
            echo "      - Type: " . $info['mimeType'] . "\n";
            echo "      - Created: " . $info['created'] . "\n";
        }
    }
    echo "\n";
    
    // 6. Show download results
    echo "6. Download results summary:\n";
    $downloadedFiles = glob($downloadsDir . '/*');
    echo "   📂 Downloaded files in '$downloadsDir':\n";
    foreach ($downloadedFiles as $file) {
        if (is_file($file)) {
            $size = filesize($file);
            echo "      - " . basename($file) . " ($size bytes)\n";
        }
    }
    
    // Check backup directory
    $backupDir = $downloadsDir . '/backup';
    if (is_dir($backupDir)) {
        $backupFiles = glob($backupDir . '/*');
        echo "   📂 Files in backup directory:\n";
        foreach ($backupFiles as $file) {
            if (is_file($file)) {
                $size = filesize($file);
                echo "      - " . basename($file) . " ($size bytes)\n";
            }
        }
    }
    
    // Check batch directory
    $batchDir = $downloadsDir . '/batch';
    if (is_dir($batchDir)) {
        $batchFiles = glob($batchDir . '/*');
        echo "   📂 Files in batch directory:\n";
        foreach ($batchFiles as $file) {
            if (is_file($file)) {
                $size = filesize($file);
                echo "      - " . basename($file) . " ($size bytes)\n";
            }
        }
    }
    
    echo "\n🎉 All download operations completed successfully!\n\n";
    
    // 7. Cleanup (optional - uncomment if you want to clean up)
    echo "7. Cleaning up test files from Google Drive...\n";
    foreach ($testFiles as $fileName) {
        $info = $downloader->getFileInfo($fileName);
        if ($info) {
            $service->files->delete($info['id']);
            echo "   🗑️  Deleted: $fileName\n";
        }
    }
    
    // Delete test folder
    $service->files->delete($folder->getId());
    echo "   🗑️  Deleted folder: DownloadTestFolder\n";
    
    echo "\n✅ Cleanup completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error during download operations: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Download Test Complete ===\n";
echo "Check the 'downloads' directory for downloaded files.\n";
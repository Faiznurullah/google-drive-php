<?php

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Google Drive Upload Handler
 * Supports upload file dari local path atau content string
 */
class GoogleDriveUploader
{
    private $drive;
    
    public function __construct()
    {
        // Setup client dengan credentials dari .env
        $clientId = '';
        $clientSecret = '';
        $accessToken = '';
        
        $client = new Google\Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setScopes(['https://www.googleapis.com/auth/drive']);
        $client->setAccessToken($accessToken);
        
        $this->drive = new Google\Service\Drive($client);
    }
    
    /**
     * Upload file dari content string
     */
    public function uploadFromContent($fileName, $content, $mimeType = 'text/plain', $folderId = null)
    {
        try {
            $fileMetadata = new Google\Service\Drive\DriveFile([
                'name' => $fileName
            ]);
            
            // Jika ada folder ID, set sebagai parent
            if ($folderId) {
                $fileMetadata->setParents([$folderId]);
            }
            
            $uploadedFile = $this->drive->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id,name,size,parents'
            ]);
            
            return [
                'success' => true,
                'fileId' => $uploadedFile->getId(),
                'fileName' => $uploadedFile->getName(),
                'size' => $uploadedFile->getSize(),
                'parents' => $uploadedFile->getParents()
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Upload file dari local path
     */
    public function uploadFromFile($localFilePath, $driveFileName = null, $folderId = null)
    {
        try {
            if (!file_exists($localFilePath)) {
                throw new Exception("Local file not found: $localFilePath");
            }
            
            $content = file_get_contents($localFilePath);
            $fileName = $driveFileName ?: basename($localFilePath);
            $mimeType = mime_content_type($localFilePath) ?: 'application/octet-stream';
            
            return $this->uploadFromContent($fileName, $content, $mimeType, $folderId);
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Upload file besar dengan resumable upload
     */
    public function uploadLargeFile($localFilePath, $driveFileName = null, $folderId = null)
    {
        try {
            if (!file_exists($localFilePath)) {
                throw new Exception("Local file not found: $localFilePath");
            }
            
            $fileName = $driveFileName ?: basename($localFilePath);
            $mimeType = mime_content_type($localFilePath) ?: 'application/octet-stream';
            
            $fileMetadata = new Google\Service\Drive\DriveFile([
                'name' => $fileName
            ]);
            
            if ($folderId) {
                $fileMetadata->setParents([$folderId]);
            }
            
            // Set chunk size untuk upload besar (1MB chunks)
            $client = $this->drive->getClient();
            $client->setDefer(true);
            
            $request = $this->drive->files->create($fileMetadata);
            $media = new Google\Http\MediaFileUpload(
                $client,
                $request,
                $mimeType,
                null,
                true,
                1024 * 1024 // 1MB chunk size
            );
            $media->setFileSize(filesize($localFilePath));
            
            $status = false;
            $handle = fopen($localFilePath, "rb");
            while (!$status && !feof($handle)) {
                $chunk = fread($handle, 1024 * 1024);
                $status = $media->nextChunk($chunk);
            }
            fclose($handle);
            
            $client->setDefer(false);
            
            if ($status) {
                return [
                    'success' => true,
                    'fileId' => $status->getId(),
                    'fileName' => $status->getName(),
                    'size' => $status->getSize()
                ];
            } else {
                throw new Exception("Upload failed");
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

// ========== CONTOH PENGGUNAAN ==========

echo "=== Google Drive Upload Examples ===\n\n";

$uploader = new GoogleDriveUploader();

// 1. Upload dari content string
echo "1. Upload dari content string...\n";
$result1 = $uploader->uploadFromContent(
    'test-content-' . date('Y-m-d-H-i-s') . '.txt',
    'Hello Google Drive! This is uploaded from string content.',
    'text/plain'
);

if ($result1['success']) {
    echo "✅ Upload berhasil: {$result1['fileName']} (ID: {$result1['fileId']})\n";
} else {
    echo "❌ Upload gagal: {$result1['error']}\n";
}

// 2. Upload dari file lokal (buat file test dulu)
echo "\n2. Upload dari file lokal...\n";
$testContent = "This is a test file created at " . date('Y-m-d H:i:s');
file_put_contents('test-local.txt', $testContent);

$result2 = $uploader->uploadFromFile('test-local.txt', 'test-local-' . date('Y-m-d-H-i-s') . '.txt');

if ($result2['success']) {
    echo "✅ Upload berhasil: {$result2['fileName']} (ID: {$result2['fileId']})\n";
} else {
    echo "❌ Upload gagal: {$result2['error']}\n";
}

// Cleanup local file
unlink('test-local.txt');

// 3. Upload JSON data
echo "\n3. Upload data JSON...\n";
$jsonData = json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'data' => ['item1', 'item2', 'item3'],
    'status' => 'success'
], JSON_PRETTY_PRINT);

$result3 = $uploader->uploadFromContent(
    'data-' . date('Y-m-d-H-i-s') . '.json',
    $jsonData,
    'application/json'
);

if ($result3['success']) {
    echo "✅ Upload JSON berhasil: {$result3['fileName']} (ID: {$result3['fileId']})\n";
} else {
    echo "❌ Upload JSON gagal: {$result3['error']}\n";
}

echo "\n🎉 Upload examples completed!\n";

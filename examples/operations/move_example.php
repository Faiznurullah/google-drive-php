<?php
/**
 * Google Drive Move Files/Folders Example
 * 
 * Contoh lengkap untuk memindahkan file dan folder di Google Drive:
 * - Membuat folder
 * - Memindahkan file ke folder
 * - Memindahkan folder ke folder lain
 * - Mengelola parent folders
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
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class DriveManager extends SimpleDrive
{
    private Drive $service;
    
    public function __construct(string $clientId, string $clientSecret, string $refreshToken, ?string $accessToken = null)
    {
        parent::__construct($clientId, $clientSecret, $refreshToken, $accessToken);
        
        // Get service from parent
        $reflection = new ReflectionClass(parent::class);
        $serviceProperty = $reflection->getProperty('service');
        $serviceProperty->setAccessible(true);
        $this->service = $serviceProperty->getValue($this);
    }
    
    /**
     * Find folder by name
     */
    public function findFolder(string $folderName): ?DriveFile
    {
        $response = $this->service->files->listFiles([
            'q' => "name = '{$folderName}' and mimeType = 'application/vnd.google-apps.folder'",
            'fields' => 'files(id, name)'
        ]);
        
        $folders = $response->getFiles();
        return count($folders) > 0 ? $folders[0] : null;
    }
    
    /**
     * Find file by name
     */
    public function findFile(string $filename): ?DriveFile
    {
        $response = $this->service->files->listFiles([
            'q' => "name = '{$filename}' and mimeType != 'application/vnd.google-apps.folder'",
            'fields' => 'files(id, name)'
        ]);
        
        $files = $response->getFiles();
        return count($files) > 0 ? $files[0] : null;
    }
    
    /**
     * Move file to folder
     */
    public function moveFileToFolder(string $filename, string $targetFolderName): bool
    {
        $file = $this->findFile($filename);
        if (!$file) {
            throw new Exception("File '{$filename}' not found");
        }
        
        $targetFolder = $this->findFolder($targetFolderName);
        if (!$targetFolder) {
            throw new Exception("Target folder '{$targetFolderName}' not found");
        }
        
        // Get current parents
        $fileDetails = $this->service->files->get($file->getId(), ['fields' => 'parents']);
        $previousParents = join(',', $fileDetails->getParents());
        
        // Move file
        $this->service->files->update($file->getId(), new DriveFile(), [
            'addParents' => $targetFolder->getId(),
            'removeParents' => $previousParents,
            'fields' => 'id, parents'
        ]);
        
        return true;
    }
    
    /**
     * Move folder to another folder
     */
    public function moveFolderToFolder(string $sourceFolderName, string $targetFolderName): bool
    {
        $sourceFolder = $this->findFolder($sourceFolderName);
        if (!$sourceFolder) {
            throw new Exception("Source folder '{$sourceFolderName}' not found");
        }
        
        $targetFolder = $this->findFolder($targetFolderName);
        if (!$targetFolder) {
            throw new Exception("Target folder '{$targetFolderName}' not found");
        }
        
        // Get current parents
        $folderDetails = $this->service->files->get($sourceFolder->getId(), ['fields' => 'parents']);
        $previousParents = join(',', $folderDetails->getParents());
        
        // Move folder
        $this->service->files->update($sourceFolder->getId(), new DriveFile(), [
            'addParents' => $targetFolder->getId(),
            'removeParents' => $previousParents,
            'fields' => 'id, parents'
        ]);
        
        return true;
    }
    
    /**
     * Create file in specific folder
     */
    public function putInFolder(string $filename, $content, string $folderName): string
    {
        $folder = $this->findFolder($folderName);
        if (!$folder) {
            throw new Exception("Folder '{$folderName}' not found");
        }
        
        $fileMetadata = new DriveFile([
            'name' => $filename,
            'parents' => [$folder->getId()]
        ]);
        
        $file = $this->service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => 'application/octet-stream',
            'uploadType' => 'multipart',
            'fields' => 'id'
        ]);
        
        return $file->getId();
    }
    
    /**
     * List files in folder
     */
    public function listFilesInFolder(string $folderName): array
    {
        $folder = $this->findFolder($folderName);
        if (!$folder) {
            throw new Exception("Folder '{$folderName}' not found");
        }
        
        $response = $this->service->files->listFiles([
            'q' => "'{$folder->getId()}' in parents",
            'fields' => 'files(id, name, size, mimeType, modifiedTime)'
        ]);
        
        $files = [];
        foreach ($response->getFiles() as $file) {
            $files[] = [
                'id' => $file->getId(),
                'name' => $file->getName(),
                'size' => $file->getSize(),
                'mimeType' => $file->getMimeType(),
                'modifiedTime' => $file->getModifiedTime()
            ];
        }
        
        return $files;
    }
}

try {
    // Initialize Google Drive
    $drive = new DriveManager(
        getenv('GOOGLE_DRIVE_CLIENT_ID'),
        getenv('GOOGLE_DRIVE_CLIENT_SECRET'),
        getenv('GOOGLE_DRIVE_REFRESH_TOKEN'),
        getenv('GOOGLE_DRIVE_ACCESS_TOKEN')
    );
    echo "✅ Google Drive initialized successfully\n\n";
    
    // 1. Create test folders
    echo "1. Creating test folders...\n";
    $folder1Id = $drive->makeDirectory('TestFolder1');
    $folder2Id = $drive->makeDirectory('TestFolder2');
    $subFolderId = $drive->makeDirectory('SubFolder');
    echo "   - TestFolder1 created (ID: $folder1Id)\n";
    echo "   - TestFolder2 created (ID: $folder2Id)\n";
    echo "   - SubFolder created (ID: $subFolderId)\n\n";
    
    // 2. Create test files in root
    echo "2. Creating test files in root...\n";
    $file1Id = $drive->put('test_move_file1.txt', 'Content of test file 1');
    $file2Id = $drive->put('test_move_file2.txt', 'Content of test file 2');
    echo "   - test_move_file1.txt created (ID: $file1Id)\n";
    echo "   - test_move_file2.txt created (ID: $file2Id)\n\n";
    
    // 3. Move files to folders
    echo "3. Moving files to folders...\n";
    $drive->moveFileToFolder('test_move_file1.txt', 'TestFolder1');
    echo "   - test_move_file1.txt moved to TestFolder1\n";
    
    $drive->moveFileToFolder('test_move_file2.txt', 'TestFolder2');
    echo "   - test_move_file2.txt moved to TestFolder2\n\n";
    
    // 4. Create file directly in folder
    echo "4. Creating file directly in folder...\n";
    $directFileId = $drive->putInFolder('direct_file.txt', 'This file was created directly in TestFolder1', 'TestFolder1');
    echo "   - direct_file.txt created directly in TestFolder1 (ID: $directFileId)\n\n";
    
    // 5. Move subfolder to another folder
    echo "5. Moving SubFolder to TestFolder1...\n";
    $drive->moveFolderToFolder('SubFolder', 'TestFolder1');
    echo "   - SubFolder moved to TestFolder1\n\n";
    
    // 6. Create files in subfolder
    echo "6. Creating files in moved subfolder...\n";
    $subFile1Id = $drive->putInFolder('sub_file1.txt', 'File in subfolder 1', 'SubFolder');
    $subFile2Id = $drive->putInFolder('sub_file2.txt', 'File in subfolder 2', 'SubFolder');
    echo "   - sub_file1.txt created in SubFolder (ID: $subFile1Id)\n";
    echo "   - sub_file2.txt created in SubFolder (ID: $subFile2Id)\n\n";
    
    // 7. List files in each folder
    echo "7. Listing files in folders...\n";
    
    echo "   Files in TestFolder1:\n";
    $folder1Files = $drive->listFilesInFolder('TestFolder1');
    foreach ($folder1Files as $file) {
        $type = $file['mimeType'] === 'application/vnd.google-apps.folder' ? '[FOLDER]' : '[FILE]';
        echo "     $type {$file['name']}\n";
    }
    
    echo "\n   Files in TestFolder2:\n";
    $folder2Files = $drive->listFilesInFolder('TestFolder2');
    foreach ($folder2Files as $file) {
        $type = $file['mimeType'] === 'application/vnd.google-apps.folder' ? '[FOLDER]' : '[FILE]';
        echo "     $type {$file['name']}\n";
    }
    
    echo "\n   Files in SubFolder:\n";
    $subFolderFiles = $drive->listFilesInFolder('SubFolder');
    foreach ($subFolderFiles as $file) {
        $type = $file['mimeType'] === 'application/vnd.google-apps.folder' ? '[FOLDER]' : '[FILE]';
        echo "     $type {$file['name']}\n";
    }
    echo "\n";
    
    // 8. Move file between folders
    echo "8. Moving file between folders...\n";
    $drive->moveFileToFolder('test_move_file2.txt', 'TestFolder1');
    echo "   - test_move_file2.txt moved from TestFolder2 to TestFolder1\n\n";
    
    // 9. Final verification
    echo "9. Final verification - Files in TestFolder1:\n";
    $finalFiles = $drive->listFilesInFolder('TestFolder1');
    foreach ($finalFiles as $file) {
        $type = $file['mimeType'] === 'application/vnd.google-apps.folder' ? '[FOLDER]' : '[FILE]';
        echo "   $type {$file['name']}\n";
    }
    echo "\n";
    
    echo "✅ All move operations completed successfully!\n\n";
    
    // Summary
    echo "📋 MOVE OPERATIONS SUMMARY:\n";
    echo "- Created test folders: ✅\n";
    echo "- Moved files to folders: ✅\n";
    echo "- Created files directly in folders: ✅\n";
    echo "- Moved folder to another folder: ✅\n";
    echo "- Listed files in folders: ✅\n";
    echo "- Moved files between folders: ✅\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

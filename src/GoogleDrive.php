<?php

declare(strict_types=1);

namespace GoogleDrivePHP;

use GoogleDrivePHP\Contracts\GoogleDriveInterface;
use GoogleDrivePHP\Support\GoogleDriveFactory;
use GoogleDrivePHP\Support\FileHelper;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * GoogleDrive - Static Helper Class
 * 
 * Static helper class untuk operasi Google Drive dengan design pattern yang clean dan mudah digunakan.
 * Semua method bersifat static untuk kemudahan penggunaan tanpa perlu instansiasi.
 */
class GoogleDrive implements GoogleDriveInterface
{
    /** @var Client|null */
    private static ?Client $client = null;
    
    /** @var Drive|null */
    private static ?Drive $service = null;
    
    /** @var array<string, DriveFile> */
    private static array $fileCache = [];
    
    /** @var bool */
    private static bool $initialized = false;

    /**
     * Initialize Google Drive service dari environment variables
     */
    public static function init(?array $config = null): void
    {
        if ($config) {
            $instances = GoogleDriveFactory::fromCredentials(
                $config['client_id'],
                $config['client_secret'],
                $config['refresh_token'],
                $config['access_token'] ?? null
            );
        } else {
            $instances = GoogleDriveFactory::fromEnv();
        }
        
        self::$client = $instances['client'];
        self::$service = $instances['service'];
        self::$initialized = true;
    }

    /**
     * Initialize dari credentials file
     */
    public static function initFromCredentialsFile(string $credentialsPath, string $refreshToken, ?string $accessToken = null): void
    {
        $instances = GoogleDriveFactory::fromCredentialsFile($credentialsPath, $refreshToken, $accessToken);
        self::$client = $instances['client'];
        self::$service = $instances['service'];
        self::$initialized = true;
    }

    /**
     * Auto initialize jika belum initialized
     */
    private static function ensureInitialized(): void
    {
        if (!self::$initialized) {
            self::init();
        }
    }

    // ======================
    // FILE OPERATIONS
    // ======================

    /**
     * Upload file dari string content
     */
    public static function put(string $filename, string $content, ?string $folderId = null): string
    {
        self::ensureInitialized();
        
        try {
            $sanitizedFilename = FileHelper::sanitizeFilename($filename);
            
            $fileMetadata = new DriveFile(['name' => $sanitizedFilename]);
            
            if ($folderId) {
                $fileMetadata->setParents([$folderId]);
            }
            
            $file = self::$service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => FileHelper::getMimeType($filename),
                'uploadType' => 'multipart',
                'fields' => 'id'
            ]);
            
            // Cache dengan nama asli dan sanitized
            $createdFile = self::$service->files->get($file->getId(), [
                'fields' => 'id, name, size, mimeType, modifiedTime, createdTime, parents, webViewLink, webContentLink'
            ]);
            self::$fileCache[$filename] = $createdFile;
            self::$fileCache[$sanitizedFilename] = $createdFile;
            
            return $file->getId();
        } catch (Exception $e) {
            throw new RuntimeException("Failed to upload file {$filename}: " . $e->getMessage());
        }
    }

    /**
     * Upload file dari path lokal
     */
    public static function putFile(string $localPath, ?string $filename = null, ?string $folderId = null): string
    {
        if (!file_exists($localPath)) {
            throw new InvalidArgumentException("Local file not found: {$localPath}");
        }
        
        $content = file_get_contents($localPath);
        $filename = $filename ?? basename($localPath);
        
        return self::put($filename, $content, $folderId);
    }

    /**
     * Download file content
     */
    public static function get(string $filename): ?string
    {
        self::ensureInitialized();
        
        try {
            $file = self::findFile($filename);
            if (!$file) {
                return null;
            }
            
            $response = self::$service->files->get($file->getId(), ['alt' => 'media']);
            return (string) $response->getBody();
        } catch (Exception $e) {
            throw new RuntimeException("Failed to download file {$filename}: " . $e->getMessage());
        }
    }

    /**
     * Download file by ID
     */
    public static function getById(string $fileId): ?string
    {
        self::ensureInitialized();
        
        try {
            $response = self::$service->files->get($fileId, ['alt' => 'media']);
            return (string) $response->getBody();
        } catch (Exception $e) {
            throw new RuntimeException("Failed to download file with ID {$fileId}: " . $e->getMessage());
        }
    }

    /**
     * Download file ke path lokal
     */
    public static function downloadToFile(string $filename, string $localPath): bool
    {
        $content = self::get($filename);
        if ($content === null) {
            return false;
        }
        
        // Create directory if not exists
        $directory = dirname($localPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        return file_put_contents($localPath, $content) !== false;
    }

    /**
     * Delete file
     */
    public static function delete(string $filename): bool
    {
        self::ensureInitialized();
        
        try {
            $file = self::findFile($filename);
            if (!$file) {
                return false;
            }
            
            self::$service->files->delete($file->getId());
            
            // Clear cache
            self::clearFileCache($filename);
            
            return true;
        } catch (Exception $e) {
            throw new RuntimeException("Failed to delete file {$filename}: " . $e->getMessage());
        }
    }

    /**
     * Delete file by ID
     */
    public static function deleteById(string $fileId): bool
    {
        self::ensureInitialized();
        
        try {
            self::$service->files->delete($fileId);
            self::$fileCache = []; // Clear all cache
            return true;
        } catch (Exception $e) {
            throw new RuntimeException("Failed to delete file with ID {$fileId}: " . $e->getMessage());
        }
    }

    /**
     * Copy file
     */
    public static function copy(string $source, string $destination, ?string $folderId = null): string
    {
        self::ensureInitialized();
        
        try {
            $sourceFile = self::findFile($source);
            if (!$sourceFile) {
                throw new InvalidArgumentException("Source file not found: {$source}");
            }
            
            $sanitizedDestination = FileHelper::sanitizeFilename($destination);
            $copiedFile = new DriveFile(['name' => $sanitizedDestination]);
            
            if ($folderId) {
                $copiedFile->setParents([$folderId]);
            }
            
            $result = self::$service->files->copy($sourceFile->getId(), $copiedFile);
            return $result->getId();
        } catch (Exception $e) {
            throw new RuntimeException("Failed to copy file {$source} to {$destination}: " . $e->getMessage());
        }
    }

    /**
     * Move file ke folder
     */
    public static function move(string $filename, string $folderId): bool
    {
        self::ensureInitialized();
        
        try {
            $file = self::findFile($filename);
            if (!$file) {
                throw new InvalidArgumentException("File not found: {$filename}");
            }
            
            $previousParents = $file->getParents();
            $previousParentsStr = $previousParents ? implode(',', $previousParents) : '';
            
            $emptyFile = new DriveFile();
            self::$service->files->update($file->getId(), $emptyFile, [
                'addParents' => $folderId,
                'removeParents' => $previousParentsStr
            ]);
            
            self::clearFileCache($filename);
            return true;
        } catch (Exception $e) {
            throw new RuntimeException("Failed to move file {$filename}: " . $e->getMessage());
        }
    }

    /**
     * Rename file
     */
    public static function rename(string $oldName, string $newName): bool
    {
        self::ensureInitialized();
        
        try {
            $file = self::findFile($oldName);
            if (!$file) {
                throw new InvalidArgumentException("File not found: {$oldName}");
            }
            
            $sanitizedNewName = FileHelper::sanitizeFilename($newName);
            $updatedFile = new DriveFile(['name' => $sanitizedNewName]);
            self::$service->files->update($file->getId(), $updatedFile);
            
            self::clearFileCache($oldName);
            return true;
        } catch (Exception $e) {
            throw new RuntimeException("Failed to rename file {$oldName}: " . $e->getMessage());
        }
    }

    // ======================
    // FILE INFO & LISTING
    // ======================

    /**
     * Check if file exists
     */
    public static function exists(string $filename): bool
    {
        self::ensureInitialized();
        return self::findFile($filename) !== null;
    }

    /**
     * Get file information
     */
    public static function getFileInfo(string $filename): ?array
    {
        self::ensureInitialized();
        
        $file = self::findFile($filename);
        if (!$file) {
            return null;
        }
        
        return FileHelper::driveFileToArray($file);
    }

    /**
     * List files
     */
    public static function files(?string $folderId = null, int $limit = 100): array
    {
        self::ensureInitialized();
        
        try {
            $query = "mimeType != 'application/vnd.google-apps.folder'";
            
            if ($folderId) {
                $query .= " and '{$folderId}' in parents";
            }
            
            $response = self::$service->files->listFiles([
                'q' => $query,
                'pageSize' => $limit,
                'fields' => 'files(id, name, size, mimeType, modifiedTime, createdTime, parents, webViewLink, webContentLink)'
            ]);
            
            $files = [];
            foreach ($response->getFiles() as $file) {
                $files[] = FileHelper::driveFileToArray($file);
            }
            
            return $files;
        } catch (Exception $e) {
            throw new RuntimeException("Failed to list files: " . $e->getMessage());
        }
    }

    /**
     * Search files
     */
    public static function search(string $query, int $limit = 100): array
    {
        self::ensureInitialized();
        
        try {
            $searchQuery = "name contains '{$query}'";
            
            $response = self::$service->files->listFiles([
                'q' => $searchQuery,
                'pageSize' => $limit,
                'fields' => 'files(id, name, size, mimeType, modifiedTime, createdTime, parents, webViewLink, webContentLink)'
            ]);
            
            $files = [];
            foreach ($response->getFiles() as $file) {
                $files[] = FileHelper::driveFileToArray($file);
            }
            
            return $files;
        } catch (Exception $e) {
            throw new RuntimeException("Failed to search files: " . $e->getMessage());
        }
    }

    // ======================
    // FOLDER OPERATIONS
    // ======================

    /**
     * Create directory
     */
    public static function makeDir(string $folderName, ?string $parentId = null): string
    {
        self::ensureInitialized();
        
        try {
            $sanitizedFolderName = FileHelper::sanitizeFilename($folderName);
            
            $fileMetadata = new DriveFile([
                'name' => $sanitizedFolderName,
                'mimeType' => 'application/vnd.google-apps.folder'
            ]);
            
            if ($parentId) {
                $fileMetadata->setParents([$parentId]);
            }
            
            $folder = self::$service->files->create($fileMetadata, [
                'fields' => 'id'
            ]);
            
            return $folder->getId();
        } catch (Exception $e) {
            throw new RuntimeException("Failed to create folder {$folderName}: " . $e->getMessage());
        }
    }

    /**
     * Delete directory
     */
    public static function deleteDir(string $folderName): bool
    {
        return self::delete($folderName);
    }

    /**
     * List folders
     */
    public static function folders(?string $parentId = null, int $limit = 100): array
    {
        self::ensureInitialized();
        
        try {
            $query = "mimeType = 'application/vnd.google-apps.folder'";
            
            if ($parentId) {
                $query .= " and '{$parentId}' in parents";
            }
            
            $response = self::$service->files->listFiles([
                'q' => $query,
                'pageSize' => $limit,
                'fields' => 'files(id, name, modifiedTime, createdTime, parents, webViewLink)'
            ]);
            
            $folders = [];
            foreach ($response->getFiles() as $folder) {
                $folders[] = FileHelper::driveFileToArray($folder);
            }
            
            return $folders;
        } catch (Exception $e) {
            throw new RuntimeException("Failed to list folders: " . $e->getMessage());
        }
    }

    /**
     * Find folder ID by name
     */
    public static function findFolderId(string $folderName): ?string
    {
        self::ensureInitialized();
        
        $folder = self::findFolder($folderName);
        return $folder ? $folder->getId() : null;
    }

    // ======================
    // SHARING & PERMISSIONS
    // ======================

    /**
     * Share file with email
     */
    public static function shareWithEmail(string $filename, string $email, string $role = 'reader'): bool
    {
        self::ensureInitialized();
        
        try {
            $file = self::findFile($filename);
            if (!$file) {
                throw new InvalidArgumentException("File not found: {$filename}");
            }
            
            $permission = new Permission([
                'type' => 'user',
                'role' => $role, // reader, writer, owner
                'emailAddress' => $email
            ]);
            
            self::$service->permissions->create($file->getId(), $permission);
            return true;
        } catch (Exception $e) {
            throw new RuntimeException("Failed to share file {$filename}: " . $e->getMessage());
        }
    }

    /**
     * Make file public
     */
    public static function makePublic(string $filename): string
    {
        self::ensureInitialized();
        
        try {
            $file = self::findFile($filename);
            if (!$file) {
                throw new InvalidArgumentException("File not found: {$filename}");
            }
            
            $permission = new Permission([
                'type' => 'anyone',
                'role' => 'reader'
            ]);
            
            self::$service->permissions->create($file->getId(), $permission);
            
            // Return shareable link
            return "https://drive.google.com/file/d/{$file->getId()}/view";
        } catch (Exception $e) {
            throw new RuntimeException("Failed to make file public {$filename}: " . $e->getMessage());
        }
    }

    /**
     * Get shareable link
     */
    public static function getShareableLink(string $filename): ?string
    {
        self::ensureInitialized();
        
        $file = self::findFile($filename);
        if (!$file) {
            return null;
        }
        
        return "https://drive.google.com/file/d/{$file->getId()}/view";
    }

    // ======================
    // BATCH OPERATIONS
    // ======================

    /**
     * Upload multiple files
     */
    public static function putMultiple(array $files, ?string $folderId = null): array
    {
        $results = [];
        
        foreach ($files as $filename => $content) {
            try {
                $fileId = self::put($filename, $content, $folderId);
                $results[$filename] = ['success' => true, 'fileId' => $fileId];
            } catch (Exception $e) {
                $results[$filename] = ['success' => false, 'error' => $e->getMessage()];
            }
        }
        
        return $results;
    }

    /**
     * Delete multiple files
     */
    public static function deleteMultiple(array $filenames): array
    {
        $results = [];
        
        foreach ($filenames as $filename) {
            try {
                $success = self::delete($filename);
                $results[$filename] = ['success' => $success];
            } catch (Exception $e) {
                $results[$filename] = ['success' => false, 'error' => $e->getMessage()];
            }
        }
        
        return $results;
    }

    /**
     * Backup folder
     */
    public static function backupFolder(?string $folderId = null, string $localPath = './backup'): array
    {
        if (!is_dir($localPath)) {
            mkdir($localPath, 0755, true);
        }
        
        $files = self::files($folderId);
        $results = [];
        
        foreach ($files as $file) {
            try {
                $content = self::getById($file['id']);
                $localFilePath = $localPath . '/' . $file['name'];
                
                if (file_put_contents($localFilePath, $content) !== false) {
                    $results[$file['name']] = ['success' => true, 'localPath' => $localFilePath];
                } else {
                    $results[$file['name']] = ['success' => false, 'error' => 'Failed to write local file'];
                }
            } catch (Exception $e) {
                $results[$file['name']] = ['success' => false, 'error' => $e->getMessage()];
            }
        }
        
        return $results;
    }

    /**
     * List all contents (files + folders)
     */
    public static function all(?string $folderId = null, bool $recursive = false): array
    {
        self::ensureInitialized();
        
        try {
            $query = '';
            
            if ($folderId) {
                $query = "'{$folderId}' in parents";
            }
            
            $response = self::$service->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name, size, mimeType, modifiedTime, createdTime, parents, webViewLink, webContentLink)'
            ]);
            
            $items = [];
            foreach ($response->getFiles() as $item) {
                $itemData = FileHelper::driveFileToArray($item);
                $itemData['type'] = FileHelper::isFolder($item) ? 'folder' : 'file';
                $items[] = $itemData;
                
                // Recursive untuk folder
                if ($recursive && FileHelper::isFolder($item)) {
                    $subItems = self::all($item->getId(), true);
                    foreach ($subItems as $subItem) {
                        $items[] = $subItem;
                    }
                }
            }
            
            return $items;
        } catch (Exception $e) {
            throw new RuntimeException("Failed to list all contents: " . $e->getMessage());
        }
    }

    // ======================
    // HELPER METHODS
    // ======================

    /**
     * Find file by name
     */
    private static function findFile(string $filename): ?DriveFile
    {
        if (isset(self::$fileCache[$filename])) {
            return self::$fileCache[$filename];
        }
        
        try {
            // Coba cari dengan nama asli dulu
            $response = self::$service->files->listFiles([
                'q' => "name = '{$filename}'",
                'fields' => 'files(id, name, size, mimeType, modifiedTime, createdTime, parents, webViewLink, webContentLink)'
            ]);
            
            $files = $response->getFiles();
            if (count($files) > 0) {
                self::$fileCache[$filename] = $files[0];
                return $files[0];
            }
            
            // Jika tidak ditemukan, coba cari dengan nama sanitized
            $sanitizedFilename = FileHelper::sanitizeFilename($filename);
            if ($sanitizedFilename !== $filename) {
                $response = self::$service->files->listFiles([
                    'q' => "name = '{$sanitizedFilename}'",
                    'fields' => 'files(id, name, size, mimeType, modifiedTime, createdTime, parents, webViewLink, webContentLink)'
                ]);
                
                $files = $response->getFiles();
                if (count($files) > 0) {
                    self::$fileCache[$filename] = $files[0];
                    self::$fileCache[$sanitizedFilename] = $files[0];
                    return $files[0];
                }
            }
            
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Find folder by name
     */
    private static function findFolder(string $folderName): ?DriveFile
    {
        try {
            $response = self::$service->files->listFiles([
                'q' => "name = '{$folderName}' and mimeType = 'application/vnd.google-apps.folder'",
                'fields' => 'files(id, name, modifiedTime, createdTime, parents, webViewLink)'
            ]);
            
            $folders = $response->getFiles();
            return count($folders) > 0 ? $folders[0] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Clear file cache
     */
    private static function clearFileCache(?string $filename = null): void
    {
        if ($filename) {
            unset(self::$fileCache[$filename]);
        } else {
            self::$fileCache = [];
        }
    }

    /**
     * Reset static state (untuk testing)
     */
    public static function reset(): void
    {
        self::$client = null;
        self::$service = null;
        self::$fileCache = [];
        self::$initialized = false;
    }

    /**
     * Get Google Client instance (untuk advanced usage)
     */
    public static function getClient(): ?Client
    {
        self::ensureInitialized();
        return self::$client;
    }

    /**
     * Get Google Drive Service instance (untuk advanced usage)
     */
    public static function getService(): ?Drive
    {
        self::ensureInitialized();
        return self::$service;
    }
}

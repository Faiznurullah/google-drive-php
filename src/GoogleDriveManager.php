<?php

declare(strict_types=1);

namespace GoogleDrivePHP;

use GoogleDrivePHP\Auth\GoogleDriveClient;
use GoogleDrivePHP\Contracts\GoogleDriveInterface;
use GoogleDrivePHP\Exceptions\GoogleDriveException;
use GoogleDrivePHP\Models\DriveFile;
use GoogleDrivePHP\Models\DriveFolder;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile as GoogleDriveFile;
use Exception;
use InvalidArgumentException;

class GoogleDriveManager implements GoogleDriveInterface
{
    protected GoogleDriveClient $client;
    /** @var Drive|null */
    protected $service = null;
    /** @var array<string, GoogleDriveFile> */
    protected array $fileCache = [];

    public function __construct(GoogleDriveClient $client)
    {
        $this->client = $client;
        $this->initializeService();
    }

    private function initializeService(): void
    {
        // Check if Google Client Library is installed
        if (!class_exists('Google\Service\Drive')) {
            throw new \RuntimeException(
                'Google Client Library is not installed. Run: composer require google/apiclient'
            );
        }
        
        $this->service = new Drive($this->client->getGoogleClient());
    }

    public function get(string $path): ?string
    {
        try {
            $file = $this->findFileByPath($path);
            if (!$file) {
                return null;
            }

            $response = $this->service->files->get($file->getId(), [
                'alt' => 'media'
            ]);

            // For Google Drive API v3, response with alt=media returns string content
            return (string) $response;
        } catch (Exception $e) {
            throw GoogleDriveException::downloadFailed($path, $e->getMessage());
        }
    }

    public function put(string $path, string $contents, array $options = []): bool
    {
        try {
            $fileName = basename($path);
            $parentPath = dirname($path);
            
            // Create parent directories if they don't exist
            $parentId = null;
            if ($parentPath !== '.' && $parentPath !== '') {
                $parentId = $this->ensureDirectoryExists($parentPath);
            }

            /** @var GoogleDriveFile $file */
            $file = new GoogleDriveFile();
            $file->setName($fileName);
            
            if ($parentId) {
                $file->setParents([$parentId]);
            }

            $result = $this->service->files->create($file, [
                'data' => $contents,
                'mimeType' => $options['mimeType'] ?? 'application/octet-stream',
                'uploadType' => 'multipart'
            ]);

            // Clear cache for this path
            unset($this->fileCache[$path]);

            return $result->getId() !== null;
        } catch (Exception $e) {
            throw GoogleDriveException::uploadFailed($path, $e->getMessage());
        }
    }

    public function putFile(string $path, $file, array $options = []): bool
    {
        $contents = '';
        
        if (class_exists('\Illuminate\Http\UploadedFile') && is_object($file) && method_exists($file, 'getPathname')) {
            $contents = file_get_contents($file->getPathname());
            // Try to get mime type from uploaded file, suppress any potential errors
            try {
                if (method_exists($file, 'getMimeType')) {
                    /** @var \Illuminate\Http\UploadedFile $file */
                    $options['mimeType'] = $options['mimeType'] ?? $file->getMimeType();
                } elseif (method_exists($file, 'getClientMimeType')) {
                    /** @var \Illuminate\Http\UploadedFile $file */
                    $options['mimeType'] = $options['mimeType'] ?? $file->getClientMimeType();
                }
            } catch (Exception $e) {
                // Fallback to default mime type if method call fails
            }
        } elseif (is_string($file) && file_exists($file)) {
            $contents = file_get_contents($file);
            $options['mimeType'] = $options['mimeType'] ?? mime_content_type($file);
        } else {
            throw new InvalidArgumentException('Invalid file provided');
        }

        return $this->put($path, $contents, $options);
    }

    /**
     * @param string $path
     * @return resource|false
     */
    public function readStream(string $path)
    {
        try {
            $file = $this->findFileByPath($path);
            if (!$file) {
                return false;
            }

            $response = $this->service->files->get($file->getId(), [
                'alt' => 'media'
            ]);

            // Create a temporary resource for the response
            $resource = fopen('php://temp', 'r+');
            fwrite($resource, (string) $response);
            rewind($resource);
            return $resource;
        } catch (Exception $e) {
            throw GoogleDriveException::downloadFailed($path, $e->getMessage());
        }
    }

    public function writeStream(string $path, $resource, array $options = []): bool
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException('Invalid resource provided');
        }

        $contents = stream_get_contents($resource);
        if ($contents === false) {
            throw new InvalidArgumentException('Could not read from resource');
        }
        
        return $this->put($path, $contents, $options);
    }

    public function delete(string $path): bool
    {
        try {
            $file = $this->findFileByPath($path);
            if (!$file) {
                return false;
            }

            $this->service->files->delete($file->getId());
            
            // Clear cache
            unset($this->fileCache[$path]);
            
            return true;
        } catch (Exception $e) {
            throw GoogleDriveException::deletionFailed($path, $e->getMessage());
        }
    }

    public function exists(string $path): bool
    {
        return $this->findFileByPath($path) !== null;
    }

    public function size(string $path): int
    {
        $file = $this->findFileByPath($path);
        if (!$file) {
            throw GoogleDriveException::fileNotFound($path);
        }

        $size = $file->getSize();
        return $size ? (int) $size : 0;
    }

    public function lastModified(string $path): int
    {
        $file = $this->findFileByPath($path);
        if (!$file) {
            throw GoogleDriveException::fileNotFound($path);
        }

        $modifiedTime = $file->getModifiedTime();
        return $modifiedTime ? strtotime($modifiedTime) : 0;
    }

    /**
     * @param string $directory
     * @return array<int, array<string, mixed>>
     */
    public function files(string $directory = ''): array
    {
        $folderId = $this->getFolderId($directory);
        if ($directory && !$folderId) {
            return [];
        }

        $query = "mimeType != 'application/vnd.google-apps.folder'";
        if ($folderId && $folderId !== 'root') {
            $query .= " and '{$folderId}' in parents";
        } elseif (!$directory) {
            $query .= " and 'root' in parents";
        }

        $response = $this->service->files->listFiles([
            'q' => $query,
            'fields' => 'files(id, name, size, modifiedTime, mimeType, parents)'
        ]);

        $files = [];
        foreach ($response->getFiles() as $file) {
            $filePath = $directory ? "{$directory}/{$file->getName()}" : $file->getName();
            $files[] = DriveFile::fromGoogleFile($file, $filePath)->toArray();
        }

        return $files;
    }

    /**
     * @param string $directory
     * @return array<int, array<string, mixed>>
     */
    public function directories(string $directory = ''): array
    {
        $folderId = $this->getFolderId($directory);
        if ($directory && !$folderId) {
            return [];
        }

        $query = "mimeType = 'application/vnd.google-apps.folder'";
        if ($folderId && $folderId !== 'root') {
            $query .= " and '{$folderId}' in parents";
        } elseif (!$directory) {
            $query .= " and 'root' in parents";
        }

        $response = $this->service->files->listFiles([
            'q' => $query,
            'fields' => 'files(id, name, modifiedTime, mimeType, parents)'
        ]);

        $directories = [];
        foreach ($response->getFiles() as $folder) {
            $folderPath = $directory ? "{$directory}/{$folder->getName()}" : $folder->getName();
            $directories[] = DriveFolder::fromGoogleFile($folder, $folderPath)->toArray();
        }

        return $directories;
    }

    public function makeDirectory(string $path): bool
    {
        try {
            return $this->ensureDirectoryExists($path) !== null;
        } catch (Exception $e) {
            throw GoogleDriveException::directoryCreationFailed($path, $e->getMessage());
        }
    }

    public function deleteDirectory(string $path): bool
    {
        return $this->delete($path);
    }

    public function copy(string $from, string $to): bool
    {
        try {
            $sourceFile = $this->findFileByPath($from);
            if (!$sourceFile) {
                throw GoogleDriveException::fileNotFound($from);
            }

            $fileName = basename($to);
            $parentPath = dirname($to);
            
            $parentId = null;
            if ($parentPath !== '.' && $parentPath !== '') {
                $parentId = $this->ensureDirectoryExists($parentPath);
            }

            /** @var GoogleDriveFile $copiedFile */
            $copiedFile = new GoogleDriveFile();
            $copiedFile->setName($fileName);
            
            if ($parentId) {
                $copiedFile->setParents([$parentId]);
            }

            $result = $this->service->files->copy($sourceFile->getId(), $copiedFile);
            
            return $result->getId() !== null;
        } catch (Exception $e) {
            throw new GoogleDriveException("Copy failed from {$from} to {$to}: " . $e->getMessage());
        }
    }

    public function move(string $from, string $to): bool
    {
        try {
            $file = $this->findFileByPath($from);
            if (!$file) {
                throw GoogleDriveException::fileNotFound($from);
            }

            $fileName = basename($to);
            $parentPath = dirname($to);
            
            $newParentId = null;
            if ($parentPath !== '.' && $parentPath !== '') {
                $newParentId = $this->ensureDirectoryExists($parentPath);
            }

            /** @var GoogleDriveFile $emptyFile */
            $emptyFile = new GoogleDriveFile();
            $emptyFile->setName($fileName);
            
            $previousParents = $file->getParents();
            $previousParentsStr = $previousParents ? implode(',', $previousParents) : '';
            
            $updateParams = [
                'addParents' => $newParentId ?: 'root',
                'removeParents' => $previousParentsStr
            ];

            $result = $this->service->files->update($file->getId(), $emptyFile, $updateParams);
            
            // Clear cache for both paths
            unset($this->fileCache[$from], $this->fileCache[$to]);
            
            return $result->getId() !== null;
        } catch (Exception $e) {
            throw new GoogleDriveException("Move failed from {$from} to {$to}: " . $e->getMessage());
        }
    }

    // Helper methods

    /**
     * @param string $path
     * @return GoogleDriveFile|null
     */
    protected function findFileByPath(string $path): ?GoogleDriveFile
    {
        if (isset($this->fileCache[$path])) {
            return $this->fileCache[$path];
        }

        if (empty($path) || $path === '/') {
            return null;
        }

        $parts = explode('/', trim($path, '/'));
        $currentParentId = 'root';
        $file = null;

        foreach ($parts as $part) {
            $query = "name = '" . addslashes($part) . "' and '{$currentParentId}' in parents";
            
            $response = $this->service->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name, mimeType, size, modifiedTime, parents)'
            ]);

            $files = $response->getFiles();
            if (empty($files)) {
                return null;
            }

            $file = $files[0];
            $currentParentId = $file->getId();
        }

        if ($file) {
            $this->fileCache[$path] = $file;
        }

        return $file;
    }

    protected function ensureDirectoryExists(string $path): ?string
    {
        if (empty($path) || $path === '/') {
            return 'root';
        }

        $folder = $this->findFileByPath($path);
        if ($folder && $this->isFolder($folder)) {
            return $folder->getId();
        }

        // Create directory structure
        $parts = explode('/', trim($path, '/'));
        $currentParentId = 'root';

        foreach ($parts as $part) {
            $query = "name = '" . addslashes($part) . "' and '{$currentParentId}' in parents and mimeType = 'application/vnd.google-apps.folder'";
            
            $response = $this->service->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name)'
            ]);

            $folders = $response->getFiles();
            
            if (empty($folders)) {
                // Create folder
                /** @var GoogleDriveFile $folderMetadata */
                $folderMetadata = new GoogleDriveFile();
                $folderMetadata->setName($part);
                $folderMetadata->setMimeType('application/vnd.google-apps.folder');
                $folderMetadata->setParents([$currentParentId]);

                $folder = $this->service->files->create($folderMetadata);
                $currentParentId = $folder->getId();
            } else {
                $currentParentId = $folders[0]->getId();
            }
        }

        return $currentParentId;
    }

    private function getFolderId(string $directory): ?string
    {
        if (!$directory) {
            return 'root';
        }

        $folder = $this->findFileByPath($directory);
        if (!$folder || !$this->isFolder($folder)) {
            return null;
        }

        return $folder->getId();
    }

    /**
     * Check if a Google Drive file is a folder
     * @param GoogleDriveFile $file
     */
    private function isFolder(GoogleDriveFile $file): bool
    {
        return $file->getMimeType() === 'application/vnd.google-apps.folder';
    }
}
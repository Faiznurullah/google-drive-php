<?php

namespace GoogleDrivePHP;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class SimpleDrive
{
    private Client $client;
    private Drive $service;
    
    public function __construct(string $clientId, string $clientSecret, string $refreshToken, ?string $accessToken = null)
    {
        $this->client = new Client();
        $this->client->setClientId($clientId);
        $this->client->setClientSecret($clientSecret);
        $this->client->setRedirectUri('https://developers.google.com/oauthplayground');
        $this->client->setScopes(['https://www.googleapis.com/auth/drive']);
        $this->client->setAccessType('offline');
        
        // Set tokens
        if ($accessToken) {
            $this->client->setAccessToken($accessToken);
        }
        
        if ($refreshToken) {
            $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
        }
        
        $this->service = new Drive($this->client);
    }
    
    /**
     * Create from environment variables
     */
    public static function fromEnv(): self
    {
        return new self(
            getenv('GOOGLE_DRIVE_CLIENT_ID'),
            getenv('GOOGLE_DRIVE_CLIENT_SECRET'),
            getenv('GOOGLE_DRIVE_REFRESH_TOKEN'),
            getenv('GOOGLE_DRIVE_ACCESS_TOKEN')
        );
    }
    
    /**
     * Upload a file
     */
    public function put(string $filename, $content): string
    {
        $fileMetadata = new DriveFile(['name' => $filename]);
        
        $file = $this->service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => 'application/octet-stream',
            'uploadType' => 'multipart',
            'fields' => 'id'
        ]);
        
        return $file->getId();
    }
    
    /**
     * Download a file
     */
    public function get(string $filename): ?string
    {
        $file = $this->findFile($filename);
        if (!$file) {
            return null;
        }
        
        $response = $this->service->files->get($file->getId(), ['alt' => 'media']);
        return $response->getBody()->getContents();
    }
    
    /**
     * Delete a file
     */
    public function delete(string $filename): bool
    {
        $file = $this->findFile($filename);
        if (!$file) {
            return false;
        }
        
        $this->service->files->delete($file->getId());
        return true;
    }
    
    /**
     * List all files
     */
    public function files(): array
    {
        $response = $this->service->files->listFiles([
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
    
    /**
     * Check if file exists
     */
    public function exists(string $filename): bool
    {
        return $this->findFile($filename) !== null;
    }
    
    /**
     * Create a folder
     */
    public function makeDirectory(string $folderName): string
    {
        $fileMetadata = new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder'
        ]);
        
        $folder = $this->service->files->create($fileMetadata, [
            'fields' => 'id'
        ]);
        
        return $folder->getId();
    }
    
    /**
     * Find file by name
     */
    private function findFile(string $filename): ?DriveFile
    {
        $response = $this->service->files->listFiles([
            'q' => "name = '{$filename}'",
            'fields' => 'files(id, name)'
        ]);
        
        $files = $response->getFiles();
        return count($files) > 0 ? $files[0] : null;
    }
}

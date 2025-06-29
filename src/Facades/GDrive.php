<?php

namespace GoogleDrivePHP\Facades;

use GoogleDrivePHP\GoogleDrive;

/**
 * GoogleDrive Facade
 * 
 * Facade untuk GoogleDrive static class - memberikan cara alternatif untuk akses
 * Bisa digunakan dengan: use GoogleDrivePHP\Facades\GDrive;
 */
class GDrive
{
    /**
     * Initialize Google Drive service
     */
    public static function init(?array $config = null): void
    {
        GoogleDrive::init($config);
    }

    /**
     * Initialize dari credentials file
     */
    public static function initFromCredentialsFile(string $credentialsPath, string $refreshToken, ?string $accessToken = null): void
    {
        GoogleDrive::initFromCredentialsFile($credentialsPath, $refreshToken, $accessToken);
    }

    /**
     * Upload file dari string content
     */
    public static function put(string $filename, string $content, ?string $folderId = null): string
    {
        return GoogleDrive::put($filename, $content, $folderId);
    }

    /**
     * Upload file dari path lokal
     */
    public static function putFile(string $localPath, ?string $filename = null, ?string $folderId = null): string
    {
        return GoogleDrive::putFile($localPath, $filename, $folderId);
    }

    /**
     * Download file content
     */
    public static function get(string $filename): ?string
    {
        return GoogleDrive::get($filename);
    }

    /**
     * Download file by ID
     */
    public static function getById(string $fileId): ?string
    {
        return GoogleDrive::getById($fileId);
    }

    /**
     * Download file ke path lokal
     */
    public static function downloadToFile(string $filename, string $localPath): bool
    {
        return GoogleDrive::downloadToFile($filename, $localPath);
    }

    /**
     * Delete file
     */
    public static function delete(string $filename): bool
    {
        return GoogleDrive::delete($filename);
    }

    /**
     * Delete file by ID
     */
    public static function deleteById(string $fileId): bool
    {
        return GoogleDrive::deleteById($fileId);
    }

    /**
     * Copy file
     */
    public static function copy(string $source, string $destination, ?string $folderId = null): string
    {
        return GoogleDrive::copy($source, $destination, $folderId);
    }

    /**
     * Move file ke folder
     */
    public static function move(string $filename, string $folderId): bool
    {
        return GoogleDrive::move($filename, $folderId);
    }

    /**
     * Rename file
     */
    public static function rename(string $oldName, string $newName): bool
    {
        return GoogleDrive::rename($oldName, $newName);
    }

    /**
     * Check if file exists
     */
    public static function exists(string $filename): bool
    {
        return GoogleDrive::exists($filename);
    }

    /**
     * Get file information
     */
    public static function getFileInfo(string $filename): ?array
    {
        return GoogleDrive::getFileInfo($filename);
    }

    /**
     * List files
     */
    public static function files(?string $folderId = null, int $limit = 100): array
    {
        return GoogleDrive::files($folderId, $limit);
    }

    /**
     * Search files
     */
    public static function search(string $query, int $limit = 100): array
    {
        return GoogleDrive::search($query, $limit);
    }

    /**
     * Create directory
     */
    public static function makeDir(string $folderName, ?string $parentId = null): string
    {
        return GoogleDrive::makeDir($folderName, $parentId);
    }

    /**
     * Delete directory
     */
    public static function deleteDir(string $folderName): bool
    {
        return GoogleDrive::deleteDir($folderName);
    }

    /**
     * List folders
     */
    public static function folders(?string $parentId = null, int $limit = 100): array
    {
        return GoogleDrive::folders($parentId, $limit);
    }

    /**
     * Find folder ID by name
     */
    public static function findFolderId(string $folderName): ?string
    {
        return GoogleDrive::findFolderId($folderName);
    }

    /**
     * Share file with email
     */
    public static function shareWithEmail(string $filename, string $email, string $role = 'reader'): bool
    {
        return GoogleDrive::shareWithEmail($filename, $email, $role);
    }

    /**
     * Make file public
     */
    public static function makePublic(string $filename): string
    {
        return GoogleDrive::makePublic($filename);
    }

    /**
     * Get shareable link
     */
    public static function getShareableLink(string $filename): ?string
    {
        return GoogleDrive::getShareableLink($filename);
    }

    /**
     * Upload multiple files
     */
    public static function putMultiple(array $files, ?string $folderId = null): array
    {
        return GoogleDrive::putMultiple($files, $folderId);
    }

    /**
     * Delete multiple files
     */
    public static function deleteMultiple(array $filenames): array
    {
        return GoogleDrive::deleteMultiple($filenames);
    }

    /**
     * Backup folder
     */
    public static function backupFolder(?string $folderId = null, string $localPath = './backup'): array
    {
        return GoogleDrive::backupFolder($folderId, $localPath);
    }

    /**
     * List all contents (files + folders)
     */
    public static function all(?string $folderId = null, bool $recursive = false): array
    {
        return GoogleDrive::all($folderId, $recursive);
    }

    /**
     * Reset static state
     */
    public static function reset(): void
    {
        GoogleDrive::reset();
    }
}

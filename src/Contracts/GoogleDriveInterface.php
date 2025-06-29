<?php

namespace GoogleDrivePHP\Contracts;

/**
 * Google Drive Interface
 * 
 * Kontrak untuk operasi Google Drive
 */
interface GoogleDriveInterface
{
    /**
     * Upload file dari string content
     */
    public static function put(string $filename, string $content, ?string $folderId = null): string;

    /**
     * Upload file dari path lokal
     */
    public static function putFile(string $localPath, ?string $filename = null, ?string $folderId = null): string;

    /**
     * Download file content
     */
    public static function get(string $filename): ?string;

    /**
     * Download file by ID
     */
    public static function getById(string $fileId): ?string;

    /**
     * Download file ke path lokal
     */
    public static function downloadToFile(string $filename, string $localPath): bool;

    /**
     * Delete file
     */
    public static function delete(string $filename): bool;

    /**
     * Delete file by ID
     */
    public static function deleteById(string $fileId): bool;

    /**
     * Copy file
     */
    public static function copy(string $source, string $destination, ?string $folderId = null): string;

    /**
     * Move file ke folder
     */
    public static function move(string $filename, string $folderId): bool;

    /**
     * Rename file
     */
    public static function rename(string $oldName, string $newName): bool;

    /**
     * Check if file exists
     */
    public static function exists(string $filename): bool;

    /**
     * Get file information
     */
    public static function getFileInfo(string $filename): ?array;

    /**
     * List files
     */
    public static function files(?string $folderId = null, int $limit = 100): array;

    /**
     * Search files
     */
    public static function search(string $query, int $limit = 100): array;

    /**
     * Create directory
     */
    public static function makeDir(string $folderName, ?string $parentId = null): string;

    /**
     * Delete directory
     */
    public static function deleteDir(string $folderName): bool;

    /**
     * List folders
     */
    public static function folders(?string $parentId = null, int $limit = 100): array;

    /**
     * Find folder ID by name
     */
    public static function findFolderId(string $folderName): ?string;

    /**
     * Share file with email
     */
    public static function shareWithEmail(string $filename, string $email, string $role = 'reader'): bool;

    /**
     * Make file public
     */
    public static function makePublic(string $filename): string;

    /**
     * Get shareable link
     */
    public static function getShareableLink(string $filename): ?string;

    /**
     * Upload multiple files
     */
    public static function putMultiple(array $files, ?string $folderId = null): array;

    /**
     * Delete multiple files
     */
    public static function deleteMultiple(array $filenames): array;

    /**
     * Backup folder
     */
    public static function backupFolder(?string $folderId = null, string $localPath = './backup'): array;

    /**
     * List all contents (files + folders)
     */
    public static function all(?string $folderId = null, bool $recursive = false): array;
}

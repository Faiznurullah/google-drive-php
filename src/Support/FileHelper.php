<?php

namespace GoogleDrivePHP\Support;

use Google\Service\Drive\DriveFile;

/**
 * File Helper
 * 
 * Helper untuk operasi file dan informasi file
 */
class FileHelper
{
    /**
     * Get file information dari path
     */
    public static function getFileInfo(string $filepath): object
    {
        $path = str_replace('\\', '/', $filepath);
        $arr = explode('/', $path);
        $filename = end($arr);
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        return (object) [
            'filename' => $filename,
            'basename' => pathinfo($filename, PATHINFO_FILENAME),
            'ext' => $ext,
            'path' => $path,
            'directory' => dirname($path)
        ];
    }

    /**
     * Convert Google Drive file ke array
     */
    public static function driveFileToArray(DriveFile $file): array
    {
        return [
            'id' => $file->getId(),
            'name' => $file->getName(),
            'size' => $file->getSize(),
            'mimeType' => $file->getMimeType(),
            'modifiedTime' => $file->getModifiedTime(),
            'createdTime' => $file->getCreatedTime(),
            'parents' => $file->getParents(),
            'webViewLink' => $file->getWebViewLink(),
            'webContentLink' => $file->getWebContentLink()
        ];
    }

    /**
     * Get MIME type dari file path
     */
    public static function getMimeType(string $filepath): string
    {
        if (file_exists($filepath)) {
            $mimeType = mime_content_type($filepath);
            return $mimeType ?: 'application/octet-stream';
        }

        // Fallback berdasarkan extension
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'txt' => 'text/plain',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'zip' => 'application/zip',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'csv' => 'text/csv'
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Check if file is folder
     */
    public static function isFolder(DriveFile $file): bool
    {
        return $file->getMimeType() === 'application/vnd.google-apps.folder';
    }

    /**
     * Format file size
     */
    public static function formatFileSize(?string $bytes): string
    {
        if ($bytes === null || $bytes === '') {
            return '0 B';
        }

        $size = (int) $bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * Sanitize filename untuk Google Drive
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove atau replace karakter yang tidak diizinkan
        $filename = preg_replace('/[<>:"\\/\\\\|?*]/', '_', $filename);
        
        // Trim spaces
        $filename = trim($filename);
        
        // Pastikan tidak kosong
        if (empty($filename)) {
            $filename = 'untitled_' . date('YmdHis');
        }

        return $filename;
    }

    /**
     * Generate unique filename jika sudah ada
     */
    public static function generateUniqueFilename(string $filename, array $existingFiles): string
    {
        $originalFilename = $filename;
        $fileInfo = pathinfo($filename);
        $basename = $fileInfo['filename'];
        $extension = isset($fileInfo['extension']) ? '.' . $fileInfo['extension'] : '';
        
        $counter = 1;
        while (in_array($filename, $existingFiles)) {
            $filename = $basename . '_' . $counter . $extension;
            $counter++;
        }

        return $filename;
    }
}

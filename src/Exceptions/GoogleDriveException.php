<?php

declare(strict_types=1);

namespace GoogleDrivePHP\Exceptions;

use Exception;

class GoogleDriveException extends Exception
{
    public static function fileNotFound(string $path): self
    {
        return new self("File not found: {$path}");
    }

    public static function uploadFailed(string $path, string $reason = ''): self
    {
        $message = "Upload failed for {$path}";
        if ($reason) {
            $message .= ": {$reason}";
        }
        return new self($message);
    }

    public static function downloadFailed(string $path, string $reason = ''): self
    {
        $message = "Download failed for {$path}";
        if ($reason) {
            $message .= ": {$reason}";
        }
        return new self($message);
    }

    public static function authenticationFailed(string $reason = ''): self
    {
        $message = "Google Drive authentication failed";
        if ($reason) {
            $message .= ": {$reason}";
        }
        return new self($message);
    }

    public static function directoryCreationFailed(string $path, string $reason = ''): self
    {
        $message = "Directory creation failed for {$path}";
        if ($reason) {
            $message .= ": {$reason}";
        }
        return new self($message);
    }

    public static function deletionFailed(string $path, string $reason = ''): self
    {
        $message = "Deletion failed for {$path}";
        if ($reason) {
            $message .= ": {$reason}";
        }
        return new self($message);
    }

    public static function invalidCredentials(): self
    {
        return new self("Invalid Google Drive credentials provided");
    }

    public static function apiQuotaExceeded(): self
    {
        return new self("Google Drive API quota exceeded");
    }
}

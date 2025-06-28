<?php

declare(strict_types=1);

namespace GoogleDrivePHP\Models;

class DriveFolder extends DriveFile
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->mimeType = 'application/vnd.google-apps.folder';
    }

    public function isFolder(): bool
    {
        return true;
    }

    public function isFile(): bool
    {
        return false;
    }

    /**
     * Create DriveFolder from Google API file object.
     *
     * @param mixed $googleFile Google API file object
     * @param string $path The folder path
     * @return self
     */
    public static function fromGoogleFile($googleFile, string $path = ''): self
    {
        return new self([
            'id' => $googleFile->getId(),
            'name' => $googleFile->getName(),
            'path' => $path,
            'mimeType' => $googleFile->getMimeType(),
            'modifiedTime' => $googleFile->getModifiedTime(),
            'createdTime' => $googleFile->getCreatedTime(),
            'parents' => $googleFile->getParents(),
        ]);
    }
}

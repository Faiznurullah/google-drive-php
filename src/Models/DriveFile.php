<?php

declare(strict_types=1);

namespace GoogleDrivePHP\Models;

class DriveFile
{
    public string $id;
    public string $name;
    public string $path;
    public ?int $size;
    public ?string $mimeType;
    public ?string $modifiedTime;
    public ?string $createdTime;
    /** @var array<string>|null */
    public ?array $parents;
    /** @var array<string, mixed>|null */
    public ?array $permissions;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->path = $data['path'] ?? '';
        $this->size = $data['size'] ?? null;
        $this->mimeType = $data['mimeType'] ?? null;
        $this->modifiedTime = $data['modifiedTime'] ?? null;
        $this->createdTime = $data['createdTime'] ?? null;
        $this->parents = $data['parents'] ?? null;
        $this->permissions = $data['permissions'] ?? null;
    }

    public function isFolder(): bool
    {
        return $this->mimeType === 'application/vnd.google-apps.folder';
    }

    public function isFile(): bool
    {
        return !$this->isFolder();
    }

    public function getExtension(): ?string
    {
        return pathinfo($this->name, PATHINFO_EXTENSION) ?: null;
    }

    public function getBasename(): string
    {
        return pathinfo($this->name, PATHINFO_FILENAME);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'path' => $this->path,
            'size' => $this->size,
            'mimeType' => $this->mimeType,
            'modifiedTime' => $this->modifiedTime,
            'createdTime' => $this->createdTime,
            'parents' => $this->parents,
            'permissions' => $this->permissions,
            'isFolder' => $this->isFolder(),
            'extension' => $this->getExtension(),
        ];
    }

    /**
     * Create DriveFile from Google API file object.
     *
     * @param mixed $googleFile Google API file object
     * @param string $path The file path
     * @return self
     */
    public static function fromGoogleFile($googleFile, string $path = ''): self
    {
        return new self([
            'id' => $googleFile->getId(),
            'name' => $googleFile->getName(),
            'path' => $path,
            'size' => $googleFile->getSize(),
            'mimeType' => $googleFile->getMimeType(),
            'modifiedTime' => $googleFile->getModifiedTime(),
            'createdTime' => $googleFile->getCreatedTime(),
            'parents' => $googleFile->getParents(),
        ]);
    }
}

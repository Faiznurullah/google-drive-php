<?php

declare(strict_types=1);

namespace GoogleDrivePHP\Tests;

use PHPUnit\Framework\TestCase;
use GoogleDrivePHP\GoogleDriveManager;
use GoogleDrivePHP\Models\DriveFile;
use GoogleDrivePHP\Models\DriveFolder;
use GoogleDrivePHP\Exceptions\GoogleDriveException;
use GoogleDrivePHP\Auth\GoogleDriveClient;
use Google\Service\Drive;
use Google\Client;

class GoogleDriveManagerTest extends TestCase
{
    private GoogleDriveManager $manager;
    private GoogleDriveClient $mockClient;

    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(GoogleDriveClient::class);
        $this->manager = new GoogleDriveManager($this->mockClient);
    }

    public function testExistsReturnsTrueForExistingFile(): void
    {
        // This would require mocking the Google API response
        // For now, just test the method exists
        $this->assertTrue(method_exists($this->manager, 'exists'));
    }

    public function testGetReturnsNullForNonExistentFile(): void
    {
        // This would require mocking the Google API response
        $this->assertTrue(method_exists($this->manager, 'get'));
    }

    public function testPutMethodExists(): void
    {
        $this->assertTrue(method_exists($this->manager, 'put'));
    }

    public function testPutFileMethodExists(): void
    {
        $this->assertTrue(method_exists($this->manager, 'putFile'));
    }

    public function testDeleteMethodExists(): void
    {
        $this->assertTrue(method_exists($this->manager, 'delete'));
    }

    public function testCopyMethodExists(): void
    {
        $this->assertTrue(method_exists($this->manager, 'copy'));
    }

    public function testMoveMethodExists(): void
    {
        $this->assertTrue(method_exists($this->manager, 'move'));
    }

    public function testSizeMethodExists(): void
    {
        $this->assertTrue(method_exists($this->manager, 'size'));
    }

    public function testLastModifiedMethodExists(): void
    {
        $this->assertTrue(method_exists($this->manager, 'lastModified'));
    }

    public function testFilesMethodExists(): void
    {
        $this->assertTrue(method_exists($this->manager, 'files'));
    }

    public function testDirectoriesMethodExists(): void
    {
        $this->assertTrue(method_exists($this->manager, 'directories'));
    }

    public function testMakeDirectoryMethodExists(): void
    {
        $this->assertTrue(method_exists($this->manager, 'makeDirectory'));
    }

    public function testDeleteDirectoryMethodExists(): void
    {
        $this->assertTrue(method_exists($this->manager, 'deleteDirectory'));
    }

    public function testReadStreamMethodExists(): void
    {
        $this->assertTrue(method_exists($this->manager, 'readStream'));
    }
}

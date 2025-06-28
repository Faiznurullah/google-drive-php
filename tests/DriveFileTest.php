<?php

declare(strict_types=1);

namespace GoogleDrivePHP\Tests;

use PHPUnit\Framework\TestCase;
use GoogleDrivePHP\Models\DriveFile;
use GoogleDrivePHP\Models\DriveFolder;

class DriveFileTest extends TestCase
{
    public function test_can_create_drive_file()
    {
        $data = [
            'id' => 'file123',
            'name' => 'test.txt',
            'path' => 'documents/test.txt',
            'size' => 1024,
            'mimeType' => 'text/plain'
        ];

        $file = new DriveFile($data);

        $this->assertEquals('file123', $file->id);
        $this->assertEquals('test.txt', $file->name);
        $this->assertEquals('documents/test.txt', $file->path);
        $this->assertEquals(1024, $file->size);
        $this->assertEquals('text/plain', $file->mimeType);
    }

    public function test_is_file_returns_true_for_regular_files()
    {
        $file = new DriveFile([
            'mimeType' => 'text/plain'
        ]);

        $this->assertTrue($file->isFile());
        $this->assertFalse($file->isFolder());
    }

    public function test_is_folder_returns_false_for_regular_files()
    {
        $file = new DriveFile([
            'mimeType' => 'text/plain'
        ]);

        $this->assertFalse($file->isFolder());
    }

    public function test_get_extension_returns_correct_extension()
    {
        $file = new DriveFile([
            'name' => 'document.pdf'
        ]);

        $this->assertEquals('pdf', $file->getExtension());
    }

    public function test_get_extension_returns_null_for_no_extension()
    {
        $file = new DriveFile([
            'name' => 'document'
        ]);

        $this->assertNull($file->getExtension());
    }

    public function test_get_basename_returns_filename_without_extension()
    {
        $file = new DriveFile([
            'name' => 'document.pdf'
        ]);

        $this->assertEquals('document', $file->getBasename());
    }

    public function test_to_array_returns_all_properties()
    {
        $data = [
            'id' => 'file123',
            'name' => 'test.txt',
            'path' => 'documents/test.txt',
            'size' => 1024,
            'mimeType' => 'text/plain'
        ];

        $file = new DriveFile($data);
        $array = $file->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('path', $array);
        $this->assertArrayHasKey('size', $array);
        $this->assertArrayHasKey('mimeType', $array);
        $this->assertArrayHasKey('isFolder', $array);
        $this->assertArrayHasKey('extension', $array);

        $this->assertEquals('file123', $array['id']);
        $this->assertEquals('test.txt', $array['name']);
        $this->assertFalse($array['isFolder']);
        $this->assertEquals('txt', $array['extension']);
    }
}

class DriveFolderTest extends TestCase
{
    public function test_can_create_drive_folder()
    {
        $data = [
            'id' => 'folder123',
            'name' => 'Documents',
            'path' => 'Documents'
        ];

        $folder = new DriveFolder($data);

        $this->assertEquals('folder123', $folder->id);
        $this->assertEquals('Documents', $folder->name);
        $this->assertEquals('Documents', $folder->path);
        $this->assertEquals('application/vnd.google-apps.folder', $folder->mimeType);
    }

    public function test_is_folder_returns_true()
    {
        $folder = new DriveFolder();

        $this->assertTrue($folder->isFolder());
        $this->assertFalse($folder->isFile());
    }

    public function test_to_array_shows_as_folder()
    {
        $folder = new DriveFolder([
            'id' => 'folder123',
            'name' => 'Documents'
        ]);

        $array = $folder->toArray();

        $this->assertTrue($array['isFolder']);
        $this->assertEquals('application/vnd.google-apps.folder', $array['mimeType']);
    }
}

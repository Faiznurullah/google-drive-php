<?php

declare(strict_types=1);

namespace GoogleDrivePHP\Tests;

use PHPUnit\Framework\TestCase;
use GoogleDrivePHP\GoogleDriveBuilder;
use GoogleDrivePHP\GoogleDriveManager;
use GoogleDrivePHP\Auth\GoogleDriveClient;
use GoogleDrivePHP\Exceptions\GoogleDriveException;

class GoogleDriveBuilderTest extends TestCase
{
    public function test_can_create_builder_instance()
    {
        $builder = GoogleDriveBuilder::create();
        $this->assertInstanceOf(GoogleDriveBuilder::class, $builder);
    }

    public function test_can_set_credentials()
    {
        $builder = GoogleDriveBuilder::create()
            ->withCredentials('/path/to/credentials.json');
        
        $this->assertInstanceOf(GoogleDriveBuilder::class, $builder);
    }

    public function test_can_set_scopes()
    {
        $scopes = ['https://www.googleapis.com/auth/drive'];
        $builder = GoogleDriveBuilder::create()
            ->withScopes($scopes);
        
        $this->assertInstanceOf(GoogleDriveBuilder::class, $builder);
    }

    public function test_can_set_access_type()
    {
        $builder = GoogleDriveBuilder::create()
            ->withAccessType('online');
        
        $this->assertInstanceOf(GoogleDriveBuilder::class, $builder);
    }

    public function test_can_set_access_token()
    {
        $token = 'sample_access_token';
        $builder = GoogleDriveBuilder::create()
            ->withAccessToken($token);
        
        $this->assertInstanceOf(GoogleDriveBuilder::class, $builder);
    }

    public function test_can_set_app_name()
    {
        $appName = 'My Custom App';
        $builder = GoogleDriveBuilder::create()
            ->withAppName($appName);
        
        $this->assertInstanceOf(GoogleDriveBuilder::class, $builder);
    }

    public function test_can_configure_full_access()
    {
        $builder = GoogleDriveBuilder::create()
            ->withFullAccess();
        
        $this->assertInstanceOf(GoogleDriveBuilder::class, $builder);
    }

    public function test_can_configure_readonly_access()
    {
        $builder = GoogleDriveBuilder::create()
            ->withReadOnlyAccess();
        
        $this->assertInstanceOf(GoogleDriveBuilder::class, $builder);
    }

    public function test_can_configure_file_access()
    {
        $builder = GoogleDriveBuilder::create()
            ->withFileAccess();
        
        $this->assertInstanceOf(GoogleDriveBuilder::class, $builder);
    }

    public function test_fluent_interface()
    {
        $builder = GoogleDriveBuilder::create()
            ->withCredentials('/path/to/credentials.json')
            ->withFullAccess()
            ->withAccessType('offline')
            ->withAppName('Test App');
        
        $this->assertInstanceOf(GoogleDriveBuilder::class, $builder);
    }
}

<?php

declare(strict_types=1);

namespace Tests;

use GoogleDrivePHP\GoogleDriveBuilder;
use GoogleDrivePHP\GoogleDriveManager;
use GoogleDrivePHP\Auth\GoogleDriveClient;
use PHPUnit\Framework\TestCase;

class DirectCredentialsTest extends TestCase
{
    private string $testClientId = 'test-client-id.apps.googleusercontent.com';
    private string $testClientSecret = 'test-client-secret';
    private string $testRefreshToken = 'test-refresh-token';
    private string $testAccessToken = 'test-access-token';

    public function setUp(): void
    {
        parent::setUp();
        
        // Set environment variables for testing
        $_ENV['GOOGLE_DRIVE_CLIENT_ID'] = $this->testClientId;
        $_ENV['GOOGLE_DRIVE_CLIENT_SECRET'] = $this->testClientSecret;
        $_ENV['GOOGLE_DRIVE_REFRESH_TOKEN'] = $this->testRefreshToken;
        $_ENV['GOOGLE_DRIVE_ACCESS_TOKEN'] = $this->testAccessToken;
    }

    public function tearDown(): void
    {
        // Clean up environment variables
        unset($_ENV['GOOGLE_DRIVE_CLIENT_ID']);
        unset($_ENV['GOOGLE_DRIVE_CLIENT_SECRET']);
        unset($_ENV['GOOGLE_DRIVE_REFRESH_TOKEN']);
        unset($_ENV['GOOGLE_DRIVE_ACCESS_TOKEN']);
        
        parent::tearDown();
    }

    public function testFromCredentialsMethod(): void
    {
        $drive = GoogleDriveBuilder::fromCredentials(
            clientId: $this->testClientId,
            clientSecret: $this->testClientSecret,
            refreshToken: $this->testRefreshToken,
            accessToken: $this->testAccessToken
        );

        $this->assertInstanceOf(GoogleDriveManager::class, $drive);
    }

    public function testFromCredentialsWithoutAccessToken(): void
    {
        $drive = GoogleDriveBuilder::fromCredentials(
            clientId: $this->testClientId,
            clientSecret: $this->testClientSecret,
            refreshToken: $this->testRefreshToken
        );

        $this->assertInstanceOf(GoogleDriveManager::class, $drive);
    }

    public function testFromEnvironmentMethod(): void
    {
        $drive = GoogleDriveBuilder::fromEnvironment();
        
        $this->assertInstanceOf(GoogleDriveManager::class, $drive);
    }

    public function testFromEnvironmentMissingCredentials(): void
    {
        unset($_ENV['GOOGLE_DRIVE_CLIENT_ID']);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('GOOGLE_DRIVE_CLIENT_ID and GOOGLE_DRIVE_CLIENT_SECRET environment variables are required');
        
        GoogleDriveBuilder::fromEnvironment();
    }

    public function testManualBuilderWithDirectCredentials(): void
    {
        $drive = GoogleDriveBuilder::create()
            ->withClientCredentials($this->testClientId, $this->testClientSecret, $this->testRefreshToken)
            ->withAccessToken($this->testAccessToken)
            ->withFullAccess()
            ->withAppName('Test App')
            ->build();

        $this->assertInstanceOf(GoogleDriveManager::class, $drive);
    }

    public function testBuilderWithRefreshTokenSeparately(): void
    {
        $drive = GoogleDriveBuilder::create()
            ->withClientCredentials($this->testClientId, $this->testClientSecret)
            ->withRefreshToken($this->testRefreshToken)
            ->withAccessToken($this->testAccessToken)
            ->withFullAccess()
            ->build();

        $this->assertInstanceOf(GoogleDriveManager::class, $drive);
    }

    public function testBuilderWithRedirectUri(): void
    {
        $drive = GoogleDriveBuilder::create()
            ->withClientCredentials($this->testClientId, $this->testClientSecret)
            ->withRedirectUri('http://localhost:8080/callback')
            ->withRefreshToken($this->testRefreshToken)
            ->withFullAccess()
            ->build();

        $this->assertInstanceOf(GoogleDriveManager::class, $drive);
    }

    public function testDirectCredentialsPrioritizedOverFile(): void
    {
        // This test ensures direct credentials take priority over credentials file
        $drive = GoogleDriveBuilder::create()
            ->withCredentials('/non/existent/file.json') // This would normally fail
            ->withClientCredentials($this->testClientId, $this->testClientSecret) // But this should work
            ->withRefreshToken($this->testRefreshToken)
            ->withFullAccess()
            ->build();

        $this->assertInstanceOf(GoogleDriveManager::class, $drive);
    }
}

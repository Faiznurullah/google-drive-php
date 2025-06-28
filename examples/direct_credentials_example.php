<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GoogleDrivePHP\GoogleDriveBuilder;

// Example 1: Using direct credentials
try {
    $drive = GoogleDriveBuilder::fromCredentials(
        clientId: 'your-client-id.apps.googleusercontent.com',
        clientSecret: 'your-client-secret',
        refreshToken: 'your-refresh-token',
        accessToken: 'your-access-token' // Optional, will be refreshed if expired
    );

    // List files
    $files = $drive->files();
    echo "Found " . count($files) . " files\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Example 2: Using environment variables
// Set these in your .env file or system environment:
// GOOGLE_DRIVE_CLIENT_ID=your-client-id.apps.googleusercontent.com
// GOOGLE_DRIVE_CLIENT_SECRET=your-client-secret
// GOOGLE_DRIVE_REFRESH_TOKEN=your-refresh-token
// GOOGLE_DRIVE_ACCESS_TOKEN=your-access-token (optional)

try {
    $drive = GoogleDriveBuilder::fromEnvironment();

    // Upload a file using putFile
    $success = $drive->putFile(
        path: 'uploaded-test.txt',
        file: __DIR__ . '/test.txt'
    );

    if ($success) {
        echo "File uploaded successfully\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Example 3: Manual builder configuration
try {
    $drive = GoogleDriveBuilder::create()
        ->withClientCredentials(
            clientId: 'your-client-id.apps.googleusercontent.com',
            clientSecret: 'your-client-secret'
        )
        ->withRefreshToken('your-refresh-token')
        ->withAccessToken('your-access-token') // Optional
        ->withFullAccess()
        ->withAppName('My Custom App')
        ->build();

    // Create a folder
    $success = $drive->makeDirectory('Test Folder');
    if ($success) {
        echo "Folder created successfully\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Example 4: Only with access token (no refresh capability)
try {
    $drive = GoogleDriveBuilder::create()
        ->withClientCredentials(
            clientId: 'your-client-id.apps.googleusercontent.com',
            clientSecret: 'your-client-secret'
        )
        ->withAccessToken('your-access-token')
        ->withFullAccess()
        ->build();

    // This will work as long as the access token is valid
    $files = $drive->files();
    echo "Found " . count($files) . " files\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

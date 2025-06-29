# Google Drive PHP Library

🚀 A modern, production-ready PHP library for Google Drive integration using **Static Design Pattern**. Simple, clean, and powerful - no object instantiation required!

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.1-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Google Drive API](https://img.shields.io/badge/Google%20Drive%20API-v3-red.svg)](https://developers.google.com/drive/api/v3/about-sdk)

## ✨ Why Choose This Library?

- 🎯 **Zero Learning Curve** - Intuitive API inspired by Laravel Storage
- ⚡ **No Object Creation** - Direct static method calls
- 🔄 **Auto-Initialization** - Reads credentials from `.env` automatically
- 🎭 **Facade Pattern** - Alternative clean syntax via `GDrive::method()`
- 🛡️ **Production Ready** - Comprehensive error handling & validation
- 🌐 **Framework Agnostic** - Works with any PHP project
- 📦 **Laravel Compatible** - Easy Laravel/Symfony integration
- 🚀 **Batch Operations** - Handle multiple files efficiently
- 📁 **Full CRUD** - Upload, download, delete, copy, move, rename
- 🔍 **Advanced Search** - Find files with powerful queries
- 🔗 **File Sharing** - Public links & email sharing
- 📊 **File Management** - Folders, permissions, metadata

## � Installation

Install via Composer:

```bash
composer require faiznurullah/google-drive-php
```

### Requirements

- PHP 8.1 or higher
- Composer
- Google Drive API credentials
- `vlucas/phpdotenv` (for environment variables)

```bash
# Install dependencies
composer require faiznurullah/google-drive-php vlucas/phpdotenv
```

## ⚡ Quick Start

### 1. Setup Google Drive API Credentials

Create a `.env` file in your project root:

```env
# Google Drive API Credentials
GOOGLE_DRIVE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_DRIVE_CLIENT_SECRET=GOCSPX-your-client-secret
GOOGLE_DRIVE_REFRESH_TOKEN=1//04your-refresh-token
GOOGLE_DRIVE_ACCESS_TOKEN=ya29.your-access-token
```

### 2. Load Environment Variables

```php
<?php
require_once 'vendor/autoload.php';

use Dotenv\Dotenv;
use GoogleDrivePHP\GoogleDrive;
use GoogleDrivePHP\Facades\GDrive;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
```

### 3. Start Using (Zero Configuration!)

```php
// Upload file (auto-initializes from .env)
$fileId = GoogleDrive::put('hello.txt', 'Hello World!');

// Download file
$content = GoogleDrive::get('hello.txt');

// Check if file exists
$exists = GoogleDrive::exists('hello.txt');

// List all files
$files = GoogleDrive::files();

// Delete file
GoogleDrive::delete('hello.txt');

echo "File ID: $fileId\n";
echo "Content: $content\n";
echo "Exists: " . ($exists ? 'Yes' : 'No') . "\n";
echo "Total files: " . count($files) . "\n";
```

### 4. Alternative Facade Syntax

```php
// Same functionality, cleaner syntax
$fileId = GDrive::put('hello.txt', 'Hello World!');
$content = GDrive::get('hello.txt');
$files = GDrive::files();
GDrive::delete('hello.txt');
```

## 📚 Complete API Reference

### 📁 File Upload & Download

```php
// Upload from string
$fileId = GoogleDrive::put('filename.txt', 'file content', $folderId = null);

// Upload from local file
$fileId = GoogleDrive::putFile('/path/to/local/file.pdf', 'remote-name.pdf', $folderId = null);

// Upload multiple files at once
$files = [
    'file1.txt' => 'Content 1',
    'file2.txt' => 'Content 2',
    'file3.txt' => 'Content 3'
];
$results = GoogleDrive::putMultiple($files, $folderId = null);

// Download file content
$content = GoogleDrive::get('filename.txt');

// Download by file ID
$content = GoogleDrive::getById('1ABC123_file_id');

// Download to local file
$success = GoogleDrive::downloadToFile('remote-file.pdf', '/local/path/file.pdf');
```

### 🗂️ File Management

```php
// Check if file exists
$exists = GoogleDrive::exists('filename.txt');

// Get file information
$info = GoogleDrive::getFileInfo('filename.txt');
// Returns: ['id', 'name', 'size', 'mimeType', 'modifiedTime', 'createdTime', ...]

// Copy file
$newFileId = GoogleDrive::copy('source.txt', 'destination.txt', $targetFolderId = null);

// Move file to folder
$success = GoogleDrive::move('filename.txt', $folderId);

// Rename file
$success = GoogleDrive::rename('old-name.txt', 'new-name.txt');

// Delete file
$success = GoogleDrive::delete('filename.txt');

// Delete by file ID
$success = GoogleDrive::deleteById('1ABC123_file_id');

// Delete multiple files
$results = GoogleDrive::deleteMultiple(['file1.txt', 'file2.txt', 'file3.txt']);
```

### 📂 Folder Operations

```php
// Create folder
$folderId = GoogleDrive::makeDir('My Folder', $parentFolderId = null);

// Delete folder
$success = GoogleDrive::deleteDir('Folder Name');

// List all folders
$folders = GoogleDrive::folders($parentId = null, $limit = 100);

// Find folder ID by name
$folderId = GoogleDrive::findFolderId('My Folder');
```

### 🔍 Search & Listing

```php
// List all files
$files = GoogleDrive::files($folderId = null, $limit = 100);

// List files in specific folder
$files = GoogleDrive::files('1ABC123_folder_id', 50);

// Search files by name
$results = GoogleDrive::search('vacation photos', $limit = 100);

// List all contents (files + folders)
$contents = GoogleDrive::all($folderId = null, $recursive = false);

// Recursive listing
$allContents = GoogleDrive::all('1ABC123_folder_id', true);
```

### 🔗 Sharing & Permissions

```php
// Share with email (reader access)
$success = GoogleDrive::shareWithEmail('filename.txt', 'user@example.com', 'reader');

// Share with write access
$success = GoogleDrive::shareWithEmail('filename.txt', 'user@example.com', 'writer');

// Make file public
$publicLink = GoogleDrive::makePublic('filename.txt');
// Returns: "https://drive.google.com/file/d/FILE_ID/view"

// Get shareable link
$link = GoogleDrive::getShareableLink('filename.txt');
```

### 🎯 Batch Operations

```php
// Backup entire folder to local directory
$results = GoogleDrive::backupFolder('1ABC123_folder_id', './backup-folder');

// Process backup results
foreach ($results as $filename => $result) {
    if ($result['success']) {
        echo "✅ Backed up: {$filename} to {$result['localPath']}\n";
    } else {
        echo "❌ Failed: {$filename} - {$result['error']}\n";
    }
}
```

## 🔧 Advanced Configuration

### Manual Initialization

If you prefer not to use environment variables:

```php
// Manual configuration
GoogleDrive::init([
    'client_id' => 'your-client-id.apps.googleusercontent.com',
    'client_secret' => 'GOCSPX-your-client-secret',
    'refresh_token' => '1//04your-refresh-token',
    'access_token' => 'ya29.your-access-token' // optional
]);
```

### Initialize from Credentials File

```php
// Using JSON credentials file
GoogleDrive::initFromCredentialsFile(
    'path/to/credentials.json', 
    'your-refresh-token',
    'your-access-token' // optional
);
```

### Reset and Reinitialize

```php
// Reset static state (useful for testing)
GoogleDrive::reset();

// Initialize with new credentials
GoogleDrive::init($newConfig);
```

### Get Client Instances (Advanced Usage)

```php
// Get Google Client instance for advanced operations
$client = GoogleDrive::getClient();

// Get Google Drive Service instance
$service = GoogleDrive::getService();
```

## 🎨 Real-World Examples

### File Upload System

```php
<?php
require_once 'vendor/autoload.php';

use Dotenv\Dotenv;
use GoogleDrivePHP\GoogleDrive;

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Handle file upload
if ($_FILES['upload']) {
    $uploadedFile = $_FILES['upload'];
    
    // Upload to Google Drive
    $fileId = GoogleDrive::putFile(
        $uploadedFile['tmp_name'], 
        $uploadedFile['name']
    );
    
    // Get public link
    $publicLink = GoogleDrive::makePublic($uploadedFile['name']);
    
    echo "File uploaded successfully!\n";
    echo "File ID: $fileId\n";
    echo "Public Link: $publicLink\n";
}
```

### Project Backup System

```php
// Create backup structure
$backupFolder = GoogleDrive::makeDir('Project Backup ' . date('Y-m-d'));

// Upload project files
$projectFiles = [
    'config.json' => file_get_contents('config.json'),
    'database.sql' => file_get_contents('backup.sql'),
    'app.log' => file_get_contents('logs/app.log')
];

$results = GoogleDrive::putMultiple($projectFiles, $backupFolder);

foreach ($results as $filename => $result) {
    if ($result['success']) {
        echo "✅ Backed up: $filename\n";
    } else {
        echo "❌ Failed: $filename - {$result['error']}\n";
    }
}
```

### File Management System

```php
// Create organized folder structure
$mainFolder = GoogleDrive::makeDir('Document Management');
$docsFolder = GoogleDrive::makeDir('Documents', $mainFolder);
$imagesFolder = GoogleDrive::makeDir('Images', $mainFolder);
$archiveFolder = GoogleDrive::makeDir('Archive', $mainFolder);

// Upload and organize files
GoogleDrive::putFile('contract.pdf', null, $docsFolder);
GoogleDrive::putFile('logo.png', null, $imagesFolder);

// Move old files to archive
$oldFiles = GoogleDrive::search('2023');
foreach ($oldFiles as $file) {
    GoogleDrive::move($file['name'], $archiveFolder);
}

// List organized structure
$allContents = GoogleDrive::all($mainFolder, true);
foreach ($allContents as $item) {
    echo ($item['type'] === 'folder' ? '📁' : '📄') . " {$item['name']}\n";
}
```

### Batch File Processing

```php
// Process multiple uploads
$uploadDirectory = './uploads';
$files = glob($uploadDirectory . '/*');

foreach ($files as $filePath) {
    $filename = basename($filePath);
    
    try {
        // Upload file
        $fileId = GoogleDrive::putFile($filePath, $filename);
        
        // Get file info
        $info = GoogleDrive::getFileInfo($filename);
        
        echo "✅ Uploaded: $filename (Size: {$info['size']} bytes)\n";
        
        // Clean up local file
        unlink($filePath);
        
    } catch (Exception $e) {
        echo "❌ Failed to upload $filename: {$e->getMessage()}\n";
    }
}
```

### File Synchronization

```php
// Sync local folder with Google Drive
function syncFolder($localPath, $driveFolder = null) {
    // Get local files
    $localFiles = glob($localPath . '/*');
    
    // Get drive files
    $driveFiles = GoogleDrive::files($driveFolder);
    $driveFileNames = array_column($driveFiles, 'name');
    
    foreach ($localFiles as $localFile) {
        $filename = basename($localFile);
        
        if (!in_array($filename, $driveFileNames)) {
            // Upload new file
            $fileId = GoogleDrive::putFile($localFile, $filename, $driveFolder);
            echo "📤 Uploaded: $filename\n";
        } else {
            // Check if local file is newer
            $localModified = filemtime($localFile);
            $driveFile = array_filter($driveFiles, fn($f) => $f['name'] === $filename)[0];
            $driveModified = strtotime($driveFile['modifiedTime']);
            
            if ($localModified > $driveModified) {
                // Update existing file
                GoogleDrive::delete($filename);
                $fileId = GoogleDrive::putFile($localFile, $filename, $driveFolder);
                echo "🔄 Updated: $filename\n";
            }
        }
    }
}

// Usage
syncFolder('./documents', GoogleDrive::findFolderId('My Documents'));
```

## 🔗 Framework Integration

### 🅻 Laravel Integration

#### Service Provider

```php
<?php
// app/Providers/GoogleDriveServiceProvider.php

namespace App\Providers;

use GoogleDrivePHP\GoogleDrive;
use Illuminate\Support\ServiceProvider;

class GoogleDriveServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('gdrive', function ($app) {
            return GoogleDrive::class;
        });
    }
    
    public function boot()
    {
        // Initialize from Laravel env
        GoogleDrive::init([
            'client_id' => env('GOOGLE_DRIVE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
            'refresh_token' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
            'access_token' => env('GOOGLE_DRIVE_ACCESS_TOKEN'),
        ]);
    }
}
```

#### Controller Example

```php
<?php
// app/Http/Controllers/DriveController.php

namespace App\Http\Controllers;

use GoogleDrivePHP\Facades\GDrive;
use Illuminate\Http\Request;

class DriveController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240' // 10MB max
        ]);
        
        $file = $request->file('file');
        
        try {
            $fileId = GDrive::putFile(
                $file->getPathname(),
                $file->getClientOriginalName()
            );
            
            return response()->json([
                'success' => true,
                'file_id' => $fileId,
                'message' => 'File uploaded successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function download($filename)
    {
        try {
            $content = GDrive::get($filename);
            
            if (!$content) {
                return abort(404, 'File not found');
            }
            
            return response($content)
                ->header('Content-Type', 'application/octet-stream')
                ->header('Content-Disposition', "attachment; filename=\"$filename\"");
                
        } catch (\Exception $e) {
            return abort(500, 'Download failed: ' . $e->getMessage());
        }
    }
    
    public function list(Request $request)
    {
        $folderId = $request->get('folder_id');
        $limit = $request->get('limit', 50);
        
        $files = GDrive::files($folderId, $limit);
        
        return response()->json([
            'files' => $files,
            'total' => count($files)
        ]);
    }
}
```

#### Artisan Command

```php
<?php
// app/Console/Commands/BackupToGoogleDrive.php

namespace App\Console\Commands;

use GoogleDrivePHP\GoogleDrive;
use Illuminate\Console\Command;

class BackupToGoogleDrive extends Command
{
    protected $signature = 'backup:google-drive {--folder=}';
    protected $description = 'Backup application to Google Drive';
    
    public function handle()
    {
        $folderName = $this->option('folder') ?: 'Laravel Backup ' . now()->format('Y-m-d H:i');
        
        $this->info("Creating backup folder: $folderName");
        $folderId = GoogleDrive::makeDir($folderName);
        
        // Backup database
        $this->info('Backing up database...');
        $dbBackup = $this->createDatabaseBackup();
        GoogleDrive::put('database.sql', $dbBackup, $folderId);
        
        // Backup storage
        $this->info('Backing up storage files...');
        $this->backupDirectory(storage_path(), $folderId, 'storage');
        
        $publicLink = GoogleDrive::getShareableLink($folderName);
        $this->info("Backup completed! Folder: $publicLink");
    }
    
    private function backupDirectory($path, $parentFolderId, $folderName)
    {
        $backupFolderId = GoogleDrive::makeDir($folderName, $parentFolderId);
        
        $files = glob($path . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                GoogleDrive::putFile($file, basename($file), $backupFolderId);
            }
        }
    }
}
```

### 🎵 Symfony Integration

```php
<?php
// src/Service/GoogleDriveService.php

namespace App\Service;

use GoogleDrivePHP\GoogleDrive;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GoogleDriveService
{
    public function __construct(ParameterBagInterface $params)
    {
        GoogleDrive::init([
            'client_id' => $params->get('google_drive_client_id'),
            'client_secret' => $params->get('google_drive_client_secret'),
            'refresh_token' => $params->get('google_drive_refresh_token'),
        ]);
    }
    
    public function uploadFile(string $filename, string $content): string
    {
        return GoogleDrive::put($filename, $content);
    }
    
    public function downloadFile(string $filename): ?string
    {
        return GoogleDrive::get($filename);
    }
}
```

### 🔧 Plain PHP Integration

```php
<?php
// GoogleDriveManager.php

class GoogleDriveManager
{
    private static $initialized = false;
    
    public static function init()
    {
        if (!self::$initialized) {
            // Load from config file
            $config = include 'config/google-drive.php';
            
            \GoogleDrivePHP\GoogleDrive::init($config);
            self::$initialized = true;
        }
    }
    
    public static function upload($filename, $content)
    {
        self::init();
        return \GoogleDrivePHP\GoogleDrive::put($filename, $content);
    }
    
    public static function download($filename)
    {
        self::init();
        return \GoogleDrivePHP\GoogleDrive::get($filename);
    }
}

// Usage
$fileId = GoogleDriveManager::upload('test.txt', 'Hello World');
$content = GoogleDriveManager::download('test.txt');
```

## 🔐 Google Drive API Setup

### Step 1: Google Cloud Console Setup

1. **Create Project**
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Create new project or select existing one
   - Note your Project ID

2. **Enable Google Drive API**
   - Navigate to **APIs & Services** > **Library**
   - Search for "Google Drive API"
   - Click **Enable**

3. **Create Credentials**
   - Go to **APIs & Services** > **Credentials**
   - Click **Create Credentials** > **OAuth client ID**
   - Choose **Desktop Application**
   - Name it (e.g., "Google Drive PHP Client")
   - Download the JSON file

### Step 2: Get Refresh Token

Create a script to get your refresh token:

```php
<?php
// get_token.php

require_once 'vendor/autoload.php';

$client = new Google\Client();
$client->setClientId('your-client-id.apps.googleusercontent.com');
$client->setClientSecret('GOCSPX-your-client-secret');
$client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
$client->addScope(Google\Service\Drive::DRIVE);
$client->setAccessType('offline');
$client->setPrompt('consent');

// Get authorization URL
$authUrl = $client->createAuthUrl();
echo "Open this URL in your browser:\n$authUrl\n\n";
echo "Enter the authorization code: ";
$authCode = trim(fgets(STDIN));

// Exchange code for token
$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

echo "\nYour refresh token:\n";
echo $accessToken['refresh_token'] . "\n";
echo "\nAdd this to your .env file:\n";
echo "GOOGLE_DRIVE_REFRESH_TOKEN=" . $accessToken['refresh_token'] . "\n";
```

Run the script:
```bash
php get_token.php
```

### Step 3: Setup Environment Variables

Create `.env` file in your project root:

```env
# Google Drive API Credentials
GOOGLE_DRIVE_CLIENT_ID=535863022892-your-client-id.apps.googleusercontent.com
GOOGLE_DRIVE_CLIENT_SECRET=GOCSPX-your-client-secret
GOOGLE_DRIVE_REFRESH_TOKEN=1//04your-refresh-token

# Optional - will be auto-refreshed
GOOGLE_DRIVE_ACCESS_TOKEN=ya29.your-access-token

# Optional - default folder to work in
GOOGLE_DRIVE_FOLDER_ID=1ABC123your-folder-id
```

### Step 4: Verify Setup

Test your configuration:

```php
<?php
require_once 'vendor/autoload.php';

use Dotenv\Dotenv;
use GoogleDrivePHP\GoogleDrive;

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Test connection
try {
    GoogleDrive::init();
    
    // Test basic operation
    $files = GoogleDrive::files(null, 1);
    echo "✅ Connection successful! Found " . count($files) . " files.\n";
    
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
}
```

### 🔧 Troubleshooting

**Common Issues:**

1. **"Invalid refresh token"**
   - Regenerate refresh token using the script above
   - Make sure you clicked "Allow" during authorization

2. **"Access denied"**
   - Check if Google Drive API is enabled
   - Verify your OAuth credentials

3. **"Token has been expired or revoked"**
   - Run the token generation script again
   - Update your `.env` file with new tokens

4. **"File not found" after upload**
   - Check if file name sanitization is causing issues
   - Use file ID instead of filename for operations

For detailed setup guide, check our [CREDENTIALS.md](CREDENTIALS.md) file.

## 🏗️ Architecture & Design Patterns

This library implements several design patterns for clean, maintainable code:

### Static Helper Pattern
- **No Object Instantiation**: Direct class method calls
- **Auto-Initialization**: Automatically configures from environment
- **Global State Management**: Maintains single client instance
- **Lazy Loading**: Only initializes when first method is called

```php
// No need for: $drive = new GoogleDrive()
GoogleDrive::put('file.txt', 'content'); // Direct usage
```

### Facade Pattern
- **Simplified Interface**: Clean, consistent API
- **Alternative Access**: Same functionality, different syntax
- **Proxy Behavior**: Forwards calls to underlying implementation

```php
// Both are equivalent:
GoogleDrive::put('file.txt', 'content');
GDrive::put('file.txt', 'content');
```

### Factory Pattern
- **Object Creation**: Handles complex client instantiation
- **Configuration Management**: Multiple initialization methods
- **Credential Abstraction**: Supports various auth methods

```php
// Factory handles complexity internally
$instances = GoogleDriveFactory::fromEnv();
$instances = GoogleDriveFactory::fromCredentials($id, $secret, $token);
```

### Strategy Pattern
- **File Operations**: Consistent interface for different operations
- **Error Handling**: Unified exception management
- **Batch Processing**: Same pattern for single/multiple operations

### Interface Segregation
- **Clean Contracts**: `GoogleDriveInterface` defines public API
- **Implementation Freedom**: Multiple implementations possible
- **Testing Support**: Easy mocking and testing

### Class Structure

```
GoogleDrivePHP/
├── GoogleDrive.php (Main static class)
├── Contracts/
│   └── GoogleDriveInterface.php
├── Facades/
│   └── GDrive.php
└── Support/
    ├── GoogleDriveFactory.php
    └── FileHelper.php
```

### Design Benefits

1. **Developer Experience**: Intuitive, Laravel-like API
2. **Maintainability**: Clear separation of concerns
3. **Testability**: Easy to mock and unit test
4. **Extensibility**: Simple to add new features
5. **Performance**: Efficient resource management
6. **Error Handling**: Comprehensive exception management

This design makes the library both powerful for advanced users and simple for beginners.

## 📁 Project Structure

```
google-drive-php/
├── 📁 src/                          # Source code
│   ├── GoogleDrive.php              # Main static class
│   ├── 📁 Contracts/
│   │   └── GoogleDriveInterface.php # Interface contract
│   ├── 📁 Facades/
│   │   └── GDrive.php               # Facade implementation
│   └── 📁 Support/
│       ├── GoogleDriveFactory.php   # Factory for client creation
│       └── FileHelper.php           # File utility helpers
├── 📁 examples/                     # Usage examples
│   ├── static_pattern_demo.php      # Complete demo
│   └── README.md                    # Examples documentation
├── 📁 tests/                        # Unit tests
│   ├── DirectCredentialsTest.php
│   └── DriveFileTest.php
├── 📄 composer.json                 # Dependencies
├── 📄 .env.example                  # Environment template
├── 📄 phpunit.xml                   # PHPUnit configuration
├── 📄 phpstan.neon                  # Static analysis config
├── 📄 phpcs.xml                     # Code style config
├── 📄 README.md                     # This file
└── 📄 LICENSE                       # MIT License
```

## 🔄 Version History

### v2.0.0 (Current)
- ✨ **New**: Static design pattern implementation
- ✨ **New**: Facade pattern support (`GDrive::method()`)
- ✨ **New**: Auto-initialization from environment
- ✨ **New**: Batch operations (`putMultiple`, `deleteMultiple`)
- ✨ **New**: Advanced search and listing
- ✨ **New**: File sharing and permissions
- ✨ **New**: Comprehensive error handling
- 🔧 **Improved**: Better file sanitization
- 🔧 **Improved**: Enhanced documentation

### v1.0.0
- 🎉 Initial release
- ✅ Basic file operations
- ✅ Google Drive API integration

## 📋 TODO / Roadmap

- [ ] **File Streaming**: Support for large file uploads/downloads
- [ ] **Progress Callbacks**: Upload/download progress tracking
- [ ] **Cache Layer**: File metadata caching for better performance
- [ ] **File Versioning**: Handle Google Drive file versions
- [ ] **Advanced Search**: More Google Drive search operators
- [ ] **Webhook Support**: Google Drive push notifications
- [ ] **Team Drives**: Support for Google Workspace shared drives
- [ ] **File Comments**: Add/read file comments
- [ ] **Thumbnail Generation**: Generate and retrieve thumbnails
- [ ] **File Properties**: Custom file properties support

## 🧪 Testing & Examples

### Running the Demo

The library includes comprehensive examples to test all features:

```bash
# Run the complete demo
cd examples
php static_pattern_demo.php
```

**Demo Output:**
```
Loading environment variables from .env file...
✓ Environment variables loaded successfully

✓ All required credentials are present
  CLIENT_ID: 535863022892-ac...
  CLIENT_SECRET: GOCSPX-SqTTg...
  REFRESH_TOKEN: 1//04C64Nm_dfJW...

=== Google Drive PHP - Static Pattern Demo ===

1. BASIC FILE OPERATIONS
------------------------
• Upload file from string content...
  → File uploaded with ID: 1OHXmPNKvcxRhWnin2issas2svna6LcMS
• Download file...
  → Downloaded content: Hello World from GoogleDrive static method!
• Check if file exists...
  → File exists: Yes

2. FOLDER OPERATIONS
--------------------
• Create folder...
  → Folder created with ID: 1zMG0CtKxX1SXKJVhoI_BQGfVjQiudrMM

🎉 === ALL OPERATIONS COMPLETED SUCCESSFULLY! ===
```

### Available Examples

1. **`examples/static_pattern_demo.php`** - Complete feature demonstration
2. **`simple_static.php`** - Basic usage example
3. **`static_example.php`** - Advanced patterns

### Unit Testing

```bash
# Install PHPUnit
composer require --dev phpunit/phpunit

# Run tests
composer test

# Run specific test
./vendor/bin/phpunit tests/DirectCredentialsTest.php
```

### Manual Testing

Create your own test script:

```php
<?php
require_once 'vendor/autoload.php';

use Dotenv\Dotenv;
use GoogleDrivePHP\GoogleDrive;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Test file operations
echo "Testing file operations...\n";

// Upload
$fileId = GoogleDrive::put('test.txt', 'Test content');
echo "✅ Upload: $fileId\n";

// Download
$content = GoogleDrive::get('test.txt');
echo "✅ Download: $content\n";

// List files
$files = GoogleDrive::files(null, 5);
echo "✅ List: " . count($files) . " files\n";

// Cleanup
GoogleDrive::delete('test.txt');
echo "✅ Cleanup completed\n";
```

## 🤝 Contributing

We welcome contributions! Here's how you can help:

### Development Setup

```bash
# Clone repository
git clone https://github.com/faiznurullah/google-drive-php.git
cd google-drive-php

# Install dependencies
composer install

# Copy environment template
cp .env.example .env

# Edit .env with your credentials
nano .env

# Run tests
composer test
```

### Contribution Guidelines

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/amazing-feature`)
3. **Follow** PSR-12 coding standards
4. **Add** tests for new features
5. **Update** documentation
6. **Commit** changes (`git commit -m 'Add amazing feature'`)
7. **Push** to branch (`git push origin feature/amazing-feature`)
8. **Open** a Pull Request

### Code Standards

```bash
# Code style check
composer cs-check

# Fix code style
composer cs-fix

# Static analysis
composer analyze

# Run all checks
composer check
```

### Adding New Features

When adding new methods to `GoogleDrive` class:

1. Add method to `GoogleDriveInterface`
2. Implement in `GoogleDrive` class
3. Add corresponding method to `GDrive` facade
4. Write unit tests
5. Update documentation
6. Add to examples

### Bug Reports

Please include:
- PHP version
- Library version
- Error messages
- Steps to reproduce
- Expected vs actual behavior

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

### MIT License Summary

- ✅ **Commercial use**
- ✅ **Modification**
- ✅ **Distribution**
- ✅ **Private use**
- ❌ **Liability**
- ❌ **Warranty**

## 🙏 Acknowledgments

This library was inspired by and built upon excellent work from:

- **[yaza-putu/laravel-google-drive-storage](https://github.com/yaza-putu/laravel-google-drive-storage)** - Original inspiration for static pattern
- **[Google API PHP Client](https://github.com/googleapis/google-api-php-client)** - Official Google APIs client
- **[Laravel Framework](https://laravel.com/)** - API design inspiration
- **[PHP Community](https://www.php.net/)** - For their continuous support

Special thanks to all contributors who help improve this library!

## 📞 Support & Community

### � Issues & Bug Reports
[GitHub Issues](https://github.com/faiznurullah/google-drive-php/issues)

### � Discussions
[GitHub Discussions](https://github.com/faiznurullah/google-drive-php/discussions)

### 📧 Contact
- **Email**: faizn103a@gmail.com
- **GitHub**: [@faiznurullah](https://github.com/faiznurullah)

### � Documentation
- **API Reference**: [README_STATIC.md](README_STATIC.md)
- **Credentials Guide**: [CREDENTIALS.md](CREDENTIALS.md)
- **Examples**: [examples/README.md](examples/README.md)

### 🌟 Show Your Support

If this library helps you, please:
- ⭐ **Star** the repository
- 🐛 **Report** issues
- 🔀 **Contribute** improvements
- 📢 **Share** with others
- ☕ **Buy me a coffee** (optional)

---

<div align="center">

**Made with ❤️ for the PHP community**

[⭐ Star on GitHub](https://github.com/faiznurullah/google-drive-php) • 
[📚 Documentation](README_STATIC.md) • 
[🐛 Report Issues](https://github.com/faiznurullah/google-drive-php/issues) • 
[💬 Discussions](https://github.com/faiznurullah/google-drive-php/discussions)

</div>

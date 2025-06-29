# Google Drive PHP Library - Static Design Pattern

A simple and powerful PHP library for Google Drive API integration using **Static Design Pattern**. Inspired by clean and intuitive libraries, this implementation provides static methods for all operations without the need for object instantiation.

## 🎯 Design Pattern Features

- ✅ **Static Methods** - No object instantiation required
- ✅ **Auto-Initialization** - Automatically initialize from environment variables
- ✅ **Interface Contract** - Clean interface for all operations
- ✅ **Factory Pattern** - Smart factory for credentials handling
- ✅ **Facade Pattern** - Alternative access through facade
- ✅ **Helper Utilities** - File helper for common operations
- ✅ **Clean Architecture** - Separation of concerns

## 🚀 Installation

```bash
composer require faiznurullah/google-drive-php
```

## ⚡ Quick Start

### Simple Usage (Auto-Initialize)

```php
<?php
require_once 'vendor/autoload.php';

use GoogleDrivePHP\GoogleDrive;

// Upload file (auto-initialize from environment)
$fileId = GoogleDrive::put('hello.txt', 'Hello World!');

// Download file
$content = GoogleDrive::get('hello.txt');

// List files
$files = GoogleDrive::files();

// Delete file
GoogleDrive::delete('hello.txt');
```

### Using Facade Pattern

```php
<?php
use GoogleDrivePHP\Facades\GDrive;

// Same operations using facade
$fileId = GDrive::put('hello.txt', 'Hello World!');
$content = GDrive::get('hello.txt');
$files = GDrive::files();
GDrive::delete('hello.txt');
```

## 🔧 Setup

### 1. Environment Variables

Create `.env` file:
```env
GOOGLE_DRIVE_CLIENT_ID=your_client_id
GOOGLE_DRIVE_CLIENT_SECRET=your_client_secret
GOOGLE_DRIVE_REFRESH_TOKEN=your_refresh_token
```

### 2. Manual Initialization (Optional)

```php
// Initialize with config array
GoogleDrive::init([
    'client_id' => 'your-client-id',
    'client_secret' => 'your-client-secret', 
    'refresh_token' => 'your-refresh-token',
    'access_token' => 'your-access-token' // optional
]);

// Initialize from credentials file
GoogleDrive::initFromCredentialsFile('credentials.json', $refreshToken);
```

## 📖 Complete API Reference

### File Operations

```php
// Upload operations
$fileId = GoogleDrive::put('filename.txt', 'content');
$fileId = GoogleDrive::putFile('/local/path/file.pdf', 'remote-name.pdf');
$results = GoogleDrive::putMultiple([
    'file1.txt' => 'content1',
    'file2.txt' => 'content2'
]);

// Download operations
$content = GoogleDrive::get('filename.txt');
$content = GoogleDrive::getById($fileId);
$success = GoogleDrive::downloadToFile('remote.pdf', '/local/file.pdf');

// File management
$newFileId = GoogleDrive::copy('source.txt', 'destination.txt');
GoogleDrive::move('filename.txt', $folderId);
GoogleDrive::rename('old.txt', 'new.txt');
GoogleDrive::delete('filename.txt');
GoogleDrive::deleteById($fileId);

// File information
$exists = GoogleDrive::exists('filename.txt');
$info = GoogleDrive::getFileInfo('filename.txt');
```

### Folder Operations

```php
// Folder management
$folderId = GoogleDrive::makeDir('My Folder');
$folderId = GoogleDrive::makeDir('Subfolder', $parentFolderId);
GoogleDrive::deleteDir('Folder Name');
$folderId = GoogleDrive::findFolderId('Folder Name');

// Listing
$files = GoogleDrive::files();           // All files
$files = GoogleDrive::files($folderId);  // Files in folder
$folders = GoogleDrive::folders();       // All folders
$folders = GoogleDrive::folders($folderId); // Subfolders
$all = GoogleDrive::all();              // Files + folders
$all = GoogleDrive::all($folderId, true); // Recursive
```

### Search & Information

```php
// Search operations
$results = GoogleDrive::search('vacation photos');
$results = GoogleDrive::search('*.pdf', 50); // Limit results

// File information
$info = GoogleDrive::getFileInfo('document.pdf');
echo "Size: {$info['size']}, Modified: {$info['modifiedTime']}";
```

### Sharing & Permissions

```php
// Sharing operations
GoogleDrive::shareWithEmail('file.pdf', 'user@example.com', 'reader');
GoogleDrive::shareWithEmail('file.pdf', 'user@example.com', 'writer');

$publicLink = GoogleDrive::makePublic('document.pdf');
$shareLink = GoogleDrive::getShareableLink('document.pdf');
```

### Batch Operations

```php
// Batch uploads
$uploadResults = GoogleDrive::putMultiple([
    'doc1.txt' => 'content 1',
    'doc2.txt' => 'content 2'
], $folderId);

// Batch deletions
$deleteResults = GoogleDrive::deleteMultiple([
    'file1.txt', 'file2.txt', 'file3.txt'
]);

// Folder backup
$backupResults = GoogleDrive::backupFolder($folderId, './backup');
```

## 🏗️ Architecture Overview

### Design Patterns Used

#### 1. Static Methods Pattern
```php
// No object instantiation needed
GoogleDrive::put('file.txt', 'content');

// Instead of:
// $drive = new GoogleDrive($credentials);
// $drive->put('file.txt', 'content');
```

#### 2. Factory Pattern
```php
// Smart factory handles credential creation
class GoogleDriveFactory 
{
    public static function fromEnv(): array
    public static function fromCredentialsFile(string $path, string $token): array
    public static function fromCredentials(string $id, string $secret, string $token): array
}
```

#### 3. Interface Pattern
```php
// Contract ensures all required methods
interface GoogleDriveInterface 
{
    public static function put(string $filename, string $content): string;
    public static function get(string $filename): ?string;
    // ... all methods
}
```

#### 4. Facade Pattern
```php
// Alternative clean access
use GoogleDrivePHP\Facades\GDrive;

GDrive::put('file.txt', 'content');
GDrive::get('file.txt');
```

#### 5. Helper Pattern
```php
// Utility helpers
FileHelper::sanitizeFilename($name);
FileHelper::getMimeType($path);
FileHelper::formatFileSize($bytes);
```

### Class Structure

```
src/
├── GoogleDrive.php              # Main static class
├── Contracts/
│   └── GoogleDriveInterface.php # Interface contract
├── Support/
│   ├── GoogleDriveFactory.php   # Factory for credentials
│   └── FileHelper.php           # File utilities
└── Facades/
    └── GDrive.php               # Facade for alternative access
```

## 🔄 Laravel Integration

### Using in Controllers

```php
<?php

namespace App\Http\Controllers;

use GoogleDrivePHP\GoogleDrive;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileId = GoogleDrive::putFile(
                $file->getPathname(),
                $file->getClientOriginalName()
            );
            
            return response()->json(['fileId' => $fileId]);
        }
        
        return response()->json(['error' => 'No file'], 400);
    }
    
    public function download($filename)
    {
        $content = GoogleDrive::get($filename);
        
        if (!$content) {
            abort(404);
        }
        
        return response($content)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }
    
    public function list()
    {
        $files = GoogleDrive::files();
        return response()->json($files);
    }
}
```

### Using as Helper

```php
// In any Laravel component
$fileId = GoogleDrive::put('user-upload.pdf', $pdfContent);

// In Blade template
@if(GoogleDrive::exists('user-avatar.jpg'))
    <img src="{{ GoogleDrive::getShareableLink('user-avatar.jpg') }}">
@endif
```

## 🧪 Testing

```php
// Reset static state untuk testing
GoogleDrive::reset();

// Initialize untuk test
GoogleDrive::init($testConfig);

// Run tests
$this->assertTrue(GoogleDrive::exists('test-file.txt'));
```

## 📚 Examples

### Basic Operations
```php
// See: simple_static.php
GoogleDrive::put('test.txt', 'Hello World!');
$content = GoogleDrive::get('test.txt');
GoogleDrive::delete('test.txt');
```

### Complete Example
```php
// See: static_example.php
// Comprehensive example with all features
```

### Laravel Example
```php
// See: examples/laravel_controller.php
// Complete Laravel integration example
```

## 💡 Why This Design Pattern?

### Advantages

1. **Simplicity** - No object instantiation required
2. **Clean Syntax** - Similar to popular libraries like Laravel's Storage
3. **Auto-Configuration** - Automatically loads from environment
4. **Framework Agnostic** - Works with any PHP framework
5. **Memory Efficient** - Single static instance
6. **Easy Testing** - Simple reset and configuration

### Comparison

```php
// Old Pattern (Object-Oriented)
$drive = new GoogleDrive($clientId, $clientSecret, $refreshToken);
$fileId = $drive->put('file.txt', 'content');

// New Pattern (Static)
$fileId = GoogleDrive::put('file.txt', 'content');

// Even Cleaner (Facade)
$fileId = GDrive::put('file.txt', 'content');
```

## 🔧 Advanced Usage

### Custom Configuration

```php
// Multiple configurations
GoogleDrive::init($userConfig);
$userFileId = GoogleDrive::put('user-file.txt', $content);

GoogleDrive::reset();
GoogleDrive::init($adminConfig);
$adminFileId = GoogleDrive::put('admin-file.txt', $content);
```

### Error Handling

```php
try {
    $fileId = GoogleDrive::put('file.txt', $content);
} catch (InvalidArgumentException $e) {
    // Configuration or parameter errors
} catch (RuntimeException $e) {
    // Google Drive API errors
} catch (Exception $e) {
    // Other errors
}
```

### Direct Client Access

```php
// For advanced operations
$client = GoogleDrive::getClient();
$service = GoogleDrive::getService();

// Custom operations not covered by the library
$customResponse = $service->files->export($fileId, 'application/pdf');
```

## 📋 Requirements

- PHP 8.1+
- google/apiclient ^2.18
- Valid Google Drive API credentials

## 🤝 Contributing

This library follows clean architecture principles and design patterns. When contributing:

1. Maintain static method pattern
2. Follow interface contracts
3. Use factory pattern for object creation
4. Keep facade in sync with main class
5. Add helper methods to FileHelper when appropriate

## 📄 License

MIT License - see LICENSE file for details.

## 🎉 Credits

Design pattern inspired by:
- Laravel's elegant static facades
- [yaza-putu/laravel-google-drive-storage](https://github.com/yaza-putu/laravel-google-drive-storage)
- Modern PHP static helper patterns

---

**✨ Simple. Clean. Powerful. - Google Drive operations made easy with static design pattern!**

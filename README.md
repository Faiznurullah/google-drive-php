# Google Drive PHP Library

A modern PHP library for Google Drive integration using **Static Design Pattern**. Simple, clean, and powerful - no object instantiation required.

## 🎯 Key Features

- ✅ **Static Methods** - Direct usage without object creation
- ✅ **Auto-Initialization** - Reads credentials from environment
- ✅ **Clean API** - Intuitive method names inspired by Laravel Storage
- ✅ **Facade Pattern** - Alternative access through GDrive facade  
- ✅ **Production Ready** - Comprehensive error handling
- ✅ **Framework Agnostic** - Works with any PHP project
- ✅ **Laravel Compatible** - Easy Laravel integration
- ✅ **Batch Operations** - Handle multiple files efficiently

## 🚀 Installation

```bash
composer require faiznurullah/google-drive-php
```

## ⚡ Quick Start

### 1. Environment Setup
```bash
# .env file
GOOGLE_DRIVE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_DRIVE_CLIENT_SECRET=your-client-secret
GOOGLE_DRIVE_REFRESH_TOKEN=your-refresh-token
GOOGLE_DRIVE_ACCESS_TOKEN=your-access-token  # optional
```

### 2. Basic Usage

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

### 3. Using Facade (Alternative)

```php
use GoogleDrivePHP\Facades\GDrive;

$fileId = GDrive::put('hello.txt', 'Hello World!');
$content = GDrive::get('hello.txt');
```

## 📖 Complete API Reference

### File Operations

```php
// Upload & Download
GoogleDrive::put($filename, $content, $folderId = null)
GoogleDrive::putFile($localPath, $filename = null, $folderId = null)
GoogleDrive::get($filename)
GoogleDrive::getById($fileId)
GoogleDrive::downloadToFile($filename, $localPath)

// File Management
GoogleDrive::delete($filename)
GoogleDrive::deleteById($fileId)
GoogleDrive::copy($source, $destination, $folderId = null)
GoogleDrive::move($filename, $folderId)
GoogleDrive::rename($oldName, $newName)

// File Info
GoogleDrive::exists($filename)
GoogleDrive::getFileInfo($filename)
```

### Folder Operations

```php
GoogleDrive::makeDir($folderName, $parentId = null)
GoogleDrive::deleteDir($folderName)
GoogleDrive::folders($parentId = null, $limit = 100)
GoogleDrive::findFolderId($folderName)
```

### Search & Listing

```php
GoogleDrive::files($folderId = null, $limit = 100)
GoogleDrive::search($query, $limit = 100)
GoogleDrive::all($folderId = null, $recursive = false)
```

### Sharing

```php
GoogleDrive::shareWithEmail($filename, $email, $role = 'reader')
GoogleDrive::makePublic($filename)
GoogleDrive::getShareableLink($filename)
```

### Batch Operations

```php
GoogleDrive::putMultiple($files, $folderId = null)
GoogleDrive::deleteMultiple($filenames)
GoogleDrive::backupFolder($folderId = null, $localPath = './backup')
```

## 🔧 Advanced Configuration

### Manual Initialization
```php
// If you don't want to use environment variables
GoogleDrive::init([
    'client_id' => 'your-client-id',
    'client_secret' => 'your-client-secret',
    'refresh_token' => 'your-refresh-token',
    'access_token' => 'your-access-token'  // optional
]);
```

### From Credentials File
```php
GoogleDrive::initFromCredentialsFile('credentials.json', $refreshToken, $accessToken);
```

## 💡 Usage Examples
$fileId = $drive->putFile('/path/to/local/file.pdf');

// Upload to specific folder
$fileId = $drive->put('file.txt', $content, $folderId);

// Upload with custom filename and MIME type
$fileId = $drive->putFile('/path/file.jpg', 'custom-name.jpg', $folderId, 'image/jpeg');

// Batch upload
$files = [
    'file1.txt' => 'Content 1',
    'file2.txt' => 'Content 2'
];
$results = $drive->putMultiple($files, $folderId);
```

### File Download

```php
// Download as string
$content = $drive->get('filename.txt');

// Download by file ID
$content = $drive->getById($fileId);

// Download to local file
$drive->downloadToFile('remote-file.pdf', '/local/path/file.pdf');

// Check if file exists
if ($drive->exists('filename.txt')) {
    // File exists
}
```

### File Management

```php
// Copy file
$newFileId = $drive->copy('source.txt', 'copy.txt', $targetFolderId);

// Move file to folder
$drive->move('filename.txt', $folderId);

// Rename file
$drive->rename('old-name.txt', 'new-name.txt');

// Delete file
$drive->delete('filename.txt');

// Delete by ID
$drive->deleteById($fileId);

// Get file information
$info = $drive->getFileInfo('filename.txt');
echo "Size: {$info['size']}, Modified: {$info['modifiedTime']}";
```

### Folder Operations

```php
// Create folder
$folderId = $drive->makeDirectory('My Folder');

// Create nested folder
$subfolderId = $drive->makeDirectory('Subfolder', $folderId);

// List all folders
$folders = $drive->folders();

// List folders in specific folder
$subfolders = $drive->folders($parentFolderId);

// Find folder ID by name
$folderId = $drive->findFolderId('My Folder');

// Delete folder
$drive->deleteDirectory('Folder Name');
```

### File Listing & Search

```php
// List all files
$files = $drive->files();

// List files in folder
$files = $drive->files($folderId);

// Search files by name
$results = $drive->search('vacation');

// Limit results
$files = $drive->files(null, 50); // Max 50 files
$results = $drive->search('query', 20); // Max 20 results
```

### File Sharing

```php
// Share with specific email
$drive->shareWithEmail('filename.txt', 'user@example.com', 'reader');

// Share with write access
$drive->shareWithEmail('filename.txt', 'user@example.com', 'writer');

// Make file public
$publicLink = $drive->makePublic('filename.txt');
echo "Public link: $publicLink";

// Get shareable link
$link = $drive->getShareableLink('filename.txt');
```

### Batch Operations

```php
// Delete multiple files
$filesToDelete = ['file1.txt', 'file2.txt', 'file3.txt'];
$results = $drive->deleteMultiple($filesToDelete);

// Backup entire folder
$backupResults = $drive->backupFolder($folderId, './backup-folder');
foreach ($backupResults as $filename => $result) {
    if ($result['success']) {
        echo "✅ Backed up: $filename\n";
    }
}
```

## Laravel Integration

### Controller Example

```php
<?php

namespace App\Http\Controllers;

use GoogleDrivePHP\GoogleDrive;
use Illuminate\Http\Request;

class DriveController extends Controller
{
    private $drive;
    
    public function __construct()
    {
        $this->drive = GoogleDrive::fromEnv();
    }
    
    public function upload(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileId = $this->drive->putFile(
                $file->getPathname(),
                $file->getClientOriginalName(),
                null,
                $file->getMimeType()
            );
            
            return response()->json(['fileId' => $fileId]);
        }
        
        return response()->json(['error' => 'No file uploaded'], 400);
    }
    
    public function download($filename)
    {
        $content = $this->drive->get($filename);
        
        if (!$content) {
            return abort(404);
        }
        
        return response($content)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }
    
    public function list()
    {
        $files = $this->drive->files();
        return response()->json($files);
    }
}
```

### Service Provider

```php
<?php

namespace App\Providers;

use GoogleDrivePHP\GoogleDrive;
use Illuminate\Support\ServiceProvider;

class GoogleDriveServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(GoogleDrive::class, function ($app) {
            return GoogleDrive::fromEnv();
        });
    }
}
```

### Laravel Integration
```php
// In Laravel Controller
use GoogleDrivePHP\Facades\GDrive;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $file = $request->file('upload');
        $fileId = GDrive::putFile($file->path(), $file->getClientOriginalName());
        
        return response()->json(['file_id' => $fileId]);
    }
    
    public function download($filename)
    {
        $content = GDrive::get($filename);
        return response($content)->header('Content-Type', 'application/octet-stream');
    }
}
```

### Backup System
```php
// Daily backup script
$backupData = file_get_contents('database.sql');
$filename = 'backup-' . date('Y-m-d') . '.sql';
$fileId = GoogleDrive::put($filename, $backupData);
echo "Backup uploaded: $fileId\n";
```

### File Manager
```php
// Create project structure
$projectId = GoogleDrive::makeDir('My Project');
$docsId = GoogleDrive::makeDir('Documents', $projectId);
$imagesId = GoogleDrive::makeDir('Images', $projectId);

// Upload files to folders
GoogleDrive::put('readme.txt', $content, $docsId);
GoogleDrive::putFile('logo.png', null, $imagesId);

// List project files
$projectFiles = GoogleDrive::all($projectId, true);
```

### Batch Processing
```php
// Upload multiple files
$files = [
    'file1.txt' => 'Content 1',
    'file2.txt' => 'Content 2', 
    'file3.txt' => 'Content 3'
];
$results = GoogleDrive::putMultiple($files);

// Process results
foreach ($results as $filename => $result) {
    if ($result['success']) {
        echo "✅ $filename uploaded (ID: {$result['fileId']})\n";
    } else {
        echo "❌ $filename failed: {$result['error']}\n";
    }
}
```

## 🔐 Credentials Setup

### Get Google Drive API Credentials

1. **Google Cloud Console**
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Create new project or select existing
   - Enable Google Drive API

2. **Create OAuth 2.0 Credentials**
   - Go to Credentials → Create Credentials → OAuth client ID
   - Choose "Desktop Application"
   - Download JSON file

3. **Get Refresh Token**
   ```bash
   php scripts/get_refresh_token.php
   ```

For detailed setup guide, see [CREDENTIALS.md](CREDENTIALS.md)

## 🏗️ Design Pattern

This library uses **Static Helper Pattern** inspired by [yaza-putu/laravel-google-drive-storage](https://github.com/yaza-putu/laravel-google-drive-storage):

- **Static Methods**: No object instantiation required
- **Auto-initialization**: Reads from environment automatically  
- **Facade Pattern**: Alternative access through GDrive facade
- **Factory Pattern**: Clean client creation and configuration
- **Interface Contract**: Ensures consistent API

For complete design pattern documentation, see [README_STATIC.md](README_STATIC.md)

## 📁 Examples

Check the [examples/](examples/) directory for complete usage examples:

- `examples/static_pattern_demo.php` - Complete feature demonstration
- `simple_static.php` - Simple usage example
- `static_example.php` - Advanced usage patterns

## 🧪 Testing

```bash
# Run tests
composer test

# Test specific feature
php simple_static.php
```

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Inspired by [yaza-putu/laravel-google-drive-storage](https://github.com/yaza-putu/laravel-google-drive-storage)
- Built on [Google API PHP Client](https://github.com/googleapis/google-api-php-client)
- Design patterns from Laravel ecosystem

## 📞 Support

- 📧 Email: faizn103a@gmail.com  
- 🐛 Issues: [GitHub Issues](https://github.com/faiznurullah/google-drive-php/issues)
- 📖 Documentation: [README_STATIC.md](README_STATIC.md)

---

**Made with ❤️ for the PHP community**

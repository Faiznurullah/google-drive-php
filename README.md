# Google Drive PHP Library

A simple and powerful PHP library for interacting with Google Drive API. This library provides an intuitive interface for managing files and folders in Google Drive with comprehensive examples and real-world usage patterns.

## ✨ Features

- 🚀 **Simple & Intuitive API** - Easy-to-use methods for all Google Drive operations
- 📁 **Complete File Management** - Upload, download, move, delete files and folders
- 🔄 **Real-world Examples** - Comprehensive examples for every operation
- 🔑 **Flexible Authentication** - Support for access tokens, refresh tokens, and environment variables
- ✅ **Production Ready** - Error handling, retry logic, and robust file operations
- 🧪 **Tested & Verified** - All examples tested and working
- 📝 **Well Documented** - Clear documentation with working code samples

## 📋 Requirements

- PHP 7.4 or higher
- Google Drive API credentials
- Composer for dependency management

## 🚀 Installation

```bash
# Clone or download this library
git clone https://github.com/faiznurullah/google-drive-php.git
cd google-drive-php

# Install dependencies
composer install
```

## ⚙️ Setup

### 1. Get Google Drive API Credentials

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create or select a project
3. Enable Google Drive API
4. Create OAuth 2.0 credentials
5. Get your Client ID, Client Secret, and Refresh Token

### 2. Configure Environment Variables

Create a `.env` file in the root directory:

```env
# Required credentials
GOOGLE_DRIVE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_DRIVE_CLIENT_SECRET=your-client-secret

# For automatic token refresh
GOOGLE_DRIVE_REFRESH_TOKEN=your-refresh-token

# Current access token (will be refreshed automatically)
GOOGLE_DRIVE_ACCESS_TOKEN=your-access-token
```

> 💡 **Need help getting tokens?** Use [Google OAuth 2.0 Playground](https://developers.google.com/oauthplayground) with your credentials.

### 3. Quick Test

```bash
# Test your credentials and basic operations
php quick_start.php
```

## 📖 Basic Usage

### Using SimpleDrive Helper Class

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/SimpleDrive.php';

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

use GoogleDrivePHP\SimpleDrive;

// Initialize from environment
$drive = SimpleDrive::fromEnv();

// Upload file
$fileId = $drive->put('test.txt', 'Hello World!');
echo "Uploaded: $fileId\n";

// Download file
$content = $drive->get('test.txt');
echo "Downloaded: $content\n";

// List files
$files = $drive->files();
foreach ($files as $file) {
    echo "- " . $file['name'] . " (" . $file['id'] . ")\n";
}

// Check if file exists
if ($drive->exists('test.txt')) {
    echo "File exists!\n";
}

// Delete file
$drive->delete('test.txt');
echo "File deleted\n";
```

### Direct Google API Usage

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

// Initialize Google Drive
$client = new Google\Client();
$client->setClientId(getenv('GOOGLE_DRIVE_CLIENT_ID'));
$client->setClientSecret(getenv('GOOGLE_DRIVE_CLIENT_SECRET'));
$client->setAccessToken(getenv('GOOGLE_DRIVE_ACCESS_TOKEN'));

$service = new Google\Service\Drive($client);

// Upload a file
$fileMetadata = new Google\Service\Drive\DriveFile(['name' => 'test.txt']);
$content = "Hello, Google Drive!";
$file = $service->files->create($fileMetadata, [
    'data' => $content,
    'mimeType' => 'text/plain',
    'uploadType' => 'multipart'
]);

echo "File uploaded with ID: " . $file->getId() . "\n";
```

## 🎯 Complete Examples Collection

This library includes comprehensive examples for all operations:

### 🚀 Quick Start
```bash
# Test all basic operations
php quick_start.php
```

**What it does:**
- ✅ Tests credentials
- ✅ Uploads a test file
- ✅ Lists files in your Drive
- ✅ Downloads the file
- ✅ Verifies content
- ✅ Cleans up test files

### 📋 Interactive Examples Menu
```bash
# Access all examples through interactive menu
php examples/operations/index.php
```

### 📁 Individual Operation Examples

#### 1. Upload Operations
```bash
php examples/operations/upload_example.php
```
**Features:**
- Upload from string content
- Upload from local files
- Upload JSON and CSV data
- Multiple file uploads
- Upload verification

#### 2. Move Operations
```bash
php examples/operations/move_example.php
```
**Features:**
- Create folders and subfolders
- Move files between folders
- Move folders to other folders
- Upload directly to specific folders
- List folder contents

#### 3. Delete Operations
```bash
php examples/operations/delete_example.php
```
**Features:**
- Delete individual files
- Backup files before deletion
- Delete multiple files by pattern
- Delete folders recursively
- Safe delete with confirmation

#### 4. Download Operations
```bash
php examples/operations/download_example.php
```
**Features:**
- Download individual files
- Batch download multiple files
- Download to specific local paths
- Download with progress tracking
- File information retrieval

## 🛠️ Core Classes

### SimpleDrive
Main helper class for common operations:

```php
$drive = SimpleDrive::fromEnv();

// File operations
$fileId = $drive->put($filename, $content);           // Upload file
$content = $drive->get($filename);                    // Download file
$success = $drive->delete($filename);                 // Delete file
$exists = $drive->exists($filename);                  // Check if exists
$files = $drive->files();                             // List all files

// Folder operations
$folderId = $drive->makeDirectory($folderName);       // Create folder
```

### Extended Classes
Examples include extended classes with advanced features:

- **`DriveManager`** - Advanced move and folder operations
- **`DriveDeleter`** - Safe deletion with backup options  
- **`DriveDownloader`** - Batch downloads with progress tracking

## 🔧 Error Handling

The library includes robust error handling:

```php
try {
    $content = $drive->get('test.txt');
    echo "Downloaded: $content\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    // Handle specific errors
    if (strpos($e->getMessage(), 'File not found') !== false) {
        echo "The file doesn't exist\n";
    } elseif (strpos($e->getMessage(), 'Unauthorized') !== false) {
        echo "Check your credentials\n";
    }
}
```

## 📊 Authentication Methods

### Method 1: Environment Variables (Recommended)
```php
// Load from .env file automatically
$drive = SimpleDrive::fromEnv();
```

### Method 2: Direct Initialization
```php
$drive = new SimpleDrive(
    clientId: 'your-client-id',
    clientSecret: 'your-client-secret', 
    refreshToken: 'your-refresh-token',
    accessToken: 'your-access-token'
);
```

### Method 3: Google Client Direct
```php
$client = new Google\Client();
$client->setClientId('your-client-id');
$client->setClientSecret('your-client-secret');
$client->setAccessToken('your-access-token');

$service = new Google\Service\Drive($client);
```

## 🔍 Testing Your Setup

### Basic Credential Test
```bash
php test_credentials.php
```

### Quick Operations Test
```bash
php quick_start.php
```

### Specific Operation Tests
```bash
# Test specific file operations
php test_json_data.php        # Test delete operation
php final_test_new.php        # Comprehensive test
```

## 📚 Dependencies

This library uses:
- `google/apiclient` - Google API Client Library
- `google/apiclient-services` - Google Drive API services
- Standard PHP extensions (json, curl, openssl)

Already included via composer.json:
```json
{
    "require": {
        "google/apiclient": "^2.0"
    }
}
```

## 🎯 Common Use Cases

### 1. File Backup System
```php
// Backup local files to Google Drive
$drive = SimpleDrive::fromEnv();
$localFiles = glob('/path/to/backup/*');

foreach ($localFiles as $file) {
    $content = file_get_contents($file);
    $fileId = $drive->put(basename($file), $content);
    echo "Backed up: " . basename($file) . " (ID: $fileId)\n";
}
```

### 2. Data Export to Drive
```php
// Export data as JSON to Google Drive
$data = [
    'export_date' => date('Y-m-d H:i:s'),
    'records' => fetchDataFromDatabase()
];

$jsonContent = json_encode($data, JSON_PRETTY_PRINT);
$fileId = $drive->put('export_' . date('Y-m-d') . '.json', $jsonContent);
echo "Data exported with ID: $fileId\n";
```

### 3. File Processing Pipeline
```php
// Download, process, and re-upload files
$files = $drive->files();

foreach ($files as $file) {
    if (strpos($file['name'], '.txt') !== false) {
        // Download file
        $content = $drive->get($file['name']);
        
        // Process content
        $processedContent = strtoupper($content);
        
        // Upload processed version
        $newName = 'processed_' . $file['name'];
        $drive->put($newName, $processedContent);
        
        echo "Processed: {$file['name']} -> $newName\n";
    }
}
```

## 🚨 Troubleshooting

### Common Issues

#### 1. Authentication Errors
```
Error: Unauthorized / Invalid credentials
```
**Solution:**
- Check your Client ID and Client Secret in `.env`
- Verify your refresh token is not expired
- Regenerate access token using Google OAuth Playground

#### 2. File Not Found
```
Error: File not found
```
**Solution:**
- Verify file exists in Google Drive using `$drive->files()`
- Check file permissions and sharing settings
- Ensure case-sensitive filename matching

#### 3. Permission Denied
```
Error: The user does not have sufficient permissions
```
**Solution:**
- Check API scopes in your Google Cloud Console
- Ensure your OAuth app has Drive API permissions
- Verify you have edit permissions for the file

#### 4. Token Expired
```
Error: Invalid credentials (access token expired)
```
**Solution:**
- Use refresh token for automatic token renewal
- Regenerate tokens from Google OAuth Playground
- Check token expiration time

### Getting Fresh Tokens

Use Google OAuth 2.0 Playground:
1. Go to https://developers.google.com/oauthplayground
2. Select "Drive API v3" scopes
3. Authorize with your Google account
4. Exchange authorization code for tokens
5. Use the refresh token in your `.env` file

### Debug Mode

Enable detailed error reporting:
```php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test credentials step by step
$drive = SimpleDrive::fromEnv();
echo "Connection successful!\n";
```

## 📄 File Structure

```
google-drive-php/
├── src/
│   └── SimpleDrive.php         # Main library class
├── examples/
│   ├── operations/
│   │   ├── index.php          # Interactive menu
│   │   ├── upload_example.php # Upload operations
│   │   ├── move_example.php   # Move operations
│   │   ├── delete_example.php # Delete operations
│   │   └── download_example.php # Download operations
│   └── README.md              # Examples documentation
├── vendor/                     # Composer dependencies
├── .env                       # Your credentials
├── composer.json              # Dependencies
├── quick_start.php            # Quick test script
├── test_credentials.php       # Credential tester
└── README.md                  # This file
```

## 📄 License

This library is open-sourced software licensed under the MIT license.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Test your changes with the examples
4. Submit a pull request

## 📞 Support

For issues and questions:
1. Check the comprehensive examples in `/examples/operations/`
2. Review the troubleshooting section above
3. Test with the provided test files
4. Create an issue with detailed error information

---

## 🎉 Quick Start Summary

```bash
# 1. Install dependencies
composer install

# 2. Setup .env with your credentials
cp .env.example .env
# Edit .env with your Google Drive credentials

# 3. Test your setup
php quick_start.php

# 4. Try specific operations
php examples/operations/upload_example.php
php examples/operations/download_example.php
php examples/operations/move_example.php
php examples/operations/delete_example.php

# 5. Use interactive menu
php examples/operations/index.php
```

**🎯 Your Google Drive PHP library is ready to use!**

For complete examples documentation, see [`EXAMPLES_COMPLETE.md`](EXAMPLES_COMPLETE.md)

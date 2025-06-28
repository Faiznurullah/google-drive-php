<?php
/**
 * Google Drive PHP Library - Operation Examples Index
 * 
 * Menu untuk menjalankan contoh-contoh operasi Google Drive:
 * 1. Upload Example - Upload file dengan berbagai cara
 * 2. Move Example - Pindah file dan folder
 * 3. Delete Example - Hapus file dan folder
 * 4. Download Example - Download file dan folder
 */

require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment variables dari .env
if (file_exists(__DIR__ . '/../../.env')) {
    $env = parse_ini_file(__DIR__ . '/../../.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

function displayMenu() {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🚀 GOOGLE DRIVE PHP LIBRARY - OPERATION EXAMPLES\n";
    echo str_repeat("=", 60) . "\n";
    echo "Pilih operasi yang ingin dijalankan:\n\n";
    echo "1. 📤 Upload Example - Upload file dengan berbagai cara\n";
    echo "2. 📁 Move Example - Pindah file dan folder\n";
    echo "3. 🗑️  Delete Example - Hapus file dan folder\n";
    echo "4. 📥 Download Example - Download file dan folder\n";
    echo "5. 🔄 Run All Examples - Jalankan semua contoh\n";
    echo "6. ℹ️  Show Info - Tampilkan informasi library\n";
    echo "0. ❌ Exit\n";
    echo str_repeat("-", 60) . "\n";
    echo "Masukkan pilihan (0-6): ";
}

function checkCredentials() {
    $required = [
        'GOOGLE_DRIVE_CLIENT_ID',
        'GOOGLE_DRIVE_CLIENT_SECRET', 
        'GOOGLE_DRIVE_REFRESH_TOKEN'
    ];
    
    $missing = [];
    foreach ($required as $key) {
        if (!getenv($key)) {
            $missing[] = $key;
        }
    }
    
    if (!empty($missing)) {
        echo "❌ Credential tidak lengkap. Yang hilang:\n";
        foreach ($missing as $key) {
            echo "   - $key\n";
        }
        echo "\nSilakan lengkapi file .env terlebih dahulu.\n";
        return false;
    }
    
    echo "✅ Credentials valid\n";
    return true;
}

function runExample(string $filename, string $description) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🚀 MENJALANKAN: $description\n";
    echo str_repeat("=", 60) . "\n";
    
    $fullPath = __DIR__ . "/$filename";
    
    if (!file_exists($fullPath)) {
        echo "❌ File tidak ditemukan: $fullPath\n";
        return false;
    }
    
    echo "📁 File: $filename\n";
    echo "⏱️  Mulai: " . date('Y-m-d H:i:s') . "\n\n";
    
    $startTime = microtime(true);
    
    // Capture output
    ob_start();
    try {
        include $fullPath;
        $output = ob_get_contents();
        ob_end_clean();
        
        echo $output;
        
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        
        echo "\n" . str_repeat("-", 60) . "\n";
        echo "✅ Selesai dalam {$duration} detik\n";
        echo "⏱️  Selesai: " . date('Y-m-d H:i:s') . "\n";
        
        return true;
        
    } catch (Exception $e) {
        ob_end_clean();
        echo "❌ Error: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
        return false;
    }
}

function showInfo() {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "ℹ️  INFORMASI GOOGLE DRIVE PHP LIBRARY\n";
    echo str_repeat("=", 60) . "\n";
    
    echo "📚 Library: Google Drive PHP Integration\n";
    echo "🔧 Framework: Standalone PHP dengan Google API Client\n";
    echo "📝 Version: 1.0.0\n";
    echo "👨‍💻 Author: Custom Implementation\n\n";
    
    echo "🎯 FITUR UTAMA:\n";
    echo "   • Upload file (dari string, file lokal, JSON)\n";
    echo "   • Download file dan folder\n";
    echo "   • List file dan folder\n";
    echo "   • Pindah file/folder ke folder lain\n";
    echo "   • Hapus file dan folder\n";
    echo "   • Buat folder\n";
    echo "   • Error handling yang robust\n\n";
    
    echo "📁 STRUKTUR FILE:\n";
    echo "   • src/SimpleDrive.php - Class utama\n";
    echo "   • examples/operations/ - Contoh penggunaan\n";
    echo "   • .env - Konfigurasi credentials\n\n";
    
    echo "🔧 REQUIREMENTS:\n";
    echo "   • PHP 7.4+\n";
    echo "   • Composer\n";
    echo "   • Google API Client\n";
    echo "   • Google Drive API credentials\n\n";
    
    echo "📖 CARA PENGGUNAAN:\n";
    echo "   1. Setup credentials di .env\n";
    echo "   2. Jalankan composer install\n";
    echo "   3. Pilih operasi yang diinginkan\n";
    echo "   4. Ikuti instruksi di setiap contoh\n\n";
    
    // Check environment
    echo "🔍 STATUS ENVIRONMENT:\n";
    checkCredentials();
    
    echo "\n📋 CONTOH OPERASI TERSEDIA:\n";
    $examples = [
        'upload_example.php' => 'Upload file dengan berbagai metode',
        'move_example.php' => 'Memindahkan file dan folder',
        'delete_example.php' => 'Menghapus file dan folder',
        'download_example.php' => 'Download file dan folder'
    ];
    
    foreach ($examples as $file => $desc) {
        $status = file_exists(__DIR__ . "/$file") ? "✅" : "❌";
        echo "   $status $file - $desc\n";
    }
}

function waitForInput() {
    echo "\nTekan Enter untuk kembali ke menu...";
    fgets(STDIN);
}

// Main program
clear_screen();

function clear_screen() {
    // Clear screen (cross-platform)
    if (PHP_OS_FAMILY === 'Windows') {
        system('cls');
    } else {
        system('clear');
    }
}

echo "🌟 SELAMAT DATANG DI GOOGLE DRIVE PHP LIBRARY\n";
echo "==============================================\n\n";

// Check credentials first
if (!checkCredentials()) {
    echo "\nSilakan setup credentials terlebih dahulu.\n";
    echo "Lihat file SETUP.md untuk panduan lengkap.\n";
    exit(1);
}

while (true) {
    displayMenu();
    
    $handle = fopen("php://stdin", "r");
    $choice = trim(fgets($handle));
    fclose($handle);
    
    switch ($choice) {
        case '1':
            runExample('upload_example.php', 'Upload Example');
            waitForInput();
            break;
            
        case '2':
            runExample('move_example.php', 'Move Example');
            waitForInput();
            break;
            
        case '3':
            runExample('delete_example.php', 'Delete Example');
            waitForInput();
            break;
            
        case '4':
            runExample('download_example.php', 'Download Example');
            waitForInput();
            break;
            
        case '5':
            echo "\n🔄 MENJALANKAN SEMUA CONTOH...\n";
            
            $examples = [
                'upload_example.php' => 'Upload Example',
                'move_example.php' => 'Move Example', 
                'delete_example.php' => 'Delete Example',
                'download_example.php' => 'Download Example'
            ];
            
            foreach ($examples as $file => $desc) {
                runExample($file, $desc);
                echo "\n" . str_repeat("⭐", 20) . "\n";
                sleep(2); // Pause between examples
            }
            
            echo "\n🎉 SEMUA CONTOH SELESAI DIJALANKAN!\n";
            waitForInput();
            break;
            
        case '6':
            showInfo();
            waitForInput();
            break;
            
        case '0':
            echo "\n👋 Terima kasih telah menggunakan Google Drive PHP Library!\n";
            echo "🌟 Semoga bermanfaat untuk project Anda.\n\n";
            exit(0);
            
        default:
            echo "\n❌ Pilihan tidak valid. Silakan pilih 0-6.\n";
            sleep(1);
            break;
    }
    
    clear_screen();
}

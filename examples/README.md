# Google Drive PHP Library - Examples

Folder ini berisi contoh-contoh lengkap penggunaan Google Drive PHP Library untuk berbagai operasi.

## 📁 Struktur Examples

```
examples/
├── operations/
│   ├── index.php              # Menu utama untuk menjalankan contoh
│   ├── upload_example.php     # Contoh upload file
│   ├── move_example.php       # Contoh memindahkan file/folder
│   ├── delete_example.php     # Contoh menghapus file/folder
│   ├── download_example.php   # Contoh download file/folder
│   └── downloads/            # Folder hasil download (auto-created)
└── direct_credentials_example.php  # Contoh dengan direct credentials
```

## 🚀 Cara Menjalankan

### 1. Quick Start (Recommended)
```bash
# Jalankan menu interaktif
cd examples/operations
php index.php
```

### 2. Jalankan Contoh Individual
```bash
# Upload example
php examples/operations/upload_example.php

# Move example  
php examples/operations/move_example.php

# Delete example
php examples/operations/delete_example.php

# Download example
php examples/operations/download_example.php
```

## 📖 Penjelasan Setiap Contoh

### 1. Upload Example (`upload_example.php`)
Mendemonstrasikan berbagai cara upload file:
- ✅ Upload dari string
- ✅ Upload dari file lokal
- ✅ Upload JSON data
- ✅ Upload dengan nama file kompleks
- ✅ Upload multiple files
- ✅ Verifikasi upload dengan listing

**Output:**
- File-file test akan di-upload ke Google Drive
- Menampilkan ID file yang berhasil di-upload
- Verifikasi dengan listing files

### 2. Move Example (`move_example.php`)
Mendemonstrasikan operasi pemindahan:
- ✅ Membuat folder test
- ✅ Memindahkan file ke folder
- ✅ Memindahkan folder ke folder lain
- ✅ Upload file langsung ke folder
- ✅ List file dalam folder
- ✅ Pindah file antar folder

**Fitur Khusus:**
- Extended class `DriveManager` dengan method tambahan
- Method `moveFileToFolder()` dan `moveFolderToFolder()`
- Method `putInFolder()` untuk upload langsung ke folder
- Method `listFilesInFolder()` untuk list isi folder

### 3. Delete Example (`delete_example.php`)
Mendemonstrasikan berbagai cara penghapusan:
- ✅ Hapus file individual
- ✅ Backup file sebelum dihapus
- ✅ Hapus multiple files berdasarkan pattern
- ✅ Hapus folder beserta isinya
- ✅ Safe delete dengan konfirmasi
- ✅ Verifikasi penghapusan

**Fitur Khusus:**
- Extended class `DriveDeleter` dengan method tambahan
- Method `backupAndDelete()` untuk backup otomatis
- Method `deleteByPattern()` untuk hapus berdasarkan nama
- Method `safeDelete()` dengan konfirmasi user
- Method `deleteFolderAndContents()` untuk hapus folder rekursif

### 4. Download Example (`download_example.php`)
Mendemonstrasikan berbagai cara download:
- ✅ Download file individual
- ✅ Download ke path lokal spesifik
- ✅ Download multiple files sekaligus
- ✅ Download dengan progress tracking
- ✅ Get informasi file sebelum download
- ✅ List hasil download lokal

**Fitur Khusus:**
- Extended class `DriveDownloader` dengan method tambahan
- Method `downloadToFile()` untuk save ke file lokal
- Method `downloadMultiple()` untuk batch download
- Method `downloadWithProgress()` dengan progress info
- Auto-create directory struktur lokal
- File info dan size tracking

## 🛠️ Prerequisites

Pastikan Anda sudah:

1. **Setup Credentials** - File `.env` sudah dikonfigurasi dengan benar
2. **Install Dependencies** - `composer install` sudah dijalankan
3. **Valid Access Token** - Token Google Drive masih valid

## 📋 Environment Variables

Pastikan file `.env` berisi:
```env
GOOGLE_DRIVE_CLIENT_ID=your_client_id
GOOGLE_DRIVE_CLIENT_SECRET=your_client_secret
GOOGLE_DRIVE_REFRESH_TOKEN=your_refresh_token
GOOGLE_DRIVE_ACCESS_TOKEN=your_access_token
```

## 🔧 Extended Classes

Setiap example menggunakan extended class dari `SimpleDrive` untuk menambahkan fungsi-fungsi khusus:

### DriveManager (Move Example)
- `findFolder()` - Cari folder berdasarkan nama
- `moveFileToFolder()` - Pindah file ke folder
- `moveFolderToFolder()` - Pindah folder ke folder lain
- `putInFolder()` - Upload langsung ke folder
- `listFilesInFolder()` - List isi folder

### DriveDeleter (Delete Example)  
- `deleteByName()` - Hapus berdasarkan nama
- `deleteFolderAndContents()` - Hapus folder rekursif
- `deleteByPattern()` - Hapus berdasarkan pattern
- `backupAndDelete()` - Backup lalu hapus
- `safeDelete()` - Hapus dengan konfirmasi

### DriveDownloader (Download Example)
- `downloadById()` - Download berdasarkan ID
- `downloadToFile()` - Download ke file lokal
- `downloadMultiple()` - Batch download
- `downloadWithProgress()` - Download dengan progress
- `listFolderFiles()` - List file dalam folder

## 🎯 Tips Penggunaan

1. **Jalankan Satu per Satu**: Untuk pemahaman yang lebih baik, jalankan setiap example secara individual
2. **Periksa Google Drive**: Buka Google Drive di browser untuk melihat hasil operasi
3. **Monitor Output**: Perhatikan output di terminal untuk tracking progress
4. **Backup Important Data**: Untuk delete example, pastikan data penting sudah di-backup
5. **Check Downloads**: Periksa folder `downloads/` untuk hasil download

## ⚠️ Perhatian

- **Rate Limiting**: Google Drive API memiliki rate limit, jangan jalankan terlalu cepat
- **Storage Quota**: Perhatikan quota Google Drive Anda
- **File Permissions**: Pastikan aplikasi memiliki permission yang tepat
- **Network Connection**: Operasi memerlukan koneksi internet yang stabil

## 🐛 Troubleshooting

Jika mengalami error:

1. **Invalid Credentials**: Periksa file `.env` dan regenerate token jika perlu
2. **Permission Denied**: Pastikan scope Drive API sudah benar
3. **File Not Found**: Periksa nama file dan pastikan file ada di Drive
4. **Network Error**: Periksa koneksi internet
5. **Token Expired**: Generate refresh token baru menggunakan script helper

## 📞 Support

Untuk bantuan lebih lanjut:
- Periksa file `TESTING_GUIDE.md` untuk troubleshooting
- Lihat `SETUP.md` untuk setup ulang credentials
- Jalankan `debug_step_by_step.php` untuk diagnosis masalah

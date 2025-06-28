<?php

declare(strict_types=1);

namespace GoogleDrivePHP\Contracts;

interface GoogleDriveInterface
{
    /**
     * Get the contents of a file.
     *
     * @param string $path The path to the file
     * @return string|null The file contents or null if not found
     */
    public function get(string $path): ?string;

    /**
     * Store the given contents at the given path.
     *
     * @param string $path The path where to store the contents
     * @param string $contents The contents to store
     * @param array<string, mixed> $options Additional options
     * @return bool True on success, false on failure
     */
    public function put(string $path, string $contents, array $options = []): bool;

    /**
     * Store the given file at the given path.
     *
     * @param string $path The path where to store the file
     * @param resource|string|\SplFileInfo $file The file to store
     * @param array<string, mixed> $options Additional options
     * @return bool True on success, false on failure
     */
    public function putFile(string $path, $file, array $options = []): bool;

    /**
     * Get a read-stream for the file at the given path.
     *
     * @param string $path The path to the file
     * @return resource|null The file stream or null if not found
     */
    public function readStream(string $path);

    /**
     * Store the given stream at the given path.
     *
     * @param string $path The path where to store the stream
     * @param resource $resource The stream resource
     * @param array<string, mixed> $options Additional options
     * @return bool True on success, false on failure
     */
    public function writeStream(string $path, $resource, array $options = []): bool;

    /**
     * Delete the file at the given path.
     *
     * @param string $path The path to the file to delete
     * @return bool True on success, false on failure
     */
    public function delete(string $path): bool;

    /**
     * Determine if a file exists.
     *
     * @param string $path The path to check
     * @return bool True if exists, false otherwise
     */
    public function exists(string $path): bool;

    /**
     * Get the file size of a given file.
     *
     * @param string $path The path to the file
     * @return int The file size in bytes
     * @throws GoogleDriveException If file not found
     */
    public function size(string $path): int;

    /**
     * Get the last modification time of the file.
     *
     * @param string $path The path to the file
     * @return int The last modification timestamp
     * @throws GoogleDriveException If file not found
     */
    public function lastModified(string $path): int;

    /**
     * Get an array of all files in a directory.
     *
     * @param string $directory The directory path to list
     * @return array<int, array<string, mixed>> Array of file information
     */
    public function files(string $directory = ''): array;

    /**
     * Get all directories within a given directory.
     *
     * @param string $directory The directory path to list
     * @return array<int, array<string, mixed>> Array of directory information
     */
    public function directories(string $directory = ''): array;

    /**
     * Create a directory.
     *
     * @param string $path The directory path to create
     * @return bool True on success, false on failure
     */
    public function makeDirectory(string $path): bool;

    /**
     * Recursively delete a directory.
     *
     * @param string $path The directory path to delete
     * @return bool True on success, false on failure
     */
    public function deleteDirectory(string $path): bool;

    /**
     * Copy a file to a new location.
     *
     * @param string $from The source file path
     * @param string $to The destination file path
     * @return bool True on success, false on failure
     */
    public function copy(string $from, string $to): bool;

    /**
     * Move a file to a new location.
     *
     * @param string $from The source file path
     * @param string $to The destination file path
     * @return bool True on success, false on failure
     */
    public function move(string $from, string $to): bool;
}

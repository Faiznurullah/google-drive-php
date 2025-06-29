<?php

namespace GoogleDrivePHP\Support;

use Google\Client;
use Google\Service\Drive;
use InvalidArgumentException;

/**
 * Google Drive Factory
 * 
 * Factory untuk membuat instance Google Drive dengan berbagai konfigurasi
 */
class GoogleDriveFactory
{
    /**
     * Create Google Drive instance dari environment variables
     */
    public static function fromEnv(): array
    {
        $clientId = $_ENV['GOOGLE_DRIVE_CLIENT_ID'] ?? getenv('GOOGLE_DRIVE_CLIENT_ID');
        $clientSecret = $_ENV['GOOGLE_DRIVE_CLIENT_SECRET'] ?? getenv('GOOGLE_DRIVE_CLIENT_SECRET');
        $refreshToken = $_ENV['GOOGLE_DRIVE_REFRESH_TOKEN'] ?? getenv('GOOGLE_DRIVE_REFRESH_TOKEN');
        $accessToken = $_ENV['GOOGLE_DRIVE_ACCESS_TOKEN'] ?? getenv('GOOGLE_DRIVE_ACCESS_TOKEN');

        if (!$clientId || !$clientSecret || !$refreshToken) {
            throw new InvalidArgumentException('Missing required environment variables: GOOGLE_DRIVE_CLIENT_ID, GOOGLE_DRIVE_CLIENT_SECRET, GOOGLE_DRIVE_REFRESH_TOKEN');
        }

        return self::createClient($clientId, $clientSecret, $refreshToken, $accessToken);
    }

    /**
     * Create Google Drive instance dari credentials file
     */
    public static function fromCredentialsFile(string $credentialsPath, string $refreshToken, ?string $accessToken = null): array
    {
        if (!file_exists($credentialsPath)) {
            throw new InvalidArgumentException("Credentials file not found: {$credentialsPath}");
        }

        $credentials = json_decode(file_get_contents($credentialsPath), true);
        
        if (!isset($credentials['installed']) && !isset($credentials['web'])) {
            throw new InvalidArgumentException('Invalid credentials file format');
        }

        $clientConfig = $credentials['installed'] ?? $credentials['web'];

        return self::createClient(
            $clientConfig['client_id'],
            $clientConfig['client_secret'],
            $refreshToken,
            $accessToken
        );
    }

    /**
     * Create Google Drive instance dengan credentials langsung
     */
    public static function fromCredentials(string $clientId, string $clientSecret, string $refreshToken, ?string $accessToken = null): array
    {
        return self::createClient($clientId, $clientSecret, $refreshToken, $accessToken);
    }

    /**
     * Create Google Client dan Service
     */
    private static function createClient(string $clientId, string $clientSecret, string $refreshToken, ?string $accessToken = null): array
    {
        $client = new Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri('https://developers.google.com/oauthplayground');
        $client->setScopes(['https://www.googleapis.com/auth/drive']);
        $client->setAccessType('offline');

        // Set tokens
        if ($accessToken) {
            $client->setAccessToken($accessToken);
        }

        if ($refreshToken) {
            $client->fetchAccessTokenWithRefreshToken($refreshToken);
        }

        $service = new Drive($client);

        return [
            'client' => $client,
            'service' => $service
        ];
    }
}

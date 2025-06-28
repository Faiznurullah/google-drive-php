<?php

declare(strict_types=1);

namespace GoogleDrivePHP;

use GoogleDrivePHP\Auth\GoogleDriveClient;

class GoogleDriveBuilder
{
    protected ?string $credentials = null;
    protected ?string $clientId = null;
    protected ?string $clientSecret = null;
    protected ?string $refreshToken = null;
    protected ?string $redirectUri = null;
    /** @var array<string> */
    protected array $scopes = [];
    protected string $accessType = 'offline';
    protected ?string $accessToken = null;
    protected string $appName = 'Google Drive PHP Library';

    public static function create(): self
    {
        return new self();
    }

    /**
     * Set the path to the Google credentials JSON file.
     */
    public function withCredentials(string $credentialsPath): self
    {
        $this->credentials = $credentialsPath;
        return $this;
    }

    /**
     * Set Google Client credentials directly.
     */
    public function withClientCredentials(string $clientId, string $clientSecret, ?string $refreshToken = null): self
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     * Set refresh token.
     */
    public function withRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     * Set redirect URI.
     */
    public function withRedirectUri(string $redirectUri): self
    {
        $this->redirectUri = $redirectUri;
        return $this;
    }

    /**
     * Set the OAuth scopes for the Google Drive API.
     *
     * @param array<string> $scopes
     */
    public function withScopes(array $scopes): self
    {
        $this->scopes = $scopes;
        return $this;
    }

    /**
     * Set the access type (online or offline).
     */
    public function withAccessType(string $type): self
    {
        $this->accessType = $type;
        return $this;
    }

    /**
     * Set an existing access token.
     */
    public function withAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * Set the application name.
     */
    public function withAppName(string $appName): self
    {
        $this->appName = $appName;
        return $this;
    }

    /**
     * Configure for full Google Drive access.
     */
    public function withFullAccess(): self
    {
        $this->scopes = ['https://www.googleapis.com/auth/drive'];
        return $this;
    }

    /**
     * Configure for read-only Google Drive access.
     */
    public function withReadOnlyAccess(): self
    {
        $this->scopes = ['https://www.googleapis.com/auth/drive.readonly'];
        return $this;
    }

    /**
     * Configure for file-only Google Drive access.
     */
    public function withFileAccess(): self
    {
        $this->scopes = ['https://www.googleapis.com/auth/drive.file'];
        return $this;
    }

    /**
     * Build the GoogleDriveManager instance.
     */
    public function build(): GoogleDriveManager
    {
        $config = [
            'app_name' => $this->appName,
            'access_type' => $this->accessType,
        ];

        // Prioritize direct credentials over file credentials
        if ($this->clientId !== null && $this->clientSecret !== null) {
            $config['client_id'] = $this->clientId;
            $config['client_secret'] = $this->clientSecret;
            
            if ($this->redirectUri !== null) {
                $config['redirect_uri'] = $this->redirectUri;
            }
        } elseif ($this->credentials !== null) {
            $config['credentials'] = $this->credentials;
        }

        if ($this->refreshToken !== null) {
            $config['refresh_token'] = $this->refreshToken;
        }

        if (!empty($this->scopes)) {
            $config['scopes'] = $this->scopes;
        }

        if ($this->accessToken !== null) {
            $config['access_token'] = $this->accessToken;
        }

        $client = new GoogleDriveClient($config);
        return new GoogleDriveManager($client);
    }

    /**
     * Quick setup for basic usage with credentials file.
     */
    public static function quick(string $credentialsPath): GoogleDriveManager
    {
        return self::create()
            ->withCredentials($credentialsPath)
            ->withFullAccess()
            ->build();
    }

    /**
     * Quick setup using direct credentials.
     */
    public static function fromCredentials(string $clientId, string $clientSecret, ?string $refreshToken = null, ?string $accessToken = null): GoogleDriveManager
    {
        $builder = self::create()
            ->withClientCredentials($clientId, $clientSecret, $refreshToken)
            ->withFullAccess();

        if ($accessToken !== null) {
            $builder->withAccessToken($accessToken);
        }

        return $builder->build();
    }

    /**
     * Quick setup using environment variables.
     * Expected environment variables:
     * - GOOGLE_DRIVE_CLIENT_ID
     * - GOOGLE_DRIVE_CLIENT_SECRET
     * - GOOGLE_DRIVE_REFRESH_TOKEN (optional)
     * - GOOGLE_DRIVE_ACCESS_TOKEN (optional)
     */
    public static function fromEnvironment(): GoogleDriveManager
    {
        $clientId = $_ENV['GOOGLE_DRIVE_CLIENT_ID'] ?? getenv('GOOGLE_DRIVE_CLIENT_ID');
        $clientSecret = $_ENV['GOOGLE_DRIVE_CLIENT_SECRET'] ?? getenv('GOOGLE_DRIVE_CLIENT_SECRET');
        $refreshToken = $_ENV['GOOGLE_DRIVE_REFRESH_TOKEN'] ?? getenv('GOOGLE_DRIVE_REFRESH_TOKEN') ?: null;
        $accessToken = $_ENV['GOOGLE_DRIVE_ACCESS_TOKEN'] ?? getenv('GOOGLE_DRIVE_ACCESS_TOKEN') ?: null;

        if (empty($clientId) || empty($clientSecret)) {
            throw new \InvalidArgumentException('GOOGLE_DRIVE_CLIENT_ID and GOOGLE_DRIVE_CLIENT_SECRET environment variables are required');
        }

        return self::fromCredentials($clientId, $clientSecret, $refreshToken, $accessToken);
    }
}

<?php

declare(strict_types=1);

namespace GoogleDrivePHP\Auth;

use Google\Client;
use Google\Service\Drive;
use GoogleDrivePHP\Exceptions\GoogleDriveException;

class GoogleDriveClient
{
    protected Client $client;
    protected array $config;
    protected ?string $accessToken = null;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->initializeClient();
    }

    protected function initializeClient(): void
    {
        $this->client = new Client();
        
        // Set basic configuration
        $this->client->setApplicationName($this->config['app_name'] ?? 'Google Drive PHP Library');
        $this->client->setAccessType($this->config['access_type'] ?? 'offline');
        $this->client->setPrompt('select_account consent');

        // Set scopes
        if (isset($this->config['scopes'])) {
            $this->client->setScopes($this->config['scopes']);
        } else {
            $this->client->setScopes(['https://www.googleapis.com/auth/drive']);
        }

        // Set credentials - prioritize direct credentials over file
        if (isset($this->config['client_id']) && isset($this->config['client_secret'])) {
            $this->setDirectCredentials(
                $this->config['client_id'],
                $this->config['client_secret'],
                $this->config['redirect_uri'] ?? 'urn:ietf:wg:oauth:2.0:oob'
            );
        } elseif (isset($this->config['credentials'])) {
            $this->setCredentials($this->config['credentials']);
        }

        // Prepare token data array
        $tokenData = [];
        
        // Add access token if provided
        if (isset($this->config['access_token'])) {
            // If access token is JSON, parse it, otherwise use as string
            $accessTokenData = json_decode($this->config['access_token'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($accessTokenData)) {
                $tokenData = $accessTokenData;
            } else {
                $tokenData['access_token'] = $this->config['access_token'];
            }
        }

        // Add refresh token if provided
        if (isset($this->config['refresh_token'])) {
            $tokenData['refresh_token'] = $this->config['refresh_token'];
        }

        // Set the combined token data if we have any
        if (!empty($tokenData)) {
            $this->accessToken = json_encode($tokenData);
            $this->client->setAccessToken($tokenData);
        }
    }

    public function setCredentials(string $credentialsPath): self
    {
        if (!file_exists($credentialsPath)) {
            throw GoogleDriveException::invalidCredentials();
        }

        $this->client->setAuthConfig($credentialsPath);
        return $this;
    }

    public function setDirectCredentials(string $clientId, string $clientSecret, string $redirectUri = 'urn:ietf:wg:oauth:2.0:oob'): self
    {
        $this->client->setClientId($clientId);
        $this->client->setClientSecret($clientSecret);
        $this->client->setRedirectUri($redirectUri);
        return $this;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        
        // Parse token if it's JSON
        $tokenData = json_decode($accessToken, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($tokenData)) {
            $this->client->setAccessToken($tokenData);
        } else {
            // If it's just a plain access token string
            $this->client->setAccessToken($accessToken);
        }
        
        return $this;
    }

    public function authenticate(string $authCode = null): string
    {
        if ($authCode) {
            // Exchange authorization code for access token
            $token = $this->client->fetchAccessTokenWithAuthCode($authCode);
            
            if (isset($token['error'])) {
                throw GoogleDriveException::authenticationFailed($token['error_description'] ?? '');
            }

            $this->setAccessToken(json_encode($token));
            return $this->accessToken;
        }

        // Check if we have a valid access token
        if ($this->accessToken && !$this->client->isAccessTokenExpired()) {
            return $this->accessToken;
        }

        // Try to refresh the token if we have a refresh token
        if ($this->client->getRefreshToken()) {
            $token = $this->client->fetchAccessTokenWithRefreshToken();
            
            if (isset($token['error'])) {
                throw GoogleDriveException::authenticationFailed($token['error_description'] ?? '');
            }

            $this->setAccessToken(json_encode($token));
            return $this->accessToken;
        }

        // If we have an access token but no refresh token, try to use it anyway
        if ($this->accessToken) {
            return $this->accessToken;
        }

        throw GoogleDriveException::authenticationFailed('No valid authentication method available. Please provide either an auth code, access token, or refresh token.');
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    public function getGoogleClient(): Client
    {
        // Ensure we have a valid token
        if (!$this->accessToken || $this->client->isAccessTokenExpired()) {
            // Only authenticate if we have a refresh token or haven't tried yet
            if ($this->client->getRefreshToken() || !$this->accessToken) {
                $this->authenticate();
            }
        }

        return $this->client;
    }

    public function isAuthenticated(): bool
    {
        return $this->accessToken && !$this->client->isAccessTokenExpired();
    }

    public function revokeToken(): bool
    {
        if ($this->accessToken) {
            $this->client->revokeToken();
            $this->accessToken = null;
            return true;
        }

        return false;
    }
}

<?php

namespace App;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NetatmoApi
{
    private ?string $accessToken = null;

    private ?string $refreshToken = null;

    public function __construct(
        #[Autowire(env: 'NETATMO_API_CLIENT_ID')]
        private readonly string $clientId,
        #[Autowire(env: 'NETATMO_API_CLIENT_SECRET')]
        private readonly string $clientSecret,
        #[Autowire(env: 'NETATMO_HOME_ID')]
        private readonly string $homeId,
        #[Autowire('%kernel.project_dir%/var/tokens.json')]
        private readonly string $tokensFile,
        private readonly HttpClientInterface $httpClient,
    )
    {
        $this->loadTokens();
    }

    public function setTokens(string $accessToken, string $refreshToken): void
    {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;

        $this->saveTokens();
    }

    public function refreshToken()
    {
        $data = $this->httpClient->request('POST', 'https://api.netatmo.com/oauth2/token', [
            'body' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->refreshToken,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]
        ])->toArray();

        $this->setTokens($data['access_token'], $data['refresh_token']);
    }

    public function getStatus(): array
    {
        $response = $this->httpClient->request('GET', 'https://api.netatmo.com/api/homestatus', [
            'query' => [
                'home_id' => $this->homeId
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
            ],
        ]);

        if (403 === $response->getStatusCode()) {
            $this->refreshToken();

            return $this->getStatus();
        }

        dd($response->toArray());
    }

    private function saveTokens(): void
    {
        $json = json_encode([
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
        ]);

        file_put_contents($this->tokensFile, $json);
    }

    public function loadTokens(): void
    {
        if (false === file_exists($this->tokensFile)) {
            return;
        }

        $json = file_get_contents($this->tokensFile);
        $data = json_decode($json, true);

        if (array_key_exists('access_token', $data) && array_key_exists('refresh_token', $data)) {
            $this->accessToken = $data['access_token'];
            $this->refreshToken = $data['refresh_token'];
        }
    }
}
